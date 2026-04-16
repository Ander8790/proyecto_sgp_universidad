<?php
/**
 * PdfGenerator — Wrapper unificado para DomPDF y TCPDF
 *
 * USO:
 *   $pdf = new PdfGenerator();
 *   // Para reportes visuales (HTML → PDF)
 *   $pdf->renderDomPdf($htmlContent, 'reporte.pdf');
 *   // Para reportes tabulares masivos
 *   $pdf->renderTcpdf($titulo, $columnas, $filas, 'reporte.pdf');
 */

declare(strict_types=1);

class PdfGenerator
{
    private string $vendorPath;

    public function __construct()
    {
        $this->vendorPath = dirname(__DIR__, 2) . '/vendor';
    }

    // ─────────────────────────────────────────────────────────────────
    // DOM PDF — Reportes con diseño visual (HTML to PDF)
    // Ideal para: Kardex individual, informes de evaluación
    // ─────────────────────────────────────────────────────────────────

    /**
     * Genera un PDF a partir de HTML usando DomPDF y lo descarga.
     *
     * @param string $html     Contenido HTML completo del reporte
     * @param string $filename Nombre del archivo .pdf para descarga
     * @param bool   $download true = forzar descarga | false = inline (para pdf.js viewer)
     */
    public function renderDomPdf(string $html, string $filename = 'reporte.pdf', bool $download = true): void
    {
        $autoload = $this->vendorPath . '/autoload.php';
        if (!file_exists($autoload)) {
            throw new RuntimeException('Composer vendor no encontrado. Ejecuta: composer install');
        }
        require_once $autoload;

        $options = new \Dompdf\Options();
        $options->setIsRemoteEnabled(false);
        $options->setIsHtml5ParserEnabled(false); // parser HTML4 es suficiente y más rápido
        $options->setDefaultFont('Helvetica');     // fuente built-in PDF: carga en 0ms vs ~1s de DejaVu

        $dompdf = new \Dompdf\Dompdf($options);
        $dompdf->loadHtml($html, 'UTF-8');
        $dompdf->setPaper('A4', 'portrait');
        $dompdf->render();
        $dompdf->stream($filename, ['Attachment' => $download]);
    }

    // ─────────────────────────────────────────────────────────────────
    // TCP DF — Reportes tabulares masivos con datos exactos
    // Ideal para: Nóminas de pasantes, listados de asistencia completos
    // ─────────────────────────────────────────────────────────────────

    /**
     * Genera un PDF tabular con TCPDF.
     *
     * @param string  $titulo   Título del reporte
     * @param array   $columnas ['Cabecera 1', 'Cabecera 2', ...]
     * @param array   $filas    [['dato1','dato2'], ['dato3','dato4'], ...]
     * @param string  $filename Nombre del archivo
     * @param string  $subtitulo Subtítulo/descripción del reporte
     * @param bool    $download true = forzar descarga | false = inline
     */
    public function renderTcpdf(
        string $titulo,
        array  $columnas,
        array  $filas,
        string $filename    = 'reporte.pdf',
        string $subtitulo   = '',
        bool   $download    = true
    ): void {
        $autoload = $this->vendorPath . '/autoload.php';
        if (!file_exists($autoload)) {
            throw new RuntimeException('Composer vendor no encontrado. Ejecuta: composer install');
        }
        require_once $autoload;

        $pdf = new \TCPDF('P', 'mm', 'A4', true, 'UTF-8', false);

        // Metadatos
        $pdf->SetCreator('SGP — Sistema de Gestión de Pasantes');
        $pdf->SetAuthor('UPT Bolívar');
        $pdf->SetTitle($titulo);

        // Encabezado / Pie de página
        $pdf->setPrintHeader(false);
        $pdf->setPrintFooter(true);
        $pdf->SetFooterFont(['helvetica', '', 8]);
        $pdf->SetFooterMargin(10);

        $pdf->SetMargins(15, 20, 15);
        $pdf->SetAutoPageBreak(true, 15);
        $pdf->AddPage();

        // ── Encabezado del reporte ─────────────
        $pdf->SetFont('helvetica', 'B', 16);
        $pdf->SetTextColor(22, 38, 96); // Deep Azure
        $pdf->Cell(0, 10, $titulo, 0, 1, 'C');

        if ($subtitulo) {
            $pdf->SetFont('helvetica', '', 10);
            $pdf->SetTextColor(100, 116, 139);
            $pdf->Cell(0, 6, $subtitulo, 0, 1, 'C');
        }

        $pdf->SetFont('helvetica', '', 9);
        $pdf->SetTextColor(148, 163, 184);
        $pdf->Cell(0, 6, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'R');
        $pdf->Ln(4);

        // ── Cabecera de tabla ──────────────────
        $colWidth = (180) / count($columnas);
        $pdf->SetFont('helvetica', 'B', 9);
        $pdf->SetFillColor(22, 38, 96);
        $pdf->SetTextColor(255, 255, 255);
        $pdf->SetDrawColor(22, 38, 96);

        foreach ($columnas as $col) {
            $pdf->Cell($colWidth, 8, $col, 1, 0, 'C', true);
        }
        $pdf->Ln();

        // ── Filas de datos ─────────────────────
        $pdf->SetFont('helvetica', '', 8.5);
        $pdf->SetTextColor(30, 41, 59);
        $pdf->SetDrawColor(226, 232, 240);

        $altRow = false;
        foreach ($filas as $fila) {
            $pdf->SetFillColor($altRow ? 248 : 255, $altRow ? 250 : 255, $altRow ? 252 : 255);
            foreach ($fila as $celda) {
                $pdf->Cell($colWidth, 7, (string)$celda, 1, 0, 'L', $altRow);
            }
            $pdf->Ln();
            $altRow = !$altRow;
        }

        // Descarga o Previsualización
        $mode = $download ? 'D' : 'I';
        $pdf->Output($filename, $mode);
    }

