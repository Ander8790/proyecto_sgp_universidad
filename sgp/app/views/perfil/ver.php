<?php
// Ensure we have user data
if (!isset($user) || empty($user)) {
    header('Location: ' . URLROOT . '/dashboard');
    exit;
}
?>

<div class="dashboard-container" style="margin-top: 24px;">

<div class="container-fluid mt-4">
    <div class="row" style="display: flex; gap: 24px; flex-wrap: wrap;">
        <!-- Left Column: Profile Summary -->
        <div class="col-md-4" style="flex: 1; min-width: 300px; max-width: 400px;">
            <div class="smart-card" style="text-align: center;">
                <!-- Avatar -->
                <div style="width: 120px; height: 120px; background: var(--color-primary); border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px; font-size: 48px; color: white; font-weight: 700;">
                    <?= strtoupper(substr($user['nombres'] ?? $user['correo'], 0, 1)) ?>
                </div>
                
                <!-- Name -->
                <h3 style="font-size: 1.25rem; color: var(--color-primary); font-weight: 700; margin-bottom: 8px;">
                    <?= htmlspecialchars(($user['nombres'] ?? '') . ' ' . ($user['apellidos'] ?? '')) ?: 'Usuario' ?>
                </h3>
                
                <!-- Role Badge -->
                <span style="display: inline-block; background: rgba(22, 38, 96, 0.1); color: var(--color-primary); padding: 6px 16px; border-radius: 20px; font-size: 0.85rem; font-weight: 600; margin-bottom: 16px;">
                    <?= htmlspecialchars($user['rol_nombre'] ?? 'Sin rol') ?>
                </span>
                
                <!-- Status -->
                <div style="display: flex; align-items: center; justify-content: center; gap: 6px; margin-bottom: 24px;">
                    <i class="fas fa-check-circle" style="color: #10B981; font-size: 16px;"></i>
                    <span style="color: #059669; font-size: 0.9rem; font-weight: 600;">
                        <?= ucfirst($user['estado'] ?? 'activo') ?>
                    </span>
                </div>
                
                <!-- Divider -->
                <div style="border-top: 1px solid #E5E7EB; padding-top: 20px; text-align: left;">
                    <!-- Email -->
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; margin-bottom: 4px;">
                            <i class="fas fa-envelope" style="color: var(--color-primary); font-size: 14px; margin-right: 8px;"></i>
                            <span style="font-size: 0.7rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px;">Correo</span>
                        </div>
                        <div style="color: var(--color-primary); font-weight: 500; font-size: 0.9rem; word-break: break-word;">
                            <?= htmlspecialchars($user['correo']) ?>
                        </div>
                    </div>
                    
                    <?php if (!empty($user['departamento_nombre'])): ?>
                    <!-- Department -->
                    <div style="margin-bottom: 16px;">
                        <div style="display: flex; align-items: center; margin-bottom: 4px;">
                            <i class="fas fa-building" style="color: var(--color-primary); font-size: 14px; margin-right: 8px;"></i>
                            <span style="font-size: 0.7rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px;">Departamento</span>
                        </div>
                        <div style="color: var(--color-primary); font-weight: 500; font-size: 0.9rem;">
                            <?= htmlspecialchars($user['departamento_nombre']) ?>
                        </div>
                    </div>
                    <?php endif; ?>
                    
                    <!-- Member Since -->
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 4px;">
                            <i class="fas fa-calendar-alt" style="color: var(--color-primary); font-size: 14px; margin-right: 8px;"></i>
                            <span style="font-size: 0.7rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px;">Miembro desde</span>
                        </div>
                        <div style="color: var(--color-primary); font-weight: 500; font-size: 0.9rem;">
                            <?= date('d/m/Y', strtotime($user['created_at'])) ?>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Right Column: Detailed Info -->
        <div class="col-md-8" style="flex: 2; min-width: 300px;">
            <div class="smart-card">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #E5E7EB;">
                    <h3 style="font-size: 1.2rem; color: var(--color-primary); font-weight: 700; margin: 0;">
                        <i class="fas fa-user mr-1" style="margin-right: 8px;"></i> Datos Personales
                    </h3>
                    <button class="btn-primary" style="width: auto; padding: 8px 16px; font-size: 0.9rem;" onclick="openEditModal()">
                        <i class="ti ti-edit"></i> Editar
                    </button>
                </div>
                
                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(250px, 1fr)); gap: 24px;">
                    <!-- Cédula -->
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <i class="fas fa-id-card" style="color: var(--color-primary); margin-right: 8px;"></i>
                            <span style="font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Cédula de Identidad</span>
                        </div>
                        <h5 style="font-weight: 600; margin: 0; color: #1F2937; font-size: 1.05rem;">
                            <?= htmlspecialchars($user['cedula'] ?? 'No especificado') ?>
                        </h5>
                    </div>
                    
                    <!-- Teléfono -->
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <i class="fas fa-phone-alt" style="color: var(--color-primary); margin-right: 8px;"></i>
                            <span style="font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Teléfono</span>
                        </div>
                        <h5 style="font-weight: 600; margin: 0; color: #1F2937; font-size: 1.05rem;">
                            <?= htmlspecialchars($user['telefono'] ?? 'No especificado') ?>
                        </h5>
                    </div>
                    
                    <!-- Cargo -->
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <i class="fas fa-briefcase" style="color: var(--color-primary); margin-right: 8px;"></i>
                            <span style="font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Cargo</span>
                        </div>
                        <h5 style="font-weight: 600; margin: 0; color: #1F2937; font-size: 1.05rem;">
                            <?= htmlspecialchars($user['cargo'] ?? 'No especificado') ?>
                        </h5>
                    </div>
                    
                    <!-- Teléfono -->
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <i class="fas fa-venus-mars" style="color: var(--color-primary); margin-right: 8px;"></i>
                            <span style="font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Género</span>
                        </div>
                        <h5 style="font-weight: 600; margin: 0; color: #1F2937; font-size: 1.05rem;">
                            <?= isset($user['genero']) ? ($user['genero'] == 'M' ? 'Masculino' : 'Femenino') : 'No especificado' ?>
                        </h5>
                    </div>
                    
                    <!-- Fecha de Nacimiento -->
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <i class="fas fa-birthday-cake" style="color: var(--color-primary); margin-right: 8px;"></i>
                            <span style="font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Fecha de Nacimiento</span>
                        </div>
                        <h5 style="font-weight: 600; margin: 0; color: #1F2937; font-size: 1.05rem;">
                            <?= isset($user['fecha_nacimiento']) ? date('d/m/Y', strtotime($user['fecha_nacimiento'])) : 'No especificado' ?>
                        </h5>
                    </div>
                    
                    <!-- Dirección (Full Width) -->
                    <div style="grid-column: 1 / -1;">
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <i class="fas fa-map-marker-alt" style="color: var(--color-primary); margin-right: 8px;"></i>
                            <span style="font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Dirección</span>
                        </div>
                        <h5 style="font-weight: 600; margin: 0; color: #1F2937; font-size: 1.05rem;">
                            <?= htmlspecialchars($user['direccion'] ?? 'No especificado') ?>
                        </h5>
                    </div>

                    <?php if (Session::get('role_id') == 3 && !empty($user['institucion_procedencia'])): // Pasante ?>
                    <div style="grid-column: 1 / -1;">
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <i class="fas fa-university" style="color: var(--color-primary); margin-right: 8px;"></i>
                            <span style="font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Institución de Procedencia</span>
                        </div>
                        <h5 style="font-weight: 600; margin: 0; color: #1F2937; font-size: 1.05rem;">
                            <?= htmlspecialchars($user['institucion_procedencia']) ?>
                        </h5>
                    </div>
                    <?php endif; ?>
                </div>
            </div>
        </div>
    </div>
