<?php
/**
 * KioscoController — Registro de Asistencia Público
 *
 * SEGURIDAD: Acceso público — NO requiere sesión.
 * El pasante se autentica con su Cédula + PIN de 4 dígitos.
 *
 * ARQUITECTURA 3NF:
 *   - cedula/nombres/apellidos  → datos_personales (JOIN)
 *   - estado_pasantia           → datos_pasante (JOIN)
 *   - horas_acumuladas          → datos_pasante (UPDATE)
 *
 * RUTAS:
 *   GET  /kiosco          → index()   Vista fullscreen del kiosco
 *   POST /kiosco/marcar   → marcar()  JSON endpoint — registra asistencia
 *
 * @version 3.0 — Arquitectura 3NF
 */
class KioscoController extends Controller
{
    private $db;

    public function __construct()
    {
        // ⚠️ NO Session::start() — El Kiosco es 100% independiente de cualquier sesión de administrador
        // ⚠️ NO AuthMiddleware — acceso público para pasantes
        $config  = require '../app/config/config.php';
        $this->db = new Database($config['db']);
    }

    /**
     * Vista fullscreen del kiosco — sin layout maestro.
     */
    public function index(): void
    {
        // ⚔️ ARQUITECTURA STATELESS: El Kiosco no utiliza ni inicia sesiones PHP.
        header("Cache-Control: no-store, no-cache, must-revalidate, max-age=0");
        header("Pragma: no-cache");

        $data = ['title' => 'Kiosco de Asistencia — SGP'];
        $this->view('kiosco/index', $data, false);
    }

