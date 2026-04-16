<?php
/**
 * Vista: Gestión de Asignaciones — Admin
 * Módulo de asignación de pasantes a tutores y departamentos
 */

$asignaciones  = $data['asignaciones']  ?? [];
$departamentos = $data['departamentos'] ?? [];
$tutores       = $data['tutores']       ?? [];
$periodos      = $data['periodos']      ?? [];
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
    <div class="asig-banner" style="background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); border-radius: 20px; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between;">
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
        <div class="asig-banner-actions" style="display: flex; gap: 12px; z-index: 1;">
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
    <div class="sgp-solo-desktop" style="background: white; border-radius: 20px; box-shadow: 0 4px 20px rgba(0,0,0,0.05); overflow: hidden; border: 0;">
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
                                
                                <?php if (in_array($estado, ['Sin Asignar', 'Pendiente'])): 
                                    $instAsig = htmlspecialchars($a->institucion_procedencia ?? 'No registrada');
                                ?>
                                <button onclick="editarAsignacion(<?= (int)$a->pasante_id ?>, '<?= addslashes($nombre) ?>', '<?= addslashes($a->cedula) ?>', '<?= addslashes($instAsig) ?>')"
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

    <!-- CARD VIEW — solo en móvil (< 992px) -->
    <div class="sgp-solo-mobile gap-3 px-1 pb-3" id="cardsAsignaciones">
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
        $tutorNom  = !empty(trim($tutorNom)) ? htmlspecialchars($tutorNom) : 'Sin tutor';
    ?>
    <div class="bca-card">

        <!-- Encabezado: avatar + nombre + badge estado -->
        <div class="bca-header">
            <div class="bca-avatar"><?= $iniciales ?></div>
            <div class="bca-info">
                <span class="bca-nombre"><?= $nombre ?></span>
                <span class="bca-cedula">C.I: <?= htmlspecialchars($a->cedula ?? '—') ?></span>
            </div>
            <span class="bca-badge"
                  style="background:<?= $cfg['bg'] ?>; color:<?= $cfg['color'] ?>;">
                <i class="ti <?= $cfg['icon'] ?>" style="font-size:0.7rem; margin-right:2px;"></i>
                <?= htmlspecialchars($estado) ?>
            </span>
        </div>

        <!-- Cuerpo: datos secundarios -->
        <div class="bca-body">
            <div class="bca-row">
                <span class="bca-label">Tutor</span>
                <span class="bca-value"><?= $tutorNom ?></span>
            </div>
            <div class="bca-row">
                <span class="bca-label">Departamento</span>
                <span class="bca-value"><?= htmlspecialchars($a->departamento_nombre ?? '—') ?></span>
            </div>
            <?php if (!empty($a->fecha_inicio_pasantia)): ?>
            <div class="bca-row">
                <span class="bca-label">Inicio</span>
                <span class="bca-value"><?= date('d/m/Y', strtotime($a->fecha_inicio_pasantia)) ?><?php if (!empty($a->fecha_fin_estimada)): ?> — <?= date('d/m/Y', strtotime($a->fecha_fin_estimada)) ?><?php endif; ?></span>
            </div>
            <?php endif; ?>
            <div class="bca-row">
                <span class="bca-label">Progreso</span>
                <div class="bca-progress-wrap">
                    <div class="bca-progress-bar">
                        <div class="bca-progress-fill" style="width:<?= $progreso ?>%; background:<?= $pColor ?>;"></div>
                    </div>
                    <span class="bca-progress-pct" style="color:<?= $pColor ?>;"><?= $progreso ?>%</span>
                </div>
            </div>
            <div class="bca-row">
                <span class="bca-label">Horas</span>
                <span class="bca-value"><?= $hCumpl ?> / <?= $hMeta ?> hrs</span>
            </div>
        </div>

        <!-- Acciones — mismas funciones JS que la tabla -->
        <div class="bca-actions">
            <button class="bca-btn bca-btn-view"
                    onclick="verDetalleAsignacion(<?= (int)$a->pasante_id ?>)"
                    title="Ver detalles">
                <i class="ti ti-eye"></i> Ver
            </button>
            <?php if (in_array($estado, ['Sin Asignar', 'Pendiente'])):
                $instAsig = htmlspecialchars($a->institucion_procedencia ?? 'No registrada'); ?>
            <button class="bca-btn bca-btn-edit"
                    onclick="editarAsignacion(<?= (int)$a->pasante_id ?>, '<?= addslashes($nombre) ?>', '<?= addslashes($a->cedula) ?>', '<?= addslashes($instAsig) ?>')"
                    title="Editar asignación">
                <i class="ti ti-pencil"></i> Editar
            </button>
            <?php endif; ?>
            <?php if ($estado === 'Activo'): ?>
            <button class="bca-btn bca-btn-fin"
                    onclick="finalizarAsignacion(<?= (int)$a->pasante_id ?>, '<?= addslashes($nombre) ?>')"
                    title="Finalizar pasantía">
                <i class="ti ti-flag"></i> Finalizar
            </button>
            <?php endif; ?>
        </div>

    </div>
    <?php endforeach; ?>
    </div>

</div><!-- /dashboard-container -->

<!-- COMPONENTE: MODAL DE ASIGNACIONES -->
<?php require APPROOT . '/views/inc/modal_asignacion.php'; ?>

<script>
// ── DataTables & Filtros ──────────────────────────────────
$(document).ready(function() {
    var $tablaAsignaciones = $('#tablaAsignaciones');
    var dt;
    if ($tablaAsignaciones.length && !$.fn.DataTable.isDataTable($tablaAsignaciones)) {
        dt = $tablaAsignaciones.DataTable({
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
    } else if ($tablaAsignaciones.length && $.fn.DataTable.isDataTable($tablaAsignaciones)) {
        dt = $tablaAsignaciones.DataTable();
        dt.draw(false);
    }

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

    // SGP-FIX: recalcular columnas al pasar de móvil a desktop
    var _dtAsigAdjusted = false;
    window.addEventListener('resize', function () {
        if (window.innerWidth >= 992 && !_dtAsigAdjusted) {
            var _dt = $('#tablaAsignaciones').DataTable();
            if (_dt) { _dt.columns.adjust().draw(false); }
            _dtAsigAdjusted = true;
        }
        if (window.innerWidth < 992) { _dtAsigAdjusted = false; }
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
