<?php
// Vista exclusiva para Tutores
$user_name = $data['user_name'] ?? 'Tutor';
$role = $data['role'] ?? 'Tutor';
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
                <span class="stat-label">Mis Pasantes</span>
                <div class="stat-icon"><i class="ti ti-users-group"></i></div>
            </div>
            <div class="stat-value">8</div>
            <div style="font-size: 0.8rem; color: #10B981; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
                <i class="ti ti-check"></i> 6 activos
            </div>
        </div>
        <div class="smart-card">
            <div class="stat-header">
                <span class="stat-label">Evaluaciones Pendientes</span>
                <div class="stat-icon"><i class="ti ti-clipboard-check"></i></div>
            </div>
            <div class="stat-value">3</div>
        </div>
        <div class="smart-card">
            <div class="stat-header">
                <span class="stat-label">Reportes Este Mes</span>
                <div class="stat-icon"><i class="ti ti-file-text"></i></div>
            </div>
            <div class="stat-value">12</div>
        </div>
    </div>

    <div class="table-container">
        <div style="margin-bottom: 20px;">
            <h3 style="color: var(--color-primary); font-weight: 700; font-size: 1.1rem;">Pasantes Asignados</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Pasante</th>
                    <th>Institución</th>
                    <th>Horas Completadas</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 28px; height: 28px; background: var(--color-bg); color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">MP</div>
                            María Pérez
                        </div>
                    </td>
                    <td>Hospital Ruiz Páez</td>
                    <td>120/240 horas</td>
                    <td><span style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">En Progreso</span></td>
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
