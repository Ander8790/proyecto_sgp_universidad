<?php
// Vista exclusiva para Tutores — SGP Premium Dashboard v2
$user_name               = $data['user_name']               ?? 'Tutor';
$totalPasantes           = $data['totalPasantes']           ?? 0;
$pasantesActivos         = $data['pasantesActivos']         ?? 0;
$evaluacionesPendientes  = $data['evaluacionesPendientes']  ?? 0;
$horasSupervisadas       = $data['horasSupervisadas']       ?? 0;
$misPasantes             = $data['misPasantes']             ?? [];
?>

<style>
/* === TUTOR DASHBOARD — Design System SGP === */
.dashboard-bento { display:flex; flex-direction:column; gap:24px; width:100%; }
.dashboard-kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:20px; width:100%; }

@media(max-width:1200px){ .dashboard-kpi-grid{grid-template-columns:repeat(2,1fr);} }
@media(max-width:768px) { .dashboard-kpi-grid{grid-template-columns:1fr;} }

/* KPI Cards — mismo patrón Admin */
.kpi-card-sgp {
    background:#fff;
    border-radius:16px;
    padding:22px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    display:flex;
    justify-content:space-between;
    align-items:center;
    transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.kpi-card-sgp:hover { transform:translateY(-4px); }
.kpi-card-sgp.kpi-indigo  { border-left:4px solid #6366f1; }
.kpi-card-sgp.kpi-green   { border-left:4px solid #059669; }
.kpi-card-sgp.kpi-amber   { border-left:4px solid #F59E0B; }
.kpi-card-sgp.kpi-violet  { border-left:4px solid #7c3aed; }
.kpi-card-sgp:hover.kpi-indigo { box-shadow:0 12px 25px rgba(99,102,241,0.25); }
.kpi-card-sgp:hover.kpi-green  { box-shadow:0 12px 25px rgba(5,150,105,0.25); }
.kpi-card-sgp:hover.kpi-amber  { box-shadow:0 12px 25px rgba(245,158,11,0.25); }
.kpi-card-sgp:hover.kpi-violet { box-shadow:0 12px 25px rgba(124,58,237,0.25); }
.kpi-left p  { color:#64748b; font-size:0.82rem; margin:0 0 8px; font-weight:600; text-transform:uppercase; letter-spacing:0.5px; }
.kpi-left h2 { font-size:2.4rem; font-weight:800; margin:0; line-height:1; }
.kpi-icon-box { width:48px; height:48px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.5rem; flex-shrink:0; transition:transform 0.2s; }
.kpi-icon-box:hover { transform:scale(1.1); }
/* Pasantes table (.figma-card style) */
.figma-card { background:#fff; border-radius:16px; padding:24px; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
.figma-card-header { display:flex; justify-content:space-between; align-items:center; margin-bottom:20px; }
.figma-card-header h3 { margin:0; color:#162660; font-size:1.1rem; font-weight:700; }
.sgp-table { width:100%; border-collapse:collapse; }
.sgp-table th { padding:10px 14px; font-size:0.75rem; font-weight:600; text-transform:uppercase; letter-spacing:0.04em; color:#64748b; background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0; }
.sgp-table td { padding:12px 14px; font-size:0.875rem; border-bottom:1px solid #f1f5f9; color:#1e293b; vertical-align:middle; }
.sgp-table tr:last-child td { border-bottom:none; }
.sgp-table tr:hover td { background:#f8faff; }
.sgp-bar { height:6px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin-top:4px; min-width:80px; }
.sgp-bar-fill { height:100%; border-radius:999px; background:linear-gradient(90deg,#6366f1,#8b5cf6); }
.sgp-badge { display:inline-block; padding:2px 10px; border-radius:999px; font-size:0.72rem; font-weight:600; }
.sgp-badge.activo     { background:rgba(5,150,105,.12); color:#059669; }
.sgp-badge.pendiente  { background:rgba(245,158,11,.12); color:#d97706; }
.sgp-badge.finalizado { background:rgba(99,102,241,.12); color:#6366f1; }
.sgp-badge.retirado   { background:rgba(239,68,68,.12); color:#ef4444; }
.sgp-badge.default    { background:rgba(100,116,139,.12); color:#64748b; }
.btn-micro { background:transparent; border:none; padding:6px; border-radius:6px; cursor:pointer; color:#64748b; transition:all 0.2s; }
.btn-micro:hover { background:#e2e8f0; color:#1e293b; }
</style>

<div class="dashboard-bento">

    <!-- ══ BANNER TUTOR (Azul Institucional) ══ -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:14px;">
                <i class="ti ti-school" style="font-size:32px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.8rem;font-weight:700;margin:0;">¡Bienvenido, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>!</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;">
                    <i class="ti ti-layout-dashboard"></i> Panel de Control · Tutor
                </p>
            </div>
        </div>
        <div style="display:flex;z-index:1;align-items:center;">
            <div style="background:rgba(0,0,0,0.15);backdrop-filter:blur(10px);border:1px solid rgba(255,255,255,0.1);border-radius:50px;padding:10px 24px;display:flex;align-items:center;gap:20px;color:white;">
                <div style="display:flex;align-items:center;gap:8px;">
                    <i class="ti ti-calendar" style="color:rgba(255,255,255,0.8);font-size:1.1rem;"></i>
                    <span id="currentDate" style="font-size:0.9rem;font-weight:500;letter-spacing:0.5px;"></span>
                </div>
                <div style="width:1px;height:24px;background:rgba(255,255,255,0.2);"></div>
                <div style="display:flex;align-items:center;gap:8px;">
                    <i class="ti ti-clock" style="color:rgba(255,255,255,0.8);font-size:1.2rem;"></i>
                    <span id="currentTime" style="font-size:1.1rem;font-weight:700;letter-spacing:0.5px;"></span>
                </div>
            </div>
        </div>
    </div>

    <!-- ══ KPIs (4 tarjetas) ══ -->
    <div class="dashboard-kpi-grid">
        <!-- Total Pasantes -->
        <div class="kpi-card-sgp kpi-indigo">
            <div class="kpi-left">
                <p>Mis Pasantes</p>
                <h2 style="color:#6366f1;" data-kpi-value="<?= $totalPasantes ?>"><?= $totalPasantes ?></h2>
            </div>
            <a href="<?= URLROOT ?>/tutor/pasantes" class="kpi-icon-box" style="background:#ede9fe;color:#6366f1;" title="Ver pasantes">
                <i class="ti ti-users-group"></i>
            </a>
        </div>

        <!-- Activos -->
        <div class="kpi-card-sgp kpi-green">
            <div class="kpi-left">
                <p>Activos</p>
                <h2 style="color:#059669;" data-kpi-value="<?= $pasantesActivos ?>"><?= $pasantesActivos ?></h2>
            </div>
            <a href="<?= URLROOT ?>/tutor/asistencias" class="kpi-icon-box" style="background:#d1fae5;color:#059669;" title="Ver asistencias">
                <i class="ti ti-clock-check"></i>
            </a>
        </div>

        <!-- Evaluaciones Pendientes -->
        <div class="kpi-card-sgp kpi-amber">
            <div class="kpi-left">
                <p>Sin Evaluar</p>
                <h2 style="color:#d97706;" data-kpi-value="<?= $evaluacionesPendientes ?>"><?= $evaluacionesPendientes ?></h2>
            </div>
            <a href="<?= URLROOT ?>/evaluaciones" class="kpi-icon-box" style="background:#fef3c7;color:#d97706;" title="Ir a evaluaciones">
                <i class="ti ti-star"></i>
            </a>
        </div>

        <!-- Horas Supervisadas (pro-rata) -->
        <div class="kpi-card-sgp kpi-violet">
            <div class="kpi-left">
                <p>Horas Supervisadas</p>
                <h2 style="color:#7c3aed;" data-kpi-value="<?= $horasSupervisadas ?>"><?= $horasSupervisadas ?></h2>
            </div>
            <a href="<?= URLROOT ?>/tutor/puntualidad" class="kpi-icon-box" style="background:#ede9fe;color:#7c3aed;" title="Dashboard de Puntualidad">
                <i class="ti ti-clock-exclamation"></i>
            </a>
        </div>
    </div>

    <!-- ══ TABLA PASANTES (.figma-card) ══ -->
    <div class="figma-card">
        <div class="figma-card-header">
            <div>
                <h3>Mis Pasantes Asignados</h3>
                <span style="font-size:0.8rem;color:#94a3b8;">Progreso y estado en tiempo real</span>
            </div>
            <a href="<?= URLROOT ?>/tutor/pasantes" style="font-size:0.82rem;font-weight:600;color:#6366f1;text-decoration:none;">Ver todos →</a>
        </div>

        <?php if (empty($misPasantes)): ?>
        <div style="text-align:center;padding:48px 24px;color:#94a3b8;">
            <i class="ti ti-users-off" style="font-size:40px;display:block;margin-bottom:12px;opacity:.4;"></i>
            <p>No tienes pasantes asignados aún.</p>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
        <table class="sgp-table">
            <thead>
                <tr>
                    <th>Pasante</th>
                    <th>Departamento</th>
                    <th style="min-width:160px;">Progreso</th>
                    <th>Promedio</th>
                    <th>Estado</th>
                    <th></th>
                </tr>
            </thead>
            <tbody>
            <?php foreach ($misPasantes as $p):
                $nombre   = trim(($p->nombres ?? '') . ' ' . ($p->apellidos ?? ''));
                $iniciales= strtoupper(substr($p->nombres ?? 'P', 0, 1) . substr($p->apellidos ?? 'A', 0, 1));
                $pct      = (float)($p->progreso_pct ?? 0);
                $badgeKey = strtolower($p->estado_pasantia ?? '');
                $badgeClass = match($p->estado_pasantia ?? '') {
                    'Activo'     => 'activo',
                    'Pendiente'  => 'pendiente',
                    'Finalizado' => 'finalizado',
                    'Retirado'   => 'retirado',
                    default      => 'default',
                };
            ?>
            <tr>
                <td>
                    <div style="display:flex;align-items:center;gap:10px;">
                        <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#6366f1,#8b5cf6);color:#fff;display:flex;align-items:center;justify-content:center;font-weight:700;font-size:.8rem;flex-shrink:0;"><?= $iniciales ?></div>
                        <div>
                            <div style="font-weight:600;"><?= htmlspecialchars($nombre) ?></div>
                            <div style="font-size:.75rem;color:#94a3b8;">V-<?= htmlspecialchars($p->cedula ?? '—') ?></div>
                        </div>
                    </div>
                </td>
                <td style="color:#64748b;"><?= htmlspecialchars($p->departamento ?? '—') ?></td>
                <td>
                    <div style="display:flex;justify-content:space-between;font-size:.78rem;color:#64748b;margin-bottom:3px;">
                        <span><?= (int)($p->horas_acumuladas ?? 0) ?>h / <?= (int)($p->horas_meta ?? 1440) ?>h</span>
                        <span style="font-weight:600;color:#6366f1;"><?= $pct ?>%</span>
                    </div>
                    <div class="sgp-bar"><div class="sgp-bar-fill" style="width:<?= $pct ?>%;"></div></div>
                </td>
                <td style="color:#1e293b;font-weight:600;">
                    <?= $p->promedio_eval ? number_format($p->promedio_eval, 2) . '<span style="font-size:.75rem;color:#94a3b8;">/5</span>' : '<span style="color:#94a3b8;">—</span>' ?>
                </td>
                <td><span class="sgp-badge <?= $badgeClass ?>"><?= htmlspecialchars($p->estado_pasantia ?? 'N/A') ?></span></td>
                <td>
                    <a href="<?= URLROOT ?>/tutor/perfil/<?= $p->pasante_id ?>" class="btn-micro" title="Ver perfil">
                        <i class="ti ti-eye"></i>
                    </a>
                </td>
            </tr>
            <?php endforeach; ?>
            </tbody>
        </table>
        </div>
        <?php endif; ?>
    </div>

</div>

<script>
function updateDateTime() {
    const now = new Date();
    const dateStr = now.toLocaleDateString('es-ES', { weekday:'long', year:'numeric', month:'long', day:'numeric' });
    const timeStr = now.toLocaleTimeString('es-ES', { hour:'2-digit', minute:'2-digit', second:'2-digit', hour12:true });
    const d = document.getElementById('currentDate');
    const t = document.getElementById('currentTime');
    if (d) d.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
    if (t) t.textContent = timeStr.toUpperCase();
}
updateDateTime(); setInterval(updateDateTime, 1000);
</script>
