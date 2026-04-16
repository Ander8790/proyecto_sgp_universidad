<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cambio de Contraseña Requerido - SGP</title>
    <link rel="stylesheet" href="<?= URLROOT ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/swal-bento-navy.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
    
    <!-- ============================================ -->
    <!-- DEFINIR URLROOT PARA JAVASCRIPT -->
    <!-- ============================================ -->
    <script>
        /**
         * Definir constante URLROOT para uso en JavaScript
         * Necesario para notifications.js y otros scripts del sistema
         */
        const URLROOT = <?php echo json_encode(URLROOT, JSON_UNESCAPED_SLASHES) ?>; // [FIX-C3]
    </script>
</head>
<body class="auth-wrapper">
    <?php include_once APPROOT . '/views/layouts/header_strip.php'; ?>
    
    <!-- Tarjeta Premium Flotante -->
    <div class="auth-card">
        <div class="auth-header">
            <div style="width: 80px; height: 80px; background: #FEF3C7; border-radius: 50%; display: flex; align-items: center; justify-content: center; margin: 0 auto 20px;">
                <i class="ti ti-lock" style="font-size: 40px; color: #F59E0B;"></i>
            </div>
            <h1 class="auth-title">Cambio de Contraseña Obligatorio</h1>
            <p class="auth-subtitle">Por seguridad, debes actualizar tu contraseña temporal antes de continuar</p>
        </div>

        <!-- Alerta informativa -->
        <div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
            <p style="color: #92400E; font-size: 14px; margin: 0;">
                <i class="ti ti-alert-triangle" style="margin-right: 8px;"></i>
                <strong>Atención:</strong> Detectamos que estás usando una contraseña temporal. Por tu seguridad, debes cambiarla ahora.
            </p>
        </div>

        <form action="<?= URLROOT ?>/auth/cambiar-password" method="POST" id="passwordChangeForm">
            <!-- Contraseña Actual (Temporal) -->
            <div class="form-group has-toggle">
                <input type="password" name="current_password" id="current_password" class="input-modern" placeholder=" " required>
                <label for="current_password" class="label-floating">
                    <i class="ti ti-key" style="margin-right: 8px; font-size: 18px;"></i>Contraseña Temporal
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('current_password', this)"></i>
            </div>

            <!-- Nueva Contraseña -->
            <div class="form-group has-toggle">
                <input type="password" name="new_password" id="new_password" class="input-modern" placeholder=" " required>
                <label for="new_password" class="label-floating">
                    <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Nueva Contraseña
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('new_password', this)"></i>
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
            </div>

            <!-- Hint de contraseña -->
            <span class="password-hint" style="display: block; margin-top: -16px; margin-bottom: 8px;">
                Mín. 8 caracteres: mayúscula, minúscula, número y símbolo
            </span>

            <!-- Indicador de fortaleza -->
            <div class="password-strength">
                <div class="password-strength-bar" id="strengthBar"></div>
            </div>
            <div class="password-strength-text" id="strengthText"></div>

            <!-- Confirmar Nueva Contraseña -->
            <div class="form-group has-toggle" style="margin-top: 20px;">
                <input type="password" name="confirm_password" id="confirm_password" class="input-modern" placeholder=" " required>
                <label for="confirm_password" class="label-floating">
                    <i class="ti ti-lock-check" style="margin-right: 8px; font-size: 18px;"></i>Confirmar Nueva Contraseña
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
                <span class="input-hint error" id="matchHint" style="display: none;">Las contraseñas no coinciden</span>
            </div>

            <!-- Botón de Cambiar -->
            <button type="submit" class="btn-primary" style="margin-top: 24px;">
                Cambiar Contraseña
                <i class="ti ti-arrow-right" style="font-size: 20px;"></i>
            </button>
        </form>

        <!-- Logout option -->
        <div style="text-align: center; margin-top: 20px;">
            <a href="<?= URLROOT ?>/auth/logout" class="auth-link" style="font-size: 14px; display: inline-flex; align-items: center; gap: 6px;" onclick="event.preventDefault(); confirmLogout();">
                <i class="ti ti-logout"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>
    
    <script src="<?= URLROOT ?>/js/sweetalert2.min.js"></script>
    <script src="<?= URLROOT ?>/js/validation.js"></script>
    
    <script>
        // Password strength indicator
        const newPasswordInput = document.getElementById('new_password');
        const strengthBar = document.getElementById('strengthBar');
        const strengthText = document.getElementById('strengthText');
        
        newPasswordInput.addEventListener('input', function() {
            updatePasswordStrengthWithRequirements(this, strengthBar, strengthText);
        });
        
        // Password match validation
        const confirmInput = document.getElementById('confirm_password');
        const matchHint = document.getElementById('matchHint');
        
        confirmInput.addEventListener('input', function() {
            const match = validatePasswordMatch(newPasswordInput, this);
            matchHint.style.display = match === false ? 'block' : 'none';
        });

        // Form submission
        document.getElementById('passwordChangeForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            const currentPassword = document.getElementById('current_password').value;
            const newPassword = document.getElementById('new_password').value;
            const confirmPassword = document.getElementById('confirm_password').value;
            
            // Validate passwords match
            if (newPassword !== confirmPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'Las contraseñas no coinciden',
                    confirmButtonColor: '#162660'
                });
                return;
            }
            
            // Validate new password is different from current
            if (newPassword === currentPassword) {
                Swal.fire({
                    icon: 'error',
                    title: 'Error',
                    text: 'La nueva contraseña debe ser diferente a la contraseña temporal',
                    confirmButtonColor: '#162660'
                });
                return;
            }

            // Validar requisitos estrictos
            const validation = validatePasswordRequirements(newPassword);
            if (!validation.isValid) {
                Swal.fire({
                    icon: 'error',
                    title: 'Contraseña Débil',
                    text: validation.message,
                    confirmButtonColor: '#162660'
                });
                return;
            }
            
            // Submit form
            const btn = this.querySelector('button[type="submit"]');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Cambiando...';
            btn.disabled = true;
            
            const formData = new FormData(this);
            // [FIX-A3] Adjuntar token CSRF al FormData
            formData.append('_csrf', document.querySelector('meta[name="csrf-token"]')?.content || '');

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    Swal.fire({
                        icon: 'success',
                        title: '¡Contraseña Cambiada!',
                        text: 'Redirigiendo al sistema...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect || '<?= URLROOT ?>/dashboard';
                    });
                } else {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: data.message,
                        confirmButtonColor: '#162660'
                    });
                    btn.innerHTML = originalText;
                    btn.disabled = false;
                }
            })
            .catch(error => {
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error del Sistema',
                    text: 'Ocurrió un error inesperado',
                    confirmButtonColor: '#162660'
                });
                btn.innerHTML = originalText;
                btn.disabled = false;
            });
        });
        
        // Logout confirmation
        function confirmLogout() {
            Swal.fire({
                title: '¿Cerrar Sesión?',
                text: 'Deberás cambiar tu contraseña temporal la próxima vez que inicies sesión',
                icon: 'warning',
                showCancelButton: true,
                confirmButtonColor: '#EF4444',
                cancelButtonColor: '#6B7280',
                confirmButtonText: 'Sí, cerrar sesión',
                cancelButtonText: 'Cancelar'
            }).then((result) => {
                if (result.isConfirmed) {
                    window.location.href = '<?= URLROOT ?>/auth/logout';
                }
            });
        }
    </script>
</body>
</html>
