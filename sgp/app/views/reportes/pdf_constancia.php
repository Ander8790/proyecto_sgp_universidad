<!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<style>
@page { margin: 2.5cm 2cm; }
body {
    font-family: Helvetica, Arial, sans-serif;
    font-size: 10.5px; color: #0D1424; line-height: 1.7;
}
.carta-body { max-width: 100%; }
.ref-line {
    text-align: right; font-size: 9.5px;
    color: #64748b; margin-bottom: 24px;
}
.asunto {
    font-weight: bold; font-size: 11px; color: #162660;
    margin-bottom: 18px; border-bottom: 1px solid #cbd5e1;
    padding-bottom: 6px;
}
.parrafo { margin-bottom: 14px; text-align: justify; }
.destacado {
    font-weight: bold; color: #162660;
}
.firma-block {
    margin-top: 60px; text-align: center;
}
.firma-linea {
    border-top: 1px solid #0D1424; display: inline-block;
    min-width: 260px; padding-top: 6px;
    font-size: 9.5px; font-weight: bold;
}
.firma-sub { font-size: 8.5px; font-weight: normal; color: #475569; }
.sello-box {
    border: 1px dashed #cbd5e1; border-radius: 6px;
    width: 100px; height: 80px; display: inline-block;
    margin-left: 40px; vertical-align: top; margin-top: 6px;
    color: #94a3b8; font-size: 7.5px; text-align: center;
    padding-top: 32px;
}
.footer-line {
    text-align: center; font-size: 8px; color: #94a3b8;
    margin-top: 36px; padding-top: 8px;
    border-top: 1px solid #e2e8f0;
}
</style>
</head>
<body>

<?php include __DIR__ . '/comunes/header.php'; ?>

<div class="carta-body">

    <div class="ref-line">
        Barcelona, <?= htmlspecialchars($fechaEmision ?? date('d \d\e F \d\e Y')) ?>
    </div>

    <?php
    $nombreCompleto = mb_strtoupper(
        trim(($pasante->apellidos ?? '') . ' ' . ($pasante->nombres ?? '')),
        'UTF-8'
    );
    $ci         = htmlspecialchars($pasante->cedula      ?? 'N/D');
    $depto      = htmlspecialchars($pasante->departamento ?? 'N/D');
    $institucion= htmlspecialchars($pasante->institucion  ?? 'N/D');
    $horasAcum  = (int)($pasante->horas_acumuladas ?? 0);
    $horasMeta  = (int)($pasante->horas_meta       ?? 440);
    $fechaInicio= $pasante->fecha_inicio ? date('d/m/Y', strtotime($pasante->fecha_inicio)) : 'N/D';
    $fechaFin   = $pasante->fecha_fin    ? date('d/m/Y', strtotime($pasante->fecha_fin))    : 'N/D';
    $tutor      = mb_strtoupper($pasante->tutor_nombre ?? 'N/D', 'UTF-8');
    $tutorCargo = htmlspecialchars($pasante->tutor_cargo ?? '');
    ?>

    <?php if (($tipo ?? 'servicio') === 'servicio'): ?>
    <!-- ══════════════════ CONSTANCIA DE SERVICIO ══════════════════ -->

    <div class="asunto">CONSTANCIA DE PRESTACIÓN DE SERVICIOS COMUNITARIOS</div>

    <p class="parrafo">
        La Coordinación de Pasantías de la <span class="destacado">Unidad de Producción Tecnológica (UPT) Bolívar</span>,
        hace constar por medio de la presente que el/la ciudadano/a
        <span class="destacado">V-<?= $ci ?> <?= htmlspecialchars($nombreCompleto) ?></span>,
        procedente de la institución de educación superior
        <span class="destacado"><?= $institucion ?></span>,
        se encuentra actualmente realizando sus <span class="destacado">Pasantías Profesionales</span>
        en esta institución, bajo la supervisión del área de
        <span class="destacado"><?= $depto ?></span>.
    </p>

    <p class="parrafo">
        Las actividades de pasantía se iniciaron el día
        <span class="destacado"><?= $fechaInicio ?></span>
        y tienen una duración estimada hasta el
        <span class="destacado"><?= $fechaFin ?></span>,
        acumulando a la fecha de emisión de este documento un total de
        <span class="destacado"><?= $horasAcum ?> horas</span>
        de las <span class="destacado"><?= $horasMeta ?> horas</span> requeridas.
    </p>

    <p class="parrafo">
        La presente constancia se expide a solicitud del interesado/a a los fines
        consiguientes que estime convenientes.
    </p>

    <?php else: ?>
    <!-- ══════════════════ CARTA DE CULMINACIÓN ══════════════════ -->

    <div class="asunto">CARTA DE CULMINACIÓN DE PASANTÍAS PROFESIONALES</div>

    <p class="parrafo">
        La Coordinación de Pasantías de la <span class="destacado">Unidad de Producción Tecnológica (UPT) Bolívar</span>
        certifica que el/la ciudadano/a
        <span class="destacado">V-<?= $ci ?> <?= htmlspecialchars($nombreCompleto) ?></span>,
        estudiante de la institución
        <span class="destacado"><?= $institucion ?></span>,
        ha <span class="destacado">culminado satisfactoriamente</span> sus Pasantías Profesionales
        en el área de <span class="destacado"><?= $depto ?></span>.
    </p>

    <p class="parrafo">
        El período de pasantía comprendió desde el
        <span class="destacado"><?= $fechaInicio ?></span>
        hasta el <span class="destacado"><?= $fechaFin ?></span>,
        completando un total de
        <span class="destacado"><?= $horasAcum ?> horas de servicio</span>,
        cumpliendo con los requisitos institucionales establecidos de
        <span class="destacado"><?= $horasMeta ?> horas</span>.
    </p>

    <p class="parrafo">
        Durante su estadía, el/la pasante demostró responsabilidad, compromiso y
        disposición en el desempeño de las actividades encomendadas, siendo supervisado/a
        por <span class="destacado"><?= htmlspecialchars($tutor) ?></span>
        <?= $tutorCargo ? '(' . htmlspecialchars($tutorCargo) . ')' : '' ?>.
    </p>

    <p class="parrafo">
        Se expide la presente carta a solicitud del interesado/a, para ser presentada
        ante la institución educativa correspondiente.
    </p>
    <?php endif; ?>

</div>

<div class="firma-block">
    <div>
        <div class="firma-linea">
            Coordinación de Pasantías<br>
            <span class="firma-sub">UPT Bolívar — SGP</span>
        </div>
        <div class="sello-box">Sello<br>Oficial</div>
    </div>
</div>

<div class="footer-line">
    Documento generado por el Sistema de Gestión de Pasantes (SGP) &nbsp;|&nbsp;
    UPT Bolívar &nbsp;|&nbsp; <?= date('d/m/Y H:i') ?>
</div>

</body>
</html>
