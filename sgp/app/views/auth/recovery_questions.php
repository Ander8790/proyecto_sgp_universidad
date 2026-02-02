<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Preguntas de Seguridad</title>
</head>
<body class="auth-wrapper">
    <?php include_once APPROOT . '/views/layouts/header_strip.php'; ?>
    <div class="auth-card" style="position: relative;">
        <!-- Back Button -->
        <a href="<?= URLROOT ?>/auth/login" class="btn-back" title="Cancelar y volver">
            <i class="ti ti-arrow-left"></i>
        </a>

        <div class="auth-header">
            <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" class="auth-logo">
            <h1 class="auth-title">Verificación de Seguridad</h1>
            <p class="auth-subtitle">Paso 2: Responde tus preguntas de seguridad</p>
        </div>
        
        <form action="<?= URLROOT ?>/auth/recovery" method="POST">
            <input type="hidden" name="step" value="2">
            <?php foreach ($questions as $q): ?>
                <div class="form-group">
                    <input type="text" name="answers[<?= $q['question_id'] ?>]" id="answer_<?= $q['question_id'] ?>" class="input-modern" placeholder=" " required>
                    <label for="answer_<?= $q['question_id'] ?>" class="label-floating">
                        <?= htmlspecialchars($q['question']) ?>
                    </label>
                </div>
            <?php endforeach; ?>
            
            <button type="submit" class="btn-primary" style="margin-top: 1rem;">
                Verificar <i class="ti ti-check" style="margin-left: 8px;"></i>
            </button>
        </form>
        
        <!-- SECCIÓN: SOLICITUD DE AYUDA -->
        <div class="text-center" style="margin-top: 1.5rem; padding-top: 1rem; border-top: 1px solid #E5E7EB;">
            <p style="font-size: 13px; color: #6B7280; margin-bottom: 0.75rem;">
                ¿No recuerdas tus respuestas?
            </p>
            <a href="#" 
               id="btnSolicitarAyuda"
               class="btn-link" 
               style="color: #EF4444; font-weight: 600; font-size: 14px; text-decoration: none; display: inline-flex; align-items: center; transition: all 0.3s ease;">
                <i class="ti ti-help-circle" style="margin-right: 6px; font-size: 18px;"></i>
                Solicitar ayuda al administrador
            </a>
            <p style="font-size: 12px; color: #9CA3AF; margin-top: 0.5rem;">
                El administrador revisará tu solicitud y te contactará pronto.
            </p>
        </div>
        
        <div class="auth-footer">
             <a href="<?= URLROOT ?>/auth/login" class="auth-link">Cancelar</a>
        </div>
    </div>
    
    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>
    <script>
        // ============================================
        // SWEETALERT: CONFIRMACIÓN DE SOLICITUD DE AYUDA
        // ============================================
        document.getElementById('btnSolicitarAyuda')?.addEventListener('click', function(e) {
            e.preventDefault();
            
            Swal.fire({
                icon: 'question',
                title: '¿Solicitar ayuda?',
                text: 'Se notificará al administrador para resetear tu cuenta. ¿Deseas continuar?',
                showCancelButton: true,
                confirmButtonText: 'Sí, solicitar',
                cancelButtonText: 'Cancelar',
                confirmButtonColor: '#162660',
                cancelButtonColor: '#6B7280',
                reverseButtons: true
            }).then((result) => {
                if (result.isConfirmed) {
                    // Redirigir a la ruta de desbloqueo
                    window.location.href = '<?= URLROOT ?>/auth/solicitarDesbloqueo';
                }
            });
        });
        
        // ============================================
        // SWEETALERT: RESPUESTAS INCORRECTAS
        // ============================================
        <?php if (!empty($error)): ?>
            Swal.fire({
                icon: 'error',
                title: 'Respuestas Incorrectas',
                text: '<?= htmlspecialchars($error) ?>',
                confirmButtonColor: '#162660'
            });
        <?php endif; ?>
    </script>
</body>
</html>
