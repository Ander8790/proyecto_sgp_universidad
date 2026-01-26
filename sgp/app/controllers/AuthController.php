<?php
class AuthController extends Controller
{
    public function index() {
        $this->login();
    }

    public function login(): void
    {
        Session::start();
        if (Session::get('user_id')) {
            $this->redirectByRole(Session::get('role_id'));
            return;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('auth/login', [], false);
            return;
        }

        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';

        $email = Validator::email('email');
        $password = Validator::post('password');
        $captcha = Validator::post('captcha');

        // Validar CAPTCHA primero
        require_once APPROOT . '/helpers/CaptchaHelper.php';
        if (!CaptchaHelper::validate($captcha)) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Código CAPTCHA incorrecto']);
                exit;
            }
            $this->view('auth/login', ['error' => 'Código CAPTCHA incorrecto'], false);
            return;
        }

        $userModel = $this->model('User');
        $user = $userModel->findByEmail($email);

        if (!$user || !password_verify($password, $user['password'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Credenciales inválidas']);
                exit;
            }
            $this->view('auth/login', ['error' => 'Credenciales inválidas'], false);
            return;
        }

        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('role_id', $user['role_id']);
        Session::set('user_name', $user['name']);
        Session::set('requiere_cambio_clave', $user['requiere_cambio_clave'] ?? 0);
        Session::set('departamento_id', $user['departamento_id'] ?? null);
        Session::set('last_activity', time());

        // Check if password change is required ("La Jaula")
        if ($user['requiere_cambio_clave'] == 1) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => URLROOT . '/auth/cambiar-password']);
                exit;
            }
            $this->redirect('/auth/cambiar-password');
            return;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode(['success' => true, 'redirect' => URLROOT . '/dashboard']);
            exit;
        }

        $this->redirectByRole($user['role_id']);
    }

    public function register(): void
    {
        Session::start();
        $questions = $this->model('User')->getSecurityQuestions();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: text/html; charset=UTF-8');
            $this->view('auth/register', ['questions' => $questions], false);
            return;
        }
        
        // Capturar datos del formulario
        $fullName = Validator::post('name'); // "Juan Pérez" o "María García López"
        $email = Validator::email('email');
        $password = Validator::post('password');
        $roleId = 3; // Pasante por defecto

        $userModel = $this->model('User');
        
        // Verificar si el correo ya existe
        if ($userModel->findByEmail($email)) {
            $this->view('auth/register', ['questions' => $questions, 'error' => 'El correo ya existe'], false);
            return;
        }

        // Parsear nombre completo en nombres y apellidos
        $nameParts = explode(' ', trim($fullName), 2);
        $nombres = $nameParts[0] ?? 'Usuario';
        $apellidos = $nameParts[1] ?? 'Nuevo';

        // REGISTRO PÚBLICO: requiere_cambio_clave = 0 (el usuario ya eligió su contraseña)
        $userData = [
            'email' => $email,
            'password' => $password,
            'role_id' => $roleId,
            'departamento_id' => null,
            'requiere_cambio_clave' => 0 // ← CLAVE: No forzar cambio en registro público
        ];

        if ($userModel->create($userData)) {
            // Obtener el usuario recién creado
            $newUser = $userModel->findByEmail($email);
            
            if ($newUser) {
                // Guardar respuestas de seguridad
                $answers = $_POST['answers'] ?? [];
                foreach ($answers as $qid => $ans) {
                    $userModel->saveSecurityAnswer((int)$newUser['id'], (int)$qid, $ans);
                }
                
                // EVITAR REDUNDANCIA: Guardar datos personales inmediatamente
                // para que el Nivel 2 no vuelva a pedirlos
                $perfilData = [
                    'usuario_id' => $newUser['id'],
                    'cedula' => 'TEMP' . str_pad($newUser['id'], 7, '0', STR_PAD_LEFT), // Temporal único: TEMP0000001, TEMP0000002, etc.
                    'nombres' => $nombres,
                    'apellidos' => $apellidos,
                    'telefono' => '0000000000', // Placeholder
                    'direccion' => 'Por definir',
                    'genero' => 'M', // Placeholder
                    'fecha_nacimiento' => '2000-01-01' // Placeholder
                ];
                
                $userModel->registrarPerfil($perfilData);
            }
            
            $this->view('auth/login', ['success' => 'Registro completado. Inicie sesión.'], false);
        } else {
            $this->view('auth/register', ['questions' => $questions, 'error' => 'Error al registrar'], false);
        }
    }

    public function recovery(): void
    {
        Session::start();
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
             $this->view('auth/recovery', [], false); // Start
             return;
        }
        
        $step = $_POST['step'] ?? '1';
        
        if ($step == '1') {
            $email = Validator::email('email');
            $user = $this->model('User')->findByEmail($email);
            if (!$user) {
                $this->view('auth/recovery', ['error' => 'Correo no encontrado'], false);
                return;
            }
            
            $questions = $this->model('User')->getUserAnswers($user['id']);
            if (empty($questions)) {
                $this->view('auth/recovery', ['error' => 'Sin preguntas de seguridad configuradas'], false);
                return;
            }
            
            Session::set('rec_uid', $user['id']);
            $this->view('auth/recovery_questions', ['questions' => $questions], false);
        }
        elseif ($step == '2') {
            $uid = Session::get('rec_uid');
            if (!$uid) { $this->redirect('/auth/recovery'); return; }
            
            $inputAnswers = $_POST['answers'] ?? [];
            $userAnswers = $this->model('User')->getUserAnswers($uid);
            
            $allCorrect = true;
            foreach ($userAnswers as $ua) {
                $qid = $ua['question_id'];
                if (!isset($inputAnswers[$qid]) || !password_verify(strtolower(trim($inputAnswers[$qid])), $ua['answer_hash'])) {
                    $allCorrect = false;
                    break;
                }
            }
            
            if ($allCorrect) {
                Session::set('rec_verified', true);
                $this->view('auth/reset_password', [], false);
            } else {
                $this->view('auth/recovery_questions', ['questions' => $userAnswers, 'error' => 'Respuestas incorrectas'], false);
            }
        }
        elseif ($step == '3') {
            if (!Session::get('rec_verified')) { $this->redirect('/auth/recovery'); return; }
            
            $pass = Validator::post('password');
            $uid = Session::get('rec_uid');
            $this->model('User')->updatePassword($uid, $pass);
            
            Session::destroy();
            $this->view('auth/login', ['success' => 'Contraseña actualizada'], false);
        }
    }


    public function cambiarPassword(): void
    {
        Session::start();
        
        // Verify user is logged in
        if (!Session::get('user_id')) {
            $this->redirect('/auth/login');
            return;
        }
        
        // Show form if GET request
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->view('auth/cambiar_password', [], false);
            return;
        }
        
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) == 'xmlhttprequest';
        
        $currentPassword = Validator::post('current_password');
        $newPassword = Validator::post('new_password');
        $confirmPassword = Validator::post('confirm_password');
        
        $userId = Session::get('user_id');
        $userModel = $this->model('User');
        $user = $userModel->findById($userId);
        
        // Validate current password
        if (!password_verify($currentPassword, $user['password'])) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Contraseña temporal incorrecta']);
                exit;
            }
            $this->view('auth/cambiar_password', ['error' => 'Contraseña temporal incorrecta'], false);
            return;
        }
        
        // Validate new password is different from current
        if ($newPassword === $currentPassword) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'La nueva contraseña debe ser diferente a la temporal']);
                exit;
            }
            $this->view('auth/cambiar_password', ['error' => 'La nueva contraseña debe ser diferente'], false);
            return;
        }
        
        // Validate passwords match
        if ($newPassword !== $confirmPassword) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Las contraseñas no coinciden']);
                exit;
            }
            $this->view('auth/cambiar_password', ['error' => 'Las contraseñas no coinciden'], false);
            return;
        }
        
        // Update password and clear flag
        if ($userModel->updatePassword($userId, $newPassword)) {
            Session::set('requiere_cambio_clave', 0);
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => true, 'redirect' => URLROOT . '/dashboard']);
                exit;
            }
            
            $this->redirect('/dashboard');
        } else {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => 'Error al actualizar la contraseña']);
                exit;
            }
            $this->view('auth/cambiar_password', ['error' => 'Error al actualizar'], false);
        }
    }

    /**
     * API endpoint for real-time email validation (AJAX)
     * Returns JSON: { exists: true/false }
     */
    public function apiCheckEmail(): void
    {
        header('Content-Type: application/json');
        
        // Get JSON input
        $data = json_decode(file_get_contents("php://input"), true);
        
        if (isset($data['email']) && !empty($data['email'])) {
            $email = trim($data['email']);
            
            // Validate email format
            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['exists' => false, 'valid' => false]);
                return;
            }
            
            // Check if email exists in database
            $userModel = $this->model('User');
            $user = $userModel->findByEmail($email);
            
            echo json_encode([
                'exists' => $user ? true : false,
                'valid' => true
            ]);
        } else {
            echo json_encode(['exists' => false, 'valid' => false]);
        }
    }

    public function logout(): void
    {
        // Aplicar cache control para prevenir botón atrás
        CacheControl::noCache();
        
        Session::start();
        
        // Limpiar todas las variables de sesión
        $_SESSION = [];
        
        // Destruir la cookie de sesión si existe
        if (isset($_COOKIE[session_name()])) {
            setcookie(session_name(), '', time() - 3600, '/');
        }
        
        // Destruir la sesión
        Session::destroy();
        
        // Redirigir al login con mensaje
        Session::start(); // Reiniciar para el flash message
        Session::setFlash('success', 'Sesión cerrada correctamente');
        
        $this->redirect('/auth/login');
    }

    protected function redirectByRole($roleId) {
        $routes = [
            1 => '/admin',      // Administrador
            2 => '/tutor',      // Tutor
            3 => '/pasante'     // Pasante
        ];
        $this->redirect($routes[$roleId] ?? '/auth/login');
    }
}
