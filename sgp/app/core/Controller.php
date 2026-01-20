<?php
abstract class Controller
{
    // Load model
    protected function model($model)
    {
        // Require model file
        // Support both naming conventions: User.php or UserModel.php
        if (file_exists('../app/models/' . $model . 'Model.php')) {
            require_once '../app/models/' . $model . 'Model.php';
            $className = $model . 'Model';
        } elseif (file_exists('../app/models/' . $model . '.php')) {
            require_once '../app/models/' . $model . '.php';
            $className = $model;
        } else {
            die("Model {$model} does not exist");
        }
        
        // Instantiate Database
        $config = require '../app/config/config.php';
        $db = new Database($config['db']);
        
        // Instantiate model with DB connection
        return new $className($db);
    }

    // Load view with optional layout support
    protected function view($view, $data = [], $useMasterLayout = true)
    {
        // Check for view file
        $viewPath = '../app/views/' . $view . '.php';
        
        if (file_exists($viewPath)) {
            // Extract data for the view
            extract($data);
            
            if ($useMasterLayout) {
                // Use master layout (Dashboard, Profile, etc.)
                ob_start();
                require_once $viewPath;
                $viewContent = ob_get_clean();
                
                // Set content path for layout to include
                $content = $viewPath;
                
                // Load main layout (which will require the $content path)
                require_once '../app/views/layouts/main_layout.php';
            } else {
                // Load standalone view (Login, Register, Landing, etc.)
                require_once $viewPath;
            }
        } else {
            // View does not exist
            die("View {$view} does not exist");
        }
    }

    // Helper for redirection
    protected function redirect($path) {
        $path = ltrim($path, '/');
        // Use URLROOT constant defined in index.php
        header("Location: " . URLROOT . '/' . $path);
        exit();
    }
}
