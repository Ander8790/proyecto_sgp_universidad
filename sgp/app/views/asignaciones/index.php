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

/* ===== ASIG-KPI-CARD (Clase Aislada — evita conflictos de cascada kpi-card) ===== */
.asig-kpi-card {
    background: #fff;
    border-radius: 16px;
    padding: 22px;
    box-shadow: 0 4px 15px rgba(22,38,96,0.05);
    display: flex;
    flex-direction: row;
    justify-content: space-between;
    align-items: center;
    gap: 16px;
    transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
    cursor: pointer;
    position: relative;
    overflow: hidden;
}
.asig-kpi-card:hover {
    transform: translateY(-4px);
    box-shadow: 0 12px 25px rgba(22,38,96,0.1);
}
.asig-kpi-card .kpi-info {
    flex: 1;
    display: flex;
    flex-direction: column;
    gap: 4px;
}
.asig-kpi-card .kpi-label {
    font-size: 0.78rem;
    color: #64748b;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.5px;
    margin: 0;
}
.asig-kpi-card .kpi-value {
    font-size: 2rem;
    font-weight: 800;
    line-height: 1.1;
    margin: 4px 0 0;
}
.asig-kpi-card .kpi-sub {
    font-size: 0.78rem;
    color: #94a3b8;
    margin: 2px 0 0;
}
.asig-kpi-card .kpi-icon-box {
    width: 52px;
    height: 52px;
    border-radius: 14px;
    display: flex;
    align-items: center;
    justify-content: center;
    font-size: 1.5rem;
    flex-shrink: 0;
}
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">

    <!-- ===== BANNER ===== -->
    <div style="background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); border-radius: 20px; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between;">
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

    <!-- ===== KPI CARDS (Asig Standard — Clase Aislada) ===== -->
    <div class="asig-stats-grid" style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px;">
        <?php foreach ([
            ['label' => 'Total Pasantes', 'value' => $total,       'sub' => 'registrados', 'color' => '#1e3a8a', 'icon' => 'ti-users',        'filter' => ''],
            ['label' => 'Activos',        'value' => $activos,     'sub' => 'en pasantía', 'color' => '#16a34a', 'icon' => 'ti-circle-check', 'filter' => 'Activo'],
            ['label' => 'Pendientes',     'value' => $pendientes,  'sub' => 'por asignar', 'color' => '#f59e0b', 'icon' => 'ti-clock',        'filter' => 'Pendiente'],
            ['label' => 'Finalizados',    'value' => $finalizados, 'sub' => 'completados', 'color' => '#7c3aed', 'icon' => 'ti-award',        'filter' => 'Finalizado'],
        ] as $s): ?>
        <div class="asig-kpi-card"
             onclick="filtrarPorEstado('<?= $s['filter'] ?>')"
             style="border-left: 4px solid <?= $s['color'] ?>;">
            <div class="kpi-info">
                <p class="kpi-label"><?= $s['label'] ?></p>
                <h2 class="kpi-value" style="color: <?= $s['color'] ?>;"><?= $s['value'] ?></h2>
                <p class="kpi-sub"><?= $s['sub'] ?></p>
            </div>
            <div class="kpi-icon-box" style="background: <?= $s['color'] ?>18;">
                <i class="ti <?= $s['icon'] ?>" style="color: <?= $s['color'] ?>; font-size: 1.4rem;"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- Filtros Rápidos y Buscador -->
    <div style="margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between; flex-wrap: wrap; gap: 16px; background: white; padding: 16px 24px; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.04);">
        
        <div style="display: flex; align-items: center; gap: 12px;">
            <p style="font-size: 0.9rem; font-weight: 700; color: #64748b; margin: 0; display:flex; align-items:center; gap:6px;">
                <i class="ti ti-filter" style="font-size:1.1rem; color:#2563eb;"></i> Filtrar:
            </p>
            <div style="display: flex; gap: 10px; flex-wrap: wrap;" id="ventoFilterPillsAsignaciones">
                <button class="vento-pill active" onclick="filtrarPorEstado('')" style="background: #2563eb; color: white; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s; box-shadow: 0 4px 12px rgba(37,99,235,0.25);">
                    Todos
                </button>
                <button class="vento-pill" onclick="filtrarPorEstado('Activo')" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                    Activos
                </button>
                <button class="vento-pill" onclick="filtrarPorEstado('Pendiente')" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                    Pendientes
                </button>
                <button class="vento-pill" onclick="filtrarPorEstado('Sin Asignar')" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                    Sin Asignar
                </button>
                <button class="vento-pill" onclick="filtrarPorEstado('Finalizado')" style="background: #f1f5f9; color: #475569; border: none; padding: 8px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; cursor: pointer; transition: all 0.2s;" onmouseover="if(!this.classList.contains('active')) {this.style.background='#e2e8f0'}" onmouseout="if(!this.classList.contains('active')) {this.style.background='#f1f5f9'}">
                    Finalizados
                </button>
            </div>
        </div>

        <div style="position: relative;">
            <i class="ti ti-search" style="position: absolute; left: 14px; top: 50%; transform: translateY(-50%); color: #94a3b8; font-size: 1.1rem; pointer-events: none;"></i>
            <input type="text" id="searchAsignaciones" placeholder="Buscar por Cédula o Nombre..."
                style="padding: 10px 16px 10px 42px; border: 1.5px solid #e5e7eb; border-radius: 12px; font-size: 0.9rem; color: #1e293b; outline: none; width: 280px; transition: all 0.2s; background: #f8fafc;"
                onfocus="this.style.borderColor='#2563eb';this.style.background='white';this.style.boxShadow='0 0 0 4px rgba(37,99,235,0.1)'"
                onblur="this.style.borderColor='#e5e7eb';this.style.background='#f8fafc';this.style.boxShadow='none'">
        </div>
    </div>

    <!-- ===== TABLA DE ASIGNACIONES (Boxy SaaS) ===== -->
    <div style="background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; border: 0;">
        <div class="table-responsive">
            <table id="tablaAsignaciones" class="table table-hover align-middle mb-0" style="width: 100%; opacity: 0; transition: opacity 0.4s ease-in-out;">
                <thead class="bg-light text-uppercase text-muted small fw-bold">
                    <tr>
                        <?php foreach (['Pasante', 'Cédula', 'Tutor Asignado', 'Departamento', 'Horario', 'Progreso', 'Estado', 'Acciones'] as $h): ?>
                        <th class="px-4 py-3 border-0"><?= $h ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody class="border-top-0">
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
                        <td style="padding: 16px 20px;" data-search="<?= htmlspecialchars($estado) ?>" data-order="<?= htmlspecialchars($estado) ?>">
                            <span style="background: <?= $cfg['bg'] ?>; color: <?= $cfg['color'] ?>; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700; display: inline-flex; align-items: center; gap: 4px;">
                                <i class="ti <?= $cfg['icon'] ?>" style="font-size: 0.85rem;"></i>
                                <?= htmlspecialchars($estado) ?>
                            </span>
                        </td>

                        <td class="px-4 py-3">
                            <div class="d-flex justify-content-center" style="gap: 12px;">
                                <button onclick="verDetalleAsignacion(<?= (int)$a->pasante_id ?>)"
                                        class="btn btn-sm border-0 shadow-sm transition-all" 
                                        data-bs-toggle="tooltip" title="Ver detalles" 
                                        style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #2563eb; color: #ffffff; border-radius: 6px !important;">
                                    <i class="ti ti-eye fs-5 text-white"></i>
                                </button>
                                
                                <?php if (in_array($estado, ['Sin Asignar', 'Pendiente'])): ?>
                                <button onclick="editarAsignacion(<?= (int)$a->pasante_id ?>, '<?= addslashes($nombre) ?>')"
                                        class="btn btn-sm border-0 shadow-sm transition-all" 
                                        data-bs-toggle="tooltip" title="Editar asignación" 
                                        style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #f59e0b; color: #ffffff; border-radius: 6px !important;">
                                    <i class="ti ti-pencil fs-5 text-white"></i>
                                </button>
                                <?php endif; ?>

                                <?php if ($estado === 'Activo'): ?>
                                <button onclick="finalizarAsignacion(<?= (int)$a->pasante_id ?>, '<?= addslashes($nombre) ?>')"
                                        class="btn btn-sm border-0 shadow-sm transition-all" 
                                        data-bs-toggle="tooltip" title="Finalizar pasantía" 
                                        style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #7c3aed; color: #ffffff; border-radius: 6px !important;">
                                    <i class="ti ti-flag fs-5 text-white"></i>
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

                <!-- Pasante (Buscador AJAX + Bento Box) -->
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                        <i class="ti ti-user-search" style="margin-right: 6px;"></i>Buscar Pasante (Sin Asignar) *
                    </label>
                    <div style="position: relative;" id="contenedorBuscadorPasante">
                        <i class="ti ti-search" style="position: absolute; left: 14px; top: 14px; color: #94a3b8; font-size: 1.1rem; pointer-events: none;"></i>
                        <input type="text" id="inputBuscarPasanteAJAX" class="input-modern" placeholder="Cédula o Apellidos..." autocomplete="off" style="padding-left: 42px;">
                        <!-- Lista de sugerencias AJAX -->
                        <div id="listaSugerenciasAjax" style="display:none; position:absolute; top:100%; left:0; right:0; background:white; border:1px solid #e2e8f0; border-radius:12px; margin-top:8px; box-shadow:0 10px 25px rgba(0,0,0,0.1); max-height:220px; overflow-y:auto; z-index:1000;"></div>
                    </div>

                    <!-- Bento Box ReadOnly (Oculto al inicio) -->
                    <div id="bentoPasanteSeleccionado" style="display: none; background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 16px; margin-top: 12px; transition: all 0.3s ease;">
                        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 12px;">
                            <div style="display: flex; gap: 12px; align-items: center;">
                                <div id="bentoAvatar" style="width: 42px; height: 42px; border-radius: 10px; background: linear-gradient(135deg, #10b981, #059669); color: white; display: flex; align-items: center; justify-content: center; font-weight: 800; font-size: 1.1rem; box-shadow: 0 4px 10px rgba(16, 185, 129, 0.2);"></div>
                                <div>
                                    <h4 id="bentoNombre" style="margin: 0; color: #1e293b; font-size: 1rem; font-weight: 800;">—</h4>
                                    <div style="font-size: 0.8rem; color: #64748b; margin-top: 2px;">C.I: <span id="bentoCedula" style="font-weight: 700; color: #475569;">—</span></div>
                                </div>
                            </div>
                            <button type="button" onclick="cancelarPasanteSeleccionado()" style="background: white; border: 1px solid #fee2e2; color: #ef4444; cursor: pointer; font-size: 0.75rem; font-weight: 600; padding: 6px 10px; display: flex; align-items: center; gap: 4px; border-radius: 8px; transition: all 0.2s; box-shadow: 0 2px 4px rgba(239, 68, 68, 0.05);" onmouseover="this.style.background='#fee2e2'; this.style.borderColor='#fca5a5';" onmouseout="this.style.background='white'; this.style.borderColor='#fee2e2';">
                                <i class="ti ti-exchange"></i> Cambiar
                            </button>
                        </div>
                        <div style="background: #eff6ff; padding: 12px 14px; border-radius: 8px; border: 1px dashed #bfdbfe;">
                            <div style="font-size: 0.65rem; font-weight: 800; color: #3b82f6; text-transform: uppercase; letter-spacing: 0.5px; margin-bottom: 4px;">Institución de Procedencia</div>
                            <div id="bentoInstitucion" style="font-size: 0.85rem; color: #1e3a8a; font-weight: 700;">—</div>
                        </div>
                    </div>
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

                <!-- Fecha de inicio y Turno Fijo unificados al estilo Premium Vento -->
                <div style="background: #f8fafc; border: 1.5px solid #e2e8f0; border-radius: 12px; padding: 20px; margin-bottom: 24px; box-shadow: inset 0 2px 4px rgba(0,0,0,0.02);">
                    
                    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 8px;">
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                                <i class="ti ti-calendar" style="margin-right: 6px;"></i>Fecha Inicio *
                            </label>
                            <input type="date" name="fecha_inicio" id="inputFechaInicio" required class="input-modern" value="<?= date('Y-m-d') ?>" style="background: white;">
                        </div>
                        <div>
                            <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                                <i class="ti ti-calendar-event" style="margin-right: 6px;"></i>Fecha Fin (est.)
                            </label>
                            <input type="date" name="fecha_fin" id="inputFechaFin" class="input-modern" readonly style="background: #f1f5f9; cursor: not-allowed;" title="Calculado automáticamente (180 días hábiles)">
                        </div>
                    </div>

                    <!-- Mensaje de Feedback Visual -->
                    <div id="mensaje_fechas" class="d-none" style="margin-bottom: 16px; padding: 10px; border-radius: 8px; background: #ecfdf5; border: 1px solid #a7f3d0; display: flex; align-items: center; gap: 8px;">
                        <i class="ti ti-circle-check" style="color: #10b981; font-size: 1.1rem;"></i>
                        <span style="color: #065f46; font-size: 0.8rem; font-weight: 600;">Cumplimiento de 1440 horas (180 días hábiles) calculado con éxito.</span>
                    </div>

                    <div style="background: white; border: 1px solid #cbd5e1; border-radius: 8px; padding: 12px 16px; display: flex; align-items: center; justify-content: space-between;">
                        <div style="display: flex; align-items: center; gap: 10px;">
                            <div style="background: #eff6ff; color: #2563eb; width: 36px; height: 36px; border-radius: 8px; display: flex; align-items: center; justify-content: center; font-size: 1.1rem;">
                                <i class="ti ti-clock-check"></i>
                            </div>
                            <div>
                                <h4 style="margin: 0; font-size: 0.9rem; font-weight: 700; color: #1e293b;">Turno Institucional Estándar</h4>
                                <p style="margin: 2px 0 0; font-size: 0.8rem; color: #64748b;">08:00 a.m. a 04:00 p.m. (8 hrs/día)</p>
                            </div>
                        </div>
                        <span style="background: #10b981; color: white; padding: 4px 10px; border-radius: 20px; font-size: 0.75rem; font-weight: 700;">Convenio Fijo</span>
                    </div>
                </div>

                <input type="hidden" name="hora_entrada" value="08:00:00">
                <input type="hidden" name="hora_salida" value="16:00:00">

                <!-- Info calculada (ESTÉTICA LEGACY) -->
                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                    <p style="margin: 0; font-size: 0.85rem; color: #166534;">
                        <i class="ti ti-calculator" style="margin-right: 6px;"></i>
                        <strong>Horas meta:</strong> 1,440 hrs (bloqueadas)
                        &nbsp;·&nbsp;
                        <strong>Duración:</strong> 180 días hábiles ≈ 9 meses
                    </p>
                    <p style="margin: 6px 0 0; font-size: 0.8rem; color: #15803d;">
                        La fecha de fin se calculará automáticamente saltando fines de semana.
                    </p>
                </div>

                <!-- Auto-Rellenado (Pasantes Tardíos) -->
                <div style="background: #fdf4ff; border: 1px solid #fbcfe8; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                    <label style="display: flex; align-items: flex-start; gap: 10px; cursor: pointer; margin: 0;">
                        <input type="checkbox" name="auto_rellenar" value="1" checked style="margin-top: 4px; width: 18px; height: 18px; accent-color: #d946ef;">
                        <div>
                            <span style="font-weight: 700; color: #86198f; font-size: 0.95rem; display: block; margin-bottom: 4px;">
                                <i class="ti ti-wand"></i> Botón Mágico: Auto-Rellenar Historial
                            </span>
                            <span style="color: #a21caf; font-size: 0.8rem; line-height: 1.4; display: block;">
                                Si la Fecha de Inicio es en el pasado, el sistema rellenará automáticamente las asistencias (L-V) desde la fecha de inicio hasta hoy, saltando los fines de semana.
                            </span>
                        </div>
                    </label>
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

