<?php
/**
 * ReportesController — Informes y Reportes SGP
 *
 * Exporta en 3 formatos:
 *  - JSON  (AJAX para DataTables Buttons / renderizado frontend)
 *  - PDF visual (DomPDF — kardex, evaluaciones)
 *  - PDF tabular (TCPDF — listados masivos, nóminas)
 */

class ReportesController extends Controller {
    private Database $db;

    public function __construct() {
        Session::start();
        AuthMiddleware::require();
        AuthMiddleware::verificarEstado();

        $config   = require APPROOT . '/config/config.php';
        $this->db = Database::getInstance(); // SGP-FIX-v2 [6/2.1] aplicado
    }

    public function index(): void {
        $this->db->query("SELECT id, nombre FROM departamentos WHERE activo = 1 ORDER BY nombre");
        $departamentos = $this->db->resultSet();
        $this->view('reportes/index', [
            'title'         => 'Informes y Reportes',
            'departamentos' => $departamentos,
        ]);
    }

    // ─── JSON ENDPOINTS ──────────────────────────────────────────────

    public function exportarPasantes(): void {
        header('Content-Type: application/json');
        try {
            $estado = $_POST['estado'] ?? 'todos';
            $depto  = $_POST['departamento_id'] ?? 'todos';

            $sql = "
                SELECT
                    u.cedula, CONCAT(dp.nombres,' ',dp.apellidos) AS nombre_completo,
                    dp.telefono, u.correo, dpa.institucion_procedencia,
                    dept.nombre AS departamento_nombre, dpa.estado_pasantia,
                    dpa.horas_acumuladas, dpa.horas_meta
                FROM usuarios u
                JOIN datos_personales dp ON u.id = dp.usuario_id
                LEFT JOIN datos_pasante    dpa  ON u.id  = dpa.usuario_id
                LEFT JOIN departamentos    dept ON dept.id = dpa.departamento_asignado_id
                WHERE u.rol_id = 3
            ";
            if ($estado !== 'todos') $sql .= " AND LOWER(dpa.estado_pasantia) = :estado";
            if ($depto  !== 'todos') $sql .= " AND dpa.departamento_asignado_id = :depto";
            $sql .= " ORDER BY dp.apellidos ASC";

            $this->db->query($sql);
            if ($estado !== 'todos') $this->db->bind(':estado', strtolower($estado));
            if ($depto  !== 'todos') $this->db->bind(':depto',  $depto);

            echo json_encode(['success' => true, 'data' => $this->db->resultSet()]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    public function exportarAsistencias(): void {
        header('Content-Type: application/json');
        try {
            $inicio = $_POST['fecha_inicio'] ?? date('Y-m-01');
            $fin    = $_POST['fecha_fin']    ?? date('Y-m-d');
            $depto  = $_POST['departamento_id'] ?? 'todos';

            $sql = "
                SELECT a.fecha, a.hora_entrada, a.hora_salida, a.estado,
                    a.horas_calculadas, u.cedula,
                    CONCAT(dp.nombres,' ',dp.apellidos) AS pasante_nombre,
                    dept.nombre AS departamento
                FROM asistencias a
                JOIN usuarios u ON a.pasante_id = u.id
                JOIN datos_personales dp ON u.id = dp.usuario_id
                JOIN datos_pasante dpa ON u.id = dpa.usuario_id
                LEFT JOIN departamentos dept ON dept.id = dpa.departamento_asignado_id
                WHERE a.fecha BETWEEN :inicio AND :fin
            ";
            if ($depto !== 'todos') $sql .= " AND dpa.departamento_asignado_id = :depto";
            $sql .= " ORDER BY a.fecha DESC, dp.apellidos ASC";

            $this->db->query($sql);
            $this->db->bind(':inicio', $inicio);
            $this->db->bind(':fin',    $fin);
            if ($depto !== 'todos') $this->db->bind(':depto', $depto);

            echo json_encode(['success' => true, 'data' => $this->db->resultSet()]);
        } catch (Exception $e) {
            echo json_encode(['success' => false, 'message' => $e->getMessage()]);
        }
    }

    // ─── DomPDF: Reporte Individual (PDF) ───────────────────────────
    public function pdfIndividual(int $pasanteId = 0): void {
        $pasanteId = $pasanteId ?: (int)($_GET['id'] ?? 0);
        if (!$pasanteId) { http_response_code(400); echo 'ID requerido'; return; }

        $download = ($_GET['download'] ?? '1') === '1';

        require_once APPROOT . '/lib/PdfGenerator.php';

        $this->db->query("
            SELECT dp.nombres, dp.apellidos, u.cedula, u.correo, u.rol_id, dp.telefono,
                dpa.estado_pasantia, dpa.horas_acumuladas, dpa.horas_meta,
                dpa.fecha_inicio_pasantia, dpa.fecha_fin_estimada,
                dpa.institucion_procedencia, d.nombre AS departamento, r.nombre AS nombre_rol, u.estado AS user_estado
            FROM usuarios u
            JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            LEFT JOIN roles r ON r.id = u.rol_id
            WHERE u.id = :id
        ");
        $this->db->bind(':id', $pasanteId);
        $p = $this->db->single();
        if (!$p) { http_response_code(404); echo 'Usuario no encontrado'; return; }

        $evals = [];
        if ($p->rol_id == 3) {
            $this->db->query("SELECT * FROM evaluaciones WHERE pasante_id = :id ORDER BY fecha_evaluacion DESC LIMIT 5");
            $this->db->bind(':id', $pasanteId);
            $evals = $this->db->resultSet();
        }

        $nombreCompleto = mb_strtoupper(htmlspecialchars(($p->nombres ?? '') . ' ' . ($p->apellidos ?? '')), 'UTF-8');
        
        $isPasante = ($p->rol_id == 3);
        $tituloReporte = $isPasante ? 'REPORTE DE PASANTÍA' : 'FICHA TÉCNICA DE USUARIO';
        
        $pathLogo = APPROOT . '/../public/img/logo.png';
        $pathCintillo = APPROOT . '/../public/img/cintillo.png';
        $bLogo = file_exists($pathLogo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($pathLogo)) : '';
        $bCintillo = file_exists($pathCintillo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($pathCintillo)) : '';

        $rolColor = $isPasante ? '#059669' : ($p->rol_id == 1 ? '#d97706' : '#2563eb'); 
        $estadoColor = ($p->user_estado === 'activo') ? '#059669' : '#dc2626';

        $progressHtml = '';
        if ($isPasante) {
            $horasMeta = (int)($p->horas_meta ?? 1440);
            $horasAcum = (int)($p->horas_acumuladas ?? 0);
            $porcentaje = $horasMeta > 0 ? min(100, round(($horasAcum / $horasMeta) * 100)) : 0;
            $progressHtml = "
            <div style='margin-bottom: 25px; background: #fff; padding: 18px; border: 1px solid #e2e8f0; border-radius: 12px;'>
                <h4 style='margin: 0 0 12px 0; color: #1e293b; font-size: 13px; text-transform: uppercase;'>Rendimiento de Pasantía</h4>
                <div style='font-size: 11px; color: #475569; margin-bottom: 6px;'>
                    Horas Acumuladas: <strong>{$horasAcum} / {$horasMeta} hrs</strong> ({$porcentaje}%)
                </div>
                <!-- Barra estática -> no animaciones en PDF -->
                <div style='width: 100%; height: 14px; background: #e2e8f0; border-radius: 7px; overflow: hidden;'>
                    <div style='width: {$porcentaje}%; height: 100%; background: {$rolColor};'></div>
                </div>
            </div>";
        }

        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>{$tituloReporte} - {$nombreCompleto}</title>
            <style>
                @page { margin: 40px 45px; }
                body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #334155; }
                
                .table-invisible { width: 100%; border-collapse: collapse; }
                .table-invisible td { padding: 12px 8px; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
                .label { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 4px; display: block; }
                .value { font-size: 12px; color: #0f172a; font-weight: normal; }
                
                .section-title { font-size: 12px; font-weight: bold; color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; margin: 25px 0 12px 0; text-transform: uppercase; }
                
                .datatable { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
                .datatable th { background: #f8fafc; color: #475569; font-weight: bold; text-align: left; padding: 8px; border: 1px solid #e2e8f0; font-size: 10px; }
                .datatable td { padding: 8px; border: 1px solid #e2e8f0; font-size: 10px; }
                
                #footer { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 2px solid #e2e8f0; padding-top: 15px; }
            </style>
        </head>
        <body>
            <!-- HEADER INSTITUCIONAL -->
            <table style='width: 100%; margin-bottom: 25px; border-bottom: 2px solid #1e3a8a; padding-bottom: 12px;'>
                <tr>
                    <td style='width: 25%; vertical-align: middle;'>
                        " . ($bLogo ? "<img src='{$bLogo}' style='height: 40px;'>" : "<h2 style='color:#1e3a8a;'>SGP</h2>") . "
                    </td>
                    <td style='width: 50%; text-align: center; vertical-align: middle;'>
                        <h2 style='margin: 0; color: #1e3a8a; font-size: 16px; letter-spacing: 1px;'>{$tituloReporte}</h2>
                        <div style='font-size: 9px; color: #64748b; margin-top: 4px;'>Emisión: " . date('d/m/Y h:i A') . "</div>
                    </td>
                    <td style='width: 25%; text-align: right; vertical-align: middle;'>
                        " . ($bCintillo ? "<img src='{$bCintillo}' style='height: 35px;'>" : "<b>Gob/Salud</b>") . "
                    </td>
                </tr>
            </table>

            <!-- BLOQUE DE IDENTIDAD -->
            <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 25px; text-align: center;'>
                <h1 style='margin: 0 0 14px 0; color: #1e3a8a; font-size: 22px; font-weight: bold; letter-spacing: 0.5px;'>{$nombreCompleto}</h1>
                <div>
                    <span style='display: inline-block; background: {$rolColor}; color: white; border-radius: 14px; padding: 5px 14px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-right: 8px;'>
                        " . htmlspecialchars($p->nombre_rol ?? 'USUARIO') . "
                    </span>
                    <span style='display: inline-block; background: {$estadoColor}; color: white; border-radius: 14px; padding: 5px 14px; font-size: 10px; font-weight: bold; text-transform: uppercase;'>
                        " . htmlspecialchars($p->user_estado ?? 'INACTIVO') . "
                    </span>
                </div>
            </div>

            <!-- CUADRÍCULA DE DATOS NORMALES (Bento Grid HTML) -->
            <div style='margin-bottom: 25px;'>
                <table class='table-invisible'>
                    <tr>
                        <td style='width: 50%;'>
                            <span class='label'>Cédula de Identidad</span>
                            <span class='value'>" . htmlspecialchars($p->cedula ?? '—') . "</span>
                        </td>
                        <td style='width: 50%;'>
                            <span class='label'>Correo Electrónico</span>
                            <span class='value'>" . htmlspecialchars($p->correo ?? '—') . "</span>
                        </td>
                    </tr>
                    <tr>
                        <td>
                            <span class='label'>Departamento Asignado</span>
                            <span class='value'>" . htmlspecialchars($p->departamento ?? 'Sin Asignar') . "</span>
                        </td>
                        <td>
                            <span class='label'>Teléfono</span>
                            <span class='value'>" . htmlspecialchars($p->telefono ?? '—') . "</span>
                        </td>
                    </tr>
                    " . ($isPasante ? "
                    <tr>
                        <td>
                            <span class='label'>Institución de Procedencia</span>
                            <span class='value'>" . htmlspecialchars($p->institucion_procedencia ?? '—') . "</span>
                        </td>
                        <td>
                            <span class='label'>Período de Pasantía</span>
                            <span class='value'>" . htmlspecialchars($p->fecha_inicio_pasantia ?? '—') . " al " . htmlspecialchars($p->fecha_fin_estimada ?? '—') . "</span>
                        </td>
                    </tr>
                    " : "") . "
                </table>
            </div>

            <!-- BLOQUE DE RENDIMIENTO (Kardex progresivo - Solo Pasantes) -->
            {$progressHtml}

            <!-- TABLAS ADICIONALES (Sólo pasantes - Evaluaciones) -->
            ";

        if ($isPasante) {
            $html .= "<div class='section-title'>Últimas Evaluaciones Registradas</div>";
            $html .= "<table class='datatable'>
                        <tr>
                            <th style='width: 25%'>Fecha</th>
                            <th style='width: 30%'>Fase Evaluada</th>
                            <th style='width: 15%; text-align: center;'>Promedio</th>
                            <th style='width: 30%'>Estado</th>
                        </tr>";
            if (empty($evals)) {
                $html .= "<tr><td colspan='4' style='text-align: center; color: #94a3b8; padding: 15px;'>El pasante aún no posee evaluaciones registradas en el sistema.</td></tr>";
            } else {
                foreach ($evals as $e) {
                    $html .= "<tr>
                        <td>" . htmlspecialchars($e->fecha_evaluacion ?? '—') . "</td>
                        <td>" . htmlspecialchars($e->tipo_evaluacion ?? '—') . "</td>
                        <td style='text-align: center; font-weight: bold;'>" . number_format($e->promedio_final ?? 0, 2) . " / 5.00</td>
                        <td>" . htmlspecialchars($e->estado ?? '—') . "</td>
                    </tr>";
                }
            }
            $html .= "</table>";
        }

        $html .= "
            <!-- PIE DE PÁGINA (Footer Formal) -->
            <div id='footer'>
                <table style='width: 100%;'>
                    <tr>
                        <td style='width: 50%; vertical-align: bottom;'>
                            <div style='width: 280px; border-top: 1px solid #475569; text-align: center; padding-top: 6px; font-size: 10px; font-weight: bold; color: #1e293b;'>
                                Firma y Sello<br>
                                <span style='font-size: 9px; font-weight: normal; color: #64748b;'>Coordinación de RRHH / Sistemas</span>
                            </div>
                        </td>
                        <td style='width: 50%; text-align: right; vertical-align: bottom; color: #94a3b8; font-size: 9px;'>
                            Documento generado automáticamente por el SGP.<br>".date('d/m/Y H:i:s')."
                        </td>
                    </tr>
                </table>
            </div>

        </body>
        </html>
        ";

        (new PdfGenerator())->renderDomPdf($html, 'REPORTE_' . htmlspecialchars($p->cedula ?? $pasanteId) . '_' . date('Ymd_Hi') . '.pdf', $download);
    }

    // ─── PhpSpreadsheet: Reporte Individual (Excel) ───────────────────
    public function exportarExcel(int $pasanteId = 0): void {
        $pasanteId = $pasanteId ?: (int)($_GET['id'] ?? 0);
        if (!$pasanteId) { http_response_code(400); echo 'ID requerido'; return; }

        require_once APPROOT . '/../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Reporte de Pasantía');

        $this->db->query("
            SELECT dp.nombres, dp.apellidos, u.cedula, u.correo, dpa.horas_acumuladas, dpa.horas_meta, 
                   d.nombre AS departamento, dpa.estado_pasantia
            FROM usuarios u
            JOIN datos_personales dp ON dp.usuario_id = u.id
            LEFT JOIN datos_pasante dpa ON dpa.usuario_id = u.id
            LEFT JOIN departamentos d ON d.id = dpa.departamento_asignado_id
            WHERE u.id = :id
        ");
        $this->db->bind(':id', $pasanteId);
        $p = $this->db->single();
        if (!$p) { http_response_code(404); echo 'Usuario no encontrado'; return; }

        // Estilos básicos
        $sheet->setCellValue('A1', 'SGP - REPORTE DE PASANTÍAS');
        $sheet->mergeCells('A1:D1');
        $sheet->getStyle('A1')->getFont()->setBold(true)->setSize(14);

        $sheet->setCellValue('A3', 'Pasante:');
        $sheet->setCellValue('B3', ($p->nombres ?? '') . ' ' . ($p->apellidos ?? ''));
        $sheet->setCellValue('A4', 'Cédula:');
        $sheet->setCellValue('B4', $p->cedula ?? '—');
        $sheet->setCellValue('A5', 'Departamento:');
        $sheet->setCellValue('B5', $p->departamento ?? 'Sin Asignar');
        
        $sheet->setCellValue('A7', 'Horas Acumuladas');
        $sheet->setCellValue('B7', 'Horas Meta');
        $sheet->setCellValue('C7', '% Progreso');
        $sheet->setCellValue('D7', 'Estado');

        $hrsAcum = (int)($p->horas_acumuladas ?? 0);
        $hrsMeta = (int)($p->horas_meta ?? 1440);
        $pct = $hrsMeta > 0 ? min(100, round(($hrsAcum / $hrsMeta) * 100)) : 0;

        $sheet->setCellValue('A8', $hrsAcum);
        $sheet->setCellValue('B8', $hrsMeta);
        $sheet->setCellValue('C8', $pct . '%');
        $sheet->setCellValue('D8', strtoupper($p->estado_pasantia ?? 'ACTIVO'));

        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="REPORTE_PASANTE_' . ($p->cedula ?? $pasanteId) . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    public function excelAnual(): void {
        require_once APPROOT . '/../vendor/autoload.php';
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Nómina de Pasantes');

        $this->db->query("
            SELECT u.cedula, CONCAT(dp.nombres,' ',dp.apellidos) AS nombre,
                dpa.estado_pasantia, dpa.horas_acumuladas, dpa.horas_meta, 
                dept.nombre AS departamento
            FROM usuarios u
            JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            LEFT JOIN departamentos dept ON dept.id = dpa.departamento_asignado_id
            WHERE u.rol_id = 3
            ORDER BY dp.apellidos
        ");
        $data = $this->db->resultSet();

        // Encabezados
        $headers = ['Cédula', 'Nombre Completo', 'Departamento', 'Estado', 'Horas Acum.', 'Meta', '%'];
        $column = 'A';
        foreach ($headers as $h) {
            $sheet->setCellValue($column . '1', $h);
            $sheet->getStyle($column . '1')->getFont()->setBold(true);
            $column++;
        }

        // Datos
        $row = 2;
        foreach ($data as $r) {
            $hrsAcum = (int)($r->horas_acumuladas ?? 0);
            $hrsMeta = (int)($r->horas_meta ?? 1440);
            $pct = $hrsMeta > 0 ? min(100, round(($hrsAcum / $hrsMeta) * 100)) : 0;

            $sheet->setCellValue('A' . $row, $r->cedula);
            $sheet->setCellValue('B' . $row, $r->nombre);
            $sheet->setCellValue('C' . $row, $r->departamento ?? 'Sin Asignar');
            $sheet->setCellValue('D' . $row, strtoupper($r->estado_pasantia ?? 'ACTIVO'));
            $sheet->setCellValue('E' . $row, $hrsAcum);
            $sheet->setCellValue('F' . $row, $hrsMeta);
            $sheet->setCellValue('G' . $row, $pct . '%');
            $row++;
        }

        // Auto-size columnas
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        if (ob_get_length()) ob_end_clean();
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="nomina_anual_pasantes_' . date('Ymd') . '.xlsx"');
        header('Cache-Control: max-age=0');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    /**
     * DomPDF: Informe de Evaluación Individual
     */
    public function evaluacion_pdf(int $evalId = 0): void {
        if (!$evalId) { http_response_code(400); echo 'ID de evaluación requerido'; return; }

        require_once APPROOT . '/lib/PdfGenerator.php';

        $this->db->query("
            SELECT e.*, 
                dp.nombres AS pasante_nombres, dp.apellidos AS pasante_apellidos, u.cedula AS pasante_cedula,
                tp.nombres AS tutor_nombres, tp.apellidos AS tutor_apellidos
            FROM evaluaciones e
            JOIN usuarios u ON e.pasante_id = u.id
            JOIN datos_personales dp ON u.id = dp.usuario_id
            JOIN usuarios ut ON e.tutor_id = ut.id
            JOIN datos_personales tp ON ut.id = tp.usuario_id
            WHERE e.id = :id
        ");
        $this->db->bind(':id', $evalId);
        $ev = $this->db->single();

        if (!$ev) { http_response_code(404); echo 'Evaluación no encontrada'; return; }

        $pNom = mb_strtoupper(($ev->pasante_nombres ?? '') . ' ' . ($ev->pasante_apellidos ?? ''), 'UTF-8');
        $tNom = ($ev->tutor_nombres ?? '') . ' ' . ($ev->tutor_apellidos ?? '');
        $prom = number_format((float)($ev->promedio_final ?? 0), 2);

        // Estilos y Layout
        $html = "
        <!DOCTYPE html>
        <html lang='es'>
        <head>
            <meta charset='UTF-8'>
            <title>EVALUACIÓN - {$pNom}</title>
            <style>
                @page { margin: 40px 45px; }
                body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #334155; }
                .header { border-bottom: 2px solid #1e3a8a; padding-bottom: 15px; margin-bottom: 25px; }
                .section-title { font-size: 12px; font-weight: bold; color: #1e3a8a; border-bottom: 1px solid #e2e8f0; padding-bottom: 5px; margin: 25px 0 12px 0; text-transform: uppercase; }
                .criteria-table { width: 100%; border-collapse: collapse; margin-bottom: 15px; }
                .criteria-table td { padding: 10px; border: 1px solid #f1f5f9; font-size: 10px; }
                .criteria-label { font-weight: bold; width: 70%; }
                .criteria-value { text-align: center; font-weight: bold; color: #1e3a8a; width: 30%; font-size: 12px; }
                .kpi-box { background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 20px; text-align: center; margin-bottom: 30px; }
                .footer { position: fixed; bottom: -20px; left: 0; right: 0; border-top: 1px solid #e2e8f0; padding-top: 15px; text-align: center; font-size: 9px; color: #64748b; }
            </style>
        </head>
        <body>
            <div class='header'>
                <table style='width:100%'>
                    <tr>
                        <td style='font-size: 18px; font-weight: bold; color: #1e3a8a;'>INFORME DE EVALUACIÓN</td>
                        <td style='text-align: right; color: #64748b;'>SGP — Sistema de Gestión de Pasantes</td>
                    </tr>
                </table>
            </div>

            <div class='kpi-box'>
                <h1 style='margin: 0; font-size: 24px;'>{$pNom}</h1>
                <p style='margin: 5px 0; color: #64748b;'>Cédula: {$ev->pasante_cedula} | Fecha: " . date('d/m/Y', strtotime($ev->fecha_evaluacion)) . "</p>
                <div style='margin-top: 15px;'>
                    <span style='font-size: 14px; color: #64748b;'>Promedio Final:</span><br>
                    <span style='font-size: 36px; font-weight: 900; color: #1e3a8a;'>{$prom} / 5.00</span>
                </div>
            </div>

            <table style='width: 100%; margin-bottom: 20px;'>
                <tr>
                    <td style='width: 50%'><strong>Tutor Evaluador:</strong> {$tNom}</td>
                    <td style='width: 50%'><strong>Lapso Académico:</strong> " . ($ev->lapso_academico ?? '—') . "</td>
                </tr>
            </table>

            <div class='section-title'>Criterios de Evaluación</div>
            <table class='criteria-table'>
                <tr><td class='criteria-label'>Iniciativa y proactividad</td><td class='criteria-value'>{$ev->criterio_iniciativa} ★</td></tr>
                <tr><td class='criteria-label'>Interés por el aprendizaje</td><td class='criteria-value'>{$ev->criterio_interes} ★</td></tr>
                <tr><td class='criteria-label'>Conocimientos técnicos aplicados</td><td class='criteria-value'>{$ev->criterio_conocimiento} ★</td></tr>
                <tr><td class='criteria-label'>Capacidad de análisis</td><td class='criteria-value'>{$ev->criterio_analisis} ★</td></tr>
                <tr><td class='criteria-label'>Habilidades de comunicación</td><td class='criteria-value'>{$ev->criterio_comunicacion} ★</td></tr>
                <tr><td class='criteria-label'>Velocidad de aprendizaje</td><td class='criteria-value'>{$ev->criterio_aprendizaje} ★</td></tr>
                <tr><td class='criteria-label'>Compañerismo y actitud grupal</td><td class='criteria-value'>{$ev->criterio_companerismo} ★</td></tr>
                <tr><td class='criteria-label'>Cooperación institucional</td><td class='criteria-value'>{$ev->criterio_cooperacion} ★</td></tr>
                <tr><td class='criteria-label'>Puntualidad y asistencia</td><td class='criteria-value'>{$ev->criterio_puntualidad} ★</td></tr>
                <tr><td class='criteria-label'>Presentación personal</td><td class='criteria-value'>{$ev->criterio_presentacion} ★</td></tr>
                <tr><td class='criteria-label'>Calidad de las tareas desarrolladas</td><td class='criteria-value'>{$ev->criterio_desarrollo} ★</td></tr>
                <tr><td class='criteria-label'>Análisis de resultados obtenidos</td><td class='criteria-value'>{$ev->criterio_analisis_res} ★</td></tr>
                <tr><td class='criteria-label'>Conclusiones sobre el proyecto</td><td class='criteria-value'>{$ev->criterio_conclusiones} ★</td></tr>
                <tr><td class='criteria-label'>Recomendaciones y aportes</td><td class='criteria-value'>{$ev->criterio_recomendacion} ★</td></tr>
            </table>

            <div class='section-title'>Observaciones del Tutor</div>
            <div style='padding: 15px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 8px; font-size: 12px; line-height: 1.5;'>
                " . (!empty($ev->observaciones) ? htmlspecialchars($ev->observaciones) : '<em>Sin observaciones adicionales registradas.</em>') . "
            </div>

            <div class='footer'>
                <p>Documento generado electrónicamente por el SGP el " . date('d/m/Y H:i:s') . "</p>
            </div>
        </body>
        </html>
        ";

        (new PdfGenerator())->renderDomPdf($html, 'EVAL_PASANTE_' . ($ev->pasante_cedula ?? $evalId) . '.pdf');
    }

    // ─── TCPDF: Informe de asistencias ───────────────────────────────

    public function pdfAsistencias(): void {
        require_once APPROOT . '/lib/PdfGenerator.php';

        $inicio = $_GET['inicio'] ?? date('Y-m-01');
        $fin    = $_GET['fin']    ?? date('Y-m-d');
        $depto  = $_GET['depto']  ?? 'todos';
        $download = ($_GET['download'] ?? '1') === '1';

        $sql = "
            SELECT a.fecha, CONCAT(dp.nombres,' ',dp.apellidos) AS pasante,
                u.cedula, a.estado,
                COALESCE(a.horas_calculadas,0) AS horas, dept.nombre AS departamento
            FROM asistencias a
            JOIN usuarios u ON a.pasante_id = u.id
            JOIN datos_personales dp ON u.id = dp.usuario_id
            JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            LEFT JOIN departamentos dept ON dept.id = dpa.departamento_asignado_id
            WHERE a.fecha BETWEEN :inicio AND :fin
        ";
        if ($depto !== 'todos') $sql .= " AND dpa.departamento_asignado_id = :depto";
        $sql .= " ORDER BY a.fecha DESC, dp.apellidos ASC";

        $this->db->query($sql);
        $this->db->bind(':inicio', $inicio);
        $this->db->bind(':fin',    $fin);
        if ($depto !== 'todos') $this->db->bind(':depto', $depto);

        $filas = [];
        foreach ($this->db->resultSet() as $r) {
            $filas[] = [$r->fecha ?? '—', $r->pasante ?? '—', $r->cedula ?? '—', $r->estado ?? '—', ($r->horas).'h', $r->departamento ?? '—'];
        }

        (new PdfGenerator())->renderTcpdf(
            'Informe de Asistencias',
            ['Fecha','Pasante','Cédula','Estado','Horas','Departamento'],
            $filas,
            'asistencias_' . $inicio . '_' . $fin . '.pdf',
            "Período: {$inicio} al {$fin}",
            $download
        );
    }

    // ─── TCPDF: Nómina general de pasantes ───────────────────────────

    public function pdfNomina(): void {
        require_once APPROOT . '/lib/PdfGenerator.php';
        $download = ($_GET['download'] ?? '1') === '1';

        $this->db->query("
            SELECT u.cedula, CONCAT(dp.nombres,' ',dp.apellidos) AS nombre,
                dp.telefono, dpa.estado_pasantia,
                dpa.horas_acumuladas, dpa.horas_meta, dept.nombre AS departamento
            FROM usuarios u
            JOIN datos_personales dp ON u.id = dp.usuario_id
            LEFT JOIN datos_pasante dpa ON u.id = dpa.usuario_id
            LEFT JOIN departamentos dept ON dept.id = dpa.departamento_asignado_id
            WHERE u.rol_id = 3
            ORDER BY dpa.estado_pasantia, dp.apellidos
        ");

        $filas = [];
        foreach ($this->db->resultSet() as $r) {
            $filas[] = [
                $r->cedula ?? '—', $r->nombre ?? '—',
                $r->departamento ?? 'Sin asignar', $r->estado_pasantia ?? '—',
                ($r->horas_acumuladas ?? 0) . '/' . ($r->horas_meta ?? 0),
                $r->telefono ?? '—',
            ];
        }

        (new PdfGenerator())->renderTcpdf(
            'Nómina de Pasantes',
            ['Cédula','Nombre','Departamento','Estado','Horas','Teléfono'],
            $filas,
            'nomina_pasantes_' . date('Ymd') . '.pdf',
            'Registrados al ' . date('d/m/Y'),
            $download
        );
    }
}
