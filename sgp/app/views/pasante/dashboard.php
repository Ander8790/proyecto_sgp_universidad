<?php
// Vista exclusiva para Pasantes
$user_name = $data['user_name'] ?? 'Pasante';
$role = $data['role'] ?? 'Pasante';
?>

<!-- === ESTILOS DASHBOARD PASANTE === -->
<style>
.dashboard-bento { display: flex !important; flex-direction: column !important; gap: 24px; width: 100%; }
.dashboard-kpi-grid { display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; width: 100%; align-items: stretch;}

@media (max-width: 1200px) {
    .dashboard-kpi-grid { grid-template-columns: repeat(2, 1fr); }
}
@media (max-width: 768px) {
    .dashboard-kpi-grid { grid-template-columns: 1fr; }
}

/* === KPI CARDS WITH HOVER === */
.kpi-card { display: flex; flex-direction: column; justify-content: space-between; padding: 22px; background: #fff; border-radius: 16px; box-shadow: 0 4px 15px rgba(22, 38, 96, 0.05); transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1); border-left: 5px solid transparent; min-height: 140px;}
.kpi-card:hover { transform: translateY(-4px); box-shadow: 0 12px 25px rgba(0,0,0,0.1); border-left-color: #2563eb; }
.kpi-header { display: flex; justify-content: space-between; align-items: center; width: 100%; margin-bottom: 12px; }
.kpi-label { font-size: 0.85rem; color: #64748b; font-weight: 700; text-transform: uppercase; letter-spacing: 0.5px; }
.kpi-icon { width: 40px; height: 40px; display: flex; align-items: center; justify-content: center; background: rgba(37, 99, 235, 0.1); color: #2563eb; border-radius: 10px; font-size: 1.2rem; }
.kpi-value { font-size: 2rem; font-weight: 800; color: #1e293b; line-height: 1; margin: 0; }
.kpi-badge { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 600; margin-top: 12px; }
.kpi-badge.success { background: #d1fae5; color: #059669; }
.kpi-badge.info { background: #dbeafe; color: #1d4ed8; }
.kpi-badge.warning { background: #fef3c7; color: #d97706; }
</style>
<div class="dashboard-bento">
    
    <!-- Banner de Bienvenida (Compacto) - Clonado del Admin Dashboard -->
    <div class="welcome-banner welcome-banner-compact slide-up">
        <div class="welcome-icon">
            <i class="ti ti-sparkles"></i>
        </div>
        
        <div class="welcome-content">
            <div class="welcome-text">
                <h1 class="welcome-title">¡Bienvenido de nuevo, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Usuario')[0]) ?>!</h1>
                <p class="welcome-subtitle">
                    <i class="ti ti-layout-dashboard"></i>
                    <span>Panel de Control</span>
                    <span class="subtitle-separator">-</span>
                    <span><?= $role ?></span>
                </p>
            </div>
        </div>
        
        <div class="welcome-meta">
            <div class="welcome-date">
                <i class="ti ti-calendar"></i>
                <span id="currentDate"></span>
            </div>
            <div class="welcome-time">
                <i class="ti ti-clock"></i>
                <span id="currentTime"></span>
            </div>
        </div>
    </div>
    
    <?php 
        $estadoPasantia = $pasante->estado_pasantia ?? 'Pendiente';
        if ($estadoPasantia === 'Sin Asignar' || empty($pasante->estado_pasantia) || $estadoPasantia === 'Pendiente'): 
    ?>
    <!-- ============================================== -->
    <!-- EMPTY STATE PREMIUM (Sin Asignar / En Revisión)-->
    <!-- ============================================== -->
    <div class="card slide-up" style="animation-delay: 0.1s; padding: 60px 40px; text-align: center; border-radius: 20px; background: linear-gradient(to bottom, #ffffff, #f8fafc); border: 2px dashed #cbd5e1; display: flex; flex-direction: column; align-items: center; justify-content: center; min-height: 400px; box-shadow: 0 10px 30px rgba(0,0,0,0.02);">
        
        <div style="width: 100px; height: 100px; background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin-bottom: 24px; box-shadow: 0 8px 16px rgba(37, 99, 235, 0.1);">
            <i class="ti ti-user-scan" style="font-size: 3.5rem; color: #2563eb;"></i>
        </div>
        
        <h2 style="font-size: 1.8rem; font-weight: 800; color: #1e293b; margin-bottom: 12px; letter-spacing: -0.5px;">Perfil en Revisión</h2>
        
        <p style="font-size: 1rem; color: #64748b; max-width: 500px; line-height: 1.6; margin-bottom: 30px;">
            ¡Hola! Tu cuenta ha sido creada exitosamente. Actualmente estás en la lista de espera. <strong>El equipo de Telemática te asignará pronto</strong> un Departamento y un Tutor para que puedas comenzar a registrar tus asistencias.
        </p>
        
        <div style="display: flex; gap: 16px; align-items: center; background: #fff; padding: 12px 24px; border-radius: 50px; border: 1px solid #e2e8f0; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
            <span style="display: flex; align-items: center; gap: 8px; font-size: 0.85rem; font-weight: 600; color: #475569;">
                <i class="ti ti-loader ti-spin" style="color: #2563eb; font-size: 1.2rem;"></i> Esperando Asignación...
            </span>
        </div>

    </div>
    <?php else: ?>
    <!-- ============================================== -->
    <!-- VISTA NORMAL (Con KPIs y Tabla)                 -->
    <!-- ============================================== -->
    <div class="dashboard-kpi-grid">
        <!-- Fila 1: 4 KPIs para Pasante -->
        <div class="card kpi-card span-1 slide-up" style="animation-delay: 0.1s;">
        <div class="kpi-header">
            <span class="kpi-label">Horas Completadas</span>
            <div class="kpi-icon">
                <i class="ti ti-clock"></i>
            </div>
        </div>
        <?php
            $hCumpl = (int)($pasante->horas_acumuladas ?? 0);
            $hMeta  = (int)($pasante->horas_meta ?? 480);
        ?>
        <div class="kpi-value" data-kpi-value="<?= $hCumpl ?>">0</div>
        <div class="kpi-badge success">
            <i class="ti ti-trending-up"></i> de <?= $hMeta ?> horas
        </div>
    </div>

    <div class="card kpi-card span-1 slide-up" style="animation-delay: 0.2s;">
        <div class="kpi-header">
            <span class="kpi-label">Este Mes</span>
            <div class="kpi-icon">
                <i class="ti ti-calendar-check"></i>
            </div>
        </div>
        <?php
            $asistenciasEsteMes = 0;
            if (!empty($actividades)) {
                $mesActual = date('m');
                $anioActual = date('Y');
                foreach ($actividades as $act) {
                    $fechaAct = $act->fecha ?? '';
                    if (!empty($fechaAct) && date('m', strtotime($fechaAct)) == $mesActual && date('Y', strtotime($fechaAct)) == $anioActual) {
                        $asistenciasEsteMes++;
                    }
                }
            }
        ?>
        <div class="kpi-value" data-kpi-value="<?= $asistenciasEsteMes ?>">0</div>
    </div>

    <div class="card kpi-card span-1 slide-up" style="animation-delay: 0.3s;">
        <div class="kpi-header">
            <span class="kpi-label">Registros Totales</span>
            <div class="kpi-icon">
                <i class="ti ti-file-text"></i>
            </div>
        </div>
        <div class="kpi-value" data-kpi-value="<?= count($actividades ?? []) ?>">0</div>
    </div>

    <div class="card kpi-card span-1 slide-up" style="animation-delay: 0.4s;">
        <div class="kpi-header">
            <span class="kpi-label">Progreso General</span>
            <div class="kpi-icon">
                <i class="ti ti-chart-pie"></i>
            </div>
        </div>
        <?php
            $progreso = ($hMeta > 0) ? min(100, round(($hCumpl / $hMeta) * 100)) : 0;
            $estadoPasantia = $pasante->estado_pasantia ?? 'Pendiente';
        ?>
        <div class="kpi-value" data-kpi-value="<?= $progreso ?>">0</div>
        <div class="kpi-badge info">
            <i class="ti ti-clock"></i> <?= htmlspecialchars($estadoPasantia) ?>
        </div>
    </div>
    </div> <!-- Cierre de la grilla de métricas -->

    <!-- Fila 2: Tabla de Actividades Recientes -->
    <div class="card span-4 slide-up" style="animation-delay: 0.5s;">
        <div class="card-header" style="padding-bottom: 20px;">
            <h3 style="color: var(--deep-azure); font-size: 1.125rem; font-weight: 600; margin: 0;">Mis Últimas Asistencias</h3>
        </div>
        <div class="table-container" style="overflow-x: auto; margin-top: -10px;">
            <table class="modern-table" style="width: 100%;">
                <thead>
                    <tr>
                        <th style="text-align: left; padding: 12px 16px;">Fecha</th>
                        <th style="padding: 12px 16px;">Hora</th>
                        <th style="text-align: right; padding: 12px 16px;">Estado</th>
                    </tr>
                </thead>
                <tbody>
                    <?php if (!empty($actividades)): ?>
                        <?php foreach ($actividades as $act):
                            $fecha   = !empty($act->fecha) ? date('d/m/Y', strtotime($act->fecha)) : '—';
                            $hora    = !empty($act->hora_registro) ? date('h:i A', strtotime($act->hora_registro)) : '—';
                            $estadoBadge = ($act->estado ?? '') === 'Presente' ? 'success' : (($act->estado ?? '') === 'Justificado' ? 'warning' : 'info');
                        ?>
                        <tr>
                            <td style="padding: 12px 16px;"><strong><?= htmlspecialchars($fecha) ?></strong></td>
                            <td style="color: var(--text-muted); text-align: center; padding: 12px 16px;"><?= htmlspecialchars($hora) ?></td>
                            <td style="text-align: right; padding: 12px 16px;"><span class="badge <?= $estadoBadge ?>"><?= htmlspecialchars($act->estado ?? 'N/A') ?></span></td>
                        </tr>
                        <?php endforeach; ?>
                    <?php else: ?>
                        <tr>
                            <td colspan="3" style="text-align:center;padding:48px 24px;color:#94a3b8;border-bottom:none;">
                                <i class="ti ti-calendar-off" style="font-size:42px;display:block;margin-bottom:12px;opacity:0.5;"></i>
                                <span style="font-size:0.95rem;font-weight:500;">Aún no tienes asistencias registradas.</span>
                            </td>
                        </tr>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>
    <?php endif; ?>

</div>

<script>
function confirmLogout() {
    Swal.fire({
        title: '¿Cerrar Sesión?',
        text: "Estás a punto de salir del sistema",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#162660',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, salir'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = '<?= URLROOT ?>/auth/logout';
    });
}

// ========================================
// ACTUALIZAR FECHA Y HORA EN BANNER
// ========================================
function updateDateTime() {
    const now = new Date();
    
    // Formato de fecha: "Jueves, 23 de Enero 2026"
    const dateOptions = { weekday: 'long', year: 'numeric', month: 'long', day: 'numeric' };
    const dateStr = now.toLocaleDateString('es-ES', dateOptions);
    
    // Formato de hora: "22:56:03"
    const timeStr = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit', hour12: true });
    
    const dateEl = document.getElementById('currentDate');
    const timeEl = document.getElementById('currentTime');
    
    if (dateEl) dateEl.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
    if (timeEl) timeEl.textContent = timeStr.toUpperCase();
}

// Actualizar inmediatamente y cada segundo
updateDateTime();
setInterval(updateDateTime, 1000);

// ========================================
// POLL DE ESTADO (Auto-recarga al asignar)
// ========================================
<?php if ($estadoPasantia === 'Sin Asignar' || empty($pasante->estado_pasantia) || $estadoPasantia === 'Pendique' || $estadoPasantia === 'Pendiente'): ?>
const currentStatus = '<?= $estadoPasantia ?>';
let checkCount = 0;

const statusInterval = setInterval(async () => {
    try {
        const response = await fetch('<?= URLROOT ?>/pasante/getStatusAjax');
        const data = await response.json();
        
        if (data.success && data.estado !== currentStatus) {
            // Si el estado cambió a algo que no sea "Pendiente/Sin Asignar", recargamos
            if (data.estado !== 'Sin Asignar' && data.estado !== 'Pendiente' && data.estado !== '') {
                clearInterval(statusInterval);
                window.location.reload();
            }
        }
        
        // Seguridad: Detener después de 30 minutos de inactividad en esta vista (opcional)
        checkCount++;
        if (checkCount > 360) clearInterval(statusInterval); // 360 * 5s = 30min
        
    } catch (error) {
        console.error('Error verificando estado:', error);
    }
}, 5000); // Cada 5 segundos
<?php endif; ?>

</script>
