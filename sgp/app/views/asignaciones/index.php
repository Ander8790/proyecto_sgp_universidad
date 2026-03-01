<?php
/**
 * Vista: Gestión de Asignaciones — Admin
 * Módulo de asignación de pasantes a tutores y departamentos
 */

$asignaciones  = $data['asignaciones']  ?? [];
$departamentos = $data['departamentos'] ?? [];
$tutores       = $data['tutores']       ?? [];
$total         = $data['total']         ?? 0;
$activos       = $data['activos']       ?? 0;
$pendientes    = $data['pendientes']    ?? 0;
$sinAsignar    = $data['sinAsignar']    ?? 0;
$finalizados   = $data['finalizados']   ?? 0;

$estadoConfig = [
    'Sin Asignar' => ['bg' => '#fef9c3', 'color' => '#ca8a04', 'icon' => 'ti-clock'],
    'Pendiente'   => ['bg' => '#fed7aa', 'color' => '#ea580c', 'icon' => 'ti-hourglass'],
    'Activo'      => ['bg' => '#dcfce7', 'color' => '#16a34a', 'icon' => 'ti-check'],
    'Finalizado'  => ['bg' => '#ede9fe', 'color' => '#7c3aed', 'icon' => 'ti-award'],
    'Retirado'    => ['bg' => '#fee2e2', 'color' => '#dc2626', 'icon' => 'ti-user-off'],
    'cancelado'   => ['bg' => '#fee2e2', 'color' => '#dc2626', 'icon' => 'ti-x'],
    'activo'      => ['bg' => '#dcfce7', 'color' => '#16a34a', 'icon' => 'ti-check'],
    'finalizado'  => ['bg' => '#ede9fe', 'color' => '#7c3aed', 'icon' => 'ti-award'],
];
?>

<style>
/* ===== MODAL STYLES (Gold Standard — idéntico a users/index.php) ===== */
.modal {
    display: none;
    position: fixed;
    z-index: 1100;
    inset: 0;
    background: rgba(15, 23, 42, 0.65);
    backdrop-filter: blur(6px);
    animation: fadeIn 0.3s;
    align-items: center;
    justify-content: center;
}
.modal.active { display: flex; }

.modal-content {
    background: white;
    border-radius: 24px;
    max-width: 560px;
    width: 92%;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden;
    animation: slideUp 0.3s;
    box-shadow: 0 32px 80px rgba(15, 23, 42, 0.3);
}

@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }

.modal-header {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
    padding: 24px 28px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0;
    color: white;
}
.modal-header-info { display: flex; align-items: center; gap: 14px; }
.modal-header-icon {
    background: rgba(255,255,255,0.15);
    border-radius: 12px;
    width: 44px; height: 44px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem;
    flex-shrink: 0;
}
.modal-title { font-size: 1.3rem; font-weight: 700; color: white !important; margin: 0; }
.modal-subtitle { font-size: 0.82rem; color: rgba(255,255,255,0.75); margin: 3px 0 0; }
.modal-body-scroll { padding: 28px; overflow-y: auto; flex: 1; }
.modal-close {
    background: rgba(255,255,255,0.15);
    border: none; color: white;
    width: 36px; height: 36px;
    display: flex; align-items: center; justify-content: center;
    border-radius: 50%; cursor: pointer;
    font-size: 1.1rem; transition: background 0.2s;
    flex-shrink: 0;
}
.modal-close:hover { background: rgba(255,255,255,0.3); }
.modal-close i { color: white !important; }

/* ===== FORM INPUTS ===== */
.input-modern {
    width: 100%; padding: 12px 16px;
    border: 2px solid #e5e7eb; border-radius: 12px;
    font-size: 0.9rem; transition: all 0.2s;
    background: white; color: #1e293b; font-weight: 500;
    box-sizing: border-box;
}
.input-modern:focus {
    outline: none; border-color: #2563eb;
    box-shadow: 0 0 0 4px rgba(37,99,235,0.1);
}

/* ===== RESPONSIVE ===== */
@media (max-width: 1200px) {
    .asig-stats-grid { grid-template-columns: repeat(2, 1fr) !important; }
}
@media (max-width: 768px) {
    .asig-stats-grid { grid-template-columns: 1fr !important; }
}
</style>

