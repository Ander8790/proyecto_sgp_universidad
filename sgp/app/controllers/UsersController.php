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

        // 1. CSRF Validation
        $csrf_token = $_POST['csrf_token'] ?? '';
        if (!Session::validateCsrfToken($csrf_token)) {
            http_response_code(403);
            $this->jsonResponse(false, 'Token CSRF inválido o expirado. Por favor recarga la página.');
        }

        // 2. Validate extracted logic
        $validacion = $this->validarDatosUsuario($_POST);
        if (!$validacion['success']) {
            $this->jsonResponse(false, $validacion['message']);
        }
        
        $datos = $validacion['data'];
        $nombre = $datos['nombre'];
        $apellido = $datos['apellido'];
        $cedula = $datos['cedula'];
        $correo = $datos['correo'];
        $rol_id = $datos['rol_id'];
        $departamento_id = $datos['departamento_id'];

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

    // 1. CSRF Validation
    $csrf_token = $_POST['csrf_token'] ?? '';
    if (!Session::validateCsrfToken($csrf_token)) {
        http_response_code(403);
        $this->jsonResponse(false, 'Token CSRF inválido o expirado. Por favor recarga la página.');
    }

    $id = filter_input(INPUT_POST, 'id', FILTER_VALIDATE_INT);
    if (!$id) {
        $this->jsonResponse(false, 'ID de usuario inválido');
    }

    // 2. Validate extracted logic
    $validacion = $this->validarDatosUsuario($_POST, $id);
    if (!$validacion['success']) {
        $this->jsonResponse(false, $validacion['message']);
    }
    
    $datos = $validacion['data'];
    $nombres = $datos['nombre'];
    $apellidos = $datos['apellido'];
    $cedula = $datos['cedula'];
    $correo = $datos['correo'];
    $rol_id = $datos['rol_id'];
    $departamento_id = $datos['departamento_id'];

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
     * Validar consistencia de datos de usuario (Principio DRY)
     */
    private function validarDatosUsuario($datos, $usuario_id = null)
    {
        // Sanitize fields
        $nombre = htmlspecialchars(trim($datos['nombre'] ?? ''), ENT_QUOTES, 'UTF-8');
        $apellido = htmlspecialchars(trim($datos['apellido'] ?? ''), ENT_QUOTES, 'UTF-8');
        $cedula = trim($datos['cedula'] ?? '');
        $correo = filter_var(trim($datos['correo'] ?? ''), FILTER_VALIDATE_EMAIL);
        $rol_id = filter_var($datos['rol_id'] ?? null, FILTER_VALIDATE_INT);

        // Required fields
        if (empty($nombre) || empty($apellido) || empty($cedula)) {
            return ['success' => false, 'message' => 'Nombre, apellido y cédula son obligatorios'];
        }

        if (!$correo) {
            return ['success' => false, 'message' => 'Formato de correo inválido'];
        }

        // Format rules
        if (!preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/', $nombre) || !preg_match('/^[A-Za-záéíóúÁÉÍÓÚñÑ\s]+$/', $apellido)) {
            return ['success' => false, 'message' => 'Nombres y apellidos solo pueden contener letras y espacios'];
        }

        if (strlen($nombre) > 100 || strlen($apellido) > 100) {
            return ['success' => false, 'message' => 'El nombre/apellido excede los 100 caracteres permitidos'];
        }

        if (!preg_match('/^[0-9]{7,8}$/', $cedula)) {
            return ['success' => false, 'message' => 'La cédula debe contener entre 7 y 8 dígitos numéricos'];
        }

        // Duplicate checks
        $existingEmail = $this->userModel->findByEmail($correo);
        if ($existingEmail && ($usuario_id === null || $existingEmail['id'] != $usuario_id)) {
            return ['success' => false, 'message' => 'El correo ya está registrado por otro usuario'];
        }
        
        $existingCedula = $this->userModel->findByCedula($cedula);
        if ($existingCedula && ($usuario_id === null || $existingCedula['id'] != $usuario_id)) {
            return ['success' => false, 'message' => 'La cédula ya se encuentra registrada en el sistema'];
        }

        // Tutors strict dependencies
        $departamento_id = null;
        if ($rol_id == 2) {
            $departamento_id = filter_var($datos['departamento_id'] ?? null, FILTER_VALIDATE_INT);
            if (!$departamento_id) {
                return ['success' => false, 'message' => 'Debe seleccionar obligatoriamente un departamento para asignar al tutor'];
            }
        }

        return [
            'success' => true,
            'data' => [
                'nombre' => $nombre,
                'apellido' => $apellido,
                'cedula' => $cedula,
                'correo' => $correo,
                'rol_id' => $rol_id,
                'departamento_id' => $departamento_id
            ]
        ];
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
    public function obtenerDetalles($id)
    {
        // Validate ID (allow plain ID for AJAX GET)
        $id = filter_var($id, FILTER_VALIDATE_INT);
        
        if (!$id) {
            $this->jsonResponse(false, 'ID de usuario inválido o manipulado');
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

        $user = $this->userModel->getDatosCompletos($id);

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
     * AJAX: Buscar pasante por cédula (para Kardex en Reportes)
     * POST /users/buscarPorCedula  { cedula: '12345678' }
     * Solo Admin (1) o Tutor (2)
     */
    public function buscarPorCedula(): void {
        header('Content-Type: application/json');

        // Solo admins y tutores
        $role = (int)Session::get('role_id');
        if (!in_array($role, [1, 2])) {
            echo json_encode(['success' => false, 'message' => 'Acceso denegado']);
            exit;
        }

        $cedula = trim($_POST['cedula'] ?? '');

        // Normalizar: quitar prefijo V-, v-, E-, etc.
        $cedula = preg_replace('/^[a-zA-Z]-?/', '', $cedula);

        if (empty($cedula) || !preg_match('/^[0-9]{6,9}$/', $cedula)) {
            echo json_encode(['success' => false, 'message' => 'Cédula inválida']);
            exit;
        }

        $p = $this->userModel->findPasanteByCedula($cedula);

        if (!$p) {
            echo json_encode(['success' => false, 'message' => 'No se encontró ningún pasante con esa cédula']);
            exit;
        }

        echo json_encode([
            'success' => true,
            'pasante' => [
                'id'               => (int)$p->id,
                'cedula'           => $p->cedula,
                'nombres'          => $p->nombres ?? '',
                'apellidos'        => $p->apellidos ?? '',
                'correo'           => $p->correo ?? '',
                'departamento'     => $p->departamento ?? '—',
                'estado_pasantia'  => $p->estado_pasantia ?? '—',
                'horas_acumuladas' => (int)($p->horas_acumuladas ?? 0),
                'horas_meta'       => (int)($p->horas_meta ?? 0),
            ]
        ]);
        exit;
    }

    /**
     * PDF: Exportar Ficha Técnica / Kardex (Previsualización)
     */
    public function exportPdf(int $id = 0): void {
        if (!$id) {
            http_response_code(400);
            die('ID de usuario requerido.');
        }

        $config = require APPROOT . '/config/config.php';
        $db = new Database($config['db']);

        // Extraer Data
        $db->query("
            SELECT dp.nombres, dp.apellidos, u.cedula, u.correo, u.rol_id, dp.telefono,
                dpa.estado_pasantia, dpa.horas_acumuladas, dpa.horas_meta,
                dpa.fecha_inicio_pasantia, dpa.fecha_fin_estimada,
                dpa.institucion_procedencia, d.nombre AS departamento, r.nombre AS nombre_rol, u.estado AS user_estado
            FROM usuarios u
            JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            LEFT JOIN roles r ON r.id = u.rol_id
            WHERE u.id = :id
        ");
        $db->bind(':id', $id);
        $p = $db->single();
        
        if (!$p) {
            http_response_code(404);
            die('Usuario no encontrado.');
        }

        $isPasante = ($p->rol_id == 3);
        $evals = [];
        if ($isPasante) {
            $db->query("SELECT * FROM evaluaciones WHERE pasante_id = :id ORDER BY fecha_evaluacion DESC LIMIT 5");
            $db->bind(':id', $id);
            $evals = $db->resultSet();
        }

        $data = [
            'p' => $p,
            'isPasante' => $isPasante,
            'evals' => $evals
        ];

        $autoload = dirname(APPROOT) . '/vendor/autoload.php';
        if (!file_exists($autoload)) {
            die('Autoloader de Composer no encontrado.');
        }
        require_once $autoload;

        // Configuración Dompdf
        $options = new \Dompdf\Options();
        $options->set('isRemoteEnabled', true); 
        $options->set('isHtml5ParserEnabled', true);
        $options->setDefaultFont('Helvetica');
        $dompdf = new \Dompdf\Dompdf($options);

        // Capturar Vista HTML
        ob_start();
        require APPROOT . '/views/users/pdf_profile.php';
        $html = ob_get_clean();

        // Renderizar y forzar previsualización (Attachment => false)
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();

        $dompdf->stream("Perfil_Usuario_{$id}.pdf", ["Attachment" => false]);
        exit();
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
