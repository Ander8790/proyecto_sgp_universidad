<?php
/**
 * RoleMiddleware - Middleware de Autorización por Rol
 * 
 * Funciones:
 * - Verificar que el usuario tenga el rol correcto
 * - Redirigir automáticamente si el rol no coincide
 * - Centralizar lógica de autorización
 */
class RoleMiddleware
{
    /**
     * Mapeo de roles a rutas
     */
    const ROLE_ROUTES = [
        1 => '/admin',      // Administrador
        2 => '/tutor',      // Tutor
        3 => '/pasante'     // Pasante
    ];
    
    /**
     * Requiere que el usuario tenga un rol específico
     * Si no lo tiene, redirige al dashboard correcto
     * 
     * @param int $expectedRoleId ID del rol esperado
     * @return void
     */
    public static function requireRole($expectedRoleId)
    {
        Session::start();
        
        $currentRoleId = Session::get('role_id');
        
        // Si el usuario no tiene el rol esperado, redirigir
        if ($currentRoleId != $expectedRoleId) {
            self::redirectToRoleDashboard($currentRoleId);
        }
    }
    
    /**
     * Redirige al dashboard correcto según el rol del usuario
     * 
     * @param int $roleId ID del rol
     * @return void
     */
    public static function redirectToRoleDashboard($roleId)
    {
        $route = self::ROLE_ROUTES[$roleId] ?? '/auth/login';
        header('Location: ' . URLROOT . $route);
        exit;
    }
    
    /**
     * Verifica si el usuario tiene un rol específico
     * 
     * @param int $roleId ID del rol a verificar
     * @return bool
     */
    public static function hasRole($roleId)
    {
        Session::start();
        return Session::get('role_id') == $roleId;
    }
    
    /**
     * Verifica si el usuario tiene alguno de los roles especificados
     * 
     * @param array $roleIds Array de IDs de roles permitidos
     * @return bool
     */
    public static function hasAnyRole(array $roleIds)
    {
        Session::start();
        $currentRoleId = Session::get('role_id');
        return in_array($currentRoleId, $roleIds);
    }
    
    /**
     * Obtiene el nombre del rol actual
     * 
     * @return string
     */
    public static function getRoleName()
    {
        Session::start();
        $roleId = Session::get('role_id');
        
        $roleNames = [
            1 => 'Administrador',
            2 => 'Tutor',
            3 => 'Pasante'
        ];
        
        return $roleNames[$roleId] ?? 'Invitado';
    }
}
