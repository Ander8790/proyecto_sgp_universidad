<?php
/**
 * WizardController - Wizard de Seguridad / Onboarding para Nuevos Usuarios
 *
 * FLUJO DE SEGURIDAD ("LA JAULA") — 4 PASOS:
 *   Paso 1: Cambio de Contraseña
 *   Paso 2: Preguntas de Seguridad
 *   Paso 3: Datos Personales (nombres, apellidos, teléfono, fecha_nac, género)
 *   Paso 4: Perfil Profesional (PIN + Institución para pasantes / Cargo + Dpto para admin/tutor)
 *
 * ESQUEMA HÍBRIDO DEFINITIVO:
 *   usuarios         → id, cedula (UNIQUE), correo, password, pin_asistencia, rol_id, estado
 *   datos_personales → usuario_id, nombres, apellidos, telefono, genero, fecha_nacimiento
 *   datos_pasante    → lógica académica (pasantes)
 *
 * @version 5.0 - Wizard de 4 Pasos con Datos Personales obligatorios
 */
class WizardController extends Controller
{
    private $userModel;
    private $db;

    public function __construct()
    {
        Session::start();

        if (!Session::get('user_id')) {
            header('Location: ' . URLROOT . '/auth/login');
            exit;
        }

        $this->userModel = $this->model('User');
        $config = require '../app/config/config.php';
        $this->db = new Database($config['db']);
    }

    /**
     * Vista: Mostrar Wizard de 4 Pasos
     */
    public function index()
    {
        $userId = Session::get('user_id');

        // Datos básicos del usuario + nombres/apellidos ya existentes (para la tarjeta de identidad)
        $this->db->query("
            SELECT u.id, u.cedula, u.correo, u.rol_id, u.requiere_cambio_clave,
                   u.pin_asistencia,
                   r.nombre AS rol_nombre,
                   dp.nombres, dp.apellidos, dp.telefono, dp.genero, dp.fecha_nacimiento,
                   dpa.institucion_procedencia
            FROM usuarios u
            LEFT JOIN roles r ON u.rol_id = r.id
            LEFT JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            WHERE u.id = :uid
        ");
        $this->db->bind(':uid', $userId);
        $user = $this->db->single();

        // Preguntas de seguridad
        $questions = $this->userModel->getSecurityQuestions();

        // Paso 4: Catálogos según rol
        $instituciones = [];
        $departamentos = [];

        try {
            $this->db->query("SELECT id, nombre, direccion FROM instituciones ORDER BY nombre ASC");
            $instituciones = $this->db->resultSet();
        } catch (Exception $e) {
            error_log("⚠️ WIZARD: Error cargando instituciones: " . $e->getMessage());
        }

        try {
            $this->db->query("SELECT id, nombre, descripcion FROM departamentos WHERE activo = 1 ORDER BY nombre ASC");
            $departamentos = $this->db->resultSet();
        } catch (Exception $e) {
            error_log("⚠️ WIZARD: Error cargando departamentos: " . $e->getMessage());
        }

        $data = [
            'user'          => $user,
            'questions'     => $questions,
            'instituciones' => $instituciones,
            'departamentos' => $departamentos,
            'title'         => 'Configuración de Seguridad',
            // NUEVO: Bandera para saltar pasos si es auto-registrado
            'saltar_seguridad' => (Session::get('requiere_cambio_clave') == 0)
        ];

        $this->view('wizard/index', $data, false);
    }

    /**
     * Procesar Wizard — Guardar Configuración
     *
     * ACID: Todo se guarda dentro de una única transacción.
     */
    public function procesar()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->redirect('/wizard/index');
            return;
        }

        $userId = Session::get('user_id');
        $user   = $this->userModel->findById($userId);
        $rolId  = (int)($user['rol_id'] ?? Session::get('role_id') ?? 0);

        $saltarSeguridad = ($user['requiere_cambio_clave'] == 0);