<!-- ===== MODAL DETALLE DE ASIGNACIÓN ===== -->
<div id="modalDetalleAsignacion" class="modal">
    <div class="modal-content" style="max-width: 500px; border-radius: 20px;">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon" style="background: rgba(255,255,255,0.1); color: #fff;">
                    <i class="ti ti-id-badge"></i>
                </div>
                <div>
                    <h2 class="modal-title">Detalle de Asignación</h2>
                    <p class="modal-subtitle">Ficha Técnica Operativa</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModalDetalle()">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <div class="modal-body-scroll" id="cuerpoDetalleAsignacion" style="position: relative; background: #fff;">
            <!-- Loading -->
            <div id="loadingDetalle" style="text-align: center; padding: 40px 0;">
                <div class="spinner-border text-primary" role="status">
                    <span class="visually-hidden">Loading...</span>
                </div>
                <p style="color: #64748b; font-size: 0.9rem; margin-top: 12px; font-weight: 500;">Cargando expediente...</p>
            </div>
            
            <!-- Content -->
            <div id="contenidoDetalle" style="display: none;">
                <!-- Perfil -->
                <div style="display: flex; align-items: center; gap: 16px; margin-bottom: 24px;">
                    <div id="detAvatar" style="width: 56px; height: 56px; border-radius: 14px; background: linear-gradient(135deg, #3b82f6, #60a5fa); display: flex; align-items: center; justify-content: center; font-size: 1.4rem; font-weight: 800; color: white; box-shadow: 0 4px 12px rgba(59, 130, 246, 0.3);">
                        —
                    </div>
                    <div>
                        <h3 id="detNombre" style="margin: 0; font-size: 1.15rem; font-weight: 800; color: #1e293b;">—</h3>
                        <p style="margin: 2px 0 0; font-size: 0.9rem; color: #64748b; font-weight: 600;">C.I: <span id="detCedula">—</span></p>
                    </div>
                    <div style="margin-left: auto;">
                        <span id="detEstadoBadge" style="padding: 6px 14px; border-radius: 20px; font-size: 0.8rem; font-weight: 800; display: inline-flex; align-items: center; gap: 6px;">—</span>
                    </div>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px; margin-bottom: 20px;">
                    <div style="background: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Departamento</div>
                        <div id="detDepartamento" style="font-size: 0.9rem; font-weight: 700; color: #0f172a; margin-top: 6px;">—</div>
                    </div>
                    <div style="background: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <div style="font-size: 0.72rem; color: #64748b; font-weight: 700; text-transform: uppercase;">Tutor Asignado</div>
                        <div id="detTutor" style="font-size: 0.9rem; font-weight: 700; color: #0f172a; margin-top: 6px;">—</div>
                    </div>
                </div>

                <div style="background: #f0fdf4; border: 1px solid #bbf7d0; border-radius: 12px; padding: 16px; margin-bottom: 20px;" id="detProgresoContainer">
                    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 10px;">
                        <span id="detHorasEtiqueta" style="font-size: 0.85rem; font-weight: 700; color: #166534;"><i class="ti ti-chart-bar"></i> Progreso (Metas)</span>
                        <span id="detHorasLabel" style="font-size: 0.85rem; font-weight: 800; color: #15803d;">0 / 1440 hrs</span>
                    </div>
                    <div style="height: 10px; background: rgba(255,255,255,0.6); border-radius: 5px; overflow: hidden; margin-bottom: 8px;">
                        <div id="detBarraProgreso" style="height: 100%; width: 0%; background: #16a34a; transition: width 0.8s cubic-bezier(0.4, 0, 0.2, 1); border-radius: 5px;"></div>
                    </div>
                    <p style="margin: 0; font-size: 0.75rem; color: #166534; text-align: right; font-weight: 600;" id="detPorcentajeLabel">0% Completado</p>
                </div>

                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 12px;">
                    <div style="background: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <span style="color: #64748b; font-weight: 600; font-size: 0.72rem; display: block; margin-bottom: 4px; text-transform: uppercase;">Fecha Inicio Pasantía</span>
                        <span id="detFechaInicio" style="color: #1e293b; font-weight: 700; font-size: 0.9rem;"><i class="ti ti-calendar" style="color: #3b82f6;"></i> —</span>
                    </div>
                    <div style="background: #f8fafc; padding: 14px; border-radius: 12px; border: 1px solid #e2e8f0;">
                        <span style="color: #64748b; font-weight: 600; font-size: 0.72rem; display: block; margin-bottom: 4px; text-transform: uppercase;">Vencimiento Est.</span>
                        <span id="detFechaFin" style="color: #1e293b; font-weight: 700; font-size: 0.9rem;"><i class="ti ti-calendar-event" style="color: #f59e0b;"></i> —</span>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>

