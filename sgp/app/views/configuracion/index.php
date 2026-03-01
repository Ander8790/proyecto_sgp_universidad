<?php
// Vista Monolítica de Configuración — SGP
if (!defined('APPROOT')) require_once '../app/config/config.php';

// Leer mensajes flash
$flashSuccess = Session::getFlash('success');
$flashError   = Session::getFlash('error');

// Datos del controlador (ya saneados como arrays)
$instituciones = $data['instituciones'] ?? [];
$departamentos = $data['departamentos'] ?? [];
$totalInst     = count($instituciones);
$totalDepto    = count($departamentos);
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP — Configuración del Sistema</title>

    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/variables.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/base.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/animations.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notifications.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sidebar.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/topbar.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/loading.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/modal-universal.css">

    <script>const URLROOT = '<?= URLROOT ?>';</script>
    <script src="<?= URLROOT ?>/js/sweetalert2.min.js"></script>
    <script src="<?= URLROOT ?>/js/notyf.min.js"></script>
    <script src="<?= URLROOT ?>/js/notification-service.js"></script>
    <script src="<?= URLROOT ?>/js/modal-universal.js"></script>

    <style>
    /* === Cards de sección === */
    .cfg-card {
        background: white;
        border-radius: 16px;
        padding: 28px;
        box-shadow: 0 2px 12px rgba(0,0,0,0.06);
        margin-bottom: 24px;
    }
    .cfg-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding-bottom: 16px;
        border-bottom: 1px solid #f1f5f9;
        margin-bottom: 22px;
    }
    .cfg-card-title {
        display: flex;
        align-items: center;
        gap: 10px;
    }
    .cfg-icon-box {
        width: 38px; height: 38px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
    }
    .cfg-count-badge {
        padding: 4px 12px;
        border-radius: 20px;
        font-size: 0.8rem;
        font-weight: 700;
    }

    /* === Ítems de lista === */
    .cfg-list-item {
        display: flex;
        justify-content: space-between;
        align-items: center;
        padding: 14px 0;
        border-bottom: 1px solid #f8fafc;
        gap: 12px;
    }
    .cfg-list-item:last-child { border-bottom: none; }
    .cfg-list-item-info {
        display: flex;
        align-items: center;
        gap: 12px;
        flex: 1;
        min-width: 0;
    }
    .cfg-list-icon {
        width: 40px; height: 40px;
        border-radius: 10px;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        background: linear-gradient(135deg, #eff6ff, #dbeafe);
    }
    .cfg-list-name {
        margin: 0;
        font-weight: 700;
        color: #1e293b;
        font-size: 0.92rem;
        white-space: nowrap;
        overflow: hidden;
        text-overflow: ellipsis;
    }
    .cfg-list-sub {
        margin: 3px 0 0;
        color: #64748b;
        font-size: 0.8rem;
    }
    .cfg-delete-btn {
        width: 34px; height: 34px;
        border-radius: 8px;
        border: none;
        background: #fef2f2;
        color: #ef4444;
        cursor: pointer;
        display: flex; align-items: center; justify-content: center;
        flex-shrink: 0;
        transition: all 0.2s;
    }
    .cfg-delete-btn:hover { background: #fee2e2; transform: scale(1.05); }

    /* === Formulario lateral === */
    .cfg-form-panel {
        background: #f8fafc;
        border-radius: 14px;
        padding: 22px;
        border: 1.5px solid #e2e8f0;
        align-self: start;
    }
    .cfg-form-panel h4 {
        margin: 0 0 18px;
        font-size: 0.95rem;
        font-weight: 700;
        color: #1e293b;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    .cfg-field-label {
        display: block;
        font-size: 0.75rem;
        font-weight: 700;
        color: #64748b;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        margin-bottom: 7px;
    }
    .cfg-input {
        width: 100%;
        padding: 11px 14px;
        border: 2px solid #e5e7eb;
        border-radius: 10px;
        font-size: 0.9rem;
        color: #1e293b;
        background: white;
        box-sizing: border-box;
        transition: border-color 0.2s;
        font-family: inherit;
    }
    .cfg-input:focus { outline: none; border-color: #2563eb; }
    .cfg-submit-btn {
        width: 100%;
        padding: 13px;
        background: linear-gradient(135deg, #172554 0%, #2563eb 100%);
        color: white;
        border: none;
        border-radius: 10px;
        font-size: 0.95rem;
        font-weight: 700;
        cursor: pointer;
        display: flex;
        align-items: center;
        justify-content: center;
        gap: 8px;
        transition: all 0.2s;
        box-shadow: 0 4px 12px rgba(37,99,235,0.3);
    }
    .cfg-submit-btn:hover {
        transform: translateY(-2px);
        box-shadow: 0 8px 20px rgba(37,99,235,0.4);
    }

    /* === DOS COLUMNAS === */
    .cfg-two-col { display: grid; grid-template-columns: 1fr 1fr; gap: 24px; }
    .cfg-main-col { display: grid; grid-template-columns: 1fr 340px; gap: 28px; }

    /* === Empty state === */
    .cfg-empty {
        text-align: center;
        padding: 32px 20px;
        color: #94a3b8;
    }
    .cfg-empty i { font-size: 2.5rem; display: block; margin-bottom: 8px; }

    @media (max-width: 900px) {
        .cfg-two-col, .cfg-main-col { grid-template-columns: 1fr; }
    }
    </style>
</head>
<body>
<div class="wrapper">
    <?php require_once '../app/views/inc/header.php'; ?>
    <?php require_once '../app/views/inc/sidebar.php'; ?>

    <div class="content-wrapper">
        <div class="dashboard-container">

            <!-- ===== BANNER ===== -->
            <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:28px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
                <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
                <div style="position:absolute;bottom:-40px;left:-20px;width:160px;height:160px;background:rgba(255,255,255,0.04);border-radius:50%;"></div>
                <div style="display:flex;align-items:center;gap:16px;z-index:1;">
                    <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:14px;">
                        <i class="ti ti-settings" style="font-size:32px;color:white;"></i>
                    </div>
                    <div>
                        <h1 style="color:white;font-size:1.8rem;font-weight:700;margin:0;">Configuración del Sistema</h1>
                        <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;">
                            <?= $totalDepto ?> departamentos · <?= $totalInst ?> institución<?= $totalInst !== 1 ? 'es' : '' ?> registrada<?= $totalInst !== 1 ? 's' : '' ?>
                        </p>
                    </div>
                </div>
                <div style="z-index:1;">
                    <div style="background:rgba(255,255,255,0.12);border-radius:12px;padding:10px 20px;color:rgba(255,255,255,0.9);font-size:0.85rem;font-weight:600;">
                        <i class="ti ti-building-hospital" style="margin-right:6px;"></i>Instituto de Salud Pública
                    </div>
                </div>
            </div>

            <!-- ===== FILA 1: DOS COLUMNAS ===== -->
            <div class="cfg-two-col">

                <!-- CARD: Departamentos -->
                <div class="cfg-card">
                    <div class="cfg-card-header">
                        <div class="cfg-card-title">
                            <div class="cfg-icon-box" style="background:#f0fdf4;">
                                <i class="ti ti-building-community" style="font-size:1.3rem;color:#059669;"></i>
                            </div>
                            <span style="font-size:1rem;font-weight:700;color:#1e293b;">Departamentos</span>
                        </div>
                        <span class="cfg-count-badge" style="background:#f0fdf4;color:#059669;">
                            <?= $totalDepto ?> activo<?= $totalDepto !== 1 ? 's' : '' ?>
                        </span>
                    </div>

                    <!-- Lista de departamentos desde BD -->
                    <?php if (empty($departamentos)): ?>
                        <div class="cfg-empty">
                            <i class="ti ti-building-community"></i>
                            <p style="margin:0;font-size:0.9rem;">No hay departamentos registrados.</p>
                        </div>
                    <?php else: ?>
                        <?php foreach ($departamentos as $depto): ?>
                        <div class="cfg-list-item">
                            <div class="cfg-list-item-info">
                                <div class="cfg-list-icon" style="background:linear-gradient(135deg,#172554,#1e3a8a);">
                                    <i class="ti ti-building-community" style="font-size:1rem;color:white;"></i>
                                </div>
                                <div>
                                    <p class="cfg-list-name"><?= htmlspecialchars($depto['nombre']) ?></p>
                                    <?php if (!empty($depto['descripcion'])): ?>
                                    <p class="cfg-list-sub"><?= htmlspecialchars($depto['descripcion']) ?></p>
                                    <?php endif; ?>
                                </div>
                            </div>
                            <form method="POST" action="<?= URLROOT ?>/configuracion" style="margin:0;">
                                <input type="hidden" name="accion" value="eliminar_departamento">
                                <input type="hidden" name="id" value="<?= (int)$depto['id'] ?>">
                                <button type="button" class="cfg-delete-btn"
                                    onclick="confirmarEliminar(this, 'departamento', '<?= htmlspecialchars($depto['nombre'], ENT_QUOTES) ?>')"
                                    title="Desactivar departamento">
                                    <i class="ti ti-trash"></i>
                                </button>
                            </form>
                        </div>
                        <?php endforeach; ?>
                    <?php endif; ?>

                    <!-- Mini formulario nuevo departamento -->
                    <div style="margin-top:20px;padding-top:18px;border-top:2px dashed #e2e8f0;">
                        <form method="POST" action="<?= URLROOT ?>/configuracion">
                            <input type="hidden" name="accion" value="agregar_departamento">
                            <div style="display:grid;grid-template-columns:1fr 1fr;gap:12px;margin-bottom:12px;">
                                <div>
                                    <label class="cfg-field-label">Nombre *</label>
                                    <input type="text" name="nombre" class="cfg-input" placeholder="Ej: Informática" required>
                                </div>
                                <div>
                                    <label class="cfg-field-label">Descripción</label>
                                    <input type="text" name="descripcion" class="cfg-input" placeholder="Opcional">
                                </div>
                            </div>
                            <button type="submit" class="cfg-submit-btn" style="padding:10px;">
                                <i class="ti ti-plus"></i> Agregar Departamento
                            </button>
                        </form>
                    </div>
                </div>

                <!-- CARD: Datos de la Institución (lectura) -->
                <div class="cfg-card">
                    <div class="cfg-card-header">
                        <div class="cfg-card-title">
                            <div class="cfg-icon-box" style="background:#eff6ff;">
                                <i class="ti ti-building-hospital" style="font-size:1.3rem;color:#2563eb;"></i>
                            </div>
                            <span style="font-size:1rem;font-weight:700;color:#1e293b;">Datos de la Institución</span>
                        </div>
                    </div>
                    <?php
                    $campos = [
                        ['label' => 'Nombre', 'value' => 'Instituto de Salud Pública (ISP)'],
                        ['label' => 'RIF', 'value' => 'G-20000123-4'],
                        ['label' => 'Estado', 'value' => 'Bolívar'],
                        ['label' => 'Ciudad', 'value' => 'Ciudad Bolívar'],
                        ['label' => 'Dirección', 'value' => 'Av. Paseo Caroní, Torre ISP, Piso 3'],
                    ];
                    foreach ($campos as $campo): ?>
                    <div style="margin-bottom:16px;">
                        <label class="cfg-field-label"><?= $campo['label'] ?></label>
                        <input type="text" value="<?= htmlspecialchars($campo['value']) ?>" readonly
                            style="width:100%;padding:11px 14px;border:2px solid #e5e7eb;border-radius:10px;font-size:0.9rem;color:#64748b;background:#f8fafc;box-sizing:border-box;font-family:inherit;">
                    </div>
                    <?php endforeach; ?>
                </div>

            </div><!-- /cfg-two-col -->

            <!-- ===== CARD: Escuelas Técnicas Aliadas ===== -->
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <div class="cfg-card-title">
                        <div class="cfg-icon-box" style="background:#eff6ff;">
                            <i class="ti ti-school" style="font-size:1.3rem;color:#2563eb;"></i>
                        </div>
                        <div>
                            <p style="margin:0;font-size:1rem;font-weight:700;color:#1e293b;">Escuelas Técnicas Aliadas</p>
                            <p style="margin:2px 0 0;color:#64748b;font-size:0.82rem;">Instituciones de donde provienen los pasantes · Meta: 1440 horas</p>
                        </div>
                    </div>
                    <span class="cfg-count-badge" style="background:#eff6ff;color:#2563eb;">
                        <?= $totalInst ?> institución<?= $totalInst !== 1 ? 'es' : '' ?>
                    </span>
                </div>

                <div class="cfg-main-col">

                    <!-- Listado de instituciones -->
                    <div>
                        <?php if (empty($instituciones)): ?>
                            <div class="cfg-empty">
                                <i class="ti ti-school"></i>
                                <p style="margin:0;font-size:0.9rem;">No hay escuelas registradas aún.</p>
                                <p style="margin:6px 0 0;font-size:0.8rem;">Agrega la primera usando el formulario.</p>
                            </div>
                        <?php else: ?>
                            <?php foreach ($instituciones as $inst): ?>
                            <div class="cfg-list-item">
                                <div class="cfg-list-item-info">
                                    <div class="cfg-list-icon">
                                        <i class="ti ti-building-factory-2" style="font-size:1.2rem;color:#2563eb;"></i>
                                    </div>
                                    <div>
                                        <p class="cfg-list-name"><?= htmlspecialchars($inst['nombre']) ?></p>
                                        <p class="cfg-list-sub">
                                            <i class="ti ti-map-pin" style="margin-right:3px;"></i><?= htmlspecialchars($inst['direccion']) ?>
                                            <span style="margin-left:10px;background:#eff6ff;color:#2563eb;padding:2px 8px;border-radius:6px;font-size:0.75rem;font-weight:600;">1440 horas</span>
                                        </p>
                                    </div>
                                </div>
                                <form method="POST" action="<?= URLROOT ?>/configuracion" style="margin:0;">
                                    <input type="hidden" name="accion" value="eliminar_institucion">
                                    <input type="hidden" name="id" value="<?= (int)$inst['id'] ?>">
                                    <button type="button" class="cfg-delete-btn"
                                        onclick="confirmarEliminar(this, 'institución', '<?= htmlspecialchars($inst['nombre'], ENT_QUOTES) ?>')"
                                        title="Eliminar institución">
                                        <i class="ti ti-trash"></i>
                                    </button>
                                </form>
                            </div>
                            <?php endforeach; ?>
                        <?php endif; ?>
                    </div>

                    <!-- Formulario nueva institución -->
                    <div class="cfg-form-panel">
                        <h4>
                            <div style="width:28px;height:28px;background:linear-gradient(135deg,#172554,#2563eb);border-radius:7px;display:flex;align-items:center;justify-content:center;">
                                <i class="ti ti-plus" style="color:white;font-size:0.85rem;"></i>
                            </div>
                            Nueva Escuela Técnica
                        </h4>
                        <form method="POST" action="<?= URLROOT ?>/configuracion">
                            <input type="hidden" name="accion" value="agregar_institucion">

                            <div style="margin-bottom:14px;">
                                <label class="cfg-field-label">Nombre *</label>
                                <input type="text" name="nombre" class="cfg-input" placeholder="Ej: E.T.C Juan Bautista González" required>
                            </div>
                            <div style="margin-bottom:18px;">
                                <label class="cfg-field-label">Dirección / Ciudad *</label>
                                <input type="text" name="direccion" class="cfg-input" placeholder="Ciudad Bolívar" required>
                            </div>
                            <div style="background:#eff6ff;border-radius:10px;padding:12px;margin-bottom:16px;display:flex;align-items:center;gap:8px;">
                                <i class="ti ti-info-circle" style="color:#2563eb;font-size:1.1rem;flex-shrink:0;"></i>
                                <span style="font-size:0.8rem;color:#1e40af;font-weight:500;">
                                    Las escuelas técnicas tienen una meta fija de <strong>1440 horas</strong> (≈9 meses).
                                </span>
                            </div>
                            <button type="submit" class="cfg-submit-btn">
                                <i class="ti ti-check"></i> Registrar Institución
                            </button>
                        </form>
                    </div>

                </div><!-- /cfg-main-col -->
            </div><!-- /cfg-card -->

            <!-- ===== CARD: Configuración del Kiosco ===== -->
            <div class="cfg-card">
                <div class="cfg-card-header">
                    <div class="cfg-card-title">
                        <div class="cfg-icon-box" style="background:#fef3c7;">
                            <i class="ti ti-device-desktop" style="font-size:1.3rem;color:#d97706;"></i>
                        </div>
                        <div>
                            <span style="font-size:1rem;font-weight:700;color:#1e293b;">Configuración del Kiosco</span>
                            <p style="margin:2px 0 0;color:#64748b;font-size:0.82rem;">Registro de asistencia de pasantes</p>
                        </div>
                    </div>
                </div>

                <div style="display:grid;grid-template-columns:1fr 1fr;gap:20px;">
                    <!-- Toggle Kiosco -->
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;border:1px solid #f1f5f9;">
                        <div style="display:flex;align-items:center;justify-content:space-between;margin-bottom:12px;">
                            <div>
                                <p style="margin:0;font-weight:700;color:#1e293b;font-size:0.92rem;">Estado del Kiosco</p>
                                <p style="margin:3px 0 0;color:#64748b;font-size:0.8rem;">Habilitar o deshabilitar el acceso</p>
                            </div>
                            <label style="position:relative;display:inline-block;width:52px;height:28px;cursor:pointer;">
                                <input type="checkbox" id="kioscoToggle" checked style="opacity:0;width:0;height:0;">
                                <span style="position:absolute;top:0;left:0;right:0;bottom:0;background:#e2e8f0;border-radius:28px;transition:0.3s;" id="kioscoToggleTrack"></span>
                                <span style="position:absolute;top:3px;left:3px;width:22px;height:22px;background:#fff;border-radius:50%;transition:0.3s;box-shadow:0 2px 4px rgba(0,0,0,0.15);" id="kioscoToggleDot"></span>
                            </label>
                        </div>
                        <div style="background:#d1fae5;border-radius:10px;padding:10px 14px;display:flex;align-items:center;gap:8px;" id="kioscoStatusBadge">
                            <i class="ti ti-circle-check" style="color:#059669;font-size:1.1rem;"></i>
                            <span style="font-size:0.82rem;color:#065f46;font-weight:600;" id="kioscoStatusText">Kiosco activo y operativo</span>
                        </div>
                    </div>

                    <!-- Reset PIN -->
                    <div style="background:#f8fafc;border-radius:14px;padding:20px;border:1px solid #f1f5f9;">
                        <p style="margin:0 0 4px;font-weight:700;color:#1e293b;font-size:0.92rem;">Restablecer PIN de Pasante</p>
                        <p style="margin:0 0 16px;color:#64748b;font-size:0.8rem;">Genera un nuevo PIN de 4 dígitos aleatorio</p>
                        <button onclick="SGPModal.buscar()" style="width:100%;padding:13px;background:linear-gradient(135deg,#f59e0b,#d97706);color:white;border:none;border-radius:12px;font-size:0.9rem;font-weight:700;cursor:pointer;display:flex;align-items:center;justify-content:center;gap:8px;transition:all 0.2s;box-shadow:0 4px 12px rgba(245,158,11,0.3);font-family:inherit;">
                            <i class="ti ti-key" style="font-size:1.1rem;"></i> Buscar Pasante y Restablecer PIN
                        </button>
                        <div style="background:#fef3c7;border-radius:10px;padding:10px 14px;margin-top:12px;display:flex;align-items:center;gap:8px;">
                            <i class="ti ti-info-circle" style="color:#92400e;font-size:1rem;flex-shrink:0;"></i>
                            <span style="font-size:0.78rem;color:#92400e;font-weight:500;">El nuevo PIN se mostrará una sola vez.</span>
                        </div>
                    </div>
                </div>
            </div><!-- /kiosco card -->

        </div><!-- /dashboard-container -->
    </div><!-- /content-wrapper -->

    <div id="sidebarOverlay" class="sidebar-overlay"></div>
</div><!-- /wrapper -->

<!-- Scripts -->
<script src="<?= URLROOT ?>/js/sidebar.js"></script>
<script src="<?= URLROOT ?>/js/notifications.js?v=2"></script>

<script>
    // ── Mostrar flash messages ──────────────────────────────
    <?php if ($flashSuccess): ?>
    NotificationService.success('<?= addslashes($flashSuccess) ?>');
    <?php endif; ?>
    <?php if ($flashError): ?>
    Swal.fire({
        icon: 'warning',
        title: 'Aviso',
        text: '<?= addslashes($flashError) ?>',
        confirmButtonColor: '#162660',
        confirmButtonText: 'Entendido'
    });
    <?php endif; ?>

    // ── Confirmar eliminación con SweetAlert ───────────────
    function confirmarEliminar(btn, tipo, nombre) {
        Swal.fire({
            icon: 'warning',
            title: '¿Eliminar ' + tipo + '?',
            html: '<strong>' + nombre + '</strong><br><small style="color:#64748b;">Esta acción no se puede deshacer fácilmente.</small>',
            showCancelButton: true,
            confirmButtonColor: '#dc2626',
            cancelButtonColor: '#6b7280',
            confirmButtonText: '<i class="ti ti-trash" style="margin-right:4px;"></i>Sí, eliminar',
            cancelButtonText: 'Cancelar'
        }).then(function(result) {
            if (result.isConfirmed) {
                // Submittear el form padre del botón
                btn.closest('form').submit();
            }
        });
    }

    // ── Fix resize para sidebar ────────────────────────────
    document.addEventListener('DOMContentLoaded', function() {
        setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 300);
        var toggleBtn = document.getElementById('sidebarToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', function() {
                setTimeout(function() { window.dispatchEvent(new Event('resize')); }, 300);
            });
        }
    });
</script>
</body>
</html>
