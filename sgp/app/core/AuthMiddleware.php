<?php
/**
 * AuthMiddleware - Middleware de Autenticación
 * 
 * Funciones:
 * - Verificar sesión activa
 * - Control de inactividad (30 minutos)
 * - Redirección automática a login
 * - Compatible con AJAX
 */
class AuthMiddleware
{
    /**
     * Tiempo máximo de inactividad en segundos (30 minutos)
     */
    const INACTIVITY_LIMIT = 1800; // 30 * 60
    
    /**
     * Verificar si el usuario tiene sesión activa
     * Si no tiene sesión o está inactivo, redirige al login
     * 
     * @param bool $isAjax Si la petición es AJAX
     * @return void
     */
    public static function verificarSesion($isAjax = false)
    {
        Session::start();
        
        // Verificar si existe sesión de usuario
        if (!Session::get('user_id')) {
            self::redirectToLogin($isAjax, 'Debe iniciar sesión');
            return;
        }
        
        // Verificar inactividad
        if (self::isInactive()) {
            Session::destroy();
            self::redirectToLogin($isAjax, 'Sesión expirada por inactividad');
            return;
        }
        
        // Actualizar última actividad
        self::updateActivity();
    }
    
    /**
     * Verificar si la sesión está inactiva
     * 
     * @return bool
     */
    private static function isInactive()
    {
        $lastActivity = Session::get('last_activity');
        
        if (!$lastActivity) {
            return false;
        }
        
        $inactiveTime = time() - $lastActivity;
        return $inactiveTime > self::INACTIVITY_LIMIT;
    }
    
    /**
     * Actualizar timestamp de última actividad
     * 
     * @return void
     */
    private static function updateActivity()
    {
        Session::set('last_activity', time());
    }
    
    /**
     * Redirigir al login (compatible con AJAX)
     * 
     * @param bool $isAjax
     * @param string $message
     * @return void
     */
    private static function redirectToLogin($isAjax, $message)
    {
        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false,
                'message' => $message,
                'redirect' => URLROOT . '/auth/login',
                'session_expired' => true
            ]);
            exit;
        }
        
        // Redirección normal
        Session::setFlash('error', $message);
        header('Location: ' . URLROOT . '/auth/login');
        exit;
    }
    
    /**
     * Verificar si la petición es AJAX
     * 
     * @return bool
     */
    public static function isAjaxRequest()
    {
        return !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && 
               strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
    }
    
    /**
     * Verificar sesión automáticamente detectando AJAX
     * 
     * @return void
     */
    public static function require()
    {
        self::verificarSesion(self::isAjaxRequest());
    }
    
    /**
     * Verificar estado del usuario - Sistema "La Jaula"
     * Fuerza cambio de contraseña y verifica completitud del perfil
     * 
     * @return void
     */
    public static function verificarEstado(): void
    {
        Session::start();
        
        // Si no hay sesión, no hacer nada
        if (!Session::get('user_id')) {
            return;
        }
        
        // Obtener URL actual
        $currentUrl = $_SERVER['REQUEST_URI'];
        $urlPath = parse_url($currentUrl, PHP_URL_PATH);
        
        // NIVEL 1: LA JAULA - Cambio de Clave Obligatorio
        // Check if user needs to change password (admin-created users)
        if (Session::get('requiere_cambio_clave') == 1) {
            $allowedPaths = [
                '/auth/logout',
                '/perfil/completar',
                '/perfil/guardarWizard'
            ];
            
            $currentPath = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);
            $currentPath = str_replace('/proyecto_sgp/sgp/public', '', $currentPath);
            
            // Allow access to logout and wizard completion
            $isAllowed = false;
            foreach ($allowedPaths as $path) {
                if (strpos($currentPath, $path) !== false) {
                    $isAllowed = true;
                    break;
                }
            }
            
            if (!$isAllowed) {
                header('Location: ' . URLROOT . '/perfil/completar');
                exit;
            }
            // Si está en ruta permitida, dejar continuar
            return;
        }
        
        // NIVEL 2: Datos Personales Incompletos
        // =====================================================
        // Cargar modelo para verificar perfil
        require_once '../app/models/UserModel.php';
        
        // Cargar configuración de base de datos
        $config = require '../app/config/config.php';
        
        $db = new Database($config['db']);
        $userModel = new UserModel($db);
        
        // Verificar si el usuario tiene perfil completo
        if (!$userModel->tienePerfil(Session::get('user_id'))) {
            // LISTA BLANCA: Permitir solo rutas de perfil
            $allowedPaths = [
                '/perfil/completar',
                '/perfil/guardarWizard',
                '/auth/logout'
            ];
            
            $isAllowed = false;
            foreach ($allowedPaths as $path) {
                if (strpos($urlPath, $path) !== false) {
                    $isAllowed = true;
                    break;
                }
            }
            
            // Si no está en ruta permitida, redirigir a completar perfil
            if (!$isAllowed) {
                header('Location: ' . URLROOT . '/perfil/completar');
                exit;
            }
            
            // Si está en ruta permitida, dejar continuar
            return;
        }
        
        // NIVEL 3: Pasante sin Departamento
        // =====================================================
        // TODO: Implementar verificación de asignación
    }
}
