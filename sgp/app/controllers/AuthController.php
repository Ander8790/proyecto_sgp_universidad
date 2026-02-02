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

        // ============================================
        // VALIDACIÓN ESPECÍFICA (UX MEJORADA)
        // ============================================
        // Separamos la validación para dar feedback específico al usuario
        
        // 1. Verificar si el correo existe
        if (!$user) {
            $errorMsg = 'El correo electrónico no se encuentra registrado.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            Session::setFlash('login_error', $errorMsg);
            $this->view('auth/login', [], false);
            return;
        }
        
        // 2. Verificar si la contraseña es correcta
        if (!password_verify($password, $user['password'])) {
            $errorMsg = 'La contraseña ingresada es incorrecta.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            Session::setFlash('login_error', $errorMsg);
            $this->view('auth/login', [], false);
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
        
        // ============================================
        // CARGAR PREGUNTAS SIEMPRE (GET y POST)
        // ============================================
        $preguntas = $this->model('User')->getSecurityQuestions();

        // Fallback: Datos de prueba si la BD está vacía
        if (empty($preguntas)) {
            error_log("⚠️ ADVERTENCIA: No se encontraron preguntas en la BD. Usando datos de prueba.");
            $preguntas = [
                ['id' => 1, 'pregunta' => '🔧 PRUEBA: ¿Nombre de tu primera mascota?'],
                ['id' => 2, 'pregunta' => '🔧 PRUEBA: ¿Ciudad donde naciste?'],
                ['id' => 3, 'pregunta' => '🔧 PRUEBA: ¿Tu comida favorita?'],
                ['id' => 4, 'pregunta' => '🔧 PRUEBA: ¿Nombre de tu mejor amigo de infancia?'],
                ['id' => 5, 'pregunta' => '🔧 PRUEBA: ¿Marca de tu primer carro?']
            ];
        }

        // ============================================
        // GET REQUEST: Mostrar formulario
        // ============================================
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: text/html; charset=UTF-8');
            $this->view('auth/register', ['questions' => $preguntas], false);
            return;
        }
        
        // ============================================
        // POST REQUEST: Procesar registro
        // ============================================
        
        // Capturar datos del formulario
        $fullName = Validator::post('name');
        $cedula = Validator::post('cedula');
        $email = Validator::email('email');
        $password = Validator::post('password');
        $roleId = 3; // Pasante por defecto

        $userModel = $this->model('User');
        
        // ============================================
        // TRANSACCIÓN ACID: TODO O NADA
        // ============================================
        try {
            // Iniciar transacción
            $userModel->beginTransaction();
            
            // Validación 1: Email duplicado
            if ($userModel->findByEmail($email)) {
                throw new Exception('El correo ya existe');
            }

            // Validación 2: Cédula duplicada
            if ($userModel->findByCedula($cedula)) {
                throw new Exception('La cédula ya está registrada');
            }

            // Parsear nombre completo
            $nameParts = explode(' ', trim($fullName), 2);
            $nombres = $nameParts[0] ?? 'Usuario';
            $apellidos = $nameParts[1] ?? 'Nuevo';

            // Crear usuario
            $userData = [
                'email' => $email,
                'password' => $password,
                'role_id' => $roleId,
                'departamento_id' => null,
                'requiere_cambio_clave' => 0
            ];

            if (!$userModel->create($userData)) {
                throw new Exception('Error al crear el usuario');
            }

            // Obtener el usuario recién creado
            $newUser = $userModel->findByEmail($email);
            
            if (!$newUser) {
                throw new Exception('Error al obtener el usuario creado');
            }

            // ============================================
            // GUARDAR RESPUESTAS DE SEGURIDAD (3 PREGUNTAS)
            // ============================================
            $pregunta1 = Validator::post('pregunta_id_1');
            $respuesta1 = Validator::post('respuesta_1');
            
            $pregunta2 = Validator::post('pregunta_id_2');
            $respuesta2 = Validator::post('respuesta_2');
            
            $pregunta3 = Validator::post('pregunta_id_3');
            $respuesta3 = Validator::post('respuesta_3');

            // Validar que las 3 preguntas sean diferentes
            if ($pregunta1 == $pregunta2 || $pregunta1 == $pregunta3 || $pregunta2 == $pregunta3) {
                throw new Exception('Debes seleccionar 3 preguntas diferentes');
            }

            // Guardar las 3 respuestas (si falla alguna, se hace rollback)
            if (!$userModel->saveSecurityAnswer((int)$newUser['id'], (int)$pregunta1, $respuesta1)) {
                throw new Exception('Error al guardar la respuesta de seguridad 1');
            }
            
            if (!$userModel->saveSecurityAnswer((int)$newUser['id'], (int)$pregunta2, $respuesta2)) {
                throw new Exception('Error al guardar la respuesta de seguridad 2');
            }
            
            if (!$userModel->saveSecurityAnswer((int)$newUser['id'], (int)$pregunta3, $respuesta3)) {
                throw new Exception('Error al guardar la respuesta de seguridad 3');
            }

            // ============================================
            // GUARDAR DATOS PERSONALES
            // ============================================
            $perfilData = [
                'usuario_id' => $newUser['id'],
                'cedula' => $cedula,
                'nombres' => $nombres,
                'apellidos' => $apellidos,
                'telefono' => null,
                'direccion' => null,
                'genero' => null,
                'fecha_nacimiento' => null
            ];
            
            if (!$userModel->registrarPerfil($perfilData)) {
                throw new Exception('Error al crear el perfil del usuario');
            }

            // ============================================
            // CONFIRMAR TRANSACCIÓN (TODO EXITOSO)
            // ============================================
            $userModel->commit();
            
            // PATRÓN PRG (Post-Redirect-Get): Redirigir al login
            // Esto evita el error ERR_CACHE_MISS al dar "Atrás" en el navegador
            header('Location: ' . URLROOT . '/auth/login?status=success');
            exit();
            
        } catch (Exception $e) {
            // ============================================
            // ROLLBACK: REVERTIR TODO (ACID)
            // ============================================
            $userModel->rollBack();
            
            // Mostrar vista con error Y CON LAS PREGUNTAS
            $this->view('auth/register', [
                'questions' => $preguntas,
                'error' => $e->getMessage()
            ], false);
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

    /**
     * Solicitar Desbloqueo de Cuenta (Recuperación Asistida)
     * 
     * PROPÓSITO:
     * Permitir que usuarios bloqueados (que olvidaron respuestas de seguridad)
     * soliciten ayuda al administrador mediante una notificación interna.
     * 
     * FLUJO:
     * 1. Verificar sesión de recuperación válida
     * 2. Obtener datos del usuario
     * 3. Verificar que no existe solicitud pendiente (prevenir abuso)
     * 4. Crear notificación para el administrador
     * 5. Limpiar sesión y redirigir con mensaje de confirmación
     * 
     * SEGURIDAD:
     * - Solo 1 solicitud cada 24 horas por usuario
     * - Requiere sesión de recuperación activa
     * - Limpia sesión después de solicitar
     */
    public function solicitarDesbloqueo()
    {
        // 1. Verificar sesión de recuperación
        if (!isset($_SESSION['recovery_user_id'])) {
            Session::setFlash('error', 'Sesión expirada. Inicia el proceso de recuperación nuevamente.');
            $this->redirect('/auth/recovery');
            return;
        }
        
        $userId = $_SESSION['recovery_user_id'];
        
        // 2. Obtener datos del usuario
        $user = $this->userModel->findById($userId);
        if (!$user) {
            Session::setFlash('error', 'Usuario no encontrado.');
            $this->redirect('/auth/login');
            return;
        }
        
        // 3. Verificar si ya existe una solicitud pendiente (últimas 24 horas)
        // NOTA: Esto previene que usuarios hagan spam de solicitudes
        $this->db->query("
            SELECT COUNT(*) as count 
            FROM notificaciones 
            WHERE tipo = 'unlock_request' 
            AND metadata LIKE :user_pattern
            AND created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
        ");
        $this->db->bind(':user_pattern', '%"user_id":' . $userId . '%');
        $result = $this->db->single();
        
        if ($result && $result['count'] > 0) {
            Session::setFlash('warning', 'Ya tienes una solicitud pendiente. El administrador la revisará pronto.');
            $this->redirect('/auth/login');
            return;
        }
        
        // 4. Crear notificación para el administrador
        $adminId = 1; // ID del administrador principal
        
        $mensaje = "El usuario {$user['nombre']} {$user['apellido']} ({$user['email']}) ha solicitado un reseteo de cuenta por olvido de respuestas de seguridad.";
        
        $metadata = json_encode([
            'user_id' => $userId,
            'user_email' => $user['email'],
            'user_name' => $user['nombre'] . ' ' . $user['apellido'],
            'request_type' => 'unlock_account'
        ]);
        
        $this->db->query("
            INSERT INTO notificaciones (user_id, tipo, mensaje, metadata, leida, created_at) 
            VALUES (:user_id, 'unlock_request', :mensaje, :metadata, 0, NOW())
        ");
        $this->db->bind(':user_id', $adminId);
        $this->db->bind(':mensaje', $mensaje);
        $this->db->bind(':metadata', $metadata);
        
        if ($this->db->execute()) {
            // 5. Limpiar sesión de recuperación
            unset($_SESSION['recovery_user_id']);
            unset($_SESSION['recovery_questions']);
            
            // 6. Redirigir con mensaje de éxito
            Session::setFlash('success', 'Solicitud enviada. El administrador revisará tu caso y te contactará pronto.');
            $this->redirect('/auth/login');
        } else {
            Session::setFlash('error', 'Error al enviar la solicitud. Intenta nuevamente.');
            $this->redirect('/auth/recovery');
        }
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
