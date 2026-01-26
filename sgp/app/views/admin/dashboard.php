<?php
// Vista exclusiva para Administradores
$user_name = $data['user_name'] ?? 'Administrador';
$role = $data['role'] ?? 'Administrador';
?>

<div class="dashboard-bento">
    
    <!-- Banner de Bienvenida (Compacto) -->
    <div class="welcome-banner welcome-banner-compact">
        <div class="welcome-icon">
            <i class="ti ti-sparkles"></i>
        </div>
        
        <div class="welcome-content">
            <div class="welcome-text">
                <h1 class="welcome-title">¡Bienvenido de nuevo, <?= htmlspecialchars(explode(' ', $user_name)[0]) ?>!</h1>
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
    
    <!-- Fila 1: 4 KPIs con Radial Charts (Compactos) -->
    <div class="card kpi-card kpi-card-compact">
        <div class="kpi-chart">
            <div id="chart-pasantes"></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Total Pasantes</span>
            <h3 class="kpi-value">45</h3>
            <div class="kpi-badge success">
                <i class="ti ti-trending-up"></i> +5
            </div>
        </div>
    </div>

    <div class="card kpi-card kpi-card-compact">
        <div class="kpi-chart">
            <div id="chart-tutores"></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Tutores Activos</span>
            <h3 class="kpi-value">12</h3>
        </div>
    </div>

    <div class="card kpi-card kpi-card-compact">
        <div class="kpi-chart">
            <div id="chart-instituciones"></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Instituciones</span>
            <h3 class="kpi-value">8</h3>
        </div>
    </div>

    <div class="card kpi-card kpi-card-compact">
        <div class="kpi-chart">
            <div id="chart-cupos"></div>
        </div>
        <div class="kpi-info">
            <span class="kpi-label">Cupos Disponibles</span>
            <h3 class="kpi-value">23</h3>
            <div class="kpi-badge warning">
                <i class="ti ti-alert-circle"></i> Limitados
            </div>
        </div>
    </div>


    <!-- Fila 2: Gráfico de Barras + Gráfico de Donut (LADO A LADO) -->
    <div class="card span-2">
        <div class="card-header-compact">
            <h3>Registros Mensuales</h3>
        </div>
        <div id="chart-registrations"></div>
    </div>

    <div class="card span-2">
        <div class="card-header-compact">
            <h3>Distribución por Institución</h3>
        </div>
        <div id="chart-institutions"></div>
    </div>

    <!-- Fila 3: Activity Feed + Tabla -->
    <div class="card span-2">
        <div class="card-header-compact">
            <h3>Actividad Reciente</h3>
            <a href="#" class="btn-text-link">Ver todo</a>
        </div>
        <div class="activity-list-compact">
            <div class="activity-item">
                <div class="avatar-circle">JS</div>
                <div class="activity-details">
                    <div class="activity-name">Juan Silva</div>
                    <div class="activity-action">Registro de Asistencia</div>
                    <div class="activity-time">Hace 2 horas</div>
                </div>
                <span class="badge success">Check</span>
            </div>
        </div>
    </div>

    <div class="card span-2">
        <div class="card-header-compact">
            <h3>Últimos Registros</h3>
            <a href="#" class="btn-text-link">Ver todo</a>
        </div>
        <table class="modern-table-compact">
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
                            <div class="avatar-circle" style="width: 28px; height: 28px; font-size: 0.7rem;">JS</div>
                            <span>Juan Silva</span>
                        </div>
                    </td>
                    <td style="color: var(--text-muted);">Registro de Asistencia</td>
                    <td style="color: var(--text-muted);">Hace 2 horas</td>
                    <td><span class="badge success">Completado</span></td>
                </tr>
            </tbody>
        </table>
    </div>

</div>