        // ────────────────────────────────────────────────────
        // PASO 1: VALIDAR CONTRASEÑA (Solo si requiere_cambio_clave = 1)
        // ────────────────────────────────────────────────────
        $newPassword = null;
        if (!$saltarSeguridad) {
            $currentPassword = trim($_POST['current_password'] ?? '');
            $newPassword     = trim($_POST['new_password']     ?? '');
            $confirmPassword = trim($_POST['confirm_password'] ?? '');

            if (!password_verify($currentPassword, $user['password'])) {
                Session::setFlash('error', 'La contraseña actual es incorrecta.');
                $this->redirect('/wizard/index');
                return;
            }

            if ($newPassword !== $confirmPassword) {
                Session::setFlash('error', 'Las contraseñas nuevas no coinciden.');
                $this->redirect('/wizard/index');
                return;
            }

            if (strlen($newPassword) < 8) {
                Session::setFlash('error', 'La contraseña debe tener al menos 8 caracteres.');
                $this->redirect('/wizard/index');
                return;
            }
        }

        // ────────────────────────────────────────────────────
        // PASO 2: VALIDAR PREGUNTAS DE SEGURIDAD (Solo si requiere_cambio_clave = 1)
        // ────────────────────────────────────────────────────
        $securityQuestions = [];
        if (!$saltarSeguridad) {
            for ($i = 1; $i <= 3; $i++) {
                $questionId = $_POST["question_$i"] ?? null;
                $answer     = trim($_POST["answer_$i"] ?? '');

                if (!$questionId || !$answer) {
                    Session::setFlash('error', 'Debes completar todas las preguntas de seguridad.');
                    $this->redirect('/wizard/index');
                    return;
                }

                $securityQuestions[] = ['question_id' => $questionId, 'answer' => $answer];
            }

            $questionIds = array_column($securityQuestions, 'question_id');
            if (count($questionIds) !== count(array_unique($questionIds))) {
                Session::setFlash('error', 'No puedes seleccionar la misma pregunta más de una vez.');
                $this->redirect('/wizard/index');
                return;
            }
        }

        // ────────────────────────────────────────────────────
        // PASO 3: DATOS PERSONALES
        // nombres/apellidos: vienen de la BD (asignados por Admin), NO del POST
        // ────────────────────────────────────────────────────
        // nombres/apellidos: viven en datos_personales (fijados por Admin), NO del POST
        $this->db->query("SELECT nombres, apellidos FROM datos_personales WHERE usuario_id = :uid");
        $this->db->bind(':uid', $userId);
        $dpRow = $this->db->single();
        $nombres_reales = $dpRow ? trim(($dpRow->nombres ?? '') . ' ' . ($dpRow->apellidos ?? '')) : '';

        $telefono        = trim($_POST['telefono']        ?? '');
        $fechaNacimiento = trim($_POST['fecha_nacimiento'] ?? '');
        $genero          = trim($_POST['genero']          ?? '');

        if (!$telefono) {
            Session::setFlash('error', 'El campo Teléfono es obligatorio.');
            $this->redirect('/wizard/index');
            return;
        }
        if (!$fechaNacimiento) {
            Session::setFlash('error', 'La Fecha de Nacimiento es obligatoria.');
            $this->redirect('/wizard/index');
            return;
        }
        if (!$genero) {
            Session::setFlash('error', 'Selecciona tu género.');
            $this->redirect('/wizard/index');
            return;
        }

        // ────────────────────────────────────────────────────
        // PASO 4: VALIDAR PERFIL PROFESIONAL
        // ────────────────────────────────────────────────────
        $pinAsistencia  = null;
        $institucionId  = null;
        $cargo          = null;
        $departamentoId = null;

