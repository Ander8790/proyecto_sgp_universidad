<?php
class PasantesController extends Controller {
    public function __construct() {
        if (!Session::isLoggedIn()) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data = [
            'title' => 'Pasantes',
            'module_name' => 'Gestión de Pasantes',
            'icon' => 'ti-user-check',
            'message' => 'Este módulo está en desarrollo activo.',
            'description' => 'Pronto podrás gestionar pasantes de liceos técnicos.'
        ];
        
        $this->view('common/under_development', $data);
    }
}
