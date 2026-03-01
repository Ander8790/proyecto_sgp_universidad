<?php
// === ADAPTADOR DE COMPATIBILIDAD ===
$user_name = Session::get('user_name') ?? Session::get('nombres') ?? Session::get('nombre') ?? 'Usuario';

// Definir Rol
$rol_id = Session::get('role_id') ?? Session::get('rol_id') ?? 0;
$role = 'Invitado';

switch ($rol_id) {
    case 1: $role = 'Administrador'; break;
    case 2: $role = 'Tutor'; break;
    case 3: $role = 'Pasante'; break;
    default: 
        $role = Session::get('rol_nombre') ?? 'Invitado';
        break;
}
?>

<div class="dashboard-bento">
    
    <?php if ($role == 'Administrador'): ?>
    <!-- Fila 1: 4 KPIs -->

    <div class="card kpi-card slide-up" style="animation-delay: 0.1s;">
        <div class="kpi-header">
            <span class="kpi-label">Pasantes Activos</span>
            <div class="kpi-icon">
                <i class="ti ti-users"></i>
            </div>
        </div>
        <div class="kpi-value">45</div>
        <div class="kpi-badge success">
            <i class="ti ti-trending-up"></i> +5 este mes
        </div>
    </div>

    <div class="card kpi-card slide-up" style="animation-delay: 0.2s;">
        <div class="kpi-header">
            <span class="kpi-label">Tutores Activos</span>
            <div class="kpi-icon">
                <i class="ti ti-school"></i>
            </div>
        </div>
        <div class="kpi-value">12</div>
    </div>

    <div class="card kpi-card slide-up" style="animation-delay: 0.3s;">
        <div class="kpi-header">
            <span class="kpi-label">Departamentos</span>
            <div class="kpi-icon">
                <i class="ti ti-building-hospital"></i>
            </div>
        </div>
        <div class="kpi-value">4</div>
    </div>

    <div class="card kpi-card slide-up" style="animation-delay: 0.4s;">
        <div class="kpi-header">
            <span class="kpi-label">Asignaciones pendientes</span>
            <div class="kpi-icon">
                <i class="ti ti-clipboard-check"></i>
            </div>
        </div>
        <div class="kpi-value">23</div>
        <div class="kpi-badge warning">
            <i class="ti ti-alert-circle"></i> en espera
        </div>
    </div>

    <!-- Fila 2: Gráfico Principal + Activity Feed -->
    <div class="card span-3 row-span-2 slide-up" style="animation-delay: 0.5s;">
        <div class="card-header">
            <h3>Asistencias por Mes</h3>
            <p>Asistencias mensuales de pasantes</p>
        </div>
        <div id="chart-asistencias-mes"></div>
    </div>

    <div class="card span-1 row-span-2 slide-up" style="animation-delay: 0.6s;">
        <h3 style="color: var(--deep-azure); font-size: 1.125rem; font-weight: 600; margin-bottom: 16px;">Actividad Reciente</h3>
        <div class="activity-list">
            <div class="activity-item">
                <div class="avatar-circle">JP</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem;">Juan Pérez</div>
                    <div style="color: var(--text-muted); font-size: 0.75rem;">Entregó informe final</div>
                    <div style="color: var(--text-muted); font-size: 0.7rem; margin-top: 2px;">Hace 2 horas</div>
                </div>
                <span class="badge success">Docs</span>
            </div>

            <div class="activity-item">
                <div class="avatar-circle" style="background: linear-gradient(135deg, #DBEAFE, #93C5FD);">MG</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem;">María González</div>
                    <div style="color: var(--text-muted); font-size: 0.75rem;">Registró asistencia</div>
                    <div style="color: var(--text-muted); font-size: 0.7rem; margin-top: 2px;">Hace 3 horas</div>
                </div>
                <span class="badge info">Check</span>
            </div>

            <div class="activity-item">
                <div class="avatar-circle" style="background: linear-gradient(135deg, #FEF3C7, #FCD34D);">CR</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem;">Carlos Rodríguez</div>
                    <div style="color: var(--text-muted); font-size: 0.75rem;">Actualizó bitácora</div>
                    <div style="color: var(--text-muted); font-size: 0.7rem; margin-top: 2px;">Hace 5 horas</div>
                </div>
                <span class="badge warning">Edit</span>
            </div>

            <div class="activity-item">
                <div class="avatar-circle" style="background: linear-gradient(135deg, #D1FAE5, #6EE7B7);">AS</div>
                <div style="flex: 1;">
                    <div style="font-weight: 600; color: var(--text-primary); font-size: 0.875rem;">Ana Silva</div>
                    <div style="color: var(--text-muted); font-size: 0.75rem;">Completó evaluación</div>
                    <div style="color: var(--text-muted); font-size: 0.7rem; margin-top: 2px;">Hace 1 día</div>
                </div>
                <span class="badge success">Eval</span>
            </div>
        </div>
    </div>

    <!-- Fila 3: Donut + Tabla -->
    <div class="card span-2 slide-up" style="animation-delay: 0.7s;">
        <h3 style="color: var(--deep-azure); font-size: 1.125rem; font-weight: 600; margin-bottom: 16px;">Distribución por Departamentos</h3>
        <div id="chart-departamentos"></div>
    </div>

    <div class="card span-2 slide-up" style="animation-delay: 0.8s;">
        <h3 style="color: var(--deep-azure); font-size: 1.125rem; font-weight: 600; margin-bottom: 16px;">Últimos Registros</h3>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Pasante</th>
                    <th>Institución</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem;">MG</div>
                            <span>María González</span>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">H. Ruiz Páez</td>
                    <td><span class="badge success">Activo</span></td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem; background: linear-gradient(135deg, #DBEAFE, #93C5FD);">JP</div>
                            <span>Juan Pérez</span>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">CDI Los Próceres</td>
                    <td><span class="badge success">Activo</span></td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem; background: linear-gradient(135deg, #FEF3C7, #FCD34D);">CR</div>
                            <span>Carlos Rodríguez</span>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">IVSS</td>
                    <td><span class="badge warning">Pendiente</span></td>
                </tr>
                <tr>
                    <td>
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div class="avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem; background: linear-gradient(135deg, #D1FAE5, #6EE7B7);">AS</div>
                            <span>Ana Silva</span>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">Ambulatorio A</td>
                    <td><span class="badge success">Activo</span></td>
                </tr>
            </tbody>
        </table>
    </div>
    <?php endif; ?>

</div>

<script src="<?= URLROOT ?>/js/apexcharts.min.js"></script>
<script>
// Configuración de gráficos con paleta institucional
document.addEventListener('DOMContentLoaded', function() {
    
    // Gráfico de Barras - Pasantes por Mes
    const barChartOptions = {
        series: [{
            name: 'Pasantes',
            data: [10, 15, 8, 12, 20, 32, 18, 25, 22, 28, 24, 30]
        }],
        chart: {
            type: 'bar',
            height: 350,
            toolbar: { show: false },
            fontFamily: 'Plus Jakarta Sans, sans-serif'
        },
        colors: ['#162660'],  // Deep Azure
        plotOptions: {
            bar: {
                borderRadius: 8,
                columnWidth: '60%',
                distributed: false
            }
        },
        fill: {
            type: 'gradient',
            gradient: {
                shade: 'light',
                type: 'vertical',
                shadeIntensity: 0.4,
                gradientToColors: ['#3B82F6'],
                inverseColors: false,
                opacityFrom: 1,
                opacityTo: 0.8,
                stops: [0, 100]
            }
        },
        dataLabels: { enabled: false },
        grid: {
            borderColor: '#F1F5F9',
            strokeDashArray: 4,
            padding: { top: 0, right: 0, bottom: 0, left: 10 }
        },
        xaxis: {
            categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
            labels: {
                style: {
                    colors: '#94A3B8',
                    fontSize: '12px'
                }
            },
            axisBorder: { show: false },
            axisTicks: { show: false }
        },
        yaxis: {
            labels: {
                style: {
                    colors: '#94A3B8',
                    fontSize: '12px'
                }
            }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function(val) {
                    return val + ' pasantes';
                }
            }
        }
    };
    
    const barChart = new ApexCharts(document.querySelector('#chart-pasantes-mes'), barChartOptions);
    barChart.render();
    
    // Gráfico Donut - Distribución por Departamentos
    const donutChartOptions = {
        series: [35, 25, 20, 12, 8],
        chart: {
            type: 'donut',
            height: 300,
            fontFamily: 'Plus Jakarta Sans, sans-serif'
        },
        colors: ['#162660', '#3B82F6', '#60A5FA', '#93C5FD', '#F59E0B'],
        labels: ['Soporte Técnico', 'Enfermería', 'Administración', 'Laboratorio', 'Otros'],
        dataLabels: {
            enabled: true,
            style: {
                fontSize: '12px',
                fontWeight: 600
            }
        },
        legend: {
            position: 'bottom',
            fontSize: '13px',
            labels: {
                colors: '#475569'
            }
        },
        plotOptions: {
            pie: {
                donut: {
                    size: '70%',
                    labels: {
                        show: true,
                        name: {
                            show: true,
                            fontSize: '14px',
                            color: '#162660'
                        },
                        value: {
                            show: true,
                            fontSize: '24px',
                            fontWeight: 700,
                            color: '#162660'
                        },
                        total: {
                            show: true,
                            label: 'Total',
                            fontSize: '14px',
                            color: '#94A3B8',
                            formatter: function(w) {
                                return w.globals.seriesTotals.reduce((a, b) => a + b, 0);
                            }
                        }
                    }
                }
            }
        },
        tooltip: {
            theme: 'light',
            y: {
                formatter: function(val) {
                    return val + ' pasantes';
                }
            }
        }
    };
    
    const donutChart = new ApexCharts(document.querySelector('#chart-departamentos'), donutChartOptions);
    donutChart.render();
});

// Función de logout (mantener compatibilidad)
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
