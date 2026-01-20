<?php
class Pasante extends Controller {
    
    public function __construct() {
        // Aplicar Cache Control (prevenir botón atrás)
        CacheControl::noCache();
        
        // Verificar autenticación (con soporte AJAX)
        AuthMiddleware::require();
        
        // SISTEMA "LA JAULA" - Verificar estado del usuario
        AuthMiddleware::verificarEstado();
        
        // Verificar que el usuario sea Pasante (rol_id = 3)
        RoleMiddleware::requireRole(3);
    }
    
    public function index() {
        Session::start();
        
        $data = [
            'title' => 'Panel de Pasante',
            'role' => 'Pasante',
            'user_name' => Session::get('user_name')
        ];
        
        $this->view('pasante/dashboard', $data);
    }
    

}
