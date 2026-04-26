<?php
$tituloReporte  = $data['isPasante'] ? 'RESUMEN HISTORICO DE PASANTIA' : 'FICHA TECNICA DE USUARIO';
$nombreCompleto = mb_strtoupper(trim(($data['p']->nombres ?? '') . ' ' . ($data['p']->apellidos ?? '')), 'UTF-8');

$pathCintillo = APPROOT . '/../public/img/cintillo_isp_bolivar.jpg';
$bCintillo    = file_exists($pathCintillo)
    ? 'data:image/jpeg;base64,' . base64_encode(file_get_contents($pathCintillo))
    : '';

$estadoCfg = [
    'Activo'     => ['bg' => '#dcfce7', 'color' => '#15803d'],
    'Pendiente'  => ['bg' => '#fef9c3', 'color' => '#a16207'],
    'Finalizado' => ['bg' => '#dbeafe', 'color' => '#1d4ed8'],
    'Retirado'   => ['bg' => '#fee2e2', 'color' => '#b91c1c'],
];
$estadoPas    = $data['p']->estado_pasantia ?? 'Sin asignar';
$eCfg         = $estadoCfg[$estadoPas] ?? ['bg' => '#f1f5f9', 'color' => '#475569'];
$cuentaActiva = ($data['p']->user_estado ?? '') === 'activo';

$horasMeta = max(1, (int)($data['p']->horas_meta       ?? 1440));
$horasAcum = (int)($data['p']->horas_acumuladas ?? 0);
$horasRest = max(0, $horasMeta - $horasAcum);
$diasAcum  = (int)ceil($horasAcum / 8);
$diasTotal = (int)ceil($horasMeta / 8);
$diasRest  = max(0, $diasTotal - $diasAcum);
$pct       = min(100, round(($horasAcum / $horasMeta) * 100));
$barColor  = $pct >= 80 ? '#059669' : ($pct >= 50 ? '#2563eb' : '#dc2626');

$deptoText  = trim($data['p']->departamento ?? '');
$deptoVacio = $deptoText === '';
$deptoColor = $deptoVacio ? '#dc2626' : '#1e3a8a';
$deptoLabel = $deptoVacio ? 'Sin asignar' : $deptoText;
$tutorNombre = trim($data['p']->tutor_nombre ?? '');

