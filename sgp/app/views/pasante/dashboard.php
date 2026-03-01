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
        <div class="kpi-value">85</div>
        <div class="kpi-badge success">
            <i class="ti ti-trending-up"></i> de 240 horas
        </div>
    </div>

    <div class="card kpi-card slide-up" style="animation-delay: 0.2s;">
        <div class="kpi-header">
            <span class="kpi-label">Asistencias Este Mes</span>
            <div class="kpi-icon">
                <i class="ti ti-calendar-check"></i>
            </div>
        </div>
        <div class="kpi-value">18</div>
    </div>

    <div class="card kpi-card slide-up" style="animation-delay: 0.3s;">
        <div class="kpi-header">
            <span class="kpi-label">Bitácoras Registradas</span>
            <div class="kpi-icon">
                <i class="ti ti-file-text"></i>
            </div>
        </div>
        <div class="kpi-value">24</div>
    </div>

    <div class="card kpi-card slide-up" style="animation-delay: 0.4s;">
        <div class="kpi-header">
            <span class="kpi-label">Progreso General</span>
            <div class="kpi-icon">
                <i class="ti ti-chart-pie"></i>
            </div>
        </div>
        <div class="kpi-value">35%</div>
        <div class="kpi-badge info">
            <i class="ti ti-clock"></i> En tiempo
        </div>
    </div>

    <!-- Fila 2: Tabla de Actividades Recientes -->
    <div class="card span-4 slide-up" style="animation-delay: 0.5s;">
        <h3 style="color: var(--deep-azure); font-size: 1.125rem; font-weight: 600; margin-bottom: 16px;">Mis Actividades Recientes</h3>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Actividad</th>
                    <th>Fecha</th>
                    <th>Horas</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>Asistencia - Turno Mañana</td>
                    <td style="color: var(--text-muted);">15 Enero 2026</td>
                    <td style="color: var(--text-muted);">4 horas</td>
                    <td><span class="badge success">Aprobado</span></td>
                </tr>
                <tr>
                    <td>Bitácora - Actividades del día</td>
                    <td style="color: var(--text-muted);">15 Enero 2026</td>
                    <td style="color: var(--text-muted);">-</td>
                    <td><span class="badge info">Registrado</span></td>
                </tr>
                <tr>
                    <td>Asistencia - Turno Tarde</td>
                    <td style="color: var(--text-muted);">14 Enero 2026</td>
                    <td style="color: var(--text-muted);">4 horas</td>
                    <td><span class="badge success">Aprobado</span></td>
                </tr>
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
