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

        // ── POST: Agregar institución ─────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'agregar_institucion') {
            $nombre    = trim($_POST['nombre']    ?? '');
            $direccion = trim($_POST['direccion'] ?? '');

            if ($nombre && $direccion) {
                try {
                    $this->db->query("SELECT COUNT(*) as total FROM instituciones WHERE nombre = :nombre");
                    $this->db->bind(':nombre', $nombre);
                    $row = $this->db->single();
                    if ($row->total > 0) {
                        Session::setFlash('error', '⚠️ Ya existe una institución con ese nombre.');
                    } else {
                        $this->db->query("INSERT INTO instituciones (nombre, direccion) VALUES (:nombre, :direccion)");
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

        // ── POST: Eliminar institución ────────────────────────────────────
        if ($_SERVER['REQUEST_METHOD'] === 'POST' && ($_POST['accion'] ?? '') === 'eliminar_institucion') {
            $id = (int)($_POST['id'] ?? 0);
            if ($id > 0) {
                try {
                    // Verificar que no tenga pasantes asignados
                    $this->db->query("SELECT COUNT(*) as total FROM usuarios WHERE institucion_id = :id");
                    $this->db->bind(':id', $id);
                    $row = $this->db->single();
                    if ($row->total > 0) {
                        Session::setFlash('error', '⚠️ No se puede eliminar: hay ' . $row->total . ' pasante(s) asignados a esta institución.');
                    } else {
                        $this->db->query("DELETE FROM instituciones WHERE id = :id");
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

        try {
            $this->db->query("SELECT * FROM instituciones ORDER BY nombre ASC");
            $instituciones = array_map(fn($i) => (array) $i, $this->db->resultSet());
        } catch (Exception $e) { /* tabla vacía o no existe */ }

        try {
            $this->db->query("SELECT * FROM departamentos ORDER BY nombre ASC");
            $departamentos = array_map(fn($d) => (array) $d, $this->db->resultSet());
        } catch (Exception $e) { /* tabla vacía o no existe */ }

        $data = [
            'title'         => 'Configuración del Sistema',
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
                
                echo json_encode([
                    'success' => true, 
                    'message' => 'PIN restablecido correctamente.',
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
}


