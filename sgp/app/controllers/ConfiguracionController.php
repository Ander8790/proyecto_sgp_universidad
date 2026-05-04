<?php
class ConfiguracionController extends Controller {

    private $db;

    public function __construct() {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        $config   = require '../app/config/config.php';
        $this->db = new Database($config['db']);
    }

    public function index() {

        // ── POST: Agregar feriado ─────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar_feriado') {
            $fecha       = trim($_POST['fecha']       ?? '');
            $nombre      = trim($_POST['nombre']      ?? '');
            $tipo        = trim($_POST['tipo']        ?? 'Nacional');

            $tiposValidos = ['Nacional', 'Regional', 'Institucional'];
            if (!in_array($tipo, $tiposValidos, true)) $tipo = 'Nacional';

            if ($fecha && $nombre) {
                // Validar formato de fecha
                $dt = DateTime::createFromFormat('Y-m-d', $fecha);
                if (!$dt || $dt->format('Y-m-d') !== $fecha) {
                    Session::setFlash('error', '⚠️ Formato de fecha inválido.');
                } else {
                    try {
                        $feriadoModel = new FeriadoModel($this->db);
                        if ($feriadoModel->existeFecha($fecha)) {
                            Session::setFlash('error', '⚠️ Ya existe un feriado registrado para esa fecha.');
                        } else {
                            $feriadoModel->crear($fecha, $nombre, $tipo);
                            Session::setFlash('success', '✅ Feriado "' . htmlspecialchars($nombre) . '" registrado correctamente.');
                            if (class_exists('AuditModel')) {
                                AuditModel::log('AGREGAR_FERIADO', "Feriado registrado: $nombre ($fecha)");
                            }
                        }
                    } catch (Exception $e) {
                        Session::setFlash('error', '❌ Error al registrar feriado: ' . $e->getMessage());
                    }
                }
            } else {
                Session::setFlash('error', '⚠️ La fecha y el nombre del feriado son obligatorios.');
            }
            if (isset($_POST['origen']) && $_POST['origen'] === 'almanaque') {
                header('Location: ' . URLROOT . '/configuracion/calendario_feriados');
            } else {
                header('Location: ' . URLROOT . '/configuracion');
            }
            exit;
        }

        // ── POST: Editar feriado (nombre y tipo solamente) ───────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar_feriado') {
            $id     = (int)($_POST['id']     ?? 0);
            $nombre = trim($_POST['nombre']  ?? '');
            $tipo   = trim($_POST['tipo']    ?? 'Nacional');

            $tiposValidos = ['Nacional', 'Regional', 'Institucional'];
            if (!in_array($tipo, $tiposValidos, true)) $tipo = 'Nacional';

