<?php
// Vista exclusiva para Pasantes
$user_name = $data['user_name'] ?? 'Pasante';
$role = $data['role'] ?? 'Pasante';
?>

<div class="dashboard-container">
    
    <div class="welcome-card">
        <div class="welcome-content">
            <div class="welcome-icon">
                <i class="ti ti-sparkles"></i>
            </div>
            <div class="welcome-text">
                <h1 class="welcome-title">¡Bienvenido de nuevo, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>!</h1>
                <p class="welcome-subtitle">
                    <i class="ti ti-dashboard"></i> Panel de Control - 
                    <span class="welcome-role"><?= htmlspecialchars($role) ?></span>
                </p>
            </div>
        </div>
        <div class="welcome-date">
            <i class="ti ti-calendar"></i>
            <?= date('l, d F Y') ?>
        </div>
    </div>

    <div class="stats-grid">
        <div class="smart-card">
            <div class="stat-header">
                <span class="stat-label">Horas Completadas</span>
                <div class="stat-icon"><i class="ti ti-clock"></i></div>
            </div>
            <div class="stat-value">85</div>
            <div style="font-size: 0.8rem; color: #10B981; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
                <i class="ti ti-trending-up"></i> de 240 horas
            </div>
        </div>
        <div class="smart-card">
            <div class="stat-header">
                <span class="stat-label">Asistencias Este Mes</span>
                <div class="stat-icon"><i class="ti ti-calendar-check"></i></div>
            </div>
            <div class="stat-value">18</div>
        </div>
        <div class="smart-card">
            <div class="stat-header">
                <span class="stat-label">Bitácoras Registradas</span>
                <div class="stat-icon"><i class="ti ti-file-text"></i></div>
            </div>
            <div class="stat-value">24</div>
        </div>
    </div>

    <div class="table-container">
        <div style="margin-bottom: 20px;">
            <h3 style="color: var(--color-primary); font-weight: 700; font-size: 1.1rem;">Mis Actividades Recientes</h3>
        </div>
        <table>
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
                    <td>15 Enero 2026</td>
                    <td>4 horas</td>
                    <td><span style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">Aprobado</span></td>
                </tr>
                <tr>
                    <td>Bitácora - Actividades del día</td>
                    <td>15 Enero 2026</td>
                    <td>-</td>
                    <td><span style="background: rgba(59, 130, 246, 0.1); color: #2563eb; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">Registrado</span></td>
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
</script>
