<?php
class ConfiguracionController extends Controller {
    public function __construct() {
        if (!Session::isLoggedIn()) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data = [
            'title' => 'Configuración',
            'module_name' => 'Configuración del Sistema',
            'icon' => 'ti-settings',
            'message' => 'Este módulo está en desarrollo activo.',
            'description' => 'Pronto podrás configurar parámetros del sistema.'
        ];
        
        $this->view('common/under_development', $data);
    }
}
