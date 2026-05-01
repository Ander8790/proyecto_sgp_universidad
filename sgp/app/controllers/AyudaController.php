<?php
declare(strict_types=1);

class AyudaController extends Controller
{
    public function index(): void
    {
        Session::start();
        AuthMiddleware::require();
        $this->view('ayuda/index', []);
    }

    /**
     * GET /ayuda/pdf — Documento PDF profesional del manual de usuario.
     * Página HTML standalone (sin layout), optimizada para imprimir como PDF.
     */
    public function pdf(): void
    {
        Session::start();
        // AuthMiddleware::require(); // Comentado para permitir acceso desde el Login
        // Pasar variables de rol al documento
        $rolId     = (int)(Session::get('role_id') ?? Session::get('rol_id') ?? 0);
        $esAdmin   = ($rolId === 1 || $rolId === 0);
        $esTutor   = $rolId === 2;
        $esPasante = ($rolId === 3 || $rolId === 0);
        require APPROOT . '/views/ayuda/pdf_manual.php';
        exit;
    }
}
