<?php
// Vista exclusiva para Pasantes
$user_name = $data['user_name'] ?? 'Pasante';
$role = $data['role'] ?? 'Pasante';
?>

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
    
    <!-- Fila 1: 3 KPIs para Pasante -->
    <div class="card kpi-card slide-up" style="animation-delay: 0.1s;">
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

    <div class="card kpi-card slide-up" style="animation-delay: 0.2s;">
        <div class="kpi-header">
            <span class="kpi-label">Asistencias Este Mes</span>
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

    <div class="card kpi-card slide-up" style="animation-delay: 0.3s;">
        <div class="kpi-header">
            <span class="kpi-label">Bitácoras Registradas</span>
            <div class="kpi-icon">
                <i class="ti ti-file-text"></i>
            </div>
        </div>
        <div class="kpi-value" data-kpi-value="<?= count($actividades ?? []) ?>">0</div>
    </div>

    <div class="card kpi-card slide-up" style="animation-delay: 0.4s;">
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

    <!-- Fila 2: Tabla de Actividades Recientes -->
    <div class="card span-4 slide-up" style="animation-delay: 0.5s;">
        <h3 style="color: var(--deep-azure); font-size: 1.125rem; font-weight: 600; margin-bottom: 16px;">Mis Últimas Asistencias</h3>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Fecha</th>
                    <th>Hora</th>
                    <th>Estado</th>
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
                        <td><?= htmlspecialchars($fecha) ?></td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($hora) ?></td>
                        <td><span class="badge <?= $estadoBadge ?>"><?= htmlspecialchars($act->estado ?? 'N/A') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="3" style="text-align:center;padding:32px;color:#94a3b8;">
                            <i class="ti ti-calendar-off" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                            Aún no tienes asistencias registradas.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

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
    const timeStr = now.toLocaleTimeString('es-ES', { hour: '2-digit', minute: '2-digit', second: '2-digit' });
    
    const dateEl = document.getElementById('currentDate');
    const timeEl = document.getElementById('currentTime');
    
    if (dateEl) dateEl.textContent = dateStr.charAt(0).toUpperCase() + dateStr.slice(1);
    if (timeEl) timeEl.textContent = timeStr;
}

// Actualizar inmediatamente y cada segundo
updateDateTime();
setInterval(updateDateTime, 1000);

</script>
