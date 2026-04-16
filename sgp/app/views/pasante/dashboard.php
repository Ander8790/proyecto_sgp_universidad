<?php
// Dashboard Premium — Pasante SGP v3 Bento
$user_name   = $data['user_name']  ?? 'Pasante';
$pasante     = $data['pasante']    ?? null;
$actividades = $data['actividades']?? [];
$proRata     = $data['proRata']    ?? null;

$estadoPasantia = $pasante->estado_pasantia ?? 'Pendiente';
$sinAsignar     = in_array($estadoPasantia, ['Sin Asignar', 'Pendiente', '']);

$diasValidos = (int)($proRata->dias_presentes  ?? $pasante->horas_acumuladas / 8 ?? 0);
$horasAcum   = (int)($proRata->horas_mostradas ?? $pasante->horas_acumuladas   ?? 0);
$horasMeta   = (int)($proRata->horas_meta      ?? $pasante->horas_meta         ?? 1440);
$pct         = $horasMeta > 0 ? min(100, round($horasAcum / $horasMeta * 100)) : 0;
$pctCal      = (float)($proRata->porcentaje_calendario ?? 0);

$mesActual  = date('m'); $anioActual = date('Y');
$estesMes   = count(array_filter($actividades, fn($a) => !empty($a->fecha) && date('m', strtotime($a->fecha)) == $mesActual && date('Y', strtotime($a->fecha)) == $anioActual));
$presentes  = 0; $ausentes = 0; $justificados = 0;
foreach ($actividades as $a) {
    $est = $a->estado ?? '';
    if ($est === 'Presente') $presentes++;
    elseif ($est === 'Ausente') $ausentes++;
    elseif ($est === 'Justificado') $justificados++;
}

// Mini almanac: mapa fecha → estado de los últimos 30 días
$actMap = [];
foreach ($actividades as $a) {
    if (!empty($a->fecha)) $actMap[$a->fecha] = $a->estado ?? 'Presente';
}

// 30-day grid (weeks)
$hoy        = new DateTime();
$inicio30   = (clone $hoy)->modify('-29 days');
$dias30     = [];
$cur        = clone $inicio30;
while ($cur <= $hoy) {
    $dias30[] = clone $cur;
    $cur->modify('+1 day');
}

// Días faltantes = meta - acumulados
$diasMeta    = (int)ceil($horasMeta / 8);
$diasFaltan  = max(0, $diasMeta - $diasValidos);

$primerNombre = explode(' ', trim($user_name))[0];
$iniciales    = strtoupper(mb_substr(trim($user_name), 0, 1));
?>
<style>
/* ─── RESET / BASE ──────────────────────────────────── */
.pb-wrap { display:flex; flex-direction:column; gap:20px; }

/* ─── BENTO GRID ────────────────────────────────────── */
.pb-grid {
    display: grid;
    grid-template-columns: 2fr 1fr;
    gap: 20px;
}
.pb-grid-kpi {
    display: grid;
    grid-template-columns: repeat(4, 1fr);
    gap: 16px;
}
@keyframes fadeUp {
    from { opacity:0; transform:translateY(20px); }
    to   { opacity:1; transform:translateY(0); }
}
.pb-wrap > * { animation: fadeUp .45s ease both; }
.pb-wrap > *:nth-child(1){ animation-delay:.05s; }
.pb-wrap > *:nth-child(2){ animation-delay:.12s; }
.pb-wrap > *:nth-child(3){ animation-delay:.18s; }
.pb-wrap > *:nth-child(4){ animation-delay:.24s; }
.pb-wrap > *:nth-child(5){ animation-delay:.30s; }
.pb-wrap > *:nth-child(6){ animation-delay:.36s; }

