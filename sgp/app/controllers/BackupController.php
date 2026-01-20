<?php
/**
 * BackupController - Database Backup Management
 * Admin-only access for creating, downloading, and managing database backups
 */
class BackupController extends Controller
{
    private $backupDir;

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
        
        $this->backupDir = APPROOT . '/storage/backups';
        
        // Ensure backup directory exists
        if (!is_dir($this->backupDir)) {
            mkdir($this->backupDir, 0755, true);
        }
    }

    /**
     * List all backups
     */
    public function index()
    {
        $backups = $this->getBackupList();
        
        $this->view('backup/index', [
            'backups' => $backups
        ]);
    }

    /**
     * Create new backup
     */
    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }

        try {
            $filename = 'backup_' . date('Y-m-d_H-i-s') . '.sql';
            $filepath = $this->backupDir . '/' . $filename;
            
            // Get database credentials from config
            $config = require APPROOT . '/config/config.php';
            $dbConfig = $config['db'];
            
            $host = $dbConfig['host'];
            $user = $dbConfig['user'];
            $pass = $dbConfig['pass'];
            $dbname = $dbConfig['name'];
            
            // Build mysqldump command for Windows (XAMPP)
            $mysqldumpPath = 'C:\\xampp\\mysql\\bin\\mysqldump.exe';
            
            // Check if mysqldump exists
            if (!file_exists($mysqldumpPath)) {
                // Try system PATH
                $mysqldumpPath = 'mysqldump';
            }
            
            // Build command - handle empty password
            if (empty($pass)) {
                $command = sprintf(
                    '%s --user=%s --host=%s %s > %s 2>&1',
                    $mysqldumpPath,
                    escapeshellarg($user),
                    escapeshellarg($host),
                    escapeshellarg($dbname),
                    escapeshellarg($filepath)
                );
            } else {
                $command = sprintf(
                    '%s --user=%s --password=%s --host=%s %s > %s 2>&1',
                    $mysqldumpPath,
                    escapeshellarg($user),
                    escapeshellarg($pass),
                    escapeshellarg($host),
                    escapeshellarg($dbname),
                    escapeshellarg($filepath)
                );
            }
            
            // Execute backup
            exec($command, $output, $returnVar);
            
            // Check if backup was successful
            if ($returnVar !== 0) {
                // Command failed
                if (file_exists($filepath)) {
                    unlink($filepath);
                }
                $errorMsg = !empty($output) ? implode("\n", $output) : 'mysqldump no está disponible o falló';
                $this->jsonResponse(false, 'Error al crear el respaldo: ' . $errorMsg);
            }
            
            if (!file_exists($filepath)) {
                $this->jsonResponse(false, 'El archivo de respaldo no se creó. Verifica que mysqldump esté instalado.');
            }
            
            if (filesize($filepath) === 0) {
                unlink($filepath);
                $this->jsonResponse(false, 'El respaldo está vacío. Error en la exportación.');
            }
            
            $this->jsonResponse(true, 'Respaldo creado exitosamente: ' . $filename);
            
        } catch (Exception $e) {
            $this->jsonResponse(false, 'Error: ' . $e->getMessage());
        }
    }

    /**
     * Download backup file
     */
    public function download($filename = null)
    {
        if (!$filename) {
            Session::setFlash('error', 'Archivo no especificado');
            $this->redirect('/backup');
            return;
        }
        
        // Validate filename (prevent directory traversal)
        $filename = basename($filename);
        if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
            Session::setFlash('error', 'Nombre de archivo inválido');
            $this->redirect('/backup');
            return;
        }
        
        $filepath = $this->backupDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            Session::setFlash('error', 'Archivo no encontrado');
            $this->redirect('/backup');
            return;
        }
        
        // Send file for download
        header('Content-Type: application/octet-stream');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Content-Length: ' . filesize($filepath));
        header('Cache-Control: no-cache');
        
        readfile($filepath);
        exit;
    }

    /**
     * Delete backup file
     */
    public function delete($filename = null)
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->jsonResponse(false, 'Método no permitido');
        }
        
        if (!$filename) {
            $this->jsonResponse(false, 'Archivo no especificado');
        }
        
        // Validate filename
        $filename = basename($filename);
        if (!preg_match('/^backup_\d{4}-\d{2}-\d{2}_\d{2}-\d{2}-\d{2}\.sql$/', $filename)) {
            $this->jsonResponse(false, 'Nombre de archivo inválido');
        }
        
        $filepath = $this->backupDir . '/' . $filename;
        
        if (!file_exists($filepath)) {
            $this->jsonResponse(false, 'Archivo no encontrado');
        }
        
        if (unlink($filepath)) {
            $this->jsonResponse(true, 'Respaldo eliminado exitosamente');
        } else {
            $this->jsonResponse(false, 'Error al eliminar el respaldo');
        }
    }

    /**
     * Get list of backup files
     */
    private function getBackupList()
    {
        $files = glob($this->backupDir . '/backup_*.sql');
        $backups = [];
        
        if ($files) {
            // Sort by modification time (newest first)
            usort($files, function($a, $b) {
                return filemtime($b) - filemtime($a);
            });
            
            foreach ($files as $file) {
                $backups[] = [
                    'filename' => basename($file),
                    'size' => $this->formatBytes(filesize($file)),
                    'date' => date('d/m/Y H:i:s', filemtime($file)),
                    'timestamp' => filemtime($file)
                ];
            }
        }
        
        return $backups;
    }

    /**
     * Format bytes to human readable
     */
    private function formatBytes($bytes, $precision = 2)
    {
        $units = ['B', 'KB', 'MB', 'GB'];
        $bytes = max($bytes, 0);
        $pow = floor(($bytes ? log($bytes) : 0) / log(1024));
        $pow = min($pow, count($units) - 1);
        $bytes /= pow(1024, $pow);
        
        return round($bytes, $precision) . ' ' . $units[$pow];
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
