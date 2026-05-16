<?php
/**
 * BitacoraController - Controlador de Auditoría
 * 
 * PROPÓSITO:
 * Gestionar la visualización y filtrado de registros de auditoría del sistema.
 * Solo accesible para administradores.
 * 
 * RESPONSABILIDADES:
 * - Mostrar vista principal con tabla de logs
 * - Filtrar registros por usuario, acción, fecha
 * - Exportar logs a CSV
 * - Proveer estadísticas para dashboard
 * 
 * @author Sistema SGP
 * @version 1.0
 */

class BitacoraController extends Controller
{
    private $auditModel;
    private $userModel;

    public function __construct()
    {
        // Verificar autenticación y rol de administrador
        AuthMiddleware::require();
        RoleMiddleware::authorize([0, 1]); // Validación estricta solo para administradores
        
        // Inicializar modelos
        $this->auditModel = $this->model('Audit');
        $this->userModel = $this->model('User');
    }

    /**
     * Vista Principal - Tabla de Bitácora
     * 
     * FLUJO:
     * 1. Obtener últimos 500 registros de auditoría
     * 2. Obtener lista de usuarios para filtros
     * 3. Renderizar vista con datos
     */
    public function index(): void
    {
        // Obtener acciones únicas para filtro
        $actions = $this->getUniqueActions();

        // KPIs del ciclo de vida (header cards)
        $kpis = [];
        try {
            $kpis = $this->auditModel->getKPIs();
        } catch (\Throwable $e) {
            // Si la tabla historico aún no existe, degradar gracefully
            error_log('[SGP-BITACORA] getKPIs() fallback: ' . $e->getMessage());
            $db = $this->auditModel->getDb();
            $db->query('SELECT COUNT(*) AS total_activos, SUM(DATE(created_at)=CURDATE()) AS hoy, SUM(created_at>=DATE_SUB(NOW(),INTERVAL 7 DAY)) AS semana FROM bitacora');
            $row = $db->single();
            $kpis = [
                'total_activos'   => (int)($row->total_activos ?? 0),
                'hoy'             => (int)($row->hoy           ?? 0),
                'semana'          => (int)($row->semana        ?? 0),
                'ultima_purga'    => null,
                'total_historico' => 0,
            ];
        }

        // Datos para la vista
        $data = [
            'title'      => 'Bitácora de Auditoría',
            'actions'    => $actions,
            'total_logs' => $kpis['total_activos'],
            'kpis'       => $kpis,
        ];

        $this->view('bitacora/index', $data);
    }

    /**
     * API AJAX - Filtrar Registros
     * 
     * PROPÓSITO:
     * Endpoint para filtrado dinámico de logs sin recargar página.
     * 
     * PARÁMETROS POST:
     * - usuario_id: ID del usuario (opcional)
     * - accion: Tipo de acción (opcional)
     * - fecha_desde: Fecha inicio (opcional)
     * - fecha_hasta: Fecha fin (opcional)
     * 
     * RETORNA: JSON con registros filtrados
     */
    public function filter(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }
        
