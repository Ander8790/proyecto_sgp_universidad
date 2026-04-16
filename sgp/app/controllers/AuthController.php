<?php
class AuthController extends Controller
{
    public function index() {
        $this->login();
    }

    /**
     * Iniciar Sesión de Usuario
     * 
     * LÓGICA DE CONTROL (Dualidad de Acceso):
     * 1. Autenticación: Verifica credenciales contra hashed password en BD.
     * 2. Auditoría: Valida que la cuenta esté 'activa'.
     * 3. "La Jaula" (Fuerza Wizard):
     *    - Si requiere_cambio_clave = 1: Redirige al Wizard (Paso 1). Típico en usuarios creados por Admin.
     *    - Si perfil_completado = false: Redirige al Wizard (Paso 3). Típico en auto-registros.
     * 
     * @return void
     */
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

        try {

        // SGP-FIX-v2 [T8] Rate limiting simplificado por email
        // Ya no penalizamos con 15 minutos de inactividad por IP.
        // Si hay intentos, solo sirven para hacer trigger a la validación de estado.
        $ip    = $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
        $email = trim($_POST['email'] ?? '');
        $db    = Database::getInstance();

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
            // SGP-FIX-v2 [1.4] — Mensaje genérico para evitar enumeración de usuarios (OWASP)
            $errorMsg = 'Credenciales incorrectas. Verifica tu correo y contraseña.';

            // SGP-FIX-v2 [T8] Registrar intento fallido
            $db->query(
                'INSERT INTO intentos_acceso (direccion_ip, correo, intentos, ultimo_intento)
                 VALUES (:ip, :email, 1, NOW())
                 ON DUPLICATE KEY UPDATE
                     intentos       = intentos + 1,
                     ultimo_intento = NOW()'
            );
            $db->bind(':ip',    $ip);
            $db->bind(':email', $email);
            $db->execute();

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
            // SGP-FIX-v2 [1.4] — Mismo mensaje genérico (no revelar si correo existe)
            $errorMsg = 'Credenciales incorrectas. Verifica tu correo y contraseña.';

            // SGP-FIX-v2 [T8] Registrar intento fallido
            $db->query(
                'INSERT INTO intentos_acceso (direccion_ip, correo, intentos, ultimo_intento)
                 VALUES (:ip, :email, 1, NOW())
                 ON DUPLICATE KEY UPDATE
                     intentos       = intentos + 1,
                     ultimo_intento = NOW()'
            );
            $db->bind(':ip',    $ip);
            $db->bind(':email', $email);
            $db->execute();

            // SGP Hard Lock: Bloquear si alcanza 5 intentos consecutivos (cuenta existente)
            $db->query(
                'SELECT intentos FROM intentos_acceso
                 WHERE direccion_ip = :ip AND correo = :email
                 LIMIT 1'
            );
            $db->bind(':ip',    $ip);
            $db->bind(':email', $email);
            $fila = $db->single();

            if ($fila && (int)$fila->intentos >= 5) {
                // Hard Lock: Cambia el estado del usuario permanentemente hasta el reset.
                $db->query("UPDATE usuarios SET estado = 'bloqueado' WHERE id = :id_usuario");
                $db->bind(':id_usuario', $user['id']);
                $db->execute();
                
                // Actualizar la variable local de usuario para el flujo posterior.
                $user['estado'] = 'bloqueado';
            }
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            Session::setFlash('login_error', $errorMsg);
            $this->view('auth/login', [], false);
            return;
        }

        // 3. Verificar si la cuenta está activa (Estado Auditoría Nivel Crítico)
        if (($user['estado'] ?? 'activo') === 'bloqueado') {
            $errorMsg = 'Tu cuenta ha sido bloqueada por seguridad tras múltiples intentos fallidos.<br><br><a href="' . URLROOT . '/auth/recovery" style="color:#ffffff; font-weight:bold; text-decoration:underline;">➜ Recuperar mi cuenta ahora</a>';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            
            // Pasamos raw_html = true por si el sistema lo renderiza
            Session::setFlash('login_error', $errorMsg);
            $this->view('auth/login', [], false);
            return;
        }
        
