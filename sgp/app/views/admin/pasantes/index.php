<?php
/**
 * Vista: Gestión de Pasantes — Admin
 * Fase 5: Datos reales desde la tabla usuarios + Modal de Asignación funcional
 */

// Contadores dinámicos desde BD
$totalPasantes   = count($data['pasantes']);
$activos         = count(array_filter($data['pasantes'], fn($p) => ($p->estado_pasantia ?? '') === 'Activo'));
$sinAsignar      = count(array_filter($data['pasantes'], fn($p) => ($p->estado_pasantia ?? 'Sin Asignar') === 'Sin Asignar'));
$finalizados     = count(array_filter($data['pasantes'], fn($p) => ($p->estado_pasantia ?? '') === 'Finalizado'));

$estadoConfig = [
    'Sin Asignar' => ['bg' => '#fef9c3', 'color' => '#ca8a04', 'label' => 'Sin Asignar'],
    'Activo'      => ['bg' => '#dcfce7', 'color' => '#16a34a', 'label' => 'Activo'],
    'Finalizado'  => ['bg' => '#ede9fe', 'color' => '#7c3aed', 'label' => 'Finalizado'],
];
?>

<div class="main-content">

    <!-- ===== BANNER ===== -->
    <div style="background: linear-gradient(135deg, #0f172a 0%, #1e3a8a 60%, #3b82f6 100%); border-radius: 20px; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between;">
        <div style="position: absolute; top: -40px; right: -40px; width: 220px; height: 220px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
        <div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 10px;">
                    <i class="ti ti-user-check" style="font-size: 28px; color: white;"></i>
                </div>
                <div>
                    <h1 style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;">Gestión de Pasantes</h1>
                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem;">Administración y seguimiento del ciclo de pasantías</p>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 12px; z-index: 1;">
            <a href="<?= URLROOT ?>/users" style="background: white; color: #162660; border: none; padding: 10px 20px; border-radius: 10px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; text-decoration: none; font-size: 0.9rem; transition: all 0.2s;">
                <i class="ti ti-plus"></i> Nuevo Pasante
            </a>
        </div>
    </div>

    <!-- ===== ESTADÍSTICAS DINÁMICAS ===== -->
    <div style="display: grid; grid-template-columns: repeat(4, 1fr); gap: 20px; margin-bottom: 28px;">
        <?php foreach ([
            ['label' => 'Total Pasantes',  'value' => $totalPasantes, 'sub' => 'registrados', 'color' => '#162660', 'icon' => 'ti-users'],
            ['label' => 'Activos',          'value' => $activos,       'sub' => 'en pasantía',  'color' => '#10b981', 'icon' => 'ti-user-check'],
            ['label' => 'Sin Asignar',      'value' => $sinAsignar,    'sub' => 'por asignar',  'color' => '#f59e0b', 'icon' => 'ti-clock'],
            ['label' => 'Finalizados',      'value' => $finalizados,   'sub' => 'completados',  'color' => '#8b5cf6', 'icon' => 'ti-award'],
        ] as $s): ?>
        <div style="background: white; border-radius: 16px; padding: 24px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); border-left: 4px solid <?= $s['color'] ?>;">
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

    <!-- ===== TABLA DE PASANTES ===== -->
    <div style="background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0;"><i class="ti ti-list" style="color: #162660;"></i> Lista de Pasantes</h3>
            <span style="background: #eff6ff; color: #162660; padding: 4px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;"><?= $totalPasantes ?> pasantes</span>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <?php foreach (['Pasante', 'Cédula', 'Institución', 'Departamento', 'Progreso', 'Estado', 'Acciones'] as $h): ?>
                        <th style="padding: 14px 20px; text-align: left; font-size: 0.8rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><?= $h ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php if (empty($data['pasantes'])): ?>
                    <tr>
                        <td colspan="7" style="text-align:center; padding: 48px 20px; color: #94a3b8;">
                            <i class="ti ti-users-off" style="font-size: 48px; display:block; margin-bottom: 12px;"></i>
                            No hay pasantes registrados aún.
                        </td>
                    </tr>
                    <?php else: ?>
                    <?php foreach ($data['pasantes'] as $p):
                        $estado    = $p->estado_pasantia ?? 'Sin Asignar';
                        $cfg       = $estadoConfig[$estado] ?? $estadoConfig['Sin Asignar'];
                        $progreso  = (int)($p->progreso_porcentaje ?? 0);
                        $pColor    = $progreso >= 80 ? '#10b981' : ($progreso >= 50 ? '#f59e0b' : '#ef4444');
                        $iniciales = strtoupper(substr($p->nombres ?? '?', 0, 1) . substr($p->apellidos ?? '', 0, 1));
                        $nombre    = htmlspecialchars(($p->apellidos ?? '') . ', ' . ($p->nombres ?? ''));
                    ?>
                    <tr style="border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 16px 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #162660, #3b82f6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                    <?= $iniciales ?>
                                </div>
                                <span style="font-weight: 600; color: #1e293b; font-size: 0.9rem;"><?= $nombre ?></span>
                            </div>
                        </td>
                        <td style="padding: 16px 20px; color: #64748b; font-size: 0.85rem;"><?= htmlspecialchars($p->cedula ?? '—') ?></td>
                        <td style="padding: 16px 20px; color: #64748b; font-size: 0.85rem;"><?= htmlspecialchars($p->institucion_nombre ?? '—') ?></td>
                        <td style="padding: 16px 20px; color: #64748b; font-size: 0.85rem;"><?= htmlspecialchars($p->departamento_nombre ?? '—') ?></td>
                        <td style="padding: 16px 20px; min-width: 140px;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div style="flex: 1; height: 8px; background: #f1f5f9; border-radius: 4px; overflow: hidden;">
                                    <div style="width: <?= $progreso ?>%; height: 100%; background: <?= $pColor ?>; border-radius: 4px; transition: width 0.5s;"></div>
                                </div>
                                <span style="font-size: 0.8rem; font-weight: 700; color: <?= $pColor ?>; min-width: 35px;"><?= $progreso ?>%</span>
                            </div>
                        </td>
                        <td style="padding: 16px 20px;">
                            <span style="background: <?= $cfg['bg'] ?>; color: <?= $cfg['color'] ?>; padding: 5px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 700;">
                                <?= $cfg['label'] ?>
                            </span>
                        </td>
                        <td style="padding: 16px 20px; white-space: nowrap;">
                            <a href="<?= URLROOT ?>/pasantes/show/<?= (int)$p->id ?>" style="background: #eff6ff; border: none; padding: 7px 12px; border-radius: 8px; cursor: pointer; color: #162660; font-size: 0.8rem; text-decoration: none; margin-right: 4px;" title="Ver Kardex">
                                <i class="ti ti-id-badge"></i>
                            </a>
                            <?php if ($estado === 'Sin Asignar'): ?>
                            <button
                                onclick="abrirModalAsignacion(<?= (int)$p->id ?>, '<?= addslashes($nombre) ?>')"
                                style="background: #dcfce7; border: none; padding: 7px 12px; border-radius: 8px; cursor: pointer; color: #16a34a; font-size: 0.8rem;"
                                title="Asignar Pasante">
                                <i class="ti ti-check"></i> Asignar
                            </button>
                            <?php elseif ($estado === 'Activo'): ?>
                            <button
                                onclick="confirmarFinalizar(<?= (int)$p->id ?>, '<?= addslashes($nombre) ?>')"
                                style="background: #fef3c7; border: none; padding: 7px 12px; border-radius: 8px; cursor: pointer; color: #d97706; font-size: 0.8rem;"
                                title="Finalizar Pasantía">
                                <i class="ti ti-flag"></i> Finalizar
                            </button>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                    <?php endif; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /main-content -->

