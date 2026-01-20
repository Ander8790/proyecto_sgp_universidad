<?php
class Admin extends Controller {
    
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
