<?php
/**
 * Vista: Analíticas del Sistema — Bento Premium
 * Datos reales desde AnaliticasController
 */

// JSON encode para JavaScript
$jsPresentes    = json_encode($seriesPresentes    ?? array_fill(0,12,0));
$jsJustificados = json_encode($seriesJustificados ?? array_fill(0,12,0));
$jsAusentes     = json_encode($seriesAusentes     ?? array_fill(0,12,0));
$jsHorasMes     = json_encode($seriesHorasMes     ?? array_fill(0,12,0));
$jsHeatmap      = json_encode($heatmap            ?? array_fill(0,31,0));

// Departamentos para donut
$deptLabels = [];
$deptData   = [];
foreach ($porDepartamento ?? [] as $d) {
    $deptLabels[] = $d->departamento ?? 'N/A';
    $deptData[]   = (int)($d->total ?? 0);
}
$jsDeptLabels = json_encode($deptLabels);
$jsDeptData   = json_encode($deptData);
?>

<style>
/* ── BENTO GRID ──────────────────────────────────── */
.bento-grid {
    display: grid;
    grid-template-columns: repeat(12, 1fr);
    gap: 18px;
    margin-bottom: 20px;
}
.bento-card {
    background: white;
    border-radius: 20px;
    padding: 24px;
    box-shadow: 0 1px 8px rgba(0,0,0,0.06), 0 4px 20px rgba(0,0,0,0.04);
    transition: transform 0.2s ease, box-shadow 0.2s ease;
    position: relative;
    overflow: hidden;
}
.bento-card:hover {
    transform: translateY(-2px);
    box-shadow: 0 4px 20px rgba(0,0,0,0.1);
}
/* Span helpers */
.col-3 { grid-column: span 3; }
.col-4 { grid-column: span 4; }
.col-5 { grid-column: span 5; }
.col-6 { grid-column: span 6; }
.col-7 { grid-column: span 7; }
.col-8 { grid-column: span 8; }
.col-12 { grid-column: span 12; }

