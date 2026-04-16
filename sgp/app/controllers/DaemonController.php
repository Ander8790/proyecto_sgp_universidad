<?php
/**
 * DaemonController — Procesos automáticos del sistema SGP
 * =========================================================
 * Expone endpoints de administración para ejecutar procesos
 * de mantenimiento de datos. Todos los métodos requieren:
 *   - Sesión activa con rol Administrador (rol_id = 1)
 *   - Token CSRF válido (peticiones POST)
 */
class DaemonController extends Controller
{
    public function __construct()
    {
        // Solo administradores
        AuthMiddleware::verificar();
        RoleMiddleware::verificar([1]);

        require_once APPROOT . '/helpers/AutoFillService.php';
        require_once APPROOT . '/models/AuditModel.php';
    }

    // ── GET /daemon ─────────────────────────────────────────────────────────
    public function index(): void
    {
        $this->view('daemon/index', [], true);
    }

    // ── POST /daemon/autoFill ────────────────────────────────────────────────
    /**
     * Ejecuta el Auto-Fill de asistencias.
     * Modo dry_run=1 → solo simula sin insertar.
     *
     * Response JSON: { success, stats }
     */
    public function autoFill(): void
    {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        // Validar CSRF
        $token = $_POST['csrf_token'] ?? $_SERVER['HTTP_X_CSRF_TOKEN'] ?? '';
        if (!Session::validateCsrfToken($token)) {
            http_response_code(403);
            echo json_encode(['success' => false, 'message' => 'Token CSRF inválido']);
            exit;
        }

        try {
            $dryRun = isset($_POST['dry_run']) && $_POST['dry_run'] === '1';
            $db     = Database::getInstance();
            $stats  = AutoFillService::ejecutar($db, $dryRun);

            AuditModel::log(
                $dryRun ? 'DAEMON_AUTOFILL_PREVIEW' : 'DAEMON_AUTOFILL_EJECUTADO',
                'asistencias',
                null,
                json_encode([
                    'rellenos' => $stats['dias_rellenos'],
                    'pasantes' => $stats['pasantes_afectados'],
                    'dry_run'  => $dryRun,
                ])
            );

            echo json_encode([
                'success' => true,
                'dry_run' => $dryRun,
                'stats'   => [
                    'revisados'          => $stats['revisados'],
                    'pasantes_afectados' => $stats['pasantes_afectados'],
                    'dias_rellenos'      => $stats['dias_rellenos'],
                    'dias_omitidos'      => $stats['dias_omitidos'],
                    'errores'            => $stats['errores'],
                ],
                'detalle' => $stats['detalle'],
            ]);

        } catch (\Throwable $e) {
            error_log('[SGP-DAEMON] autoFill error: ' . $e->getMessage());
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => 'Error interno: ' . $e->getMessage()]);
        }

        exit;
    }

    // ── GET /daemon/autoFillPreview ──────────────────────────────────────────
    /**
     * Vista previa vía GET: muestra cuántos días se rellenarían sin ejecutar.
     * Devuelve JSON (consumido desde la UI de configuración).
     */
    public function autoFillPreview(): void
    {
        header('Content-Type: application/json');

        try {
            $db    = Database::getInstance();
            $stats = AutoFillService::ejecutar($db, true); // dry_run = true

            echo json_encode([
                'success'            => true,
                'pasantes_afectados' => $stats['pasantes_afectados'],
                'dias_a_rellenar'    => $stats['dias_rellenos'],
                'detalle'            => array_slice($stats['detalle'], 0, 10), // máx 10 en preview
            ]);

        } catch (\Throwable $e) {
            http_response_code(500);
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }

        exit;
    }
}