        if (($user['estado'] ?? 'activo') === 'inactivo') {
            $errorMsg = 'Esta cuenta ha sido desactivada. Contacte al administrador.';
            
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            
            Session::setFlash('login_error', $errorMsg);
            $this->view('auth/login', [], false);
            return;
        }

        // ✨ LOGIN EXITOSO: Reset contador de intentos
        // SGP-FIX-v2 [T8] Limpiar intentos fallidos tras login exitoso
        $db->query(
            'DELETE FROM intentos_acceso
             WHERE direccion_ip = :ip AND correo = :email'
        );
        $db->bind(':ip',    $ip);
        $db->bind(':email', $email);
        $db->execute();

        Session::regenerate();
        Session::set('user_id', $user['id']);
        Session::set('role_id', $user['role_id']);
        
        // Tarea: Establecer nombre real desde datos_personales
        $fullName = trim(($user['nombres'] ?? '') . ' ' . ($user['apellidos'] ?? ''));
        if (empty($fullName)) $fullName = 'Usuario';
        Session::set('user_name', $fullName);

        Session::set('requiere_cambio_clave', $user['requiere_cambio_clave'] ?? 0);
        Session::set('departamento_id', $user['departamento_id'] ?? null);
        Session::set('user_avatar', $user['avatar'] ?? 'default.png');
        Session::set('last_activity', time());

        // Verificar si el perfil está completo
        $perfilCompletado = $userModel->verificarPerfilCompleto((int)$user['id']);
        Session::set('perfil_completado', $perfilCompletado);

        // Registrar login en bitácora
        AuditModel::log('LOGIN');