/* ─── HERO BANNER ───────────────────────────────────── */
.pb-hero {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 45%, #2563eb 100%);
    border-radius: 22px;
    padding: 30px 36px;
    position: relative;
    overflow: hidden;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 20px;
    flex-wrap: wrap;
}
.pb-hero::before {
    content:'';
    position:absolute; top:-60px; right:-60px;
    width:260px; height:260px;
    background:rgba(255,255,255,.05);
    border-radius:50%;
}
.pb-hero::after {
    content:'';
    position:absolute; bottom:-40px; left:30%;
    width:160px; height:160px;
    background:rgba(255,255,255,.03);
    border-radius:50%;
}
.pb-avatar {
    width: 56px; height: 56px;
    background: rgba(255,255,255,.2);
    border: 2px solid rgba(255,255,255,.3);
    border-radius: 16px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.6rem; font-weight: 900; color: #fff;
    flex-shrink: 0;
}
.pb-status-pill {
    display: inline-flex;
    align-items: center; gap: 6px;
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.18);
    border-radius: 50px;
    padding: 6px 16px;
    color: rgba(255,255,255,.9);
    font-size: .8rem; font-weight: 600;
    backdrop-filter: blur(8px);
}
.pb-clock-box {
    display: flex; align-items: center; gap: 18px;
    background: rgba(0,0,0,.18);
    backdrop-filter: blur(12px);
    border: 1px solid rgba(255,255,255,.1);
    border-radius: 50px;
    padding: 10px 22px;
    color: #fff;
    z-index: 1;
}

