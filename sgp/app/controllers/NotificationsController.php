<?php
/**
 * NotificationsController
 * Handles notification API endpoints
 */
class NotificationsController extends Controller
{
    private $notificationModel;

    public function __construct()
    {
        // Start output buffering to catch any PHP warnings/notices
        ob_start();
        
        // Security - Check authentication FIRST
        Session::start();
        AuthMiddleware::require();
        
        // ✅ REMOVED: AuthMiddleware::verificarEstado() 
        // RAZÓN: Este endpoint es API JSON, no debe redirigir a HTML
        // La verificación de estado se hace en páginas normales, no en APIs
        
        // Then send cache control headers
        CacheControl::noCache();
        
        // Load model
        $config = require '../app/config/config.php';
        $db = new Database($config['db']);
        $this->notificationModel = new NotificationModel($db);
    }

    /**
     * Default endpoint to prevent router crashes
     */
    public function index()
    {
        $this->jsonResponse(false, 'Endpoint no válido');
    }

    /**
     * Get unread notifications (AJAX)
     */
    public function getUnread()
    {
        try {
            $user_id = Session::get('user_id');
            $role_id = Session::get('role_id');
            
            if (!$user_id) {
                $this->jsonResponse(false, 'Usuario no autenticado');
            }

            $notifications = $this->notificationModel->getUnreadByUser($user_id);
            $count = $this->notificationModel->getCountUnread($user_id);

            // Format timestamps
            if ($notifications) {
                foreach ($notifications as &$notification) {
                    $notification->time_ago = $this->timeAgo($notification->created_at);
                }
            } else {
                $notifications = [];
            }

            // ── Dynamic system alerts for admins and tutors ──
            if ((int)$role_id === 1 || (int)$role_id === 2) {
                $dynamicAlerts = $this->getDynamicAlerts((int)$role_id, (int)$user_id);
                if (!empty($dynamicAlerts)) {
                    $notifications = array_merge($dynamicAlerts, $notifications);
                    $count += count($dynamicAlerts);
                }
            }

            $this->jsonResponse(true, 'Notificaciones obtenidas', [
                'count' => $count,
                'notifications' => $notifications
            ]);
        } catch (Exception $e) {
            // Log error for debugging
            error_log('❌ NotificationsController::getUnread - Error: ' . $e->getMessage());
            
            // Return JSON error instead of HTML
            $this->jsonResponse(false, 'Error al obtener notificaciones: ' . $e->getMessage());
        }
    }

