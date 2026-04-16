<?php
/**
 * TutorController — Panel del Tutor
 *
 * RUTAS:
 *   GET  /tutor                    → index()           Dashboard del tutor
 *   GET  /tutor/pasantes           → pasantes()        Lista de mis pasantes
 *   GET  /tutor/perfil/{id}        → perfil($id)       Ficha individual de pasante
 *   GET  /tutor/asistencias        → asistencias()     Asistencias de mis pasantes (diaria/semanal)
 *   POST /tutor/resetPin           → resetPin()        Resetear PIN de un pasante (AJAX)
 *
 * ACCESO: Solo Tutor (rol 2)
 */

class TutorController extends Controller {

    private $db;

    public function __construct() {
        CacheControl::noCache();
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();
        RoleMiddleware::requireRole(2);

        $config = require '../app/config/config.php';
        $this->db = Database::getInstance();
    }

    // ────────────────────────────────────────────────────────────
    // Dashboard del Tutor
    // ────────────────────────────────────────────────────────────
    public function index(): void {
        $tutorId = (int)Session::get('user_id');

        // KPI 1: Total pasantes asignados
        $this->db->query("SELECT COUNT(*) AS total FROM datos_pasante WHERE tutor_id = :tid");
        $this->db->bind(':tid', $tutorId);
        $totalPasantes = (int)($this->db->single()->total ?? 0);

        // KPI 2: Pasantes activos
        $this->db->query("SELECT COUNT(*) AS total FROM datos_pasante WHERE tutor_id = :tid AND estado_pasantia = 'Activo'");
        $this->db->bind(':tid', $tutorId);
        $pasantesActivos = (int)($this->db->single()->total ?? 0);

        // KPI 3: Pasantes activos sin evaluación
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM datos_pasante dpa
            LEFT JOIN evaluaciones e ON e.pasante_id = dpa.usuario_id
            WHERE dpa.tutor_id = :tid AND dpa.estado_pasantia = 'Activo' AND e.id IS NULL
        ");
        $this->db->bind(':tid', $tutorId);
        $evaluacionesPendientes = (int)($this->db->single()->total ?? 0);

        // KPI 4: Horas supervisadas totales (pro-rata: días válidos × 8)
        $this->db->query("
            SELECT COALESCE(SUM(dias_validos), 0) * 8 AS total
            FROM (
                SELECT a.pasante_id, COUNT(*) AS dias_validos
                FROM asistencias a
                INNER JOIN datos_pasante dpa ON dpa.usuario_id = a.pasante_id
                WHERE dpa.tutor_id = :tid
                  AND a.estado IN ('Presente', 'Justificado')
                GROUP BY a.pasante_id
            ) AS sub
        ");
        $this->db->bind(':tid', $tutorId);
        $horasSupervisadas = (int)($this->db->single()->total ?? 0);

        // Lista de pasantes con progreso pro-rata y promedio de evaluaciones
        $this->db->query("
            SELECT
                dpa.usuario_id              AS pasante_id,
                dp.nombres,
                dp.apellidos,
                u.cedula,
                d.nombre                    AS departamento,
                COALESCE(prog.dias_validos, 0) * 8 AS horas_acumuladas,
                dpa.horas_meta,
                ROUND(LEAST(100, (COALESCE(prog.dias_validos, 0) * 8 / NULLIF(dpa.horas_meta, 0)) * 100), 1) AS progreso_pct,
                dpa.estado_pasantia,
                dpa.institucion_procedencia AS institucion,
                dpa.fecha_inicio_pasantia   AS fecha_inicio,
                AVG(e.promedio_final)       AS promedio_eval
            FROM datos_pasante dpa
            INNER JOIN usuarios          u  ON u.id  = dpa.usuario_id
            LEFT  JOIN datos_personales  dp ON dp.usuario_id = dpa.usuario_id
            LEFT  JOIN departamentos     d  ON d.id  = dpa.departamento_asignado_id
            LEFT  JOIN evaluaciones      e  ON e.pasante_id  = dpa.usuario_id
            LEFT  JOIN (
                SELECT pasante_id, COUNT(*) AS dias_validos
                FROM asistencias
                WHERE estado IN ('Presente', 'Justificado')
                GROUP BY pasante_id
            ) AS prog ON prog.pasante_id = dpa.usuario_id
            WHERE dpa.tutor_id = :tid
            GROUP BY dpa.usuario_id
            ORDER BY
                FIELD(dpa.estado_pasantia, 'Activo','Pendiente','Sin Asignar','Finalizado','Retirado'),
                dp.apellidos ASC
            LIMIT 20
        ");
        $this->db->bind(':tid', $tutorId);
        $misPasantes = $this->db->resultSet();

        $this->view('tutor/dashboard', [
            'title'                  => 'Panel de Tutor',
            'role'                   => 'Tutor',
            'user_name'              => Session::get('user_name'),
            'totalPasantes'          => $totalPasantes,
            'pasantesActivos'        => $pasantesActivos,
            'evaluacionesPendientes' => $evaluacionesPendientes,
            'horasSupervisadas'      => $horasSupervisadas,
            'misPasantes'            => $misPasantes,
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // Lista completa de mis pasantes
    // GET /tutor/pasantes
    // ────────────────────────────────────────────────────────────
    public function pasantes(): void {
        $tutorId = (int)Session::get('user_id');

        $this->db->query("
            SELECT
                dpa.usuario_id              AS pasante_id,
                dp.nombres,
                dp.apellidos,
                u.cedula,
                d.nombre                    AS departamento,
                COALESCE(prog.dias_validos, 0)         AS dias_acumulados,
                COALESCE(prog.dias_validos, 0) * 8     AS horas_acumuladas,
                dpa.horas_meta,
                ROUND(LEAST(100, (COALESCE(prog.dias_validos, 0) * 8 / NULLIF(dpa.horas_meta, 0)) * 100), 1) AS progreso_pct,
                dpa.estado_pasantia,
                dpa.fecha_inicio_pasantia   AS fecha_inicio,
                dpa.fecha_fin_estimada      AS fecha_fin,
                COALESCE(i.nombre, dpa.institucion_procedencia, 'No especificada') AS institucion,
                COALESCE(ult.ultima_asistencia, '—') AS ultima_asistencia,
                COALESCE(ult.ultimo_estado, '—')     AS ultimo_estado,
                ROUND(AVG(e.promedio_final), 2)       AS promedio_eval,
                COUNT(DISTINCT e.id)                  AS total_evaluaciones
            FROM datos_pasante dpa
            INNER JOIN usuarios u ON u.id = dpa.usuario_id
            LEFT  JOIN datos_personales dp ON dp.usuario_id = dpa.usuario_id
            LEFT  JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            LEFT  JOIN instituciones i ON
                dpa.institucion_procedencia REGEXP '^[0-9]+$'
                AND i.id = CAST(dpa.institucion_procedencia AS UNSIGNED)
            LEFT  JOIN evaluaciones e ON e.pasante_id = dpa.usuario_id
            LEFT  JOIN (
                SELECT pasante_id, COUNT(*) AS dias_validos
                FROM asistencias
                WHERE estado IN ('Presente', 'Justificado')
                GROUP BY pasante_id
            ) AS prog ON prog.pasante_id = dpa.usuario_id
            LEFT  JOIN (
                SELECT pasante_id,
                       MAX(fecha) AS ultima_asistencia,
                       SUBSTRING_INDEX(GROUP_CONCAT(estado ORDER BY fecha DESC, id DESC SEPARATOR '|'), '|', 1) AS ultimo_estado
                FROM asistencias
                GROUP BY pasante_id
            ) AS ult ON ult.pasante_id = dpa.usuario_id
            WHERE dpa.tutor_id = :tid
            GROUP BY dpa.usuario_id
            ORDER BY
                FIELD(dpa.estado_pasantia, 'Activo','Pendiente','Sin Asignar','Finalizado','Retirado'),
                dp.apellidos ASC
        ");
        $this->db->bind(':tid', $tutorId);
        $pasantes = $this->db->resultSet();

        // KPI extra: activos sin ninguna evaluación
        $this->db->query("
            SELECT COUNT(*) AS total
            FROM datos_pasante dpa
            LEFT JOIN evaluaciones e ON e.pasante_id = dpa.usuario_id
            WHERE dpa.tutor_id = :tid AND dpa.estado_pasantia = 'Activo' AND e.id IS NULL
        ");
        $this->db->bind(':tid', $tutorId);
        $pendientesEval = (int)($this->db->single()->total ?? 0);

        // KPI extra: % puntualidad del mes actual (llegadas a tiempo vs total presente)
        $this->db->query("
            SELECT
                COUNT(*) AS total,
                SUM(CASE WHEN TIME(COALESCE(a.hora_entrada, a.hora_registro)) <= '08:05:00' THEN 1 ELSE 0 END) AS a_tiempo
            FROM asistencias a
            INNER JOIN datos_pasante dpa ON dpa.usuario_id = a.pasante_id
            WHERE dpa.tutor_id = :tid
              AND a.estado IN ('Presente','Justificado')
              AND a.fecha >= DATE_FORMAT(NOW(), '%Y-%m-01')
        ");
        $this->db->bind(':tid', $tutorId);
        $puntRow = $this->db->single();
        $pctPuntualidad = ($puntRow && $puntRow->total > 0)
            ? (int)round($puntRow->a_tiempo / $puntRow->total * 100)
            : 0;

        $this->view('tutor/mis_pasantes', [
            'title'           => 'Mis Pasantes',
            'pasantes'        => $pasantes,
            'total'           => count($pasantes),
            'activos'         => count(array_filter($pasantes, fn($p) => $p->estado_pasantia === 'Activo')),
            'pendientesEval'  => $pendientesEval,
            'pctPuntualidad'  => $pctPuntualidad,
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // Ficha individual de un pasante
    // GET /tutor/perfil/{id}
    // ────────────────────────────────────────────────────────────
    public function perfil($pasanteId = null): void {
        $tutorId   = (int)Session::get('user_id');
        $pasanteId = (int)($pasanteId ?? 0);

        if ($pasanteId <= 0) {
            $this->redirect('tutor/pasantes');
            return;
        }

        // Verificar que el pasante pertenece a este tutor
        $this->db->query("
            SELECT
                u.id, u.cedula, dp.nombres, dp.apellidos, dp.telefono,
                d.nombre AS departamento,
                COALESCE(i.nombre, dpa.institucion_procedencia, 'No especificada') AS institucion,
                dpa.estado_pasantia,
                dpa.fecha_inicio_pasantia AS fecha_inicio,
                dpa.fecha_fin_estimada    AS fecha_fin,
                dpa.horas_meta,
                dpa.tutor_id
            FROM usuarios u
            INNER JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT  JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            LEFT  JOIN instituciones i ON
                dpa.institucion_procedencia REGEXP '^[0-9]+$'
                AND i.id = CAST(dpa.institucion_procedencia AS UNSIGNED)
            WHERE u.id = :pid AND u.rol_id = 3
            LIMIT 1
        ");
        $this->db->bind(':pid', $pasanteId);
        $pasante = $this->db->single();

        if (!$pasante || (int)($pasante->tutor_id ?? 0) !== $tutorId) {
            $this->redirect('tutor/pasantes');
            return;
        }

        // Progreso pro-rata
        require_once APPROOT . '/models/AsistenciaModel.php';
        $asistenciaModel = new AsistenciaModel($this->db);
        $progreso = $asistenciaModel->calcularProgresoProRata($pasanteId, (int)($pasante->horas_meta ?? 1440));
        $calendario = null;
        if ($pasante->fecha_inicio) {
            $calendario = $asistenciaModel->calcularProgresoPorCalendario($pasante->fecha_inicio, (int)($pasante->horas_meta ?? 1440));
        }

        // Historial de asistencias (últimos 60 registros)
        $historial = $asistenciaModel->obtenerHistorialCompletoPasante($pasanteId, 60);

        // Evaluaciones de este pasante hechas por este tutor
        $this->db->query("
            SELECT id, fecha_evaluacion, lapso_academico, promedio_final, observaciones
            FROM evaluaciones
            WHERE pasante_id = :pid
            ORDER BY fecha_evaluacion DESC
            LIMIT 10
        ");
        $this->db->bind(':pid', $pasanteId);
        $evaluaciones = $this->db->resultSet();

        $this->view('tutor/perfil_pasante', [
            'title'      => 'Perfil — ' . trim(($pasante->nombres ?? '') . ' ' . ($pasante->apellidos ?? '')),
            'pasante'    => $pasante,
            'progreso'   => $progreso,
            'calendario' => $calendario,
            'historial'  => $historial,
            'evaluaciones' => $evaluaciones,
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // Asistencias de mis pasantes (diaria/semanal)
    // GET /tutor/asistencias
    // ────────────────────────────────────────────────────────────
    public function asistencias(): void {
        $tutorId = (int)Session::get('user_id');
        $hoy     = date('Y-m-d');
        $vista   = $_GET['vista'] ?? 'diaria';

        $fechaInicio = $hoy;
        $fechaFin    = $hoy;

        $paramsUrl = [
            'fecha'  => $_GET['fecha']  ?? $hoy,
            'semana' => $_GET['semana'] ?? date('W'),
            'anio'   => $_GET['anio']   ?? date('Y'),
        ];

        if ($vista === 'diaria') {
            $fechaInicio = $paramsUrl['fecha'];
            $fechaFin    = $paramsUrl['fecha'];
        } elseif ($vista === 'semanal') {
            $dto = new DateTime();
            $dto->setISODate((int)$paramsUrl['anio'], (int)$paramsUrl['semana']);
            $fechaInicio = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $fechaFin = $dto->format('Y-m-d');
        }

        // Registros de asistencia para todos mis pasantes en el rango
        $this->db->query("
            SELECT
                a.id, a.fecha, a.hora_registro, a.estado, a.metodo,
                a.motivo_justificacion,
                u.id   AS pasante_id,
                u.cedula,
                dp.nombres, dp.apellidos,
                d.nombre AS departamento_nombre
            FROM asistencias a
            INNER JOIN usuarios u ON u.id = a.pasante_id
            LEFT  JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT  JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos d ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE dpa.tutor_id = :tid
              AND a.fecha >= :fi AND a.fecha <= :ff
            ORDER BY a.fecha DESC, a.hora_registro DESC
        ");
        $this->db->bind(':tid', $tutorId);
        $this->db->bind(':fi', $fechaInicio);
        $this->db->bind(':ff', $fechaFin);
        $registros = $this->db->resultSet();

        // Todos mis pasantes activos (para saber quién no marcó)
        $this->db->query("
            SELECT u.id, u.cedula, dp.nombres, dp.apellidos, d.nombre AS departamento_nombre
            FROM datos_pasante dpa
            INNER JOIN usuarios u ON u.id = dpa.usuario_id
            LEFT  JOIN datos_personales dp ON dp.usuario_id = dpa.usuario_id
            LEFT  JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            WHERE dpa.tutor_id = :tid AND dpa.estado_pasantia = 'Activo'
            ORDER BY dp.apellidos ASC
        ");
        $this->db->bind(':tid', $tutorId);
        $misPasantes = $this->db->resultSet();

        // Calcular KPIs diarios
        $presentes    = count(array_filter($registros, fn($r) => $r->estado === 'Presente'));
        $ausentes     = count(array_filter($registros, fn($r) => $r->estado === 'Ausente'));
        $justificados = count(array_filter($registros, fn($r) => $r->estado === 'Justificado'));
        $sinMarcar    = [];

        if ($vista === 'diaria') {
            $marcadosIds = array_map(fn($r) => (int)$r->pasante_id, $registros);
            $sinMarcar   = array_filter($misPasantes, fn($p) => !in_array((int)$p->id, $marcadosIds));
            $ausentes    += count($sinMarcar);
        }

        // Datos semanales (mapa de calor)
        $datosSemanales = [];
        $navSemana = [];
        if ($vista === 'semanal') {
            foreach ($misPasantes as $p) {
                $depto = $p->departamento_nombre ?? 'Sin Asignar';
                if (!isset($datosSemanales[$depto])) $datosSemanales[$depto] = [];
                $datosSemanales[$depto][$p->id] = [
                    'nombre'  => trim(($p->apellidos ?? '') . ', ' . ($p->nombres ?? '')),
                    'dias'    => [1 => '-', 2 => '-', 3 => '-', 4 => '-', 5 => '-'],
                    'totales' => ['P' => 0, 'A' => 0, 'J' => 0],
                ];
            }
            foreach ($registros as $reg) {
                $depto     = $reg->departamento_nombre ?? 'Sin Asignar';
                $pid       = $reg->pasante_id;
                $diaSemana = date('N', strtotime($reg->fecha));
                if ($diaSemana <= 5 && isset($datosSemanales[$depto][$pid])) {
                    if (stripos($reg->estado, 'Presente') !== false) {
                        $datosSemanales[$depto][$pid]['dias'][$diaSemana] = 'P';
                        $datosSemanales[$depto][$pid]['totales']['P']++;
                    } elseif (stripos($reg->estado, 'Ausente') !== false) {
                        $datosSemanales[$depto][$pid]['dias'][$diaSemana] = 'A';
                        $datosSemanales[$depto][$pid]['totales']['A']++;
                    } elseif (stripos($reg->estado, 'Justificado') !== false) {
                        $datosSemanales[$depto][$pid]['dias'][$diaSemana] = 'J';
                        $datosSemanales[$depto][$pid]['totales']['J']++;
                    }
                }
            }
            $fechaBase = new DateTime();
            $fechaBase->setISODate((int)$paramsUrl['anio'], (int)$paramsUrl['semana']);
            $ant = clone $fechaBase; $ant->modify('-1 week');
            $sig = clone $fechaBase; $sig->modify('+1 week');
            $navSemana = [
                'ant_url' => URLROOT . "/tutor/asistencias?vista=semanal&semana={$ant->format('W')}&anio={$ant->format('Y')}",
                'sig_url' => URLROOT . "/tutor/asistencias?vista=semanal&semana={$sig->format('W')}&anio={$sig->format('Y')}",
                'texto'   => "Semana {$paramsUrl['semana']} — {$paramsUrl['anio']}",
            ];
        }

        $this->view('tutor/asistencias', [
            'title'         => 'Asistencias de Mis Pasantes',
            'vista'         => $vista,
            'paramsUrl'     => $paramsUrl,
            'fechaInicio'   => $fechaInicio,
            'fechaFin'      => $fechaFin,
            'hoy'           => $hoy,
            'registros'     => $registros,
            'misPasantes'   => $misPasantes,
            'sinMarcar'     => array_values($sinMarcar),
            'presentes'     => $presentes,
            'ausentes'      => $ausentes,
            'justificados'  => $justificados,
            'totalActivos'  => count($misPasantes),
            'datosSemanales'=> $datosSemanales,
            'navSemana'     => $navSemana,
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // Dashboard de Puntualidad
    // GET /tutor/puntualidad?rango=mes|semana|hoy|todo
    // ────────────────────────────────────────────────────────────
    public function puntualidad(): void {
        $tutorId = (int)Session::get('user_id');

        require_once APPROOT . '/helpers/RetardoHelper.php';

        // Rango de fechas
        $rangosValidos = ['hoy', 'semana', 'mes', 'todo'];
        $rango = in_array($_GET['rango'] ?? '', $rangosValidos) ? $_GET['rango'] : 'mes';
        $hoy   = date('Y-m-d');

        switch ($rango) {
            case 'hoy':
                $fi = $hoy; $ff = $hoy;
                $rangoLabel = 'Hoy (' . date('d/m/Y') . ')';
                break;
            case 'semana':
                $lunes = date('Y-m-d', strtotime('monday this week'));
                $fi = $lunes; $ff = date('Y-m-d', strtotime('sunday this week'));
                $rangoLabel = 'Esta semana';
                break;
            case 'todo':
                $fi = '2000-01-01'; $ff = $hoy;
                $rangoLabel = 'Todo el período';
                break;
            default: // mes
                $fi = date('Y-m-01'); $ff = date('Y-m-t');
                $rangoLabel = 'Este mes (' . date('F Y') . ')';
                break;
        }

        // Registros de asistencia con hora programada (JOIN con asignaciones)
        $this->db->query("
            SELECT
                a.fecha,
                a.estado,
                COALESCE(a.hora_entrada, a.hora_registro) AS hora_entrada,
                COALESCE(asig.hora_entrada, '08:00:00')   AS hora_asignacion,
                u.id       AS pasante_id,
                dp.nombres,
                dp.apellidos,
                d.nombre   AS departamento
            FROM asistencias a
            INNER JOIN usuarios         u    ON u.id            = a.pasante_id
            LEFT  JOIN datos_personales dp   ON dp.usuario_id   = u.id
            LEFT  JOIN datos_pasante    dpa  ON dpa.usuario_id  = u.id
            LEFT  JOIN departamentos    d    ON d.id = dpa.departamento_asignado_id
            LEFT  JOIN asignaciones     asig ON asig.pasante_id = a.pasante_id
                                           AND asig.estado = 'activo'
            WHERE dpa.tutor_id = :tid
              AND a.fecha BETWEEN :fi AND :ff
            ORDER BY dp.apellidos ASC, a.fecha DESC
        ");
        $this->db->bind(':tid', $tutorId);
        $this->db->bind(':fi',  $fi);
        $this->db->bind(':ff',  $ff);
        $todosRegistros = $this->db->resultSet();

        // Agrupar por pasante y calcular resumen con RetardoHelper
        $porPasante = [];
        foreach ($todosRegistros as $r) {
            $pid = $r->pasante_id;
            if (!isset($porPasante[$pid])) {
                $porPasante[$pid] = [
                    'id'          => $pid,
                    'nombre'      => trim(($r->nombres ?? '') . ' ' . ($r->apellidos ?? '')),
                    'departamento'=> $r->departamento ?? 'Sin asignar',
                    'registros'   => [],
                ];
            }
            $porPasante[$pid]['registros'][] = $r;
        }

        $ranking = [];
        foreach ($porPasante as $pid => $datos) {
            $res = RetardoHelper::resumen($datos['registros']);
            $ranking[] = [
                'pasante_id'   => $pid,
                'nombre'       => $datos['nombre'],
                'departamento' => $datos['departamento'],
                'total'        => $res['total'],
                'a_tiempo'     => $res['a_tiempo'],
                'leve'         => $res['leve'],
                'severo'       => $res['severo'],
                'ausente'      => $res['ausente'],
                'pct_puntual'  => $res['pct_puntual'],
                'retraso_prom' => $res['retraso_prom'],
            ];
        }

        // Ordenar por % puntualidad ascendente (los más problemáticos primero)
        usort($ranking, fn($a, $b) => $a['pct_puntual'] <=> $b['pct_puntual']);

        // KPIs globales del tutor en el rango
        $totalRegistros = count($todosRegistros);
        $globalATiempo  = array_sum(array_column($ranking, 'a_tiempo'));
        $globalLeve     = array_sum(array_column($ranking, 'leve'));
        $globalSevero   = array_sum(array_column($ranking, 'severo'));
        $globalAusente  = array_sum(array_column($ranking, 'ausente'));
        $globalPct      = $totalRegistros > 0
            ? round(($globalATiempo / $totalRegistros) * 100, 1) : 0;

        // Historial reciente de retardos (para la tabla detalle)
        $historialRetardos = array_values(array_filter($todosRegistros, function($r) {
            $hora     = $r->hora_entrada ?? null;
            $horaAsig = $r->hora_asignacion ?? '08:00:00';
            $estado   = RetardoHelper::clasificar($hora, $horaAsig);
            return in_array($estado, [RetardoHelper::ESTADO_LEVE, RetardoHelper::ESTADO_SEVERO]);
        }));
        // Limitar a los 50 más recientes
        $historialRetardos = array_slice($historialRetardos, 0, 50);

        $this->view('tutor/puntualidad', [
            'title'            => 'Dashboard de Puntualidad',
            'rango'            => $rango,
            'rangoLabel'       => $rangoLabel,
            'fi'               => $fi,
            'ff'               => $ff,
            'ranking'          => $ranking,
            'historialRetardos'=> $historialRetardos,
            'kpis'             => [
                'total'       => $totalRegistros,
                'a_tiempo'    => $globalATiempo,
                'leve'        => $globalLeve,
                'severo'      => $globalSevero,
                'ausente'     => $globalAusente,
                'pct_puntual' => $globalPct,
            ],
        ]);
    }

    // ────────────────────────────────────────────────────────────
    // Resetear PIN de un pasante asignado a este tutor
    // POST /tutor/resetPin  (AJAX → JSON)
    // ────────────────────────────────────────────────────────────
    public function resetPin(): void {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $tutorId   = (int)Session::get('user_id');
        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        $nuevoPin  = trim($_POST['nuevo_pin'] ?? '');

        if (!$pasanteId || !preg_match('/^\d{4}$/', $nuevoPin)) {
            echo json_encode(['success' => false, 'message' => 'Pasante o PIN inválido (PIN debe tener 4 dígitos).']);
            exit;
        }

        // Verificar que el pasante sea del tutor
        $this->db->query("SELECT tutor_id FROM datos_pasante WHERE usuario_id = :pid LIMIT 1");
        $this->db->bind(':pid', $pasanteId);
        $dpa = $this->db->single();

        if (!$dpa || (int)($dpa->tutor_id ?? 0) !== $tutorId) {
            echo json_encode(['success' => false, 'message' => 'Este pasante no está asignado a ti.']);
            exit;
        }

        // Actualizar el PIN con bcrypt
        $pinHash = password_hash($nuevoPin, PASSWORD_BCRYPT);

        $this->db->query("UPDATE usuarios SET pin_asistencia = :pin WHERE id = :id");
        $this->db->bind(':pin', $pinHash);
        $this->db->bind(':id', $pasanteId);
        $ok = $this->db->execute();

        // Log en auditoría
        if ($ok) {
            $this->db->query("
                INSERT INTO bitacora (usuario_id, accion, descripcion, ip)
                VALUES (:uid, 'resetear_pin_pasante', CONCAT('Tutor reseteó PIN del pasante ID: ', :pid), :ip)
            ");
            $this->db->bind(':uid', $tutorId);
            $this->db->bind(':pid', $pasanteId);
            $this->db->bind(':ip', $_SERVER['REMOTE_ADDR'] ?? 'N/A');
            $this->db->execute();
        }

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? '✅ PIN actualizado correctamente.' : 'Error al actualizar el PIN.',
        ]);
        exit;
    }
}