<div class="dashboard-container">

    <!-- ===== BANNER ===== -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #3b82f6 100%); border-radius: 20px; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between;">
        <div style="position: absolute; top: -40px; right: -40px; width: 220px; height: 220px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -60px; left: 30%; width: 160px; height: 160px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>
        <div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 10px;">
                    <i class="ti ti-link" style="font-size: 28px; color: white;"></i>
                </div>
                <div>
                    <h1 style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;">Gestión de Asignaciones</h1>
                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                        <i class="ti ti-arrows-exchange"></i>
                        Asignación de Pasantes a Tutores y Departamentos
                        <span style="background: rgba(255,255,255,0.2); color: white; padding: 2px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                            <?= $total ?> registros
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 12px; z-index: 1;">
            <button onclick="abrirModalAsignacion()"
                style="background: white; color: #162660; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.25s; z-index: 1; flex-shrink: 0;"
                onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.3)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'">
                <i class="ti ti-plus" style="font-size: 1.1rem;"></i>
                Nueva Asignación
            </button>
        </div>
    </div>

    <!-- ===== PREMIUM SYSTEM TIP (UX) ===== -->
    <div style="background: linear-gradient(to right, #f8fafc, #edf2f9); border: 1px solid #e2e8f0; border-left: 4px solid #2563eb; border-radius: 12px; padding: 16px 24px; margin-bottom: 28px; display: flex; align-items: flex-start; gap: 16px; box-shadow: 0 4px 6px rgba(0,0,0,0.02);">
        <div style="background: white; width: 40px; height: 40px; border-radius: 50%; display: flex; align-items: center; justify-content: center; box-shadow: 0 2px 8px rgba(37,99,235,0.15); flex-shrink: 0;">
            <i class="ti ti-bulb" style="color: #2563eb; font-size: 1.3rem;"></i>
        </div>
        <div>
            <h4 style="margin: 0 0 4px 0; color: #1e293b; font-size: 0.95rem; font-weight: 700;">Tip del Sistema SGP</h4>
            <p style="margin: 0; color: #64748b; font-size: 0.85rem; line-height: 1.5;">
                Recuerda que solo los pasantes con estado <strong style="color: #10b981; background: #dcfce7; padding: 2px 6px; border-radius: 4px;">Activo</strong> podrán registrar sus horas en el módulo de <b style="color: #1e293b;">Asistencias</b>. Asegúrate de configurar correctamente la fecha de inicio y el horario asignado.
            </p>
        </div>
    </div>

    <!-- ===== KPI CARDS ===== -->
    <div class="asig-stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px;">
        <?php foreach ([
            ['label' => 'Total Pasantes', 'value' => $total,       'sub' => 'registrados',  'color' => '#162660', 'icon' => 'ti-users'],
            ['label' => 'Activos',        'value' => $activos,     'sub' => 'en pasantía',  'color' => '#10b981', 'icon' => 'ti-check-circle'],
            ['label' => 'Pendientes',     'value' => $pendientes,  'sub' => 'por asignar',  'color' => '#f59e0b', 'icon' => 'ti-clock'],
            ['label' => 'Finalizados',    'value' => $finalizados, 'sub' => 'completados',  'color' => '#8b5cf6', 'icon' => 'ti-award'],
        ] as $s): ?>
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-left: 4px solid <?= $s['color'] ?>; transition: all 0.3s;"
             onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.1)'"
             onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="display: flex; justify-content: space-between; align-items: flex-start;">
                <div>
                    <p style="color: #64748b; font-size: 0.85rem; margin: 0 0 8px; font-weight: 500;"><?= $s['label'] ?></p>
                    <h2 style="font-size: 2.2rem; font-weight: 800; color: <?= $s['color'] ?>; margin: 0;"><?= $s['value'] ?></h2>
                    <p style="color: #64748b; font-size: 0.8rem; margin: 4px 0 0;"><?= $s['sub'] ?></p>
                </div>
                <div style="background: <?= $s['color'] ?>18; border-radius: 12px; padding: 12px;">
                    <i class="ti <?= $s['icon'] ?>" style="font-size: 24px; color: <?= $s['color'] ?>;"></i>
                </div>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== TABLA DE ASIGNACIONES ===== -->
    <div style="background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0;">
                <i class="ti ti-list" style="color: #162660;"></i> Registro de Asignaciones
            </h3>
            <div style="display: flex; gap: 8px; align-items: center;">
                <div style="position: relative;">
                    <i class="ti ti-search" style="position: absolute; left: 12px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 0.9rem; pointer-events: none;"></i>
                    <input type="text" id="searchAsignaciones" placeholder="Buscar pasante..."
                        style="padding: 9px 14px 9px 36px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.85rem; color: #334155; outline: none; width: 220px; transition: all 0.2s; background: #f8fafc;"
                        onfocus="this.style.borderColor='#2563eb';this.style.background='white';this.style.boxShadow='0 0 0 3px rgba(37,99,235,0.1)'"
                        onblur="this.style.borderColor='#e2e8f0';this.style.background='#f8fafc';this.style.boxShadow='none'">
                </div>
                <select id="filterEstado"
                    style="padding: 9px 14px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.85rem; color: #334155; outline: none; cursor: pointer; background: white;"
                    onfocus="this.style.borderColor='#2563eb'"
                    onblur="this.style.borderColor='#e2e8f0'">
                    <option value="">Todos los estados</option>
                    <option value="Activo">✅ Activos</option>
                    <option value="Pendiente">⏳ Pendientes</option>
                    <option value="Sin Asignar">🟡 Sin Asignar</option>
                    <option value="Finalizado">🏆 Finalizados</option>
                </select>
            </div>
        </div>
        <div style="overflow-x: auto;">
            <table id="tablaAsignaciones" style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <?php foreach (['Pasante', 'Cédula', 'Tutor Asignado', 'Departamento', 'Horario', 'Progreso', 'Estado', 'Acciones'] as $h): ?>
                        <th style="padding: 14px 20px; text-align: left; font-size: 0.8rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><?= $h ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($asignaciones)): ?>
                    <tr>
                        <td colspan="8" style="text-align: center; padding: 48px 20px; color: #94a3b8;">
                            <i class="ti ti-link-off" style="font-size: 48px; display: block; margin-bottom: 12px;"></i>
                            No hay asignaciones registradas aún.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($asignaciones as $a):
                        $estado    = $a->estado_pasantia ?? 'Sin Asignar';
                        $cfg       = $estadoConfig[$estado] ?? $estadoConfig['Sin Asignar'];
                        $hMeta     = (int)($a->horas_meta ?? 480);
                        $hCumpl    = (int)($a->horas_acumuladas ?? 0);
                        $progreso  = $hMeta > 0 ? round(($hCumpl / $hMeta) * 100) : 0;
                        $pColor    = $progreso >= 80 ? '#10b981' : ($progreso >= 50 ? '#f59e0b' : '#ef4444');
                        $iniciales = strtoupper(substr($a->nombres ?? '?', 0, 1) . substr($a->apellidos ?? '', 0, 1));
                        $nombre    = htmlspecialchars(($a->nombres ?? '') . ' ' . ($a->apellidos ?? ''));
                        $tutorNom  = trim(($a->tutor_nombres ?? '') . ' ' . ($a->tutor_apellidos ?? ''));
                        $tutorNom  = !empty(trim($tutorNom)) ? htmlspecialchars($tutorNom) : '<span style="color:#94a3b8;">Sin tutor</span>';
                    ?>
                    <tr class="fila-asignacion" data-estado="<?= htmlspecialchars($estado) ?>" data-nombre="<?= strtolower($nombre) ?>"
                        style="border-bottom: 1px solid #f1f5f9; transition: all 0.2s;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 16px 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #162660, #3b82f6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                    <?= $iniciales ?>
                                </div>
                                <span style="font-weight: 600; color: #1e293b; font-size: 0.9rem;"><?= $nombre ?></span>
                            </div>
                        </td>
                        <td style="padding: 16px 20px; color: #64748b; font-size: 0.85rem;"><?= htmlspecialchars($a->cedula ?? '—') ?></td>
                        <td style="padding: 16px 20px; font-size: 0.85rem; font-weight: 500; color: #1e293b;"><?= $tutorNom ?></td>
                        <td style="padding: 16px 20px; color: #64748b; font-size: 0.85rem;"><?= htmlspecialchars($a->departamento_nombre ?? '—') ?></td>
                        <td style="padding: 16px 20px; color: #64748b; font-size: 0.85rem;">
                            <?php if (!empty($a->fecha_inicio_pasantia)): ?>
                                <span style="font-size: 0.8rem;"><?= date('d/m/Y', strtotime($a->fecha_inicio_pasantia)) ?></span>
                                <?php if (!empty($a->fecha_fin_estimada)): ?>
                                    <br><span style="font-size: 0.75rem; color: #94a3b8;">al <?= date('d/m/Y', strtotime($a->fecha_fin_estimada)) ?></span>
                                <?php endif; ?>
                            <?php else: ?>
                                <span style="color: #94a3b8;">—</span>
                            <?php endif; ?>
                        </td>
                        <td style="padding: 16px 20px; min-width: 140px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?= $progreso ?>%; height: 100%; background: <?= $pColor ?>; border-radius: 4px; transition: width 0.5s;"></div>
                                </div>
                                <span style="font-size: 0.8rem; font-weight: 700; color: <?= $pColor ?>; min-width: 35px;"><?= $progreso ?>%</span>
                            </div>
                            <span style="font-size: 0.7rem; color: #94a3b8;"><?= $hCumpl ?>/<?= $hMeta ?> hrs</span>
                        </td>
                        <td style="padding: 16px 20px;">
                            <span style="background: <?= $cfg['bg'] ?>; color: <?= $cfg['color'] ?>; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="ti <?= $cfg['icon'] ?>" style="font-size: 0.85rem;"></i>
                                <?= htmlspecialchars($estado) ?>
                            </span>
                        </td>
                        <td style="padding: 16px 20px; white-space: nowrap;">
                            <div style="display: flex; gap: 6px;">
                                <button onclick="verDetalle(<?= (int)$a->pasante_id ?>)"
                                    style="background: #f1f5f9; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #475569; font-size: 0.85rem; transition: all 0.2s;"
                                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 6px rgba(0,0,0,0.05)'"
                                    onmouseout="this.style.transform='none';this.style.boxShadow='none'"
                                    title="Ver detalles">
                                    <i class="ti ti-eye"></i>
                                </button>
                                <?php if (in_array($estado, ['Sin Asignar', 'Pendiente'])): ?>
                                <button onclick="editarAsignacion(<?= (int)$a->pasante_id ?>, '<?= addslashes($nombre) ?>')"
                                    style="background: #eff6ff; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #2563eb; font-size: 0.85rem; transition: all 0.2s;"
                                    onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 4px 6px rgba(37,99,235,0.15)'"
                                    onmouseout="this.style.transform='none';this.style.boxShadow='none'"
                                    title="Editar asignación">
                                    <i class="ti ti-pencil"></i>
                                </button>
                                <?php endif; ?>
                                <?php if ($estado === 'Activo'): ?>
                                <button onclick="finalizarAsignacion(<?= (int)$a->pasante_id ?>, '<?= addslashes($nombre) ?>')"
                                    style="background: #f5f3ff; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #7c3aed; font-size: 0.85rem; transition: all 0.2s;"
                                    onmouseover="this.style.transform='translateY(-2px)'"
                                    onmouseout="this.style.transform='none'"
                                    title="Finalizar pasantía">
                                    <i class="ti ti-flag"></i>
                                </button>
                                <?php endif; ?>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /dashboard-container -->

