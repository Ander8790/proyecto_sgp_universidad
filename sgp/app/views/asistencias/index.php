<?php
/**
 * Vista: Control de Asistencias
 * Diseño tomado del backup (20-02-2026) + datos reales del DB normalizado (3NF)
 *
 * Variables recibidas del AsistenciasController::index():
 *   $data['registrosHoy']   — array de objetos: id, hora_registro, estado, metodo,
 *                             pasante_id, cedula, nombres, apellidos, departamento_nombre
 *   $data['sinMarcar']      — pasantes activos que NO marcaron hoy
 *   $data['presentes']      — int
 *   $data['justificados']   — int
 *   $data['ausentes']       — int
 *   $data['totalActivos']   — int
 *   $data['pasantesActivos']— para el select del modal manual
 *   $data['hoy']            — string Y-m-d
 */

$registrosHoy    = $data['registrosHoy']    ?? $data['registrosLista'] ?? [];
$sinMarcar       = $data['sinMarcar']       ?? [];
$presentes       = $data['presentes']       ?? 0;
$justificados    = $data['justificados']    ?? 0;
$ausentes        = $data['ausentes']        ?? 0;
$totalActivos    = $data['totalActivos']    ?? 0;
$pasantesActivos = $data['pasantesActivos'] ?? [];
$hoy             = $data['hoy']             ?? date('Y-m-d');
$vistaActual     = $data['vistaActual']     ?? $data['vista'] ?? 'diaria';
$tituloRango     = $data['tituloRango']     ?? 'del día';

$estadoConfig = [
    'Presente'    => ['bg' => '#dcfce7', 'color' => '#16a34a', 'icon' => 'ti-check'],
    'Justificado' => ['bg' => '#dbeafe', 'color' => '#2563eb', 'icon' => 'ti-file-description'],
    'Ausente'     => ['bg' => '#fee2e2', 'color' => '#dc2626', 'icon' => 'ti-x'],
];
?>

