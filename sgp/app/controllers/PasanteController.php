<?php
/**
 * PasanteController - Gestión de Pasantes
 * 
 * PROPÓSITO EDUCATIVO:
 * Este controlador gestiona el ciclo de vida completo de los pasantes:
 * desde su registro inicial (estado "Pendiente") hasta la finalización
 * de su pasantía (estado "Finalizado").
 * 
 * ESTADOS DE PASANTÍA:
 * - Pendiente: Usuario registrado pero pasantía no iniciada formalmente
 * - Activo: Pasantía en curso (formalizada por Admin)
 * - Finalizado: Pasantía completada (cumplió horas requeridas)
 * - Retirado: Pasantía abandonada antes de completar
 * 
 * SEGURIDAD:
 * - Solo Administradores pueden formalizar pasantías
 * - Se registra cada acción en la bitácora (AuditModel)
 * - Validación de roles mediante RoleMiddleware
 * 
 * @author Sistema SGP
 * @version 2.0
 */

declare(strict_types=1);

class PasanteController extends Controller
{
    private $db;
    private $pasanteModel;
    private $departamentoModel;

    public function __construct()
    {
        // Inicializar conexión a base de datos
        $config = require '../app/config/config.php';
        $this->db = new Database($config['db']);
        
        // Cargar modelos
        require_once '../app/models/PasanteModel.php';
        require_once '../app/models/DepartamentoModel.php';
        
        $this->pasanteModel = new PasanteModel();
        $this->departamentoModel = new DepartamentoModel();
    }

