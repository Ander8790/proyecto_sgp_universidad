<?php
class BitacoraController extends Controller {
    public function __construct() {
        if (!Session::isLoggedIn()) {
            redirect('auth/login');
        }
    }

    public function index() {
        $data = [
            'title' => 'Bitácora',
            'module_name' => 'Bitácora de Actividades',
            'icon' => 'ti-file-text',
            'message' => 'Este módulo está en desarrollo activo.',
            'description' => 'Pronto podrás registrar actividades diarias de tu pasantía.'
        ];
        
        $this->view('common/under_development', $data);
    }
}
