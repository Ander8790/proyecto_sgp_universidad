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
        $db = Database::getInstance(); // SGP-FIX-v2 [6/2.1] aplicado
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

            $notifications = $this->notificationModel->getUnreadByUser($user_id, $role_id);
            $count = $this->notificationModel->getCountUnread($user_id, $role_id);

            // Format timestamps
            if ($notifications) {
                foreach ($notifications as &$notification) {
                    $notification->time_ago = $this->timeAgo($notification->created_at);
                }
            } else {
                $notifications = [];
            }

            // [REMOVED] Dynamic system alerts were moved to Dashboard (Event-Driven only architecture)

            $this->jsonResponse(true, 'Notificaciones obtenidas', [
                'count' => $count,
                'notifications' => $notifications
            ]);
        } catch (Exception $e) {
            // Log error for debugging
            // [FIX-C2] Detalle de excepción solo en log — mensaje genérico al cliente
            error_log('[SGP-NOTIF] getUnread() Error: ' . $e->getMessage());
            $this->jsonResponse(false, 'Error al obtener notificaciones.');
        }
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
