<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Iniciar Sesión</title>
</head>
<body class="auth-wrapper">
    <?php include_once APPROOT . '/views/layouts/header_strip.php'; ?>
    
    <!-- Tarjeta Premium Flotante -->
    <div class="auth-card">
        <div class="auth-header">
            <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" class="auth-logo">
            <h1 class="auth-title">Iniciar Sesión</h1>
            <p class="auth-subtitle">Bienvenido al Sistema de Gestión de Pasantes</p>
        </div>

        <form action="<?= URLROOT ?>/auth/login" method="POST">
            <!-- Email con Floating Label e Icono -->
            <div class="form-group">
                <input type="email" name="email" id="email" class="input-modern" placeholder=" " required>
                <label for="email" class="label-floating">
                    <i class="ti ti-mail" style="margin-right: 8px; font-size: 18px;"></i>Correo Electrónico
                </label>
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
            </div>

            <!-- Password con Floating Label, Toggle e Icono -->
            <div class="form-group has-toggle">
                <input type="password" name="password" id="password" class="input-modern" placeholder=" " required style="padding-right: 48px;">
                <label for="password" class="label-floating">
                    <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Contraseña
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('password', this)"></i>
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
            </div>

            <!-- CAPTCHA Component -->
            <div class="captcha-wrapper">
                <label class="captcha-label">Código de Verificación</label>
                <div class="captcha-container">
                    <img src="<?= URLROOT ?>/captcha/generate" 
                         alt="CAPTCHA" 
                         class="captcha-image" 
                         id="captchaImage">
                    <button type="button" 
                            class="captcha-refresh-btn" 
                            onclick="refreshCaptcha()"
                            title="Generar nuevo código">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>
                <div class="form-group" style="margin-top: 12px;">
                    <input type="text" 
                           name="captcha" 
                           id="captcha" 
                           class="input-modern" 
                           placeholder="Ingrese los 5 caracteres que ve en la imagen" 
                           required 
                           maxlength="5"
                           autocomplete="off">
                    <i class="ti ti-check input-feedback icon-check"></i>
                    <i class="ti ti-x input-feedback icon-error"></i>
                </div>
            </div>

            <!-- Enlace de Recuperación -->
            <div style="text-align: right; margin-bottom: 28px;">
                <a href="<?= URLROOT ?>/auth/recovery" class="auth-link" style="font-size: 0.9rem;">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>

            <!-- Botón Premium -->
            <button type="submit" class="btn-primary">
                Ingresar
                <i class="ti ti-arrow-right" style="font-size: 20px;"></i>
            </button>
        </form>

        <div class="auth-footer">
            ¿No tienes cuenta? <a href="<?= URLROOT ?>/auth/register" class="auth-link">Regístrate aquí</a>
        </div>
    </div>

    <!-- Loading Spinner Overlay -->
    <div id="loginLoader" style="display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,0.7); z-index: 9999; align-items: center; justify-content: center;">
        <div class="spinner-container">
            <div class="spinner"></div>
            <p style="color: white; margin-top: 20px; font-size: 16px;">Validando credenciales...</p>
        </div>
    </div>

    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>
    <script src="<?= URLROOT ?>/js/validation.js"></script>
    
    <script>
        // Función para refrescar CAPTCHA
        function refreshCaptcha() {
            const captchaImage = document.getElementById('captchaImage');
            const captchaInput = document.getElementById('captcha');
            
            // Agregar timestamp para evitar cache
            captchaImage.src = '<?= URLROOT ?>/captcha/generate?' + Date.now();
            
            // Limpiar input
            captchaInput.value = '';
            captchaInput.classList.remove('valid', 'invalid');
            
            // Animación de rotación del botón
            const refreshBtn = document.querySelector('.captcha-refresh-btn i');
            refreshBtn.style.transform = 'rotate(360deg)';
            setTimeout(() => {
                refreshBtn.style.transform = 'rotate(0deg)';
            }, 500);
        }
        
        // Validación en tiempo real de email
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('input', function() {
            validateEmail(this);
        });
        
        // Validación de contraseña
        const passwordInput = document.getElementById('password');
        passwordInput.addEventListener('input', function() {
            validatePasswordLength(this, 6);
        });
        
        // Validación de CAPTCHA (solo longitud)
        const captchaInput = document.getElementById('captcha');
        captchaInput.addEventListener('input', function() {
            validatePasswordLength(this, 5);
        });
        
        // Submit del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const loader = document.getElementById('loginLoader');
            
            // Show loading overlay
            loader.style.display = 'flex';
            btn.disabled = true;

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(response => response.json())
            .then(data => {
                loader.style.display = 'none';
                
                if (data.success) {
                    // Success feedback
                    Swal.fire({
                        icon: 'success',
                        title: '¡Bienvenido!',
                        text: 'Redirigiendo...',
                        timer: 1500,
                        showConfirmButton: false
                    }).then(() => {
                        window.location.href = data.redirect;
                    });
                } else {
                    // Error feedback
                    Swal.fire({
                        icon: 'error',
                        title: 'Error de Acceso',
                        text: data.message,
                        confirmButtonColor: '#162660'
                    });
                    btn.disabled = false;
                }
            })
            .catch(error => {
                loader.style.display = 'none';
                console.error('Error:', error);
                Swal.fire({
                    icon: 'error',
                    title: 'Error del Sistema',
                    text: 'Ocurrió un error inesperado',
                    confirmButtonColor: '#162660'
                });
                btn.disabled = false;
            });
        });

        <?php if (!empty($success)): ?>
            Swal.fire({
                icon: 'success',
                title: '¡Éxito!',
                text: '<?= htmlspecialchars($success) ?>',
                confirmButtonColor: '#162660'
            });
        <?php endif; ?>
    </script>
</body>
</html>
