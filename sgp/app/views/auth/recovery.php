<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Recuperación</title>
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
                <i class="ti ti-check input-feedback icon-check"></i>
                <i class="ti ti-x input-feedback icon-error"></i>
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
        emailInput.addEventListener('input', function() {
            validateEmail(this);
        });

        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'warning',
                title: 'Atención',
                text: '<?= htmlspecialchars($error) ?>',
                confirmButtonColor: '#162660'
            });
        <?php endif; ?>
    </script>
</body>
</html>
