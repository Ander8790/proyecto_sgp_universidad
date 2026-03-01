<?php
class AnaliticasController extends Controller {
    public function __construct() {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();
    }

    public function index() {
        $data = ['title' => 'Analíticas del Sistema'];
        $this->view('analiticas/index', $data);
    }
}
