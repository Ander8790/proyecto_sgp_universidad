<?php
/**
 * Vista: Actividades Extras — Servicio Comunitario
 */
$actividades     = $data['actividades']     ?? [];
$instituciones   = $data['instituciones']   ?? [];
$statActivas     = $data['statActivas']     ?? 0;
$statFinalizadas = $data['statFinalizadas'] ?? 0;
$statParticip    = $data['statParticip']    ?? 0;
$csrfToken       = $data['csrfToken']       ?? '';
?>
<style>
@keyframes sc-fadeUp { from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)} }

.sc-wrap { width:100%; }

/* Banner */
.sc-banner { background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:28px 36px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;overflow:hidden; }
.sc-banner::before { content:'';position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:rgba(255,255,255,0.06);border-radius:50%; }
.sc-back { display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);backdrop-filter:blur(10px);color:white;padding:8px 16px;border-radius:9px;font-size:.82rem;font-weight:700;text-decoration:none;transition:all .2s; }
.sc-back:hover { background:rgba(255,255,255,0.25);color:white; }
.sc-action-btn { display:inline-flex;align-items:center;gap:7px;background:white;color:#1e3a8a;border:none;padding:10px 20px;border-radius:10px;font-weight:700;font-size:.88rem;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(0,0,0,0.15); }
.sc-action-btn:hover { transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,0.2); }

/* KPIs */
.sc-kpi-row { display:grid;grid-template-columns:repeat(3,1fr);gap:16px;margin-bottom:22px; }
.sc-kpi { background:white;border-radius:14px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.06);display:flex;justify-content:space-between;align-items:center;transition:all .3s;animation:sc-fadeUp .4s ease both; }
.sc-kpi:hover { transform:translateY(-3px); }

