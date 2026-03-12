<?php
/**
 * AsistenciasController — Panel de Control de Asistencias (Admin)
 *
 * RUTAS:
 *   GET  /asistencias                → index()            Lista de hoy
 *   GET  /asistencias/historial      → historial()        Búsqueda por rango/pasante
 *   POST /asistencias/registro_manual → registro_manual() JSON — justificar/registrar manual
 *
 * @version 2.0 — Fase 6
 */
class AsistenciasController extends Controller
{
    private $db;
    private $asistenciaModel;

    public function __construct()
    {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        $config  = require '../app/config/config.php';
        $this->db = Database::getInstance(); // SGP-FIX-v2 [6/2.1] aplicado
        $this->asistenciaModel = $this->model('Asistencia');
    }

    // ────────────────────────────────────────────────────────────────
    // VISTA PRINCIPAL — Asistencias de Hoy
    // ────────────────────────────────────────────────────────────────

    /**
     * index() — Lista registros de asistencia según rango de fecha.
     * Soporta vistas Diaria (hoy), Semanal (semana actual) y Mensual (mes actual).
     */
    public function index(): void
    {
        $rolId = (int)Session::get('role_id');
        $userId = (int)Session::get('user_id');

        if ($rolId !== 1 && $rolId !== 2 && $rolId !== 3) { // solo Admin, Tutor y Pasante
            Session::setFlash('error', 'Sin permisos para esta sección.');
            $this->redirect('/dashboard');
            return;
        }

        $vista = $_GET['vista'] ?? 'diaria'; // 'diaria', 'semanal', 'mensual', 'anual'
        $hoy = date('Y-m-d');
        
        // Determinar fechas de inicio y fin según el rango
        $fechaInicio = $hoy;
        $fechaFin    = $hoy;
        $tituloRango = 'del día';

        $paramsUrl = [
            'fecha'  => $_GET['fecha'] ?? $hoy,
            'semana' => $_GET['semana'] ?? date('W'),
            'mes'    => str_pad($_GET['mes'] ?? date('m'), 2, '0', STR_PAD_LEFT),
            'anio'   => $_GET['anio'] ?? date('Y')
        ];

        // Inicializar variables para las vistas (evitar Undefined Warnings)
        $meses = ['01'=>'Enero','02'=>'Febrero','03'=>'Marzo','04'=>'Abril','05'=>'Mayo','06'=>'Junio','07'=>'Julio','08'=>'Agosto','09'=>'Septiembre','10'=>'Octubre','11'=>'Noviembre','12'=>'Diciembre'];
        $nombreMes  = $meses[$paramsUrl['mes']] ?? '';
        $anioActual = $paramsUrl['anio'];
        $urlAnt     = URLROOT . "/asistencias?vista=mensual&mes=" . str_pad(($paramsUrl['mes']==1?12:$paramsUrl['mes']-1),2,'0',STR_PAD_LEFT) . "&anio=" . ($paramsUrl['mes']==1?$paramsUrl['anio']-1:$paramsUrl['anio']);
        $urlSig     = URLROOT . "/asistencias?vista=mensual&mes=" . str_pad(($paramsUrl['mes']==12?1:$paramsUrl['mes']+1),2,'0',STR_PAD_LEFT) . "&anio=" . ($paramsUrl['mes']==12?$paramsUrl['anio']+1:$paramsUrl['anio']);

        // KPIs Anuales (Inicialización)
        $historicoAnual   = 0;
        $finalizadosAnual = 0;
        $enCursoAnual     = 0;

        if ($vista === 'diaria') {
            $fechaInicio = $paramsUrl['fecha'];
            $fechaFin    = $paramsUrl['fecha'];
            $tituloRango = 'del ' . date('d/m/Y', strtotime($fechaInicio));
        } elseif ($vista === 'semanal') {
            $dto = new DateTime();
            $dto->setISODate((int)$paramsUrl['anio'], (int)$paramsUrl['semana']);
            $fechaInicio = $dto->format('Y-m-d');
            $dto->modify('+6 days');
            $fechaFin = $dto->format('Y-m-d');
            $tituloRango = "de la semana " . $paramsUrl['semana'] . " del " . $paramsUrl['anio'];
        } elseif ($vista === 'mensual') {
            $fechaInicio = $paramsUrl['anio'] . '-' . $paramsUrl['mes'] . '-01';
            $fechaFin    = date('Y-m-t', strtotime($fechaInicio));
            $tituloRango = "de " . ($nombreMes) . " " . $anioActual;
        } elseif ($vista === 'anual') {
            $fechaInicio = $paramsUrl['anio'] . '-01-01';
            $fechaFin    = $paramsUrl['anio'] . '-12-31';
            $tituloRango = "del año " . $paramsUrl['anio'];
        }

        // Si es pasante, solo ve sus propios registros
        $wherePasante = "";
        $filtroBusquedaPasante = (int)($_GET['pasante_id'] ?? 0);

        if ($rolId === 3) {
            $wherePasante = " AND a.pasante_id = :uid_pasante ";
        } elseif ($filtroBusquedaPasante > 0) {
            $wherePasante = " AND a.pasante_id = :uid_filtro ";
        }

        // Registros en el rango con datos del pasante (3NF: JOINs a tablas normalizadas)
        $this->db->query("
            SELECT
                a.id,
                a.fecha,
                a.hora_registro,
                a.estado,
                a.metodo,
                a.motivo_justificacion,
                a.ruta_evidencia,
                u.id           AS pasante_id,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                d.nombre       AS departamento_nombre
            FROM asistencias a
            INNER JOIN usuarios          u   ON u.id          = a.pasante_id
            LEFT  JOIN datos_personales  dp  ON dp.usuario_id = u.id
            LEFT  JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos     d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE a.fecha >= :fecha_inicio AND a.fecha <= :fecha_fin $wherePasante
            ORDER BY a.fecha DESC, a.hora_registro DESC
        ");
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);
        if ($rolId === 3) {
            $this->db->bind(':uid_pasante', $userId);
        } elseif ($filtroBusquedaPasante > 0) {
            $this->db->bind(':uid_filtro', $filtroBusquedaPasante);
        }
        $registrosLista = $this->db->resultSet();

        // Obtener la cantidad de pasantes activos en total (para los cálculos y el select de 'filtro')
        $this->db->query("
            SELECT u.id, u.cedula, dp.nombres, dp.apellidos,
                   d.nombre AS departamento_nombre
            FROM   usuarios u
            LEFT JOIN datos_personales  dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos     d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE  u.rol_id = 3
              AND  u.estado = 'activo'
              AND  COALESCE(dpa.estado_pasantia, 'Sin Asignar') = 'Activo'
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
        ");
        $todosActivos = $this->db->resultSet();
        $totalActivos = count($todosActivos);

        // Cálculos dinámicos
        $presentes    = count(array_filter($registrosLista, fn($r) => $r->estado === 'Presente'));
        $justificados = count(array_filter($registrosLista, fn($r) => $r->estado === 'Justificado'));
        $ausentes     = count(array_filter($registrosLista, fn($r) => $r->estado === 'Ausente'));
        $sinMarcar    = [];
        $porcentajeAsistencia = -1;

        if ($vista === 'diaria') {
            // Ausentes HOY / Diaria son TODOS los activos que NO tienen registro ese día (ni p, ni j, ni a)
            $marcadasIds = array_map(fn($r) => (int)$r->pasante_id, $registrosLista);
            $sinMarcar = array_filter($todosActivos, fn($p) => !in_array((int)$p->id, $marcadasIds));
            // Actualizamos la métrica visual sumando los que no marcaron a los que están "Ausente" formalmente.
            $ausentes += count($sinMarcar);

            if ($totalActivos > 0) {
                $porcentajeAsistencia = round(($presentes + $justificados) / $totalActivos * 100);
            }
        }

        // ==========================================
        // PROCESADOR DE DATOS: VISTA SEMANAL
        // ==========================================
        $datosSemanales = [];
        $navSemana = [];
        
        if ($vista === 'semanal') {
            // 1. Inicializar la estructura con TODOS los pasantes activos (Incluso si no han marcado)
            foreach ($todosActivos as $pasante) {
                // Adaptado para usar objetos (como es costumbre de PDO en este proyecto)
                $depto = $pasante->departamento_nombre ?? 'Sin Asignar';
                $pasanteId = $pasante->id;
                $nombreCompleto = trim(($pasante->apellidos ?? '') . ', ' . ($pasante->nombres ?? ''));
            
                if (!isset($datosSemanales[$depto])) {
                    $datosSemanales[$depto] = [];
                }
                $datosSemanales[$depto][$pasanteId] = [
                    'nombre'  => $nombreCompleto,
                    'dias'    => [1 => '-', 2 => '-', 3 => '-', 4 => '-', 5 => '-'], // 1=Lun, 5=Vie
                    'totales' => ['P' => 0, 'A' => 0, 'J' => 0]
                ];
            }
            
            // 2. Rellenar el "Mapa de Calor" con los registros reales de la semana
            foreach ($registrosLista as $reg) {
                $depto = $reg->departamento_nombre ?? 'Sin Asignar';
                $pasanteId = $reg->pasante_id ?? null; 
                if ($pasanteId && isset($datosSemanales[$depto][$pasanteId])) {
                    $diaSemana = date('N', strtotime($reg->fecha)); // 1 (Lun) a 7 (Dom)
                    if ($diaSemana <= 5) { // Solo procesamos de Lunes a Viernes
                        $letra = '-';
                        if (stripos($reg->estado, 'Presente') !== false) { 
                            $letra = 'P'; 
                            $datosSemanales[$depto][$pasanteId]['totales']['P']++; 
                        } elseif (stripos($reg->estado, 'Ausente') !== false) { 
                            $letra = 'A'; 
                            $datosSemanales[$depto][$pasanteId]['totales']['A']++; 
                        } elseif (stripos($reg->estado, 'Justificado') !== false) { 
                            $letra = 'J'; 
                            $datosSemanales[$depto][$pasanteId]['totales']['J']++; 
                        }
                        $datosSemanales[$depto][$pasanteId]['dias'][$diaSemana] = $letra;
                    }
                }
            }
            
            // 3. Lógica de Paginación Semanal (Botones Ant y Sig)
            $semanaActual = $paramsUrl['semana'] ?? date('W');
            $anioActual = $paramsUrl['anio'] ?? date('Y');
            
            $fechaBase = new DateTime();
            $fechaBase->setISODate((int)$anioActual, (int)$semanaActual);
            $fechaAnt = clone $fechaBase;
            $fechaAnt->modify('-1 week');
            $fechaSig = clone $fechaBase;
            $fechaSig->modify('+1 week');
            
            $navSemana = [
                'ant_url' => URLROOT . "/asistencias?vista=semanal&semana=" . $fechaAnt->format('W') . "&anio=" . $fechaAnt->format('Y'),
                'sig_url' => URLROOT . "/asistencias?vista=semanal&semana=" . $fechaSig->format('W') . "&anio=" . $fechaSig->format('Y'),
                'texto'   => "Semana " . $semanaActual . " - " . $anioActual
            ];
        }

        // ==========================================
        // PROCESADOR DE DATOS: VISTA ANUAL (Kpis)
        // ==========================================
        if ($vista === 'anual') {
            $pasantesVistos = [];
            foreach ($registrosLista as $reg) {
                $pid = $reg->pasante_id;
                if (!isset($pasantesVistos[$pid])) {
                    $pasantesVistos[$pid] = ['presentes' => 0];
                }
                if (strtolower($reg->estado) === 'presente' || strtolower($reg->estado) === 'justificado') {
                    $pasantesVistos[$pid]['presentes']++;
                }
            }
            $historicoAnual = count($pasantesVistos);
            foreach ($pasantesVistos as $pData) {
                $hrs = $pData['presentes'] * 8;
                if ($hrs >= 1440) {
                    $finalizadosAnual++;
                } else {
                    $enCursoAnual++;
                }
            }
        }

        // ==========================================
        // PROCESADOR DE DATOS: VISTA MENSUAL
        // ==========================================
        $bentoMensual     = [];
        $pasantesFaltas   = [];
        $healthIndex      = 100;
        $totalEventos     = 0;
        $chartSemanasJson = "[]";
        $chartPctsJson    = "[]";
        $pasantesParaJS   = [];
        $resumenDeptosJS  = [];
        $calendarioJS     = [];
        $daysJS           = [];
        
        if ($vista === 'mensual') {
            $totalAsistencias = 0;
            $totalFaltasGlobal = 0;
            $totalJustificadosGlobal = 0;

            foreach($registrosLista as $reg) {
                $depto = $reg->departamento_nombre ?? 'Sin Asignar';
                $pid = $reg->pasante_id ?? 'P'.$reg->id;
                
                if(!isset($bentoMensual[$depto])) {
                    $bentoMensual[$depto] = [];
                }
                if(!isset($bentoMensual[$depto][$pid])) {
                    $rawApellidos = trim($reg->apellidos ?? '');
                    $rawNombres   = trim($reg->nombres ?? '');
                    $apellido1    = trim(explode(' ', $rawApellidos)[0] ?? 'A');
                    $nombre1      = trim(explode(' ', $rawNombres)[0]   ?? 'A');
                    $iniciales    = strtoupper(substr($nombre1, 0, 1) . substr($apellido1, 0, 1));
                    if(strlen(trim($iniciales)) < 2) $iniciales = strtoupper(substr($rawApellidos, 0, 2));
                    $nombreDisplay = ucwords(strtolower($rawNombres . ' ' . $rawApellidos));

                    $bentoMensual[$depto][$pid] = [
                        'nombre'   => $nombreDisplay,
                        'cedula'   => $reg->cedula ?? '',
                        'iniciales'=> $iniciales,
                        'presentes'   => 0,
                        'ausentes'    => 0,
                        'justificados'=> 0,
                        'history'     => []
                    ];
                }
                $estado = strtolower($reg->estado);
                if(strpos($estado, 'presente') !== false) {
                    $bentoMensual[$depto][$pid]['presentes']++;
                    array_unshift($bentoMensual[$depto][$pid]['history'], 'presente');
                } elseif(strpos($estado, 'ausente') !== false) {
                    $bentoMensual[$depto][$pid]['ausentes']++;
                    array_unshift($bentoMensual[$depto][$pid]['history'], 'ausente');
                } else {
                    $bentoMensual[$depto][$pid]['justificados']++;
                    array_unshift($bentoMensual[$depto][$pid]['history'], 'justificado');
                }
                if(count($bentoMensual[$depto][$pid]['history']) > 5) {
                    array_pop($bentoMensual[$depto][$pid]['history']);
                }
            }

            foreach($bentoMensual as $depto => &$pasantesObj) {
                foreach($pasantesObj as $pid => &$rm) {
                    $totalAsistencias += $rm['presentes'];
                    $totalFaltasGlobal += $rm['ausentes'];
                    $totalJustificadosGlobal += $rm['justificados'];
                    if($rm['ausentes'] > 0) {
                        $pasantesFaltas[] = [
                            'nombre' => $rm['nombre'], 
                            'depto' => $depto, 
                            'faltas' => $rm['ausentes'], 
                            'iniciales' => $rm['iniciales']
                        ];
                    }
                }
            }
            usort($pasantesFaltas, function($a, $b) { return $b['faltas'] <=> $a['faltas']; });
            
            $pasantesFaltas = array_slice($pasantesFaltas, 0, 3);
            $totalEventos = $totalAsistencias + $totalFaltasGlobal + $totalJustificadosGlobal;
            $healthIndex = $totalEventos > 0 ? round(($totalAsistencias / $totalEventos) * 100) : 100;

            // ===== ÁREA CHART: Datos por semana del mes =====
            $semanaData  = []; 
            if(!empty($registrosLista)) {
                foreach($registrosLista as $reg) {
                    $fechaReg = $reg->fecha ?? null;
                    if($fechaReg) {
                        $dia = (int)date('j', strtotime($fechaReg));
                        $semana = (int)ceil($dia / 7);
                        if(!isset($semanaData[$semana])) $semanaData[$semana] = ['pres'=>0,'total'=>0];
                        $semanaData[$semana]['total']++;
                        if(strpos(strtolower($reg->estado ?? ''), 'presente') !== false) {
                            $semanaData[$semana]['pres']++;
                        }
                    }
                }
            }
            $chartSemanas = []; $chartPcts = [];
            // Forzar 4 o 5 semanas según el mes
            $ultimoDiaMes = (int)date('t', strtotime($fechaInicio));
            $numSemanas = (int)ceil($ultimoDiaMes / 7);

            for($sw = 1; $sw <= $numSemanas; $sw++) {
                $chartSemanas[] = 'Sem ' . $sw;
                $d = $semanaData[$sw] ?? null;
                $chartPcts[] = ($d && $d['total'] > 0) ? round(($d['pres'] / $d['total']) * 100) : 0;
            }
            if(empty($chartPcts) || count(array_filter($chartPcts, fn($v) => $v !== null)) === 0) {
                $chartSemanas = ['Sem 1','Sem 2','Sem 3','Sem 4'];
                $chartPcts    = [$healthIndex, $healthIndex, $healthIndex, $healthIndex];
            }
            // Use JSON_HEX tags to prevent XSS issues
            $chartSemanasJson = json_encode($chartSemanas, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);
            $chartPctsJson    = json_encode($chartPcts, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP);

            // ===== JS DATA: Pasantes para la Tabla Maestra y Top List =====
            $pasantesParaJS = [];
            foreach($bentoMensual as $depto => $pasantes) {
                foreach($pasantes as $pid => $rm) {
                    $pasantesParaJS[] = [
                        'n'  => $rm['nombre'],
                        'ci' => $rm['cedula'],
                        'd'  => $depto,
                        'p'  => $rm['presentes'],
                        'f'  => $rm['ausentes'],
                        'j'  => $rm['justificados'],
                        'av' => $rm['iniciales'],
                        'c'  => 'linear-gradient(135deg, #3b82f6, #2563eb)' // Color base
                    ];
                }
            }

            // ===== JS DATA: Resumen por Departamento =====
            $resumenDeptosJS = [];
            foreach($bentoMensual as $depto => $pasantes) {
                $dp = 0; $df = 0; $dj = 0;
                foreach($pasantes as $rm) {
                    $dp += $rm['presentes']; $df += $rm['ausentes']; $dj += $rm['justificados'];
                }
                $resumenDeptosJS[$depto] = [
                    'p' => $dp, 'f' => $df, 'j' => $dj, 't' => ($dp + $df + $dj)
                ];
            }

            // SGP-FIX-v2 [1.3] aplicado — JSON_HEX_* flags para prevenir XSS via </script>
            $pasantesParaJSJson  = json_encode($pasantesParaJS,  JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);
            $resumenDeptosJSJson = json_encode($resumenDeptosJS, JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP | JSON_UNESCAPED_UNICODE);

            // ===== JS DATA: Calendario =====
            $calendarioJS = [];
            $daysJS = [];
            $numDays = (int)date('t', strtotime($fechaInicio));
            for($i=1; $i<=$numDays; $i++) { $daysJS[] = $i; }

            foreach($registrosLista as $reg) {
                $d = (int)date('j', strtotime($reg->fecha));
                $est = strtolower($reg->estado);
                $code = 'em';
                if(strpos($est, 'presente') !== false) $code = 'p';
                elseif(strpos($est, 'ausente') !== false) $code = 'a';
                elseif(strpos($est, 'justificado') !== false) $code = 'j';
                
                // Si ya hay un registro (p.ej. presente), no lo sobreescribimos con 'em'
                if(!isset($calendarioJS[$d]) || $code !== 'em') {
                    $calendarioJS[$d] = $code;
                }
            }
        }

        $this->view('asistencias/index', [
            'title'         => 'Asistencias — ' . ucfirst($vista),
            'vista'         => $vista,
            'paramsUrl'     => $paramsUrl, // Para repoblar los selects en la UI
            'filtroPasante' => $filtroBusquedaPasante,
            'fechaInicio'   => $fechaInicio,
            'fechaFin'      => $fechaFin,
            'tituloRango'   => $tituloRango,
            'hoy'           => $hoy,
            'registrosLista'=> $registrosLista,
            'sinMarcar'     => array_values($sinMarcar),
            'sinMarcarMes'  => array_values($sinMarcar), // Reutilizamos si no hay lógica específica
            'presentes'     => $presentes,
            'justificados'  => $justificados,
            'ausentes'      => $ausentes,
            'totalActivos'  => $totalActivos,
            'pasantesActivos' => $todosActivos, // para modals y listados
            'porcAsistencia'=> $porcentajeAsistencia,
            'datosSemanales'=> $datosSemanales,
            'navSemana'     => $navSemana,
            'bentoMensual'  => $bentoMensual,
            'pasantesFaltas'=> $pasantesFaltas,
            'healthIndex'   => $healthIndex,
            'totalEventosMensual'=> $totalEventos,
            'chartSemanasJson' => $chartSemanasJson,
            'chartPctsJson' => $chartPctsJson,
            'pasantesParaJS' => $pasantesParaJS,
            'resumenDeptosJS' => $resumenDeptosJS,
            'calendarioJS' => (object)$calendarioJS,
            'daysJS' => $daysJS,
            'nombreMes' => $nombreMes,
            'anioActual' => $anioActual,
            'urlAnt' => $urlAnt,
            'urlSig' => $urlSig,
            'historicoAnual' => $historicoAnual,
            'finalizadosAnual' => $finalizadosAnual,
            'enCursoAnual' => $enCursoAnual
        ]);
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINT JSON — Registro Manual / Justificación
    // ────────────────────────────────────────────────────────────────

    /**
     * registro_manual() — El Admin justifica una falta o registra manualmente.
     *
     * POST /asistencias/registro_manual
     * Body (form-data):
     *   pasante_id          int      requerido
     *   fecha               Y-m-d    requerido  (puede ser hoy o retroactivo)
     *   estado              'Presente'|'Justificado'
     *   motivo_justificacion text    requerido si estado = Justificado
     *   evidencia           file     opcional (imagen del récipe médico)
     */
    public function registro_manual(): void
    {
        $this->verifyCsrf(); // SGP-FIX-v2 [1.1] aplicado
        header('Content-Type: application/json');

        if ((int)Session::get('role_id') !== 1) {
            echo json_encode(['success' => false, 'message' => 'Solo el Administrador puede hacer esto.']);
            exit;
        }

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        // Captura de datos
        $pasanteId = (int)($_POST['pasante_id']           ?? 0);
        $fecha     = trim($_POST['fecha']                  ?? date('Y-m-d'));
        $estado    = trim($_POST['estado']                 ?? 'Justificado');
        $motivo    = trim($_POST['motivo_justificacion']   ?? '');

        // Validaciones
        if ($pasanteId <= 0) {
            echo json_encode(['success' => false, 'message' => 'Selecciona un pasante válido.']);
            exit;
        }

        if (!in_array($estado, ['Presente', 'Justificado', 'Ausente'])) {
            echo json_encode(['success' => false, 'message' => 'Estado no válido.']);
            exit;
        }

        if ($estado === 'Justificado' && !$motivo) {
            echo json_encode(['success' => false, 'message' => 'El motivo de justificación es obligatorio.']);
            exit;
        }

        if (!preg_match('/^\d{4}-\d{2}-\d{2}$/', $fecha)) {
            echo json_encode(['success' => false, 'message' => 'Formato de fecha inválido.']);
            exit;
        }

        // ── Upload de evidencia (récipe médico) ────────────────────
        $rutaEvidencia = null;

        if (!empty($_FILES['evidencia']['tmp_name'])) {
            $file      = $_FILES['evidencia'];
            $ext       = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
            $allowed   = ['jpg', 'jpeg', 'png', 'pdf', 'webp'];

            if (!in_array($ext, $allowed)) {
                echo json_encode(['success' => false, 'message' => 'Formato de archivo no permitido. Use JPG, PNG o PDF.']);
                exit;
            }

            // SGP-FIX-v2 [1.5] aplicado — Validar MIME real además de la extensión
            $allowedMimes = ['image/jpeg', 'image/png', 'image/webp', 'application/pdf'];
            $finfo        = new finfo(FILEINFO_MIME_TYPE);
            $mimeReal     = $finfo->file($file['tmp_name']);
            if (!in_array($mimeReal, $allowedMimes)) {
                echo json_encode(['success' => false, 'message' => 'Tipo de archivo no permitido (MIME inválido).']);
                exit;
            }

            if ($file['size'] > 5 * 1024 * 1024) { // 5 MB máx
                echo json_encode(['success' => false, 'message' => 'El archivo supera el límite de 5 MB.']);
                exit;
            }

            $uploadDir = '../public/uploads/evidencias/';
            if (!is_dir($uploadDir)) {
                mkdir($uploadDir, 0755, true);
            }

            $nombreArchivo   = 'evidencia_' . $pasanteId . '_' . str_replace('-', '', $fecha) . '_' . time() . '.' . $ext;
            $destinoCompleto = $uploadDir . $nombreArchivo;

            if (!move_uploaded_file($file['tmp_name'], $destinoCompleto)) {
                echo json_encode(['success' => false, 'message' => 'Error al subir el archivo.']);
                exit;
            }

            $rutaEvidencia = '/uploads/evidencias/' . $nombreArchivo;
        }

        $registroId = (int)($_POST['registro_id'] ?? 0);
        $existente = null;

        if ($registroId > 0) {
            // Edición de un registro existente por su ID explícito
            $this->db->query("SELECT id, estado FROM asistencias WHERE id = :id LIMIT 1");
            $this->db->bind(':id', $registroId);
            $existente = $this->db->single();
        } else {
            // ── Verificar si ya existe registro para esa fecha ─────────
            $this->db->query("
                SELECT id, estado FROM asistencias
                WHERE pasante_id = :pid AND fecha = :fecha
                LIMIT 1
            ");
            $this->db->bind(':pid',   $pasanteId);
            $this->db->bind(':fecha', $fecha);
            $existente = $this->db->single();
        }

        if ($existente) {
            // Actualizar el registro existente
            $this->db->query("
                UPDATE asistencias
                SET estado = :estado,
                    motivo_justificacion = :motivo,
                    ruta_evidencia       = COALESCE(:evidencia, ruta_evidencia),
                    metodo               = 'Manual'
                WHERE id = :id
            ");
            $this->db->bind(':estado',    $estado);
            $this->db->bind(':motivo',    $motivo ?: null);
            $this->db->bind(':evidencia', $rutaEvidencia);
            $this->db->bind(':id',        (int)$existente->id);
        } else {
            // Insertar nuevo registro manual
            $this->db->query("
                INSERT INTO asistencias
                    (pasante_id, fecha, hora_registro, estado, metodo, motivo_justificacion, ruta_evidencia)
                VALUES
                    (:pid, :fecha, :hora, :estado, 'Manual', :motivo, :evidencia)
            ");
            $this->db->bind(':pid',      $pasanteId);
            $this->db->bind(':fecha',    $fecha);
            $this->db->bind(':hora',     date('H:i:s'));
            $this->db->bind(':estado',   $estado);
            $this->db->bind(':motivo',   $motivo ?: null);
            $this->db->bind(':evidencia', $rutaEvidencia);
        }

        $ok = $this->db->execute();

        // ✅ PRO-RATA: El progreso se calcula dinámicamente desde la tabla 'asistencias'.
        // NO se modifica horas_acumuladas en datos_pasante — eliminamos el anti-patrón de suma/resta.

        echo json_encode([
            'success' => $ok,
            'message' => $ok
                ? ($existente ? '✅ Registro actualizado correctamente.' : '✅ Asistencia manual registrada.')
                : 'Error al guardar el registro.',
        ]);

        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINT JSON — Exportar Datos (CSV / PDF)
    // ────────────────────────────────────────────────────────────────

    /**
     * exportar_datos() — Devuelve los registros en formato JSON según el rango.
     * Usado por el frontend para generar CSV o PDF desde el cliente.
     */
    public function exportar_datos(): void
    {
        header('Content-Type: application/json');

        $rolId = (int)Session::get('role_id');
        $userId = (int)Session::get('user_id');

        if ($rolId !== 1 && $rolId !== 2 && $rolId !== 3) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos de exportación.']);
            exit;
        }

        $vista = $_GET['vista'] ?? 'diaria'; // 'diaria', 'semanal', 'mensual', 'anual'
        $hoy = date('Y-m-d');
        
        $fechaInicio = $hoy;
        $fechaFin    = $hoy;

        $paramsUrl = [
            'fecha'  => $_GET['fecha'] ?? $hoy,
            'semana' => $_GET['semana'] ?? date('W'),
            'mes'    => str_pad($_GET['mes'] ?? date('m'), 2, '0', STR_PAD_LEFT),
            'anio'   => $_GET['anio'] ?? date('Y')
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
        } elseif ($vista === 'mensual') {
            $fechaInicio = $paramsUrl['anio'] . '-' . $paramsUrl['mes'] . '-01';
            $fechaFin    = date('Y-m-t', strtotime($fechaInicio));
        } elseif ($vista === 'anual') {
            $fechaInicio = $paramsUrl['anio'] . '-01-01';
            $fechaFin    = $paramsUrl['anio'] . '-12-31';
        }

        $wherePasante = "";
        $filtroBusquedaPasante = (int)($_GET['pasante_id'] ?? 0);

        if ($rolId === 3) {
            $wherePasante = " AND a.pasante_id = :uid_pasante ";
        } elseif ($filtroBusquedaPasante > 0) {
            $wherePasante = " AND a.pasante_id = :uid_filtro ";
        }

        $this->db->query("
            SELECT
                a.id,
                a.fecha,
                a.hora_registro,
                a.estado,
                a.metodo,
                a.motivo_justificacion,
                u.cedula,
                dp.nombres,
                dp.apellidos,
                d.nombre AS departamento_nombre
            FROM asistencias a
            INNER JOIN usuarios          u   ON u.id          = a.pasante_id
            LEFT  JOIN datos_personales  dp  ON dp.usuario_id = u.id
            LEFT  JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT  JOIN departamentos     d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE a.fecha >= :fecha_inicio AND a.fecha <= :fecha_fin $wherePasante
            ORDER BY a.fecha DESC, a.hora_registro DESC
        ");
        
        $this->db->bind(':fecha_inicio', $fechaInicio);
        $this->db->bind(':fecha_fin', $fechaFin);
        if ($rolId === 3) {
            $this->db->bind(':uid_pasante', $userId);
        } elseif ($filtroBusquedaPasante > 0) {
            $this->db->bind(':uid_filtro', $filtroBusquedaPasante);
        }
        
        $registrosLista = $this->db->resultSet();

        echo json_encode([
            'success' => true,
            'vista'   => $vista,
            'data'    => $registrosLista
        ]);
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINT JSON — Buscador Autocomplete
    // ────────────────────────────────────────────────────────────────

    /**
     * buscar_pasantes() — Endpoint para el Autocomplete del Modal de Búsqueda
     */
    public function buscar_pasantes(): void
    {
        header('Content-Type: application/json');
        
        $term = $_GET['q'] ?? '';
        if (strlen(trim($term)) < 2) {
            echo json_encode(['success' => true, 'data' => []]);
            exit;
        }

        $termSql = "%" . trim($term) . "%";

        // Mismos filtros que pasantesActivos: Rol 3, Usuario activo, Pasantía activa
        $this->db->query("
            SELECT u.id, u.cedula, dp.nombres, dp.apellidos,
                   d.nombre AS departamento_nombre
            FROM   usuarios u
            LEFT JOIN datos_personales  dp  ON dp.usuario_id  = u.id
            LEFT JOIN datos_pasante     dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos     d   ON d.id = COALESCE(dpa.departamento_asignado_id, u.departamento_id)
            WHERE  u.rol_id = 3
              AND  u.estado = 'activo'
              AND  COALESCE(dpa.estado_pasantia, 'Sin Asignar') = 'Activo'
              AND  (u.cedula LIKE :q OR dp.nombres LIKE :q OR dp.apellidos LIKE :q)
            ORDER BY IFNULL(dp.apellidos, u.correo) ASC
            LIMIT 15
        ");
        $this->db->bind(':q', $termSql);
        $resultados = $this->db->resultSet();

        echo json_encode(['success' => true, 'data' => $resultados]);
        exit;
    }

    // ────────────────────────────────────────────────────────────────
    // ENDPOINT JSON — Anular Registro
    // ────────────────────────────────────────────────────────────────

    /**
     * anular_registro() — Borrado lógico o cambio a estado 'Anulado'
     */
    public function anular_registro(): void
    {
        $this->verifyCsrf(); // SGP-FIX-v2 [1.1] aplicado
        header('Content-Type: application/json');
        if ((int)Session::get('role_id') !== 1) {
            echo json_encode(['success' => false, 'message' => 'Sin permisos.']);
            exit;
        }
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $id = (int)($_POST['id'] ?? 0);
        if ($id <= 0) {
            echo json_encode(['success' => false, 'message' => 'ID de registro inválido.']);
            exit;
        }

        // ✅ PRO-RATA: No se restan horas al anular. El conteo Pro-Rata excluye estado 'Anulado' automáticamente.
        // Convertir a 'Anulado'
        $this->db->query("UPDATE asistencias SET estado = 'Anulado' WHERE id = :id");
        $this->db->bind(':id', $id);
        $ok = $this->db->execute();

        echo json_encode([
            'success' => $ok,
            'message' => $ok ? '✅ Asistencia anulada correctamente.' : 'Error al anular.'
        ]);
        exit;
    }

    // ==========================================
    // API AJAX: MÓDULO DE CONSULTA RÁPIDA
    // ==========================================

    /**
     * Endpoint 1: Buscador en vivo para el autocompletado del Modal
     */
    public function buscarPasanteAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $query = trim($_POST['query'] ?? '');
            
            // Llamamos al modelo para buscar coincidencias por cédula o nombre
            // IMPORTANTE: Asegúrate de crear este método en tu AsistenciaModel
            $resultados = $this->asistenciaModel->buscarPasanteLive($query);
            
            header('Content-Type: application/json');
            echo json_encode($resultados);
            exit; // Evitamos que se renderice cualquier vista HTML
        }
    }

    /**
     * Endpoint 2: Extrae todo el expediente y marcajes de un pasante
     */
    public function obtenerDatosAuditoriaAjax() {
        if ($_SERVER['REQUEST_METHOD'] == 'POST') {
            $idPasante = intval($_POST['id_pasante'] ?? 0);
            
            if ($idPasante > 0) {
                // A. Obtener el perfil (Nombres, Cédula, Depto, fecha_inicio, fecha_fin)
                $perfil = $this->asistenciaModel->obtenerPerfilParaAuditoria($idPasante);
                
                // B. Obtener el historial completo de marcajes
                $historial = $this->asistenciaModel->obtenerHistorialCompletoPasante($idPasante);

                // C. ✅ PRO-RATA: Calcular progreso dinámicamente desde la tabla asistencias
                //    horasMostradas = COUNT(Presente|Justificado) * 8
                //    porcentaje     = horasMostradas / 1440 * 100
                $horasMeta = (int)(($perfil->horas_meta ?? 0) > 0 ? $perfil->horas_meta : 1440);
                $proRata   = $this->asistenciaModel->calcularProgresoProRata($idPasante, $horasMeta);
                
                // D. ✅ CALENDARIO: Calcular progreso estrictamente por calendario (L-V)
                $fechaInicio = $perfil->fecha_inicio ?? date('Y-m-d');
                $calendario  = $this->asistenciaModel->calcularProgresoPorCalendario($fechaInicio, $horasMeta);
                
                header('Content-Type: application/json');
                echo json_encode([
                    'status'   => 'success',
                    'perfil'   => $perfil,
                    'historial'=> $historial,
                    'pro_rata' => [
                        'dias_presentes'  => $proRata->dias_presentes,
                        'horas_mostradas' => $proRata->horas_mostradas,
                        'horas_meta'      => $proRata->horas_meta,
                        'porcentaje'      => $proRata->porcentaje,
                    ],
                    'calendario' => [
                        'dias_habiles_transcurridos' => $calendario->dias_habiles_transcurridos,
                        'horas_calendario'           => $calendario->horas_calendario,
                        'porcentaje_calendario'      => $calendario->porcentaje_calendario,
                        'dias_habiles_restantes'     => $calendario->dias_habiles_restantes
                    ]
                ]);
            } else {
                header('Content-Type: application/json');
                echo json_encode(['status' => 'error', 'message' => 'ID inválido']);
            }
            exit;
        }
    }

    /**
     * Endpoint 3: obtenerResumenMensual() — Almanaque Inteligente (Heatmap)
     * Devuelve el historial de asistencias de un pasante en un mes específico.
     */
    public function obtenerResumenMensual(): void
    {
        header('Content-Type: application/json');
        
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Método no permitido.']);
            exit;
        }

        $pasanteId = (int)($_POST['pasante_id'] ?? 0);
        $mesAnio = trim($_POST['mes_anio'] ?? ''); // Formato YYYY-MM
        
        if ($pasanteId <= 0 || !preg_match('/^\d{4}-\d{2}$/', $mesAnio)) {
            echo json_encode(['success' => false, 'message' => 'Parámetros inválidos.']);
            exit;
        }

        $fechaInicio = $mesAnio . '-01';
        $fechaFin = date('Y-m-t', strtotime($fechaInicio));

        $this->db->query("
            SELECT fecha, estado 
            FROM asistencias 
            WHERE pasante_id = :pid 
              AND fecha >= :inicio AND fecha <= :fin
        ");
        $this->db->bind(':pid', $pasanteId);
        $this->db->bind(':inicio', $fechaInicio);
        $this->db->bind(':fin', $fechaFin);
        
        $registros = $this->db->resultSet();
        
        $datosGrid = [];
        foreach ($registros as $reg) {
            $datosGrid[$reg->fecha] = $reg->estado;
        }

        echo json_encode(['success' => true, 'datos' => $datosGrid]);
        exit;
    }
}
