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
                <?php 
                // ALERTA: Perfil Incompleto (solo para pasantes)
                if (Session::get('role_id') == 3) {
                    $incompleto = empty($user['telefono']) || empty($user['institucion_procedencia']);
                    if ($incompleto):
                ?>
                <div class="alert alert-warning" style="background: #FEF3C7; border: 1px solid #FCD34D; border-radius: 12px; padding: 14px 16px; margin-bottom: 20px; display: flex; align-items: center; gap: 12px;">
                    <i class="ti ti-alert-circle" style="color: #F59E0B; font-size: 24px;"></i>
                    <div>
                        <strong style="color: #92400E; font-size: 14px; display: block; margin-bottom: 4px;">⚠️ Perfil Incompleto</strong>
                        <span style="color: #78350F; font-size: 13px;">Actualiza tus datos de contacto e institución de procedencia para completar tu perfil.</span>
                    </div>
                </div>
                <?php endif; } ?>
                
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-top: 1px solid #E5E7EB;">
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
                    
                    <!-- Cargo (SOLO para Admin/Tutor, NO para Pasantes) -->
                    <?php if (Session::get('role_id') != 3): ?>
                    <div>
                        <div style="display: flex; align-items: center; margin-bottom: 6px;">
                            <i class="fas fa-briefcase" style="color: var(--color-primary); margin-right: 8px;"></i>
                            <span style="font-size: 0.75rem; color: #6B7280; text-transform: uppercase; letter-spacing: 0.5px; font-weight: 600;">Cargo</span>
                        </div>
                        <h5 style="font-weight: 600; margin: 0; color: #1F2937; font-size: 1.05rem;">
                            <?= htmlspecialchars($user['cargo'] ?? 'No especificado') ?>
                        </h5>
                    </div>
                    <?php endif; ?>
                    
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

            <!-- Tarjeta de Seguridad -->
            <div class="smart-card" style="margin-top: 24px;">
                <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 24px; padding-bottom: 16px; border-bottom: 1px solid #E5E7EB;">
                    <h3 style="font-size: 1.2rem; color: var(--color-primary); font-weight: 700; margin: 0;">
                        <i class="fas fa-shield-alt mr-1" style="margin-right: 8px;"></i> Seguridad
                    </h3>
                </div>

                <div style="display: grid; grid-template-columns: repeat(auto-fit, minmax(300px, 1fr)); gap: 20px;">
                    <!-- Cambiar Contraseña -->
                    <div style="background: #F9FAFB; padding: 20px; border-radius: 12px; border: 1px solid #E5E7EB;">
                        <div style="display: flex; align-items: center; margin-bottom: 12px;">
                            <i class="fas fa-key" style="color: var(--color-primary); font-size: 24px; margin-right: 12px;"></i>
                            <div>
                                <h5 style="margin: 0; font-weight: 600; color: var(--color-primary);">Contraseña</h5>
                                <p style="margin: 0; font-size: 0.85rem; color: #6B7280;">Actualiza tu contraseña periódicamente</p>
                            </div>
                        </div>
                        <button class="btn-primary" style="width: 100%; margin-top: 12px;" onclick="openPasswordModal()">
                            <i class="ti ti-lock"></i> Cambiar Contraseña
                        </button>
                    </div>

                    <!-- Preguntas de Seguridad -->
                    <div style="background: #F9FAFB; padding: 20px; border-radius: 12px; border: 1px solid #E5E7EB;">
                        <div style="display: flex; align-items: center; margin-bottom: 12px;">
                            <i class="fas fa-question-circle" style="color: var(--color-primary); font-size: 24px; margin-right: 12px;"></i>
                            <div>
                                <h5 style="margin: 0; font-weight: 600; color: var(--color-primary);">Preguntas de Seguridad</h5>
                                <p style="margin: 0; font-size: 0.85rem; color: #6B7280;">Para recuperación de cuenta</p>
                            </div>
                        </div>
                        <a href="<?= URLROOT ?>/perfil/gestionar_preguntas" class="btn-primary" style="width: 100%; margin-top: 12px; display: block; text-align: center; text-decoration: none;">
                            <i class="ti ti-settings"></i> Gestionar Preguntas
                        </a>
                    </div>
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
                    <label>Cédula * <small style="color: #6B7280;">(No editable)</small></label>
                    <input type="text" name="cedula" id="edit_cedula" readonly
                           placeholder="V-12234567"
                           value="<?= htmlspecialchars($user['cedula'] ?? '') ?>" 
                           class="input-modern" 
                           style="background-color: #F3F4F6; cursor: not-allowed; color: #6B7280;">
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
                <?php if (Session::get('role_id') != 3): // Solo Admin/Tutor ?>
                <div class="form-group">
                    <label>Cargo *</label>
                    <input type="text" name="cargo" id="edit_cargo" required 
                           placeholder="Ej: Analista de Soporte"
                           value="<?= htmlspecialchars($user['cargo'] ?? '') ?>" class="input-modern">
                </div>
                <?php endif; ?>
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

