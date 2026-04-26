<?php
$act           = $data['actividad'];
$participantes = $data['participantes']  ?? [];
$asistenciasMap= $data['asistenciasMap'] ?? [];
$stats         = $data['statsAsistencia']?? [];
$diasActiva    = $data['diasActiva']     ?? 0;
$pctAsistencia = $data['pctAsistencia']  ?? 0;
$instituciones = $data['instituciones']  ?? [];
$supervisores  = $data['supervisores']   ?? [];
$esPasantia    = ($act->tipo ?? '') === 'Pasantía Corta';
?>
<style>
/* Bento Grid & Premium UI - Actividades Ver */
:root {
    --navy-grad: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
    --glass-bg: rgba(255, 255, 255, 0.15);
}

/* Flash Messages */
.flash-msg {
    padding: 12px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px; font-weight: 500;
    animation: flashSlideIn 0.4s cubic-bezier(0.16, 1, 0.3, 1);
}
.flash-msg i { font-size: 1.2rem; }
.success-msg { background: #ecfdf5; color: #065f46; border: 1px solid #10b981; }
.error-msg { background: #fef2f2; color: #991b1b; border: 1px solid #ef4444; }

@keyframes flashSlideIn { from { opacity: 0; transform: translateY(-10px); } to { opacity: 1; transform: translateY(0); } }

/* Banner Premium - Redesigned */
.detail-banner {
    background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 45%, #2563eb 100%);
    border-radius: 22px;
    padding: 0;
    color: white;
    margin-bottom: 22px;
    box-shadow: 0 16px 48px rgba(30, 58, 138, 0.28);
    position: relative;
    overflow: hidden;
}
.detail-banner::before {
    content: '';
    position: absolute; top: -80px; right: -60px;
    width: 380px; height: 380px;
    background: radial-gradient(circle, rgba(255,255,255,0.08) 0%, transparent 65%);
    border-radius: 50%; pointer-events: none;
}
.detail-banner::after {
    content: '';
    position: absolute; bottom: -60px; left: 30px;
    width: 250px; height: 250px;
    background: radial-gradient(circle, rgba(96,165,250,0.1) 0%, transparent 65%);
    border-radius: 50%; pointer-events: none;
}
.banner-inner {
    padding: 28px 36px 24px;
    position: relative; z-index: 1;
    display: flex; align-items: flex-start; gap: 22px;
}
.banner-icon-wrap {
    width: 68px; height: 68px; flex-shrink: 0;
    background: rgba(255,255,255,0.12);
    border: 2px solid rgba(255,255,255,0.2);
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    backdrop-filter: blur(10px);
    font-size: 1.9rem; color: white;
}
.banner-content { flex: 1; min-width: 0; }
.banner-back {
    display: inline-flex; align-items: center; gap: 5px;
    color: rgba(255,255,255,0.6); text-decoration: none;
    font-size: 0.78rem; font-weight: 600; margin-bottom: 10px;
    transition: color 0.2s; letter-spacing: 0.3px;
}
.banner-back:hover { color: rgba(255,255,255,0.95); }
.banner-title {
    font-size: 1.85rem; font-weight: 900; color: #ffffff;
    margin: 0 0 12px; line-height: 1.15;
    letter-spacing: -0.5px;
    text-shadow: 0 2px 8px rgba(0,0,0,0.2);
}
.banner-pills { display: flex; align-items: center; gap: 8px; flex-wrap: wrap; margin-bottom: 18px; }
.b-pill {
    display: inline-flex; align-items: center; gap: 5px;
    padding: 5px 13px; border-radius: 20px;
    font-size: 0.75rem; font-weight: 700; letter-spacing: 0.2px;
}
.b-pill-tipo   { background: rgba(255,255,255,0.15); border: 1px solid rgba(255,255,255,0.25); color: rgba(255,255,255,0.95); }
.b-pill-activa { background: rgba(16,185,129,0.25);  border: 1px solid rgba(16,185,129,0.5);  color: #6ee7b7; }
.b-pill-fin    { background: rgba(100,116,139,0.25);  border: 1px solid rgba(100,116,139,0.5);  color: #cbd5e1; }
.b-pill-cancel { background: rgba(239,68,68,0.25);    border: 1px solid rgba(239,68,68,0.5);    color: #fca5a5; }
.pulsing-dot   { width: 7px; height: 7px; background: #34d399; border-radius: 50%; display: inline-block; animation: pulse-dot-w 1.5s infinite; }
@keyframes pulse-dot-w { 0%,100%{transform:scale(1);opacity:1;} 50%{transform:scale(1.5);opacity:0.8;} }

.banner-meta-grid {
    display: flex; flex-wrap: wrap; gap: 8px 20px;
}
.banner-meta-item {
    display: flex; align-items: center; gap: 7px;
    color: rgba(255,255,255,0.75); font-size: 0.82rem; font-weight: 500;
    background: rgba(255,255,255,0.07);
    border: 1px solid rgba(255,255,255,0.12);
    border-radius: 10px; padding: 5px 12px;
}
.banner-meta-item i { font-size: 0.95rem; color: rgba(255,255,255,0.5); }

/* Progress strip at bottom of banner */
.banner-progress-strip {
    padding: 12px 36px;
    background: rgba(0,0,0,0.2);
    display: flex; align-items: center; gap: 16px;
    border-top: 1px solid rgba(255,255,255,0.08);
    position: relative; z-index: 1;
}
.bp-label  { font-size: 0.7rem; color: rgba(255,255,255,0.55); font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; white-space: nowrap; }
.bp-bar    { flex: 1; height: 5px; background: rgba(255,255,255,0.12); border-radius: 10px; overflow: hidden; }
.bp-fill   { height: 100%; background: linear-gradient(90deg, #34d399, #60a5fa); border-radius: 10px; transition: width 1s ease; }
.bp-pct    { font-size: 0.72rem; font-weight: 800; color: #93c5fd; white-space: nowrap; }

/* KPI Row redesigned */
.kpi-row-mini { display: grid; grid-template-columns: repeat(4, 1fr); gap: 14px; margin-bottom: 22px; }
.kpi-mini {
    background: white; border-radius: 16px; padding: 18px 20px;
    display: flex; align-items: center; gap: 16px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.04);
    border: 1px solid #f1f5f9;
    transition: transform 0.2s, box-shadow 0.2s;
}
.kpi-mini:hover { transform: translateY(-3px); box-shadow: 0 8px 24px rgba(0,0,0,0.07); }
.kpi-mini .km-icon {
    width: 48px; height: 48px; border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem; flex-shrink: 0;
}
.kpi-mini .val { font-size: 1.65rem; font-weight: 900; color: #0f172a; line-height: 1; margin-bottom: 3px; }
.kpi-mini .lbl { font-size: 0.7rem; color: #94a3b8; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }

.km-part .km-icon { background: #eff6ff; color: #2563eb; }
.km-days .km-icon { background: #fff7ed; color: #f59e0b; }
.km-pct  .km-icon { background: #f0fdf4; color: #10b981; }
.km-stat .km-icon { background: #f8fafc; color: #64748b; }

.breadcrumb-custom, .d-title, .d-badges, .d-meta { display: none; } /* legacy hidden */

@media(max-width: 992px) {
    .kpi-row-mini { grid-template-columns: repeat(2, 1fr); }
    .banner-title { font-size: 1.5rem; }
    .banner-inner { padding: 20px 20px 16px; }
    .banner-progress-strip { padding: 10px 20px; }
}
@media(max-width: 768px) {
    .kpi-row-mini { grid-template-columns: 1fr; }
    .banner-icon-wrap { width: 52px; height: 52px; font-size: 1.4rem; }
}

/* Tabs Premium */
.nav-tabs { border-bottom: 2px solid #e2e8f0; gap: 4px; }
.nav-tabs .nav-link { font-weight: 600; color: #64748b; border-radius: 12px 12px 0 0; padding: 12px 24px; border: none; transition: all 0.2s; }
.nav-tabs .nav-link:hover { color: #1e293b; background: rgba(241, 245, 249, 0.5); }
.nav-tabs .nav-link.active { color: #2563eb; background: #fff; border-bottom: 2px solid #2563eb; }
.nav-tabs .badge-count { background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 10px; font-size: 0.75rem; margin-left: 6px; }
.nav-tabs .nav-link.active .badge-count { background: rgba(37, 99, 235, 0.1); color: #2563eb; }

.tab-content { background: #fff; border: 1px solid #e2e8f0; border-top: none; border-radius: 0 0 16px 16px; padding: 28px; box-shadow: 0 10px 15px -3px rgba(0,0,0,0.02); }

/* Participantes Grid */
.part-grid { display: grid; grid-template-columns: repeat(2, 1fr); gap: 16px; }
.part-card { display: flex; align-items: center; gap: 16px; padding: 16px; border: 1px solid #e2e8f0; border-radius: 16px; transition: all 0.2s; background:#fafafa;}
.part-card:hover { border-color: #cbd5e1; transform: translateY(-2px); box-shadow: 0 4px 6px -1px rgba(0,0,0,0.05); }
.pc-avatar { width: 44px; height: 44px; border-radius: 50%; background: var(--navy-grad); color: white; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 1rem; flex-shrink: 0; }
.pc-info { flex-grow: 1; min-width: 0; }
.pc-name { margin: 0; font-weight: 700; font-size: 1rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.pc-cedula { margin: 2px 0 0; font-size: 0.85rem; color: #64748b; display: flex; gap:10px; }
.pc-obs-badge { background: #fef3c7; color: #d97706; padding: 2px 8px; border-radius: 10px; font-size: 0.7rem; font-weight: 600; display: inline-block; margin-top: 4px; }

/* Asistencia Styles */
.asis-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #e2e8f0; }
.date-selector { display: flex; align-items: center; gap: 12px; background: #f8fafc; padding: 8px 16px; border-radius: 12px; border: 1px solid #e2e8f0; }
.date-selector label { font-weight: 600; color: #475569; margin:0; }
.date-selector input { border: none; background: transparent; font-weight: 600; color: #1e293b; outline: none; }
.loader-spinner { display: none; width: 24px; height: 24px; border: 3px solid #e2e8f0; border-top: 3px solid #2563eb; border-radius: 50%; animation: spin 1s linear infinite; }
@keyframes spin { 0% { transform: rotate(0deg); } 100% { transform: rotate(360deg); } }

.asis-list { display: flex; flex-direction: column; gap: 12px; }
.asis-row { display: flex; justify-content: space-between; align-items: center; padding: 12px 16px; border: 1px solid #f1f5f9; border-radius: 12px; background: #fff; transition: background 0.2s; }
.asis-row:hover { background: #f8fafc; }
.asis-user { display: flex; align-items: center; gap: 12px; }
.asis-avatar { width: 36px; height: 36px; border-radius: 50%; background: #e2e8f0; color: #475569; display: flex; align-items: center; justify-content: center; font-weight: 700; font-size: 0.85rem; }
.asis-name { font-weight: 600; color: #334155; }
.asis-actions { display: flex; gap: 8px; }
.btn-est { width: 40px; height: 40px; border-radius: 10px; border: 2px solid #e2e8f0; background: white; font-weight: 700; color: #94a3b8; transition: all 0.2s; display:flex; align-items:center; justify-content:center; padding:0; cursor:pointer;}
.btn-est:hover { background: #f8fafc; border-color: #cbd5e1; }
.btn-est.active[data-est="Presente"]    { background: #ecfdf5; border-color: #10b981; color: #10b981; }
/* A (Ausente) */
.btn-est.active[data-est="Ausente"]     { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
/* J (Justificado) */
.btn-est.active[data-est="Justificado"] { background: #fffbeb; border-color: #f59e0b; color: #f59e0b; }

/* Modal & Forms */
.form-label { font-weight: 600; color: #334155; }
.form-control, .form-select { border-radius: 10px; border: 1px solid #e2e8f0; padding: 10px 12px; background: #f8fafc; }
.form-control:focus, .form-select:focus { border-color: #2563eb; box-shadow: 0 0 0 3px rgba(37,99,235,0.1); background: #fff; }
.btn-action { border-radius: 10px; font-weight: 600; padding: 10px 20px; display: inline-flex; align-items: center; gap: 8px; }

.modal-content { border-radius: 20px; border: none; overflow: hidden; }
.modal-header.premium { background: var(--navy-grad); color: white; border: none; padding: 24px; }
.modal-header.premium .btn-close { filter: invert(1) grayscale(100%) brightness(200%); }

@media(max-width: 992px) {
    .kpi-row-mini { grid-template-columns: repeat(2, 1fr); }
    .part-grid { grid-template-columns: 1fr; }
}
@media(max-width: 768px) {
    .detail-banner { padding: 24px; }
    .d-title { font-size: 1.75rem; }
    .kpi-row-mini { grid-template-columns: 1fr; }
    .asis-row { flex-direction: column; align-items: flex-start; gap: 12px; }
    .asis-actions { width: 100%; justify-content: space-between; }
    .btn-est { flex-grow: 1; }
    .asis-header { flex-direction: column; align-items: stretch; gap: 12px; }
}
</style>

<div class="container-fluid py-4">

    <?php if ($msg = Session::getFlash('success')): ?>
        <div class="flash-msg success-msg"><i class="ti ti-check"></i> <?php echo $msg; ?></div>
    <?php endif; ?>
    <?php if ($msg = Session::getFlash('error')): ?>
        <div class="flash-msg error-msg"><i class="ti ti-alert-circle"></i> <?php echo $msg; ?></div>
    <?php endif; ?>

    <!-- Banner Premium Rediseñado -->
    <?php
        $tipoIcono = match($act->tipo ?? '') {
            'Pasantía Corta'       => 'ti-user-check',
            'Servicio Comunitario' => 'ti-users-group',
            'Mantenimiento'        => 'ti-tool',
            default                => 'ti-briefcase',
        };
        $estadoPill = match($act->estado ?? '') {
            'Activa'     => ['cls'=>'b-pill-activa', 'dot'=>true,  'label'=>'Activa'],
            'Finalizada' => ['cls'=>'b-pill-fin',    'dot'=>false, 'label'=>'Finalizada'],
            'Cancelada'  => ['cls'=>'b-pill-cancel', 'dot'=>false, 'label'=>'Cancelada'],
            default      => ['cls'=>'b-pill-fin',    'dot'=>false, 'label'=>$act->estado],
        };
        // Progreso de días
        $fi  = new DateTime($act->fecha_inicio);
        $ff  = $act->fecha_fin ? new DateTime($act->fecha_fin) : null;
        $hoy = new DateTime();
        $totalDias   = $ff ? max(1, (int)$fi->diff($ff)->days) : 0;
        $diasPasados = max(0, min($totalDias, (int)$fi->diff($hoy)->days));
        $pctDias     = $totalDias > 0 ? min(100, round($diasPasados / $totalDias * 100)) : 0;
        $diasRestantes = $ff ? max(0, (int)$hoy->diff($ff)->days) : null;
    ?>
    <div class="detail-banner">
        <div class="banner-inner">
            <!-- Ícono tipo actividad -->
            <div class="banner-icon-wrap">
                <i class="ti <?= $tipoIcono ?>" style="font-size:1.8rem;"></i>
            </div>

            <div class="banner-content">
                <!-- Breadcrumb -->
                <a href="<?= URLROOT ?>/actividades" class="banner-back">
                    <i class="ti ti-arrow-left"></i> Volver a Actividades
                </a>

                <!-- Título -->
                <h1 class="banner-title"><?= htmlspecialchars($act->nombre) ?></h1>

                <!-- Pills tipo + estado -->
                <div class="banner-pills">
                    <span class="b-pill b-pill-tipo">
                        <i class="ti ti-tag"></i> <?= htmlspecialchars($act->tipo) ?>
                    </span>
                    <span class="b-pill <?= $estadoPill['cls'] ?>">
                        <?php if($estadoPill['dot']): ?><span class="pulsing-dot"></span><?php endif; ?>
                        <?= $estadoPill['label'] ?>
                    </span>
                    <?php if($diasRestantes !== null && $act->estado === 'Activa'): ?>
                    <span class="b-pill" style="background:rgba(251,191,36,0.2);border:1px solid rgba(251,191,36,0.4);color:#fde68a;">
                        <i class="ti ti-hourglass"></i> <?= $diasRestantes ?> días restantes
                    </span>
                    <?php endif; ?>
                </div>

                <!-- Meta info chips -->
                <div class="banner-meta-grid">
                    <?php if(!empty($act->institucion_nombre)): ?>
                    <div class="banner-meta-item">
                        <i class="ti ti-building"></i>
                        <?= htmlspecialchars($act->institucion_nombre) ?>
                    </div>
                    <?php endif; ?>
                    <?php
                        $supNombre = trim($act->supervisor_nombre ?? '');
                        if(!empty($supNombre) && $supNombre !== ' '):
                    ?>
                    <div class="banner-meta-item">
                        <i class="ti ti-user-check"></i>
                        <?= htmlspecialchars($supNombre) ?>
                    </div>
                    <?php endif; ?>
                    <div class="banner-meta-item">
                        <i class="ti ti-calendar"></i>
                        <?= date('d/m/Y', strtotime($act->fecha_inicio)) ?>
                        <?php if($act->fecha_fin): ?>
                        → <?= date('d/m/Y', strtotime($act->fecha_fin)) ?>
                        <?php else: ?>
                        <span style="color:rgba(255,255,255,0.45);">(En curso)</span>
                        <?php endif; ?>
                    </div>
                    <div class="banner-meta-item">
                        <i class="ti ti-users"></i>
                        <?= count($participantes) ?> participante<?= count($participantes)!=1?'s':'' ?>
                    </div>
                </div>
            </div>
        </div>

        <?php if($totalDias > 0): ?>
        <!-- Barra de progreso de días -->
        <div class="banner-progress-strip">
            <span class="bp-label">Avance</span>
            <div class="bp-bar">
                <div class="bp-fill" style="width:<?= $pctDias ?>%;"></div>
            </div>
            <span class="bp-pct"><?= $diasPasados ?> / <?= $totalDias ?> días (<?= $pctDias ?>%)</span>
        </div>
        <?php endif; ?>
    </div>

    <!-- Mini KPIs -->
    <div class="kpi-row-mini">
        <div class="kpi-mini km-part">
            <div class="km-icon"><i class="ti ti-users"></i></div>
            <div>
                <div class="val"><?= count($participantes) ?></div>
                <div class="lbl">Participantes</div>
            </div>
        </div>
        <div class="kpi-mini km-days">
            <div class="km-icon"><i class="ti ti-calendar-stats"></i></div>
            <div>
                <div class="val"><?= $diasActiva ?></div>
                <div class="lbl">Días de Actividad</div>
            </div>
        </div>
        <div class="kpi-mini km-pct">
            <div class="km-icon"><i class="ti ti-chart-pie"></i></div>
            <div>
                <div class="val"><?= $pctAsistencia ?>%</div>
                <div class="lbl">Asistencia Global</div>
            </div>
        </div>
        <div class="kpi-mini km-stat">
            <div class="km-icon"><i class="ti ti-info-circle"></i></div>
            <div>
                <div class="val" style="font-size:1.05rem;margin-top:2px;"><?= htmlspecialchars($act->estado) ?></div>
                <div class="lbl">Estado Actual</div>
            </div>
        </div>
    </div>

    <!-- Tabs Bootstrap -->
    <ul class="nav nav-tabs" id="actTabs" role="tablist">
        <li class="nav-item" role="presentation">
            <button class="nav-link active" data-bs-toggle="tab" data-bs-target="#tab-participantes" type="button">
                Participantes <span class="badge-count"><?php echo count($participantes); ?></span>
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-asistencia" type="button">
                Control de Asistencia
            </button>
        </li>
        <li class="nav-item" role="presentation">
            <button class="nav-link" data-bs-toggle="tab" data-bs-target="#tab-info" type="button">
                Información y Edición
            </button>
        </li>
    </ul>

    <div class="tab-content">
        <!-- Tab Participantes -->
        <div class="tab-pane fade show active" id="tab-participantes">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h4 class="m-0" style="font-weight:700; color:#1e293b;">Lista de Participantes</h4>
                <button class="btn btn-primary btn-action" style="background:var(--navy-grad); border:none;" data-bs-toggle="modal" data-bs-target="#modalAddParticipante">
                    <i class="ti ti-user-plus"></i> Añadir Participante
                </button>
            </div>
            
            <?php if(empty($participantes)): ?>
                <div class="text-center py-5">
                    <i class="ti ti-users" style="font-size:3rem; color:#cbd5e1; margin-bottom:10px; display:block;"></i>
                    <p style="color:#64748b; font-weight:500;">No hay participantes registrados aún.</p>
                </div>
            <?php else: ?>
                <div class="part-grid">
                    <?php foreach($participantes as $p): 
                        $ini = mb_strtoupper(mb_substr($p->nombres, 0, 1) . mb_substr($p->apellidos, 0, 1));
                    ?>
                    <div class="part-card">
                        <div class="pc-avatar"><?= $ini ?></div>
                        <div class="pc-info">
                            <h5 class="pc-name" title="<?= htmlspecialchars($p->nombres . ' ' . $p->apellidos) ?>">
                                <?= htmlspecialchars($p->nombres . ' ' . $p->apellidos) ?>
                            </h5>
                            <p class="pc-cedula">
                                <span><i class="ti ti-id"></i> <?= htmlspecialchars($p->cedula) ?></span>
                                <?php if(!empty($p->carrera)): ?>
                                <span><i class="ti ti-book"></i> <?= htmlspecialchars($p->carrera) ?></span>
                                <?php endif; ?>
                            </p>
                            <?php if(!empty($p->observaciones)): ?>
                                <span class="pc-obs-badge"><i class="ti ti-message"></i> Obs.</span>
                            <?php endif; ?>
                        </div>
                        <?php if ($esPasantia): ?>
                        <a href="<?= URLROOT ?>/actividades/participante/<?= $p->id ?>"
                           title="Ver asistencia individual"
                           style="display:inline-flex;align-items:center;gap:5px;padding:6px 12px;border-radius:9px;
                                  background:linear-gradient(135deg,#172554,#2563eb);color:white;
                                  font-size:.75rem;font-weight:700;text-decoration:none;flex-shrink:0;
                                  transition:transform .2s,box-shadow .2s;"
                           onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 10px rgba(37,99,235,.3)'"
                           onmouseout="this.style.transform='none';this.style.boxShadow='none'">
                            <i class="ti ti-calendar-stats" style="font-size:.9rem;"></i> Asistencia
                        </a>
                        <?php endif; ?>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Asistencia -->
        <div class="tab-pane fade" id="tab-asistencia">
            <div class="asis-header">
                <div>
                    <h4 class="m-0" style="font-weight:700; color:#1e293b;">Control de Asistencia</h4>
                    <p class="m-0" style="color:#64748b; font-size:0.9rem;">Seleccione la fecha y marque el estado de cada participante.</p>
                </div>
                <div class="date-selector">
                    <label for="asisDate"><i class="ti ti-calendar"></i> Fecha:</label>
                    <input type="date" id="asisDate" value="<?php echo date('Y-m-d'); ?>">
                    <div class="loader-spinner" id="asisLoader"></div>
                </div>
            </div>

            <?php if(empty($participantes)): ?>
                <div class="text-center py-5">
                    <p style="color:#64748b; font-weight:500;">Debe agregar participantes para llevar el control de asistencia.</p>
                </div>
            <?php else: ?>
                <div class="asis-list">
                    <?php foreach($participantes as $p): 
                        $ini = mb_strtoupper(mb_substr($p->nombres, 0, 1) . mb_substr($p->apellidos, 0, 1));
                    ?>
                    <div class="asis-row">
                        <div class="asis-user">
                            <div class="asis-avatar"><?= $ini ?></div>
                            <div>
                                <div class="asis-name"><?= htmlspecialchars($p->nombres . ' ' . $p->apellidos) ?></div>
                                <?php if($esPasantia): ?>
                                <a href="<?= URLROOT ?>/actividades/participante/<?= $p->id ?>"
                                   style="font-size:.7rem;color:#2563eb;font-weight:600;text-decoration:none;display:inline-flex;align-items:center;gap:3px;margin-top:2px;"
                                   title="Ver historial individual">
                                    <i class="ti ti-calendar-week" style="font-size:.75rem;"></i> Ver historial
                                </a>
                                <?php endif; ?>
                            </div>
                        </div>
                        <div class="asis-actions">
                            <button class="btn-est" title="Presente"    onclick="registrarAsistencia(<?= $p->id ?>, 'Presente', this)"    data-est="Presente">P</button>
                            <button class="btn-est" title="Ausente"     onclick="registrarAsistencia(<?= $p->id ?>, 'Ausente', this)"     data-est="Ausente">A</button>
                            <button class="btn-est" title="Justificado" onclick="registrarAsistencia(<?= $p->id ?>, 'Justificado', this)" data-est="Justificado">J</button>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>

        <!-- Tab Info/Editar -->
        <div class="tab-pane fade" id="tab-info">
            <h4 style="font-weight:700; color:#1e293b; margin-bottom:24px;">Editar Información</h4>
            <form action="<?php echo URLROOT; ?>/actividades/editar" method="POST">
                <input type="hidden" name="csrf_token" value="<?php echo Session::generateCsrfToken(); ?>">
                <input type="hidden" name="id" value="<?php echo $act->id; ?>">
                
                <div class="row g-4">
                    <div class="col-md-12">
                        <label class="form-label">Nombre de la Actividad</label>
                        <input type="text" name="nombre" class="form-control" required value="<?php echo htmlspecialchars($act->nombre); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Tipo</label>
                        <select name="tipo" class="form-select" required>
                            <option value="Servicio Comunitario" <?php echo $act->tipo=='Servicio Comunitario'?'selected':''; ?>>Servicio Comunitario</option>
                            <option value="Pasantía Corta" <?php echo $act->tipo=='Pasantía Corta'?'selected':''; ?>>Pasantía Corta</option>
                            <option value="Mantenimiento" <?php echo $act->tipo=='Mantenimiento'?'selected':''; ?>>Mantenimiento</option>
                            <option value="Otro" <?php echo $act->tipo=='Otro'?'selected':''; ?>>Otro</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Estado</label>
                        <select name="estado" class="form-select" required>
                            <option value="Activa" <?php echo $act->estado=='Activa'?'selected':''; ?>>Activa</option>
                            <option value="Finalizada" <?php echo $act->estado=='Finalizada'?'selected':''; ?>>Finalizada</option>
                            <option value="Cancelada" <?php echo $act->estado=='Cancelada'?'selected':''; ?>>Cancelada</option>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Institución</label>
                        <select name="institucion_id" class="form-select">
                            <option value="">Ninguna</option>
                            <?php foreach($instituciones as $inst): ?>
                                <option value="<?php echo $inst->id; ?>" <?php echo $act->institucion_id==$inst->id?'selected':''; ?>><?php echo htmlspecialchars($inst->nombre); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Supervisor</label>
                        <select name="supervisor_id" class="form-select">
                            <option value="">Ninguno</option>
                            <?php foreach($supervisores as $sup): ?>
                                <option value="<?php echo $sup->id; ?>" <?php echo $act->supervisor_id==$sup->id?'selected':''; ?>><?php echo htmlspecialchars($sup->nombre_completo); ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha Inicio</label>
                        <input type="date" name="fecha_inicio" class="form-control" required value="<?php echo date('Y-m-d', strtotime($act->fecha_inicio)); ?>">
                    </div>
                    <div class="col-md-6">
                        <label class="form-label">Fecha Fin</label>
                        <input type="date" name="fecha_fin" class="form-control" value="<?php echo $act->fecha_fin ? date('Y-m-d', strtotime($act->fecha_fin)) : ''; ?>">
                    </div>
                    <div class="col-md-12">
                        <label class="form-label">Descripción</label>
                        <textarea name="descripcion" class="form-control" rows="4"><?php echo htmlspecialchars($act->descripcion ?? ''); ?></textarea>
                    </div>
                    <div class="col-12 mt-4 text-end">
                        <button type="submit" class="btn btn-primary btn-action" style="background:var(--navy-grad); border:none;">
                            <i class="ti ti-device-floppy"></i> Guardar Cambios
                        </button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- Modal Agregar Participante (Premium Bento UI) -->
<div class="modal fade" id="modalAddParticipante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header premium">
                <div style="display:flex;align-items:center;gap:12px;">
                    <div style="width:42px;height:42px;border-radius:12px;background:rgba(255,255,255,0.15);display:flex;align-items:center;justify-content:center;font-size:1.4rem;backdrop-filter:blur(5px);border:1px solid rgba(255,255,255,0.2);">
                        <i class="ti ti-link"></i>
                    </div>
                    <div>
                        <h5 class="modal-title m-0" style="font-weight:800;letter-spacing:-0.4px;">Nueva Asignación</h5>
                        <div style="font-size:0.75rem;color:rgba(255,255,255,0.7);font-weight:600;margin-top:2px;">Vincular pasante a esta actividad</div>
                    </div>
                </div>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close" onclick="if(typeof $ !== 'undefined') $('#modalAddParticipante').modal('hide');"></button>
            </div>
            
            <form id="formAddParticipante">
                <div class="modal-body" style="padding:24px;">
                    <div style="margin-bottom: 24px;">
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 12px; font-size: 0.9rem;">
                            <i class="ti ti-user-plus" style="margin-right: 6px;"></i>Datos del Pasante a Vincular *
                        </label>
                        
                        <div style="display:flex; flex-direction:column; gap:12px; background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:16px;">
                            <div>
                                <label style="display:block; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">Cédula *</label>
                                <input type="text" name="cedula" id="cedulaPart" class="form-control" placeholder="Ej: 27123456" required autocomplete="off">
                            </div>
                            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px;">
                                <div>
                                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">Nombres *</label>
                                    <input type="text" name="nombres" id="nombresPart" class="form-control" placeholder="Ej: Juan" required autocomplete="off">
                                </div>
                                <div>
                                    <label style="display:block; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">Apellidos *</label>
                                    <input type="text" name="apellidos" id="apellidosPart" class="form-control" placeholder="Ej: Perez" required autocomplete="off">
                                </div>
                            </div>
                            <div>
                                <label style="display:block; font-size:0.75rem; font-weight:700; color:#64748b; margin-bottom:4px;">Carrera / Especialidad</label>
                                <input type="text" name="carrera" id="carreraPart" class="form-control" placeholder="Ej: Ing. de Sistemas" autocomplete="off">
                            </div>
                        </div>
                    </div>

                    <!-- Campos Adicionales (Teléfono y Observaciones) -->
                    <div style="display:grid; grid-template-columns:1fr 1fr; gap:16px;">
                        <div class="f-group">
                            <label class="form-label" style="font-size:0.85rem;">Teléfono Contácto</label>
                            <input type="text" name="telefono" class="form-control" placeholder="Ej: 0414-0000000">
                        </div>
                        <div class="f-group" style="grid-column:1 / span 2;">
                            <label class="form-label" style="font-size:0.85rem;">Observaciones (Botón Mágico de Auto-Relleno)</label>
                            <textarea name="observaciones" id="txtObservaciones" class="form-control" rows="2" placeholder="Notas sobre el participante..."></textarea>
                        </div>
                    </div>
                </div>
                <!-- Action bar idéntica a modal_asignacion -->
                <div style="background:#f8fafc; padding:20px 24px; border-top:1px solid #e2e8f0; display:flex; gap:12px;">
                    <button type="button" data-bs-dismiss="modal" style="flex: 1; padding: 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: white; color: #64748b; font-weight: 600; cursor: pointer; font-size: 0.9rem; transition: all 0.2s;">
                        Cancelar
                    </button>
                    <button type="submit" id="btnGuardarPart" style="flex: 2; padding: 14px; background: var(--navy-grad); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.95rem; transition: all 0.2s;">
                        <i class="ti ti-check"></i> Confirmar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
(function(){
    const URLROOT = '<?php echo URLROOT; ?>';
    const actividadId = <?php echo $act->id; ?>;
    const csrfToken = document.querySelector('meta[name="csrf-token"]')?.getAttribute('content') || '';
    
    // UI Elements
    const asisDateInput = document.getElementById('asisDate');
    const asisLoader = document.getElementById('asisLoader');
    
    // Events
    if(asisDateInput) {
        asisDateInput.addEventListener('change', (e) => cargarAsistencias(e.target.value));
        // Init load
        cargarAsistencias(asisDateInput.value);
    }
    
    // Function to load assistances
    window.cargarAsistencias = function(fecha) {
        asisLoader.style.display = 'block';
        fetch(`${URLROOT}/actividades/asistenciasFecha?actividad_id=${actividadId}&fecha=${fecha}`)
            .then(res => res.json())
            .then(res => {
                asisLoader.style.display = 'none';
                if(res.success && res.data) {
                    actualizarBotonesAsistencia(res.data);
                } else {
                    actualizarBotonesAsistencia({}); // Limpiar si no hay datos
                }
            })
            .catch(err => {
                asisLoader.style.display = 'none';
                console.error(err);
                if(typeof NotificationService !== 'undefined') NotificationService.error('Error al cargar asistencias');
            });
    };
    
    window.actualizarBotonesAsistencia = function(dataMap) {
        document.querySelectorAll('.asis-row').forEach(row => {
            const btns = row.querySelectorAll('.btn-est');
            btns.forEach(b => b.classList.remove('active'));

            const exampleBtn = row.querySelector('.btn-est');
            if (exampleBtn) {
                const match = exampleBtn.getAttribute('onclick').match(/registrarAsistencia\((\d+)/);
                if (match && match[1]) {
                    const pid = match[1];
                    const rowEstado = dataMap[pid]?.estado; // 'Presente' | 'Ausente' | 'Justificado'
                    if (rowEstado) {
                        const btnActive = row.querySelector(`.btn-est[data-est="${rowEstado}"]`);
                        if (btnActive) btnActive.classList.add('active');
                    }
                }
            }
        });
    };
    
    window.registrarAsistencia = function(participanteId, estado, btnObj) {
        const fecha = asisDateInput.value;
        if(!fecha) return;
        
        // Optimistic UI update
        const parent = btnObj.parentElement;
        parent.querySelectorAll('.btn-est').forEach(b => b.classList.remove('active'));
        btnObj.classList.add('active');
        
        fetch(`${URLROOT}/actividades/registrarAsistencia`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({
                actividad_id: actividadId,
                participante_id: participanteId,
                fecha: fecha,
                estado: estado
            })
        })
        .then(res => res.json())
        .then(res => {
            if(!res.success) {
                if(typeof NotificationService !== 'undefined') NotificationService.error(res.message || 'Error al registrar.');
                parent.querySelectorAll('.btn-est').forEach(b => b.classList.remove('active')); // Revert
            } else {
                if(typeof NotificationService !== 'undefined') NotificationService.success('Asistencia registrada');
            }
        })
        .catch(err => {
            console.error(err);
            if(typeof NotificationService !== 'undefined') NotificationService.error('Error de red al registrar.');
            parent.querySelectorAll('.btn-est').forEach(b => b.classList.remove('active')); // Revert
        });
    };

    // Form agregar participante
    const formAdd = document.getElementById('formAddParticipante');
    if(formAdd) {
        formAdd.addEventListener('submit', function(e){
            e.preventDefault();
            
            const btn = document.getElementById('btnGuardarPart');
            const hdnCed = document.getElementById('hdnCedula').value.trim();
            if(!hdnCed) {
                if(typeof NotificationService !== 'undefined') NotificationService.error('Debes buscar y seleccionar un pasante primero.');
                return;
            }
            
            btn.disabled = true;
            btn.innerHTML = '<i class="ti ti-loader"></i> Guardando...';

            const formData = new FormData(this);
            const data = Object.fromEntries(formData.entries());
            data.actividad_id = actividadId;
            
            fetch(`${URLROOT}/actividades/agregarParticipante`, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify(data)
            })
            .then(res => res.json())
            .then(res => {
                if(res.success) {
                    if(typeof NotificationService !== 'undefined') NotificationService.success('Participante agregado con éxito');
                    setTimeout(() => location.reload(), 1500);
                } else {
                    if(typeof NotificationService !== 'undefined') NotificationService.error(res.message || 'Error al agregar');
                    btn.disabled = false;
                    btn.innerHTML = '<i class="ti ti-check"></i> Confirmar Asignación';
                }
            })
            .catch(err => {
                console.error(err);
                if(typeof NotificationService !== 'undefined') NotificationService.error('Error de conexión');
                btn.disabled = false;
                btn.innerHTML = '<i class="ti ti-check"></i> Confirmar Asignación';
            });
        });
    }

    // Buscador AJAX & Bento Box Logic
    const inputBuscar = document.getElementById('inputBuscarPasanteAJAX');
    const listaSug = document.getElementById('listaSugerenciasAjax');
    
    if (inputBuscar) {
        let timeoutId;
        inputBuscar.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();
            if (query.length < 2) {
                listaSug.style.display = 'none';
                return;
            }
            
            timeoutId = setTimeout(() => {
                const formData = new FormData();
                formData.append('query', query);
                const _esc = s => String(s ?? '').replace(/&/g,'&amp;').replace(/</g,'&lt;').replace(/>/g,'&gt;').replace(/"/g,'&quot;');
                fetch(`${URLROOT}/asignaciones/buscarPasanteAjax`, { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    listaSug.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(p => {
                            const div = document.createElement('div');
                            div.style.padding = '12px 16px';
                            div.style.cursor = 'pointer';
                            div.style.borderBottom = '1px solid #f1f5f9';
                            div.style.display = 'flex';
                            div.style.alignItems = 'center';
                            div.style.gap = '12px';
                            div.addEventListener('mouseover', () => div.style.background = '#f8fafc');
                            div.addEventListener('mouseout', () => div.style.background = 'white');

                            const inis = (_esc(p.nombres.charAt(0)) + _esc(p.apellidos.charAt(0))).toUpperCase();
                            div.innerHTML = `
                                <div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg, #10b981, #059669); display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:800; color:white;">${inis}</div>
                                <div>
                                    <div style="font-size:0.9rem; font-weight:800; color:#1e293b;">${_esc(p.nombres)} ${_esc(p.apellidos)}</div>
                                    <div style="font-size:0.75rem; font-weight:600; color:#64748b;">C.I: ${_esc(p.cedula)}</div>
                                </div>
                            `;

                            div.onclick = () => window.seleccionarPasante(p);
                            listaSug.appendChild(div);
                        });
                    } else {
                        listaSug.innerHTML = '<div style="padding:16px; text-align:center; color:#94a3b8; font-size:0.85rem; font-weight:600;">No se encontró en la base de datos central</div>';
                    }
                    listaSug.style.display = 'block';
                })
                .catch(() => {
                    listaSug.innerHTML = '<div style="padding:16px; text-align:center; color:#ef4444; font-size:0.85rem; font-weight:600;">Error al buscar</div>';
                    listaSug.style.display = 'block';
                });
            }, 300);
        });
        
        document.addEventListener('click', (e) => {
            if (!inputBuscar.contains(e.target) && !listaSug.contains(e.target)) {
                listaSug.style.display = 'none';
            }
        });
    }

    window.seleccionarPasante = function(p) {
        document.getElementById('contenedorBuscadorPasante').style.display = 'none';
        document.getElementById('listaSugerenciasAjax').style.display = 'none';
        
        document.getElementById('hdnNombres').value = p.nombres;
        document.getElementById('hdnApellidos').value = p.apellidos;
        document.getElementById('hdnCedula').value = p.cedula;
        document.getElementById('hdnCarrera').value = p.institucion_procedencia || 'Sin carrera especificada';
        
        document.getElementById('bentoNombre').innerText = p.nombres + ' ' + p.apellidos;
        document.getElementById('bentoCedula').innerText = p.cedula;
        document.getElementById('bentoAvatar').innerText = (p.nombres.charAt(0) + p.apellidos.charAt(0)).toUpperCase();
        document.getElementById('bentoInstitucion').innerText = p.institucion_procedencia || 'No especificada';
        
        // Auto-observación
        const txtObs = document.getElementById('txtObservaciones');
        if(txtObs && txtObs.value.trim() === '') {
            txtObs.value = `Registrado en Actividad Extra el ${new Date().toLocaleDateString()}`;
        }
        
        document.getElementById('bentoPasanteSeleccionado').style.display = 'block';
    }

    window.cancelarPasanteSeleccionado = function() {
        document.getElementById('hdnNombres').value = '';
        document.getElementById('hdnApellidos').value = '';
        document.getElementById('hdnCedula').value = '';
        document.getElementById('hdnCarrera').value = '';
        
        document.getElementById('bentoPasanteSeleccionado').style.display = 'none';
        document.getElementById('contenedorBuscadorPasante').style.display = 'block';
        const num = document.getElementById('inputBuscarPasanteAJAX');
        num.value = '';
        num.focus();
    }

    // Limpiar al cerrar modal
    document.getElementById('modalAddParticipante').addEventListener('hidden.bs.modal', function() {
        document.getElementById('formAddParticipante').reset();
        window.cancelarPasanteSeleccionado();
    });

})();
</script>
