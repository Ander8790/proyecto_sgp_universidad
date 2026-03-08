<?php
// Vista exclusiva para Administradores — SGP Premium Dashboard
$user_name          = $data['user_name']          ?? 'Administrador';
$role               = $data['role']               ?? 'Administrador';
$totalActivos       = $data['totalActivos']       ?? 0;
$pendientesAsignar  = $data['pendientesAsignar']  ?? 0;
$asistenciasHoy     = $data['asistenciasHoy']     ?? 0;
$faltasHoy          = $data['faltasHoy']          ?? 0;
$actividadReciente  = $data['actividadReciente']  ?? [];
?>

<style>
/* === SGP BENTO UI & STRUCTURAL FIXES === */
.dashboard-bento { display: flex !important; flex-direction: column !important; gap: 24px; width: 100%; }
.dashboard-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; width: 100%; }
.charts-grid-50 { display: grid; grid-template-columns: repeat(2, 1fr); gap: 20px; width: 100%; align-items: stretch; }
.bottom-grid-60-40 { display: grid; grid-template-columns: 6fr 4fr; gap: 20px; width: 100%; align-items: stretch; }

@media (max-width: 1200px) {
    .dashboard-kpi-grid { grid-template-columns: repeat(2, 1fr); }
    .charts-grid-50, .bottom-grid-60-40 { grid-template-columns: 1fr; }
}
@media (max-width: 768px) {
    .dashboard-kpi-grid { grid-template-columns: 1fr; }
}

