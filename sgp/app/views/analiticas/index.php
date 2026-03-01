<?php
/**
 * Vista: Analíticas del Sistema (Mockup Alta Fidelidad)
 * FASE 4 - Datos hardcoded + Chart.js para demostración
 */
?>

<div class="dashboard-container">

    <!-- ===== BANNER ===== -->
    <div style="background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); border-radius: 20px; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between;">
        <div style="position: absolute; top: -30px; right: -30px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        <div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 10px;">
                    <i class="ti ti-chart-dots" style="font-size: 28px; color: white;"></i>
                </div>
                <div>
                    <h1 style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;">Analíticas del Sistema</h1>
                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem;">Métricas y estadísticas en tiempo real</p>
                </div>
            </div>
        </div>
        <div style="color: rgba(255,255,255,0.8); font-size: 0.85rem; text-align: right;">
            <i class="ti ti-refresh"></i> Actualizado: <?= date('d/m/Y H:i') ?>
        </div>
    </div>

    <!-- ===== KPIs PRINCIPALES ===== -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px;">
        <?php
        $kpis = [
            ['label' => 'Tasa de Asistencia', 'valor' => '87.3%', 'sub' => '↑ 2.1% vs mes anterior', 'color' => '#059669', 'icon' => 'ti-trending-up', 'trend' => 'up'],
            ['label' => 'Pasantes Activos', 'valor' => '38', 'sub' => 'de 45 registrados', 'color' => '#162660', 'icon' => 'ti-users', 'trend' => 'neutral'],
            ['label' => 'Horas Acumuladas', 'valor' => '18,450', 'sub' => '↑ 1,240 este mes', 'color' => '#1d4ed8', 'icon' => 'ti-clock-hour-4', 'trend' => 'up'],
            ['label' => 'Promedio Progreso', 'valor' => '64%', 'sub' => 'Meta: 600 horas', 'color' => '#f59e0b', 'icon' => 'ti-chart-pie', 'trend' => 'neutral'],
        ];
        foreach ($kpis as $k): ?>
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-top: 4px solid <?= $k['color'] ?>;">
            <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                <p style="color: #64748b; font-size: 0.85rem; margin: 0; font-weight: 500;"><?= $k['label'] ?></p>
                <div style="background: <?= $k['color'] ?>18; border-radius: 10px; padding: 8px;">
                    <i class="ti <?= $k['icon'] ?>" style="font-size: 1.2rem; color: <?= $k['color'] ?>;"></i>
                </div>
            </div>
            <h2 style="font-size: 2rem; font-weight: 800; color: <?= $k['color'] ?>; margin: 0 0 6px;"><?= $k['valor'] ?></h2>
            <p style="color: #64748b; font-size: 0.8rem; margin: 0;"><?= $k['sub'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== GRÁFICAS ===== -->
    <div style="display: grid; grid-template-columns: 2fr 1fr; gap: 24px; margin-bottom: 24px;">

        <!-- Gráfica de Asistencia Mensual -->
        <div style="background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px;">
                <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0;"><i class="ti ti-chart-bar" style="color: #059669;"></i> Asistencia Mensual 2026</h3>
                <select style="padding: 6px 12px; border: 1.5px solid #e2e8f0; border-radius: 8px; font-size: 0.8rem; color: #64748b;">
                    <option>Todos los departamentos</option>
                    <option>Soporte Técnico</option>
                    <option>Redes</option>
                </select>
            </div>
            <canvas id="chartAsistencia" height="100"></canvas>
        </div>

        <!-- Gráfica de Pasantes por Departamento -->
        <div style="background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 24px;"><i class="ti ti-chart-donut" style="color: #1d4ed8;"></i> Por Departamento</h3>
            <canvas id="chartDepartamentos" height="180"></canvas>
            <div style="margin-top: 16px;">
                <?php
                $deptos = [
                    ['nombre' => 'Soporte Técnico', 'valor' => 14, 'color' => '#162660'],
                    ['nombre' => 'Redes y Telecom.', 'valor' => 11, 'color' => '#1d4ed8'],
                    ['nombre' => 'Desarrollo SW', 'valor' => 9, 'color' => '#059669'],
                    ['nombre' => 'Administración', 'valor' => 4, 'color' => '#f59e0b'],
                ];
                foreach ($deptos as $d): ?>
                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 8px;">
                    <div style="width: 10px; height: 10px; border-radius: 50%; background: <?= $d['color'] ?>; flex-shrink: 0;"></div>
                    <span style="font-size: 0.8rem; color: #64748b; flex: 1;"><?= $d['nombre'] ?></span>
                    <span style="font-size: 0.85rem; font-weight: 700; color: #1e293b;"><?= $d['valor'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>

    <!-- Gráfica de Progreso de Horas -->
    <div style="background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
        <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0 0 24px;"><i class="ti ti-chart-line" style="color: #162660;"></i> Progreso de Horas por Pasante (Top 5)</h3>
        <canvas id="chartProgreso" height="80"></canvas>
    </div>
</div>

<!-- Chart.js CDN -->
<script src="https://cdn.jsdelivr.net/npm/chart.js@4.4.0/dist/chart.umd.min.js"></script>
<script>
// ===== GRÁFICA 1: Asistencia Mensual =====
const ctx1 = document.getElementById('chartAsistencia').getContext('2d');
new Chart(ctx1, {
    type: 'bar',
    data: {
        labels: ['Ene', 'Feb', 'Mar', 'Abr', 'May', 'Jun', 'Jul', 'Ago', 'Sep', 'Oct', 'Nov', 'Dic'],
        datasets: [
            {
                label: 'Presentes',
                data: [35, 38, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                backgroundColor: 'rgba(5, 150, 105, 0.8)',
                borderRadius: 6,
            },
            {
                label: 'Ausentes',
                data: [5, 3, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                backgroundColor: 'rgba(239, 68, 68, 0.7)',
                borderRadius: 6,
            },
            {
                label: 'Con Permiso',
                data: [5, 4, 0, 0, 0, 0, 0, 0, 0, 0, 0, 0],
                backgroundColor: 'rgba(245, 158, 11, 0.7)',
                borderRadius: 6,
            }
        ]
    },
    options: {
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            y: { beginAtZero: true, grid: { color: '#f1f5f9' } },
            x: { grid: { display: false } }
        }
    }
});

// ===== GRÁFICA 2: Pasantes por Departamento =====
const ctx2 = document.getElementById('chartDepartamentos').getContext('2d');
new Chart(ctx2, {
    type: 'doughnut',
    data: {
        labels: ['Soporte Técnico', 'Redes y Telecom.', 'Desarrollo SW', 'Administración'],
        datasets: [{
            data: [14, 11, 9, 4],
            backgroundColor: ['#162660', '#1d4ed8', '#059669', '#f59e0b'],
            borderWidth: 0,
            hoverOffset: 8
        }]
    },
    options: {
        responsive: true,
        cutout: '65%',
        plugins: { legend: { display: false } }
    }
});

// ===== GRÁFICA 3: Progreso de Horas =====
const ctx3 = document.getElementById('chartProgreso').getContext('2d');
new Chart(ctx3, {
    type: 'bar',
    data: {
        labels: ['García, M.', 'López, C.', 'Pérez, A.', 'Hernández, L.', 'Ramírez, S.'],
        datasets: [
            {
                label: 'Horas Acumuladas',
                data: [432, 270, 546, 0, 600],
                backgroundColor: 'rgba(22, 38, 96, 0.8)',
                borderRadius: 8,
            },
            {
                label: 'Meta (600 hrs)',
                data: [600, 600, 600, 600, 600],
                backgroundColor: 'rgba(22, 38, 96, 0.1)',
                borderRadius: 8,
            }
        ]
    },
    options: {
        indexAxis: 'y',
        responsive: true,
        plugins: { legend: { position: 'top' } },
        scales: {
            x: { beginAtZero: true, max: 650, grid: { color: '#f1f5f9' } },
            y: { grid: { display: false } }
        }
    }
});
</script>
