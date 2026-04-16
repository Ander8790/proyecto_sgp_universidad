<?php
/**
 * Vista: Almanaque — Panel Histórico de Pasantía (Bento Grid Premium)
 * Ruta: GET /asistencias/almanaque/{pasante_id}?anio=YYYY
 */

$pasante           = $data['pasante']           ?? null;
$anio              = (int)($data['anio']         ?? date('Y'));
$anios             = $data['anios']              ?? [$anio];
$periodo           = $data['periodo']            ?? ['nombre' => null, 'estado' => null];
$grilla            = $data['grilla']             ?? [];
$weekLabels        = $data['weekLabels']         ?? [];
$stats             = $data['stats']              ?? ['P'=>0,'A'=>0,'J'=>0,'laborables'=>0];
$pct               = (float)($data['pctAsistencia'] ?? 0);
$rachaActual       = (int)($data['rachaActual']  ?? 0);
$rachaMax          = (int)($data['rachaMax']     ?? 0);
$historialCompleto = $data['historialCompleto']  ?? [];
$diasTrans         = (int)($data['diasTrans']    ?? 0);
$diasTotal         = (int)($data['diasTotal']    ?? 0);
$diasRest          = (int)($data['diasRest']     ?? 0);
$pctTiempo         = (float)($data['pctTiempo']  ?? 0);

$nombre    = trim(($pasante->nombres ?? '') . ' ' . ($pasante->apellidos ?? ''));
$ini       = strtoupper(substr($pasante->nombres ?? '?', 0, 1) . substr($pasante->apellidos ?? '?', 0, 1));
$horasAcum = (int)($pasante->horas_acumuladas ?? 0);
$horasMeta = (int)($pasante->horas_meta       ?? 240);
$pctHoras  = $horasMeta > 0 ? min(100, round($horasAcum / $horasMeta * 100)) : 0;
$estadoPas = $pasante->estado_pasantia ?? 'Sin Asignar';
$instNombre= $pasante->institucion_nombre ?? '—';
$tutorNom  = trim($pasante->tutor_nombre  ?? '—');

// KPI color por % asistencia
$kpiColor = $pct >= 90 ? '#16a34a' : ($pct >= 75 ? '#d97706' : '#dc2626');

// Color estado pasantía
$estadoMap = [
    'Activo'     => ['color'=>'#16a34a','bg'=>'#dcfce7','label'=>'ACTIVO'],
    'Finalizado' => ['color'=>'#2563eb','bg'=>'#dbeafe','label'=>'FINALIZADO'],
    'Retirado'   => ['color'=>'#dc2626','bg'=>'#fee2e2','label'=>'RETIRADO'],
    'Pendiente'  => ['color'=>'#d97706','bg'=>'#fef3c7','label'=>'PENDIENTE'],
];
$estadoCfg = $estadoMap[$estadoPas] ?? ['color'=>'#64748b','bg'=>'#f1f5f9','label'=>strtoupper($estadoPas)];

// Etiquetas mes heatmap
$mesesEtiquetas = [
    'Jan'=>'Ene','Feb'=>'Feb','Mar'=>'Mar','Apr'=>'Abr','May'=>'May','Jun'=>'Jun',
    'Jul'=>'Jul','Aug'=>'Ago','Sep'=>'Sep','Oct'=>'Oct','Nov'=>'Nov','Dec'=>'Dic',
];
$diasNombreCorto = ['lunes','martes','miércoles','jueves','viernes','sábado','domingo'];
?>

<!-- ── TOOLTIP GLOBAL ── -->
<div id="almTooltip">
    <div class="tt-fecha" id="tt-fecha"></div>
    <div class="tt-estado"><div class="tt-dot" id="tt-dot"></div><span id="tt-est"></span></div>
    <div id="tt-extra"></div>
</div>

<style>
/* ═══ TOKENS ═══════════════════════════════════════════════════════ */
:root {
    --alm-navy:    #172554;
    --alm-blue:    #2563eb;
    --alm-card:    #ffffff;
    --alm-bg:      #f1f5f9;
    --alm-radius:  18px;
    --alm-shadow:  0 2px 16px rgba(15,23,42,.07);
    --alm-border:  #e2e8f0;
    --cell-size:   13px;
    --cell-gap:    3px;
    /* estado colores */
    --c-P:         #16a34a;
    --c-J:         #2563eb;
    --c-A:         #dc2626;
    --c-feriado:   #f59e0b;
    --c-sin:       #e2e8f0;
    --c-fuera:     #f8fafc;
}

/* ═══ WRAPPER ══════════════════════════════════════════════════════ */
.alm-wrap { width:100%; padding-bottom:60px; }

/* ═══ BENTO GRID ═══════════════════════════════════════════════════ */
/* Tarjeta genérica */
.alm-card {
    background: var(--alm-card);
    border-radius: var(--alm-radius);
    box-shadow: var(--alm-shadow);
    border: 1px solid var(--alm-border);
    padding: 22px 24px;
}
.alm-card-title {
    font-size: .82rem; font-weight: 800; color: #64748b;
    text-transform: uppercase; letter-spacing: .6px;
    display: flex; align-items: center; gap: 7px;
    margin-bottom: 16px;
}

