<?php
// Vista HTML exclusiva para la previsualización del PDF del Kardex/Ficha Técnica
// $data contendrá la información extraída de UsersController

$tituloReporte = $data['isPasante'] ? 'KARDEX DE PASANTÍA' : 'FICHA TÉCNICA DE USUARIO';
$nombreCompleto = mb_strtoupper(htmlspecialchars(($data['p']->nombres ?? '') . ' ' . ($data['p']->apellidos ?? '')), 'UTF-8');

$pathLogo = APPROOT . '/../public/img/logo.png';
$pathCintillo = APPROOT . '/../public/img/cintillo.png';
$bLogo = file_exists($pathLogo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($pathLogo)) : '';
$bCintillo = file_exists($pathCintillo) ? 'data:image/png;base64,' . base64_encode(file_get_contents($pathCintillo)) : '';

$rolColor = $data['isPasante'] ? '#059669' : ($data['p']->rol_id == 1 ? '#d97706' : '#2563eb'); 
$estadoColor = ($data['p']->user_estado === 'activo') ? '#059669' : '#dc2626';

$progressHtml = '';
if ($data['isPasante']) {
    $horasMeta = (int)($data['p']->horas_meta ?? 1440);
    $horasAcum = (int)($data['p']->horas_acumuladas ?? 0);
    $porcentaje = $horasMeta > 0 ? min(100, round(($horasAcum / $horasMeta) * 100)) : 0;
    
    $progressHtml = "
    <div style='margin-bottom: 25px; background: #fff; padding: 18px; border: 1px solid #e2e8f0; border-radius: 12px;'>
        <h4 style='margin: 0 0 12px 0; color: #1e293b; font-size: 13px; text-transform: uppercase;'>Rendimiento de Pasantía</h4>
        <div style='font-size: 11px; color: #475569; margin-bottom: 6px;'>
            Horas Acumuladas: <strong>{$horasAcum} / {$horasMeta} hrs</strong> ({$porcentaje}%)
        </div>
        <div style='width: 100%; height: 14px; background: #e2e8f0; border-radius: 7px; overflow: hidden;'>
            <div style='width: {$porcentaje}%; height: 100%; background: {$rolColor};'></div>
        </div>
    </div>";
}
?>
<!DOCTYPE html>
<html lang='es'>
<head>
    <meta charset='UTF-8'>
    <title><?= $tituloReporte ?> - <?= $nombreCompleto ?></title>
    <style>
        @page { margin: 40px 45px; }
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 11px; color: #333; }
        
        .table-invisible { width: 100%; border-collapse: collapse; }
        .table-invisible td { padding: 12px 8px; vertical-align: top; border-bottom: 1px solid #f1f5f9; }
        .label { font-size: 9px; text-transform: uppercase; color: #64748b; font-weight: bold; margin-bottom: 4px; display: block; }
        .value { font-size: 12px; color: #333; font-weight: normal; }
        
        .section-title { font-size: 12px; font-weight: bold; color: #1e3a8a; border-bottom: 2px solid #e2e8f0; padding-bottom: 4px; margin: 25px 0 12px 0; text-transform: uppercase; }
        
        .datatable { width: 100%; border-collapse: collapse; margin-bottom: 20px; }
        .datatable th { background: #f8fafc; color: #475569; font-weight: bold; text-align: left; padding: 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        .datatable td { padding: 8px; border: 1px solid #e2e8f0; font-size: 10px; }
        
        #footer { position: fixed; bottom: -20px; left: 0; right: 0; width: 100%; border-top: 2px solid #e2e8f0; padding-top: 15px; }
    </style>
</head>
<body>
    <table style='width: 100%; margin-bottom: 25px; border-bottom: 2px solid #1e3a8a; padding-bottom: 12px;'>
        <tr>
            <td style='width: 25%; vertical-align: middle;'>
                <?= $bLogo ? "<img src='{$bLogo}' style='height: 40px;'>" : "<h2 style='color:#1e3a8a;'>SGP</h2>" ?>
            </td>
            <td style='width: 50%; text-align: center; vertical-align: middle;'>
                <h2 style='margin: 0; color: #1e3a8a; font-size: 16px; letter-spacing: 1px;'><?= $tituloReporte ?></h2>
                <div style='font-size: 9px; color: #64748b; margin-top: 4px;'>Emisión: <?= date('d/m/Y h:i A') ?></div>
            </td>
            <td style='width: 25%; text-align: right; vertical-align: middle;'>
                <?= $bCintillo ? "<img src='{$bCintillo}' style='height: 35px;'>" : "<b>Gob/Salud</b>" ?>
            </td>
        </tr>
    </table>

    <div style='background: #f8fafc; border: 1px solid #e2e8f0; border-radius: 12px; padding: 22px; margin-bottom: 25px; text-align: center;'>
        <h1 style='margin: 0 0 14px 0; color: #1e3a8a; font-size: 22px; font-weight: bold; letter-spacing: 0.5px;'><?= $nombreCompleto ?></h1>
        <div>
            <span style='display: inline-block; background: <?= $rolColor ?>; color: white; border-radius: 14px; padding: 5px 14px; font-size: 10px; font-weight: bold; text-transform: uppercase; margin-right: 8px;'>
                <?= htmlspecialchars($data['p']->nombre_rol ?? 'USUARIO') ?>
            </span>
            <span style='display: inline-block; background: <?= $estadoColor ?>; color: white; border-radius: 14px; padding: 5px 14px; font-size: 10px; font-weight: bold; text-transform: uppercase;'>
                <?= htmlspecialchars($data['p']->user_estado ?? 'INACTIVO') ?>
            </span>
        </div>
    </div>

    <div style='margin-bottom: 25px;'>
        <table class='table-invisible'>
            <tr>
                <td style='width: 50%;'>
                    <span class='label'>Cédula de Identidad</span>
                    <span class='value'><?= htmlspecialchars($data['p']->cedula ?? '—') ?></span>
                </td>
                <td style='width: 50%;'>
                    <span class='label'>Correo Electrónico</span>
                    <span class='value'><?= htmlspecialchars($data['p']->correo ?? '—') ?></span>
                </td>
            </tr>
            <tr>
                <td>
                    <span class='label'>Departamento Asignado</span>
                    <span class='value'><?= htmlspecialchars($data['p']->departamento ?? 'Sin Asignar') ?></span>
                </td>
                <td>
                    <span class='label'>Teléfono</span>
                    <span class='value'><?= htmlspecialchars($data['p']->telefono ?? '—') ?></span>
                </td>
            </tr>
            <?php if ($data['isPasante']): ?>
            <tr>
                <td>
                    <span class='label'>Institución de Procedencia</span>
                    <span class='value'><?= htmlspecialchars($data['p']->institucion_procedencia ?? '—') ?></span>
                </td>
                <td>
                    <span class='label'>Período de Pasantía</span>
                    <span class='value'><?= htmlspecialchars($data['p']->fecha_inicio_pasantia ?? '—') ?> al <?= htmlspecialchars($data['p']->fecha_fin_estimada ?? '—') ?></span>
                </td>
            </tr>
            <?php endif; ?>
        </table>
    </div>

    <?= $progressHtml ?>

    <?php if ($data['isPasante']): ?>
        <div class='section-title'>Últimas Evaluaciones Registradas</div>
        <table class='datatable'>
            <tr>
                <th style='width: 25%'>Fecha</th>
                <th style='width: 30%'>Fase Evaluada</th>
                <th style='width: 15%; text-align: center;'>Promedio</th>
                <th style='width: 30%'>Estado</th>
            </tr>
            <?php if (empty($data['evals'])): ?>
                <tr><td colspan='4' style='text-align: center; color: #94a3b8; padding: 15px;'>El pasante aún no posee evaluaciones registradas en el sistema.</td></tr>
            <?php else: ?>
                <?php foreach ($data['evals'] as $e): ?>
                    <tr>
                        <td><?= htmlspecialchars($e->fecha_evaluacion ?? '—') ?></td>
                        <td><?= htmlspecialchars($e->tipo_evaluacion ?? '—') ?></td>
                        <td style='text-align: center; font-weight: bold;'><?= number_format($e->promedio_final ?? 0, 2) ?> / 5.00</td>
                        <td><?= htmlspecialchars($e->estado ?? '—') ?></td>
                    </tr>
                <?php endforeach; ?>
            <?php endif; ?>
        </table>
    <?php endif; ?>

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
                    Documento generado automáticamente por el SGP.<br><?= date('d/m/Y H:i:s') ?>
                </td>
            </tr>
        </table>
    </div>

</body>
</html>
