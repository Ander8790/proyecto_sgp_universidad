<?php
/**
 * Vista: Actividades Extras — Lista principal
 * URL: /actividades  —  Cargada por ActividadesController::index()
 */
$actividades      = $data['actividades']      ?? [];
$kpiTotal         = $data['kpiTotal']         ?? 0;
$kpiActivas       = $data['kpiActivas']       ?? 0;
$kpiFinalizadas   = $data['kpiFinalizadas']   ?? 0;
$kpiParticipantes = $data['kpiParticipantes'] ?? 0;
$instituciones    = $data['instituciones']    ?? [];
$supervisores     = $data['supervisores']     ?? [];

// ── Datos para gráfico ApexCharts ──────────────────────────────
$distCount = [
    'Servicio Comunitario' => 0,
    'Pasantía Corta'       => 0,
    'Mantenimiento'        => 0,
    'Otro'                 => 0,
];
foreach ($actividades as $a) {
    $tipo = $a->tipo ?? 'Otro';
    if (isset($distCount[$tipo])) $distCount[$tipo]++;
    else $distCount['Otro']++;
}
$jsLabels = json_encode(array_keys($distCount));
$jsSeries = json_encode(array_values($distCount));

// ── Auto-abrir modal aliados si viene de instituciones.php ──────
$abrirAliados = isset($_GET['aliados']);
?>

<style>
/* ══════════════════════════════════════════════════════════════
   ACTIVIDADES EXTRAS — BENTO PREMIUM UI
   Coherente con configuracion/index.php
   ══════════════════════════════════════════════════════════════ */

/* ── Keyframes ─────────────────────────────────────────────── */
@keyframes act-fadeIn  { from{opacity:0}to{opacity:1} }
@keyframes act-slideUp { from{transform:translateY(20px);opacity:0}to{transform:translateY(0);opacity:1} }
@keyframes act-pulse   { 0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.5);opacity:.8} }

/* ── BENTO GRID (12 columnas) ──────────────────────────────── */
.act-bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 22px;
    margin-bottom: 22px;
}
.acg-3  { grid-column: span 3;  }
.acg-4  { grid-column: span 4;  }
.acg-5  { grid-column: span 5;  }
.acg-6  { grid-column: span 6;  }
.acg-7  { grid-column: span 7;  }
.acg-12 { grid-column: span 12; }

/* ── CFG-CARD base (idéntico a configuracion) ───────────────── */
.act-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    display: flex;
    flex-direction: column;
    min-width: 0;
    transition: transform .25s ease, box-shadow .25s ease;
}
.act-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.07);
}