<!-- Change Password Modal -->
<div id="passwordModal" class="modal">
    <div class="modal-content" style="max-width: 500px;">
        <div class="modal-header">
            <h2 class="modal-title">Cambiar Contraseña</h2>
            <button class="modal-close" onclick="closePasswordModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        
        <form id="changePasswordForm" action="<?= URLROOT ?>/perfil/cambiar_password" method="POST">
            <!-- Contraseña Actual con Floating Label y Toggle -->
            <div class="form-group has-toggle" style="margin-bottom: 24px;">
                <input type="password" 
                       name="password_actual" 
                       id="password_actual" 
                       class="input-modern" 
                       placeholder=" " 
                       required 
                       style="padding-right: 48px;">
                <label for="password_actual" class="label-floating">
                    <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Contraseña Actual
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('password_actual', this)"></i>
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
            </div>

            <!-- Nueva Contraseña con Floating Label, Toggle y Barra de Fortaleza -->
            <div class="form-group has-toggle" style="margin-bottom: 12px;">
                <input type="password" 
                       name="password_nueva" 
                       id="password_nueva" 
                       class="input-modern" 
                       placeholder=" " 
                       required 
                       style="padding-right: 48px;"
                       minlength="8"
                       oninput="updatePasswordStrengthWithRequirements(this, document.getElementById('strength-bar'), document.getElementById('strength-text'))">
                <label for="password_nueva" class="label-floating">
                    <i class="ti ti-key" style="margin-right: 8px; font-size: 18px;"></i>Nueva Contraseña
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('password_nueva', this)"></i>
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
            </div>
            
            <!-- Barra de Fortaleza de Contraseña -->
            <div style="margin-bottom: 20px;">
                <div class="password-strength-bar" id="strength-bar"></div>
                <div id="strength-text" class="password-strength-text" style="text-align: right; font-size: 0.85rem; margin-top: 4px; font-weight: 600;"></div>
            </div>

            <!-- Confirmar Contraseña con Floating Label y Toggle -->
            <div class="form-group has-toggle" style="margin-bottom: 24px;">
                <input type="password" 
                       name="password_confirmar" 
                       id="password_confirmar" 
                       class="input-modern" 
                       placeholder=" " 
                       required 
                       style="padding-right: 48px;"
                       minlength="8"
                       oninput="validatePasswordMatch(document.getElementById('password_nueva'), this)">
                <label for="password_confirmar" class="label-floating">
                    <i class="ti ti-check" style="margin-right: 8px; font-size: 18px;"></i>Confirmar Nueva Contraseña
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('password_confirmar', this)"></i>
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
            </div>

            <!-- Checklist Visual de Requisitos -->
            <div style="background: linear-gradient(135deg, #EFF6FF 0%, #DBEAFE 100%); border: 1px solid #BFDBFE; border-radius: 12px; padding: 16px; margin-bottom: 24px;">
                <h6 style="color: #1E40AF; margin: 0 0 12px 0; font-size: 0.9rem; font-weight: 600;">
                    <i class="ti ti-info-circle" style="margin-right: 6px;"></i> Requisitos de Seguridad
                </h6>
                <ul style="margin: 0; padding: 0; list-style: none; color: #1E3A8A; font-size: 0.85rem; line-height: 2;">
                    <li id="req-length" class="password-requirement">
                        <i class="ti ti-circle-check requirement-icon"></i>
                        <span>Mínimo 8 caracteres</span>
                    </li>
                    <li id="req-uppercase" class="password-requirement">
                        <i class="ti ti-circle-check requirement-icon"></i>
                        <span>Al menos una letra mayúscula</span>
                    </li>
                    <li id="req-lowercase" class="password-requirement">
                        <i class="ti ti-circle-check requirement-icon"></i>
                        <span>Al menos una letra minúscula</span>
                    </li>
                    <li id="req-number" class="password-requirement">
                        <i class="ti ti-circle-check requirement-icon"></i>
                        <span>Al menos un número</span>
                    </li>
                    <li id="req-special" class="password-requirement">
                        <i class="ti ti-circle-check requirement-icon"></i>
                        <span>Al menos un carácter especial (!@#$%^&*)</span>
                    </li>
                </ul>
            </div>

            <button type="submit" class="btn-primary" style="width: 100%;">
                <i class="ti ti-check"></i> Cambiar Contraseña
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
    
    /* Password Strength Bar */
    .password-strength-bar {
        height: 6px;
        background: #E5E7EB;
        border-radius: 3px;
        overflow: hidden;
        position: relative;
        transition: all 0.3s ease;
    }
    
    .password-strength-bar::before {
        content: '';
        position: absolute;
        top: 0;
        left: 0;
        height: 100%;
        width: 0;
        transition: all 0.4s ease;
        border-radius: 3px;
    }
    
    .password-strength-bar.weak::before {
        width: 33%;
        background: linear-gradient(90deg, #EF4444, #DC2626);
    }
    
    .password-strength-bar.medium::before {
        width: 66%;
        background: linear-gradient(90deg, #F59E0B, #D97706);
    }
    
    .password-strength-bar.strong::before {
        width: 100%;
        background: linear-gradient(90deg, #10B981, #059669);
    }
    
    .password-strength-text {
        font-size: 0.85rem;
        font-weight: 600;
        margin-top: 4px;
    }
    
    .password-strength-text.weak {
        color: #DC2626;
    }
    
    .password-strength-text.medium {
        color: #D97706;
    }
    
    .password-strength-text.strong {
        color: #059669;
    }
    
    /* Password Requirements Checklist */
    .password-requirement {
        display: flex;
        align-items: center;
        gap: 8px;
        color: #6B7280;
        transition: all 0.3s ease;
    }
    
    .password-requirement .requirement-icon {
        font-size: 18px;
        color: #D1D5DB;
        transition: all 0.3s ease;
    }
    
    .password-requirement.met {
        color: #059669;
    }
    
    .password-requirement.met .requirement-icon {
        color: #10B981;
        transform: scale(1.1);
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

    // Password Modal Functions
    function openPasswordModal() {
        document.getElementById('passwordModal').classList.add('active');
        
        // Inicializar validación de contraseña
        const passwordInput = document.getElementById('password_nueva');
        const strengthBar = document.getElementById('strength-bar');
        const strengthText = document.getElementById('strength-text');
        
        // Limpiar campos
        passwordInput.value = '';
        document.getElementById('password_confirmar').value = '';
        
        // Resetear barra y checks
        strengthBar.className = 'password-strength-bar';
        strengthText.textContent = '';
        
        // Resetear todos los requisitos
        ['req-length', 'req-uppercase', 'req-lowercase', 'req-number', 'req-special'].forEach(id => {
            const elem = document.getElementById(id);
            if (elem) elem.classList.remove('met');
        });
    }
    
    function closePasswordModal() {
        document.getElementById('passwordModal').classList.remove('active');
        document.getElementById('changePasswordForm').reset();
    }
    
    // Close modal on outside click
    window.onclick = function(event) {
        const editModal = document.getElementById('editModal');
        const passwordModal = document.getElementById('passwordModal');
        
        if (event.target === editModal) {
            closeEditModal();
        }
        if (event.target === passwordModal) {
            closePasswordModal();
        }
    }

    // Handle Edit Profile Form Submission
    document.getElementById('editProfileForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => {
            // Verificar que la respuesta sea OK
            if (!response.ok) {
                throw new Error(`HTTP error! status: ${response.status}`);
            }
            
            // Obtener el texto de la respuesta primero
            return response.text();
        })
        .then(text => {
            // Intentar parsear como JSON
            try {
                const data = JSON.parse(text);
                
                if (data.success) {
                    notyf.success(data.message || 'Perfil actualizado exitosamente');
                    closeEditModal();
                    setTimeout(() => location.reload(), 1500);
                } else {
                    notyf.error(data.message || 'Error al actualizar el perfil');
                }
            } catch (e) {
                // Si no es JSON válido, mostrar el error
                console.error('Respuesta del servidor:', text);
                console.error('Error al parsear JSON:', e);
                notyf.error('Error del servidor. Revisa la consola para más detalles.');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            notyf.error('Error de conexión. Por favor intenta nuevamente.');
        });
    });

    // Handle Change Password Form Submission
    document.getElementById('changePasswordForm').addEventListener('submit', function(e) {
        e.preventDefault();
        
        const passwordActual = document.getElementById('password_actual').value;
        const passwordNueva = document.getElementById('password_nueva').value;
        const passwordConfirmar = document.getElementById('password_confirmar').value;
        
        // Validar que la nueva contraseña no sea igual a la actual
        if (passwordNueva === passwordActual) {
            notyf.error('La nueva contraseña debe ser diferente a la actual');
            return;
        }
        
        // Validar que coincidan
        if (passwordNueva !== passwordConfirmar) {
            notyf.error('Las contraseñas no coinciden');
            return;
        }
        
        // Validar longitud
        if (passwordNueva.length < 8) {
            notyf.error('La contraseña debe tener al menos 8 caracteres');
            return;
        }
        
        // Validar requisitos completos
        if (!validatePasswordRequirementsInline(passwordNueva)) {
            notyf.error('La contraseña debe cumplir todos los requisitos de seguridad');
            return;
        }
        
        const formData = new FormData(this);
        
        fetch(this.action, {
            method: 'POST',
            body: formData
        })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                notyf.success(data.message || 'Contraseña cambiada exitosamente');
                closePasswordModal();
                this.reset();
            } else {
                notyf.error(data.message || 'Error al cambiar la contraseña');
            }
        })
        .catch(error => {
            console.error('Error:', error);
            notyf.error('Error de conexión. Por favor intenta nuevamente.');
        });
    });
    
    // Función inline para validar requisitos de contraseña y actualizar checks
    function validatePasswordRequirementsInline(password) {
        const requirements = {
            length: password.length >= 8,
            uppercase: /[A-Z]/.test(password),
            lowercase: /[a-z]/.test(password),
            number: /[0-9]/.test(password),
            special: /[!@#$%^&*()_+\-=\[\]{};':"\\|,.<>\/?]/.test(password)
        };
        
        // Actualizar UI
        const reqLength = document.getElementById('req-length');
        const reqUppercase = document.getElementById('req-uppercase');
        const reqLowercase = document.getElementById('req-lowercase');
        const reqNumber = document.getElementById('req-number');
        const reqSpecial = document.getElementById('req-special');
        
        if (reqLength) reqLength.classList.toggle('met', requirements.length);
        if (reqUppercase) reqUppercase.classList.toggle('met', requirements.uppercase);
        if (reqLowercase) reqLowercase.classList.toggle('met', requirements.lowercase);
        if (reqNumber) reqNumber.classList.toggle('met', requirements.number);
        if (reqSpecial) reqSpecial.classList.toggle('met', requirements.special);
        
        return Object.values(requirements).every(req => req === true);
    }
    
    // Sobrescribir la función updatePasswordStrengthWithRequirements si no existe
    if (typeof updatePasswordStrengthWithRequirements === 'undefined') {
        window.updatePasswordStrengthWithRequirements = function(input, strengthBar, strengthText) {
            const password = input.value;
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength-bar';
                strengthText.textContent = '';
                input.classList.remove('valid', 'invalid');
                
                // Resetear checks
                ['req-length', 'req-uppercase', 'req-lowercase', 'req-number', 'req-special'].forEach(id => {
                    const elem = document.getElementById(id);
                    if (elem) elem.classList.remove('met');
                });
                return;
            }
            
            // Validar requisitos
            const meetsRequirements = validatePasswordRequirementsInline(password);
            
            // Calcular fortaleza
            let strength = 0;
            if (password.length >= 8) strength++;
            if (password.length >= 12) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            let strengthLevel = 'weak';
            if (strength <= 2) strengthLevel = 'weak';
            else if (strength <= 4) strengthLevel = 'medium';
            else strengthLevel = 'strong';
            
            strengthBar.className = `password-strength-bar ${strengthLevel}`;
            
            const messages = {
                weak: 'Débil',
                medium: 'Media',
                strong: 'Fuerte'
            };
            
            strengthText.textContent = messages[strengthLevel];
            strengthText.className = `password-strength-text ${strengthLevel}`;
            
            // Validar que cumpla requisitos mínimos
            if (meetsRequirements) {
                input.classList.remove('invalid');
                input.classList.add('valid');
            } else {
                input.classList.remove('valid');
                input.classList.add('invalid');
            }
        };
    }
</script>

<!-- Validation.js para toggles de contraseña -->
<script src="<?= URLROOT ?>/js/validation.js"></script>
