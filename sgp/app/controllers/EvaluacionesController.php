<?php
class EvaluacionesController extends Controller {
    public function __construct() {
        if (!Session::isLoggedIn()) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data = [
            'title' => 'Evaluaciones',
            'module_name' => 'Evaluaciones',
            'icon' => 'ti-star',
            'message' => 'Este módulo está en desarrollo activo.',
            'description' => 'Pronto podrás gestionar evaluaciones de pasantes.'
        ];
        
        $this->view('common/under_development', $data);
    }
}