<script>
// ── Modal ──────────────────────────────────────────────────
window.abrirModalAsignacion = function() {
    document.getElementById('formAsignacion').reset();
    document.getElementById('modalPasanteId').value = '';
    document.getElementById('inputFechaInicio').value = new Date().toISOString().split('T')[0];
    document.getElementById('modalTitulo').textContent = 'Nueva Asignación';
    document.getElementById('modalSubtitulo').textContent = 'Asignar pasante a un tutor y departamento';
    
    // Resetear Buscador y Bento Box
    document.getElementById('bentoPasanteSeleccionado').style.display = 'none';
    const inputBuscar = document.getElementById('inputBuscarPasanteAJAX');
    if (inputBuscar) {
        inputBuscar.value = '';
    }
    const contBuscar = document.getElementById('contenedorBuscadorPasante');
    if (contBuscar) contBuscar.style.display = 'block';
    const listaSug = document.getElementById('listaSugerenciasAjax');
    if (listaSug) listaSug.style.display = 'none';
    
    document.getElementById('modalAsignacion').classList.add('active');
    document.body.style.overflow = 'hidden';

    // Disparar cálculo inicial
    updateCalculatedEndDate();
    
    // Reinicializar Flatpickr después de resetear el formulario
    if (window.SGPFlatpickr) {
        window.SGPFlatpickr.reinit('#inputFechaInicio');
        window.SGPFlatpickr.reinit('#inputFechaFin');
    }
    
    // Reinicializar Choices
    if (window.SGPChoices) {
        window.SGPChoices.reinit('#selectTutor');
        window.SGPChoices.reinit('#selectDepartamento');
    }
}

