<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Registro</title>
    
    <!-- JAVASCRIPT EN HEAD - CARGA PRIMERO -->
    <script>
        console.log("=== SISTEMA DE REGISTRO HÍBRIDO INICIADO ===");
        
        // ====================================================
        // FUNCIONES GLOBALES (window.*)
        // ====================================================
        
        // 1. TOGGLE PASSWORD VISIBILITY
        window.togglePass = function(fieldId, icon) {
            console.log("👁️ Toggle activado para: " + fieldId);
            var input = document.getElementById(fieldId);
            if (!input) { 
                console.error("❌ Input no encontrado: " + fieldId); 
                return; 
            }

            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.remove('ti-eye');
                icon.classList.add('ti-eye-off');
                console.log("✅ Contraseña visible");
            } else {
                input.type = 'password';
                icon.classList.remove('ti-eye-off');
                icon.classList.add('ti-eye');
                console.log("✅ Contraseña oculta");
            }
        };
        
        // 2. VALIDAR PASO 1
        window.validarPaso1 = function() {
            console.log("🔍 Validando paso 1...");
            
            var nombre = document.getElementById('nombre');
            var cedula = document.getElementById('cedula');
            var email = document.getElementById('email');
            var password = document.getElementById('password');
            var confirm = document.getElementById('password_confirm');
            
            // Validar que todos los campos existan y estén llenos
            if (!nombre || !nombre.value.trim()) {
                alert('Por favor ingresa tu nombre completo');
                if (nombre) nombre.focus();
                return false;
            }
            
            if (!cedula || !cedula.value.trim()) {
                alert('Por favor ingresa tu cédula');
                if (cedula) cedula.focus();
                return false;
            }
            
            // Validar formato de cédula (solo números, 7-8 dígitos)
            if (!/^[0-9]{7,8}$/.test(cedula.value)) {
                alert('La cédula debe contener solo números (7-8 dígitos)');
                cedula.focus();
                return false;
            }
            
            if (!email || !email.value.trim()) {
                alert('Por favor ingresa tu correo electrónico');
                if (email) email.focus();
                return false;
            }
            
            // Validar formato de email
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            if (!emailRegex.test(email.value)) {
                alert('Por favor ingresa un correo electrónico válido');
                email.focus();
                return false;
            }
            
            if (!password || !password.value) {
                alert('Por favor ingresa una contraseña');
                if (password) password.focus();
                return false;
            }
            
            // Validar longitud mínima de contraseña
            if (password.value.length < 8) {
                alert('La contraseña debe tener al menos 8 caracteres');
                password.focus();
                return false;
            }
            
            if (!confirm || !confirm.value) {
                alert('Por favor confirma tu contraseña');
                if (confirm) confirm.focus();
                return false;
            }
            
            // Validar que las contraseñas coincidan
            if (password.value !== confirm.value) {
                alert('Las contraseñas no coinciden');
                confirm.focus();
                return false;
            }
            
            console.log("✅ Paso 1 validado correctamente");
            return true;
        };
        
        // 3. AVANZAR AL PASO 2
        window.nextStep = function() {
            console.log("➡️ Intentando avanzar al paso 2...");
            
            if (!window.validarPaso1()) {
                console.log("❌ Validación fallida");
                return;
            }
            
            var step1 = document.getElementById('step1');
            var step2 = document.getElementById('step2');
            
            if (!step1 || !step2) {
                console.error("❌ No se encontraron los contenedores de pasos");
                return;
            }
            
            // Cambiar formularios
            step1.style.display = 'none';
            step2.style.display = 'block';
            
            // ============================================
            // ACTUALIZAR INDICADOR VISUAL (BOLITAS)
            // ============================================
            var steps = document.querySelectorAll('.steps-indicator .step');
            if (steps.length >= 2) {
                // Paso 1: Completado (verde/check)
                steps[0].classList.remove('active');
                steps[0].classList.add('completed');
                
                // Paso 2: Activo (azul)
                steps[1].classList.add('active');
                
                console.log("✅ Indicador visual actualizado");
            }
            
            console.log("✅ Avanzado a paso 2");
        };
        
        // 4. VOLVER AL PASO 1
        window.prevStep = function() {
            console.log("⬅️ Volviendo al paso 1...");
            
            var step1 = document.getElementById('step1');
            var step2 = document.getElementById('step2');
            
            if (!step1 || !step2) {
                console.error("❌ No se encontraron los contenedores de pasos");
                return;
            }
            
            // Cambiar formularios
            step2.style.display = 'none';
            step1.style.display = 'block';
            
            // ============================================
            // REVERTIR INDICADOR VISUAL (BOLITAS)
            // ============================================
            var steps = document.querySelectorAll('.steps-indicator .step');
            if (steps.length >= 2) {
                // Paso 1: Activo de nuevo
                steps[0].classList.add('active');
                steps[0].classList.remove('completed');
                
                // Paso 2: Inactivo
                steps[1].classList.remove('active');
                
                console.log("✅ Indicador visual revertido");
            }
            
            console.log("✅ Vuelto a paso 1");
        };
        
        // 5. VALIDACIÓN DE EMAIL EN TIEMPO REAL
        window.validarEmail = function(input) {
            var emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
            var value = input.value.trim();
            
            if (value.length === 0) {
                input.classList.remove('valid', 'invalid');
                return null;
            }
            
            if (emailRegex.test(value)) {
                input.classList.remove('invalid');
                input.classList.add('valid');
                return true;
            } else {
                input.classList.remove('valid');
                input.classList.add('invalid');
                return false;
            }
        };
        
        // 6. MEDIDOR DE FORTALEZA DE CONTRASEÑA
        window.actualizarFortaleza = function(input) {
            var password = input.value;
            var strengthBar = document.getElementById('strengthBar');
            var strengthText = document.getElementById('strengthText');
            
            if (!strengthBar || !strengthText) return;
            
            if (password.length === 0) {
                strengthBar.className = 'password-strength-bar';
                strengthText.textContent = '';
                return;
            }
            
            var strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password)) strength++;
            if (/[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[!@#$%^&*(),.?":{}|<>]/.test(password)) strength++;
            
            if (strength <= 2) {
                strengthBar.className = 'password-strength-bar weak';
                strengthText.textContent = 'Débil';
                strengthText.style.color = '#ef4444';
            } else if (strength <= 4) {
                strengthBar.className = 'password-strength-bar medium';
                strengthText.textContent = 'Media';
                strengthText.style.color = '#f59e0b';
            } else {
                strengthBar.className = 'password-strength-bar strong';
                strengthText.textContent = 'Fuerte';
                strengthText.style.color = '#10b981';
            }
            
            // Actualizar requisitos visuales
            var reqLength = document.getElementById('req-length');
            var reqUpper = document.getElementById('req-uppercase');
            var reqLower = document.getElementById('req-lowercase');
            var reqNumber = document.getElementById('req-number');
            var reqSpecial = document.getElementById('req-special');
            
            if (reqLength) reqLength.classList.toggle('met', password.length >= 8);
            if (reqUpper) reqUpper.classList.toggle('met', /[A-Z]/.test(password));
            if (reqLower) reqLower.classList.toggle('met', /[a-z]/.test(password));
            if (reqNumber) reqNumber.classList.toggle('met', /[0-9]/.test(password));
            if (reqSpecial) reqSpecial.classList.toggle('met', /[!@#$%^&*(),.?":{}|<>]/.test(password));
        };
        
        // 7. VALIDAR CONFIRMACIÓN DE CONTRASEÑA
        window.validarConfirmacion = function(passwordInput, confirmInput) {
            if (!confirmInput.value) return null;
            
            if (passwordInput.value === confirmInput.value) {
                confirmInput.classList.remove('invalid');
                confirmInput.classList.add('valid');
                return true;
            } else {
                confirmInput.classList.remove('valid');
                confirmInput.classList.add('invalid');
                return false;
            }
        };
        
        // 8. VALIDAR PREGUNTAS DE SEGURIDAD (NO DUPLICADAS)
        window.validarPreguntasSeguridad = function() {
            console.log("🔍 Validando preguntas de seguridad...");
            
            var pregunta1 = document.getElementById('pregunta_1');
            var pregunta2 = document.getElementById('pregunta_2');
            var pregunta3 = document.getElementById('pregunta_3');
            
            var respuesta1 = document.getElementById('respuesta_1');
            var respuesta2 = document.getElementById('respuesta_2');
            var respuesta3 = document.getElementById('respuesta_3');
            
            // Validar que todas las preguntas estén seleccionadas
            if (!pregunta1 || !pregunta1.value || pregunta1.value === '') {
                alert('Por favor selecciona la Pregunta de Seguridad 1');
                if (pregunta1) pregunta1.focus();
                return false;
            }
            
            if (!pregunta2 || !pregunta2.value || pregunta2.value === '') {
                alert('Por favor selecciona la Pregunta de Seguridad 2');
                if (pregunta2) pregunta2.focus();
                return false;
            }
            
            if (!pregunta3 || !pregunta3.value || pregunta3.value === '') {
                alert('Por favor selecciona la Pregunta de Seguridad 3');
                if (pregunta3) pregunta3.focus();
                return false;
            }
            
            // Validar que las preguntas sean diferentes
            if (pregunta1.value === pregunta2.value) {
                alert('Las Preguntas 1 y 2 deben ser diferentes');
                pregunta2.focus();
                return false;
            }
            
            if (pregunta1.value === pregunta3.value) {
                alert('Las Preguntas 1 y 3 deben ser diferentes');
                pregunta3.focus();
                return false;
            }
            
            if (pregunta2.value === pregunta3.value) {
                alert('Las Preguntas 2 y 3 deben ser diferentes');
                pregunta3.focus();
                return false;
            }
            
            // Validar que todas las respuestas estén llenas
            if (!respuesta1 || !respuesta1.value.trim()) {
                alert('Por favor ingresa la Respuesta 1');
                if (respuesta1) respuesta1.focus();
                return false;
            }
            
            if (!respuesta2 || !respuesta2.value.trim()) {
                alert('Por favor ingresa la Respuesta 2');
                if (respuesta2) respuesta2.focus();
                return false;
            }
            
            if (!respuesta3 || !respuesta3.value.trim()) {
                alert('Por favor ingresa la Respuesta 3');
                if (respuesta3) respuesta3.focus();
                return false;
            }
            
            console.log("✅ Preguntas de seguridad validadas correctamente");
            return true;
        };
        
        console.log("✅ Funciones globales cargadas correctamente");
    </script>
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
            <div id="step1" style="display: block;">
                <!-- Nombre Completo -->
                <div class="form-group">
                    <input type="text" name="name" id="nombre" class="input-modern" placeholder=" " required accept-charset="UTF-8" value="<?= htmlspecialchars($_POST['name'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
                    <label for="nombre" class="label-floating">
                        <i class="ti ti-user" style="margin-right: 8px; font-size: 18px;"></i>Nombre Completo
                    </label>
                </div>

                <!-- Cédula -->
                <div class="form-group">
                    <input type="number" name="cedula" id="cedula" class="input-modern" placeholder=" " required autocomplete="off" value="<?= htmlspecialchars($_POST['cedula'] ?? '', ENT_QUOTES, 'UTF-8') ?>" oninput="if(this.value.length > 8) this.value = this.value.slice(0, 8);">
                    <label for="cedula" class="label-floating">
                        <i class="ti ti-id" style="margin-right: 8px; font-size: 18px;"></i>Cédula de Identidad
                    </label>
                    <small style="color: #6b7280; font-size: 0.85rem;">Solo números, sin puntos ni guiones (máx. 8 dígitos)</small>
                </div>

                <!-- Email -->
                <div class="form-group">
                    <input type="email" name="email" id="email" class="input-modern validate-email" placeholder=" " required autocomplete="off" value="<?= htmlspecialchars($_POST['email'] ?? '', ENT_QUOTES, 'UTF-8') ?>" oninput="window.validarEmail(this)">
                    <label for="email" class="label-floating">
                        <i class="ti ti-mail" style="margin-right: 8px; font-size: 18px;"></i>Correo Electrónico
                    </label>
                    <i class="ti ti-check input-feedback icon-check"></i>
                    <i class="ti ti-x input-feedback icon-error"></i>
                </div>

                <!-- Contraseña -->
                <div class="form-group has-toggle">
                    <input type="password" name="password" id="password" class="input-modern" placeholder=" " required oninput="window.actualizarFortaleza(this)">
                    <label for="password" class="label-floating">
                        <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Contraseña
                    </label>
                    <i class="ti ti-eye password-toggle" onclick="window.togglePass('password', this)"></i>
                    <i class="ti ti-check input-feedback icon-check"></i>
                    <i class="ti ti-x input-feedback icon-error"></i>
                </div>
                
                <!-- Texto guía minimalista -->
                <span class="password-hint" style="display: block; margin-top: -16px; margin-bottom: 8px;">
                    Mín. 8 caracteres: mayúscula, minúscula, número y símbolo
                </span>
                
                <!-- Indicador de fortaleza -->
                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <div class="password-strength-text" id="strengthText"></div>
                
                <!-- Requisitos de contraseña -->
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
                    <input type="password" name="password_confirm" id="password_confirm" class="input-modern" placeholder=" " required oninput="window.validarConfirmacion(document.getElementById('password'), this)">
                    <label for="password_confirm" class="label-floating">
                        <i class="ti ti-lock-check" style="margin-right: 8px; font-size: 18px;"></i>Confirmar Contraseña
                    </label>
                    <i class="ti ti-eye password-toggle" onclick="window.togglePass('password_confirm', this)"></i>
                    <i class="ti ti-check input-feedback icon-check"></i>
                    <i class="ti ti-x input-feedback icon-error"></i>
                    <span class="input-hint error" id="matchHint" style="display: none;">Las contraseñas no coinciden</span>
                </div>

                <!-- Botón Siguiente -->
                <button type="button" class="btn-primary" onclick="window.nextStep()" style="margin-top: 1rem;">
                    Siguiente <i class="ti ti-arrow-right" style="margin-left: 8px;"></i>
                </button>
            </div>

            <!-- PASO 2: Preguntas de Seguridad -->
            <div id="step2" style="display: none;">
                <h3 style="font-size: 1rem; margin-bottom: 20px; color: var(--color-primary); font-weight: 600;">
                    <i class="ti ti-shield-check" style="margin-right: 8px; font-size: 18px;"></i>Preguntas de Seguridad
                </h3>
                
                
                
                <p style="color: #6b7280; font-size: 0.9rem; margin-bottom: 1.5rem;">
                    Selecciona 3 preguntas diferentes y proporciona tus respuestas. Estas te ayudarán a recuperar tu cuenta.
                </p>
                
                <?php 
                // DEBUG TEMPORAL: Ver qué variables están disponibles
                echo "<!-- DEBUG: ";
                echo "isset(\$questions) = " . (isset($questions) ? 'SI' : 'NO') . " | ";
                if (isset($questions)) {
                    echo "count(\$questions) = " . (is_array($questions) || is_object($questions) ? count($questions) : '0') . " | ";
                    echo "type = " . gettype($questions);
                }
                echo " -->";
                ?>
                
                <?php if (!empty($questions)): ?>
                    <!-- PREGUNTA 1 -->
                    <div class="form-group">
                        <label for="pregunta_1" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="ti ti-help-circle" style="margin-right: 6px;"></i>Pregunta de Seguridad 1 *
                        </label>
                        <select name="pregunta_id_1" id="pregunta_1" class="input-modern" required style="cursor: pointer;">
                            <option value="">Seleccione una pregunta...</option>
                            
                            <?php 
                            // LECTOR UNIVERSAL: Detecta automáticamente la variable y el tipo de dato
                            $lista = [];
                            if (isset($questions)) $lista = $questions;
                            elseif (isset($preguntas)) $lista = $preguntas;
                            elseif (isset($data['questions'])) $lista = $data['questions'];
                            elseif (isset($data['preguntas'])) $lista = $data['preguntas'];
                            
                            // Renderizar (soporta objetos y arrays)
                            if (!empty($lista)) {
                                foreach ($lista as $item) {
                                    // Normalizar ID y Texto
                                    $id = is_object($item) ? $item->id : $item['id'];
                                    $texto = is_object($item) ? $item->pregunta : $item['pregunta'];
                                    
                                    echo "<option value='{$id}'>" . htmlspecialchars($texto) . "</option>";
                                }
                            } else {
                                echo "<option disabled>⚠️ ERROR: No llegaron datos (Variable desconocida)</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="respuesta_1" id="respuesta_1" class="input-modern" placeholder=" " required accept-charset="UTF-8">
                        <label for="respuesta_1" class="label-floating">
                            <i class="ti ti-message" style="margin-right: 8px; font-size: 18px;"></i>Tu Respuesta 1
                        </label>
                    </div>
                    
                    <!-- PREGUNTA 2 -->
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <label for="pregunta_2" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="ti ti-help-circle" style="margin-right: 6px;"></i>Pregunta de Seguridad 2 *
                        </label>
                        <select name="pregunta_id_2" id="pregunta_2" class="input-modern" required style="cursor: pointer;">
                            <option value="">Seleccione una pregunta...</option>
                            
                            <?php 
                            // LECTOR UNIVERSAL: Detecta automáticamente la variable y el tipo de dato
                            $lista = [];
                            if (isset($questions)) $lista = $questions;
                            elseif (isset($preguntas)) $lista = $preguntas;
                            elseif (isset($data['questions'])) $lista = $data['questions'];
                            elseif (isset($data['preguntas'])) $lista = $data['preguntas'];
                            
                            // Renderizar (soporta objetos y arrays)
                            if (!empty($lista)) {
                                foreach ($lista as $item) {
                                    // Normalizar ID y Texto
                                    $id = is_object($item) ? $item->id : $item['id'];
                                    $texto = is_object($item) ? $item->pregunta : $item['pregunta'];
                                    
                                    echo "<option value='{$id}'>" . htmlspecialchars($texto) . "</option>";
                                }
                            } else {
                                echo "<option disabled>⚠️ ERROR: No llegaron datos (Variable desconocida)</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="respuesta_2" id="respuesta_2" class="input-modern" placeholder=" " required accept-charset="UTF-8">
                        <label for="respuesta_2" class="label-floating">
                            <i class="ti ti-message" style="margin-right: 8px; font-size: 18px;"></i>Tu Respuesta 2
                        </label>
                    </div>
                    
                    <!-- PREGUNTA 3 -->
                    <div class="form-group" style="margin-top: 1.5rem;">
                        <label for="pregunta_3" class="form-label" style="display: block; margin-bottom: 0.5rem; font-weight: 600; color: #374151;">
                            <i class="ti ti-help-circle" style="margin-right: 6px;"></i>Pregunta de Seguridad 3 *
                        </label>
                        <select name="pregunta_id_3" id="pregunta_3" class="input-modern" required style="cursor: pointer;">
                            <option value="">Seleccione una pregunta...</option>
                            
                            <?php 
                            // LECTOR UNIVERSAL: Detecta automáticamente la variable y el tipo de dato
                            $lista = [];
                            if (isset($questions)) $lista = $questions;
                            elseif (isset($preguntas)) $lista = $preguntas;
                            elseif (isset($data['questions'])) $lista = $data['questions'];
                            elseif (isset($data['preguntas'])) $lista = $data['preguntas'];
                            
                            // Renderizar (soporta objetos y arrays)
                            if (!empty($lista)) {
                                foreach ($lista as $item) {
                                    // Normalizar ID y Texto
                                    $id = is_object($item) ? $item->id : $item['id'];
                                    $texto = is_object($item) ? $item->pregunta : $item['pregunta'];
                                    
                                    echo "<option value='{$id}'>" . htmlspecialchars($texto) . "</option>";
                                }
                            } else {
                                echo "<option disabled>⚠️ ERROR: No llegaron datos (Variable desconocida)</option>";
                            }
                            ?>
                        </select>
                    </div>
                    
                    <div class="form-group">
                        <input type="text" name="respuesta_3" id="respuesta_3" class="input-modern" placeholder=" " required accept-charset="UTF-8">
                        <label for="respuesta_3" class="label-floating">
                            <i class="ti ti-message" style="margin-right: 8px; font-size: 18px;"></i>Tu Respuesta 3
                        </label>
                    </div>
                    
                <?php else: ?>
                    <div style="padding: 1rem; background: #fee2e2; color: #ef4444; border-radius: 12px; margin-bottom: 1.5rem;">
                        <i class="ti ti-alert-circle" style="margin-right: 8px;"></i>
                        Error: No hay preguntas de seguridad configuradas. Contacte soporte.
                    </div>
                <?php endif; ?>

                <!-- Botones de navegación -->
                <div class="step-buttons">
                    <button type="button" class="btn-secondary" onclick="window.prevStep()">
                        <i class="ti ti-arrow-left"></i> Atrás
                    </button>
                    <button type="button" class="btn-primary" style="flex: 1;" onclick="if(window.validarPreguntasSeguridad()) { document.getElementById('registerForm').submit(); }">
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
    
    <script>
        console.log("🚀 Inicializando eventos del DOM...");
        
        // Mostrar errores PHP si existen
        <?php if (!empty($error)): ?>
            if (typeof Swal !== 'undefined') {
                Swal.fire({
                    icon: 'error',
                    title: 'No se pudo registrar',
                    text: '<?= htmlspecialchars($error) ?>',
                    confirmButtonColor: '#162660'
                });
            } else {
                alert('Error: <?= htmlspecialchars($error) ?>');
            }
        <?php endif; ?>
        
        console.log("✅ Sistema de registro completamente inicializado");
    </script>
</body>
</html>
