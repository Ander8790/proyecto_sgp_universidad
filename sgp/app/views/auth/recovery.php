<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Recuperación</title>
    <!-- 🛡️ No-Caché: Impide restaurar esta página con el botón Atrás -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/img/favicon.png">
    
    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
    
    <!-- ============================================ -->
    <!-- DEFINIR URLROOT PARA JAVASCRIPT -->
    <!-- ============================================ -->
    <script>
        /**
         * Definir constante URLROOT para uso en JavaScript
         * Necesario para notifications.js y otros scripts del sistema
         */
        const URLROOT = '<?php echo URLROOT; ?>';
    </script>
</head>
<body class="auth-wrapper">
    <?php include_once APPROOT . '/views/layouts/header_strip.php'; ?>
    <div class="auth-card" style="position: relative;">
        <!-- Back Button -->
        <a href="<?= URLROOT ?>/auth/login" class="btn-back" title="Volver al Login">
            <i class="ti ti-arrow-left"></i>
        </a>

        <div class="auth-header">
            <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" class="auth-logo">
            <h1 class="auth-title">Recuperar Cuenta</h1>
            <p class="auth-subtitle">Paso 1: Identificación</p>
        </div>
        
        <form action="<?= URLROOT ?>/auth/recovery" method="POST">
            <input type="hidden" name="step" value="1">
            
            <!-- Email con Floating Label y Validación -->
            <div class="form-group">
                <input type="email" name="email" id="email" class="input-modern" placeholder=" " required>
                <label for="email" class="label-floating">
                    <i class="ti ti-search" style="margin-right: 8px; font-size: 18px;"></i>Ingrese su correo electrónico
                </label>
                <div class="email-feedback"></div>
                <span class="input-hint">Ingresa el correo con el que te registraste</span>
            </div>
            
            <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                Continuar <i class="ti ti-arrow-right" style="margin-left: 8px;"></i>
            </button>
        </form>
        
        <div class="auth-footer">
            <a href="<?= URLROOT ?>/auth/login" class="auth-link">Cancelar y volver</a>
        </div>
    </div>
    
    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>
    <script src="<?= URLROOT ?>/js/validation.js"></script>

    <script>
        // Validación de email en tiempo real
        const emailInput = document.getElementById('email');
        emailInput.addEventListener('blur', function() {
            validateEmailWithFeedback(this);
        });

        <?php if (!empty($error)): ?>
            if (typeof NotificationService !== 'undefined') {
                NotificationService.error('<?= htmlspecialchars($error) ?>');
            }
        <?php endif; ?>
    </script>
</body>
</html>
