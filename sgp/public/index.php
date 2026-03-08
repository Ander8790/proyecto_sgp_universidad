<?php
// Configuración global de Zona Horaria para Venezuela (-04:00)
date_default_timezone_set('America/Caracas');

// NOTE: Content-Type is NOT set globally here — each controller/endpoint sets its own headers.
//       Previously, the `header('Content-Type: text/html')` here was overriding application/json endpoints.
require_once '../app/config/config.php';

// Define App Root
define('APPROOT', dirname(dirname(__FILE__)) . '/app');

// Auto-detect URLROOT for mobile access support
$protocol = (!empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off') ? 'https://' : 'http://';
$host = $_SERVER['HTTP_HOST'] ?? 'localhost';
$scriptDir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME']));
define('URLROOT', $protocol . $host . $scriptDir);


require_once '../app/core/Database.php';
require_once '../app/core/Session.php';
require_once '../app/core/Validator.php';
require_once '../app/core/Controller.php';
require_once '../app/core/AuthMiddleware.php';
require_once '../app/core/RoleMiddleware.php';
require_once '../app/helpers/CacheControl.php';
require_once '../app/libraries/UrlSecurity.php';
require_once '../app/models/NotificationModel.php'; // Modelo de notificaciones
require_once '../app/models/AuditModel.php'; // Modelo de auditoría (Global)
require_once '../app/core/App.php';

// ============================================
// ROUTER INTELIGENTE - Redirección Automática
// ============================================
Session::start();

// Si accede a la raíz (/) sin URL específica
if (!isset($_GET['url']) || empty($_GET['url'])) {
    // Si ya tiene sesión activa → Redirigir a Dashboard
    if (Session::get('user_id')) {
        header('Location: ' . URLROOT . '/dashboard');
        exit;
    }
    // Si no tiene sesión → Mostrar Login (se cargará por defecto en App.php)
}

// ── Composer Autoload (DomPDF, TCPDF, etc.) ──────────────────────────────────
// Cargado condicionalmente: el sistema funciona sin él salvo en endpoints PDF.
$vendorAutoload = dirname(__DIR__) . '/vendor/autoload.php';
if (file_exists($vendorAutoload)) {
    require_once $vendorAutoload;
}

$app = new App();
