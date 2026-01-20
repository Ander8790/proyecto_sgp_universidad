<?php
class Tutor extends Controller {
    
    public function __construct() {
        // Aplicar Cache Control (prevenir botón atrás)
        CacheControl::noCache();
        
        // Verificar autenticación (con soporte AJAX)
        AuthMiddleware::require();
        
        // SISTEMA "LA JAULA" - Verificar estado del usuario
        AuthMiddleware::verificarEstado();
        
        // Verificar que el usuario sea Tutor (rol_id = 2)
        RoleMiddleware::requireRole(2);
    }
    
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
