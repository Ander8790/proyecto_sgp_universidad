<?php
/**
 * NotificationModel
 * Handles database operations for notifications
 */
class NotificationModel
{
    private $db;

    public function __construct($database)
    {
        $this->db = $database;
    }

    /**
     * Tipos de notificación exclusivos para administradores/tutores.
     * Los pasantes (role_id = 3) no deben verlos.
     */
    private const ADMIN_ONLY_TYPES = [
        'usuario_creado',
        'perfil_actualizado',
        'alerta_sistema',
        'solicitud_pin',
        'solicitud_recovery',
    ];

    /**
     * Construye la cláusula NOT IN con parámetros nombrados para los tipos admin.
     * Retorna ['clause' => 'AND tipo NOT IN (:t0,:t1,...)', 'binds' => [':t0' => ..., ...]]
     */
    private function buildAdminTypeFilter(): array
    {
        $named = [];
        $binds = [];
        foreach (self::ADMIN_ONLY_TYPES as $i => $tipo) {
            $key = ':admintype' . $i;
            $named[] = $key;
            $binds[$key] = $tipo;
        }
        return [
            'clause' => 'AND tipo NOT IN (' . implode(',', $named) . ')',
            'binds'  => $binds,
        ];
    }

    /**
     * Get unread notifications for a user, filtered by role.
     * @param int $user_id
     * @param int $role_id  1=Admin, 2=Tutor, 3=Pasante
     * @param int $limit
     */
    public function getUnreadByUser($user_id, $role_id = 1, $limit = 10)
    {
        $filter = ((int)$role_id === 3) ? $this->buildAdminTypeFilter() : ['clause' => '', 'binds' => []];

        $this->db->query("
            SELECT id, tipo, titulo, mensaje, url, created_at
            FROM notificaciones
            WHERE usuario_id = :user_id
              AND leida = 0
              {$filter['clause']}
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        foreach ($filter['binds'] as $key => $val) {
            $this->db->bind($key, $val);
        }
        return $this->db->resultSet();
    }

    /**
     * Get count of unread notifications, filtered by role.
     */
    public function getCountUnread($user_id, $role_id = 1)
    {
        $filter = ((int)$role_id === 3) ? $this->buildAdminTypeFilter() : ['clause' => '', 'binds' => []];

        $this->db->query("
            SELECT COUNT(*) as count
            FROM notificaciones
            WHERE usuario_id = :user_id
              AND leida = 0
              {$filter['clause']}
        ");
        $this->db->bind(':user_id', $user_id);
        foreach ($filter['binds'] as $key => $val) {
            $this->db->bind($key, $val);
        }
        $result = $this->db->single();
        return $result ? $result->count : 0;
    }

    /**
     * Mark notification as read
     */
    public function markAsRead($id, $user_id)
    {
        $this->db->query("
            UPDATE notificaciones
            SET leida = 1
            WHERE id = :id AND usuario_id = :user_id
        ");
        
        $this->db->bind(':id', $id);
        $this->db->bind(':user_id', $user_id);
        
        return $this->db->execute();
    }

    /**
     * Mark all notifications as read for a user
     */
    public function markAllAsRead($user_id)
    {
        $this->db->query("
            UPDATE notificaciones
            SET leida = 1
            WHERE usuario_id = :user_id AND leida = 0
        ");
        
        $this->db->bind(':user_id', $user_id);
        
        return $this->db->execute();
    }

    /**
     * Create a new notification
     */
    public function create($usuario_id, $tipo, $titulo, $mensaje, $url = '#')
    {
        $this->db->query("
            INSERT INTO notificaciones (usuario_id, tipo, titulo, mensaje, url)
            VALUES (:usuario_id, :tipo, :titulo, :mensaje, :url)
        ");
        
        $this->db->bind(':usuario_id', $usuario_id);
        $this->db->bind(':tipo', $tipo);
        $this->db->bind(':titulo', $titulo);
        $this->db->bind(':mensaje', $mensaje);
        $this->db->bind(':url', $url);
        
        return $this->db->execute();
    }

    /**
     * Delete old read notifications (cleanup)
     */
    public function deleteOldRead($days = 30)
    {
        $this->db->query("
            DELETE FROM notificaciones
            WHERE leida = 1 AND created_at < DATE_SUB(NOW(), INTERVAL :days DAY)
        ");
        
        $this->db->bind(':days', $days);
        
        return $this->db->execute();
    }
}
