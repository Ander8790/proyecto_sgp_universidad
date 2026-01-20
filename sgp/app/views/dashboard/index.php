<?php
// === ADAPTADOR DE COMPATIBILIDAD V1 ===
// Usar la clase Session en lugar de acceso directo a $_SESSION
$user_name = Session::get('user_name') ?? Session::get('nombres') ?? Session::get('nombre') ?? 'Usuario';

// 2. Definir Rol (Convertir ID numérico a String para la vista V1)
$rol_id = Session::get('role_id') ?? Session::get('rol_id') ?? 0;
$role = 'Invitado'; // Default

switch ($rol_id) {
    case 1: $role = 'Administrador'; break;
    case 2: $role = 'Tutor'; break;
    case 3: $role = 'Pasante'; break;
    default: 
        // Fallback: Si viene el string directo en la sesión
        $role = Session::get('rol_nombre') ?? 'Invitado';
        break;
}
// === FIN ADAPTADOR ===
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
        <?php if ($role == 'Administrador'): ?>
            <div class="smart-card">
                <div class="stat-header">
                    <span class="stat-label">Total Pasantes</span>
                    <div class="stat-icon"><i class="ti ti-users"></i></div>
                </div>
                <div class="stat-value">45</div>
                <div style="font-size: 0.8rem; color: #10B981; margin-top: 5px; display: flex; align-items: center; gap: 4px;">
                    <i class="ti ti-trending-up"></i> +5 este mes
                </div>
            </div>
            <div class="smart-card">
                <div class="stat-header">
                    <span class="stat-label">Tutores Activos</span>
                    <div class="stat-icon"><i class="ti ti-school"></i></div>
                </div>
                <div class="stat-value">12</div>
            </div>
            <div class="smart-card">
                <div class="stat-header">
                    <span class="stat-label">Instituciones</span>
                    <div class="stat-icon"><i class="ti ti-building-hospital"></i></div>
                </div>
                <div class="stat-value">8</div>
            </div>
        <?php endif; ?>
        
        </div>

    <div class="charts-grid" style="display: grid; grid-template-columns: repeat(auto-fit, minmax(400px, 1fr)); gap: 24px; margin-bottom: 24px;">
        <?php if ($role == 'Administrador'): ?>
            <div class="smart-card">
                <h3 style="font-size: 1rem; color: var(--color-primary); margin-bottom: 15px; font-weight: 600;">Registros Mensuales</h3>
                <div id="chart-registrations"></div>
            </div>
            <div class="smart-card">
                <h3 style="font-size: 1rem; color: var(--color-primary); margin-bottom: 15px; font-weight: 600;">Pasantes por Institución</h3>
                <div id="chart-institutions"></div>
            </div>
        <?php endif; ?>
    </div>

    <div class="table-container">
        <div style="margin-bottom: 20px;">
            <h3 style="color: var(--color-primary); font-weight: 700; font-size: 1.1rem;">Actividad Reciente</h3>
        </div>
        <table>
            <thead>
                <tr>
                    <th>Usuario</th>
                    <th>Acción</th>
                    <th>Fecha</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="width: 28px; height: 28px; background: var(--color-bg); color: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: bold;">JS</div>
                            Juan Silva
                        </div>
                    </td>
                    <td>Registro de Asistencia</td>
                    <td>Hace 2 horas</td>
                    <td><span style="background: rgba(16, 185, 129, 0.1); color: #059669; padding: 4px 10px; border-radius: 12px; font-size: 0.75rem; font-weight: 600;">Completado</span></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script src="<?= URLROOT ?>/js/apexcharts.min.js"></script>
<script>
    document.addEventListener("DOMContentLoaded", function () {
        // Charts Initialization (Admin)
        const chartRegEl = document.querySelector("#chart-registrations");
        if (chartRegEl) {
            const options = {
                series: [{ name: 'Nuevos Pasantes', data: [10, 15, 8, 12, 20, 32] }],
                chart: { height: 300, type: 'area', toolbar: { show: false }, fontFamily: 'inherit' },
                dataLabels: { enabled: false },
                stroke: { curve: 'smooth', width: 2 },
                colors: ['#162660'],
                fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.7, opacityTo: 0.3 } },
                xaxis: { categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun'] }
            };
            new ApexCharts(chartRegEl, options).render();
        }

        const chartInstEl = document.querySelector("#chart-institutions");
        if (chartInstEl) {
            const options = {
                series: [{ name: 'Pasantes', data: [12, 8, 5, 15, 3] }],
                chart: { height: 300, type: 'bar', toolbar: { show: false } },
                plotOptions: { bar: { borderRadius: 4, horizontal: true } },
                colors: ['#059669'],
                xaxis: { categories: ['H. Ruiz Páez', 'CDI Los Proceres', 'Uyapar', 'IVSS', 'Ambulatorio A'] }
            };
            new ApexCharts(chartInstEl, options).render();
        }
    });

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