    // ─────────────────────────────────────────────────────────────────
    // HTML Template helper para DomPDF
    // ─────────────────────────────────────────────────────────────────

    /**
     * Envuelve contenido en template HTML base para DomPDF.
     */
    public static function wrapHtml(string $body, string $titulo = 'Reporte SGP'): string
    {
        return <<<HTML
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title>{$titulo}</title>
<style>
  * { box-sizing: border-box; margin: 0; padding: 0; }
  body { font-family: DejaVu Sans, sans-serif; font-size: 11px; color: #1e293b; }
  .header { background: #162660; color: white; padding: 20px 30px; margin-bottom: 20px; }
  .header h1 { font-size: 18px; font-weight: bold; margin-bottom: 4px; }
  .header p  { font-size: 11px; opacity: 0.8; }
  .section-title { font-size: 13px; font-weight: bold; color: #162660; border-left: 4px solid #162660; padding-left: 10px; margin: 16px 0 10px; }
  table { width: 100%; border-collapse: collapse; margin-bottom: 16px; }
  th { background: #162660; color: white; padding: 7px 10px; font-size: 10px; text-align: left; }
  td { padding: 6px 10px; font-size: 10px; border-bottom: 1px solid #f1f5f9; }
  tr:nth-child(even) td { background: #f8fafc; }
  .badge { display: inline-block; padding: 2px 8px; border-radius: 10px; font-size: 9px; font-weight: bold; }
  .badge-success { background: #dcfce7; color: #16a34a; }
  .badge-warning { background: #fef3c7; color: #d97706; }
  .badge-info    { background: #dbeafe; color: #1d4ed8; }
  .footer { text-align: center; font-size: 9px; color: #94a3b8; margin-top: 30px; padding-top: 10px; border-top: 1px solid #e2e8f0; }
  .kpi-row { display: flex; gap: 12px; margin-bottom: 16px; }
  .kpi-box { flex: 1; border: 1px solid #e2e8f0; border-radius: 8px; padding: 12px; text-align: center; }
  .kpi-val { font-size: 20px; font-weight: bold; color: #162660; }
  .kpi-lbl { font-size: 9px; color: #64748b; margin-top: 2px; }
</style>
</head>
<body>
  <div class="header">
    <h1>SGP — Sistema de Gestión de Pasantes</h1>
    <p>UPT Bolívar &nbsp;|&nbsp; {$titulo} &nbsp;|&nbsp; Generado: HTML</p>
  </div>
  {$body}
  <div class="footer">Sistema de Gestión de Pasantes — UPT Bolívar — Documento generado el HTML</div>
</body>
</html>
HTML;
    }
}