$fIni = ($data['p']->fecha_inicio_pasantia ?? null) ? date('d/m/Y', strtotime($data['p']->fecha_inicio_pasantia)) : '—';
$fFin = ($data['p']->fecha_fin_estimada    ?? null) ? date('d/m/Y', strtotime($data['p']->fecha_fin_estimada))    : '—';
?>
<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<title><?= $tituloReporte ?> - <?= $nombreCompleto ?></title>
<style>
@page { margin: 18px 28px 14px 28px; }
* { box-sizing: border-box; }
body { font-family: Helvetica, Arial, sans-serif; font-size: 9px; color: #1e293b; margin:0; padding:0; }

.lbl { font-size:6.5px; text-transform:uppercase; color:#64748b; font-weight:bold; letter-spacing:0.4px; display:block; margin-bottom:1px; }
.val { font-size:9.5px; color:#1e293b; }

.sec { font-size:7.5px; font-weight:bold; color:#1e3a8a; text-transform:uppercase;
       letter-spacing:0.5px; border-bottom:1.5px solid #dbeafe;
       padding:2px 0 2px 7px; margin:7px 0 4px 0; position:relative; }
.sec::before { content:''; position:absolute; left:0; top:0; width:3px; height:13px; background:#2563eb; border-radius:2px; }

.badge { display:inline-block; border-radius:20px; padding:2px 7px; font-size:6.5px; font-weight:bold; text-transform:uppercase; }

.tbl    { width:100%; border-collapse:collapse; }
.tbl td { padding:4px 8px; vertical-align:top; border-bottom:1px solid #f1f5f9; }

.etbl    { width:100%; border-collapse:collapse; }
.etbl th { background:#eff6ff; color:#1e3a8a; font-weight:bold; padding:4px 6px; border:1px solid #bfdbfe; font-size:7px; text-align:left; }
.etbl td { padding:4px 6px; border:1px solid #e2e8f0; font-size:7.5px; color:#374151; }
.etbl tr:nth-child(even) td { background:#f8fafc; }

.card { border:1px solid #e2e8f0; border-radius:5px; padding:7px 9px; }

#footer { position:fixed; bottom:-10px; left:0; right:0; width:100%; border-top:1px solid #e2e8f0; padding-top:4px; }
</style>
</head>
<body>

<?php if ($bCintillo): ?>
<div style="width:100%;margin-bottom:5px;line-height:0;">
    <img src="<?= $bCintillo ?>" style="width:100%;height:auto;display:block;" alt="Cintillo ISP Bolivar">
</div>
<?php else: ?>
<div style="background:#162660;color:white;padding:4px 14px;font-size:9pt;font-weight:bold;margin-bottom:5px;">
    Instituto de Salud Publica de Bolivar | SGP
</div>
<?php endif; ?>

<!-- ENCABEZADO -->
<table style="width:100%;margin-bottom:6px;border-bottom:1.5px solid #1e3a8a;padding-bottom:4px;">
    <tr>
        <td style="text-align:center;">
            <div style="font-size:10px;font-weight:bold;color:#1e3a8a;letter-spacing:1px;"><?= $tituloReporte ?></div>
            <div style="font-size:6.5px;color:#94a3b8;margin-top:1px;">
                Generado: <?= date('d/m/Y h:i A') ?> | SGP - Instituto de Salud Publica del Estado Bolivar
            </div>
        </td>
    </tr>
</table>

<!-- IDENTIFICACION -->
<table style="width:100%;border:1px solid #e2e8f0;border-radius:5px;margin-bottom:6px;">
    <tr>
        <td style="padding:7px 10px;border-right:1px solid #e2e8f0;width:62%;vertical-align:middle;">
            <div style="font-size:12px;font-weight:bold;color:#1e3a8a;margin-bottom:4px;"><?= htmlspecialchars($nombreCompleto) ?></div>
            <span class="badge" style="background:#1e3a8a;color:white;margin-right:3px;">
                <?= htmlspecialchars($data['p']->nombre_rol ?? 'USUARIO') ?>
            </span>
            <span class="badge" style="background:<?= $cuentaActiva ? '#059669' : '#dc2626' ?>;color:white;margin-right:3px;">
                Cuenta: <?= $cuentaActiva ? 'Activa' : 'Inactiva' ?>
            </span>
            <?php if ($data['isPasante']): ?>
            <span class="badge" style="background:<?= $eCfg['bg'] ?>;color:<?= $eCfg['color'] ?>;border:1px solid <?= $eCfg['color'] ?>;">
                Pasantia: <?= htmlspecialchars($estadoPas) ?>
            </span>
            <?php endif; ?>
        </td>
        <td style="padding:7px 10px;text-align:center;vertical-align:middle;width:38%;">
            <span class="lbl" style="text-align:center;display:block;">Cedula de Identidad</span>
            <div style="font-size:14px;font-weight:bold;color:#1e3a8a;margin-top:2px;"><?= htmlspecialchars($data['p']->cedula ?? '—') ?></div>
        </td>
    </tr>
</table>

<!-- CONTACTO -->
<div class="sec">Datos de Contacto</div>
<table class="tbl" style="margin-bottom:4px;">
    <tr>
        <td style="width:50%;"><span class="lbl">Correo Electronico</span><span class="val"><?= htmlspecialchars($data['p']->correo ?? '—') ?></span></td>
        <td style="width:50%;"><span class="lbl">Telefono</span><span class="val"><?= htmlspecialchars($data['p']->telefono ?? '—') ?></span></td>
    </tr>
</table>

<?php if ($data['isPasante']): ?>

<!-- PASANTIA -->
<div class="sec">Informacion de la Pasantia</div>
<table class="tbl" style="margin-bottom:4px;">
    <tr>
        <td style="width:50%;"><span class="lbl">Institucion de Procedencia</span><span class="val"><?= htmlspecialchars($data['p']->institucion_procedencia ?? '—') ?></span></td>
        <td style="width:50%;"><span class="lbl">Departamento Asignado</span><span class="val" style="font-weight:bold;color:<?= $deptoColor ?>;"><?= htmlspecialchars($deptoLabel) ?></span></td>
    </tr>
    <tr>
        <td><span class="lbl">Tutor Asignado</span><span class="val"><?= htmlspecialchars($tutorNombre ?: '—') ?></span></td>
        <td><span class="lbl">Periodo</span><span class="val"><?= $fIni ?> al <?= $fFin ?></span></td>
    </tr>
</table>

<!-- PROGRESO -->
<div class="sec">Progreso de Horas</div>
<table style="width:100%;border-collapse:separate;border-spacing:5px 0;margin-bottom:7px;">
    <tr>
        <!-- Horas -->
        <td style="width:50%;vertical-align:top;">
            <div class="card" style="background:#f8fafc;">
                <div style="font-size:6px;font-weight:bold;color:#1e3a8a;text-transform:uppercase;margin-bottom:4px;">Progreso de Horas</div>
                <div style="margin-bottom:4px;">
                    <span style="font-size:18px;font-weight:bold;color:<?= $barColor ?>;"><?= $horasAcum ?></span>
                    <span style="font-size:9px;color:#64748b;"> / <?= $horasMeta ?>h</span>
                    <span style="float:right;font-size:13px;font-weight:bold;color:<?= $barColor ?>;margin-top:2px;"><?= $pct ?>%</span>
                </div>
                <div style="width:100%;height:7px;background:#e2e8f0;border-radius:4px;overflow:hidden;margin-bottom:3px;">
                    <div style="width:<?= $pct ?>%;height:100%;background:<?= $barColor ?>;border-radius:4px;"></div>
                </div>
                <table style="width:100%;border-collapse:collapse;">
                    <tr>
                        <td style="font-size:6px;color:#94a3b8;">0h</td>
                        <td style="font-size:6px;color:#94a3b8;text-align:right;"><?= $horasMeta ?>h meta</td>
                    </tr>
                </table>
                <div style="margin-top:4px;font-size:7px;color:#475569;background:#fff;border-radius:4px;padding:3px 6px;border:1px solid #e2e8f0;">
                    Faltan <strong style="color:<?= $barColor ?>;"><?= $horasRest ?> horas</strong> para completar la pasantia
                </div>
            </div>
        </td>
        <!-- Timeline dias -->
        <td style="width:50%;vertical-align:top;">
            <div class="card" style="background:#f8fafc;">
                <div style="font-size:6px;font-weight:bold;color:#1e3a8a;text-transform:uppercase;margin-bottom:4px;">Timeline de Pasantia</div>
                <table style="width:100%;border-collapse:collapse;margin-bottom:4px;">
                    <tr>
                        <td style="width:47%;background:#eff6ff;border:1px solid #bfdbfe;border-radius:4px;padding:3px 5px;text-align:center;">
                            <div style="font-size:5.5px;font-weight:bold;color:#1e3a8a;text-transform:uppercase;">Inicio</div>
                            <div style="font-size:9px;font-weight:bold;color:#1e293b;margin-top:1px;"><?= $fIni ?></div>
                        </td>
                        <td style="width:6%;text-align:center;font-size:9px;color:#94a3b8;">|</td>
                        <td style="width:47%;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:4px;padding:3px 5px;text-align:center;">
                            <div style="font-size:5.5px;font-weight:bold;color:#15803d;text-transform:uppercase;">Fin Estimado</div>
                            <div style="font-size:9px;font-weight:bold;color:#1e293b;margin-top:1px;"><?= $fFin ?></div>
                        </td>
                    </tr>
                </table>
                <div style="width:100%;height:13px;background:#e2e8f0;border-radius:6px;overflow:hidden;margin-bottom:3px;position:relative;">
                    <div style="width:<?= $pct ?>%;height:100%;background:<?= $barColor ?>;border-radius:6px;"></div>
                    <div style="position:absolute;top:0;left:0;width:100%;text-align:center;font-size:7px;font-weight:bold;color:white;line-height:13px;">
                        <?= $pct ?>% completado
                    </div>
                </div>
                <table style="width:100%;border-collapse:collapse;margin-top:2px;">
                    <tr>
                        <td style="font-size:6.5px;color:#475569;"><strong style="color:<?= $barColor ?>;"><?= $diasAcum ?></strong> dias cursados</td>
                        <td style="font-size:6.5px;color:#475569;text-align:right;"><strong style="color:#64748b;"><?= $diasRest ?></strong> dias restantes</td>
                    </tr>
                </table>
                <div style="margin-top:4px;">
                    <span class="badge" style="background:<?= $eCfg['bg'] ?>;color:<?= $eCfg['color'] ?>;border:1px solid <?= $eCfg['color'] ?>;">
                        <?= htmlspecialchars($estadoPas) ?>
                    </span>
                    <span style="font-size:6.5px;color:#64748b;margin-left:3px;">&bull; <?= $diasRest ?> dias restantes</span>
                </div>
            </div>
        </td>
    </tr>
</table>

<!-- EVALUACIONES -->
<div class="sec">Evaluaciones Registradas</div>
<table class="etbl">
    <tr>
        <th style="width:15%;">Fecha</th>
        <th style="width:28%;">Lapso / Fase</th>
        <th style="width:17%;text-align:center;">Promedio</th>
        <th style="width:40%;">Tutor Evaluador</th>
    </tr>
    <?php if (empty($data['evals'])): ?>
        <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:10px;font-style:italic;">Sin evaluaciones registradas.</td></tr>
    <?php else: ?>
        <?php foreach ($data['evals'] as $e):
            $prom = (float)($e->promedio_final ?? 0);
            $pc   = $prom >= 4 ? '#059669' : ($prom >= 3 ? '#d97706' : '#dc2626');
        ?>
        <tr>
            <td><?= htmlspecialchars($e->fecha_formateada ?? ($e->fecha_evaluacion ?? '—')) ?></td>
            <td><?= htmlspecialchars($e->lapso_academico ?? '—') ?></td>
            <td style="text-align:center;font-weight:bold;color:<?= $pc ?>;"><?= number_format($prom, 2) ?> / 5.00</td>
            <td style="color:#475569;"><?= htmlspecialchars(trim($e->tutor_nombre_eval ?? '') ?: '—') ?></td>
        </tr>
        <?php endforeach; ?>
    <?php endif; ?>
</table>

<?php endif; ?>

<!-- PIE -->
<div id="footer">
    <table style="width:100%;border-collapse:collapse;">
        <tr>
            <td style="color:#94a3b8;font-size:6.5px;">Documento de consulta interna — SGP | Instituto de Salud Publica del Estado Bolivar</td>
            <td style="text-align:right;color:#94a3b8;font-size:6.5px;"><?= date('d/m/Y H:i:s') ?></td>
        </tr>
    </table>
</div>

</body>
</html>