// ── Lógica de Cálculo de 180 Días Hábiles (Calculadora Inteligente) ───
window.updateCalculatedEndDate = function() {
    const startInput = document.getElementById('inputFechaInicio');
    const endInput = document.getElementById('inputFechaFin');
    const msgFechas = document.getElementById('mensaje_fechas');
    
    if (!startInput || !endInput || !startInput.value) {
        if (msgFechas) msgFechas.classList.add('d-none');
        return;
    }

    let startDate = new Date(startInput.value + 'T00:00:00');
    let businessDaysCount = 0;
    let currentDate = new Date(startDate);

    // Motor Matemático: Bucle para sumar exactamente 180 días hábiles (Lunes a Viernes)
    while (businessDaysCount < 180) {
        currentDate.setDate(currentDate.getDate() + 1);
        let dayOfWeek = currentDate.getDay(); // 0 es Domingo, 6 es Sábado
        if (dayOfWeek !== 0 && dayOfWeek !== 6) {
            businessDaysCount++;
        }
    }
    
    const formattedDate = currentDate.toISOString().split('T')[0];
    endInput.value = formattedDate;
    
    // Feedback Visual Premium
    if (msgFechas) {
        msgFechas.classList.remove('d-none');
        // Efecto visual sutil de confirmación
        msgFechas.style.animation = 'none';
        msgFechas.offsetHeight; // trigger reflow
        msgFechas.style.animation = 'pulse-green 0.5s ease-out';
    }
    
    // Sincronizar con Flatpickr si existe
    if (window.SGPFlatpickr && endInput._flatpickr) {
        endInput._flatpickr.setDate(formattedDate, false);
    }
}

