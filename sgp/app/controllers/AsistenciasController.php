<?php
class AsistenciasController extends Controller {
    public function __construct() {
        if (!Session::isLoggedIn()) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data = [
            'title' => 'Asistencias',
            'module_name' => 'Control de Asistencias',
            'icon' => 'ti-calendar-stats',
            'message' => 'Este es el módulo CORE del sistema.',
            'description' => 'Pronto podrás registrar y controlar asistencias de pasantes (reemplaza Excel/papel).'
        ];
        
        $this->view('common/under_development', $data);
    }
}
