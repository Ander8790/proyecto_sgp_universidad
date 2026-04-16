<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Iniciar Sesión</title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/img/favicon.png">
    
    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/swal-bento-navy.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notifications.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/captcha.css">
    
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
    <!-- ===== BOTÓN KIOSCO - TOP RIGHT (solo si está activo) ===== -->
    <?php
    $kioscoHabilitado = 1;
    try {
        $dbCfg = Database::getInstance();
        $dbCfg->query("SELECT valor FROM configuracion WHERE clave = 'kiosco_activo' LIMIT 1");
        $cfgRow = $dbCfg->single();
        if ($cfgRow) $kioscoHabilitado = (int)$cfgRow->valor;
    } catch (Exception $e) { /* asumir activo si no existe tabla */ }
    ?>
    <?php if ($kioscoHabilitado): ?>
    <a href="<?= URLROOT ?>/kiosco" class="kiosco-pill" target="_blank" rel="noopener noreferrer">
        <i class="ti ti-clock-check"></i>
        <span class="kiosco-text">
            <span>Marcar Asistencia</span>
            <span class="kiosco-label-sub">Acceso al Kiosco Público</span>
        </span>
    </a>
    <?php endif; ?>

    <style>
    .kiosco-pill {
        position: fixed;
        top: 130px;
        right: 50px;
        z-index: 10000;
        display: flex;
        align-items: center;
        gap: 12px;
        padding: 14px 28px;
        background: linear-gradient(135deg, #2ecc71 0%, #219150 100%);
        color: white !important;
        text-decoration: none;
        border-radius: 50px;
        font-weight: 700;
        font-size: 1.1rem;
        box-shadow: 0 8px 20px rgba(46, 204, 113, 0.4);
        transition: all 0.3s cubic-bezier(0.25, 0.8, 0.25, 1);
        letter-spacing: 0.3px;
    }
    .kiosco-pill i { font-size: 1.4rem; }
    .kiosco-pill:hover {
        transform: translateY(-4px) scale(1.02);
        box-shadow: 0 12px 25px rgba(46, 204, 113, 0.6);
        color: white !important;
        text-decoration: none;
    }
    /* Móvil: manejado por style.css (.kiosco-pill mobile) */
    @media (max-width: 768px) {
        .kiosco-pill .kiosco-label-sub {
            display: block;
        }
    }
    .kiosco-label-sub {
        display: none;
        font-size: 0.75rem;
        font-weight: 500;
        opacity: 0.85;
        margin-top: 1px;
    }
    /* Wrapper para texto multi-línea en móvil */
    .kiosco-text { display: flex; flex-direction: column; }
    </style>

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
                <div class="email-feedback"></div>
            </div>

            <!-- Password con Floating Label, Toggle e Icono -->
            <div class="form-group has-toggle">
                <input type="password" name="password" id="password" class="input-modern" placeholder=" " required style="padding-right: 48px;">
                <label for="password" class="label-floating">
                    <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Contraseña
                </label>
                <i class="ti ti-eye password-toggle" onclick="togglePass('password', this)"></i>
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
            </div>


            <!-- CAPTCHA Component -->
            <div class="captcha-wrapper">
                <label class="captcha-label">Código de Verificación</label>
                <div class="captcha-container">
                    <div class="captcha-display">
                        <img src="<?= URLROOT ?>/captcha/generate?v=<?= time() ?>"
                             alt="CAPTCHA"
                             class="captcha-image"
                             id="captchaImage">
                    </div>
                    <button type="button" 
                            class="captcha-refresh-btn" 
                            onclick="refreshCaptcha()"
                            title="Generar nuevo código">
                        <i class="ti ti-refresh"></i>
                    </button>
                </div>
                <div class="form-group" style="margin-top: 0; margin-bottom: 0;">
                    <input type="text" 
                           name="captcha" 
                           id="captcha" 
                           class="input-modern" 
                           placeholder=" " 
                           required 
                           maxlength="5"
                           autocomplete="off"
                           style="text-transform: uppercase;"
                           oninput="this.value = this.value.toUpperCase()">
                    <label for="captcha" class="label-floating">
                        <i class="ti ti-shield-check" style="margin-right: 8px; font-size: 18px;"></i>Ingrese el código
                    </label>
                    <i class="ti ti-check input-feedback icon-check"></i>
                    <i class="ti ti-x input-feedback icon-error"></i>
                </div>
            </div>


            <!-- Botón Premium -->
            <button type="submit" class="btn-primary" style="margin-bottom: 16px;">
                Ingresar
                <i class="ti ti-arrow-right" style="font-size: 20px;"></i>
            </button>

            <!-- Enlace de Recuperación (debajo del botón) -->
            <div style="text-align: center; margin-bottom: 20px;">
                <a href="<?= URLROOT ?>/auth/recovery" class="auth-link" style="font-size: 0.85rem;">
                    ¿Olvidaste tu contraseña?
                </a>
            </div>
        </form>

        <div class="auth-footer">
            ¿No tienes cuenta? <a href="<?= URLROOT ?>/auth/register" class="auth-link">Regístrate aquí</a>
        </div>
    </div>

    <!-- Loading Spinner: Usamos loading en el botón (setLoading) -->

    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>
    <script src="<?= URLROOT ?>/js/validation.js"></script>
    
    <script>
        // ============================================
        // FUNCIÓN: REFRESCAR CAPTCHA
        // ============================================
        /**
         * Refresca la imagen del CAPTCHA
         * @param {boolean} isError - true si es por error de validación, false si es refresco manual
         */
        function refreshCaptcha(isError = false) {
            const captchaImage = document.getElementById('captchaImage');
            const captchaInput = document.getElementById('captcha');
            
            // Agregar timestamp para evitar cache
            captchaImage.src = '<?= URLROOT ?>/captcha/generate?' + Date.now();
            
            // Limpiar input
            captchaInput.value = '';
            captchaInput.classList.remove('valid', 'invalid');
            
            // ✨ SOLO hacer shake si es un error de validación
            if (isError) {
                captchaInput.classList.add('shake-error');
                setTimeout(() => {
                    captchaInput.classList.remove('shake-error');
                    captchaInput.focus(); // Focus para mejor UX
                }, 500);
            } else {
                // Si es refresco manual, solo dar focus sin "regañar"
                captchaInput.focus();
            }
            
            // Animación de rotación del botón
            const refreshBtn = document.querySelector('.captcha-refresh-btn i');
            if (refreshBtn) {
                refreshBtn.style.transform = 'rotate(360deg)';
                setTimeout(() => {
                    refreshBtn.style.transform = 'rotate(0deg)';
                }, 500);
            }
        }
        
        // Auto-renovación del CAPTCHA cada 4 min (CAPTCHA expira en 5 min en sesión)
        setInterval(function() { refreshCaptcha(false); }, 240000);

        // Validación en tiempo real de email
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            validateEmailWithFeedback(this);
        });
        
        // Validación de contraseña (solo visual)
        const passwordInput = document.getElementById('password');
        passwordInput.addEventListener('input', function() {
            // No mostramos check verde en login para no confundir con validación de requisitos
            if(this.value.length > 0) {
                this.classList.remove('invalid');
            }
        });
        
        // Validación de CAPTCHA (solo longitud)
        // Validación de CAPTCHA (solo visual)
        const captchaInput = document.getElementById('captcha');
        captchaInput.addEventListener('input', function() {
             if(this.value.length === 5) {
                this.classList.add('valid');
                this.classList.remove('invalid');
            } else {
                this.classList.remove('valid');
            }
        });
        
        // Submit del formulario
        document.querySelector('form').addEventListener('submit', function(e) {
            e.preventDefault();
            const btn = this.querySelector('button[type="submit"]');
            const loader = document.getElementById('loginLoader');
            
            // Show loading overlay
            // loader.style.display = 'flex'; // Usamos spinner en botón mejor
            setLoading(btn, true, 'Iniciando...');

            const formData = new FormData(this);

            fetch(this.action, {
                method: 'POST',
                body: formData,
                headers: {
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function(response) {
                var contentType = response.headers.get('content-type');
                if (contentType && contentType.includes('application/json')) {
                    return response.json();
                }
                // El servidor devolvió HTML — probablemente un redirect exitoso
                // Verificar si fue un redirect a dashboard
                if (response.redirected || response.url.includes('dashboard')) {
                    window.location.href = response.url;
                    return null;
                }
                // Respuesta inesperada — leer como texto para debuggear
                return response.text().then(function(text) {
                    console.error('[SGP-LOGIN] Respuesta no-JSON del servidor:', text.substring(0, 200));
                    throw new Error('Respuesta inesperada del servidor');
                });
            })
            .then(data => {
                if (!data) return; // fue un redirect manejado arriba
                // loader.style.display = 'none';
                
                if (data.success) {
                    // ✨ Toast de bienvenida personalizado
                    const userName = data.user_name || 'Usuario';
                    const firstName = userName.split(' ')[0];
                    NotificationService.success(`¡Bienvenido, ${firstName}! Redirigiendo...`);
                    
                    setTimeout(() => {
                        window.location.href = data.redirect;
                    }, 1000);
                } else {
                    // Error feedback
                    NotificationService.error(data.message);
                    
                    // AUTO-REFRESH CAPTCHA on error (con shake)
                    refreshCaptcha(true);
                    
                    setLoading(btn, false);
                }
            })
            .catch(error => {
                if (loader) loader.style.display = 'none';
                console.error('Error:', error);
                NotificationService.error('Ocurrió un error inesperado');
                
                // AUTO-REFRESH CAPTCHA on error (con shake)
                refreshCaptcha(true);
                
                setLoading(btn, false);
            });
        });

        // ============================================
        // TOAST: REGISTRO EXITOSO
        // ============================================
        // Cuando el usuario se registra y es redirigido al login,
        // mostramos un toast de éxito
        <?php if (!empty($success)): ?>
            NotificationService.success('<?= htmlspecialchars($success) ?>');
        <?php endif; ?>
        
        // ============================================
        // TOAST: SOLICITUD DE AYUDA ENVIADA
        // ============================================
        // Cuando el usuario solicita ayuda y es redirigido al login
        <?php if (Session::hasFlash('success')): ?>
            NotificationService.success('<?= addslashes(Session::getFlash('success')) ?>');
        <?php endif; ?>
        
        // ============================================
        // TOAST: ERRORES
        // ============================================
        <?php if (Session::hasFlash('error')): ?>
            NotificationService.error('<?= addslashes(Session::getFlash('error')) ?>');
        <?php endif; ?>
        
        // ============================================
        // TOAST: ERRORES ESPECÍFICOS DE LOGIN
        // ============================================
        // Mostrar errores específicos (correo no existe, contraseña incorrecta)
        <?php if (Session::hasFlash('login_error')): ?>
            NotificationService.error('<?= addslashes(Session::getFlash('login_error')) ?>');
        <?php endif; ?>

        // ============================================
        // ALERTA: INACTIVIDAD (Con recarga para Captcha)
        // ============================================
        <?php
        // Detectar inactividad por flash (Ruta A: logout JS) O por URL param
        // (Ruta B: checkInactivity server-side, Ruta C: PJAX 401 handler)
        $mostrarInactividad = Session::hasFlash('inactividad')
            || (($_GET['razon'] ?? '') === 'inactividad');
        ?>
        <?php if ($mostrarInactividad): ?>
            Swal.fire({
                icon: 'info',
                title: 'Sesión Finalizada',
                html: [
                    '<div style="text-align:center;padding:4px 0 8px;">',
                    '<div style="display:inline-flex;align-items:center;justify-content:center;',
                    'width:56px;height:56px;border-radius:50%;',
                    'background:linear-gradient(135deg,#dbeafe,#bfdbfe);margin-bottom:14px;">',
                    '<i class="ti ti-clock-off" style="font-size:1.7rem;color:#2563eb;"></i>',
                    '</div>',
                    '<p style="color:#64748b;font-size:0.9rem;line-height:1.6;margin:0;">',
                    'Tu sesión fue cerrada automáticamente<br>',
                    '<strong style="color:#1e293b;">por inactividad prolongada</strong><br>',
                    '<span style="font-size:0.8rem;color:#94a3b8;margin-top:6px;display:block;">',
                    'Tus datos están protegidos. Puedes volver a iniciar sesión.',
                    '</span>',
                    '</p>',
                    '</div>'
                ].join(''),
                confirmButtonColor: '#1d4ed8',
                confirmButtonText: '<i class="ti ti-login" style="margin-right:5px;"></i> Okay, entendido',
                customClass: {
                    popup: 'sgp-swal-inactividad',
                    confirmButton: 'sgp-swal-btn-confirm'
                },
                didOpen: () => {
                    const popup = Swal.getPopup();
                    if (popup) {
                        popup.style.borderRadius = '20px';
                        popup.style.padding = '32px 28px 28px';
                        popup.style.boxShadow = '0 25px 60px rgba(0,0,0,0.18)';
                    }
                }
            }).then(() => {
                // Limpiar el param ?razon=inactividad de la URL antes de recargar
                const cleanUrl = window.location.pathname;
                window.history.replaceState({}, document.title, cleanUrl);
                window.location.reload();
            });
        <?php endif; ?>

        // ============================================
        // TOAST: REGISTRO EXITOSO (PATRÓN PRG)
        // ============================================
        // Detectar si venimos de un registro exitoso (parámetro ?status=success)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.get('status') === 'success') {
            Swal.fire({
                icon: 'success',
                title: '¡Registro Exitoso! 🎉',
                html: 'Tu cuenta ha sido creada correctamente.<br><strong>Ya puedes iniciar sesión.</strong>',
                confirmButtonColor: '#162660',
                confirmButtonText: '<i class="ti ti-login"></i> Iniciar Sesión',
                timer: 10000,
                timerProgressBar: true,
                didOpen: () => {
                    // Permitir que el timer detenga al hacer clic en el modal
                    Swal.getPopup().addEventListener('mouseenter', () => Swal.stopTimer());
                    Swal.getPopup().addEventListener('mouseleave', () => Swal.resumeTimer());
                }
            }).then(() => {
                document.getElementById('email').focus();
            });
            // Limpiar la URL
            window.history.replaceState({}, document.title, window.location.pathname);
        }
    </script>
    
    <?php 
    // ✨ CRÍTICO: Incluir footer para cargar notification-service.js
    include_once APPROOT . '/views/layouts/footer.php'; 
    ?>
</body>
</html>