        // Redirección al Wizard si requiere cambio de clave O perfil incompleto
        if ($user['requiere_cambio_clave'] == 1 || !$perfilCompletado) {
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode([
                    'success'   => true,
                    'redirect'  => URLROOT . '/wizard/index',
                    'user_name' => $fullName
                ]);
                exit;
            }
            $this->redirect('/wizard/index');
            return;
        }

        if ($isAjax) {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'redirect' => URLROOT . '/dashboard',
                'user_name' => $fullName
            ]);
            exit;
        }

        // ✨ Toast de bienvenida en el Dashboard
        Session::setFlash('success', '¡Bienvenido, ' . $fullName . '!');

        $this->redirectByRole($user['role_id']);

        } catch (\Throwable $e) {
            error_log('[SGP-AUTH] login() DB error: ' . $e->getMessage());
            $genericMsg = 'Error cargando el sistema. Intente de nuevo.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $genericMsg]);
                exit;
            }
            $this->view('auth/login', ['error' => $genericMsg], false);
            return;
        }
    }

    /**
     * Registro Público de Pasantes
     * 
     * FLUJO TÉCNICO:
     * 1. Validación de Entrada: Email y Cédula únicos.
     * 2. Transacción ACID: Crea cuenta y perfil simultáneamente.
     * 3. Configuración Inicial:
     *    - requiere_cambio_clave = 0: El usuario mismo elije su clave.
     *    - perfil_completado = false: Disparad por falta de campos obligatorios.
     * 4. Seguridad: Inserta 3 respuestas de seguridad hasheadas.
     * 
     * @return void
     */
    public function register(): void
    {
        Session::start();
        
        // ============================================
        // CARGAR PREGUNTAS SIEMPRE (GET y POST)
        // ============================================
        $preguntas = $this->model('User')->getSecurityQuestions();

        // Fallback: Datos de prueba si la BD está vacía
        if (empty($preguntas)) {
            error_log('[SGP-AUTH] [WARN] No se encontraron preguntas en la BD. Usando datos de prueba.');
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
                'cedula' => $cedula, // FIX: PERSISTENCIA EN TABLA USUARIOS
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

            // ✨ NUEVO: Validar que las 3 respuestas sean diferentes (case-insensitive)
            $r1_lower = strtolower(trim($respuesta1));
            $r2_lower = strtolower(trim($respuesta2));
            $r3_lower = strtolower(trim($respuesta3));

            if ($r1_lower === $r2_lower || $r1_lower === $r3_lower || $r2_lower === $r3_lower) {
                throw new Exception('Las respuestas de seguridad deben ser diferentes');
            }

            // ✨ NUEVO: Validar longitud mínima de respuestas (seguridad)
            if (strlen($respuesta1) < 3 || strlen($respuesta2) < 3 || strlen($respuesta3) < 3) {
                throw new Exception('Las respuestas deben tener al menos 3 caracteres');
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
            
        } catch (\Throwable $e) {
            // ============================================
            // ROLLBACK: REVERTIR TODO (ACID)
            // ============================================
            $userModel->rollBack();
            error_log('[SGP-AUTH] register() error: ' . $e->getMessage());

            // Mensajes explícitos de validación se muestran al usuario.
            // Errores internos (PDOException, etc.) muestran mensaje genérico.
            $mensajesValidacion = [
                'El correo ya existe',
                'La cédula ya está registrada',
                'Debes seleccionar 3 preguntas diferentes',
                'Las respuestas de seguridad deben ser diferentes',
                'Las respuestas deben tener al menos 3 caracteres',
            ];
            $errorMsg = in_array($e->getMessage(), $mensajesValidacion)
                ? $e->getMessage()
                : 'Error al procesar el registro. Intente de nuevo.';

            $this->view('auth/register', [
                'questions' => $preguntas,
                'error' => $errorMsg
            ], false);
        }
    }

    public function recovery(): void
    {
        Session::start();

        // ============================================================
        // 🛡️ CAPA 1 — GUARDIA: No mezclar recovery con sesión activa
        // Si el usuario ya tiene sesión iniciada en otra pestaña,
        // redirigirlo a su dashboard en lugar de continuar el flujo.
        // ============================================================
        if (Session::get('user_id')) {
            $this->redirectByRole(Session::get('role_id'));
            return;
        }

        // ============================================================
        // 🛡️ CAPA 2 — HEADERS NO-CACHÉ
        // Impide que el navegador restaure estas páginas desde caché
        // al presionar el botón "Atrás", evitando estados obsoletos.
        // ============================================================
        CacheControl::noCache();

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            // Limpiar cualquier sesión de recovery previa al iniciar
            $this->clearRecoverySession();
            $this->view('auth/recovery', [], false);
            return;
        }

        $step = $_POST['step'] ?? '1';

        // ============================================================
        // PASO 1: Validar correo y mostrar preguntas de seguridad
        // ============================================================
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

            // 🛡️ CAPA 3 — TOKEN DE FLUJO CON EXPIRACIÓN (15 minutos)
            // Cada Recovery genera un token único. Si el usuario retrocede
            // más allá del tiempo límite, el flujo se invalida automáticamente.
            Session::set('rec_uid',            $user['id']);
            Session::set('rec_email',          $email);  // Requerido por solicitud de ayuda
            Session::set('rec_token',          bin2hex(random_bytes(16)));
            Session::set('rec_token_expires',  time() + 900); // 15 minutos
            Session::set('rec_verified',       false);

            $this->view('auth/recovery_questions', ['questions' => $questions], false);
        }

        // ============================================================
        // PASO 2: Verificar respuestas de seguridad
        // ============================================================
        elseif ($step == '2') {
            $uid     = Session::get('rec_uid');
            $expires = Session::get('rec_token_expires');

            // Validar que el flujo sea vigente (sesión + token no expirado)
            if (!$uid || !$expires || time() > $expires) {
                $this->clearRecoverySession();
                $this->redirect('/auth/recovery');
                return;
            }

            $inputAnswers = $_POST['answers'] ?? [];
            $userAnswers  = $this->model('User')->getUserAnswers($uid);

            $allCorrect = true;
            foreach ($userAnswers as $ua) {
                $qid = $ua['question_id'];
                if (!isset($inputAnswers[$qid]) || !password_verify(strtolower(trim($inputAnswers[$qid])), $ua['answer_hash'])) {
                    $allCorrect = false;
                    break;
                }
            }

            if ($allCorrect) {
                // Marcar verificación exitosa y renovar expiración
                Session::set('rec_verified',      true);
                Session::set('rec_token_expires', time() + 900); // 15 min para cambiar contraseña
                $this->view('auth/reset_password', [], false);
            } else {
                $this->view('auth/recovery_questions', ['questions' => $userAnswers, 'error' => 'Las respuestas de seguridad no coinciden. Verifíquelas e intente nuevamente.'], false);
            }
        }

        // ============================================================
        // PASO 3: Actualizar contraseña
        // ============================================================
        elseif ($step == '3') {
            $uid     = Session::get('rec_uid');
            $expires = Session::get('rec_token_expires');

            // Doble verificación: token vigente + respuestas validadas
            if (!Session::get('rec_verified') || !$uid || !$expires || time() > $expires) {
                $this->clearRecoverySession();
                $this->redirect('/auth/recovery');
                return;
            }

            $pass = Validator::post('password');

            // 🔒 Bloquear contraseña temporal como contraseña permanente
            $userRec = $this->model('User')->findById($uid);
            $cedula  = $userRec['cedula'] ?? '';
            if (!empty($cedula) && $pass === 'Sgp.' . $cedula) {
                $this->view('auth/reset_password', [
                    'error' => 'No puedes usar tu contraseña temporal como nueva contraseña. Elige una contraseña personalizada.'
                ], false);
                return;
            }

            $this->model('User')->updatePassword($uid, $pass);

            // Limpiar SOLO las variables de recovery, no destruir sesión entera
            $this->clearRecoverySession();

            Session::setFlash('success', 'Contraseña actualizada correctamente. Por favor inicia sesión.');
            $this->redirect('/auth/login');
        }
    }

    /**
     * Limpia todas las variables de sesión del flujo de recovery.
     * Se llama al completar el flujo, al cancelar, o al detectar token expirado.
     */
    private function clearRecoverySession(): void
    {
        $keys = ['rec_uid', 'rec_email', 'rec_token', 'rec_token_expires', 'rec_verified', 'recovery_email'];
        foreach ($keys as $key) {
            if (isset($_SESSION[$key])) {
                unset($_SESSION[$key]);
            }
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

        // 🔒 Bloquear contraseña temporal como contraseña permanente
        $cedula = $user['cedula'] ?? '';
        if (!empty($cedula) && $newPassword === 'Sgp.' . $cedula) {
            $errorMsg = 'No puedes usar tu contraseña temporal como contraseña permanente. Elige una contraseña personalizada.';
            if ($isAjax) {
                header('Content-Type: application/json');
                echo json_encode(['success' => false, 'message' => $errorMsg]);
                exit;
            }
            $this->view('auth/cambiar_password', ['error' => $errorMsg], false);
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
        
        $razon = $_GET['razon'] ?? '';
        
        Session::start();
        
        // Registrar logout en bitácora
        // Usamos el ID de la sesión actual antes de destruirla
        AuditModel::log('LOGOUT');

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
        
        if ($razon === 'inactividad') {
            Session::setFlash('inactividad', 'Tu sesión ha concluido automáticamente debido un tiempo prolongado de inactividad.');
            session_write_close(); // Garantizar que el flash quede escrito antes del redirect
            $this->redirect('/auth/login?razon=inactividad');
        } else {
            Session::setFlash('success', 'Sesión cerrada correctamente');
            $this->redirect('/auth/login');
        }
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
        $this->verifyCsrf(); // SGP-FIX-v2 [1.1] aplicado

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

    /**
     * Solicitar ayuda al administrador cuando el usuario olvida sus respuestas de seguridad (AJAX).
     *
     * FLUJO:
     * 1. Validar que es una petición AJAX con email válido en sesión de recovery.
     * 2. Rate-limit: 1 solicitud cada 24 h por usuario para evitar spam.
     * 3. Obtener nombre + cédula del usuario desde datos_personales (JOIN).
     * 4. Consultar TODOS los admins activos (role_id = 1).
     * 5. Crear notificación tipo 'warning' para cada admin vía NotificationModel.
     * 6. Devolver JSON de éxito/error al frontend.
     */
    public function requestHelp(): void
    {
        Session::start();

        // ── Validar petición AJAX ─────────────────────────────────────────────
        $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH'])
                  && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

        if (!$isAjax) {
            $this->redirect('/auth/login');
            return;
        }

        header('Content-Type: application/json');

        try {
            // ── Leer y validar el email del body JSON ─────────────────────────
            $raw   = file_get_contents('php://input');
            $data  = json_decode($raw, true);
            $email = trim($data['email'] ?? '');

            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Correo inválido o no proporcionado.']);
                exit;
            }

            $db = Database::getInstance();

            // ── Obtener usuario con nombre y cédula (cedula está en `usuarios`, no en datos_personales) ──
            $db->query("
                SELECT u.id,
                       u.correo,
                       u.cedula,
                       dp.nombres,
                       dp.apellidos
                FROM   usuarios u
                LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
                WHERE  u.correo = :email
                LIMIT 1
            ");
            $db->bind(':email', $email);
            $usuario = $db->single();

            if (!$usuario) {
                echo json_encode(['success' => false, 'message' => 'Usuario no encontrado en el sistema.']);
                exit;
            }

            $userId    = (int) $usuario->id;
            $nombreC   = trim(($usuario->nombres ?? 'Usuario') . ' ' . ($usuario->apellidos ?? ''));
            $cedulaC   = $usuario->cedula ?? 'N/D';

            // ── Rate-limit: máximo 1 solicitud por usuario en las últimas 24h ─
            $db->query("
                SELECT COUNT(*) AS total
                FROM   notificaciones
                WHERE  tipo    = 'warning'
                  AND  titulo  = 'Asistencia de Seguridad Requerida'
                  AND  mensaje LIKE :patron
                  AND  created_at >= DATE_SUB(NOW(), INTERVAL 24 HOUR)
            ");
            $db->bind(':patron', '%' . $cedulaC . '%');
            $check = $db->single();

            if ($check && (int)$check->total > 0) {
                echo json_encode([
                    'success' => false,
                    'message' => 'Ya tienes una solicitud pendiente. El administrador la revisará pronto.'
                ]);
                exit;
            }

            // ── Obtener TODOS los administradores activos (rol_id = 1) ────────
            $db->query("
                SELECT id
                FROM   usuarios
                WHERE  rol_id = 1
                  AND  estado  = 'activo'
            ");
            $admins = $db->resultSet();

            if (empty($admins)) {
                // Sin admins activos: registrar en log pero responder éxito al usuario
                error_log('[SGP-AUTH][requestHelp] No se encontraron administradores activos para notificar.');
                echo json_encode([
                    'success' => true,
                    'message' => 'Solicitud registrada. Un administrador te contactará pronto.'
                ]);
                exit;
            }

            // ── Instanciar NotificationModel y disparar evento por cada admin ─
            require_once APPROOT . '/models/NotificationModel.php';
            $notifModel = new NotificationModel($db);

            $titulo  = 'Asistencia de Seguridad Requerida';
            $mensaje = "El usuario {$nombreC} (Cédula: {$cedulaC}) ha olvidado sus respuestas "
                     . "de seguridad y solicita asistencia para restablecer su acceso. "
                     . "Correo: {$email}.";
            $url     = URLROOT . '/users';

            foreach ($admins as $admin) {
                $notifModel->create((int) $admin->id, 'warning', $titulo, $mensaje, $url);
            }

            echo json_encode([
                'success' => true,
                'message' => 'Solicitud enviada correctamente.'
            ]);

        } catch (\Throwable $e) {
            // Exponer el error real para diagnóstico (en producción considerar mensaje genérico)
            error_log('[SGP-AUTH][requestHelp] Error: ' . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error interno: ' . $e->getMessage()
            ]);
        }

        exit;
    }

    protected function redirectByRole($roleId) {
        $routes = [
            1 => '/admin',      // Administrador
            2 => '/tutor',      // Tutor
            3 => '/pasante'     // Pasante
        ];
        $this->redirect($routes[$roleId] ?? '/auth/login');
    }

    /**
     * Renueva la sesión activa desde JS (keep-alive de inactividad).
     * SGP-FIX-v2 [sesión_inactividad paso_4] aplicado
     */
    public function keepalive(): void
    {
        header('Content-Type: application/json');
        if (empty($_SESSION['user_id'])) {
            http_response_code(401);
            echo json_encode(['success' => false]);
            exit;
        }
        $_SESSION['last_activity'] = time();
        echo json_encode(['success' => true]);
        exit;
    }
}
