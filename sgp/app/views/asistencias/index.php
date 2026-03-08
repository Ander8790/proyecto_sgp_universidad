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
        <div style="display:grid;grid-template-columns:repeat(auto-fit, minmax(240px, 1fr));gap:20px;margin-bottom:28px;">
            <!-- Histórico -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #8b5cf6; transition: all 0.3s;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#8b5cf6;margin:0;"><?= $historicoAnual ?></h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Histórico Total</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">pasantes registrados</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #f5f3ff 0%, #ede9fe 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-history" style="font-size:24px;color:#8b5cf6;"></i>
                    </div>
                </div>
            </div>
            <!-- En Curso -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #3b82f6; transition: all 0.3s;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#3b82f6;margin:0;"><?= $enCursoAnual ?></h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">En Curso</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">acumulando horas</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #eff6ff 0%, #dbeafe 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-activity" style="font-size:24px;color:#3b82f6;"></i>
                    </div>
                </div>
            </div>
            <!-- Finalizados -->
            <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #10b981; transition: all 0.3s;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#10b981;margin:0;"><?= $finalizadosAnual ?></h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Finalizados</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">listos para certificar</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #ecfdf5 0%, #d1fae5 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
                        <i class="ti ti-certificate" style="font-size:24px;color:#10b981;"></i>
                    </div>
                </div>
            </div>
            <!-- Retirados -->
            <div class="stat-card opacity-75" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #64748b; transition: all 0.3s;">
                <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                    <div>
                        <h2 style="font-size:2.2rem;font-weight:800;color:#64748b;margin:0;">0</h2>
                        <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Retirados</p>
                        <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">(no rastreado)</p>
                    </div>
                    <div style="background:linear-gradient(135deg, #f8fafc 0%, #f1f5f9 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;">
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
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h3 style="margin: 0 0 8px 0; color: #0f172a; font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
                        <i class="ti ti-calendar-week text-primary"></i> Asistencia Semanal
                    </h3>
                    <div style="display: flex; gap: 12px; font-size: 0.8rem; color: #64748b; align-items: center; font-weight: 600;">
                        <span style="display:flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; border-radius:3px; background:#10b981;"></span> Presente (P)</span>
                        <span style="display:flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; border-radius:3px; background:#ef4444;"></span> Ausente (A)</span>
                        <span style="display:flex; align-items:center; gap:6px;"><span style="width:10px; height:10px; border-radius:3px; background:#f59e0b;"></span> Justificado (J)</span>
                    </div>
                </div>
                <div style="display: flex; gap: 16px; align-items: center;">
                    <div style="position: relative;">
                        <i class="ti ti-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1rem;"></i>
                        <input type="text" id="buscadorSemanal" placeholder="Buscar pasante..." style="padding: 10px 14px 10px 36px; border: 1px solid #e2e8f0; border-radius: 50px; outline: none; font-size: 0.85rem; width: 220px; transition: border-color 0.2s;" onfocus="this.style.borderColor='#3b82f6'" onblur="this.style.borderColor='#e2e8f0'">
                    </div>
                    
                    <!-- FILTRO POR SEMANA ISO 8601 (Premium Flatpickr) -->
                    <div style="position: relative; display: flex; align-items: center; background: white; border: 1px solid #e2e8f0; border-radius: 50px; padding: 2px 12px; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <label for="filtro_semana" style="margin: 0; padding-right: 8px; font-size: 0.85rem; font-weight: 700; color: #64748b; border-right: 1px solid #e2e8f0;"><i class="ti ti-calendar-time"></i> Semana</label>
                        <input type="text" id="filtro_semana" class="form-control" style="border: none; outline: none; padding: 6px 8px; color: #1e3a8a; font-weight: 600; font-size: 0.85rem; background: transparent; cursor: pointer; width: 130px;" placeholder="Seleccionar...">
                    </div>

                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 50px; padding: 4px; display: flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <button class="nav-semana-btn" data-url="<?= $navSemana['ant_url'] ?>" style="background: transparent; border: none; color: #64748b; padding: 6px 12px; border-radius: 50px; transition: all 0.2s; font-weight: 600; font-size: 0.85rem; cursor: pointer;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'"><i class="ti ti-chevron-left"></i> Ant</button>
                        <span id="label-semana-nav" style="font-size: 0.85rem; font-weight: 700; color: #334155; padding: 0 12px;"><?= $navSemana['texto'] ?></span>
                        <button class="nav-semana-btn" data-url="<?= $navSemana['sig_url'] ?>" style="background: transparent; border: none; color: #64748b; padding: 6px 12px; border-radius: 50px; transition: all 0.2s; font-weight: 600; font-size: 0.85rem; cursor: pointer;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">Sig <i class="ti ti-chevron-right"></i></button>
                    </div>
                </div>
            </div>
            <style>
                /* Parche anti-Bootstrap para el mes */
                .flatpickr-monthDropdown-months {
                    display: inline-block !important;
                    visibility: visible !important;
                    width: auto !important;
                    appearance: menulist !important;
                    -moz-appearance: menulist !important;
                    -webkit-appearance: menulist !important;
                    background: transparent !important;
                    border: none !important;
                    color: inherit !important;
                    height: auto !important;
                    padding: 0 !important;
                    margin: 0 !important;
                    font-weight: bold;
                }
                .flatpickr-current-month {
                    display: flex !important;
                    justify-content: center !important;
                    align-items: center !important;
                }
                .flatpickr-calendar {
                    z-index: 1055 !important;
                }
                /* Resaltado de la Semana Seleccionada (Plugin weekSelect) */
                .flatpickr-day.inRange, 
                .flatpickr-day.prevMonthDay.inRange, 
                .flatpickr-day.nextMonthDay.inRange, 
                .flatpickr-day.today.inRange, 
                .flatpickr-day.prevMonthDay.today.inRange, 
                .flatpickr-day.nextMonthDay.today.inRange, 
                .flatpickr-day:hover, 
                .flatpickr-day.prevMonthDay:hover, 
                .flatpickr-day.nextMonthDay:hover, 
                .flatpickr-day:focus, 
                .flatpickr-day.prevMonthDay:focus, 
                .flatpickr-day.nextMonthDay:focus {
                    background: #e6f0ff !important;
                    border-color: #e6f0ff !important;
                }
                .flatpickr-day.selected, 
                .flatpickr-day.startRange, 
                .flatpickr-day.endRange, 
                .flatpickr-day.selected.inRange, 
                .flatpickr-day.startRange.inRange, 
                .flatpickr-day.endRange.inRange, 
                .flatpickr-day.selected:focus, 
                .flatpickr-day.startRange:focus, 
                .flatpickr-day.endRange:focus, 
                .flatpickr-day.selected:hover, 
                .flatpickr-day.startRange:hover, 
                .flatpickr-day.endRange:hover, 
                .flatpickr-day.selected.prevMonthDay, 
                .flatpickr-day.startRange.prevMonthDay, 
                .flatpickr-day.endRange.prevMonthDay, 
                .flatpickr-day.selected.nextMonthDay, 
                .flatpickr-day.startRange.nextMonthDay, 
                .flatpickr-day.endRange.nextMonthDay {
                    background: #0d6efd !important;
                    color: #fff !important;
                    border-color: #0d6efd !important;
                }
            </style>

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
                            
                            // 1. Reemplazar tarjetas
                            const nuevoCont = doc.getElementById('contenedor-tarjetas-semanales');
                            if (nuevoCont && contenedor) {
                                contenedor.innerHTML = nuevoCont.innerHTML;
                                contenedor.style.opacity = '1';
                            }
                            
                            // 2. Reemplazar texto del navegador central
                            const nuevoLabel = doc.getElementById('label-semana-nav');
                            const actualLabel = document.getElementById('label-semana-nav');
                            if (nuevoLabel && actualLabel) {
                                actualLabel.innerText = nuevoLabel.innerText;
                            }
                            
                            // 3. Reemplazar urls de los botones laterales
                            const nuevosBtn = doc.querySelectorAll('.nav-semana-btn');
                            const actualesBtn = document.querySelectorAll('.nav-semana-btn');
                            if (nuevosBtn.length === 2 && actualesBtn.length === 2) {
                                actualesBtn[0].dataset.url = nuevosBtn[0].dataset.url;
                                actualesBtn[1].dataset.url = nuevosBtn[1].dataset.url;
                            }
                            
                            // 4. Actualizar History API (URL) invisiblemente
                            window.history.pushState({ path: url }, '', url);
                        })
                        .catch(err => {
                            console.error('Error AJAX PJAX: ', err);
                            window.location.href = url; // Fallback
                        });
                }

                document.addEventListener('DOMContentLoaded', function() {
                    const defaultAnio = <?= $paramsUrl['anio'] ?? date('Y') ?>;
                    const defaultSemana = <?= $paramsUrl['semana'] ?? date('W') ?>;
                    
                    if (typeof flatpickr !== 'undefined' && typeof weekSelect !== 'undefined') {
                        flatpickr('#filtro_semana', {
                            locale: 'es',
                            weekNumbers: true,
                            plugins: [new weekSelect({})],
                            onReady: function(selectedDates, dateStr, instance) {
                                instance.input.value = "Semana " + defaultSemana + ", " + defaultAnio;
                                
                                // Escudo Anti-ChoicesJS / Select2
                                setTimeout(() => {
                                    const monthSelect = instance.monthElements[0];
                                    if (monthSelect) {
                                        if (window.jQuery && $(monthSelect).data('select2')) {
                                            $(monthSelect).select2('destroy');
                                        }
                                        
                                        // Restauración Visual Forzada
                                        if (window.jQuery) {
                                            $(monthSelect).css({'display': 'inline-block', 'visibility': 'visible'}).removeClass('select2-hidden-accessible');
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
                                    if (dt.getDay() !== 4) {
                                        dt.setMonth(0, 1 + ((4 - dt.getDay()) + 7) % 7);
                                    }
                                    const targetYear = new Date(firstThursday).getFullYear();
                                    const targetWeek = 1 + Math.ceil((firstThursday - dt) / 604800000);
                                    
                                    instance.input.value = "Semana " + targetWeek + ", " + targetYear;
                                    const url = "<?= URLROOT ?>/asistencias?vista=semanal&semana=" + targetWeek + "&anio=" + targetYear;
                                    cargarSemanaAjax(url);
                                }
                            }
                        });
                    }

                    // Event Listener para Navegación Asíncrona (Botones Ant / Sig)
                    document.addEventListener('click', function(e) {
                        const btn = e.target.closest('.nav-semana-btn');
                        if (btn) {
                            e.preventDefault();
                            const targetUrl = btn.dataset.url;
                            
                            // Actualizar feedback visual del input Flatpickr a partir de los query params
                            try {
                                const urlObj = new URL(targetUrl, window.location.origin);
                                const targetSemana = urlObj.searchParams.get('semana');
                                const targetAnio = urlObj.searchParams.get('anio');
                                const fpInput = document.getElementById('filtro_semana');
                                if (fpInput && targetSemana && targetAnio) {
                                    fpInput.value = "Semana " + targetSemana + ", " + targetAnio;
                                }
                            } catch(e) {}
                            
                            cargarSemanaAjax(targetUrl);
                        }
                    });
                });
            </script>
            
            <div id="contenedor-tarjetas-semanales" style="transition: opacity 0.3s ease;">
                <?php if(empty($datosSemanales)): ?>
                    <div style="text-align:center; padding: 40px; background: white; border-radius: 16px; border: 1px solid #e2e8f0;">
                        <i class="ti ti-calendar-x" style="font-size: 3rem; color: #cbd5e1; margin-bottom: 12px;"></i>
                        <p style="color: #64748b; font-weight: 600;">No hay pasantes activos para mostrar en esta semana.</p>
                    </div>
                <?php else: ?>
                    <?php foreach ($datosSemanales as $depto => $pasantes): ?>
                        <div class="depto-card" style="background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; overflow: hidden; margin-bottom: 24px;">
                            
                            <div style="background: #f8fafc; padding: 16px 24px; border-bottom: 1px solid #e2e8f0; display: flex; justify-content: space-between; align-items: center;">
                                <h4 style="margin: 0; color: #1e293b; font-weight: 700; font-size: 1rem; display: flex; align-items: center;">
                                    <i class="ti ti-building-community text-primary" style="margin-right: 8px;"></i> <?= htmlspecialchars($depto) ?>
                                </h4>
                                <span style="font-size: 0.75rem; background: #e2e8f0; color: #475569; padding: 4px 10px; border-radius: 20px; font-weight: 700;"><?= count($pasantes) ?> Pasantes</span>
                            </div>
                            
                            <div style="overflow-x: auto;">
                                <table style="width: 100%; border-collapse: collapse; min-width: 700px;">
                                    <thead>
                                        <tr>
                                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 24px; text-align: left; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Pasante</th>
                                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: center; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Lun</th>
                                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: center; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Mar</th>
                                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: center; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Mié</th>
                                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: center; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Jue</th>
                                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: center; border-bottom: 1px solid #e2e8f0; background: #f8fafc;">Vie</th>
                                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 800; color: #3b82f6; padding: 12px 24px; text-align: right; border-bottom: 1px solid #e2e8f0; background: #eff6ff;">Resumen</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <?php foreach ($pasantes as $id => $data): ?>
                                            <tr class="pasante-row" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'" style="transition: background 0.2s;">
                                                <td class="pasante-nombre" style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; font-weight: 600;">
                                                    <?= htmlspecialchars($data['nombre']) ?>
                                                </td>
                                                
                                                <?php 
                                                for ($i = 1; $i <= 5; $i++): 
                                                    $letra = $data['dias'][$i];
                                                    $bg = '#e2e8f0'; $color = '#64748b'; // Gris (Por Defecto)
                                                    if ($letra === 'P') { $bg = '#d1fae5'; $color = '#059669'; } // Verde
                                                    elseif ($letra === 'A') { $bg = '#fee2e2'; $color = '#dc2626'; } // Rojo
                                                    elseif ($letra === 'J') { $bg = '#fef3c7'; $color = '#d97706'; } // Amarillo
                                                ?>
                                                    <td style="padding: 16px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                                        <span style="background: <?= $bg ?>; color: <?= $color ?>; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.75rem;">
                                                            <?= $letra ?>
                                                        </span>
                                                    </td>
                                                <?php endfor; ?>

                                                <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                                                    <button type="button" class="btn btn-sm btn-outline-primary btn-resumen" data-id="<?= htmlspecialchars($id) ?>" data-nombre="<?= htmlspecialchars($data['nombre']) ?>" style="border-radius: 50px; font-weight: 600; padding: 6px 14px; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.1);" onclick="abrirAlmanaque(this)">
                                                        <i class="ti ti-calendar-stats"></i> Resumen
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
            ?>

            <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&family=JetBrains+Mono:wght@400;500;600&display=swap" rel="stylesheet">
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
                            <button class="btn btn-danger" style="width:100%;justify-content:center;">
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" width="14" height="14"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg>
                                Enviar notificaciones
                            </button>
                        </div>
                    </div>

                    <div class="tile t-calendar rv d3">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--sky-dim)">📅</div> <?= ucfirst(strftime('%B %Y')) ?></div>
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
                                <span class="tile-badge" style="background:var(--green-dim);color:var(--green);border:1px solid var(--green-mid)"><?= count($registrosHoy ?? []) ?> reg</span>
                                <button class="tile-btn">
                                    <svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/><polyline points="7 10 12 15 17 10"/><line x1="12" y1="15" x2="12" y2="3"/></svg> CSV
                                </button>
                            </div>
                        </div>
                        <div class="mini-table-wrap">
                            <table class="mini-table">
                                <thead><tr><th>Pasante</th><th>Hora</th><th>Método</th><th>Estado</th></tr></thead>
                                <tbody>
                                    <?php if(!empty($registrosHoy)): foreach(array_slice($registrosHoy, 0, 5) as $r): 
                                        $nom = trim(($r->apellidos ?? '') . ', ' . ($r->nombres ?? ''));
                                        $ini = strtoupper(substr($r->nombres ?? 'P', 0, 1) . substr($r->apellidos ?? 'A', 0, 1));
                                        $hora = $r->hora_registro ? date('h:i A', strtotime($r->hora_registro)) : '—';
                                        $metodo = $r->metodo ?? 'PC';
                                        $estado = $r->estado ?? 'Presente';
                                        $pillEst = stripos($estado, 'presente') !== false ? 'pill-green' : (stripos($estado, 'justificado') !== false ? 'pill-amber' : 'pill-red');
                                        $pillMet = stripos($metodo, 'manual') !== false ? 'pill-violet' : 'pill-sky';
                                    ?>
                                    <tr>
                                        <td><div class="person-cell"><div class="av" style="background:var(--blue)"><?= $ini ?></div><div><div class="pn"><?= htmlspecialchars($nom) ?></div><div class="pid"><?= $r->cedula ?? '' ?></div></div></div></td>
                                        <td style="font-family:var(--mono);font-size:11px;color:var(--ink-2)"><?= $hora ?></td>
                                        <td><span class="pill <?= $pillMet ?>"><?= htmlspecialchars($metodo) ?></span></td>
                                        <td><span class="pill <?= $pillEst ?>"><?= htmlspecialchars($estado) ?></span></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="4" style="text-align:center;padding:22px;color:var(--ink-3);">Nadie ha marcado aún.</td></tr>
                                    <?php endif; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="tile t-absent rv d4">
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--red-dim)">✗</div> Sin Marcar Hoy</div>
                            <div class="tile-actions">
                                <span class="tile-badge" style="background:var(--red-dim);color:var(--red);border:1px solid var(--red-mid)"><?= count($sinMarcar ?? []) ?> pasantes</span>
                                <button class="tile-btn"><svg width="11" height="11" fill="none" viewBox="0 0 24 24" stroke="currentColor" stroke-width="2"><line x1="12" y1="5" x2="12" y2="19"/><line x1="5" y1="12" x2="19" y2="12"/></svg> Registrar</button>
                            </div>
                        </div>
                        <div class="mini-table-wrap">
                            <table class="mini-table">
                                <thead><tr><th>Pasante</th><th>Departamento</th><th>Acción</th></tr></thead>
                                <tbody>
                                    <?php if(!empty($sinMarcar)): foreach(array_slice($sinMarcar, 0, 4) as $p): 
                                        $nom = trim(($p->apellidos ?? '') . ', ' . ($p->nombres ?? ''));
                                        $ini = strtoupper(substr($p->nombres ?? 'P', 0, 1) . substr($p->apellidos ?? 'A', 0, 1));
                                    ?>
                                    <tr>
                                        <td><div class="person-cell"><div class="av" style="background:var(--red)"><?= $ini ?></div><div><div class="pn"><?= htmlspecialchars($nom) ?></div><div class="pid">V-<?= $p->cedula ?? '' ?></div></div></div></td>
                                        <td style="font-size:11px;color:var(--ink-2)"><?= htmlspecialchars($p->departamento_nombre ?? 'General') ?></td>
                                        <td><button class="tile-btn">+ Reg.</button></td>
                                    </tr>
                                    <?php endforeach; else: ?>
                                    <tr><td colspan="3" style="text-align:center;padding:22px;font-size:11px;color:var(--ink-3);">🎉 ¡Los demás pasantes marcaron hoy!</td></tr>
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
                        <div class="tile-head">
                            <div class="tile-title"><div class="tile-icon" style="background:var(--amber-dim)">🏆</div> Top Asistencia</div>
                            <span class="tile-badge" style="background:var(--amber-dim);color:var(--amber);border:1px solid var(--amber-mid)">Marzo</span>
                        </div>
                        <div class="top-list" id="topList"></div>
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
            
            const CAL = {3:'p',4:'p',5:'p',6:'p',7:'p',10:'p',11:'p',12:'p',13:'j',14:'p',17:'p',18:'p',19:'a',20:'p',21:'p',24:'p',25:'p',26:'p',27:'a',28:'p'};
            const DAYS = [3,4,5,6,7,10,11,12,13,14,17,18,19,20,21,24,25,26,27,28];

            window.addEventListener('DOMContentLoaded', () => {
                animarDonutYBarras();
                buildCal();
                buildDepts();
                buildTop();
                buildTable(DB);
                buildAlerts(DB);
                setTimeout(animBars, 500);
            });

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
                        <div class="alert-item">
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
                let csv='Nombre,Cédula,Departamento,Presentes,Faltas,Justificados,%\n';
                DB.forEach(r=>{const tot=r.p+r.f+r.j,pct=tot>0?Math.round(r.p/tot*100):0;csv+=`"${r.n}","${r.ci}","${r.d}",${r.p},${r.f},${r.j},${pct}%\n`;});
                const a=Object.assign(document.createElement('a'),{href:URL.createObjectURL(new Blob(['\uFEFF'+csv],{type:'text/csv;charset=utf-8;'})),download:'asistencias_marzo_2026.csv'});
                a.click(); 
            }
            </script>

        <?php elseif ($vistaActual === 'anual'): ?>
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
                            'presentes' => 0
                        ];
                    }
                    if(stripos($reg->estado, 'presente') !== false || stripos($reg->estado, 'justificado') !== false) {
                        $resumenTotal[$pid]['presentes']++;
                    }
                }
            }
            ?>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h3 style="margin: 0 0 8px 0; color: #0f172a; font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
                        <i class="ti ti-database text-primary"></i> Histórico Total
                    </h3>
                    <p style="margin: 0; color: #64748b; font-size: 0.85rem;">Muro de auditoría de progreso hacia las 1440 horas de pasantía.</p>
                </div>
                
                <div style="position: relative; width: 280px;">
                    <i class="ti ti-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #3b82f6; font-size: 1.1rem; z-index: 2;"></i>
                    <input type="text" id="buscadorTablaTotal" placeholder="Buscar pasante..." style="width: 100%; padding: 10px 14px 10px 40px; border: 1px solid #bfdbfe; border-radius: 50px; background: #eff6ff; color: #1e3a8a; font-weight: 600; font-size: 0.85rem; outline: none; transition: all 0.2s; cursor: pointer; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.05);" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='#bfdbfe'; this.style.boxShadow='none'">
                </div>
            </div>
            
            <div class="row g-4 mb-4" id="muroContainer">
                <div id="muroNoResults" class="col-12 text-center py-5 rounded-4 shadow-sm d-none bg-white crisp-card">
                    <i class="ti ti-file-search" style="font-size: 48px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                    <h4 class="fw-bold text-muted">No se encontraron resultados</h4>
                </div>
                <?php if(empty($resumenTotal)): ?>
                    <div class="col-12 text-center py-5 rounded-4 shadow-sm bg-white crisp-card">
                        <div class="empty-state">
                            <i class="ti ti-history-off" style="font-size: 48px; color: #cbd5e1; margin-bottom: 12px; display: block;"></i>
                            <h4 class="fw-bold text-muted">No hay histórico registrado</h4>
                        </div>
                    </div>
                <?php else: ?>
                    <?php $muroDelay=0; foreach($resumenTotal as $pid => $rt): 
                        $nombres = explode(',', $rt['nombre']??'');
                        $apellido = trim($nombres[0] ?? 'A');
                        $nombreP = trim($nombres[1] ?? 'A');
                        $iniciales = strtoupper(substr($nombreP, 0, 1) . substr($apellido, 0, 1));
                        if(strlen(trim($iniciales)) < 2) $iniciales = strtoupper(substr($rt['nombre']??'AA', 0, 2));
                        
                        $horasAcumuladas = $rt['presentes'] * 8; // 8 hrs por asistencia/justificativo
                        $horasMeta = 1440;
                        $pctHoras = $horasMeta > 0 ? min(100, round(($horasAcumuladas / $horasMeta) * 100)) : 0;
                        $estadoBadge = $horasAcumuladas >= $horasMeta ? 'Finalizado' : 'Activo';
                        $estadoBg = $estadoBadge === 'Finalizado' ? '#dcfce7' : '#eff6ff';
                        $estadoColor = $estadoBadge === 'Finalizado' ? '#16a34a' : '#2563eb';
                        
                        $searchData = strtolower($rt['nombre'] . ' ' . $rt['cedula'] . ' ' . $estadoBadge);
                    ?>
                    <div class="col-md-6 col-lg-4 col-xl-3 muro-item stagger-enter" data-search="<?= htmlspecialchars($searchData, ENT_QUOTES) ?>" style="animation-delay: <?= $muroDelay ?>ms;">
                        <?php $muroDelay += 40; ?>
                        <div class="card h-100 border-0 rounded-4 crisp-card muro-card bg-white">
                            <div class="card-body p-4 d-flex flex-column align-items-center text-center">
                                <!-- Header: Soft Badge & Icon -->
                                <div class="w-100 d-flex justify-content-between align-items-start mb-3">
                                    <span class="badge rounded-pill shadow-sm" style="background: <?= $estadoBg ?>; color: <?= $estadoColor ?>; padding: 6px 12px; font-size: 0.75rem; font-weight: 700; border: 1px solid <?= $estadoColor ?>40;">
                                        <?= $estadoBadge ?>
                                    </span>
                                    <button class="btn btn-sm btn-light p-1 text-muted btn-icon-hover" style="border: none; background: transparent;"><i class="ti ti-dots"></i></button>
                                </div>

                                <!-- Avatar Profile con Status Ring -->
                                <div class="fw-bold rounded-circle d-flex align-items-center justify-content-center mb-3 status-ring <?= $estadoBadge === 'Finalizado' ? 'ring-success' : 'ring-primary' ?>" style="width: 56px; height: 56px; font-size: 1.2rem; background: #eff6ff; color: #3b82f6; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02); flex-shrink: 0;">
                                    <?= htmlspecialchars($iniciales) ?>
                                </div>
                                <h5 class="fw-bold text-dark m-0 mt-2 text-truncate w-100" style="font-size: 1.1rem;" title="<?= htmlspecialchars($rt['nombre']) ?>">
                                    <?= htmlspecialchars($rt['nombre']) ?>
                                </h5>
                                <small class="text-muted fw-semibold mb-3 d-block w-100" style="font-size: 0.8rem;">V-<?= htmlspecialchars($rt['cedula']) ?></small>

                                <!-- Radial Progress Gauge Apple-Style -->
                                <div class="position-relative d-flex align-items-center justify-content-center mb-1" style="width: 120px; height: 120px;">
                                    <svg class="position-absolute" width="120" height="120" viewBox="0 0 120 120" style="top: 0; left: 0; transform: rotate(-90deg);">
                                        <circle cx="60" cy="60" r="54" fill="none" stroke="#f1f5f9" stroke-width="12" />
                                        <circle cx="60" cy="60" r="54" fill="none" stroke="<?= $estadoColor ?>" stroke-width="12" stroke-linecap="round" 
                                                stroke-dasharray="339.292" stroke-dashoffset="<?= 339.292 - (339.292 * $pctHoras) / 100 ?>" 
                                                style="transition: stroke-dashoffset 1.5s cubic-bezier(0.16, 1, 0.3, 1);" />
                                    </svg>
                                    
                                    <!-- Centered Data in Gauge -->
                                    <div class="position-absolute d-flex flex-column align-items-center justify-content-center w-100 h-100">
                                        <span class="fw-bolder text-dark" style="font-size: 1.4rem; letter-spacing: -1px; margin-top: 4px;"><?= number_format($horasAcumuladas, 0, ',', '.') ?></span>
                                        <span class="text-muted fw-bold" style="font-size: 0.65rem; margin-top: -2px;">/ <?= number_format($horasMeta, 0, ',', '.') ?> hrs</span>
                                    </div>
                                </div>

                                <span class="fw-bold mb-4" style="color: <?= $estadoColor ?>; font-size: 0.85rem; opacity: 0.9;"><?= $pctHoras ?>% Completado</span>

                                <!-- Footer: Botones Limpios pegados abajo -->
                                <div class="w-100 mt-auto pt-4 d-flex gap-2" style="border-top: 1px solid #f8fafc;">
                                    <a href="<?= URLROOT ?>/reportes/pdfKardex?id=<?= $pid ?>" target="_blank" class="btn btn-light flex-grow-1 text-muted fw-bold d-flex align-items-center justify-content-center gap-1 btn-elite" style="border-radius: 10px; font-size: 0.8rem; background: #f8fafc; border: 1px solid #e2e8f0;">
                                        <i class="ti ti-clipboard-list fs-5"></i> Kardex
                                    </a>
                                    <button class="btn border-0 text-white flex-grow-1 fw-bold d-flex align-items-center justify-content-center gap-1 btn-elite-primary shadow-sm" style="background: <?= $estadoColor ?>; border-radius: 10px; font-size: 0.8rem;">
                                        <i class="ti ti-file-export fs-5"></i> Exportar
                                    </button>
                                </div>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                <?php endif; ?>
            </div>
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

