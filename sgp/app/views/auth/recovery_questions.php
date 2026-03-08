<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Preguntas de Seguridad</title>
    <!-- 🛡️ No-Caché: Impide restaurar esta página con el botón Atrás -->
    <meta http-equiv="Cache-Control" content="no-store, no-cache, must-revalidate, max-age=0">
    <meta http-equiv="Pragma" content="no-cache">
    <meta http-equiv="Expires" content="0">
    
    <!-- CSS Assets -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
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

        <script>
            document.querySelector('form').addEventListener('submit', function() {
                const btn = this.querySelector('button[type="submit"]');
                if (typeof setLoading === 'function') {
                    setLoading(btn, true, 'Verificando...');
                } else {
                    // Fallback si validation.js no está cargado (aunque debería por footer)
                    btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Verificando...';
                    btn.disabled = true;
                }
            });
        </script>
        
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
                    // ✨ MEJORADO: Usar AJAX en lugar de redirect
                    sendHelpRequest();
                }
            });
        });
        
        /**
         * Enviar solicitud de ayuda al administrador vía AJAX
         */
        function sendHelpRequest() {
            // Obtener email del usuario (de sesión PHP)
            const email = '<?= htmlspecialchars($_SESSION['rec_email'] ?? '', ENT_QUOTES) ?>';
            
            if (!email) {
                NotificationService.error('No se pudo identificar tu correo');
                return;
            }
            
            // Mostrar loading
            Swal.fire({
                title: 'Enviando solicitud...',
                html: 'Por favor espera',
                allowOutsideClick: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Enviar solicitud vía AJAX
            fetch('<?= URLROOT ?>/auth/request-help', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest'
                },
                body: JSON.stringify({ email: email })
            })
            .then(response => response.json())
            .then(data => {
                Swal.close();
                
                if (data.success) {
                    NotificationService.success('Solicitud enviada correctamente. Redirigiendo...');
                    setTimeout(() => {
                        window.location.href = '<?= URLROOT ?>/auth/login';
                    }, 2000);
                } else {
                    NotificationService.error(data.message || 'Error al enviar solicitud');
                }
            })
            .catch(error => {
                Swal.close();
                console.error('Error:', error);
                NotificationService.error('Error de conexión. Intenta de nuevo.');
            });
        }
        
        // ============================================
        // SWEETALERT: RESPUESTAS INCORRECTAS
        // ============================================
        <?php if (!empty($error)): ?>
            NotificationService.error('<?= htmlspecialchars($error) ?>');
        <?php endif; ?>
    </script>
</body>
</html>
