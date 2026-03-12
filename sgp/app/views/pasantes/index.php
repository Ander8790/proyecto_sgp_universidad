<?php
/**
 * Vista: Gestión de Pasantes (Admin)
 * URL: /pasantes  —  Cargada por PasantesController::index()
 */
?>

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

/* Modal box: flexbox para que header sea fijo y body haga scroll */
.modal-box {
    background: white;
    border-radius: 24px;
    width: 90%;
    max-width: 580px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden; /* Clip de esquinas redondeadas sin matar el scroll */
    box-shadow: 0 32px 80px rgba(15,23,42,0.3);
    animation: slideUp 0.3s ease;
}
/* Header: fijo, nunca hace scroll */
.modal-head {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
    padding: 28px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0; /* No se encoge — se queda fijo arriba */
    color: white;
}
.modal-head h2 { font-size:1.3rem; font-weight:700; margin:0; color:white !important; }
.modal-head p  { font-size:0.85rem; margin:4px 0 0; color:rgba(255,255,255,0.8) !important; }
.modal-head * { color: white !important; }
.btn-close-modal {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white !important;
    width: 36px; height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
    flex-shrink: 0;
}
.btn-close-modal:hover { background: rgba(255,255,255,0.35); }
.btn-close-modal i { color: white !important; }
/* Body: ocupa el espacio restante y hace scroll */
.modal-body {
    padding: 28px 32px;
    overflow-y: auto; /* Este es el que scrollea */
    flex: 1;
}

.form-group { margin-bottom: 20px; }
.form-label {
    display:block; font-size:0.82rem; font-weight:700; color:#374151;
    margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;
}
.form-input {
    width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px;
    font-size:0.95rem; color:#1e293b; transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing:border-box; background:#fafafa;
}
.form-input:focus {
    outline:none; border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(79,70,229,0.1); background:white;
}

