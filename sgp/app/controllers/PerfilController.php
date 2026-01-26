<?php
/**
 * PerfilController - Role-Based Profile Management
 * Handles profile completion for Pasantes and Tutores
 */

class PerfilController extends Controller
{
    private $userModel;
    private $db;
    private $notificationModel;

    public function __construct()
    {
        Session::start();
        
        if (!Session::get('user_id')) {
            header('Location: ' . URLROOT . '/auth/login');
            exit;
        }
        
        $this->userModel = $this->model('User');
        $config = require '../app/config/config.php';
        $this->db = new Database($config['db']);
        
        // Import Notification model
        require_once '../app/models/Notification.php';
        $this->notificationModel = new Notification($this->db);
    }

    /**
     * Default action - redirect to completar
     */
    public function index()
    {
        $this->completar();
    }

    /**
 * Route to appropriate profile form based on role
 */
public function completar()
{
    Session::start();
    $userId = Session::get('user_id');
    
    // Get user data with department name
    $this->db->query("
        SELECT 
            u.id,
            u.correo,
            u.rol_id,
            u.departamento_id,
            dp.cedula,
            dp.nombres,
            dp.apellidos,
            d.nombre as departamento_nombre
        FROM usuarios u
        LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
        LEFT JOIN departamentos d ON u.departamento_id = d.id
        WHERE u.id = :uid
    ");
    $this->db->bind(':uid', $userId);
    $user = $this->db->single();
    
    // Get security questions
    $questions = $this->userModel->getSecurityQuestions();
    
    $data = [
        'user' => $user,
        'questions' => $questions
    ];
    
    $this->view('perfil/completar_wizard', $data);
}







    /**
 * Save Wizard data (all 3 steps) - ATOMIC TRANSACTION
 */
public function guardarWizard()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->redirect('/perfil/completar');
        return;
    }

    Session::start();
    $userId = Session::get('user_id');
    
    // Get current user
    $user = $this->userModel->findById($userId);
    
    // STEP 1: Validate and STORE password in session (don't commit yet)
    $currentPassword = $_POST['current_password'] ?? '';
    $newPassword = $_POST['new_password'] ?? '';
    $confirmPassword = $_POST['confirm_password'] ?? '';
    
    // Verify current password
    if (!password_verify($currentPassword, $user['password'])) {
        Session::setFlash('error', 'La contraseña actual es incorrecta');
        $this->redirect('/perfil/completar');
        return;
    }
    
    // Validate new password
    if ($newPassword !== $confirmPassword) {
        Session::setFlash('error', 'Las contraseñas no coinciden');
        $this->redirect('/perfil/completar');
        return;
    }
    
    if (strlen($newPassword) < 8) {
        Session::setFlash('error', 'La contraseña debe tener al menos 8 caracteres');
        $this->redirect('/perfil/completar');
        return;
    }
    
    // STEP 2: Validate and STORE security questions in session
    $securityQuestions = [];
    for ($i = 1; $i <= 3; $i++) {
        $questionId = $_POST["question_$i"] ?? null;
        $answer = $_POST["answer_$i"] ?? '';
        
        if (!$questionId || !$answer) {
            Session::setFlash('error', 'Debe completar todas las preguntas de seguridad');
            $this->redirect('/perfil/completar');
            return;
        }
        
        $securityQuestions[] = [
            'question_id' => $questionId,
            'answer' => $answer
        ];
    }
    
    // STEP 3: Validate personal data
    $datosPersonales = [
        'usuario_id' => $userId,
        'cedula' => trim($_POST['cedula']),
        'nombres' => trim($_POST['nombres']),
        'apellidos' => trim($_POST['apellidos']),
        'cargo' => trim($_POST['cargo'] ?? ''),
        'telefono' => trim($_POST['telefono']),
        'direccion' => trim($_POST['direccion']),
        'genero' => $_POST['genero'],
        'fecha_nacimiento' => $_POST['fecha_nacimiento']
    ];
    
    // Validate required fields
    foreach ($datosPersonales as $key => $value) {
        if ($key !== 'usuario_id' && empty($value)) {
            Session::setFlash('error', 'Todos los campos son obligatorios');
            $this->redirect('/perfil/completar');
            return;
        }
    }
    