<!-- ===== MODAL DE ASIGNACIÓN (Gold Standard) ===== -->
<div id="modalAsignacion" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon">
                    <i class="ti ti-link"></i>
                </div>
                <div>
                    <h2 class="modal-title" id="modalTitulo">Nueva Asignación</h2>
                    <p class="modal-subtitle" id="modalSubtitulo">Asignar pasante a un tutor y departamento</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <div class="modal-body-scroll">
            <form id="formAsignacion" onsubmit="submitAsignacion(event)">
                <input type="hidden" id="modalPasanteId" name="pasante_id">

                <!-- Pasante (Select) -->
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                        <i class="ti ti-user" style="margin-right: 6px;"></i>Pasante *
                    </label>
                    <select name="pasante_id_select" id="selectPasante" required class="input-modern">
                        <option value="">Selecciona un pasante...</option>
                        <?php foreach ($asignaciones as $a): ?>
                            <?php if (in_array($a->estado_pasantia ?? 'Sin Asignar', ['Sin Asignar', 'Pendiente'])): ?>
                            <option value="<?= (int)$a->pasante_id ?>">
                                <?= htmlspecialchars(($a->nombres ?? '') . ' ' . ($a->apellidos ?? '')) ?> — <?= htmlspecialchars($a->cedula ?? '') ?>
                            </option>
                            <?php endif; ?>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Tutor -->
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                        <i class="ti ti-school" style="margin-right: 6px;"></i>Tutor Asignado *
                    </label>
                    <select name="tutor_id" id="selectTutor" required class="input-modern">
                        <option value="">Selecciona un tutor...</option>
                        <?php foreach ($tutores as $t): ?>
                        <option value="<?= (int)$t->id ?>">
                            <?= htmlspecialchars(($t->nombres ?? '') . ' ' . ($t->apellidos ?? '')) ?>
                            <?php if (!empty($t->departamento_nombre)): ?> — <?= htmlspecialchars($t->departamento_nombre) ?><?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Departamento -->
                <div style="margin-bottom: 18px;">
                    <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                        <i class="ti ti-building-community" style="margin-right: 6px;"></i>Departamento *
                    </label>
                    <select name="departamento_id" id="selectDepartamento" required class="input-modern">
                        <option value="">Selecciona un departamento...</option>
                        <?php foreach ($departamentos as $dept): ?>
                        <option value="<?= (int)$dept->id ?>"><?= htmlspecialchars($dept->nombre) ?></option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <!-- Fecha de inicio -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                            <i class="ti ti-calendar" style="margin-right: 6px;"></i>Fecha Inicio *
                        </label>
                        <input type="date" name="fecha_inicio" id="inputFechaInicio" required class="input-modern" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                            <i class="ti ti-calendar-event" style="margin-right: 6px;"></i>Fecha Fin (est.)
                        </label>
                        <input type="date" name="fecha_fin" id="inputFechaFin" class="input-modern">
                    </div>
                </div>

                <!-- Horario -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                            <i class="ti ti-clock" style="margin-right: 6px;"></i>Hora Entrada *
                        </label>
                        <input type="time" name="hora_entrada" required class="input-modern" value="08:00">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                            <i class="ti ti-clock-off" style="margin-right: 6px;"></i>Hora Salida *
                        </label>
                        <input type="time" name="hora_salida" required class="input-modern" value="16:00">
                    </div>
                </div>

                <!-- Info calculada -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                    <p style="margin: 0; font-size: 0.85rem; color: #166534;">
                        <i class="ti ti-calculator" style="margin-right: 6px;"></i>
                        <strong>Horas meta:</strong> 480 hrs
                        &nbsp;·&nbsp;
                        <strong>Jornada:</strong> 8 hrs/día
                    </p>
                    <p style="margin: 6px 0 0; font-size: 0.8rem; color: #15803d;">
                        La fecha de fin se calculará automáticamente si no se especifica.
                    </p>
                </div>

                <!-- Observaciones -->
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                        <i class="ti ti-notes" style="margin-right: 6px;"></i>Observaciones
                    </label>
                    <textarea name="observaciones" rows="3" class="input-modern" placeholder="Notas adicionales sobre la asignación..." style="resize: vertical;"></textarea>
                </div>

                <div style="display: flex; gap: 12px;">
                    <button type="button" onclick="cerrarModal()" style="flex: 1; padding: 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: white; color: #64748b; font-weight: 600; cursor: pointer; font-size: 0.9rem; transition: all 0.2s;">
                        Cancelar
                    </button>
                    <button type="submit" id="btnGuardar"
                        style="flex: 2; padding: 14px; background: linear-gradient(135deg, #172554 0%, #1e3a8a 100%); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.95rem; transition: all 0.2s;">
                        <i class="ti ti-check"></i> Confirmar Asignación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<script>
