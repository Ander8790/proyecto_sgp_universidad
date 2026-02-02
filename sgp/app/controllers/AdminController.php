<?php
/**
 * AdminController - Controlador del Panel de Administrador
 * 
 * PROPÓSITO:
 * Gestionar el dashboard y funcionalidades del rol Administrador.
 * 
 * SEGURIDAD:
 * - Cache Control (prevenir botón atrás)
 * - Autenticación requerida
 * - Sistema "La Jaula" (verificar estado)
 * - Solo rol Administrador (role_id = 1)
 * 
 * @author Sistema SGP
 * @version 1.0
 */

class AdminController extends Controller {
    
    public function __construct() {
        // Aplicar Cache Control (prevenir botón atrás)
        CacheControl::noCache();
        
        // Verificar autenticación (con soporte AJAX)
        AuthMiddleware::require();
        
        // SISTEMA "LA JAULA" - Verificar estado del usuario
        AuthMiddleware::verificarEstado();
        
        // Verificar que el usuario sea Administrador (rol_id = 1)
        RoleMiddleware::requireRole(1);
    }
    
    /**
     * Dashboard del Administrador
     * 
     * PROPÓSITO:
     * Mostrar panel principal con estadísticas y accesos rápidos.
     */
    public function index() {
        Session::start();
        
        $data = [
            'title' => 'Panel de Administrador',
            'role' => 'Administrador',
            'user_name' => Session::get('user_name')
        ];
        
        $this->view('admin/dashboard', $data);
    }
}
