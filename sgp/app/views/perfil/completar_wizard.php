<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Completar Perfil - SGP</title>
</head>
<body class="auth-wrapper">
    <?php include_once APPROOT . '/views/layouts/header_strip.php'; ?>
    
    <!-- Tarjeta Premium Flotante -->
    <div class="auth-card" style="max-width: 700px;">
        <div class="auth-header">
            <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" class="auth-logo">
            <h1 class="auth-title">Completar Perfil</h1>
            <p class="auth-subtitle">Configura tu cuenta en 3 sencillos pasos</p>
        </div>

        <!-- Progress Steps -->
        <div class="wizard-progress" style="margin-bottom: 32px;">
            <div class="progress-steps">
                <div class="step active" data-step="1">
                    <div class="step-circle">1</div>
                    <span class="step-label">Seguridad</span>
                </div>
                <div class="step" data-step="2">
                    <div class="step-circle">2</div>
                    <span class="step-label">Preguntas</span>
                </div>
                <div class="step" data-step="3">
                    <div class="step-circle">3</div>
                    <span class="step-label">Datos</span>
                </div>
            </div>
            <div class="progress-bar">
                <div class="progress-fill" id="progressFill"></div>
            </div>
        </div>

        <form id="wizardForm" method="POST" action="<?= URLROOT ?>/perfil/guardarWizard">
            <!-- Step 1: Security -->
            <div class="wizard-step active" data-step="1">
                <h3 style="font-size: 18px; font-weight: 600; color: var(--color-primary); margin-bottom: 24px;">
                    Cambio de Contraseña
                </h3>

                <!-- Current Password -->
                <div class="form-group has-toggle">
                    <input type="password" name="current_password" id="current_password" class="input-modern" placeholder=" " required style="padding-right: 48px;">
                    <label for="current_password" class="label-floating">
                        <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Contraseña Temporal
                    </label>
                    <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('current_password', this)"></i>
                    <span class="input-hint">Formato: Sgp.TuCédula (Ej: Sgp.12345678)</span>
                </div>

                <!-- New Password -->
                <div class="form-group has-toggle">
                    <input type="password" name="new_password" id="new_password" class="input-modern" placeholder=" " required style="padding-right: 48px;">
                    <label for="new_password" class="label-floating">
                        <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Nueva Contraseña
                    </label>
                    <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('new_password', this)"></i>
                </div>
                <div class="password-strength-container">
                    <div class="password-strength-bar" id="strengthBar"></div>
                    <span class="password-strength-text" id="strengthText"></span>
                </div>

                <!-- Confirm Password -->
                <div class="form-group has-toggle">
                    <input type="password" name="confirm_password" id="confirm_password" class="input-modern" placeholder=" " required style="padding-right: 48px;">
                    <label for="confirm_password" class="label-floating">
                        <i class="ti ti-lock" style="margin-right: 8px; font-size: 18px;"></i>Confirmar Contraseña
                    </label>
                    <i class="ti ti-eye password-toggle" onclick="togglePasswordVisibility('confirm_password', this)"></i>
                </div>
            </div>

            <!-- Step 2: Security Questions -->
            <div class="wizard-step" data-step="2">
                <h3 style="font-size: 18px; font-weight: 600; color: var(--color-primary); margin-bottom: 24px;">
                    Preguntas de Seguridad
                </h3>

                <?php 
                // Assign first 3 questions automatically to avoid duplicates
                $assignedQuestions = array_slice($questions, 0, 3);
                foreach ($assignedQuestions as $index => $q): 
                ?>
                <input type="hidden" name="question_<?= $index + 1 ?>" value="<?= $q['id'] ?>">
                <div class="form-group">
                    <input type="text" name="answer_<?= $index + 1 ?>" id="answer_<?= $index + 1 ?>" class="input-modern" placeholder=" " required>
                    <label for="answer_<?= $index + 1 ?>" class="label-floating">
                        <i class="ti ti-help" style="margin-right: 8px; font-size: 18px;"></i><?= htmlspecialchars($q['pregunta']) ?>
                    </label>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Step 3: Personal Data -->
            <div class="wizard-step" data-step="3">
                <h3 style="font-size: 18px; font-weight: 600; color: var(--color-primary); margin-bottom: 24px;">
                    Datos Personales
                </h3>

                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="nombres" value="<?= htmlspecialchars($user->nombres ?? '') ?>" class="input-modern" placeholder=" " readonly style="background: #F3F4F6;">
                        <label class="label-floating">
                            <i class="ti ti-user" style="margin-right: 8px; font-size: 18px;"></i>Nombres
                        </label>
                    </div>

                    <div class="form-group">
                        <input type="text" name="apellidos" value="<?= htmlspecialchars($user->apellidos ?? '') ?>" class="input-modern" placeholder=" " readonly style="background: #F3F4F6;">
                        <label class="label-floating">
                            <i class="ti ti-user" style="margin-right: 8px; font-size: 18px;"></i>Apellidos
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <input type="text" name="cedula" value="<?= htmlspecialchars($user->cedula ?? '') ?>" class="input-modern" placeholder=" " readonly style="background: #F3F4F6;">
                        <label class="label-floating">
                            <i class="ti ti-id" style="margin-right: 8px; font-size: 18px;"></i>Cédula
                        </label>
                    </div>

                    <div class="form-group">
                        <input type="text" name="departamento" value="<?= htmlspecialchars($user->departamento_nombre ?? 'N/A') ?>" class="input-modern" placeholder=" " readonly style="background: #F3F4F6;">
                        <label class="label-floating">
                            <i class="ti ti-building" style="margin-right: 8px; font-size: 18px;"></i>Departamento
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <input type="tel" name="telefono" class="input-modern" placeholder="0414-1231234" required>
                        <label class="label-floating">
                            <i class="ti ti-phone" style="margin-right: 8px; font-size: 18px;"></i>Teléfono
                        </label>
                    </div>

                    <div class="form-group">
                        <input type="text" name="cargo" class="input-modern" placeholder="Ej: Analista de Soporte">
                        <label class="label-floating">
                            <i class="ti ti-briefcase" style="margin-right: 8px; font-size: 18px;"></i>Cargo <span class="text-muted" style="font-weight: 400;">(Opcional)</span>
                        </label>
                    </div>
                </div>

                <div class="form-row">
                    <div class="form-group">
                        <select name="genero" class="input-modern" required>
                            <option value="">Seleccione...</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                        </select>
                        <label class="label-floating">
                            <i class="ti ti-gender-bigender" style="margin-right: 8px; font-size: 18px;"></i>Género
                        </label>
                    </div>

                    <div class="form-group">
                        <input type="date" name="fecha_nacimiento" class="input-modern" placeholder=" " required>
                        <label class="label-floating">
                            <i class="ti ti-calendar" style="margin-right: 8px; font-size: 18px;"></i>Fecha de Nacimiento
                        </label>
                    </div>
                </div>

                <div class="form-group">
                    <textarea name="direccion" class="input-modern" placeholder="Ej: Av. Táchira, Casa Nro 5..." required rows="3"></textarea>
                    <label class="label-floating">
                        <i class="ti ti-map-pin" style="margin-right: 8px; font-size: 18px;"></i>Dirección
                    </label>
                </div>
            </div>

            <!-- Navigation Buttons -->
            <div class="wizard-buttons">
                <button type="button" class="btn-secondary" id="prevBtn" onclick="changeStep(-1)" style="display: none;">
                    <i class="ti ti-arrow-left"></i> Anterior
                </button>
                <button type="button" class="btn-primary" id="nextBtn" onclick="changeStep(1)">
                    Siguiente <i class="ti ti-arrow-right"></i>
                </button>
                <button type="submit" class="btn-primary" id="submitBtn" style="display: none;">
                    <i class="ti ti-check"></i> Finalizar
                </button>
            </div>
        </form>

        <div class="auth-footer">
            <a href="<?= URLROOT ?>/auth/logout" class="auth-link">
                <i class="ti ti-logout"></i> Cerrar Sesión
            </a>
        </div>
    </div>

    <?php include_once APPROOT . '/views/layouts/footer.php'; ?>

    <style>
        /* Wizard Progress Styles */
        .wizard-progress {
            margin-bottom: 32px;
        }

        .progress-steps {
            display: flex;
            justify-content: space-between;
            margin-bottom: 16px;
            position: relative;
        }

        .step {
            display: flex;
            flex-direction: column;
            align-items: center;
            flex: 1;
            position: relative;
        }

        .step-circle {
            width: 40px;
            height: 40px;
            border-radius: 50%;
            background: #E5E7EB;
            color: #6B7280;
            display: flex;
            align-items: center;
            justify-content: center;
            font-weight: 700;
            margin-bottom: 8px;
            transition: all 0.3s;
            z-index: 2;
        }

        .step.active .step-circle {
            background: var(--color-primary);
            color: white;
        }

        .step.completed .step-circle {
            background: #10B981;
            color: white;
        }

        .step-label {
            font-size: 12px;
            color: #6B7280;
            font-weight: 500;
        }

        .step.active .step-label {
            color: var(--color-primary);
            font-weight: 600;
        }

        .progress-bar {
            height: 4px;
            background: #E5E7EB;
            border-radius: 2px;
            overflow: hidden;
        }

        .progress-fill {
            height: 100%;
            background: var(--color-primary);
            width: 0%;
            transition: width 0.3s ease;
        }

        /* Wizard Steps */
        .wizard-step {
            display: none;
        }

        .wizard-step.active {
            display: block;
            animation: fadeIn 0.3s;
        }

        @keyframes fadeIn {
            from { opacity: 0; transform: translateY(10px); }
            to { opacity: 1; transform: translateY(0); }
        }

        /* Form Row for Grid */
        .form-row {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 16px;
        }

        @media (max-width: 640px) {
            .form-row {
                grid-template-columns: 1fr;
            }
        }

        /* Wizard Buttons */
        .wizard-buttons {
            display: flex;
            gap: 12px;
            margin-top: 32px;
        }

        .wizard-buttons button {
            flex: 1;
        }

        /* Password Strength */
        .password-strength-container {
            margin-top: -12px;
            margin-bottom: 20px;
        }

        .password-strength-bar {
            height: 4px;
            background: #E5E7EB;
            border-radius: 2px;
            overflow: hidden;
            margin-bottom: 4px;
        }

        .password-strength-bar::after {
            content: '';
            display: block;
            height: 100%;
            width: 0%;
            transition: all 0.3s;
        }

        .password-strength-bar.weak::after { width: 33%; background: #EF4444; }
        .password-strength-bar.medium::after { width: 66%; background: #F59E0B; }
        .password-strength-bar.strong::after { width: 100%; background: #10B981; }

        .password-strength-text {
            font-size: 12px;
            color: #6B7280;
        }
    </style>

    <script>
        let currentStep = 1;
        const totalSteps = 3;

        function changeStep(direction) {
            if (direction === 1 && !validateStep(currentStep)) {
                return;
            }

            currentStep += direction;
            if (currentStep < 1) currentStep = 1;
            if (currentStep > totalSteps) currentStep = totalSteps;

            updateUI();
        }

        function validateStep(step) {
            const stepElement = document.querySelector(`.wizard-step[data-step="${step}"]`);
            const inputs = stepElement.querySelectorAll('input[required], select[required], textarea[required]');
            
            for (let input of inputs) {
                if (!input.value) {
                    input.focus();
                    return false;
                }
            }

            if (step === 1) {
                const newPass = document.getElementById('new_password').value;
                const confirmPass = document.getElementById('confirm_password').value;
                
                if (newPass !== confirmPass) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'Las contraseñas no coinciden',
                        confirmButtonColor: '#162660'
                    });
                    return false;
                }
                
                if (newPass.length < 8) {
                    Swal.fire({
                        icon: 'error',
                        title: 'Error',
                        text: 'La contraseña debe tener al menos 8 caracteres',
                        confirmButtonColor: '#162660'
                    });
                    return false;
                }
            }

            return true;
        }

        function updateUI() {
            // Update steps
            document.querySelectorAll('.wizard-step').forEach(step => {
                step.classList.remove('active');
            });
            document.querySelector(`.wizard-step[data-step="${currentStep}"]`).classList.add('active');

            // Update progress circles
            document.querySelectorAll('.step').forEach((step, index) => {
                step.classList.remove('active', 'completed');
                if (index + 1 < currentStep) {
                    step.classList.add('completed');
                } else if (index + 1 === currentStep) {
                    step.classList.add('active');
                }
            });

            // Update progress bar
            const progress = ((currentStep - 1) / (totalSteps - 1)) * 100;
            document.getElementById('progressFill').style.width = progress + '%';

            // Update buttons
            document.getElementById('prevBtn').style.display = currentStep === 1 ? 'none' : 'flex';
            document.getElementById('nextBtn').style.display = currentStep === totalSteps ? 'none' : 'flex';
            document.getElementById('submitBtn').style.display = currentStep === totalSteps ? 'flex' : 'none';
        }

        function togglePasswordVisibility(inputId, icon) {
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

        // Password strength indicator
        document.getElementById('new_password').addEventListener('input', function() {
            const password = this.value;
            const strengthBar = document.getElementById('strengthBar');
            const strengthText = document.getElementById('strengthText');
            
            let strength = 0;
            if (password.length >= 8) strength++;
            if (/[a-z]/.test(password) && /[A-Z]/.test(password)) strength++;
            if (/[0-9]/.test(password)) strength++;
            if (/[^a-zA-Z0-9]/.test(password)) strength++;
            
            strengthBar.className = 'password-strength-bar';
            if (strength <= 1) {
                strengthBar.classList.add('weak');
                strengthText.textContent = 'Débil';
                strengthText.style.color = '#EF4444';
            } else if (strength <= 3) {
                strengthBar.classList.add('medium');
                strengthText.textContent = 'Media';
                strengthText.style.color = '#F59E0B';
            } else {
                strengthBar.classList.add('strong');
                strengthText.textContent = 'Fuerte';
                strengthText.style.color = '#10B981';
            }
        });

        // Form submission
        document.getElementById('wizardForm').addEventListener('submit', function(e) {
            e.preventDefault();
            
            if (!validateStep(currentStep)) {
                return;
            }
            
            const btn = document.getElementById('submitBtn');
            const originalText = btn.innerHTML;
            btn.innerHTML = '<i class="ti ti-loader animate-spin"></i> Guardando...';
            btn.disabled = true;
            
            this.submit();
        });
    </script>
</body>
</html>