        try {
            // Obtener parámetros de filtro
            $userId = Validator::post('usuario_id') ?: null;
            $action = Validator::post('accion') ?: null;
            $dateFrom = Validator::post('fecha_desde') ?: null;
            $dateTo = Validator::post('fecha_hasta') ?: null;
            
            // Construir query dinámica
            $query = "
                SELECT 
                    b.id,
                    b.usuario_id,
                    b.accion,
                    b.tabla_afectada,
                    b.registro_id,
                    b.ip_address,
                    b.user_agent,
                    b.detalles,
                    b.created_at,
                    u.correo as usuario_email,
                    CONCAT(COALESCE(dp.nombres, ''), ' ', COALESCE(dp.apellidos, '')) as usuario_nombre
                FROM bitacora b
                LEFT JOIN usuarios u ON b.usuario_id = u.id
                LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
                WHERE 1=1
            ";
            
            $params = [];
            
            // Filtro por usuario
            if ($userId) {
                $query .= " AND b.usuario_id = :usuario_id";
                $params[':usuario_id'] = $userId;
            }
            
            // Filtro por acción
            if ($action) {
                $query .= " AND b.accion = :accion";
                $params[':accion'] = $action;
            }
            
            // Filtro por fecha desde
            if ($dateFrom) {
                $query .= " AND DATE(b.created_at) >= :fecha_desde";
                $params[':fecha_desde'] = $dateFrom;
            }
            
            // Filtro por fecha hasta
            if ($dateTo) {
                $query .= " AND DATE(b.created_at) <= :fecha_hasta";
                $params[':fecha_hasta'] = $dateTo;
            }
            
            $query .= " ORDER BY b.created_at DESC LIMIT 500";
            
            // Ejecutar query
            $db = $this->auditModel->getDb();
            $db->query($query);
            
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            
            $results = $db->resultSet();
            
            echo json_encode([
                'success' => true,
                'data' => $results,
                'count' => count($results)
            ]);
            
        } catch (Exception $e) {
            error_log("BitacoraController::filter() Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al filtrar registros'
            ]);
        }
    }

    /**
     * API - Grid.js Server-Side Pagination
     * Devuelve el JSON requerido por Grid.js para Server-Side (array data y total rows)
     */
    public function apiGrid(): void
    {
        // Limpiar cualquier buffer previo (HTML residual del framework)
        if (ob_get_level()) { ob_clean(); }
        header('Content-Type: application/json');
        
        try {
            // Grid.js envía limit y offset via GET
            $limit = isset($_GET['limit']) ? (int)$_GET['limit'] : 10;
            $offset = isset($_GET['offset']) ? (int)$_GET['offset'] : 0;
            
            // Filtros
            $action   = $_GET['accion']      ?? null;
            $dateFrom = $_GET['fecha_desde'] ?? null;
            $dateTo   = $_GET['fecha_hasta'] ?? null;
            $q        = trim($_GET['q']      ?? '');

            $query = "
                SELECT
                    b.id,
                    b.usuario_id,
                    b.accion,
                    b.tabla_afectada,
                    b.ip_address,
                    b.detalles,
                    b.created_at,
                    u.correo as usuario_email,
                    r.nombre as rol_nombre,
                    CONCAT(COALESCE(dp.nombres, ''), ' ', COALESCE(dp.apellidos, '')) as usuario_nombre
                FROM bitacora b
                LEFT JOIN usuarios u ON b.usuario_id = u.id
                LEFT JOIN roles r ON u.rol_id = r.id
                LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
                WHERE 1=1
            ";

            $countQuery = "
                SELECT COUNT(*) as total
                FROM bitacora b
                LEFT JOIN usuarios u ON b.usuario_id = u.id
                LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
                WHERE 1=1
            ";

            $params = [];

            if ($action) {
                $query .= " AND b.accion = :accion";
                $countQuery .= " AND b.accion = :accion";
                $params[':accion'] = $action;
            }

            if ($dateFrom) {
                $query .= " AND DATE(b.created_at) >= :fecha_desde";
                $countQuery .= " AND DATE(b.created_at) >= :fecha_desde";
                $params[':fecha_desde'] = $dateFrom;
            }

            if ($dateTo) {
                $query .= " AND DATE(b.created_at) <= :fecha_hasta";
                $countQuery .= " AND DATE(b.created_at) <= :fecha_hasta";
                $params[':fecha_hasta'] = $dateTo;
            }

            if ($q !== '') {
                // [FIX-M2] Escapar wildcards LIKE ('%', '_') para prevenir full-table scans por DoS
                $like = '%' . str_replace(['\\', '%', '_'], ['\\\\', '\\%', '\\_'], $q) . '%';
                $query .= " AND (b.ip_address LIKE :q OR u.correo LIKE :q2 OR b.tabla_afectada LIKE :q3
                                 OR CONCAT(COALESCE(dp.nombres,''),' ',COALESCE(dp.apellidos,'')) LIKE :q4)";
                $countQuery .= " AND (b.ip_address LIKE :q OR u.correo LIKE :q2 OR b.tabla_afectada LIKE :q3
                                      OR CONCAT(COALESCE(dp.nombres,''),' ',COALESCE(dp.apellidos,'')) LIKE :q4)";
                $params[':q']  = $like;
                $params[':q2'] = $like;
                $params[':q3'] = $like;
                $params[':q4'] = $like;
            }

            
            $query .= " ORDER BY b.created_at DESC LIMIT :limit OFFSET :offset";
            
            $db = $this->auditModel->getDb();
            
            // Obtener count total
            $db->query($countQuery);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            $totalRow = $db->single();
            $total = $totalRow->total ?? 0;
            
            // Obtener filas
            $db->query($query);
            foreach ($params as $key => $value) {
                $db->bind($key, $value);
            }
            // Bind variables de paginación (deben ser enteros)
            $db->bind(':limit', $limit, PDO::PARAM_INT);
            $db->bind(':offset', $offset, PDO::PARAM_INT);
            
            $results = $db->resultSet();
            
            echo json_encode([
                'data' => $results,
                'total' => (int)$total
            ]);
            exit;
            
        } catch (Exception $e) {
            error_log("BitacoraController::apiGrid() Error: " . $e->getMessage());
            echo json_encode([
                'data'  => [],
                'total' => 0,
                'error' => 'Error al cargar registros'
            ]);
            exit;
        }
    }

    /**
     * Exportar PDF Individual de un Registro de Auditoría
     * 
     * PROPÓSITO:
     * Genera una página HTML imprimible con el detalle de UN registro de auditoría.
     * Se abre en pestaña nueva y el navegador muestra el diálogo de impresión.
     * 
     * @param int $id ID del registro
     */
    public function exportPdfRow(?int $id = null): void
    {
        if (!$id || !is_numeric($id)) {
            http_response_code(400);
            exit('ID de registro inválido.');
        }

        // Usar la instancia de DB del modelo (igual que apiGrid)
        $db = $this->auditModel->getDb();

        $db->query(
            "SELECT b.*,
                    u.correo AS usuario_email,
                    CONCAT(COALESCE(dp.nombres, 'Sistema'), ' ', COALESCE(dp.apellidos, '')) AS usuario_nombre
             FROM bitacora b
             LEFT JOIN usuarios u ON b.usuario_id = u.id
             LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
             WHERE b.id = :id"
        );
        $db->bind(':id', (int)$id);
        $log = $db->single();

        if (!$log) {
            http_response_code(404);
            exit('Registro no encontrado.');
        }

        $accion        = htmlspecialchars($log->accion ?? '—');
        $usuario       = htmlspecialchars($log->usuario_nombre ?? 'Sistema');
        $email         = htmlspecialchars($log->usuario_email ?? 'N/A');
        $tabla         = htmlspecialchars($log->tabla_afectada ?? '—');
        $ip            = htmlspecialchars($log->ip_address ?? '—');
        $inicial       = strtoupper(substr($usuario, 0, 1));
        $fecha_raw     = $log->created_at ?? '';
        $fecha         = $fecha_raw ? date('d/m/Y H:i:s', strtotime($fecha_raw)) : '—';
        $registro_id   = htmlspecialchars($log->registro_id ?? '—');

        // Traducción de acciones a español
        $acciones_es = [
            'LOGIN'                     => 'Inicio de Sesión',
            'LOGOUT'                    => 'Cierre de Sesión',
            'RESET_PASSWORD'            => 'Contraseña Restablecida',
            'RESET_PIN'                 => 'PIN de Asistencia Restablecido',
            'UPDATE_SECURITY_QUESTIONS' => 'Preguntas de Seguridad Actualizadas',
            'CREATE_USER'               => 'Usuario Creado en el Sistema',
            'UPDATE_USER'               => 'Datos de Usuario Modificados',
            'DELETE_USER'               => 'Usuario Eliminado del Sistema',
            'TOGGLE_USER_STATUS'        => 'Estado de Cuenta Alterado',
            'UPDATE_PROFILE'            => 'Perfil de Usuario Actualizado',
            'CREATE_PASANTE'            => 'Pasante Registrado',
            'UPDATE_PASANTE'            => 'Datos de Pasante Modificados',
            'DELETE_PASANTE'            => 'Pasante Eliminado',
            'CHANGE_PASANTE_STATUS'     => 'Estado de Pasantía Actualizado',
            'MARCAR_ASISTENCIA_KIOSCO'  => 'Asistencia Marcada desde Kiosco',
            'CREATE_EVALUACION'         => 'Evaluación Creada',
            'UPDATE_EVALUACION'         => 'Evaluación Modificada',
            'DELETE_EVALUACION'         => 'Evaluación Eliminada',
            'PERMISO_MODIFICADO'        => 'Permiso de Acceso Modificado',
            'PERMISOS_RESET'            => 'Permisos Restablecidos al Rol por Defecto',
            'AUDIT_PURGE'               => 'Mantenimiento de Bitácora Ejecutado',
            'EXPORT_CSV'                => 'Exportación de Datos CSV',
            'UPDATE_CONFIG'             => 'Configuración del Sistema Actualizada',
            'REGISTRAR_ASISTENCIA_MANUAL'  => 'Asistencia Registrada Manualmente',
            'ACTUALIZAR_ASISTENCIA_MANUAL' => 'Asistencia Actualizada Manualmente',
            'REGISTRAR_ASISTENCIA_MASIVA'  => 'Asistencias Marcadas de Forma Masiva',
            'ANULAR_ASISTENCIA'            => 'Asistencia Anulada',
            'CREATE_EXAMEN'             => 'Examen Rápido Creado',
            'TOGGLE_EXAMEN'             => 'Estado de Examen Cambiado',
            'DELETE_EXAMEN'             => 'Examen Eliminado',
            'ENVIO_EXAMEN'              => 'Examen Enviado por Pasante',
            'REVISAR_EXAMEN'            => 'Examen Revisado por Tutor/Admin',
            'EDITAR_PUNTOS_PREGUNTA'    => 'Puntos de Pregunta Modificados',
            'DELETE_INTENTO'            => 'Intento de Examen Eliminado',
        ];
        $accion_es = $acciones_es[strtoupper($log->accion ?? '')] ?? htmlspecialchars($log->accion ?? '—');

        $tablas_es = [
            'usuarios'        => 'Usuarios del Sistema',
            'datos_personales'=> 'Perfil de Usuario',
            'pasantes'        => 'Pasantes',
            'datos_pasante'   => 'Datos de Pasante',
            'bitacora'        => 'Bitácora de Auditoría',
            'evaluaciones'    => 'Evaluaciones',
            'asistencias'     => 'Asistencias',
            'configuracion'   => 'Configuración del Sistema',
            'asignaciones'    => 'Asignaciones',
            'usuario_permisos'=> 'Permisos de Usuario',
        ];
        $tabla_es = $tablas_es[strtolower($log->tabla_afectada ?? '')] ?? htmlspecialchars($log->tabla_afectada ?? '—');

        // Detalles legibles (no JSON crudo)
        $detalles_rows = '';
        $labels_map = [
            'pasante'        => 'Pasante',
            'cedula'         => 'Cédula',
            'hora'           => 'Hora de Registro',
            'retardo'        => 'Llegada con Retardo',
            'metodo'         => 'Método de Marcado',
            'clave'          => 'Clave del Permiso',
            'habilitado'     => 'Estado del Permiso',
            'usuario'        => 'Usuario Afectado',
            'email'          => 'Correo Electrónico',
            'campo'          => 'Campo Modificado',
            'valor_anterior' => 'Valor Anterior',
            'valor_nuevo'    => 'Valor Nuevo',
            'motivo'         => 'Motivo',
        ];
        if (!empty($log->detalles)) {
            $detalles_obj = json_decode($log->detalles, true);
            if (is_array($detalles_obj)) {
                foreach ($detalles_obj as $key => $val) {
                    $label = $labels_map[$key] ?? ucwords(str_replace('_', ' ', $key));
                    if (is_bool($val)) $val = $val ? 'Sí' : 'No';
                    elseif ($val === null || $val === '') $val = '—';
                    $val_safe = htmlspecialchars((string)$val);
                    $detalles_rows .= "
                    <tr>
                        <td style=\"width:35%;font-size:0.78rem;font-weight:700;color:#475569;text-transform:uppercase;letter-spacing:0.5px;padding:10px 14px;border-bottom:1px solid #f1f5f9;background:#f8fafc;\">{$label}</td>
                        <td style=\"font-size:0.88rem;color:#1e293b;font-weight:500;padding:10px 14px;border-bottom:1px solid #f1f5f9;\">{$val_safe}</td>
                    </tr>";
                }
            }
        }

        $fecha_generacion = date('d/m/Y H:i:s');
        $doc_num = str_pad($id, 8, '0', STR_PAD_LEFT);

        // Sección: Detalles de la Operación
        $detalles_html = $detalles_rows
            ? "<table class='details-table'>
                <thead><tr><th>Campo</th><th>Valor</th></tr></thead>
                <tbody>{$detalles_rows}</tbody>
               </table>"
            : "<p class='no-details'>Esta acción no contiene detalles adicionales registrados.</p>";

        // Generar PDF con DomPDF
        require_once '../app/lib/PdfGenerator.php';
        $pdfGen = new PdfGenerator();

        $html = <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Comprobante de Auditoria {$doc_num}</title>
    <style>
        @page { margin: 1.5cm; size: A4 portrait; }
        * { box-sizing: border-box; }
        body { font-family: Arial, Helvetica, sans-serif; background: white; color: #1e293b; font-size: 11px; margin: 0; padding: 0; }

        .doc-header { background: #1e3a8a; padding: 18px 28px 14px; color: white; }
        .doc-institution { font-size: 7.5px; text-transform: uppercase; letter-spacing: 1px; color: #bfdbfe; margin-bottom: 3px; }
        .doc-title { font-size: 16px; font-weight: 700; color: white; line-height: 1.2; }
        .doc-subtitle { font-size: 8.5px; color: #93c5fd; margin-top: 2px; }
        .doc-num-box { background: #1d4ed8; border: 1px solid #3b82f6; padding: 7px 14px; text-align: center; }
        .doc-num-label { font-size: 7px; text-transform: uppercase; letter-spacing: 1px; color: #bfdbfe; }
        .doc-num-value { font-size: 15px; font-weight: 800; letter-spacing: 2px; color: white; }
        .doc-type-badge { background: #1d4ed8; border: 1px solid #3b82f6; padding: 3px 10px; font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: white; }

        .action-band { background: #eff6ff; border-left: 4px solid #2563eb; padding: 10px 28px; border-bottom: 1px solid #e2e8f0; }
        .action-icon-cell { width: 44px; vertical-align: middle; text-align: center; }
        .action-icon-box { width: 36px; height: 36px; background: #2563eb; text-align: center; font-size: 18px; line-height: 36px; font-weight: bold; color: white; }
        .action-text { font-size: 12px; font-weight: 700; color: #1e3a8a; }
        .action-sub { font-size: 8.5px; color: #64748b; margin-top: 2px; }

        .doc-body { padding: 18px 28px; }
        .section-title { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.8px; color: #64748b; padding-bottom: 4px; border-bottom: 1px solid #e2e8f0; margin-bottom: 10px; }

        .user-card { background: #f8fafc; border: 1px solid #e2e8f0; padding: 12px; margin-bottom: 16px; }
        .user-avatar { width: 44px; height: 44px; background: #1e3a8a; text-align: center; font-weight: 800; color: white; font-size: 18px; line-height: 44px; vertical-align: middle; }
        .user-name { font-weight: 700; font-size: 12px; color: #0f172a; }
        .user-email { font-size: 9px; color: #64748b; margin-top: 2px; }

        .meta-cell { background: #f8fafc; border: 1px solid #e2e8f0; padding: 9px 11px; vertical-align: top; }
        .meta-label { font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; color: #94a3b8; margin-bottom: 3px; }
        .meta-value { font-size: 10px; font-weight: 600; color: #1e293b; }

        .details-table { width: 100%; border-collapse: collapse; border: 1px solid #e2e8f0; }
        .details-table thead tr { background: #1e3a8a; }
        .details-table thead th { color: white; font-size: 8px; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; padding: 8px 12px; text-align: left; }
        .details-table tbody td { padding: 7px 12px; font-size: 9.5px; border-bottom: 1px solid #e2e8f0; color: #374151; }
        .details-table tbody tr:nth-child(even) td { background: #f8fafc; }
        .no-details { font-size: 10px; color: #94a3b8; font-style: italic; padding: 12px 0; }

        .doc-footer { background: #f8fafc; border-top: 2px solid #e2e8f0; padding: 12px 28px; }
        .footer-stamp { border: 2px solid #2563eb; color: #2563eb; padding: 3px 10px; font-size: 7.5px; font-weight: 700; text-transform: uppercase; letter-spacing: 1px; }
    </style>
</head>
<body>

    <!-- Encabezado institucional -->
    <div class="doc-header">
        <table style="width:100%;border-collapse:collapse;margin-bottom:10px;">
            <tr>
                <td style="vertical-align:top;">
                    <div class="doc-institution">Sistema de Gestion de Pasantias &mdash; SGP</div>
                    <div class="doc-title">Comprobante de Auditoria</div>
                    <div class="doc-subtitle">Registro oficial de actividad del sistema</div>
                </td>
                <td style="vertical-align:top;text-align:right;width:155px;">
                    <div class="doc-num-box">
                        <div class="doc-num-label">N. Documento</div>
                        <div class="doc-num-value">{$doc_num}</div>
                    </div>
                </td>
            </tr>
        </table>
        <span class="doc-type-badge">Bitacora Oficial &middot; Confidencial</span>
    </div>

    <!-- Banda de accion -->
    <div class="action-band">
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td class="action-icon-cell">
                    <div class="action-icon-box">#</div>
                </td>
                <td style="vertical-align:middle;padding-left:12px;">
                    <div class="action-text">{$accion_es}</div>
                    <div class="action-sub">Accion registrada automaticamente por el sistema de auditoria</div>
                </td>
            </tr>
        </table>
    </div>

    <div class="doc-body">

        <!-- Seccion: Responsable del Evento -->
        <div class="section-title">Responsable del Evento</div>
        <div class="user-card">
            <table style="width:100%;border-collapse:collapse;">
                <tr>
                    <td style="width:56px;vertical-align:middle;">
                        <div class="user-avatar">{$inicial}</div>
                    </td>
                    <td style="vertical-align:middle;padding-left:12px;">
                        <div class="user-name">{$usuario}</div>
                        <div class="user-email">{$email}</div>
                    </td>
                </tr>
            </table>
        </div>

        <!-- Seccion: Datos del Registro -->
        <div class="section-title">Datos del Registro</div>
        <table style="width:100%;border-collapse:separate;border-spacing:7px 0;margin-bottom:16px;">
            <tr>
                <td class="meta-cell" style="width:33%;">
                    <div class="meta-label">Modulo Afectado</div>
                    <div class="meta-value">{$tabla_es}</div>
                </td>
                <td class="meta-cell" style="width:33%;">
                    <div class="meta-label">Direccion IP</div>
                    <div class="meta-value">{$ip}</div>
                </td>
                <td class="meta-cell" style="width:34%;">
                    <div class="meta-label">Fecha y Hora</div>
                    <div class="meta-value">{$fecha}</div>
                </td>
            </tr>
        </table>

        <!-- ID del registro afectado -->
        <div style="margin-bottom:16px;">
            <span style="font-size:8px;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">ID del Registro Afectado:</span>
            &nbsp;&nbsp;<span style="background:#eff6ff;color:#2563eb;padding:3px 10px;font-size:10px;font-weight:700;border:1px solid #bfdbfe;">{$registro_id}</span>
        </div>

        <!-- Seccion: Detalles de la Operacion -->
        <div class="section-title">Detalles de la Operacion</div>
        {$detalles_html}
    </div>

    <!-- Pie de pagina institucional -->
    <div class="doc-footer">
        <table style="width:100%;border-collapse:collapse;">
            <tr>
                <td style="vertical-align:middle;">
                    <div style="font-size:8.5px;color:#64748b;"><strong>SGP &mdash; Sistema de Gestion de Pasantias</strong><br>Documento generado el {$fecha_generacion}</div>
                </td>
                <td style="text-align:right;vertical-align:middle;">
                    <div style="font-size:8.5px;color:#94a3b8;">Registro N. {$doc_num}<br><span class="footer-stamp">Documento Oficial</span></div>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
HTML;
        $pdfGen->renderDomPdf($html, "Comprobante_Auditoria_{$doc_num}", false);
    }


    /**
     * Exportar Logs a CSV
     * 
     * PROPÓSITO:
     * Generar archivo CSV con registros de auditoría para análisis externo.
     * 
     * PARÁMETROS GET:
     * - usuario_id, accion, fecha_desde, fecha_hasta (opcionales)
     */
    public function export(): void
    {
        try {
            // Obtener parámetros de filtro desde GET
            $action   = $_GET['accion']       ?? null;
            $dateFrom = $_GET['fecha_desde']  ?? null;
            $dateTo   = $_GET['fecha_hasta']  ?? null;

            // Construir query con filtros aplicados (fix bug: antes ignoraba los filtros)
            $query  = "
                SELECT b.id, b.accion, b.tabla_afectada, b.registro_id,
                       b.ip_address, b.user_agent, b.detalles, b.created_at,
                       u.correo AS usuario_email,
                       CONCAT(COALESCE(dp.nombres,''),' ',COALESCE(dp.apellidos,'')) AS usuario_nombre
                FROM bitacora b
                LEFT JOIN usuarios u ON b.usuario_id = u.id
                LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
                WHERE 1=1
            ";
            $params = [];

            if ($action) {
                $query .= ' AND b.accion = :accion';
                $params[':accion'] = $action;
            }
            if ($dateFrom) {
                $query .= ' AND DATE(b.created_at) >= :fecha_desde';
                $params[':fecha_desde'] = $dateFrom;
            }
            if ($dateTo) {
                $query .= ' AND DATE(b.created_at) <= :fecha_hasta';
                $params[':fecha_hasta'] = $dateTo;
            }
            $query .= ' ORDER BY b.created_at DESC';

            $db = $this->auditModel->getDb();
            $db->query($query);
            foreach ($params as $k => $v) $db->bind($k, $v);
            $logs = $db->resultSet();

            // Generar CSV
            $filename = 'bitacora_activa_' . date('Y-m-d_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"{$filename}\"");

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF)); // BOM UTF-8

            fputcsv($output, ['ID','Usuario','Email','Acción','Tabla','Registro ID','IP','Navegador','Detalles','Fecha']);

            foreach ($logs as $log) {
                $log = (array) $log;
                fputcsv($output, [
                    $log['id'],
                    $log['usuario_nombre'] ?? 'Sistema',
                    $log['usuario_email']  ?? 'N/A',
                    $log['accion'],
                    $log['tabla_afectada'] ?? 'N/A',
                    $log['registro_id']    ?? 'N/A',
                    $log['ip_address'],
                    substr($log['user_agent'] ?? 'N/A', 0, 80),
                    $log['detalles']       ?? 'N/A',
                    $log['created_at'],
                ]);
            }

            fclose($output);
            exit;

        } catch (Exception $e) {
            error_log("BitacoraController::export() Error: " . $e->getMessage());
            Session::setFlash('error', 'Error al exportar registros');
            $this->redirect('/bitacora');
        }
    }

    // ============================================================
    // LIFECYCLE: Mantenimiento y Archivado
    // ============================================================

    /**
     * AJAX — Ejecutar ciclo de purga/archivado
     *
     * POST: dias_criticos (int), dias_operacion (int)
     * Responde JSON: { success, archivados, purgados, message }
     */
    public function mantenimiento(): void
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            return;
        }

        try {
            $diasCriticos  = max(30,  (int)($_POST['dias_criticos']  ?? 365));
            $diasOperacion = max(7,   (int)($_POST['dias_operacion'] ?? 90));
            $adminId       = (int)(Session::get('user_id') ?? 0);

            $result = $this->auditModel->purgar($diasCriticos, $diasOperacion, $adminId);

            echo json_encode([
                'success'    => true,
                'archivados' => $result['archivados'],
                'purgados'   => $result['purgados'],
                'message'    => $result['archivados'] > 0
                    ? "Mantenimiento completado: {$result['archivados']} registros archivados y {$result['purgados']} eliminados de la tabla activa."
                    : 'No había registros que superen el período de retención configurado.',
            ]);

        } catch (\Throwable $e) {
            // [FIX-C2] Mensaje genérico al cliente — detalle solo en log
            error_log('[SGP-BITACORA] mantenimiento() Error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Error al ejecutar el mantenimiento. Intente de nuevo.']);
        }
        exit;
    }

    /**
     * API Grid.js — Histórico paginado (tabla cold)
     *
     * GET: limit, offset, accion, fecha_desde, fecha_hasta
     * Responde JSON: { data: [], total: int }
     */
    public function apiGridHistorico(): void
    {
        if (ob_get_level()) ob_clean();
        header('Content-Type: application/json');

        try {
            $limit    = max(1, (int)($_GET['limit']       ?? 10));
            $offset   = max(0, (int)($_GET['offset']      ?? 0));
            $accion   = $_GET['accion']      ?? null;
            $dateFrom = $_GET['fecha_desde'] ?? null;
            $dateTo   = $_GET['fecha_hasta'] ?? null;

            $result = $this->auditModel->getHistorico($limit, $offset, $accion ?: null, $dateFrom ?: null, $dateTo ?: null);

            echo json_encode(['data' => $result['data'], 'total' => $result['total']]);

        } catch (\Throwable $e) {
            error_log('[SGP-BITACORA] apiGridHistorico() Error: ' . $e->getMessage());
            echo json_encode(['data' => [], 'total' => 0]);
        }
        exit;
    }

    /**
     * Exportar histórico (tabla cold) a CSV con filtros
     *
     * GET: accion, fecha_desde, fecha_hasta
     */
    public function exportHistorico(): void
    {
        try {
            $accion   = $_GET['accion']      ?? null;
            $dateFrom = $_GET['fecha_desde'] ?? null;
            $dateTo   = $_GET['fecha_hasta'] ?? null;

            $result = $this->auditModel->getHistorico(10000, 0, $accion ?: null, $dateFrom ?: null, $dateTo ?: null);
            $logs   = $result['data'];

            $filename = 'bitacora_historico_' . date('Y-m-d_His') . '.csv';
            header('Content-Type: text/csv; charset=utf-8');
            header("Content-Disposition: attachment; filename=\"{$filename}\"");

            $output = fopen('php://output', 'w');
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

            fputcsv($output, ['ID Orig.','Usuario','Email','Acción','Tabla','IP','Detalles','Fecha Original','Archivado En']);

            foreach ($logs as $log) {
                $log = (array) $log;
                fputcsv($output, [
                    $log['bitacora_id']    ?? '—',
                    $log['usuario_nombre'] ?? 'Sistema',
                    $log['usuario_email']  ?? 'N/A',
                    $log['accion'],
                    $log['tabla_afectada'] ?? 'N/A',
                    $log['ip_address'],
                    $log['detalles']       ?? 'N/A',
                    $log['created_at'],
                    $log['archivado_at'],
                ]);
            }

            fclose($output);
            exit;

        } catch (\Throwable $e) {
            error_log('[SGP-BITACORA] exportHistorico() Error: ' . $e->getMessage());
            Session::setFlash('error', 'Error al exportar el histórico');
            $this->redirect('/bitacora');
        }
    }

    /**
     * API - Estadísticas de Auditoría
     * 
     * PROPÓSITO:
     * Proveer datos para gráficas en dashboard de administración.
     * 
     * RETORNA: JSON con estadísticas
     */
    public function stats(): void
    {
        header('Content-Type: application/json');
        
        try {
            $stats = $this->auditModel->getActionStats();
            
            echo json_encode([
                'success' => true,
                'data' => $stats
            ]);
            
        } catch (Exception $e) {
            error_log("BitacoraController::stats() Error: " . $e->getMessage());
            echo json_encode([
                'success' => false,
                'message' => 'Error al obtener estadísticas'
            ]);
        }
    }

    /**
     * Obtener Acciones Únicas
     * 
     * PROPÓSITO:
     * Extraer lista de acciones únicas para el filtro dropdown.
     * 
     * @return array Lista de acciones
     */
    private function getUniqueActions(): array
    {
        $db = $this->auditModel->getDb();
        $db->query("SELECT DISTINCT accion FROM bitacora ORDER BY accion ASC");
        $results = $db->resultSet();
        
        return array_column($results, 'accion');
    }
}
