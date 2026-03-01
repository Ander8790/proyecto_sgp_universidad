<?php
/**
 * EvaluacionesController — Gestión de Evaluaciones de Pasantes
 * 
 * Planilla digital de evaluación con 14 criterios agrupados por categoría.
 * Los datos se almacenan en la tabla `evaluaciones`.
 */

declare(strict_types=1);

class EvaluacionesController extends Controller {
    
    private $db;

    public function __construct() {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        $config    = require APPROOT . '/config/config.php';
        $this->db  = new Database($config['db']);
    }

    // ─────────────────────────────────────────────────────────
    // GET /evaluaciones  — Listado
    // ─────────────────────────────────────────────────────────
    public function index(): void
    {
        $rol_id = (int)(Session::get('role_id') ?? 0);

        // Obtener evaluaciones existentes con datos del pasante y tutor
        $this->db->query("
            SELECT 
                e.*,
                dp.nombres   AS pasante_nombres,
                dp.apellidos AS pasante_apellidos,
                u.cedula     AS pasante_cedula,
                tp.nombres   AS tutor_nombres,
                tp.apellidos AS tutor_apellidos
            FROM evaluaciones e
            LEFT JOIN datos_personales dp ON dp.usuario_id = e.pasante_id
            LEFT JOIN usuarios         u  ON u.id = e.pasante_id
            LEFT JOIN datos_personales tp ON tp.usuario_id = e.tutor_id
            ORDER BY e.created_at DESC
        ");
        $evaluaciones = $this->db->resultSet();

        // Pasantes activos (para el select del modal)
        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos, u.cedula
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            WHERE u.rol_id = 3 AND u.estado = 'activo'
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
        ");
        $pasantes = $this->db->resultSet();

        // Si es Tutor, obtener su ID para preseleccionar
        $tutorActualId = ($rol_id == 2) ? (int)Session::get('user_id') : null;

        // Tutores activos
        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            WHERE u.rol_id = 2 AND u.estado = 'activo'
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
        ");
        $tutores = $this->db->resultSet();

        $this->view('evaluaciones/index', [
            'evaluaciones'   => $evaluaciones,
            'pasantes'       => $pasantes,
            'tutores'        => $tutores,
            'tutorActualId'  => $tutorActualId,
            'total'          => count($evaluaciones),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // POST /evaluaciones/guardar  — Crear evaluación (AJAX)
    // ─────────────────────────────────────────────────────────
    public function guardar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        $pasanteId  = (int)($_POST['pasante_id'] ?? 0);
        $tutorId    = (int)($_POST['tutor_id']   ?? 0);
        $fecha      = $_POST['fecha_evaluacion']  ?? date('Y-m-d');
        $lapso      = trim($_POST['lapso_academico'] ?? '');
        $obs        = trim($_POST['observaciones']   ?? '');

        if (!$pasanteId || !$tutorId) {
            echo json_encode(['success' => false, 'message' => 'Pasante y Tutor son obligatorios']);
            return;
        }

        // Recoger los 14 criterios
        $criterios = [
            'criterio_iniciativa',    'criterio_interes',
            'criterio_conocimiento',  'criterio_analisis',
            'criterio_comunicacion',  'criterio_aprendizaje',
            'criterio_companerismo',  'criterio_cooperacion',
            'criterio_puntualidad',   'criterio_presentacion',
            'criterio_desarrollo',    'criterio_analisis_res',
            'criterio_conclusiones',  'criterio_recomendacion',
        ];

        $valores = [];
        $suma = 0;
        foreach ($criterios as $c) {
            $v = (int)($_POST[$c] ?? 0);
            if ($v < 1 || $v > 5) {
                echo json_encode(['success' => false, 'message' => "El criterio {$c} debe estar entre 1 y 5"]);
                return;
            }
            $valores[$c] = $v;
            $suma += $v;
        }

        $promedio = round($suma / count($criterios), 2);

        try {
            $this->db->query("
                INSERT INTO evaluaciones 
                    (pasante_id, tutor_id, fecha_evaluacion, lapso_academico,
                     criterio_iniciativa, criterio_interes, criterio_conocimiento,
                     criterio_analisis, criterio_comunicacion, criterio_aprendizaje,
                     criterio_companerismo, criterio_cooperacion, criterio_puntualidad,
                     criterio_presentacion, criterio_desarrollo, criterio_analisis_res,
                     criterio_conclusiones, criterio_recomendacion,
                     promedio_final, observaciones)
                VALUES 
                    (:pasante, :tutor, :fecha, :lapso,
                     :c1, :c2, :c3, :c4, :c5, :c6, :c7, :c8, :c9, :c10, :c11, :c12, :c13, :c14,
                     :promedio, :obs)
            ");

            $this->db->bind(':pasante', $pasanteId);
            $this->db->bind(':tutor',   $tutorId);
            $this->db->bind(':fecha',   $fecha);
            $this->db->bind(':lapso',   $lapso ?: null);
            
            $i = 1;
            foreach ($criterios as $c) {
                $this->db->bind(":c{$i}", $valores[$c]);
                $i++;
            }
            
            $this->db->bind(':promedio', $promedio);
            $this->db->bind(':obs',      $obs ?: null);

            if ($this->db->execute()) {
                // --- Notificar al Pasante ---
                $this->db->query("SELECT dp.nombres, dp.apellidos FROM datos_personales dp WHERE dp.usuario_id = :tutor_id");
                $this->db->bind(':tutor_id', $tutorId);
                $tutorInfo = $this->db->single();
                $nombreTutor = $tutorInfo ? trim(($tutorInfo->nombres ?? '') . ' ' . ($tutorInfo->apellidos ?? '')) : 'Tu tutor';

                require_once APPROOT . '/models/NotificationModel.php';
                $notificationModel = new NotificationModel($this->db);
                
                $lapsoTexto = $lapso ? " del lapso {$lapso}" : "";
                $notificationModel->create(
                    $pasanteId,
                    'evaluacion_nueva',
                    'Nueva Evaluación',
                    "{$nombreTutor} ha registrado tu evaluación{$lapsoTexto} con un promedio de {$promedio}/5.",
                    URLROOT . '/perfil'
                );

                echo json_encode([
                    'success'  => true,
                    'message'  => "Evaluación guardada exitosamente. Promedio: {$promedio}/5",
                    'promedio' => $promedio,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Error al guardar en la base de datos']);
            }
        } catch (\Exception $e) {
            error_log("Error evaluación: " . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }
}
