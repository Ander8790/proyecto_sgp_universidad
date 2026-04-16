<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 2cm 1cm; }
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 9px;
    color: #000;
    line-height: 1.3;
}
table { border-collapse: collapse; }
.info-table {
    width: 100%;
    margin-bottom: 14px;
    border: 1px solid #000;
}
.info-table td {
    border: 1px solid #000;
    padding: 4px 7px;
    vertical-align: middle;
}
.info-label {
    font-weight: bold;
    color: #162660;
    font-size: 8px;
    text-transform: uppercase;
    display: block;
    margin-bottom: 2px;
}
.info-value {
    font-size: 10px;
    font-weight: bold;
}
.asistencia-table {
    width: 100%;
    border-collapse: collapse;
    table-layout: fixed;
}
.asistencia-table td {
    border: 1px solid #000;
    padding: 3px 2px;
    text-align: center;
    vertical-align: middle;
    height: 28px;
}
.week-num-cell {
    background: #f8fafc;
    font-size: 8px;
}
.marca {
    font-weight: bold;
    font-size: 11px;
}
.marca-x { color: #1D9E75; }
.marca-a { color: #E24B4A; }
.marca-j { color: #BA7517; }
.divider-row td {
    border-bottom: 2px solid #162660;
}
.firma-table {
    width: 100%;
    margin-top: 50px;
    border-collapse: collapse;
}
.firma-table td {
    width: 45%;
    text-align: center;
    padding: 0 15px;
    vertical-align: top;
}
.firma-linea {
    border-top: 1px solid #000;
    padding-top: 5px;
    margin-top: 35px;
    font-size: 9px;
    font-weight: bold;
}
.firma-sub {
    font-size: 8px;
    font-weight: normal;
}
.leyenda {
    margin-top: 12px;
    font-size: 7px;
    border: 1px solid #ccc;
    padding: 4px 6px;
}
</style>
</head>
<body>

<?php include __DIR__ . '/comunes/header.php'; ?>

<p style="text-align:center; font-size:13px; font-weight:bold;
   color:#162660; text-transform:uppercase; margin:10px 0 14px;">
    Registro y Control de Asistencia
</p>

<table style="width:100%; border-collapse:collapse;
              margin-bottom:14px; border:1px solid #000;">
    <tr>
        <td style="width:28%; border:1px solid #000;
                   padding:5px 8px; font-size:9px;
                   font-weight:bold; background:#f1f5f9;
                   vertical-align:middle;">
            Nombre y Apellido:
        </td>
        <td style="border:1px solid #000; padding:5px 8px;
                   font-size:10px; font-weight:bold;
                   vertical-align:middle;">
            <?= htmlspecialchars(mb_strtoupper(
                ($pasante->apellidos ?? '') . ' ' .
                ($pasante->nombres ?? ''), 'UTF-8'
            )) ?>
        </td>
    </tr>
    <tr>
        <td style="width:28%; border:1px solid #000;
                   padding:5px 8px; font-size:9px;
                   font-weight:bold; background:#f1f5f9;
                   vertical-align:middle;">
            Tutor Empresarial:
        </td>
        <td style="border:1px solid #000; padding:5px 8px;
                   font-size:10px; font-weight:bold;
                   vertical-align:middle;">
            <?= htmlspecialchars(mb_strtoupper(
                trim(($pasante->tutor_cargo ?? '') . ' ' .
                ($pasante->tutor_nombre ?? 'N/D')), 'UTF-8'
            )) ?>
        </td>
    </tr>
    <tr>
        <td style="width:28%; border:1px solid #000;
                   padding:5px 8px; font-size:9px;
                   font-weight:bold; background:#f1f5f9;
                   vertical-align:middle;">
            Dependencia:
        </td>
        <td style="border:1px solid #000; padding:5px 8px;
                   font-size:10px; font-weight:bold;
                   vertical-align:middle;">
            <?= htmlspecialchars(mb_strtoupper(
                $pasante->departamento ?? 'N/D', 'UTF-8'
            )) ?>
        </td>
    </tr>
    <tr>
        <td style="width:28%; border:1px solid #000;
                   padding:5px 8px; font-size:9px;
                   font-weight:bold; background:#f1f5f9;
                   vertical-align:middle;">
            Lapso de Pasant&iacute;as:
        </td>
        <td style="border:1px solid #000; padding:5px 8px;
                   font-size:10px; font-weight:bold;
                   vertical-align:middle;">
            Desde: <?= $pasante->fecha_inicio_pasantia
                ? date('d/m/Y', strtotime($pasante->fecha_inicio_pasantia))
                : 'N/D' ?>
            &nbsp;&nbsp;&nbsp;
            Hasta: <?= $pasante->fecha_fin_estimada
                ? date('d/m/Y', strtotime($pasante->fecha_fin_estimada))
                : date('d/m/Y', strtotime($fecha_fin)) ?>
        </td>
    </tr>
</table>

<table class="asistencia-table">
    <thead>
        <tr>
            <th rowspan="2"
                style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:5px 2px; text-align:center;
                       border:1px solid #000;
                       vertical-align:middle; width:9%;">
                Semana<br>N&ordm;
            </th>
            <th colspan="7"
                style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:5px 2px; text-align:center;
                       border:1px solid #000;">
                D&iacute;as
            </th>
            <th colspan="2"
                style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:5px 2px; text-align:center;
                       border:1px solid #000; width:14%;">
                Firma
            </th>
            <th rowspan="2"
                style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:5px 2px; text-align:center;
                       border:1px solid #000; width:14%;">
                Observaciones
            </th>
        </tr>
        <tr>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:4px 2px; text-align:center;
                       border:1px solid #000; width:6%;">D</th>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:4px 2px; text-align:center;
                       border:1px solid #000; width:6%;">L</th>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:4px 2px; text-align:center;
                       border:1px solid #000; width:6%;">M</th>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:4px 2px; text-align:center;
                       border:1px solid #000; width:6%;">M</th>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:4px 2px; text-align:center;
                       border:1px solid #000; width:6%;">J</th>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:4px 2px; text-align:center;
                       border:1px solid #000; width:6%;">V</th>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       font-size:8px; font-weight:bold;
                       padding:4px 2px; text-align:center;
                       border:1px solid #000; width:6%;">S</th>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       border:1px solid #000; width:7%;"></th>
            <th style="background-color:#162660 !important;
                       color:#ffffff !important;
                       border:1px solid #000; width:7%;"></th>
        </tr>
    </thead>
    <tbody>
    <?php
    $dow_inicio_trim = (int) date('w', strtotime($fecha_inicio));
    $ts_inicio_trim  = strtotime($fecha_inicio);
    $orden_dias = [0, 1, 2, 3, 4, 5, 6];

    for ($s = 1; $s <= 14; $s++):
        // Timestamp del primer día de esta semana relativa
        $ts_primer_dia_semana = $ts_inicio_trim + (($s - 1) * 7 * 86400);
        // Retroceder al domingo de esa semana
        $dow_primer = (int) date('w', $ts_primer_dia_semana);
        $ts_domingo = $ts_primer_dia_semana - ($dow_primer * 86400);
        $es_divisor = in_array($s, [5, 10, 14]);
    ?>
    <tr style="height:28px;<?= $es_divisor
        ? 'border-bottom:2px solid #162660;' : '' ?>">
        <td style="background:#f8fafc; text-align:center;
                   border:1px solid #000; font-size:8px;
                   vertical-align:middle; font-weight:bold;">
            <?= date('W', $ts_primer_dia_semana) ?>
        </td>
        <?php foreach ($orden_dias as $dow):
            $ts_dia    = $ts_domingo + ($dow * 86400);
            $fecha_dia = date('Y-m-d', $ts_dia);
            $reg = $asistencias_procesadas[$fecha_dia][$dow] ?? null;
        ?>
        <td style="text-align:center; border:1px solid #000;
                   font-size:9px; padding:2px;
                   vertical-align:middle;">
            <?php if ($reg):
                $estado = $reg->estado ?? '';
                if (in_array($estado, ['Presente','abierto','cerrado'])): ?>
                    <strong style="color:#1D9E75;font-size:11px;">X</strong>
                <?php elseif ($estado === 'Ausente'): ?>
                    <strong style="color:#E24B4A;font-size:11px;">A</strong>
                <?php elseif ($estado === 'Justificado'): ?>
                    <strong style="color:#BA7517;font-size:11px;">J</strong>
                <?php endif;
            endif; ?>
        </td>
        <?php endforeach; ?>
        <td style="border:1px solid #000;"></td>
        <td style="border:1px solid #000;"></td>
        <td style="border:1px solid #000;"></td>
    </tr>
    <?php endfor; ?>
    </tbody>
</table>

<div class="leyenda">
    <strong>Leyenda:</strong>
    <strong style="color:#1D9E75;">X</strong> = Asisti&oacute; &nbsp;|&nbsp;
    <strong style="color:#E24B4A;">A</strong> = Ausencia injustificada &nbsp;|&nbsp;
    <strong style="color:#BA7517;">J</strong> = Justificado
</div>

<table class="firma-table">
    <tr>
        <td>
            <div class="firma-linea">
                Pasante (Firma y Huella)<br>
                <span class="firma-sub">
                    <?= htmlspecialchars(
                        mb_strtoupper(
                            ($pasante->apellidos ?? '') . ' ' .
                            ($pasante->nombres ?? ''),
                            'UTF-8'
                        )
                    ) ?>
                </span><br>
                <span class="firma-sub">
                    C.I: <?= htmlspecialchars($pasante->cedula ?? 'N/D') ?>
                </span>
            </div>
        </td>
        <td style="width:10%;"></td>
        <td>
            <div class="firma-linea">
                Tutor Empresarial (Firma y Sello)<br>
                <span class="firma-sub">
                    <?= htmlspecialchars(
                        mb_strtoupper($pasante->tutor_nombre ?? 'N/D', 'UTF-8')
                    ) ?>
                </span><br>
                <span class="firma-sub">
                    <?= htmlspecialchars($pasante->tutor_cargo ?? '') ?>
                </span>
            </div>
        </td>
    </tr>
</table>

</body>
</html>
