<?php
/**
 * Vista: Dashboard de Puntualidad del Tutor
 * Ruta: GET /tutor/puntualidad?rango=hoy|semana|mes|todo
 */

$rango             = $data['rango']             ?? 'mes';
$rangoLabel        = $data['rangoLabel']         ?? 'Este mes';
$ranking           = $data['ranking']            ?? [];
$historialRetardos = $data['historialRetardos']  ?? [];
$kpis              = $data['kpis']               ?? [];

require_once APPROOT . '/helpers/RetardoHelper.php';

$pct      = (float)($kpis['pct_puntual'] ?? 0);
$pctColor = $pct >= 90 ? '#16a34a' : ($pct >= 75 ? '#d97706' : '#dc2626');
$pctBg    = $pct >= 90 ? '#dcfce7' : ($pct >= 75 ? '#fef3c7' : '#fee2e2');

$rangos = [
    'hoy'    => 'Hoy',
    'semana' => 'Esta semana',
    'mes'    => 'Este mes',
    'todo'   => 'Todo el período',
];

$esc = fn($s) => htmlspecialchars((string)$s, ENT_QUOTES, 'UTF-8');
?>

<style>
:root {
    --p-radius: 16px;
    --p-shadow: 0 2px 12px rgba(0,0,0,0.07);
}
.punt-wrap  { width: 100%; max-width: 100%; padding-bottom: 60px; }
.punt-banner {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
    border-radius: 20px; padding: 28px 36px; margin-bottom: 24px;
    display: flex; align-items: center; justify-content: space-between;
    gap: 16px; position: relative; overflow: hidden;
}
.punt-banner::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:200px; height:200px; background:rgba(255,255,255,0.04);
    border-radius:50%; pointer-events:none;
}
.punt-kpis  { display:grid; grid-template-columns:repeat(auto-fit,minmax(150px,1fr)); gap:14px; margin-bottom:24px; }
.punt-kpi   {
    background:#fff; border-radius:var(--p-radius); box-shadow:var(--p-shadow);
    padding:18px 20px; display:flex; flex-direction:column; gap:4px;
}
.punt-kpi-val { font-size:1.9rem; font-weight:900; line-height:1; }
.punt-kpi-lbl { font-size:0.78rem; color:#64748b; font-weight:600; text-transform:uppercase; letter-spacing:0.4px; }
.punt-card  { background:#fff; border-radius:var(--p-radius); box-shadow:var(--p-shadow); padding:24px 28px; margin-bottom:20px; }
.punt-card-title { font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:16px; display:flex; align-items:center; gap:8px; }

/* ── Ranking rows ── */
.rank-row {
    display:flex; align-items:center; gap:14px;
    padding:12px 0; border-bottom:1px solid #f1f5f9;
}
.rank-row:last-child { border-bottom:none; }
.rank-avatar {
    width:40px; height:40px; border-radius:10px; flex-shrink:0;
    background:linear-gradient(135deg,#162660,#2563eb);
    display:flex; align-items:center; justify-content:center;
    color:#fff; font-weight:800; font-size:0.9rem;
}
.rank-bar-wrap { flex:1; min-width:0; }
.rank-name    { font-size:0.88rem; font-weight:700; color:#1e293b; }
.rank-depto   { font-size:0.73rem; color:#94a3b8; }
.rank-bar     { height:6px; border-radius:20px; background:#e2e8f0; margin-top:5px; overflow:hidden; }
.rank-bar-fill{ height:100%; border-radius:20px; transition:width 0.6s ease; }
.rank-pct     { font-size:0.88rem; font-weight:800; flex-shrink:0; min-width:44px; text-align:right; }
.rank-badges  { display:flex; gap:4px; flex-shrink:0; flex-wrap:wrap; justify-content:flex-end; min-width:100px; }
.rank-badge   { font-size:0.7rem; font-weight:700; padding:3px 7px; border-radius:20px; white-space:nowrap; }

/* ── Rango selector pills ── */
.rango-pills { display:flex; gap:6px; flex-wrap:wrap; }
.rango-pill  {
    padding:7px 16px; border-radius:20px; font-size:0.82rem; font-weight:600;
    text-decoration:none; transition:all 0.2s; border:2px solid transparent;
}
.rango-pill-active  { background:#fff; color:#1e3a8a; border-color:#c7d2fe; }
.rango-pill-inactive { background:rgba(255,255,255,0.12); color:rgba(255,255,255,0.85); }
.rango-pill-inactive:hover { background:rgba(255,255,255,0.22); }

/* ── Historial retardos table ── */
.ret-table { width:100%; border-collapse:collapse; font-size:0.85rem; }
.ret-table th { padding:10px 12px; text-align:left; font-size:0.75rem; font-weight:700; color:#64748b; text-transform:uppercase; letter-spacing:0.4px; border-bottom:2px solid #f1f5f9; }
.ret-table td { padding:10px 12px; border-bottom:1px solid #f8fafc; vertical-align:middle; }
.ret-table tr:hover td { background:#f8fafc; }
.ret-estado { display:inline-flex; align-items:center; gap:5px; padding:3px 10px; border-radius:20px; font-size:0.75rem; font-weight:700; }

/* ── Gauge ring ── */
.gauge-wrap { display:flex; flex-direction:column; align-items:center; }

@media (max-width:768px) {
    .punt-banner { flex-direction:column; align-items:flex-start; }
    .punt-kpis { grid-template-columns:repeat(2,1fr); }
}
</style>

<div class="punt-wrap">

<!-- BANNER -->
<div class="punt-banner">
    <div style="z-index:1;">
        <div style="display:flex;align-items:center;gap:14px;">
            <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:11px;">
                <i class="ti ti-clock-check" style="font-size:26px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:#fff;font-size:1.5rem;font-weight:800;margin:0;">Dashboard de Puntualidad</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.85rem;">
                    <i class="ti ti-calendar"></i> <?= $esc($rangoLabel) ?>
                </p>
            </div>
        </div>
    </div>
    <div style="z-index:1;display:flex;flex-direction:column;align-items:flex-end;gap:12px;">
        <!-- Filtros de rango -->
        <div class="rango-pills">
            <?php foreach ($rangos as $key => $label): ?>
            <a href="<?= URLROOT ?>/tutor/puntualidad?rango=<?= $key ?>"
               class="rango-pill <?= $rango === $key ? 'rango-pill-active' : 'rango-pill-inactive' ?>">
                <?= $label ?>
            </a>
            <?php endforeach; ?>
        </div>
        <a href="<?= URLROOT ?>/tutor"
           style="background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);border-radius:10px;
                  padding:8px 16px;color:#fff;font-size:0.82rem;font-weight:600;text-decoration:none;
                  display:flex;align-items:center;gap:6px;">
            <i class="ti ti-arrow-left"></i> Panel Tutor
        </a>
    </div>
</div>

<!-- KPIs -->
<div class="punt-kpis">
    <div class="punt-kpi">
        <div class="punt-kpi-val" style="color:<?= $pctColor ?>;"><?= $pct ?>%</div>
        <div class="punt-kpi-lbl">Puntualidad global</div>
        <div style="height:6px;border-radius:20px;background:#e2e8f0;overflow:hidden;margin-top:8px;">
            <div style="height:100%;border-radius:20px;background:<?= $pctColor ?>;width:<?= $pct ?>%;"></div>
        </div>
    </div>
    <div class="punt-kpi">
        <div class="punt-kpi-val" style="color:#16a34a;"><?= (int)($kpis['a_tiempo'] ?? 0) ?></div>
        <div class="punt-kpi-lbl">A tiempo</div>
    </div>
    <div class="punt-kpi">
        <div class="punt-kpi-val" style="color:#f59e0b;"><?= (int)($kpis['leve'] ?? 0) ?></div>
        <div class="punt-kpi-lbl">Retardo leve</div>
    </div>
    <div class="punt-kpi">
        <div class="punt-kpi-val" style="color:#dc2626;"><?= (int)($kpis['severo'] ?? 0) ?></div>
        <div class="punt-kpi-lbl">Retardo severo</div>
    </div>
    <div class="punt-kpi">
        <div class="punt-kpi-val" style="color:#64748b;"><?= (int)($kpis['ausente'] ?? 0) ?></div>
        <div class="punt-kpi-lbl">Ausentes</div>
    </div>
    <div class="punt-kpi">
        <div class="punt-kpi-val" style="color:#1e293b;"><?= (int)($kpis['total'] ?? 0) ?></div>
        <div class="punt-kpi-lbl">Registros totales</div>
    </div>
</div>

<?php if (empty($ranking)): ?>
<div class="punt-card" style="text-align:center;padding:48px 24px;color:#94a3b8;">
    <i class="ti ti-calendar-off" style="font-size:3rem;display:block;margin-bottom:12px;"></i>
    Sin registros de asistencia para el rango seleccionado.
</div>
<?php else: ?>

<!-- RANKING DE PUNTUALIDAD -->
<div class="punt-card">
    <div class="punt-card-title">
        <i class="ti ti-trophy" style="color:#f59e0b;"></i>
        Ranking de puntualidad — pasantes con más tardanzas primero
        <span style="margin-left:auto;font-size:0.78rem;color:#94a3b8;font-weight:500;">
            <?= count($ranking) ?> pasantes
        </span>
    </div>

    <?php foreach ($ranking as $idx => $p):
        $pctP  = (float)$p['pct_puntual'];
        $colP  = $pctP >= 90 ? '#16a34a' : ($pctP >= 75 ? '#d97706' : '#dc2626');
        $ini   = strtoupper(
            substr(explode(' ', $p['nombre'])[0] ?? '?', 0, 1) .
            substr(explode(' ', $p['nombre'])[1] ?? '?', 0, 1)
        );
        $retrasoProm = (float)$p['retraso_prom'];
    ?>
    <div class="rank-row">
        <!-- Posición -->
        <div style="font-size:0.8rem;font-weight:800;color:#cbd5e1;min-width:22px;text-align:center;">
            <?= $idx + 1 ?>
        </div>

        <!-- Avatar -->
        <div class="rank-avatar"><?= $esc($ini) ?></div>

        <!-- Barra de progreso -->
        <div class="rank-bar-wrap">
            <div class="rank-name"><?= $esc($p['nombre']) ?></div>
            <div class="rank-depto"><?= $esc($p['departamento']) ?></div>
            <div class="rank-bar">
                <div class="rank-bar-fill" style="width:<?= $pctP ?>%;background:<?= $colP ?>;"></div>
            </div>
        </div>

        <!-- % puntualidad -->
        <div class="rank-pct" style="color:<?= $colP ?>;"><?= $pctP ?>%</div>

        <!-- Badges desglose -->
        <div class="rank-badges">
            <?php if ($p['a_tiempo'] > 0): ?>
            <span class="rank-badge" style="background:#dcfce7;color:#16a34a;">
                <i class="ti ti-check" style="font-size:0.7rem;"></i> <?= $p['a_tiempo'] ?>
            </span>
            <?php endif; ?>
            <?php if ($p['leve'] > 0): ?>
            <span class="rank-badge" style="background:#fef3c7;color:#d97706;">
                ⚠ <?= $p['leve'] ?>
            </span>
            <?php endif; ?>
            <?php if ($p['severo'] > 0): ?>
            <span class="rank-badge" style="background:#fee2e2;color:#dc2626;">
                ⛔ <?= $p['severo'] ?>
            </span>
            <?php endif; ?>
        </div>

        <!-- Promedio de retraso y link almanaque -->
        <div style="flex-shrink:0;text-align:right;min-width:110px;">
            <?php if ($retrasoProm > 0): ?>
            <div style="font-size:0.75rem;color:#94a3b8;">
                ~<?= round($retrasoProm) ?> min prom.
            </div>
            <?php endif; ?>
            <a href="<?= URLROOT ?>/asistencias/almanaque/<?= (int)$p['pasante_id'] ?>"
               class="pjax-link"
               style="font-size:0.72rem;color:#2563eb;font-weight:600;text-decoration:none;
                      display:inline-flex;align-items:center;gap:3px;margin-top:3px;"
               title="Ver almanaque de asistencias">
                <i class="ti ti-calendar-stats"></i> Almanaque
            </a>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<?php if (!empty($historialRetardos)): ?>
<!-- HISTORIAL DETALLADO DE RETARDOS -->
<div class="punt-card">
    <div class="punt-card-title">
        <i class="ti ti-list-details" style="color:#dc2626;"></i>
        Historial de tardanzas — últimos <?= count($historialRetardos) ?> registros
    </div>
    <div style="overflow-x:auto;">
    <table class="ret-table">
        <thead>
            <tr>
                <th>Pasante</th>
                <th>Fecha</th>
                <th>Hora llegada</th>
                <th>Hora programada</th>
                <th>Retraso</th>
                <th>Clasificación</th>
            </tr>
        </thead>
        <tbody>
        <?php foreach ($historialRetardos as $r):
            $horaReal = $r->hora_entrada ?? null;
            $horaAsig = $r->hora_asignacion ?? '08:00:00';
            $analisis = RetardoHelper::analizar($horaReal, $horaAsig, $r->estado ?? 'Presente');
            $nombre   = trim(($r->nombres ?? '') . ' ' . ($r->apellidos ?? ''));
            $horaFmt  = $horaReal ? substr($horaReal, 0, 5) : '—';
            $asigFmt  = substr($horaAsig, 0, 5);
            $retrMin  = $analisis['retraso_min'] ?? 0;
            $retrFmt  = $retrMin > 0
                ? ($retrMin >= 60
                    ? intdiv($retrMin, 60) . 'h ' . ($retrMin % 60) . 'min'
                    : $retrMin . ' min')
                : '—';
        ?>
        <tr>
            <td style="font-weight:600;color:#1e293b;"><?= $esc($nombre) ?></td>
            <td><?= date('d/m/Y', strtotime($r->fecha)) ?></td>
            <td style="font-family:monospace;font-weight:700;color:#1e293b;"><?= $horaFmt ?></td>
            <td style="font-family:monospace;color:#64748b;"><?= $asigFmt ?></td>
            <td style="font-weight:700;color:<?= $analisis['color'] ?>;"><?= $retrFmt ?></td>
            <td>
                <span class="ret-estado" style="background:<?= $analisis['color'] ?>18;color:<?= $analisis['color'] ?>;">
                    <i class="ti <?= $esc($analisis['icono']) ?>" style="font-size:0.8rem;"></i>
                    <?= $esc($analisis['etiqueta']) ?>
                </span>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
</div>
<?php endif; ?>

<?php endif; ?>

</div><!-- /punt-wrap -->