// ── Modal ──────────────────────────────────────────────────
function abrirModalAsignacion() {
    document.getElementById('formAsignacion').reset();
    document.getElementById('modalPasanteId').value = '';
    document.getElementById('inputFechaInicio').value = new Date().toISOString().split('T')[0];
    document.getElementById('modalTitulo').textContent = 'Nueva Asignación';
    document.getElementById('modalSubtitulo').textContent = 'Asignar pasante a un tutor y departamento';
    // Mostrar el contenedor de Choices si existe
    const selPasante = document.getElementById('selectPasante');
    if (selPasante.closest('.choices')) selPasante.closest('.choices').style.display = '';
    else selPasante.style.display = '';
    
    document.getElementById('modalAsignacion').classList.add('active');
    document.body.style.overflow = 'hidden';
    
    // Reinicializar Flatpickr después de resetear el formulario
    if (window.SGPFlatpickr) {
        window.SGPFlatpickr.reinit('#inputFechaInicio');
        window.SGPFlatpickr.reinit('#inputFechaFin');
    }
    
    // Reinicializar Choices
    if (window.SGPChoices) {
        window.SGPChoices.reinit('#selectPasante');
        window.SGPChoices.reinit('#selectTutor');
        window.SGPChoices.reinit('#selectDepartamento');
    }
}