.horas-wrapper { display:flex; gap:10px; align-items:center; }
.horas-wrapper .form-input { flex:1; }
.btn-reset-horas {
    white-space:nowrap; padding:10px 14px; background:#eff6ff;
    color:#2563eb; border:2px solid #bfdbfe; border-radius:10px;
    font-size:0.8rem; font-weight:700; cursor:pointer; transition:all 0.2s;
    display:flex; align-items:center; gap:6px;
}
.btn-reset-horas:hover { background:#2563eb; color:white; border-color:#2563eb; }

.jornada-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.jornada-option {
    border:2px solid #e5e7eb; border-radius:12px; padding:14px;
    cursor:pointer; transition:all 0.2s; text-align:center; background:#fafafa;
}
.jornada-option:hover { border-color:#2563eb; background:#eff6ff; }
.jornada-option.selected {
    border-color:#2563eb; background:linear-gradient(135deg,#eff6ff,#dbeafe);
    box-shadow:0 0 0 3px rgba(79,70,229,0.15);
}
.jornada-option input[type="radio"] { display:none; }
.jornada-icon  { font-size:1.6rem; display:block; margin-bottom:6px; }
.jornada-label { font-weight:700; color:#1e293b; font-size:0.9rem; display:block; }
.jornada-sub   { color:#6b7280; font-size:0.78rem; }

.proyeccion-card {
    display:none; background:linear-gradient(135deg,#eff6ff,#dbeafe);
    border:1.5px solid #bfdbfe; border-radius:14px; padding:18px 20px;
    margin-top:16px; animation:slideUp 0.3s ease;
}
.proyeccion-card.visible { display:block; }
.proy-title {
    font-size:0.78rem; font-weight:700; color:#2563eb; text-transform:uppercase;
    letter-spacing:0.5px; margin-bottom:10px; display:flex; align-items:center; gap:6px;
}
.proy-dato {
    display:flex; justify-content:space-between; align-items:center;
    padding:6px 0; border-bottom:1px solid rgba(79,70,229,0.1); font-size:0.88rem;
}
.proy-dato:last-child { border-bottom:none; }
.proy-dato span:first-child { color:#6b7280; }
.proy-dato strong { color:#1e3a8a; font-weight:700; }
.proy-highlight {
    background:#2563eb; color:white; padding:10px 14px; border-radius:10px;
    margin-top:10px; font-size:0.9rem; text-align:center; font-weight:600;
}
.modal-divider { border:none; border-top:1px solid #f1f5f9; margin:24px 0; }
.btn-submit {
    width:100%; padding:14px;
    background:linear-gradient(135deg,#172554 0%,#2563eb 100%);
    color:white; border:none; border-radius:12px; font-size:1rem;
    font-weight:700; cursor:pointer; display:flex; align-items:center;
    justify-content:center; gap:10px; transition:all 0.2s;
    box-shadow:0 4px 16px rgba(79,70,229,0.35);
}
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(79,70,229,0.45); }

.progress-wrap { min-width:140px; }
.progress-track { background:#f1f5f9; border-radius:100px; height:7px; overflow:hidden; margin-top:5px; }
.progress-fill  { height:100%; border-radius:100px; background:linear-gradient(90deg,#2563eb,#1d4ed8); transition:width 0.5s ease; }
.progress-label { font-size:0.78rem; color:#64748b; font-weight:600; }
.progress-pct   { float:right; font-weight:700; color:#1e3a8a; }

/* AJUSTE MAESTRO: Forzar que el select flote sobre el modal sin estirarlo */
#modalCambiarEstado .modal-box,
#modalCambiarEstado .modal-body { 
    overflow: visible !important; 
}

/* Recuperar bordes redondos de la cabecera azul */
#modalCambiarEstado .modal-head {
    border-top-left-radius: 20px !important;
    border-top-right-radius: 20px !important;
}

#modalCambiarEstado .choices {
    position: relative !important;
}

#modalCambiarEstado .choices__list--dropdown {
    position: absolute !important;
    top: 100% !important;
    left: 0 !important;
    width: 100% !important;
    z-index: 999999 !important;
    background-color: #ffffff !important;
    border-radius: 0 0 12px 12px !important;
    box-shadow: 0px 10px 15px rgba(0,0,0,0.1) !important;
}
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">

    <!-- BANNER ESTANDARIZADO SGP -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:28px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:14px;">
                <i class="ti ti-user-check" style="font-size:32px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.8rem;font-weight:700;margin:0;">Gestión de Pasantes</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;display:flex;align-items:center;">
                    <span>Registro, seguimiento y proyección de pasantías</span>
                    <span style="display:inline-block; background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.1); border-radius:50px; padding:4px 14px; margin-left:12px; color:white; font-weight:700; font-size:0.8rem; box-shadow:0 4px 6px rgba(0,0,0,0.05); white-space:nowrap;">
                        <?= $total ?> pasantes
                    </span>
                </p>
            </div>
        </div>
        <div style="display:flex; z-index:1; align-items:center;">
            <!-- Contenedor Glassmorphism para el botón -->
            <div style="background: rgba(0, 0, 0, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 6px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <button onclick="SGPModal.buscar({rol: 3})" style="background:white;color:#1e3a8a;border:none;padding:12px 24px;border-radius:10px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:0.95rem;transition:all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <i class="ti ti-search" style="font-size: 1.1rem;"></i> Consulta Rápida
                </button>
            </div>
        </div>
    </div>


    <!-- KPI CARDS ESTANDARIZADAS -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:28px;">
        <?php
        $kpis = [
            ['label' => 'Total Pasantes',  'val' => $total,      'color' => '#2563eb', 'boxShadow' => 'rgba(37,99,235,0.15)', 'icon' => 'ti-users',        'sub' => 'registrados'],
            ['label' => 'En Curso',        'val' => $enCurso,    'color' => '#10b981', 'boxShadow' => 'rgba(16,185,129,0.15)', 'icon' => 'ti-player-play',  'sub' => 'pasantías activas'],
            ['label' => 'Pendientes',      'val' => $pendientes, 'color' => '#f59e0b', 'boxShadow' => 'rgba(245,158,11,0.15)', 'icon' => 'ti-clock',        'sub' => 'por formalizar'],
            ['label' => 'Culminados',      'val' => $culminados, 'color' => '#8b5cf6', 'boxShadow' => 'rgba(139,92,246,0.15)', 'icon' => 'ti-medal',        'sub' => 'este período'],
        ];
        foreach ($kpis as $k): ?>
        <div style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid <?= $k['color'] ?>;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px <?= $k['boxShadow'] ?>'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;"><?= $k['label'] ?></p>
                <i class="ti <?= $k['icon'] ?>" style="color:<?= $k['color'] ?>;font-size:1.4rem;opacity:0.7;"></i>
            </div>
            <h2 style="font-size:2.4rem;font-weight:800;color:<?= $k['color'] ?>;margin:0;" data-kpi-value="<?= $k['val'] ?>">0</h2>
            <p style="color:#94a3b8;font-size:0.8rem;margin:4px 0 0;"><?= $k['sub'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- TABLA -->
    <div style="background:white;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.06);overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0;">
                <i class="ti ti-list-check" style="color:#2563eb;margin-right:6px;"></i>Pasantes Registrados
            </h3>
            <span style="background:#eff6ff;color:#2563eb;padding:4px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;"><?= $total ?> pasantes</span>
        </div>

        <?php if (empty($pasantes)): ?>
        <div style="padding:60px;text-align:center;color:#94a3b8;">
            <i class="ti ti-users-off" style="font-size:3rem;display:block;margin-bottom:12px;"></i>
            <p style="font-size:1rem;font-weight:600;">No hay pasantes registrados aún.</p>
            <button onclick="abrirModal()" style="background:#2563eb;color:white;border:none;padding:10px 20px;border-radius:10px;font-weight:700;cursor:pointer;margin-top:8px;">
                <i class="ti ti-plus"></i> Agregar el primero
            </button>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto; padding: 20px;">
            <table id="tablaPasantes" class="table table-hover align-middle mb-0" style="width:100%; opacity: 0; transition: opacity 0.4s ease-in-out;">
                <thead class="bg-light text-uppercase text-muted small fw-bold">
                    <tr>
                        <?php foreach (['Pasante','Cédula','Institución','Departamento','Progreso (Días)','Estado','Acciones'] as $th): ?>
                        <th class="px-4 py-3 border-0"><?= $th ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pasantes as $p):
                    $nombres  = htmlspecialchars(($p->apellidos ?? '') . ', ' . ($p->nombres ?? ''));
                    $cedula   = htmlspecialchars($p->cedula ?? '—');
                    $inst     = htmlspecialchars($p->institucion_procedencia ?? '—');
                    $depto    = htmlspecialchars($p->departamento_nombre ?? 'Sin asignar');
                    $estado   = $p->estado_pasantia ?? 'Pendiente';
                    $horasAcum = (int)($p->horas_acumuladas ?? 0);
                    $horasMeta = (int)($p->horas_meta ?? 1440);
                    // Días: 1 día = 8h
                    $diasAcum = (int)ceil($horasAcum / 8);
                    $diasTotal= (int)ceil($horasMeta / 8);
                    $pct = $horasMeta > 0 ? min(100, round(($horasAcum / $horasMeta) * 100)) : 0;

                    $estadoMap = [
                        'Activo'     => ['bg' => '#eff6ff', 'color' => '#2563eb'],
                        'Pendiente'  => ['bg' => '#fef9c3', 'color' => '#ca8a04'],
                        'Finalizado' => ['bg' => '#dcfce7', 'color' => '#16a34a'],
                        'Retirado'   => ['bg' => '#fee2e2', 'color' => '#dc2626'],
                    ];
                    $cfg = $estadoMap[$estado] ?? ['bg' => '#f1f5f9', 'color' => '#64748b'];
                    $inicial = strtoupper(substr($p->nombres ?? 'P', 0, 1));
                ?>
                <tr>
                    <td class="px-4 py-3">
                        <div class="dt-name-cell">
                            <div class="dt-avatar"><?= $inicial ?></div>
                            <div>
                                <span class="dt-cell-primary"><?= $nombres ?></span>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-muted"><?= $cedula ?></td>
                    <td class="px-4 py-3 text-muted">
                        <span class="dt-cell-truncate" title="<?= $inst ?>"><?= $inst ?></span>
                    </td>
                    <td class="px-4 py-3 text-muted"><?= $depto ?></td>
                    <td class="px-4 py-3">
                        <div class="progress-wrap">
                            <div class="progress-label">
                                Día <?= $diasAcum ?> / <?= $diasTotal ?>
                                <span class="progress-pct"><?= $pct ?>%</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width:<?= $pct ?>%;"></div>
                            </div>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="badge badge-<?= strtolower($estado) ?>" style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;">
                            <?= $estado ?>
                        </span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="d-flex justify-content-center gap-2 flex-nowrap dt-row-actions">
                            <button onclick="SGPModal.verUsuario(<?= $p->id ?>)" class="btn-action btn-view" title="Ver perfil">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button onclick="abrirModalEditar(<?= $p->id ?>)" class="btn-action btn-edit" title="Editar Pasante">
                                <i class="ti ti-edit"></i>
                            </button>
                            <button onclick="cambiarEstado('<?= UrlSecurity::encrypt($p->id) ?>', '<?= $nombres ?>')" class="btn-action btn-config" title="Cambiar Estado">
                                <i class="ti ti-settings"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /dashboard-container -->



<!-- Modal de Asignación eliminado (VULN-04) — La funcionalidad vive en /asignaciones -->

<!-- ======= MODAL: CAMBIAR ESTADO PREMIUM ======= -->
<div id="modalCambiarEstado" class="modal-overlay">
    <div class="modal-box" style="max-width: 460px; min-height: auto;">
        <div class="modal-head">
            <div>
                <h2><i class="ti ti-settings" style="margin-right:8px;"></i>Cambiar Estado</h2>
                <p id="txt-nombre-pasante">Selecciona el nuevo estado</p>
            </div>
            <button class="btn-close-modal" onclick="cerrarModalEstado()"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body" style="padding: 30px 40px 40px;">
            <input type="hidden" id="inp-pasante-id">
            
            <div class="form-group" style="margin-bottom: 25px;">
                <label class="form-label" style="font-size: 0.85rem; color: #1e3a8a;">Nuevo Estado</label>
                <select class="form-input" id="inp-nuevo-estado">
                    <option value="Pendiente">⏳ Pendiente</option>
                    <option value="Activo">✅ Activo</option>
                    <option value="Finalizado">🏆 Finalizado</option>
                    <option value="Retirado">❌ Retirado</option>
                </select>
            </div>

            <button class="btn-submit" onclick="confirmarCambioEstado()" style="padding: 12px 20px; font-size: 0.9rem; width: auto; margin: 0 auto; display: flex; box-shadow: 0 4px 12px rgba(37,99,235,0.2);">
                <i class="ti ti-device-floppy"></i> Guardar Estado
            </button>
        </div>
    </div>
</div>

<!-- DataTables Premium Assets (Estandarizado) -->
<link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.css">
<script src="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.js"></script>

<script>
// ── Editar Asignación (redirige al módulo de Asignaciones) ──
function abrirModalEditar(pasanteId) {
    window.location.href = URLROOT + '/asignaciones?editar=' + pasanteId;
}

// Variable global para Choices.js
let choicesEstado = null;

// ── Cambiar Estado del Pasante (MODAL PREMIUM) ──
function cambiarEstado(pasanteId, nombre) {
    document.getElementById('inp-pasante-id').value = pasanteId;
    document.getElementById('txt-nombre-pasante').textContent = 'Pasante: ' + (nombre || 'Seleccionado');
    
    // Abrir modal
    document.getElementById('modalCambiarEstado').classList.add('active');

    // Inicializar Choices si no existe
    const select = document.getElementById('inp-nuevo-estado');
    if (!choicesEstado) {
        choicesEstado = new Choices(select, {
            searchEnabled: false,
            itemSelectText: '',
            position: 'bottom',
            shouldSort: false
        });
    }
}

function cerrarModalEstado() {
    document.getElementById('modalCambiarEstado').classList.remove('active');
}

async function confirmarCambioEstado() {
    const pasanteId = document.getElementById('inp-pasante-id').value;
    const nuevoEstado = document.getElementById('inp-nuevo-estado').value;

    try {
        var fd = new FormData();
        fd.append('pasante_id', pasanteId);
        fd.append('estado', nuevoEstado);
        
        // Mostrar cargando con Notyf si existe o SweetAlert
        const resp = await fetch(URLROOT + '/pasantes/cambiarEstado', {
            method: 'POST',
            body: fd,
            headers: { 'X-Requested-With': 'XMLHttpRequest' }
        });
        
        var json = await resp.json();
        if (json.success) {
            cerrarModalEstado();
            if (typeof NotificationService !== 'undefined') {
                NotificationService.success('¡Estado Actualizado!', json.message);
            } else {
                await Swal.fire({ icon: 'success', title: '¡Estado Actualizado!', text: json.message, confirmButtonColor: '#2563eb' });
            }
            window.location.reload();
        } else {
            Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'No se pudo cambiar el estado.', confirmButtonColor: '#2563eb' });
        }
    } catch (err) {
        Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Intenta de nuevo.', confirmButtonColor: '#2563eb' });
    }
}

$(document).ready(function() {
    var $tablaPasantes = $('#tablaPasantes');
    if ($tablaPasantes.length && !$.fn.DataTable.isDataTable($tablaPasantes)) {
        $tablaPasantes.DataTable({
            language: {
                url: '<?= URLROOT ?>/assets/libs/datatables/es-ES.json'
            },
            pageLength: 10,
            order: [[0, 'asc']],
            responsive: true,
            dom: '<"top"f>rt<"bottom"ip><"clear">',
            columnDefs: [
                { responsivePriority: 1, targets: 0 },   // Nombre
                { responsivePriority: 2, targets: -1 },  // Acciones
                { responsivePriority: 3, targets: 5 },   // Estado
                { responsivePriority: 4, targets: 4 },   // Progreso
                { responsivePriority: 8, targets: 3 },   // Departamento
                { responsivePriority: 9, targets: 2 },   // Institución
                { responsivePriority: 10, targets: 1 },  // Cédula
                { orderable: false, targets: 6 }
            ],
            initComplete: function(settings, json) {
                $(this.api().table().node()).css('opacity', '1');
            }
        });
    } else if ($tablaPasantes.length && $.fn.DataTable.isDataTable($tablaPasantes)) {
        $tablaPasantes.DataTable().draw(false);
    }
});
</script>