<!-- ===== MODAL DE ASIGNACIÓN ===== -->
<div id="modalAsignacion" style="display:none; position:fixed; inset:0; background:rgba(0,0,0,0.5); z-index:9999; align-items:center; justify-content:center;">
    <div style="background:white; border-radius:20px; padding:36px; width:100%; max-width:480px; box-shadow:0 20px 60px rgba(0,0,0,0.2); animation: fadeInUp 0.3s ease;">
        <div style="display:flex; justify-content:space-between; align-items:center; margin-bottom:24px;">
            <div>
                <h2 style="margin:0; font-size:1.2rem; color:#0f172a; font-weight:700;">
                    <i class="ti ti-user-plus" style="color:#162660; margin-right:8px;"></i>Asignar Pasante
                </h2>
                <p id="modalNombrePasante" style="margin:4px 0 0; color:#64748b; font-size:0.85rem;"></p>
            </div>
            <button onclick="cerrarModal()" style="background:#f1f5f9; border:none; padding:8px; border-radius:8px; cursor:pointer; color:#64748b; font-size:1.1rem;">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <form id="formAsignacion" onsubmit="submitAsignacion(event)">
            <input type="hidden" id="modalPasanteId" name="pasante_id">

            <!-- Departamento -->
            <div style="margin-bottom:18px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:0.9rem;">
                    <i class="ti ti-building-community" style="margin-right:6px;"></i>Departamento *
                </label>
                <select name="departamento_id" id="selectDepartamento" required style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem; color:#374151; box-sizing:border-box; cursor:pointer;">
                    <option value="">Selecciona un departamento...</option>
                    <?php foreach ($data['departamentos'] as $dept): ?>
                    <option value="<?= (int)$dept->id ?>"><?= htmlspecialchars($dept->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- Fecha de inicio -->
            <div style="margin-bottom:18px;">
                <label style="display:block; font-weight:600; color:#374151; margin-bottom:8px; font-size:0.9rem;">
                    <i class="ti ti-calendar" style="margin-right:6px;"></i>Fecha de Inicio *
                </label>
                <input type="date" name="fecha_inicio" id="inputFechaInicio" required
                    value="<?= date('Y-m-d') ?>"
                    style="width:100%; padding:10px 14px; border:1.5px solid #e2e8f0; border-radius:10px; font-size:0.9rem; box-sizing:border-box;">
            </div>

            <!-- Info calculada automáticamente -->
            <div style="background:#f0fdf4; border:1px solid #bbf7d0; border-radius:12px; padding:16px; margin-bottom:24px;">
                <p style="margin:0; font-size:0.85rem; color:#166534;">
                    <i class="ti ti-calculator" style="margin-right:6px;"></i>
                    <strong>Horas meta:</strong> 1,440 hrs (bloqueadas)
                    &nbsp;·&nbsp;
                    <strong>Duración:</strong> 180 días hábiles ≈ 9 meses
                </p>
                <p style="margin:6px 0 0; font-size:0.8rem; color:#15803d;">
                    La fecha de fin se calculará automáticamente saltando sábados y domingos.
                </p>
            </div>

            <div style="display:flex; gap:12px;">
                <button type="button" onclick="cerrarModal()" style="flex:1; padding:12px; border:1.5px solid #e2e8f0; border-radius:10px; background:white; color:#64748b; font-weight:600; cursor:pointer;">
                    Cancelar
                </button>
                <button type="submit" id="btnAsignar" style="flex:2; padding:12px; background:linear-gradient(135deg, #162660, #3b82f6); border:none; border-radius:10px; color:white; font-weight:700; cursor:pointer; display:flex; align-items:center; justify-content:center; gap:8px;">
                    <i class="ti ti-check"></i> Confirmar Asignación
                </button>
            </div>
        </form>
    </div>
</div>

<style>
@keyframes fadeInUp {
    from { opacity: 0; transform: translateY(20px); }
    to   { opacity: 1; transform: translateY(0); }
}
</style>

<script>
// ── Modal de Asignación ──────────────────────────────────────────────
function abrirModalAsignacion(pasanteId, nombre) {
    document.getElementById('modalPasanteId').value = pasanteId;
    document.getElementById('modalNombrePasante').textContent = nombre;
    document.getElementById('inputFechaInicio').value = new Date().toISOString().split('T')[0];
    document.getElementById('selectDepartamento').value = '';
    var modal = document.getElementById('modalAsignacion');
    modal.style.display = 'flex';
    document.body.style.overflow = 'hidden';
    
    // Reinicializar Flatpickr
    if (window.SGPFlatpickr) {
        window.SGPFlatpickr.reinit('#inputFechaInicio');
    }
    
    // Reinicializar Choices.js
    if (window.SGPChoices) {
        window.SGPChoices.reinit('#selectDepartamento');
    }
}

function cerrarModal() {
    document.getElementById('modalAsignacion').style.display = 'none';
    document.body.style.overflow = '';
}

// Cerrar al hacer clic fuera del modal
document.getElementById('modalAsignacion').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});