// Escuchar cambios en la fecha de inicio
document.getElementById('inputFechaInicio')?.addEventListener('change', window.updateCalculatedEndDate);

window.editarAsignacion = function(pasanteId, nombre) {
    abrirModalAsignacion();
    document.getElementById('modalPasanteId').value = pasanteId;
    
    // Configurar Bento Box para Edición Manualmente
    document.getElementById('contenedorBuscadorPasante').style.display = 'none';
    document.getElementById('bentoNombre').innerText = nombre;
    document.getElementById('bentoCedula').innerText = 'Ver Expediente';
    document.getElementById('bentoAvatar').innerText = nombre.substring(0,2).toUpperCase();
    document.getElementById('bentoInstitucion').innerText = 'Registrada en sistema';
    document.getElementById('bentoPasanteSeleccionado').style.display = 'block';
    
    document.getElementById('modalTitulo').textContent = 'Editar Asignación';
    document.getElementById('modalSubtitulo').textContent = nombre;
}

window.cerrarModal = function() {
    document.getElementById('modalAsignacion').classList.remove('active');
    document.body.style.overflow = '';
}

// Cerrar al hacer clic fuera
document.getElementById('modalAsignacion').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

// ── Enviar Formulario ──────────────────────────────────────
window.submitAsignacion = async function(e) {
    e.preventDefault();

    var btn = document.getElementById('btnGuardar');
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader"></i> Guardando...';

    var fd = new FormData(document.getElementById('formAsignacion'));
    // Validación de pasante_id 
    if (!fd.get('pasante_id')) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'Pasante Requerido', text: 'Por favor, busca y selecciona un pasante.', confirmButtonColor: '#162660' });
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check"></i> Confirmar Asignación';
        return;
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
window.finalizarAsignacion = function(pasanteId, nombre) {
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

// ── Buscador AJAX & Bento Box Logic ────────────────────────
document.addEventListener('DOMContentLoaded', function() {
    const inputBuscar = document.getElementById('inputBuscarPasanteAJAX');
    const listaSug = document.getElementById('listaSugerenciasAjax');
    const inputOculto = document.getElementById('modalPasanteId');
    const bentoBox = document.getElementById('bentoPasanteSeleccionado');
    
    if (inputBuscar) {
        let timeoutId;
        inputBuscar.addEventListener('input', function() {
            clearTimeout(timeoutId);
            const query = this.value.trim();
            if (query.length < 2) {
                listaSug.style.display = 'none';
                return;
            }
            
            timeoutId = setTimeout(() => {
                const formData = new FormData();
                formData.append('query', query);
                fetch('<?= URLROOT ?>/asignaciones/buscarPasanteAjax', { method: 'POST', body: formData })
                .then(r => r.json())
                .then(data => {
                    listaSug.innerHTML = '';
                    if (data.length > 0) {
                        data.forEach(p => {
                            const div = document.createElement('div');
                            div.style.padding = '12px 16px';
                            div.style.cursor = 'pointer';
                            div.style.borderBottom = '1px solid #f1f5f9';
                            div.style.display = 'flex';
                            div.style.alignItems = 'center';
                            div.style.gap = '12px';
                            div.addEventListener('mouseover', () => div.style.background = '#f8fafc');
                            div.addEventListener('mouseout', () => div.style.background = 'white');
                            
                            const inis = (p.nombres.charAt(0) + p.apellidos.charAt(0)).toUpperCase();
                            div.innerHTML = `
                                <div style="width:36px; height:36px; border-radius:10px; background:linear-gradient(135deg, #162660, #2563eb); display:flex; align-items:center; justify-content:center; font-size:0.85rem; font-weight:800; color:white;">${inis}</div>
                                <div>
                                    <div style="font-size:0.9rem; font-weight:800; color:#1e293b;">${p.nombres} ${p.apellidos}</div>
                                    <div style="font-size:0.75rem; font-weight:600; color:#64748b;">C.I: ${p.cedula}</div>
                                </div>
                            `;
                            
                            div.onclick = () => seleccionarPasante(p);
                            listaSug.appendChild(div);
                        });
                    } else {
                        listaSug.innerHTML = '<div style="padding:16px; text-align:center; color:#94a3b8; font-size:0.85rem; font-weight:600;">No se encontraron resultados pendientes</div>';
                    }
                    listaSug.style.display = 'block';
                });
            }, 300);
        });
        
        document.addEventListener('click', (e) => {
            if (!inputBuscar.contains(e.target) && !listaSug.contains(e.target)) {
                listaSug.style.display = 'none';
            }
        });
    }
});

window.seleccionarPasante = function(p) {
    document.getElementById('contenedorBuscadorPasante').style.display = 'none';
    document.getElementById('listaSugerenciasAjax').style.display = 'none';
    
    document.getElementById('modalPasanteId').value = p.pasante_id;
    
    document.getElementById('bentoNombre').innerText = p.nombres + ' ' + p.apellidos;
    document.getElementById('bentoCedula').innerText = p.cedula;
    document.getElementById('bentoAvatar').innerText = (p.nombres.charAt(0) + p.apellidos.charAt(0)).toUpperCase();
    document.getElementById('bentoInstitucion').innerText = p.institucion_procedencia || 'No especificada';
    
    document.getElementById('bentoPasanteSeleccionado').style.display = 'block';
}

window.cancelarPasanteSeleccionado = function() {
    document.getElementById('modalPasanteId').value = '';
    document.getElementById('bentoPasanteSeleccionado').style.display = 'none';
    document.getElementById('contenedorBuscadorPasante').style.display = 'block';
    const num = document.getElementById('inputBuscarPasanteAJAX');
    num.value = '';
    num.focus();
}

// ── Modal Detalles ──────────────────────────────────────
window.verDetalleAsignacion = async function(pasanteId) {
    const modal = document.getElementById('modalDetalleAsignacion');
    if (modal) modal.classList.add('active');
    document.body.style.overflow = 'hidden';
    
    const loader = document.getElementById('loadingDetalle');
    if (loader) loader.style.display = 'block';
    
    const contenido = document.getElementById('contenidoDetalle');
    if (contenido) contenido.style.display = 'none';

    try {
        const formData = new FormData();
        formData.append('pasante_id', pasanteId);
        
        const resp = await fetch('<?= URLROOT ?>/asignaciones/getDetalleAjax', {
            method: 'POST',
            body: formData
        });
        
        const data = await resp.json();
        if (data.error) throw new Error(data.error);
        
        // Cargar datos (con optional chaining blindado)
        const nombresStr = data.nombres || '';
        const apeStr = data.apellidos ? ' ' + data.apellidos : '';
        if (document.getElementById('detNombre')) document.getElementById('detNombre').innerText = nombresStr + apeStr;
        if (document.getElementById('detCedula')) document.getElementById('detCedula').innerText = data.cedula || 'N/A';
        if (document.getElementById('detAvatar')) document.getElementById('detAvatar').innerText = (nombresStr.charAt(0) + (data.apellidos ? data.apellidos.charAt(0) : '')).toUpperCase();
        
        if (document.getElementById('detDepartamento')) document.getElementById('detDepartamento').innerText = data.departamento_nombre || 'No asignado';
        if (document.getElementById('detTutor')) document.getElementById('detTutor').innerText = data.tutor_nombres ? (data.tutor_nombres + ' ' + (data.tutor_apellidos||'')) : 'No asignado';
        
        if (document.getElementById('detFechaInicio')) document.getElementById('detFechaInicio').innerHTML = `<i class="ti ti-calendar" style="color: #3b82f6; margin-right: 4px;"></i>` + (data.fecha_inicio_pasantia || 'N/A');
        if (document.getElementById('detFechaFin')) document.getElementById('detFechaFin').innerHTML = `<i class="ti ti-calendar-event" style="color: #f59e0b; margin-right: 4px;"></i>` + (data.fecha_fin_estimada || 'N/A');
        
        // Progreso
        const hrAcum = parseInt(data.horas_acumuladas) || 0;
        const hrMeta = parseInt(data.horas_meta) || 1440;
        const pct = hrMeta > 0 ? Math.min(100, Math.round((hrAcum / hrMeta) * 100)) : 0;
        
        if (document.getElementById('detHorasLabel')) document.getElementById('detHorasLabel').innerText = `${hrAcum} / ${hrMeta} hrs`;
        
        // Colores de alerta
        let bColor = '#10b981';
        let bgCont = '#f0fdf4';
        let bBorder = '#bbf7d0';
        let bText = '#15803d';
        
        if(pct < 50) { bColor = '#ef4444'; bgCont = '#fef2f2'; bBorder = '#fecaca'; bText = '#b91c1c'; }
        else if (pct < 80) { bColor = '#f59e0b'; bgCont = '#fffbeb'; bBorder = '#fde68a'; bText = '#b45309'; }
        
        const progresoContainer = document.getElementById('detProgresoContainer');
        if (progresoContainer) {
            progresoContainer.style.background = bgCont;
            progresoContainer.style.borderColor = bBorder;
        }
        
        const badgeHoras = document.getElementById('detHorasLabel');
        if (badgeHoras) {
            badgeHoras.style.color = bText;
        }

        const etiquetaHoras = document.getElementById('detHorasEtiqueta');
        if (etiquetaHoras) {
            etiquetaHoras.style.color = bText;
        }
        
        const porcLabel = document.getElementById('detPorcentajeLabel');
        if (porcLabel) {
            porcLabel.style.color = bText;
            porcLabel.innerText = `${pct}% Completado`;
        }
        
        // Animacion del bar
        setTimeout(() => {
            const bar = document.getElementById('detBarraProgreso');
            if (bar) {
                bar.style.width = pct + '%';
                bar.style.background = bColor;
            }
        }, 150);
        
        // Badge
        const estadoCfg = {
            'Activo': {bg: '#dcfce7', c: '#16a34a', i: 'ti-check'},
            'Pendiente': {bg: '#fed7aa', c: '#ea580c', i: 'ti-hourglass'},
            'Sin Asignar': {bg: '#fef9c3', c: '#ca8a04', i: 'ti-clock'},
            'Finalizado': {bg: '#ede9fe', c: '#7c3aed', i: 'ti-award'}
        };
        const ec = estadoCfg[data.estado_pasantia] || estadoCfg['Sin Asignar'];
        const badge = document.getElementById('detEstadoBadge');
        if (badge) {
            badge.style.background = ec.bg;
            badge.style.color = ec.c;
            badge.innerHTML = `<i class="ti ${ec.i}"></i> ${data.estado_pasantia}`;
        }
        
        if (loader) loader.style.display = 'none';
        if (contenido) contenido.style.display = 'block';
    } catch(e) {
        const errorBody = document.getElementById('cuerpoDetalleAsignacion');
        if (errorBody) {
             errorBody.innerHTML = `<div style="text-align:center; padding: 30px; color:#ef4444;"><i class="ti ti-alert-triangle" style="font-size:2rem;"></i><br>Error cargando detalles.</div>`;
        }
    }
}

window.cerrarModalDetalle = function() {
    document.getElementById('modalDetalleAsignacion').classList.remove('active');
    document.body.style.overflow = '';
}

// Cerrar al hacer clic fuera del detalle
document.getElementById('modalDetalleAsignacion').addEventListener('click', function(e) {
    if (e.target === this) window.cerrarModalDetalle();
});
</script>

<!-- DataTables & Buttons Assets -->
<link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.css">
<script src="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.js"></script>

<link rel="stylesheet" href="https://cdn.datatables.net/buttons/2.4.1/css/buttons.dataTables.min.css">
<script src="https://cdn.datatables.net/buttons/2.4.1/js/dataTables.buttons.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/jszip/3.10.1/jszip.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/pdfmake.min.js"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/pdfmake/0.1.53/vfs_fonts.js"></script>
<script src="https://cdn.datatables.net/buttons/2.4.1/js/buttons.html5.min.js"></script>

<script>
// ── DataTables & Filtros ──────────────────────────────────
$(document).ready(function() {
    var dt = $('#tablaAsignaciones').DataTable({
        language: {
            url: '<?= URLROOT ?>/assets/libs/datatables/es-ES.json'
        },
        pageLength: 10,
        responsive: true,
        dom: '<"top"f>rt<"bottom"ip><"clear">',
        buttons: [
            {
                extend: 'excel',
                className: 'btn btn-light btn-sm border rounded-3 me-2',
                text: '<i class="ti ti-file-spreadsheet text-success me-1"></i> Excel'
            },
            {
                extend: 'pdf',
                className: 'btn btn-light btn-sm border rounded-3',
                text: '<i class="ti ti-file-type-pdf text-danger me-1"></i> PDF'
            }
        ],
        columnDefs: [
            { orderable: false, targets: 7 } // Desactivar ordenamiento de Acciones
        ],
        initComplete: function(settings, json) {
            $(this.api().table().node()).css('opacity', '1');
        }
    });

    // Conectar inputs de búsqueda visuales (Premium SGP) al core de DataTables
    $('#searchAsignaciones').on('input', function() {
        dt.search(this.value).draw();
    });

    $('#filterEstado').on('change', function() {
        var term = this.value;
        dt.column(6).search(term ? '^' + term + '$' : '', true, false).draw();
    });

    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});

// ── Filtros interactivos de KPIs ───────────────────────────────────
window.filtrarPorEstado = function(estado) {
    var dt = $('#tablaAsignaciones').DataTable();
    if (estado === '') {
        dt.column(6).search('').draw();
    } else {
        dt.column(6).search('^' + estado + '$', true, false).draw();
    }
    
    // Sincronizar Vento Pills Visualmente
    const pills = document.querySelectorAll('#ventoFilterPillsAsignaciones .vento-pill');
    if (pills.length > 0) {
        pills.forEach(p => {
            p.style.background = '#f1f5f9';
            p.style.color = '#475569';
            p.style.boxShadow = 'none';
            p.classList.remove('active');
            
            const isMatch = (estado === '' && p.innerText.includes('Todos')) || 
                            (estado === 'Activo' && p.innerText.includes('Activos')) ||
                            (estado === 'Pendiente' && p.innerText.includes('Pendientes')) ||
                            (estado === 'Sin Asignar' && p.innerText.includes('Sin Asignar')) ||
                            (estado === 'Finalizado' && p.innerText.includes('Finalizados'));
            if(isMatch) {
                p.classList.add('active');
                p.style.background = '#2563eb';
                p.style.color = 'white';
                p.style.boxShadow = '0 4px 12px rgba(37,99,235,0.25)';
            }
        });
    }

    // Filtrado silencioso — sin notificaciones Toast (Silencio Operativo SGP)
}
</script>
