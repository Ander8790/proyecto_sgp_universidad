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

        // ── Normalizar cédula: extraer solo los dígitos numéricos ──
        // El usuario ingresa solo números (ej: 12345678), pero en la BD
        // puede estar guardada como "V-12345678", "V12345678" o "12345678".
        // Se normaliza para buscar el sufijo numérico en ambos casos.
        $cedulaNumeros = preg_replace('/[^0-9]/', '', $cedula);

        // ── Validar que no es fin de semana ───────────────────────
        $diaSemana = (int)date('N'); // 1=Lun … 5=Vie, 6=Sáb, 7=Dom
        if ($diaSemana >= 6) {
            $nombreDia = $diaSemana === 6 ? 'sábado' : 'domingo';
            echo json_encode([
                'success' => false,
                'message' => "Hoy es {$nombreDia}. No se registra asistencia los fines de semana — solo días hábiles de lunes a viernes.",
            ]);
            exit;
        }

        // ── Validar que no es feriado ─────────────────────────────
        $hoyFecha = date('Y-m-d');
        $this->db->query("
            SELECT nombre FROM dias_feriados
            WHERE fecha = :hoy
            LIMIT 1
        ");
        $this->db->bind(':hoy', $hoyFecha);
        $feriadoHoy = $this->db->single();
        if ($feriadoHoy) {
            echo json_encode([
                'success'    => false,
                'is_feriado' => true,
                'message'    => "Hoy es feriado: {$feriadoHoy->nombre}. La asistencia de este día se registrará automáticamente como Justificado.",
                'feriado'    => $feriadoHoy->nombre,
            ]);
            exit;
        }

        // ── Buscar pasante activo por cédula (3NF: JOINs a tablas normalizadas) ──
        // FIX: Comparamos el sufijo numérico de la cédula para tolerar prefijos
        // como "V-", "E-", "V", etc., que pueden existir en la BD.
        $this->db->query("
            SELECT
                u.id,
                COALESCE(dp.nombres, u.correo)  AS nombres,
                COALESCE(dp.apellidos, '')        AS apellidos,
                u.pin_asistencia,
                COALESCE(dpa.estado_pasantia, 'Sin Asignar') AS estado_pasantia,
                d.nombre AS departamento_nombre
            FROM   usuarios u
            LEFT  JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT  JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos    d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE  REGEXP_REPLACE(u.cedula, '[^0-9]', '') = :cedula_num
              AND  u.rol_id  = 3
              AND  LOWER(u.estado) = 'activo'
            LIMIT 1
        ");
        $this->db->bind(':cedula_num', $cedulaNumeros);
        $pasante = $this->db->single();

        // ── Verificar que existe ───────────────────────────────────
        if (!$pasante) {
            echo json_encode(['success' => false, 'message' => 'Cédula no encontrada o usuario inactivo.']);
            exit;
        }

        // ── Verificar que está activo (asignado a departamento) ───
        // FIX: Comparación case-insensitive para tolerar variaciones de capitalización
        if (strtolower($pasante->estado_pasantia ?? '') !== 'activo') {
            echo json_encode([
                'success' => false,
                'message' => 'Tu pasantía aún no ha sido activada. Contacta al Administrador.',
            ]);
            exit;
        }

        // ── Verificar que el período académico del pasante está activo ─
        $this->db->query("
            SELECT pa.estado FROM datos_pasante dp
            LEFT JOIN periodos_academicos pa ON dp.periodo_id = pa.id
            WHERE dp.usuario_id = :pid
            LIMIT 1
        ");
        $this->db->bind(':pid', (int)$pasante->id);
        $periodo = $this->db->single();
        if ($periodo && !in_array(strtolower($periodo->estado ?? ''), ['activo', 'planificado'])) {
            echo json_encode([
                'success' => false,
                'message' => 'El período académico actual no está activo. Contacta al Administrador.',
            ]);
            exit;
        }

        // ── Verificar que el pasante tenga PIN configurado ────────
        if (empty($pasante->pin_asistencia)) {
            echo json_encode([
                'success' => false,
                'message' => 'No tienes un PIN de asistencia configurado. Contacta al Administrador para que te asigne uno.',
            ]);
            exit;
        }

        // ── Verificar PIN (BCRYPT hash) ───────────────────────────
        if (!password_verify($pin, $pasante->pin_asistencia)) {
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
            // ── LOG BITÁCORA (sin sesión — contexto público del Kiosco) ───────
            // El pasante ya está autenticado (cédula + bcrypt PIN), así que usamos
            // su ID directamente como usuario_id. No se requiere $_SESSION.
            try {
                $asistenciaId = (int)$this->db->lastInsertId();
                $this->db->query("
                    INSERT INTO bitacora
                        (usuario_id, accion, tabla_afectada, registro_id, ip_address, user_agent, detalles)
                    VALUES
                        (:uid, 'MARCAR_ASISTENCIA_KIOSCO', 'asistencias', :asistencia_id, :ip, :ua, :detalles)
                ");
                $this->db->bind(':uid',          $pasanteId);
                $this->db->bind(':asistencia_id', $asistenciaId);
                $this->db->bind(':ip',            $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN');
                $this->db->bind(':ua',            $_SERVER['HTTP_USER_AGENT'] ?? null);
                $this->db->bind(':detalles', json_encode([
                    'pasante' => trim($pasante->nombres . ' ' . $pasante->apellidos),
                    'cedula'  => $cedula,
                    'hora'    => $horaActual,
                    'retardo' => (bool)$esRetardo,
                    'metodo'  => 'Kiosco',
                ], JSON_UNESCAPED_UNICODE));
                $this->db->execute();
            } catch (\Throwable $e) {
                // El log de bitácora nunca debe interrumpir el flujo principal
                error_log('[SGP-KIOSCO] bitacora log error: ' . $e->getMessage());
            }

            // ── Notificación en campana al pasante ───────────────────────────
            try {
                require_once '../app/models/NotificationModel.php';
                $notifModel = new NotificationModel($this->db);
                $horaFmt    = date('h:i A');
                if ($esRetardo) {
                    $notifModel->create(
                        $pasanteId,
                        'asistencia_registrada',
                        'Asistencia registrada con retardo',
                        "Llegaste a las {$horaFmt}. Recuerda llegar antes de las 9:00 AM.",
                        URLROOT . '/pasante/asistencia'
                    );
                } else {
                    $notifModel->create(
                        $pasanteId,
                        'asistencia_registrada',
                        'Asistencia registrada',
                        "Tu asistencia del día de hoy fue registrada a las {$horaFmt}.",
                        URLROOT . '/pasante/asistencia'
                    );
                }
            } catch (\Throwable $e) {
                error_log('[SGP-KIOSCO] notif error: ' . $e->getMessage());
            }
            // ────────────────────────────────────────────────────────────────

            // ✅ PRO-RATA: El progreso se calcula dinámicamente desde la tabla 'asistencias'.
            // NO se suma ni resta a horas_acumuladas — 'asistencias' es la única fuente de verdad.
            echo json_encode([
                'success' => true,
                'message' => $esRetardo
                    ? '¡Registro exitoso! Recuerda llegar antes de las 9:00 AM.'
                    : '¡Asistencia registrada exitosamente!',
                'hora'    => date('h:i A'),
                'retardo' => (bool)$esRetardo,
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
     * Endpoint JSON — Consultar si hoy (o una fecha dada) es feriado.
     * GET /kiosco/esFeriado
     * GET /kiosco/esFeriado?fecha=YYYY-MM-DD
     *
     * Responde: { is_feriado: bool, nombre: string|null, fecha: string }
     */
    public function esFeriado(): void
    {
        header('Content-Type: application/json');
        header('Cache-Control: no-store');

        $fecha = trim($_GET['fecha'] ?? date('Y-m-d'));
        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            $fecha = date('Y-m-d');
        }

        $this->db->query("
            SELECT nombre FROM dias_feriados
            WHERE fecha = :fecha
            LIMIT 1
        ");
        $this->db->bind(':fecha', $fecha);
        $feriado = $this->db->single();

        echo json_encode([
            'is_feriado' => (bool)$feriado,
            'nombre'     => $feriado ? $feriado->nombre : null,
            'fecha'      => $fecha,
        ]);
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

        // FIX: Normalizar cédula (solo dígitos) para tolerar prefijos V-, E-, etc.
        $cedulaNumerosPinReset = preg_replace('/[^0-9]/', '', $cedula);

        // Buscar pasante interactuando con 3NF
        // FIX: LEFT JOIN para tolerar pasantes sin datos_personales completos
        $this->db->query("
            SELECT u.id,
                   COALESCE(dp.nombres, u.correo) AS nombres,
                   COALESCE(dp.apellidos, '')      AS apellidos,
                   dpa.tutor_id
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            WHERE REGEXP_REPLACE(u.cedula, '[^0-9]', '') = :cedula_num
              AND u.rol_id = 3
              AND LOWER(u.estado) = 'activo'
        ");
        $this->db->bind(':cedula_num', $cedulaNumerosPinReset);
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

        // Admin (enviamos a todos los admins activos)
        $this->db->query("SELECT id FROM usuarios WHERE rol_id = 1 AND estado = 'activo'");
        $admins = $this->db->resultSet();
        foreach ($admins as $admin) {
            $this->notificationModel->createWithRef(
                $admin->id, 'solicitud_pin', $titulo, $mensaje, $url,
                $pasante->id  // referencia_id = pasante para resolución global
            );
        }

        // Tutor (si existe)
        if (!empty($pasante->tutor_id)) {
            $this->notificationModel->createWithRef(
                $pasante->tutor_id, 'solicitud_pin', $titulo, $mensaje, $url,
                $pasante->id
            );
        }

        echo json_encode(['success' => true, 'message' => 'Solicitud enviada a tu administrador y tutor.']);
        exit;
    }
}