/* ═══ BANNER ═══════════════════════════════════════════════════════ */
.alm-banner {
    background: linear-gradient(135deg,var(--alm-navy) 0%,#1e3a8a 50%,var(--alm-blue) 100%);
    border-radius: 20px; padding: 28px 32px; margin-bottom: 20px;
    display: flex; align-items: center; justify-content: space-between;
    flex-wrap: wrap;
    gap: 16px; position: relative; overflow: hidden;
}
.alm-banner::before {
    content:''; position:absolute; top:-50px; right:-50px;
    width:220px; height:220px; background:rgba(255,255,255,.04);
    border-radius:50%; pointer-events:none;
}

/* ═══ KPIs ══════════════════════════════════════════════════════════ */
.alm-kpis {
    display: grid;
    grid-template-columns: repeat(6,1fr);
    gap: 14px;
    margin-bottom: 20px;
}
.alm-kpi {
    background: var(--alm-card);
    border-radius: var(--alm-radius);
    box-shadow: var(--alm-shadow);
    border: 1px solid var(--alm-border);
    padding: 18px 18px 14px;
    display: flex; flex-direction: column; gap: 4px;
    transition: transform .2s, box-shadow .2s;
}
.alm-kpi:hover { transform: translateY(-3px); box-shadow: 0 10px 28px rgba(15,23,42,.1); }
.alm-kpi-val { font-size: 1.85rem; font-weight: 900; line-height: 1; }
.alm-kpi-lbl { font-size: .72rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: .4px; }

/* ═══ BENTO FILA 2: Progreso + Timeline ══════════════════════════ */
.alm-row2 {
    display: grid;
    grid-template-columns: 1fr 1fr;
    gap: 14px;
    margin-bottom: 20px;
}

/* Barra de progreso horas */
.alm-progress-track {
    height: 12px; border-radius: 20px; background: #e2e8f0; overflow: hidden; margin: 10px 0 6px;
}
.alm-progress-fill {
    height: 100%; border-radius: 20px;
    transition: width .8s cubic-bezier(.4,0,.2,1);
}

/* Timeline */
.alm-timeline {
    position: relative; padding: 12px 0;
}
.alm-tl-line {
    position: absolute; top: 50%; left: 0; right: 0;
    height: 4px; background: #e2e8f0; border-radius: 4px; transform: translateY(-50%);
}
.alm-tl-fill {
    position: absolute; top: 0; left: 0; height: 100%;
    background: linear-gradient(90deg, #16a34a, #22c55e);
    border-radius: 4px; transition: width .6s ease;
}
.alm-tl-dots {
    position: relative; display: flex; justify-content: space-between; align-items: center;
}
.alm-tl-dot {
    width: 14px; height: 14px; border-radius: 50%; border: 3px solid white;
    box-shadow: 0 0 0 2px currentColor; flex-shrink: 0;
}
.alm-tl-hoy {
    position: absolute; display: flex; flex-direction: column; align-items: center;
}

/* ═══ MULTI-MONTH GRID CALENDAR ══════════════════════════════════ */
.alm-row-mid {
    display: grid;
    grid-template-columns: 380px 1fr;
    gap: 20px;
    margin-bottom: 20px;
}
.alm-calendar-wrapper {
    background: var(--alm-card); border-radius: var(--alm-radius);
    box-shadow: var(--alm-shadow); border: 1px solid var(--alm-border);
    padding: 24px 28px;
}
/* Unified 1-Month Slider for Desktop and Mobile */
.alm-mm-mobile-controls {
    display: flex; align-items: center; justify-content: space-between;
    margin-bottom: 14px; padding: 0 4px;
}
.alm-mm-grid {
    display: flex; gap: 0;
}
.alm-mm-card {
    display: none; width: 100%; flex-shrink: 0;
    border: 1px solid #f1f5f9; border-radius: 14px; padding: 14px;
    background: #f8fafc; transition: transform .2s, box-shadow .2s;
}
.alm-mm-card.active-mobile {
    display: block;
}
.alm-mm-card:hover { transform: translateY(-3px); box-shadow: 0 4px 12px rgba(0,0,0,.05); border-color:#e2e8f0; }
.alm-mm-title {
    font-size: .85rem; font-weight: 800; color: #1e293b; text-transform: uppercase;
    letter-spacing: .5px; margin-bottom: 12px; text-align: center; display: none; /* Hide internal title as it's in the controls */
}
.alm-mm-days-header {
    display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px;
    margin-bottom: 8px;
}
.alm-mm-days-header div {
    font-size: .7rem; font-weight: 700; color: #94a3b8; text-align: center;
}
.alm-mm-days-grid {
    display: grid; grid-template-columns: repeat(5, 1fr); gap: 4px;
}
.alm-cell {
    aspect-ratio: 1; border-radius: 6px; display: flex; align-items: center; justify-content: center;
    font-size: .75rem; font-weight: 700; cursor: pointer; transition: transform .1s;
    color: transparent; /* color oculto si es empty. se sobreescribe abajo */
}
.alm-cell:hover { transform: scale(1.15); z-index: 10; box-shadow: 0 2px 6px rgba(0,0,0,.15); }
.alm-cell.is-empty { cursor: default; }
.alm-cell.is-empty:hover { transform: none; box-shadow: none; }

.alm-cell[data-e="fuera"]    { background: transparent; color: #cbd5e1; font-weight: 600; opacity: .5; }
.alm-cell[data-e="futuro"]   { background: #ffffff; color: #cbd5e1; font-weight: 600; border: 1px solid #e2e8f0; }
.alm-cell[data-e="sin_dato"] { background: var(--c-sin); color: #64748b; }
.alm-cell[data-e="P"]        { background: var(--c-P); color: white; }
.alm-cell[data-e="J"]        { background: var(--c-J); color: white; }
.alm-cell[data-e="A"]        { background: var(--c-A); color: white; }
.alm-cell[data-e="feriado"]  { background: var(--c-feriado); color: white; }

.alm-legend {
    display: flex; align-items: center; gap: 12px; flex-wrap: wrap;
    margin-top: 18px; font-size: .75rem; color: #64748b;
}
.alm-legend-item { display: flex; align-items: center; gap: 5px; font-weight: 600; }
.alm-legend-dot  { width: 12px; height: 12px; border-radius: 3px; flex-shrink: 0; }

/* ═══ HISTORIAL TABLE BENTO ════════════════════════════════════════ */
.hist-filters {
    display: flex; gap: 6px; flex-wrap: wrap; margin-bottom: 16px;
}
.hist-pill {
    padding: 5px 14px; border-radius: 20px; font-size: .77rem; font-weight: 700;
    cursor: pointer; border: 2px solid transparent; transition: all .15s;
    user-select: none;
}
.hist-pill[data-active="1"] { border-color: currentColor; }

/* Tabla historial */
.hist-table { width: 100%; border-collapse: collapse; font-size: .85rem; }
.hist-table th {
    padding: 9px 14px; text-align: left; font-size: .7rem; font-weight: 800;
    color: #64748b; text-transform: uppercase; letter-spacing: .5px;
    border-bottom: 2px solid #f1f5f9; white-space: nowrap;
}
.hist-table td {
    padding: 10px 14px; border-bottom: 1px solid #f8fafc; vertical-align: middle;
}
.hist-table tr:hover td { background: #f8fafc; }
.hist-table tr.hist-ausente td { background: #fff8f8; }
.hist-table tr.hist-ausente:hover td { background: #fee2e2; }

.hist-badge {
    display: inline-flex; align-items: center; gap: 4px;
    padding: 3px 10px; border-radius: 20px; font-size: .72rem; font-weight: 800;
}
.hist-metodo-badge {
    display: inline-flex; align-items: center; gap: 3px;
    padding: 2px 8px; border-radius: 8px; font-size: .68rem; font-weight: 700;
    background: #f1f5f9; color: #64748b;
}
.hist-empty {
    text-align: center; padding: 40px 20px;
    color: #94a3b8; font-size: .9rem;
}

/* ═══ DESGLOSE MENSUAL ═════════════════════════════════════════════ */
.alm-meses-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(90px, 1fr));
    gap: 10px;
}
.alm-mes-card {
    background: #f8fafc; border-radius: 12px; padding: 12px 10px;
    text-align: center; border: 2px solid transparent; transition: all .2s;
}
.alm-mes-card:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(0,0,0,.06); }
.alm-mes-card.has-data { border-color: #e2e8f0; }

/* ═══ TOOLTIP ══════════════════════════════════════════════════════ */
#almTooltip {
    position: fixed; z-index: 9999; pointer-events: none;
    background: #1e293b; color: #f8fafc; border-radius: 10px;
    padding: 10px 14px; font-size: .8rem; line-height: 1.5;
    box-shadow: 0 8px 24px rgba(0,0,0,.3);
    opacity: 0; transition: opacity .15s; max-width: 200px;
}
#almTooltip.show { opacity: 1; }
#almTooltip .tt-fecha  { font-weight: 700; font-size: .85rem; margin-bottom: 2px; }
#almTooltip .tt-estado { display: flex; align-items: center; gap: 5px; }
#almTooltip .tt-dot    { width: 8px; height: 8px; border-radius: 2px; flex-shrink: 0; }
#almTooltip #tt-extra  { color: #94a3b8; font-size: .75rem; margin-top: 2px; }

/* ═══ RESPONSIVE ════════════════════════════════════════════════════ */
@media (max-width: 1100px) {
    .alm-kpis { grid-template-columns: repeat(3, 1fr); }
    .alm-row-mid { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .alm-banner {
        flex-direction: column; align-items: flex-start;
        padding: 20px 18px; gap: 14px;
    }
    .alm-banner-actions {
        width: 100%; display: flex; flex-wrap: wrap; gap: 8px;
    }
    .alm-banner-actions form  { flex: 1; min-width: 140px; }
    .alm-banner-actions a     { flex: 1; min-width: 120px; justify-content: center; text-align: center; }
    .alm-kpis    { grid-template-columns: repeat(2, 1fr); gap: 10px; }
    .alm-row2    { grid-template-columns: 1fr; }
    .alm-heatmap-wrap { padding: 16px 12px; }
    :root { --cell-size: 11px; --cell-gap: 2px; }
    .hist-table th:nth-child(6),
    .hist-table td:nth-child(6)  { display: none; }  /* Ocultar obsrv en móvil */
    .alm-meses-grid { grid-template-columns: repeat(3,1fr); }
    /* Evaluación mini-radar: 2 cols en móvil */
    .eval-grupos-grid { grid-template-columns: repeat(2, 1fr) !important; }
    /* Justificaciones: header con wrap */
    .just-item-header { flex-wrap: wrap !important; gap: 6px !important; }
}
@media (max-width: 480px) {
    .alm-kpis { grid-template-columns: repeat(2,1fr); gap: 8px; }
    .alm-kpi-val { font-size: 1.5rem; }
    .alm-banner  { padding: 18px 14px; }
    .alm-banner h1 { font-size: 1.15rem; }
}
</style>

<div class="dashboard-container" style="width:100%; max-width:100%; padding:0;">
<div class="alm-wrap">

<!-- ═══ BANNER ════════════════════════════════════════════════════════ -->
<div class="alm-banner">
    <!-- Info pasante -->
    <div style="display:flex;align-items:center;gap:16px;z-index:1;min-width:0;">
        <div style="width:56px;height:56px;border-radius:16px;background:rgba(255,255,255,0.18);
                    display:flex;align-items:center;justify-content:center;
                    color:#fff;font-weight:900;font-size:1.2rem;flex-shrink:0;letter-spacing:1px;
                    border:2px solid rgba(255,255,255,0.25);">
            <?= htmlspecialchars($ini) ?>
        </div>
        <div style="min-width:0;">
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <h1 style="color:#fff;font-size:1.4rem;font-weight:800;margin:0;word-break:break-word;overflow-wrap:anywhere;">
                    <?= htmlspecialchars($nombre) ?>
                </h1>
                <span style="background:<?= $estadoCfg['bg'] ?>;color:<?= $estadoCfg['color'] ?>;
                             font-size:.65rem;font-weight:800;padding:3px 10px;border-radius:20px;
                             text-transform:uppercase;letter-spacing:.6px;white-space:nowrap;flex-shrink:0;">
                    <?= $estadoCfg['label'] ?>
                </span>
            </div>
            <div style="display:flex;flex-wrap:wrap;gap:10px;margin-top:6px;">
                <span style="color:rgba(255,255,255,.75);font-size:.8rem;display:flex;align-items:center;gap:5px;">
                    <i class="ti ti-id-badge"></i> C.I. <?= htmlspecialchars($pasante->cedula ?? '—') ?>
                </span>
                <?php if ($pasante->departamento ?? null): ?>
                <span style="background:rgba(255,255,255,.12);border-radius:20px;padding:2px 10px;
                             color:rgba(255,255,255,.85);font-size:.78rem;font-weight:600;">
                    <i class="ti ti-building"></i> <?= htmlspecialchars($pasante->departamento) ?>
                </span>
                <?php endif; ?>
                <?php if ($instNombre !== '—'): ?>
                <span style="background:rgba(255,255,255,.10);border-radius:20px;padding:2px 10px;
                             color:rgba(255,255,255,.75);font-size:.78rem;">
                    <i class="ti ti-school"></i> <?= htmlspecialchars($instNombre) ?>
                </span>
                <?php endif; ?>
                <?php if ($tutorNom !== '—'): ?>
                <span style="color:rgba(255,255,255,.65);font-size:.78rem;display:flex;align-items:center;gap:4px;">
                    <i class="ti ti-user-check"></i> <?= htmlspecialchars($tutorNom) ?>
                </span>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- Acciones -->
    <div class="alm-banner-actions" style="display:flex;align-items:center;gap:8px;z-index:1;flex-shrink:0;">

        <?php
        // ── Badge de Período ──
        $periodoEstadoColors = [
            'activo'  => ['bg' => 'rgba(16,185,129,.25)', 'color' => '#6ee7b7', 'dot' => '#10b981'],
            'cerrado' => ['bg' => 'rgba(148,163,184,.2)', 'color' => 'rgba(255,255,255,.6)', 'dot' => '#94a3b8'],
        ];
        $perEstado  = strtolower($periodo['estado'] ?? '');
        $perCfg     = $periodoEstadoColors[$perEstado] ?? $periodoEstadoColors['cerrado'];
        $perNombre  = $periodo['nombre'] ?? null;
        ?>

        <?php if ($perNombre): ?>
        <!-- Badge de Período / Cohorte -->
        <div style="background:<?= $perCfg['bg'] ?>;border:1px solid rgba(255,255,255,.18);
                    border-radius:12px;padding:8px 14px;display:flex;align-items:center;gap:8px;">
            <span style="width:8px;height:8px;border-radius:50%;background:<?= $perCfg['dot'] ?>;flex-shrink:0;
                         <?= $perEstado === 'activo' ? 'animation:alm-pulse-dot 1.5s infinite;' : '' ?>"></span>
            <span style="color:<?= $perCfg['color'] ?>;font-size:.8rem;font-weight:700;
                               white-space:nowrap;max-width:160px;overflow:hidden;text-overflow:ellipsis;"
                  title="<?= htmlspecialchars($perNombre) ?>">
                <?= htmlspecialchars(mb_strimwidth($perNombre, 0, 28, '…')) ?>
            </span>
        </div>
        <style>
        @keyframes alm-pulse-dot {
            0%,100% { opacity:1; transform:scale(1); }
            50%      { opacity:.5; transform:scale(1.4); }
        }
        </style>
        <?php endif; ?>

        <?php if (count($anios) > 1): ?>
        <!-- Select de año — solo si hay registros en múltiples años -->
        <form method="GET" style="display:flex;align-items:center;">
            <select name="anio" onchange="this.form.submit()"
                    style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.25);
                           color:#fff;border-radius:10px;padding:8px 14px;font-size:.85rem;
                           font-weight:700;cursor:pointer;appearance:none;text-align:center;"
                    title="Cambiar año de visualización">
                <?php foreach (array_reverse($anios) as $a): ?>
                <option value="<?= $a ?>" <?= $a === $anio ? 'selected' : '' ?> style="background:#1e3a8a;">
                    <?= $a ?>
                </option>
                <?php endforeach; ?>
            </select>
        </form>
        <?php endif; ?>

        <a href="<?= URLROOT ?>/asistencias"
           class="pjax-link"
           style="background:rgba(255,255,255,.15);border:1px solid rgba(255,255,255,.2);
                  border-radius:10px;padding:9px 18px;color:#fff;font-size:.84rem;
                  font-weight:600;text-decoration:none;display:flex;align-items:center;gap:6px;
                  transition:background .2s;white-space:nowrap;"
           onmouseover="this.style.background='rgba(255,255,255,.25)'"
           onmouseout="this.style.background='rgba(255,255,255,.15)'">
            <i class="ti ti-arrow-left"></i> Asistencias
        </a>
    </div>
</div>

<!-- ═══ KPIs ═══════════════════════════════════════════════════════════ -->
<div class="alm-kpis">

    <div class="alm-kpi" style="border-left:4px solid <?= $kpiColor ?>;">
        <div class="alm-kpi-val" style="color:<?= $kpiColor ?>;"><?= $pct ?>%</div>
        <div class="alm-kpi-lbl">Asistencia <?= $anio ?></div>
        <div style="height:5px;border-radius:20px;background:#e2e8f0;overflow:hidden;margin-top:6px;">
            <div style="height:100%;border-radius:20px;background:<?= $kpiColor ?>;width:<?= $pct ?>%;transition:width .8s ease;"></div>
        </div>
    </div>

    <div class="alm-kpi" style="border-left:4px solid #16a34a;">
        <div class="alm-kpi-val" style="color:#16a34a;"><?= $stats['P'] ?></div>
        <div class="alm-kpi-lbl">Presentes</div>
        <div style="height:5px;border-radius:20px;background:#dcfce7;overflow:hidden;margin-top:6px;">
            <div style="height:100%;border-radius:20px;background:#16a34a;width:<?= $stats['laborables']>0?round($stats['P']/$stats['laborables']*100):0 ?>%;"></div>
        </div>
    </div>

    <div class="alm-kpi" style="border-left:4px solid #2563eb;">
        <div class="alm-kpi-val" style="color:#2563eb;"><?= $stats['J'] ?></div>
        <div class="alm-kpi-lbl">Justificados</div>
        <div style="height:5px;border-radius:20px;background:#dbeafe;overflow:hidden;margin-top:6px;">
            <div style="height:100%;border-radius:20px;background:#2563eb;width:<?= $stats['laborables']>0?round($stats['J']/$stats['laborables']*100):0 ?>%;"></div>
        </div>
    </div>

    <div class="alm-kpi" style="border-left:4px solid #dc2626;">
        <div class="alm-kpi-val" style="color:#dc2626;"><?= $stats['A'] ?></div>
        <div class="alm-kpi-lbl">Ausentes</div>
        <div style="height:5px;border-radius:20px;background:#fee2e2;overflow:hidden;margin-top:6px;">
            <div style="height:100%;border-radius:20px;background:#dc2626;width:<?= $stats['laborables']>0?round($stats['A']/$stats['laborables']*100):0 ?>%;"></div>
        </div>
    </div>

    <div class="alm-kpi" style="border-left:4px solid #7c3aed;">
        <div class="alm-kpi-val" style="color:#7c3aed;"><?= $rachaMax ?></div>
        <div class="alm-kpi-lbl">Racha máxima</div>
        <div style="font-size:.72rem;color:#94a3b8;margin-top:4px;">días sin faltar (seguidos)</div>
    </div>

    <div class="alm-kpi" style="border-left:4px solid #0891b2;">
        <div class="alm-kpi-val" style="color:#0891b2;"><?= $rachaActual ?></div>
        <div class="alm-kpi-lbl">Racha actual</div>
        <div style="font-size:.72rem;color:#94a3b8;margin-top:4px;">días activos consecutivos</div>
    </div>

</div>

<!-- ═══ FILA 2: Progreso Horas + Timeline ══════════════════════════════ -->
<div class="alm-row2">

    <!-- Card Progreso de Horas -->
    <div class="alm-card">
        <div class="alm-card-title">
            <i class="ti ti-clock-hour-4" style="color:#2563eb;"></i>
            Progreso de Horas de Pasantía
        </div>
        <?php
        $pctHorasColor = $pctHoras >= 90 ? '#16a34a' : ($pctHoras >= 60 ? '#d97706' : '#2563eb');
        ?>
        <div style="display:flex;align-items:flex-end;justify-content:space-between;margin-bottom:4px;">
            <div>
                <span style="font-size:2.4rem;font-weight:900;color:<?= $pctHorasColor ?>;line-height:1;"><?= $horasAcum ?></span>
                <span style="font-size:1rem;color:#94a3b8;font-weight:600;"> / <?= $horasMeta ?>h</span>
            </div>
            <span style="background:<?= $pctHorasColor ?>18;color:<?= $pctHorasColor ?>;
                         font-size:.8rem;font-weight:800;padding:6px 14px;border-radius:10px;">
                <?= $pctHoras ?>%
            </span>
        </div>
        <div class="alm-progress-track">
            <div class="alm-progress-fill" style="width:<?= $pctHoras ?>%;background:<?= $pctHorasColor ?>;"></div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#94a3b8;margin-top:4px;">
            <span>0h</span>
            <span><?= $horasMeta ?>h meta</span>
        </div>
        <?php if ($horasMeta - $horasAcum > 0): ?>
        <div style="margin-top:14px;padding:10px 14px;background:#f8fafc;border-radius:10px;
                    border:1px dashed #e2e8f0;font-size:.78rem;color:#64748b;">
            <i class="ti ti-info-circle" style="color:#2563eb;"></i>
            Faltan <strong style="color:#1e293b;"><?= $horasMeta - $horasAcum ?> horas</strong>
            para completar la pasantía
            <?php if ($stats['P'] > 0): ?>
            · promedio de <strong><?= round($horasAcum / max(1,$stats['P']+$stats['J']), 1) ?>h/día</strong>
            <?php endif; ?>
        </div>
        <?php else: ?>
        <div style="margin-top:14px;padding:10px 14px;background:#dcfce7;border-radius:10px;
                    border:1px solid #bbf7d0;font-size:.78rem;color:#16a34a;font-weight:700;">
            <i class="ti ti-circle-check"></i> ¡Meta de horas completada!
        </div>
        <?php endif; ?>
    </div>

    <!-- Card Timeline Pasantía -->
    <div class="alm-card">
        <div class="alm-card-title">
            <i class="ti ti-timeline" style="color:#7c3aed;"></i>
            Timeline de Pasantía
        </div>
        <?php if ($pasante->fecha_inicio): ?>
        <div style="display:grid;grid-template-columns:1fr 1fr;gap:10px;margin-bottom:20px;">
            <div style="background:#f0fdf4;border-radius:12px;padding:12px 14px;border:1px solid #bbf7d0;">
                <div style="font-size:.65rem;font-weight:800;color:#16a34a;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Inicio</div>
                <div style="font-size:.95rem;font-weight:800;color:#15803d;">
                    <?= date('d/m/Y', strtotime($pasante->fecha_inicio)) ?>
                </div>
            </div>
            <div style="background:<?= $pasante->fecha_fin ? '#f0f4ff' : '#f8fafc' ?>;border-radius:12px;padding:12px 14px;border:1px solid <?= $pasante->fecha_fin ? '#c7d2fe' : '#e2e8f0' ?>;">
                <div style="font-size:.65rem;font-weight:800;color:<?= $pasante->fecha_fin ? '#4f46e5' : '#94a3b8' ?>;text-transform:uppercase;letter-spacing:.5px;margin-bottom:2px;">Fin Estimado</div>
                <div style="font-size:.95rem;font-weight:800;color:<?= $pasante->fecha_fin ? '#312e81' : '#94a3b8' ?>;">
                    <?= $pasante->fecha_fin ? date('d/m/Y', strtotime($pasante->fecha_fin)) : 'Sin definir' ?>
                </div>
            </div>
        </div>

        <?php if ($diasTotal > 0): ?>
        <!-- Barra Timeline -->
        <div style="position:relative;height:32px;background:#f1f5f9;border-radius:20px;overflow:hidden;margin-bottom:10px;">
            <div style="position:absolute;top:0;left:0;height:100%;width:<?= min(100,$pctTiempo) ?>%;
                        background:linear-gradient(90deg,#16a34a,#22c55e);border-radius:20px;
                        transition:width .8s ease;"></div>
            <div style="position:absolute;inset:0;display:flex;align-items:center;justify-content:center;
                        font-size:.72rem;font-weight:800;color:<?= $pctTiempo>40?'white':'#374151' ?>;">
                <?= $pctTiempo ?>% completado
            </div>
        </div>
        <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#94a3b8;margin-bottom:14px;">
            <span><?= $diasTrans ?> día<?= $diasTrans !== 1 ? 's' : '' ?> hábil<?= $diasTrans !== 1 ? 'es' : '' ?> transcurrido<?= $diasTrans !== 1 ? 's' : '' ?></span>
            <span><?= $diasRest ?> día<?= $diasRest !== 1 ? 's' : '' ?> hábil<?= $diasRest !== 1 ? 'es' : '' ?> restante<?= $diasRest !== 1 ? 's' : '' ?></span>
        </div>
        <?php endif; ?>

        <div style="display:flex;gap:8px;flex-wrap:wrap;">
            <span style="background:<?= $estadoCfg['bg'] ?>;color:<?= $estadoCfg['color'] ?>;
                         padding:5px 14px;border-radius:20px;font-size:.75rem;font-weight:800;">
                <i class="ti ti-point-filled"></i> <?= $estadoCfg['label'] ?>
            </span>
            <?php if ($diasRest > 0 && $estadoPas === 'Activo'): ?>
            <span style="background:#f1f5f9;color:#475569;padding:5px 14px;border-radius:20px;font-size:.75rem;font-weight:700;">
                <i class="ti ti-clock"></i> <?= $diasRest ?> día<?= $diasRest !== 1 ? 's' : '' ?> hábil<?= $diasRest !== 1 ? 'es' : '' ?> restante<?= $diasRest !== 1 ? 's' : '' ?>
            </span>
            <?php endif; ?>
        </div>

        <?php else: ?>
        <div style="text-align:center;padding:24px;color:#94a3b8;">
            <i class="ti ti-calendar-off" style="font-size:2rem;display:block;margin-bottom:8px;"></i>
            <p style="font-size:.85rem;margin:0;">Sin fecha de inicio registrada</p>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ═══ LAYOUT EN GRAN GRILLA: CALENDARIO + HISTORIAL ═══ -->
<div class="alm-row-mid">

<!-- COLUMNA IZQ: CALENDARIO -->
<div class="alm-calendar-col">
<!-- ═══ CALENDARIO MULTI-MONTH BENTO ═══════════════════════════════════════ -->
<div class="alm-calendar-wrapper" style="margin-bottom: 20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:20px;">
        <div style="font-size:1rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:8px;">
            <i class="ti ti-calendar-stats" style="color:#2563eb;font-size:1.1rem;"></i>
            Calendario de Asistencias <?= $anio ?>
            <span style="font-size:.75rem;color:#94a3b8;font-weight:500;">
                <?= $stats['laborables'] ?> días laborables
            </span>
        </div>
    </div>

    <div class="alm-mm-container" id="alm-mm-container">
        <!-- Controles Móviles -->
        <div class="alm-mm-mobile-controls">
            <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:10px;font-weight:700;padding:6px 14px;" onclick="almSlideMes(-1)"><i class="ti ti-chevron-left"></i></button>
            <span id="alm-mm-mes-lbl" style="font-weight:800;font-size:.95rem;color:#1e3a8a;text-transform:uppercase;">Enero</span>
            <button type="button" class="btn btn-sm btn-outline-secondary" style="border-radius:10px;font-weight:700;padding:6px 14px;" onclick="almSlideMes(1)"><i class="ti ti-chevron-right"></i></button>
        </div>

        <div class="alm-mm-grid" id="alm-mm-grid">
        <?php
        // Construir mapa lineal por fecha para fácil consulta
        $mapaFechas = [];
        foreach ($grilla as $wk => $dias) {
            foreach ($dias as $dow => $cell) {
                if ($cell) {
                    $mapaFechas[$cell['fecha']] = $cell;
                }
            }
        }

        $estadoLabel = ['P'=>'Presente','A'=>'Ausente','J'=>'Justificado',
                        'feriado'=>'Feriado','sin_dato'=>'Sin registro',
                        'futuro'=>'Futuro','fuera'=>'Fuera del período'];
        $estadoColor = ['P'=>'#16a34a','A'=>'#dc2626','J'=>'#2563eb',
                        'feriado'=>'#f59e0b','sin_dato'=>'#e2e8f0',
                        'futuro'=>'#f1f5f9','fuera'=>'transparent'];
        $mesesEsFull = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio',
                        'Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];

        for ($m = 1; $m <= 12; $m++):
            $dt = new DateTime("$anio-" . sprintf('%02d', $m) . "-01");
            $diasEnMes = $dt->format('t');
        ?>
            <div class="alm-mm-card <?= $m === 1 ? 'active-mobile' : '' ?>" data-mes="<?= $m ?>" data-nombre="<?= $mesesEsFull[$m] ?>">
                <div class="alm-mm-title" style="display:none;"><?= $mesesEsFull[$m] ?></div>
                <div class="alm-mm-title pc-only-title"><?= $mesesEsFull[$m] ?></div>
                <div class="alm-mm-days-header">
                    <div>L</div><div>M</div><div>M</div><div>J</div><div>V</div>
                </div>
                <div class="alm-mm-days-grid">
                    <?php
                    $primerDiaDow = (int)$dt->format('N'); // 1 = Lunes
                    // Rellenos en blanco hasta el primer día laborable (lun a vie)
                    $espacios = ($primerDiaDow <= 5) ? ($primerDiaDow - 1) : 0;
                    for ($i = 0; $i < $espacios; $i++) {
                        echo '<div></div>';
                    }

                    for ($d = 1; $d <= $diasEnMes; $d++) {
                        $currentDt = new DateTime("$anio-" . sprintf('%02d', $m) . "-" . sprintf('%02d', $d));
                        $dow = (int)$currentDt->format('N');
                        if ($dow > 5) continue; // Solo lunes a viernes

                        $fechaS = $currentDt->format('Y-m-d');
                        $cell = $mapaFechas[$fechaS] ?? null;

                        if ($cell) {
                            $e = $cell['estado'];
                            $hora = $cell['hora'] ?? '';
                            $metodo = $cell['metodo'] ?? '';
                            $ttEst = $estadoLabel[$e] ?? $e;
                            $ttColor = $estadoColor[$e] ?? '#94a3b8';
                            $ttExtra = $hora ? $hora . ($metodo ? ' · '.$metodo : '') : '';
                            
                            if ($e === 'feriado' && !empty($cell['feriadoNombre'])) {
                                $ttExtra = $cell['feriadoNombre'];
                            }

                            $isEmptyClass = ($e === 'fuera' || $e === 'futuro') ? ' is-empty' : '';

                            echo '<div class="alm-cell'.$isEmptyClass.'"
                                       data-e="'.htmlspecialchars($e).'"
                                       data-f="'.htmlspecialchars($fechaS).'"
                                       data-est="'.htmlspecialchars($ttEst).'"
                                       data-color="'.htmlspecialchars($ttColor).'"
                                       data-extra="'.htmlspecialchars($ttExtra).'">'.$d.'</div>';
                        } else {
                            echo '<div class="alm-cell is-empty"></div>'; // Fuera de grilla
                        }
                    }
                    ?>
                </div>
            </div>
        <?php endfor; ?>
        </div>
    </div>

    <!-- Leyenda -->
    <div class="alm-legend">
        <span style="color:#94a3b8;">Leyenda:</span>
        <div class="alm-legend-item"><div class="alm-legend-dot" style="background:#16a34a;"></div> Presente</div>
        <div class="alm-legend-item"><div class="alm-legend-dot" style="background:#2563eb;"></div> Justificado</div>
        <div class="alm-legend-item"><div class="alm-legend-dot" style="background:#dc2626;"></div> Ausente</div>
        <div class="alm-legend-item" title="Basado en el Almanaque Oficial de Feriados de Venezuela" style="cursor:help;">
            <div class="alm-legend-dot" style="background:#f59e0b;"></div> Feriado
        </div>
        <div class="alm-legend-item"><div class="alm-legend-dot" style="background:#e2e8f0;"></div> Sin registro</div>
    </div>
</div>
<style>
@media (max-width: 768px) { .pc-only-title { display: none !important; } }
</style>
</div>
<!-- /COLUMNA IZQ -->

<!-- COLUMNA DER: HISTORIAL -->
<div class="alm-hist-col" style="min-width: 0;">
<!-- ═══ HISTORIAL CRONOLÓGICO ═══════════════════════════════════════════ -->
<div class="alm-card" style="height: 100%; margin-bottom: 20px;">
    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:16px;">
        <div class="alm-card-title" style="margin-bottom:0;">
            <i class="ti ti-list-details" style="color:#1e3a8a;"></i>
            Historial de Asistencias
            <span style="background:#f1f5f9;color:#475569;font-size:.68rem;padding:2px 10px;border-radius:20px;font-weight:700;text-transform:none;letter-spacing:0;">
                <?= count($historialCompleto) ?> registros
            </span>
        </div>
        <!-- Filtros por estado y fecha -->
        <div class="hist-filters" id="histFilters">
            <!-- Buscador por fecha -->
            <div style="position:relative; margin-right:auto;">
                <i class="ti ti-calendar" style="position:absolute; left:10px; top:50%; transform:translateY(-50%); color:#94a3b8; z-index:2; pointer-events:none;"></i>
                <input type="text" id="filtroFechaHistorial" class="form-control form-control-sm" placeholder="Buscar por fecha..." style="width:200px; padding-left:32px; border-radius:20px; font-size:.8rem;">
            </div>
            
            <button type="button" id="btnLimpiarFechaAlm" class="btn btn-sm btn-light" style="display:none; border-radius:20px; font-size:.7rem; font-weight:700;"><i class="ti ti-x"></i> Limpiar</button>
            <div class="hist-pill" data-filtro="todos" data-active="1"
                 style="background:#1e2b58;color:white;"
                 onclick="filtrarHistorial('todos',this)">
                <i class="ti ti-list"></i> Todos
            </div>
            <div class="hist-pill" data-filtro="Presente" data-active="0"
                 style="background:#dcfce7;color:#16a34a;"
                 onclick="filtrarHistorial('Presente',this)">
                <i class="ti ti-check"></i> Presentes
            </div>
            <div class="hist-pill" data-filtro="Ausente" data-active="0"
                 style="background:#fee2e2;color:#dc2626;"
                 onclick="filtrarHistorial('Ausente',this)">
                <i class="ti ti-x"></i> Ausentes
            </div>
            <div class="hist-pill" data-filtro="Justificado" data-active="0"
                 style="background:#dbeafe;color:#2563eb;"
                 onclick="filtrarHistorial('Justificado',this)">
                <i class="ti ti-file-check"></i> Justificados
            </div>
        </div>
    </div>

    <?php if (empty($historialCompleto)): ?>
    <div class="hist-empty">
        <i class="ti ti-ghost" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.3;"></i>
        <p style="margin:0;font-weight:600;">Sin registros de asistencia aún</p>
        <p style="margin:4px 0 0;font-size:.8rem;color:#b0bec5;">Los registros aparecerán conforme el pasante marque asistencia</p>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="hist-table" id="histTable">
        <thead>
            <tr>
                <th>Fecha</th>
                <th>Día</th>
                <th>Estado</th>
                <th>Hora Entrada</th>
                <th>Método</th>
                <th>Observación</th>
            </tr>
        </thead>
        <tbody>
        <?php
        $mesesEs = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
                    '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
        $diasEs  = ['Monday'=>'Lunes','Tuesday'=>'Martes','Wednesday'=>'Miércoles',
                    'Thursday'=>'Jueves','Friday'=>'Viernes','Saturday'=>'Sábado','Sunday'=>'Domingo'];
        foreach ($historialCompleto as $h):
            $est     = $h->estado ?? '—';
            $fecha   = $h->fecha  ?? '';
            $horaEnt = $h->hora_entrada  ?? ($h->hora_registro ?? null);
            $horaFmt = $horaEnt ? substr($horaEnt, 0, 5) : '—';
            $metodo  = $h->metodo        ?? '—';
            $obs     = trim($h->observacion ?? $h->motivo_justificacion ?? '');
            $diaEn   = date('l', strtotime($fecha));
            $diaEs   = $diasEs[$diaEn] ?? $diaEn;
            $mes     = substr($fecha, 5, 2);
            $fechaFmt= date('d', strtotime($fecha)) . ' ' . ($mesesEs[$mes]??'') . ' '. date('Y', strtotime($fecha));

            $rowClass = $est === 'Ausente' ? 'hist-ausente' : '';

            $badgeCfg = [
                'Presente'   => ['bg'=>'#dcfce7','color'=>'#15803d','ico'=>'ti-check'],
                'Ausente'    => ['bg'=>'#fee2e2','color'=>'#dc2626','ico'=>'ti-x'],
                'Justificado'=> ['bg'=>'#dbeafe','color'=>'#1d4ed8','ico'=>'ti-file-check'],
            ];
            $bc = $badgeCfg[$est] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','ico'=>'ti-minus'];

            $metodoCfg = [
                'Kiosco' => ['bg'=>'#f0f4ff','color'=>'#4338ca','ico'=>'ti-device-tablet'],
                'Manual' => ['bg'=>'#fef3c7','color'=>'#d97706','ico'=>'ti-pencil'],
                'AutoFill' => ['bg'=>'#f0fdf4','color'=>'#16a34a','ico'=>'ti-refresh'],
            ];
            $mc = $metodoCfg[$metodo] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','ico'=>'ti-help'];
        ?>
        <tr class="<?= $rowClass ?>" data-estado="<?= htmlspecialchars($est) ?>">
            <td style="font-weight:700;color:#1e293b;white-space:nowrap;"><?= htmlspecialchars($fechaFmt) ?></td>
            <td style="color:#64748b;font-size:.8rem;"><?= htmlspecialchars($diaEs) ?></td>
            <td>
                <span class="hist-badge" style="background:<?= $bc['bg'] ?>;color:<?= $bc['color'] ?>;">
                    <i class="ti <?= $bc['ico'] ?>" style="font-size:.75rem;"></i>
                    <?= htmlspecialchars($est) ?>
                </span>
            </td>
            <td>
                <?php if ($horaFmt !== '—'): ?>
                <span style="font-family:monospace;font-weight:700;color:#1e293b;font-size:.9rem;">
                    <?= htmlspecialchars($horaFmt) ?>
                </span>
                <?php else: ?>
                <span style="color:#cbd5e1;font-size:.8rem;">—</span>
                <?php endif; ?>
            </td>
            <td>
                <span class="hist-metodo-badge" style="background:<?= $mc['bg'] ?>;color:<?= $mc['color'] ?>;">
                    <i class="ti <?= $mc['ico'] ?>" style="font-size:.75rem;"></i>
                    <?= htmlspecialchars($metodo) ?>
                </span>
            </td>
            <td style="color:#64748b;font-size:.8rem;max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;"
                title="<?= htmlspecialchars($obs) ?>">
                <?= $obs ? htmlspecialchars($obs) : '<span style="color:#cbd5e1">—</span>' ?>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>
</div>
<!-- /COLUMNA DER -->
</div><!-- /alm-row-mid -->

<!-- ═══ DESGLOSE MENSUAL ══════════════════════════════════════════════════ -->
<?php
$statsMes = [];
foreach ($grilla as $wk => $dias) {
    foreach ($dias as $dow => $cell) {
        if (!$cell || !in_array($cell['estado'], ['P','A','J'])) continue;
        $mes = (int)substr($cell['fecha'], 5, 2);
        if (!isset($statsMes[$mes])) $statsMes[$mes] = ['P'=>0,'A'=>0,'J'=>0];
        $statsMes[$mes][$cell['estado']]++;
    }
}
$mesesNombres = ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];
if (!empty($statsMes)):
?>
<div class="alm-card">
    <div class="alm-card-title" style="margin-bottom:16px;">
        <i class="ti ti-chart-bar" style="color:#2563eb;"></i>
        Desglose mensual <?= $anio ?>
    </div>
    <div class="alm-meses-grid">
    <?php for ($m = 1; $m <= 12; $m++):
        $ms  = $statsMes[$m] ?? ['P'=>0,'A'=>0,'J'=>0];
        $tot = $ms['P'] + $ms['A'] + $ms['J'];
        $pcm = $tot > 0 ? round(($ms['P']+$ms['J'])/$tot*100) : null;
        $col = $pcm === null ? '#94a3b8' : ($pcm >= 90 ? '#16a34a' : ($pcm >= 75 ? '#d97706' : '#dc2626'));
    ?>
    <div class="alm-mes-card <?= $tot > 0 ? 'has-data' : '' ?>">
        <div style="font-size:.72rem;font-weight:800;color:#64748b;margin-bottom:6px;text-transform:uppercase;">
            <?= $mesesNombres[$m-1] ?>
        </div>
        <?php if ($tot > 0): ?>
        <div style="font-size:1.35rem;font-weight:900;color:<?= $col ?>;"><?= $pcm ?>%</div>
        <div style="font-size:.66rem;color:#94a3b8;margin-top:3px;display:flex;justify-content:center;gap:4px;">
            <span style="color:#16a34a;font-weight:700;"><?= $ms['P'] ?>P</span>
            <span style="color:#dc2626;font-weight:700;"><?= $ms['A'] ?>A</span>
            <?php if ($ms['J']>0): ?><span style="color:#2563eb;font-weight:700;"><?= $ms['J'] ?>J</span><?php endif; ?>
        </div>
        <?php else: ?>
        <div style="font-size:.8rem;color:#cbd5e1;margin-top:4px;">—</div>
        <?php endif; ?>
    </div>
    <?php endfor; ?>
    </div>
</div>
<?php endif; ?>

<!-- ═══ FILA 3: JUSTIFICACIONES + EVALUACIONES (BENTO) ════════════════════ -->
<?php
// extract($data) en Controller::view() ya las expone como variables directas
if (!isset($justificaciones)) $justificaciones = [];
if (!isset($evaluaciones))    $evaluaciones    = [];

// Meses en español (disponible para ambas cards)
$mesesEs2 = ['01'=>'Ene','02'=>'Feb','03'=>'Mar','04'=>'Abr','05'=>'May','06'=>'Jun',
             '07'=>'Jul','08'=>'Ago','09'=>'Sep','10'=>'Oct','11'=>'Nov','12'=>'Dic'];
?>
<div style="display:grid; grid-template-columns: 1fr 1fr; gap:14px; margin-bottom:20px;" class="alm-row-jeval">
<style>
@media (max-width: 900px) { .alm-row-jeval { grid-template-columns: 1fr !important; } }
</style>

<!-- ────── CARD: Justificaciones ────── -->
<div class="alm-card" style="min-height:280px;">
    <div class="alm-card-title" style="margin-bottom:14px;">
        <i class="ti ti-file-certificate" style="color:#d97706;"></i>
        Justificaciones
        <span style="background:#fef3c7;color:#d97706;font-size:.68rem;padding:2px 10px;border-radius:20px;font-weight:700;text-transform:none;letter-spacing:0;">
            <?= count($justificaciones) ?>
        </span>
    </div>

    <?php if (empty($justificaciones)): ?>
    <div style="text-align:center;padding:32px 20px;color:#94a3b8;">
        <i class="ti ti-check-circle" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3;color:#16a34a;"></i>
        <p style="margin:0;font-weight:600;font-size:.88rem;">Sin justificaciones registradas</p>
        <p style="margin:4px 0 0;font-size:.75rem;color:#b0bec5;">No hay ausencias justificadas este período</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:10px;max-height:420px;overflow-y:auto;padding-right:4px;">
    <?php
    foreach ($justificaciones as $j):
        $fStr   = $j->fecha ?? '';
        $mes    = $fStr ? ($mesesEs2[substr($fStr,5,2)] ?? '') : '';
        $dia    = $fStr ? (int)substr($fStr,8,2) : '—';
        $yr     = $fStr ? substr($fStr,0,4) : '';
        $fechaFmt2 = "$dia $mes $yr";
        $motivo = $j->motivo_justificacion ?? '—';
        $ruta   = $j->ruta_evidencia ?? null;
    ?>
    <div style="background:#fffbeb;border-left:4px solid #f59e0b;border-radius:10px;padding:12px 14px;
                transition:box-shadow .2s;"
         onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.07)'"
         onmouseout="this.style.boxShadow='none'">
        <div class="just-item-header" style="display:flex;align-items:flex-start;justify-content:space-between;margin-bottom:6px;gap:8px;">
            <span style="font-size:.72rem;font-weight:800;color:#d97706;display:flex;align-items:center;gap:4px;">
                <i class="ti ti-calendar-event" style="font-size:.85rem;"></i>
                <?= htmlspecialchars($fechaFmt2) ?>
            </span>
            <?php if ($ruta): ?>
            <a href="<?= URLROOT ?><?= htmlspecialchars($ruta) ?>" target="_blank"
               style="background:#fde68a;color:#92400e;font-size:.68rem;font-weight:800;padding:3px 10px;
                      border-radius:20px;text-decoration:none;display:flex;align-items:center;gap:4px;
                      transition:background .15s;"
               onmouseover="this.style.background='#fcd34d'"
               onmouseout="this.style.background='#fde68a'">
                <?php
                $ext = strtolower(pathinfo($ruta, PATHINFO_EXTENSION));
                $icon = $ext === 'pdf' ? 'ti-file-type-pdf' : 'ti-photo';
                ?>
                <i class="ti <?= $icon ?>"></i>
                <?= strtoupper($ext) === 'PDF' ? 'Ver PDF' : 'Ver Imagen' ?>
            </a>
            <?php else: ?>
            <span style="font-size:.65rem;color:#94a3b8;font-weight:600;">Sin evidencia</span>
            <?php endif; ?>
        </div>
        <p style="margin:0;font-size:.8rem;color:#92400e;line-height:1.45;">
            <?= htmlspecialchars(mb_strimwidth($motivo, 0, 120, '…')) ?>
        </p>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<!-- ────── CARD: Evaluaciones ────── -->
<div class="alm-card" style="min-height:280px;">
    <div class="alm-card-title" style="margin-bottom:14px;">
        <i class="ti ti-award" style="color:#7c3aed;"></i>
        Evaluaciones
        <span style="background:#f3e8ff;color:#7c3aed;font-size:.68rem;padding:2px 10px;border-radius:20px;font-weight:700;text-transform:none;letter-spacing:0;">
            <?= count($evaluaciones) ?>
        </span>
    </div>

    <?php if (empty($evaluaciones)): ?>
    <div style="text-align:center;padding:32px 20px;color:#94a3b8;">
        <i class="ti ti-chart-radar" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3;color:#7c3aed;"></i>
        <p style="margin:0;font-weight:600;font-size:.88rem;">Sin evaluaciones registradas</p>
        <p style="margin:4px 0 0;font-size:.75rem;color:#b0bec5;">Aún no se ha aplicado ninguna evaluación</p>
    </div>
    <?php else: ?>
    <div style="display:flex;flex-direction:column;gap:12px;max-height:420px;overflow-y:auto;padding-right:4px;">
    <?php foreach ($evaluaciones as $ev):
        $prom = (float)($ev->promedio_final ?? 0);
        $promPct = min(100, $prom / 5 * 100);
        $promColor = $prom >= 4 ? '#16a34a' : ($prom >= 3 ? '#d97706' : '#dc2626');
        $promBg    = $prom >= 4 ? '#dcfce7' : ($prom >= 3 ? '#fef3c7' : '#fee2e2');

        $fechaEv = $ev->fecha_evaluacion ?? '';
        $mesEv   = $fechaEv ? ($mesesEs2[substr($fechaEv,5,2)] ?? '') : '';
        $diaEv   = $fechaEv ? (int)substr($fechaEv,8,2) : '—';
        $yrEv    = $fechaEv ? substr($fechaEv,0,4) : '';
        $fechaEvFmt = "$diaEv $mesEv $yrEv";

        // Criterios agrupados
        $criteriosGrupos = [
            'Actitud'       => ['criterio_iniciativa','criterio_interes','criterio_companerismo','criterio_cooperacion'],
            'Conocimiento'  => ['criterio_conocimiento','criterio_analisis','criterio_aprendizaje'],
            'Comunicación'  => ['criterio_comunicacion','criterio_presentacion'],
            'Puntualidad'   => ['criterio_puntualidad'],
            'Resultados'    => ['criterio_desarrollo','criterio_analisis_res','criterio_conclusiones','criterio_recomendacion'],
        ];
    ?>
    <div style="background:#faf5ff;border-left:4px solid #7c3aed;border-radius:10px;padding:14px;
                transition:box-shadow .2s;"
         onmouseover="this.style.boxShadow='0 4px 12px rgba(0,0,0,.07)'"
         onmouseout="this.style.boxShadow='none'">

        <!-- Header evaluación -->
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:10px;">
            <div>
                <span style="font-size:.72rem;font-weight:800;color:#7c3aed;display:flex;align-items:center;gap:4px;">
                    <i class="ti ti-calendar-event" style="font-size:.85rem;"></i>
                    <?= htmlspecialchars($fechaEvFmt) ?>
                    <?php if ($ev->lapso_academico ?? null): ?>
                    <span style="background:#ede9fe;padding:1px 7px;border-radius:10px;font-size:.62rem;letter-spacing:.3px;">
                        <?= htmlspecialchars($ev->lapso_academico) ?>
                    </span>
                    <?php endif; ?>
                </span>
                <?php if ($ev->tutor_nombre ?? null): ?>
                <span style="font-size:.68rem;color:#64748b;display:flex;align-items:center;gap:3px;margin-top:2px;">
                    <i class="ti ti-user-check" style="font-size:.75rem;"></i>
                    <?= htmlspecialchars(trim($ev->tutor_nombre)) ?>
                </span>
                <?php endif; ?>
            </div>
            <!-- Promedio badge circular -->
            <div style="width:52px;height:52px;border-radius:50%;background:<?= $promBg ?>;
                        border:3px solid <?= $promColor ?>;display:flex;flex-direction:column;
                        align-items:center;justify-content:center;flex-shrink:0;">
                <span style="font-size:1.1rem;font-weight:900;color:<?= $promColor ?>;line-height:1;"><?= number_format($prom,1) ?></span>
                <span style="font-size:.52rem;font-weight:700;color:<?= $promColor ?>;opacity:.75;">/5</span>
            </div>
        </div>

        <!-- Barra de promedio -->
        <div style="height:6px;border-radius:20px;background:#ede9fe;overflow:hidden;margin-bottom:8px;">
            <div style="height:100%;border-radius:20px;background:<?= $promColor ?>;width:<?= $promPct ?>%;transition:width .8s ease;"></div>
        </div>

        <!-- Mini-radar de grupos -->
        <div class="eval-grupos-grid" style="display:grid;grid-template-columns:repeat(3,1fr);gap:4px;margin-bottom:8px;">
        <?php foreach ($criteriosGrupos as $grpNombre => $grpCriterios):
            $suma = 0; $cnt = 0;
            foreach ($grpCriterios as $cKey) {
                if (isset($ev->$cKey)) { $suma += (int)$ev->$cKey; $cnt++; }
            }
            $avg = $cnt > 0 ? round($suma/$cnt, 1) : 0;
            $grpPct = min(100, $avg / 5 * 100);
            $grpCol = $avg >= 4 ? '#16a34a' : ($avg >= 3 ? '#d97706' : '#dc2626');
        ?>
        <div style="background:white;border-radius:8px;padding:6px 8px;border:1px solid #ede9fe;">
            <div style="font-size:.6rem;font-weight:800;color:#64748b;margin-bottom:3px;text-transform:uppercase;letter-spacing:.3px;">
                <?= htmlspecialchars($grpNombre) ?>
            </div>
            <div style="height:4px;border-radius:10px;background:#f1f5f9;overflow:hidden;">
                <div style="height:100%;border-radius:10px;background:<?= $grpCol ?>;width:<?= $grpPct ?>%;"></div>
            </div>
            <div style="font-size:.72rem;font-weight:800;color:<?= $grpCol ?>;margin-top:2px;"><?= number_format($avg,1) ?></div>
        </div>
        <?php endforeach; ?>
        </div>

        <?php if ($ev->observaciones ?? null): ?>
        <p style="margin:0;font-size:.75rem;color:#7c3aed;line-height:1.4;font-style:italic;border-top:1px solid #ede9fe;padding-top:8px;">
            <i class="ti ti-notes" style="font-size:.8rem;"></i>
            <?= htmlspecialchars(mb_strimwidth($ev->observaciones, 0, 100, '…')) ?>
        </p>
        <?php endif; ?>
    </div>
    <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

</div><!-- /alm-row-jeval -->


</div><!-- /alm-wrap -->
</div><!-- /dashboard-container -->

<!-- ═══ TOOLTIP JS ════════════════════════════════════════════════════════ -->
<script>
(function () {
    'use strict';
    const tooltip = document.getElementById('almTooltip');
    const ttFecha = document.getElementById('tt-fecha');
    const ttEst   = document.getElementById('tt-est');
    const ttDot   = document.getElementById('tt-dot');
    const ttExtra = document.getElementById('tt-extra');

    const diasSemana = ['','Lunes','Martes','Mi\u00e9rcoles','Jueves','Viernes','S\u00e1bado','Domingo'];
    const mesesEs    = ['','Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'];

    function formatFecha(iso) {
        const [y,m,d] = iso.split('-').map(Number);
        const dt  = new Date(y,m-1,d);
        const dow = dt.getDay() || 7;
        return `${diasSemana[dow]} ${d} ${mesesEs[m]} ${y}`;
    }

    document.querySelectorAll('.alm-cell[data-f]').forEach(cell => {
        cell.addEventListener('mouseenter', e => {
            ttFecha.textContent     = formatFecha(cell.dataset.f);
            ttEst.textContent       = cell.dataset.est;
            ttDot.style.background  = cell.dataset.color;
            ttExtra.textContent     = cell.dataset.extra || '';
            ttExtra.style.display   = cell.dataset.extra ? 'block' : 'none';
            tooltip.classList.add('show');
            posTooltip(e);
        });
        cell.addEventListener('mousemove',  e => posTooltip(e));
        cell.addEventListener('mouseleave', () => tooltip.classList.remove('show'));
    });

    function posTooltip(e) {
        const x = e.clientX + 14, y = e.clientY - 10;
        const w = tooltip.offsetWidth, vw = window.innerWidth;
        tooltip.style.left = (x + w > vw ? x - w - 28 : x) + 'px';
        tooltip.style.top  = y + 'px';
    }

    // Inicializar slider móvil al mes actual
    <?php $mesActual = (int)date('n'); ?>
    window.currentAlmMonth = <?= $mesActual ?>;
    window.almSlideMes = function(delta) {
        let newMes = window.currentAlmMonth + delta;
        if (newMes < 1) newMes = 12;
        if (newMes > 12) newMes = 1;
        window.currentAlmMonth = newMes;

        document.querySelectorAll('.alm-mm-card').forEach(card => {
            card.classList.remove('active-mobile');
            if (parseInt(card.dataset.mes) === window.currentAlmMonth) {
                card.classList.add('active-mobile');
                document.getElementById('alm-mm-mes-lbl').textContent = card.dataset.nombre;
            }
        });
    };
    
    // Auto-correr al cargar para que siempre empiece en el mes que estamos (unificado PC y Móvil)
    setTimeout(() => almSlideMes(0), 50);

})();

/* ── Filtro Historial con DataTables ── */
let tableHistorial = null;
let fpHistorial = null;

document.addEventListener('DOMContentLoaded', function() {
    if (document.getElementById('histTable')) {
        tableHistorial = $('#histTable').DataTable({
            "pageLength": 5,
            "lengthMenu": [[5, 10, 25, 50, -1], [5, 10, 25, 50, "Todos"]],
            "scrollY": "380px",
            "scrollCollapse": true,
            "language": {
                "sProcessing": "Procesando...",
                "sLengthMenu": "Mostrar _MENU_ reg.",
                "sZeroRecords": "No se encontraron resultados",
                "sEmptyTable": "No hay registros disponibles",
                "sInfo": "Mostrando _START_ al _END_ de _TOTAL_",
                "sInfoEmpty": "Mostrando 0 al 0 de 0",
                "sInfoFiltered": "(filtrado de _MAX_)",
                "sSearch": "Buscar (ej: Ausente, Abr):",
                "oPaginate": { "sNext": "Sig", "sPrevious": "Ant" }
            },
            "order": [], // Mantener orden de la BD temporalmente
            "responsive": true,
            "dom": '<"dt-top"lf>rt<"dt-bottom"ip><"clear">'
        });

        // Inicializar Flatpickr si existe el field
        if (typeof flatpickr !== 'undefined' && document.getElementById('filtroFechaHistorial')) {
            fpHistorial = flatpickr("#filtroFechaHistorial", {
                mode: "range",
                dateFormat: "d/m/Y",
                locale: "es",
                onChange: function(selectedDates, dateStr, instance) {
                    if (selectedDates.length > 0) {
                        document.getElementById('btnLimpiarFechaAlm').style.display = 'inline-block';
                    } else {
                        document.getElementById('btnLimpiarFechaAlm').style.display = 'none';
                    }
                    tableHistorial.draw();
                }
            });

            document.getElementById('btnLimpiarFechaAlm').addEventListener('click', function() {
                fpHistorial.clear();
                this.style.display = 'none';
                tableHistorial.draw();
            });

            // Extensión del buscador de DataTables para evaluar las fechas del Historial
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    if (settings.nTable.id !== 'histTable') return true;

                    if (!fpHistorial || fpHistorial.selectedDates.length === 0) {
                        return true;
                    }

                    var min = fpHistorial.selectedDates[0];
                    min.setHours(0, 0, 0, 0);

                    var max = null;
                    if (fpHistorial.selectedDates.length > 1) {
                        max = fpHistorial.selectedDates[1];
                        max.setHours(23, 59, 59, 999);
                    } else {
                        max = new Date(min.getTime());
                        max.setHours(23, 59, 59, 999);
                    }

                    // Extraer fecha del texto: "13 Ene 2026" (columna 0)
                    var str = data[0].trim();
                    var partes = str.split(' ');
                    if (partes.length < 3) return true;
                    
                    var dia = parseInt(partes[0], 10);
                    var mesesList = {'Ene':0,'Feb':1,'Mar':2,'Abr':3,'May':4,'Jun':5,'Jul':6,'Ago':7,'Sep':8,'Oct':9,'Nov':10,'Dic':11};
                    var mes = mesesList[partes[1]] ?? 0;
                    var anio = parseInt(partes[2], 10);
                    
                    var rowDate = new Date(anio, mes, dia);
                    return rowDate >= min && rowDate <= max;
                }
            );
        }
    }
});

function filtrarHistorial(estado, pill) {
    // Resetear pills
    document.querySelectorAll('#histFilters .hist-pill').forEach(p => {
        p.dataset.active = '0';
        p.style.borderColor = 'transparent';
        p.style.fontWeight  = '700';
    });
    // Activar pill clicado
    if (pill) {
        pill.dataset.active   = '1';
        pill.style.borderColor = 'currentColor';
    }

    if (tableHistorial) {
        if (estado === 'todos') {
            tableHistorial.column(2).search('').draw();
        } else {
            tableHistorial.column(2).search(estado).draw();
        }
    }
}
</script>
