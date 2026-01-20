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
        
        <div class="auth-footer">
             <a href="<?= URLROOT ?>/auth/login" class="auth-link">Cancelar</a>
        </div>
    </div>
    
    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>
    <script>
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
