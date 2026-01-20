<?php
/**
 * UsersController - Admin Panel User Management
 * Handles CRUD operations with modal-based interface
 */
class UsersController extends Controller
{
    private $userModel;

    public function __construct()
    {
        // Security
        CacheControl::noCache();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();
        
        Session::start();
        
        // Only Admin can access
        if (Session::get('role_id') != 1) {
            Session::setFlash('error', 'Acceso denegado. Solo administradores.');
            $this->redirect('/dashboard');
            exit;
        }
        
        $this->userModel = $this->model('User');
    }

    /**
     * List all users with DataTable
     */
    public function index()
    {
        $users = $this->userModel->getAllUsers();
        
        // Get roles for dropdown
        $config = require '../app/config/config.php';
        $db = new Database($config['db']);
        $db->query("SELECT id, nombre FROM roles ORDER BY id");
        $roles = $db->resultSet();
        
        // Get departamentos for tutor assignment
        $db->query("SELECT id, nombre FROM departamentos ORDER BY nombre");
        $departamentos = $db->resultSet();
        
        $this->view('users/index', [
            'users' => $users,
            'roles' => $roles,
            'departamentos' => $departamentos
        ]);
    }

    /**
     * Create user with temporary password (AJAX)
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }

        // Sanitize and validate input
        $nombre = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        $apellido = htmlspecialchars(trim($_POST['apellido'] ?? ''), ENT_QUOTES, 'UTF-8');
        $cedula = trim($_POST['cedula'] ?? '');
        $correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
        $rol_id = filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT);

        // Validate required fields
        if (empty($nombre) || empty($apellido) || empty($cedula)) {
            $this->jsonResponse(false, 'Todos los campos son obligatorios');
        }
        
        // Validate email format
        if (!$correo) {
            $this->jsonResponse(false, 'Formato de correo inválido');
        }

        // Validate nombre: only letters, spaces, and accents
        if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/', $nombre)) {
            $this->jsonResponse(false, 'El nombre solo puede contener letras y espacios');
        }
        
        // Validate apellido: only letters, spaces, and accents
        if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/', $apellido)) {
            $this->jsonResponse(false, 'El apellido solo puede contener letras y espacios');
        }
        
        // Validate length
        if (strlen($nombre) > 100) {
            $this->jsonResponse(false, 'El nombre es demasiado largo (máx. 100 caracteres)');
        }
        
        if (strlen($apellido) > 100) {
            $this->jsonResponse(false, 'El apellido es demasiado largo (máx. 100 caracteres)');
        }

        // Validate cedula: only numbers, 7-8 digits
        if (!preg_match('/^[0-9]{7,8}$/', $cedula)) {
            $this->jsonResponse(false, 'La cédula debe contener entre 7 y 8 dígitos');
        }

        // Check if email already exists
        if ($this->userModel->findByEmail($correo)) {
            $this->jsonResponse(false, 'El correo ya está registrado');
        }

        // Department validation for Tutors (rol_id = 2)
        $departamento_id = null;
        if ($rol_id == 2) {
            $departamento_id = filter_input(INPUT_POST, 'departamento_id', FILTER_VALIDATE_INT);
            if (!$departamento_id) {
                $this->jsonResponse(false, 'Debe seleccionar un departamento para el tutor');
            }
        }

        // Create user with temporary password
        $data = [
            'cedula' => $cedula,
            'correo' => $correo,
            'rol_id' => $rol_id,
            'departamento_id' => $departamento_id
        ];

        if ($this->userModel->createWithTempPassword($data)) {
            $tempPassword = $this->userModel->generateTempPassword($cedula);
            $this->jsonResponse(true, 'Usuario creado exitosamente. Contraseña temporal: ' . $tempPassword);
        } else {
            $this->jsonResponse(false, 'Error al crear usuario');
        }
    }

    /**
     * Get user data for editing (AJAX)
     */
    public function edit($encrypted_id)
    {
        // Decrypt and validate ID
        $id = UrlSecurity::validateAndDecrypt($encrypted_id);
        
        if (!$id) {
            $this->jsonResponse(false, 'URL inválida o enlace manipulado');
        }
        
        $user = $this->userModel->findById($id);
        
        if ($user) {
            // Get personal data if exists
            $config = require '../app/config/config.php';
            $db = new Database($config['db']);
            $db->query("SELECT cedula, nombres, apellidos FROM datos_personales WHERE usuario_id = :uid");
            $db->bind(':uid', $id);
            $personalData = $db->single();
            
            $response = [
                'success' => true,
                'data' => [
                    'id' => $user['id'],
                    'correo' => $user['correo'],
                    'rol_id' => $user['rol_id'],
                    'departamento_id' => $user['departamento_id'] ?? null,
                    'estado' => $user['estado'],
                    'cedula' => $personalData->cedula ?? '',
                    'nombres' => $personalData->nombres ?? '',
                    'apellidos' => $personalData->apellidos ?? ''
                ]
            ];
            
            header('Content-Type: application/json');
            echo json_encode($response);
        } else {
            $this->jsonResponse(false, 'Usuario no encontrado');
        }
    }

