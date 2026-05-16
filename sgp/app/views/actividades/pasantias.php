<?php
/**
 * Vista: Actividades Extras — Pasantías Cortas
 */
$pasantesCortos      = $data['pasantesCortos']      ?? [];
$instituciones       = $data['instituciones']        ?? [];
$tutores             = $data['tutores']              ?? [];
$departamentos       = $data['departamentos']        ?? [];
$periodos            = $data['periodos']             ?? [];
$periodoCortoActivo  = $data['periodoCortoActivo']   ?? null;
$statActivos         = $data['statActivos']          ?? 0;
$statFinalizados     = $data['statFinalizados']      ?? 0;
$statPromPct         = $data['statPromPct']          ?? 0;
$kpiCortosActivos    = $data['kpiCortosActivos']     ?? 0;
$csrfToken           = $data['csrfToken']            ?? '';
?>
<style>
@keyframes pc-fadeUp { from{opacity:0;transform:translateY(14px)}to{opacity:1;transform:translateY(0)} }
@keyframes pc-pulse  { 0%,100%{opacity:1}50%{opacity:.4} }

.pc-wrap { width:100%; }

/* Banner */
.pc-banner { background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:28px 36px;margin-bottom:24px;display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:16px;position:relative;overflow:hidden; }
.pc-back   { display:inline-flex;align-items:center;gap:6px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.25);backdrop-filter:blur(10px);color:white;padding:8px 16px;border-radius:9px;font-size:.82rem;font-weight:700;text-decoration:none;transition:all .2s; }
.pc-back:hover { background:rgba(255,255,255,0.25);color:white; }
.pc-nuevo-btn { display:inline-flex;align-items:center;gap:7px;background:white;color:#2563eb;border:none;padding:10px 20px;border-radius:10px;font-weight:700;font-size:.88rem;cursor:pointer;transition:all .2s;box-shadow:0 4px 12px rgba(0,0,0,0.15); }
.pc-nuevo-btn:hover { transform:translateY(-2px);box-shadow:0 8px 20px rgba(0,0,0,0.2); }

/* KPIs */
.pc-kpi-row { display:grid;grid-template-columns:repeat(4,1fr);gap:16px;margin-bottom:22px; }
.pc-kpi { background:white;border-radius:14px;padding:18px;box-shadow:0 2px 12px rgba(0,0,0,0.06);display:flex;justify-content:space-between;align-items:center;transition:all .3s;animation:pc-fadeUp .4s ease both; }
.pc-kpi:hover { transform:translateY(-3px); }

/* Timeline período */
.pc-timeline-card { background:white;border-radius:18px;padding:20px 24px;box-shadow:0 4px 20px rgba(0,0,0,0.05);margin-bottom:22px;animation:pc-fadeUp .4s .05s ease both; }
.pc-timeline-bar  { height:10px;background:#f1f5f9;border-radius:100px;overflow:hidden;margin:14px 0 8px; }
.pc-timeline-fill { height:100%;background:linear-gradient(90deg,#34d399,#2563eb);border-radius:100px;transition:width 1.2s cubic-bezier(.4,0,.2,1); }

/* Filtros estado */
.estado-pills { display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap; }
.epill { padding:6px 16px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:.8rem;font-weight:700;cursor:pointer;background:white;color:#64748b;transition:all .2s; }
.epill:hover { border-color:#2563eb;color:#2563eb; }
.epill.active { background:#2563eb;color:white;border-color:#2563eb; }

/* Tabla */
.pc-table-card { background:white;border-radius:18px;padding:20px 22px;box-shadow:0 4px 20px rgba(0,0,0,0.05);margin-bottom:22px;animation:pc-fadeUp .4s .1s ease both; }
.pc-table { width:100%;border-collapse:collapse;font-size:.84rem; }
.pc-table th { padding:10px 12px;text-align:left;font-size:.71rem;font-weight:800;color:#94a3b8;text-transform:uppercase;letter-spacing:.5px;border-bottom:2px solid #f1f5f9; }
.pc-table td { padding:11px 12px;border-bottom:1px solid #f8fafc;vertical-align:middle; }
.pc-table tr:last-child td { border-bottom:none; }
.pc-table tr:hover td { background:#fafafa; }
.pct-name  { font-weight:700;color:#1e293b; }
.pct-sub   { font-size:.73rem;color:#94a3b8;margin-top:2px; }
.pct-bar   { display:flex;align-items:center;gap:7px; }
.pct-track { flex:1;height:6px;background:#f1f5f9;border-radius:3px;overflow:hidden; }
.pct-fill  { height:100%;border-radius:3px;background:linear-gradient(90deg,#2563eb,#10b981); }
.pct-pct   { font-size:.73rem;font-weight:700;color:#64748b;min-width:30px;text-align:right; }
.estado-act  { background:#dcfce7;color:#059669;font-size:.7rem;padding:3px 10px;border-radius:100px;font-weight:700; }
.estado-fin  { background:#f1f5f9;color:#64748b;font-size:.7rem;padding:3px 10px;border-radius:100px;font-weight:700; }
.estado-ret  { background:#fee2e2;color:#dc2626;font-size:.7rem;padding:3px 10px;border-radius:100px;font-weight:700; }
.btn-pc { width:30px;height:30px;border-radius:8px;border:none;cursor:pointer;display:inline-flex;align-items:center;justify-content:center;font-size:.88rem;transition:all .2s; }
.pc-field-msg { display:block;font-size:.72rem;margin-top:3px;min-height:14px;transition:all .2s; }
.pc-field-msg.error   { color:#ef4444; }
.pc-field-msg.success { color:#10b981; }
.btn-eye  { background:#eff6ff;color:#2563eb; } .btn-eye:hover  { background:#2563eb;color:white; }
.btn-edit { background:#f0fdf4;color:#059669; } .btn-edit:hover { background:#059669;color:white; }
.btn-asist{ background:#fdf4ff;color:#7c3aed; } .btn-asist:hover{ background:#7c3aed;color:white; }

/* Asistencias */
.asist-card  { background:white;border-radius:18px;padding:20px 24px;box-shadow:0 4px 20px rgba(0,0,0,0.05);animation:pc-fadeUp .4s .15s ease both; }
.asist-tabs  { display:flex;gap:8px;margin-bottom:18px;flex-wrap:wrap; }
.asist-tab   { padding:7px 18px;border-radius:20px;border:1.5px solid #e2e8f0;font-size:.82rem;font-weight:700;cursor:pointer;background:white;color:#64748b;transition:all .2s; }
.asist-tab:hover  { border-color:#7c3aed;color:#7c3aed; }
.asist-tab.active { background:linear-gradient(135deg,#172554,#2563eb);color:white;border-color:transparent; }
.asist-day-table  { width:100%;border-collapse:collapse;font-size:.84rem; }
.asist-day-table th { padding:10px 14px;text-align:center;font-size:.71rem;font-weight:800;color:#94a3b8;text-transform:uppercase;border-bottom:2px solid #f1f5f9; }
.asist-day-table td { padding:12px 14px;text-align:center;border-bottom:1px solid #f8fafc; }
.estado-pill { display:inline-flex;align-items:center;gap:5px;padding:4px 12px;border-radius:20px;font-size:.74rem;font-weight:700; }
.ep-p  { background:#dcfce7;color:#059669; } .ep-a { background:#fee2e2;color:#dc2626; }
.ep-j  { background:#fef9c3;color:#b45309; } .ep-r { background:#ffe4e6;color:#be123c; }
.ep-nd { background:#f1f5f9;color:#94a3b8; }
.cal-grid-wrap { display:grid;grid-template-columns:repeat(7,1fr);gap:4px;font-size:.78rem; }
.cal-day-hdr   { text-align:center;font-weight:800;color:#94a3b8;font-size:.67rem;text-transform:uppercase;padding:6px 2px; }
.cal-cell      { aspect-ratio:1;border-radius:8px;display:flex;flex-direction:column;align-items:center;justify-content:center;font-weight:700;font-size:.78rem; }
.cal-cell.finde{ opacity:.35; } .cal-cell.empty{ background:transparent; }
.cal-cell.cp   { background:#dcfce7;color:#059669; } .cal-cell.ca { background:#fee2e2;color:#dc2626; }
.cal-cell.cj   { background:#fef9c3;color:#b45309; } .cal-cell.cr { background:#ffe4e6;color:#be123c; }
.cal-cell.sin  { background:#f8fafc;color:#cbd5e1; }

/* Modales */
.pc-modal-overlay { display:none;position:fixed;inset:0;background:rgba(15,23,42,.7);backdrop-filter:blur(6px);z-index:9999;align-items:center;justify-content:center; }
.pc-modal-overlay.active { display:flex; }
.pc-modal-box { background:white;border-radius:22px;width:90%;max-width:640px;max-height:92vh;display:flex;flex-direction:column;overflow:hidden;box-shadow:0 32px 80px rgba(15,23,42,.3);animation:pc-fadeUp .3s ease; }
.pc-modal-sm  { max-width:440px; }
.pm-head { background:linear-gradient(135deg,#172554,#2563eb);padding:22px 26px;display:flex;justify-content:space-between;align-items:center;flex-shrink:0; }
.pm-head h2 { font-size:1rem;font-weight:700;margin:0;color:white; }
.pm-head p  { font-size:.78rem;margin:3px 0 0;color:rgba(255,255,255,.8); }
.pm-body { padding:22px 26px;overflow-y:auto;flex:1; }
.pm-close { background:rgba(255,255,255,.2);border:none;color:white;width:30px;height:30px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center; }
.pm-close:hover { background:rgba(255,255,255,.35); }
.f-label { display:block;font-size:.72rem;font-weight:700;color:#374151;margin-bottom:5px;text-transform:uppercase;letter-spacing:.5px; }
.f-input { width:100%;padding:9px 12px;border:2px solid #e5e7eb;border-radius:9px;font-size:.88rem;color:#1e293b;transition:border-color .2s;box-sizing:border-box;background:#fafafa;font-family:inherit; }
.f-input:focus { outline:none;border-color:#2563eb;background:white; }
.f-group { margin-bottom:14px; }
.f-row2  { display:grid;grid-template-columns:1fr 1fr;gap:12px; }
.f-section-lbl { font-size:.72rem;font-weight:800;color:#7c3aed;text-transform:uppercase;letter-spacing:.8px;margin:16px 0 10px;padding-bottom:5px;border-bottom:2px solid #f3e8ff; }
.f-btn-primary { width:100%;padding:11px;border:none;border-radius:9px;cursor:pointer;background:linear-gradient(135deg,#172554,#2563eb);color:white;font-size:.88rem;font-weight:700;display:flex;align-items:center;justify-content:center;gap:7px;transition:all .2s;font-family:inherit; }
.f-btn-primary:hover { transform:translateY(-1px);box-shadow:0 6px 16px rgba(37,99,235,.3); }
.f-btn-cancel { flex:1;padding:10px;background:#f1f5f9;color:#475569;border:2px solid #e2e8f0;border-radius:9px;font-size:.85rem;font-weight:600;cursor:pointer;transition:all .2s;font-family:inherit; }
.calc-box  { background:#f8fafc;border:1.5px solid #e2e8f0;border-radius:12px;padding:16px;margin-bottom:14px; }
.proy-card { background:linear-gradient(135deg,#172554,#1e3a8a);border-radius:10px;padding:14px;color:white;margin-top:12px; }
.proy-dato { display:flex;justify-content:space-between;align-items:center;padding:4px 0;border-bottom:1px solid rgba(255,255,255,.1);font-size:.8rem; }
.proy-dato span { color:rgba(255,255,255,.7); } .proy-dato strong { color:white; }
.proy-highlight { background:rgba(255,255,255,.12);border-radius:8px;padding:8px 12px;margin-top:8px;font-size:.83rem;font-weight:700;color:#7dd3fc; }
.temp-pass-box { background:#f0fdf4;border:2px dashed #10b981;border-radius:10px;padding:12px 16px;margin-top:14px;display:none; }
.temp-pass-box .lbl { font-size:.7rem;font-weight:800;color:#059669;text-transform:uppercase;letter-spacing:.5px; }
.temp-pass-box .val { font-size:1.2rem;font-weight:900;color:#065f46;letter-spacing:1px;margin-top:3px;font-family:monospace; }

@media(max-width:900px){ .pc-kpi-row{grid-template-columns:1fr 1fr;} }
@media(max-width:640px){ .pc-kpi-row{grid-template-columns:1fr;} }
</style>

<div class="pc-wrap">

<!-- BANNER -->
<div class="pc-banner">
    <div style="display:flex;align-items:center;gap:14px;z-index:1;flex-wrap:wrap;">
        <a href="<?= URLROOT ?>/actividades" class="pc-back"><i class="ti ti-arrow-left"></i> Volver</a>
        <div style="width:1px;height:28px;background:rgba(255,255,255,.2);"></div>
        <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:10px;">
            <i class="ti ti-user-star" style="font-size:24px;color:white;"></i>
        </div>
        <div>
            <h1 style="color:white;font-size:1.5rem;font-weight:800;margin:0;">Pasantías Cortas</h1>
            <p style="color:rgba(255,255,255,.7);margin:3px 0 0;font-size:.82rem;">
                <?php if ($periodoCortoActivo): ?>
                <span style="background:rgba(16,185,129,.25);border-radius:100px;padding:2px 10px;margin-right:6px;">
                    <span style="width:6px;height:6px;background:#34d399;border-radius:50%;display:inline-block;margin-right:4px;animation:pc-pulse 2s infinite;vertical-align:middle;"></span>
                    <?= htmlspecialchars($periodoCortoActivo->nombre) ?>
                </span>
                <?php else: ?>
                <span style="background:rgba(239,68,68,.25);border-radius:100px;padding:2px 10px;"><i class="ti ti-alert-circle"></i> Sin período corto activo</span>
                <?php endif; ?>
            </p>
        </div>
    </div>
    <button class="pc-nuevo-btn" onclick="abrirModalNuevoPasante()">
        <i class="ti ti-plus"></i> Nuevo Pasante
    </button>
</div>

<!-- KPIs -->
<div class="pc-kpi-row">
    <div class="pc-kpi" style="border-left:4px solid #2563eb;" onmouseover="this.style.boxShadow='0 12px 24px rgba(37,99,235,.2)'" onmouseout="this.style.boxShadow=''">
        <div><p style="color:#64748b;font-size:.75rem;margin:0 0 5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Activos</p>
        <h2 style="font-size:2rem;font-weight:800;color:#2563eb;margin:0;"><?= $statActivos ?></h2></div>
        <div style="background:#eff6ff;color:#2563eb;width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;"><i class="ti ti-user-check"></i></div>
    </div>
    <div class="pc-kpi" style="border-left:4px solid #059669;" onmouseover="this.style.boxShadow='0 12px 24px rgba(5,150,105,.2)'" onmouseout="this.style.boxShadow=''">
        <div><p style="color:#64748b;font-size:.75rem;margin:0 0 5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Finalizados</p>
        <h2 style="font-size:2rem;font-weight:800;color:#059669;margin:0;"><?= $statFinalizados ?></h2></div>
        <div style="background:#f0fdf4;color:#059669;width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;"><i class="ti ti-trophy"></i></div>
    </div>
    <div class="pc-kpi" style="border-left:4px solid #7c3aed;" onmouseover="this.style.boxShadow='0 12px 24px rgba(124,58,237,.2)'" onmouseout="this.style.boxShadow=''">
        <div><p style="color:#64748b;font-size:.75rem;margin:0 0 5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Promedio Progreso</p>
        <h2 style="font-size:2rem;font-weight:800;color:#7c3aed;margin:0;"><?= $statPromPct ?>%</h2></div>
        <div style="background:#fdf4ff;color:#7c3aed;width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;"><i class="ti ti-trending-up"></i></div>
    </div>
    <div class="pc-kpi" style="border-left:4px solid #d97706;" onmouseover="this.style.boxShadow='0 12px 24px rgba(217,119,6,.2)'" onmouseout="this.style.boxShadow=''">
        <div><p style="color:#64748b;font-size:.75rem;margin:0 0 5px;font-weight:600;text-transform:uppercase;letter-spacing:.4px;">Total Registrados</p>
        <h2 style="font-size:2rem;font-weight:800;color:#d97706;margin:0;"><?= count($pasantesCortos) ?></h2></div>
        <div style="background:#fffbeb;color:#d97706;width:42px;height:42px;border-radius:11px;display:flex;align-items:center;justify-content:center;font-size:1.3rem;"><i class="ti ti-users"></i></div>
    </div>
</div>

<!-- TIMELINE PERÍODO -->
<?php if ($periodoCortoActivo): ?>
<?php
$pIni  = new DateTime($periodoCortoActivo->fecha_inicio);
$pFin  = new DateTime($periodoCortoActivo->fecha_fin);
$hoy   = new DateTime();
$totD  = max(1, $pFin->diff($pIni)->days);
$trnD  = max(0, min($totD, $hoy->diff($pIni)->days));
if ($hoy < $pIni) $trnD = 0;
if ($hoy > $pFin) $trnD = $totD;
$ppct  = round($trnD / $totD * 100);
$restD = max(0, $totD - $trnD);
?>
<div class="pc-timeline-card">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:4px;">
        <span style="font-size:.82rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:7px;">
            <i class="ti ti-calendar-stats" style="color:#2563eb;"></i> Progreso del Período Corto
        </span>
        <span style="font-size:.8rem;font-weight:700;color:#2563eb;"><?= $ppct ?>% transcurrido</span>
    </div>
    <div class="pc-timeline-bar">
        <div class="pc-timeline-fill" id="timelineFill" style="width:0%"></div>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:.74rem;color:#94a3b8;">
        <span><?= date('d/m/Y', strtotime($periodoCortoActivo->fecha_inicio)) ?></span>
        <span style="font-weight:700;color:#64748b;">Hoy · Día <?= $trnD ?> de <?= $totD ?> · <?= $restD ?> restantes</span>
        <span><?= date('d/m/Y', strtotime($periodoCortoActivo->fecha_fin)) ?></span>
    </div>
</div>
<script>setTimeout(()=>{const f=document.getElementById('timelineFill');if(f)f.style.width='<?= $ppct ?>%';},200);</script>
<?php endif; ?>

<!-- TABLA PASANTES -->
<div class="pc-table-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:14px;">
        <span style="font-size:.9rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:8px;">
            <i class="ti ti-table" style="color:#2563eb;"></i> Lista de Pasantes
        </span>
        <div class="estado-pills">
            <button class="epill active" data-estado="Todos" onclick="filtrarEstado(this)">Todos</button>
            <button class="epill" data-estado="Activo" onclick="filtrarEstado(this)">Activos</button>
            <button class="epill" data-estado="Finalizado" onclick="filtrarEstado(this)">Finalizados</button>
            <button class="epill" data-estado="Retirado" onclick="filtrarEstado(this)">Retirados</button>
        </div>
    </div>
    <?php if (empty($pasantesCortos)): ?>
    <div style="text-align:center;padding:50px 20px;color:#94a3b8;">
        <i class="ti ti-users" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3;"></i>
        <div style="font-weight:600;">Sin pasantes de pasantía corta</div>
        <div style="font-size:.8rem;margin-top:4px;">Usa "Nuevo Pasante" para registrar el primero</div>
    </div>
    <?php else: ?>
    <div style="overflow-x:auto;">
    <table class="pc-table">
        <thead><tr>
            <th>Pasante</th><th>Institución</th><th>Departamento / Tutor</th>
            <th>Período</th><th>Progreso</th><th>Estado</th><th style="text-align:center;">Acciones</th>
        </tr></thead>
        <tbody id="tablaPasantes">
        <?php foreach ($pasantesCortos as $pc):
            $hMeta = max(1, (int)($pc->horas_meta ?? 480));
            $hAcum = (int)($pc->horas_acumuladas ?? 0);
            $pct   = min(100, round($hAcum / $hMeta * 100));
            $ep    = $pc->estado_pasantia ?? 'Pendiente';
            $eCls  = $ep === 'Activo' ? 'estado-act' : ($ep === 'Finalizado' ? 'estado-fin' : 'estado-ret');
            $pcJson= htmlspecialchars(json_encode($pc), ENT_QUOTES);
        ?>
        <tr data-estado="<?= $ep ?>">
            <td>
                <div class="pct-name"><?= htmlspecialchars(($pc->nombres??'').' '.($pc->apellidos??'')) ?></div>
                <div class="pct-sub"><?= htmlspecialchars($pc->cedula ?? '') ?></div>
            </td>
            <td style="font-size:.82rem;color:#475569;"><?= htmlspecialchars($pc->institucion_nombre ?? '—') ?></td>
            <td>
                <div style="font-size:.82rem;color:#475569;"><?= htmlspecialchars($pc->departamento_nombre ?? '—') ?></div>
                <div style="font-size:.73rem;color:#94a3b8;margin-top:2px;"><?= htmlspecialchars(trim($pc->tutor_nombre ?? '—')) ?></div>
            </td>
            <td style="font-size:.78rem;color:#64748b;"><?= htmlspecialchars($pc->periodo_nombre ?? '—') ?></td>
            <td style="min-width:100px;">
                <div class="pct-bar">
                    <div class="pct-track"><div class="pct-fill" style="width:<?= $pct ?>%;"></div></div>
                    <span class="pct-pct"><?= $pct ?>%</span>
                </div>
                <div style="font-size:.69rem;color:#94a3b8;margin-top:2px;"><?= $hAcum ?>/<?= $hMeta ?>h</div>
            </td>
            <td><span class="<?= $eCls ?>"><?= htmlspecialchars($ep) ?></span></td>
            <td style="text-align:center;">
                <div style="display:flex;gap:5px;justify-content:center;">
                    <a href="<?= URLROOT ?>/actividades/participante/<?= $pc->id ?>" class="btn-pc btn-eye" title="Ver almanaque"><i class="ti ti-eye"></i></a>
                    <button class="btn-pc btn-edit" title="Editar" onclick="abrirEditarPasante(<?= $pcJson ?>)"><i class="ti ti-edit"></i></button>
                    <button class="btn-pc btn-asist" title="Ver asistencia" onclick="seleccionarAsist(<?= $pc->id ?>, '<?= htmlspecialchars(($pc->nombres??'').' '.($pc->apellidos??''), ENT_QUOTES) ?>')"><i class="ti ti-calendar-check"></i></button>
                </div>
            </td>
        </tr>
        <?php endforeach; ?>
        </tbody>
    </table>
    </div>
    <?php endif; ?>
</div>

<!-- ASISTENCIAS -->
<div class="asist-card">
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:16px;flex-wrap:wrap;gap:10px;">
        <div style="font-size:.9rem;font-weight:700;color:#1e293b;display:flex;align-items:center;gap:8px;">
            <i class="ti ti-calendar-stats" style="color:#7c3aed;"></i>
            Asistencias — <span id="asistNombre" style="color:#7c3aed;">Selecciona un pasante</span>
        </div>
        <div style="display:flex;gap:8px;align-items:center;flex-wrap:wrap;">
            <select id="selPasanteAsist" onchange="cargarAsistencia()" style="padding:7px 12px;border:2px solid #e2e8f0;border-radius:10px;font-size:.84rem;color:#1e293b;font-family:inherit;">
                <option value="">— Seleccionar pasante —</option>
                <?php foreach ($pasantesCortos as $pc): ?>
                <option value="<?= $pc->id ?>"><?= htmlspecialchars(($pc->nombres??'').' '.($pc->apellidos??'')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
    </div>

    <div style="display:flex;align-items:center;justify-content:space-between;flex-wrap:wrap;gap:10px;margin-bottom:18px;">
        <div class="asist-tabs">
            <button class="asist-tab active" onclick="cambiarTab('diaria',this)"><i class="ti ti-calendar-day"></i> Diaria</button>
            <button class="asist-tab" onclick="cambiarTab('semanal',this)"><i class="ti ti-calendar-week"></i> Semanal</button>
            <button class="asist-tab" onclick="cambiarTab('mensual',this)"><i class="ti ti-calendar-month"></i> Mensual</button>
        </div>
        <div style="display:flex;gap:8px;align-items:center;">
            <input type="date" id="inputFechaAsist" value="<?= date('Y-m-d') ?>" onchange="cargarAsistencia()"
                   style="padding:6px 10px;border:2px solid #e2e8f0;border-radius:9px;font-size:.82rem;font-family:inherit;">
            <button onclick="abrirMarcarAsist()" style="display:inline-flex;align-items:center;gap:6px;background:linear-gradient(135deg,#172554,#2563eb);color:white;border:none;padding:8px 14px;border-radius:9px;font-weight:700;font-size:.82rem;cursor:pointer;">
                <i class="ti ti-plus"></i> Marcar
            </button>
        </div>
    </div>

    <div id="asistContent">
        <div style="text-align:center;padding:50px 20px;color:#94a3b8;">
            <i class="ti ti-calendar-stats" style="font-size:2.5rem;display:block;margin-bottom:10px;opacity:.3;"></i>
            <div>Selecciona un pasante de la tabla o del selector para ver su asistencia</div>
        </div>
    </div>
</div>

</div><!-- /pc-wrap -->

<!-- MODAL: Nuevo Pasante -->
<div id="modalNuevoPasante" class="pc-modal-overlay">
<div class="pc-modal-box">
    <div class="pm-head">
        <div><h2><i class="ti ti-user-plus" style="margin-right:7px;"></i>Nuevo Pasante — Pasantía Corta</h2>
        <p>Datos personales + asignación académica</p></div>
        <button class="pm-close" onclick="cerrarModalPC('modalNuevoPasante')"><i class="ti ti-x"></i></button>
    </div>
    <div class="pm-body">
        <div id="tempPassBox" class="temp-pass-box">
            <div class="lbl"><i class="ti ti-key"></i> Credenciales generadas — anótalas antes de cerrar</div>
            <div style="display:flex;gap:20px;flex-wrap:wrap;margin-top:4px;">
                <div>
                    <div style="font-size:.71rem;color:#065f46;font-weight:700;margin-bottom:3px;">Contraseña temporal (sistema)</div>
                    <div class="val" id="tempPassVal">—</div>
                </div>
                <div>
                    <div style="font-size:.71rem;color:#065f46;font-weight:700;margin-bottom:3px;">PIN de Kiosco (asistencia)</div>
                    <div class="val" id="pinKioscoVal">—</div>
                </div>
            </div>
            <div style="font-size:.72rem;color:#065f46;margin-top:6px;">El pasante usará el PIN para marcar asistencia desde el kiosco desde el primer día.</div>
        </div>

        <div class="f-section-lbl"><i class="ti ti-id-badge"></i> Datos del Pasante</div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Nombres *</label><input type="text" class="f-input" id="pc-nombres" placeholder="Juan Carlos"></div>
            <div class="f-group"><label class="f-label">Apellidos *</label><input type="text" class="f-input" id="pc-apellidos" placeholder="Pérez García"></div>
        </div>
        <div class="f-row2">
            <div class="f-group">
                <label class="f-label">Cédula * <span class="sgp-tip" data-tip="Solo números sin puntos ni letras. Ej: 12345678. Se usa como identificador único del pasante en el sistema.">?</span></label>
                <input type="text" class="f-input" id="pc-cedula" placeholder="12345678"
                    oninput="verificarCampoPc('cedula',this.value,'pc-cedula-msg')">
                <span id="pc-cedula-msg" class="pc-field-msg"></span>
            </div>
            <div class="f-group">
                <label class="f-label">Correo * <span class="sgp-tip" data-tip="Correo institucional o personal del pasante. Formato: nombre@correo.com. Se usa para notificaciones del sistema.">?</span></label>
                <input type="email" class="f-input" id="pc-correo" placeholder="correo@universidad.edu"
                    oninput="verificarCampoPc('correo',this.value,'pc-correo-msg')">
                <span id="pc-correo-msg" class="pc-field-msg"></span>
            </div>
        </div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Carrera <span class="sgp-tip" data-tip="Nombre del programa académico. Aparece en el perfil del pasante y en los PDFs. Ej: Ing. Informática.">?</span></label><input type="text" class="f-input" id="pc-carrera" placeholder="Ing. Informática"></div>
            <div class="f-group"><label class="f-label">Institución</label>
                <select class="f-input" id="pc-inst">
                    <option value="">— Sin especificar —</option>
                    <?php foreach ($instituciones as $i): ?>
                    <option value="<?= $i->id ?>"><?= htmlspecialchars($i->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>

        <div class="f-section-lbl"><i class="ti ti-link"></i> Asignación Académica</div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Tutor *</label>
                <select class="f-input" id="pc-tutor">
                    <option value="">— Selecciona —</option>
                    <?php foreach ($tutores as $t): ?>
                    <option value="<?= $t->id ?>"><?= htmlspecialchars($t->nombre_completo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="f-group"><label class="f-label">Departamento *</label>
                <select class="f-input" id="pc-depto">
                    <option value="">— Selecciona —</option>
                    <?php foreach ($departamentos as $d): ?>
                    <option value="<?= $d->id ?>"><?= htmlspecialchars($d->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="f-group">
            <label class="f-label">Período Corto</label>
            <?php if (empty($periodos)): ?>
            <div style="background:#fef9c3;border:1px solid #fde68a;border-radius:9px;padding:10px 13px;font-size:.82rem;color:#92400e;display:flex;align-items:center;gap:8px;">
                <i class="ti ti-alert-triangle"></i> Sin período corto activo. <a href="<?= URLROOT ?>/periodos" style="color:#1e3a8a;font-weight:700;">Crear en Configuración →</a>
            </div>
            <?php else: ?>
            <select class="f-input" id="pc-periodo">
                <option value="">— Sin especificar —</option>
                <?php foreach ($periodos as $p): ?>
                <option value="<?= $p->id ?>" <?= ($p->estado === 'Activo') ? 'selected' : '' ?>>
                    <?= htmlspecialchars($p->nombre) ?> (<?= $p->estado ?>)
                </option>
                <?php endforeach; ?>
            </select>
            <?php endif; ?>
        </div>

        <div class="calc-box">
            <div style="font-size:.78rem;font-weight:700;color:#374151;margin-bottom:10px;"><i class="ti ti-calculator"></i> Calculadora de Culminación</div>
            <div class="f-row2" style="margin-bottom:10px;">
                <div>
                    <label class="f-label">Horas Meta <span class="sgp-tip" data-tip="Total de horas que el pasante debe cumplir. Valor estándar: 480 horas (3 meses). Ajusta según el plan de estudios.">?</span></label>
                    <div style="display:flex;gap:7px;align-items:center;">
                        <input type="number" class="f-input" id="pc-horas" value="480" min="1" oninput="pcRecalcular()" style="flex:1;">
                        <button type="button" onclick="document.getElementById('pc-horas').value=480;pcRecalcular();"
                                style="padding:8px 10px;background:#f0fdf4;color:#059669;border:1.5px solid #bbf7d0;border-radius:9px;font-size:.72rem;font-weight:700;cursor:pointer;white-space:nowrap;">3 meses</button>
                    </div>
                </div>
                <div><label class="f-label">Fecha de Inicio *</label>
                    <input type="date" class="f-input" id="pc-fecha-inicio" value="<?= date('Y-m-d') ?>" oninput="pcRecalcular()">
                </div>
            </div>
            <div class="proy-card">
                <div style="font-size:.72rem;font-weight:800;color:rgba(255,255,255,.6);text-transform:uppercase;margin-bottom:8px;"><i class="ti ti-sparkles"></i> Proyección</div>
                <div class="proy-dato"><span>Meta</span><strong id="pc-p-horas">480h</strong></div>
                <div class="proy-dato"><span>Intensidad</span><strong>8 h/día</strong></div>
                <div class="proy-dato"><span>Días hábiles</span><strong id="pc-p-dias">60 días</strong></div>
                <div class="proy-highlight">🎓 Culminación estimada: <span id="pc-p-fin">—</span></div>
            </div>
            <input type="hidden" id="pc-fecha-fin">
        </div>

        <div style="display:flex;gap:10px;margin-top:4px;">
            <button class="f-btn-cancel" onclick="cerrarModalPC('modalNuevoPasante')">Cancelar</button>
            <button class="f-btn-primary" onclick="guardarNuevoPasante()"><i class="ti ti-user-plus"></i> Registrar Pasante</button>
        </div>
    </div>
</div>
</div>

<!-- MODAL: Editar Pasante -->
<div id="modalEditarPasante" class="pc-modal-overlay">
<div class="pc-modal-box">
    <div class="pm-head">
        <div><h2><i class="ti ti-edit" style="margin-right:7px;"></i>Editar Pasante</h2>
        <p id="editPasSubtitle">Actualizar datos</p></div>
        <button class="pm-close" onclick="cerrarModalPC('modalEditarPasante')"><i class="ti ti-x"></i></button>
    </div>
    <div class="pm-body">
        <input type="hidden" id="edit-uid">
        <div class="f-section-lbl"><i class="ti ti-id-badge"></i> Datos Personales</div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Nombres</label><input type="text" class="f-input" id="edit-nombres"></div>
            <div class="f-group"><label class="f-label">Apellidos</label><input type="text" class="f-input" id="edit-apellidos"></div>
        </div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Carrera</label><input type="text" class="f-input" id="edit-carrera"></div>
            <div class="f-group"><label class="f-label">Institución</label>
                <select class="f-input" id="edit-inst">
                    <option value="">— Sin especificar —</option>
                    <?php foreach ($instituciones as $i): ?>
                    <option value="<?= $i->id ?>"><?= htmlspecialchars($i->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="f-section-lbl"><i class="ti ti-link"></i> Asignación</div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Tutor</label>
                <select class="f-input" id="edit-tutor">
                    <option value="">— Selecciona —</option>
                    <?php foreach ($tutores as $t): ?>
                    <option value="<?= $t->id ?>"><?= htmlspecialchars($t->nombre_completo) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
            <div class="f-group"><label class="f-label">Departamento</label>
                <select class="f-input" id="edit-depto">
                    <option value="">— Selecciona —</option>
                    <?php foreach ($departamentos as $d): ?>
                    <option value="<?= $d->id ?>"><?= htmlspecialchars($d->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Estado</label>
                <select class="f-input" id="edit-estado">
                    <option value="Activo">Activo</option>
                    <option value="Finalizado">Finalizado</option>
                    <option value="Retirado">Retirado</option>
                </select>
            </div>
            <div class="f-group"><label class="f-label">Horas Meta</label>
                <input type="number" class="f-input" id="edit-horas" min="1">
            </div>
        </div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Fecha Inicio</label><input type="date" class="f-input" id="edit-fecha-inicio"></div>
            <div class="f-group"><label class="f-label">Período</label>
                <select class="f-input" id="edit-periodo">
                    <option value="">— Selecciona —</option>
                    <?php foreach ($periodos as $p): ?>
                    <option value="<?= $p->id ?>"><?= htmlspecialchars($p->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>
        </div>
        <div style="display:flex;gap:10px;margin-top:4px;">
            <button class="f-btn-cancel" onclick="cerrarModalPC('modalEditarPasante')">Cancelar</button>
            <button class="f-btn-primary" onclick="guardarEdicionPasante()"><i class="ti ti-device-floppy"></i> Guardar Cambios</button>
        </div>
    </div>
</div>
</div>

<!-- MODAL: Marcar Asistencia -->
<div id="modalMarcarAsist" class="pc-modal-overlay">
<div class="pc-modal-box pc-modal-sm">
    <div class="pm-head">
        <div><h2><i class="ti ti-calendar-check" style="margin-right:7px;"></i>Marcar Asistencia</h2></div>
        <button class="pm-close" onclick="cerrarModalPC('modalMarcarAsist')"><i class="ti ti-x"></i></button>
    </div>
    <div class="pm-body">
        <input type="hidden" id="ma-pid">
        <div class="f-group"><label class="f-label">Pasante</label>
            <select class="f-input" id="ma-sel" onchange="document.getElementById('ma-pid').value=this.value">
                <option value="">— Selecciona —</option>
                <?php foreach ($pasantesCortos as $pc): ?>
                <option value="<?= $pc->id ?>"><?= htmlspecialchars(($pc->nombres??'').' '.($pc->apellidos??'')) ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="f-row2">
            <div class="f-group"><label class="f-label">Fecha</label><input type="date" class="f-input" id="ma-fecha" value="<?= date('Y-m-d') ?>"></div>
            <div class="f-group"><label class="f-label">Estado</label>
                <select class="f-input" id="ma-estado">
                    <option value="Presente">✅ Presente</option>
                    <option value="Ausente">❌ Ausente</option>
                    <option value="Justificado">📋 Justificado</option>
                    <option value="Retardo">⚠️ Retardo</option>
                </select>
            </div>
        </div>
        <div class="f-group"><label class="f-label">Motivo / Nota</label><input type="text" class="f-input" id="ma-motivo" placeholder="Opcional..."></div>
        <div style="display:flex;gap:10px;">
            <button class="f-btn-cancel" onclick="cerrarModalPC('modalMarcarAsist')">Cancelar</button>
            <button class="f-btn-primary" onclick="enviarMarcarAsistencia()"><i class="ti ti-check"></i> Registrar</button>
        </div>
    </div>
</div>
</div>

<script>
const PC_CSRF = '<?= $csrfToken ?>';
let tabActual = 'diaria', pasanteAsistId = null;

// Filtro por estado
function filtrarEstado(btn) {
    document.querySelectorAll('.epill').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    const est = btn.dataset.estado;
    document.querySelectorAll('#tablaPasantes tr').forEach(tr => {
        tr.style.display = (est === 'Todos' || tr.dataset.estado === est) ? '' : 'none';
    });
}

// Modales
function abrirModalNuevoPasante() {
    <?php if (!$periodoCortoActivo): ?>
    Swal.fire({
        icon: 'warning',
        title: 'Acción Bloqueada',
        html: 'No puedes agregar pasantes nuevos mientras no exista un período corto <b>activo</b>.<br><br>Por favor, dirígete al módulo de <b>Períodos Académicos</b> para aperturar uno.',
        confirmButtonColor: '#2563eb',
        confirmButtonText: 'Ir a Períodos <i class="ti ti-arrow-right"></i>'
    }).then((r) => {
        if (r.isConfirmed) {
            window.location.href = '<?= URLROOT ?>/periodos';
        }
    });
    return;
    <?php endif; ?>
    document.getElementById('tempPassBox').style.display = 'none';
    document.getElementById('modalNuevoPasante').classList.add('active');
    pcRecalcular();
}
function cerrarModalPC(id) { document.getElementById(id).classList.remove('active'); }

function abrirEditarPasante(pc) {
    document.getElementById('edit-uid').value       = pc.id;
    document.getElementById('edit-nombres').value   = pc.nombres  ?? '';
    document.getElementById('edit-apellidos').value = pc.apellidos ?? '';
    document.getElementById('edit-carrera').value   = pc.cargo ?? '';
    document.getElementById('edit-horas').value     = pc.horas_meta ?? 480;
    document.getElementById('edit-fecha-inicio').value = pc.fecha_inicio_pasantia ?? '';
    document.getElementById('edit-estado').value    = pc.estado_pasantia ?? 'Activo';
    document.getElementById('editPasSubtitle').textContent = (pc.nombres??'') + ' ' + (pc.apellidos??'');
    document.getElementById('modalEditarPasante').classList.add('active');
}

function seleccionarAsist(id, nombre) {
    document.getElementById('selPasanteAsist').value = id;
    document.getElementById('asistNombre').textContent = nombre;
    pasanteAsistId = id;
    cargarAsistencia();
    document.getElementById('asistContent').scrollIntoView({ behavior: 'smooth', block: 'start' });
}

function abrirMarcarAsist() {
    if (pasanteAsistId) {
        document.getElementById('ma-pid').value = pasanteAsistId;
        document.getElementById('ma-sel').value = pasanteAsistId;
    }
    document.getElementById('ma-fecha').value = new Date().toISOString().split('T')[0];
    document.getElementById('modalMarcarAsist').classList.add('active');
}

// Calculadora
function pcRecalcular() {
    const horas = parseInt(document.getElementById('pc-horas').value) || 480;
    const fechaStr = document.getElementById('pc-fecha-inicio').value;
    const diasHab = Math.ceil(horas / 8);
    document.getElementById('pc-p-horas').textContent = horas + 'h';
    document.getElementById('pc-p-dias').textContent  = diasHab + ' días';
    if (fechaStr) {
        const d = new Date(fechaStr + 'T00:00:00');
        let count = 0, cur = new Date(d);
        while (count < diasHab) { cur.setDate(cur.getDate()+1); const dn=cur.getDay(); if(dn!==0&&dn!==6)count++; }
        document.getElementById('pc-p-fin').textContent = cur.toLocaleDateString('es-VE',{day:'2-digit',month:'2-digit',year:'numeric'});
        document.getElementById('pc-fecha-fin').value = cur.toISOString().split('T')[0];
    }
}

// Validación en tiempo real de campos únicos
const _pcVerifTimers = {};
const _pcVerifErrors = {};

function verificarCampoPc(campo, valor, msgId) {
    clearTimeout(_pcVerifTimers[campo]);
    const msgEl = document.getElementById(msgId);
    if (!msgEl) return;
    if (!valor || valor.length < 3) {
        msgEl.textContent = ''; msgEl.className = 'pc-field-msg';
        delete _pcVerifErrors[campo]; return;
    }
    msgEl.textContent = '⏳ Verificando...'; msgEl.className = 'pc-field-msg';
    _pcVerifTimers[campo] = setTimeout(async () => {
        try {
            const r   = await fetch(`${URLROOT}/actividades/verificarCampo?campo=${encodeURIComponent(campo)}&valor=${encodeURIComponent(valor)}`);
            const res = await r.json();
            if (res.disponible) {
                msgEl.textContent = '✔ Disponible'; msgEl.className = 'pc-field-msg success';
                delete _pcVerifErrors[campo];
            } else {
                msgEl.textContent = '✖ ' + res.mensaje; msgEl.className = 'pc-field-msg error';
                _pcVerifErrors[campo] = true;
            }
        } catch(e) {
            msgEl.textContent = ''; msgEl.className = 'pc-field-msg';
        }
    }, 600);
}

function pcToast(icon, title) {
    Swal.fire({ toast:true, position:'top-end', icon, title, showConfirmButton:false,
        timer:3500, timerProgressBar:true, customClass:{popup:'sgp-swal-toast'},
        didOpen: t => { t.addEventListener('mouseenter',Swal.stopTimer); t.addEventListener('mouseleave',Swal.resumeTimer); }
    });
}

// AJAX — Nuevo pasante
async function guardarNuevoPasante() {
    const nombres = document.getElementById('pc-nombres').value.trim();
    const apellidos = document.getElementById('pc-apellidos').value.trim();
    const cedula  = document.getElementById('pc-cedula').value.trim();
    const correo  = document.getElementById('pc-correo').value.trim();
    const fi      = document.getElementById('pc-fecha-inicio').value;
    if (!nombres||!apellidos||!cedula||!correo||!fi) { pcToast('warning','Completa los campos obligatorios.'); return; }
    if (Object.keys(_pcVerifErrors).length > 0) { pcToast('error','Corrige los campos marcados en rojo antes de continuar.'); return; }
    const body = { csrf_token:PC_CSRF, nombres, apellidos, cedula, correo,
        carrera: document.getElementById('pc-carrera').value.trim(),
        institucion_id: document.getElementById('pc-inst').value,
        tutor_id: document.getElementById('pc-tutor').value,
        departamento_id: document.getElementById('pc-depto').value,
        periodo_id: document.getElementById('pc-periodo')?.value ?? '',
        fecha_inicio: fi, horas_meta: document.getElementById('pc-horas').value,
        fecha_fin: document.getElementById('pc-fecha-fin').value };
    const r = await fetch(URLROOT+'/actividades/crearPasanteCorto',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':PC_CSRF},body:JSON.stringify(body)});
    const res = await r.json();
    if (res.success) {
        document.getElementById('tempPassVal').textContent = res.temp_password;
        document.getElementById('pinKioscoVal').textContent = res.pin_kiosco;
        const box = document.getElementById('tempPassBox');
        box.style.display='block'; box.scrollIntoView({behavior:'smooth'});
        pcToast('success', `${nombres} ${apellidos} registrado correctamente.`);
        setTimeout(()=>location.reload(), 4000);
    } else {
        pcToast('error', res.message||'Error al registrar el pasante.');
    }
}

// AJAX — Editar pasante
async function guardarEdicionPasante() {
    const nombres = document.getElementById('edit-nombres').value.trim();
    const apellidos = document.getElementById('edit-apellidos').value.trim();
    const body = { csrf_token:PC_CSRF, usuario_id: document.getElementById('edit-uid').value,
        nombres, apellidos,
        carrera: document.getElementById('edit-carrera').value.trim(),
        institucion_id: document.getElementById('edit-inst').value,
        tutor_id: document.getElementById('edit-tutor').value,
        departamento_id: document.getElementById('edit-depto').value,
        periodo_id: document.getElementById('edit-periodo').value,
        estado_pasantia: document.getElementById('edit-estado').value,
        fecha_inicio: document.getElementById('edit-fecha-inicio').value,
        horas_meta: document.getElementById('edit-horas').value };
    const r = await fetch(URLROOT+'/actividades/editarPasanteCorto',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':PC_CSRF},body:JSON.stringify(body)});
    const res = await r.json();
    if (res.success) {
        cerrarModalPC('modalEditarPasante');
        pcToast('success', `${nombres} ${apellidos} actualizado correctamente.`);
        setTimeout(()=>location.reload(), 1800);
    } else {
        pcToast('error', res.message||'Error al actualizar el pasante.');
    }
}

// Tabs asistencia
function cambiarTab(modo, btn) {
    tabActual = modo;
    document.querySelectorAll('.asist-tab').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    cargarAsistencia();
}

async function cargarAsistencia() {
    const pid   = document.getElementById('selPasanteAsist').value;
    const fecha = document.getElementById('inputFechaAsist').value || new Date().toISOString().split('T')[0];
    if (!pid) return;
    pasanteAsistId = pid;
    const sel = document.getElementById('selPasanteAsist');
    if (sel.options[sel.selectedIndex]) document.getElementById('asistNombre').textContent = sel.options[sel.selectedIndex].text;
    const r   = await fetch(`${URLROOT}/actividades/asistenciaCorto?pasante_id=${pid}&modo=${tabActual}&fecha=${fecha}`);
    const res = await r.json();
    if (!res.success) return;
    const cont = document.getElementById('asistContent');
    if (tabActual==='diaria') cont.innerHTML = renderDiaria(res);
    else if (tabActual==='semanal') cont.innerHTML = renderSemanal(res);
    else cont.innerHTML = renderMensual(res, fecha);
}

function estadoPill(e) {
    if (!e) return '<span class="estado-pill ep-nd">Sin registro</span>';
    const m = {Presente:'ep-p',Ausente:'ep-a',Justificado:'ep-j',Retardo:'ep-r'};
    return `<span class="estado-pill ${m[e]??'ep-nd'}">${e}</span>`;
}
function renderDiaria(res) {
    const dias=['Lunes','Martes','Miércoles','Jueves','Viernes'];
    const inicio=new Date(res.inicio+'T00:00:00');
    let html='<table class="asist-day-table"><thead><tr>'+dias.map(d=>`<th>${d}</th>`).join('')+'</tr></thead><tbody><tr>';
    for(let i=0;i<5;i++){
        const d=new Date(inicio);d.setDate(d.getDate()+i);
        const fStr=d.toISOString().split('T')[0],reg=res.registros[fStr];
        html+=`<td><div style="font-size:.72rem;color:#94a3b8;margin-bottom:5px;">${d.getDate()}/${d.getMonth()+1}</div>${estadoPill(reg?.estado)}${reg?.hora?`<div style="font-size:.7rem;color:#94a3b8;margin-top:3px;">${reg.hora}</div>`:''}</td>`;
    }
    return html+'</tr></tbody></table>';
}
function renderSemanal(res) {
    const inicio=new Date(res.inicio+'T00:00:00'),fin=new Date(res.fin+'T00:00:00');
    let stats={Presente:0,Ausente:0,Justificado:0,Retardo:0,sinReg:0},rows='',cur=new Date(inicio);
    while(cur<=fin){
        const fStr=cur.toISOString().split('T')[0],dn=cur.getDay();
        if(dn>=1&&dn<=5){const reg=res.registros[fStr];if(reg)stats[reg.estado]=(stats[reg.estado]||0)+1;else stats.sinReg++;
        rows+=`<tr><td>${cur.toLocaleDateString('es-VE',{weekday:'long',day:'2-digit',month:'2-digit'})}</td><td>${estadoPill(reg?.estado)}</td><td style="font-size:.8rem;color:#64748b;">${reg?.hora??'—'}</td><td style="font-size:.8rem;color:#94a3b8;">${reg?.metodo??'—'}</td></tr>`;}
        cur.setDate(cur.getDate()+1);
    }
    return `<table class="asist-day-table"><thead><tr><th style="text-align:left;">Día</th><th>Estado</th><th>Hora</th><th>Método</th></tr></thead><tbody>${rows}</tbody></table>
    <div style="display:flex;gap:10px;margin-top:14px;flex-wrap:wrap;">${Object.entries(stats).map(([k,v])=>`<div style="background:#f8fafc;padding:7px 14px;border-radius:10px;font-size:.8rem;"><strong>${v}</strong> <span style="color:#94a3b8;">${k}</span></div>`).join('')}</div>`;
}
function renderMensual(res,fecha){
    const [y,m]=fecha.split('-').map(Number),primerDia=new Date(y,m-1,1),ultimoDia=new Date(y,m,0).getDate();
    const inicioSem=(primerDia.getDay()+6)%7,dias=['Lu','Ma','Mi','Ju','Vi','Sa','Do'];
    let html='<div class="cal-grid-wrap">'+dias.map(d=>`<div class="cal-day-hdr">${d}</div>`).join('');
    for(let i=0;i<inicioSem;i++)html+='<div class="cal-cell empty"></div>';
    for(let d=1;d<=ultimoDia;d++){
        const fStr=`${y}-${String(m).padStart(2,'0')}-${String(d).padStart(2,'0')}`,dn=new Date(fStr+'T00:00:00').getDay();
        const esFinde=dn===0||dn===6,reg=res.registros[fStr];
        const cls=esFinde?'finde':(reg?({Presente:'cp',Ausente:'ca',Justificado:'cj',Retardo:'cr'}[reg.estado]??'sin'):'sin');
        html+=`<div class="cal-cell ${cls}" title="${fStr}${reg?' · '+reg.estado:''}">${d}</div>`;
    }
    return html+'</div>';
}

async function enviarMarcarAsistencia() {
    const pid=document.getElementById('ma-pid').value||document.getElementById('ma-sel').value;
    if(!pid){pcToast('warning','Selecciona un pasante.');return;}
    const body={csrf_token:PC_CSRF,pasante_id:pid,fecha:document.getElementById('ma-fecha').value,
        estado:document.getElementById('ma-estado').value,motivo:document.getElementById('ma-motivo').value.trim()};
    const r=await fetch(URLROOT+'/actividades/marcarAsistenciaCorto',{method:'POST',headers:{'Content-Type':'application/json','X-CSRF-TOKEN':PC_CSRF},body:JSON.stringify(body)});
    const res=await r.json();
    if(res.success){
        cerrarModalPC('modalMarcarAsist');
        document.getElementById('ma-motivo').value='';
        pcToast('success','Asistencia registrada correctamente.');
        if(pasanteAsistId==pid)cargarAsistencia();
    } else {
        pcToast('error', res.message||'Error al registrar la asistencia.');
    }
}

// Escape / overlay
document.addEventListener('keydown',e=>{if(e.key==='Escape')document.querySelectorAll('.pc-modal-overlay.active').forEach(m=>m.classList.remove('active'));});
document.querySelectorAll('.pc-modal-overlay').forEach(m=>m.addEventListener('click',e=>{if(e.target===m)m.classList.remove('active');}));
document.addEventListener('DOMContentLoaded', pcRecalcular);
</script>
