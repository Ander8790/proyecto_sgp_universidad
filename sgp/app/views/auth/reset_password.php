<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Restablecer Contraseña</title>
</head>
<body class="auth-wrapper">
    <?php include_once APPROOT . '/views/layouts/header_strip.php'; ?>
    <div class="auth-card">
        <div class="auth-header">
            <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" class="auth-logo">
            <h1 class="auth-title">Nueva Contraseña</h1>
            <p class="auth-subtitle">Establece tu nueva clave de acceso</p>
        </div>
        
        <form action="<?= URLROOT ?>/auth/recovery" method="POST">
            <input type="hidden" name="step" value="3">
            
            <!-- Password con Floating Label y Toggle -->
            <div class="form-group">
                <input type="password" name="password" id="new_password" class="input-modern" placeholder=" " required style="padding-right: 48px;">
                <label for="new_password" class="label-floating">
                    <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Contraseña Nueva
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePassword('new_password', this)"></i>
            </div>

            <!-- Confirmar Password -->
            <div class="form-group">
                <input type="password" name="password_confirm" id="confirm_password" class="input-modern" placeholder=" " required style="padding-right: 48px;">
                <label for="confirm_password" class="label-floating">
                    <i class="ti ti-lock-check" style="margin-right: 8px; font-size: 18px;"></i>Confirmar Contraseña
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePassword('confirm_password', this)"></i>
            </div>
            
            <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                Actualizar <i class="ti ti-refresh" style="margin-left: 8px;"></i>
            </button>
        </form>
    </div>
    
    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>
    <script>
        function togglePassword(inputId, icon) {
            const input = document.getElementById(inputId);
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ti-eye');
                icon.classList.add('ti-eye-off');
            } else {
                input.type = 'password';
                icon.classList.remove('ti-eye-off');
                icon.classList.add('ti-eye');
            }
        }
    </script>
</body>
</html>
