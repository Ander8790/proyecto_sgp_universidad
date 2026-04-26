<?php
error_reporting(E_ALL);
ini_set('display_errors', 1);
require '../core/Database.php';
require '../models/Asistencia.php';
require 'AsistenciasController.php';

// Mock session and auth
class Session {
    public static function get($k) {
        if ($k == 'role_id') return 1;
        if ($k == 'user_id') return 1;
        return null;
    }
}
class AuthMiddleware {
    public static function check() {}
}

// Emulate request
$_GET['vista'] = 'mensual';
$_GET['mes'] = '03';
$_GET['anio'] = '2026';
$_SERVER['HTTP_X_PJAX'] = '1';
define('URLROOT', 'http://localhost/proyecto_sgp/sgp/public');

// Call controller directly
$controller = new AsistenciasController();
$controller->index();