    // ============================================
    // ATOMIC COMMIT: All or nothing
    // ============================================
    
    try {
        // Begin transaction
        $this->db->beginTransaction();
        
        // 1. Update password and remove flag
        $hashedPassword = password_hash($newPassword, PASSWORD_DEFAULT);
        $this->db->query("UPDATE usuarios SET password = :password, requiere_cambio_clave = 0 WHERE id = :id");
        $this->db->bind(':password', $hashedPassword);
        $this->db->bind(':id', $userId);
        
        if (!$this->db->execute()) {
            throw new Exception('Error al actualizar contraseña');
        }
        
        // 2. Save security questions
        foreach ($securityQuestions as $sq) {
            $this->db->query("
                INSERT INTO usuarios_respuestas (usuario_id, pregunta_id, respuesta_hash)
                VALUES (:usuario_id, :pregunta_id, :respuesta_hash)
            ");
            $this->db->bind(':usuario_id', $userId);
            $this->db->bind(':pregunta_id', $sq['question_id']);
            $this->db->bind(':respuesta_hash', password_hash(strtolower(trim($sq['answer'])), PASSWORD_DEFAULT));
            
            if (!$this->db->execute()) {
                throw new Exception('Error al guardar preguntas de seguridad');
            }
        }
        
        // 3. Save or update personal data
        $this->db->query("SELECT id FROM datos_personales WHERE usuario_id = :uid");
        $this->db->bind(':uid', $userId);
        $exists = $this->db->single();
        
        if ($exists) {
            // Update
            $this->db->query("
                UPDATE datos_personales 
                SET cedula = :cedula,
                    nombres = :nombres,
                    apellidos = :apellidos,
                    cargo = :cargo,
                    telefono = :telefono,
                    direccion = :direccion,
                    genero = :genero,
                    fecha_nacimiento = :fecha_nacimiento
                WHERE usuario_id = :usuario_id
            ");
        } else {
            // Insert
            $this->db->query("
                INSERT INTO datos_personales 
                (usuario_id, cedula, nombres, apellidos, cargo, telefono, direccion, genero, fecha_nacimiento) 
                VALUES (:usuario_id, :cedula, :nombres, :apellidos, :cargo, :telefono, :direccion, :genero, :fecha_nacimiento)
            ");
        }
        
        foreach ($datosPersonales as $key => $value) {
            $this->db->bind(':' . $key, $value);
        }
        
        if (!$this->db->execute()) {
            throw new Exception('Error al guardar datos personales');
        }
        
        // Commit transaction
        $this->db->commit();
        
        // Update session
        Session::set('requiere_cambio_clave', 0);
        Session::set('user_name', trim($_POST['nombres']) . ' ' . trim($_POST['apellidos']));
        
        Session::setFlash('success', '¡Bienvenido a SGP! Tu perfil ha sido completado exitosamente');
        $this->redirect('/dashboard');
        
    } catch (Exception $e) {
        // Rollback on error
        $this->db->rollback();
        Session::setFlash('error', 'Error al guardar: ' . $e->getMessage());
        $this->redirect('/perfil/completar');
    }
}

    /**
     * View user profile
     */
    public function ver()
    {
        $userId = Session::get('user_id');
        $roleId = Session::get('role_id');
        
        // Base query
        $query = "
            SELECT 
                u.id,
                u.correo,
                u.rol_id,
                u.estado,
                u.created_at,
                r.nombre as rol_nombre,
                dp.cedula,
                dp.nombres,
                dp.apellidos,
                dp.cargo,
                dp.telefono,
                dp.direccion,
                dp.genero,
                dp.fecha_nacimiento
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
        ";
        
        // Add role-specific joins
        if ($roleId == 2) { // Tutor
            $query .= " LEFT JOIN datos_tutor dt ON u.id = dt.usuario_id
                       LEFT JOIN departamentos d ON dt.departamento_id = d.id";
        } elseif ($roleId == 3) { // Pasante
            $query .= " LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id";
        }
        
        $query .= " WHERE u.id = :uid";
        
        $this->db->query($query);
        $this->db->bind(':uid', $userId);
        $userData = $this->db->single();
        
        if (!$userData) {
            $this->redirect('/dashboard');
            return;
        }
        
        $this->view('perfil/ver', [
            'title' => 'Mi Perfil',
            'user' => (array) $userData
        ]);
    }
    
    /**
     * Update user profile
     */
    public function actualizar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/perfil/ver');
            return;
        }

        $userId = Session::get('user_id');
        $roleId = Session::get('role_id');
        
        // Common personal data
        $datosPersonales = [
            'cedula' => trim($_POST['cedula']),
            'nombres' => trim($_POST['nombres']),
            'apellidos' => trim($_POST['apellidos']),
            'cargo' => trim($_POST['cargo']),
            'telefono' => trim($_POST['telefono']),
            'direccion' => trim($_POST['direccion']),
            'genero' => $_POST['genero'],
            'fecha_nacimiento' => $_POST['fecha_nacimiento']
        ];
        
        try {
            // Update datos_personales
            $this->db->query("
                UPDATE datos_personales 
                SET cedula = :cedula,
                    nombres = :nombres,
                    apellidos = :apellidos,
                    cargo = :cargo,
                    telefono = :telefono,
                    direccion = :direccion,
                    genero = :genero,
                    fecha_nacimiento = :fecha_nacimiento
                WHERE usuario_id = :usuario_id
            ");
            
            foreach ($datosPersonales as $key => $value) {
                $this->db->bind(':' . $key, $value);
            }
            $this->db->bind(':usuario_id', $userId);
            
            if (!$this->db->execute()) {
                Session::setFlash('error', 'Error al actualizar datos personales');
                $this->redirect('/perfil/ver');
                return;
            }
            
            // Update role-specific data
            if ($roleId == 3 && !empty($_POST['institucion_procedencia'])) { // Pasante
                $this->db->query("
                    UPDATE datos_pasante 
                    SET institucion_procedencia = :institucion
                    WHERE usuario_id = :usuario_id
                ");
                $this->db->bind(':institucion', trim($_POST['institucion_procedencia']));
                $this->db->bind(':usuario_id', $userId);
                $this->db->execute();
            }
            
            // Update session name
            Session::set('user_name', trim($_POST['nombres']) . ' ' . trim($_POST['apellidos']));
            
            // Create notification
            $this->notificationModel->create([
                'usuario_id' => $userId,
                'tipo' => 'perfil_actualizado',
                'titulo' => 'Perfil Actualizado',
                'mensaje' => 'Tu información personal ha sido actualizada correctamente',
                'url' => '/perfil/ver'
            ]);
            
            Session::setFlash('success', 'Perfil actualizado exitosamente');
            $this->redirect('/perfil/ver');
            
        } catch (PDOException $e) {
            // Check if it's a duplicate entry error
            if ($e->getCode() == 23000 && strpos($e->getMessage(), 'Duplicate entry') !== false) {
                // Extract field name from error message
                // MySQL error format: "Duplicate entry 'value' for key 'field_name'"
                $errorMessage = $e->getMessage();
                $fieldName = 'desconocido';
                
                // Try to extract the field name from the error message
                if (preg_match("/for key '(\w+)'/", $errorMessage, $matches)) {
                    $fieldName = $matches[1];
                }
                
                // Map database field names to user-friendly names
                $fieldLabels = [
                    'cedula' => 'cédula',
                    'telefono' => 'teléfono',
                    'correo' => 'correo electrónico',
                    'usuario_id' => 'usuario'
                ];
                
                $friendlyFieldName = $fieldLabels[$fieldName] ?? $fieldName;
                
                // Extract the duplicate value if possible
                if (preg_match("/Duplicate entry '([^']+)'/", $errorMessage, $valueMatches)) {
                    $duplicateValue = $valueMatches[1];
                    Session::setFlash('error', "El valor '{$duplicateValue}' para el campo {$friendlyFieldName} ya está registrado en el sistema. Por favor, verifica el dato ingresado.");
                } else {
                    Session::setFlash('error', "El {$friendlyFieldName} ingresado ya está registrado en el sistema. Por favor, verifica los datos.");
                }
            } else {
                // Generic database error
                Session::setFlash('error', 'Error al actualizar el perfil. Por favor, intenta nuevamente.');
            }
            $this->redirect('/perfil/ver');
        }
    }
}