/* ── KPI CARD ─────────────────────────────────────── */
.kpi-bento-icon {
    width: 48px; height: 48px;
    border-radius: 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 22px;
    margin-bottom: 16px;
}
.kpi-bento-value {
    font-size: 2.2rem;
    font-weight: 800;
    line-height: 1;
    margin-bottom: 6px;
}
.kpi-bento-label {
    font-size: 0.82rem;
    color: #64748b;
    font-weight: 500;
    margin-bottom: 10px;
}
.kpi-bento-trend {
    display: inline-flex;
    align-items: center;
    gap: 4px;
    font-size: 0.75rem;
    font-weight: 700;
    padding: 3px 10px;
    border-radius: 20px;
}
.trend-up   { background: #dcfce7; color: #16a34a; }
.trend-down { background: #fee2e2; color: #dc2626; }
.trend-neu  { background: #f1f5f9; color: #64748b; }

/* Accent lines */
.accent-green  { border-left: 4px solid #22c55e; }
.accent-blue   { border-left: 4px solid #162660; }
.accent-amber  { border-left: 4px solid #f59e0b; }
.accent-indigo { border-left: 4px solid #6366f1; }

/* ── CARD HEADER ──────────────────────────────────── */
.bento-card-header {
    display: flex;
    align-items: center;
    justify-content: space-between;
    margin-bottom: 20px;
}
.bento-card-title {
    display: flex;
    align-items: center;
    gap: 10px;
    font-size: 0.95rem;
    font-weight: 700;
    color: #1e293b;
}
.bento-card-title i { font-size: 1.1rem; }
.chart-badge {
    font-size: 0.72rem;
    padding: 4px 10px;
    border-radius: 20px;
    font-weight: 600;
    background: #f1f5f9;
    color: #64748b;
}

/* ── TOP 5 RANKING ─────────────────────────────────── */
.ranking-item {
    display: flex;
    align-items: center;
    gap: 14px;
    padding: 12px 0;
    border-bottom: 1px solid #f8fafc;
}
.ranking-item:last-child { border-bottom: none; }
.rank-number {
    width: 32px; height: 32px;
    border-radius: 50%;
    display: flex; align-items: center; justify-content: center;
    font-size: 0.85rem;
    font-weight: 900;
    flex-shrink: 0;
}
.rank-1 { background: linear-gradient(135deg,#fbbf24,#f59e0b); color: white; }
.rank-2 { background: linear-gradient(135deg,#94a3b8,#64748b); color: white; }
.rank-3 { background: linear-gradient(135deg,#d97706,#b45309); color: white; }
.rank-other { background: #f1f5f9; color: #475569; }
.rank-avatar {
    width: 40px; height: 40px;
    border-radius: 12px;
    background: linear-gradient(135deg, #162660, #3b82f6);
    display: flex; align-items: center; justify-content: center;
    color: white; font-weight: 800; font-size: 0.85rem;
    flex-shrink: 0;
}
.rank-info { flex: 1; min-width: 0; }
.rank-name { font-weight: 700; font-size: 0.88rem; color: #1e293b; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; }
.rank-sub  { font-size: 0.75rem; color: #94a3b8; margin-top: 2px; }
.rank-score {
    font-weight: 800;
    font-size: 1rem;
    color: #162660;
    flex-shrink: 0;
}
.rank-progress-bar {
    height: 4px;
    background: #f1f5f9;
    border-radius: 99px;
    margin-top: 6px;
    overflow: hidden;
}
.rank-progress-fill {
    height: 100%;
    border-radius: 99px;
    background: linear-gradient(90deg, #162660, #3b82f6);
    transition: width 1s ease;
}

/* ── RESPONSIVE ──────────────────────────────────── */
@media (max-width: 1200px) {
    .col-3 { grid-column: span 6; }
    .col-4 { grid-column: span 6; }
    .col-5 { grid-column: span 12; }
    .col-7 { grid-column: span 12; }
    .col-8 { grid-column: span 12; }
}
@media (max-width: 768px) {
    .col-3, .col-4, .col-5, .col-6, .col-7, .col-8 { grid-column: span 12; }
}
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">

    <!-- ===== BANNER ===== -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:24px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-40px;right:-40px;width:220px;height:220px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="position:absolute;bottom:-50px;left:-30px;width:180px;height:180px;background:rgba(255,255,255,0.04);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:16px;padding:14px;">
                <i class="ti ti-chart-dots-3" style="font-size:34px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.9rem;font-weight:800;margin:0;letter-spacing:-0.5px;">Analíticas del Sistema</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;">
                    <i class="ti ti-clock"></i> <?= date('d M Y, H:i') ?> · Datos en tiempo real
                </p>
            </div>
        </div>
        <div style="z-index:1;text-align:right;">
            <div style="background:rgba(255,255,255,0.12);border-radius:12px;padding:10px 18px;color:rgba(255,255,255,0.9);font-size:0.82rem;font-weight:600;">
                <i class="ti ti-database"></i> <?= number_format($kpiHoras ?? 0) ?> horas<br>
                <span style="opacity:0.7;">acumuladas en el sistema</span>
            </div>
        </div>
    </div>

    <!-- ===== KPIs ROW ===== -->
    <div class="bento-grid">

        <!-- KPI: Tasa de Asistencia -->
        <div class="bento-card col-3 accent-green slide-up">
            <div class="kpi-bento-icon" style="background:#dcfce7;color:#16a34a;">
                <i class="ti ti-trending-up"></i>
            </div>
            <div class="kpi-bento-value" style="color:#16a34a;" data-kpi-value="<?= $kpiTasa ?? 0 ?>">0</div>
            <div class="kpi-bento-label">Tasa de Asistencia <span style="font-size:0.7rem;color:#94a3b8;">(30 días)</span></div>
            <span class="kpi-bento-trend trend-up"><i class="ti ti-arrow-up"></i> Últimos 30 días</span>
        </div>

        <!-- KPI: Pasantes Activos -->
        <div class="bento-card col-3 accent-blue slide-up" style="animation-delay:0.1s;">
            <div class="kpi-bento-icon" style="background:#dbeafe;color:#162660;">
                <i class="ti ti-users"></i>
            </div>
            <div class="kpi-bento-value" style="color:#162660;" data-kpi-value="<?= $kpiActivos ?? 0 ?>">0</div>
            <div class="kpi-bento-label">Pasantes Activos</div>
            <span class="kpi-bento-trend trend-neu"><i class="ti ti-point"></i> En pasantía</span>
        </div>

        <!-- KPI: Horas Acumuladas -->
        <div class="bento-card col-3 accent-indigo slide-up" style="animation-delay:0.2s;">
            <div class="kpi-bento-icon" style="background:#e0e7ff;color:#6366f1;">
                <i class="ti ti-clock-hour-4"></i>
            </div>
            <div class="kpi-bento-value" style="color:#6366f1;" data-kpi-value="<?= $kpiHoras ?? 0 ?>">0</div>
            <div class="kpi-bento-label">Horas Acumuladas</div>
            <span class="kpi-bento-trend trend-up"><i class="ti ti-arrow-up"></i> Total sistema</span>
        </div>

        <!-- KPI: Progreso Promedio -->
        <div class="bento-card col-3 accent-amber slide-up" style="animation-delay:0.3s;">
            <div class="kpi-bento-icon" style="background:#fef3c7;color:#f59e0b;">
                <i class="ti ti-chart-pie"></i>
            </div>
            <div class="kpi-bento-value" style="color:#f59e0b;" data-kpi-value="<?= $kpiProgreso ?? 0 ?>">0</div>
            <div class="kpi-bento-label">Progreso Promedio (%)</div>
            <span class="kpi-bento-trend trend-neu"><i class="ti ti-target"></i> Meta: 100%</span>
        </div>

    </div><!-- /KPIs -->

    <!-- ===== FILA 2: Área + Donut ===== -->
    <div class="bento-grid">

        <!-- Gráfica Área: Asistencias por Mes -->
        <div class="bento-card col-8 slide-up" style="animation-delay:0.4s;">
            <div class="bento-card-header">
                <div class="bento-card-title">
                    <i class="ti ti-chart-area" style="color:#22c55e;"></i>
                    Asistencias por Mes — <?= date('Y') ?>
                </div>
                <span class="chart-badge">Área interactiva</span>
            </div>
            <div id="chart-area-asistencias"></div>
        </div>

        <!-- Gráfica Donut: Por Departamento -->
        <div class="bento-card col-4 slide-up" style="animation-delay:0.5s;">
            <div class="bento-card-header">
                <div class="bento-card-title">
                    <i class="ti ti-chart-donut" style="color:#162660;"></i>
                    Por Departamento
                </div>
            </div>
            <div id="chart-donut-depto"></div>
            <?php if (!empty($porDepartamento)): ?>
            <div style="margin-top:12px;">
                <?php foreach ($porDepartamento as $d): ?>
                <div style="display:flex;align-items:center;justify-content:space-between;padding:5px 0;font-size:0.8rem;">
                    <span style="color:#64748b;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;max-width:70%;"><?= htmlspecialchars($d->departamento ?? '—') ?></span>
                    <span style="font-weight:700;color:#1e293b;"><?= $d->total ?? 0 ?></span>
                </div>
                <?php endforeach; ?>
            </div>
            <?php else: ?>
            <div style="text-align:center;padding:20px;color:#94a3b8;font-size:0.85rem;">
                <i class="ti ti-database-off" style="font-size:28px;display:block;margin-bottom:8px;"></i>
                Sin datos de departamentos
            </div>
            <?php endif; ?>
        </div>

    </div><!-- /Fila 2 -->

    <!-- ===== FILA 3: Heatmap + Tendencia Horas ===== -->
    <div class="bento-grid">

        <!-- Heatmap: Asistencias por día -->
        <div class="bento-card col-5 slide-up" style="animation-delay:0.6s;">
            <div class="bento-card-header">
                <div class="bento-card-title">
                    <i class="ti ti-grid-dots" style="color:#ef4444;"></i>
                    Heatmap — <?= date('F Y', mktime(0,0,0,date('m'),1,date('Y'))) ?>
                </div>
                <span class="chart-badge">Patrón diario</span>
            </div>
            <div id="chart-heatmap"></div>
        </div>

        <!-- Tendencia Horas por Mes -->
        <div class="bento-card col-7 slide-up" style="animation-delay:0.65s;">
            <div class="bento-card-header">
                <div class="bento-card-title">
                    <i class="ti ti-chart-line" style="color:#6366f1;"></i>
                    Tendencia de Horas Supervisadas
                </div>
                <span class="chart-badge">Estimado mensual</span>
            </div>
            <div id="chart-horas-tendencia"></div>
        </div>

    </div><!-- /Fila 3 -->

    <!-- ===== FILA 4: TOP 5 + Radar ===== -->
    <div class="bento-grid">

        <!-- TOP 5 Pasantes Destacados -->
        <div class="bento-card col-7 slide-up" style="animation-delay:0.7s;">
            <div class="bento-card-header">
                <div class="bento-card-title">
                    <i class="ti ti-trophy" style="color:#f59e0b;font-size:1.3rem;"></i>
                    🏆 Top 5 Pasantes Destacados
                </div>
                <span class="chart-badge">Score = 60% eval + 40% horas</span>
            </div>

            <?php if (!empty($top5Pasantes)): ?>
            <?php foreach ($top5Pasantes as $i => $p):
                $nombre    = trim(($p->nombres ?? '') . ' ' . ($p->apellidos ?? ''));
                $iniciales = strtoupper(mb_substr($p->nombres ?? 'P', 0, 1) . mb_substr($p->apellidos ?? '', 0, 1));
                $depto     = $p->departamento ?? 'Sin departamento';
                $progreso  = (float)($p->progreso ?? 0);
                $eval      = (float)($p->prom_eval ?? 0);
                $score     = (float)($p->score_total ?? 0);
                $rankClass = match($i) { 0=>'rank-1', 1=>'rank-2', 2=>'rank-3', default=>'rank-other' };
                $gradients = [
                    'linear-gradient(135deg,#162660,#3b82f6)',
                    'linear-gradient(135deg,#6366f1,#8b5cf6)',
                    'linear-gradient(135deg,#059669,#10b981)',
                    'linear-gradient(135deg,#f59e0b,#ef4444)',
                    'linear-gradient(135deg,#ec4899,#f43f5e)',
                ];
                $grad = $gradients[$i] ?? $gradients[0];
            ?>
            <div class="ranking-item">
                <div class="rank-number <?= $rankClass ?>"><?= $i+1 ?></div>
                <div class="rank-avatar" style="background:<?= $grad ?>;"><?= htmlspecialchars($iniciales) ?></div>
                <div class="rank-info">
                    <div class="rank-name"><?= htmlspecialchars($nombre) ?></div>
                    <div class="rank-sub">
                        <?= htmlspecialchars($depto) ?>
                        <?php if ($eval > 0): ?> · ⭐ <?= number_format($eval, 1) ?>/5<?php endif; ?>
                    </div>
                    <div class="rank-progress-bar">
                        <div class="rank-progress-fill" style="width:<?= min(100,$progreso) ?>%;background:<?= $grad ?>;"></div>
                    </div>
                    <div style="font-size:0.7rem;color:#94a3b8;margin-top:3px;"><?= number_format($progreso,1) ?>% completado</div>
                </div>
                <div class="rank-score" style="background:<?= $grad ?>;-webkit-background-clip:text;-webkit-text-fill-color:transparent;">
                    <?= number_format($score, 1) ?>
                </div>
            </div>
            <?php endforeach; ?>
            <?php else: ?>
            <div style="text-align:center;padding:32px;color:#94a3b8;">
                <i class="ti ti-trophy-off" style="font-size:40px;display:block;margin-bottom:10px;"></i>
                No hay pasantes activos con evaluaciones aún.<br>
                <small>El ranking se generará automáticamente cuando haya evaluaciones registradas.</small>
            </div>
            <?php endif; ?>
        </div>

        <!-- Radar: Métricas comparativas -->
        <div class="bento-card col-5 slide-up" style="animation-delay:0.75s;">
            <div class="bento-card-header">
                <div class="bento-card-title">
                    <i class="ti ti-radar" style="color:#6366f1;"></i>
                    Índice de Rendimiento
                </div>
            </div>
            <div id="chart-radar"></div>
            <p style="text-align:center;font-size:0.75rem;color:#94a3b8;margin-top:8px;">
                Comparativa del sistema actual vs. óptimo esperado
            </p>
        </div>

    </div><!-- /Fila 4 -->

</div><!-- /dashboard-container -->

<!-- ===== SCRIPTS: ApexCharts (local) ===== -->
<script src="<?= URLROOT ?>/js/apexcharts.min.js"></script>
<script>
document.addEventListener('DOMContentLoaded', function () {

    const PALETTE = ['#162660','#3b82f6','#22c55e','#f59e0b','#ef4444','#6366f1','#ec4899','#14b8a6'];
    const FONT    = 'Plus Jakarta Sans, Inter, sans-serif';
    const GRID_CLR = '#f1f5f9';

    const baseChart = {
        fontFamily: FONT,
        toolbar: { show: false },
        animations: { enabled: true, speed: 600, animateGradually: { enabled: true, delay: 80 } }
    };

    // ── 1. ÁREA: Asistencias por mes ──────────────────────────────
    new ApexCharts(document.getElementById('chart-area-asistencias'), {
        chart: { ...baseChart, type: 'area', height: 290 },
        series: [
            { name: 'Presentes',    data: <?= $jsPresentes ?> },
            { name: 'Justificados', data: <?= $jsJustificados ?> },
            { name: 'Ausentes',     data: <?= $jsAusentes ?> }
        ],
        colors: ['#22c55e','#f59e0b','#ef4444'],
        fill: {
            type: 'gradient',
            gradient: {
                opacityFrom: 0.55,
                opacityTo:   0.05,
                shadeIntensity: 1,
                type: 'vertical'
            }
        },
        dataLabels: { enabled: false },
        stroke: { curve: 'smooth', width: 2.5 },
        xaxis: {
            categories: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
            axisBorder: { show: false },
            axisTicks:  { show: false },
            labels: { style: { colors: '#94a3b8', fontSize: '12px', fontFamily: FONT } }
        },
        yaxis: {
            labels: { style: { colors: '#94a3b8', fontSize: '11px', fontFamily: FONT } }
        },
        grid: { borderColor: GRID_CLR, strokeDashArray: 5 },
        legend: { position: 'top', horizontalAlign: 'right', fontSize: '12px', fontFamily: FONT },
        tooltip: { theme: 'light', x: { show: true } }
    }).render();

    // ── 2. DONUT: Por departamento ────────────────────────────────
    const deptLabels = <?= $jsDeptLabels ?>;
    const deptData   = <?= $jsDeptData ?>;
    if (deptData.length > 0) {
        new ApexCharts(document.getElementById('chart-donut-depto'), {
            chart: { ...baseChart, type: 'donut', height: 220 },
            series: deptData,
            labels: deptLabels,
            colors: PALETTE,
            dataLabels: { enabled: false },
            plotOptions: {
                pie: {
                    donut: {
                        size: '72%',
                        labels: {
                            show: true,
                            total: {
                                show: true,
                                label: 'Pasantes',
                                fontSize: '13px',
                                fontFamily: FONT,
                                color: '#64748b',
                                formatter: w => w.globals.seriesTotals.reduce((a,b) => a+b, 0)
                            }
                        }
                    }
                }
            },
            legend: { show: false },
            tooltip: { theme: 'light', style: { fontFamily: FONT } }
        }).render();
    } else {
        document.getElementById('chart-donut-depto').innerHTML = '<p style="text-align:center;padding:30px;color:#94a3b8;font-size:0.85rem;"><i class="ti ti-database-off" style="font-size:28px;display:block;margin-bottom:8px;"></i>Sin datos</p>';
    }

    // ── 3. HEATMAP: Asistencias por día ──────────────────────────
    const heatmapRaw = <?= $jsHeatmap ?>;
    const heatmapSeries = [{
        name: 'Asistencias',
        data: heatmapRaw.map((v, i) => ({ x: 'Día ' + (i+1), y: v }))
    }];
    new ApexCharts(document.getElementById('chart-heatmap'), {
        chart: { ...baseChart, type: 'heatmap', height: 240 },
        series: heatmapSeries,
        colors: ['#162660'],
        dataLabels: { enabled: false },
        xaxis: {
            labels: { show: false },
            axisBorder: { show: false },
            axisTicks:  { show: false }
        },
        plotOptions: {
            heatmap: {
                shadeIntensity: 0.5,
                radius: 4,
                colorScale: {
                    ranges: [
                        { from: 0,  to: 0,  color: '#f1f5f9', name: 'Sin datos' },
                        { from: 1,  to: 5,  color: '#bfdbfe', name: 'Bajo'      },
                        { from: 6,  to: 15, color: '#3b82f6', name: 'Moderado'  },
                        { from: 16, to: 999,color: '#162660', name: 'Alto'      }
                    ]
                }
            }
        },
        grid: { padding: { right: 0, left: 0 } },
        tooltip: {
            theme: 'light',
            y: { formatter: v => v + ' registros' }
        }
    }).render();

    // ── 4. LÍNEA: Tendencia Horas Supervisadas ────────────────────
    new ApexCharts(document.getElementById('chart-horas-tendencia'), {
        chart: { ...baseChart, type: 'line', height: 240 },
        series: [{ name: 'Horas supervisadas (estimado)', data: <?= $jsHorasMes ?> }],
        colors: ['#6366f1'],
        stroke: { curve: 'smooth', width: 3 },
        fill: {
            type: 'gradient',
            gradient: { opacityFrom: 0.25, opacityTo: 0 }
        },
        markers: { size: 5, colors: ['#6366f1'], strokeColors: '#fff', strokeWidth: 2 },
        dataLabels: { enabled: false },
        xaxis: {
            categories: ['Ene','Feb','Mar','Abr','May','Jun','Jul','Ago','Sep','Oct','Nov','Dic'],
            axisBorder: { show: false },
            axisTicks: { show: false },
            labels: { style: { colors: '#94a3b8', fontSize: '12px', fontFamily: FONT } }
        },
        yaxis: {
            labels: {
                style: { colors: '#94a3b8', fontSize: '11px', fontFamily: FONT },
                formatter: v => v.toLocaleString()
            }
        },
        grid: { borderColor: GRID_CLR, strokeDashArray: 5 },
        tooltip: { theme: 'light', y: { formatter: v => v.toLocaleString() + ' hrs' } }
    }).render();

    // ── 5. RADAR: Índice de rendimiento ──────────────────────────
    const tasaVal    = <?= $kpiTasa ?? 0 ?>;
    const activosVal = Math.min(100, (<?= $kpiActivos ?? 0 ?> / 50) * 100);
    const progVal    = <?= $kpiProgreso ?? 0 ?>;
    const horasVal   = Math.min(100, (<?= $kpiHoras ?? 0 ?> / 5000) * 100);

    new ApexCharts(document.getElementById('chart-radar'), {
        chart: { ...baseChart, type: 'radar', height: 300 },
        series: [
            { name: 'Sistema Actual', data: [tasaVal, activosVal, progVal, horasVal, 0] },
            { name: 'Óptimo',         data: [100, 100, 100, 100, 100]                  }
        ],
        colors: ['#162660', '#e2e8f0'],
        labels: ['Asistencia', 'Capacidad', 'Progreso', 'Horas', 'Evaluaciones'],
        fill: { opacity: [0.35, 0.1] },
        stroke: { width: [2, 1], dashArray: [0, 5] },
        markers: { size: [4, 0] },
        plotOptions: {
            radar: {
                polygons: {
                    strokeColors: '#e2e8f0',
                    fill: { colors: ['#f8fafc', '#fff'] }
                }
            }
        },
        dataLabels: { enabled: false },
        legend: { position: 'bottom', fontSize: '12px', fontFamily: FONT },
        tooltip: { theme: 'light', style: { fontFamily: FONT } },
        yaxis: { show: false }
    }).render();

});
</script>