    /**
     * Generate dynamic system alerts (not stored in DB)
     * These are ephemeral and computed on each request.
     */
    private function getDynamicAlerts(int $role_id, int $user_id): array
    {
        $alerts = [];
        $config = require APPROOT . '/config/config.php';
        $db = new Database($config['db']);

        // 1. Pasantes sin asignar (Solo Admin)
        if ($role_id === 1) {
            $db->query("
                SELECT COUNT(*) AS total 
                FROM datos_pasante 
                WHERE estado_pasantia IN ('Pendiente', '') OR estado_pasantia IS NULL
            ");
            $pendientes = (int)($db->single()->total ?? 0);
            if ($pendientes > 0) {
                $alerts[] = $this->createDynamicAlert(
                    'sys_pendientes', 'alerta_sistema',
                    $pendientes . ' pasante' . ($pendientes > 1 ? 's' : '') . ' sin asignar',
                    'Requieren departamento o tutor para iniciar pasantía.',
                    URLROOT . '/pasantes'
                );
            }
        }

        // 2. Pasantías próximas a vencer (Admin y Tutor)
        $whereTutor = ($role_id === 2) ? " AND tutor_id = {$user_id}" : "";
        $db->query("
            SELECT COUNT(*) AS total 
            FROM datos_pasante 
            WHERE estado_pasantia = 'Activo' 
              AND fecha_fin_estimada IS NOT NULL 
              AND DATEDIFF(fecha_fin_estimada, CURDATE()) <= 15
              AND DATEDIFF(fecha_fin_estimada, CURDATE()) >= 0
              {$whereTutor}
        ");
        $prox_vencer = (int)($db->single()->total ?? 0);
        if ($prox_vencer > 0) {
            $alerts[] = $this->createDynamicAlert(
                'sys_proximos_vencer', 'alerta_urgente',
                $prox_vencer . ' pasantía' . ($prox_vencer > 1 ? 's' : '') . ' próxima' . ($prox_vencer > 1 ? 's' : '') . ' a vencer',
                'Finalizan en los próximos 15 días. Verificar progreso de horas.',
                URLROOT . '/asignaciones'
            );
        }

        // 3. Pasantes que completaron sus horas (Solo Admin)
        if ($role_id === 1) {
            $db->query("
                SELECT COUNT(*) AS total 
                FROM datos_pasante 
                WHERE estado_pasantia = 'Activo' AND horas_acumuladas >= horas_meta
            ");
            $horas_listas = (int)($db->single()->total ?? 0);
            if ($horas_listas > 0) {
                $alerts[] = $this->createDynamicAlert(
                    'sys_horas_completadas', 'alerta_exito',
                    $horas_listas . ' pasante' . ($horas_listas > 1 ? 's' : '') . ' completó sus horas',
                    'Listos para finalizar pasantía y generar constancia.',
                    URLROOT . '/pasantes'
                );
            }
        }

        // 4. Usuarios pendientes de wizard (Solo Admin)
        if ($role_id === 1) {
            $db->query("
                SELECT COUNT(*) AS total 
                FROM usuarios 
                WHERE requiere_cambio_clave = 1 AND estado = 'activo'
            ");
            $wizard_pendientes = (int)($db->single()->total ?? 0);
            if ($wizard_pendientes > 0) {
                $alerts[] = $this->createDynamicAlert(
                    'sys_wizard_pendiente', 'alerta_sistema',
                    $wizard_pendientes . ' usuario' . ($wizard_pendientes > 1 ? 's' : '') . ' pendiente' . ($wizard_pendientes > 1 ? 's' : '') . ' de registro',
                    'Falta completar el wizard de primer ingreso.',
                    URLROOT . '/users'
                );
            }
        }

        // 5. Asistencia de hoy (Admin y Tutor)
        $db->query("
            SELECT COUNT(*) AS total 
            FROM datos_pasante 
            WHERE estado_pasantia = 'Activo' {$whereTutor}
        ");
        $pasantes_activos = (int)($db->single()->total ?? 0);

        if ($pasantes_activos > 0) {
                if ($role_id === 2) {
                    $db->query("
                        SELECT COUNT(DISTINCT a.pasante_id) AS total 
                        FROM asistencias a
                        JOIN datos_pasante dp ON a.pasante_id = dp.usuario_id
                        WHERE a.fecha = CURDATE()
                          AND dp.tutor_id = {$user_id}
                    ");
                } else {
                    $db->query("
                        SELECT COUNT(DISTINCT pasante_id) AS total 
                        FROM asistencias 
                        WHERE fecha = CURDATE()
                    ");
                }
            $asistencias_hoy = (int)($db->single()->total ?? 0);

            $alerts[] = $this->createDynamicAlert(
                'sys_asistencia_hoy', 'info',
                "Asistencia de hoy: {$asistencias_hoy} de {$pasantes_activos}",
                "Pasantes activos que han registrado entrada el día de hoy.",
                URLROOT . '/asistencias'
            );
        }

        return $alerts;
    }

    /**
     * Helper param dynamic alerts
     */
    private function createDynamicAlert($id, $tipo, $titulo, $mensaje, $url) 
    {
        return (object)[
            'id'         => $id,
            'tipo'       => $tipo,
            'titulo'     => $titulo,
            'mensaje'    => $mensaje,
            'url'        => $url,
            'leida'      => 0,
            'leido'      => 0,
            'created_at' => date('Y-m-d H:i:s'),
            'time_ago'   => 'Ahora'
        ];
    }

    /**
     * Mark notification as read (AJAX)
     */
    public function markAsRead($id = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }

        $user_id = Session::get('user_id');
        
        if (!$id) {
            $this->jsonResponse(false, 'ID de notificación requerido');
        }

        if ($this->notificationModel->markAsRead($id, $user_id)) {
            $this->jsonResponse(true, 'Notificación marcada como leída');
        } else {
            $this->jsonResponse(false, 'Error al marcar notificación');
        }
    }

    /**
     * Mark all notifications as read (AJAX)
     */
    public function markAllAsRead()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }

        $user_id = Session::get('user_id');

        if ($this->notificationModel->markAllAsRead($user_id)) {
            $this->jsonResponse(true, 'Todas las notificaciones marcadas como leídas');
        } else {
            $this->jsonResponse(false, 'Error al marcar notificaciones');
        }
    }

    /**
     * Helper: Convert timestamp to "time ago" format
     */
    private function timeAgo($timestamp)
    {
        $time = strtotime($timestamp);
        $diff = time() - $time;

        if ($diff < 60) {
            return 'Hace ' . $diff . ' segundos';
        } elseif ($diff < 3600) {
            $mins = floor($diff / 60);
            return 'Hace ' . $mins . ($mins == 1 ? ' minuto' : ' minutos');
        } elseif ($diff < 86400) {
            $hours = floor($diff / 3600);
            return 'Hace ' . $hours . ($hours == 1 ? ' hora' : ' horas');
        } elseif ($diff < 604800) {
            $days = floor($diff / 86400);
            return 'Hace ' . $days . ($days == 1 ? ' día' : ' días');
        } else {
            return date('d/m/Y H:i', $time);
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse($success, $message, $data = null)
    {
        // Clear any buffered output (warnings, notices, etc.)
        if (ob_get_level() > 0) {
            ob_clean();
        }
        
        header('Content-Type: application/json');
        $response = [
            'success' => $success,
            'message' => $message
        ];
        
        if ($data !== null) {
            $response = array_merge($response, $data);
        }
        
        echo json_encode($response);
        exit;
    }
}