function editarAsignacion(pasanteId, nombre) {
    abrirModalAsignacion();
    document.getElementById('modalPasanteId').value = pasanteId;
    
    // Setear valor y ocultar
    const selPasante = document.getElementById('selectPasante');
    selPasante.value = pasanteId;
    if (window.SGPChoices) window.SGPChoices.reinit('#selectPasante'); // Reinicializar para que tome el value
    
    if (selPasante.closest('.choices')) selPasante.closest('.choices').style.display = 'none';
    else selPasante.style.display = 'none';
    
    document.getElementById('modalTitulo').textContent = 'Editar Asignación';
    document.getElementById('modalSubtitulo').textContent = nombre;
}

function cerrarModal() {
    document.getElementById('modalAsignacion').classList.remove('active');
    document.body.style.overflow = '';
}

// Cerrar al hacer clic fuera
document.getElementById('modalAsignacion').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

// ── Enviar Formulario ──────────────────────────────────────
async function submitAsignacion(e) {
    e.preventDefault();

    var btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader"></i> Guardando...';

    var fd = new FormData(document.getElementById('formAsignacion'));
    // Si hay pasante seleccionado del select, usarlo
    var pasanteIdHidden = document.getElementById('modalPasanteId').value;
    if (!pasanteIdHidden) {
        fd.set('pasante_id', document.getElementById('selectPasante').value);
    }

    try {
        var resp = await fetch('<?= URLROOT ?>/asignaciones/guardar', {
            method: 'POST',
            body: fd,
        });
        var json = await resp.json();

        if (json.success) {
            cerrarModal();
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Asignación Guardada!',
                    text: json.message || 'La asignación se registró correctamente.',
                    confirmButtonColor: '#162660',
                });
            }
            window.location.reload();
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'No se pudo guardar.', confirmButtonColor: '#162660' });
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check"></i> Confirmar Asignación';
        }
    } catch (err) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Intenta de nuevo.', confirmButtonColor: '#162660' });
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check"></i> Confirmar Asignación';
    }
}

