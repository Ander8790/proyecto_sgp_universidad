<?php
/**
 * AuditModel - Modelo de Auditoría
 * 
 * PROPÓSITO EDUCATIVO:
 * Este modelo implementa un sistema de trazabilidad (Audit Log) que registra
 * todas las acciones críticas del sistema para fines de seguridad y auditoría.
 * 
 * RESPONSABILIDADES:
 * - Registrar acciones de usuarios (login, updates, deletes)
 * - Almacenar IP, user agent y detalles adicionales
 * - Proveer consultas para el panel de administración
 * 
 * SEGURIDAD:
 * - Método estático para fácil integración
 * - Captura automática de IP y user agent
 * - Almacenamiento de detalles en formato JSON
 * 
 * @author Sistema SGP
 * @version 1.0
 */

declare(strict_types=1);

class AuditModel
{
    private $db;

    /**
     * Constructor del Modelo
     * 
     * @param Database $db Instancia de conexión a la base de datos
     */
    public function __construct(Database $db)
    {
        $this->db = $db;
    }

    /**
     * Registrar Acción en Bitácora (Método Estático)
     * 
     * PROPÓSITO:
     * Método estático para registrar acciones desde cualquier parte del sistema
     * sin necesidad de instanciar el modelo.
     * 
     * FLUJO:
     * Input: $accion, $tabla (opcional), $registroId (opcional), $detalles (opcional)
     * Proceso: Captura IP, user agent, usuario actual
     * Output: Registro en tabla bitacora
     * 
     * EJEMPLOS DE USO:
     * AuditModel::log('LOGIN');
     * AuditModel::log('UPDATE_PROFILE', 'datos_personales', $userId);
     * AuditModel::log('FORMALIZE_INTERN', 'datos_pasante', $pasanteId, ['departamento' => 'IT']);
     * 
     * @param string $accion Acción realizada (LOGIN, UPDATE, DELETE, etc.)
     * @param string|null $tabla Tabla afectada (opcional)
     * @param int|null $registroId ID del registro afectado (opcional)
     * @param array|null $detalles Datos adicionales en formato array (opcional)
     * @return void
     */
    public static function log(
        string $accion, 
        ?string $tabla = null, 
        ?int $registroId = null, 
        ?array $detalles = null
    ): void {
        try {
            // Crear instancia de Database con configuración
            $config = require dirname(dirname(__DIR__)) . '/app/config/config.php';
            $db = new Database($config['db']);
            
            // Obtener datos del contexto actual
            $userId = Session::get('user_id') ?? 0; // 0 = Sistema/Anónimo
            $ip = $_SERVER['REMOTE_ADDR'] ?? 'UNKNOWN';
            $userAgent = $_SERVER['HTTP_USER_AGENT'] ?? null;
            
            // Convertir detalles a JSON si existen
            $detallesJson = $detalles ? json_encode($detalles, JSON_UNESCAPED_UNICODE) : null;
            
            // Preparar query
            $db->query("
                INSERT INTO bitacora (
                    usuario_id, 
                    accion, 
                    tabla_afectada, 
                    registro_id, 
                    ip_address, 
                    user_agent, 
                    detalles
                )
                VALUES (
                    :usuario_id, 
                    :accion, 
                    :tabla, 
                    :registro_id, 
                    :ip, 
                    :user_agent, 
                    :detalles
                )
            ");
            
            // Bind de parámetros
            $db->bind(':usuario_id', $userId);
            $db->bind(':accion', $accion);
            $db->bind(':tabla', $tabla);
            $db->bind(':registro_id', $registroId);
            $db->bind(':ip', $ip);
            $db->bind(':user_agent', $userAgent);
            $db->bind(':detalles', $detallesJson);
            
            // Ejecutar
            $db->execute();
            
        } catch (Exception $e) {
            // En caso de error, no interrumpir el flujo principal
            // Solo registrar en log de errores
            error_log("AuditModel::log() Error: " . $e->getMessage());
        }
    }

    /**
     * Obtener Todos los Registros de Bitácora
     * 
     * PROPÓSITO:
     * Consultar los registros de auditoría para el panel de administración.
     * 
     * FLUJO:
     * Input: $limit (cantidad de registros)
     * Proceso: JOIN con usuarios y datos_personales para obtener nombres
     * Output: Array con registros ordenados por fecha descendente
     * 
     * @param int $limit Cantidad máxima de registros a retornar
     * @return array Array de registros de bitácora
     */
    public function getAll(int $limit = 100): array
    {
        $this->db->query("
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
            ORDER BY b.created_at DESC
            LIMIT :limit
        ");
        
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    /**
     * Obtener Registros por Usuario
     * 
     * PROPÓSITO:
     * Consultar las acciones de un usuario específico.
     * 
     * @param int $userId ID del usuario
     * @param int $limit Cantidad máxima de registros
     * @return array Array de registros del usuario
     */
    public function getByUser(int $userId, int $limit = 50): array
    {
        $this->db->query("
            SELECT 
                b.*,
                u.correo as usuario_email
            FROM bitacora b
            LEFT JOIN usuarios u ON b.usuario_id = u.id
            WHERE b.usuario_id = :user_id
            ORDER BY b.created_at DESC
            LIMIT :limit
        ");
        
        $this->db->bind(':user_id', $userId);
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    /**
     * Obtener Registros por Acción
     * 
     * PROPÓSITO:
     * Filtrar registros por tipo de acción (ej: todos los LOGIN).
     * 
     * @param string $accion Tipo de acción a filtrar
     * @param int $limit Cantidad máxima de registros
     * @return array Array de registros filtrados
     */
    public function getByAction(string $accion, int $limit = 50): array
    {
        $this->db->query("
            SELECT 
                b.*,
                u.correo as usuario_email,
                CONCAT(COALESCE(dp.nombres, ''), ' ', COALESCE(dp.apellidos, '')) as usuario_nombre
            FROM bitacora b
            LEFT JOIN usuarios u ON b.usuario_id = u.id
            LEFT JOIN datos_personales dp ON u.id = dp.usuario_id
            WHERE b.accion = :accion
            ORDER BY b.created_at DESC
            LIMIT :limit
        ");
        
        $this->db->bind(':accion', $accion);
        $this->db->bind(':limit', $limit);
        
        return $this->db->resultSet();
    }

    /**
     * Obtener Estadísticas de Acciones
     * 
     * PROPÓSITO:
     * Generar resumen de acciones para dashboard de administración.
     * 
     * @return array Array con conteo de acciones por tipo
     */
    public function getActionStats(): array
    {
        $this->db->query("
            SELECT 
                accion,
                COUNT(*) as total,
                DATE(created_at) as fecha
            FROM bitacora
            WHERE created_at >= DATE_SUB(NOW(), INTERVAL 30 DAY)
            GROUP BY accion, DATE(created_at)
            ORDER BY fecha DESC, total DESC
        ");
        
        return $this->db->resultSet();
    }
}