    /**
 * Update user (AJAX)
 */
public function update()
{
    if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
        $this->jsonResponse(false, 'Método no permitido');
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    $correo = filter_var(trim($_POST['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
    $rol_id = filter_input(INPUT_POST, 'rol_id', FILTER_VALIDATE_INT);
    
    // Sanitize identity fields
    $nombres = htmlspecialchars(trim($_POST['nombres'] ?? ''), ENT_QUOTES, 'UTF-8');
    $apellidos = htmlspecialchars(trim($_POST['apellidos'] ?? ''), ENT_QUOTES, 'UTF-8');
    $cedula = trim($_POST['cedula'] ?? '');
    
    // Validate required fields
    if (empty($nombres) || empty($apellidos) || empty($cedula)) {
        $this->jsonResponse(false, 'Nombres, apellidos y cédula son obligatorios');
    }
    
    // Validate email format
    if (!$correo) {
        $this->jsonResponse(false, 'Formato de correo inválido');
    }
    
    // Validate nombres: only letters, spaces, and accents
    if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/', $nombres)) {
        $this->jsonResponse(false, 'Los nombres solo pueden contener letras y espacios');
    }
    
    // Validate apellidos: only letters, spaces, and accents
    if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/', $apellidos)) {
        $this->jsonResponse(false, 'Los apellidos solo pueden contener letras y espacios');
    }
    
    // Validate length
    if (strlen($nombres) > 100) {
        $this->jsonResponse(false, 'Los nombres son demasiado largos (máx. 100 caracteres)');
    }
    
    if (strlen($apellidos) > 100) {
        $this->jsonResponse(false, 'Los apellidos son demasiado largos (máx. 100 caracteres)');
    }
    
    // Validate cedula: only numbers, 7-8 digits
    if (!preg_match('/^[0-9]{7,8}$/', $cedula)) {
        $this->jsonResponse(false, 'La cédula debe contener entre 7 y 8 dígitos');
    }

    // Check if email exists for another user
    $existing = $this->userModel->findByEmail($correo);
    if ($existing && $existing['id'] != $id) {
        $this->jsonResponse(false, 'El correo ya está en uso');
    }

    // Department validation for Tutors
    $departamento_id = null;
    if ($rol_id == 2) {
        $departamento_id = filter_input(INPUT_POST, 'departamento_id', FILTER_VALIDATE_INT);
        if (!$departamento_id) {
            $this->jsonResponse(false, 'Debe seleccionar un departamento para el tutor');
        }
    }

    // Update usuarios table
    $data = [
        'id' => $id,
        'email' => $correo,
        'role_id' => $rol_id,
        'departamento_id' => $departamento_id
    ];

    if (!$this->userModel->update($data)) {
        $this->jsonResponse(false, 'Error al actualizar usuario');
    }
    
    // Update or insert datos_personales
    $config = require '../app/config/config.php';
    $db = new Database($config['db']);
    
    // Check if datos_personales exists
    $db->query("SELECT id FROM datos_personales WHERE usuario_id = :uid");
    $db->bind(':uid', $id);
    $exists = $db->single();
    
    if ($exists) {
        // Update
        $db->query("
            UPDATE datos_personales 
            SET cedula = :cedula,
                nombres = :nombres,
                apellidos = :apellidos
            WHERE usuario_id = :usuario_id
        ");
    } else {
        // Insert
        $db->query("
            INSERT INTO datos_personales (usuario_id, cedula, nombres, apellidos)
            VALUES (:usuario_id, :cedula, :nombres, :apellidos)
        ");
    }
    
    $db->bind(':usuario_id', $id);
    $db->bind(':cedula', $cedula);
    $db->bind(':nombres', $nombres);
    $db->bind(':apellidos', $apellidos);
    
    if ($db->execute()) {
        $this->jsonResponse(true, 'Usuario actualizado correctamente');
    } else {
        $this->jsonResponse(false, 'Error al actualizar datos personales');
    }
}

    /**
     * Reset user password to temporary (AJAX)
     */
    public function reset($encrypted_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }
        
        // Decrypt and validate ID
        $id = UrlSecurity::validateAndDecrypt($encrypted_id);
        
        if (!$id) {
            $this->jsonResponse(false, 'URL inválida o enlace manipulado');
        }

        // Get user cedula
        $config = require '../app/config/config.php';
        $db = new Database($config['db']);
        $db->query("SELECT cedula FROM datos_personales WHERE usuario_id = :uid");
        $db->bind(':uid', $id);
        $result = $db->single();

        if (!$result || empty($result->cedula)) {
            $this->jsonResponse(false, 'Usuario no tiene cédula registrada');
        }

        $cedula = $result->cedula;

        if ($this->userModel->resetToTempPassword($id, $cedula)) {
            $tempPassword = $this->userModel->generateTempPassword($cedula);
            $this->jsonResponse(true, 'Contraseña restablecida a: ' . $tempPassword);
        } else {
            $this->jsonResponse(false, 'Error al restablecer contraseña');
        }
    }

    /**
     * Delete user (soft delete - set estado to inactivo)
     */
    public function delete($encrypted_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }
        
        // Decrypt and validate ID
        $id = UrlSecurity::validateAndDecrypt($encrypted_id);
        
        if (!$id) {
            $this->jsonResponse(false, 'URL inválida o enlace manipulado');
        }

        // Prevent self-deletion
        if ($id == Session::get('user_id')) {
            $this->jsonResponse(false, 'No puedes eliminar tu propia cuenta');
        }

        // Soft delete - set estado to inactivo
        $config = require '../app/config/config.php';
        $db = new Database($config['db']);
        $db->query("UPDATE usuarios SET estado = 'inactivo' WHERE id = :id");
        $db->bind(':id', $id);

        if ($db->execute()) {
            $this->jsonResponse(true, 'Usuario desactivado correctamente');
        } else {
            $this->jsonResponse(false, 'Error al desactivar usuario');
        }
    }

    /**
     * Toggle user status (AJAX)
     */
    public function toggleStatus($id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }

        $user = $this->userModel->findById($id);
        if (!$user) {
            $this->jsonResponse(false, 'Usuario no encontrado');
        }

        $newStatus = $user['estado'] === 'activo' ? 'inactivo' : 'activo';

        $config = require '../app/config/config.php';
        $db = new Database($config['db']);
        $db->query("UPDATE usuarios SET estado = :status WHERE id = :id");
        $db->bind(':status', $newStatus);
        $db->bind(':id', $id);

        if ($db->execute()) {
            $this->jsonResponse(true, 'Estado actualizado a: ' . $newStatus);
        } else {
            $this->jsonResponse(false, 'Error al actualizar estado');
        }
    }

    /**
     * JSON response helper
     */
    private function jsonResponse($success, $message)
    {
        header('Content-Type: application/json');
        echo json_encode([
            'success' => $success,
            'message' => $message
        ]);
        exit;
    }
}
