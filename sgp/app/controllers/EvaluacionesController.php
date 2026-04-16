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
    private $evaluacionModel;

    public function __construct() {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        $config   = require APPROOT . '/config/config.php';
        $this->db = Database::getInstance();

        $this->evaluacionModel = $this->model('Evaluacion');
    }

    // ─────────────────────────────────────────────────────────
    // GET /evaluaciones  — Listado
    // ─────────────────────────────────────────────────────────
    public function index(): void
    {
        $rol_id        = (int)(Session::get('role_id') ?? 0);
        $tutorActualId = ($rol_id === 2) ? (int)Session::get('user_id') : null;

        // Tutor: solo ve evaluaciones de sus propios pasantes
        // Admin: ve todas las evaluaciones
        $evaluaciones = ($rol_id === 2)
            ? $this->evaluacionModel->getByTutor($tutorActualId)
            : $this->evaluacionModel->getAll();

        // Tutor: solo sus pasantes asignados / Admin: todos los pasantes activos
        $baseSelect = "
                SELECT u.id, dp.nombres, dp.apellidos, u.cedula,
                       COALESCE(d.nombre, 'Sin departamento') AS departamento,
                       dpa.estado_pasantia,
                       dpa.tutor_id,
                       COALESCE(pa.nombre, '') AS periodo_nombre,
                       CONCAT(tpdp.nombres, ' ', tpdp.apellidos) AS tutor_nombre,
                       COALESCE(inst.nombre, dpa.institucion_procedencia, 'Sin institución') AS institucion_nombre,
                       COALESCE(eval_stats.total_evals, 0)   AS total_evaluaciones,
                       eval_stats.ultima_fecha,
                       eval_stats.ultimo_promedio
                FROM usuarios u
                LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
                LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
                LEFT JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
                LEFT JOIN periodos_academicos pa ON pa.id = dpa.periodo_id
                LEFT JOIN usuarios         tu  ON tu.id = dpa.tutor_id
                LEFT JOIN datos_personales tpdp ON tpdp.usuario_id = dpa.tutor_id
                LEFT JOIN instituciones    inst ON (
                    dpa.institucion_procedencia REGEXP '^[0-9]+$'
                    AND inst.id = CAST(dpa.institucion_procedencia AS UNSIGNED)
                )
                LEFT JOIN (
                    SELECT pasante_id,
                           COUNT(*) AS total_evals,
                           MAX(fecha_evaluacion) AS ultima_fecha,
                           ROUND(AVG(promedio_final), 2) AS ultimo_promedio
                    FROM evaluaciones GROUP BY pasante_id
                ) eval_stats ON eval_stats.pasante_id = u.id
        ";

        if ($rol_id === 2) {
            $this->db->query($baseSelect . "
                WHERE u.rol_id = 3 AND u.estado = 'activo'
                  AND dpa.tutor_id = :tutor_id
                ORDER BY eval_stats.total_evals ASC, IFNULL(dp.apellidos, u.correo) ASC
            ");
            $this->db->bind(':tutor_id', $tutorActualId);
        } else {
            $this->db->query($baseSelect . "
                WHERE u.rol_id = 3 AND u.estado = 'activo'
                ORDER BY eval_stats.total_evals ASC, IFNULL(dp.apellidos, u.correo) ASC
            ");
        }
        $pasantes = $this->db->resultSet();

        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos, u.rol_id
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            WHERE u.rol_id IN (1, 2) AND u.estado = 'activo'
            ORDER BY u.rol_id DESC, IFNULL(dp.apellidos, u.correo) ASC
        ");
        $tutores = $this->db->resultSet();

        // ── Notificaciones: pasantes próximos a culminar sin evaluación ──────
        $this->_notificarCulminacionProxima();

        $this->view('evaluaciones/index', [
            'evaluaciones'  => $evaluaciones,
            'pasantes'      => $pasantes,
            'tutores'       => $tutores,
            'tutorActualId' => $tutorActualId,
            'total'         => count($evaluaciones),
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // GET /evaluaciones/nueva/{pasanteId} — Vista dedicada
    // ─────────────────────────────────────────────────────────
    public function nueva($pasanteId = null): void
    {
        $rol_id    = (int)(Session::get('role_id') ?? 0);
        $pasanteId = (int)($pasanteId ?? 0);

        // Solo tutores (rol 2) y admins (rol 1)
        if (!in_array($rol_id, [1, 2])) {
            $this->redirect('/evaluaciones');
            return;
        }

        // Sin pasante: mostrar selector de pasante (página dedicada, no modal)
        if ($pasanteId <= 0) {
            $tutorActualId = ($rol_id === 2) ? (int)Session::get('user_id') : null;
            if ($rol_id === 2) {
                $this->db->query("
                    SELECT u.id, dp.nombres, dp.apellidos, u.cedula,
                           d.nombre AS departamento
                    FROM usuarios u
                    LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
                    INNER JOIN datos_pasante dpa ON dpa.usuario_id = u.id
                    LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
                    WHERE u.rol_id = 3 AND u.estado = 'activo'
                      AND dpa.tutor_id = :tutor_id AND dpa.estado_pasantia = 'Activo'
                    ORDER BY IFNULL(dp.apellidos, u.correo) ASC
                ");
                $this->db->bind(':tutor_id', $tutorActualId);
            } else {
                $this->db->query("
                    SELECT u.id, dp.nombres, dp.apellidos, u.cedula,
                           d.nombre AS departamento
                    FROM usuarios u
                    LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
                    LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
                    LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
                    WHERE u.rol_id = 3 AND u.estado = 'activo'
                      AND COALESCE(dpa.estado_pasantia, 'Sin Asignar') = 'Activo'
                    ORDER BY IFNULL(dp.apellidos, u.correo) ASC
                ");
            }
            $this->view('evaluaciones/nueva', [
                'title'         => 'Nueva Evaluación — Seleccionar Pasante',
                'pasante'       => null,
                'pasantes'      => $this->db->resultSet(),
                'tutores'       => [],
                'tutorActualId' => $tutorActualId,
            ]);
            return;
        }

        // Resolver el pasante (con datos de asignación incluidos)
        $this->db->query("
            SELECT u.id, u.cedula, u.rol_id, u.estado,
                   dp.nombres, dp.apellidos,
                   d.nombre AS departamento,
                   dpa.tutor_id AS tutor_asignado_id,
                   COALESCE(pa.nombre, '') AS periodo_nombre,
                   COALESCE(pa.id, 0)     AS periodo_id
            FROM usuarios u
            LEFT JOIN datos_personales dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante    dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos    d   ON d.id = dpa.departamento_asignado_id
            LEFT JOIN periodos_academicos pa ON pa.id = dpa.periodo_id
            WHERE u.id = :id AND u.rol_id = 3 AND u.estado = 'activo'
            LIMIT 1
        ");
        $this->db->bind(':id', $pasanteId);
        $pasante = $this->db->single();

        if (!$pasante) {
            Session::setFlash('eval_error', 'Pasante no encontrado o inactivo.');
            $this->redirect('/evaluaciones');
            return;
        }

        // Guard: si es tutor, verificar que el pasante esté asignado a él
        $tutorActualId = ($rol_id === 2) ? (int)Session::get('user_id') : null;
        if ($rol_id === 2) {
            if ((int)($pasante->tutor_asignado_id ?? 0) !== $tutorActualId) {
                Session::setFlash('eval_error', 'No tienes permiso para evaluar a este pasante.');
                $this->redirect('/evaluaciones');
                return;
            }
        }

        // Para admin: preseleccionar el tutor asignado al pasante
        if ($rol_id === 1 && $tutorActualId === null) {
            $tutorActualId = (int)($pasante->tutor_asignado_id ?? 0) ?: null;
        }

        // Tutores activos para el select del admin
        $this->db->query("
            SELECT u.id, dp.nombres, dp.apellidos, u.rol_id
            FROM usuarios u
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            WHERE u.rol_id IN (1, 2) AND u.estado = 'activo'
            ORDER BY u.rol_id DESC, IFNULL(dp.apellidos, u.correo) ASC
        ");
        $tutores = $this->db->resultSet();

        $this->view('evaluaciones/nueva', [
            'title'           => 'Nueva Evaluación — ' . trim(($pasante->nombres ?? '') . ' ' . ($pasante->apellidos ?? '')),
            'pasante'         => $pasante,
            'tutores'         => $tutores,
            'tutorActualId'   => $tutorActualId,
            'periodoNombre'   => $pasante->periodo_nombre ?? '',
        ]);
    }

    // ─────────────────────────────────────────────────────────
    // POST /evaluaciones/guardar — Crear evaluación (AJAX)
    // ─────────────────────────────────────────────────────────
    public function guardar(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        // Solo Administradores (1) y Tutores (2) pueden guardar evaluaciones
        $rolId    = (int)(Session::get('role_id') ?? 0);
        $userId   = (int)(Session::get('user_id') ?? 0);
        if (!in_array($rolId, [1, 2])) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Sin permiso para registrar evaluaciones']);
            return;
        }

        // Validar CSRF (campo _csrf inyectado por CsrfHelper::field())
        $token = $_POST['_csrf'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido. Recarga la página e inténtalo de nuevo.']);
            return;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        $fecha     = trim($_POST['fecha_evaluacion'] ?? date('Y-m-d'));
        $obs       = trim($_POST['observaciones']    ?? '');

        // --- Lapso: se lee automáticamente desde datos_pasante → periodos_academicos ---
        // Ya no se recibe del formulario (eliminamos el campo manual).
        $lapso = '';
        if ($pasanteId > 0) {
            $this->db->query("
                SELECT COALESCE(pa.nombre, '') AS lapso_nombre
                FROM datos_pasante dpa
                LEFT JOIN periodos_academicos pa ON pa.id = dpa.periodo_id
                WHERE dpa.usuario_id = :pid
                LIMIT 1
            ");
            $this->db->bind(':pid', $pasanteId);
            $dpaInfo = $this->db->single();
            $lapso   = $dpaInfo ? trim($dpaInfo->lapso_nombre) : '';
        }

        // --- Tutor: Tutor usa siempre su propio id.
        //            Admin: usa el tutor asignado al pasante como valor por defecto,
        //            pero puede sobreescribirse con el select del formulario.
        if ($rolId === 2) {
            $tutorId = $userId;
        } else {
            $tutorIdPost = (int)($_POST['tutor_id'] ?? 0);
            if ($tutorIdPost > 0) {
                // Admin seleccionó un tutor manualmente
                $tutorId = $tutorIdPost;
            } else {
                // Auto: usar el tutor asignado al pasante
                $this->db->query("SELECT tutor_id FROM datos_pasante WHERE usuario_id = :pid LIMIT 1");
                $this->db->bind(':pid', $pasanteId);
                $dpaFallback = $this->db->single();
                $tutorId     = (int)($dpaFallback->tutor_id ?? 0);
            }
        }

        if (!$pasanteId || !$tutorId) {
            echo json_encode(['success' => false, 'message' => 'Pasante y Tutor son obligatorios']);
            return;
        }

        // Guard: una sola evaluación por pasante
        $evalExistente = $this->evaluacionModel->getByPasante($pasanteId);
        if ($evalExistente) {
            echo json_encode([
                'success' => false,
                'message' => 'Este pasante ya fue evaluado el ' . date('d/m/Y', strtotime($evalExistente->fecha_evaluacion)) . ' con promedio ' . number_format((float)$evalExistente->promedio_final, 2) . '/5. No se permiten evaluaciones duplicadas.',
            ]);
            return;
        }

        // Tutor: verificar que el pasante esté asignado a él
        if ($rolId === 2) {
            $this->db->query("SELECT tutor_id FROM datos_pasante WHERE usuario_id = :pid LIMIT 1");
            $this->db->bind(':pid', $pasanteId);
            $dpa = $this->db->single();
            if (!$dpa || (int)($dpa->tutor_id ?? 0) !== $userId) {
                http_response_code(403);
                echo json_encode(['success' => false, 'message' => 'No tienes permiso para evaluar a este pasante']);
                return;
            }
        }

        $criterios = [
            'criterio_iniciativa',   'criterio_interes',
            'criterio_conocimiento', 'criterio_analisis',
            'criterio_comunicacion', 'criterio_aprendizaje',
            'criterio_companerismo', 'criterio_cooperacion',
            'criterio_puntualidad',  'criterio_presentacion',
            'criterio_desarrollo',   'criterio_analisis_res',
            'criterio_conclusiones', 'criterio_recomendacion',
        ];

        $valores = [];
        $suma    = 0;
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
            if ($this->evaluacionModel->guardar($pasanteId, $tutorId, $fecha, $lapso, $promedio, $obs, $valores, $criterios)) {

                // Notificar al pasante
                $this->db->query("
                    SELECT dp.nombres, dp.apellidos
                    FROM datos_personales dp
                    WHERE dp.usuario_id = :tutor_id
                ");
                $this->db->bind(':tutor_id', $tutorId);
                $tutorInfo   = $this->db->single();
                $nombreTutor = $tutorInfo
                    ? trim(($tutorInfo->nombres ?? '') . ' ' . ($tutorInfo->apellidos ?? ''))
                    : 'Tu tutor';

                require_once APPROOT . '/models/NotificationModel.php';
                $notificationModel = new NotificationModel($this->db);

                $lapsoTexto = $lapso ? " del lapso {$lapso}" : '';
                $notificationModel->create(
                    $pasanteId,
                    'info',
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
            error_log('[SGP-EVAL] Error al guardar evaluación: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno del servidor']);
        }
    }

    // ─────────────────────────────────────────────────────────
    // GET /evaluaciones/obtenerDetalleAjax/{id} — Detalle (AJAX)
    // ─────────────────────────────────────────────────────────
    public function obtenerDetalleAjax($id = null): void
    {
        header('Content-Type: application/json');

        $id = (int)($id ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            return;
        }

        try {
            $ev = $this->evaluacionModel->getById($id);
            if (!$ev) {
                echo json_encode(['success' => false, 'message' => 'Evaluación no encontrada']);
                return;
            }
            echo json_encode(['success' => true, 'evaluacion' => $ev]);
        } catch (\Exception $e) {
            error_log('[SGP-EVAL] obtenerDetalleAjax: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error interno']);
        }
    }

    // ─────────────────────────────────────────────────────────
    // PRIVATE — Notificar culminación próxima (throttle 24h)
    // ─────────────────────────────────────────────────────────
    private function _notificarCulminacionProxima(): void
    {
        // Throttle: solo una vez por sesión cada 24 horas
        $ultimoCheck = $_SESSION['ultimo_notif_culm_eval'] ?? 0;
        if (time() - $ultimoCheck < 86400) return;
        $_SESSION['ultimo_notif_culm_eval'] = time();

        try {
            require_once APPROOT . '/models/NotificationModel.php';
            $notifModel = new NotificationModel($this->db);

            // Pasantes activos sin evaluación cuya fecha_fin_estimada está en los próximos 14 días
            $this->db->query("
                SELECT u.id AS pasante_id,
                       dp.nombres, dp.apellidos,
                       dpa.fecha_fin_estimada,
                       dpa.tutor_id,
                       DATEDIFF(dpa.fecha_fin_estimada, CURDATE()) AS dias_restantes
                FROM usuarios u
                INNER JOIN datos_pasante dpa ON dpa.usuario_id = u.id
                INNER JOIN datos_personales dp ON dp.usuario_id = u.id
                WHERE u.rol_id = 3
                  AND u.estado = 'activo'
                  AND dpa.estado_pasantia = 'Activo'
                  AND dpa.fecha_fin_estimada IS NOT NULL
                  AND dpa.fecha_fin_estimada BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 14 DAY)
                  AND NOT EXISTS (
                      SELECT 1 FROM evaluaciones e WHERE e.pasante_id = u.id
                  )
                ORDER BY dpa.fecha_fin_estimada ASC
            ");
            $proximosACulminar = $this->db->resultSet();

            foreach ($proximosACulminar as $p) {
                $nombrePasante = trim(($p->nombres ?? '') . ' ' . ($p->apellidos ?? ''));
                $diasRest      = (int)$p->dias_restantes;
                $fechaFin      = date('d/m/Y', strtotime($p->fecha_fin_estimada));
                $urgencia      = $diasRest <= 3 ? 'danger' : 'warning';

                $msg = $diasRest === 0
                    ? "La pasantía de {$nombrePasante} culmina HOY ({$fechaFin}). Registra su evaluación antes del cierre."
                    : "La pasantía de {$nombrePasante} culmina en {$diasRest} día(s) ({$fechaFin}). Pendiente de evaluación.";

                $url = URLROOT . '/evaluaciones/nueva/' . $p->pasante_id;

                // Notificar al tutor asignado
                if (!empty($p->tutor_id)) {
                    $notifModel->create((int)$p->tutor_id, $urgencia, 'Evaluación Pendiente', $msg, $url);
                }

                // Notificar a todos los admins (rol 1)
                $this->db->query("SELECT id FROM usuarios WHERE rol_id = 1 AND estado = 'activo'");
                $admins = $this->db->resultSet();
                foreach ($admins as $admin) {
                    $notifModel->create((int)$admin->id, $urgencia, 'Evaluación Pendiente', $msg, $url);
                }
            }
        } catch (\Throwable $e) {
            error_log('[SGP-EVAL-NOTIF] ' . $e->getMessage());
        }
    }
}