        if ($rolId === 3) {
            // PASANTE: PIN + Institución
            $pinAsistencia = trim($_POST['pin_asistencia'] ?? '');
            $institucionId = (int)($_POST['institucion_id'] ?? 0);

            if (!preg_match('/^[0-9]{4}$/', $pinAsistencia)) {
                Session::setFlash('error', 'El PIN debe tener exactamente 4 dígitos numéricos.');
                $this->redirect('/wizard/index');
                return;
            }
            if ($institucionId <= 0) {
                Session::setFlash('error', 'Debes seleccionar tu institución de procedencia.');
                $this->redirect('/wizard/index');
                return;
            }
        } else {
            // ADMIN / TUTOR: solo Departamento (cargo se gestiona desde perfil)
            $cargo          = null;
            $departamentoId = (int)($_POST['departamento_id'] ?? 0);

            if ($departamentoId <= 0) {
                Session::setFlash('error', 'Debes seleccionar un departamento.');
                $this->redirect('/wizard/index');
                return;
            }
        }

        // ────────────────────────────────────────────────────
        // TRANSACCIÓN ATÓMICA (ACID)
        // Esquema híbrido definitivo:
        //   usuarios         → credenciales + cedula (identificador único)
        //   datos_personales → nombres, apellidos, teléfono, fecha_nac, género
        //   datos_pasante    → lógica académica
        // ────────────────────────────────────────────────────
        try {
            $this->db->beginTransaction();

            // SÓLO SI NO ES AUTO-REGISTRADO (Paso 1 y 2)
            if (!$saltarSeguridad) {
                $hashedPassword = password_hash($newPassword, PASSWORD_BCRYPT);
                
                // Actualizar contraseña
                $this->db->query("UPDATE usuarios SET password = :password WHERE id = :user_id");
                $this->db->bind(':password', $hashedPassword);
                $this->db->bind(':user_id',  $userId);
                $this->db->execute();

                // Guardar preguntas de seguridad
                $this->db->query("DELETE FROM usuarios_respuestas WHERE usuario_id = :user_id");
                $this->db->bind(':user_id', $userId);
                $this->db->execute();

                foreach ($securityQuestions as $sq) {
                    $normalizedAnswer = strtolower(trim($sq['answer']));
                    $hashedAnswer     = password_hash($normalizedAnswer, PASSWORD_BCRYPT);

                    $this->db->query("
                        INSERT INTO usuarios_respuestas (usuario_id, pregunta_id, respuesta_hash)
                        VALUES (:user_id, :question_id, :answer_hash)
                    ");
                    $this->db->bind(':user_id',     $userId);
                    $this->db->bind(':question_id', $sq['question_id']);
                    $this->db->bind(':answer_hash', $hashedAnswer);
                    $this->db->execute();
                }
            }

            if ($rolId === 3) {
                // ── PASANTE ──────────────────────────────────────────────
                // A. usuarios: solo pin_asistencia y flag
                $this->db->query("
                    UPDATE usuarios
                    SET pin_asistencia        = :pin,
                        requiere_cambio_clave = 0
                    WHERE id = :user_id
                ");
                $this->db->bind(':pin',      $pinAsistencia);
                $this->db->bind(':user_id',  $userId);

                if (!$this->db->execute()) {
                    throw new Exception('Error al actualizar registro del pasante.');
                }

                // B. datos_personales: biografía (UPSERT)
                $this->db->query("
                    INSERT INTO datos_personales
                        (usuario_id, telefono, fecha_nacimiento, genero)
                    VALUES
                        (:uid, :telefono, :fnac, :genero)
                    ON DUPLICATE KEY UPDATE
                        telefono        = VALUES(telefono),
                        fecha_nacimiento = VALUES(fecha_nacimiento),
                        genero          = VALUES(genero)
                ");
                $this->db->bind(':uid',       $userId);
                $this->db->bind(':telefono',  $telefono);
                $this->db->bind(':fnac',      $fechaNacimiento);
                $this->db->bind(':genero',    $genero);

                if (!$this->db->execute()) {
                    throw new Exception('Error al guardar datos personales del pasante.');
                }

                // C. datos_pasante: lógica académica (UPSERT)
                // Usamos institucion_procedencia (VARCHAR) para guardar la selección
                $this->db->query("
                    INSERT INTO datos_pasante (usuario_id, estado_pasantia, horas_meta, horas_acumuladas, institucion_procedencia)
                    VALUES (:uid, 'Sin Asignar', 1440, 0, :inst_txt)
                    ON DUPLICATE KEY UPDATE
                        institucion_procedencia = VALUES(institucion_procedencia)
                ");
                $this->db->bind(':uid', $userId);
                $this->db->bind(':inst_txt', $institucionId); // Guardamos el valor (ID o texto) del select

                if (!$this->db->execute()) {
                    throw new Exception('Error al crear registro de pasantía.');
                }

            } else {
                // ── ADMIN / TUTOR ─────────────────────────────────────────
                // A. usuarios: credenciales + departamento
                $this->db->query("
                    UPDATE usuarios
                    SET password              = :password,
                        departamento_id       = :dept_id,
                        requiere_cambio_clave = 0
                    WHERE id = :user_id
                ");
                $this->db->bind(':password', $hashedPassword);
                $this->db->bind(':dept_id',  $departamentoId);
                $this->db->bind(':user_id',  $userId);

                if (!$this->db->execute()) {
                    throw new Exception('Error al actualizar credenciales.');
                }

                // B. datos_personales: nombres, apellidos, cargo, etc. (UPSERT)
                $this->db->query("
                    INSERT INTO datos_personales
                        (usuario_id, telefono, fecha_nacimiento, genero, cargo)
                    VALUES
                        (:uid, :telefono, :fnac, :genero, :cargo)
                    ON DUPLICATE KEY UPDATE
                        telefono         = VALUES(telefono),
                        fecha_nacimiento = VALUES(fecha_nacimiento),
                        genero           = VALUES(genero),
                        cargo            = VALUES(cargo)
                ");
                $this->db->bind(':uid',       $userId);
                $this->db->bind(':telefono',  $telefono);
                $this->db->bind(':fnac',      $fechaNacimiento);
                $this->db->bind(':genero',    $genero);
                $this->db->bind(':cargo',     $cargo);

                if (!$this->db->execute()) {
                    throw new Exception('Error al guardar datos personales.');
                }
            }

            // ── Preguntas de seguridad ────────────────────────────────
            $this->db->query("DELETE FROM usuarios_respuestas WHERE usuario_id = :user_id");
            $this->db->bind(':user_id', $userId);
            $this->db->execute();

            foreach ($securityQuestions as $sq) {
                $normalizedAnswer = strtolower(trim($sq['answer']));
                $hashedAnswer     = password_hash($normalizedAnswer, PASSWORD_BCRYPT);

                $this->db->query("
                    INSERT INTO usuarios_respuestas (usuario_id, pregunta_id, respuesta_hash)
                    VALUES (:user_id, :question_id, :answer_hash)
                ");
                $this->db->bind(':user_id',     $userId);
                $this->db->bind(':question_id', $sq['question_id']);
                $this->db->bind(':answer_hash', $hashedAnswer);

                if (!$this->db->execute()) {
                    throw new Exception('Error al guardar las preguntas de seguridad.');
                }
            }

            $this->db->commit();

            Session::set('requiere_cambio_clave', 0);
            Session::set('perfil_completado', true);

            // Actualizar sesión con el nombre real del usuario (Tarea 1)
            if ($nombres_reales) {
                Session::set('user_name', $nombres_reales);
            }

            error_log("✅ WIZARD ÉXITO: Usuario ID $userId (rol $rolId) completó el onboarding de 4 pasos.");

            Session::setFlash('success', '¡Configuración completada! Bienvenido al sistema.');
            $this->redirect('/dashboard');

        } catch (Exception $e) {
            $this->db->rollback();
            error_log("❌ WIZARD ERROR: " . $e->getMessage());
            Session::setFlash('error', 'Error al procesar la configuración: ' . $e->getMessage());
            $this->redirect('/wizard/index');
        }
    }
}