/* Toggle vista */
.view-toggle { display:flex;gap:0;background:#f1f5f9;border-radius:10px;padding:3px; }
.vt-btn { padding:7px 16px;border:none;border-radius:8px;background:transparent;color:#64748b;font-size:.82rem;font-weight:700;cursor:pointer;transition:all .2s;display:flex;align-items:center;gap:5px; }
.vt-btn.active { background:white;color:#1e293b;box-shadow:0 2px 6px rgba(0,0,0,0.08); }

/* Grid actividades */
.sc-grid { display:grid;grid-template-columns:repeat(3,1fr);gap:18px;animation:sc-fadeUp .4s .1s ease both; }
.sc-card { background:white;border-radius:16px;border:1px solid #f1f5f9;border-left:5px solid #10b981;overflow:hidden;transition:all .3s; }
.sc-card:hover { transform:translateY(-4px);box-shadow:0 14px 28px rgba(0,0,0,0.08); }
.sc-card-head { padding:16px 18px 10px; }
.sc-card-title { font-size:.9rem;font-weight:700;color:#0f172a;margin:0 0 8px; }
.sc-card-body { padding:0 18px 10px;display:flex;flex-direction:column;gap:7px; }
.sc-row { display:flex;align-items:center;gap:7px;color:#475569;font-size:.8rem;font-weight:500; }
.sc-prog-bar { height:5px;background:#f1f5f9;border-radius:3px;overflow:hidden;margin:8px 0; }
.sc-prog-fill { height:100%;border-radius:3px; }
.sc-foot { padding:10px 18px;border-top:1px solid #f1f5f9;background:#fafafa;display:flex;justify-content:space-between;align-items:center; }
.btn-ver-sc { display:inline-flex;align-items:center;gap:5px;background:linear-gradient(135deg,#172554,#2563eb);color:white;border:none;padding:7px 14px;border-radius:8px;font-weight:600;font-size:.8rem;text-decoration:none;cursor:pointer;transition:all .2s; }
.btn-ver-sc:hover { opacity:.9;color:white; }
.btn-del-sc { display:inline-flex;align-items:center;gap:5px;background:#fef2f2;color:#dc2626;border:1px solid #fecaca;padding:7px 12px;border-radius:8px;font-weight:600;font-size:.8rem;cursor:pointer;transition:all .2s; }
.btn-del-sc:hover { background:#dc2626;color:white;border-color:#dc2626; }

/* Vista lista */
.sc-list-card { background:white;border-radius:18px;padding:20px 22px;box-shadow:0 4px 20px rgba(0,0,0,0.05);animation:sc-fadeUp .4s .1s ease both; }
.sc-list-row  { display:flex;align-items:center;gap:14px;padding:12px 0;border-bottom:1px solid #f8fafc; }
.sc-list-row:last-child { border-bottom:none; }
.sc-list-dot  { width:12px;height:12px;border-radius:50%;flex-shrink:0; }

/* Timeline vertical */
.timeline-card { background:white;border-radius:18px;padding:22px;box-shadow:0 4px 20px rgba(0,0,0,0.05);margin-top:22px;animation:sc-fadeUp .4s .15s ease both; }
.tl-item { display:flex;gap:16px;padding-bottom:22px;position:relative; }
.tl-item:last-child { padding-bottom:0; }
.tl-item::before { content:'';position:absolute;left:17px;top:32px;bottom:0;width:2px;background:#f1f5f9; }
.tl-item:last-child::before { display:none; }
.tl-dot  { width:36px;height:36px;border-radius:50%;flex-shrink:0;display:flex;align-items:center;justify-content:center;font-size:.9rem;z-index:1; }
.tl-body { flex:1;min-width:0;padding-top:6px; }
.tl-title { font-weight:700;color:#1e293b;font-size:.88rem;margin-bottom:3px; }
.tl-meta  { font-size:.75rem;color:#94a3b8; }

/* Modales */
.sc-modal-overlay { display:none;position:fixed;inset:0;background:rgba(15,23,42,.7);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center; }
.sc-modal-overlay.active { display:flex; }
.sc-modal-box { background:white;border-radius:22px;width:90%;max-width:560px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 32px 80px rgba(15,23,42,.3);animation:sc-fadeUp .3s ease; }
.sm-head { background:linear-gradient(135deg,#172554,#2563eb);padding:22px 26px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0; }
.sm-head h2 { font-size:1rem;font-weight:700;margin:0;color:white; }
.sm-body { padding:22px 26px;overflow-y:auto;flex:1; }
.sm-close { background:rgba(255,255,255,.2);border:none;color:white;width:30px;height:30px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center; }
.sm-close:hover { background:rgba(255,255,255,.35); }
.f-label { display:block;font-size:.72rem;font-weight:700;color:#374151;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px; }
.f-input { width:100%;padding:9px 12px;border:2px solid #e5e7eb;border-radius:9px;font-size:.88rem;color:#1e293b;transition:border-color .2s;box-sizing:border-box;background:#fafafa;font-family:inherit; }
.f-input:focus { outline:none;border-color:#2563eb;background:white; }
.f-group { margin-bottom:14px; }
.f-row2  { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
.f-btn-primary { width:100%;padding:11px;border:none;border-radius:9px;cursor:pointer;background:linear-gradient(135deg,#172554,#2563eb);color:white;font-size:.88rem;font-weight:700;display:flex;align-items:center;justify-content:center;gap:7px;transition:all .2s;font-family:inherit; }
.f-btn-primary:hover { transform:translateY(-1px);box-shadow:0 6px 16px rgba(37,99,235,.3); }
.f-btn-cancel { flex:1;padding:10px;background:#f1f5f9;color:#475569;border:2px solid #e2e8f0;border-radius:9px;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s;font-family:inherit; }
.f-btn-cancel:hover { background:#e2e8f0; }
.estado-badge { font-size:.7rem;padding:3px 10px;border-radius:100px;font-weight:700; }
.eb-activa { background:#dcfce7;color:#059669; } .eb-finalizada { background:#f1f5f9;color:#64748b; } .eb-otra { background:#fef9c3;color:#b45309; }

@media(max-width:1100px){ .sc-grid{grid-template-columns:1fr 1fr;} }
@media(max-width:640px)  { .sc-grid{grid-template-columns:1fr;} .sc-kpi-row{grid-template-columns:1fr;} }
</style>

<div class="sc-wrap">

<!-- BANNER -->
<div class="sc-banner">
    <div style="display:flex;align-items:center;gap:14px;z-index:1;flex-wrap:wrap;">
        <a href="<?= URLROOT ?>/actividades" class="sc-back"><i class="ti ti-arrow-left"></i> Volver</a>
        <div style="width:1px;height:28px;background:rgba(255,255,255,.2);"></div>
        <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:10px;">
            <i class="ti ti-hearts" style="font-size:24px;color:white;"></i>
        </div>
        <div>
            <h1 style="color:white;font-size:1.5rem;font-weight:800;margin:0;">Servicio Comunitario</h1>
            <p style="color:rgba(255,255,255,.7);margin:3px 0 0;font-size:.82rem;">Actividades externas e impacto social · <?= count($actividades) ?> actividades registradas</p>
        </div>
    </div>
    <div style="display:flex;align-items:center;gap:10px;z-index:1;">
        <div class="view-toggle">
            <button class="vt-btn active" id="btnGrid" onclick="cambiarVista('grid')"><i class="ti ti-layout-grid"></i> Grid</button>
            <button class="vt-btn" id="btnLista" onclick="cambiarVista('lista')"><i class="ti ti-list"></i> Lista</button>
        </div>
        <button class="sc-action-btn" onclick="document.getElementById('modalNuevaActividad').classList.add('active')">
            <i class="ti ti-plus"></i> Nueva Actividad
        </button>
    </div>
</div>

<!-- KPIs -->
<div class="sc-kpi-row">
    <div class="sc-kpi" style="border-left:4px solid #059669;" onmouseover="this.style.boxShadow='0 12px 24px rgba(5,150,105,.2)'" onmouseout="this.style.boxShadow=''">
        <div><p style="color:#64748b;font-size:.75rem;margin:0 0 5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Actividades en Curso</p>
        <h2 style="font-size:2rem;font-weight:800;color:#059669;margin:0;"><?= $statActivas ?></h2></div>
        <div style="background:#f0fdf4;color:#059669;width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;"><i class="ti ti-activity"></i></div>
    </div>
    <div class="sc-kpi" style="border-left:4px solid #2563eb;" onmouseover="this.style.boxShadow='0 12px 24px rgba(37,99,235,.2)'" onmouseout="this.style.boxShadow=''">
        <div><p style="color:#64748b;font-size:.75rem;margin:0 0 5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Total Participantes</p>
        <h2 style="font-size:2rem;font-weight:800;color:#2563eb;margin:0;"><?= $statParticip ?></h2></div>
        <div style="background:#eff6ff;color:#2563eb;width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;"><i class="ti ti-users"></i></div>
    </div>
    <div class="sc-kpi" style="border-left:4px solid #64748b;" onmouseover="this.style.boxShadow='0 12px 24px rgba(100,116,139,.2)'" onmouseout="this.style.boxShadow=''">
        <div><p style="color:#64748b;font-size:.75rem;margin:0 0 5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Finalizadas</p>
        <h2 style="font-size:2rem;font-weight:800;color:#64748b;margin:0;"><?= $statFinalizadas ?></h2></div>
        <div style="background:#f1f5f9;color:#64748b;width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;"><i class="ti ti-check"></i></div>
    </div>
</div>

<!-- VISTA GRID -->
<div id="vistaGrid">
<?php if (empty($actividades)): ?>
<div style="background:white;border-radius:18px;padding:60px 20px;text-align:center;color:#94a3b8;box-shadow:0 4px 20px rgba(0,0,0,0.05);">
    <i class="ti ti-hearts" style="font-size:3rem;display:block;margin-bottom:14px;opacity:.3;"></i>
    <div style="font-weight:600;font-size:1rem;">Sin actividades registradas</div>
    <div style="font-size:.85rem;margin-top:6px;">Crea la primera actividad de servicio comunitario</div>
</div>
<?php else: ?>
<div class="sc-grid">
<?php foreach ($actividades as $act):
    $colorBorder = $act->estado === 'Activa' ? '#10b981' : ($act->estado === 'Finalizada' ? '#64748b' : '#d97706');
    $eBadge = $act->estado === 'Activa' ? 'eb-activa' : ($act->estado === 'Finalizada' ? 'eb-finalizada' : 'eb-otra');
    // Progreso temporal
    $tProg = 0;
    if ($act->fecha_inicio && $act->fecha_fin) {
        $ini = new DateTime($act->fecha_inicio); $fin = new DateTime($act->fecha_fin); $hoy = new DateTime();
        $totD = max(1, $fin->diff($ini)->days); $trD = max(0, min($totD, $hoy->diff($ini)->days));
        if ($hoy > $fin) $trD = $totD; if ($hoy < $ini) $trD = 0;
        $tProg = round($trD / $totD * 100);
    }
    $progColor = $act->estado === 'Activa' ? '#10b981' : '#64748b';
?>
<div class="sc-card" style="border-left-color:<?= $colorBorder ?>;">
    <div class="sc-card-head">
        <h5 class="sc-card-title"><?= htmlspecialchars($act->nombre) ?></h5>
        <span class="estado-badge <?= $eBadge ?>">
            <?php if ($act->estado === 'Activa'): ?><span style="display:inline-block;width:6px;height:6px;background:#10b981;border-radius:50%;margin-right:4px;vertical-align:middle;"></span><?php endif; ?>
            <?= htmlspecialchars($act->estado) ?>
        </span>
        <?php if ($act->tipo !== 'Servicio Comunitario'): ?>
        <span style="background:#f1f5f9;color:#64748b;font-size:.67rem;font-weight:700;padding:2px 8px;border-radius:100px;margin-left:4px;"><?= htmlspecialchars($act->tipo) ?></span>
        <?php endif; ?>
    </div>
    <?php if ($act->fecha_inicio && $act->fecha_fin): ?>
    <div style="padding:0 18px 4px;">
        <div class="sc-prog-bar"><div class="sc-prog-fill" style="width:<?= $tProg ?>%;background:<?= $progColor ?>;"></div></div>
        <div style="font-size:.69rem;color:#94a3b8;text-align:right;"><?= $tProg ?>% del período</div>
    </div>
    <?php endif; ?>
    <div class="sc-card-body">
        <?php if ($act->institucion_nombre): ?>
        <div class="sc-row"><i class="ti ti-building-bank" style="color:#7c3aed;"></i><?= htmlspecialchars($act->institucion_nombre) ?></div>
        <?php endif; ?>
        <div class="sc-row"><i class="ti ti-users" style="color:#2563eb;"></i><?= (int)$act->total_participantes ?> participantes</div>
        <div class="sc-row"><i class="ti ti-calendar" style="color:#d97706;"></i>
            <?= date('d/m/Y', strtotime($act->fecha_inicio)) ?>
            <?= $act->fecha_fin ? ' → '.date('d/m/Y', strtotime($act->fecha_fin)) : '' ?>
        </div>
    </div>
    <div class="sc-foot">
        <a href="<?= URLROOT ?>/actividades/ver/<?= $act->id ?>" class="btn-ver-sc">
            <i class="ti ti-eye"></i> Ver detalle
        </a>
        <button type="button" class="btn-del-sc" onclick="confirmarEliminarActividad(<?= (int)$act->id ?>, '<?= addslashes(htmlspecialchars($act->nombre)) ?>')">
            <i class="ti ti-trash"></i> Eliminar
        </button>
    </div>
</div>
<?php endforeach; ?>
</div>
<?php endif; ?>
</div>

<!-- VISTA LISTA -->
<div id="vistaLista" style="display:none;">
<div class="sc-list-card">
    <?php if (empty($actividades)): ?>
    <div style="text-align:center;padding:40px;color:#94a3b8;">Sin actividades registradas.</div>
    <?php else: ?>
    <?php foreach ($actividades as $act):
        $dotColor = $act->estado === 'Activa' ? '#10b981' : ($act->estado === 'Finalizada' ? '#64748b' : '#d97706');
    ?>
    <div class="sc-list-row">
        <div class="sc-list-dot" style="background:<?= $dotColor ?>;"></div>
        <div style="flex:1;min-width:0;">
            <div style="font-weight:700;color:#1e293b;font-size:.88rem;"><?= htmlspecialchars($act->nombre) ?></div>
            <div style="font-size:.75rem;color:#94a3b8;margin-top:2px;display:flex;gap:10px;flex-wrap:wrap;">
                <span><?= htmlspecialchars($act->tipo) ?></span>
                <?php if ($act->institucion_nombre): ?><span>· <?= htmlspecialchars($act->institucion_nombre) ?></span><?php endif; ?>
                <span>· <?= (int)$act->total_participantes ?> participantes</span>
                <span>· <?= date('d/m/Y', strtotime($act->fecha_inicio)) ?></span>
            </div>
        </div>
        <span class="estado-badge <?= $act->estado === 'Activa' ? 'eb-activa' : ($act->estado === 'Finalizada' ? 'eb-finalizada' : 'eb-otra') ?>"><?= htmlspecialchars($act->estado) ?></span>
        <a href="<?= URLROOT ?>/actividades/ver/<?= $act->id ?>" class="btn-ver-sc" style="margin-left:10px;"><i class="ti ti-eye"></i></a>
        <button type="button" class="btn-del-sc" style="margin-left:6px;" onclick="confirmarEliminarActividad(<?= (int)$act->id ?>, '<?= addslashes(htmlspecialchars($act->nombre)) ?>')"><i class="ti ti-trash"></i></button>
    </div>
    <?php endforeach; ?>
    <?php endif; ?>
</div>
</div>

<!-- TIMELINE VERTICAL -->
<?php if (!empty($actividades)): ?>
<div class="timeline-card">
    <div style="font-size:.88rem;font-weight:700;color:#1e293b;margin-bottom:18px;display:flex;align-items:center;gap:8px;">
        <i class="ti ti-timeline" style="color:#059669;"></i> Línea de Tiempo
    </div>
    <?php
    $sortedActs = $actividades;
    usort($sortedActs, fn($a,$b) => strcmp($a->fecha_inicio, $b->fecha_inicio));
    foreach ($sortedActs as $act):
        $dotBg    = $act->estado === 'Activa' ? '#f0fdf4' : '#f1f5f9';
        $dotColor = $act->estado === 'Activa' ? '#059669' : '#64748b';
        $dotIcon  = $act->estado === 'Activa' ? 'ti-heart' : 'ti-check';
    ?>
    <div class="tl-item">
        <div class="tl-dot" style="background:<?= $dotBg ?>;color:<?= $dotColor ?>;border:2px solid <?= $dotColor ?>;">
            <i class="ti <?= $dotIcon ?>" style="font-size:.85rem;"></i>
        </div>
        <div class="tl-body">
            <div class="tl-title"><?= htmlspecialchars($act->nombre) ?>
                <span class="estado-badge <?= $act->estado === 'Activa' ? 'eb-activa' : 'eb-finalizada' ?>" style="margin-left:8px;"><?= htmlspecialchars($act->estado) ?></span>
            </div>
            <div class="tl-meta">
                <?= date('d/m/Y', strtotime($act->fecha_inicio)) ?><?= $act->fecha_fin ? ' → '.date('d/m/Y', strtotime($act->fecha_fin)) : '' ?>
                <?= $act->institucion_nombre ? ' · '.htmlspecialchars($act->institucion_nombre) : '' ?>
                · <?= (int)$act->total_participantes ?> participantes
            </div>
        </div>
        <a href="<?= URLROOT ?>/actividades/ver/<?= $act->id ?>" style="color:#059669;font-size:.78rem;font-weight:700;text-decoration:none;white-space:nowrap;">Ver →</a>
    </div>
    <?php endforeach; ?>
</div>
<?php endif; ?>

</div><!-- /sc-wrap -->

<!-- Formulario oculto para eliminar actividad -->
<form id="formEliminarActividad" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
</form>

<!-- MODAL: Nueva Actividad -->
<div id="modalNuevaActividad" class="sc-modal-overlay">
<div class="sc-modal-box">
    <div class="sm-head">
        <h2><i class="ti ti-hearts" style="margin-right:7px;"></i>Nueva Actividad</h2>
        <button class="sm-close" onclick="document.getElementById('modalNuevaActividad').classList.remove('active')"><i class="ti ti-x"></i></button>
    </div>
    <div class="sm-body">
        <form method="POST" action="<?= URLROOT ?>/actividades/crear">
            <input type="hidden" name="csrf_token" value="<?= $csrfToken ?>">
            <div class="f-group"><label class="f-label">Nombre / Título * <span class="sgp-tip" data-tip="Nombre descriptivo de la actividad. Aparece en reportes y PDFs. Ej: Brigada de mantenimiento escolar.">?</span></label>
                <input type="text" name="nombre" class="f-input" required placeholder="Ej. Brigada de mantenimiento...">
            </div>
            <div class="f-row2">
                <div class="f-group"><label class="f-label">Tipo <span class="sgp-tip" data-tip="'Servicio Comunitario' tiene seguimiento especial en los reportes. Elige 'Otro' para actividades no clasificadas.">?</span></label>
                    <select name="tipo" class="f-input">
                        <option value="Servicio Comunitario">Servicio Comunitario</option>
                        <option value="Mantenimiento">Mantenimiento</option>
                        <option value="Otro">Otro</option>
                    </select>
                </div>
                <div class="f-group"><label class="f-label">Institución <span class="sgp-tip" data-tip="Organismo externo donde se desarrolla la actividad. Si no aparece, añádela desde Configuración → Instituciones.">?</span></label>
                    <select name="institucion_id" class="f-input">
                        <option value="">— Sin especificar —</option>
                        <?php foreach ($instituciones as $i): ?>
                        <option value="<?= $i->id ?>"><?= htmlspecialchars($i->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>
            </div>
            <div class="f-row2">
                <div class="f-group"><label class="f-label">Fecha Inicio * <span class="sgp-tip" data-tip="Fecha en que comienza la actividad. Debe ser igual o anterior a la fecha de fin.">?</span></label>
                    <input type="date" name="fecha_inicio" class="f-input" required value="<?= date('Y-m-d') ?>">
                </div>
                <div class="f-group"><label class="f-label">Fecha Fin <span class="sgp-tip" data-tip="Opcional. Si la actividad es de un solo día, deja en blanco o iguala a la fecha de inicio.">?</span></label>
                    <input type="date" name="fecha_fin" class="f-input">
                </div>
            </div>
            <div class="f-group"><label class="f-label">Descripción</label>
                <textarea name="descripcion" class="f-input" rows="2" placeholder="Breve descripción de la actividad..."></textarea>
            </div>
            <div style="display:flex;gap:10px;">
                <button type="button" class="f-btn-cancel" onclick="document.getElementById('modalNuevaActividad').classList.remove('active')">Cancelar</button>
                <button type="submit" class="f-btn-primary"><i class="ti ti-plus"></i> Crear Actividad</button>
            </div>
        </form>
    </div>
</div>
</div>

<script>
function cambiarVista(modo) {
    const grid  = document.getElementById('vistaGrid');
    const lista = document.getElementById('vistaLista');
    const btnG  = document.getElementById('btnGrid');
    const btnL  = document.getElementById('btnLista');
    if (modo === 'grid') {
        grid.style.display=''; lista.style.display='none';
        btnG.classList.add('active'); btnL.classList.remove('active');
    } else {
        grid.style.display='none'; lista.style.display='';
        btnL.classList.add('active'); btnG.classList.remove('active');
    }
}
document.addEventListener('keydown', e => {
    if (e.key === 'Escape') document.querySelectorAll('.sc-modal-overlay.active').forEach(m => m.classList.remove('active'));
});
document.querySelectorAll('.sc-modal-overlay').forEach(m => m.addEventListener('click', e => { if(e.target===m) m.classList.remove('active'); }));

function confirmarEliminarActividad(id, nombre) {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
        title: '¿Eliminar actividad?',
        html: `<p style="color:#64748b;font-size:.95rem;">Estás a punto de eliminar permanentemente la actividad <strong>${nombre}</strong> y todos sus participantes registrados.<br><br>Esta acción <strong>no se puede deshacer</strong>.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="ti ti-trash"></i> Sí, eliminar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        customClass: { popup: 'sgp-swal-modal' },
        didOpen: () => { document.querySelector('.swal2-popup').style.borderRadius = '20px'; }
    }).then(result => {
        if (!result.isConfirmed) return;
        const form = document.getElementById('formEliminarActividad');
        form.action = `<?= URLROOT ?>/actividades/eliminar/${id}`;
        form.submit();
    });
}
</script>