<script src="<?= URLROOT ?>/js/apexcharts.min.js"></script>
<script>
document.addEventListener("DOMContentLoaded", function () {
    // ========================================
    // FUNCIÓN PARA CREAR MINI RADIAL CHARTS
    // ========================================
    function createRadialKPI(id, value, color) {
        var options = {
            series: [value],
            chart: {
                height: 56,  /* Especificación profesional */
                width: 56,
                type: 'radialBar',
                sparkline: { enabled: true }
            },
            plotOptions: {
                radialBar: {
                    hollow: {
                        size: '55%', // Hueco más pequeño = anillo más grueso
                    },
                    track: {
                        background: '#F1F5F9', // Gris claro (no arena)
                        strokeWidth: '100%',
                        margin: 0,
                    },
                    dataLabels: {
                        show: true,
                        name: { show: false },
                        value: {
                            offsetY: 3,
                            fontSize: '14px',  /* Más pequeño */
                            fontWeight: '700',
                            color: '#162660',
                            formatter: function (val) {
                                return val + "%";
                            }
                        }
                    }
                }
            },
            stroke: {
                lineCap: 'round' // Puntas redondas (Estilo Outrunix)
            },
            fill: {
                colors: [color]
            }
        };

        var chart = new ApexCharts(document.querySelector(id), options);
        chart.render();
    }

    // ========================================
    // CREAR LOS 4 KPI RADIAL CHARTS
    // ========================================
    createRadialKPI("#chart-pasantes", 78, "#6366F1");      // Índigo
    createRadialKPI("#chart-tutores", 65, "#3B82F6");       // Azul
    createRadialKPI("#chart-instituciones", 85, "#10B981"); // Verde
    createRadialKPI("#chart-cupos", 45, "#F59E0B");         // Naranja

    // ========================================
    // GRÁFICO PRINCIPAL - REGISTROS MENSUALES (COMPACT)
    // ========================================
    const chartRegEl = document.querySelector("#chart-registrations");
    if (chartRegEl) {
        const options = {
            series: [{ name: 'Nuevos Pasantes', data: [10, 15, 8, 12, 20, 32, 18, 25, 22, 28, 24, 30] }],
            chart: { 
                height: 240,  /* Reducido para layout compacto */
                type: 'bar',
                toolbar: { show: false },
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            plotOptions: {
                bar: {
                    borderRadius: 10,
                    borderRadiusApplication: 'end',
                    columnWidth: '55%',
                    distributed: false
                }
            },
            dataLabels: { enabled: false },
            colors: ['#6366F1'],
            fill: { 
                type: 'solid',
                opacity: 1
            },
            grid: {
                show: true,
                borderColor: '#F1F5F9',
                strokeDashArray: 0,
                position: 'back',
                xaxis: {
                    lines: {
                        show: false
                    }
                },
                yaxis: {
                    lines: {
                        show: true
                    }
                },
                padding: {
                    top: 0,
                    right: 0,
                    bottom: 0,
                    left: 10
                }
            },
            xaxis: { 
                categories: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
                labels: {
                    style: {
                        colors: '#94A3B8',
                        fontSize: '12px',
                        fontWeight: 500
                    }
                },
                axisBorder: {
                    show: false
                },
                axisTicks: {
                    show: false
                }
            },
            yaxis: {
                labels: {
                    style: {
                        colors: '#94A3B8',
                        fontSize: '12px'
                    },
                    formatter: function(val) {
                        return val.toFixed(0)
                    }
                }
            },
            tooltip: {
                theme: 'light',
                y: {
                    formatter: function(val) {
                        return val + " pasantes"
                    }
                }
            },
            states: {
                hover: {
                    filter: {
                        type: 'darken',
                        value: 0.1
                    }
                }
            }
        };
        new ApexCharts(chartRegEl, options).render();
    }

    // Gráfico de Barras Radiales - Instituciones (COMPACT)
    const chartInstEl = document.querySelector("#chart-institutions");
    if (chartInstEl) {
        const options = {
            series: [28, 19, 12, 26, 7],
            chart: { 
                height: 240,  /* Reducido para layout compacto */
                type: 'radialBar',
                fontFamily: 'Plus Jakarta Sans, sans-serif'
            },
            plotOptions: {
                radialBar: {
                    offsetY: 0,
                    startAngle: 0,
                    endAngle: 270,
                    hollow: {
                        margin: 5,
                        size: '30%',
                        background: 'transparent',
                    },
                    dataLabels: {
                        name: {
                            show: false
                        },
                        value: {
                            show: false
                        }
                    },
                    track: {
                        background: '#F1F5F9',
                        strokeWidth: '100%',
                        margin: 8,
                    }
                }
            },
            colors: ['#6366F1', '#10B981', '#F59E0B', '#3B82F6', '#8B5CF6'],
            labels: ['H. Ruiz Páez', 'CDI Los Próceres', 'Uyapar', 'IVSS', 'Ambulatorio A'],
            legend: {
                show: true,
                floating: false,
                position: 'right',
                offsetX: 0,
                offsetY: 10,
                fontSize: '13px',
                fontWeight: 500,
                markers: {
                    width: 10,
                    height: 10,
                    radius: 10
                },
                itemMargin: {
                    horizontal: 5,
                    vertical: 6
                },
                formatter: function(seriesName, opts) {
                    return seriesName + ": " + opts.w.globals.series[opts.seriesIndex] + "%"
                }
            },
            stroke: {
                lineCap: 'round'
            }
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
