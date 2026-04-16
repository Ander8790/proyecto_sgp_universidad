<?php
$act          = $data['actividad'];
$participantes = $data['participantes'] ?? [];
$asistenciasMap = $data['asistenciasMap'] ?? [];
$stats        = $data['statsAsistencia'] ?? [];
$diasActiva   = $data['diasActiva'] ?? 0;
$pctAsistencia = $data['pctAsistencia'] ?? 0;
$instituciones = $data['instituciones'] ?? [];
$supervisores  = $data['supervisores'] ?? [];
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

/* Banner Premium */
.detail-banner {
    background: var(--navy-grad);
    border-radius: 20px;
    padding: 32px;
    color: white;
    margin-bottom: 24px;
    box-shadow: 0 10px 30px rgba(30, 58, 138, 0.2);
    position: relative;
    overflow: hidden;
}
.detail-banner::before {
    content: ''; position: absolute; top: -50%; right: -10%; width: 400px; height: 400px;
    background: radial-gradient(circle, rgba(255,255,255,0.1) 0%, rgba(255,255,255,0) 70%);
    border-radius: 50%; pointer-events: none;
}
.breadcrumb-custom {
    display: inline-flex; align-items: center; gap: 6px;
    color: rgba(255,255,255,0.7); text-decoration: none; font-weight: 600; font-size: 0.9rem; margin-bottom: 16px; transition: color 0.2s;
}
.breadcrumb-custom:hover { color: white; }
.d-title { font-size: 2.2rem; font-weight: 800; margin: 0 0 12px 0; line-height: 1.2; letter-spacing: -0.5px; }

.d-badges { display: flex; gap: 10px; margin-bottom: 20px; align-items: center; }
.badge-premium { padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; backdrop-filter: blur(10px); }
.bp-tipo { background: rgba(255,255,255,0.2); border: 1px solid rgba(255,255,255,0.3); }

