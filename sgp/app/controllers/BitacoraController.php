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
        RoleMiddleware::authorize([1]); // Validación estricta solo para administradores
        
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
        // Obtener registros de auditoría (últimos 500)
        $logs = $this->auditModel->getAll(500);
        
        // Obtener acciones únicas para filtro
        $actions = $this->getUniqueActions();
        
        // Datos para la vista
        $data = [
            'title' => 'Bitácora de Auditoría',
            'logs' => $logs,
            'actions' => $actions,
            'total_logs' => count($logs)
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
            $action = $_GET['accion'] ?? null;
            $dateFrom = $_GET['fecha_desde'] ?? null;
            $dateTo = $_GET['fecha_hasta'] ?? null;
            
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
    public function exportPdfRow($id = null): void
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

        $detalles_html = '';
        if (!empty($log->detalles)) {
            $json_pretty   = json_encode(json_decode($log->detalles), JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE);
            $detalles_html = '<pre style="background:#0f172a;color:#e2e8f0;padding:16px;border-radius:10px;font-size:0.78rem;line-height:1.6;white-space:pre-wrap;word-wrap:break-word;">'
                           . htmlspecialchars($json_pretty)
                           . '</pre>';
        }

        // Salida HTML imprimible
        header('Content-Type: text/html; charset=UTF-8');
        echo <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Auditoría #{$id} — {$accion}</title>
    <link rel="stylesheet" href="https://fonts.googleapis.com/css2?family=Inter:wght@400;600;700;800&display=swap">
    <style>
        * { box-sizing: border-box; margin: 0; padding: 0; }
        body { font-family: 'Inter', sans-serif; background: #f8fafc; color: #1e293b; }
        .sheet { max-width: 720px; margin: 0 auto; background: white; border-radius: 16px; overflow: hidden; box-shadow: 0 4px 24px rgba(0,0,0,0.08); }
        .header { background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); padding: 28px 36px; color: white; display: flex; align-items: center; gap: 16px; }
        .header-icon { width: 48px; height: 48px; background: rgba(255,255,255,0.15); border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
        .header h1 { font-size: 1.2rem; font-weight: 700; }
        .header p  { font-size: 0.82rem; opacity: 0.7; margin-top: 2px; }
        .body { padding: 28px 36px; }
        .user-card { display: flex; align-items: center; gap: 16px; background: #f8fafc; border-radius: 12px; padding: 16px; margin-bottom: 20px; }
        .avatar { width: 48px; height: 48px; border-radius: 50%; background: linear-gradient(135deg, #1e3a8a, #3b82f6); display: flex; align-items: center; justify-content: center; font-weight: 800; color: white; font-size: 1.1rem; }
        .badge { padding: 5px 14px; border-radius: 20px; font-size: 0.72rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; background: #eff6ff; color: #2563eb; border: 1px solid rgba(37,99,235,0.2); }
        .grid { display: grid; grid-template-columns: 1fr 1fr 1fr; gap: 12px; margin-bottom: 20px; }
        .cell { background: #f8fafc; border-radius: 10px; padding: 12px 14px; }
        .cell-label { color: #94a3b8; font-size: 0.7rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.6px; margin-bottom: 4px; }
        .cell-val   { font-weight: 600; font-size: 0.85rem; font-family: monospace; }
        .section-title { font-size: 0.75rem; font-weight: 700; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 8px; }
        .footer { text-align: center; color: #94a3b8; font-size: 0.72rem; padding: 16px; border-top: 1px solid #f1f5f9; }
        @media print { body { background: white; } .sheet { box-shadow: none; border-radius: 0; } @page { margin: 1cm; } }
    </style>
</head>
<body>
<div class="sheet">
    <div class="header">
        <div class="header-icon">🛡️</div>
        <div>
            <h1>Registro de Auditoría #{$id}</h1>
            <p>Sistema de Gestión de Pasantías — SGP</p>
        </div>
    </div>
    <div class="body">
        <div class="user-card">
            <div class="avatar">{$inicial}</div>
            <div style="flex:1;">
                <div style="font-weight:700;font-size:0.95rem;">{$usuario}</div>
                <div style="color:#94a3b8;font-size:0.8rem;margin-top:2px;">{$email}</div>
            </div>
            <span class="badge">{$accion}</span>
        </div>
        <div class="grid">
            <div class="cell"><div class="cell-label">📋 Tabla</div><div class="cell-val">{$tabla}</div></div>
            <div class="cell"><div class="cell-label">🌐 IP</div><div class="cell-val">{$ip}</div></div>
            <div class="cell"><div class="cell-label">📅 Fecha</div><div class="cell-val" style="font-family:inherit;">{$fecha}</div></div>
        </div>
        <div style="margin-bottom:14px;">
            <span style="font-size:0.78rem;font-weight:700;color:#64748b;">Registro ID:</span>
            <code style="background:#eff6ff;color:#2563eb;padding:2px 10px;border-radius:6px;font-size:0.78rem;font-weight:700;margin-left:6px;">{$registro_id}</code>
        </div>
        {$detalles_html}
    </div>
    <div class="footer">Generado el {$fecha} · SGP Sistema de Gestión de Pasantías</div>
</div>
<script>window.onload = function() { window.print(); }</script>
</body>
</html>
HTML;
        exit;
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
            // Obtener parámetros de filtro (igual que filter())
            $userId = $_GET['usuario_id'] ?? null;
            $action = $_GET['accion'] ?? null;
            $dateFrom = $_GET['fecha_desde'] ?? null;
            $dateTo = $_GET['fecha_hasta'] ?? null;
            
            // Obtener registros (reutilizar lógica de filter)
            // Por simplicidad, obtenemos todos
            $logs = $this->auditModel->getAll(1000);
            
            // Configurar headers para descarga CSV
            header('Content-Type: text/csv; charset=utf-8');
            header('Content-Disposition: attachment; filename="bitacora_' . date('Y-m-d_His') . '.csv"');
            
            // Crear output stream
            $output = fopen('php://output', 'w');
            
            // Escribir BOM para UTF-8
            fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));
            
            // Escribir encabezados
            fputcsv($output, [
                'ID',
                'Usuario',
                'Email',
                'Acción',
                'Tabla',
                'Registro ID',
                'IP',
                'Navegador',
                'Detalles',
                'Fecha'
            ]);
            
            // Escribir datos
            foreach ($logs as $log) {
                fputcsv($output, [
                    $log['id'],
                    $log['usuario_nombre'] ?? 'Sistema',
                    $log['usuario_email'] ?? 'N/A',
                    $log['accion'],
                    $log['tabla_afectada'] ?? 'N/A',
                    $log['registro_id'] ?? 'N/A',
                    $log['ip_address'],
                    substr($log['user_agent'] ?? 'N/A', 0, 50),
                    $log['detalles'] ?? 'N/A',
                    $log['created_at']
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