    /**
     * Endpoint JSON — Marcar Asistencia.
     *
     * POST /kiosco/marcar
     * Body: cedula, pin_asistencia
     *
     * Responde con:
     *   { success: bool, message: string, pasante?: { nombres, apellidos, departamento } }
     */
    public function marcar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {

            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $cedula = trim($_POST['cedula']        ?? '');
        $pin    = trim($_POST['pin_asistencia'] ?? '');

        // ── Validación de formato ──────────────────────────────────
        if (!$cedula || !$pin) {
            echo json_encode(['success' => false, 'message' => 'Cédula y PIN son obligatorios.']);
            exit;
        }

        if (!preg_match('/^[0-9]{4}$/', $pin)) {
            echo json_encode(['success' => false, 'message' => 'El PIN debe tener 4 dígitos numéricos.']);
            exit;
        }

        // ── Buscar pasante activo por cédula (3NF: JOINs a tablas normalizadas) ──
        $this->db->query("
            SELECT
                u.id,
                dp.nombres,
                dp.apellidos,
                u.pin_asistencia,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                d.nombre AS departamento_nombre
            FROM   usuarios u
            INNER JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT  JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos    d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE  u.cedula = :cedula
              AND  u.rol_id  = 3
              AND  u.estado  = 'activo'
            LIMIT 1
        ");
        $this->db->bind(':cedula', $cedula);
        $pasante = $this->db->single();

        // ── Verificar que existe ───────────────────────────────────
        if (!$pasante) {
            echo json_encode(['success' => false, 'message' => 'Cédula no encontrada o usuario inactivo.']);
            exit;
        }

        // ── Verificar que está activo (asignado a departamento) ───
        if (($pasante->estado_pasantia ?? '') !== 'Activo') {
            echo json_encode([
                'success' => false,
                'message' => 'Tu pasantía aún no ha sido activada. Contacta al Administrador.',
            ]);
            exit;
        }

        // ── Verificar PIN (BCRYPT hash) ───────────────────────────
        if (!password_verify($pin, $pasante->pin_asistencia ?? '')) {
            echo json_encode(['success' => false, 'message' => 'PIN incorrecto. Inténtalo de nuevo.']);
            exit;
        }

        // ── VULN-02: Validar departamento asignado ────────────────
        if (empty($pasante->departamento_nombre)) {
            echo json_encode(['success' => false, 'message' => 'No tienes departamento asignado. Contacta al Administrador.']);
            exit;
        }

        // ── VULN-02: Validar rango de fechas de pasantía ──────────
        $this->db->query("SELECT fecha_inicio_pasantia, fecha_fin_estimada 
                           FROM datos_pasante WHERE usuario_id = :pid");
        $this->db->bind(':pid', (int)$pasante->id);
        $fechas = $this->db->single();
        if ($fechas) {
            $hoyCheck = date('Y-m-d');
            if ($fechas->fecha_inicio_pasantia && $hoyCheck < $fechas->fecha_inicio_pasantia) {
                echo json_encode(['success' => false, 'message' => 'Tu pasantía aún no ha iniciado. Fecha de inicio: ' . $fechas->fecha_inicio_pasantia]);
                exit;
            }
            if ($fechas->fecha_fin_estimada && $hoyCheck > $fechas->fecha_fin_estimada) {
                echo json_encode(['success' => false, 'message' => 'Tu pasantía ha expirado. Contacta al Administrador.']);
                exit;
            }
        }

        $hoy       = date('Y-m-d');
        $pasanteId = (int)$pasante->id;

        // ── Verificar si ya marcó hoy ──────────────────────────────
        $this->db->query("
            SELECT id FROM asistencias
            WHERE pasante_id = :pid AND fecha = :hoy
            LIMIT 1
        ");
        $this->db->bind(':pid', $pasanteId);
        $this->db->bind(':hoy', $hoy);
        $yaMarco = $this->db->single();

        if ($yaMarco) {
            echo json_encode([
                'success'     => false,
                'message'     => '✅ Ya registraste tu asistencia hoy. ¡Hasta mañana!',
                'ya_registro' => true,
            ]);
            exit;
        }

        // ── INSERT — Registrar asistencia ──────────────────────────
        // Detectar retardo: hora_registro > 09:00:00 → es_retardo = 1
        $horaActual = date('H:i:s');
        $esRetardo  = ($horaActual > '09:00:00') ? 1 : 0;

        $this->db->query("
            INSERT INTO asistencias
                (pasante_id, fecha, hora_registro, estado, metodo, es_retardo, es_auto_fill)
            VALUES
                (:pid, :fecha, :hora, 'Presente', 'Kiosco', :retardo, 0)
        ");
        $this->db->bind(':pid',     $pasanteId);
        $this->db->bind(':fecha',   $hoy);
        $this->db->bind(':hora',    $horaActual);
        $this->db->bind(':retardo', $esRetardo);

        $ok = $this->db->execute();

        if ($ok) {
            // ✅ PRO-RATA: El progreso se calcula dinámicamente desde la tabla 'asistencias'.
            // NO se suma ni resta a horas_acumuladas — 'asistencias' es la única fuente de verdad.
            echo json_encode([
                'success' => true,
                'message' => '¡Asistencia registrada exitosamente!',
                'hora'    => date('h:i A'),
                'pasante' => [
                    'nombres'      => $pasante->nombres,
                    'apellidos'    => $pasante->apellidos,
                    'departamento' => $pasante->departamento_nombre ?? 'Sin asignar',
                ],
            ]);
        } else {
            echo json_encode(['success' => false, 'message' => 'Error al guardar el registro. Intenta de nuevo.']);
        }

        exit;
    }

    /**
     * Endpoint JSON — Solicitar reseteo de PIN
     * POST /kiosco/solicitarResetPin
     * Body: cedula
     */
    public function solicitarResetPin(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $cedula = trim($_POST['cedula'] ?? '');

        if (!$cedula) {
            echo json_encode(['success' => false, 'message' => 'La cédula es obligatoria.']);
            exit;
        }

        // Buscar pasante interactuando con 3NF
        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos, dpa.tutor_id
            FROM usuarios u
            INNER JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            WHERE u.cedula = :cedula AND u.rol_id = 3 AND u.estado = 'activo'
        ");
        $this->db->bind(':cedula', $cedula);
        $pasante = $this->db->single();

        if (!$pasante) {
            echo json_encode(['success' => false, 'message' => 'No se encontró un pasante activo con esa cédula.']);
            exit;
        }

        // Insertar notificaciones para Admin (user 1) y Tutor (si tiene)
        require_once APPROOT . '/models/NotificationModel.php';
        $this->notificationModel = new NotificationModel($this->db);
        $titulo = "Solicitud de reseteo de PIN";
        $mensaje = "El pasante {$pasante->nombres} {$pasante->apellidos} (V-{$cedula}) ha olvidado su PIN y solicita un reseteo.";
        $url = URLROOT . "/configuracion#restablecer-pin";

        // Admin (se asume ID 1 o rol 1, enviaremos a todos los admins)
        $this->db->query("SELECT id FROM usuarios WHERE rol_id = 1 AND estado = 'activo'");
        $admins = $this->db->resultSet();
        foreach ($admins as $admin) {
            $this->notificationModel->create(
                $admin->id, 'solicitud_pin', $titulo, $mensaje, $url
            );
        }

        // Tutor
        if (!empty($pasante->tutor_id)) {
            $this->notificationModel->create(
                $pasante->tutor_id, 'solicitud_pin', $titulo, $mensaje, $url
            );
        }

        echo json_encode(['success' => true, 'message' => 'Solicitud enviada a tu administrador y tutor.']);
        exit;
    }
}
