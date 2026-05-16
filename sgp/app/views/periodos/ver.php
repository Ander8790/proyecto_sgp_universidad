<?php
/**
 * Vista: Detalle de Período Académico (Cohorte)
 * Muestra los pasantes agrupados por departamento mediante acordeones
 * con opciones individuales y masivas.
 */
?>
<style>
/* ── Modal overlay ─────────────────────────────────────────────────── */
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,0.7); backdrop-filter: blur(6px);
    z-index: 1000; align-items: center; justify-content: center;
    animation: act-fadeIn 0.2s ease;
}
.modal-overlay.active { display: flex; }
@keyframes act-fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes act-slideUp { from { transform:translateY(24px);opacity:0; } to { transform:translateY(0);opacity:1; } }

.modal-box {
    background: white; border-radius: 20px;
    width: 90%; max-width: 480px; max-height: 90vh;
    display: flex; flex-direction: column; overflow: hidden;
    box-shadow: 0 24px 60px rgba(15,23,42,0.25);
    animation: act-slideUp 0.3s ease;
}
.modal-head {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
    padding: 24px; display: flex; justify-content: space-between; align-items: center;
    color: white;
}
.modal-head h2 { font-size:1.25rem; font-weight:700; margin:0; color:white; }
.modal-head p  { font-size:0.85rem; margin:4px 0 0; color:rgba(255,255,255,0.8); }
.btn-close-modal { background: rgba(255,255,255,0.2); border: none; color: white; width: 32px; height: 32px; border-radius: 50%; cursor: pointer; display: flex; align-items: center; justify-content: center; transition: background 0.2s; }
.btn-close-modal:hover { background: rgba(255,255,255,0.35); }
.modal-body { padding: 24px; overflow-y: auto; flex: 1; }