/* ── KPI CARDS — Vertical (igual a configuracion) ───────────── */
.act-kpi-icon {
    width: 46px; height: 46px; border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; margin-bottom: 14px;
}
.act-kpi-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 5px; }
.act-kpi-label { font-size: 0.8rem; color: #64748b; font-weight: 500; }

/* ── CARD HEADER (idéntico a configuracion) ────────────────── */
.act-card-hdr {
    display: flex; align-items: center;
    justify-content: space-between;
    margin-bottom: 18px; padding-bottom: 14px;
    border-bottom: 1px solid #f1f5f9;
}
.act-card-title { display: flex; align-items: center; gap: 10px; font-size: .95rem; font-weight: 700; color: #1e293b; }
.act-icon-box { width: 36px; height: 36px; border-radius: 10px; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
.act-badge { font-size: .72rem; padding: 3px 10px; border-radius: 20px; font-weight: 700; }

/* ── GALERÍA de actividades ────────────────────────────────── */
.act-gallery-grid { display: grid; grid-template-columns: repeat(3,1fr); gap: 20px; }
.act-gallery-card {
    background: white; border-radius: 20px;
    border: 1px solid #f1f5f9; border-left-width: 6px;
    display: flex; flex-direction: column;
    transition: all .3s ease; overflow: hidden;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
}
.act-gallery-card:hover { transform: translateY(-4px); box-shadow: 0 12px 25px rgba(0,0,0,0.08); }
.act-gallery-head { padding: 18px 20px 14px; }
.act-gallery-head h5 { margin: 0 0 10px; font-size: .95rem; font-weight: 700; color: #0f172a; }
.act-badge-estado {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 12px; border-radius: 20px;
    font-size: .72rem; font-weight: 700;
}
.act-pulsing-dot { width:6px; height:6px; background:#10b981; border-radius:50%; display:inline-block; animation:act-pulse 1.5s infinite; }
.act-gallery-body { padding: 0 20px 14px; display: flex; flex-direction: column; gap: 10px; flex-grow: 1; }
.act-info-row { display: flex; align-items: center; gap: 8px; color: #475569; font-size: .82rem; font-weight: 500; }
.act-info-row.hl { background: #f8fafc; padding: 7px 10px; border-radius: 8px; border: 1px solid #e2e8f0; }
.act-gallery-foot { padding: 14px 20px; border-top: 1px solid #f1f5f9; background: #fafafa; display: flex; gap: 10px; }
.btn-ver-act {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
    background: linear-gradient(135deg,#172554 0%,#2563eb 100%);
    color: white; border: none; padding: 9px 14px; border-radius: 10px;
    font-weight: 600; font-size: .85rem; text-decoration: none; transition: transform .2s,box-shadow .2s;
}
.btn-ver-act:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(37,99,235,.3); color:white; }

/* ── Feed ──────────────────────────────────────────────────── */
.act-feed-list { flex:1; overflow-y:auto; max-height:250px; }
.act-feed-item { display:flex; gap:12px; padding:10px 0; border-bottom:1px solid #f8fafc; align-items:flex-start; }
.act-feed-item:last-child { border-bottom:none; }
.act-feed-avatar { width:36px; height:36px; border-radius:10px; flex-shrink:0; display:flex; align-items:center; justify-content:center; font-size:.9rem; }
.act-feed-name { font-size:.85rem; font-weight:600; color:#334155; margin:0; }
.act-feed-date { font-size:.73rem; color:#94a3b8; margin-top:2px; }

/* ── Quick Buttons ─────────────────────────────────────────── */
.act-quick-btn {
    display:flex; align-items:center; gap:12px; padding:11px 14px;
    border-radius:12px; border:1.5px solid #f1f5f9; cursor:pointer;
    background:#f8fafc; transition:all .2s; text-decoration:none;
    color:#1e293b; font-weight:600; font-size:.875rem; width:100%; text-align:left;
}
.act-quick-btn:hover { border-color:#2563eb; background:#eff6ff; color:#2563eb; transform:translateX(4px); }
.act-quick-icon { width:38px; height:38px; border-radius:10px; display:flex; align-items:center; justify-content:center; flex-shrink:0; font-size:1.05rem; }

/* ── Filter chips ──────────────────────────────────────────── */
.act-chip {
    padding:6px 14px; border-radius:20px; border:1.5px solid #e2e8f0;
    font-size:.78rem; font-weight:700; cursor:pointer;
    background:white; color:#64748b; transition:all .2s;
}
.act-chip:hover  { border-color:#2563eb; color:#2563eb; }
.act-chip.active { background:linear-gradient(135deg,#172554,#2563eb); color:white; border-color:transparent; }

/* ── Search ────────────────────────────────────────────────── */
.act-search-wrap { position:relative; }
.act-search-wrap i { position:absolute; left:12px; top:50%; transform:translateY(-50%); color:#94a3b8; pointer-events:none; }
.act-search-wrap input {
    padding:10px 14px 10px 40px; border-radius:12px;
    border:2px solid #e2e8f0; font-size:.85rem; width:220px;
    outline:none; transition:border-color .3s; background:#f8fafc; font-family:inherit;
}
.act-search-wrap input:focus { border-color:#2563eb; background:white; }

/* ── Empty State ───────────────────────────────────────────── */
.act-empty { border-radius:20px; padding:60px 20px; text-align:center; border:1px dashed #cbd5e1; }

/* ══════════════════════════════════════════════════════════════
   MODALES — sistema unificado
   ══════════════════════════════════════════════════════════════ */
.modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(15,23,42,.7); backdrop-filter:blur(6px);
    z-index:9999; align-items:center; justify-content:center;
    animation:act-fadeIn .2s ease;
}
.modal-overlay.active { display:flex; }
.modal-box {
    background:white; border-radius:24px;
    width:90%; max-width:560px; max-height:92vh;
    display:flex; flex-direction:column; overflow:hidden;
    box-shadow:0 32px 80px rgba(15,23,42,.3); animation:act-slideUp .3s ease;
}
.modal-head {
    background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);
    padding:26px 30px; display:flex; justify-content:space-between;
    align-items:center; flex-shrink:0;
}
.modal-head h2 { font-size:1.2rem; font-weight:700; margin:0; color:white !important; }
.modal-head p  { font-size:.83rem; margin:3px 0 0; color:rgba(255,255,255,.8) !important; }
.btn-cls-modal {
    background:rgba(255,255,255,.2); border:none; color:white;
    width:34px; height:34px; border-radius:50%; cursor:pointer;
    font-size:1rem; display:flex; align-items:center; justify-content:center; transition:background .2s;
}
.btn-cls-modal:hover { background:rgba(255,255,255,.35); }
.modal-bd { padding:24px 28px; overflow-y:auto; flex:1; }

/* ── Form fields (mismo estilo configuracion) ──────────────── */
.f-group { margin-bottom:16px; }
.f-label {
    display:block; font-size:.78rem; font-weight:700; color:#374151;
    margin-bottom:7px; text-transform:uppercase; letter-spacing:.5px;
}
.f-input {
    width:100%; padding:11px 14px; border:2px solid #e5e7eb; border-radius:11px;
    font-size:.9rem; color:#1e293b; transition:border-color .2s;
    box-sizing:border-box; background:#fafafa; font-family:inherit;
}
.f-input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 4px rgba(79,70,229,.1); background:white; }
.f-btn-primary {
    width:100%; padding:12px; border:none; border-radius:11px; cursor:pointer;
    background:linear-gradient(135deg,#172554,#2563eb);
    color:white; font-size:.9rem; font-weight:700;
    display:flex; align-items:center; justify-content:center; gap:8px;
    transition:all .2s; box-shadow:0 4px 12px rgba(37,99,235,.25); font-family:inherit;
}
.f-btn-primary:hover { transform:translateY(-2px); box-shadow:0 8px 20px rgba(37,99,235,.35); }
.f-btn-cancel {
    flex:1; padding:11px; background:#f1f5f9; color:#475569;
    border:2px solid #e2e8f0; border-radius:11px; font-size:.88rem;
    font-weight:600; cursor:pointer; transition:all .2s; font-family:inherit;
}
.f-btn-cancel:hover { background:#e2e8f0; }

/* ══════════════════════════════════════════════════════════════
   MODAL ALIADOS — panel grande (2 columnas)
   ══════════════════════════════════════════════════════════════ */
.modal-aliados-box {
    background:white; border-radius:24px;
    width:95%; max-width:900px; max-height:92vh;
    display:flex; flex-direction:column; overflow:hidden;
    box-shadow:0 32px 80px rgba(15,23,42,.3); animation:act-slideUp .3s ease;
}
.modal-aliados-body {
    display:grid; grid-template-columns:1fr 320px;
    gap:24px; padding:24px 28px; overflow:hidden; flex:1;
}

/* Lista instituciones (cfg-list-item style) */
.inst-list-item {
    display:flex; align-items:center; gap:12px;
    padding:10px 0; border-bottom:1px solid #f8fafc;
}
.inst-list-item:last-child { border-bottom:none; }
.inst-avatar {
    width:38px; height:38px; border-radius:10px; flex-shrink:0;
    display:flex; align-items:center; justify-content:center;
}
.inst-name { font-weight:700; color:#1e293b; font-size:.875rem; white-space:nowrap; overflow:hidden; text-overflow:ellipsis; }
.inst-sub  { font-size:.74rem; color:#94a3b8; margin-top:2px; }

/* Badges tipo */
.badge-tipo { font-size:.68rem; padding:2px 8px; border-radius:100px; font-weight:700; text-transform:uppercase; letter-spacing:.3px; }
.tipo-univ  { background:#eff6ff; color:#2563eb; }
.tipo-inst  { background:#f5f3ff; color:#7c3aed; }
.tipo-col   { background:#fffbeb; color:#d97706; }
.tipo-otro  { background:#f0fdf4; color:#059669; }

/* Badges estado */
.inst-badge-estado { font-size:.68rem; padding:2px 8px; border-radius:100px; font-weight:700; text-transform:uppercase; }
.badge-activo   { background:#dcfce7; color:#059669; }
.badge-inactivo { background:#fee2e2; color:#dc2626; }

/* Botones toggle */
.btn-toggle-inst {
    padding:5px 10px; border-radius:8px; font-size:.75rem; font-weight:700;
    border:1.5px solid transparent; cursor:pointer; transition:all .18s;
    display:inline-flex; align-items:center; gap:4px; flex-shrink:0;
}
.btn-toggle-inst.act   { background:#fef2f2; color:#dc2626; border-color:#fecaca; }
.btn-toggle-inst.act:hover   { background:#dc2626; color:white; border-color:#dc2626; }
.btn-toggle-inst.inact { background:#f0fdf4; color:#059669; border-color:#bbf7d0; }
.btn-toggle-inst.inact:hover { background:#059669; color:white; border-color:#059669; }

/* Separador panel derecho */
.modal-panel-sep { border-left:2px dashed #e2e8f0; padding-left:24px; }

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width:1200px) {
    .acg-3  { grid-column: span 6; }
    .acg-7  { grid-column: span 12; }
    .acg-5  { grid-column: span 12; }
    .act-gallery-grid { grid-template-columns: repeat(2,1fr); }
}
@media (max-width:900px)  {
    .modal-aliados-body { grid-template-columns: 1fr !important; padding:20px; }
    .modal-panel-sep    { border-left:none; border-top:2px dashed #e2e8f0; padding-left:0; padding-top:20px; }
}
@media (max-width:768px)  {
    .acg-6  { grid-column: span 12; }
    .acg-3  { grid-column: span 6;  }
    .act-gallery-grid { grid-template-columns: 1fr; }
}
@media (max-width:480px)  {
    .acg-3  { grid-column: span 12; }
}
</style>

<div style="width:100%;max-width:1600px;margin:0 auto;padding:20px;" id="actividades-pjax-container">

    <!-- ══════════════════════════════════════════════════════
         BANNER PREMIUM
         ══════════════════════════════════════════════════════ -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:28px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;box-shadow:0 10px 25px rgba(30,58,138,.2);flex-wrap:wrap;gap:20px;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:radial-gradient(circle,rgba(255,255,255,.1) 0%,rgba(255,255,255,0) 70%);border-radius:50%;pointer-events:none;"></div>
        <div style="position:absolute;bottom:-40px;left:120px;width:160px;height:160px;background:radial-gradient(circle,rgba(255,255,255,.06) 0%,rgba(255,255,255,0) 70%);border-radius:50%;pointer-events:none;"></div>
        <div style="display:flex;align-items:center;gap:20px;z-index:1;">
            <div style="background:rgba(255,255,255,.2);backdrop-filter:blur(10px);border-radius:16px;width:64px;height:64px;display:flex;align-items:center;justify-content:center;">
                <i class="ti ti-briefcase" style="font-size:2rem;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:2rem;font-weight:700;margin:0;letter-spacing:-.5px;">Actividades Extras</h1>
                <p style="color:rgba(255,255,255,.8);margin:4px 0 0;font-size:1rem;">
                    <i class="ti ti-school"></i> <?= count($instituciones) ?> institución<?= count($instituciones) !== 1 ? 'es' : '' ?>
                    &nbsp;·&nbsp;
                    <i class="ti ti-briefcase"></i> <?= $kpiTotal ?> proyecto<?= $kpiTotal !== 1 ? 's' : '' ?>
                </p>
            </div>
        </div>
        <div style="z-index:1;">
            <button data-bs-toggle="modal" data-bs-target="#modalNuevaActividad"
                style="background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3);color:white;padding:12px 24px;border-radius:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:.95rem;transition:all .3s;"
                onmouseover="this.style.background='rgba(255,255,255,.25)';this.style.transform='translateY(-2px)'"
                onmouseout="this.style.background='rgba(255,255,255,.15)';this.style.transform='none'">
                <i class="ti ti-plus"></i> Nueva Actividad
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         KPI CARDS — vertical (estilo configuracion)
         ══════════════════════════════════════════════════════ -->
    <div class="act-bento-grid" style="margin-bottom:22px;">
        <?php
        $kpiItems = [
            ['label' => 'Total Proyectos',   'val' => $kpiTotal,         'color' => '#7c3aed', 'icon' => 'ti-folder',    'bg' => '#f5f3ff'],
            ['label' => 'Proyectos Activos', 'val' => $kpiActivas,       'color' => '#059669', 'icon' => 'ti-activity',  'bg' => '#f0fdf4'],
            ['label' => 'Finalizadas',       'val' => $kpiFinalizadas,   'color' => '#64748b', 'icon' => 'ti-checkbox',  'bg' => '#f8fafc'],
            ['label' => 'Participantes',     'val' => $kpiParticipantes, 'color' => '#2563eb', 'icon' => 'ti-users',     'bg' => '#eff6ff'],
        ];
        foreach ($kpiItems as $k): ?>
        <div class="act-card acg-3" style="border-left:4px solid <?= $k['color'] ?>;cursor:default;">
            <div class="act-kpi-icon" style="background:<?= $k['bg'] ?>;color:<?= $k['color'] ?>;">
                <i class="ti <?= $k['icon'] ?>"></i>
            </div>
            <div class="act-kpi-value" style="color:<?= $k['color'] ?>;"><?= $k['val'] ?></div>
            <div class="act-kpi-label"><?= $k['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════
         BENTO GRID — Widgets
         ══════════════════════════════════════════════════════ -->
    <div class="act-bento-grid">

        <!-- ── Distribución por Tipo (Chart) ─────────────────── -->
        <div class="act-card acg-7">
            <div class="act-card-hdr">
                <div class="act-card-title">
                    <div class="act-icon-box" style="background:#eff6ff;">
                        <i class="ti ti-chart-pie" style="font-size:1.1rem;color:#2563eb;"></i>
                    </div>
                    <div>
                        <div>Distribución por Tipo</div>
                        <div style="font-size:.72rem;color:#94a3b8;font-weight:500;margin-top:1px;">Proyectos según categoría</div>
                    </div>
                </div>
                <span class="act-badge" style="background:#eff6ff;color:#2563eb;"><?= $kpiTotal ?> total</span>
            </div>
            <div style="flex:1;display:flex;align-items:center;justify-content:center;min-height:210px;">
                <?php if ($kpiTotal > 0): ?>
                    <div id="chart-actividades" style="width:100%;"></div>
                <?php else: ?>
                    <div style="text-align:center;color:#94a3b8;padding:30px 0;">
                        <i class="ti ti-chart-arrows-vertical" style="font-size:3rem;opacity:.3;display:block;margin-bottom:12px;"></i>
                        <span style="font-size:.88rem;font-weight:600;">Sin datos suficientes</span>
                        <p style="font-size:.78rem;margin:4px 0 0;color:#cbd5e1;">Crea actividades para ver estadísticas</p>
                    </div>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Actividad Reciente (Feed) ────────────────────── -->
        <div class="act-card acg-5">
            <div class="act-card-hdr">
                <div class="act-card-title">
                    <div class="act-icon-box" style="background:#fff7ed;">
                        <i class="ti ti-history" style="font-size:1.1rem;color:#f59e0b;"></i>
                    </div>
                    <div>
                        <div>Actividad Reciente</div>
                        <div style="font-size:.72rem;color:#94a3b8;font-weight:500;margin-top:1px;">Últimos proyectos</div>
                    </div>
                </div>
            </div>
            <div class="act-feed-list">
                <?php if (empty($actividades)): ?>
                    <div style="text-align:center;padding:36px 20px;color:#94a3b8;">
                        <i class="ti ti-history" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4;"></i>
                        <p style="margin:0;font-size:.88rem;">No hay actividad reciente</p>
                    </div>
                <?php else: ?>
                    <?php
                    $feedColors = ['#6366f1','#10b981','#f59e0b','#3b82f6','#ec4899'];
                    foreach (array_slice($actividades, 0, 6) as $idx => $act):
                        $clr = $feedColors[$idx % count($feedColors)];
                        $ec  = $act->estado === 'Activa' ? '#10b981' : '#94a3b8';
                    ?>
                    <div class="act-feed-item">
                        <div class="act-feed-avatar" style="background:<?= $clr ?>18;color:<?= $clr ?>;">
                            <i class="ti ti-briefcase"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <p class="act-feed-name"><?= htmlspecialchars($act->nombre) ?></p>
                            <div style="display:flex;align-items:center;gap:8px;margin-top:3px;">
                                <span style="font-size:.7rem;font-weight:700;padding:2px 8px;border-radius:10px;background:<?= $ec ?>18;color:<?= $ec ?>;"><?= $act->estado ?></span>
                                <span class="act-feed-date"><?= date('d/m/Y', strtotime($act->fecha_inicio ?? 'now')) ?></span>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- ── Acciones Rápidas ──────────────────────────────── -->
        <div class="act-card acg-6">
            <div class="act-card-hdr">
                <div class="act-card-title">
                    <div class="act-icon-box" style="background:#f0fdf4;">
                        <i class="ti ti-rocket" style="font-size:1.1rem;color:#059669;"></i>
                    </div>
                    <div>
                        <div>Acciones Rápidas</div>
                        <div style="font-size:.72rem;color:#94a3b8;font-weight:500;margin-top:1px;">Gestión central</div>
                    </div>
                </div>
            </div>
            <div style="display:flex;flex-direction:column;gap:9px;flex:1;">
                <button data-bs-toggle="modal" data-bs-target="#modalNuevaActividad" class="act-quick-btn">
                    <div class="act-quick-icon" style="background:linear-gradient(135deg,#172554,#2563eb);color:white;"><i class="ti ti-plus"></i></div>
                    <div style="flex:1;"><div>Nueva Actividad</div><div style="font-size:.74rem;color:#94a3b8;font-weight:400;">Registrar proyecto</div></div>
                    <i class="ti ti-chevron-right" style="color:#94a3b8;"></i>
                </button>
                <button onclick="abrirModalAliados()" class="act-quick-btn">
                    <div class="act-quick-icon" style="background:linear-gradient(135deg,#7c3aed,#8b5cf6);color:white;"><i class="ti ti-school"></i></div>
                    <div style="flex:1;"><div>Gestionar Aliados</div><div style="font-size:.74rem;color:#94a3b8;font-weight:400;">Instituciones universitarias</div></div>
                    <i class="ti ti-chevron-right" style="color:#94a3b8;"></i>
                </button>
                <a href="<?= URLROOT ?>/reportes/actividades" class="act-quick-btn">
                    <div class="act-quick-icon" style="background:linear-gradient(135deg,#f59e0b,#d97706);color:white;"><i class="ti ti-file-report"></i></div>
                    <div style="flex:1;"><div>Generar Reporte</div><div style="font-size:.74rem;color:#94a3b8;font-weight:400;">Exportar estadísticas</div></div>
                    <i class="ti ti-chevron-right" style="color:#94a3b8;"></i>
                </a>
            </div>
        </div>

        <!-- ── Aliados Universitarios ────────────────────────── -->
        <div class="act-card acg-6">
            <div class="act-card-hdr">
                <div class="act-card-title">
                    <div class="act-icon-box" style="background:#faf5ff;">
                        <i class="ti ti-school" style="font-size:1.1rem;color:#7c3aed;"></i>
                    </div>
                    <div>
                        <div>Aliados Universitarios</div>
                        <div style="font-size:.72rem;color:#94a3b8;font-weight:500;margin-top:1px;">Origen de participantes</div>
                    </div>
                </div>
                <span class="act-badge" style="background:#faf5ff;color:#7c3aed;"><?= count($instituciones) ?> aliado<?= count($instituciones)!==1?'s':'' ?></span>
            </div>

            <div style="flex:1;overflow-y:auto;max-height:200px;margin-bottom:14px;">
                <?php if (empty($instituciones)): ?>
                    <div style="text-align:center;padding:28px 20px;color:#94a3b8;">
                        <i class="ti ti-school" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.4;"></i>
                        <p style="margin:0;font-size:.88rem;">No hay aliados registrados</p>
                    </div>
                <?php else: ?>
                    <?php foreach (array_slice($instituciones, 0, 5) as $inst): ?>
                    <div style="display:flex;align-items:center;gap:12px;padding:8px 0;border-bottom:1px solid #f8fafc;">
                        <div style="width:34px;height:34px;border-radius:9px;background:linear-gradient(135deg,#1e40af,#3b82f6);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="ti ti-building-factory-2" style="font-size:.9rem;color:white;"></i>
                        </div>
                        <div style="flex:1;min-width:0;">
                            <div style="font-weight:700;color:#1e293b;font-size:.85rem;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($inst->nombre) ?></div>
                            <?php if (!empty($inst->tipo)): ?>
                            <div style="font-size:.72rem;color:#94a3b8;"><?= htmlspecialchars($inst->tipo) ?></div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                    <?php if (count($instituciones) > 5): ?>
                    <div style="text-align:center;padding:7px 0;font-size:.78rem;color:#94a3b8;font-weight:600;">+<?= count($instituciones)-5 ?> más</div>
                    <?php endif; ?>
                <?php endif; ?>
            </div>

            <button onclick="abrirModalAliados()"
               style="display:flex;align-items:center;justify-content:center;gap:8px;padding:11px;background:linear-gradient(135deg,#7c3aed,#8b5cf6);color:white;border-radius:10px;font-weight:700;font-size:.875rem;border:none;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(124,58,237,.25);width:100%;"
               onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(124,58,237,.35)'"
               onmouseout="this.style.transform='none';this.style.boxShadow='0 4px 12px rgba(124,58,237,.25)'">
                <i class="ti ti-settings-2"></i> Gestionar Aliados
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         EXPLORAR ACTIVIDADES — Galería premium
         ══════════════════════════════════════════════════════ -->
    <div style="background:white;border-radius:20px;padding:24px;box-shadow:0 4px 20px rgba(0,0,0,0.04);border:1px solid rgba(0,0,0,0.05);">
        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:22px;padding-bottom:18px;border-bottom:1px solid #f1f5f9;flex-wrap:wrap;gap:14px;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:40px;height:40px;border-radius:12px;background:linear-gradient(135deg,#172554,#2563eb);display:flex;align-items:center;justify-content:center;">
                    <i class="ti ti-layout-grid" style="font-size:1.1rem;color:white;"></i>
                </div>
                <div>
                    <h2 style="font-size:1.1rem;font-weight:800;color:#0f172a;margin:0;">Explorar Actividades</h2>
                    <span style="font-size:.78rem;color:#94a3b8;"><?= count($actividades) ?> proyecto<?= count($actividades)!==1?'s':'' ?> registrado<?= count($actividades)!==1?'s':'' ?></span>
                </div>
            </div>
            <div style="display:flex;gap:10px;align-items:center;flex-wrap:wrap;">
                <div class="act-search-wrap">
                    <i class="ti ti-search"></i>
                    <input type="text" id="busqActividad" placeholder="Buscar proyecto..." oninput="filtrarActividades()">
                </div>
                <div style="display:flex;gap:6px;">
                    <button class="act-chip active" data-tipo="" onclick="setChip(this)">Todos</button>
                    <button class="act-chip" data-tipo="Servicio Comunitario" onclick="setChip(this)">S. Comunitario</button>
                    <button class="act-chip" data-tipo="Pasantía Corta" onclick="setChip(this)">Pasantía</button>
                </div>
            </div>
        </div>

        <?php if (empty($actividades)): ?>
        <div class="act-empty">
            <i class="ti ti-briefcase-off" style="font-size:4rem;color:#94a3b8;margin-bottom:16px;display:inline-block;"></i>
            <h3 style="font-size:1.4rem;color:#1e293b;font-weight:700;margin-bottom:8px;">No hay actividades registradas</h3>
            <p style="color:#64748b;margin-bottom:24px;">Crea la primera actividad para comenzar a gestionar proyectos.</p>
            <button data-bs-toggle="modal" data-bs-target="#modalNuevaActividad"
                style="background:linear-gradient(135deg,#172554,#2563eb);color:white;border:none;padding:12px 28px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.95rem;display:inline-flex;align-items:center;gap:8px;">
                <i class="ti ti-plus"></i> Nueva Actividad
            </button>
        </div>
        <?php else: ?>
        <div class="act-gallery-grid" id="actividadesGrid">
            <?php foreach ($actividades as $act):
                $esActiva     = $act->estado === 'Activa';
                $borderClr    = $esActiva ? '#10b981' : '#94a3b8';
                $headBg       = $esActiva ? 'rgba(16,185,129,0.05)' : 'rgba(100,116,139,0.04)';
                $estadoBg     = $esActiva ? '#ecfdf5' : '#f1f5f9';
                $estadoTxt    = $esActiva ? '#065f46' : '#475569';
                $estadoBorder = $esActiva ? '#10b981' : '#94a3b8';
                $nPart        = (int)($act->total_participantes ?? 0);
            ?>
            <div class="act-gallery-card"
                 data-tipo="<?= htmlspecialchars($act->tipo) ?>"
                 data-nombre="<?= htmlspecialchars($act->nombre) ?>"
                 style="border-left-color:<?= $borderClr ?>;">
                <div class="act-gallery-head" style="background:<?= $headBg ?>;">
                    <h5><?= htmlspecialchars($act->nombre) ?></h5>
                    <span class="act-badge-estado" style="background:<?= $estadoBg ?>;color:<?= $estadoTxt ?>;border:1px solid <?= $estadoBorder ?>;">
                        <?php if ($esActiva): ?><span class="act-pulsing-dot"></span><?php endif; ?>
                        <?= $act->estado ?>
                    </span>
                </div>
                <div class="act-gallery-body">
                    <div class="act-info-row hl">
                        <i class="ti ti-building"></i>
                        <span style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;"><?= htmlspecialchars($act->institucion_nombre ?? 'Sin Institución') ?></span>
                    </div>
                    <div class="act-info-row">
                        <i class="ti ti-tag"></i>
                        <span style="font-size:.78rem;"><?= htmlspecialchars($act->tipo ?? '—') ?></span>
                    </div>
                    <div style="margin-top:auto;display:flex;align-items:center;gap:6px;">
                        <i class="ti ti-users" style="color:#64748b;font-size:.95rem;"></i>
                        <span style="font-size:.85rem;font-weight:700;color:#1e293b;"><?= $nPart ?> participante<?= $nPart!==1?'s':'' ?></span>
                    </div>
                </div>
                <div class="act-gallery-foot">
                    <a href="<?= URLROOT ?>/actividades/ver/<?= $act->id ?>" class="btn-ver-act">
                        Ver Detalles <i class="ti ti-arrow-right"></i>
                    </a>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
        <?php endif; ?>
    </div>

</div>

<!-- ════════════════════════════════════════════════════════════
     MODAL: Gestionar Aliados Universitarios (PANEL GRANDE)
     ════════════════════════════════════════════════════════════ -->
<div class="modal-overlay" id="modalAliados">
    <div class="modal-aliados-box">
        <!-- Header -->
        <div class="modal-head">
            <div>
                <h2><i class="ti ti-school" style="margin-right:8px;"></i>Aliados Universitarios</h2>
                <p>Gestión de instituciones que envían participantes al ISP</p>
            </div>
            <button class="btn-cls-modal" onclick="cerrarModalAliados()"><i class="ti ti-x"></i></button>
        </div>
        <!-- Body: dos columnas -->
        <div class="modal-aliados-body">

            <!-- ── COLUMNA IZQUIERDA: Lista ───────────────────── -->
            <div style="display:flex;flex-direction:column;min-height:0;">
                <!-- Sub-header lista -->
                <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;">
                    <div>
                        <h4 style="margin:0;font-size:.95rem;font-weight:800;color:#1e293b;">Instituciones Registradas</h4>
                        <span style="font-size:.78rem;color:#94a3b8;" id="instCount"><?= count($instituciones) ?> aliado<?= count($instituciones)!==1?'s':'' ?></span>
                    </div>
                    <span style="font-size:.72rem;padding:4px 10px;border-radius:20px;background:#eff6ff;color:#2563eb;font-weight:700;"><?= count($instituciones) ?> total</span>
                </div>

                <!-- Lista scrollable -->
                <div id="aliadosList" style="flex:1;overflow-y:auto;max-height:360px;border:1px solid #f1f5f9;border-radius:14px;padding:8px 14px;">
                    <?php if (empty($instituciones)): ?>
                    <div style="text-align:center;padding:50px 20px;color:#94a3b8;">
                        <i class="ti ti-school" style="font-size:3rem;display:block;margin-bottom:12px;opacity:.4;"></i>
                        <p style="margin:0;font-size:.9rem;font-weight:600;">No hay instituciones registradas</p>
                        <p style="margin:4px 0 0;font-size:.78rem;">Usa el formulario para agregar la primera</p>
                    </div>
                    <?php else: ?>
                        <?php foreach ($instituciones as $inst):
                            $tipoCls = match($inst->tipo ?? '') {
                                'Universidad'    => 'tipo-univ',
                                'Instituto'      => 'tipo-inst',
                                'Colegio Técnico'=> 'tipo-col',
                                default          => 'tipo-otro',
                            };
                            $esActiva = (bool)($inst->activo ?? 1);
                        ?>
                        <div class="inst-list-item" id="rowInst_<?= $inst->id ?>">
                            <div class="inst-avatar" style="background:linear-gradient(135deg,#1e40af,#3b82f6);">
                                <i class="ti ti-building-factory-2" style="font-size:.95rem;color:white;"></i>
                            </div>
                            <div style="flex:1;min-width:0;">
                                <div class="inst-name"><?= htmlspecialchars($inst->nombre) ?></div>
                                <div style="display:flex;align-items:center;gap:6px;margin-top:3px;">
                                    <span class="badge-tipo <?= $tipoCls ?>"><?= htmlspecialchars($inst->tipo ?? 'Otro') ?></span>
                                    <span class="inst-badge-estado <?= $esActiva ? 'badge-activo' : 'badge-inactivo' ?>" id="badgeInst_<?= $inst->id ?>"><?= $esActiva ? 'Activa' : 'Inactiva' ?></span>
                                </div>
                            </div>
                            <?php if (!empty($inst->contacto)): ?>
                            <span style="font-size:.75rem;color:#64748b;white-space:nowrap;display:none;" class="d-md-block"><?= htmlspecialchars($inst->contacto) ?></span>
                            <?php endif; ?>
                            <button class="btn-toggle-inst <?= $esActiva ? 'act' : 'inact' ?>"
                                    id="btnToggle_<?= $inst->id ?>"
                                    onclick="toggleInstitucion(<?= $inst->id ?>, <?= $esActiva ? 1 : 0 ?>)">
                                <i class="ti <?= $esActiva ? 'ti-eye-off' : 'ti-eye' ?>"></i>
                                <?= $esActiva ? 'Desactivar' : 'Activar' ?>
                            </button>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div>

            <!-- ── COLUMNA DERECHA: Formulario agregar ──────── -->
            <div class="modal-panel-sep">
                <!-- Título -->
                <div style="display:flex;align-items:center;gap:10px;margin-bottom:18px;">
                    <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#7c3aed,#8b5cf6);display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-circle-plus" style="font-size:1rem;color:white;"></i>
                    </div>
                    <div>
                        <h4 style="margin:0;font-size:.95rem;font-weight:800;color:#1e293b;">Nueva Institución</h4>
                        <span style="font-size:.75rem;color:#94a3b8;">Registrar aliado universitario</span>
                    </div>
                </div>

                <form id="formNuevaInstitucion" method="POST" action="<?= URLROOT ?>/actividades/crearInstitucion" onsubmit="submitInstitucion(event)">
                    <?= Session::generateCsrfToken() ?>

                    <div class="f-group">
                        <label class="f-label">Nombre *</label>
                        <input type="text" name="nombre" class="f-input" placeholder="Ej. Universidad de Oriente" required>
                    </div>

                    <div class="f-group">
                        <label class="f-label">Tipo *</label>
                        <select name="tipo" class="f-input" required>
                            <option value="Universidad">Universidad</option>
                            <option value="Instituto">Instituto</option>
                            <option value="Colegio Técnico">Colegio Técnico</option>
                            <option value="Otro">Otro</option>
                        </select>
                    </div>

                    <div class="f-group">
                        <label class="f-label">Persona de Contacto</label>
                        <input type="text" name="contacto" class="f-input" placeholder="Ej. Prof. María González">
                    </div>

                    <div class="f-group">
                        <label class="f-label">Teléfono</label>
                        <input type="text" name="telefono" class="f-input" placeholder="Ej. 0286-1234567">
                    </div>

                    <div style="border-top:1px solid #f1f5f9;margin:18px 0;"></div>
                    <div style="display:flex;gap:10px;">
                        <button type="button" class="f-btn-cancel" onclick="cerrarModalAliados()">Cancelar</button>
                        <button type="submit" class="f-btn-primary" style="flex:2;" id="btnCrearInst">
                            <i class="ti ti-circle-plus"></i> Agregar
                        </button>
                    </div>
                </form>

                <!-- Info box -->
                <div style="margin-top:16px;background:#f8fafc;border-radius:10px;padding:11px 13px;display:flex;align-items:flex-start;gap:8px;">
                    <i class="ti ti-info-circle" style="color:#64748b;font-size:1rem;flex-shrink:0;margin-top:1px;"></i>
                    <span style="font-size:.76rem;color:#64748b;line-height:1.5;">Las instituciones activas pueden ser seleccionadas al crear nuevas actividades.</span>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     SCRIPTS
     ════════════════════════════════════════════════════════════ -->
<script>
const URLROOT_ACT = '<?= URLROOT ?>';
const SGP = { openModalAsignar() { new bootstrap.Modal(document.getElementById('modalNuevaActividad')).show(); } };

/* ── Modal Aliados ─────────────────────────────────────────── */
function abrirModalAliados()  { document.getElementById('modalAliados').classList.add('active'); }
function cerrarModalAliados() { document.getElementById('modalAliados').classList.remove('active'); }
document.getElementById('modalAliados').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('active');
});

<?php if ($abrirAliados): ?>
document.addEventListener('DOMContentLoaded', () => setTimeout(abrirModalAliados, 300));
<?php endif; ?>

/* ── Submit Nueva Institución (AJAX) ───────────────────────── */
async function submitInstitucion(e) {
    e.preventDefault();
    const form = e.target;
    const btn  = document.getElementById('btnCrearInst');
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader-2" style="animation:spin .8s linear infinite"></i> Guardando...';

    try {
        const res  = await fetch(form.action, { method:'POST', body: new FormData(form), headers:{'X-Requested-With':'XMLHttpRequest'} });
        const text = await res.text();
        let data;
        try { data = JSON.parse(text); } catch { data = null; }

        if (data && data.success) {
            agregarInstALista(data.institucion ?? { id: Date.now(), nombre: form.nombre.value, tipo: form.tipo.value, activo: 1 });
            form.reset();
            if (typeof NotificationService !== 'undefined') NotificationService.success('Institución registrada exitosamente');
        } else {
            // Fallback: recargar y reabrir modal
            window.location.href = URLROOT_ACT + '/actividades?aliados=1';
        }
    } catch(err) {
        window.location.href = URLROOT_ACT + '/actividades?aliados=1';
    } finally {
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-circle-plus"></i> Agregar';
    }
}

/* ── Agregar institución al DOM sin reload ─────────────────── */
function agregarInstALista(inst) {
    const list = document.getElementById('aliadosList');
    // Quitar estado vacío si existe
    const emptyEl = list.querySelector('[style*="opacity:.4"]');
    if (emptyEl) emptyEl.closest('div[style*="text-align"]')?.remove();

    const div = document.createElement('div');
    div.className = 'inst-list-item';
    div.id = `rowInst_${inst.id}`;
    div.innerHTML = `
        <div class="inst-avatar" style="background:linear-gradient(135deg,#1e40af,#3b82f6);">
            <i class="ti ti-building-factory-2" style="font-size:.95rem;color:white;"></i>
        </div>
        <div style="flex:1;min-width:0;">
            <div class="inst-name">${escHtml(inst.nombre)}</div>
            <div style="display:flex;align-items:center;gap:6px;margin-top:3px;">
                <span class="badge-tipo tipo-otro">${escHtml(inst.tipo ?? 'Otro')}</span>
                <span class="inst-badge-estado badge-activo" id="badgeInst_${inst.id}">Activa</span>
            </div>
        </div>
        <button class="btn-toggle-inst act" id="btnToggle_${inst.id}" onclick="toggleInstitucion(${inst.id}, 1)">
            <i class="ti ti-eye-off"></i> Desactivar
        </button>`;
    list.appendChild(div);

    // Actualizar contador
    const spans = document.querySelectorAll('#instCount, [id^="instCount"]');
    spans.forEach(s => {
        const n = list.querySelectorAll('.inst-list-item').length;
        s.textContent = `${n} aliado${n !== 1 ? 's' : ''}`;
    });
}
function escHtml(s) { const d = document.createElement('div'); d.textContent = s; return d.innerHTML; }

/* ── Toggle estado institución (AJAX) ──────────────────────── */
const CSRF_TOKEN_ACT = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
window.toggleInstitucion = async function(id, activo) {
    const btn = document.getElementById(`btnToggle_${id}`);
    const badge = document.getElementById(`badgeInst_${id}`);
    if (btn) { btn.disabled = true; btn.style.opacity = '0.6'; }
    try {
        const res  = await fetch(`${URLROOT_ACT}/actividades/toggleInstitucion`, {
            method:'POST',
            headers:{'Content-Type':'application/x-www-form-urlencoded','X-CSRF-TOKEN':CSRF_TOKEN_ACT,'X-Requested-With':'XMLHttpRequest'},
            body: new URLSearchParams({id}).toString()
        });
        const data = await res.json();
        if (data.success) {
            if (data.activo) {
                badge.textContent = 'Activa';
                badge.className   = 'inst-badge-estado badge-activo';
                btn.className     = 'btn-toggle-inst act';
                btn.innerHTML     = '<i class="ti ti-eye-off"></i> Desactivar';
                btn.setAttribute('onclick', `toggleInstitucion(${id}, 1)`);
            } else {
                badge.textContent = 'Inactiva';
                badge.className   = 'inst-badge-estado badge-inactivo';
                btn.className     = 'btn-toggle-inst inact';
                btn.innerHTML     = '<i class="ti ti-eye"></i> Activar';
                btn.setAttribute('onclick', `toggleInstitucion(${id}, 0)`);
            }
            if (typeof NotificationService !== 'undefined') NotificationService.success(data.message);
        } else {
            if (typeof NotificationService !== 'undefined') NotificationService.error(data.message || 'Error al cambiar estado.');
        }
    } catch(err) {
        if (typeof NotificationService !== 'undefined') NotificationService.error('Error de conexión.');
    } finally {
        if (btn) { btn.disabled = false; btn.style.opacity = '1'; }
    }
};

/* ── ApexCharts ────────────────────────────────────────────── */
(function initCharts() {
    if (typeof ApexCharts === 'undefined') { setTimeout(initCharts, 200); return; }
    const chartEl = document.getElementById('chart-actividades');
    if (!chartEl) return;
    const series = <?= $jsSeries ?>;
    const labels = <?= $jsLabels ?>;
    if (series.reduce((a, b) => a + b, 0) === 0) return;
    new ApexCharts(chartEl, {
        series, labels,
        chart: { type:'donut', height:230, fontFamily:'Inter,sans-serif', toolbar:{show:false} },
        colors: ['#6366f1','#3b82f6','#f59e0b','#10b981'],
        stroke: { show:false },
        plotOptions: { pie: { donut: { size:'72%', labels: { show:true, total: { show:true, label:'Total', fontSize:'12px', fontWeight:600, color:'#94a3b8', formatter: () => '<?= $kpiTotal ?>' } } } } },
        dataLabels: { enabled:false },
        legend: { position:'bottom', fontSize:'11px', markers:{radius:12} },
        tooltip: { y: { formatter: v => v + ' actividades' } }
    }).render();
})();

/* ── Filtros galería ───────────────────────────────────────── */
function setChip(el) {
    document.querySelectorAll('.act-chip').forEach(c => c.classList.remove('active'));
    el.classList.add('active');
    filtrarActividades();
}
function filtrarActividades() {
    const tipo = document.querySelector('.act-chip.active')?.dataset.tipo ?? '';
    const txt  = (document.getElementById('busqActividad')?.value ?? '').toLowerCase();
    document.querySelectorAll('.act-gallery-card').forEach(c => {
        const matchT = !tipo || c.dataset.tipo === tipo;
        const matchX = !txt  || c.dataset.nombre.toLowerCase().includes(txt);
        c.style.display = (matchT && matchX) ? 'flex' : 'none';
    });
}

/* ── Spin keyframe ─────────────────────────────────────────── */
const ss = document.createElement('style');
ss.textContent = '@keyframes spin{to{transform:rotate(360deg)}}';
document.head.appendChild(ss);
</script>