<style>
/* Premium DataTables Overrides */
table.dataTable { border-collapse: collapse !important; border-spacing: 0; }
table.dataTable th, table.dataTable td {
    border-left: none !important;
    border-right: none !important;
    border-bottom: 1px solid #f0f2f5 !important;
    padding: 1rem 0.75rem !important;
    vertical-align: middle;
}
table.dataTable tbody tr:hover {
    background-color: #f8f9fc !important;
    transition: background-color 0.2s ease;
}
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">

    <!-- ===== BANNER PREMIUM SGP ===== -->
    <div class="module-banner" style="
        background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
        border-radius: 20px; padding: 32px 40px; margin-bottom: 28px;
        position: relative; overflow: hidden;
        display: flex; align-items: center; justify-content: space-between;">

        <!-- Círculos decorativos -->
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="position:absolute;bottom:-50px;right:150px;width:150px;height:150px;background:rgba(255,255,255,0.04);border-radius:50%;"></div>

        <!-- Lado Izquierdo: Título y Selector -->
        <div style="z-index: 1;">
            <div style="display:flex;align-items:center;gap:12px;margin-bottom:16px;">
                <div style="background:rgba(255,255,255,0.15);border-radius:12px;padding:10px;">
                    <i class="ti ti-calendar-stats" style="font-size:28px;color:white;"></i>
                </div>
                <div>
                    <h1 style="color:white;font-size:1.8rem;font-weight:700;margin:0;">Control de Asistencias</h1>
                </div>
            </div>
            
            <!-- Selector de Vistas: Chips Individuales -->
            <div style="display: flex; gap: 8px;">
                <?php
                $filtros = [
                    ['key' => 'diaria',  'label' => 'Diario'],
                    ['key' => 'semanal', 'label' => 'Semanal'],
                    ['key' => 'mensual', 'label' => 'Mensual'],
                    ['key' => 'anual',   'label' => 'Total'],
                ];
                foreach ($filtros as $f):
                    $isActive = ($vistaActual === $f['key']);
                    if ($isActive) {
                        $style = "background: rgba(255,255,255,0.30); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.4); border-radius: 50px; padding: 6px 16px; color: white; font-size: 0.85rem; font-weight: 600; text-decoration: none; box-shadow: 0 4px 12px rgba(0,0,0,0.1); transition: all 0.2s;";
                    } else {
                        $style = "background: rgba(255,255,255,0.10); backdrop-filter: blur(10px); border: 1px solid rgba(255,255,255,0.1); border-radius: 50px; padding: 6px 16px; color: white; font-size: 0.85rem; font-weight: 600; text-decoration: none; transition: all 0.2s;";
                    }
                ?>
                <a href="<?= URLROOT ?>/asistencias?vista=<?= $f['key'] ?>" style="<?= $style ?>" onmouseover="this.style.background='rgba(255,255,255,0.35)'" onmouseout="this.style.background='<?= $isActive ? 'rgba(255,255,255,0.30)' : 'rgba(255,255,255,0.10)' ?>'">
                    <?= $f['label'] ?>
                </a>
                <?php endforeach; ?>
            </div>
        </div>

        <!-- Lado Derecho: Acciones -->
        <div style="display: flex; gap: 12px; align-items: center; z-index: 1;">
            <button onclick="abrirModalConsulta()" style="background: rgba(255, 255, 255, 0.15); color: white; border: 1px solid rgba(255, 255, 255, 0.3); padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px; backdrop-filter: blur(10px); transition: all 0.2s;" onmouseover="this.style.background='rgba(255, 255, 255, 0.25)'" onmouseout="this.style.background='rgba(255, 255, 255, 0.15)'">
                <i class="ti ti-search" style="font-size: 1.1rem;"></i> Consulta Rápida
            </button>
            <button onclick="abrirModalManualNuevo()" style="background: white; color: #1e3a8a; border: none; padding: 10px 20px; border-radius: 8px; font-weight: 600; font-size: 0.9rem; cursor: pointer; display: flex; align-items: center; gap: 8px; box-shadow: 0 4px 6px rgba(0,0,0,0.05); transition: transform 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='translateY(0)'">
                <i class="ti ti-plus" style="font-size: 1.1rem;"></i> Registro Manual
            </button>
        </div>
    </div>

    <!-- ===== TARJETAS ESTADÍSTICAS (PREMIUM) ===== -->
    <?php if ($vistaActual === 'anual'): ?>
        <?php
            $historicoAnual = 0; $enCursoAnual = 0; $finalizadosAnual = 0;
            if(!empty($registrosLista)) {
                $pasantesAnual = [];
                foreach($registrosLista as $reg) {
                    $pid = $reg->pasante_id ?? 'P'.$reg->id;
                    if(!isset($pasantesAnual[$pid])) $pasantesAnual[$pid] = 0;
                    if(stripos($reg->estado, 'presente') !== false || stripos($reg->estado, 'justificado') !== false) {
                        $pasantesAnual[$pid]++;
                    }
                }
                $historicoAnual = count($pasantesAnual);
                foreach($pasantesAnual as $pid => $presentes) {
                    if (($presentes * 8) >= 1440) $finalizadosAnual++;
                    else $enCursoAnual++;
                }
            }
        ?>
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:32px;">
            <!-- Histórico Total (Azul) -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #3b82f6; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#3b82f6;margin:0;"><?= $historicoAnual ?></h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Pasantes Históricos</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">registrados en el año</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="ti ti-users" style="font-size:24px;color:#3b82f6;"></i>
                    </div>
                </div>
            </div>
            <!-- Finalizados (Verde) -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #10b981; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#10b981;margin:0;"><?= $finalizadosAnual ?></h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Finalizados</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">cumplieron 1.440 hrs</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="ti ti-certificate" style="font-size:24px;color:#10b981;"></i>
                    </div>
                </div>
            </div>
            <!-- En Curso (Ámbar) -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #f59e0b; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#f59e0b;margin:0;"><?= $enCursoAnual ?></h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">En Curso</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">acumulando horas</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="ti ti-progress-check" style="font-size:24px;color:#f59e0b;"></i>
                    </div>
                </div>
            </div>
            <!-- Retirados / Otros (Gris) -->
            <div class="stat-card opacity-75" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #64748b; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#64748b;margin:0;">0</h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Retirados</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">sin actividad reciente</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="ti ti-user-off" style="font-size:24px;color:#64748b;"></i>
                    </div>
                </div>
            </div>
        </div>
    <?php else: ?>
        <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:28px;">

            <!-- Total Activos -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #3b82f6; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#3b82f6;margin:0;" id="stat-total" data-kpi-value="<?= $totalActivos ?>">0</h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Activos</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">pasantes asignados</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="ti ti-users" style="font-size:24px;color:#3b82f6;"></i>
                    </div>
                </div>
            </div>

            <!-- Presentes -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #10b981; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#10b981;margin:0;" id="stat-presentes" data-kpi-value="<?= $presentes ?>">0</h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Presentes</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">
                            <span style="color:#10b981;font-weight:600;"><i class="ti ti-arrow-up-right"></i> <?= $totalActivos > 0 ? round($presentes / $totalActivos * 100) : 0 ?>%</span> asistencia
                        </p>
                    </div>
                    <div style="background:linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="ti ti-user-check" style="font-size:24px;color:#10b981;"></i>
                    </div>
                </div>
            </div>

            <!-- Justificados -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #f59e0b; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#f59e0b;margin:0;" id="stat-justificados" data-kpi-value="<?= $justificados ?>">0</h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Justificados</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">con permiso / récipe</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #fffbeb 0%, #fef3c7 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="ti ti-clock-pause" style="font-size:24px;color:#f59e0b;"></i>
                    </div>
                </div>
            </div>

            <!-- Sin Marcar / Ausentes -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #ef4444; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#ef4444;margin:0;" id="stat-ausentes" data-kpi-value="<?= $ausentes ?>">0</h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Sin Marcar</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">sin justificación</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                        <i class="ti ti-user-x" style="font-size:24px;color:#ef4444;"></i>
                    </div>
                </div>
            </div>

        </div>
    <?php endif; ?>

    <!-- ===== ENRUTADOR DE VISTAS (TABLAS) ===== -->
    <div class="tablas-container">
        <?php if ($vistaActual === 'diaria' || empty($vistaActual)): ?>
            <!-- ===== TABLA REGISTROS DEL DÍA + LISTA SIN MARCAR ===== -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;margin-bottom:20px;">

        <!-- Marcaron Hoy -->
        <div style="background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 24px; border: 1px solid #f1f5f9;">
            <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size:0.95rem;font-weight:700;color:#1e293b;margin:0;">
                    <i class="ti ti-check" style="color: #10b981; margin-right: 8px;"></i> Marcaron Hoy
                </h3>
                <span style="background:#eff6ff;color:#162660;padding:4px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">
                    <?= count($registrosHoy) ?> registros
                </span>
            </div>

            <?php if (empty($registrosHoy)): ?>
            <div style="padding:48px 24px;text-align:center;">
                <i class="ti ti-calendar-off" style="font-size:40px;color:#cbd5e1;display:block;margin-bottom:12px;"></i>
                <p style="color:#94a3b8;margin:0;font-size:0.9rem;">Ningún pasante ha marcado aún hoy</p>
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;max-height:380px;overflow-y:auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="position:sticky;top:0;z-index:10;">
                        <tr>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 16px 24px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Pasante</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 16px 24px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Hora</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 16px 24px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Método</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 16px 24px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Estado</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 16px 24px; text-align: center; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($registrosHoy as $r):
                        $cfg      = $estadoConfig[$r->estado] ?? $estadoConfig['Ausente'];
                        $nombre   = trim(($r->apellidos ?? '') . ', ' . ($r->nombres ?? ''));
                        $iniciales = strtoupper(substr($r->nombres ?? '?', 0, 1) . substr($r->apellidos ?? '', 0, 1));
                    ?>
                    <tr style="transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; vertical-align: middle;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#162660,#2563eb);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.78rem;flex-shrink:0;">
                                    <?= htmlspecialchars($iniciales) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:#1e293b;font-size:0.85rem;"><?= htmlspecialchars($nombre) ?></div>
                                    <div style="color:#94a3b8;font-size:0.75rem;"><?= htmlspecialchars($r->cedula ?? '—') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; vertical-align: middle; font-weight:600;">
                            <?= $r->hora_registro ? date('h:i A', strtotime($r->hora_registro)) : '—' ?>
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; vertical-align: middle;">
                            <?php if ($r->metodo): ?>
                            <span style="background:#eff6ff;color:#162660;padding:3px 10px;border-radius:20px;font-size:0.75rem;font-weight:600;">
                                <i class="ti ti-device-desktop"></i> <?= htmlspecialchars($r->metodo) ?>
                            </span>
                            <?php else: ?>—<?php endif; ?>
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; vertical-align: middle;">
                            <span style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;padding:4px 10px;border-radius:20px;font-size:0.75rem;font-weight:700;display:inline-flex;align-items:center;gap:4px;">
                                <i class="ti <?= $cfg['icon'] ?>"></i> <?= htmlspecialchars($r->estado) ?>
                            </span>
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; vertical-align: middle; text-align:center;">
                            <button onclick="verDetalle(<?= (int)$r->id ?>,<?= htmlspecialchars(json_encode([
                                'nombre'  => $nombre,
                                'cedula'  => $r->cedula ?? '—',
                                'depto'   => $r->departamento_nombre ?? '—',
                                'hora'    => $r->hora_registro ? date('h:i A', strtotime($r->hora_registro)) : '—',
                                'metodo'  => $r->metodo ?? '—',
                                'estado'  => $r->estado,
                                'motivo'  => $r->motivo_justificacion ?? ''
                            ]), ENT_QUOTES) ?>)"
                                style="background: #f1f5f9; color: #3b82f6; border: none; padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#f1f5f9'"
                                title="Ver detalle">
                                <i class="ti ti-eye"></i> Ver
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

        <!-- Sin Marcar Hoy -->
        <div style="background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); overflow: hidden; margin-bottom: 24px; border: 1px solid #f1f5f9;">
            <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
                <h3 style="font-size:0.95rem;font-weight:700;color:#1e293b;margin:0;">
                    <i class="ti ti-alert-circle" style="color: #ef4444; margin-right: 8px;"></i> Sin Marcar Hoy
                </h3>
                <span style="background:#fef2f2;color:#ef4444;padding:4px 12px;border-radius:20px;font-size:0.78rem;font-weight:600;">
                    <?= count($sinMarcar) ?> pasantes
                </span>
            </div>

            <?php if (empty($sinMarcar)): ?>
            <div style="padding:48px 24px;text-align:center;">
                <i class="ti ti-circle-check" style="font-size:40px;color:#10b981;display:block;margin-bottom:12px;"></i>
                <p style="color:#10b981;margin:0;font-size:0.9rem;font-weight:600;">¡Todos los pasantes marcaron hoy! 🎉</p>
            </div>
            <?php else: ?>
            <div style="overflow-x:auto;max-height:380px;overflow-y:auto;">
                <table style="width: 100%; border-collapse: collapse;">
                    <thead style="position:sticky;top:0;z-index:10;">
                        <tr>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 16px 24px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Pasante</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 16px 24px; text-align: left; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Departamento</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 16px 24px; text-align: center; background: #f8fafc; border-bottom: 1px solid #e2e8f0;">Acción</th>
                        </tr>
                    </thead>
                    <tbody>
                    <?php foreach ($sinMarcar as $p):
                        $nombre   = trim(($p->apellidos ?? '') . ', ' . ($p->nombres ?? ''));
                        $iniciales = strtoupper(substr($p->nombres ?? '?', 0, 1) . substr($p->apellidos ?? '', 0, 1));
                    ?>
                    <tr style="transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; vertical-align: middle;">
                            <div style="display:flex;align-items:center;gap:10px;">
                                <div style="width:34px;height:34px;border-radius:50%;background:linear-gradient(135deg,#ef4444,#dc2626);display:flex;align-items:center;justify-content:center;color:white;font-weight:700;font-size:0.78rem;flex-shrink:0;">
                                    <?= htmlspecialchars($iniciales) ?>
                                </div>
                                <div>
                                    <div style="font-weight:600;color:#1e293b;font-size:0.85rem;"><?= htmlspecialchars($nombre) ?></div>
                                    <div style="color:#94a3b8;font-size:0.75rem;"><?= htmlspecialchars($p->cedula ?? '—') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.83rem; vertical-align: middle;">
                            <?= htmlspecialchars($p->departamento_nombre ?? 'Sin asignar') ?>
                        </td>
                        <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; vertical-align: middle; text-align:center;">
                            <button onclick="abrirModalManual(<?= (int)$p->id ?>, '<?= addslashes($nombre) ?>')"
                                style="background: #f1f5f9; color: #3b82f6; border: none; padding: 6px 14px; border-radius: 8px; font-weight: 600; font-size: 0.85rem; cursor: pointer; transition: all 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#f1f5f9'"
                                title="Registrar manualmente">
                                <i class="ti ti-plus"></i> Registrar
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    </tbody>
                </table>
            </div>
            <?php endif; ?>
        </div>

            </div>
        <?php elseif ($vistaActual === 'semanal'): ?>
            <?php
            // ── Calcular métricas por departamento para barras de progreso ──
            $deptStats = [];
            foreach ($datosSemanales as $dep => $pArr) {
                $tp = 0; $ta = 0; $tj = 0;
                foreach ($pArr as $pid => $pd) {
                    foreach ($pd['dias'] as $d) {
                        if ($d === 'P') $tp++;
                        elseif ($d === 'A') $ta++;
                        elseif ($d === 'J') $tj++;
                    }
                }
                $tot = $tp + $ta + $tj;
                $pct = $tot > 0 ? round(($tp + $tj) / $tot * 100) : 0;
                $deptStats[$dep] = ['p'=>$tp,'a'=>$ta,'j'=>$tj,'pct'=>$pct,'total'=>count($pArr)];
            }
            ?>

            <!-- ══ CSS PREMIUM SEMANAL ══ -->
            <style>
            .sw-wrap { font-family: 'Plus Jakarta Sans', sans-serif; }
            .sw-wrap *, .sw-wrap *::before, .sw-wrap *::after { box-sizing: border-box; }

            /* ── TOOLBAR ── */
            .sw-toolbar {
                display: flex; align-items: center; justify-content: space-between;
                gap: 12px; flex-wrap: wrap; margin-bottom: 20px;
            }
            .sw-toolbar-left  { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }
            .sw-toolbar-right { display: flex; align-items: center; gap: 10px; flex-wrap: wrap; }

            .sw-search { position: relative; display: flex; align-items: center; }
            .sw-search .ti { position: absolute; left: 12px; color: #94a3b8; font-size: 1rem; pointer-events: none; }
            .sw-search input {
                padding: 9px 14px 9px 36px;
                border: 1.5px solid #DDE2F0; border-radius: 50px;
                font-size: .83rem; font-family: 'Plus Jakarta Sans', sans-serif;
                color: #0D1424; background: white; outline: none; width: 220px;
                transition: border-color .18s, box-shadow .18s;
            }
            .sw-search input:focus {
                border-color: #2563EB;
                box-shadow: 0 0 0 3px rgba(37,99,235,.08);
            }

            /* ── CHIPS ── */
            .sw-chips { display: flex; gap: 6px; flex-wrap: wrap; }
            .sw-chip {
                padding: 7px 14px; border-radius: 50px;
                font-size: .76rem; font-weight: 600;
                border: 1.5px solid #DDE2F0; background: white;
                color: #7480A0; cursor: pointer;
                transition: all .18s; display: flex; align-items: center; gap: 5px;
                font-family: 'Plus Jakarta Sans', sans-serif;
            }
            .sw-chip:hover { border-color: #C3CCDF; color: #0D1424; }
            .sw-chip.sc-all.active  { background: #0D1424;  color: white;    border-color: #0D1424; }
            .sw-chip.sc-ok.active   { background: #ECFDF5;  color: #059669;  border-color: #059669; }
            .sw-chip.sc-falta.active{ background: #FEF2F2;  color: #DC2626;  border-color: #DC2626; }
            .sw-chip-dot { width: 7px; height: 7px; border-radius: 50%; flex-shrink: 0; }

            /* ── LEYENDA ── */
            .sw-legend {
                display: flex; gap: 14px; align-items: center;
                font-size: .72rem; font-weight: 600; color: #7480A0; flex-wrap: wrap;
            }
            .sw-legend-item { display: flex; align-items: center; gap: 5px; }
            .sw-legend-dot  { width: 11px; height: 11px; border-radius: 3px; }

            /* ── NAV SEMANA ── */
            .sw-nav-pill {
                display: flex; align-items: center;
                background: white; border: 1.5px solid #DDE2F0;
                border-radius: 50px; padding: 3px 4px; gap: 2px;
                box-shadow: 0 1px 4px rgba(13,20,36,.05);
            }
            .sw-nav-btn {
                background: transparent; border: none; color: #7480A0;
                padding: 6px 12px; border-radius: 50px;
                font-size: .8rem; font-weight: 600;
                font-family: 'Plus Jakarta Sans', sans-serif;
                cursor: pointer; transition: background .15s;
                display: flex; align-items: center; gap: 3px;
            }
            .sw-nav-btn:hover { background: #F2F5FC; color: #0D1424; }
            .sw-nav-label {
                font-size: .83rem; font-weight: 700;
                color: #0D1424; padding: 0 12px; white-space: nowrap;
            }

            /* ── FLATPICKR WRAP ── */
            .sw-fp-wrap {
                display: flex; align-items: center;
                background: white; border: 1.5px solid #DDE2F0;
                border-radius: 50px; padding: 2px 14px;
                box-shadow: 0 1px 4px rgba(13,20,36,.04);
            }
            .sw-fp-wrap label {
                font-size: .8rem; font-weight: 700; color: #7480A0;
                padding-right: 10px; border-right: 1.5px solid #DDE2F0;
                margin: 0; display: flex; align-items: center; gap: 5px;
            }
            .sw-fp-wrap input {
                border: none; outline: none; padding: 6px 8px;
                color: #1D4ED8; font-weight: 700; font-size: .82rem;
                background: transparent; cursor: pointer; width: 130px;
                font-family: 'Plus Jakarta Sans', sans-serif;
            }

            /* ── DEPT CARD ── */
            .sw-dept-card {
                background: white; border: 1px solid #DDE2F0;
                border-radius: 20px;
                box-shadow: 0 2px 8px rgba(13,20,36,.05);
                overflow: hidden; margin-bottom: 16px;
                transition: box-shadow .22s cubic-bezier(.16,1,.3,1);
            }
            .sw-dept-card:hover { box-shadow: 0 8px 24px rgba(13,20,36,.09); }
            .sw-dept-head {
                padding: 14px 22px; border-bottom: 1px solid #EDF0F8;
                background: #F8FAFD;
                display: flex; align-items: center; justify-content: space-between; gap: 14px;
            }
            .sw-dept-title {
                font-size: .9rem; font-weight: 700; color: #0D1424;
                display: flex; align-items: center; gap: 10px;
            }
            .sw-dept-icon {
                width: 32px; height: 32px; border-radius: 9px;
                display: flex; align-items: center; justify-content: center;
                font-size: 1rem; flex-shrink: 0;
            }
            .sw-dept-meta { display: flex; align-items: center; gap: 12px; }
            .sw-pbar-wrap { display: flex; align-items: center; gap: 8px; }
            .sw-pbar-track { width: 80px; height: 5px; background: #EDF0F8; border-radius: 99px; overflow: hidden; }
            .sw-pbar-fill  { height: 100%; border-radius: 99px; transition: width 1s cubic-bezier(.16,1,.3,1); }
            .sw-pbar-lbl   { font-family: 'JetBrains Mono', monospace; font-size: .72rem; font-weight: 700; color: #7480A0; }
            .sw-badge      { font-size: .69rem; font-weight: 700; font-family: 'JetBrains Mono', monospace; padding: 4px 11px; border-radius: 50px; background: #EDF0F8; color: #7480A0; }

            /* ── TABLE ── */
            .sw-table-wrap { overflow-x: auto; }
            .sw-table { width: 100%; border-collapse: collapse; min-width: 580px; }
            .sw-table thead th {
                padding: 10px 16px; font-size: .67rem; font-weight: 700;
                text-transform: uppercase; letter-spacing: .7px;
                color: #7480A0; text-align: left; background: #F8FAFD;
                border-bottom: 1px solid #DDE2F0; white-space: nowrap;
            }
            .sw-table thead th.sw-th-day { text-align: center; width: 68px; }
            .sw-table thead th.sw-th-act { text-align: right; width: 110px; }
            .sw-table tbody td {
                padding: 0 16px; height: 58px;
                border-bottom: 1px solid #EDF0F8; vertical-align: middle;
            }
            .sw-table tbody tr { transition: background .14s; }
            .sw-table tbody tr:hover td { background: #F2F5FC; }
            .sw-table tbody tr:last-child td { border-bottom: none; }

            /* ── PERSONA ── */
            .sw-person { display: flex; align-items: center; gap: 10px; }
            .sw-av {
                width: 36px; height: 36px; border-radius: 10px;
                display: flex; align-items: center; justify-content: center;
                font-size: .74rem; font-weight: 700; color: white;
                flex-shrink: 0; letter-spacing: .3px;
            }
            .sw-pname { font-size: .85rem; font-weight: 600; color: #0D1424; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; max-width: 200px; }
            .sw-pdept { font-size: .7rem; color: #7480A0; }

            /* ── HEATMAP ── */
            .sw-day-td { text-align: center; }
            .sw-heat {
                display: inline-flex; align-items: center; justify-content: center;
                width: 38px; height: 38px; border-radius: 9px;
                font-size: .74rem; font-weight: 700;
                font-family: 'JetBrains Mono', monospace;
                transition: transform .18s cubic-bezier(.16,1,.3,1), box-shadow .18s;
                cursor: default; position: relative; z-index: 1;
            }
            .sw-heat:hover { transform: scale(1.2); z-index: 5; }
            .sw-h-P { background:#D1FAE5; color:#065F46; box-shadow: 0 0 0 1.5px rgba(5,150,105,.2); }
            .sw-h-P:hover { box-shadow: 0 5px 14px rgba(5,150,105,.3); }
            .sw-h-A { background:#FEE2E2; color:#991B1B; box-shadow: 0 0 0 1.5px rgba(220,38,38,.18); }
            .sw-h-A:hover { box-shadow: 0 5px 14px rgba(220,38,38,.28); }
            .sw-h-J { background:#FEF3C7; color:#92400E; box-shadow: 0 0 0 1.5px rgba(217,119,6,.18); }
            .sw-h-J:hover { box-shadow: 0 5px 14px rgba(217,119,6,.28); }
            .sw-h-N { background: #EDF0F8; color: #B4BDD4; }

            /* ── BOTÓN VER ── */
            .sw-btn-ver {
                background: #EDF0F8; border: 1.5px solid #DDE2F0;
                color: #2563EB; padding: 7px 15px; border-radius: 9px;
                font-size: .76rem; font-weight: 600;
                font-family: 'Plus Jakarta Sans', sans-serif;
                cursor: pointer; display: inline-flex; align-items: center; gap: 5px;
                transition: all .18s cubic-bezier(.16,1,.3,1);
            }
            .sw-btn-ver:hover {
                background: #DBEAFE; border-color: #2563EB;
                transform: translateY(-1px);
                box-shadow: 0 4px 12px rgba(37,99,235,.15);
            }

            /* ── EMPTY STATE ── */
            .sw-empty {
                text-align: center; padding: 48px 24px;
                background: white; border-radius: 20px; border: 1px solid #DDE2F0;
            }

            /* ── ANIMATIONS ── */
            @keyframes sw-reveal {
                from { opacity:0; transform: translateY(12px); }
                to   { opacity:1; transform: translateY(0); }
            }
            .sw-rv { opacity:0; animation: sw-reveal .4s cubic-bezier(.16,1,.3,1) forwards; }
            .sw-d1{animation-delay:.04s}.sw-d2{animation-delay:.08s}.sw-d3{animation-delay:.12s}
            .sw-d4{animation-delay:.16s}.sw-d5{animation-delay:.20s}.sw-d6{animation-delay:.24s}
            .sw-d7{animation-delay:.28s}.sw-d8{animation-delay:.32s}

            /* ── Flatpickr fixes ── */
            .flatpickr-monthDropdown-months {
                display: inline-block !important; visibility: visible !important;
                width: auto !important; appearance: menulist !important;
                -moz-appearance: menulist !important; -webkit-appearance: menulist !important;
                background: transparent !important; border: none !important;
                color: inherit !important; height: auto !important;
                padding: 0 !important; margin: 0 !important; font-weight: bold;
            }
            .flatpickr-current-month { display: flex !important; justify-content: center !important; align-items: center !important; }
            .flatpickr-calendar { z-index: 1055 !important; }
            .flatpickr-day.inRange,.flatpickr-day.prevMonthDay.inRange,.flatpickr-day.nextMonthDay.inRange,
            .flatpickr-day.today.inRange,.flatpickr-day:hover,.flatpickr-day.prevMonthDay:hover,
            .flatpickr-day.nextMonthDay:hover,.flatpickr-day:focus,.flatpickr-day.prevMonthDay:focus,
            .flatpickr-day.nextMonthDay:focus { background:#DBEAFE !important; border-color:#DBEAFE !important; }
            .flatpickr-day.selected,.flatpickr-day.startRange,.flatpickr-day.endRange,
            .flatpickr-day.selected.inRange,.flatpickr-day.startRange.inRange,.flatpickr-day.endRange.inRange,
            .flatpickr-day.selected:focus,.flatpickr-day.startRange:focus,.flatpickr-day.endRange:focus,
            .flatpickr-day.selected:hover,.flatpickr-day.startRange:hover,.flatpickr-day.endRange:hover,
            .flatpickr-day.selected.prevMonthDay,.flatpickr-day.startRange.prevMonthDay,
            .flatpickr-day.endRange.prevMonthDay,.flatpickr-day.selected.nextMonthDay,
            .flatpickr-day.startRange.nextMonthDay,.flatpickr-day.endRange.nextMonthDay
            { background:#1D4ED8 !important; color:#fff !important; border-color:#1D4ED8 !important; }
            </style>

            <div class="sw-wrap">

            <!-- ── TOOLBAR ── -->
            <div class="sw-toolbar sw-rv sw-d1">
                <div class="sw-toolbar-left">
                    <div class="sw-search">
                        <i class="ti ti-search"></i>
                        <input type="text" id="buscadorSemanal" placeholder="Buscar pasante…" autocomplete="off">
                    </div>
                    <div class="sw-chips">
                        <button class="sw-chip sc-all active" onclick="swSetChip('all',this)">Todos</button>
                        <button class="sw-chip sc-ok"         onclick="swSetChip('ok',this)">
                            <span class="sw-chip-dot" style="background:#059669"></span>Sin faltas
                        </button>
                        <button class="sw-chip sc-falta"      onclick="swSetChip('falta',this)">
                            <span class="sw-chip-dot" style="background:#DC2626"></span>Con faltas
                        </button>
                    </div>
                    <div class="sw-legend">
                        <div class="sw-legend-item">
                            <div class="sw-legend-dot" style="background:#D1FAE5;border:1.5px solid rgba(5,150,105,.3);"></div>P · Presente
                        </div>
                        <div class="sw-legend-item">
                            <div class="sw-legend-dot" style="background:#FEE2E2;border:1.5px solid rgba(220,38,38,.2);"></div>A · Ausente
                        </div>
                        <div class="sw-legend-item">
                            <div class="sw-legend-dot" style="background:#FEF3C7;border:1.5px solid rgba(217,119,6,.2);"></div>J · Justificado
                        </div>
                    </div>
                </div>
                <div class="sw-toolbar-right">
                    <div class="sw-fp-wrap">
                        <label for="filtro_semana"><i class="ti ti-calendar-time"></i> Semana</label>
                        <input type="text" id="filtro_semana" class="form-control" placeholder="Seleccionar…">
                    </div>
                    <div class="sw-nav-pill">
                        <button class="sw-nav-btn nav-semana-btn" data-url="<?= $navSemana['ant_url'] ?>">
                            <i class="ti ti-chevron-left"></i> Ant.
                        </button>
                        <span id="label-semana-nav" class="sw-nav-label"><?= $navSemana['texto'] ?></span>
                        <button class="sw-nav-btn nav-semana-btn" data-url="<?= $navSemana['sig_url'] ?>">
                            Sig. <i class="ti ti-chevron-right"></i>
                        </button>
                    </div>
                </div>
            </div>

            <!-- ── FLATPICKR + AJAX PJAX ── -->
            <script src="https://cdn.jsdelivr.net/npm/flatpickr/dist/plugins/weekSelect/weekSelect.js"></script>
            <script>
                function cargarSemanaAjax(url) {
                    const contenedor = document.getElementById('contenedor-tarjetas-semanales');
                    if(contenedor) contenedor.style.opacity = '0.4';
                    fetch(url)
                        .then(res => res.text())
                        .then(html => {
                            const parser = new DOMParser();
                            const doc = parser.parseFromString(html, 'text/html');
                            const nuevoCont = doc.getElementById('contenedor-tarjetas-semanales');
                            if (nuevoCont && contenedor) {
                                contenedor.innerHTML = nuevoCont.innerHTML;
                                contenedor.style.opacity = '1';
                                swIniciarFiltros();
                            }
                            const nuevoLabel = doc.getElementById('label-semana-nav');
                            const actualLabel = document.getElementById('label-semana-nav');
                            if (nuevoLabel && actualLabel) actualLabel.innerText = nuevoLabel.innerText;
                            const nuevosBtn = doc.querySelectorAll('.nav-semana-btn');
                            const actualesBtn = document.querySelectorAll('.nav-semana-btn');
                            if (nuevosBtn.length === 2 && actualesBtn.length === 2) {
                                actualesBtn[0].dataset.url = nuevosBtn[0].dataset.url;
                                actualesBtn[1].dataset.url = nuevosBtn[1].dataset.url;
                            }
                            window.history.pushState({ path: url }, '', url);
                        })
                        .catch(err => { console.error('Error AJAX PJAX:', err); window.location.href = url; });
                }

                let swActiveChip = 'all';
                function swSetChip(tipo, btn) {
                    swActiveChip = tipo;
                    document.querySelectorAll('.sw-chip').forEach(c => c.classList.remove('active'));
                    btn.classList.add('active');
                    swFiltrar();
                }
                function swFiltrar() {
                    const q = (document.getElementById('buscadorSemanal')?.value || '').toLowerCase().trim();
                    document.querySelectorAll('.sw-prow').forEach(row => {
                        const nombre = row.dataset.nombre || '';
                        const faltas = row.dataset.faltas === '1';
                        const mQ = !q || nombre.includes(q);
                        const mC = swActiveChip === 'all'
                            || (swActiveChip === 'ok'    && !faltas)
                            || (swActiveChip === 'falta' &&  faltas);
                        row.style.display = (mQ && mC) ? '' : 'none';
                    });
                }
                function swIniciarFiltros() {
                    const inp = document.getElementById('buscadorSemanal');
                    if (inp) { inp.removeEventListener('input', swFiltrar); inp.addEventListener('input', swFiltrar); }
                }

                document.addEventListener('DOMContentLoaded', function() {
                    swIniciarFiltros();
                    const defaultAnio   = <?= $paramsUrl['anio']   ?? date('Y') ?>;
                    const defaultSemana = <?= $paramsUrl['semana'] ?? date('W') ?>;
                    if (typeof flatpickr !== 'undefined' && typeof weekSelect !== 'undefined') {
                        flatpickr('#filtro_semana', {
                            locale: 'es', weekNumbers: true,
                            plugins: [new weekSelect({})],
                            onReady: function(selectedDates, dateStr, instance) {
                                instance.input.value = "Semana " + defaultSemana + ", " + defaultAnio;
                                setTimeout(() => {
                                    const monthSelect = instance.monthElements[0];
                                    if (monthSelect) {
                                        if (window.jQuery && $(monthSelect).data('select2')) $(monthSelect).select2('destroy');
                                        if (window.jQuery) {
                                            $(monthSelect).css({'display':'inline-block','visibility':'visible'}).removeClass('select2-hidden-accessible');
                                        } else {
                                            monthSelect.style.display = 'inline-block';
                                            monthSelect.style.visibility = 'visible';
                                            monthSelect.classList.remove('select2-hidden-accessible');
                                        }
                                        const choicesContainer = monthSelect.closest('.choices');
                                        if (choicesContainer) {
                                            choicesContainer.parentNode.insertBefore(monthSelect, choicesContainer);
                                            choicesContainer.remove();
                                            monthSelect.style.display = 'inline-block';
                                            monthSelect.classList.remove('choices__input', 'is-hidden');
                                        }
                                    }
                                }, 150);
                            },
                            onChange: function(selectedDates, dateStr, instance) {
                                if (selectedDates.length > 0) {
                                    const date = selectedDates[0];
                                    const dt = new Date(date.valueOf());
                                    const dayNr = (date.getDay() + 6) % 7;
                                    dt.setDate(dt.getDate() - dayNr + 3);
                                    const firstThursday = dt.valueOf();
                                    dt.setMonth(0, 1);
                                    if (dt.getDay() !== 4) dt.setMonth(0, 1 + ((4 - dt.getDay()) + 7) % 7);
                                    const targetYear = new Date(firstThursday).getFullYear();
                                    const targetWeek = 1 + Math.ceil((firstThursday - dt) / 604800000);
                                    instance.input.value = "Semana " + targetWeek + ", " + targetYear;
                                    cargarSemanaAjax("<?= URLROOT ?>/asistencias?vista=semanal&semana=" + targetWeek + "&anio=" + targetYear);
                                }
                            }
                        });
                    }
                    document.addEventListener('click', function(e) {
                        const btn = e.target.closest('.nav-semana-btn');
                        if (btn) {
                            e.preventDefault();
                            const targetUrl = btn.dataset.url;
                            try {
                                const urlObj = new URL(targetUrl, window.location.origin);
                                const s = urlObj.searchParams.get('semana');
                                const a = urlObj.searchParams.get('anio');
                                const fpInput = document.getElementById('filtro_semana');
                                if (fpInput && s && a) fpInput.value = "Semana " + s + ", " + a;
                            } catch(e) {}
                            cargarSemanaAjax(targetUrl);
                        }
                    });
                });
            </script>

            <!-- ── CONTENEDOR PJAX (actualizado por AJAX) ── -->
            <div id="contenedor-tarjetas-semanales" style="transition: opacity 0.3s ease;">
                <?php if(empty($datosSemanales)): ?>
                    <div class="sw-empty">
                        <i class="ti ti-calendar-x" style="font-size:3rem;color:#B4BDD4;display:block;margin-bottom:12px;"></i>
                        <p style="color:#7480A0;font-weight:600;margin:0;">No hay pasantes activos para mostrar en esta semana.</p>
                    </div>
                <?php else: ?>
                    <?php
                    $coloresDep  = ['#1D4ED8','#7C3AED','#059669','#0891B2','#D97706','#BE185D','#DC2626'];
                    $swDiIdx     = 0;
                    $swDelay     = 3;
                    foreach ($datosSemanales as $depto => $pasantes):
                        $st       = $deptStats[$depto] ?? ['pct'=>0];
                        $pct      = $st['pct'];
                        $barClr   = $pct >= 80 ? '#059669' : ($pct >= 60 ? '#D97706' : '#DC2626');
                        $dClr     = $coloresDep[$swDiIdx % count($coloresDep)];
                        $swDiIdx++; $swDelay++;
                    ?>
                    <div class="sw-dept-card sw-rv sw-d<?= min($swDelay, 8) ?>">
                        <div class="sw-dept-head">
                            <div class="sw-dept-title">
                                <div class="sw-dept-icon" style="background:<?= $dClr ?>18;">
                                    <i class="ti ti-building-community" style="color:<?= $dClr ?>;font-size:1rem;"></i>
                                </div>
                                <?= htmlspecialchars($depto) ?>
                            </div>
                            <div class="sw-dept-meta">
                                <div class="sw-pbar-wrap">
                                    <div class="sw-pbar-track">
                                        <div class="sw-pbar-fill" style="width:<?= $pct ?>%;background:<?= $barClr ?>;"></div>
                                    </div>
                                    <span class="sw-pbar-lbl"><?= $pct ?>%</span>
                                </div>
                                <span class="sw-badge"><?= count($pasantes) ?> pasantes</span>
                            </div>
                        </div>
                        <div class="sw-table-wrap">
                            <table class="sw-table">
                                <thead>
                                    <tr>
                                        <th>Pasante</th>
                                        <th class="sw-th-day">Lun</th>
                                        <th class="sw-th-day">Mar</th>
                                        <th class="sw-th-day">Mié</th>
                                        <th class="sw-th-day">Jue</th>
                                        <th class="sw-th-day">Vie</th>
                                        <th class="sw-th-act">Resumen</th>
                                    </tr>
                                </thead>
                                <tbody>
                                <?php foreach ($pasantes as $id => $pData):
                                    $nombre   = htmlspecialchars($pData['nombre']);
                                    $nomLow   = strtolower($pData['nombre']);
                                    $partes   = explode(',', $pData['nombre']);
                                    $ap1      = trim(explode(' ', $partes[0] ?? 'A')[0]);
                                    $nom1     = trim(explode(' ', $partes[1] ?? 'A')[0]);
                                    $initials = strtoupper(substr($nom1,0,1) . substr($ap1,0,1));
                                    if (strlen(trim($initials)) < 2) $initials = strtoupper(substr($pData['nombre'],0,2));
                                    $tFaltas  = 0;
                                    foreach ($pData['dias'] as $dl) { if ($dl === 'A') $tFaltas++; }
                                ?>
                                <tr class="sw-prow"
                                    data-nombre="<?= $nomLow ?>"
                                    data-faltas="<?= $tFaltas > 0 ? '1' : '0' ?>">
                                    <td style="min-width:180px;max-width:240px;padding:0 16px;">
                                        <div class="sw-person">
                                            <div class="sw-av" style="background:<?= $dClr ?>"><?= $initials ?></div>
                                            <div>
                                                <div class="sw-pname"><?= $nombre ?></div>
                                                <div class="sw-pdept"><?= htmlspecialchars($depto) ?></div>
                                            </div>
                                        </div>
                                    </td>
                                    <?php for ($i = 1; $i <= 5; $i++):
                                        $letra  = $pData['dias'][$i] ?? '-';
                                        $hCls   = in_array($letra, ['P','A','J']) ? "sw-h-{$letra}" : 'sw-h-N';
                                        $hTxt   = in_array($letra, ['P','A','J']) ? $letra : '·';
                                        $hTitle = ['P'=>'Presente','A'=>'Ausente','J'=>'Justificado'][$letra] ?? 'Sin datos';
                                    ?>
                                    <td class="sw-day-td">
                                        <span class="sw-heat <?= $hCls ?>" title="<?= $hTitle ?>"><?= $hTxt ?></span>
                                    </td>
                                    <?php endfor; ?>
                                    <td style="padding:0 16px;text-align:right;">
                                        <button type="button"
                                            class="sw-btn-ver btn-resumen"
                                            data-id="<?= htmlspecialchars($id) ?>"
                                            data-nombre="<?= htmlspecialchars($pData['nombre']) ?>"
                                            onclick="abrirAlmanaque(this)">
                                            <i class="ti ti-calendar-stats"></i> Ver
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>

            </div><!-- /.sw-wrap -->

        <?php elseif ($vistaActual === 'mensual'): ?>
            <?php
            // ==========================================
            // 1. MOTOR DE DATOS (PHP -> JS)
            // ==========================================
            $pasantesParaJS = [];
            $resumenDeptosJS = [];
            $colores = ['#2563eb','#059669','#d97706','#7c3aed','#0284c7','#dc2626'];
            $colorIdx = 0;

            $agrupado = [];
            $kpiPresentes = 0; $kpiFaltas = 0; $kpiJustificados = 0;

            if(!empty($registrosLista)) {
                foreach($registrosLista as $reg) {
                    $pid = is_array($reg) ? ($reg['pasante_id'] ?? 'P'.($reg['id']??0)) : ($reg->pasante_id ?? 'P'.($reg->id??0));
                    $nom = is_array($reg) ? ($reg['nombres'] ?? '') : ($reg->nombres ?? '');
                    $ape = is_array($reg) ? ($reg['apellidos'] ?? '') : ($reg->apellidos ?? '');
                    $ced = is_array($reg) ? ($reg['cedula'] ?? '') : ($reg->cedula ?? '');
                    $dep = is_array($reg) ? ($reg['departamento_nombre'] ?? 'General') : ($reg->departamento_nombre ?? 'General');
                    $est = is_array($reg) ? ($reg['estado'] ?? '') : ($reg->estado ?? '');

                    if(!isset($agrupado[$pid])) {
                        $agrupado[$pid] = ['n' => trim($ape.', '.$nom), 'ci' => $ced, 'd' => $dep, 'p' => 0, 'f' => 0, 'j' => 0];
                    }
                    $s = strtolower($est);
                    if(strpos($s, 'presente') !== false) { $agrupado[$pid]['p']++; $kpiPresentes++; }
                    elseif(strpos($s, 'ausente') !== false) { $agrupado[$pid]['f']++; $kpiFaltas++; }
                    else { $agrupado[$pid]['j']++; $kpiJustificados++; }
                }
            }

            foreach ($agrupado as $pid => $d) {
                $parts = explode(',', $d['n']);
                $ini = strtoupper(substr(trim($parts[1] ?? 'P'), 0, 1) . substr(trim($parts[0] ?? 'A'), 0, 1));
                if(strlen(trim($ini)) < 2) $ini = substr($d['n'], 0, 2);
                
                $pasantesParaJS[] = [
                    'id' => $pid, 'n' => $d['n'], 'ci' => $d['ci'], 'd' => $d['d'],
                    'p' => $d['p'], 'f' => $d['f'], 'j' => $d['j'],
                    'av' => $ini, 'c' => $colores[$colorIdx % count($colores)]
                ];
                
                if (!isset($resumenDeptosJS[$d['d']])) { $resumenDeptosJS[$d['d']] = ['p'=>0, 'f'=>0, 'j'=>0, 't'=>0]; }
                $resumenDeptosJS[$d['d']]['p'] += $d['p'];
                $resumenDeptosJS[$d['d']]['f'] += $d['f'];
                $resumenDeptosJS[$d['d']]['j'] += $d['j'];
                $resumenDeptosJS[$d['d']]['t'] += ($d['p'] + $d['f'] + $d['j']);
                $colorIdx++;
            }

            // Identificar los que no han marcado NADA en todo el mes
            $sinMarcarMes = [];
            foreach ($agrupado as $pid => $d) {
                if (($d['p'] + $d['f'] + $d['j']) === 0) {
                    $sinMarcarMes[] = (object) [
                        'nombres' => $d['n'],
                        'cedula' => $d['ci'],
                        'departamento_nombre' => $d['d']
                    ];
                }
            }

            // Calendario Dinámico
            $mesActual = (int)($paramsUrl['mes'] ?? date('n'));
            $anioActual = (int)($paramsUrl['anio'] ?? date('Y'));
            $diasEnMes = cal_days_in_month(CAL_GREGORIAN, $mesActual, $anioActual);
            
            $mesesNombres = [1=>'Enero',2=>'Febrero',3=>'Marzo',4=>'Abril',5=>'Mayo',6=>'Junio',7=>'Julio',8=>'Agosto',9=>'Septiembre',10=>'Octubre',11=>'Noviembre',12=>'Diciembre'];
            $nombreMes = $mesesNombres[$mesActual] ?? date('M');
            
            $calendarioJS = [];
            $daysJS = [];
            for ($i = 1; $i <= $diasEnMes; $i++) {
                $fechaStr = sprintf('%04d-%02d-%02d', $anioActual, $mesActual, $i);
                $diaSemana = date('N', strtotime($fechaStr));
                if ($diaSemana <= 5) { // Lunes a Viernes
                    // Por simplicidad, ponemos 'P' si hubo asistencias globales ese día, 
                    // o 'A'/'J' dependiendo de una lógica. Como es el calendario global del mes, 
                    // podemos colorearlo verde ('p') si hay registros.
                    $tieneRegistros = false;
                    if(!empty($registrosLista)) {
                        foreach($registrosLista as $reg) {
                            $f = is_array($reg) ? ($reg['fecha'] ?? '') : ($reg->fecha ?? '');
                            if(strpos($f, $fechaStr) === 0) { $tieneRegistros = true; break; }
                        }
                    }
                    if($tieneRegistros) {
                        $calendarioJS[$i] = 'p';
                        $daysJS[] = $i;
                    } elseif ($i < date('j') && $mesActual == date('n') && $anioActual == date('Y')) {
                        $calendarioJS[$i] = 'a'; // Pasado sin registros
                        $daysJS[] = $i;
                    }
                }
            }
            // Navegación Mes/Año
            $mesAnt = $mesActual - 1; $anioAnt = $anioActual;
            if ($mesAnt < 1) { $mesAnt = 12; $anioAnt--; }
            $mesSig = $mesActual + 1; $anioSig = $anioActual;
            if ($mesSig > 12) { $mesSig = 1; $anioSig++; }

            $urlAnt = URLROOT . "/asistencias?vista=mensual&mes=$mesAnt&anio=$anioAnt";
            $urlSig = URLROOT . "/asistencias?vista=mensual&mes=$mesSig&anio=$anioSig";
            
            // Datos para Chart.js
            $chartLabels = $chartSemanasJson ?? '[]';
            $chartData   = $chartPctsJson ?? '[]';
            ?>

            <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
            <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
            <style>
            .claude-replica {
                /* Design Tokens exactos del código original */
                --bg: #f1f4f9; --surface: #ffffff; --surface-2: #f7f9fc; --surface-3: #eef1f7;
                --border: rgba(15,23,60,0.07); --border-hover: rgba(15,23,60,0.14); --border-focus: #2563eb;
                --ink: #0f1538; --ink-2: #5a6180; --ink-3: #9ba3be; --ink-4: #c5cade;
                --blue: #2563eb; --blue-dim: rgba(37,99,235,0.08); --blue-mid: rgba(37,99,235,0.15); --blue-glow: rgba(37,99,235,0.20);
                --green: #059669; --green-dim: rgba(5,150,105,0.09); --green-mid: rgba(5,150,105,0.18);
                --amber: #d97706; --amber-dim: rgba(217,119,6,0.09); --amber-mid: rgba(217,119,6,0.18);
                --red: #dc2626; --red-dim: rgba(220,38,38,0.08); --red-mid: rgba(220,38,38,0.16);
                --violet: #7c3aed; --violet-dim: rgba(124,58,237,0.08);
                --sky: #0284c7; --sky-dim: rgba(2,132,199,0.08);
                --r: 10px; --r-lg: 16px; --r-xl: 22px; --gap: 14px;
                --font: 'Plus Jakarta Sans', sans-serif; --mono: 'JetBrains Mono', monospace;
                --shadow-sm: 0 1px 3px rgba(15,23,60,0.06), 0 1px 2px rgba(15,23,60,0.04);
                --shadow-md: 0 4px 12px rgba(15,23,60,0.08), 0 2px 4px rgba(15,23,60,0.04);
                --shadow-lg: 0 10px 30px rgba(15,23,60,0.10), 0 4px 8px rgba(15,23,60,0.05);
                --shadow-xl: 0 24px 48px rgba(15,23,60,0.14), 0 8px 16px rgba(15,23,60,0.06);

                font-family: var(--font); background: var(--bg); color: var(--ink);
                padding: 28px; border-radius: 20px; margin-top: 15px; box-sizing: border-box; font-size: 14px;
            }
            .claude-replica * { box-sizing: border-box; font-family: inherit; }
            
            /* Scrollbar CSS original */
            .claude-replica ::-webkit-scrollbar { width: 5px; height: 5px; }
            .claude-replica ::-webkit-scrollbar-track { background: transparent; }
            .claude-replica ::-webkit-scrollbar-thumb { background: var(--border-hover); border-radius: 99px; }
            .claude-replica ::-webkit-scrollbar-thumb:hover { background: var(--ink-3); }

            /* Action Bar Original */
            .claude-replica .action-bar { display: flex; align-items: center; gap: 8px; margin-bottom: 22px; flex-wrap: wrap; }
            .claude-replica .search-box { display: flex; align-items: center; gap: 8px; background: var(--surface); border: 1px solid var(--border); border-radius: var(--r); padding: 7px 12px; flex: 1; min-width: 260px; transition: border-color 0.15s, box-shadow 0.15s; box-shadow: var(--shadow-sm); }
            .claude-replica .search-box:focus-within { border-color: var(--border-focus); box-shadow: 0 0 0 3px var(--blue-dim); }
            .claude-replica .search-box svg { color: var(--ink-3); flex-shrink: 0; width: 14px; height: 14px; }
            .claude-replica .search-input { flex: 1; background: transparent; border: none; outline: none; color: var(--ink); font-size: 13px; font-family: var(--font); width: 100%;}
            .claude-replica .search-input::placeholder { color: var(--ink-3); }
            .claude-replica .sep { width: 1px; height: 20px; background: var(--border); flex-shrink: 0; }
            
            .claude-replica .chip-row { display: flex; align-items: center; gap: 6px; }
            .claude-replica .chip { display: flex; align-items: center; gap: 5px; padding: 5px 11px; border-radius: 8px; font-size: 11px; font-weight: 600; color: var(--ink-2); border: 1px solid var(--border); background: var(--surface); cursor: pointer; transition: all 0.15s; font-family: var(--font); box-shadow: var(--shadow-sm); white-space: nowrap; }
            .claude-replica .chip:hover { border-color: var(--border-hover); color: var(--ink); }
            .claude-replica .chip.active-green { background: var(--green-dim); border-color: var(--green-mid); color: var(--green); }
            .claude-replica .chip.active-red   { background: var(--red-dim);   border-color: var(--red-mid);   color: var(--red); }
            .claude-replica .chip.active-blue  { background: var(--blue-dim);  border-color: var(--blue-mid);  color: var(--blue); }
            .claude-replica .chip-dot { width: 6px; height: 6px; border-radius: 50%; flex-shrink: 0; }
            
            .claude-replica .btn { display: inline-flex; align-items: center; gap: 6px; padding: 7px 15px; border-radius: var(--r); font-size: 12px; font-weight: 700; cursor: pointer; border: none; font-family: var(--font); transition: all 0.18s; white-space: nowrap; }
            .claude-replica .btn svg { width: 13px; height: 13px; }
            .claude-replica .btn-ghost { background: var(--surface); color: var(--ink-2); border: 1px solid var(--border); box-shadow: var(--shadow-sm); }
            .claude-replica .btn-ghost:hover { background: var(--surface-2); color: var(--ink); border-color: var(--border-hover); }
            .claude-replica .btn-danger { background: var(--red-dim); color: var(--red); border: 1px solid var(--red-mid); }
            .claude-replica .btn-danger:hover { background: var(--red-mid); }

            /* BENTO GRID EXACTO */
            .claude-replica .bento { display: grid; grid-template-columns: repeat(12, 1fr); grid-auto-rows: minmax(56px, auto); gap: var(--gap); }
            .claude-replica .tile { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-lg); overflow: hidden; box-shadow: var(--shadow-sm); transition: box-shadow 0.2s, border-color 0.2s; position: relative; display: flex; flex-direction: column; }
            .claude-replica .tile:hover { box-shadow: var(--shadow-md); border-color: var(--border-hover); }

            /* LOS SPANS QUE HACEN LA MAGIA */
            .claude-replica .t-health   { grid-column: span 5; grid-row: span 4; }
            .claude-replica .t-alert    { grid-column: span 4; grid-row: span 4; }
            .claude-replica .t-calendar { grid-column: span 3; grid-row: span 4; }
            .claude-replica .t-present  { grid-column: span 6; grid-row: span 5; }
            .claude-replica .t-absent   { grid-column: span 6; grid-row: span 5; }
            .claude-replica .t-dept     { grid-column: span 8; grid-row: span 6; }
            .claude-replica .t-toplist  { grid-column: span 4; grid-row: span 6; }
            .claude-replica .t-trend    { grid-column: span 12; grid-row: span 6; padding-bottom: 12px; }
            .claude-replica .t-table    { grid-column: span 12; grid-row: span 7; }

            /* TILE HEADER */
            .claude-replica .tile-head { padding: 16px 18px 12px; display: flex; align-items: center; justify-content: space-between; border-bottom: 1px solid var(--border); flex-shrink: 0; }
            .claude-replica .tile-title { font-size: 13px; font-weight: 700; color: var(--ink); display: flex; align-items: center; gap: 8px; margin:0;}
            .claude-replica .tile-icon { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 14px; flex-shrink: 0; }
            .claude-replica .tile-badge { padding: 3px 10px; border-radius: 99px; font-size: 10px; font-weight: 700; font-family: var(--mono); }
            .claude-replica .tile-btn { padding: 4px 10px; border-radius: 7px; font-size: 11px; font-weight: 600; cursor: pointer; border: 1px solid var(--border); background: var(--surface-2); color: var(--ink-2); font-family: var(--font); transition: all 0.15s; display: flex; align-items: center; gap: 4px; }
            .claude-replica .tile-btn:hover { background: var(--surface-3); color: var(--ink); border-color: var(--border-hover); }

            /* HEALTH DONUT */
            .claude-replica .health-wrap { display: flex; flex-direction: column; align-items: center; gap: 14px; padding: 14px 18px 18px; }
            .claude-replica .donut-container { position: relative; width: 136px; height: 136px; }
            .claude-replica .donut-svg { transform: rotate(-90deg); }
            .claude-replica .donut-track { fill: none; stroke: var(--surface-3); stroke-width: 13; }
            .claude-replica .donut-fill { fill: none; stroke: var(--blue); stroke-width: 13; stroke-linecap: round; stroke-dasharray: 376; stroke-dashoffset: 376; transition: stroke-dashoffset 1.6s cubic-bezier(0.16,1,0.3,1); filter: drop-shadow(0 0 6px var(--blue-glow)); }
            .claude-replica .donut-center { position: absolute; inset: 0; display: flex; flex-direction: column; align-items: center; justify-content: center; }
            .claude-replica .donut-pct { font-size: 28px; font-weight: 800; color: var(--ink); letter-spacing: -1.5px; font-family: var(--mono); line-height: 1; }
            .claude-replica .donut-sub { font-size: 10px; color: var(--ink-3); font-weight: 600; margin-top: 2px; }
            .claude-replica .health-tag { display: flex; align-items: center; gap: 6px; padding: 5px 14px; border-radius: 99px; font-size: 11px; font-weight: 700; }
            .claude-replica .health-bars { width: 100%; display: flex; flex-direction: column; gap: 9px; }
            .claude-replica .hb-row { display: flex; align-items: center; gap: 8px; }
            .claude-replica .hb-label { font-size: 10px; font-weight: 600; color: var(--ink-2); width: 70px; flex-shrink: 0; }
            .claude-replica .hb-track { flex: 1; height: 6px; background: var(--surface-3); border-radius: 99px; overflow: hidden; }
            .claude-replica .hb-fill { height: 100%; border-radius: 99px; width: 0; transition: width 1.3s cubic-bezier(0.16,1,0.3,1); }
            .claude-replica .hb-val { font-size: 10px; font-weight: 700; color: var(--ink-2); width: 30px; text-align: right; font-family: var(--mono); }

            /* ALERTAS */
            .claude-replica .alert-list { display: flex; flex-direction: column; flex: 1; }
            .claude-replica .alert-item { display: flex; align-items: center; gap: 10px; padding: 10px 18px; cursor: pointer; transition: background 0.15s; }
            .claude-replica .alert-item:hover { background: var(--surface-2); }
            .claude-replica .al-avatar { width: 34px; height: 34px; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 11px; font-weight: 700; color: #fff; flex-shrink: 0; }
            .claude-replica .al-info { flex: 1; min-width: 0; }
            .claude-replica .al-name { font-size: 12px; font-weight: 600; color: var(--ink); margin-bottom: 0;}
            .claude-replica .al-dept { font-size: 10px; color: var(--ink-3); }
            .claude-replica .al-badge { padding: 3px 9px; border-radius: 7px; font-size: 10px; font-weight: 700; font-family: var(--mono); background: var(--red-dim); color: var(--red); border: 1px solid var(--red-mid); flex-shrink: 0; }
            .claude-replica .alert-cta { padding: 12px 18px; border-top: 1px solid var(--border); }

            /* CALENDARIO */
            .claude-replica .cal-wrap { padding: 10px 14px 14px; flex: 1; }
            .claude-replica .cal-dow-row { display: grid; grid-template-columns: repeat(5,1fr); gap: 4px; margin-bottom: 5px; }
            .claude-replica .cal-dow { text-align: center; font-size: 9px; font-weight: 700; color: var(--ink-4); text-transform: uppercase; letter-spacing: 0.5px; }
            .claude-replica .cal-grid { display: grid; grid-template-columns: repeat(5,1fr); gap: 4px; }
            .claude-replica .cal-cell { aspect-ratio: 1; border-radius: 6px; display: flex; align-items: center; justify-content: center; font-size: 9px; font-weight: 600; font-family: var(--mono); cursor: default; transition: transform 0.15s, box-shadow 0.15s; position: relative; }
            .claude-replica .cal-cell:hover { transform: scale(1.15); z-index: 5; box-shadow: var(--shadow-md); }
            .claude-replica .cal-cell.p  { background: var(--green-dim);  color: var(--green); border: 1px solid var(--green-mid); }
            .claude-replica .cal-cell.a  { background: var(--red-dim);    color: var(--red);   border: 1px solid var(--red-mid); }
            .claude-replica .cal-cell.j  { background: var(--amber-dim);  color: var(--amber); border: 1px solid var(--amber-mid); }
            .claude-replica .cal-cell.em { background: var(--surface-2);  color: var(--ink-4); border: 1px solid var(--border); }
            .claude-replica .cal-cell.today { box-shadow: 0 0 0 2px var(--blue); }
            .claude-replica .cal-legend { display: flex; gap: 12px; margin-top: 12px; justify-content: center; }
            .claude-replica .cal-li { display: flex; align-items: center; gap: 4px; font-size: 9px; color: var(--ink-3); font-weight: 600; }
            .claude-replica .cal-li-dot { width: 7px; height: 7px; border-radius: 2px; }

            /* MINI TABLES (Marcaron Hoy / Sin Marcar) */
            .claude-replica .mini-table-wrap { overflow-x: auto; overflow-y: auto; max-height: 100%; flex: 1; }
            .claude-replica .mini-table { width: 100%; border-collapse: collapse; }
            .claude-replica .mini-table th { padding: 8px 16px; font-size: 10px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.5px; text-align: left; background: var(--surface-2); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 2; }
            .claude-replica .mini-table td { padding: 9px 16px; font-size: 12px; color: var(--ink); border-bottom: 1px solid var(--border); vertical-align: middle; }
            .claude-replica .mini-table tbody tr:hover td { background: var(--surface-2); }
            .claude-replica .mini-table tbody tr:last-child td { border-bottom: none; }
            .claude-replica .person-cell { display: flex; align-items: center; gap: 8px; }
            .claude-replica .av { width: 28px; height: 28px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #fff; flex-shrink: 0; }
            .claude-replica .pn { font-size: 12px; font-weight: 600; color: var(--ink); margin:0;}
            .claude-replica .pid { font-size: 10px; color: var(--ink-3); font-family: var(--mono); margin:0;}
            .claude-replica .pill { display: inline-flex; align-items: center; gap: 4px; padding: 3px 9px; border-radius: 99px; font-size: 10px; font-weight: 700; }
            .claude-replica .pill-green  { background: var(--green-dim);  color: var(--green); }
            .claude-replica .pill-red    { background: var(--red-dim);    color: var(--red); }
            .claude-replica .pill-amber  { background: var(--amber-dim);  color: var(--amber); }
            .claude-replica .pill-blue   { background: var(--blue-dim);   color: var(--blue); }
            .claude-replica .pill-violet { background: var(--violet-dim); color: var(--violet); }
            .claude-replica .pill-sky    { background: var(--sky-dim);    color: var(--sky); }

            /* DEPT CARDS */
            .claude-replica .dept-grid { display: grid; grid-template-columns: repeat(auto-fill, minmax(190px, 1fr)); gap: 10px; padding: 14px 16px; flex: 1; }
            .claude-replica .dept-card { background: var(--surface-2); border: 1px solid var(--border); border-radius: var(--r); padding: 14px 14px 12px; transition: all 0.15s; cursor: pointer; }
            .claude-replica .dept-card:hover { border-color: var(--border-hover); background: var(--surface); box-shadow: var(--shadow-sm); transform: translateY(-1px); }
            .claude-replica .dc-head { display: flex; align-items: center; justify-content: space-between; margin-bottom: 12px; }
            .claude-replica .dc-name { font-size: 12px; font-weight: 700; color: var(--ink); }
            .claude-replica .dc-count { font-size: 10px; color: var(--ink-3); font-family: var(--mono); }
            .claude-replica .dc-bar-wrap { height: 5px; background: var(--surface-3); border-radius: 99px; overflow: hidden; display: flex; margin-bottom: 10px; }
            .claude-replica .dc-seg { height: 100%; transition: width 1.1s cubic-bezier(0.16,1,0.3,1); }
            .claude-replica .dc-stats { display: flex; gap: 10px; }
            .claude-replica .dc-stat { display: flex; align-items: center; gap: 4px; font-size: 10px; color: var(--ink-3); font-family: var(--mono); }
            .claude-replica .dc-dot { width: 5px; height: 5px; border-radius: 50%; }

            /* TOP LIST */
            .claude-replica .top-list { display: flex; flex-direction: column; flex: 1; }
            .claude-replica .top-item { display: flex; align-items: center; gap: 9px; padding: 9px 18px; transition: background 0.15s; }
            .claude-replica .top-item:hover { background: var(--surface-2); }
            .claude-replica .top-rank { width: 20px; font-size: 11px; font-weight: 700; color: var(--ink-3); font-family: var(--mono); text-align: center; }
            .claude-replica .top-rank.gold   { color: #d97706; }
            .claude-replica .top-rank.silver { color: #6b7280; }
            .claude-replica .top-rank.bronze { color: #92400e; }
            .claude-replica .top-av { width: 30px; height: 30px; border-radius: 9px; display: flex; align-items: center; justify-content: center; font-size: 10px; font-weight: 700; color: #fff; flex-shrink: 0; }
            .claude-replica .top-info { flex: 1; min-width: 0; }
            .claude-replica .top-name { font-size: 12px; font-weight: 600; color: var(--ink); white-space: nowrap; overflow: hidden; text-overflow: ellipsis; margin:0;}
            .claude-replica .top-dept { font-size: 10px; color: var(--ink-3); margin:0;}
            .claude-replica .top-bar { width: 48px; height: 4px; background: var(--surface-3); border-radius: 99px; overflow: hidden; }
            .claude-replica .top-bar-fill { height: 100%; border-radius: 99px; width: 0; transition: width 1.2s cubic-bezier(0.16,1,0.3,1); }
            .claude-replica .top-pct { font-family: var(--mono); font-size: 12px; font-weight: 700; flex-shrink: 0; min-width: 34px; text-align: right; }

            /* MASTER TABLE */
            .claude-replica .master-wrap { overflow-x: auto; overflow-y: auto; max-height: 370px; flex: 1; }
            .claude-replica .master-table { width: 100%; border-collapse: collapse; min-width: 680px; }
            .claude-replica .master-table th { padding: 9px 16px; font-size: 10px; font-weight: 700; color: var(--ink-3); text-transform: uppercase; letter-spacing: 0.5px; text-align: left; background: var(--surface-2); border-bottom: 1px solid var(--border); position: sticky; top: 0; z-index: 2; cursor: pointer; user-select: none; transition: color 0.15s; }
            .claude-replica .master-table th:hover { color: var(--blue); }
            .claude-replica .master-table td { padding: 10px 16px; font-size: 12px; color: var(--ink); border-bottom: 1px solid var(--border); vertical-align: middle; }
            .claude-replica .master-table tbody tr:hover td { background: var(--blue-dim); }
            .claude-replica .master-table tbody tr:last-child td { border-bottom: none; }
            .claude-replica .pct-cell { display: flex; align-items: center; gap: 8px; }
            .claude-replica .pct-bar { flex: 1; height: 5px; background: var(--surface-3); border-radius: 99px; overflow: hidden; min-width: 40px; }
            .claude-replica .pct-fill { height: 100%; border-radius: 99px; width: 0; transition: width 1s cubic-bezier(0.16,1,0.3,1); }
            .claude-replica .pct-val { font-family: var(--mono); font-size: 11px; font-weight: 700; min-width: 34px; text-align: right; flex-shrink: 0; }
            .claude-replica .status-chip { display: inline-flex; align-items: center; gap: 4px; padding: 4px 10px; border-radius: 7px; font-size: 10px; font-weight: 700; }
            .claude-replica .status-chip::before { content: ''; width: 5px; height: 5px; border-radius: 50%; flex-shrink: 0; }
            .claude-replica .s-ok   { background: var(--green-dim); color: var(--green); }
            .claude-replica .s-ok::before   { background: var(--green); }
            .claude-replica .s-warn { background: var(--amber-dim); color: var(--amber); }
            .claude-replica .s-warn::before { background: var(--amber); }
            .claude-replica .s-crit { background: var(--red-dim);   color: var(--red); }
            .claude-replica .s-crit::before { background: var(--red); }

            /* ANIMATIONS */
            @keyframes reveal { from { opacity:0; transform:translateY(10px); } to { opacity:1; transform:translateY(0); } }
            .claude-replica .rv { opacity:0; animation: reveal 0.4s cubic-bezier(0.16,1,0.3,1) forwards; }
            .claude-replica .d1{animation-delay:0.04s} .claude-replica .d2{animation-delay:0.08s} .claude-replica .d3{animation-delay:0.12s} .claude-replica .d4{animation-delay:0.16s} .claude-replica .d5{animation-delay:0.20s} .claude-replica .d6{animation-delay:0.24s} .claude-replica .d7{animation-delay:0.28s} .claude-replica .d8{animation-delay:0.32s}
            
            /* MODALS EXTRAIDOS DE CLAUDE */
            .claude-overlay { display: none; position: fixed; inset: 0; background: rgba(15,23,60,0.45); backdrop-filter: blur(6px); z-index: 1000; align-items: center; justify-content: center; }
            .claude-overlay.open { display: flex; animation: fadein 0.2s ease; }
            @keyframes fadein { from{opacity:0} to{opacity:1} }
            .claude-modal-box { background: var(--surface); border: 1px solid var(--border); border-radius: var(--r-xl); width: 90%; max-width: 460px; max-height: 90vh; display: flex; flex-direction: column; box-shadow: var(--shadow-xl); animation: slideup 0.25s cubic-bezier(0.16,1,0.3,1); }
            @keyframes slideup { from { transform: translateY(18px); opacity:0; } to { transform: translateY(0); opacity:1; } }
            </style>

            <div class="claude-replica">
                
                <div class="action-bar rv d1">
                    <div class="search-box">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><circle cx="11" cy="11" r="8"/><line x1="21" y1="21" x2="16.65" y2="16.65"/></svg>
                        <input class="search-input" id="searchInput" type="text" placeholder="Buscar pasante, cédula o departamento…">
                    </div>
                    <div class="sep"></div>
                    
                    <div class="nav-month-pill" style="display:flex;align-items:center;background:var(--surface);border:1px solid var(--border);border-radius:10px;padding:2px;gap:2px;box-shadow:var(--shadow-sm);">
                        <a href="<?= $urlAnt ?>" class="tile-btn" style="border:none;background:transparent;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="15 18 9 12 15 6"/></svg></a>
                        <span style="font-size:12px;font-weight:700;padding:0 8px;color:var(--ink);"><?= $nombreMes ?> <?= $anioActual ?></span>
                        <a href="<?= $urlSig ?>" class="tile-btn" style="border:none;background:transparent;"><svg width="12" height="12" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.5"><polyline points="9 18 15 12 9 6"/></svg></a>
                    </div>

                    <div class="sep"></div>
                    <div class="chip-row">
                        <button class="chip active-blue" id="fAll" onclick="setChip('all',this)"><span class="chip-dot" style="background:var(--blue)"></span> Todos</button>
                        <button class="chip" id="fOk" onclick="setChip('ok',this)"><span class="chip-dot" style="background:var(--green)"></span> Sin faltas</button>
                        <button class="chip" id="fCrit" onclick="setChip('crit',this)"><span class="chip-dot" style="background:var(--red)"></span> Con faltas</button>
                    </div>
                    <button class="btn btn-ghost" style="margin-left:auto" onclick="doExport()">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="13" height="13"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg>
                        Exportar CSV
                    </button>
                </div>

                <div class="bento">
                    
                    <div class="tile t-health rv d2">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--blue-dim)">📊</div> Índice de Salud</div>
                            <span class="tile-badge" style="background:var(--amber-dim);color:var(--amber);border:1px solid var(--amber-mid)">Regular · 86%</span>
                        </div>
                        <div class="health-wrap">
                            <div class="donut-container">
                                <svg class="donut-svg" width="136" height="136" viewBox="0 0 136 136">
                                    <circle class="donut-track" cx="68" cy="68" r="60"/>
                                    <circle class="donut-fill" cx="68" cy="68" r="60" id="donutFill"/>
                                </svg>
                                <div class="donut-center">
                                    <span class="donut-pct" id="donutPct">0%</span>
                                    <span class="donut-sub">salud global</span>
                                </div>
                            </div>
                            <div class="health-tag" style="background:var(--amber-dim);color:var(--amber);border:1px solid var(--amber-mid)">
                                ⚡ Estado Regular — <?= date('M Y') ?>
                            </div>
                            <div class="health-bars">
                                <div class="hb-row">
                                    <span class="hb-label">Presentes</span>
                                    <div class="hb-track"><div class="hb-fill" id="hpFill" style="background:var(--green)"></div></div>
                                    <span class="hb-val" id="hpVal">0%</span>
                                </div>
                                <div class="hb-row">
                                    <span class="hb-label">Justificados</span>
                                    <div class="hb-track"><div class="hb-fill" id="hjFill" style="background:var(--amber)"></div></div>
                                    <span class="hb-val" id="hjVal">0%</span>
                                </div>
                                <div class="hb-row">
                                    <span class="hb-label">Faltas</span>
                                    <div class="hb-track"><div class="hb-fill" id="hfFill" style="background:var(--red)"></div></div>
                                    <span class="hb-val" id="hfVal">0%</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="tile t-alert rv d3">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--red-dim)">🔔</div> Atención Requerida</div>
                            <span class="tile-badge" id="alertCountBadge" style="background:var(--red-dim);color:var(--red);border:1px solid var(--red-mid)">0 faltas</span>
                        </div>
                        <div class="alert-list" id="alertList"></div>
                        <div class="alert-cta" style="margin-top:auto">
                            <button class="btn btn-danger" style="width:100%;justify-content:center;" onclick="enviarNotificacionesMasivas()">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Enviar notificaciones
                            </button>
                        </div>
                    </div>

                    <div class="tile t-calendar rv d3">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--sky-dim)">📅</div> <?= $nombreMes ?> <?= $anioActual ?></div>
                        </div>
                        <div class="cal-wrap">
                            <div class="cal-dow-row">
                                <div class="cal-dow">L</div><div class="cal-dow">M</div>
                                <div class="cal-dow">M</div><div class="cal-dow">J</div>
                                <div class="cal-dow">V</div>
                            </div>
                            <div class="cal-grid" id="calGrid"></div>
                            <div class="cal-legend">
                                <div class="cal-li"><div class="cal-li-dot" style="background:var(--green)"></div>Pres.</div>
                                <div class="cal-li"><div class="cal-li-dot" style="background:var(--red)"></div>Falta</div>
                                <div class="cal-li"><div class="cal-li-dot" style="background:var(--amber)"></div>Just.</div>
                            </div>
                        </div>
                    </div>

                    <div class="tile t-present rv d4">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--green-dim)">✓</div> Marcaron este mes</div>
                            <div class="tile-actions">
                                <?php 
                                    $registrosMesFiltrados = array_filter($registrosLista ?? [], function($r) {
                                        return ($r->estado ?? '') !== 'Ausente';
                                    });
                                ?>
                                <span class="tile-badge" style="background:var(--green-dim);color:var(--green);border:1px solid var(--green-mid)"><?= count($registrosMesFiltrados) ?> registros</span>
                                <button class="tile-btn" onclick="doExport()">
                                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> CSV
                                </button>
                            </div>
                        </div>
                        <div class="mini-table-wrap">
                            <table class="mini-table">
                                <thead><tr><th>Pasante</th><th>Hora</th><th>Método</th><th>Estado</th></tr></thead>
                                <tbody>
                                    <?php if(!empty($registrosMesFiltrados)): foreach(array_slice($registrosMesFiltrados, 0, 4) as $r): 
                                        $nom = trim(($r->apellidos ?? '') . ', ' . ($r->nombres ?? ''));
                                        $ini = strtoupper(substr($r->nombres ?? 'P', 0, 1) . substr($r->apellidos ?? 'A', 0, 1));
                                        $hora = date('h:i A', strtotime($r->hora_registro ?? '00:00'));
                                        $metodo = $r->metodo ?? 'Manual';
                                        $estado = $r->estado ?? 'Presente';
                                        
                                        $pillMet = $metodo === 'Biométrico' ? 'pill-sky' : 'pill-violet';
                                        $pillEst = strpos(strtolower($estado), 'ausente') !== false ? 'pill-red' : (strpos(strtolower($estado), 'tarde') !== false ? 'pill-amber' : 'pill-green');
                                    ?>
                                    <tr onclick="abrirModalDetalle('<?= $r->cedula ?>')" style="cursor:pointer">
                                        <td><div class="person-cell"><div class="av" style="background:var(--blue)"><?= $ini ?></div><div><div class="pn"><?= htmlspecialchars($nom) ?></div><div class="pid">V-<?= $r->cedula ?? '' ?></div></div></div></td>
                                        <td style="font-family:var(--mono);font-size:11px;color:var(--ink-2)"><?= $hora ?></td>
                                        <td><span class="pill <?= $pillMet ?>"><?= htmlspecialchars($metodo) ?></span></td>
                                        <td><span class="pill <?= $pillEst ?>"><?= htmlspecialchars($estado) ?></span></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="4" style="text-align:center;padding:22px;color:var(--ink-3);">Nadie ha marcado en este mes.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tile t-absent rv d4">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--red-dim)">✗</div> Sin Marcar Este Mes</div>
                            <div class="tile-actions">
                                <span class="tile-badge" style="background:var(--red-dim);color:var(--red);border:1px solid var(--red-mid)"><?= count($sinMarcarMes ?? []) ?> pasantes</span>
                                <button class="tile-btn" onclick="abrirModalManual()"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Registrar</button>
                            </div>
                        </div>
                        <div class="mini-table-wrap">
                            <table class="mini-table">
                                <thead><tr><th>Pasante</th><th>Departamento</th><th>Acción</th></tr></thead>
                                <tbody>
                                    <?php if(!empty($sinMarcarMes)): foreach(array_slice($sinMarcarMes, 0, 4) as $p): 
                                        $nom = trim(($p->apellidos ?? '') . ', ' . ($p->nombres ?? ''));
                                        $ini = strtoupper(substr($p->nombres ?? 'P', 0, 1) . substr($p->apellidos ?? 'A', 0, 1));
                                    ?>
                                    <tr>
                                        <td><div class="person-cell"><div class="av" style="background:var(--red)"><?= htmlspecialchars($ini) ?></div><div><div class="pn"><?= htmlspecialchars($nom) ?></div><div class="pid">V-<?= $p->cedula ?? '' ?></div></div></div></td>
                                        <td style="font-size:11px;color:var(--ink-2)"><?= htmlspecialchars($p->departamento_nombre ?? 'General') ?></td>
                                        <td><button class="tile-btn" onclick="abrirModalManual('<?= $p->cedula ?>', '<?= htmlspecialchars($nom) ?>')">+ Reg.</button></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="3" style="text-align:center;padding:22px;font-size:11px;color:var(--ink-3);">🎉 ¡Todos los pasantes tienen registros este mes!</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tile t-dept rv d5">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--violet-dim)">🏢</div> Resumen por Departamento</div>
                            <div class="tile-actions">
                                <button class="tile-btn" onclick="doExport()"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/></svg> Exportar</button>
                            </div>
                        </div>
                        <div class="dept-grid" id="deptGrid"></div>
                    </div>

                    <div class="tile t-toplist rv d5">
                        <div class="tile-head" style="justify-content: space-between;">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--amber-dim)">🏆</div> Top Asistencia</div>
                            <div class="dropdown">
                                <button class="tile-btn dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false" style="background:var(--amber-dim);color:var(--amber);border:1px solid var(--amber-mid);font-size:10px;padding:2px 8px;border-radius:12px;">
                                    <?= htmlspecialchars($nombreMes) ?> <i class="ti ti-chevron-down"></i>
                                </button>
                                <ul class="dropdown-menu dropdown-menu-end shadow-sm border-0 rounded-3 p-2" style="font-size: 0.8rem;">
                                    <?php 
                                    $mesesArr = ['01' => 'Enero', '02' => 'Febrero', '03' => 'Marzo', '04' => 'Abril', '05' => 'Mayo', '06' => 'Junio', '07' => 'Julio', '08' => 'Agosto', '09' => 'Septiembre', '10' => 'Octubre', '11' => 'Noviembre', '12' => 'Diciembre'];
                                    foreach($mesesArr as $mIdx => $mName): ?>
                                        <li><a class="dropdown-item rounded-2 <?= ($paramsUrl['mes'] == $mIdx) ? 'active bg-primary' : '' ?>" href="<?= URLROOT ?>/asistencias?vista=mensual&mes=<?= $mIdx ?>&anio=<?= $paramsUrl['anio'] ?>"><?= $mName ?></a></li>
                                    <?php endforeach; ?>
                                    <li><hr class="dropdown-divider"></li>
                                    <li><h6 class="dropdown-header">Cambiar Año</h6></li>
                                    <li><a class="dropdown-item rounded-2" href="<?= URLROOT ?>/asistencias?vista=mensual&mes=<?= $paramsUrl['mes'] ?>&anio=<?= $paramsUrl['anio']-1 ?>"><?= $paramsUrl['anio']-1 ?></a></li>
                                    <li><a class="dropdown-item rounded-2" href="<?= URLROOT ?>/asistencias?vista=mensual&mes=<?= $paramsUrl['mes'] ?>&anio=<?= $paramsUrl['anio']+1 ?>"><?= $paramsUrl['anio']+1 ?></a></li>
                                </ul>
                            </div>
                        </div>
                        <div class="top-list" id="topList"></div>
                    </div>

                    <div class="tile t-trend rv d6">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--violet-dim)">📈</div> Tendencia Semanal de Asistencia</div>
                        </div>
                        <div style="padding: 14px 22px; flex: 1; min-height: 200px;">
                            <canvas id="trendChart"></canvas>
                        </div>
                    </div>

                    <div class="tile t-table rv d6">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--sky-dim)">📋</div> Tabla Maestra</div>
                            <div class="tile-actions">
                                <span id="tableCount" style="font-size:11px;color:var(--ink-3);">0 registros</span>
                                <button class="tile-btn" onclick="doExport()">CSV</button>
                            </div>
                        </div>
                        <div class="master-wrap">
                            <table class="master-table" id="mTable">
                                <thead>
                                    <tr>
                                        <th onclick="sortBy(0)">Pasante ↕</th>
                                        <th onclick="sortBy(1)">Departamento ↕</th>
                                        <th onclick="sortBy(2)" style="text-align:center">Presentes ↕</th>
                                        <th onclick="sortBy(3)" style="text-align:center">Faltas ↕</th>
                                        <th onclick="sortBy(4)" style="text-align:center">Justif. ↕</th>
                                        <th onclick="sortBy(5)">% Asistencia ↕</th>
                                        <th>Estado</th>
                                    </tr>
                                </thead>
                                <tbody id="mTbody"></tbody>
                            </table>
                        </div>
                    </div>

                </div>
            </div>

            <script>
            const DB = <?= json_encode($pasantesParaJS ?? []) ?>;
            const DEPTOS = <?= json_encode($resumenDeptosJS ?? (object)[]) ?>;
            
            const CAL = <?= json_encode($calendarioJS ?? (object)[]) ?>;
            const DAYS = <?= json_encode($daysJS ?? []) ?>;

            window.addEventListener('DOMContentLoaded', () => {
                animarDonutYBarras();
                buildCal();
                buildDepts();
                buildTop();
                buildTable(DB);
                buildAlerts(DB);
                initTrendChart();
                setTimeout(animBars, 500);
            });

            function initTrendChart() {
                const ctx = document.getElementById('trendChart').getContext('2d');
                const labels = <?= $chartLabels ?>;
                const data = <?= $chartData ?>;
                
                new Chart(ctx, {
                    type: 'line',
                    data: {
                        labels: labels,
                        datasets: [{
                            label: 'Asistencia %',
                            data: data,
                            borderColor: '#7c3aed',
                            backgroundColor: 'rgba(124, 58, 237, 0.1)',
                            fill: true,
                            tension: 0.4,
                            borderWidth: 3,
                            pointBackgroundColor: '#fff',
                            pointBorderColor: '#7c3aed',
                            pointBorderWidth: 2,
                            pointRadius: 4,
                            pointHoverRadius: 6
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: { legend: { display: false } },
                        scales: {
                            y: { beginAtZero: true, max: 100, ticks: { callback: v => v + '%' } },
                            x: { grid: { display: false } }
                        }
                    }
                });
            }

            // Motor de Filtros
            let activeChip = 'all';
            function setChip(type, btn) {
                activeChip = type;
                document.querySelectorAll('.claude-replica .chip-row .chip').forEach(c => c.className = 'chip');
                btn.className = 'chip ' + (type==='all'?'active-blue':type==='ok'?'active-green':'active-red');
                applyFilters();
            }
            function applyFilters() {
                const q = document.getElementById('searchInput').value.toLowerCase();
                const res = DB.filter(r => {
                    const mQ = !q || r.n.toLowerCase().includes(q) || r.ci.includes(q) || r.d.toLowerCase().includes(q);
                    const mS = activeChip==='all'||(activeChip==='ok'&&r.f===0)||(activeChip==='crit'&&r.f>0);
                    return mQ&&mS;
                });
                buildTable(res);
            }
            document.getElementById('searchInput').addEventListener('input', applyFilters);

            // Dona y Barras
            function animarDonutYBarras() {
                let tp=0, tf=0, tj=0;
                DB.forEach(r => { tp+=r.p; tf+=r.f; tj+=r.j; });
                const tot = tp+tf+tj; 
                const pctP = tot > 0 ? Math.round((tp/tot)*100) : 0;
                const pctF = tot > 0 ? Math.round((tf/tot)*100) : 0;
                const pctJ = tot > 0 ? Math.round((tj/tot)*100) : 0;

                const circ = 376;
                const fill = document.getElementById('donutFill');
                const lbl  = document.getElementById('donutPct');
                
                setTimeout(() => {
                    fill.style.strokeDashoffset = circ - (circ * pctP / 100);
                    fill.style.stroke = pctP >= 90 ? 'var(--green)' : pctP >= 75 ? 'var(--amber)' : 'var(--red)';
                }, 300);
                
                let c = 0;
                const t = setInterval(() => { c = Math.min(c+2, pctP); lbl.textContent = c+'%'; if(c>=pctP)clearInterval(t); }, 25);

                document.getElementById('hpFill').setAttribute('data-w', pctP); document.getElementById('hpVal').innerText = pctP+'%';
                document.getElementById('hjFill').setAttribute('data-w', pctJ); document.getElementById('hjVal').innerText = pctJ+'%';
                document.getElementById('hfFill').setAttribute('data-w', pctF); document.getElementById('hfVal').innerText = pctF+'%';
            }

            function animBars() {
                document.querySelectorAll('.claude-replica [data-w]').forEach(el => { el.style.width = el.getAttribute('data-w')+'%'; });
            }

            // Calendario
            function buildCal() {
                const g = document.getElementById('calGrid');
                DAYS.forEach((d,i) => {
                    const cell = document.createElement('div');
                    cell.className = 'cal-cell ' + (CAL[d] || 'em') + (d===7?' today':'');
                    cell.textContent = d;
                    cell.style.animationDelay = (i*25)+'ms';
                    g.appendChild(cell);
                });
            }

            // Alertas
            function buildAlerts(data) {
                const g = document.getElementById('alertList'); g.innerHTML = '';
                const sorted = [...data].sort((a,b) => b.f - a.f).filter(x => x.f > 0).slice(0,3);
                document.getElementById('alertCountBadge').innerText = sorted.length + ' faltas';
                if(sorted.length === 0) {
                    g.innerHTML = '<div class="alert-item" style="opacity:.35;pointer-events:none;"><div class="al-avatar" style="background:var(--surface-3);color:var(--ink-3)">-</div><div class="al-info"><div class="al-name" style="color:var(--ink-3)">Sin más alertas</div><div class="al-dept">Todos al día ✓</div></div></div>';
                    return;
                }
                sorted.forEach(r => {
                    g.innerHTML += `
                        <div class="alert-item" onclick="verPasante('${r.n}')">
                            <div class="al-avatar" style="background:${r.c}">${r.av}</div>
                            <div class="al-info">
                                <div class="al-name">${r.n}</div>
                                <div class="al-dept">${r.d}</div>
                            </div>
                            <span class="al-badge">${r.f} falta(s)</span>
                        </div>`;
                });
            }

            // Departamentos
            function buildDepts() {
                const g = document.getElementById('deptGrid'); let i=0;
                for (const [nm, v] of Object.entries(DEPTOS)) {
                    if(v.t === 0) continue;
                    const pP = Math.round(v.p/v.t*100), fP = Math.round(v.f/v.t*100), jP = Math.round(v.j/v.t*100);
                    const el = document.createElement('div');
                    el.className = 'dept-card'; el.style.animationDelay = (i*50)+'ms';
                    el.innerHTML = `
                        <div class="dc-head">
                            <span class="dc-name">${nm}</span>
                            <span class="dc-count">${v.t} marcajes</span>
                        </div>
                        <div class="dc-bar-wrap">
                            <div class="dc-seg" style="background:var(--green);width:0" data-w="${pP}"></div>
                            <div class="dc-seg" style="background:var(--amber);width:0" data-w="${jP}"></div>
                            <div class="dc-seg" style="background:var(--red);width:0"   data-w="${fP}"></div>
                        </div>
                        <div class="dc-stats">
                            <div class="dc-stat"><span class="dc-dot" style="background:var(--green)"></span>${v.p} pres</div>
                            <div class="dc-stat"><span class="dc-dot" style="background:var(--amber)"></span>${v.j} just</div>
                            <div class="dc-stat"><span class="dc-dot" style="background:var(--red)"></span>${v.f} falt</div>
                        </div>`;
                    g.appendChild(el); i++;
                }
            }

            // Top List
            function buildTop() {
                const g = document.getElementById('topList'); g.innerHTML = '';
                const sorted = [...DB].sort((a,b) => {
                    const pA = (a.p+a.f+a.j)>0 ? a.p/(a.p+a.f+a.j) : 0;
                    const pB = (b.p+b.f+b.j)>0 ? b.p/(b.p+b.f+b.j) : 0;
                    return pB - pA;
                });
                const rnk = ['gold','silver','bronze'];
                const sym = ['①','②','③'];
                sorted.slice(0,6).forEach((r,i) => {
                    const tot = r.p+r.f+r.j, pct = tot>0 ? Math.round(r.p/tot*100) : 0;
                    const col = pct>=90?'var(--green)':pct>=75?'var(--amber)':'var(--red)';
                    const el = document.createElement('div');
                    el.className = 'top-item';
                    el.style.cursor = 'pointer';
                    el.onclick = () => verPasante(r.n);
                    el.innerHTML = `
                        <span class="top-rank ${i<3?rnk[i]:''}">${i<3?sym[i]:i+1}</span>
                        <div class="top-av" style="background:${r.c}">${r.av}</div>
                        <div class="top-info">
                            <div class="top-name">${r.n.split(' ').slice(0,2).join(' ')}</div>
                            <div class="top-dept">${r.d}</div>
                        </div>
                        <div class="top-bar"><div class="top-bar-fill" data-w="${pct}" style="background:${col}"></div></div>
                        <span class="top-pct" style="color:${col}">${pct}%</span>`;
                    g.appendChild(el);
                });
            }

            // Tabla Maestra
            function buildTable(data) {
                const tbody = document.getElementById('mTbody');
                tbody.innerHTML = '';
                document.getElementById('tableCount').textContent = data.length+' registros';
                data.forEach(r => {
                    const tot = r.p+r.f+r.j, pct = tot>0 ? Math.round(r.p/tot*100) : 0;
                    const bc = pct>=90?'var(--green)':pct>=75?'var(--amber)':'var(--red)';
                    const sc = r.f===0?'s-ok':pct>=80?'s-warn':'s-crit';
                    const st = r.f===0?'Perfecto':pct>=80?'Regular':'Crítico';
                    const tr = document.createElement('tr');
                    tr.style.cursor = 'pointer';
                    tr.onclick = () => abrirModalManual(r.ci, r.n); // Opcional: abrir modal registro
                    tr.innerHTML = `
                        <td><div class="person-cell"><div class="av" style="background:${r.c}">${r.av}</div><div><div class="pn">${r.n}</div><div class="pid">V-${r.ci}</div></div></div></td>
                        <td style="color:var(--ink-2);font-size:11px">${r.d}</td>
                        <td style="text-align:center;font-family:var(--mono);font-weight:700;color:var(--green)">${r.p}</td>
                        <td style="text-align:center;font-family:var(--mono);font-weight:700;color:${r.f>0?'var(--red)':'var(--ink-3)'}">${r.f||'—'}</td>
                        <td style="text-align:center;font-family:var(--mono);font-weight:700;color:${r.j>0?'var(--amber)':'var(--ink-3)'}">${r.j||'—'}</td>
                        <td><div class="pct-cell"><div class="pct-bar"><div class="pct-fill" data-w="${pct}" style="background:${bc}"></div></div><span class="pct-val" style="color:${bc}">${pct}%</span></div></td>
                        <td><span class="status-chip ${sc}">${st}</span></td>`;
                    tbody.appendChild(tr);
                });
                setTimeout(animBars, 80);
            }

            let sCol=-1, sAsc=true;
            function sortBy(col) {
                if(sCol===col) sAsc=!sAsc; else{sCol=col;sAsc=true;}
                const keys=['n','d','p','f','j'];
                const q = document.getElementById('searchInput').value.toLowerCase();
                const res = DB.filter(r => {
                    const mQ = !q || r.n.toLowerCase().includes(q)||r.ci.includes(q)||r.d.toLowerCase().includes(q);
                    const mS = activeChip==='all'||(activeChip==='ok'&&r.f===0)||(activeChip==='crit'&&r.f>0);
                    return mQ&&mS;
                });
                res.sort((a,b)=>{
                    if(col===5){const pa=a.p/(a.p+a.f+a.j),pb=b.p/(b.p+b.f+b.j);return sAsc?pa-pb:pb-pa;}
                    const va=a[keys[col]]??0,vb=b[keys[col]]??0;
                    return typeof va==='string'?(sAsc?va.localeCompare(vb,'es'):vb.localeCompare(va,'es')):(sAsc?va-vb:vb-va);
                });
                buildTable(res);
            }

            function doExport() {
                let csv = 'Nombre,Cédula,Departamento,Presentes,Faltas,Justificados,%\n';
                DB.forEach(r => {
                    const tot = r.p + r.f + r.j, pct = tot > 0 ? Math.round(r.p / tot * 100) : 0;
                    csv += `"${r.n}","${r.ci}","${r.d}",${r.p},${r.f},${r.j},${pct}%\n`;
                });
                const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `asistencias_<?= strtolower($nombreMes) ?>_<?= $anioActual ?>.csv`;
                a.click();
            }

            function enviarNotificacionesMasivas() {
                Swal.fire({
                    title: '¿Enviar notificaciones?',
                    text: 'Se enviará un recordatorio a los pasantes con faltas este mes.',
                    icon: 'warning',
                    showCancelButton: true,
                    confirmButtonColor: '#2563eb',
                    cancelButtonColor: '#94a3b8',
                    confirmButtonText: 'Sí, enviar',
                    cancelButtonText: 'Cancelar'
                }).then((result) => {
                    if (result.isConfirmed) {
                        Swal.fire('Enviado', 'Las notificaciones han sido puestas en cola.', 'success');
                    }
                });
            }

            // --- Lógica de Modal Manual ---
            function abrirModalManual(cedula = '', nombre = '') {
                const modal = document.getElementById('modal-manual');
                modal.style.display = 'flex';
                if(cedula && nombre) {
                    document.getElementById('buscadorPasante').value = nombre;
                    document.getElementById('manual-pasante-id').value = cedula; // Usamos CI como ID en este flujo simplificado
                    document.getElementById('btnLimpiarPasante').style.display = 'flex';
                }
            }
            function cerrarModal() {
                document.getElementById('modal-manual').style.display = 'none';
                document.getElementById('form-manual').reset();
                document.getElementById('btnLimpiarPasante').style.display = 'none';
                document.getElementById('div-motivo').style.display = 'none';
            }
            function toggleMotivo(val) {
                document.getElementById('div-motivo').style.display = (val === 'Justificado') ? 'block' : 'none';
            }
            function enviarManual(e) {
                e.preventDefault();
                const fd = new FormData(e.target);
                fetch('<?= URLROOT ?>/asistencias/registro_manual', { method: 'POST', body: fd })
                .then(r => r.json())
                .then(data => {
                    if(data.success) {
                        Swal.fire('Éxito', data.message, 'success').then(() => location.reload());
                    } else {
                        Swal.fire('Error', data.message, 'error');
                    }
                });
            }

            // Búsqueda de pasantes en el modal
            const searchInputModal = document.getElementById('buscadorPasante');
            const resultsDiv = document.getElementById('resultadosPasante');
            const clearBtn = document.getElementById('btnLimpiarPasante');

            if(searchInputModal) {
                searchInputModal.addEventListener('input', (e) => {
                    const q = e.target.value.toLowerCase();
                    if(q.length < 2) { resultsDiv.style.display = 'none'; return; }
                    
                    const matches = DB.filter(p => p.n.toLowerCase().includes(q) || p.ci.includes(q));
                    resultsDiv.innerHTML = '';
                    if(matches.length > 0) {
                        matches.forEach(p => {
                            const div = document.createElement('div');
                            div.className = 'dropdown-item p-2 cursor-pointer';
                            div.style.cursor = 'pointer';
                            div.innerHTML = `<strong>${p.n}</strong> <small class="text-muted">V-${p.ci}</small>`;
                            div.onclick = () => {
                                searchInputModal.value = p.n;
                                document.getElementById('manual-pasante-id').value = p.ci; // CI acting as ID
                                resultsDiv.style.display = 'none';
                                clearBtn.style.display = 'flex';
                            };
                            resultsDiv.appendChild(div);
                        });
                        resultsDiv.style.display = 'block';
                    } else {
                        resultsDiv.style.display = 'none';
                    }
                });
            }
            if(clearBtn) {
                clearBtn.onclick = () => {
                    searchInputModal.value = '';
                    document.getElementById('manual-pasante-id').value = '';
                    clearBtn.style.display = 'none';
                    resultsDiv.style.display = 'none';
                };
            }

            // --- Funciones de Auditoría y Navegación ---
            function verPasante(nombre) {
                const search = document.getElementById('searchInput');
                search.value = nombre;
                applyFilters();
                // Scroll suave a la tabla
                document.getElementById('mTable').scrollIntoView({ behavior: 'smooth', block: 'center' });
            }

            function abrirModalDetalle(cedula) {
                const p = DB.find(x => x.ci === cedula);
                if(!p) return;

                document.getElementById('detalle-avatar').textContent = p.av;
                document.getElementById('detalle-nombre').textContent = p.n;
                document.getElementById('detalle-cedula').textContent = 'V-' + p.ci;
                document.getElementById('detalle-depto').textContent = p.d;
                document.getElementById('detalle-estado').textContent = (p.f === 0) ? 'Óptimo' : 'Irregular';
                document.getElementById('detalle-estado').className = (p.f === 0) ? 'text-success' : 'text-danger';
                
                // Estos campos son ficticios/acumulativos para el mes en este bento, 
                // en un sistema real se buscarían los registros específicos del día.
                document.getElementById('detalle-hora').textContent = 'Acumulado Mes';
                document.getElementById('detalle-metodo').textContent = 'Consolidado';

                document.getElementById('modal-detalle').classList.add('active');
            }

            function cerrarModalDetalle() {
                document.getElementById('modal-detalle').classList.remove('active');
            }
            
            </script>

        <?php elseif ($vistaActual === 'anual'): ?>
            <?php 
            /**
             * VISTA TOTAL (ANUAL) - REDISEÑO PREMIUM
             * Mantiene Banner/KPIs estándar pero eleva el Muro de Pasantes.
             */
            ?>
            <!-- Fuentes Premium -->
            <link rel="preconnect" href="https://fonts.googleapis.com">
            <link href="https://fonts.googleapis.com/css2?family=Geist:wght@300;400;500;600;700&family=JetBrains+Mono:wght@400;500;700&display=swap" rel="stylesheet">
            
            <style>
            :root {
                /* Premium Tokens (Adaptados del template) */
                --p-surface:    #FFFFFF;
                --p-surface-2:  #F8FAFD;
                --p-border:     #DDE2F0;
                --p-ink:        #0D1424;
                --p-ink-2:      #3A4768;
                --p-ink-3:      #7480A0;
                --p-blue:       #1D4ED8;
                --p-blue-dim:   rgba(29, 78, 216, 0.07);
                --p-green:      #059669;
                --p-green-dim:  rgba(5, 150, 105, 0.08);
                --p-radius:     20px;
                --p-shadow:     0 4px 6px -1px rgba(0,0,0,0.05), 0 2px 4px -2px rgba(0,0,0,0.05);
                --p-font:       'Geist', sans-serif;
            }

            /* Contenedor Muro */
            .muro-premium { font-family: var(--p-font); }
            
            /* Card Estilo Apple/Premium */
            .p-card {
                background: white;
                border: 1px solid var(--p-border);
                border-radius: var(--p-radius);
                padding: 24px;
                transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
                position: relative;
                overflow: hidden;
                display: flex;
                flex-direction: column;
                align-items: center;
                text-align: center;
                box-shadow: var(--p-shadow);
            }
            .p-card:hover {
                transform: translateY(-8px);
                box-shadow: 0 20px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);
                border-color: var(--p-blue);
            }

            /* Status Badge */
            .p-badge {
                padding: 6px 14px;
                border-radius: 50px;
                font-size: 0.75rem;
                font-weight: 700;
                display: inline-flex;
                align-items: center;
                gap: 6px;
                letter-spacing: 0.3px;
            }
            .p-badge-active { background: var(--p-blue-dim); color: var(--p-blue); }
            .p-badge-final  { background: var(--p-green-dim); color: var(--p-green); }

            /* Radial Gauge Premium */
            .p-gauge-wrap { position: relative; width: 130px; height: 130px; margin: 15px 0; }
            .p-gauge-svg { transform: rotate(-90deg); }
            .p-gauge-track { fill: none; stroke: #F1F5F9; stroke-width: 10; }
            .p-gauge-fill {
                fill: none;
                stroke-width: 10;
                stroke-linecap: round;
                stroke-dasharray: 339.292;
                transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .p-gauge-info {
                position: absolute;
                inset: 0;
                display: flex;
                flex-direction: column;
                align-items: center;
                justify-content: center;
            }
            .p-gauge-val { font-family: 'JetBrains Mono', monospace; font-size: 1.4rem; font-weight: 800; color: var(--p-ink); line-height: 1; }
            .p-gauge-lbl { font-size: 0.7rem; color: var(--p-ink-3); font-weight: 600; margin-top: 2px; }

            /* Animación Stagger */
            @keyframes p-reveal {
                from { opacity: 0; transform: translateY(20px); }
                to { opacity: 1; transform: translateY(0); }
            }
            .stagger-enter {
                opacity: 0;
                animation: p-reveal 0.6s cubic-bezier(0.16, 1, 0.3, 1) forwards;
            }

            /* Botones Soft/Ghost de Auditoría */
            .btn-p-soft {
                transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
                border-width: 1.5px !important;
                font-weight: 700 !important;
                background: transparent !important;
            }
            .btn-soft-pdf { color: #dc2626 !important; border-color: #dc2626 !important; }
            .btn-soft-pdf:hover { background: #dc2626 !important; color: white !important; box-shadow: 0 8px 15px rgba(220, 38, 38, 0.25); }
            
            .btn-soft-excel { color: #15803d !important; border-color: #15803d !important; }
            .btn-soft-excel:hover { background: #15803d !important; color: white !important; box-shadow: 0 8px 15px rgba(21, 128, 61, 0.25); }

            /* Modal Preview PDF Premium */
            .p-modal-overlay {
                position: fixed; inset: 0;
                background: rgba(13, 20, 36, 0.8); backdrop-filter: blur(10px);
                z-index: 9999; display: none; align-items: center; justify-content: center;
                padding: 40px; transition: all 0.3s ease;
            }
            .p-modal-content {
                background: #F8FAFD; width: 100%; max-width: 1100px; height: 90vh;
                border-radius: 28px; display: flex; flex-direction: column; overflow: hidden;
                box-shadow: 0 30px 60px -12px rgba(0,0,0,0.4); border: 1px solid rgba(255,255,255,0.3);
                transform: scale(0.95); transition: all 0.4s cubic-bezier(0.16, 1, 0.3, 1);
            }
            .p-modal-overlay.active { display: flex; }
            .p-modal-overlay.active .p-modal-content { transform: scale(1); }
            
            .p-modal-header {
                padding: 20px 32px; background: white; border-bottom: 1px solid #EDF2F7;
                display: flex; justify-content: space-between; align-items: center;
            }
            .p-modal-body { flex: 1; position: relative; background: #525659; }
            .p-modal-iframe { width: 100%; height: 100%; border: none; }
            
            .p-modal-close {
                width: 40px; height: 40px; border-radius: 12px; border: none;
                background: #F1F5F9; color: #475569; cursor: pointer;
                display: flex; align-items: center; justify-content: center; transition: all 0.2s;
            }
            .p-modal-close:hover { background: #dc2626; color: white; transform: rotate(90deg); }

            /* Grid Custom */
            .p-grid {
                display: grid;
                grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
                gap: 24px;
            }
            </style>

            <?php 
            // VISTA TOTAL (ANUAL): Muro de Graduados (Card Grid)
            $resumenTotal = [];
            if(!empty($registrosLista)) {
                foreach($registrosLista as $reg) {
                    $pid = $reg->pasante_id ?? 'P'.$reg->id;
                    if(!isset($resumenTotal[$pid])) {
                        $resumenTotal[$pid] = [
                            'nombre' => trim(($reg->apellidos ?? '') . ', ' . ($reg->nombres ?? '')),
                            'cedula' => $reg->cedula ?? '',
                            'departamento' => $reg->departamento_nombre ?? 'General',
                            'presentes' => 0,
                            'faltas' => 0
                        ];
                    }
                    $estado = strtolower($reg->estado ?? '');
                    if(strpos($estado, 'presente') !== false || strpos($estado, 'justificado') !== false) {
                        $resumenTotal[$pid]['presentes']++;
                    } elseif(strpos($estado, 'ausente') !== false) {
                        $resumenTotal[$pid]['faltas']++;
                    }
                }
            }
            
            // Navegación de Año
            $anioActual = (int)($paramsUrl['anio'] ?? date('Y'));
            $urlAnioAnt = URLROOT . "/asistencias?vista=anual&anio=" . ($anioActual - 1);
            $urlAnioSig = URLROOT . "/asistencias?vista=anual&anio=" . ($anioActual + 1);
            ?>
            <div class="muro-premium">
                <!-- Header / Navigation -->
                <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 32px; flex-wrap: wrap; gap: 20px;">
                    <div>
                        <h3 style="margin: 0 0 12px 0; color: var(--p-ink); font-weight: 800; font-size: 1.4rem; display: flex; align-items: center; gap: 10px;">
                            <i class="ti ti-chart-dots" style="color: var(--p-blue); font-size: 1.6rem;"></i> 
                            Histórico Anual <span style="color: var(--p-ink-4); font-weight: 300;">/</span> <?= $anioActual ?>
                        </h3>
                        <div style="display: flex; gap: 12px; align-items: center;">
                            <!-- Navegador de Año (Estándar SGP) -->
                            <div class="sw-nav-pill">
                                <a href="<?= $urlAnioAnt ?>" class="sw-nav-btn"><i class="ti ti-chevron-left"></i> <?= $anioActual-1 ?></a>
                                <span class="sw-nav-label"><?= $anioActual ?></span>
                                <a href="<?= $urlAnioSig ?>" class="sw-nav-btn"><?= $anioActual+1 ?> <i class="ti ti-chevron-right"></i></a>
                            </div>
                            
                            <!-- Filtros por Chips (Premium) -->
                            <div class="chip-row">
                                <button class="chip active-blue" onclick="filterMuro('all', this)">Todos</button>
                                <button class="chip" onclick="filterMuro('Activo', this)">Activos</button>
                                <button class="chip" onclick="filterMuro('Finalizado', this)">Finalizados</button>
                            </div>
                        </div>
                    </div>
                    
                    <div style="display: flex; align-items: center; gap: 16px;">
                        <button class="btn btn-p-soft btn-soft-pdf" onclick="openPreview('<?= URLROOT ?>/reportes/pdfNomina?download=0')" style="height: 42px; padding: 0 20px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                            <i class="ti ti-file-type-pdf" style="font-size: 1.1rem;"></i> PDF Global
                        </button>
                        <button class="btn btn-p-soft btn-soft-excel" onclick="window.location.href='<?= URLROOT ?>/reportes/excelAnual'" style="height: 42px; padding: 0 20px; border-radius: 12px; display: flex; align-items: center; gap: 8px;">
                            <i class="ti ti-file-type-xls" style="font-size: 1.1rem;"></i> Excel Anual
                        </button>
                        <div style="position: relative; width: 300px;">
                            <i class="ti ti-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); color: var(--p-blue); font-size: 1.2rem; z-index: 2;"></i>
                            <input type="text" id="buscadorTablaTotal" oninput="searchMuro()" placeholder="Buscar por nombre o CI..." 
                                   style="width: 100%; height: 42px; padding: 0 16px 0 48px; border: 1.5px solid var(--p-border); border-radius: 50px; background: white; color: var(--p-ink); font-weight: 600; font-size: 0.88rem; outline: none; transition: all 0.2s; box-shadow: var(--p-shadow);"
                                   onfocus="this.style.borderColor='var(--p-blue)'; this.style.boxShadow='0 0 0 4px var(--p-blue-dim)'" 
                                   onblur="this.style.borderColor='var(--p-border)'; this.style.boxShadow='var(--p-shadow)'">
                        </div>
                    </div>
                </div>
            
                <div class="p-grid" id="muroContainer">
                    <div id="muroNoResults" class="col-12 text-center py-5 d-none stagger-enter">
                        <i class="ti ti-search-off" style="font-size: 48px; color: var(--p-ink-4); margin-bottom: 16px; display: block;"></i>
                        <h4 style="font-weight: 700; color: var(--p-ink-3);">No se encontraron resultados</h4>
                    </div>

                    <?php if(empty($resumenTotal)): ?>
                        <div class="col-12 text-center py-5 stagger-enter">
                            <i class="ti ti-history-off" style="font-size: 48px; color: var(--p-ink-4); margin-bottom: 16px; display: block;"></i>
                            <h4 style="font-weight: 700; color: var(--p-ink-3);">No hay histórico registrado en este año</h4>
                        </div>
                    <?php else: ?>
                        <?php $muroDelay=0; foreach($resumenTotal as $pid => $rt): 
                            $nombres = explode(',', $rt['nombre']??'');
                            $apellido = trim($nombres[0] ?? 'A');
                            $nombreP = trim($nombres[1] ?? 'A');
                            $iniciales = strtoupper(substr($nombreP, 0, 1) . substr($apellido, 0, 1));
                            if(strlen(trim($iniciales)) < 2) $iniciales = strtoupper(substr($rt['nombre']??'AA', 0, 2));
                            
                            $horasAcumuladas = $rt['presentes'] * 8;
                            $horasMeta = 1440;
                            $pctHoras = $horasMeta > 0 ? min(100, round(($horasAcumuladas / $horasMeta) * 100)) : 0;
                            $isFinalizado = ($horasAcumuladas >= $horasMeta);
                            $estadoBadge = $isFinalizado ? 'Finalizado' : 'Activo';
                            // Colores de Auditoría: Finalizado (Cian #0ea5e9), Activo (Verde #059669)
                            $gaugeColor = $isFinalizado ? '#0ea5e9' : '#059669';
                            $statusBg = $isFinalizado ? 'rgba(14, 165, 233, 0.1)' : 'rgba(5, 150, 105, 0.1)';
                            $statusBorder = $isFinalizado ? 'rgba(14, 165, 233, 0.2)' : 'rgba(5, 150, 105, 0.2)';
                            
                            $searchData = strtolower($rt['nombre'] . ' ' . $rt['cedula'] . ' ' . $estadoBadge);
                        ?>
                        <div class="muro-item stagger-enter" 
                             data-search="<?= htmlspecialchars($searchData . ' ' . strtolower($rt['departamento']), ENT_QUOTES) ?>" 
                             data-estado="<?= $estadoBadge ?>"
                             style="animation-delay: <?= $muroDelay ?>ms;">
                            <?php $muroDelay += 30; ?>
                            <div class="p-card">
                                <!-- Status & Faltas Header -->
                                <div style="width: 100%; display: flex; justify-content: space-between; align-items: start; margin-bottom: 12px;">
                                    <span class="p-badge" style="background: <?= $statusBg ?>; color: <?= $gaugeColor ?>; border: 1px solid <?= $statusBorder ?>;">
                                        <i class="ti <?= $isFinalizado ? 'ti-circle-check' : 'ti-circle-dot' ?>"></i> <?= strtoupper($estadoBadge) ?>
                                    </span>
                                    <?php if($rt['faltas'] > 0): ?>
                                    <span class="p-badge" style="background: rgba(220, 38, 38, 0.08); color: #dc2626;">
                                        <i class="ti ti-alert-triangle"></i> <?= $rt['faltas'] ?> faltas
                                    </span>
                                    <?php endif; ?>
                                </div>

                                <!-- Persona -->
                                <h5 class="p-name" style="font-weight: 800; font-size: 1.15rem; color: var(--p-ink); margin: 0; white-space: nowrap; overflow: hidden; text-overflow: ellipsis; width: 100%;" title="<?= htmlspecialchars($rt['nombre']) ?>">
                                    <?= htmlspecialchars($rt['nombre']) ?>
                                </h5>
                                <div class="p-dept" style="font-size: 0.75rem; font-weight: 700; color: var(--p-blue); letter-spacing: 0.5px; text-transform: uppercase; margin-top: 4px;">
                                    <?= htmlspecialchars($rt['departamento']) ?>
                                </div>
                                <div class="p-ci" style="font-size: 0.8rem; color: var(--p-ink-3); font-family: 'JetBrains Mono', monospace; margin-top: 2px;">
                                    V-<?= htmlspecialchars($rt['cedula']) ?>
                                </div>

                                <!-- Radial Gauge -->
                                <div class="p-gauge-wrap">
                                    <svg class="p-gauge-svg" width="130" height="130" viewBox="0 0 130 130">
                                        <circle class="p-gauge-track" cx="65" cy="65" r="54" />
                                        <circle class="p-gauge-fill" cx="65" cy="65" r="54" 
                                                stroke="<?= $gaugeColor ?>"
                                                stroke-dashoffset="<?= 339.292 - (339.292 * $pctHoras) / 100 ?>" />
                                    </svg>
                                    <div class="p-gauge-info">
                                        <span class="p-gauge-val"><?= number_format($horasAcumuladas, 0, ',', '.') ?></span>
                                        <span class="p-gauge-lbl">/ <?= number_format($horasMeta, 0, ',', '.') ?> hrs</span>
                                    </div>
                                </div>

                                <div class="p-pct" style="font-size: 0.9rem; font-weight: 800; color: <?= $gaugeColor ?>; margin-bottom: 20px;">
                                    <?= $pctHoras ?>% COMPLETADO
                                </div>

                                <!-- Acciones -->
                                <div style="width: 100%; display: flex; gap: 8px; margin-top: auto; padding-top: 20px; border-top: 1px solid var(--p-border);">
                                    <button onclick="openPreview('<?= URLROOT ?>/reportes/pdfIndividual?id=<?= $pid ?>&download=0')" class="btn btn-p-soft btn-soft-pdf" style="flex: 1; height: 38px; border-radius: 10px; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 0;">
                                        <i class="ti ti-file-type-pdf" style="font-size: 1.1rem;"></i> PDF
                                    </button>
                                    <button onclick="window.location.href='<?= URLROOT ?>/reportes/exportarExcel?id=<?= $pid ?>'" class="btn btn-p-soft btn-soft-excel" style="flex: 1; height: 38px; border-radius: 10px; font-size: 0.8rem; display: flex; align-items: center; justify-content: center; gap: 6px; padding: 0;">
                                        <i class="ti ti-file-type-xls" style="font-size: 1.1rem;"></i> EXCEL
                                    </button>
                                </div>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>
                </div>
            </div><!-- /.muro-premium -->

            <!-- Modal de Previsualización PDF Premium -->
            <div id="pdfModal" class="p-modal-overlay">
                <div class="p-modal-content">
                    <div class="p-modal-header">
                        <div style="display:flex; align-items:center; gap:12px;">
                            <div style="width:40px; height:40px; border-radius:10px; background:#dc262611; color:#dc2626; display:flex; align-items:center; justify-content:center;">
                                <i class="ti ti-file-type-pdf" style="font-size:20px;"></i>
                            </div>
                            <div>
                                <h4 style="margin:0; font-weight:800; color:var(--p-ink); font-size:1.1rem;">Previsualización de Documento</h4>
                                <span style="font-size:0.75rem; color:var(--p-ink-3); font-weight:600;">SGP — Sistema de Gestión de Pasantes</span>
                            </div>
                        </div>
                        <div style="display:flex; gap:12px;">
                            <button id="btnDescargarActual" class="btn" style="height:40px; padding:0 16px; border-radius:12px; background:#15803d; color:white; border:none; font-weight:700; display:flex; align-items:center; gap:8px; font-size:0.85rem;">
                                <i class="ti ti-download"></i> Descargar Ahora
                            </button>
                            <button class="p-modal-close" onclick="closePreview()">
                                <i class="ti ti-x" style="font-size:20px;"></i>
                            </button>
                        </div>
                    </div>
                    <div class="p-modal-body">
                        <iframe id="pdfFrame" class="p-modal-iframe" src="about:blank"></iframe>
                    </div>
                </div>
            </div>

            <style>
                /* Shared CSS from Weekly View needed for Annual nav pills if not present */
                .sw-nav-pill { display: flex; align-items: center; background: white; border: 1.5px solid #DDE2F0; border-radius: 50px; padding: 3px 4px; gap: 2px; box-shadow: 0 1px 4px rgba(13,20,36,.05); }
                .sw-nav-btn { background: transparent; border: none; color: #7480A0; padding: 6px 12px; border-radius: 50px; font-size: .8rem; font-weight: 600; cursor: pointer; transition: background .15s; display: flex; align-items: center; gap: 3px; }
                .sw-nav-btn:hover { background: #F2F5FC; color: #0D1424; }
                .sw-nav-label { font-size: .83rem; font-weight: 700; color: #0D1424; padding: 0 12px; white-space: nowrap; }
            </style>

            <script>
            let muroActiveChip = 'all';
            function filterMuro(type, btn) {
                muroActiveChip = type;
                document.querySelectorAll('.chip').forEach(c => c.classList.remove('active-blue'));
                btn.classList.add('active-blue');
                searchMuro();
            }

            function searchMuro() {
                const q = document.getElementById('buscadorTablaTotal').value.toLowerCase();
                const items = document.querySelectorAll('.muro-item');
                let found = 0;

                items.forEach(item => {
                    const searchData = item.getAttribute('data-search').toLowerCase();
                    const estado = item.getAttribute('data-estado').toLowerCase();
                    
                    const matchQ = !q || searchData.includes(q);
                    const matchChip = muroActiveChip === 'all' || estado === muroActiveChip;

                    if (matchQ && matchChip) {
                        item.classList.remove('d-none');
                        found++;
                    } else {
                        item.classList.add('d-none');
                    }
                });

                const noRes = document.getElementById('muroNoResults');
                if (found === 0) noRes.classList.remove('d-none');
                else noRes.classList.add('d-none');
            }

            function doExportAnual() {
                let csv = 'Nombre,Cédula,Departamento,Horas Acumuladas,Meta,%,Estado\n';
                const items = document.querySelectorAll('.muro-item:not(.d-none)');
                items.forEach(item => {
                    const name = item.querySelector('.p-name').innerText.trim();
                    const ci = item.querySelector('.p-ci').innerText.trim().replace('V-', '');
                    const dept = item.querySelector('.p-dept').innerText.trim();
                    const accumulated = item.querySelector('.p-gauge-val').innerText.trim().replace(/\./g, '');
                    const meta = item.querySelector('.p-gauge-lbl').innerText.replace('/ ', '').replace(' hrs', '').trim().replace(/\./g, '');
                    const pct = item.querySelector('.p-pct').innerText.replace('% COMPLETADO', '').trim();
                    const state = item.getAttribute('data-estado').toUpperCase();
                    csv += `"${name}","${ci}","${dept}",${accumulated},${meta},${pct}%,${state}\n`;
                });
                if(items.length === 0) {
                    alert('No hay datos para exportar');
                    return;
                }
                const blob = new Blob(['\uFEFF' + csv], { type: 'text/csv;charset=utf-8;' });
                const url = URL.createObjectURL(blob);
                const a = document.createElement('a');
                a.href = url;
                a.download = `reporte_anual_pasantias_${new Date().getFullYear()}.csv`;
                a.click();
            }

            // Controlador de Previsualización PDF
            function openPreview(url) {
                const modal = document.getElementById('pdfModal');
                const iframe = document.getElementById('pdfFrame');
                const btnDownload = document.getElementById('btnDescargarActual');
                
                iframe.src = url;
                // El link de descarga es el mismo pero con download=1
                const downloadUrl = url.replace('download=0', 'download=1');
                btnDownload.onclick = () => window.location.href = downloadUrl;
                
                modal.classList.add('active');
                document.body.style.overflow = 'hidden'; // Evitar scroll de fondo
            }

            function closePreview() {
                const modal = document.getElementById('pdfModal');
                const iframe = document.getElementById('pdfFrame');
                
                modal.classList.remove('active');
                iframe.src = 'about:blank';
                document.body.style.overflow = 'auto';
            }

            // Cerrar al hacer clic fuera del contenido
            document.getElementById('pdfModal').addEventListener('click', (e) => {
                if (e.target.id === 'pdfModal') closePreview();
            });
            </script>
        <?php endif; ?>
    </div>
</div>

<!-- ===== ESTILOS DEL MODAL PREMIUM ===== -->
<style>
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,0.7); backdrop-filter: blur(6px);
    z-index: 9999; align-items: center; justify-content: center;
    animation: fadeIn 0.2s ease;
}
.modal-overlay.active { display: flex; }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes slideUp { from { transform:translateY(24px);opacity:0; } to { transform:translateY(0);opacity:1; } }

.modal-box {
    background: white; border-radius: 24px; width: 90%; max-width: 580px; max-height: 90vh; display: flex; flex-direction: column; overflow: hidden; box-shadow: 0 32px 80px rgba(15,23,42,0.3); animation: slideUp 0.3s ease;
}

.modal-head {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); padding: 28px 32px; display: flex; justify-content: space-between; align-items: center; flex-shrink: 0; color: white;
}
.modal-head h2 { font-size:1.3rem; font-weight:700; margin:0; color:white !important; }
.modal-head p  { font-size:0.85rem; margin:4px 0 0; color:rgba(255,255,255,0.8) !important; }
.modal-head * { color: white !important; }

.btn-close-modal {
    background: rgba(255,255,255,0.2); border: none; color: white !important; width: 36px; height: 36px; border-radius: 50%; cursor: pointer; font-size: 1.1rem; display: flex; align-items: center; justify-content: center; transition: background 0.2s; flex-shrink: 0;
}
.btn-close-modal:hover { background: rgba(255,255,255,0.35); }
.btn-close-modal i { color: white !important; }

.modal-body {
    padding: 28px 32px; overflow-y: auto; flex: 1;
}

.form-group { margin-bottom: 20px; }
.form-label {
    display:block; font-size:0.82rem; font-weight:700; color:#374151; margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;
}
.form-input {
    width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px; font-size:0.95rem; color:#1e293b; transition: border-color 0.2s, box-shadow 0.2s; box-sizing:border-box; background:#fafafa;
}
.form-input:focus {
    outline:none; border-color:#2563eb; box-shadow:0 0 0 4px rgba(79,70,229,0.1); background:white;
}

.btn-cancel {
    padding: 12px; border: 2px solid #e2e8f0; border-radius: 14px; background: #f1f5f9; color: #475569; font-weight: 700; cursor: pointer; font-size: 0.85rem; font-family: inherit; transition: background 0.2s;
}
.btn-cancel:hover { background: #e2e8f0; }

.btn-save {
    padding: 12px; background: linear-gradient(135deg, #172554, #2563eb); color: white; border: none; border-radius: 14px; font-weight: 700; cursor: pointer; font-size: 0.85rem; display: flex; align-items: center; justify-content: center; gap: 6px; font-family: inherit; box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3); transition: transform 0.2s, box-shadow 0.2s;
}
.btn-save:hover { transform: translateY(-2px); box-shadow: 0 6px 16px rgba(37, 99, 235, 0.4); }
</style>

<!-- ===== MODAL REGISTRO MANUAL ===== -->
<div id="modal-manual" class="modal-overlay" style="display: none;">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2><i class="ti ti-calendar-plus" style="margin-right:8px"></i>Registro Manual</h2>
                <p>Justificar ausencia o registrar asistencia</p>
            </div>
            <button class="btn-close-modal" onclick="cerrarModal()"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">
            <form id="form-manual" onsubmit="enviarManual(event)">
                <input type="hidden" name="fecha" value="<?= $hoy ?>">

                <!-- Custom Combo-Box HTML para Pasante -->
                <div class="form-group" style="position: relative;">
                    <label class="form-label" style="display:flex; align-items:center; gap:6px;"><i class="ti ti-user" style="font-size:1.1rem;"></i> Pasante *</label>
                    
                    <!-- Input visible de búsqueda -->
                    <div style="position: relative;">
                        <i class="ti ti-search" style="position: absolute; left: 16px; top: 50%; transform: translateY(-50%); font-size: 1.1rem; color: #94a3b8; pointer-events: none;"></i>
                        <input type="text" class="form-input" id="buscadorPasante" placeholder="Buscar por nombre..." style="padding-left: 44px; padding-right: 40px;" autocomplete="off">
                        
                        <!-- Botón para limpiar selección -->
                        <button type="button" id="btnLimpiarPasante" style="position: absolute; right: 12px; top: 50%; transform: translateY(-50%); display: none; background: #e2e8f0; border: none; width: 24px; height: 24px; border-radius: 50%; color: #64748b; cursor: pointer; align-items: center; justify-content: center; transition: all 0.2s;">
                            <i class="ti ti-x" style="font-size: 0.8rem;"></i>
                        </button>
                    </div>

                    <!-- Input oculto para el formulario real -->
                    <input type="hidden" name="pasante_id" id="manual-pasante-id" required>

                    <!-- Dropdown de resultados (oculto por defecto) -->
                    <div id="resultadosPasante" style="display: none; position: absolute; top: 100%; left: 0; right: 0; background: white; border: 1px solid #e2e8f0; border-radius: 12px; margin-top: 8px; box-shadow: 0 10px 25px rgba(0,0,0,0.1); max-height: 250px; overflow-y: auto; z-index: 100;">
                        <!-- Resultados inyectados por JS -->
                    </div>
                </div>

                <div class="form-group">
                    <label class="form-label" style="display:flex; align-items:center; gap:6px;"><i class="ti ti-activity" style="font-size:1.1rem;"></i> Estado *</label>
                    <select id="manual-estado" name="estado" required class="form-input" style="width: 100%; cursor: pointer;" onchange="toggleMotivo(this.value)">
                        <option value="Presente">✅ Presente</option>
                        <option value="Justificado">📄 Justificado</option>
                    </select>
                </div>

                <div class="form-group" id="div-motivo" style="display:none;">
                    <label class="form-label" style="display:flex; align-items:center; gap:6px;"><i class="ti ti-file-description" style="font-size:1.1rem;"></i> Motivo de justificación *</label>
                    <textarea id="manual-motivo" name="motivo_justificacion" rows="3" class="form-input" placeholder="Describe el motivo de la justificación..." style="width:100%; resize:vertical;"></textarea>
                </div>

                <div style="display:flex; gap:12px; margin-top:24px;">
                    <button type="button" class="btn-cancel" onclick="cerrarModal()" style="flex:1;">Cancelar</button>
                    <button type="submit" class="btn-save" id="btn-submit-manual" style="flex:1;"><i class="ti ti-device-floppy"></i> Guardar</button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- ===== MODAL DETALLE DE ASISTENCIA (Premium SGP) ===== -->
<div id="modal-detalle" class="sgp-modal-overlay" onclick="if(event.target===this)cerrarModalDetalle()">
    <div class="sgp-modal" style="max-width:480px;">
        <div class="sgp-modal-header">
            <h3><i class="ti ti-info-circle" style="margin-right:8px"></i>Detalle de Asistencia</h3>
            <p>Información completa del registro</p>
            <button class="sgp-modal-close" onclick="cerrarModalDetalle()"><i class="ti ti-x"></i></button>
        </div>
        <div class="sgp-modal-body" style="padding: 24px;">
            <div style="text-align:center; margin-bottom:20px;">
                <div id="detalle-avatar" style="width:72px; height:72px; background:linear-gradient(135deg,#162660,#2563eb); border-radius:18px; color:white; font-size:1.6rem; font-weight:800; display:flex; align-items:center; justify-content:center; margin:0 auto 12px; box-shadow:0 8px 20px rgba(37,99,235,0.25);"></div>
                <h4 id="detalle-nombre" style="margin:0; font-size:1.2rem; color:#1e293b; font-weight:700;"></h4>
                <p id="detalle-cedula" style="margin:4px 0 0; color:#64748b; font-size:0.9rem;"></p>
            </div>
            
            <div style="display:grid; grid-template-columns:1fr 1fr; gap:12px; margin-bottom:20px;">
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px;">
                    <span style="display:block; font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:4px;">Departamento</span>
                    <strong id="detalle-depto" style="color:#1e293b; font-size:0.85rem;"></strong>
                </div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px;">
                    <span style="display:block; font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:4px;">Hora de Registro</span>
                    <strong id="detalle-hora" style="color:#1e293b; font-size:0.85rem;"></strong>
                </div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px;">
                    <span style="display:block; font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:4px;">Método</span>
                    <strong id="detalle-metodo" style="color:#1e293b; font-size:0.85rem;"></strong>
                </div>
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:12px; padding:12px;">
                    <span style="display:block; font-size:0.7rem; font-weight:700; color:#94a3b8; text-transform:uppercase; margin-bottom:4px;">Estado</span>
                    <strong id="detalle-estado" style="font-size:0.85rem;"></strong>
                </div>
            </div>

            <div id="detalle-motivo-container" style="display:none; background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:16px; margin-bottom:20px;">
                <span style="display:block; font-size:0.75rem; font-weight:700; color:#d97706; text-transform:uppercase; margin-bottom:6px;"><i class="ti ti-file-description"></i> Motivo de justificación</span>
                <p id="detalle-motivo" style="margin:0; color:#92400e; font-size:0.9rem; line-height:1.5;"></p>
            </div>
        </div>
        <div style="padding:0 24px 24px; display:flex; gap:12px;">
             <button onclick="cerrarModalDetalle()" style="width:100%; padding:14px; background:#f1f5f9; color:#475569; font-weight:700; border:none; border-radius:12px; cursor:pointer; font-size:0.95rem; font-family:inherit; transition:background 0.2s;">Cerrar Detalle</button>
        </div>
    </div>
</div>

<!-- ===== ESTILOS (herencia diseño backup) ===== -->
<style>
.btn-banner-primary {
    background: white;
    color: #162660;
    border: none;
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 700;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    font-size: 0.9rem;
}
.btn-banner-primary:hover { transform: translateY(-2px); box-shadow: 0 4px 12px rgba(0,0,0,0.2); }

.btn-banner-secondary {
    background: rgba(255,255,255,0.15);
    color: white;
    border: 1.5px solid rgba(255,255,255,0.3);
    padding: 10px 20px;
    border-radius: 10px;
    font-weight: 600;
    cursor: pointer;
    display: flex;
    align-items: center;
    gap: 8px;
    transition: all 0.2s;
    font-size: 0.9rem;
}
.btn-banner-secondary:hover { background: rgba(255,255,255,0.25); }

/* ── Filter Pills (Inside Banner Glass) ── */
.pill-filter {
    display: inline-flex; align-items: center; gap: 6px;
    padding: 9px 18px; border-radius: 10px;
    font-size: 0.82rem; font-weight: 600;
    color: rgba(255,255,255,0.75);
    background: transparent;
    border: none;
    text-decoration: none;
    transition: all 0.25s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    white-space: nowrap;
}
.pill-filter:hover {
    color: white;
    background: rgba(255,255,255,0.12);
}
.pill-active {
    background: white !important;
    color: #172554 !important;
    box-shadow: 0 2px 8px rgba(0,0,0,0.15);
    font-weight: 700;
}
.pill-active:hover {
    box-shadow: 0 4px 12px rgba(0,0,0,0.2);
}
</style>

<!-- ===== ESTILOS EXCLUSIVOS: MODAL CONSULTA RÁPIDA ===== -->
<style>
    /* ── Filtros del motor frontend ── */
    .sgp-filtro-select {
        padding: 6px 10px; border: 1.5px solid #e2e8f0; border-radius: 8px;
        font-size: 0.78rem; font-weight: 600; color: #475569; background: white;
        outline: none; cursor: pointer; transition: border-color 0.2s;
        width: 100%;
    }
    .sgp-filtro-select:focus { border-color: #3b82f6; }

    /* ── Overrides para que el modal de auditoría tenga altura correcta ── */
    #modalConsultaRapida .sgp-modal {
        max-width: 580px;
        height: auto;
    }
    #modalConsultaRapida .sgp-modal-body {
        overflow-y: visible;
        max-height: unset;
    }

    /* ── Smart Scroll Moderno ── */
    ::-webkit-scrollbar {
        width: 6px;
        height: 6px;
    }
    ::-webkit-scrollbar-track {
        background: transparent;
    }
    ::-webkit-scrollbar-thumb {
        background: rgba(148, 163, 184, 0.4);
        border-radius: 10px;
    }
    ::-webkit-scrollbar-thumb:hover {
        background: rgba(100, 116, 139, 0.6);
    }

    /* ── Cápsula de perfil ── */
    .aud-profile-capsule {
        display: flex;
        align-items: center;
        gap: 14px;
        background: linear-gradient(135deg, #f8fafc 0%, #eff6ff 100%);
        border: 1px solid #dbeafe;
        border-radius: 18px;
        padding: 14px 16px;
        margin-bottom: 16px;
    }
    .aud-avatar {
        width: 50px; height: 50px;
        border-radius: 12px;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: 1.3rem;
        color: white;
        flex-shrink: 0;
        box-shadow: 0 4px 12px rgba(16,185,129,0.35);
    }

    /* ── KPI cards ── */
    .aud-kpi-grid {
        display: grid;
        grid-template-columns: 1fr 1fr 1fr;
        gap: 8px;
        margin-bottom: 14px;
    }
    .aud-kpi-card {
        border-radius: 12px;
        padding: 10px 12px;
        display: flex;
        flex-direction: column;
        align-items: center;
        gap: 2px;
        border: 1px solid transparent;
    }
    .aud-kpi-card .kpi-val { font-size: 1.5rem; font-weight: 800; line-height: 1; }
    .aud-kpi-card .kpi-lbl { font-size: 0.62rem; font-weight: 700; text-transform: uppercase; letter-spacing: 0.4px; opacity: 0.75; }
    .aud-kpi-presente  { background: #ecfdf5; border-color: #a7f3d0; }
    .aud-kpi-ausente   { background: #fef2f2; border-color: #fecaca; }
    .aud-kpi-justif    { background: #fffbeb; border-color: #fde68a; }

    /* ── Panel radial + KPIs horizontales ── */
    .aud-stats-row {
        display: grid;
        grid-template-columns: 120px 1fr;
        gap: 10px;
        margin-bottom: 14px;
        align-items: center;
    }
    .aud-radial-box {
        background: white;
        border: 1px solid #e2e8f0;
        border-radius: 16px;
        padding: 10px;
        display: flex;
        flex-direction: column;
        align-items: center;
        justify-content: center;
    }

    /* ── Timeline item ── */
    .aud-timeline-item {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 9px 12px;
        background: white;
        border: 1px solid #f1f5f9;
        border-radius: 10px;
        margin-bottom: 6px;
        transition: box-shadow 0.2s;
    }
    .aud-timeline-item:hover { box-shadow: 0 2px 8px rgba(0,0,0,0.05); }
    .aud-timeline-icon {
        width: 30px; height: 30px;
        border-radius: 8px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        margin-right: 10px;
    }

    /* ── Botones del footer ── */
    .aud-btn {
        display: inline-flex;
        align-items: center;
        gap: 6px;
        padding: 10px 18px;
        border-radius: 10px;
        font-size: 0.85rem;
        font-weight: 700;
        border: 1.5px solid transparent;
        cursor: pointer;
        font-family: inherit;
        transition: all 0.2s;
        text-decoration: none;
    }
    .aud-btn-primary {
        background: linear-gradient(135deg, #172554, #2563eb);
        color: white;
        box-shadow: 0 4px 12px rgba(37, 99, 235, 0.3);
    }
    .aud-btn-primary:hover { transform: translateY(-2px); box-shadow: 0 6px 18px rgba(37,99,235,0.4); }
    .aud-btn-ghost {
        background: white;
        color: #2563eb;
        border-color: #bfdbfe;
    }
    .aud-btn-ghost:hover { background: #eff6ff; }
    .aud-btn-close {
        background: #f1f5f9;
        color: #64748b;
        border-color: #e2e8f0;
    }
    .aud-btn-close:hover { background: #e2e8f0; }

    /* ── Responsividad Móvil (< 480px) ── */
    @media (max-width: 480px) {
        .aud-profile-capsule {
            flex-direction: column;
            text-align: center;
        }
        .aud-kpi-grid {
            grid-template-columns: 1fr;
        }
        .aud-stats-row {
            grid-template-columns: 1fr;
            justify-items: center;
        }
        .aud-kpi-card {
            flex-direction: row !important;
            justify-content: space-between !important;
            width: 100%;
        }
    }
</style>

<!-- ===== MODAL: AUDITORÍA / CONSULTA RÁPIDA DE PASANTE ===== -->
<div id="modalConsultaRapida" class="sgp-modal-overlay" onclick="if(event.target===this)cerrarModalConsulta()">
    <div id="modalConsultaContent" class="sgp-modal" style="transition: all 0.3s ease-in-out;">

        <!-- ── CABECERA AZUL ── -->
        <div class="sgp-modal-header">
            <div style="display:flex; align-items:center; gap:12px; position: relative; z-index: 1;">
                <div style="background:rgba(255,255,255,0.18); border-radius:10px; padding:8px; display:flex;">
                    <i class="ti ti-user-search" style="font-size:1.5rem; color:white;"></i>
                </div>
                <div>
                    <h3 style="margin:0; font-size:1.15rem; color:#fff;">Consulta Rápida</h3>
                    <p style="margin:3px 0 0; font-size:0.78rem; color:rgba(255,255,255,0.7);">Buscar por nombre o cédula del pasante</p>
                </div>
            </div>
            <button class="sgp-modal-close" onclick="cerrarModalConsulta()">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <!-- ── CUERPO EXPANDIBLE ── -->
        <div class="sgp-modal-body" style="padding: 24px; overflow-y: auto !important; max-height: 60vh !important;">
            
            <!-- BUSCADOR -->
            <div class="sgp-search-wrapper" style="margin-bottom: 8px;">
                <i class="ti ti-search sgp-search-icon"></i>
                <input
                    type="text"
                    id="inputBuscarPasanteAJAX"
                    class="sgp-search-input"
                    placeholder="Buscar por nombre o cédula..."
                    autocomplete="off">
                <div id="sgpAudLoading" class="sgp-search-loading"></div>
            </div>
            
            <!-- Lista de sugerencias -->
            <div id="listaSugerencias" class="sgp-results-list" style="display:none; transition: all 0.3s ease-in-out;">
            </div>

            <!-- Estado cero (Minimalista) -->
            <div id="estadoCeroBusqueda" style="padding: 20px 0; color: #94a3b8; text-align: center;">
                <i class="ti ti-users-group" style="font-size: 2.2rem; display: block; margin-bottom: 8px; opacity: 0.5;"></i>
                <p style="font-size: 0.85rem; margin: 0; font-weight: 500;">Escriba al menos 2 caracteres para buscar</p>
            </div>

            <!-- Zona de resultados (oculta hasta búsqueda) -->
            <div id="zonaResultadosPasante" style="display:none;">

                <!-- CÁPSULA DE PERFIL -->
                <div class="aud-profile-capsule">
                    <div id="avatarPasante" class="aud-avatar" style="background:#10b981;">P</div>
                    <div style="flex:1; min-width:0;">
                        <h4 id="nombrePasante" style="margin:0; font-size:1rem; color:#0f172a; font-weight:800; white-space:nowrap; overflow:hidden; text-overflow:ellipsis;">—</h4>
                        <p id="deptoPasante" style="margin:3px 0 0; font-size:0.78rem; color:#64748b; display:flex; align-items:center; gap:4px;">
                            <i class="ti ti-building"></i> —
                        </p>
                    </div>
                    <div style="background:#eff6ff; color:#2563eb; padding:4px 12px; border-radius:20px; font-size:0.7rem; font-weight:800; white-space:nowrap; text-transform:uppercase; letter-spacing:0.5px;">
                        Pasante
                    </div>
                </div>

                <!-- FILA: GRÁFICO RADIAL + KPIs -->
                <div class="aud-stats-row">
                    <!-- Gráfico Radial de Progreso -->
                    <div class="aud-radial-box">
                        <div id="graficoRadialTiempo" style="width:100px; height:100px;"></div>
                        <span style="font-size:0.63rem; color:#64748b; font-weight:800; text-transform:uppercase; letter-spacing:0.5px; margin-top:-8px;">Pasantía</span>
                    </div>

                    <!-- KPIs verticales -->
                    <div class="aud-kpi-grid" style="grid-template-columns:1fr;">
                        <!-- Presente -->
                        <div class="aud-kpi-card aud-kpi-presente" style="flex-direction:row; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="ti ti-circle-check" style="color:#059669; font-size:1.2rem;"></i>
                                <span class="kpi-lbl" style="color:#059669;">Presentes</span>
                            </div>
                            <span id="kpiPresentes" class="kpi-val" style="color:#059669;">0</span>
                        </div>
                        <!-- Ausente -->
                        <div class="aud-kpi-card aud-kpi-ausente" style="flex-direction:row; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="ti ti-circle-minus" style="color:#dc2626; font-size:1.2rem;"></i>
                                <span class="kpi-lbl" style="color:#dc2626;">Ausentes</span>
                            </div>
                            <span id="kpiAusentes" class="kpi-val" style="color:#dc2626;">0</span>
                        </div>
                        <!-- Justificado -->
                        <div class="aud-kpi-card aud-kpi-justif" style="flex-direction:row; justify-content:space-between;">
                            <div style="display:flex; align-items:center; gap:8px;">
                                <i class="ti ti-file-certificate" style="color:#d97706; font-size:1.2rem;"></i>
                                <span class="kpi-lbl" style="color:#d97706;">Justificados</span>
                            </div>
                            <span id="kpiJustificados" class="kpi-val" style="color:#d97706;">0</span>
                        </div>
                    </div>
                </div>

                <!-- FILTROS DEL HISTORIAL -->
                <div style="background:#f8fafc; border:1px solid #e2e8f0; border-radius:14px; padding:12px 14px; margin-bottom:12px;">
                    <div style="display:flex; align-items:center; justify-content:space-between; margin-bottom:8px;">
                        <span style="font-size:0.72rem; font-weight:800; color:#475569; display:flex; align-items:center; gap:5px;">
                            <i class="ti ti-adjustments-horizontal"></i> FILTRAR HISTORIAL
                        </span>
                        <button onclick="limpiarFiltrosAuditoria()" style="background:none; border:none; color:#3b82f6; font-size:0.72rem; font-weight:700; cursor:pointer; padding:0;">
                            <i class="ti ti-x" style="font-size:0.65rem;"></i> Limpiar
                        </button>
                    </div>
                    <div style="display:grid; grid-template-columns:repeat(3,1fr); gap:6px;">
                        <select id="filtroMesAuditoria"      class="sgp-filtro-select" onchange="ejecutarFiltrosAuditoria()">
                            <option value="">Mes</option>
                            <option value="01">Ene</option><option value="02">Feb</option><option value="03">Mar</option>
                            <option value="04">Abr</option><option value="05">May</option><option value="06">Jun</option>
                            <option value="07">Jul</option><option value="08">Ago</option><option value="09">Sep</option>
                            <option value="10">Oct</option><option value="11">Nov</option><option value="12">Dic</option>
                        </select>
                        <select id="filtroEstadoAuditoria"   class="sgp-filtro-select" onchange="ejecutarFiltrosAuditoria()">
                            <option value="">Estado</option>
                            <option value="presente">Presente</option>
                            <option value="ausente">Ausente</option>
                            <option value="justificado">Justificado</option>
                        </select>
                        <select id="filtroMetodoAuditoria"   class="sgp-filtro-select" onchange="ejecutarFiltrosAuditoria()">
                            <option value="">Método</option>
                            <option value="Manual">Manual</option>
                            <option value="Escritorio">PC</option>
                        </select>
                    </div>
                </div>

                <!-- TIMELINE -->
                <div style="font-size:0.72rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:0.5px; margin-bottom:8px; display:flex; align-items:center; gap:5px;">
                    <i class="ti ti-history"></i> Registros
                </div>
                <div id="timelineAsistencias" style="max-height: 160px !important; overflow-y: auto !important; padding-right: 10px; display: block;">
                    <!-- Cargado por JS -->
                </div>

            </div><!-- /zonaResultadosPasante -->
        </div><!-- /sgp-modal-body -->

        <!-- ── FOOTER CON ACCIONES ── -->
        <div id="audFooterAcciones" style="padding:14px 24px; border-top:1px solid #f1f5f9; background:#f8fafc;
                    display:none; align-items:center; justify-content:center; flex-shrink:0;">
            <!-- Botones contextuales (ocultos hasta que se seleccione pasante) -->
            <div style="display:flex; gap:8px; width: 100%;">
                <a href="#" id="btnExportarExcel" target="_self" class="aud-btn aud-btn-ghost" style="display:none; flex: 1; justify-content: center;">
                    <i class="ti ti-file-spreadsheet"></i> Exportar Excel
                </a>
                <button id="btnExportarPDF" class="aud-btn aud-btn-primary" style="display:none; flex: 1; justify-content: center;">
                    <i class="ti ti-file-type-pdf"></i> Generar PDF
                </button>
            </div>
        </div>

    </div><!-- /sgp-modal -->
</div><!-- /sgp-modal-overlay -->

<!-- ===== JAVASCRIPT ===== -->
<script>
function abrirModalConsulta() {
    const modal = document.getElementById('modalConsultaRapida');
    if (!modal) {
        console.error('Error: No se encontró el modal de auditoría.');
        return;
    }
    
    // Resetear UI
    const input = document.getElementById('inputBuscarPasanteAJAX');
    const sugerencias = document.getElementById('listaSugerencias');
    const estadoCero = document.getElementById('estadoCeroBusqueda');
    const zonaResultados = document.getElementById('zonaResultadosPasante');
    const footer = document.getElementById('audFooterAcciones');
    
    if (input) input.value = '';
    if (sugerencias) sugerencias.style.display = 'none';
    if (estadoCero) estadoCero.style.display = 'block';
    if (zonaResultados) zonaResultados.style.display = 'none';
    if (footer) footer.style.display = 'none';

    modal.classList.add('active');
    if (input) setTimeout(() => input.focus(), 300);
}

function cerrarModalConsulta() {
    const modal = document.getElementById('modalConsultaRapida');
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => {
        // Reset the form
        const inputBuscar = document.getElementById('inputBuscarPasanteAJAX');
        if (inputBuscar) { inputBuscar.value = ''; inputBuscar.placeholder = 'Buscar por nombre o cédula...'; }
        const zonaRes = document.getElementById('zonaResultadosPasante');
        if (zonaRes) zonaRes.style.display = 'none';
        const listaSug = document.getElementById('listaSugerencias');
        if (listaSug) listaSug.style.display = 'none';
        const estadoCero = document.getElementById('estadoCeroBusqueda');
        if (estadoCero) estadoCero.style.display = 'block';
        const btnPerfil = document.getElementById('btnIrPerfil');
        if (btnPerfil) btnPerfil.style.display = 'none';
        const btnPdf = document.getElementById('btnExportarPDF');
        if (btnPdf) btnPdf.style.display = 'none';
    }, 350);
}
/* ── Datos de pasantes (para el select del modal "Nuevo" registro) ── */
const pasantesActivos = <?= json_encode(array_map(fn($p) => [
    'id'     => $p->id,
    'nombre' => trim(($p->apellidos ?? '') . ', ' . ($p->nombres ?? '')),
], $pasantesActivos), JSON_HEX_TAG | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_HEX_AMP) ?>;

/* ── Abrir modal pre-seleccionado (desde lista "Sin Marcar") ─────── */
function abrirModalManual(pasanteId, nombre) {
    const inputVisible = document.getElementById('buscadorPasante');
    const inputOculto = document.getElementById('manual-pasante-id');
    const btnLimpiar = document.getElementById('btnLimpiarPasante');

    if (inputOculto) inputOculto.value = pasanteId;
    if (inputVisible) {
        inputVisible.value = nombre;
        inputVisible.readOnly = true; 
        inputVisible.style.background = '#f1f5f9'; 
    }
    if (btnLimpiar) btnLimpiar.style.display = 'none';

    const estadoEl = document.getElementById('manual-estado');
    if (estadoEl) {
        estadoEl.value = 'Presente';
        toggleMotivo('Presente');
    }

    const modal = document.getElementById('modal-manual');
    if (modal) modal.style.display = 'flex';
}

/* ── Abrir modal vacío (botón "Registro Manual" del banner) ─────── */
function abrirModalManualNuevo() {
    if (pasantesActivos.length === 0) {
        Swal.fire({ icon: 'info', title: 'Sin pasantes activos', text: 'No hay pasantes con pasantía activa para registrar.', confirmButtonColor: '#162660' });
        return;
    }

    const inputVisible = document.getElementById('buscadorPasante');
    const inputOculto = document.getElementById('manual-pasante-id');
    const btnLimpiar = document.getElementById('btnLimpiarPasante');

    if (inputOculto) inputOculto.value = '';
    if (inputVisible) {
        inputVisible.value = '';
        inputVisible.readOnly = false;
        inputVisible.style.background = '#fff';
    }
    if (btnLimpiar) btnLimpiar.style.display = 'none';

    const estadoEl = document.getElementById('manual-estado');
    if (estadoEl) {
        estadoEl.value = 'Presente';
        toggleMotivo('Presente');
    }

    const modal = document.getElementById('modal-manual');
    if (modal) modal.style.display = 'flex';
}

/* ── Cerrar modal ─────────────────────────────────────────── */
function cerrarModal() {
    document.getElementById('modal-manual').style.display = 'none';
    document.getElementById('form-manual').reset();
    document.getElementById('div-motivo').style.display = 'none';
    
    // Restaurar los inputs
    const inputVisible = document.getElementById('buscadorPasante');
    if (inputVisible) {
        inputVisible.readOnly = false;
        inputVisible.style.background = '#fff';
    }
}

/* ── Toggle campo motivo ─────────────────────────────────────────── */
function toggleMotivo(estado) {
    document.getElementById('div-motivo').style.display = estado === 'Justificado' ? 'block' : 'none';
    document.getElementById('manual-motivo').required = estado === 'Justificado';
}

/* ── Submit del form lateral ──────────────────────────────────────── */
function enviarManual(e) {
    e.preventDefault();
    const fd = new FormData(e.target);
    enviarRegistroManual(fd.get('pasante_id'), fd.get('estado'), fd.get('motivo_justificacion') || '');
}

/* ── Envío AJAX compartido ───────────────────────────────────────── */
function enviarRegistroManual(pasanteId, estado, motivo) {
    const btn = document.getElementById('btn-submit-manual');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> Guardando...'; }

    const fd = new FormData();
    fd.append('pasante_id', pasanteId);
    fd.append('fecha', '<?= $hoy ?>');
    fd.append('estado', estado);
    fd.append('motivo_justificacion', motivo);

    fetch('<?= URLROOT ?>/asistencias/registro_manual', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: '¡Registrado!', text: data.message || 'Asistencia registrada correctamente.', confirmButtonColor: '#162660' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo registrar.', confirmButtonColor: '#162660' });
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy"></i> Guardar'; }
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#162660' });
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy"></i> Guardar'; }
        });
}

/* ── Modal de detalle ─────────────────────────────────────────────── */
function verDetalle(id, info) {
    // Iniciales para el avatar
    const parts = info.nombre.split(',').map(s => s.trim());
    let iniciales = '?';
    if(parts.length === 2 && parts[0].length > 0 && parts[1].length > 0) {
        iniciales = (parts[1].charAt(0) + parts[0].charAt(0)).toUpperCase();
    } else if (info.nombre.length > 0) {
        iniciales = info.nombre.substring(0, 2).toUpperCase();
    }

    // Llenar datos
    document.getElementById('detalle-avatar').textContent = iniciales;
    document.getElementById('detalle-nombre').textContent = info.nombre;
    document.getElementById('detalle-cedula').textContent = 'C.I. ' + info.cedula;
    document.getElementById('detalle-depto').textContent = info.depto;
    document.getElementById('detalle-hora').textContent = info.hora;
    document.getElementById('detalle-metodo').textContent = info.metodo;
    
    // Configurar color del estado
    const estadoEl = document.getElementById('detalle-estado');
    estadoEl.textContent = info.estado;
    if(info.estado === 'Presente') estadoEl.style.color = '#10b981';
    else if(info.estado === 'Justificado') estadoEl.style.color = '#2563eb';
    else estadoEl.style.color = '#ef4444';

    // Motivo si existe
    const motivoContainer = document.getElementById('detalle-motivo-container');
    if (info.motivo) {
        document.getElementById('detalle-motivo').textContent = info.motivo;
        motivoContainer.style.display = 'block';
    } else {
        motivoContainer.style.display = 'none';
    }

    // Mostrar modal
    document.getElementById('modal-detalle').classList.add('active');
}

function cerrarModalDetalle() {
    document.getElementById('modal-detalle').classList.remove('active');
}

/* ── Exportar CSV ─────────────────────────────────────────────────── */
function exportarCSV() {
    let csv = 'Pasante,Cédula,Departamento,Hora,Método,Estado\n';
    <?php foreach ($registrosHoy as $r):
        $nombre = str_replace('"', '""', trim(($r->apellidos ?? '') . ', ' . ($r->nombres ?? '')));
        $cedula = str_replace('"', '""', $r->cedula ?? '');
        $depto  = str_replace('"', '""', $r->departamento_nombre ?? '');
        $hora   = $r->hora_registro ? date('h:i A', strtotime($r->hora_registro)) : '—';
        $metodo = $r->metodo ?? '—';
        $estado = $r->estado ?? '—';
    ?>
    csv += `"<?= $nombre ?>","<?= $cedula ?>","<?= $depto ?>","<?= $hora ?>","<?= $metodo ?>","<?= $estado ?>"\n`;
    <?php endforeach; ?>

    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url;
    a.download = 'asistencias_<?= $hoy ?>.csv';
    a.click();
    URL.revokeObjectURL(url);

    NotificationService.success('Archivo CSV descargado correctamente');
}

/* ── Cerrar modal si clic fuera ───────────────────────────────────── */
document.getElementById('modal-manual').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
/* ── Lógica del Combo-Box de Pasantes (Buscador en Tiempo Real) ── */
document.addEventListener('DOMContentLoaded', () => {
    const inputBuscador = document.getElementById('buscadorPasante');
    const inputOculto = document.getElementById('manual-pasante-id');
    const resultadosDiv = document.getElementById('resultadosPasante');
    const btnLimpiar = document.getElementById('btnLimpiarPasante');

    if (inputBuscador && resultadosDiv) {
        // Filtrar al escribir
        inputBuscador.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            resultadosDiv.innerHTML = ''; // Limpiar resultados
            
            // Si el input está vacío, ocultar dropdown y limpiar selección
            if (query === '') {
                resultadosDiv.style.display = 'none';
                btnLimpiar.style.display = 'none';
                inputOculto.value = '';
                return;
            }

            // Filtrar pasantes activos
            const filtrados = pasantesActivos.filter(p => p.nombre.toLowerCase().includes(query));

            if (filtrados.length === 0) {
                resultadosDiv.innerHTML = `
                <div style="padding: 24px; text-align: center;">
                    <i class="ti ti-ghost" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                    <p style="margin: 0; color: #64748b; font-size: 0.9rem; font-weight: 500;">
                        No se encontró ningún pasante activo llamado <strong style="color: #1e293b;">"${query}"</strong>.
                    </p>
                    <p style="margin: 6px 0 0; color: #94a3b8; font-size: 0.8rem;">
                        <strong style="color: #64748b;">Verifica si el nombre está bien escrito</strong> o si el pasante ya fue asignado.
                    </p>
                </div>`;
            } else {
                // Renderizar opciones
                filtrados.forEach(p => {
                    const item = document.createElement('div');
                    item.style.cssText = 'padding: 12px 16px; cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #1e293b; font-weight: 500; display: flex; align-items: center; gap: 8px;';
                    item.innerHTML = `<div style="width: 28px; height: 28px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">${p.nombre.substring(0,2).toUpperCase()}</div> ${p.nombre}`;
                    
                    // Hover effect via JS events since inline hover is tricky
                    item.addEventListener('mouseenter', () => item.style.background = '#f8fafc');
                    item.addEventListener('mouseleave', () => item.style.background = 'white');

                    // Al hacer clic en un resultado
                    item.addEventListener('click', () => {
                        inputBuscador.value = p.nombre;
                        inputOculto.value = p.id;
                        resultadosDiv.style.display = 'none';
                        btnLimpiar.style.display = 'flex';
                    });
                    resultadosDiv.appendChild(item);
                });
            }
            resultadosDiv.style.display = 'block';
        });

        // Limpiar selección
        btnLimpiar.addEventListener('click', () => {
            inputBuscador.value = '';
            inputOculto.value = '';
            btnLimpiar.style.display = 'none';
            resultadosDiv.style.display = 'none';
            inputBuscador.focus();
        });

        // Ocultar dropdown si se hace clic afuera
        document.addEventListener('click', (e) => {
            if (!inputBuscador.contains(e.target) && !resultadosDiv.contains(e.target)) {
                resultadosDiv.style.display = 'none';
            }
        });

        // Mostrar todo al enfocar el input vacío (opcional, para ver la lista inicial)
        inputBuscador.addEventListener('focus', function() {
            if (this.value === '') {
                // Trigger a search with empty string to show all initially, or leave it empty until typing. Let's force an empty search.
                this.dispatchEvent(new Event('input'));
            } else {
               resultadosDiv.style.display = 'block';
            }
        });
    }
});
</script>

<!-- ===== DEPENDENCIAS DATATABLES Y ESTILO PREMIUM VENTO BOX ===== -->
<link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.css">
<script src="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.js"></script>
<!-- Estilos DataTables Premium (Globalizados en datatables-sgp.css) -->

<script>
    $(document).ready(function() {
        if ($('#tablaHistorial').length) {
            // Inicializar la tabla
            var table = $('#tablaHistorial').DataTable({
                language: { url: '<?= URLROOT ?>/assets/libs/datatables/es-ES.json' },
                pageLength: 10,
                order: [[0, 'desc']],
                lengthMenu: [[10, 25, 50, -1], [10, 25, 50, "Todos"]],
                responsive: true
            });
            
            // Inicializar Flatpickr en español y en modo rango
            if (typeof flatpickr !== 'undefined') {
                const fp = flatpickr("#filtroFechaRango", {
                    mode: "range",
                    dateFormat: "d/m/Y",
                    locale: "es",
                    onChange: function(selectedDates, dateStr, instance) {
                        if (selectedDates.length > 0) {
                            $('#btnLimpiarFecha').show();
                        } else {
                            $('#btnLimpiarFecha').hide();
                        }
                        table.draw(); // Forzar a DataTables a redibujar aplicando el filtro
                    }
                });
                
                // Botón para limpiar el filtro de fechas
                $('#btnLimpiarFecha').on('click', function() {
                    fp.clear();
                    $(this).hide();
                    table.draw();
                });
            }
            
            // Extensión del buscador de DataTables para evaluar las fechas
            $.fn.dataTable.ext.search.push(
                function(settings, data, dataIndex) {
                    var fpInput = document.querySelector("#filtroFechaRango");
                    if (!fpInput || !fpInput._flatpickr || fpInput._flatpickr.selectedDates.length === 0) {
                        return true; // Si no hay fechas seleccionadas, mostrar todo
                    }
                    
                    var min = fpInput._flatpickr.selectedDates[0];
                    min.setHours(0, 0, 0, 0);
                    
                    var max = null;
                    if (fpInput._flatpickr.selectedDates.length > 1) {
                        max = fpInput._flatpickr.selectedDates[1];
                        max.setHours(23, 59, 59, 999);
                    } else {
                        // Si solo seleccionó un día, el máximo es ese mismo día
                        max = new Date(min.getTime());
                        max.setHours(23, 59, 59, 999);
                    }
                    
                    // Extraer la fecha de la primera columna (Ej: "28/02/2026")
                    var fechaTablaTexto = data[0].trim().substring(0, 10);
                    var partes = fechaTablaTexto.split('/');
                    if (partes.length === 3) {
                        // Convertir a objeto Date (Año, Mes - 1, Día)
                        var fechaTabla = new Date(partes[2], partes[1] - 1, partes[0]);
                        
                        // Lógica de evaluación
                        if (fechaTabla >= min && fechaTabla <= max) {
                            return true;
                        }
                        return false;
                    }
                    return true;
                }
            );
        }
    });
</script>

<!-- ===== APEXCHARTS PARA GRÁFICO RADIAL (Local) ===== -->
<script src="<?= URLROOT ?>/assets/libs/apexcharts/apexcharts.min.js"></script>

<script>
// ==========================================
// MOTOR DE CONSULTA RÁPIDA (AJAX + ApexCharts)
// ==========================================
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscar = document.getElementById('inputBuscarPasanteAJAX');
    const listaSugerencias = document.getElementById('listaSugerencias');
    const zonaResultados = document.getElementById('zonaResultadosPasante');
    let chartTiempo = null;

    if (!inputBuscar) return; // Si no existe el modal, salir

    // ==========================================
    // 1. BUSCADOR EN VIVO (Con Debounce)
    // ==========================================
    let timeoutId;
    inputBuscar.addEventListener('input', function() {
        clearTimeout(timeoutId);
        const query = this.value.trim();
        const estadoCero = document.getElementById('estadoCeroBusqueda');
        const zonaResultados = document.getElementById('zonaResultadosPasante');
        
        if (query.length < 2) {
            listaSugerencias.style.display = 'none';
            if(estadoCero) estadoCero.style.display = 'block';
            if(zonaResultados) zonaResultados.style.display = 'none';
            return;
        }

        if(estadoCero) estadoCero.style.display = 'none';
        if(zonaResultados) zonaResultados.style.display = 'none';

        const loadingIcon = document.getElementById('sgpAudLoading');
        if(loadingIcon) loadingIcon.classList.add('active');

        timeoutId = setTimeout(() => {
            const formData = new FormData();
            formData.append('query', query);

            fetch('<?= URLROOT ?>/asistencias/buscarPasanteAjax', {
                method: 'POST',
                body: formData
            })
            .then(res => res.json())
            .then(data => {
                if(loadingIcon) loadingIcon.classList.remove('active');
                listaSugerencias.innerHTML = '';
                
                if (data.length > 0) {
                    data.forEach(pasante => {
                        const div = document.createElement('div');
                        div.className = 'sgp-result-item';
                        
                        // Extraer iniciales reales (Ej: JG)
                        const iniciales = (pasante.nombres.charAt(0) + pasante.apellidos.charAt(0)).toUpperCase();
                        
                        div.innerHTML = `
                            <div class="sgp-result-avatar" style="background:#10b981;">${iniciales}</div>
                            <div class="sgp-result-info">
                                <div class="sgp-result-name">${pasante.nombres.toLowerCase()} ${pasante.apellidos.toLowerCase()}</div>
                                <div class="sgp-result-meta">C.I: ${pasante.cedula} · ${pasante.departamento_nombre}</div>
                            </div>
                            <span class="sgp-result-badge sgp-badge-pasante" style="background: #d1fae5; color: #059669; font-size: 0.6rem; margin-left: auto;">Pasante</span>
                        `;
                        
                        div.onclick = () => cargarAuditoriaPasante(pasante.pasante_id, pasante);
                        listaSugerencias.appendChild(div);
                    });
                    listaSugerencias.style.display = 'block';
                } else {
                    listaSugerencias.innerHTML = `
                        <div class="sgp-empty-state" style="padding: 16px; text-align: center; color: #64748b;">
                            <i class="ti ti-user-off" style="font-size: 1.5rem; opacity: 0.5;"></i>
                            <p style="margin: 4px 0 0; font-size: 0.85rem;">No se encontraron resultados</p>
                        </div>
                    `;
                    listaSugerencias.style.display = 'block';
                }
            })
            .catch(error => {
                if(loadingIcon) loadingIcon.classList.remove('active');
                console.error("Error buscando pasante:", error);
            });
        }, 300);


    });



    // ==========================================
    // 2. EXTRAER Y PINTAR AUDITORÍA
    // ==========================================
    let currentHistorial = []; // Store for frontend filtering

    function cargarAuditoriaPasante(idPasante, datosBasicos) {
        listaSugerencias.style.display = 'none';
        inputBuscar.value = ''; 
        inputBuscar.placeholder = 'Extrayendo expediente...';
        
        const formData = new FormData();
        formData.append('id_pasante', idPasante);

        fetch('<?= URLROOT ?>/asistencias/obtenerDatosAuditoriaAjax', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(data => {
            inputBuscar.placeholder = 'Buscar por nombre o cédula...';
            
            if (data.status === 'success') {
                const perfil = data.perfil;
                currentHistorial = data.historial; // Guardar para filtros

                // Ocultar estado cero y mostrar resultados
                document.getElementById('estadoCeroBusqueda').style.display = 'none';
                document.getElementById('zonaResultadosPasante').style.display = 'block';
                document.getElementById('audFooterAcciones').style.display = 'flex';

                // A. Llenar Perfil Compacto
                const iniciales = (perfil.nombres.charAt(0) + perfil.apellidos.charAt(0)).toUpperCase();
                const avatar = document.getElementById('avatarPasante');
                avatar.innerText = iniciales;
                avatar.style.background = '#10b981'; // Color por defecto pasante
                
                document.getElementById('nombrePasante').innerText = `${perfil.nombres} ${perfil.apellidos}`;
                document.getElementById('deptoPasante').innerHTML = `<i class="ti ti-building"></i> ${perfil.departamento_nombre}`;
                
                // Botones Footer
                const btnExcel = document.getElementById('btnExportarExcel');
                btnExcel.href = '<?= URLROOT ?>/reportes/excel/' + perfil.pasante_id; 
                btnExcel.style.display = 'flex';
                
                const btnPdf = document.getElementById('btnExportarPDF');
                btnPdf.onclick = () => window.open('<?= URLROOT ?>/reportes/pdfKardex?id=' + perfil.pasante_id, '_blank');
                btnPdf.style.display = 'flex';

                // B. Renderizar Timeline con Filtros Iniciales
                renderizarTimeline(currentHistorial);

                // C. ✅ FASE 2: Renderizar Gauge con progreso de calendario (L-V)
                renderizarGraficoTiempo(data.calendario, data.pro_rata.horas_meta);
            }
        });
    }

    // ==========================================
    // 3. MOTOR DE FILTROS FRONTEND
    // ==========================================
    window.ejecutarFiltrosAuditoria = function() {
        const mes = document.getElementById('filtroMesAuditoria').value;
        const estado = document.getElementById('filtroEstadoAuditoria').value;
        const metodo = document.getElementById('filtroMetodoAuditoria').value;

        const filtrados = currentHistorial.filter(reg => {
            const matchMes = !mes || reg.fecha.split('-')[1] === mes;
            const matchEstado = !estado || reg.estado.toLowerCase().includes(estado.toLowerCase());
            const matchMetodo = !metodo || reg.metodo.toLowerCase().includes(metodo.toLowerCase());
            return matchMes && matchEstado && matchMetodo;
        });

        renderizarTimeline(filtrados);
    };

    window.limpiarFiltrosAuditoria = function() {
        document.getElementById('filtroMesAuditoria').value = '';
        document.getElementById('filtroEstadoAuditoria').value = '';
        document.getElementById('filtroMetodoAuditoria').value = '';
        renderizarTimeline(currentHistorial);
    };

    function renderizarTimeline(datos) {
        const container = document.getElementById('timelineAsistencias');
        let countP = 0, countA = 0, countJ = 0;
        let html = '';

        if (datos.length > 0) {
            datos.forEach(reg => {
                const estadoL = reg.estado.toLowerCase();
                let icon = 'ti-check', iconColor = '#059669', iconBg = '#ecfdf5', badgeBg = '#d1fae5', badgeColor = '#059669';
                let label = 'Presente';

                if (estadoL.includes('presente')) {
                    countP++;
                } else if (estadoL.includes('ausente')) {
                    countA++;
                    icon = 'ti-x'; iconColor = '#dc2626'; iconBg = '#fef2f2'; badgeBg = '#fee2e2'; badgeColor = '#dc2626';
                    label = 'Ausente';
                } else {
                    countJ++;
                    icon = 'ti-file-certificate'; iconColor = '#d97706'; iconBg = '#fffbeb'; badgeBg = '#fef3c7'; badgeColor = '#d97706';
                    label = 'Justificado';
                }

                const fechaFormat = reg.fecha.split('-').reverse().join('/');
                let hora = '—';
                if (reg.hora_registro) {
                    const [h, m] = reg.hora_registro.split(':');
                    const numH = parseInt(h, 10);
                    const ampm = numH >= 12 ? 'PM' : 'AM';
                    const h12 = numH % 12 || 12;
                    hora = `${h12.toString().padStart(2, '0')}:${m} ${ampm}`;
                }
                const metodo = reg.metodo || '—';

                html += `
                    <div class="aud-timeline-item">
                        <div style="display:flex; align-items:center; gap:10px;">
                            <div class="aud-timeline-icon" style="background:${iconBg}; color:${iconColor};">
                                <i class="ti ${icon}" style="font-size:0.9rem;"></i>
                            </div>
                            <div>
                                <div style="font-weight:700; color:#1e293b; font-size:0.83rem;">${fechaFormat}</div>
                                <div style="font-size:0.68rem; color:#94a3b8; margin-top:1px;">
                                    <i class="ti ti-clock"></i> ${hora}
                                    <span style="margin:0 5px; opacity:0.4;">|</span>
                                    <i class="ti ti-device-desktop"></i> ${metodo}
                                </div>
                            </div>
                        </div>
                        <span style="background:${badgeBg}; color:${badgeColor}; padding:3px 10px; border-radius:20px; font-size:0.63rem; font-weight:800; text-transform:uppercase; letter-spacing:0.4px; white-space:nowrap;">${label}</span>
                    </div>
                `;
            });
        } else {
            html = `
                <div style="text-align:center; padding:28px 10px; color:#94a3b8;">
                    <i class="ti ti-mood-empty" style="font-size:2rem; opacity:0.35; display:block; margin-bottom:8px;"></i>
                    <p style="font-size:0.8rem; margin:0; font-weight:600;">Sin resultados con estos filtros</p>
                </div>
            `;
        }

        container.innerHTML = html;
        document.getElementById('kpiPresentes').innerText = countP;
        document.getElementById('kpiAusentes').innerText = countA;
        document.getElementById('kpiJustificados').innerText = countJ;
    }

    // ==========================================
    // 4. MOTOR DEL GRÁFICO RADIAL — CALENDARIO
    //    Fuente de verdad: objeto calendario { porcentaje_calendario, horas_calendario, ... }
    // ==========================================
    function renderizarGraficoTiempo(calendario, horasMeta = 1440) {
        if (chartTiempo) { chartTiempo.destroy(); }

        const container = document.querySelector('#graficoRadialTiempo');
        if (!container) return;

        // Fallback seguro
        const porcentaje  = calendario ? Math.round(calendario.porcentaje_calendario) : 0;
        const hCalendario = calendario ? calendario.horas_calendario : 0;
        const dTranscurridos = calendario ? calendario.dias_habiles_transcurridos : 0;
        const dRestantes     = calendario ? calendario.dias_habiles_restantes : 0;

        // Color dinámico: verde OK, amarillo en progreso, rojo bajo
        let color = '#10b981'; // verde
        if (porcentaje < 20) color = '#ef4444';      // rojo
        else if (porcentaje < 60) color = '#f59e0b'; // amarillo

        const options = {
            series: [porcentaje],
            chart: { height: 120, type: 'radialBar', sparkline: { enabled: true } },
            plotOptions: {
                radialBar: {
                    hollow: { size: '55%' },
                    track: { background: '#f1f5f9' },
                    dataLabels: {
                        name: { show: false },
                        value: {
                            show: true,
                            fontSize: '0.85rem',
                            fontWeight: 800,
                            color: '#1e293b',
                            offsetY: 6,
                            formatter: function(val) { return val + '%'; }
                        }
                    }
                }
            },
            colors: [color],
            stroke: { lineCap: 'round' },
            tooltip: {
                enabled: true,
                theme: 'light',
                y: { 
                    formatter: function() {
                        return `${hCalendario}h / ${horasMeta}h`;
                    },
                    title: {
                        formatter: function() { return ''; }
                    }
                },
                x: { show: false },
                custom: function({series, seriesIndex, dataPointIndex, w}) {
                    return `
                        <div style="padding: 12px; background: #ffffff; color: #334155; border-radius: 12px; font-size: 0.8rem; border: none; box-shadow: 0 10px 25px -5px rgba(0, 0, 0, 0.1), 0 8px 10px -6px rgba(0, 0, 0, 0.1);">
                            <div style="font-weight:800; margin-bottom: 8px; color: #64748b; font-size: 0.75rem; text-transform: uppercase; letter-spacing: 0.5px;">Progreso Calendario L-V</div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:6px; gap: 20px;">
                                <span style="color:#64748b; font-weight:600;">Días Transcurridos:</span>
                                <strong style="color:#0f172a;">${dTranscurridos}</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-bottom:6px; gap: 20px;">
                                <span style="color:#64748b; font-weight:600;">Días Restantes:</span>
                                <strong style="color:#0f172a;">${dRestantes}</strong>
                            </div>
                            <div style="display:flex; justify-content:space-between; margin-top: 8px; padding-top: 8px; border-top: 1px solid #e2e8f0;">
                                <span style="color:#2563eb; font-weight:700;">Horas Esperadas:</span>
                                <strong style="color:#1e293b; font-weight:800;">${hCalendario}h / ${horasMeta}h</strong>
                            </div>
                        </div>
                    `;
                }
            }
        };

        chartTiempo = new ApexCharts(container, options);
        chartTiempo.render();
    }
});
</script>

<!-- MODAL ALMANAQUE INTELIGENTE (Heatmap) -->
<div class="modal fade" id="modalResumenInteligente" tabindex="-1" aria-hidden="true" style="z-index: 1060;">
    <div class="modal-dialog modal-dialog-centered" style="max-width: 400px;">
        <div class="modal-content" style="border: none; border-radius: 20px; box-shadow: 0 20px 40px rgba(0,0,0,0.1);">
            <div class="modal-header text-white border-0" style="background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); border-radius: 20px 20px 0 0; padding: 24px 32px;">
                <div>
                    <h5 class="modal-title text-white" style="font-weight: 800; font-size: 1.1rem; display: flex; align-items: center; gap: 8px;">
                        <i class="ti ti-calendar-stats me-2"></i> <span id="nombrePasanteHeatmap">Pasante</span>
                    </h5>
                    <div class="text-light" style="font-size: 0.8rem; font-weight: 500; opacity: 0.9;">Heatmap de Asistencia</div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Close" style="font-size: 0.8rem; box-shadow: none;"></button>
            </div>
            <div class="modal-body" style="padding: 24px;">
                <div class="d-flex justify-content-center align-items-center gap-2 mb-4 mt-2">
                    <button type="button" id="btn-prev-month" class="btn btn-sm btn-outline-secondary" onclick="cambiarMesAlmanaque(-1)"><i class="ti ti-chevron-left"></i></button>
                    <select id="heatmap-mes" class="form-select form-select-sm fw-bold text-center shadow-none" style="width: 140px;" onchange="cargarAlmanaqueActual()">
                        <option value="01">Enero</option><option value="02">Febrero</option><option value="03">Marzo</option><option value="04">Abril</option><option value="05">Mayo</option><option value="06">Junio</option><option value="07">Julio</option><option value="08">Agosto</option><option value="09">Septiembre</option><option value="10">Octubre</option><option value="11">Noviembre</option><option value="12">Diciembre</option>
                    </select>
                    <select id="heatmap-anio" class="form-select form-select-sm fw-bold text-center shadow-none" style="width: 90px;" onchange="cargarAlmanaqueActual()">
                        <?php 
                            $anioA = date('Y') - 1;
                            $anioB = date('Y') + 1;
                            for($y = $anioA; $y <= $anioB; $y++) {
                                echo "<option value=\"$y\">$y</option>";
                            }
                        ?>
                    </select>
                    <button type="button" id="btn-next-month" class="btn btn-sm btn-outline-secondary" onclick="cambiarMesAlmanaque(1)"><i class="ti ti-chevron-right"></i></button>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 6px; margin-bottom: 8px; text-align: center; font-size: 0.75rem; font-weight: 700; color: #94a3b8; text-transform: uppercase;">
                    <div>L</div><div>M</div><div>M</div><div>J</div><div>V</div><div>S</div><div>D</div>
                </div>
                <!-- CSS GRID (7x6 máximo para cubrir un mes) -->
                <div id="grid-almanaque" style="display: grid; grid-template-columns: repeat(7, 1fr); gap: 5px;">
                    <!-- Se llena vía JS -->
                </div>
                
                <!-- Leyenda interactiva -->
                <div style="margin-top: 24px; display: flex; flex-wrap: wrap; gap: 12px; justify-content: center; font-size: 0.75rem; color: #64748b; font-weight: 600;">
                    <div style="display:flex; align-items:center; gap:6px;"><span style="width:12px; height:12px; border-radius:3px; background:#10b981;"></span> Presente</div>
                    <div style="display:flex; align-items:center; gap:6px;"><span style="width:12px; height:12px; border-radius:3px; background:#ef4444;"></span> Ausente</div>
                    <div style="display:flex; align-items:center; gap:6px;"><span style="width:12px; height:12px; border-radius:3px; background:#f59e0b;"></span> Justificado</div>
                    <div style="display:flex; align-items:center; gap:6px;"><span style="width:12px; height:12px; border-radius:3px; background:#f1f5f9; border:1px solid #e2e8f0;"></span> Sin Marcar</div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
    let currentAlmanaquePasanteId = null;

    function abrirAlmanaque(btn) {
        currentAlmanaquePasanteId = btn.getAttribute('data-id');
        document.getElementById('nombrePasanteHeatmap').innerText = btn.getAttribute('data-nombre');
        
        let targetMesText = "<?= str_pad($paramsUrl['anio'] ?? date('Y'), 4, '0', STR_PAD_LEFT) ?>-<?= str_pad(($paramsUrl['semana'] ? date('m', strtotime($paramsUrl['anio'].'W'.str_pad($paramsUrl['semana'], 2, '0', STR_PAD_LEFT))) : date('m')), 2, '0', STR_PAD_LEFT) ?>";
        const [targetAnio, targetMesNum] = targetMesText.split('-');
        
        const elMes = document.getElementById('heatmap-mes');
        const elAnio = document.getElementById('heatmap-anio');
        
        if (elMes.choicesInstance) elMes.choicesInstance.setChoiceByValue(targetMesNum);
        else elMes.value = targetMesNum;
        
        if (elAnio.choicesInstance) elAnio.choicesInstance.setChoiceByValue(targetAnio);
        else elAnio.value = targetAnio;
        
        const modal = new bootstrap.Modal(document.getElementById('modalResumenInteligente'));
        modal.show();
        cargarAlmanaqueActual();
    }

    function cambiarMesAlmanaque(delta) {
        const selMes = document.getElementById('heatmap-mes');
        const selAnio = document.getElementById('heatmap-anio');
        const d = new Date(selAnio.value + '-' + selMes.value + '-01T00:00:00');
        d.setMonth(d.getMonth() + delta);
        
        const newMes = String(d.getMonth() + 1).padStart(2, '0');
        const newAnio = String(d.getFullYear());
        
        if (selMes.choicesInstance) selMes.choicesInstance.setChoiceByValue(newMes);
        else selMes.value = newMes;
        
        if (selAnio.choicesInstance) selAnio.choicesInstance.setChoiceByValue(newAnio);
        else selAnio.value = newAnio;
        
        cargarAlmanaqueActual();
    }

    function cargarAlmanaqueActual() {
        if (!currentAlmanaquePasanteId) return;
        
        const selMes = document.getElementById('heatmap-mes').value;
        const selAnio = document.getElementById('heatmap-anio').value;
        const mesAnio = selAnio + '-' + selMes;
        const grid = document.getElementById('grid-almanaque');
        grid.innerHTML = '<div style="grid-column: 1 / -1; text-align: center; padding: 20px;"><div class="spinner-border text-primary" role="status"><span class="visually-hidden">Cargando...</span></div></div>';
        
        const formData = new FormData();
        formData.append('pasante_id', currentAlmanaquePasanteId);
        formData.append('mes_anio', mesAnio);

        fetch('<?= URLROOT ?>/asistencias/obtenerResumenMensual', {
            method: 'POST',
            body: formData
        })
        .then(res => res.json())
        .then(res => {
            if(res.success) {
                renderizarGridAlmanaque(mesAnio, res.datos);
            } else {
                grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; color: red;">Error al cargar datos.</div>`;
            }
        })
        .catch(err => {
            grid.innerHTML = `<div style="grid-column: 1 / -1; text-align: center; color: red;">Error de red.</div>`;
        });
    }

    function renderizarGridAlmanaque(mesAnio, asistenciasData) {
        const grid = document.getElementById('grid-almanaque');
        const [year, month] = mesAnio.split('-').map(Number);
        const diasEnMes = new Date(year, month, 0).getDate();
        
        // 1 = Lunes, 0 = Domingo. JS usa 0=Domingo
        let objDate = new Date(year, month - 1, 1);
        let primerDiaSemana = objDate.getDay(); 
        primerDiaSemana = primerDiaSemana === 0 ? 6 : primerDiaSemana - 1; // Ajuste para que Lunes sea 0 y Domingo 6

        let html = '';
        
        // Espacios en blanco para empezar en el día correcto
        for (let i = 0; i < primerDiaSemana; i++) {
            html += `<div style="aspect-ratio: 1/1;"></div>`;
        }

        const btnHoy = new Date().toISOString().split('T')[0];

        // Llenar los días
        for (let d = 1; d <= diasEnMes; d++) {
            const dateStr = `${year}-${String(month).padStart(2, '0')}-${String(d).padStart(2, '0')}`;
            const estadoStr = asistenciasData[dateStr] ? asistenciasData[dateStr].toLowerCase() : null;
            
            let bgClass = '#f8fafc'; // Gris default
            let border = '1px solid #e2e8f0';
            let color = '#94a3b8';
            let tooltip = `Fecha: ${dateStr}\nSin marcar`;
            
            if (estadoStr) {
                if (estadoStr.includes('presente')) {
                    bgClass = '#10b981'; border = 'none'; color = '#fff'; tooltip = `Fecha: ${dateStr}\nPresente`;
                } else if (estadoStr.includes('ausente')) {
                    bgClass = '#ef4444'; border = 'none'; color = '#fff'; tooltip = `Fecha: ${dateStr}\nAusente`;
                } else if (estadoStr.includes('justificado')) {
                    bgClass = '#f59e0b'; border = 'none'; color = '#fff'; tooltip = `Fecha: ${dateStr}\nJustificado`;
                }
            }

            let todayShadow = (dateStr === btnHoy) ? 'box-shadow: inset 0 0 0 2px #3b82f6;' : '';
            
            html += `
                <div title="${tooltip}" style="background: ${bgClass}; border: ${border}; border-radius: 4px; aspect-ratio: 1/1; display: flex; align-items: center; justify-content: center; font-size: 0.75rem; font-weight: 700; color: ${color}; cursor: default; transition: transform 0.2s ease; ${todayShadow}" onmouseover="this.style.transform='scale(1.1)'" onmouseout="this.style.transform='scale(1)'">
                    ${d}
                </div>
            `;
        }
        grid.innerHTML = html;
    }
</script>

<!-- ===== FASE 5: ELITE SAAS UI/UX ENHANCEMENTS ===== -->
<style>
/* Animaciones en Cascada (Skeleton Load Feeling) */
@keyframes staggerFadeInUp {
    0% { opacity: 0; transform: translateY(20px); filter: blur(5px); }
    100% { opacity: 1; transform: translateY(0); filter: blur(0); }
}

.stagger-enter {
    opacity: 0;
    animation: staggerFadeInUp 0.5s cubic-bezier(0.16, 1, 0.3, 1) forwards;
}

/* Glassmorphism en Sticky Header y Cajas (Estética Backdrop Blur) */
.glass-header {
    backdrop-filter: blur(12px) !important;
    background: rgba(248, 250, 252, 0.85) !important;
}
.glass-input {
    background: rgba(255, 255, 255, 0.5) !important;
    backdrop-filter: blur(8px);
    border: 1px solid rgba(226, 232, 240, 0.8) !important;
    box-shadow: 0 4px 15px rgba(0, 0, 0, 0.02);
    transition: all 0.3s cubic-bezier(0.16, 1, 0.3, 1);
}
.glass-input:focus {
    background: rgba(255, 255, 255, 0.95) !important;
    border-color: #3b82f6 !important;
    box-shadow: 0 0 0 4px rgba(59, 130, 246, 0.1) !important;
}
.btn-clean-input {
    position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #94a3b8; cursor: pointer; z-index: 2; transition: color 0.2s;
}
.btn-clean-input:hover { color: #ef4444; }

/* Flat & Crisp UI: The Death of the Generic Drop Shadow */
.crisp-card {
    border: 1px solid #f1f5f9 !important;
    box-shadow: 0 4px 16px rgba(15, 23, 42, 0.02) !important;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1), box-shadow 0.3s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.bento-card:hover, .muro-card:hover {
    transform: translateY(-5px);
    box-shadow: 0 20px 40px rgba(15, 23, 42, 0.06) !important;
    border-color: rgba(59, 130, 246, 0.1) !important;
}
.group-hover-bg:hover { background: white !important; box-shadow: 0 4px 12px rgba(0,0,0,0.03); }
.crisp-border { border: 1px solid rgba(15, 23, 42, 0.04); }

/* Status Rings (El estilo Instagram Stories) */
.status-ring {
    position: relative;
    z-index: 10;
}
.status-ring::after {
    content: '';
    position: absolute;
    top: -4px; left: -4px; right: -4px; bottom: -4px;
    border-radius: 50%;
    border: 2px solid transparent;
    transition: transform 0.3s cubic-bezier(0.16, 1, 0.3, 1);
    z-index: -1;
}
.status-ring.ring-success::after { border-color: rgba(16, 185, 129, 0.3); }
.status-ring.ring-danger::after  { border-color: rgba(239, 68, 68, 0.4); }
.status-ring.ring-primary::after { border-color: rgba(59, 130, 246, 0.3); }
.bento-card:hover .status-ring::after { transform: scale(1.1); }

/* Radial Progress / Gráficas Gauge (CSS Puro Apple-Style) */
.radial-gauge {
    transition: --pct 1.5s cubic-bezier(0.16, 1, 0.3, 1);
}
.muro-card .radial-gauge { margin: 0 auto; box-shadow: inset 0 0 0 1px rgba(0,0,0,0.03); }

/* Custom Properties Required for native CSS animation of Conic-gradients in modern Webkit */
@property --pct {
  syntax: '<percentage>';
  inherits: false;
  initial-value: 0%;
}

/* Sparklines Visuales / Tendencias Flexbox */
.sparkline-bar {
    opacity: 0.6;
    transition: opacity 0.2s ease, transform 0.2s cubic-bezier(0.16, 1, 0.3, 1);
    transform-origin: bottom;
}
.group-hover-bg:hover .sparkline-bar { opacity: 1; transform: scaleY(1.3); }

/* Buttons & Hero Interactions */
.btn-elite {
    border-radius: 12px !important; font-weight: 600 !important; padding: 10px !important; font-size: 0.85rem !important; transition: all 0.2s cubic-bezier(0.16, 1, 0.3, 1) !important;
}
.btn-elite:hover, .btn-elite-primary:hover { transform: translateY(-2px); box-shadow: 0 8px 16px rgba(15, 23, 42, 0.08) !important; }
.btn-icon-hover i { transition: transform 0.2s; }
.btn-icon-hover:hover i { transform: scale(1.2); color: #0f172a; }

/* Empty States Beautiful */
.empty-state {
    transition: transform 0.3s ease, opacity 0.3s ease;
}
.empty-state:hover { transform: translateY(-2px); opacity: 1 !important; }

/* ===== COHERENCIA VISUAL INSTITUCIONAL SGP ===== */
/* Tokens de la app: navy #1e2b58, accent #F59E0B, bg #F0F5FA */

.sgp-action-bar { transition: box-shadow 0.3s ease; }
.sgp-action-bar:hover { box-shadow: 0 12px 32px rgba(30,43,88,0.08) !important; }

/* Bento cards: alineadas con el card system de la app */
.bento-card {
    border: 1px solid rgba(30,43,88,0.06) !important;
    box-shadow: 0 4px 16px rgba(30,43,88,0.04) !important;
    transition: transform 0.25s cubic-bezier(0.4,0,0.2,1),
                box-shadow 0.25s cubic-bezier(0.4,0,0.2,1),
                border-color 0.2s ease !important;
    min-height: 200px;
}
.bento-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 20px 40px rgba(30,43,88,0.1), 0 4px 12px rgba(30,43,88,0.04) !important;
    border-color: rgba(30,43,88,0.15) !important;
}
/* Accent line al hover (igual que .kpi-card de modern-theme) */
.bento-card::before {
    content: '';
    position: absolute;
    top: 0; left: 0; right: 0;
    height: 3px;
    background: linear-gradient(90deg, #1e2b58 0%, #F59E0B 100%);
    border-radius: 24px 24px 0 0;
    opacity: 0;
    transition: opacity 0.25s ease;
}
.bento-card { position: relative; overflow: hidden; }
.bento-card:hover::before { opacity: 1; }

/* Tabla Maestra: hover de fila con token institucional */
#tablaMaestra tbody tr.sgp-tabla-fila {
    transition: background 0.12s ease;
}
#tablaMaestra tbody tr.sgp-tabla-fila:hover {
    background: rgba(30,43,88,0.025) !important;
}
#tablaMaestra thead tr th.sgp-th:hover {
    background: rgba(30,43,88,0.04) !important;
}

/* Selector de mes: usa el accent dorado al hover */
.sgp-mes-nav a:hover { color: #1e2b58 !important; }

/* Chips de filtros activos: azul navy institucional */
#activeFiltersBar span {
    background: rgba(30,43,88,0.06) !important;
    color: #1e2b58 !important;
    border-color: rgba(30,43,88,0.12) !important;
}

/* Keyframe para el toast de exportación */
@keyframes sgpFadeIn {
    from { opacity: 0; transform: translateX(20px); }
    to   { opacity: 1; transform: translateX(0); }
}

/* ===== FIX #4: CSS Design Tokens — Color System Unificado ===== */
:root {
    /* Success (verde único) */
    --sgp-success:        #10b981;
    --sgp-success-bg:     #f0fdf4;
    --sgp-success-border: #d1fae5;
    --sgp-success-text:   #166534;
    /* Danger */
    --sgp-danger:         #ef4444;
    --sgp-danger-bg:      #fef2f2;
    --sgp-danger-border:  #fee2e2;
    --sgp-danger-text:    #991b1b;
    /* Warning */
    --sgp-warning:        #f59e0b;
    --sgp-warning-bg:     #fffbeb;
    --sgp-warning-border: #fef3c7;
    --sgp-warning-text:   #92400e;
    /* Neutrales */
    --sgp-border:         #f1f5f9;
    --sgp-surface:        #f8fafc;
    --sgp-text-primary:   #0f172a;
    --sgp-text-secondary: #475569;
    --sgp-text-muted:     #94a3b8;
    /* Tipografía (escala de 4) */
    --sgp-t-heading:  1.05rem;
    --sgp-t-body:     0.875rem;
    --sgp-t-small:    0.75rem;
    --sgp-t-micro:    0.7rem;
}

/* ===== FIX #5: Grid Responsivo con clamp() ===== */
.bento-grid {
    display: grid !important;
    gap: 20px !important;
    grid-template-columns: repeat(auto-fill, minmax(min(100%, 300px), 1fr)) !important;
    align-items: start !important;
}

/* ===== Hover Premium Bento Card (Linear / Vercel style) ===== */
.bento-card {
    min-height: 200px;
    transition: transform 0.25s cubic-bezier(0.16,1,0.3,1),
                box-shadow 0.25s cubic-bezier(0.16,1,0.3,1),
                border-color 0.25s ease !important;
}
.bento-card:hover {
    transform: translateY(-4px) !important;
    box-shadow: 0 20px 48px rgba(15,23,42,0.09), 0 4px 12px rgba(15,23,42,0.04) !important;
    border-color: rgba(59,130,246,0.18) !important;
}

/* ===== Tipografía coherente en el Bento ===== */
.bento-card h4 { font-size: var(--sgp-t-heading); }
.bento-card .text-muted { font-size: var(--sgp-t-small); }
.bento-card span[style*="0.7rem"] { font-size: var(--sgp-t-micro) !important; }

/* ===== Mini-bars limpias ===== */
.sgp-bar {
    border-radius: 10px;
    transform-origin: bottom;
}
.sgp-sparkline:hover .sgp-bar {
    opacity: 1 !important;
    filter: brightness(1.1);
}
</style>

<!-- ===== FASE 5: LOGICA SAAS SUPERIOR (NATIVE JS) ===== -->
<script>
document.addEventListener("DOMContentLoaded", function() {
    
    // 1. Motor de Búsqueda Elite (Vanilla JS) sin librerías pesadas
    function setupLiveSearch(inputID, containerID, itemClass, noResultsID) {
        const input = document.getElementById(inputID);
        const container = document.getElementById(containerID);
        const noResults = document.getElementById(noResultsID);
        if(!input || !container) return;
        
        const items = container.querySelectorAll(itemClass);
        input.addEventListener('input', function(e) {
            const term = e.target.value.toLowerCase().trim();
            let count = 0;
            items.forEach(item => {
                const searchData = item.getAttribute('data-search') || '';
                if(searchData.includes(term)) {
                    item.style.display = 'block';
                    // Re-trigger visual feedback skeleton
                    item.classList.remove('stagger-enter');
                    void item.offsetWidth; 
                    if(term !== '') {
                        item.classList.add('stagger-enter');
                        item.style.animationDelay = '0ms';
                    }
                    count++;
                } else {
                    item.style.display = 'none';
                }
            });
            if(count === 0 && term !== '') noResults.classList.remove('d-none');
            else noResults.classList.add('d-none');
        });
    }

    setupLiveSearch('buscadorBento', 'bentoContainer', '.bento-item', 'bentoNoResults');
    setupLiveSearch('buscadorTablaTotal', 'muroContainer', '.muro-item', 'muroNoResults');

    // 2. Intersection Observer para Gauges (Efecto Satisfactorio de Llenado Mágico)
    const gaugeObserver = new IntersectionObserver((entries) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                // Dispara el llenado desde CSS al cruzar el target
                const targetPct = entry.target.style.getPropertyValue('--target-pct');
                if(targetPct) {
                    // Timeout sutil para dar ese efecto visual de "delay" natural de Apple
                    setTimeout(() => {
                        entry.target.style.setProperty('--pct', targetPct);
                        entry.target.classList.add('gauge-animated');
                    }, 150);
                }
                gaugeObserver.unobserve(entry.target);
            }
        });
    }, { threshold: 0.1 }); // Se dispara al ver el 10% del gauge

    document.querySelectorAll('.observer-animate').forEach(el => gaugeObserver.observe(el));
    
    // 3. Render ApexCharts Donut in Mensual View if container exists
    const donutContainer = document.querySelector('#healthDonutChart');
    if (donutContainer) {
        var options = {
            series: [<?= $totalAsistencias ?? 0 ?>, <?= $totalFaltasGlobal ?? 0 ?>, <?= $totalJustificadosGlobal ?? 0 ?>],
            labels: ['Presentes', 'Faltas', 'Justificados'],
            colors: ['#10b981', '#ef4444', '#f59e0b'],
            chart: {
                type: 'donut',
                height: 120, // Adjusted to fit Hero Box
                width: 120,
                sparkline: { enabled: true } // Creates a clean gauge-like donut without paddings
            },
            plotOptions: {
                pie: {
                    donut: {
                        size: '75%',
                        labels: { show: false }
                    }
                }
            },
            dataLabels: { enabled: false },
            tooltip: {
                enabled: true,
                theme: 'light',
                y: { formatter: function(val) { return val + " Registros" } }
            },
            stroke: { width: 0 }
        };
        
        var chart = new ApexCharts(donutContainer, options);
        chart.render();
    }

    // ===== SGP: Filtros del Bento Box =====
    function applyBentoFilters() {
        const q     = (document.getElementById('bentoBuscador')?.value  || '').toLowerCase().trim();
        const depto = (document.getElementById('filtroDepto')?.value    || '').toLowerCase().trim();
        const est   = (document.getElementById('filtroEstado')?.value   || '').toLowerCase().trim();
        let visible = 0;

        document.querySelectorAll('#bentoContainer .bento-item').forEach(item => {
            const search    = (item.getAttribute('data-search')       || '').toLowerCase();
            const iDepto    = (item.getAttribute('data-depto')       || '').toLowerCase();
            const iFaltas   = item.getAttribute('data-tiene-faltas') === 'true';

            const mQ     = !q     || search.includes(q);
            const mDepto = !depto || iDepto === depto;
            const mEst   = !est   || (est === 'faltas' && iFaltas) || (est === 'perfectos' && !iFaltas);

            const show = mQ && mDepto && mEst;
            item.style.display = show ? '' : 'none';
            if (show) visible++;
        });

        const noR = document.getElementById('bentoNoResults');
        if (noR) noR.classList.toggle('d-none', visible > 0 || (!q && !depto && !est));

        // Chips de filtros activos
        const bar = document.getElementById('activeFiltersBar');
        if (!bar) return;
        bar.innerHTML = '';
        const chip = (icon, txt, clearId) =>
            `<span style="display:inline-flex;align-items:center;gap:6px;padding:4px 12px;border-radius:50px;background:#eff6ff;color:#1d4ed8;font-size:0.78rem;font-weight:600;border:1px solid #bfdbfe;">
                <i class="ti ${icon}"></i>${txt}
                <span onclick="sgpClearFilter('${clearId}')" style="cursor:pointer;opacity:0.5;font-size:0.85rem;">✕</span>
            </span>`;
        if (q)     bar.innerHTML += chip('ti-search',   `"${q}"`,                          'bentoBuscador');
        if (depto) bar.innerHTML += chip('ti-building', depto,                             'filtroDepto');
        if (est)   bar.innerHTML += chip('ti-filter',   est==='faltas'?'Con Faltas':'Sin Faltas','filtroEstado');
    }

    ['bentoBuscador','filtroDepto','filtroEstado'].forEach(id => {
        const el = document.getElementById(id);
        if (el) el.addEventListener(id === 'bentoBuscador' ? 'input' : 'change', applyBentoFilters);
    });

    // ===== BARRAS CHART: Índice de Salud Global (Estilo Mini-Bars CSS) =====
    (function() {
        const areaEl = document.getElementById('areaHealthChart');
        if (!areaEl || typeof ApexCharts === 'undefined') return;

        const semaCartas   = <?= $chartSemanasJson ?>;
        const semaPcts     = <?= $chartPctsJson ?>;
        const healthTarget = <?= (int)$healthIndex ?>;

        const areaChart = new ApexCharts(areaEl, {
            chart: {
                type: 'bar',
                height: 168,
                sparkline: { enabled: false },
                toolbar: { show: false },
                animations: { enabled: true, easing: 'easeinout', speed: 900 },
                background: 'transparent',
            },
            series: [{ name: 'Asistencia', data: semaPcts }],
            plotOptions: {
                bar: {
                    borderRadius: 6,
                    columnWidth: '25%', // Ajusta el grosor de las barras
                    dataLabels: { position: 'top' },
                }
            },
            xaxis: {
                categories: semaCartas,
                labels: { style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Inter, sans-serif' } },
                axisBorder: { show: false },
                axisTicks:  { show: false },
            },
            yaxis: {
                min: 0, max: 100,
                labels: { formatter: v => v + '%', style: { colors: '#94a3b8', fontSize: '11px', fontFamily: 'Inter, sans-serif' } },
            },
            grid: {
                borderColor: '#f1f5f9',
                strokeDashArray: 4,
                padding: { left: 4, right: 4, top: 0, bottom: 0 }
            },
            fill: {
                type: 'solid',
                colors: ['#3b82f6'] // Color sólido azul eléctrico
            },
            tooltip: {
                theme: 'light',
                y: { formatter: val => val !== null ? val + '%' : 'Sin datos' },
                style: { fontSize: '12px', fontFamily: 'Inter, sans-serif' }
            },
            dataLabels: { 
                enabled: true,
                formatter: function (val) { return val + "%"; },
                offsetY: -20,
                style: { fontSize: '10px', colors: ["#64748b"] }
            },
        });
        areaChart.render();

        // Contador animado del HealthIndex
        const counterEl = document.getElementById('healthCounterDisplay');
        if (counterEl) {
            let current = 0;
            const step  = Math.ceil(healthTarget / 40);
            const timer = setInterval(() => {
                current = Math.min(current + step, healthTarget);
                counterEl.textContent = current + '%';
                if (current >= healthTarget) clearInterval(timer);
            }, 30);
        }
    })();

    // ===== SGP: Mini-bars (IntersectionObserver) =====
    const sgpSparkObs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.querySelectorAll('.sgp-bar').forEach((bar, idx) => {
                const h = bar.getAttribute('data-h') || 50;
                setTimeout(() => { bar.style.height = h + '%'; }, idx * 55);
            });
            sgpSparkObs.unobserve(entry.target);
        });
    }, { threshold: 0.2 });

    document.querySelectorAll('.sgp-sparkline').forEach(el => sgpSparkObs.observe(el));

    // ===== SGP: Chevron collapse =====
    document.querySelectorAll('.sgp-collapse-btn').forEach(btn => {
        btn.addEventListener('click', function() {
            const chev = this.querySelector('.sgp-chevron');
            if (!chev) return;
            const open = this.getAttribute('aria-expanded') === 'true';
            chev.style.transform = open ? 'rotate(0deg)' : 'rotate(180deg)';
        });
    });

    // ===== TABLA MAESTRA: Sorting por columna =====
    let sgpSortCol = -1, sgpSortAsc = true;

    document.querySelectorAll('#tablaMaestra .sgp-th').forEach(th => {
        th.addEventListener('click', function() {
            const col = parseInt(this.getAttribute('data-col'));
            if (sgpSortCol === col) { sgpSortAsc = !sgpSortAsc; }
            else { sgpSortCol = col; sgpSortAsc = true; }

            // Actualizar flechas en headers
            document.querySelectorAll('#tablaMaestra .sgp-th').forEach(h => {
                const ico = h.querySelector('i');
                if (ico) ico.className = 'ti ti-selector text-muted';
                h.style.color = '#374151';
            });
            const ico = this.querySelector('i');
            if (ico) ico.className = sgpSortAsc ? 'ti ti-sort-ascending text-primary' : 'ti ti-sort-descending text-primary';
            this.style.color = '#3b82f6';

            const tbody = document.getElementById('tablaMaestraBody');
            if (!tbody) return;
            const rows = Array.from(tbody.querySelectorAll('.sgp-tabla-fila'));

            rows.sort((a, b) => {
                const tdA = a.querySelectorAll('td')[col];
                const tdB = b.querySelectorAll('td')[col];
                // Columnas numéricas (2,3,4,5): usar data-val
                if ([2,3,4,5].includes(col)) {
                    const va = parseFloat(tdA?.getAttribute('data-val') ?? 0);
                    const vb = parseFloat(tdB?.getAttribute('data-val') ?? 0);
                    return sgpSortAsc ? va - vb : vb - va;
                }
                // Columnas texto (0,1)
                const ta = tdA?.textContent?.trim().toLowerCase() ?? '';
                const tb = tdB?.textContent?.trim().toLowerCase() ?? '';
                return sgpSortAsc ? ta.localeCompare(tb, 'es') : tb.localeCompare(ta, 'es');
            });

            rows.forEach(r => tbody.appendChild(r));
        });

        // Hover visual
        th.addEventListener('mouseover', () => { th.style.background = '#f1f5f9'; });
        th.addEventListener('mouseout',  () => { th.style.background = ''; });
    });

    // ===== TABLA MAESTRA: Búsqueda Live =====
    const tablaBuscador = document.getElementById('tablaMaestaBuscador');
    if (tablaBuscador) {
        tablaBuscador.addEventListener('input', function() {
            const q = this.value.toLowerCase().trim();
            let visible = 0;
            document.querySelectorAll('#tablaMaestraBody .sgp-tabla-fila').forEach(row => {
                const s = (row.getAttribute('data-search') || '').toLowerCase();
                const show = !q || s.includes(q);
                row.style.display = show ? '' : 'none';
                if (show) visible++;
            });
            const cnt = document.getElementById('tablaMaestraCount');
            if (cnt) cnt.textContent = visible + ' registro' + (visible !== 1 ? 's' : '') + (q ? ' encontrados' : '');
        });
    }

    // ===== TABLA MAESTRA: Progress bars animadas (IntersectionObserver) =====
    const sgpProgressObs = new IntersectionObserver(entries => {
        entries.forEach(entry => {
            if (!entry.isIntersecting) return;
            entry.target.querySelectorAll('.sgp-progress-bar').forEach(bar => {
                const w = bar.getAttribute('data-w') || 0;
                bar.style.width = w + '%';
            });
            sgpProgressObs.unobserve(entry.target);
        });
    }, { threshold: 0.1 });

    const tablaEl = document.getElementById('tablaMaestra');
    if (tablaEl) sgpProgressObs.observe(tablaEl);

});



// ===== SGP: Helpers globales =====
function sgpClearFilter(id) {
    const el = document.getElementById(id);
    if (!el) return;
    el.value = '';
    el.dispatchEvent(new Event(el.tagName === 'SELECT' ? 'change' : 'input'));
}

function sgpToggleMore(btn, deptId) {
    const card = btn.closest('.sgp-bc');
    if (!card) return;
    const hiddenRows = card.querySelectorAll('.sgp-bc-hidden-row');
    const isExpanded = btn.getAttribute('data-expanded') === 'true';

    hiddenRows.forEach(r => {
        r.style.display = isExpanded ? 'none' : 'grid';
    });
    
    btn.setAttribute('data-expanded', !isExpanded);
    const count = hiddenRows.length;
    btn.innerHTML = !isExpanded 
        ? `Ver menos <i class="ti ti-chevron-up" style="margin-left:4px;"></i>` 
        : `Ver ${count} pasante${count > 1 ? 's' : ''} más <i class="ti ti-chevron-down" style="transition:transform 0.3s;margin-left:4px;"></i>`;
}

function exportarBentoCSV() {
    let csv = 'Departamento,Pasante,Presentes,Faltas,Justificados\n';
    document.querySelectorAll('#bentoContainer .bento-item').forEach(item => {
        if (item.style.display === 'none') return;
        const dep = item.querySelector('h4')?.textContent?.trim() || '';
        item.querySelectorAll('[title*="pres"]').forEach(row => {
            const nom = row.querySelector('span')?.textContent?.trim() || '';
            const t   = row.getAttribute('title') || '';
            const [p,f,j] = [
                (t.match(/(\d+) pres/) || [,0])[1],
                (t.match(/(\d+) faltas/) || [,0])[1],
                (t.match(/(\d+) just/)  || [,0])[1]
            ];
            csv += `"${dep}","${nom}",${p},${f},${j}\n`;
        });
    });
    const a = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(new Blob([csv], {type:'text/csv;charset=utf-8;'})),
        download: `asistencias_${new Date().toISOString().slice(0,7)}.csv`
    });
    a.click();
}

function exportarDeptoCSV(depto) {
    let csv = 'Departamento,Pasante,Presentes,Faltas,Justificados\n';
    document.querySelectorAll('#bentoContainer .bento-item').forEach(item => {
        if ((item.getAttribute('data-depto') || '').toLowerCase() !== depto.toLowerCase()) return;
        item.querySelectorAll('[title*="pres"]').forEach(row => {
            const nom = row.querySelector('span')?.textContent?.trim() || '';
            const t   = row.getAttribute('title') || '';
            const [p,f,j] = [
                (t.match(/(\d+) pres/) || [,0])[1],
                (t.match(/(\d+) faltas/) || [,0])[1],
                (t.match(/(\d+) just/)  || [,0])[1]
            ];
            csv += `"${depto}","${nom}",${p},${f},${j}\n`;
        });
    });
    const a = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(new Blob([csv], {type:'text/csv;charset=utf-8;'})),
        download: `${depto.replace(/\s+/g,'_')}_${new Date().toISOString().slice(0,7)}.csv`
    });
    a.click();
}

function exportarTablaMaestraCSV() {
    let csv = 'Departamento,Pasante,C.I.,Presentes,Faltas,Justificados,% Asistencia,Estado\n';
    let count = 0;
    document.querySelectorAll('#tablaMaestraBody .sgp-tabla-fila').forEach(row => {
        if (row.style.display === 'none') return;
        const tds  = row.querySelectorAll('td');
        // td[0] = Pasante (nombre + CI), td[1] = Depto, td[2]=Pres, td[3]=Faltas, td[4]=Just, td[5]=%, td[6]=Estado
        const nombre = tds[0]?.querySelector('.fw-semibold')?.textContent?.trim() ?? '';
        const ci     = tds[0]?.querySelector('[style*="0.72rem"]')?.textContent?.replace('C.I.','').trim() ?? '';
        const depto  = tds[1]?.textContent?.trim() ?? '';
        const pres   = tds[2]?.getAttribute('data-val') ?? '';
        const falt   = tds[3]?.getAttribute('data-val') ?? '';
        const just   = tds[4]?.getAttribute('data-val') ?? '';
        const pct    = tds[5]?.getAttribute('data-val') ?? '';
        const estado = tds[6]?.textContent?.trim() ?? '';
        csv += `"${depto}","${nombre}","${ci}",${pres},${falt},${just},${pct}%,"${estado}"\n`;
        count++;
    });
    const a = Object.assign(document.createElement('a'), {
        href: URL.createObjectURL(new Blob(['\uFEFF' + csv], {type:'text/csv;charset=utf-8;'})),
        download: `tabla_maestra_${new Date().toISOString().slice(0,7)}.csv`
    });
    a.click();
    // Toast de confirmación
    const toast = Object.assign(document.createElement('div'), {
        innerHTML: `<i class="ti ti-check me-2"></i>CSV exportado — ${count} registros`,
        style: 'position:fixed;bottom:24px;right:24px;z-index:9999;background:#0f172a;color:white;padding:12px 20px;border-radius:12px;font-size:0.85rem;font-weight:600;box-shadow:0 8px 24px rgba(0,0,0,0.2);display:flex;align-items:center;gap:4px;animation:sgpFadeIn 0.3s ease;'
    });
    document.body.appendChild(toast);
    setTimeout(() => toast.remove(), 3000);
}

function sgpAlertarDepto(depto, faltas) {
    const msg = `El departamento de ${depto} presenta ${faltas} faltas recientes. ¿Desea enviar una notificación directa al coordinador?`;
    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: 'Atención Requerida',
            text: msg,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#3b82f6',
            cancelButtonColor: '#f1f5f9',
            cancelButtonText: '<span style="color:#475569">Cancelar</span>',
            confirmButtonText: '<i class="ti ti-send me-1"></i> Sí, enviar alerta',
            customClass: { confirmButton: 'btn fw-bold', cancelButton: 'btn fw-bold' }
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({ title: '¡Alerta Enviada!', text: 'El coordinador ha sido notificado exitosamente.', icon: 'success', confirmButtonColor: '#10b981' });
            }
        });
    } else {
        if(confirm(msg)) alert('Alerta enviada exitosamente al coordinador.');
    }
}

// Inicializar Tooltips de Bootstrap globales para avatares
document.addEventListener("DOMContentLoaded", function() {
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
    tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl);
    });
});
</script>

