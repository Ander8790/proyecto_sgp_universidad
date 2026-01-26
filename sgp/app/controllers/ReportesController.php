<?php
class ReportesController extends Controller {
    public function __construct() {
        // Verificar autenticación
        if (!Session::isLoggedIn()) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data = [
            'title' => 'Reportes',
            'module_name' => 'Reportes',
            'icon' => 'ti-file-analytics',
            'message' => 'Este módulo está en desarrollo activo.',
            'description' => 'Pronto podrás generar reportes en PDF y Excel del sistema.'
        ];
        
        $this->view('common/under_development', $data);
    }
}