.form-group { margin-bottom: 20px; }
.form-label { display:block; font-size:0.8rem; font-weight:700; color:#374151; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px; }
.form-input { width:100%; padding:12px; border:2px solid #e5e7eb; border-radius:12px; font-size:0.95rem; color:#1e293b; background:#fafafa; transition: border-color 0.2s; }
.form-input:focus { outline:none; border-color:#2563eb; }
.btn-submit { width:100%; padding:14px; background:linear-gradient(135deg,#172554 0%,#2563eb 100%); color:white; border:none; border-radius:12px; font-size:0.95rem; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px; }

/* ── KPI Cards V2 ─────────────────────────────────────────────────── */
.per-kpi-ver2 {
    background: white; border-radius: 20px; padding: 24px;
    display: flex; flex-direction: column; justify-content: space-between;
    box-shadow: 0 4px 15px rgba(22,38,96,0.05); border-left: 5px solid transparent;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
}
.per-kpi-ver2:hover { transform: translateY(-4px); box-shadow: 0 12px 25px rgba(22,38,96,0.1); }
.per-kpi-header { display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px; }
.per-kpi-label { font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; margin: 0; }
.per-kpi-icon { width: 48px; height: 48px; border-radius: 14px; display: flex; align-items: center; justify-content: center; font-size: 1.4rem; }
.per-kpi-val { font-size: 2.2rem; font-weight: 800; color: #1e293b; line-height: 1; margin: 0; }

/* ── Accordion por Departamento ───────────────────────────────────── */
.dept-accordion { background: white; border-radius: 16px; margin-bottom: 20px; border: 1px solid #e2e8f0; box-shadow: 0 2px 10px rgba(0,0,0,0.02); overflow: hidden; }
.dept-acc-header { padding: 18px 24px; display: flex; align-items: center; justify-content: space-between; cursor: pointer; transition: background 0.2s; user-select: none; }
.dept-acc-header:hover { background: #f8fafc; }
.dept-acc-title { display: flex; align-items: center; gap: 12px; }
.dept-acc-icon { width: 40px; height: 40px; border-radius: 10px; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 1.2rem; }
.dept-acc-name { margin: 0; font-size: 1.1rem; font-weight: 700; color: #1e293b; }
.dept-acc-count { background: #e2e8f0; color: #475569; padding: 2px 8px; border-radius: 20px; font-size: 0.75rem; font-weight: 700; }
.dept-acc-toggle { font-size: 1.2rem; color: #94a3b8; transition: transform 0.3s; }
.dept-accordion.open .dept-acc-toggle { transform: rotate(180deg); }
.dept-acc-body { display: none; padding: 0 24px 24px; border-top: 1px solid #f1f5f9; background: #f8fafc; }
.dept-accordion.open .dept-acc-body { display: block; animation: act-fadeIn 0.3s ease; }

/* ── Bento Pasantes Cards V2 ──────────────────────────────────────── */
.pasantes-grid { display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-top: 20px; }
@media (max-width: 1300px) { .pasantes-grid { grid-template-columns: repeat(2, 1fr); } }
@media (max-width: 768px)  { .pasantes-grid { grid-template-columns: 1fr; } }
@media (max-width: 1024px) { .kpi-grid-4 { grid-template-columns: repeat(2,1fr) !important; } }
@media (max-width: 600px)  { .kpi-grid-4 { grid-template-columns: 1fr !important; } }

.sw-pcard {
    background: white; border-radius: 16px; border: 1px solid #e2e8f0;
    box-shadow: 0 2px 8px rgba(0,0,0,0.02); padding: 18px;
    display: flex; flex-direction: column; transition: all 0.2s;
}
.sw-pcard:hover { transform: translateY(-4px); box-shadow: 0 10px 20px rgba(0,0,0,0.06); border-color:#cbd5e1; }
.sw-pcard-head { display: flex; align-items: flex-start; justify-content: space-between; gap: 10px; margin-bottom: 14px; }
.sw-pcard-user { display: flex; align-items: center; gap: 12px; }
.sw-pcard-av { width: 44px; height: 44px; border-radius: 12px; display: flex; align-items: center; justify-content: center; font-size: 1rem; font-weight: 800; color: white; flex-shrink: 0; }
.sw-pcard-info h4 { margin: 0; font-size: 1rem; font-weight: 700; color: #1e293b; line-height: 1.2; }
.sw-pcard-info p { margin: 2px 0 0; font-size: 0.75rem; color: #64748b; }

.b-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.7rem; font-weight: 700; height: fit-content;}
.b-b-activo { background: #dcfce7; color: #166534; }
.b-b-finalizado { background: #e0e7ff; color: #3730a3; }
.b-b-pendiente { background: #fef3c7; color: #92400e; }
.b-b-inactivo { background: #fef2f2; color: #991b1b; }

.sw-pcard-stats { display: flex; justify-content: space-between; align-items: center; background: #f8fafc; border-radius: 10px; padding: 10px 14px; margin-bottom: 14px; border: 1px dashed #cbd5e1; }
.sw-stat-box { display: flex; flex-direction: column; align-items: center; }
.sw-stat-val { font-size: 1.1rem; font-weight: 800; color: #1e293b; }
.sw-stat-lbl { font-size: 0.65rem; color: #64748b; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 700; margin-top: 2px; }

.sw-pcard-progress { margin-bottom: 16px; }
.sw-prog-info { display: flex; justify-content: space-between; font-size: 0.7rem; color: #475569; font-weight: 600; margin-bottom: 6px; }
.sw-prog-bar { height: 6px; background: #e2e8f0; border-radius: 4px; overflow: hidden; }
.sw-prog-fill { height: 100%; border-radius: 4px; }

.sw-pcard-actions { display: grid; grid-template-columns: 1fr 1fr; gap: 8px; margin-top: auto; }
.btn-p-acti { display:flex; align-items:center; justify-content:center; gap:6px; padding: 8px; border-radius: 8px; font-size: 0.75rem; font-weight: 700; text-decoration: none; border:none; cursor:pointer; transition:all 0.2s; white-space: nowrap;}
.btn-asis { background: rgba(16,185,129,0.1); color: #059669; }
.btn-asis:hover { background: #10b981; color: white; }
.btn-carta { background: rgba(37,99,235,0.1); color: #2563eb; }
.btn-carta:hover { background: #2563eb; color: white; }
.btn-alm { background: rgba(245,158,11,0.1); color: #d97706; }
.btn-alm:hover { background: #f59e0b; color: white; }
.btn-off { background: rgba(220,38,38,0.1); color: #dc2626; }
.btn-off:hover { background: #dc2626; color: white; }

/* ── Pills de Trimestre (mismo estilo que módulo Reportes) ────── */
.rpt-pill {
    display: flex; align-items: center; justify-content: center; gap: 6px;
    padding: 10px 12px; border-radius: 50px; border: 1.5px solid #dde2f0;
    background: #f8fafc; color: #3A4768; font-size: 0.85rem; font-weight: 600;
    cursor: pointer; user-select: none; transition: all 0.2s ease;
    width: 100%; text-align: center;
}
.rpt-pill input[type="radio"] { display: none; }
.rpt-pill:hover { border-color: #1D4ED8; color: #1D4ED8; background: rgba(29,78,216,0.07); }
.rpt-pill:has(input[type="radio"]:checked) {
    background: #162660; border-color: #162660; color: #ffffff;
    box-shadow: 0 4px 12px rgba(22,38,96,0.25);
}
/* ── Input de fecha para el modal ─────────────────────────────── */
.rpt-date-input {
    width: 100%; padding: 12px 16px; border: 2px solid #f1f5f9; border-radius: 14px;
    font-size: 0.9rem; background: #f8fafc; color: #0D1424; outline: none;
    transition: all 0.2s;
}
.rpt-date-input:focus { border-color: #1D4ED8; background: #fff; box-shadow: 0 0 0 4px rgba(29,78,216,0.07); }
</style>

<div class="dashboard-container">

    <?php
    // Estado visual del periodo
    switch ($periodo->estado) {
        case 'Activo':  $estCol = '#10b981'; $estBg = 'rgba(16,185,129,0.15)'; $estIco = 'ti-player-play'; break;
        case 'Cerrado': $estCol = '#94a3b8'; $estBg = 'rgba(255,255,255,0.15)'; $estIco = 'ti-lock'; break;
        default:        $estCol = '#f59e0b'; $estBg = 'rgba(245,158,11,0.15)'; $estIco = 'ti-clock';
    }
    ?>

    <!-- ── Banner Bento del Período ─────────────────────────────────────── -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:24px;padding:32px;margin-bottom:24px;position:relative;overflow:hidden;box-shadow:0 10px 30px rgba(30,58,138,0.25);">
        <div style="position:absolute;top:-40px;right:-40px;width:200px;height:200px;background:radial-gradient(circle, rgba(255,255,255,0.15) 0%, rgba(255,255,255,0) 70%);border-radius:50%;pointer-events:none;"></div>
        
        <div style="display:flex;flex-wrap:wrap;justify-content:space-between;align-items:center;gap:20px;position:relative;z-index:1;">
            <div style="display:flex;align-items:center;gap:20px;">
                <div style="background:rgba(255,255,255,0.15);backdrop-filter:blur(10px);width:70px;height:70px;border-radius:18px;display:flex;align-items:center;justify-content:center;border:1px solid rgba(255,255,255,0.2);">
                    <i class="ti ti-calendar-event" style="font-size:2.2rem;color:white;"></i>
                </div>
                <div>
                    <div style="display:flex;align-items:center;gap:12px;margin-bottom:6px;">
                        <h1 style="color:white;font-size:1.8rem;font-weight:800;margin:0;letter-spacing:-0.5px;">
                            <?= htmlspecialchars($periodo->nombre) ?>
                        </h1>
                        <span style="background:<?= $estBg ?>;color:<?= $estCol ?>;border:1px solid <?= $estCol ?>;padding:4px 12px;border-radius:20px;font-size:0.75rem;font-weight:700;display:inline-flex;align-items:center;gap:4px;">
                            <i class="ti <?= $estIco ?>"></i> <?= htmlspecialchars($periodo->estado) ?>
                        </span>
                    </div>
                    <p style="color:rgba(255,255,255,0.8);margin:0;font-size:0.9rem;display:flex;align-items:center;gap:8px;">
                        <i class="ti ti-calendar"></i> <?= date('d/m/Y', strtotime($periodo->fecha_inicio)) ?> &rarr; <?= date('d/m/Y', strtotime($periodo->fecha_fin)) ?>
                    </p>
                </div>
            </div>
            
            <!-- Barra de Herramientas Global -->
            <div style="display:flex;align-items:center;gap:12px;flex-wrap:wrap;background:rgba(255,255,255,0.1);padding:10px;border-radius:16px;border:1px solid rgba(255,255,255,0.15);backdrop-filter:blur(10px);">
                <a href="<?= URLROOT ?>/periodos" style="background:rgba(255,255,255,0.2);color:white;padding:10px 16px;border-radius:10px;font-weight:600;font-size:0.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s;" onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.2)'">
                    <i class="ti ti-arrow-left"></i> Volver a Cohortes
                </a>
                
                <a href="#" onclick="generarInformePeriodo(<?= (int)$periodo->id ?>, '<?= htmlspecialchars($periodo->estado) ?>', event)" style="background:white;color:#1e3a8a;border:none;padding:10px 16px;border-radius:10px;font-weight:700;font-size:0.85rem;text-decoration:none;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s;box-shadow:0 4px 10px rgba(0,0,0,0.1);" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <i class="ti ti-file-analytics"></i> Informe de Período
                </a>

                <?php if (strtolower($periodo->estado) === 'activo'): ?>
                <button onclick="confirmarCerrarDesde(<?= (int)$periodo->id ?>, '<?= htmlspecialchars(addslashes($periodo->nombre)) ?>', <?= (int)$kpiActivos ?>)"
                        style="background:rgba(220,38,38,0.9);color:white;border:1px solid #ef4444;padding:10px 16px;border-radius:10px;font-weight:700;font-size:0.85rem;cursor:pointer;display:inline-flex;align-items:center;gap:6px;transition:all 0.2s;box-shadow:0 4px 15px rgba(220,38,38,0.3);" onmouseover="this.style.background='#dc2626'" onmouseout="this.style.background='rgba(220,38,38,0.9)'">
                    <i class="ti ti-power"></i> Finalizar Cohorte (Desactivar)
                </button>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <!-- ── KPI Cards ──────────────────────────────────────────────── -->
    <div class="kpi-grid-4" style="display:grid;grid-template-columns:repeat(4,1fr);gap:24px;margin-bottom:32px;">
        <?php
        $kpis = [
            ['lbl' => 'Pasantes Cohorte', 'v' => $kpiPasantes,    'c' => '#2563eb', 'ico' => 'ti-users'],
            ['lbl' => 'En Pasantía',      'v' => $kpiActivos,     'c' => '#10b981', 'ico' => 'ti-user-check'],
            ['lbl' => 'Culminados',       'v' => $kpiFinalizados, 'c' => '#8b5cf6', 'ico' => 'ti-award'],
            ['lbl' => 'Prom. Asistencia', 'v' => $promAsistencia . '%', 'c' => '#f59e0b', 'ico' => 'ti-chart-bar'],
        ];
        foreach ($kpis as $k): ?>
        <div class="per-kpi-ver2" style="border-left-color: <?= $k['c'] ?>;">
            <div class="per-kpi-header">
                <p class="per-kpi-label"><?= $k['lbl'] ?></p>
                <div class="per-kpi-icon" style="background:<?= $k['c'] ?>18; color:<?= $k['c'] ?>;">
                    <i class="ti <?= $k['ico'] ?>"></i>
                </div>
            </div>
            <h3 class="per-kpi-val"><?= $k['v'] ?></h3>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ── Grupos por Departamento ──────────────────────────────────────── -->
    <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:20px;">
        <h2 style="font-size:1.3rem;font-weight:800;color:#1e293b;margin:0;display:flex;align-items:center;gap:10px;">
            <i class="ti ti-building-community" style="padding:6px;background:#eff6ff;color:#2563eb;border-radius:8px;"></i>
            Grupos de Trabajo
        </h2>
    </div>

    <?php if (empty($pasantesPorDepto)): ?>
    <div style="background:white; border-radius:24px; padding:60px 20px; text-align:center; border:1px dashed #cbd5e1; box-shadow:0 10px 40px rgba(0,0,0,0.03);">
        <div style="width:100px; height:100px; background:linear-gradient(135deg,#f8fafc 0%,#f1f5f9 100%); border-radius:50%; display:flex; align-items:center; justify-content:center; margin:0 auto 24px; color:#94a3b8; box-shadow:0 4px 6px -1px rgba(0,0,0,0.05) inset;">
            <i class="ti ti-users-group" style="font-size:3.5rem;"></i>
        </div>
        <h3 style="font-size:1.4rem; font-weight:800; margin:0 0 12px; color:#1e293b; letter-spacing:-0.5px;">Cohorte Reciente — Esperando Asignaciones</h3>
        <p style="color:#64748b; margin:0 auto; max-width:550px; font-size:1.05rem; line-height:1.6;">
            Aún no hay pasantes vinculados a esta línea de tiempo. Los estudiantes aparecerán listados aquí <b>automáticamente</b> en cuanto se consolide su ingreso desde el <a href="<?= URLROOT ?>/asignaciones" style="color:#2563eb; font-weight:700; text-decoration:none;"><i class="ti ti-target-arrow"></i> Módulo de Asignaciones</a>.
        </p>
    </div>
    <?php else: ?>

        <?php 
        $coloresDep = ['#2563eb', '#10b981', '#8b5cf6', '#f59e0b', '#ec4899', '#06b6d4'];
        $idxD = 0;
        
        foreach ($pasantesPorDepto as $depto => $pasantesList): 
            $dColor = $coloresDep[$idxD % count($coloresDep)]; $idxD++;
            $totalDep = count($pasantesList);
            // Default abierto el primer acordeón
            $isOpenClass = ($idxD === 1) ? 'open' : '';
        ?>
        <div class="dept-accordion <?= $isOpenClass ?>">
            <div class="dept-acc-header" onclick="this.parentElement.classList.toggle('open')">
                <div class="dept-acc-title">
                    <div class="dept-acc-icon" style="background:<?= $dColor ?>15; color:<?= $dColor ?>;"><i class="ti ti-folder-open"></i></div>
                    <h3 class="dept-acc-name"><?= htmlspecialchars($depto) ?></h3>
                    <span class="dept-acc-count"><?= $totalDep ?> pasante<?= $totalDep !== 1 ? 's' : '' ?></span>
                </div>
                <div class="dept-acc-toggle"><i class="ti ti-chevron-down"></i></div>
            </div>
            
            <div class="dept-acc-body">
                <div class="pasantes-grid">
                    <?php foreach ($pasantesList as $p):
                        $iniciales = strtoupper(substr($p->nombres ?? '?', 0, 1) . substr($p->apellidos ?? '', 0, 1));
                        $nombreCompl = htmlspecialchars(($p->nombres ?? '') . ' ' . ($p->apellidos ?? ''));
                        
                        // Progreso Horas
                        $hMeta = (int)($p->horas_meta ?? 1440);
                        $hAcum = (int)($p->horas_acumuladas ?? 0);
                        $progreso = $hMeta > 0 ? round(($hAcum / $hMeta) * 100) : 0;
                        $progColor = $progreso >= 80 ? '#10b981' : ($progreso >= 50 ? '#f59e0b' : '#ef4444');
                        if($progreso > 100) $progreso = 100;

                        // Asistencia %
                        $pctAsist = $p->total_dias_registrados > 0 ? round(($p->dias_presentes / $p->total_dias_registrados) * 100) : 0;
                        
                        // Estado y color
                        $bBadgeClass = 'b-b-sinasig';
                        if ($p->estado_usuario === 'inactivo') {
                            $bBadgeClass   = 'b-b-inactivo';
                            $p->estado_pasantia = 'Finalizado'; // Inactivo = Finalizado historicamente
                        } else {
                            switch ($p->estado_pasantia) {
                                case 'Activo':     $bBadgeClass = 'b-b-activo';     break;
                                case 'Finalizado': $bBadgeClass = 'b-b-finalizado'; break;
                                case 'Pendiente':  $bBadgeClass = 'b-b-pendiente';  break;
                            }
                        }
                    ?>
                    <div class="sw-pcard">
                        <div class="sw-pcard-head">
                            <div class="sw-pcard-user">
                                <div class="sw-pcard-av" style="background: linear-gradient(135deg, <?= $dColor ?>, <?= $dColor ?>99);"><?= htmlspecialchars($iniciales) ?></div>
                                <div class="sw-pcard-info">
                                    <h4><?= $nombreCompl ?></h4>
                                    <p>V-<?= htmlspecialchars($p->cedula ?? '—') ?></p>
                                </div>
                            </div>
                            <span class="b-badge <?= $bBadgeClass ?>"><?= htmlspecialchars($p->estado_pasantia) ?></span>
                        </div>

                        <!-- Métricas Rápidas -->
                        <div class="sw-pcard-stats">
                            <div class="sw-stat-box">
                                <span class="sw-stat-val"><?= (int)$p->dias_presentes ?></span>
                                <span class="sw-stat-lbl">Asist. Días</span>
                            </div>
                            <div class="sw-stat-box">
                                <span class="sw-stat-val" style="color:<?= $progColor ?>;"><?= $pctAsist ?>%</span>
                                <span class="sw-stat-lbl">Tasa Asist.</span>
                            </div>
                        </div>

                        <!-- Progreso Horas -->
                        <div class="sw-pcard-progress">
                            <div class="sw-prog-info">
                                <span><i class="ti ti-target" style="color:<?= $dColor ?>"></i> Progreso de Horas</span>
                                <span><?= $hAcum ?>/<?= $hMeta ?> hrs</span>
                            </div>
                            <div class="sw-prog-bar">
                                <div class="sw-prog-fill" style="width:<?= $progreso ?>%; background:<?= $progColor ?>;"></div>
                            </div>
                        </div>

                        <!-- Acciones Grid -->
                        <div class="sw-pcard-actions">
                            <a href="<?= URLROOT ?>/asistencias/almanaque/<?= (int)$p->id ?>" class="btn-p-acti btn-alm" title="Ver Almanaque Personal">
                                <i class="ti ti-calendar-stats"></i> Almanaque
                            </a>
                            <button type="button" onclick="abrirModalReporteAsist(<?= (int)$p->id ?>, '<?= addslashes($nombreCompl) ?>')" class="btn-p-acti btn-asis" title="Reporte de Asistencias por Trimestre">
                                <i class="ti ti-file-analytics"></i> Reporte Asist.
                            </button>
                            <button type="button" onclick="generarConstancia(<?= (int)$p->id ?>, '<?= $p->estado_pasantia ?>', <?= $progreso ?>)" class="btn-p-acti btn-carta" title="Generar Constancia PDF">
                                <i class="ti ti-file-certificate"></i> Constancia
                            </button>
                            
                            <?php if ($p->estado_usuario !== 'inactivo' && $p->estado_pasantia === 'Activo'): ?>
                            <form action="<?= URLROOT ?>/periodos/desactivarPasante/<?= (int)$p->id ?>" method="POST" style="margin:0;">
                                <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
                                <input type="hidden" name="periodo_id" value="<?= (int)$periodo->id ?>">
                                <button type="button" onclick="confirmarDesactivarIndividual(this.form, '<?= addslashes($nombreCompl) ?>')" class="btn-p-acti btn-off" style="width:100%;">
                                    <i class="ti ti-power"></i> Desvincular
                                </button>
                            </form>
                            <?php else: ?>
                            <div style="background:#f1f5f9; color:#94a3b8; display:flex; align-items:center; justify-content:center; padding:8px; border-radius:8px; font-size:0.75rem; font-weight:700; cursor:not-allowed;">
                                <i class="ti ti-lock" style="margin-right:4px;"></i> Inactivo
                            </div>
                            <?php endif; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    <?php endif; ?>
</div>

    <div style="margin-bottom:60px;"></div>

<!-- ── Modal: Reporte de Asistencia por Trimestre ─────────────────── -->
<div id="modalReporteAsist" class="sgp-modal-overlay" onclick="if(event.target===this)cerrarModalReporte()">
    <div class="sgp-modal" style="max-width:500px;">

        <!-- Header idéntico al módulo de reportes -->
        <div class="sgp-modal-header" style="padding:24px 28px;display:flex;justify-content:space-between;align-items:center;">
            <div>
                <h3 style="color:white;font-weight:800;font-size:1.2rem;margin:0;display:flex;align-items:center;gap:8px;">
                    <i class="ti ti-file-analytics"></i> Reporte de Asistencias
                </h3>
                <p id="rptAsistNombre" style="color:rgba(255,255,255,0.72);font-size:0.85rem;margin:5px 0 0;"></p>
            </div>
            <button onclick="cerrarModalReporte()"
                    style="background:rgba(255,255,255,0.15);border:none;color:rgba(255,255,255,0.8);width:36px;height:36px;border-radius:50%;cursor:pointer;display:flex;align-items:center;justify-content:center;font-size:1.1rem;transition:background 0.2s;"
                    onmouseover="this.style.background='rgba(255,255,255,0.3)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <!-- Body -->
        <div class="sgp-modal-body" style="padding:24px 28px;overflow-y:auto;">

            <p style="font-size:0.72rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:12px;">
                Selección rápida por trimestre
            </p>

            <!-- 3 Trimestres en fila -->
            <div style="display:grid;grid-template-columns:repeat(3,1fr);gap:8px;margin-bottom:8px;">
                <label class="rpt-pill">
                    <input type="radio" name="rptTrimestre" value="1" onchange="selTrimestre(1)">
                    <i class="ti ti-calendar-event" style="font-size:0.85rem;"></i> Trimestre I
                </label>
                <label class="rpt-pill">
                    <input type="radio" name="rptTrimestre" value="2" onchange="selTrimestre(2)">
                    <i class="ti ti-calendar-event" style="font-size:0.85rem;"></i> Trimestre II
                </label>
                <label class="rpt-pill">
                    <input type="radio" name="rptTrimestre" value="3" onchange="selTrimestre(3)">
                    <i class="ti ti-calendar-event" style="font-size:0.85rem;"></i> Trimestre III
                </label>
            </div>
            <!-- Período completo full-width -->
            <div style="margin-bottom:22px;">
                <label class="rpt-pill" id="rptPillCompleto" style="width:100%;justify-content:center;">
                    <input type="radio" name="rptTrimestre" value="0" onchange="selTrimestre(0)" checked>
                    <i class="ti ti-chart-bar" style="font-size:0.85rem;"></i> Período Completo
                </label>
            </div>

            <p style="font-size:0.72rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:10px;">
                O define un rango personalizado
            </p>
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:24px;">
                <div>
                    <label style="display:block;font-size:0.72rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;">Desde</label>
                    <input type="date" id="rptDesde" class="rpt-date-input">
                </div>
                <div>
                    <label style="display:block;font-size:0.72rem;font-weight:800;color:#64748b;text-transform:uppercase;letter-spacing:0.8px;margin-bottom:8px;">Hasta</label>
                    <input type="date" id="rptHasta" class="rpt-date-input">
                </div>
            </div>

            <button onclick="generarReporteAsist()"
                    style="width:100%;padding:14px;background:linear-gradient(135deg,#172554 0%,#2563eb 100%);color:white;border:none;border-radius:14px;font-size:0.95rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:opacity 0.2s;"
                    onmouseover="this.style.opacity='0.88'" onmouseout="this.style.opacity='1'">
                <i class="ti ti-file-download"></i> Generar PDF
            </button>
        </div>
    </div>
</div>

<!-- Formulario oculto para cerrar período y desactivar a todos -->
<form id="formCerrarPeriodo" action="<?= URLROOT ?>/periodos/cerrar" method="POST" style="display:none;">
    <input type="hidden" name="csrf_token" value="<?= htmlspecialchars($csrf) ?>">
    <input type="hidden" name="periodo_id" value="<?= (int)$periodo->id ?>">
</form>

<script>
const PERIODO_ESTADO = '<?= htmlspecialchars($periodo->estado) ?>';
const PERIODO_INICIO = '<?= $periodo->fecha_inicio ?>';
const PERIODO_FIN    = '<?= $periodo->fecha_fin ?>';

/* ── Modal: Reporte de Asistencia ────────────────────────────── */
let _rptPasanteId = 0;

function abrirModalReporteAsist(pasanteId, nombrePasante) {
    _rptPasanteId = pasanteId;
    document.getElementById('rptAsistNombre').textContent = nombrePasante;
    // Resetear al período completo
    document.querySelectorAll('input[name="rptTrimestre"]').forEach(r => r.checked = false);
    document.querySelector('input[name="rptTrimestre"][value="0"]').checked = true;
    document.getElementById('rptDesde').value = PERIODO_INICIO;
    document.getElementById('rptHasta').value = PERIODO_FIN;
    document.getElementById('modalReporteAsist').classList.add('active');
}

function cerrarModalReporte() {
    document.getElementById('modalReporteAsist').classList.remove('active');
}

function selTrimestre(num) {
    const inicio = new Date(PERIODO_INICIO + 'T00:00:00');
    const fin    = new Date(PERIODO_FIN    + 'T00:00:00');
    let desde, hasta;

    if (num === 0) {
        desde = PERIODO_INICIO;
        hasta = PERIODO_FIN;
    } else {
        // Dividir el período en 3 trimestres iguales
        const totalMs = fin.getTime() - inicio.getTime();
        const trimMs  = totalMs / 3;
        const dIni    = new Date(inicio.getTime() + (num - 1) * trimMs);
        const dFin    = new Date(inicio.getTime() + num * trimMs - 86400000);
        if (dFin > fin) dFin.setTime(fin.getTime());
        desde = dIni.toISOString().slice(0, 10);
        hasta = dFin.toISOString().slice(0, 10);
    }

    document.getElementById('rptDesde').value = desde;
    document.getElementById('rptHasta').value = hasta;
}

function generarReporteAsist() {
    const desde = document.getElementById('rptDesde').value;
    const hasta = document.getElementById('rptHasta').value;
    if (!desde || !hasta) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'Selecciona las fechas de inicio y fin.', showConfirmButton: false, timer: 2500 });
        return;
    }
    if (hasta < desde) {
        Swal.fire({ toast: true, position: 'top-end', icon: 'warning', title: 'La fecha final debe ser mayor o igual a la inicial.', showConfirmButton: false, timer: 2500 });
        return;
    }
    window.open(`<?= URLROOT ?>/periodos/reporteAsistencia/${_rptPasanteId}?desde=${desde}&hasta=${hasta}`, '_blank');
    cerrarModalReporte();
}

/* ── Constancia de Culminación ──────────────────────────────── */
function generarConstancia(id, estadoPasantia, progreso) {
    if (typeof Swal === 'undefined') return;

    if (PERIODO_ESTADO !== 'Cerrado') {
        Swal.fire({
            title: 'Período Aún Activo',
            html: '<p style="color:#64748b;font-size:0.95rem;">La constancia de culminación solo puede generarse cuando el período académico esté en estado <strong>Cerrado</strong>.<br><br>Finaliza el período primero desde la administración.</p>',
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#2563eb',
            customClass: { popup: 'sgp-swal-modal' },
            didOpen: () => { document.querySelector('.swal2-popup').style.borderRadius = '20px'; }
        });
        return;
    }

    if (progreso < 100) {
        Swal.fire({
            title: 'Horas Incompletas',
            html: `<p style="color:#64748b;font-size:0.95rem;">Este pasante solo tiene <strong>${progreso}%</strong> de sus horas completadas.<br><br>No se puede emitir la constancia de culminación hasta que haya alcanzado el <strong>100%</strong> de las horas requeridas.</p>`,
            icon: 'warning',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#f59e0b',
            customClass: { popup: 'sgp-swal-modal' },
            didOpen: () => { document.querySelector('.swal2-popup').style.borderRadius = '20px'; }
        });
        return;
    }

    window.open('<?= URLROOT ?>/periodos/cartaCulminacion/' + id, '_blank');
}

/* ── Cerrar período desde la vista de detalle ───────────────── */
function confirmarCerrarDesde(id, nombre, activosCount) {
    if (typeof Swal === 'undefined') return;

    if (activosCount > 0) {
        Swal.fire({
            title: 'Acción Denegada',
            html: `<p style="color:#64748b;font-size:0.95rem;">No puedes finalizar la cohorte <strong>${nombre}</strong> porque aún existen <strong>${activosCount} pasantes en curso activo</strong>.<br><br>Debes esperar a que culminen o desvincularlos individualmente antes de cerrar el período.</p>`,
            icon: 'error',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#2563eb',
            customClass: { popup: 'sgp-swal-modal' },
            didOpen: () => { document.querySelector('.swal2-popup').style.borderRadius = '20px'; }
        });
        return;
    }

    Swal.fire({
        title: '¿Finalizar Cohorte?',
        html: `<p style="color:#64748b;font-size:0.95rem;">Vas a finalizar y aislar la cohorte <strong>${nombre}</strong>.<br><br>Se <strong>deshabilitará permanentemente el acceso</strong> de todos los pasantes vinculados ya que se asume que culminaron sus funciones.<br><br>Esta acción es irreversible y consolida los reportes.</p>`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: '<i class="ti ti-lock"></i> Sí, Finalizar Cohorte',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#64748b',
        customClass: { popup: 'sgp-swal-modal' },
        didOpen: () => { document.querySelector('.swal2-popup').style.borderRadius = '20px'; }
    }).then(result => {
        if (result.isConfirmed) {
            document.getElementById('formCerrarPeriodo').submit();
        }
    });
}

function confirmarDesactivarIndividual(form, nombrePasante) {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
        title: 'Protección de Pasante',
        html: `<p style="color:#64748b;font-size:0.95rem;">¿De verdad deseas retirar el acceso al sistema a <strong>${nombrePasante}</strong> de forma prematura?<br><br>Esta acción cortará su actividad inmediatamente.</p>`,
        icon: 'error',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#94a3b8',
        confirmButtonText: '<i class="ti ti-power"></i> Sí, Estoy Seguro / Retirar',
        cancelButtonText: 'Cancelar',
        customClass: { popup: 'sgp-swal-modal' },
        didOpen: () => { document.querySelector('.swal2-popup').style.borderRadius = '20px'; }
    }).then(result => {
        if (result.isConfirmed) form.submit();
    });
}

function generarInformePeriodo(id, estado, event) {
    if (event) event.preventDefault();
    if (typeof Swal === 'undefined') return;

    if (estado !== 'Cerrado') {
        Swal.fire({
            title: 'Informe No Disponible',
            html: '<p style="color:#64748b;font-size:0.95rem;">Aún no se puede generar el informe general consolidado debido a que <b>este período no ha culminado legalmente</b>.<br><br>Debes esperar a que la cohorte sea finalizada desde la administración.</p>',
            icon: 'info',
            confirmButtonText: 'Entendido',
            confirmButtonColor: '#2563eb',
            customClass: { popup: 'sgp-swal-modal' },
            didOpen: () => { document.querySelector('.swal2-popup').style.borderRadius = '20px'; }
        });
    } else {
        window.open('<?= URLROOT ?>/periodos/informeGeneral/' + id, '_blank');
    }
}
</script>
