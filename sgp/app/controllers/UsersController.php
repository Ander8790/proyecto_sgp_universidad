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
        
        // AJAX requests get JSON error instead of HTML redirect
        $isAjax = (!empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest')
                  || (isset($_SERVER['HTTP_ACCEPT']) && strpos($_SERVER['HTTP_ACCEPT'], 'application/json') !== false)
                  || (!empty($_SERVER['CONTENT_TYPE']) && strpos($_SERVER['CONTENT_TYPE'], 'application/json') !== false);
        
        $roleId = (int)Session::get('role_id');
        
        if (!in_array($roleId, [1, 2])) {
            // Pasantes (rol 3) can access AJAX endpoints (buscar, verUniversal)
            if ($isAjax && $roleId === 3) {
                // Allow — individual methods handle fine-grained permissions
            } else {
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'message' => 'Acceso denegado.']);
                    exit;
                }
                Session::setFlash('error', 'Acceso denegado.');
                $this->redirect('/dashboard');
                exit;
            }
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

        // Initialize Database for transaction control
        $config = require '../app/config/config.php';
        $db = new Database($config['db']);

        try {
            $db->beginTransaction();

            // 1. Create user with temporary password in 'usuarios'
            $data = [
                'cedula' => $cedula,
                'correo' => $correo,
                'rol_id' => $rol_id,
                'departamento_id' => $departamento_id
            ];

            // Use the model for user creation
            if (!$this->userModel->createWithTempPassword($data)) {
                throw new Exception('Error al crear registro en la tabla usuarios.');
            }

            // 2. Get the new User ID
            $userId = (int) $db->lastInsertId();

            // 3. Create record in 'datos_personales'
            $db->query("
                INSERT INTO datos_personales (usuario_id, nombres, apellidos)
                VALUES (:uid, :nom, :ape)
            ");
            $db->bind(':uid', $userId);
            $db->bind(':nom', $nombre);
            $db->bind(':ape', $apellido);

            if (!$db->execute()) {
                throw new Exception('Error al crear el registro de datos personales.');
            }

            // 4. Commit transaction
            $db->commit();

            // Log activity
            AuditModel::log('CREATE_USER', 'usuarios', $userId, [
                'cedula' => $cedula, 
                'rol_id' => $rol_id,
                'email' => $correo,
                'nombre_completo' => $nombre . ' ' . $apellido
            ]);
            
            $tempPassword = $this->userModel->generateTempPassword($cedula);
            $this->jsonResponse(true, 'Usuario creado exitosamente. Contraseña temporal: ' . $tempPassword);

        } catch (Exception $e) {
            $db->rollBack();
            error_log("Error creating user: " . $e->getMessage());
            $this->jsonResponse(false, 'Error al crear usuario: ' . $e->getMessage());
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
            // Get personal data (nombres/apellidos from datos_personales, cedula from usuarios)
            $config = require '../app/config/config.php';
            $db = new Database($config['db']);
            $db->query("
                SELECT u.cedula, dp.nombres, dp.apellidos
                FROM usuarios u
                LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
                WHERE u.id = :uid
            ");
            $db->bind(':uid', $id);
            $personalData = $db->single();
            
            $response = [
                'success' => true,
                'data' => [
                    'id'             => $user['id'],
                    'correo'         => $user['correo'],
                    'rol_id'         => $user['rol_id'],
                    'departamento_id'=> $user['departamento_id'] ?? null,
                    'estado'         => $user['estado'],
                    'cedula'         => $personalData->cedula   ?? '',
                    'nombres'        => $personalData->nombres  ?? '',
                    'apellidos'      => $personalData->apellidos ?? ''
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
    $nombres = htmlspecialchars(trim($_POST['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
    $apellidos = htmlspecialchars(trim($_POST['apellido'] ?? ''), ENT_QUOTES, 'UTF-8');
    $cedula = trim($_POST['cedula'] ?? '');
    
    // Validate required fields
    if (empty($nombres) || empty($apellidos) || empty($cedula)) {
        $this->jsonResponse(false, 'Nombre, apellido y cédula son obligatorios');
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

    // Update usuarios table (including cedula)
    $data = [
        'id'             => $id,
        'email'          => $correo,
        'role_id'        => $rol_id,
        'departamento_id'=> $departamento_id
    ];

    if (!$this->userModel->update($data)) {
        $this->jsonResponse(false, 'Error al actualizar usuario');
    }
    
    // Save cedula directly to usuarios
    $config = require '../app/config/config.php';
    $db = new Database($config['db']);
    $db->query("UPDATE usuarios SET cedula = :cedula WHERE id = :id");
    $db->bind(':cedula', $cedula);
    $db->bind(':id', $id);
    $db->execute(); // best-effort, no stop on error
    
    // Update or insert datos_personales (nombres, apellidos — NO cedula)
    $db->query("SELECT id FROM datos_personales WHERE usuario_id = :uid");
    $db->bind(':uid', $id);
    $exists = $db->single();
    
    if ($exists) {
        $db->query("
            UPDATE datos_personales 
            SET nombres   = :nombres,
                apellidos = :apellidos
            WHERE usuario_id = :usuario_id
        ");
    } else {
        $db->query("
            INSERT INTO datos_personales (usuario_id, nombres, apellidos)
            VALUES (:usuario_id, :nombres, :apellidos)
        ");
    }
    
    $db->bind(':usuario_id', $id);
    $db->bind(':nombres',    $nombres);
    $db->bind(':apellidos',  $apellidos);
    
    if ($db->execute()) {
        AuditModel::log('UPDATE_USER', 'usuarios', $id, [
            'campos_actualizados' => ['email', 'rol_id', 'departamento_id', 'cedula', 'datos_personales']
        ]);
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

        // Get user cedula — now lives in usuarios table
        $config = require '../app/config/config.php';
        $db = new Database($config['db']);
        $db->query("SELECT cedula FROM usuarios WHERE id = :uid");
        $db->bind(':uid', $id);
        $result = $db->single();

        if (!$result || empty($result->cedula)) {
            $this->jsonResponse(false, 'Usuario no tiene cédula registrada');
        }

        $cedula = $result->cedula;

        if ($this->userModel->resetToTempPassword($id, $cedula)) {
            AuditModel::log('RESET_PASSWORD', 'usuarios', $id, ['motivo' => 'Solicitud admin']);
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
            AuditModel::log('DELETE_USER', 'usuarios', $id, ['tipo' => 'soft_delete']);
            $this->jsonResponse(true, 'Usuario desactivado correctamente');
        } else {
            $this->jsonResponse(false, 'Error al desactivar usuario');
        }
    }

    /**
     * Get full user details for profile view (AJAX)
     */
    public function obtenerDetalles($encrypted_id)
    {
        // Decrypt and validate ID
        $id = UrlSecurity::validateAndDecrypt($encrypted_id);
        
        if (!$id) {
            $this->jsonResponse(false, 'URL inválida o enlace manipulado');
        }
        
        $config = require '../app/config/config.php';
        $db = new Database($config['db']);
        
        $db->query("
            SELECT 
                u.id, u.cedula, u.correo, u.rol_id, u.estado,
                dp.nombres, dp.apellidos, dp.telefono, dp.genero, dp.fecha_nacimiento,
                r.nombre as rol_nombre,
                i.nombre as nombre_institucion
            FROM usuarios u
            LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN roles r ON u.rol_id = r.id
            LEFT JOIN datos_pasante dpas ON u.id = dpas.usuario_id
            LEFT JOIN instituciones i ON dpas.institucion_procedencia = i.id
            WHERE u.id = :id
        ");
        $db->bind(':id', $id);
        $user = $db->single();
        
        if ($user) {
            // Format some fields for presentation
            $user->nombre_completo = trim(($user->nombres ?? '') . ' ' . ($user->apellidos ?? ''));
            $user->fecha_nacimiento_formateada = $user->fecha_nacimiento ? date('d/m/Y', strtotime($user->fecha_nacimiento)) : 'No registrada';
            $user->genero_texto = [
                'M' => 'Masculino',
                'F' => 'Femenino',
                'Otro' => 'Otro'
            ][$user->genero] ?? 'No especificado';
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true,
                'data' => $user
            ]);
            exit;
        } else {
            $this->jsonResponse(false, 'Usuario no encontrado');
        }
    }

    /**
     * Toggle user status activo/inactivo (AJAX)
     */
    public function toggleStatus($encrypted_id)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }

        // Decrypt and validate ID
        $id = UrlSecurity::validateAndDecrypt($encrypted_id);

        if (!$id) {
            $this->jsonResponse(false, 'URL inválida o enlace manipulado');
        }

        // Prevent self-deactivation
        if ($id == Session::get('user_id')) {
            $this->jsonResponse(false, 'No puedes cambiar el estado de tu propia cuenta');
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
            AuditModel::log('TOGGLE_USER_STATUS', 'usuarios', $id, ['nuevo_estado' => $newStatus]);
            $this->jsonResponse(true, 'Usuario ' . ($newStatus === 'activo' ? 'activado' : 'desactivado') . ' correctamente');
        } else {
            $this->jsonResponse(false, 'Error al actualizar estado');
        }
    }

    /**
     * AJAX: Búsqueda universal de usuarios (Admin o Tutor)
     * GET /users/buscar?q=texto
     */
    public function buscar()
    {
        header('Content-Type: application/json');
        
        $q = trim($_GET['q'] ?? '');
        if (strlen($q) < 2) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        $config = require '../app/config/config.php';
        $db = new Database($config['db']);

        $like = '%' . $q . '%';
        $rolFilter = isset($_GET['rol']) ? (int)$_GET['rol'] : 0;
        $rolWhere = $rolFilter > 0 ? ' AND u.rol_id = :rol_filter' : '';
        
        $db->query("
            SELECT u.id, u.cedula, u.rol_id, u.estado,
                   dp.nombres, dp.apellidos,
                   r.nombre AS rol_nombre,
                   d.nombre AS departamento
            FROM usuarios u
            LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN roles r ON u.rol_id = r.id
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            WHERE (u.cedula LIKE :q1 OR dp.nombres LIKE :q2 OR dp.apellidos LIKE :q3) $rolWhere
            ORDER BY dp.nombres ASC
            LIMIT 10
        ");
        $db->bind(':q1', $like);
        $db->bind(':q2', $like);
        $db->bind(':q3', $like);
        if ($rolFilter > 0) {
            $db->bind(':rol_filter', $rolFilter);
        }
        $results = $db->resultSet();

        $data = [];
        foreach ($results as $r) {
            $data[] = [
                'id'           => $r->id,
                'cedula'       => $r->cedula,
                'nombres'      => $r->nombres ?? '',
                'apellidos'    => $r->apellidos ?? '',
                'rol_id'       => $r->rol_id,
                'rol_nombre'   => $r->rol_nombre ?? 'Sin rol',
                'departamento' => $r->departamento ?? 'Sin asignar',
                'estado'       => $r->estado,
                'iniciales'    => strtoupper(substr($r->nombres ?? '?', 0, 1) . substr($r->apellidos ?? '', 0, 1))
            ];
        }

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
    }

    /**
     * AJAX: Ver usuario completo para Modal Universal (Admin o Tutor)
     * GET /users/verUniversal/{id}
     */
    public function verUniversal($userId = null)
    {
        header('Content-Type: application/json');

        $id = (int)($userId ?? 0);
        if (!$id) {
            echo json_encode(['success' => false, 'message' => 'ID inválido']);
            exit;
        }

        // Permiso: Solo Admin(1) o Tutor(2). Pasante solo su propio perfil.
        $currentRole = (int)Session::get('role_id');
        $currentUser = (int)Session::get('user_id');
        if ($currentRole === 3 && $id !== $currentUser) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }

        $config = require '../app/config/config.php';
        $db = new Database($config['db']);

        $db->query("
            SELECT 
                u.id, u.cedula, u.correo, u.rol_id, u.estado,
                u.departamento_id,
                COALESCE(dpas.horas_acumuladas, 0) AS horas_acumuladas,
                dp.nombres, dp.apellidos, dp.telefono, dp.genero, dp.fecha_nacimiento,
                dp.cargo,
                r.nombre AS rol_nombre,
                d.nombre AS departamento,
                dpas.institucion_procedencia AS institucion,
                COALESCE(dpas.horas_meta, 240) AS horas_requeridas,
                dpas.fecha_inicio_pasantia AS fecha_inicio,
                dpas.fecha_fin_estimada AS fecha_fin,
                dpas.estado_pasantia,
                COALESCE(
                    CONCAT(tutor_asig_dp.nombres, ' ', tutor_asig_dp.apellidos),
                    CONCAT(tutor_dp.nombres, ' ', tutor_dp.apellidos)
                ) AS tutor_nombre
            FROM usuarios u
            LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN roles r ON u.rol_id = r.id
            LEFT JOIN departamentos d ON u.departamento_id = d.id
            LEFT JOIN datos_pasante dpas ON u.id = dpas.usuario_id
            LEFT JOIN datos_personales tutor_dp ON dpas.tutor_id = tutor_dp.usuario_id
            LEFT JOIN asignaciones asig ON u.id = asig.pasante_id AND asig.estado = 'activo'
            LEFT JOIN datos_personales tutor_asig_dp ON asig.tutor_id = tutor_asig_dp.usuario_id
            WHERE u.id = :id
            LIMIT 1
        ");
        $db->bind(':id', $id);
        $user = $db->single();

        if (!$user) {
            echo json_encode(['success' => false, 'message' => 'Usuario no encontrado']);
            exit;
        }

        $isPasante = ((int)$user->rol_id === 3);
        $horasAcum = (float)($user->horas_acumuladas ?? 0);
        $horasReq  = (float)($user->horas_requeridas ?? 480);
        $porcHoras = $horasReq > 0 ? min(100, round(($horasAcum / $horasReq) * 100)) : 0;

        $data = [
            'id'             => $user->id,
            'cedula'         => $user->cedula,
            'correo'         => $user->correo ?? '',
            'nombres'        => $user->nombres ?? '',
            'apellidos'      => $user->apellidos ?? '',
            'telefono'       => $user->telefono ?? '',
            'genero'         => $user->genero ?? '',
            'cargo'          => $user->cargo ?? '',
            'fecha_nacimiento' => $user->fecha_nacimiento ? date('d/m/Y', strtotime($user->fecha_nacimiento)) : '',
            'rol_id'         => (int)$user->rol_id,
            'rol_nombre'     => $user->rol_nombre ?? 'Sin rol',
            'estado'         => $user->estado,
            'departamento'   => $user->departamento ?? 'Sin asignar',
            'institucion'    => $user->institucion ?? '',
            'iniciales'      => strtoupper(substr($user->nombres ?? '?', 0, 1) . substr($user->apellidos ?? '', 0, 1)),
            'es_pasante'     => $isPasante,
            'horas_acumuladas' => $horasAcum,
            'horas_requeridas' => $horasReq,
            'porcentaje_horas' => $porcHoras,
            'estado_pasantia'  => $user->estado_pasantia ?? '',
            'fecha_inicio'     => $user->fecha_inicio ? date('d/m/Y', strtotime($user->fecha_inicio)) : '',
            'fecha_fin'        => $user->fecha_fin ? date('d/m/Y', strtotime($user->fecha_fin)) : '',
            'tutor_nombre'     => $user->tutor_nombre ?? 'Sin asignar',
            'tiene_pin'        => $isPasante
        ];

        echo json_encode(['success' => true, 'data' => $data]);
        exit;
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
