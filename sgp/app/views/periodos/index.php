<?php
/**
 * Vista: Períodos Académicos — Lista principal
 * URL: /periodos  —  Cargada por PeriodosController::index()
 */
?>

<style>
/* ══════════════════════════════════════════════════════════════
   PERÍODOS ACADÉMICOS — BENTO PREMIUM UI
   Coherente con: actividades/index.php · configuracion/index.php
   ══════════════════════════════════════════════════════════════ */

/* ── Keyframes ──────────────────────────────────────────────── */
@keyframes per-fadeIn  { from{opacity:0}to{opacity:1} }
@keyframes per-slideUp { from{transform:translateY(24px);opacity:0}to{transform:translateY(0);opacity:1} }
@keyframes per-pulse   { 0%,100%{transform:scale(1);opacity:1}50%{transform:scale(1.5);opacity:.8} }

/* ── Modal overlay ──────────────────────────────────────────── */
.modal-overlay {
    display:none; position:fixed; inset:0;
    background:rgba(15,23,42,.7); backdrop-filter:blur(6px);
    z-index:9999; align-items:center; justify-content:center;
    animation:per-fadeIn .2s ease;
}
.modal-overlay.active { display:flex; }
.modal-box {
    background:white; border-radius:24px;
    width:90%; max-width:560px; max-height:90vh;
    display:flex; flex-direction:column; overflow:hidden;
    box-shadow:0 32px 80px rgba(15,23,42,.3); animation:per-slideUp .3s ease;
}
.modal-head {
    background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);
    padding:28px 32px; display:flex; justify-content:space-between;
    align-items:center; flex-shrink:0;
}
.modal-head h2 { font-size:1.3rem; font-weight:700; margin:0; color:white !important; }
.modal-head p  { font-size:.85rem; margin:4px 0 0; color:rgba(255,255,255,.8) !important; }
.btn-close-modal {
    background:rgba(255,255,255,.2); border:none; color:white;
    width:36px; height:36px; border-radius:50%; cursor:pointer;
    font-size:1.1rem; display:flex; align-items:center; justify-content:center; transition:background .2s;
}
.btn-close-modal:hover { background:rgba(255,255,255,.35); }
.modal-body { padding:28px 32px; overflow-y:auto; flex:1; }

/* ── Form components ────────────────────────────────────────── */
.form-group { margin-bottom:20px; }
.form-label {
    display:block; font-size:.82rem; font-weight:700; color:#374151;
    margin-bottom:8px; text-transform:uppercase; letter-spacing:.5px;
}
.form-input {
    width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px;
    font-size:.95rem; color:#1e293b; transition:border-color .2s,box-shadow .2s;
    box-sizing:border-box; background:#fafafa; font-family:inherit;
}
.form-input:focus { outline:none; border-color:#2563eb; box-shadow:0 0 0 4px rgba(79,70,229,.1); background:white; }
.btn-submit {
    width:100%; padding:14px;
    background:linear-gradient(135deg,#172554 0%,#2563eb 100%);
    color:white; border:none; border-radius:12px; font-size:1rem;
    font-weight:700; cursor:pointer; display:flex; align-items:center;
    justify-content:center; gap:10px; transition:all .2s;
    box-shadow:0 4px 16px rgba(79,70,229,.35); font-family:inherit;
}
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(79,70,229,.45); }

/* ── KPI CARDS — Vertical (idéntico a configuracion) ────────── */
.per-kpi-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 22px;
    margin-bottom: 22px;
}
.per-kpi-card {
    grid-column: span 3;
    background: white;
    border-radius: 20px;
    padding: 24px;
    display: flex;
    flex-direction: column;
    box-shadow: 0 4px 20px rgba(0,0,0,0.04);
    border: 1px solid rgba(0,0,0,0.05);
    min-width: 0;
    transition: transform .25s ease, box-shadow .25s ease;
    cursor: default;
}
.per-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 10px 28px rgba(0,0,0,0.07);
}
.per-kpi-icon {
    width: 46px; height: 46px; border-radius: 13px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; margin-bottom: 14px;
}
.per-kpi-value { font-size: 2rem; font-weight: 800; line-height: 1; margin-bottom: 5px; }
.per-kpi-label { font-size: 0.8rem; color: #64748b; font-weight: 500; }

/* ── Period cards (Bento Premium) ──────────────────────────── */
.periodos-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 24px;
    margin-bottom: 24px;
}
.periodo-card {
    background: white; border-radius: 20px;
    box-shadow: 0 4px 15px rgba(0,0,0,0.03);
    border: 1px solid #f1f5f9; border-left-width: 6px;
    display: flex; flex-direction: column;
    transition: all .3s ease; overflow: hidden;
}
.periodo-card:hover { transform: translateY(-4px); box-shadow: 0 12px 25px rgba(0,0,0,0.08); }

