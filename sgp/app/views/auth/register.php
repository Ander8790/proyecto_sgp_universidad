<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Registro</title>
</head>
<body class="auth-wrapper">
    <?php include_once APPROOT . '/views/layouts/header_strip.php'; ?>
    <div class="auth-card" style="max-width: 550px; position: relative;">
        <!-- Back Button -->
        <a href="<?= URLROOT ?>/auth/login" class="btn-back" title="Volver al Login">
            <i class="ti ti-arrow-left"></i>
        </a>

        <div class="auth-header">
            <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" class="auth-logo">
            <h1 class="auth-title">Crear Cuenta</h1>
            <p class="auth-subtitle">Regístrate como pasante en el sistema</p>
        </div>

        <!-- Steps Indicator -->
        <div class="steps-indicator">
            <div class="step active">
                <div class="step-number">1</div>
                <div class="step-label">Datos Personales</div>
            </div>
            <div class="step-divider"></div>
            <div class="step">
                <div class="step-number">2</div>
                <div class="step-label">Seguridad</div>
            </div>
        </div>

        <form action="<?= URLROOT ?>/auth/register" method="POST" id="registerForm">
            
            <!-- PASO 1: Datos Personales -->
            <div class="form-step active">
                <!-- Nombre con Floating Label -->
                <div class="form-group">
                    <input type="text" name="name" id="name" class="input-modern" placeholder=" " required accept-charset="UTF-8" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <label for="name" class="label-floating">
                        <i class="ti ti-user" style="margin-right: 8px; font-size: 18px;"></i>Nombre Completo
                    </label>
                </div>

                <!-- Email con Floating Label -->
                <div class="form-group">
                    <input type="email" name="email" id="email" class="input-modern validate-email" placeholder=" " required autocomplete="off" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <label for="email" class="label-floating">
                        <i class="ti ti-mail" style="margin-right: 8px; font-size: 18px;"></i>Correo Electrónico
                    </label>
                    <i class="ti ti-check input-feedback icon-check"></i>
                    <i class="ti ti-x input-feedback icon-error"></i>
                    <div class="email-feedback" style="min-height: 20px; font-size: 0.85rem; margin-top: 4px; font-weight: 600;"></div>
                </div>

                <!-- Password con Floating Label, Toggle e Indicador de Fortaleza -->
                <div class="form-group has-toggle">
                    <input type="password" name="password" id="reg_password" class="input-modern" placeholder=" " required>
                    <label for="reg_password" class="label-floating">
                        <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Contraseña
                    </label>
                    <!-- Icono de toggle (ojo) -->
                    <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('reg_password', this)"></i>
                    <!-- Iconos de validación -->
                    <i class="ti ti-check input-feedback icon-check"></i>
                    <i class="ti ti-x input-feedback icon-error"></i>
                </div>
                
                <!-- Texto guía minimalista (FUERA del has-toggle) -->
                <span class="password-hint" style="display: block; margin-top: -16px; margin-bottom: 8px;">
                    Mín. 8 caracteres: mayúscula, minúscula, número y símbolo
                </span>
                
                <!-- Indicador de fortaleza (FUERA del has-toggle) -->
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <div class="password-strength-text" id="strengthText"></div>
                
                <!-- Requisitos de contraseña (ocultos) -->
                <div class="password-requirements">
                    <div class="requirement" id="req-length">
                        <i class="ti ti-circle"></i>
                        <span>Mínimo 8 caracteres</span>
                    </div>
                    <div class="requirement" id="req-uppercase">
                        <i class="ti ti-circle"></i>
                        <span>Una letra mayúscula (A-Z)</span>
                    </div>
                    <div class="requirement" id="req-lowercase">
                        <i class="ti ti-circle"></i>
                        <span>Una letra minúscula (a-z)</span>
                    </div>
                    <div class="requirement" id="req-number">
                        <i class="ti ti-circle"></i>
                        <span>Un número (0-9)</span>
                    </div>
                    <div class="requirement" id="req-special">
                        <i class="ti ti-circle"></i>
                        <span>Un carácter especial (!@#$%^&*)</span>
                    </div>
                </div>
                
                <!-- Confirmación de Contraseña -->
                <div class="form-group has-toggle">
                    <input type="password" name="password_confirm" id="password_confirm" class="input-modern" placeholder=" " required>
                    <label for="password_confirm" class="label-floating">
                        <i class="ti ti-lock-check" style="margin-right: 8px; font-size: 18px;"></i>Confirmar Contraseña
                    </label>
                    <!-- Icono de toggle (ojo) -->
                    <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('password_confirm', this)"></i>
                    <!-- Iconos de validación -->
                    <i class="ti ti-check input-feedback icon-check"></i>
                    <i class="ti ti-x input-feedback icon-error"></i>
                    <span class="input-hint error" id="matchHint" style="display: none;">Las contraseñas no coinciden</span>
                </div>

                <!-- Botón Siguiente -->
                <button type="button" class="btn-primary" onclick="nextStep()" style="margin-top: 1rem;">
                    Siguiente <i class="ti ti-arrow-right" style="margin-left: 8px;"></i>
                </button>
            </div>

            <!-- PASO 2: Preguntas de Seguridad -->
            <div class="form-step">
                <h3 style="font-size: 1rem; margin-bottom: 20px; color: var(--color-primary); font-weight: 600;">
                    <i class="ti ti-shield-check" style="margin-right: 8px; font-size: 18px;"></i>Preguntas de Seguridad
                </h3>
                
                <?php if (!empty($questions)): ?>
                    <?php foreach ($questions as $index => $q): ?>
                        <div class="form-group">
                            <input type="text" name="answers[<?= $q['id'] ?>]" id="answer_<?= $q['id'] ?>" class="input-modern" placeholder=" " required accept-charset="UTF-8">
                            <label for="answer_<?= $q['id'] ?>" class="label-floating">
                                <?= htmlspecialchars($q['pregunta'] ?? $q['question'] ?? 'Pregunta de seguridad') ?>
                            </label>
                        </div>
                    <?php endforeach; ?>
                <?php else: ?>
                    <div style="padding: 1rem; background: #fee2e2; color: #ef4444; border-radius: 12px; margin-bottom: 1.5rem;">
                        <i class="ti ti-alert-circle" style="margin-right: 8px;"></i>
                        Error: No hay preguntas de seguridad configuradas. Contacte soporte.
                    </div>
                <?php endif; ?>

                <!-- Botones de navegación -->
                <div class="step-buttons">
                    <button type="button" class="btn-secondary" onclick="prevStep()">
                        <i class="ti ti-arrow-left"></i> Atrás
                    </button>
                    <button type="submit" class="btn-primary" style="flex: 1;">
                        Registrarse <i class="ti ti-user-plus" style="margin-left: 8px;"></i>
                    </button>
                </div>
            </div>
        </form>
        
        <div class="auth-footer">
            ¿Ya tienes cuenta? <a href="<?= URLROOT ?>/auth/login" class="auth-link">Inicia Sesión</a>
        </div>
    </div>

    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>
    <script src="<?= URLROOT ?>/js/validation.js"></script>
    <script>
        // Inicializar en el paso 1
        showStep(1);
        
        // Validación de email en tiempo real
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('input', function() {
            validateEmail(this);
        });
        
        // Indicador de fortaleza de contraseña con requisitos
        const passwordInput = document.getElementById('reg_password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        passwordInput.addEventListener('input', function() {
            updatePasswordStrengthWithRequirements(this, strengthBar, strengthText);
        });
        
        // Validación de confirmación de contraseña
        const confirmInput = document.getElementById('password_confirm');
        const matchHint = document.getElementById('matchHint');
        
        confirmInput.addEventListener('input', function() {
            const match = validatePasswordMatch(passwordInput, this);
            matchHint.style.display = match === false ? 'block' : 'none';
        });

        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'No se pudo registrar',
                text: '<?= htmlspecialchars($error) ?>',
                confirmButtonColor: '#162660'
            });
        <?php endif; ?>
    </script>
</body>
</html>