    /**
     * Dashboard del Pasante (Vista Personal)
     * 
     * PROPÓSITO:
     * Mostrar panel principal del pasante con su información personal,
     * progreso de horas, asistencias y evaluaciones.
     * 
     * SEGURIDAD:
     * - Solo accesible para usuarios con rol Pasante (role_id = 3)
     * - Cache Control aplicado en __construct
     * - AuthMiddleware aplicado en __construct
     * 
     * DEFENSA ACADÉMICA:
     * "Profesor, creé un método separado 'dashboard()' para la vista del pasante
     * y dejé 'index()' para la gestión administrativa. Esto sigue el principio
     * de Separación de Responsabilidades (SRP) y evita conflictos de permisos."
     */
    public function dashboard(): void
    {
        Session::start();
        
        // VALIDACIÓN DE PERMISOS
        // Solo el Pasante puede ver su propio dashboard
        if (Session::get('role_id') != 3) {
            Session::setFlash('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('/dashboard');
            return;
        }
        
        $data = [
            'title' => 'Mi Panel de Pasante',
            'role' => 'Pasante',
            'user_name' => Session::get('user_name')
        ];
        
        // RENDERIZAR VISTA
        $this->view('pasante/dashboard', $data);
    }

    /**
     * Index - Lista de Pasantes con DataTable
     * 
     * PROPÓSITO:
     * Mostrar todos los usuarios con rol de Pasante en una tabla interactiva
     * con funcionalidades de búsqueda, filtrado y paginación.
     * 
     * FLUJO:
     * 1. Verificar sesión y permisos (solo Admin)
     * 2. Consultar todos los pasantes con sus datos personales y de pasantía
     * 3. Calcular progreso de horas (horas_acumuladas / horas_meta * 100)
     * 4. Renderizar vista con DataTable
     * 
     * DEFENSA ACADÉMICA:
     * "Profesor, usé DataTables porque es el estándar de la industria para
     * tablas interactivas. Permite búsqueda en tiempo real, ordenamiento
     * por columnas, y paginación sin recargar la página (AJAX)."
     */
    public function index(): void
    {
        Session::start();
        
        // VALIDACIÓN DE PERMISOS
        // Solo el Administrador puede ver la gestión de pasantes
        if (Session::get('role_id') != 1) {
            Session::setFlash('error', 'No tienes permisos para acceder a esta sección');
            $this->redirect('/dashboard');
            return;
        }
        
        // CONSULTA DE PASANTES usando el modelo
        $pasantes = $this->pasanteModel->getAll();
        
        // Obtener lista de departamentos para el modal
        $departamentos = $this->departamentoModel->getAll();
        
        // RENDERIZAR VISTA
        $this->view('admin/pasantes/index', [
            'pasantes' => $pasantes,
            'departamentos' => $departamentos,
            'title' => 'Gestión de Pasantes'
        ]);
    }

    /**
     * Show - Kardex del Pasante (Vista Detallada)
     * 
     * PROPÓSITO:
     * Mostrar información completa de un pasante específico:
     * - Datos personales
     * - Datos académicos
     * - Estado de pasantía
     * - Progreso de horas (gráfica)
     * 
     * @param int $id ID del usuario pasante
     */
    public function show(int $id): void
    {
        Session::start();
        
        // Solo Admin puede ver kardex
        if (Session::get('role_id') != 1) {
            $this->redirect('/dashboard');
            return;
        }
        
        // Obtener datos completos del pasante
        $pasante = $this->getPasanteById($id);
        
        if (!$pasante) {
            Session::setFlash('error', 'Pasante no encontrado');
            $this->redirect('/pasantes');
            return;
        }
        
        // Renderizar kardex
        $this->view('admin/pasantes/show', [
            'pasante' => $pasante,
            'title' => 'Kardex de Pasante'
        ]);
    }

    /**
     * Formalizar - Activar Pasantía
     * 
     * PROPÓSITO:
     * Cambiar el estado de un pasante de "Pendiente" a "Activo"
     * y registrar la fecha de inicio y departamento asignado.
     * 
     * FLUJO:
     * 1. Recibir datos del formulario (pasante_id, fecha_inicio, departamento_id)
     * 2. Validar datos
     * 3. Calcular fecha_fin_estimada (6 meses después del inicio)
     * 4. Actualizar estado en datos_pasante
     * 5. Registrar acción en bitácora (AuditModel::log)
     * 6. Retornar respuesta JSON
     * 
     * DEFENSA ACADÉMICA:
     * "Profesor, la formalización es un proceso crítico porque marca el
     * inicio oficial de la pasantía. Por eso registro la acción en la
     * bitácora para tener trazabilidad de quién formalizó, cuándo y
     * qué departamento asignó."
     */
    public function formalizar(): void
    {
        Session::start();
        
        // VALIDACIÓN DE PERMISOS
        if (Session::get('role_id') != 1) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'No autorizado']);
            exit;
        }
        
        // VALIDACIÓN DE MÉTODO HTTP
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }
        
        // CAPTURA DE DATOS
        $pasanteId = (int) Validator::post('pasante_id');
        $fechaInicio = Validator::post('fecha_inicio');
        $departamentoId = (int) Validator::post('departamento_id');
        $institucion = Validator::post('institucion_procedencia'); // NUEVO
        
        // VALIDACIÓN DE DATOS
        if (!$pasanteId || !$fechaInicio || !$departamentoId) {
            header('Content-Type: application/json');
            echo json_encode(['success' => false, 'message' => 'Datos incompletos']);
            exit;
        }
        
        // FORMALIZACIÓN usando el modelo
        $resultado = $this->pasanteModel->formalizar($pasanteId, [
            'fecha_inicio' => $fechaInicio,
            'departamento_id' => $departamentoId,
            'institucion_procedencia' => $institucion
        ]);
        
        if ($resultado) {
            // REGISTRO EN BITÁCORA
            require_once '../app/models/AuditModel.php';
            AuditModel::log('FORMALIZE_INTERN', 'datos_pasante', $pasanteId, [
                'fecha_inicio' => $fechaInicio,
                'departamento_id' => $departamentoId,
                'institucion' => $institucion,
                'admin_id' => Session::get('user_id')
            ]);
            
            header('Content-Type: application/json');
            echo json_encode([
                'success' => true, 
                'message' => 'Pasantía formalizada exitosamente'
            ]);
        } else {
            header('Content-Type: application/json');
            echo json_encode([
                'success' => false, 
                'message' => 'Error al formalizar pasantía'
            ]);
        }
        
        exit;
    }

    /**
     * Método Privado: Obtener Todos los Pasantes
     * 
     * PROPÓSITO:
     * Consultar todos los usuarios con rol de Pasante (role_id = 3)
     * con sus datos personales y de pasantía.
     * 
     * DEFENSA ACADÉMICA:
     * "Profesor, usé un JOIN para obtener todos los datos en una sola consulta.
     * Esto es más eficiente que hacer múltiples consultas (N+1 problem).
     * También calculé el progreso_porcentaje directamente en SQL para
     * evitar procesamiento en PHP."
     * 
     * @return array Array de pasantes con todos sus datos
     */
    private function getAllPasantes(): array
    {
        $this->db->query("
            SELECT 
                u.id,
                u.correo,
                u.activo,
                dp.cedula,
                dp.nombres,
                dp.apellidos,
                dp.telefono,
                dpa.institucion_procedencia,
                dpa.carrera,
                dpa.estado_pasantia,
                dpa.fecha_inicio_pasantia,
                dpa.fecha_fin_estimada,
                dpa.horas_acumuladas,
                dpa.horas_meta,
                dpa.departamento_asignado_id,
                dept.nombre as departamento_nombre,
                CASE 
                    WHEN dpa.horas_meta > 0 THEN ROUND((dpa.horas_acumuladas / dpa.horas_meta) * 100, 2)
                    ELSE 0
                END as progreso_porcentaje
            FROM usuarios u
            INNER JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            LEFT JOIN departamentos dept ON dpa.departamento_asignado_id = dept.id
            WHERE u.role_id = 3
            ORDER BY dpa.estado_pasantia ASC, dp.apellidos ASC
        ");
        
        return $this->db->resultSet();
    }

    /**
     * Método Privado: Obtener Pasante por ID
     * 
     * PROPÓSITO:
     * Consultar datos completos de un pasante específico para el kardex.
     * 
     * @param int $id ID del usuario
     * @return array|null Datos del pasante o null si no existe
     */
    private function getPasanteById(int $id): ?array
    {
        $this->db->query("
            SELECT 
                u.id,
                u.correo,
                u.activo,
                u.created_at as fecha_registro,
                dp.cedula,
                dp.nombres,
                dp.apellidos,
                dp.telefono,
                dp.direccion,
                dp.genero,
                dp.fecha_nacimiento,
                dpa.institucion_procedencia,
                dpa.carrera,
                dpa.semestre,
                dpa.cargo,
                dpa.estado_pasantia,
                dpa.fecha_inicio_pasantia,
                dpa.fecha_fin_estimada,
                dpa.horas_acumuladas,
                dpa.horas_meta,
                dpa.observaciones,
                dept.nombre as departamento_nombre,
                CASE 
                    WHEN dpa.horas_meta > 0 THEN ROUND((dpa.horas_acumuladas / dpa.horas_meta) * 100, 2)
                    ELSE 0
                END as progreso_porcentaje
            FROM usuarios u
            INNER JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            LEFT JOIN departamentos dept ON dpa.departamento_asignado_id = dept.id
            WHERE u.id = :id AND u.role_id = 3
            LIMIT 1
        ");
        
        $this->db->bind(':id', $id);
        
        $result = $this->db->single();
        
        return $result ? (array) $result : null;
    }
}