.card-header-c { padding: 20px 24px; }
.card-header-c h4 { margin: 0 0 12px; font-size: 1.15rem; font-weight: 700; color: #0f172a; line-height: 1.3; }

.badge-estado {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 6px 14px; border-radius: 20px;
    font-size: .75rem; font-weight: 700; letter-spacing: .3px;
}
.badge-activo  { background: #ecfdf5; color: #065f46; border: 1px solid #10b981; }
.badge-cerrado { background: #f1f5f9; color: #475569; border: 1px solid #94a3b8; }
.badge-planif  { background: #fffbeb; color: #b45309; border: 1px solid #f59e0b; }

.pulsing-dot {
    width: 6px; height: 6px; background: #10b981;
    border-radius: 50%; display: inline-block;
    animation: per-pulse 1.5s infinite;
}

.card-body-c { padding: 20px 24px; display: flex; flex-direction: column; gap: 14px; flex-grow: 1; border-top: 1px solid rgba(0,0,0,0.03); }
.info-row { display: flex; align-items: center; gap: 10px; color: #475569; font-size: .85rem; font-weight: 500; }
.info-row.fechas { background: #f8fafc; padding: 8px 12px; border-radius: 10px; border: 1px solid #e2e8f0; }

/* Avatar Stacks */
.avatar-stack { display: flex; align-items: center; }
.avatar-stack .ai {
    width: 32px; height: 32px; border-radius: 50%;
    background: linear-gradient(135deg,#1e3a8a,#2563eb); color: white;
    font-size: .75rem; font-weight: 700; display: flex; align-items: center; justify-content: center;
    border: 2px solid white; margin-left: -10px; box-shadow: 0 2px 4px rgba(0,0,0,0.1);
}
.avatar-stack .ai:first-child { margin-left: 0; }
.avatar-stack .ai-more { background: #e2e8f0; color: #475569; }

/* Progress Premium */
.progress-wrapper { margin-top: auto; padding-top: 12px; }
.progress-labels  { display: flex; justify-content: space-between; font-size: .75rem; color: #64748b; margin-bottom: 6px; font-weight: 600; }
.progress-track   { background: #e2e8f0; border-radius: 100px; height: 6px; overflow: hidden; }
.progress-fill    { height: 100%; border-radius: 100px; transition: width .6s ease; }

/* Footer Acciones */
.card-footer-c { padding: 16px 24px; border-top: 1px solid #f1f5f9; background: #fafafa; display: flex; gap: 10px; }
.btn-ver-full {
    flex: 1; display: flex; align-items: center; justify-content: center; gap: 8px;
    background: linear-gradient(135deg,#172554 0%,#2563eb 100%);
    color: white; border: none; padding: 10px; border-radius: 10px; font-weight: 600; font-size: .9rem; text-decoration: none;
    transition: transform .2s, box-shadow .2s;
}
.btn-ver-full:hover { transform: translateY(-2px); box-shadow: 0 4px 10px rgba(37,99,235,.3); color:white; }
.btn-icon-soft {
    width: 42px; height: 42px; display: flex; align-items: center; justify-content: center;
    background: white; border: 1.5px solid #e2e8f0; border-radius: 10px; color: #475569;
    transition: all .2s; cursor: pointer; text-decoration: none;
}
.btn-icon-soft:hover { background: #f1f5f9; color: #1e293b; border-color: #cbd5e1; }

/* ── Responsive ─────────────────────────────────────────────── */
@media (max-width: 1200px) {
    .per-kpi-card  { grid-column: span 6; }
    .periodos-grid { grid-template-columns: repeat(2,1fr); }
}
@media (max-width: 768px) {
    .per-kpi-card  { grid-column: span 12; }
    .periodos-grid { grid-template-columns: 1fr !important; }
    /* Banner de Períodos en móvil */
    .per-banner-actions {
        width: 100% !important;
        flex-wrap: wrap !important;
        gap: 8px !important;
    }
    .per-banner-actions button {
        flex: 1;
        min-width: 140px;
        justify-content: center;
        padding: 10px 14px !important;
        font-size: .85rem !important;
    }
}
</style>

<div style="width:100%;max-width:1600px;margin:0 auto;padding:20px;" id="periodos-pjax-container">

    <!-- ── Flash messages ───────────────────────────────────────── -->
    <?php if ($msg = Session::getFlash('success')): ?>
    <div style="background:#ecfdf5;border:1px solid #10b981;border-radius:12px;padding:14px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;color:#065f46;font-weight:600;animation:per-fadeIn .4s ease;">
        <i class="ti ti-circle-check" style="font-size:1.2rem;"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>
    <?php if ($msg = Session::getFlash('error')): ?>
    <div style="background:#fef2f2;border:1px solid #ef4444;border-radius:12px;padding:14px 20px;margin-bottom:24px;display:flex;align-items:center;gap:12px;color:#991b1b;font-weight:600;animation:per-fadeIn .4s ease;">
        <i class="ti ti-alert-circle" style="font-size:1.2rem;"></i> <?= htmlspecialchars($msg) ?>
    </div>
    <?php endif; ?>

    <!-- ══════════════════════════════════════════════════════
         BANNER PREMIUM
         ══════════════════════════════════════════════════════ -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:28px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;box-shadow:0 10px 25px rgba(30,58,138,.2);flex-wrap:wrap;gap:20px;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:radial-gradient(circle,rgba(255,255,255,.1) 0%,rgba(255,255,255,0) 70%);border-radius:50%;pointer-events:none;"></div>
        <div style="position:absolute;bottom:-40px;left:120px;width:160px;height:160px;background:radial-gradient(circle,rgba(255,255,255,.06) 0%,rgba(255,255,255,0) 70%);border-radius:50%;pointer-events:none;"></div>
        <div style="display:flex;align-items:center;gap:20px;z-index:1;">
            <div style="background:rgba(255,255,255,.2);backdrop-filter:blur(10px);border-radius:16px;width:64px;height:64px;display:flex;align-items:center;justify-content:center;">
                <i class="ti ti-calendar-event" style="font-size:2rem;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:2rem;font-weight:700;margin:0;letter-spacing:-.5px;">Períodos Académicos</h1>
                <p style="color:rgba(255,255,255,.8);margin:4px 0 0;font-size:1rem;">
                    <i class="ti ti-users"></i> <?= $totalPasantesSum ?> pasante<?= $totalPasantesSum !== 1 ? 's' : '' ?>
                    &nbsp;·&nbsp;
                    <i class="ti ti-calendar-stats"></i> <?= $totalPeriodos ?> período<?= $totalPeriodos !== 1 ? 's' : '' ?>
                </p>
            </div>
        </div>
        <div class="per-banner-actions" style="z-index:1; display:flex; gap:12px;">
            <button onclick="abrirModalConsulta()"
                style="background:white;color:#1e3a8a;border:none;padding:12px 24px;border-radius:12px;font-weight:800;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:.95rem;transition:all .3s;box-shadow:0 6px 15px rgba(0,0,0,0.15);"
                onmouseover="this.style.transform='translateY(-2px)'"
                onmouseout="this.style.transform='none'">
                <i class="ti ti-search"></i> Buscador Histórico
            </button>
            <button onclick="abrirModalCrear()"
                style="background:rgba(255,255,255,.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,.3);color:white;padding:12px 24px;border-radius:12px;font-weight:600;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:.95rem;transition:all .3s;"
                onmouseover="this.style.background='rgba(255,255,255,.25)';this.style.transform='translateY(-2px)'"
                onmouseout="this.style.background='rgba(255,255,255,.15)';this.style.transform='none'">
                <i class="ti ti-plus"></i> Nuevo Período
            </button>
        </div>
    </div>

    <!-- ══════════════════════════════════════════════════════
         KPI CARDS — vertical (estilo configuracion)
         ══════════════════════════════════════════════════════ -->
    <div class="per-kpi-grid">
        <?php
        $kpis = [
            ['label' => 'Total Períodos', 'val' => $totalPeriodos,    'color' => '#7c3aed', 'icon' => 'ti-calendar-stats', 'bg' => '#f5f3ff'],
            ['label' => 'Activos',        'val' => $totalActivos,     'color' => '#059669', 'icon' => 'ti-player-play',    'bg' => '#f0fdf4'],
            ['label' => 'Cerrados',       'val' => $totalCerrados,    'color' => '#64748b', 'icon' => 'ti-lock',           'bg' => '#f8fafc'],
            ['label' => 'Total Pasantes', 'val' => $totalPasantesSum, 'color' => '#2563eb', 'icon' => 'ti-users',          'bg' => '#eff6ff'],
        ];
        foreach ($kpis as $k): ?>
        <div class="per-kpi-card" style="border-left:4px solid <?= $k['color'] ?>;">
            <div class="per-kpi-icon" style="background:<?= $k['bg'] ?>;color:<?= $k['color'] ?>;">
                <i class="ti <?= $k['icon'] ?>"></i>
            </div>
            <div class="per-kpi-value" style="color:<?= $k['color'] ?>;"><?= $k['val'] ?></div>
            <div class="per-kpi-label"><?= $k['label'] ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ══════════════════════════════════════════════════════
         GRID DE CARDS DE PERÍODOS
         ══════════════════════════════════════════════════════ -->
    <?php if (empty($periodos)): ?>
    <div style="background:white;border-radius:20px;padding:60px 20px;text-align:center;border:1px dashed #cbd5e1;box-shadow:0 4px 15px rgba(0,0,0,0.03);">
        <i class="ti ti-calendar-off" style="font-size:4rem;color:#94a3b8;margin-bottom:16px;display:inline-block;"></i>
        <h3 style="font-size:1.5rem;color:#1e293b;font-weight:700;margin-bottom:8px;">No hay períodos académicos</h3>
        <p style="color:#64748b;margin-bottom:24px;">Crea el primer período para comenzar a gestionar las cohortes.</p>
        <button onclick="abrirModalCrear()"
            style="background:linear-gradient(135deg,#172554,#2563eb);color:white;border:none;padding:12px 28px;border-radius:10px;font-weight:600;cursor:pointer;font-size:.95rem;display:inline-flex;align-items:center;gap:8px;">
            <i class="ti ti-plus"></i> Nuevo Período
        </button>
    </div>
    <?php else: ?>

    <div class="periodos-grid">
        <?php foreach ($periodos as $p):
            $hoy   = new DateTime();
            $inicio = new DateTime($p->fecha_inicio);
            $fin    = new DateTime($p->fecha_fin);
            $totalDias     = max(1, $fin->diff($inicio)->days);
            $diasTranscurr = max(0, min($totalDias, $hoy->diff($inicio)->days));
            if ($hoy < $inicio) $diasTranscurr = 0;
            if ($hoy > $fin)    $diasTranscurr = $totalDias;
            $progresoPct = round(($diasTranscurr / $totalDias) * 100);

            $est = strtolower($p->estado ?? '');
            $cardBorder = '#d97706'; $badgeClass = 'badge-planif'; $estadoIcon = 'ti-clock';
            if ($est === 'activo') {
                $cardBorder = '#10b981'; $badgeClass = 'badge-activo'; $estadoIcon = 'ti-player-play';
            } elseif ($est === 'cerrado') {
                $cardBorder = '#64748b'; $badgeClass = 'badge-cerrado'; $estadoIcon = 'ti-lock';
            }
            $headBg = $est === 'activo'  ? 'rgba(16,185,129,0.05)'
                    : ($est === 'cerrado' ? 'rgba(100,116,139,0.04)'
                                          : 'rgba(217,119,6,0.05)');
            $nPart = (int)$p->total_pasantes;
        ?>
        <div class="periodo-card" style="border-left-color:<?= $cardBorder ?>;">
            <!-- Header -->
            <div class="card-header-c" style="background:<?= $headBg ?>;">
                <h4><?= htmlspecialchars($p->nombre) ?></h4>
                <div style="display:flex;gap:8px;align-items:center;">
                    <span class="badge-estado <?= $badgeClass ?>">
                        <?php if ($est === 'activo'): ?><span class="pulsing-dot"></span><?php endif; ?>
                        <i class="ti <?= $estadoIcon ?>" style="display:<?= $est === 'activo' ? 'none' : 'inline-block' ?>;"></i>
                        <?= ucfirst($est) ?>
                    </span>
                </div>
            </div>
            <!-- Body -->
            <div class="card-body-c">
                <div class="info-row fechas">
                    <i class="ti ti-calendar-event"></i>
                    <span style="font-weight:600;"><?= date('d M Y', strtotime($p->fecha_inicio)) ?> &rarr; <?= date('d M Y', strtotime($p->fecha_fin)) ?></span>
                </div>
                <?php if (!empty($p->descripcion)): ?>
                    <p style="font-size:.85rem;color:#64748b;margin:0;line-height:1.5;display:-webkit-box;-webkit-line-clamp:2;-webkit-box-orient:vertical;overflow:hidden;"><?= htmlspecialchars($p->descripcion) ?></p>
                <?php endif; ?>
                <div style="display:flex;align-items:center;justify-content:space-between;margin-top:auto;">
                    <div class="info-row" style="gap:12px;">
                        <i class="ti ti-users" style="font-size:1.1rem;"></i>
                        <span style="font-weight:700;color:#1e293b;"><?= $nPart ?> Pasante<?= $nPart !== 1 ? 's' : '' ?></span>
                    </div>
                    <?php if ($nPart > 0): ?>
                    <div class="avatar-stack">
                        <?php $letras = ['PA','US','IN'];
                        for ($i = 0; $i < min(3, $nPart); $i++): ?>
                            <div class="ai" title="Pasante"><?= $letras[$i] ?></div>
                        <?php endfor; ?>
                        <?php if ($nPart > 3): ?>
                            <div class="ai ai-more">+<?= ($nPart - 3) ?></div>
                        <?php endif; ?>
                    </div>
                    <?php endif; ?>
                </div>
                <div class="progress-wrapper">
                    <div class="progress-labels">
                        <span>Progreso transcurrido</span>
                        <span style="color:<?= $cardBorder ?>;"><?= $progresoPct ?>%</span>
                    </div>
                    <div class="progress-track">
                        <div class="progress-fill" style="width:<?= $progresoPct ?>%;background:<?= $cardBorder ?>;"></div>
                    </div>
                </div>
            </div>
            <!-- Footer -->
            <div class="card-footer-c">
                <a href="<?= URLROOT ?>/periodos/ver/<?= $p->id ?>" class="btn-ver-full">
                    Ver Pasantías <i class="ti ti-arrow-right"></i>
                </a>
                <?php if (strtolower($est) === 'planificado'): ?>
                <button title="Activar Período" onclick="confirmarActivar(<?= $p->id ?>, '<?= htmlspecialchars(addslashes($p->nombre)) ?>')" style="display:inline-flex; align-items:center; gap:6px; background:#eff6ff; color:#2563eb; border:1px solid #bfdbfe; padding:0 14px; border-radius:20px; font-weight:700; font-size:0.85rem; cursor:pointer; transition:all 0.2s; white-space:nowrap;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                    <i class="ti ti-toggle-right" style="font-size:1.4rem;"></i> Activar
                </button>
                <?php endif; ?>
                <?php if (strtolower($est) !== 'cerrado'): ?>
                <button title="Editar Período" onclick="abrirModalEditar(<?= htmlspecialchars(json_encode([
                    'id'           => (int)$p->id,
                    'nombre'       => $p->nombre,
                    'descripcion'  => $p->descripcion ?? '',
                    'fecha_inicio' => $p->fecha_inicio,
                    'fecha_fin'    => $p->fecha_fin,
                    'estado'       => $est,
                ]), ENT_QUOTES) ?>)" class="btn-icon-soft">
                    <i class="ti ti-pencil"></i>
                </button>
                <?php endif; ?>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <?php endif; ?>
</div>

<!-- ════════════════════════════════════════════════════════════
     MODAL: Nuevo Período
     ════════════════════════════════════════════════════════════ -->
<div id="modalCrearPeriodo" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2><i class="ti ti-calendar-plus" style="margin-right:8px;"></i>Nuevo Período Académico</h2>
                <p>Abre un nuevo ciclo de postulación y registro</p>
            </div>
            <button class="btn-close-modal" onclick="cerrarModalCrear()"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body" style="background:#f8fafc; padding:32px;">
            <form action="<?= URLROOT ?>/periodos/crear" method="POST" id="formCrearPeriodo">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                
                <div style="background:white; padding:24px; border-radius:16px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);">
                    <div class="form-group">
                        <label class="form-label" style="color:#64748b; font-size:0.75rem;">Nombre del Período / Cohorte *</label>
                        <input type="text" name="nombre" class="form-input" placeholder="Ej: Cohorte Médica 2026-I" required maxlength="100" style="border:none; border-bottom:2px solid #e2e8f0; border-radius:0; background:transparent; padding:8px 0; font-size:1.1rem; box-shadow:none;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="color:#64748b; font-size:0.75rem; margin-top:16px;">Descripción de Referencia</label>
                        <textarea name="descripcion" class="form-input" rows="2" style="resize:vertical; border:none; background:#f1f5f9; border-radius:12px; margin-top:4px;" placeholder="Agrega notas sobre este período..."></textarea>
                    </div>
                </div>

                <div style="background:white; padding:24px; border-radius:16px; border:1px solid #e2e8f0; margin-bottom:24px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);">
                    <h4 style="margin:0 0 16px; color:#1e293b; font-size:0.95rem; display:flex; align-items:center; gap:8px;"><i class="ti ti-clock-play" style="color:#2563eb;"></i> Timeline de Pasantía</h4>
                    <div class="form-group">
                        <label class="form-label" style="color:#64748b; font-size:0.75rem;">Selecciona la Fecha de Inicio *</label>
                        <input type="date" id="nuevo_fecha_inicio" name="fecha_inicio" class="form-input" required style="border-width:2px; padding:10px 14px;">
                    </div>
                    
                    <div style="margin-top:16px; padding:16px; background:#eff6ff; border:1px dashed #bfdbfe; border-radius:12px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <span style="display:block; font-size:0.75rem; font-weight:700; color:#1d4ed8; text-transform:uppercase; margin-bottom:4px;">Cierre Automático (8 Meses)</span>
                            <span id="label_calculo_fin" style="font-weight:700; color:#1e3a8a; font-size:1.1rem; letter-spacing:-0.5px;">Selecciona inicio...</span>
                        </div>
                        <div style="width:40px; height:40px; border-radius:50%; background:#dbeafe; color:#2563eb; display:flex; justify-content:center; align-items:center;">
                            <i class="ti ti-calendar-check" style="font-size:1.3rem;"></i>
                        </div>
                    </div>
                    <!-- Endpoint espera fecha_fin. Oculto para que el usuario no lo manipule UX. -->
                    <input type="hidden" id="nuevo_fecha_fin" name="fecha_fin" required>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="button" onclick="cerrarModalCrear()" style="flex:1;padding:14px;background:white;color:#475569;border:1px solid #cbd5e1;border-radius:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:all 0.2s;" onmouseover="this.style.background='#f1f5f9'">Cancelar</button>
                    <button type="submit" class="btn-submit" style="flex:2.5;"><i class="ti ti-checkups"></i> Confirmar y Crear Período</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════════════
     MODAL: Editar Período
     ════════════════════════════════════════════════════════════ -->
<div id="modalEditarPeriodo" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head" style="background:linear-gradient(135deg, #1e293b 0%, #0f172a 100%);">
            <div>
                <h2><i class="ti ti-pencil" style="margin-right:8px;"></i>Configuración de Período</h2>
                <p>Audita los parámetros de la cohorte actual</p>
            </div>
            <button class="btn-close-modal" onclick="cerrarModalEditar()"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body" style="background:#f8fafc; padding:32px;">
            <form action="<?= URLROOT ?>/periodos/editar" method="POST" id="formEditarPeriodo">
                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                <input type="hidden" name="periodo_id" id="editar_id">
                
                <div style="background:white; padding:24px; border-radius:16px; border:1px solid #e2e8f0; margin-bottom:20px; box-shadow:0 4px 6px -1px rgba(0,0,0,0.02);">
                    <div class="form-group">
                        <label class="form-label" style="color:#64748b; font-size:0.75rem;">Nombre del Período *</label>
                        <input type="text" id="editar_nombre" name="nombre" class="form-input" required maxlength="100" style="border:none; border-bottom:2px solid #e2e8f0; border-radius:0; background:transparent; padding:8px 0; font-size:1.1rem; box-shadow:none;">
                    </div>
                    <div class="form-group" style="margin-bottom:0;">
                        <label class="form-label" style="color:#64748b; font-size:0.75rem; margin-top:16px;">Descripción</label>
                        <textarea id="editar_descripcion" name="descripcion" class="form-input" rows="2" style="resize:vertical; border:none; background:#f1f5f9; border-radius:12px; margin-top:4px;"></textarea>
                    </div>
                </div>

                <div id="editar_warning_fechas" style="display:none; background:#f0fdf4; color:#166534; padding:16px; border-radius:12px; font-size:0.85rem; margin-bottom:20px; align-items:flex-start; gap:14px; border:1px solid #bbf7d0; box-shadow:0 4px 6px rgba(0,0,0,0.02);">
                    <div style="background:#22c55e; color:white; width:36px; height:36px; border-radius:50%; display:flex; align-items:center; justify-content:center; flex-shrink:0; box-shadow:0 2px 4px rgba(34,197,94,0.3);">
                        <i class="ti ti-timeline" style="font-size:1.3rem;"></i>
                    </div>
                    <div style="line-height:1.4;">
                        <b style="color:#15803d; font-size:0.95rem; display:block; margin-bottom:4px;">Timeline Blindado Visualmente</b>
                        Este período se encuentra actualmente en estado <b>Activo</b> albergando a múltiples pasantes. Para garantizar la integridad técnica de sus horas registradas, las fechas de origen solo pueden ser alteradas por base de datos, asegurando la coherencia global del sistema SGP.
                    </div>
                </div>

                <div id="editar_fechas_container" style="display:grid;grid-template-columns:1fr 1fr;gap:16px; background:white; padding:24px; border-radius:16px; border:1px solid #e2e8f0; margin-bottom:24px;">
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="color:#64748b; font-size:0.75rem;">Fecha Inicio *</label>
                        <input type="date" id="editar_fecha_inicio" name="fecha_inicio" class="form-input" required>
                    </div>
                    <div class="form-group" style="margin:0;">
                        <label class="form-label" style="color:#64748b; font-size:0.75rem;">Fecha Fin *</label>
                        <input type="date" id="editar_fecha_fin" name="fecha_fin" class="form-input" required>
                    </div>
                </div>

                <div style="display:flex;gap:12px;">
                    <button type="button" onclick="cerrarModalEditar()" style="flex:1;padding:14px;background:white;color:#475569;border:1px solid #cbd5e1;border-radius:12px;font-weight:700;cursor:pointer;font-family:inherit;transition:all .2s;" onmouseover="this.style.background='#f1f5f9'">Cancelar</button>
                    <button type="submit" class="btn-submit" style="flex:2.5; background:linear-gradient(135deg, #0f172a, #334155); box-shadow:0 6px 15px rgba(15,23,42,.3);"><i class="ti ti-device-floppy"></i> Guardar Cambios</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ═══════════════════════════════════════════════════════════════════
     MODAL: Consulta Rápida (Buscador Global)
     ═══════════════════════════════════════════════════════════════════ -->
<div id="modalConsultaRapida" class="modal-overlay">
    <div class="modal-box" style="max-width:700px; max-height:85vh;">
        <div class="modal-head" style="background:#1e293b;">
            <div>
                <h2><i class="ti ti-search" style="margin-right:8px;"></i>Buscador Histórico</h2>
                <p style="color:#94a3b8 !important;">Consulta rápida de expedientes de pasantes en toda la base de datos</p>
            </div>
            <button class="btn-close-modal" onclick="cerrarModalConsulta()"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body" style="background:#f8fafc; padding:24px; overflow-y:hidden; display:flex; flex-direction:column; gap:20px;">
            <div style="position:relative;">
                <i class="ti ti-search" style="position:absolute; left:16px; top:50%; transform:translateY(-50%); color:#64748b; font-size:1.2rem;"></i>
                <input type="text" id="inputBuscarHist" class="form-input" style="padding-left:48px; border-radius:16px; font-size:1.1rem;" placeholder="Escribe la cédula o nombre del pasante..." autocomplete="off">
            </div>
            
            <div id="resultadosBuscador" style="flex:1; overflow-y:auto; background:white; border-radius:16px; border:1px solid #e2e8f0; padding:12px; min-height:250px;">
                <div style="text-align:center; padding:40px 20px; color:#94a3b8;">
                    <i class="ti ti-file-search" style="font-size:3rem; margin-bottom:12px;"></i>
                    <p style="margin:0; font-weight:600;">Utiliza la barra superior para buscar un pasante histórico.</p>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
function abrirModalCrear()  { document.getElementById('modalCrearPeriodo').classList.add('active'); }
function cerrarModalCrear() { document.getElementById('modalCrearPeriodo').classList.remove('active'); document.getElementById('formCrearPeriodo').reset(); }

function abrirModalEditar(data) {
    document.getElementById('editar_id').value           = data.id;
    document.getElementById('editar_nombre').value       = data.nombre;
    document.getElementById('editar_descripcion').value  = data.descripcion;
    
    // Asegurar compatibilidad (si viene YYYY-MM-DD HH:MM:SS recortar solo al YYYY-MM-DD)
    const dbInicio = data.fecha_inicio ? data.fecha_inicio.substring(0, 10) : '';
    const dbFin    = data.fecha_fin ? data.fecha_fin.substring(0, 10) : '';
    document.getElementById('editar_fecha_inicio').value = dbInicio;
    document.getElementById('editar_fecha_fin').value    = dbFin;

    const fi = document.getElementById('editar_fecha_inicio');
    const ff = document.getElementById('editar_fecha_fin');
    
    // Si esta activo, bloquear fechas para no romper timeline general de estudiantes
    let alertMsg = document.getElementById('editar_warning_fechas');

    const fc = document.getElementById('editar_fechas_container');

    if (data.estado.toLowerCase() === 'activo') {
        fc.style.display = 'none';
        fi.setAttribute('readonly', 'true');
        ff.setAttribute('readonly', 'true');
        fi.style.background = '#f1f5f9'; fi.style.opacity = '0.7'; fi.style.borderColor = '#e2e8f0'; fi.style.pointerEvents = 'none';
        ff.style.background = '#f1f5f9'; ff.style.opacity = '0.7'; ff.style.borderColor = '#e2e8f0'; ff.style.pointerEvents = 'none';
        alertMsg.style.display = 'flex';
    } else {
        fc.style.display = 'grid';
        fi.removeAttribute('readonly');
        ff.removeAttribute('readonly');
        fi.style.background = '#fafafa'; fi.style.opacity = '1'; fi.style.borderColor = '#e5e7eb'; fi.style.pointerEvents = 'auto';
        ff.style.background = '#fafafa'; ff.style.opacity = '1'; ff.style.borderColor = '#e5e7eb'; ff.style.pointerEvents = 'auto';
        alertMsg.style.display = 'none';
    }

    document.getElementById('modalEditarPeriodo').classList.add('active');
}
function cerrarModalEditar() { document.getElementById('modalEditarPeriodo').classList.remove('active'); }

// Autocalculate 8 months
const nuevoInicio = document.getElementById('nuevo_fecha_inicio');
const nuevoFin = document.getElementById('nuevo_fecha_fin');
const labelFin = document.getElementById('label_calculo_fin');

function calcularOchoMeses(e) {
    if (e.target.value) {
        let d = new Date(e.target.value + 'T12:00:00'); // Evitar zona horaria
        d.setMonth(d.getMonth() + 8);
        let y = d.getFullYear();
        let m = String(d.getMonth() + 1).padStart(2, '0');
        let dd = String(d.getDate()).padStart(2, '0');
        nuevoFin.value = `${y}-${m}-${dd}`;
        
        let mesText = d.toLocaleString('es-ES', { month: 'long' });
        labelFin.innerHTML = `${d.getDate()} de ${mesText} de ${y}`;
    }
}

if(nuevoInicio) {
    nuevoInicio.addEventListener('change', calcularOchoMeses);
    nuevoInicio.addEventListener('input', calcularOchoMeses);
}

function confirmarActivar(id, nombre) {
    Swal.fire({
        title: '¿Iniciar ' + nombre + '?',
        html: 'Al activar este período, <b>el período actual en curso pasará a Cerrado automáticamente</b> y el módulo de asistencias se configurará para esta nueva cohorte.<br><br>¿Estás seguro de efectuar este salto de generación?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#2563eb',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: 'Sí, Iniciar Cohorte <i class="ti ti-rocket"></i>',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            let form = document.createElement('form');
            form.method = 'POST';
            form.action = '<?= URLROOT ?>/periodos/activar';
            
            let idInput = document.createElement('input');
            idInput.type = 'hidden';
            idInput.name = 'periodo_id';
            idInput.value = id;
            
            let csrfInput = document.createElement('input');
            csrfInput.type = 'hidden';
            csrfInput.name = 'csrf_token';
            csrfInput.value = '<?= htmlspecialchars($csrf) ?>';
            
            form.appendChild(idInput);
            form.appendChild(csrfInput);
            document.body.appendChild(form);
            form.submit();
        }
    });
}

['modalCrearPeriodo','modalEditarPeriodo', 'modalConsultaRapida'].forEach(id => {
    document.getElementById(id).addEventListener('click', function(e) {
        if (e.target === this) this.classList.remove('active');
    });
});

function abrirModalConsulta() {
    document.getElementById('modalConsultaRapida').classList.add('active');
    setTimeout(() => document.getElementById('inputBuscarHist').focus(), 100);
}
function cerrarModalConsulta() {
    document.getElementById('modalConsultaRapida').classList.remove('active');
}

let timeoutBuscador = null;
document.getElementById('inputBuscarHist').addEventListener('input', function() {
    clearTimeout(timeoutBuscador);
    const q = this.value.trim();
    const contenedor = document.getElementById('resultadosBuscador');
    
    if (q.length < 2) {
        contenedor.innerHTML = `<div style="text-align:center; padding:40px 20px; color:#94a3b8;">
            <i class="ti ti-file-search" style="font-size:3rem; margin-bottom:12px;"></i>
            <p style="margin:0; font-weight:600;">Ingresa al menos 2 caracteres.</p>
        </div>`;
        return;
    }
    
    contenedor.innerHTML = '<div style="text-align:center; padding:40px;"><i class="ti ti-loader" style="font-size:2rem; color:#2563eb; display:inline-block; animation:per-spin 1s linear infinite;"></i><style>@keyframes per-spin { 100% { transform:rotate(360deg); } }</style></div>';
    
    timeoutBuscador = setTimeout(() => {
        fetch('<?= URLROOT ?>/periodos/buscarGlobal?q=' + encodeURIComponent(q))
            .then(res => res.json())
            .then(data => {
                if (data.length === 0) {
                    contenedor.innerHTML = `<div style="text-align:center; padding:40px; color:#ef4444;">
                        <i class="ti ti-user-x" style="font-size:3rem; margin-bottom:12px;"></i>
                        <p style="margin:0; font-weight:600;">No se encontraron coincidencias.</p>
                    </div>`;
                    return;
                }
                
                let html = '<div style="display:flex; flex-direction:column; gap:12px;">';
                data.forEach(p => {
                    const pct = p.horas_meta > 0 ? Math.min(100, Math.round((p.horas_acumuladas / p.horas_meta) * 100)) : 0;
                    const depObj = p.departamento ? p.departamento : 'Dep. no asignado';
                    const perObj = p.periodo_nombre ? p.periodo_nombre : 'Sin cohorte';
                    const estPer = p.periodo_estado ? `(${p.periodo_estado})` : '';
                    
                    const btnReporte = `<button onclick="window.open('<?= URLROOT ?>/periodos/reporteHistoricoPasante/${p.id}', '_blank')" style="background:#2563eb; color:white; border:none; padding:8px 16px; border-radius:8px; font-weight:600; cursor:pointer; font-size:0.85rem; display:inline-flex; align-items:center; gap:6px; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'"><i class="ti ti-file-analytics"></i> Expediente</button>`;
                    
                    let btnConstancia = '';
                    if (p.estado_pasantia === 'Finalizado' && pct >= 100) {
                         btnConstancia = `<button onclick="window.open('<?= URLROOT ?>/periodos/cartaCulminacion/${p.id}', '_blank')" style="background:#10b981; color:white; border:none; padding:8px 16px; border-radius:8px; font-weight:600; cursor:pointer; font-size:0.85rem; display:inline-flex; align-items:center; gap:6px; transition:transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'"><i class="ti ti-certificate"></i> Constancia</button>`;
                    } else {
                         btnConstancia = `<button onclick="Swal.fire({title:'Operación bloqueada', text:'El estudiante no ha finalizado correctamente su carga horaria. Constancia no disponible.', icon:'error', confirmButtonColor:'#ef4444'})" style="background:#f1f5f9; color:#94a3b8; border:1px solid #e2e8f0; padding:8px 16px; border-radius:8px; font-weight:600; cursor:not-allowed; font-size:0.85rem; display:inline-flex; align-items:center; gap:6px;"><i class="ti ti-lock"></i> Constancia</button>`;
                    }

                    html += `
                    <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px; display:flex; justify-content:space-between; align-items:center;">
                        <div>
                            <h4 style="margin:0 0 4px; font-size:1.05rem; color:#1e293b;"><i class="ti ti-user-circle" style="color:#64748b; margin-right:4px;"></i>${p.nombres} ${p.apellidos}</h4>
                            <p style="margin:0 0 8px; font-size:0.85rem; color:#64748b;"><b>V-${p.cedula}</b> &nbsp;|&nbsp; <i class="ti ti-building"></i> ${depObj}</p>
                            <span style="background:#e0e7ff; color:#3730a3; padding:4px 10px; border-radius:8px; font-size:0.75rem; font-weight:700;"><i class="ti ti-calendar"></i> ${perObj} ${estPer}</span>
                        </div>
                        <div style="text-align:right;">
                            <div style="margin-bottom:8px; font-size:0.8rem; font-weight:700; color:#475569;">Progreso Horas: ${pct}%</div>
                            <div style="display:flex; gap:6px;">
                                ${btnConstancia}
                                ${btnReporte}
                            </div>
                        </div>
                    </div>`;
                });
                html += '</div>';
                contenedor.innerHTML = html;
            }).catch(err => {
                contenedor.innerHTML = `<div style="text-align:center; padding:40px; color:#ef4444;">Ocurrió un error cargando los datos.</div>`;
            });
    }, 450);
});
</script>