// ── Finalizar ──────────────────────────────────────────────
function finalizarAsignacion(pasanteId, nombre) {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
        icon: 'warning',
        title: '¿Finalizar Pasantía?',
        html: '<p>Estás a punto de finalizar la asignación de <strong>' + nombre + '</strong>.</p>',
        showCancelButton: true,
        confirmButtonText: 'Sí, Finalizar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#7c3aed',
        reverseButtons: true,
    }).then(async function(result) {
        if (!result.isConfirmed) return;
        var fd = new FormData();
        fd.append('pasante_id', pasanteId);
        try {
            var resp = await fetch('<?= URLROOT ?>/asignaciones/finalizar', { method: 'POST', body: fd });
            var json = await resp.json();
            if (json.success) {
                await Swal.fire({ icon: 'success', title: '¡Finalizado!', text: json.message, confirmButtonColor: '#162660' });
                window.location.reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message, confirmButtonColor: '#162660' });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Intenta de nuevo.', confirmButtonColor: '#162660' });
        }
    });
}

function verDetalle(id) {
    window.location.href = '<?= URLROOT ?>/pasantes/show/' + id;
}

// ── Filtros ────────────────────────────────────────────────
document.getElementById('searchAsignaciones').addEventListener('input', filtrar);
document.getElementById('filterEstado').addEventListener('change', filtrar);

function filtrar() {
    var texto = document.getElementById('searchAsignaciones').value.toLowerCase();
    var estado = document.getElementById('filterEstado').value;
    var filas = document.querySelectorAll('.fila-asignacion');

    filas.forEach(function(fila) {
        var nombre = fila.getAttribute('data-nombre') || '';
        var est = fila.getAttribute('data-estado') || '';
        var coincideTexto = nombre.indexOf(texto) !== -1;
        var coincideEstado = !estado || est === estado;
        fila.style.display = (coincideTexto && coincideEstado) ? '' : 'none';
    });
}
</script>
