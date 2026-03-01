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
     * Get unread notifications for a user
     */
    public function getUnreadByUser($user_id, $limit = 10)
    {
        $this->db->query("
            SELECT id, tipo, titulo, mensaje, url, created_at
            FROM notificaciones
            WHERE usuario_id = :user_id AND leida = 0
            ORDER BY created_at DESC
            LIMIT :limit
        ");
        
        $this->db->bind(':user_id', $user_id);
        $this->db->bind(':limit', $limit, PDO::PARAM_INT);
        
        return $this->db->resultSet();
    }

    /**
     * Get count of unread notifications
     */
    public function getCountUnread($user_id)
    {
        $this->db->query("
            SELECT COUNT(*) as count
            FROM notificaciones
            WHERE usuario_id = :user_id AND leida = 0
        ");
        
        $this->db->bind(':user_id', $user_id);
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