/* ─── KPI CARD ──────────────────────────────────────── */
.pb-kpi {
    background: #fff;
    border-radius: 18px;
    padding: 22px;
    box-shadow: 0 2px 14px rgba(0,0,0,.07);
    display: flex;
    flex-direction: column;
    gap: 12px;
    position: relative;
    overflow: hidden;
    transition: transform .25s, box-shadow .25s;
    cursor: default;
}
.pb-kpi:hover { transform: translateY(-4px); }
.pb-kpi-top  { display: flex; justify-content: space-between; align-items: flex-start; }
.pb-kpi-label{ font-size: .73rem; font-weight: 700; text-transform: uppercase; letter-spacing: .06em; color: #94a3b8; }
.pb-kpi-val  { font-size: 2.6rem; font-weight: 900; line-height: 1; margin-top: 2px; }
.pb-kpi-sub  { font-size: .75rem; color: #94a3b8; font-weight: 500; }
.pb-kpi-icon { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1.3rem; flex-shrink: 0; }
.pb-kpi::after {
    content:'';
    position:absolute; bottom:-20px; right:-20px;
    width:80px; height:80px;
    border-radius:50%;
    opacity:.06;
}
.pb-kpi.c-teal   { border-top: 3px solid #0d9488; } .pb-kpi.c-teal::after  { background:#0d9488; }
.pb-kpi.c-blue   { border-top: 3px solid #2563eb; } .pb-kpi.c-blue::after  { background:#2563eb; }
.pb-kpi.c-amber  { border-top: 3px solid #f59e0b; } .pb-kpi.c-amber::after { background:#f59e0b; }
.pb-kpi.c-indigo { border-top: 3px solid #6366f1; } .pb-kpi.c-indigo::after{ background:#6366f1; }
.pb-kpi:hover.c-teal   { box-shadow: 0 12px 28px rgba(13,148,136,.2); }
.pb-kpi:hover.c-blue   { box-shadow: 0 12px 28px rgba(37,99,235,.2); }
.pb-kpi:hover.c-amber  { box-shadow: 0 12px 28px rgba(245,158,11,.2); }
.pb-kpi:hover.c-indigo { box-shadow: 0 12px 28px rgba(99,102,241,.2); }

/* ─── CARD BASE ─────────────────────────────────────── */
.pb-card {
    background: #fff;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 2px 14px rgba(0,0,0,.07);
}
.pb-card-title {
    font-size: 1rem; font-weight: 700; color: #0f172a;
    display: flex; align-items: center; gap: 8px;
    margin-bottom: 18px;
}
.pb-card-title i { color: #2563eb; font-size: 1.15rem; }

/* ─── MINI ALMANAC ──────────────────────────────────── */
.alm-strip {
    display: grid;
    grid-template-columns: repeat(10, 1fr);
    gap: 5px;
}
.alm-day {
    aspect-ratio: 1;
    border-radius: 6px;
    display: flex; align-items: center; justify-content: center;
    font-size: .6rem; font-weight: 700;
    cursor: default;
    position: relative;
    transition: transform .15s;
}
.alm-day:hover { transform: scale(1.25); z-index: 2; }
.alm-day.empty     { background: #f1f5f9; color: #cbd5e1; }
.alm-day.Presente  { background: #dcfce7; color: #15803d; }
.alm-day.Ausente   { background: #fee2e2; color: #dc2626; }
.alm-day.Justificado{ background:#dbeafe; color: #1d4ed8; }
.alm-day.today     { outline: 2px solid #2563eb; outline-offset: 1px; }
.alm-legend { display: flex; gap: 14px; font-size: .72rem; font-weight: 600; color: #64748b; }
.alm-legend span { display: flex; align-items: center; gap: 5px; }
.alm-legend .dot { width: 10px; height: 10px; border-radius: 3px; }

/* ─── PROGRESS RING ─────────────────────────────────── */
.pb-ring-wrap { display: flex; flex-direction: column; align-items: center; gap: 14px; }
.pb-ring { position: relative; width: 140px; height: 140px; flex-shrink: 0; }
.pb-ring svg { transform: rotate(-90deg); }
.pb-ring-text {
    position: absolute; inset: 0;
    display: flex; flex-direction: column;
    align-items: center; justify-content: center;
    text-align: center;
}
.pb-ring-pct   { font-size: 2rem; font-weight: 900; color: #0f172a; line-height: 1; }
.pb-ring-label { font-size: .65rem; font-weight: 600; color: #94a3b8; text-transform: uppercase; letter-spacing: .05em; }

/* ─── INFO PILLS ────────────────────────────────────── */
.pb-info-pill {
    display: flex; align-items: center; gap: 10px;
    padding: 10px 14px;
    background: #f8fafc;
    border-radius: 12px;
    margin-bottom: 8px;
    font-size: .82rem;
}
.pb-info-pill i { color: #2563eb; font-size: 1rem; flex-shrink: 0; }
.pb-info-pill strong { color: #0f172a; font-weight: 700; }
.pb-info-pill span   { color: #64748b; }

/* ─── PROGRESS BAR ──────────────────────────────────── */
.pb-bar { height: 8px; background: #e2e8f0; border-radius: 999px; overflow: hidden; margin: .3rem 0; }
.pb-bar-fill { height: 100%; border-radius: 999px; transition: width .8s cubic-bezier(.4,0,.2,1); }

/* ─── TIMELINE ──────────────────────────────────────── */
.pb-timeline { display: flex; flex-direction: column; }
.pb-tl-item {
    display: flex; gap: 14px; align-items: flex-start;
    padding: 12px 0;
    border-bottom: 1px solid #f1f5f9;
    position: relative;
}
.pb-tl-item:last-child { border-bottom: none; }
.pb-tl-dot {
    width: 38px; height: 38px; border-radius: 10px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1rem; flex-shrink: 0;
}
.pb-tl-date { font-size: .82rem; font-weight: 700; color: #0f172a; }
.pb-tl-meta { font-size: .73rem; color: #94a3b8; margin-top: 1px; }
.pb-tl-badge{
    margin-left: auto; flex-shrink: 0;
    padding: 3px 10px; border-radius: 6px;
    font-size: .72rem; font-weight: 700;
}

/* ─── EMPTY STATE ───────────────────────────────────── */
.pb-empty {
    display: flex; flex-direction: column; align-items: center;
    justify-content: center; text-align: center;
    padding: 60px 30px;
    background: #fff;
    border-radius: 22px;
    border: 2px dashed #e2e8f0;
    gap: 16px;
}
.pb-empty-icon {
    width: 90px; height: 90px;
    background: linear-gradient(135deg, #dbeafe, #eff6ff);
    border-radius: 50%; display: flex; align-items: center;
    justify-content: center; font-size: 2.8rem; color: #3b82f6;
}
@keyframes spin { to { transform: rotate(360deg); } }
.pb-spin { animation: spin 2s linear infinite; }

/* ─── RESPONSIVE ─────────────────────────────────────── */
@media (max-width: 1100px) {
    .pb-grid { grid-template-columns: 1fr; }
    .pb-grid-kpi { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 600px) {
    .pb-grid-kpi { grid-template-columns: repeat(2, 1fr); }
    .pb-hero { padding: 20px; }
    .pb-clock-box { display: none; }
    .alm-strip { grid-template-columns: repeat(6, 1fr); }
}
</style>

<div class="pb-wrap">

<!-- ══════════ HERO BANNER ══════════ -->
<div class="pb-hero">
    <div style="display:flex;align-items:center;gap:16px;z-index:1;">
        <div class="pb-avatar"><?= $iniciales ?></div>
        <div>
            <h1 style="color:#fff;font-size:1.7rem;font-weight:800;margin:0 0 6px;">
                ¡Bienvenido, <?= htmlspecialchars($primerNombre) ?>!
            </h1>
            <div style="display:flex;align-items:center;gap:10px;flex-wrap:wrap;">
                <div class="pb-status-pill">
                    <i class="ti ti-layout-dashboard"></i> Mi Panel · Pasante
                </div>
                <?php if (!$sinAsignar): ?>
                <div class="pb-status-pill" style="background:rgba(16,185,129,.2);border-color:rgba(16,185,129,.3);">
                    <i class="ti ti-circle-check" style="color:#34d399;"></i>
                    <?= htmlspecialchars($estadoPasantia) ?>
                </div>
                <?php endif; ?>
            </div>
        </div>
    </div>
    <div class="pb-clock-box">
        <div style="display:flex;align-items:center;gap:8px;font-size:.85rem;">
            <i class="ti ti-calendar" style="opacity:.8;"></i>
            <span id="pb-date"></span>
        </div>
        <div style="width:1px;height:22px;background:rgba(255,255,255,.2);"></div>
        <div style="display:flex;align-items:center;gap:8px;">
            <i class="ti ti-clock" style="opacity:.8;"></i>
            <span id="pb-time" style="font-size:1.1rem;font-weight:700;font-variant-numeric:tabular-nums;"></span>
        </div>
    </div>
</div>

<?php if ($sinAsignar): ?>
<!-- ══════════ EMPTY STATE ══════════ -->
<div class="pb-empty">
    <div class="pb-empty-icon">
        <i class="ti ti-user-scan pb-spin" style="animation-duration:3s;"></i>
    </div>
    <h2 style="font-size:1.6rem;font-weight:800;color:#0f172a;margin:0;">Perfil en Revisión</h2>
    <p style="color:#64748b;max-width:480px;line-height:1.7;margin:0;">
        Tu cuenta está activa. El equipo de <strong>Telemática</strong> te asignará pronto
        un departamento y tutor para que puedas comenzar a registrar asistencias.
    </p>
    <div style="display:flex;align-items:center;gap:8px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:50px;padding:10px 22px;color:#15803d;font-weight:600;font-size:.85rem;">
        <i class="ti ti-loader pb-spin"></i> Esperando asignación…
    </div>
</div>

<?php else: ?>

<!-- ══════════ KPI BENTO ══════════ -->
<div class="pb-grid-kpi">
    <?php
    $kpis = [
        ['label'=>'Días Acumulados',  'val'=>$diasValidos, 'sub'=>"de ~$diasMeta días meta", 'icon'=>'ti-calendar-check', 'cls'=>'c-teal',   'color'=>'#0d9488', 'ibg'=>'#ccfbf1', 'link'=>'/pasante/asistencia'],
        ['label'=>'Horas Acumuladas', 'val'=>$horasAcum,   'sub'=>"de $horasMeta h meta",    'icon'=>'ti-clock',          'cls'=>'c-blue',   'color'=>'#2563eb', 'ibg'=>'#eff6ff', 'link'=>null],
        ['label'=>'Este Mes',         'val'=>$estesMes,    'sub'=>date('F Y'),                'icon'=>'ti-calendar-stats', 'cls'=>'c-amber',  'color'=>'#d97706', 'ibg'=>'#fef3c7', 'link'=>null],
        ['label'=>'Progreso',         'val'=>$pct . '%',   'sub'=>'horas completadas',        'icon'=>'ti-chart-pie',      'cls'=>'c-indigo', 'color'=>'#6366f1', 'ibg'=>'#ede9fe', 'link'=>'/pasante/analiticas'],
    ];
    foreach ($kpis as $k): ?>
    <div class="pb-kpi <?= $k['cls'] ?>">
        <div class="pb-kpi-top">
            <div>
                <div class="pb-kpi-label"><?= $k['label'] ?></div>
                <div class="pb-kpi-val" style="color:<?= $k['color'] ?>;" data-kpi-value="<?= is_int($k['val']) ? $k['val'] : '' ?>"><?= $k['val'] ?></div>
                <div class="pb-kpi-sub"><?= $k['sub'] ?></div>
            </div>
            <?php if ($k['link']): ?>
            <a href="<?= URLROOT ?><?= $k['link'] ?>" class="pb-kpi-icon" style="background:<?= $k['ibg'] ?>;color:<?= $k['color'] ?>;" title="Ver detalle">
                <i class="ti <?= $k['icon'] ?>"></i>
            </a>
            <?php else: ?>
            <div class="pb-kpi-icon" style="background:<?= $k['ibg'] ?>;color:<?= $k['color'] ?>;">
                <i class="ti <?= $k['icon'] ?>"></i>
            </div>
            <?php endif; ?>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ══════════ BENTO FILA 2: PROGRESO + INFO ══════════ -->
<div class="pb-grid">

    <!-- Progreso + Mini Almanac -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Progreso visual -->
        <div class="pb-card">
            <div class="pb-card-title">
                <i class="ti ti-trending-up"></i> Mi Progreso de Pasantía
                <span style="margin-left:auto;font-size:.78rem;color:#94a3b8;font-weight:500;">Meta: <?= number_format($horasMeta) ?> h</span>
            </div>

            <!-- Horas -->
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;font-size:.8rem;color:#64748b;margin-bottom:5px;">
                    <span><i class="ti ti-clock" style="color:#2563eb;"></i> Horas acumuladas</span>
                    <strong style="color:#2563eb;"><?= $horasAcum ?> / <?= $horasMeta ?>h</strong>
                </div>
                <div class="pb-bar">
                    <div class="pb-bar-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#2563eb,#60a5fa);"></div>
                </div>
            </div>

            <!-- Días -->
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;font-size:.8rem;color:#64748b;margin-bottom:5px;">
                    <span><i class="ti ti-calendar-check" style="color:#0d9488;"></i> Días asistidos</span>
                    <strong style="color:#0d9488;"><?= $diasValidos ?> / <?= $diasMeta ?> días</strong>
                </div>
                <div class="pb-bar">
                    <div class="pb-bar-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#0d9488,#34d399);"></div>
                </div>
            </div>

            <?php if ($pctCal > 0): ?>
            <!-- Tiempo transcurrido -->
            <div style="margin-bottom:14px;">
                <div style="display:flex;justify-content:space-between;font-size:.8rem;color:#64748b;margin-bottom:5px;">
                    <span><i class="ti ti-hourglass" style="color:#f59e0b;"></i> Tiempo transcurrido</span>
                    <strong style="color:#f59e0b;"><?= $pctCal ?>%</strong>
                </div>
                <div class="pb-bar">
                    <div class="pb-bar-fill" style="width:<?= $pctCal ?>%;background:linear-gradient(90deg,#f59e0b,#ef4444);"></div>
                </div>
            </div>
            <?php endif; ?>

            <?php if ($diasFaltan > 0): ?>
            <div style="margin-top:4px;padding:10px 14px;background:#f0fdf4;border:1px solid #bbf7d0;border-radius:10px;font-size:.78rem;color:#15803d;display:flex;gap:8px;align-items:center;">
                <i class="ti ti-info-circle"></i>
                Faltan <strong><?= $diasFaltan ?> días</strong> para completar la pasantía
            </div>
            <?php else: ?>
            <div style="margin-top:4px;padding:10px 14px;background:#dcfce7;border:1px solid #86efac;border-radius:10px;font-size:.78rem;color:#15803d;font-weight:700;display:flex;gap:8px;align-items:center;">
                <i class="ti ti-circle-check"></i> ¡Meta de horas completada!
            </div>
            <?php endif; ?>

            <?php if ($pctCal - $pct > 20): ?>
            <div style="margin-top:10px;padding:10px 14px;background:#fff1f2;border:1px solid #fecdd3;border-radius:10px;font-size:.78rem;color:#dc2626;display:flex;gap:8px;align-items:center;">
                <i class="ti ti-alert-triangle"></i>
                El tiempo avanza <strong><?= round($pctCal - $pct, 1) ?> puntos</strong> más rápido que tu asistencia. Habla con tu tutor.
            </div>
            <?php endif; ?>
        </div>

        <!-- Mini Almanac -->
        <div class="pb-card">
            <div class="pb-card-title">
                <i class="ti ti-calendar-month"></i> Últimos 30 Días
                <span style="margin-left:auto;font-size:.72rem;color:#94a3b8;font-weight:500;">Heatmap de asistencia</span>
            </div>

            <div class="alm-strip" style="margin-bottom:12px;">
                <?php foreach ($dias30 as $d):
                    $fStr  = $d->format('Y-m-d');
                    $dNum  = (int)$d->format('d');
                    $esHoy = $fStr === $hoy->format('Y-m-d');
                    $est   = $actMap[$fStr] ?? null;
                    $cls   = $est ? $est : 'empty';
                    $label = $dNum;
                    $title = $d->format('d/m') . ($est ? " — $est" : '');
                ?>
                <div class="alm-day <?= $cls ?><?= $esHoy ? ' today' : '' ?>" title="<?= $title ?>">
                    <?= $label ?>
                </div>
                <?php endforeach; ?>
            </div>

            <div class="alm-legend">
                <span><span class="dot" style="background:#dcfce7;"></span>Presente</span>
                <span><span class="dot" style="background:#fee2e2;"></span>Ausente</span>
                <span><span class="dot" style="background:#dbeafe;"></span>Justificado</span>
                <span><span class="dot" style="background:#f1f5f9;border:1px solid #e2e8f0;"></span>Sin registro</span>
            </div>
        </div>
    </div>

    <!-- Info rápida + Ring -->
    <div style="display:flex;flex-direction:column;gap:20px;">

        <!-- Anillo de progreso -->
        <div class="pb-card" style="align-items:center;text-align:center;">
            <div class="pb-card-title" style="justify-content:center;">
                <i class="ti ti-chart-donut"></i> Completado
            </div>
            <div class="pb-ring-wrap">
                <div class="pb-ring">
                    <?php
                    $r = 58; $cx = 70; $cy = 70;
                    $circ = 2 * M_PI * $r;
                    $dash = $circ * ($pct / 100);
                    $gap  = $circ - $dash;
                    ?>
                    <svg width="140" height="140" viewBox="0 0 140 140">
                        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none" stroke="#e2e8f0" stroke-width="12"/>
                        <circle cx="<?= $cx ?>" cy="<?= $cy ?>" r="<?= $r ?>" fill="none"
                            stroke="url(#pb-grad)" stroke-width="12"
                            stroke-linecap="round"
                            stroke-dasharray="<?= round($dash,2) ?> <?= round($gap,2) ?>"/>
                        <defs>
                            <linearGradient id="pb-grad" x1="0%" y1="0%" x2="100%" y2="0%">
                                <stop offset="0%"   stop-color="#2563eb"/>
                                <stop offset="100%" stop-color="#0d9488"/>
                            </linearGradient>
                        </defs>
                    </svg>
                    <div class="pb-ring-text">
                        <div class="pb-ring-pct"><?= $pct ?>%</div>
                        <div class="pb-ring-label">de la meta</div>
                    </div>
                </div>
                <div style="font-size:.82rem;color:#64748b;line-height:1.6;text-align:center;">
                    <strong style="color:#0f172a;"><?= $horasAcum ?>h</strong> acumuladas<br>
                    de <strong style="color:#0f172a;"><?= $horasMeta ?>h</strong> requeridas
                </div>
            </div>
        </div>

        <!-- Info pasantía -->
        <div class="pb-card" style="flex:1;">
            <div class="pb-card-title"><i class="ti ti-id-badge"></i> Mi Pasantía</div>

            <?php if (!empty($pasante->departamento)): ?>
            <div class="pb-info-pill">
                <i class="ti ti-building-community"></i>
                <div><span>Departamento</span><br><strong><?= htmlspecialchars($pasante->departamento ?? '—') ?></strong></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($pasante->tutor_nombre)): ?>
            <div class="pb-info-pill">
                <i class="ti ti-user-star"></i>
                <div><span>Tutor</span><br><strong><?= htmlspecialchars($pasante->tutor_nombre ?? '—') ?></strong></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($pasante->fecha_inicio)): ?>
            <div class="pb-info-pill">
                <i class="ti ti-calendar-event"></i>
                <div><span>Inicio</span><br><strong><?= date('d/m/Y', strtotime($pasante->fecha_inicio ?? 'now')) ?></strong></div>
            </div>
            <?php endif; ?>

            <?php if (!empty($pasante->fecha_fin)): ?>
            <div class="pb-info-pill">
                <i class="ti ti-calendar-due"></i>
                <div><span>Fin estimado</span><br><strong><?= date('d/m/Y', strtotime($pasante->fecha_fin ?? 'now')) ?></strong></div>
            </div>
            <?php endif; ?>

            <a href="<?= URLROOT ?>/pasante/constancia"
               style="display:flex;align-items:center;justify-content:center;gap:8px;margin-top:14px;
                      padding:10px 16px;background:linear-gradient(135deg,#172554,#2563eb);
                      color:#fff;border-radius:12px;font-size:.83rem;font-weight:700;text-decoration:none;
                      transition:opacity .2s;"
               onmouseover="this.style.opacity='.85'" onmouseout="this.style.opacity='1'">
                <i class="ti ti-file-certificate"></i> Ver Constancia
            </a>
        </div>
    </div>
</div>

<!-- ══════════ TIMELINE ACTIVIDAD ══════════ -->
<div class="pb-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:16px;">
        <div class="pb-card-title" style="margin-bottom:0;">
            <i class="ti ti-activity"></i> Mis Últimas Asistencias
        </div>
        <a href="<?= URLROOT ?>/pasante/asistencia"
           style="font-size:.8rem;font-weight:700;color:#2563eb;text-decoration:none;display:flex;align-items:center;gap:4px;">
            Ver todo <i class="ti ti-arrow-right"></i>
        </a>
    </div>

    <?php if (empty($actividades)): ?>
    <div style="text-align:center;padding:40px;color:#94a3b8;">
        <i class="ti ti-calendar-off" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4;"></i>
        Aún no tienes asistencias registradas
    </div>
    <?php else: ?>
    <div class="pb-timeline">
        <?php
        $cfg = [
            'Presente'    => ['bg'=>'#dcfce7', 'color'=>'#16a34a', 'icon'=>'ti-check',           'badge_bg'=>'#f0fdf4'],
            'Ausente'     => ['bg'=>'#fee2e2', 'color'=>'#dc2626', 'icon'=>'ti-x',               'badge_bg'=>'#fff1f2'],
            'Justificado' => ['bg'=>'#dbeafe', 'color'=>'#1d4ed8', 'icon'=>'ti-file-description', 'badge_bg'=>'#eff6ff'],
        ];
        foreach ($actividades as $a):
            $est = $a->estado ?? 'Presente';
            $c   = $cfg[$est] ?? $cfg['Presente'];
            $fecha = !empty($a->fecha) ? date('d/m/Y', strtotime($a->fecha)) : '—';
            $hora  = !empty($a->hora_registro) ? date('g:i A', strtotime($a->hora_registro)) : '—';
        ?>
        <div class="pb-tl-item">
            <div class="pb-tl-dot" style="background:<?= $c['bg'] ?>;color:<?= $c['color'] ?>;">
                <i class="ti <?= $c['icon'] ?>"></i>
            </div>
            <div>
                <div class="pb-tl-date"><?= $fecha ?></div>
                <div class="pb-tl-meta">
                    <i class="ti ti-clock" style="font-size:.7rem;"></i> <?= $hora ?>
                    &nbsp;·&nbsp;
                    <i class="ti ti-device-tablet" style="font-size:.7rem;"></i> <?= htmlspecialchars($a->metodo ?? 'Kiosco') ?>
                </div>
            </div>
            <div class="pb-tl-badge" style="background:<?= $c['badge_bg'] ?>;color:<?= $c['color'] ?>;">
                <?= $est ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>
    <?php endif; ?>
</div>

<?php endif; ?>
</div><!-- /pb-wrap -->

<script>
(function(){
    function tick() {
        const now = new Date();
        const d = document.getElementById('pb-date');
        const t = document.getElementById('pb-time');
        if (d) {
            const s = now.toLocaleDateString('es-ES', {weekday:'long', day:'numeric', month:'long', year:'numeric'});
            d.textContent = s.charAt(0).toUpperCase() + s.slice(1);
        }
        if (t) {
            t.textContent = now.toLocaleTimeString('es-ES', {hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true}).toUpperCase();
        }
    }
    tick(); setInterval(tick, 1000);
})();

<?php if ($sinAsignar): ?>
let _checks = 0;
const _poll = setInterval(async () => {
    try {
        const r = await fetch('<?= URLROOT ?>/pasante/getStatusAjax');
        const d = await r.json();
        if (d.success && d.estado !== '<?= $estadoPasantia ?>' && !['Sin Asignar','Pendiente',''].includes(d.estado)) {
            clearInterval(_poll); location.reload();
        }
        if (++_checks > 360) clearInterval(_poll);
    } catch(e) {}
}, 5000);
<?php endif; ?>
</script>
