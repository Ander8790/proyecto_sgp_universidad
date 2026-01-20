<?php
class DashboardController extends Controller {
    
    public function __construct() {
        // Aplicar Cache Control (prevenir botón atrás)
        CacheControl::noCache();
        
        // Verificar autenticación (con soporte AJAX)
        AuthMiddleware::require();
        
        // SISTEMA "LA JAULA" - Verificar estado del usuario
        AuthMiddleware::verificarEstado();
    }
    
    public function index() {
        Session::start();
        
        // Verificar que el usuario esté autenticado
        if (!Session::get('user_id')) {
            $this->redirect('/auth/login');
            return;
        }
        
        // Redirigir según rol
        $roleId = Session::get('role_id');
        $routes = [
            1 => '/admin',      // Administrador
            2 => '/tutor',      // Tutor
            3 => '/pasante'     // Pasante
        ];
        
        $this->redirect($routes[$roleId] ?? '/auth/login');
    }
}