</div>

<!-- Edit Profile Modal -->
<div id="editModal" class="modal">
    <div class="modal-content" style="max-width: 700px;">
        <div class="modal-header">
            <h2 class="modal-title">Editar Mi Perfil</h2>
            <button class="modal-close" onclick="closeEditModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        
        <form id="editProfileForm" action="<?= URLROOT ?>/perfil/actualizar" method="POST">
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label>Cédula *</label>
                    <input type="text" name="cedula" id="edit_cedula" required pattern="[0-9]+" 
                           placeholder="V-12234567"
                           value="<?= htmlspecialchars($user['cedula'] ?? '') ?>" class="input-modern">
                </div>
                
                <div class="form-group">
                    <label>Teléfono *</label>
                    <input type="text" name="telefono" id="edit_telefono" required 
                           placeholder="0414-1231234"
                           value="<?= htmlspecialchars($user['telefono'] ?? '') ?>" class="input-modern">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label>Nombres *</label>
                    <input type="text" name="nombres" id="edit_nombres" required 
                           placeholder="Ingrese sus nombres"
                           value="<?= htmlspecialchars($user['nombres'] ?? '') ?>" class="input-modern">
                </div>
                
                <div class="form-group">
                    <label>Apellidos *</label>
                    <input type="text" name="apellidos" id="edit_apellidos" required 
                           placeholder="Ingrese sus apellidos"
                           value="<?= htmlspecialchars($user['apellidos'] ?? '') ?>" class="input-modern">
                </div>
            </div>
            
            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 16px;">
                <div class="form-group">
                    <label>Cargo *</label>
                    <input type="text" name="cargo" id="edit_cargo" required 
                           placeholder="Ej: Analista de Soporte"
                           value="<?= htmlspecialchars($user['cargo'] ?? '') ?>" class="input-modern">
                </div>
                <div class="form-group">
                    <label>Género *</label>
                    <select name="genero" id="edit_genero" required class="input-modern">
                        <option value="">Seleccione...</option>
                        <option value="M" <?= ($user['genero'] ?? '') == 'M' ? 'selected' : '' ?>>Masculino</option>
                        <option value="F" <?= ($user['genero'] ?? '') == 'F' ? 'selected' : '' ?>>Femenino</option>
                    </select>
                </div>
                
                <div class="form-group">
                    <label>Fecha de Nacimiento *</label>
                    <input type="date" name="fecha_nacimiento" id="edit_fecha_nacimiento" required 
                           value="<?= htmlspecialchars($user['fecha_nacimiento'] ?? '') ?>" class="input-modern">
                </div>
            </div>
            
            <div class="form-group" style="margin-bottom: 24px;">
                <label>Dirección *</label>
                <textarea name="direccion" id="edit_direccion" required class="input-modern" 
                          placeholder="Ej: Av. Táchira, Casa Nro 5..."
                          style="min-height: 80px; resize: vertical;"><?= htmlspecialchars($user['direccion'] ?? '') ?></textarea>
            </div>
            
            <?php if (Session::get('role_id') == 3): // Pasante ?>
            <div class="form-group" style="margin-bottom: 24px;">
                <label>Institución de Procedencia *</label>
                <input type="text" name="institucion_procedencia" id="edit_institucion" required 
                       value="<?= htmlspecialchars($user['institucion_procedencia'] ?? '') ?>" class="input-modern">
            </div>
            <?php endif; ?>
            
            <button type="submit" class="btn-primary">
                <i class="ti ti-check"></i> Guardar Cambios
            </button>
        </form>
    </div>
