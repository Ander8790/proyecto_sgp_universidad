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
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:28px;">

        <!-- Total Activos -->
        <div class="stat-card" style="background:white;border-radius:16px;padding:24px;box-shadow:0 2px 12px rgba(0,0,0,0.04);border-left:4px solid #3b82f6; transition: all 0.3s; cursor: default;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 24px rgba(0,0,0,0.08)'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.04)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div>
                    <h2 style="font-size:2.2rem;font-weight:800;color:#0f172a;margin:0;" id="stat-total"><?= $totalActivos ?></h2>
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
                    <h2 style="font-size:2.2rem;font-weight:800;color:#0f172a;margin:0;" id="stat-presentes"><?= $presentes ?></h2>
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
                    <h2 style="font-size:2.2rem;font-weight:800;color:#0f172a;margin:0;" id="stat-justificados"><?= $justificados ?></h2>
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
                    <h2 style="font-size:2.2rem;font-weight:800;color:#0f172a;margin:0;" id="stat-ausentes"><?= $ausentes ?></h2>
                    <p style="color:#64748b;font-size:0.85rem;margin:4px 0 0;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;">Sin Marcar</p>
                    <p style="color:#94a3b8;font-size:0.75rem;margin:4px 0 0;">sin justificación</p>
                </div>
                <div style="background:linear-gradient(135deg, #fef2f2 0%, #fee2e2 100%);border-radius:12px;width:48px;height:48px;display:flex;align-items:center;justify-content:center;box-shadow:inset 0 2px 4px rgba(255,255,255,0.5);">
                    <i class="ti ti-user-x" style="font-size:24px;color:#ef4444;"></i>
                </div>
            </div>
        </div>

    </div>

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
                            <?= $r->hora_registro ? substr($r->hora_registro, 0, 5) : '—' ?>
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
                                'hora'    => $r->hora_registro ? substr($r->hora_registro,0,5) : '—',
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
                    
                    <div style="background: white; border: 1px solid #e2e8f0; border-radius: 50px; padding: 4px; display: flex; align-items: center; box-shadow: 0 2px 4px rgba(0,0,0,0.02);">
                        <a href="<?= $navSemana['ant_url'] ?>" style="text-decoration:none; background: transparent; border: none; color: #64748b; padding: 6px 12px; border-radius: 50px; transition: all 0.2s; font-weight: 600; font-size: 0.85rem;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'"><i class="ti ti-chevron-left"></i> Ant</a>
                        <span style="font-size: 0.85rem; font-weight: 700; color: #334155; padding: 0 12px;"><?= $navSemana['texto'] ?></span>
                        <a href="<?= $navSemana['sig_url'] ?>" style="text-decoration:none; background: transparent; border: none; color: #64748b; padding: 6px 12px; border-radius: 50px; transition: all 0.2s; font-weight: 600; font-size: 0.85rem;" onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='transparent'">Sig <i class="ti ti-chevron-right"></i></a>
                    </div>
                </div>
            </div>
            
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

                                            <?php 
                                                $totalP = $data['totales']['P'];
                                                $porcentaje = ($totalP / 5) * 100;
                                                $colorBarra = $porcentaje >= 80 ? '#10b981' : ($porcentaje >= 40 ? '#f59e0b' : '#ef4444');
                                            ?>
                                            <td style="padding: 16px 24px; border-bottom: 1px solid #f1f5f9; text-align: right;">
                                                <div style="font-weight: 800; color: #0f172a; font-size: 0.95rem;"><?= $totalP ?> / 5 <span style="font-size: 0.7rem; font-weight: 600; color: #64748b;">días</span></div>
                                                <div style="width: 100%; background: #e2e8f0; height: 6px; border-radius: 10px; margin-top: 6px; overflow: hidden;">
                                                    <div style="width: <?= $porcentaje ?>%; background: <?= $colorBarra ?>; height: 100%; border-radius: 10px; transition: width 0.5s ease;"></div>
                                                </div>
                                            </td>
                                        </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                <?php endforeach; ?>
            <?php endif; ?>

        <?php elseif ($vistaActual === 'mensual' || $vistaActual === 'anual'): ?>
            <div style="display: flex; justify-content: space-between; align-items: flex-end; margin-bottom: 24px; flex-wrap: wrap; gap: 16px;">
                <div>
                    <h3 style="margin: 0 0 8px 0; color: #0f172a; font-weight: 700; font-size: 1.2rem; display: flex; align-items: center; gap: 8px;">
                        <i class="<?= $vistaActual === 'mensual' ? 'ti ti-calendar-month' : 'ti ti-database' ?> text-primary"></i> 
                        <?= $vistaActual === 'mensual' ? 'Reporte Mensual' : 'Histórico Total' ?>
                    </h3>
                    <p style="margin: 0; color: #64748b; font-size: 0.85rem;">Explora, filtra y exporta el historial de asistencias.</p>
                </div>
                
                <div style="position: relative; width: 280px;">
                    <i class="ti ti-calendar-event" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #3b82f6; font-size: 1.1rem; z-index: 2;"></i>
                    <input type="text" id="filtroFechaRango" placeholder="Filtrar por rango de fechas..." style="width: 100%; padding: 10px 14px 10px 40px; border: 1px solid #bfdbfe; border-radius: 50px; background: #eff6ff; color: #1e3a8a; font-weight: 600; font-size: 0.85rem; outline: none; transition: all 0.2s; cursor: pointer; box-shadow: 0 2px 4px rgba(59, 130, 246, 0.05);" onfocus="this.style.borderColor='#3b82f6'; this.style.boxShadow='0 0 0 3px rgba(59,130,246,0.1)'" onblur="this.style.borderColor='#bfdbfe'; this.style.boxShadow='none'">
                    <button id="btnLimpiarFecha" style="display: none; position: absolute; right: 14px; top: 50%; transform: translateY(-50%); background: transparent; border: none; color: #94a3b8; cursor: pointer; z-index: 2;"><i class="ti ti-x"></i></button>
                </div>
            </div>
            
            <div style="background: white; border-radius: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.04); border: 1px solid #f1f5f9; padding: 24px; overflow-x: auto;">
                <table id="tablaHistorial" class="display" style="width: 100%; border-collapse: collapse;">
                    <thead>
                        <tr>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: left; border-bottom: 2px solid #f1f5f9;">Fecha</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: left; border-bottom: 2px solid #f1f5f9;">Pasante</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: left; border-bottom: 2px solid #f1f5f9;">Departamento</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: center; border-bottom: 2px solid #f1f5f9;">Estado</th>
                            <th style="text-transform: uppercase; font-size: 0.75rem; font-weight: 700; color: #64748b; padding: 12px 16px; text-align: center; border-bottom: 2px solid #f1f5f9;">Método</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php if(!empty($registrosLista)): ?>
                            <?php foreach($registrosLista as $reg): ?>
                                <tr onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'" style="transition: background 0.2s;">
                                    <td style="padding: 16px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem; font-weight: 600;">
                                        <?= date('d/m/Y', strtotime($reg->fecha)) ?> <br>
                                        <span style="font-size: 0.75rem; color: #94a3b8; font-weight: 400;"><?= date('h:i A', strtotime($reg->hora_registro)) ?></span>
                                    </td>
                                    <td style="padding: 16px; border-bottom: 1px solid #f1f5f9; color: #334155; font-size: 0.9rem;">
                                        <?= htmlspecialchars(trim(($reg->apellidos ?? '') . ', ' . ($reg->nombres ?? ''))) ?>
                                    </td>
                                    <td style="padding: 16px; border-bottom: 1px solid #f1f5f9; color: #64748b; font-size: 0.85rem;">
                                        <?= htmlspecialchars($reg->departamento_nombre ?? 'Sin Asignar') ?>
                                    </td>
                                    <td style="padding: 16px; border-bottom: 1px solid #f1f5f9; text-align: center;">
                                        <?php 
                                            $estado = strtolower($reg->estado);
                                            if (strpos($estado, 'presente') !== false) {
                                                echo '<span style="background: #d1fae5; color: #059669; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.75rem;"><i class="ti ti-check"></i> Presente</span>';
                                            } elseif (strpos($estado, 'ausente') !== false) {
                                                echo '<span style="background: #fee2e2; color: #dc2626; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.75rem;"><i class="ti ti-x"></i> Ausente</span>';
                                            } else {
                                                echo '<span style="background: #fef3c7; color: #d97706; padding: 6px 12px; border-radius: 8px; font-weight: 700; font-size: 0.75rem;"><i class="ti ti-file-certificate"></i> Justificado</span>';
                                            }
                                        ?>
                                    </td>
                                    <td style="padding: 16px; border-bottom: 1px solid #f1f5f9; text-align: center; color: #64748b; font-size: 0.85rem;">
                                        <?= htmlspecialchars($reg->metodo) ?>
                                    </td>
                                </tr>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </tbody>
                </table>
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
                <a href="#" id="btnIrPerfil" target="_blank" class="aud-btn aud-btn-ghost" style="display:none; flex: 1; justify-content: center;">
                    <i class="ti ti-user-circle"></i> Ver Expediente
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
], $pasantesActivos)) ?>;

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
        $hora   = $r->hora_registro ? substr($r->hora_registro, 0, 5) : '—';
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
<style>
/* Limpiar estilos feos por defecto */
table.dataTable.no-footer { border-bottom: none !important; }
table.dataTable thead th, table.dataTable thead td { border-bottom: 2px solid #f1f5f9 !important; }

/* Buscador DataTables */
.dataTables_wrapper .dataTables_filter input {
    border: 1px solid #e2e8f0; border-radius: 50px; padding: 8px 16px; outline: none; transition: border-color 0.2s; margin-left: 8px;
}
.dataTables_wrapper .dataTables_filter input:focus { border-color: #3b82f6; }

/* Selector de cantidad (Select) */
.dataTables_wrapper .dataTables_length select {
    border: 1px solid #e2e8f0; border-radius: 8px; padding: 4px 8px; outline: none;
}

/* Paginación Premium */
.dataTables_wrapper .dataTables_paginate .paginate_button {
    border-radius: 50px !important; border: none !important; color: #64748b !important; font-weight: 600; padding: 6px 14px !important; margin: 0 4px;
}
.dataTables_wrapper .dataTables_paginate .paginate_button.current,
.dataTables_wrapper .dataTables_paginate .paginate_button.current:hover {
    background: #eff6ff !important; color: #3b82f6 !important; box-shadow: none !important; border: none !important;
}
.dataTables_wrapper .dataTables_paginate .paginate_button:hover {
    background: #f1f5f9 !important; color: #0f172a !important; border: none !important;
}
.dataTables_info { color: #64748b !important; font-size: 0.85rem; font-weight: 600; }
</style>

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
                const btnPerfil = document.getElementById('btnIrPerfil');
                btnPerfil.href = '<?= URLROOT ?>/pasantes/ver/' + perfil.pasante_id; 
                btnPerfil.style.display = 'flex';
                
                const btnPdf = document.getElementById('btnExportarPDF');
                btnPdf.onclick = () => window.open('<?= URLROOT ?>/informes/asistenciaIndividual/' + perfil.pasante_id, '_blank');
                btnPdf.style.display = 'flex';

                // B. Renderizar Timeline con Filtros Iniciales
                renderizarTimeline(currentHistorial);

                // C. Renderizar el Gráfico Radial Compacto
                renderizarGraficoTiempo(perfil.fecha_inicio, perfil.fecha_fin);
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
                const hora = (reg.hora_registro || '—').substring(0, 5);
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
    // 4. MOTOR DEL GRÁFICO RADIAL (ApexCharts)
    // ==========================================
    function renderizarGraficoTiempo(fechaInicioStr, fechaFinStr) {
        if (chartTiempo) { chartTiempo.destroy(); } 

        const container = document.querySelector("#graficoRadialTiempo");
        if (!fechaInicioStr || !fechaFinStr) {
            container.innerHTML = '<div style="height:100%; display:flex; align-items:center; color:#94a3b8;"><i class="ti ti-calendar-off"></i></div>';
            return;
        }

        const fInicio = new Date(fechaInicioStr + 'T00:00:00');
        const fFin = new Date(fechaFinStr + 'T23:59:59');
        const fHoy = new Date();

        const totalMilisegundos = fFin - fInicio;
        const pasadosMilisegundos = fHoy - fInicio;

        let porcentaje = 0;
        let diasFaltantes = 0;

        if (pasadosMilisegundos >= totalMilisegundos) {
            porcentaje = 100;
        } else if (pasadosMilisegundos <= 0) {
            porcentaje = 0;
        } else {
            porcentaje = (pasadosMilisegundos / totalMilisegundos) * 100;
        }

        const options = {
            series: [Math.round(porcentaje)],
            chart: { height: 120, type: 'radialBar', sparkline: { enabled: true } },
            plotOptions: {
                radialBar: {
                    hollow: { size: '55%' },
                    track: { background: '#f1f5f9' },
                    dataLabels: {
                        name: { show: false },
                        value: { show: true, fontSize: '0.9rem', fontWeight: 800, color: '#1e293b', offsetY: 6 }
                    }
                }
            },
            colors: ['#3b82f6'],
            stroke: { lineCap: 'round' }
        };

        chartTiempo = new ApexCharts(container, options);
        chartTiempo.render();
    }
});
</script>
