<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>Reporte Individual de Asistencia</title>
    <style>
        @page { margin: 1.5cm 1.8cm; }
        body   { font-family: Helvetica, Arial, sans-serif; font-size: 10px; color: #111; line-height: 1.4; }
        h1     { font-size: 14px; text-align: center; text-transform: uppercase; margin: 0 0 2px; }
        h2     { font-size: 10px; text-transform: uppercase; border-bottom: 1px solid #999; padding-bottom: 3px; margin: 14px 0 7px; }
        .sub   { text-align: center; font-size: 9px; color: #555; margin-bottom: 12px; }
        .ficha { width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 14px; }
        .ficha td { padding: 5px 8px; border: 1px solid #ddd; vertical-align: middle; }
        .ficha .lbl { background: #e8e8e8; font-weight: bold; text-transform: uppercase; width: 18%; font-size: 8px; }
        table  { width: 100%; border-collapse: collapse; font-size: 9px; margin-bottom: 12px; }
        th     { background: #e8e8e8; padding: 5px 6px; text-align: left; border: 1px solid #ccc; font-size: 8px; text-transform: uppercase; }
        td     { padding: 4px 6px; border: 1px solid #ddd; vertical-align: middle; }
        .alt   { background: #f7f7f7; }
        .c     { text-align: center; }
        .b     { font-weight: bold; }
        .foot  { border-top: 1px solid #bbb; margin-top: 14px; padding-top: 5px; text-align: center; font-size: 8px; color: #777; }
    </style>
</head>
<body>

    <?php include 'comunes/header.php'; ?>

    <h1>Reporte Individual de Asistencia</h1>
    <div class="sub">
        <?= htmlspecialchars($subtitulo_pdf) ?>
        &nbsp;|&nbsp; Generado el <?= date('d/m/Y \a \l\a\s H:i') ?>
    </div>

    <h2>Datos del Pasante</h2>
    <table class="ficha" cellspacing="0" cellpadding="0">
        <tr>
            <td class="lbl">Nombre</td>
            <td class="b"><?= htmlspecialchars(ucwords(strtolower(trim(($pasante->apellidos ?? '') . ', ' . ($pasante->nombres ?? ''))))) ?></td>
            <td class="lbl">Cédula</td>
            <td><?= htmlspecialchars($pasante->cedula ?? '—') ?></td>
        </tr>
        <tr>
            <td class="lbl">Departamento</td>
            <td><?= htmlspecialchars($pasante->departamento ?? 'Sin asignar') ?></td>
            <td class="lbl">Horas acum.</td>
            <td><?= (int)($pasante->total_presentes ?? 0) * 8 ?> hrs (<?= (int)($pasante->total_presentes ?? 0) ?> días)</td>
        </tr>
    </table>

    <h2>Historial de Asistencias</h2>
    <table cellspacing="0" cellpadding="0">
        <thead>
            <tr>
                <th width="5%"  class="c">N°</th>
                <th width="14%" class="c">Fecha</th>
                <th width="14%" class="c">H. Entrada</th>
                <th width="14%" class="c">H. Salida</th>
                <th width="18%" class="c">Estado</th>
                <th width="35%">Observación</th>
            </tr>
        </thead>
        <tbody>
            <?php if (empty($asistencias)): ?>
            <tr>
                <td colspan="6" class="c" style="color:#777; padding: 10px;">Sin registros de asistencia.</td>
            </tr>
            <?php else: ?>
            <?php foreach ($asistencias as $i => $a):
                $estadoRaw = strtolower(trim($a->estado ?? ''));
                $estadoClr = match($estadoRaw) {
                    'presente'     => '#16a34a',
                    'tarde'        => '#d97706',
                    'ausente'      => '#dc2626',
                    'justificado'  => '#2563eb',
                    default        => '#6b7280',
                };
            ?>
            <tr class="<?= $i % 2 !== 0 ? 'alt' : '' ?>">
                <td class="c"><?= $i + 1 ?></td>
                <td class="c"><?= $a->fecha ? date('d/m/Y', strtotime($a->fecha)) : '—' ?></td>
                <td class="c"><?= htmlspecialchars($a->hora_entrada ?? '—') ?></td>
                <td class="c"><?= htmlspecialchars($a->hora_salida  ?? '—') ?></td>
                <td class="c" style="color:<?= $estadoClr ?>; font-weight:bold;"><?= htmlspecialchars(ucfirst($estadoRaw) ?: '—') ?></td>
                <td><?= htmlspecialchars($a->observacion ?? '—') ?></td>
            </tr>
            <?php endforeach; ?>
            <?php endif; ?>
        </tbody>
    </table>
    <?php if (!empty($asistencias)): ?>
    <div class="sub" style="margin-top:4px;">
        Total de registros: <strong><?= count($asistencias) ?></strong>
    </div>
    <?php endif; ?>

    <div class="foot">
        Documento generado electrónicamente &mdash; Sistema de Gestión de Pasantes (SGP) &mdash;
        Instituto de Salud Pública de Bolívar &mdash; <?= date('d/m/Y H:i') ?>
    </div>

</body>
</html>