async function submitAsignacion(e) {
    e.preventDefault();

    var btn = document.getElementById('btnAsignar');
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader"></i> Guardando...';

    var formData = new FormData(document.getElementById('formAsignacion'));

    try {
        var resp = await fetch('<?= URLROOT ?>/pasantes/asignar', {
            method: 'POST',
            body: formData,
        });

        var json = await resp.json();

        if (json.success) {
            cerrarModal();
            await Swal.fire({
                icon: 'success',
                title: '¡Pasante Asignado! 🎉',
                html: `
                    <p style="color:#374151;">${json.message}</p>
                    <div style="background:#f0fdf4;border-radius:10px;padding:12px;margin-top:12px;text-align:left;font-size:0.9rem;">
                        <strong>📅 Fecha de fin estimada:</strong><br>
                        <span style="font-size:1.1rem;color:#16a34a;font-weight:700;">${json.fecha_fin_formato}</span>
                        <br><small style="color:#6b7280;">(180 días hábiles, saltando fines de semana)</small>
                    </div>
                `,
                confirmButtonColor: '#162660',
                confirmButtonText: 'Perfecto',
            });
            // Recargar la página para ver el badge actualizado
            window.location.reload();
        } else {
            Swal.fire({
                icon: 'error',
                title: 'Error',
                text: json.message || 'No se pudo guardar la asignación.',
                confirmButtonColor: '#162660',
            });
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check"></i> Confirmar Asignación';
        }
    } catch (err) {
        Swal.fire({
            icon: 'error',
            title: 'Error de conexión',
            text: 'No se pudo conectar con el servidor. Intenta de nuevo.',
            confirmButtonColor: '#162660',
        });
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check"></i> Confirmar Asignación';
    }
}

// ── Finalizar Pasantía ───────────────────────────────────────────────
function confirmarFinalizar(pasanteId, nombre) {
    Swal.fire({
        icon: 'warning',
        title: '¿Finalizar Pasantía?',
        html: `<p>Estás a punto de marcar la pasantía de <strong>${nombre}</strong> como <strong>Finalizada</strong>. Esta acción no se puede deshacer fácilmente.</p>`,
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
            var resp = await fetch('<?= URLROOT ?>/pasantes/finalizar_pasantia', {
                method: 'POST',
                body: fd,
            });
            var json = await resp.json();

            if (json.success) {
                await Swal.fire({ icon: 'success', title: '¡Finalizado!', text: json.message, confirmButtonColor: '#162660' });
                window.location.reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message, confirmButtonColor: '#162660' });
            }
        } catch(err) {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Intenta de nuevo.', confirmButtonColor: '#162660' });
        }
    });
}

function simularNuevoPasante() {
    window.location.href = '<?= URLROOT ?>/users';
}
</script>