/* === KPI CARDS WITH HOVER RIBBON === */
.kpi-card { display: flex; justify-content: space-between; align-items: center; padding: 22px; background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(22, 38, 96, 0.05); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-left: 5px solid var(--kpi-color, transparent); }
.kpi-card:hover { transform: translateY(-4px); box-shadow: 0 12px 25px var(--kpi-glow, rgba(0,0,0,0.1)); }
.kpi-blue { --kpi-color: #2563eb; --kpi-glow: rgba(37,99,235,0.55); }
.kpi-orange { --kpi-color: #F59E0B; --kpi-glow: rgba(245,158,11,0.55); }
.kpi-green { --kpi-color: #059669; --kpi-glow: rgba(5,150,105,0.55); }
.kpi-red { --kpi-color: #DC2626; --kpi-glow: rgba(220,38,38,0.55); }

.kpi-left { display: flex; align-items: center; gap: 15px; }
.kpi-label { display: block; font-size: 0.8rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-value { margin: 4px 0 0; font-size: 1.8rem; font-weight: 800; line-height: 1; }

.btn-bento-action { display: flex; flex-direction: column; align-items: center; justify-content: center; gap: 6px; padding: 12px 18px; border-radius: 12px; font-size: 0.75rem; font-weight: 700; text-decoration: none; transition: all 0.3s ease; border: 1px solid transparent; min-width: 80px; }
.btn-bento-action i { font-size: 1.4rem; }
.btn-bento-action:hover { transform: scale(1.05); }

/* === CHARTS STABILITY (NO JUMPING) === */
.figma-card .chart-wrapper { height: 320px !important; min-height: 320px !important; max-height: 320px !important; width: 100%; position: relative; display: flex; flex-direction: column; justify-content: center; overflow: hidden; }
#chart-main-history, #chart-asistencia-depto, #chart-gauge-container { width: 100%; height: 100%; }
.figma-card { min-height: 440px; display: flex; flex-direction: column; }

/* === SCROLLABLE PANELS === */
.table-container-scroll, .activity-list-scroll { max-height: 350px; overflow-y: auto; padding-right: 6px; }
.table-container-scroll::-webkit-scrollbar, .activity-list-scroll::-webkit-scrollbar { width: 6px; }
.table-container-scroll::-webkit-scrollbar-thumb, .activity-list-scroll::-webkit-scrollbar-thumb { background: #cbd5e1; border-radius: 10px; }

/* === MICRO BUTTONS === */
.btn-micro { background: transparent; border: none; padding: 6px; border-radius: 6px; cursor: pointer; color: #64748b; transition: all 0.2s; }
.btn-micro:hover { background: #e2e8f0; color: #1e293b; transform: translateY(-2px); box-shadow: 0 4px 6px rgba(0,0,0,0.05); }
.btn-micro.active { background: #fff; color: #2563eb; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }
.btn-action { transition: all 0.2s; border: none; cursor: pointer; }
.btn-action:hover { transform: translateY(-2px); box-shadow: 0 4px 8px rgba(0,0,0,0.08); }
.btn-action.btn-view:hover { background: #e0f2fe !important; color: #0369a1 !important; }
.btn-action.btn-edit:hover { background: #dbeafe !important; color: #1d4ed8 !important; }

/* === GAUGE LEGEND === */
.gauge-legend { display: none; justify-content: center; flex-wrap: wrap; gap: 8px; padding: 10px 0 0; }
.gauge-legend.active { display: flex; }
.gauge-legend-chip { display: flex; align-items: center; gap: 6px; padding: 5px 12px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; background: #f8fafc; border: 1px solid #e2e8f0; color: #475569; }
.gauge-legend-dot { width: 10px; height: 10px; border-radius: 50%; flex-shrink: 0; }
</style>

<div class="dashboard-bento">
    
    <!-- BANNER ESTANDARIZADO SGP -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:28px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:14px;">
                <i class="ti ti-sparkles" style="font-size:32px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.8rem;font-weight:700;margin:0;">¡Bienvenido de nuevo, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>!</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;">
                    <i class="ti ti-layout-dashboard"></i> Panel de Control · <?= $role ?>
                </p>
            </div>
        </div>
        <div style="display:flex; z-index:1; align-items:center;">
            <div style="background: rgba(0, 0, 0, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 50px; padding: 10px 24px; display: flex; align-items: center; gap: 20px; color: white; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-calendar" style="color: rgba(255,255,255,0.8); font-size: 1.1rem;"></i> 
                    <span id="currentDate" style="font-size: 0.9rem; font-weight: 500; letter-spacing: 0.5px;"></span>
                </div>
                <div style="width: 1px; height: 24px; background: rgba(255, 255, 255, 0.2);"></div>
                <div style="display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-clock" style="color: rgba(255,255,255,0.8); font-size: 1.2rem;"></i> 
                    <span id="currentTime" style="font-size: 1.1rem; font-weight: 700; letter-spacing: 0.5px;"></span>
                </div>
            </div>
        </div>
    </div>
    <div class="dashboard-kpi-grid">
        <div class="kpi-card-blue" style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid #2563eb;display:flex;justify-content:space-between;align-items:center;">
            <div style="flex:1;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Activos</p>
                <div style="display:flex;align-items:center;gap:12px;">
                    <h2 style="font-size:2.4rem;font-weight:800;color:#2563eb;margin:0;line-height:1;"><?= $totalActivos ?></h2>
                </div>
            </div>
            <a href="<?= URLROOT ?>/pasantes" style="background:#eff6ff;color:#2563eb;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'" title="Directorio">
                <i class="ti ti-users"></i>
            </a>
        </div>

        <div class="kpi-card-yellow" style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid #F59E0B;display:flex;justify-content:space-between;align-items:center;">
            <div style="flex:1;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Por Asignar</p>
                <div style="display:flex;align-items:center;gap:12px;">
                    <h2 style="font-size:2.4rem;font-weight:800;color:#F59E0B;margin:0;line-height:1;"><?= $pendientesAsignar ?></h2>
                </div>
            </div>
            <a href="<?= URLROOT ?>/asignaciones" style="background:#FFFBEB;color:#D97706;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'" title="Asignar">
                <i class="ti ti-link"></i>
            </a>
        </div>

        <div class="kpi-card-green" style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid #059669;display:flex;justify-content:space-between;align-items:center;">
            <div style="flex:1;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">En Sitio Hoy</p>
                <div style="display:flex;align-items:center;gap:12px;">
                    <h2 style="font-size:2.4rem;font-weight:800;color:#059669;margin:0;line-height:1;"><?= $asistenciasHoy ?></h2>
                </div>
            </div>
            <a href="<?= URLROOT ?>/asistencias" style="background:#F0FDF4;color:#059669;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'" title="Registros">
                <i class="ti ti-fingerprint"></i>
            </a>
        </div>

        <div class="kpi-card-red" style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid #DC2626;display:flex;justify-content:space-between;align-items:center;">
            <div style="flex:1;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Faltas Hoy</p>
                <div style="display:flex;align-items:center;gap:12px;">
                    <h2 style="font-size:2.4rem;font-weight:800;color:#DC2626;margin:0;line-height:1;"><?= $faltasHoy ?></h2>
                </div>
            </div>
            <a href="<?= URLROOT ?>/asistencias" style="background:#FEF2F2;color:#DC2626;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'" title="Justificar">
                <i class="ti ti-alert-triangle"></i>
            </a>
        </div>
    </div>

    <div class="charts-grid-50">
        <div class="card figma-card" style="padding: 24px; background: #fff; border-radius: 16px;">
            <div class="card-header-compact" style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 20px;">
                <div>
                    <h3 style="margin: 0; color: #162660; font-size: 1.1rem;">Monitor de Asistencia</h3>
                    <span style="font-size: 0.8rem; color: #94A3B8;">Análisis de flujo de pasantes</span>
                </div>
                <div style="display: flex; flex-direction: column; align-items: flex-end; gap: 8px;">
                    <div class="time-toggles" style="display: flex; background: #F8FAFC; padding: 4px; border-radius: 8px; border: 1px solid #E2E8F0;">
                        <button id="btnTimeDay" class="btn-micro active" style="font-size:0.75rem;">Diario</button>
                        <button id="btnTimeWeek" class="btn-micro" style="font-size:0.75rem;">Semanal</button>
                        <button id="btnTimeMonth" class="btn-micro" style="font-size:0.75rem;">Mensual</button>
                    </div>
                </div>
            </div>
            <div class="chart-wrapper">
                <div id="chart-main-history"></div>
            </div>
        </div>

        <div class="card figma-card" style="padding: 24px; background: #fff; border-radius: 16px;">
            <div class="card-header-compact" style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
                <div>
                    <h3 style="margin: 0; color: #162660; font-size: 1.1rem;">Desempeño Departamental</h3>
                    <span style="font-size: 0.8rem; color: #94A3B8;">Rendimiento de asistencia</span>
                </div>
            </div>
            <div class="chart-wrapper pt-3">
                <?php
                $deptData = $data['metricas_graficos']['asistenciaDepartamento'] ?? ['labels' => [], 'series' => []];
                $labels = $deptData['labels'] ?? [];
                $series = $deptData['series'] ?? [];
                ?>
                <?php if (empty($labels) || (count($labels) === 1 && $labels[0] === 'General' && empty($series[0]))): ?>
                    <p class="text-center text-muted p-4" style="font-size:0.85rem;">No hay datos departamentales</p>
                <?php else: ?>
                    <style>
                        @keyframes fillProgressAnim { from { width: 0%; } }
                        .progress-bar-animated { animation: fillProgressAnim 1.2s cubic-bezier(0.22, 1, 0.36, 1) forwards; }
                        
                        /* Resplandor KPI Cards */
                        .kpi-card-blue, .kpi-card-yellow, .kpi-card-green, .kpi-card-red {
                            transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1) !important;
                        }
                        .kpi-card-blue:hover { transform: translateY(-4px) !important; box-shadow: 0 12px 20px -8px rgba(37, 99, 235, 0.5) !important; }
                        .kpi-card-yellow:hover { transform: translateY(-4px) !important; box-shadow: 0 12px 20px -8px rgba(245, 158, 11, 0.5) !important; }
                        .kpi-card-green:hover { transform: translateY(-4px) !important; box-shadow: 0 12px 20px -8px rgba(16, 185, 129, 0.5) !important; }
                        .kpi-card-red:hover { transform: translateY(-4px) !important; box-shadow: 0 12px 20px -8px rgba(239, 68, 68, 0.5) !important; }

                        /* Micro-interacción Departamentos */
                        .dept-progress-item {
                            padding: 8px;
                            border-radius: 8px;
                            transition: background-color 0.2s ease;
                        }
                        .dept-progress-item:hover {
                            background-color: #f8fafc;
                        }
                        .dept-percentage {
                            transition: all 0.2s cubic-bezier(0.34, 1.56, 0.64, 1);
                        }
                        .dept-progress-item:hover .dept-percentage {
                            transform: scale(1.15);
                            color: #1e293b !important;
                        }
                    </style>
                    <?php
                    $gradients = [
                        'linear-gradient(90deg, #3b82f6, #8b5cf6)', // Azul a Púrpura (Premium)
                        'linear-gradient(90deg, #10b981, #34d399)', // Esmeralda (Success)
                        'linear-gradient(90deg, #f59e0b, #fbbf24)', // Ámbar (Warning)
                        'linear-gradient(90deg, #ec4899, #f43f5e)', // Rosa a Rojo (Danger/Accent)
                        'linear-gradient(90deg, #6366f1, #a855f7)'  // Índigo a Violeta
                    ];
                    $colorIndex = 0;
                    ?>
                    <?php foreach ($labels as $index => $label): ?>
                        <?php 
                        $porcentaje = $series[$index] ?? 0;
                        $currentGradient = $gradients[$colorIndex % count($gradients)]; 
                        $colorIndex++; 
                        ?>
                        <div class="dept-progress-item mb-4">
                            <div class="d-flex justify-content-between align-items-end mb-1">
                                <span class="fw-bold text-dark" style="font-size: 0.85rem;"><?= htmlspecialchars($label) ?></span>
                                <span class="fw-bold dept-percentage" style="font-size: 0.85rem; color: #475569;"><?= $porcentaje ?>%</span>
                            </div>
                            <div class="progress" style="height: 14px; border-radius: 50rem; background-color: #f1f5f9; box-shadow: inset 0 1px 2px rgba(0,0,0,0.05);">
                                <div class="progress-bar progress-bar-animated" role="progressbar" 
                                     style="width: <?= $porcentaje ?>%; border-radius: 50rem; background: <?= $currentGradient ?>; transition: width 1.5s ease-in-out;" 
                                     aria-valuenow="<?= $porcentaje ?>" aria-valuemin="0" aria-valuemax="100"></div>
                            </div>
                        </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>
    </div>

    <div class="bottom-grid-60-40">
        
        <!-- PANEL IZQUIERDO: Feed de Actividad (Movido de la derecha) -->
        <div class="card figma-card activity-panel" style="padding: 24px; background: #fff; border-radius: 16px; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0; color: #162660; font-size: 1.1rem;">Feed de Actividad</h3>
                <a href="<?= URLROOT ?>/asistencias" style="font-size: 0.8rem; font-weight: 600; color: #2563eb; text-decoration: none;">Ver historial completo</a>
            </div>
            <div class="activity-list-scroll" style="overflow-y: auto; max-height: 380px; scrollbar-width: thin; padding-right: 8px;">
                <?php if (empty($actividadReciente)): ?>
                    <p style="text-align:center;color:#94a3b8;padding:24px;font-size:0.85rem;">Esperando actividad...</p>
                <?php else: ?>
                    <?php foreach (array_slice($actividadReciente, 0, 8) as $a): 
                        $metodo = $a->metodo ?? 'Kiosco'; 
                        $iconMetodo = $metodo === 'Kiosco' ? 'device-desktop' : 'hand-click';
                        $estadoBg = ['Presente' => '#dcfce7', 'Justificado' => '#fef9c3', 'Ausente' => '#fee2e2'];
                        $estadoTxt = ['Presente' => '#16a34a', 'Justificado' => '#ca8a04', 'Ausente' => '#dc2626'];
                        $est = $a->estado ?? 'Presente';
                    ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #F1F5F9;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #EDE9FE; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                <?= strtoupper(substr(trim($a->nombres ?? 'U'), 0, 1)) ?>
                            </div>
                            <div style="line-height: 1.3;">
                                <div style="font-size: 0.85rem; font-weight: 600; color: #1e293b;"><?= htmlspecialchars(trim(($a->nombres ?? '') . ' ' . ($a->apellidos ?? ''))) ?></div>
                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                    <span style="background:<?= $estadoBg[$est] ?? '#f1f5f9' ?>;color:<?= $estadoTxt[$est] ?? '#64748b' ?>;padding:2px 8px;border-radius:6px;font-weight:700;font-size:0.65rem;"><?= htmlspecialchars($est) ?></span>
                                    · <i class="ti ti-<?= $iconMetodo ?>"></i> <?= htmlspecialchars($metodo) ?>
                                    · <span style="color:#94a3b8;"><?= htmlspecialchars($a->hora_registro ?? '') ?></span>
                                </div>
                            </div>
                        </div>
                        <button class="btn-micro" title="Ver" onclick="SGPModal.verUsuario(<?= $a->usuario_id ?? $a->id ?? 0 ?>)"><i class="ti ti-eye"></i></button>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
        </div>

        <!-- PANEL DERECHO: Centro de Acción (Nuevo) -->
        <div class="card figma-card action-center-panel" style="padding: 24px; background: #fff; border-radius: 16px; display: flex; flex-direction: column;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0; color: #162660; font-size: 1.1rem;">Centro de Acción</h3>
                <?php if (!empty($alertas_pendientes)): ?>
                <span class="badge" style="background: #FEF2F2; color: #DC2626; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700;">+<?= count($alertas_pendientes ?? []) ?></span>
                <?php endif; ?>
            </div>
            
            <div class="activity-list-scroll" style="overflow-y: auto; max-height: 380px; scrollbar-width: thin; padding-right: 8px;">
                <?php if (empty($alertas_pendientes)): ?>
                    <div style="text-align:center; padding: 40px 20px;">
                        <div style="width: 50px; height: 50px; background: #F0FDF4; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 15px;">
                            <i class="ti ti-check" style="font-size: 1.5rem; color: #059669;"></i>
                        </div>
                        <p style="margin: 0; color: #1E293B; font-weight: 700; font-size: 0.95rem;">Todo está al día</p>
                        <p style="margin: 5px 0 0; color: #64748B; font-size: 0.8rem;">No hay pasantes pendientes ni próximos a finalizar. ¡Buen trabajo!</p>
                    </div>
                <?php else: ?>
                    <div style="display: flex; flex-direction: column; gap: 12px;">
                    <?php foreach ($alertas_pendientes as $alerta): 
                        $isSinAsignar = ($alerta->estado === 'Sin Asignar' || empty($alerta->estado));
                        $icon = $isSinAsignar ? 'user-exclamation' : 'clock-exclamation';
                        $iconColor = $isSinAsignar ? '#D97706' : '#DC2626';
                        $iconBg = $isSinAsignar ? '#FEF3C7' : '#FEE2E2';
                        $title = htmlspecialchars(trim(($alerta->nombres ?? '') . ' ' . ($alerta->apellidos ?? '')));
                        $desc = $isSinAsignar ? 'Requiere asignación de depto.' : 'Vence pronto: ' . htmlspecialchars($alerta->fecha_fin_estimada);
                        $btnLink = URLROOT . ($isSinAsignar ? '/asignaciones' : '/pasantes');
                    ?>
                        <div style="display: flex; align-items: center; justify-content: space-between; padding: 14px; background: #F8FAFC; border: 1px solid #E2E8F0; border-radius: 12px; transition: all 0.2s;" onmouseover="this.style.borderColor='<?= $iconColor ?>'" onmouseout="this.style.borderColor='#E2E8F0'">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 10px; background: <?= $iconBg ?>; color: <?= $iconColor ?>; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                                    <i class="ti ti-<?= $icon ?>"></i>
                                </div>
                                <div style="line-height: 1.3;">
                                    <div style="font-size: 0.85rem; font-weight: 700; color: #1e293b;"><?= $title ?></div>
                                    <div style="font-size: 0.75rem; color: #64748b; margin-top: 2px;">
                                        <?= $desc ?>
                                    </div>
                                </div>
                            </div>
                            <a href="<?= $btnLink ?>" title="Resolver" style="background: #fff; color: <?= $iconColor ?>; border: 1px solid #E2E8F0; width: 32px; height: 32px; border-radius: 8px; cursor: pointer; transition: all 0.2s; text-decoration: none; display: flex; align-items: center; justify-content: center; box-shadow: 0 1px 2px rgba(0,0,0,0.05);" onmouseover="this.style.background='<?= $iconColor ?>'; this.style.color='#fff';" onmouseout="this.style.background='#fff'; this.style.color='<?= $iconColor ?>';">
                                <i class="ti ti-arrow-right"></i>
                            </a>
                        </div>
                    <?php endforeach; ?>
                    </div>
                <?php endif; ?>
            </div>
        </div>

    </div>

</div>

<script src="<?= URLROOT ?>/js/apexcharts.min.js"></script>
<script src="<?= URLROOT ?>/js/echarts.min.js"></script>

<script>
document.addEventListener("DOMContentLoaded", function () {
    
    // ========================================
    // 2. MONITOR PRINCIPAL (Paleta Analógica)
    // ========================================
    // ── DATA BRIDGE: Obteniendo datos reales del backend ──
    const DashboardData = <?= json_encode($data['metricas_graficos'] ?? []) ?>;
    
    // Extrayendo series dinámicas
    const ObjectDiaria  = DashboardData.asistenciaDiaria || { cat: ['Lun','Mar','Mié','Jue','Vie'], p: [0,0,0,0,0], f: [0,0,0,0,0], j: [0,0,0,0,0] };
    const ObjectSemanal = DashboardData.asistenciaSemanal || { cat: ['Sem 1','Sem 2','Sem 3','Sem 4'], p: [0,0,0,0], f: [0,0,0,0], j: [0,0,0,0] };
    const ObjectMensual = DashboardData.asistenciaMensual || { cat: ['Ene','Feb','Mar','Abr','May','Jun'], p: [0,0,0,0,0,0], f: [0,0,0,0,0,0], j: [0,0,0,0,0,0] };

    const chartHistEl = document.querySelector("#chart-main-history");
    let histChart;
    if (chartHistEl) {
        histChart = new ApexCharts(chartHistEl, {
            series: [
                { name: 'Presentes', data: ObjectDiaria.p },
                { name: 'Justificados', data: ObjectDiaria.j },
                { name: 'Ausentes', data: ObjectDiaria.f }
            ],
            chart: { height: 300, type: 'bar', stacked: false, toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
            plotOptions: { 
                bar: { 
                    horizontal: false, 
                    columnWidth: '45%', 
                    borderRadius: 4, 
                    borderRadiusApplication: 'end' 
                } 
            },
            dataLabels: { enabled: false },
            colors: ['#10B981', '#F59E0B', '#EF4444'],
            grid: { borderColor: '#F1F5F9', strokeDashArray: 4 },
            xaxis: { categories: ObjectDiaria.cat, axisBorder: { show: false }, axisTicks: { show: false } },
            legend: { position: 'top', horizontalAlign: 'right' }
        });
        histChart.render();

        // Toggles Tiempo
        const updateTimeData = (btnId, dataObj) => {
            document.getElementById(btnId).addEventListener('click', (e) => {
                histChart.updateOptions({ xaxis: { categories: dataObj.cat } });
                histChart.updateSeries([
                    { name: 'Presentes', data: dataObj.p }, 
                    { name: 'Justificados', data: dataObj.j }, 
                    { name: 'Ausentes', data: dataObj.f }
                ]);
                document.querySelectorAll('.time-toggles .btn-micro').forEach(b => b.classList.remove('active'));
                e.currentTarget.classList.add('active');
            });
        };
        updateTimeData('btnTimeDay', ObjectDiaria);
        updateTimeData('btnTimeWeek', ObjectSemanal);
        updateTimeData('btnTimeMonth', ObjectMensual);
    }



    // ========================================
    // ACTUALIZAR RELOJ Y FECHA
    // ========================================
    function updateDateTime() {
        const now = new Date();
        const dateStr = now.toLocaleDateString('es-ES', { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' });
        const timeStr = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
        document.getElementById('currentDate').textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
        document.getElementById('currentTime').textContent = timeStr;
    }
    updateDateTime(); setInterval(updateDateTime, 1000);

    // ========================================
    // ALERTAS DE ÉXITO/ERROR (NOTIFICACIONES)
    // ========================================
    // Las notificaciones usan Session::getFlash y se muestran vía Notyf
    document.addEventListener('DOMContentLoaded', function() {
        <?php if ($success = Session::getFlash('success')): ?>
            if (typeof NotificationService !== 'undefined') { NotificationService.success('<?= addslashes($success) ?>'); }
        <?php endif; ?>
        <?php if ($error = Session::getFlash('error')): ?>
            if (typeof NotificationService !== 'undefined') { NotificationService.error('<?= addslashes($error) ?>'); }
        <?php endif; ?>
        // También mantenemos los nombres flash anteriores por retrocompatibilidad
        <?php if (isset($_SESSION['flash_success'])): ?>
            if (typeof NotificationService !== 'undefined') { NotificationService.success('<?= addslashes($_SESSION['flash_success']) ?>'); }
            <?php unset($_SESSION['flash_success']); ?>
        <?php endif; ?>
        <?php if (isset($_SESSION['flash_error'])): ?>
            if (typeof NotificationService !== 'undefined') { NotificationService.error('<?= addslashes($_SESSION['flash_error']) ?>'); }
            <?php unset($_SESSION['flash_error']); ?>
        <?php endif; ?>
    });
});
</script>
