<?php
/**
 * TutorController - Controlador del Panel de Tutor
 * 
 * PROPÓSITO:
 * Gestionar el dashboard y funcionalidades del rol Tutor.
 * 
 * SEGURIDAD:
 * - Cache Control (prevenir botón atrás)
 * - Autenticación requerida
 * - Sistema "La Jaula" (verificar estado)
 * - Solo rol Tutor (role_id = 2)
 * 
 * @author Sistema SGP
 * @version 1.0
 */

class TutorController extends Controller {
    
    public function __construct() {
        // Aplicar Cache Control (prevenir botón atrás)
        CacheControl::noCache();
        
        // Verificar autenticación (con soporte AJAX)
        AuthMiddleware::require();
        
        // SISTEMA "LA JAULA" - Verificar estado del usuario
        AuthMiddleware::verificarEstado();
        
        // Verificar que el usuario sea Tutor (role_id = 2)
        RoleMiddleware::requireRole(2);
    }
    
    /**
     * Dashboard del Tutor
     * 
     * PROPÓSITO:
     * Mostrar panel principal con pasantes asignados y evaluaciones.
     */
    public function index() {
        Session::start();
        
        $data = [
            'title' => 'Panel de Tutor',
            'role' => 'Tutor',
            'user_name' => Session::get('user_name')
        ];
        
        $this->view('tutor/dashboard', $data);
    }
}
