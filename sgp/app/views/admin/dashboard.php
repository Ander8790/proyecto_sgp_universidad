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
        <div style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid #2563eb;display:flex;justify-content:space-between;align-items:center;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px rgba(37,99,235,0.15)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="flex:1;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Activos</p>
                <div style="display:flex;align-items:center;gap:12px;">
                    <h2 style="font-size:2.4rem;font-weight:800;color:#2563eb;margin:0;line-height:1;"><?= $totalActivos ?></h2>
                    <div style="width: 55px;"><div id="chart-kpi-1"></div></div>
                </div>
            </div>
            <a href="<?= URLROOT ?>/pasantes" style="background:#eff6ff;color:#2563eb;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'" title="Directorio">
                <i class="ti ti-users"></i>
            </a>
        </div>

        <div style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid #F59E0B;display:flex;justify-content:space-between;align-items:center;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px rgba(245,158,11,0.15)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="flex:1;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Por Asignar</p>
                <div style="display:flex;align-items:center;gap:12px;">
                    <h2 style="font-size:2.4rem;font-weight:800;color:#F59E0B;margin:0;line-height:1;"><?= $pendientesAsignar ?></h2>
                    <div style="width: 55px;"><div id="chart-kpi-2"></div></div>
                </div>
            </div>
            <a href="<?= URLROOT ?>/asignaciones" style="background:#FFFBEB;color:#D97706;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'" title="Asignar">
                <i class="ti ti-link"></i>
            </a>
        </div>

        <div style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid #059669;display:flex;justify-content:space-between;align-items:center;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px rgba(5,150,105,0.15)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="flex:1;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">En Sitio Hoy</p>
                <div style="display:flex;align-items:center;gap:12px;">
                    <h2 style="font-size:2.4rem;font-weight:800;color:#059669;margin:0;line-height:1;"><?= $asistenciasHoy ?></h2>
                    <div style="width: 55px;"><div id="chart-kpi-3"></div></div>
                </div>
            </div>
            <a href="<?= URLROOT ?>/asistencias" style="background:#F0FDF4;color:#059669;width:48px;height:48px;border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.5rem;transition:transform 0.2s;" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='none'" title="Registros">
                <i class="ti ti-fingerprint"></i>
            </a>
        </div>

        <div style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid #DC2626;display:flex;justify-content:space-between;align-items:center;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px rgba(220,38,38,0.15)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="flex:1;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Faltas Hoy</p>
                <div style="display:flex;align-items:center;gap:12px;">
                    <h2 style="font-size:2.4rem;font-weight:800;color:#DC2626;margin:0;line-height:1;"><?= $faltasHoy ?></h2>
                    <div style="width: 55px;"><div id="chart-kpi-4"></div></div>
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
                    <div class="chart-toggles history-toggles" style="display: flex; gap: 4px; background: #F1F5F9; padding: 4px; border-radius: 8px;">
                        <button id="btnHistBar" class="btn-micro active" title="Barras"><i class="ti ti-chart-bar"></i></button>
                        <button id="btnHistArea" class="btn-micro" title="Montañas"><i class="ti ti-chart-area-line"></i></button>
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
                <div class="chart-toggles depto-toggles" style="display: flex; gap: 4px; background: #F5F3FF; padding: 4px; border-radius: 8px; border: 1px solid #EDE9FE;">
                    <button id="btnDepRadial" class="btn-micro active" title="Radial (Caracol)"><i class="ti ti-chart-radar"></i></button>
                    <button id="btnDepDonut" class="btn-micro" title="Dona"><i class="ti ti-chart-donut"></i></button>
                    <button id="btnDepGauge" class="btn-micro" title="Velocímetro"><i class="ti ti-gauge"></i></button>
                </div>
            </div>
            <div class="chart-wrapper">
                <div id="chart-asistencia-depto"></div>
                <div id="chart-gauge-container" style="display:none; width:100%; height:280px;"></div>
            </div>
            <!-- Leyenda del Velocímetro (se muestra solo en modo gauge) -->
            <div id="gauge-legend" class="gauge-legend">
                <div class="gauge-legend-chip"><span class="gauge-legend-dot" style="background:#A78BFA;"></span>Soporte <strong>85%</strong></div>
                <div class="gauge-legend-chip"><span class="gauge-legend-dot" style="background:#34D399;"></span>Redes <strong>72%</strong></div>
                <div class="gauge-legend-chip"><span class="gauge-legend-dot" style="background:#FBBF24;"></span>Atención <strong>91%</strong></div>
                <div class="gauge-legend-chip"><span class="gauge-legend-dot" style="background:#60A5FA;"></span>Reparación <strong>64%</strong></div>
            </div>
        </div>
    </div>

    <div class="bottom-grid-60-40">
        <div class="card figma-card" style="padding: 24px; background: #fff; border-radius: 16px;">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 15px;">
                <h3 style="margin: 0; color: #162660; font-size: 1.1rem;">Últimos Registros</h3>
                <a href="<?= URLROOT ?>/asistencias" style="font-size: 0.8rem; font-weight: 600; color: #2563eb; text-decoration: none;">Ver historial</a>
            </div>
            <div class="table-container-scroll">
                <table class="modern-table-compact" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr style="border-bottom: 2px solid #f1f5f9; text-align: left;">
                            <th style="padding: 12px 8px; font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Pasante</th>
                            <th style="padding: 12px 8px; font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Hora</th>
                            <th style="padding: 12px 8px; font-size: 0.75rem; color: #64748b; text-transform: uppercase;">Estado</th>
                            <th style="padding: 12px 8px; font-size: 0.75rem; color: #64748b; text-transform: uppercase; text-align: right;">Acciones</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php if (empty($actividadReciente)): ?>
                        <tr><td colspan="4" style="text-align:center;color:#94a3b8;padding:24px;">Sin registros hoy</td></tr>
                    <?php else: ?>
                        <?php foreach (array_slice($actividadReciente, 0, 8) as $a):
                            $nomFull= trim(($a->nombres ?? 'Usuario') . ' ' . ($a->apellidos ?? ''));
                            $hora = isset($a->hora_registro) ? substr($a->hora_registro, 0, 5) : date('H:i');
                            $estado = $a->estado ?? 'Presente';
                            $badgeColor = $estado === 'Presente' ? '#059669' : ($estado === 'Justificado' ? '#F59E0B' : '#DC2626');
                            $badgeBg = $estado === 'Presente' ? '#D1FAE5' : ($estado === 'Justificado' ? '#FEF3C7' : '#FEE2E2');
                        ?>
                        <tr style="border-bottom: 1px solid #f8fafc;">
                            <td style="padding: 12px 8px; font-size: 0.85rem; font-weight: 600; color: #1e293b;"><?= htmlspecialchars($nomFull) ?></td>
                            <td style="padding: 12px 8px; color:#64748b; font-size: 0.85rem;"><?= $hora ?></td>
                            <td style="padding: 12px 8px;">
                                <span style="background: <?= $badgeBg ?>; color: <?= $badgeColor ?>; padding: 4px 8px; border-radius: 6px; font-size: 0.7rem; font-weight: 700;"><?= htmlspecialchars($estado) ?></span>
                            </td>
                            <td style="padding: 12px 8px; text-align: right;">
                                <div class="btn-group" style="display: flex; gap: 8px; justify-content: flex-end;">
                                    <button class="btn-action btn-view" title="Consultar Rápido" onclick="SGPModal.verUsuario(<?= $a->usuario_id ?? $a->id ?? 0 ?>)" style="background:#f1f5f9; color:#475569; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; transition:all 0.2s;"><i class="ti ti-eye"></i></button>
                                    <button class="btn-action btn-edit" title="Ajustar Registro" style="background:#eff6ff; color:#2563eb; border:none; padding:6px 10px; border-radius:8px; cursor:pointer; transition:all 0.2s;"><i class="ti ti-pencil"></i></button>
                                </div>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    <?php endif; ?>
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card figma-card activity-panel" style="padding: 24px; background: #fff; border-radius: 16px; display: flex; flex-direction: column;">
            <h3 style="margin: 0 0 15px 0; color: #162660; font-size: 1.1rem;">Feed de Actividad</h3>
            <div class="activity-list-scroll" style="overflow-y: auto; max-height: 380px; scrollbar-width: thin;">
                <?php if (empty($actividadReciente)): ?>
                    <p style="text-align:center;color:#94a3b8;padding:24px;font-size:0.85rem;">Esperando actividad...</p>
                <?php else: ?>
                    <?php foreach (array_slice($actividadReciente, 0, 8) as $a): 
                        $metodo = $a->metodo ?? 'Kiosco'; 
                        $iconMetodo = $metodo === 'Kiosco' ? 'device-desktop' : 'hand-click';
                    ?>
                    <div style="display: flex; align-items: center; justify-content: space-between; padding: 12px 0; border-bottom: 1px solid #F1F5F9;">
                        <div style="display: flex; align-items: center; gap: 12px;">
                            <div style="width: 36px; height: 36px; border-radius: 10px; background: #EDE9FE; color: #7C3AED; display: flex; align-items: center; justify-content: center; font-weight: 700;">
                                <?= strtoupper(substr($a->nombres ?? 'U', 0, 1)) ?>
                            </div>
                            <div style="line-height: 1.3;">
                                <div style="font-size: 0.85rem; font-weight: 600; color: #1e293b;"><?= htmlspecialchars(trim(($a->nombres ?? '') . ' ' . ($a->apellidos ?? ''))) ?></div>
                                <div style="font-size: 0.7rem; color: #64748b; margin-top: 2px;">
                                    <?php
                                    $estadoBg = ['Presente' => '#dcfce7', 'Justificado' => '#fef9c3', 'Ausente' => '#fee2e2'];
                                    $estadoTxt = ['Presente' => '#16a34a', 'Justificado' => '#ca8a04', 'Ausente' => '#dc2626'];
                                    $est = $a->estado ?? 'Presente';
                                    ?>
                                    <span style="background:<?= $estadoBg[$est] ?? '#f1f5f9' ?>;color:<?= $estadoTxt[$est] ?? '#64748b' ?>;padding:2px 8px;border-radius:6px;font-weight:700;font-size:0.65rem;"><?= htmlspecialchars($est) ?></span>
                                    · <i class="ti ti-<?= $iconMetodo ?>"></i> Vía <?= htmlspecialchars($metodo) ?>
                                    · <span style="color:#94a3b8;"><?= htmlspecialchars($a->hora_registro ?? '') ?></span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
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
    // 1. MINI RADIAL CHARTS (KPIs)
    // ========================================
    function createRadialKPI(id, value, color) {
        var options = {
            series: [value],
            chart: { height: 60, width: 60, type: 'radialBar', sparkline: { enabled: true } },
            plotOptions: { radialBar: { hollow: { size: '45%' }, track: { background: '#F1F5F9' }, dataLabels: { name: { show: false }, value: { show: true, fontSize: '11px', fontWeight: 700, color: '#1e293b', offsetY: 4, formatter: function(val) { return Math.round(val) + '%'; } } } } },
            stroke: { lineCap: 'round' }, fill: { colors: [color] }
        };
        new ApexCharts(document.querySelector(id), options).render();
    }
    // KPIs con PORCENTAJES REALES (clamped 0-100)
    var kpiBase1 = Math.max(<?= $totalActivos ?> + <?= $pendientesAsignar ?>, 1);
    var kpiBase2 = Math.max(<?= $asistenciasHoy ?> + <?= $faltasHoy ?>, 1);
    createRadialKPI("#chart-kpi-1", Math.min(100, Math.round((<?= $totalActivos ?> / kpiBase1) * 100)), "#2563eb");
    createRadialKPI("#chart-kpi-2", Math.min(100, Math.round((<?= $pendientesAsignar ?> / kpiBase1) * 100)), "#F59E0B");
    createRadialKPI("#chart-kpi-3", Math.min(100, Math.round((<?= $asistenciasHoy ?> / kpiBase2) * 100)), "#059669");
    createRadialKPI("#chart-kpi-4", Math.min(100, Math.round((<?= $faltasHoy ?> / kpiBase2) * 100)), "#DC2626");

    // ========================================
    // 2. MONITOR PRINCIPAL (Paleta Analógica)
    // ========================================
    // Datos simulados para granularidad
    const dataDiaria = { cat: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie'], p: [45, 52, 38, 40, 55], f: [5, 2, 8, 3, 1], j: [2, 1, 0, 4, 2] };
    const dataSemanal = { cat: ['Sem 1', 'Sem 2', 'Sem 3', 'Sem 4'], p: [210, 225, 198, 240], f: [15, 8, 22, 5], j: [8, 12, 5, 10] };
    const dataMensual = { cat: ['Ene', 'Feb', 'Mar', 'Abr', 'May'], p: [850, 920, 880, 950, 910], f: [45, 30, 60, 20, 35], j: [20, 40, 15, 30, 25] };

    const chartHistEl = document.querySelector("#chart-main-history");
    let histChart;
    if (chartHistEl) {
        histChart = new ApexCharts(chartHistEl, {
            series: [
                { name: 'Asistentes', data: dataDiaria.p },
                { name: 'Inasistentes', data: dataDiaria.f },
                { name: 'Justificados', data: dataDiaria.j }
            ],
            chart: { height: 300, type: 'bar', toolbar: { show: false }, fontFamily: 'Plus Jakarta Sans, sans-serif' },
            plotOptions: { bar: { borderRadius: 8, columnWidth: '55%' } },
            dataLabels: { enabled: false },
            colors: ['#2563eb', '#DC2626', '#F59E0B'],
            stroke: { curve: 'smooth', width: 2 },
            grid: { borderColor: '#F1F5F9', strokeDashArray: 4 },
            xaxis: { categories: dataDiaria.cat, axisBorder: { show: false }, axisTicks: { show: false } },
            legend: { position: 'top', horizontalAlign: 'right' }
        });
        histChart.render();

        // Toggles Forma de Gráfica
        document.getElementById('btnHistBar').addEventListener('click', (e) => {
            histChart.updateOptions({ chart: { type: 'bar' } });
            document.querySelectorAll('.history-toggles .btn-micro').forEach(b => b.classList.remove('active'));
            e.currentTarget.classList.add('active');
        });
        document.getElementById('btnHistArea').addEventListener('click', (e) => {
            histChart.updateOptions({ chart: { type: 'area' } });
            document.querySelectorAll('.history-toggles .btn-micro').forEach(b => b.classList.remove('active'));
            e.currentTarget.classList.add('active');
        });

        // Toggles Tiempo
        const updateTimeData = (btnId, dataObj) => {
            document.getElementById(btnId).addEventListener('click', (e) => {
                histChart.updateOptions({ xaxis: { categories: dataObj.cat } });
                histChart.updateSeries([{ data: dataObj.p }, { data: dataObj.f }, { data: dataObj.j }]);
                document.querySelectorAll('.time-toggles .btn-micro').forEach(b => b.classList.remove('active'));
                e.currentTarget.classList.add('active');
            });
        };
        updateTimeData('btnTimeDay', dataDiaria);
        updateTimeData('btnTimeWeek', dataSemanal);
        updateTimeData('btnTimeMonth', dataMensual);
    }

    // ========================================
    // 3. GRÁFICA DEPARTAMENTOS (3 Modos + Velocímetro)
    // ========================================
    const chartDepEl = document.querySelector("#chart-asistencia-depto");
    let depChart;
    if (chartDepEl) {
        const depColors = ['#A78BFA', '#34D399', '#FBBF24', '#60A5FA']; // Paleta Pastel Divergente
        const depLabels = ['Soporte', 'Redes', 'Atención', 'Reparación'];
        const depData = [85, 72, 91, 64];

        const depOptions = {
            series: depData,
            labels: depLabels,
            chart: {
                height: 320,
                type: 'radialBar',
                fontFamily: 'Plus Jakarta Sans, sans-serif',
                dropShadow: { enabled: true, top: 3, left: 0, blur: 10, color: '#A78BFA', opacity: 0.12 }
            },
            colors: depColors,
            fill: { type: 'solid' },
            plotOptions: {
                radialBar: {
                    offsetY: 5,
                    hollow: { size: '30%', background: 'transparent' },
                    track: { background: '#F1F5F9', margin: 12, strokeWidth: '90%', dropShadow: { enabled: true, top: 2, left: 0, blur: 4, opacity: 0.06 } },
                    dataLabels: { show: false }
                }
            },
            states: {
                hover: { filter: { type: 'none' } },
                active: { filter: { type: 'none' } }
            },
            legend: {
                show: true,
                floating: true,
                position: 'right',
                verticalAlign: 'top',
                fontSize: '13px',
                fontFamily: 'inherit',
                fontWeight: 600,
                offsetY: 8,
                labels: { colors: '#334155' },
                markers: { width: 10, height: 10, radius: 10, offsetX: -4 },
                itemMargin: { vertical: 6 },
                formatter: function (seriesName, opts) {
                    var val = opts.w.globals.series[opts.seriesIndex];
                    return '<span style="margin-left:4px">' + seriesName + '</span>  <strong style="color:#162660">' + val + '%</strong>';
                }
            },
            tooltip: { enabled: true, y: { formatter: (val) => val + '% asistencia' } },
            stroke: { lineCap: 'round' }
        };
        depChart = new ApexCharts(chartDepEl, depOptions);
        depChart.render();

        // LA JOYA DE LA CORONA: EL VELOCÍMETRO (ECharts Gauge Real)
        let echartsGauge = null;
        document.getElementById('btnDepGauge').addEventListener('click', (e) => {
            document.getElementById('chart-asistencia-depto').style.display = 'none';
            var gaugeEl = document.getElementById('chart-gauge-container');
            gaugeEl.style.display = 'block';
            document.getElementById('gauge-legend').classList.add('active');

            // Encontrar el departamento con mayor asistencia
            var maxIdx = depData.indexOf(Math.max(...depData));
            var gaugeValue = depData[maxIdx];
            var gaugeLabel = depLabels[maxIdx];

            if (echartsGauge) { echartsGauge.dispose(); }
            echartsGauge = echarts.init(gaugeEl);

            var gaugeOption = {
                series: [{
                    type: 'gauge',
                    startAngle: 200,
                    endAngle: -20,
                    min: 0,
                    max: 100,
                    splitNumber: 10,
                    itemStyle: { color: '#162660' },
                    progress: {
                        show: false
                    },
                    pointer: {
                        show: true,
                        length: '55%',
                        width: 8,
                        itemStyle: { color: '#162660' }
                    },
                    axisLine: {
                        roundCap: true,
                        lineStyle: {
                            width: 18,
                            color: [
                                [0.25, '#A78BFA'],
                                [0.50, '#34D399'],
                                [0.75, '#FBBF24'],
                                [1.00, '#60A5FA']
                            ]
                        }
                    },
                    axisTick: {
                        distance: -22,
                        splitNumber: 5,
                        lineStyle: { width: 1, color: '#CBD5E1' }
                    },
                    splitLine: {
                        distance: -26,
                        length: 10,
                        lineStyle: { width: 2, color: '#94A3B8' }
                    },
                    axisLabel: {
                        distance: -16,
                        color: '#94A3B8',
                        fontSize: 10
                    },
                    anchor: {
                        show: true,
                        showAbove: true,
                        size: 18,
                        itemStyle: {
                            borderWidth: 6,
                            borderColor: '#162660',
                            color: '#fff'
                        }
                    },
                    title: {
                        show: true,
                        offsetCenter: [0, '70%'],
                        fontSize: 13,
                        color: '#64748B',
                        fontWeight: 600
                    },
                    detail: {
                        valueAnimation: true,
                        fontSize: 32,
                        fontWeight: 800,
                        offsetCenter: [0, '45%'],
                        color: '#162660',
                        formatter: '{value}%'
                    },
                    data: [{ value: gaugeValue, name: gaugeLabel + ' (Líder)' }]
                }]
            };

            echartsGauge.setOption(gaugeOption);
            // Responsive
            window.addEventListener('resize', function() { if(echartsGauge) echartsGauge.resize(); });

            updateActiveToggle(e, '.depto-toggles');
        });

        // Volver de ECharts a ApexCharts (Radial y Dona)
        var restoreApex = function() {
            document.getElementById('chart-asistencia-depto').style.display = 'block';
            document.getElementById('chart-gauge-container').style.display = 'none';
            document.getElementById('gauge-legend').classList.remove('active');
            if (echartsGauge) { echartsGauge.dispose(); echartsGauge = null; }
        };

        document.getElementById('btnDepRadial').addEventListener('click', (e) => {
            restoreApex();
            depChart.updateOptions({ series: depData, labels: depLabels, chart: { type: 'radialBar' }, plotOptions: depOptions.plotOptions, legend: depOptions.legend });
            updateActiveToggle(e, '.depto-toggles');
        });
        document.getElementById('btnDepDonut').addEventListener('click', (e) => {
            restoreApex();
            depChart.updateOptions({ series: depData, labels: depLabels, chart: { type: 'donut' }, plotOptions: { pie: { donut: { size: '75%', labels: { show: true, total: { show: true, label: 'Total', formatter: ()=> '312' } } } } }, legend: depOptions.legend });
            updateActiveToggle(e, '.depto-toggles');
        });

        function updateActiveToggle(event, containerClass) {
            document.querySelectorAll(containerClass + ' .btn-micro').forEach(b => b.classList.remove('active'));
            event.currentTarget.classList.add('active');
        }
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