.pulsing-dot {
    width: 8px; height: 8px; background-color: #34d399;
    border-radius: 50%; display: inline-block; margin-right: 6px;
    animation: pulse-dot-w 1.5s infinite;
}
@keyframes pulse-dot-w { 0%,100%{transform:scale(1); opacity:1;} 50%{transform:scale(1.5); opacity:0.8;} }
.bp-activa { background: rgba(16, 185, 129, 0.2); border: 1px solid rgba(16, 185, 129, 0.5); color: #34d399; }
.bp-inactiva { background: rgba(255,255,255,0.1); border: 1px solid rgba(255,255,255,0.2); }

.d-meta { display: flex; gap: 24px; flex-wrap: wrap; font-size: 0.95rem; font-weight: 500; color: rgba(255,255,255,0.85); }
.d-meta div { display: flex; align-items: center; gap: 8px; }
.d-meta i { font-size: 1.2rem; color: rgba(255,255,255,0.6); }

/* KPI Row */
.kpi-row-mini { display: grid; grid-template-columns: repeat(4, 1fr); gap: 16px; margin-bottom: 24px; }
.kpi-mini { background: white; border-radius: 16px; padding: 16px 20px; display: flex; align-items: center; gap: 16px; box-shadow: 0 4px 10px rgba(0,0,0,0.02); border: 1px solid #f1f5f9; }
.kpi-mini i { font-size: 1.5rem; width: 44px; height: 44px; display: flex; align-items: center; justify-content: center; border-radius: 12px; }
.kpi-mini .val { font-size: 1.4rem; font-weight: 700; color: #1e293b; line-height: 1; margin-bottom: 4px; }
.kpi-mini .lbl { font-size: 0.75rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px; }

.km-part i { background: rgba(37, 99, 235, 0.1); color: #2563eb; }
.km-days i { background: rgba(217, 119, 6, 0.1); color: #d97706; }
.km-pct i { background: rgba(16, 185, 129, 0.1); color: #10b981; }
.km-stat i { background: rgba(100, 116, 139, 0.1); color: #64748b; }

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
/* P (Presente) */
.btn-est.active[data-est="P"] { background: #ecfdf5; border-color: #10b981; color: #10b981; }
/* A (Ausente) */
.btn-est.active[data-est="A"] { background: #fef2f2; border-color: #ef4444; color: #ef4444; }
/* J (Justificado) */
.btn-est.active[data-est="J"] { background: #fffbeb; border-color: #f59e0b; color: #f59e0b; }

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

    <!-- Premium Banner -->
    <div class="detail-banner">
        <a href="<?php echo URLROOT; ?>/actividades" class="breadcrumb-custom"><i class="ti ti-arrow-left"></i> Volver a Actividades</a>
        <h1 class="d-title"><?php echo htmlspecialchars($act->nombre); ?></h1>
        
        <div class="d-badges">
            <span class="badge-premium bp-tipo"><i class="ti ti-tag"></i> <?php echo htmlspecialchars($act->tipo); ?></span>
            <?php if($act->estado === 'Activa'): ?>
                <span class="badge-premium bp-activa"><span class="pulsing-dot"></span> Activa</span>
            <?php else: ?>
                <span class="badge-premium bp-inactiva"><?php echo htmlspecialchars($act->estado); ?></span>
            <?php endif; ?>
        </div>
        
        <div class="d-meta">
            <?php if(!empty($act->institucion_nombre)): ?>
                <div><i class="ti ti-building"></i> <?php echo htmlspecialchars($act->institucion_nombre); ?></div>
            <?php endif; ?>
            <?php if(!empty($act->supervisor_nombre)): ?>
                <div><i class="ti ti-user-check"></i> Sup: <?php echo htmlspecialchars($act->supervisor_nombre); ?></div>
            <?php endif; ?>
            <div><i class="ti ti-calendar"></i> <?php echo date('d/m/Y', strtotime($act->fecha_inicio)); ?> <?php echo $act->fecha_fin ? ' - ' . date('d/m/Y', strtotime($act->fecha_fin)) : '(En curso)'; ?></div>
        </div>
    </div>

    <!-- Mini KPIs -->
    <div class="kpi-row-mini">
        <div class="kpi-mini km-part">
            <i class="ti ti-users"></i>
            <div>
                <div class="val"><?php echo count($participantes); ?></div>
                <div class="lbl">Participantes</div>
            </div>
        </div>
        <div class="kpi-mini km-days">
            <i class="ti ti-calendar-stats"></i>
            <div>
                <div class="val"><?php echo $diasActiva; ?></div>
                <div class="lbl">Días de Actividad</div>
            </div>
        </div>
        <div class="kpi-mini km-pct">
            <i class="ti ti-chart-pie"></i>
            <div>
                <div class="val"><?php echo $pctAsistencia; ?>%</div>
                <div class="lbl">Asistencia Global</div>
            </div>
        </div>
        <div class="kpi-mini km-stat">
            <i class="ti ti-info-circle"></i>
            <div>
                <div class="val" style="font-size:1.1rem; margin-top:4px;"><?php echo htmlspecialchars($act->estado); ?></div>
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
                        <div class="pc-avatar"><?php echo $ini; ?></div>
                        <div class="pc-info">
                            <h5 class="pc-name" title="<?php echo htmlspecialchars($p->nombres . ' ' . $p->apellidos); ?>">
                                <?php echo htmlspecialchars($p->nombres . ' ' . $p->apellidos); ?>
                            </h5>
                            <p class="pc-cedula">
                                <span><i class="ti ti-id"></i> <?php echo htmlspecialchars($p->cedula); ?></span>
                                <?php if(!empty($p->carrera)): ?><span><i class="ti ti-book"></i> <?php echo htmlspecialchars($p->carrera); ?></span><?php endif; ?>
                            </p>
                            <?php if(!empty($p->observaciones)): ?>
                                <span class="pc-obs-badge"><i class="ti ti-message"></i> Obs.</span>
                            <?php endif; ?>
                        </div>
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
                            <div class="asis-avatar"><?php echo $ini; ?></div>
                            <div class="asis-name"><?php echo htmlspecialchars($p->nombres . ' ' . $p->apellidos); ?></div>
                        </div>
                        <div class="asis-actions">
                            <button class="btn-est btn-p" title="Presente" onclick="registrarAsistencia(<?php echo $p->id; ?>, 'P', this)" data-est="P">P</button>
                            <button class="btn-est btn-a" title="Ausente" onclick="registrarAsistencia(<?php echo $p->id; ?>, 'A', this)" data-est="A">A</button>
                            <button class="btn-est btn-j" title="Justificado" onclick="registrarAsistencia(<?php echo $p->id; ?>, 'J', this)" data-est="J">J</button>
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
                <?php echo Session::generateCsrfToken(); ?>
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

<!-- Modal Agregar Participante -->
<div class="modal fade" id="modalAddParticipante" tabindex="-1" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header premium">
                <h5 class="modal-title"><i class="ti ti-user-plus"></i> Nuevo Participante</h5>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
            </div>
            <form id="formAddParticipante">
                <div class="modal-body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <label class="form-label">Cédula *</label>
                            <input type="text" name="cedula" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Teléfono</label>
                            <input type="text" name="telefono" class="form-control">
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Nombres *</label>
                            <input type="text" name="nombres" class="form-control" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label">Apellidos *</label>
                            <input type="text" name="apellidos" class="form-control" required>
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Carrera/Especialidad</label>
                            <input type="text" name="carrera" class="form-control">
                        </div>
                        <div class="col-md-12">
                            <label class="form-label">Observaciones</label>
                            <textarea name="observaciones" class="form-control" rows="2"></textarea>
                        </div>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-light btn-action" data-bs-dismiss="modal">Cancelar</button>
                    <button type="submit" class="btn btn-primary btn-action" style="background:var(--navy-grad); border:none;">Guardar</button>
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
        // dataMap format: { pid: {estado: 'P'} }
        document.querySelectorAll('.asis-row').forEach(row => {
            const btns = row.querySelectorAll('.btn-est');
            btns.forEach(b => b.classList.remove('active')); // Reset
            
            // Need pid to match. We extract it from one of the functions in button
            const exampleBtn = row.querySelector('.btn-est');
            if(exampleBtn) {
                const match = exampleBtn.getAttribute('onclick').match(/registrarAsistencia\((\d+)/);
                if(match && match[1]) {
                    const pid = match[1];
                    if(dataMap[pid] && dataMap[pid].estado) {
                        const btnActive = row.querySelector(`.btn-est[data-est="${dataMap[pid].estado}"]`);
                        if(btnActive) btnActive.classList.add('active');
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
                }
            })
            .catch(err => {
                console.error(err);
                if(typeof NotificationService !== 'undefined') NotificationService.error('Error de conexión');
            });
        });
    }

})();
</script>