</div>

<style>
    .modal {
        display: none;
        position: fixed;
        z-index: 1000;
        left: 0;
        top: 0;
        width: 100%;
        height: 100%;
        background: rgba(0, 0, 0, 0.5);
        animation: fadeIn 0.3s;
    }
    
    .modal.active {
        display: flex;
        align-items: center;
        justify-content: center;
    }
    
    .modal-content {
        background: white;
        border-radius: 16px;
        padding: 32px;
        max-width: 500px;
        width: 90%;
        max-height: 90vh;
        overflow-y: auto;
        animation: slideUp 0.3s;
    }
    
    @keyframes fadeIn {
        from { opacity: 0; }
        to { opacity: 1; }
    }
    
    @keyframes slideUp {
        from { transform: translateY(20px); opacity: 0; }
        to { transform: translateY(0); opacity: 1; }
    }
    
    .modal-header {
        display: flex;
        justify-content: space-between;
        align-items: center;
        margin-bottom: 24px;
    }
    
    .modal-title {
        font-size: 1.5rem;
        font-weight: 700;
        color: var(--color-primary);
    }
    
    .modal-close {
        background: none;
        border: none;
        font-size: 24px;
        cursor: pointer;
        color: var(--text-body);
        padding: 0;
        width: 32px;
        height: 32px;
        display: flex;
        align-items: center;
        justify-content: center;
        border-radius: 50%;
        transition: background 0.2s;
    }
    
    .modal-close:hover {
        background: #F3F4F6;
    }
    
    .form-group {
        margin-bottom: 16px;
    }
    
    .form-group label {
        display: block;
        font-weight: 600;
        color: var(--color-primary);
        margin-bottom: 8px;
        font-size: 14px;
    }
</style>

</div><!-- .dashboard-container -->

<!-- Notyf Library -->
<link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
<script src="<?= URLROOT ?>/js/notyf.min.js"></script>

<script>
    // Initialize Notyf
    const notyf = new Notyf({
        duration: 4000,
        position: {
            x: 'right',
            y: 'top'
        },
        dismissible: true
    });

    // Show flash messages with Notyf
    <?php if (Session::hasFlash('success')): ?>
        notyf.success('<?= addslashes(Session::getFlash('success')) ?>');
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
        notyf.error('<?= addslashes(Session::getFlash('error')) ?>');
    <?php endif; ?>

    // Edit Modal Functions
    function openEditModal() {
        document.getElementById('editModal').classList.add('active');
    }
    
    function closeEditModal() {
        document.getElementById('editModal').classList.remove('active');
    }
    
    // Close modal on outside click
    window.onclick = function(event) {
        const modal = document.getElementById('editModal');
        if (event.target === modal) {
            closeEditModal();
        }
    }
</script>

