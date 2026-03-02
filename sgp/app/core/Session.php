<?php
class Session
{
    public static function start()
    {
        if (session_status() === PHP_SESSION_NONE) {
            $urlActual = $_SERVER['REQUEST_URI'] ?? '';

            // Si la ruta NO contiene '/public/kiosco', iniciamos sesión normalmente
            if (strpos($urlActual, '/public/kiosco') === false) {
                session_start();
            }
        }
    }

    public static function destroy()
    {
        self::start();
        session_destroy();
    }

    public static function regenerate()
    {
        session_regenerate_id(true);
    }

    // Set a session variable
    public static function set($key, $value)
    {
        $_SESSION[$key] = $value;
    }

    // Get a session variable
    public static function get($key)
    {
        return isset($_SESSION[$key]) ? $_SESSION[$key] : null;
    }

    // Check if user is logged in
    public static function isLoggedIn()
    {
        return isset($_SESSION['user_id']);
    }

    // Flash message helper
    // Usage: Session::flash('register_success', 'You are now registered');
    // Display: echo Session::flash('register_success');
    public static function flash($name = '', $message = '', $class = 'alert alert-success')
    {
        if (!empty($name)) {
            if (!empty($message) && empty($_SESSION[$name])) {
                if (!empty($_SESSION[$name . '_class'])) {
                    unset($_SESSION[$name . '_class']);
                }
                $_SESSION[$name] = $message;
                $_SESSION[$name . '_class'] = $class;
            } elseif (empty($message) && !empty($_SESSION[$name])) {
                $class = !empty($_SESSION[$name . '_class']) ? $_SESSION[$name . '_class'] : '';
                echo '<div class="' . $class . '" id="msg-flash">' . $_SESSION[$name] . '</div>';
                unset($_SESSION[$name]);
                unset($_SESSION[$name . '_class']);
            }
        }
    }
    
    /**
     * Establecer un mensaje flash (simplificado)
     * 
     * @param string $type Tipo de mensaje: 'success', 'error', 'warning', 'info'
     * @param string $message Mensaje a mostrar
     * @return void
     */
    public static function setFlash($type, $message)
    {
        $_SESSION['flash_' . $type] = $message;
    }
    
    /**
     * Obtener y eliminar un mensaje flash
     * 
     * @param string $type Tipo de mensaje
     * @return string|null
     */
    public static function getFlash($type)
    {
        if (isset($_SESSION['flash_' . $type])) {
            $message = $_SESSION['flash_' . $type];
            unset($_SESSION['flash_' . $type]);
            return $message;
        }
        return null;
    }
    
    /**
     * Verificar si existe un mensaje flash sin consumirlo
     * 
     * @param string $type Tipo de mensaje
     * @return bool
     */
    public static function hasFlash($type)
    {
        return isset($_SESSION['flash_' . $type]);
    }
}