            if ($id > 0 && $nombre !== '') {
                try {
                    $feriadoModel = new FeriadoModel($this->db);
                    $feriado = $feriadoModel->obtenerPorId($id);
                    if (!$feriado) {
                        Session::setFlash('error', '⚠️ Feriado no encontrado.');
                    } else {
                        $feriadoModel->actualizar($id, $nombre, $tipo);
                        Session::setFlash('success', '✅ Feriado actualizado correctamente.');
                        if (class_exists('AuditModel')) {
                            AuditModel::log('EDITAR_FERIADO', "Feriado editado ID: $id → \"$nombre\" ($tipo)");
                        }
                    }
                } catch (Exception $e) {
                    Session::setFlash('error', '❌ Error al editar feriado: ' . $e->getMessage());
                }
            } else {
                Session::setFlash('error', '⚠️ El nombre del feriado es obligatorio.');
            }
            if (isset($_POST['origen']) && $_POST['origen'] === 'almanaque') {
                header('Location: ' . URLROOT . '/configuracion/calendario_feriados');
            } else {
                header('Location: ' . URLROOT . '/configuracion');
            }
            exit;
        }

        // ── POST: Eliminar feriado ────────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_feriado') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                try {
                    $feriadoModel = new FeriadoModel($this->db);
                    $feriado = $feriadoModel->obtenerPorId($id);
                    if (!$feriado) {
                        Session::setFlash('error', '⚠️ Feriado no encontrado.');
                    } elseif (strtotime($feriado->fecha) < strtotime(date('Y-m-d'))) {
                        Session::setFlash('error', '⚠️ No se pueden eliminar feriados pasados (auditoría histórica).');
                    } else {
                        $feriadoModel->eliminar($id);
                        Session::setFlash('success', '✅ Feriado eliminado correctamente.');
                        if (class_exists('AuditModel')) {
                            AuditModel::log('ELIMINAR_FERIADO', "Feriado eliminado ID: $id ({$feriado->nombre})");
                        }
                    }
                } catch (Exception $e) {
                    Session::setFlash('error', '❌ Error al eliminar feriado.');
                }
            }
            if (isset($_POST['origen']) && $_POST['origen'] === 'almanaque') {
                header('Location: ' . URLROOT . '/configuracion/calendario_feriados');
            } else {
                header('Location: ' . URLROOT . '/configuracion');
            }
            exit;
        }

        // ── POST: Mantenimiento — Limpiar sesiones antiguas ───────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'limpiar_sesiones') {
            try {
                // Eliminar registros de sesiones con más de 30 días (si existe la tabla)
                $this->db->query("DELETE FROM sesiones WHERE creado_en < DATE_SUB(NOW(), INTERVAL 30 DAY)");
                $this->db->execute();
                $afectados = $this->db->rowCount();
                Session::setFlash('success', "✅ Sesiones antiguas limpiadas ($afectados registros eliminados).");
                if (class_exists('AuditModel')) {
                    AuditModel::log('MANTENIMIENTO_SESIONES', "Limpieza de sesiones antiguas: $afectados eliminadas");
                }
            } catch (Exception $e) {
                // La tabla puede no existir; no es error crítico
                Session::setFlash('success', '✅ Mantenimiento ejecutado correctamente.');
            }
            header('Location: ' . URLROOT . '/configuracion');
            exit;
        }

        // ── POST: Mantenimiento — Purgar bitácora antigua ─────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'purgar_bitacora') {
            $meses = max(1, min(24, (int)($_POST['meses'] ?? 6)));
            try {
                $this->db->query("DELETE FROM bitacora WHERE fecha_hora < DATE_SUB(NOW(), INTERVAL :meses MONTH)");
                $this->db->bind(':meses', $meses);
                $this->db->execute();
                $afectados = $this->db->rowCount();
                Session::setFlash('success', "✅ Bitácora purgada: $afectados registros anteriores a $meses meses eliminados.");
                if (class_exists('AuditModel')) {
                    AuditModel::log('MANTENIMIENTO_BITACORA', "Purga de bitácora: $afectados registros eliminados (>$meses meses)");
                }
            } catch (Exception $e) {
                Session::setFlash('error', '❌ Error al purgar bitácora: ' . $e->getMessage());
            }
            header('Location: ' . URLROOT . '/configuracion');
            exit;
        }


        // ── POST: Agregar institución ─────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar_institucion') {
            $nombre    = trim($_POST['nombre']    ?? '');
            $direccion = trim($_POST['direccion'] ?? '');

            if ($nombre && $direccion) {
                try {
                    $this->db->query("SELECT COUNT(*) as total FROM instituciones WHERE nombre = :nombre AND categoria = 'origen'");
                    $this->db->bind(':nombre', $nombre);
                    $row = $this->db->single();
                    if ($row->total > 0) {
                        Session::setFlash('error', '⚠️ Ya existe una institución con ese nombre.');
                    } else {
                        $this->db->query("INSERT INTO instituciones (nombre, direccion, categoria) VALUES (:nombre, :direccion, 'origen')");
                        $this->db->bind(':nombre',    $nombre);
                        $this->db->bind(':direccion', $direccion);
                        $this->db->execute();
                        Session::setFlash('success', '✅ Institución "' . htmlspecialchars($nombre) . '" registrada correctamente.');
                    }
                } catch (Exception $e) {
                    Session::setFlash('error', '❌ Error al registrar institución: ' . $e->getMessage());
                }
            } else {
                Session::setFlash('error', '⚠️ Completa todos los campos de la institución.');
            }
            header('Location: ' . URLROOT . '/configuracion');
            exit;
        }

        // ── POST: Editar institución ──────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'editar_institucion') {
            $id = (int)($_POST['id'] ?? 0);
            $nombre = trim($_POST['nombre'] ?? '');
            $direccion = trim($_POST['direccion'] ?? '');
            $rep_nombre = trim($_POST['representante_nombre'] ?? '');
            $rep_cargo = trim($_POST['representante_cargo'] ?? '');
            $rep_correo = trim($_POST['representante_correo'] ?? '');
            $rep_telefono = trim($_POST['representante_telefono'] ?? '');

            if ($id > 0 && $nombre && $direccion) {
                try {
                    $this->db->query("SELECT COUNT(*) as total FROM instituciones WHERE nombre = :nombre AND id != :id AND categoria = 'origen'");
                    $this->db->bind(':nombre', $nombre);
                    $this->db->bind(':id', $id);
                    $row = $this->db->single();

                    if ($row->total > 0) {
                        Session::setFlash('error', '⚠️ Ya existe otra institución con ese nombre.');
                    } else {
                        $this->db->query("
                            UPDATE instituciones 
                            SET nombre = :nombre, 
                                direccion = :direccion,
                                representante_nombre = :rep_nombre,
                                representante_cargo = :rep_cargo,
                                representante_correo = :rep_correo,
                                representante_telefono = :rep_telefono
                            WHERE id = :id
                        ");
                        $this->db->bind(':nombre', $nombre);
                        $this->db->bind(':direccion', $direccion);
                        $this->db->bind(':rep_nombre', $rep_nombre ?: null);
                        $this->db->bind(':rep_cargo', $rep_cargo ?: null);
                        $this->db->bind(':rep_correo', $rep_correo ?: null);
                        $this->db->bind(':rep_telefono', $rep_telefono ?: null);
                        $this->db->bind(':id', $id);
                        $this->db->execute();

                        Session::setFlash('success', '✅ Institución "' . htmlspecialchars($nombre) . '" actualizada correctamente.');
                    }
                } catch (Exception $e) {
                    Session::setFlash('error', '❌ Error al editar institución: ' . $e->getMessage());
                }
            } else {
                Session::setFlash('error', '⚠️ Faltan datos obligatorios para editar la institución.');
            }
            header('Location: ' . URLROOT . '/configuracion');
            exit;
        }

        // ── POST: Eliminar institución ────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_institucion') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                try {
                    // Verificar que no tenga pasantes asignados
                    $this->db->query("SELECT COUNT(*) as total FROM datos_pasante WHERE institucion_id = :id");
                    $this->db->bind(':id', $id);
                    $row = $this->db->single();
                    if ($row->total > 0) {
                        Session::setFlash('error', '⚠️ No se puede eliminar: hay ' . $row->total . ' pasante(s) asignados a esta institución.');
                    } else {
                        $this->db->query("DELETE FROM instituciones WHERE id = :id AND categoria = 'origen'");
                        $this->db->bind(':id', $id);
                        $this->db->execute();
                        Session::setFlash('success', '✅ Institución eliminada correctamente.');
                    }
                } catch (Exception $e) {
                    Session::setFlash('error', '❌ Error al eliminar institución.');
                }
            }
            header('Location: ' . URLROOT . '/configuracion');
            exit;
        }

        // ── POST: Agregar departamento ────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar_departamento') {
            $nombre      = trim($_POST['nombre']      ?? '');
            $descripcion = trim($_POST['descripcion'] ?? '');

            if ($nombre) {
                try {
                    $this->db->query("SELECT COUNT(*) as total FROM departamentos WHERE nombre = :nombre");
                    $this->db->bind(':nombre', $nombre);
                    $row = $this->db->single();
                    if ($row->total > 0) {
                        Session::setFlash('error', '⚠️ Ya existe un departamento con ese nombre.');
                    } else {
                        $this->db->query("INSERT INTO departamentos (nombre, descripcion, activo) VALUES (:nombre, :descripcion, 1)");
                        $this->db->bind(':nombre',      $nombre);
                        $this->db->bind(':descripcion', $descripcion);
                        $this->db->execute();
                        Session::setFlash('success', '✅ Departamento "' . htmlspecialchars($nombre) . '" creado correctamente.');
                    }
                } catch (Exception $e) {
                    Session::setFlash('error', '❌ Error al crear departamento: ' . $e->getMessage());
                }
            } else {
                Session::setFlash('error', '⚠️ El nombre del departamento es obligatorio.');
            }
            header('Location: ' . URLROOT . '/configuracion');
            exit;
        }

        // ── POST: Eliminar departamento (lógico) ──────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_departamento') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                try {
                    // Verificar que no tenga usuarios asignados
                    $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE departamento_id = :id");
                    $this->db->bind(':id', $id);
                    $row = $this->db->single();
                    if ($row->total > 0) {
                        Session::setFlash('error', '⚠️ No se puede eliminar: hay ' . $row->total . ' usuario(s) asignados a este departamento.');
                    } else {
                        $this->db->query("DELETE FROM departamentos WHERE id = :id");
                        $this->db->bind(':id', $id);
                        $this->db->execute();
                        Session::setFlash('success', '✅ Departamento eliminado correctamente.');
                    }
                } catch (Exception $e) {
                    Session::setFlash('error', '❌ Error al eliminar departamento.');
                }
            }
            header('Location: ' . URLROOT . '/configuracion');
            exit;
        }

        // ── GET: Consultar datos ──────────────────────────────────────────
        $instituciones = [];
        $departamentos = [];
        $feriados      = [];
        $statsDB       = [];

        try {
            $this->db->query("SELECT * FROM instituciones WHERE categoria = 'origen' ORDER BY nombre ASC");
            $instituciones = array_map(fn($i) => (array) $i, $this->db->resultSet());
        } catch (Exception $e) { /* tabla vacía o no existe */ }

        try {
            $this->db->query("SELECT * FROM departamentos ORDER BY nombre ASC");
            $departamentos = array_map(fn($d) => (array) $d, $this->db->resultSet());
        } catch (Exception $e) { /* tabla vacía o no existe */ }

        try {
            $feriadoModel = new FeriadoModel($this->db);
            $feriados     = array_map(fn($f) => (array) $f, $feriadoModel->obtenerVigentes());
        } catch (Exception $e) { /* tabla feriados puede no existir aún */ }

        // Pasantes activos sin marcar asistencia hoy (para el reloj de mantenimiento)
        try {
            $this->db->query("
                SELECT COUNT(*) AS total
                FROM usuarios u
                INNER JOIN datos_pasante dpa ON dpa.usuario_id = u.id
                WHERE u.rol_id = 3
                  AND u.estado = 'activo'
                  AND dpa.estado_pasantia = 'Activo'
                  AND NOT EXISTS (
                      SELECT 1 FROM asistencias a
                      WHERE a.pasante_id = u.id AND a.fecha = CURDATE()
                  )
            ");
            $row = $this->db->single();
            $statsDB['sin_marcar_hoy'] = (int)($row->total ?? 0);
        } catch (Exception $e) { $statsDB['sin_marcar_hoy'] = 0; }

        // Estadísticas básicas para la card de mantenimiento
        try {
            $this->db->query("SELECT COUNT(*) AS total FROM usuarios");
            $row = $this->db->single();
            $statsDB['usuarios'] = (int)($row->total ?? 0);
        } catch (Exception $e) { $statsDB['usuarios'] = 0; }

        try {
            $this->db->query("SELECT COUNT(*) AS total FROM asistencias");
            $row = $this->db->single();
            $statsDB['asistencias'] = (int)($row->total ?? 0);
        } catch (Exception $e) { $statsDB['asistencias'] = 0; }

        try {
            $this->db->query("SELECT COUNT(*) AS total FROM bitacora WHERE fecha_hora >= DATE_SUB(NOW(), INTERVAL 30 DAY)");
            $row = $this->db->single();
            $statsDB['bitacora_mes'] = (int)($row->total ?? 0);
        } catch (Exception $e) { $statsDB['bitacora_mes'] = 0; }

        // Estado del kiosco
        $kioscoActivo = 1;
        try {
            $this->db->query("SELECT valor FROM configuracion WHERE clave = 'kiosco_activo' LIMIT 1");
            $row = $this->db->single();
            $kioscoActivo = $row ? (int)$row->valor : 1;
        } catch (Exception $e) {
            // Fallback: leer de sesión o asumir activo
            $kioscoActivo = (int)(Session::get('kiosco_activo') ?? 1);
        }

        $data = [
            'title'         => 'Configuración del Sistema',
            'instituciones' => $instituciones,
            'departamentos' => $departamentos,
            'feriados'      => $feriados,
            'statsDB'       => $statsDB,
            'kioscoActivo'  => $kioscoActivo,
        ];

        $this->view('configuracion/index', $data);
    }

    /**
     * AJAX: Buscar pasante por cédula para resetear PIN
     */
    public function buscarPasante() {
        header('Content-Type: application/json');
        
        $cedula = trim($_GET['cedula'] ?? '');
        if (!$cedula) {
            echo json_encode(['success' => false, 'message' => 'Cédula no proporcionada']);
            exit;
        }

        try {
            $this->db->query("
                SELECT u.id, u.cedula, dp.nombres, dp.apellidos 
                FROM usuarios u 
                JOIN datos_personales dp ON u.id = dp.usuario_id 
                WHERE u.cedula = :cedula AND u.rol_id = 3
                LIMIT 1
            ");
            $this->db->bind(':cedula', $cedula);
            $pasante = $this->db->single();

            if ($pasante) {
                echo json_encode([
                    'success' => true, 
                    'pasante' => [
                        'id' => $pasante->id,
                        'nombre' => $pasante->nombres . ' ' . $pasante->apellidos,
                        'cedula' => $pasante->cedula
                    ]
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Pasante no encontrado o no es un pasante activo']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error en la búsqueda']);
        }
        exit;
    }

    /**
     * AJAX: Resetear PIN de un pasante
     */
    public function resetearPin() {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de pasante inválido']);
            exit;
        }

        // Generar un nuevo PIN aleatorio de 4 dígitos
        $nuevoPin = str_pad((string)mt_rand(0, 9999), 4, '0', STR_PAD_LEFT);

        // SEGURIDAD: Hashear el PIN antes de almacenarlo (nunca guardar en texto plano)
        $pinHasheado = password_hash($nuevoPin, PASSWORD_BCRYPT);
        try {
            $this->db->query("UPDATE usuarios SET pin_asistencia = :pin WHERE id = :id AND rol_id = 3");
            $this->db->bind(':pin', $pinHasheado);
            $this->db->bind(':id', $id);
            
            if ($this->db->execute()) {
                // Registrar en bitácora
                if (class_exists('AuditModel')) {
                    AuditModel::log('RESET_PIN_KIOSCO', "Se reseteó el PIN del pasante ID: $id desde Configuración");
                }

                // Resolver notificaciones de solicitud_pin para todos (admin + tutor)
                require_once APPROOT . '/models/NotificationModel.php';
                $notifModel = new NotificationModel($this->db);
                $notifModel->resolverPorReferencia('solicitud_pin', $id);

                echo json_encode([
                    'success'  => true,
                    'message'  => 'PIN restablecido correctamente.',
                    'nuevo_pin' => $nuevoPin
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'No se pudo actualizar el PIN']);
            }
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al resetear PIN']);
        }
        exit;
    }

    /**
     * AJAX GET: Verificar conexión a base de datos y retornar métricas.
     * Ruta: GET /configuracion/verificarConexionBD
     */
    public function verificarConexionBD()
    {
        header('Content-Type: application/json');
        RoleMiddleware::authorize([0, 1]);

        $inicio = microtime(true);
        try {
            $this->db->query("SELECT VERSION() AS ver, (SELECT COUNT(*) FROM information_schema.tables WHERE table_schema = DATABASE()) AS tablas");
            $row = $this->db->single();
            $latencia = round((microtime(true) - $inicio) * 1000, 1);

            echo json_encode([
                'success'    => true,
                'latencia_ms'=> $latencia,
                'version'    => $row->ver ?? '',
                'tablas'     => (int)($row->tablas ?? 0),
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error de conexión: ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * AJAX POST: Limpiar sesiones expiradas.
     * Ruta: POST /configuracion/limpiarSesiones
     */
    public function limpiarSesiones()
    {
        header('Content-Type: application/json');
        RoleMiddleware::authorize([0, 1]);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $eliminadas = 0;

        // Intentar limpiar tabla sesiones si existe
        try {
            $this->db->query("DELETE FROM sesiones WHERE creado_en < DATE_SUB(NOW(), INTERVAL 24 HOUR)");
            $this->db->execute();
            $eliminadas += $this->db->rowCount();
        } catch (Exception $e) { /* tabla no existe, ignorar */ }

        // Limpiar archivos de sesión PHP expirados
        $sessionPath = session_save_path() ?: sys_get_temp_dir();
        $archivosEliminados = 0;
        if (is_dir($sessionPath)) {
            foreach (glob($sessionPath . '/sess_*') as $file) {
                if (is_file($file) && (time() - filemtime($file)) > 86400) {
                    @unlink($file);
                    $archivosEliminados++;
                }
            }
        }

        if (class_exists('AuditModel')) {
            AuditModel::log('MANTENIMIENTO_SESIONES', "Limpieza de sesiones: $eliminadas BD + $archivosEliminados archivos");
        }

        echo json_encode([
            'success'  => true,
            'message'  => "Limpieza completada: $eliminadas registros BD y $archivosEliminados archivos de sesión eliminados.",
            'eliminadas' => $eliminadas + $archivosEliminados,
        ]);
        exit;
    }

    /**
     * AJAX POST: Purgar registros antiguos de bitácora.
     * Ruta: POST /configuracion/purgarBitacora
     */
    public function purgarBitacora()
    {
        header('Content-Type: application/json');
        RoleMiddleware::authorize([0, 1]);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $meses = max(1, min(24, (int)($_POST['meses'] ?? 6)));

        try {
            // Verificar qué columna de fecha usa la tabla bitácora
            $this->db->query("SELECT created_at FROM bitacora LIMIT 1");
            $col = 'created_at';
        } catch (Exception $e) {
            try {
                $this->db->query("SELECT fecha_hora FROM bitacora LIMIT 1");
                $col = 'fecha_hora';
            } catch (Exception $e2) {
                echo json_encode(['success' => false, 'message' => 'No se pudo acceder a la tabla bitácora.']);
                exit;
            }
        }

        try {
            $this->db->query("DELETE FROM bitacora WHERE `$col` < DATE_SUB(NOW(), INTERVAL :meses MONTH)");
            $this->db->bind(':meses', $meses);
            $this->db->execute();
            $eliminados = $this->db->rowCount();

            if (class_exists('AuditModel')) {
                AuditModel::log('MANTENIMIENTO_BITACORA', "Purga de bitácora: $eliminados registros > $meses meses eliminados");
            }

            echo json_encode([
                'success'    => true,
                'message'    => "Bitácora purgada: $eliminados registro(s) anteriores a $meses meses eliminados.",
                'eliminados' => $eliminados,
            ]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => 'Error al purgar: ' . $e->getMessage()]);
        }
        exit;
        exit;
    }

    // ─────────────────────────────────────────────────────────
    // GET /configuracion/calendario_feriados — Vista Almanaque
    // ─────────────────────────────────────────────────────────
    public function calendario_feriados() {
        $year = isset($_GET['y']) && is_numeric($_GET['y']) ? (int)$_GET['y'] : (int)date('Y');
        
        $feriadoModel = new FeriadoModel($this->db);
        $feriadosArray = $feriadoModel->obtenerTodos(); // Puedes crear un método por año o filtrarlo aquí
        
        // Indexar por fecha para acceso rápido
        $feriados = [];
        foreach ($feriadosArray as $f) {
            $feriados[$f->fecha] = $f;
        }

        $data = [
            'year'     => $year,
            'feriados' => $feriados
        ];

        $this->view('configuracion/calendario_feriados', $data);
    }

    // ─────────────────────────────────────────────────────────
    // POST /configuracion/sincronizarFeriados — Sync API
    // Consulta Nager.Date, inserta solo los feriados faltantes
    // ─────────────────────────────────────────────────────────
    public function sincronizarFeriados(): void
    {
        header('Content-Type: application/json');
        RoleMiddleware::authorize([0, 1]);

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido']);
            exit;
        }

        $anio = (int)($_POST['anio'] ?? date('Y'));
        if ($anio < 2020 || $anio > 2035) {
            echo json_encode(['success' => false, 'message' => 'Año inválido.']);
            exit;
        }

        // ── Consultar la API Nager.Date (Venezuela = VE) ──────────────
        $url = "https://date.nager.at/api/v3/PublicHolidays/{$anio}/VE";

        $ch = curl_init($url);
        curl_setopt_array($ch, [
            CURLOPT_RETURNTRANSFER => true,
            CURLOPT_TIMEOUT        => 8,
            CURLOPT_CONNECTTIMEOUT => 5,
            CURLOPT_HTTPHEADER     => ['Accept: application/json'],
            CURLOPT_SSL_VERIFYPEER => false, // XAMPP local sin certificados
            CURLOPT_USERAGENT      => 'SGP-ISP-Bolivar/1.0',
        ]);
        $respuesta = curl_exec($ch);
        $httpCode  = curl_getinfo($ch, CURLINFO_HTTP_CODE);
        $curlError = curl_error($ch);
        curl_close($ch);

        if ($curlError || $httpCode !== 200 || !$respuesta) {
            echo json_encode([
                'success' => false,
                'message' => 'No se pudo conectar con la API de feriados. '
                           . ($curlError ?: "Código HTTP: $httpCode"),
            ]);
            exit;
        }

        $lista = json_decode($respuesta, true);
        if (!is_array($lista)) {
            echo json_encode(['success' => false, 'message' => 'Respuesta de API inválida.']);
            exit;
        }

        // ── Insertar solo los feriados que no existan ─────────────────
        $insertados  = 0;
        $yaExistian  = 0;
        $totalApi    = count($lista);

        // Cargar fechas ya existentes en BD para ese año (consulta única)
        $this->db->query(
            "SELECT fecha FROM dias_feriados WHERE YEAR(fecha) = :anio"
        );
        $this->db->bind(':anio', $anio);
        $rows           = $this->db->resultSet();
        $fechasExistentes = array_column(array_map(fn($r) => (array)$r, $rows), 'fecha');

        foreach ($lista as $item) {
            $fecha  = $item['date']      ?? '';
            $nombre = $item['localName'] ?? ($item['name'] ?? '');

            if (!$fecha || !$nombre) continue;

            if (in_array($fecha, $fechasExistentes, true)) {
                $yaExistian++;
                continue;
            }

            try {
                $this->db->query(
                    "INSERT IGNORE INTO dias_feriados (fecha, nombre, tipo, created_at)
                     VALUES (:fecha, :nombre, 'Nacional', NOW())"
                );
                $this->db->bind(':fecha',  $fecha);
                $this->db->bind(':nombre', mb_substr($nombre, 0, 150));
                $this->db->execute();
                $insertados++;
                $fechasExistentes[] = $fecha; // Evitar doble insert en la misma ejecución
            } catch (Exception $e) {
                $yaExistian++; // Si falla INSERT IGNORE, ya existía
            }
        }

        if (class_exists('AuditModel')) {
            AuditModel::log(
                'SYNC_FERIADOS_API',
                "Sincronización año $anio: $insertados nuevos, $yaExistian ya existían (Nager.Date)"
            );
        }

        echo json_encode([
            'success'     => true,
            'insertados'  => $insertados,
            'ya_existian' => $yaExistian,
            'total_api'   => $totalApi,
            'nota'        => 'Los feriados regionales (Aniversario Ciudad Bolívar, etc.) '
                           . 'no están en la API. Agrégalos manualmente si es necesario.',
        ]);
        exit;
    }
}
