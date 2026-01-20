// ====================================================
// SISTEMA DE VALIDACIÓN EN TIEMPO REAL - SGP
// ====================================================

/**
 * Validar Email en tiempo real
 * @param {HTMLInputElement} input - Input de email
 * @returns {boolean|null} true si válido, false si inválido, null si vacío
 */
function validateEmail(input) {
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    const value = input.value.trim();

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
}

/**
 * Calcular fortaleza de contraseña
 * @param {string} password - Contraseña a evaluar
 * @returns {string} 'weak', 'medium' o 'strong'
 */
function calculatePasswordStrength(password) {
    let strength = 0;

    // Criterios de fortaleza
    if (password.length >= 8) strength++;
    if (password.length >= 12) strength++;
    if (/[a-z]/.test(password)) strength++;
    if (/[A-Z]/.test(password)) strength++;
    if (/[0-9]/.test(password)) strength++;
    if (/[^a-zA-Z0-9]/.test(password)) strength++;

    if (strength <= 2) return 'weak';
    if (strength <= 4) return 'medium';
    return 'strong';
}

/**
 * Actualizar indicador de fortaleza de contraseña
 * @param {HTMLInputElement} input - Input de contraseña
 * @param {HTMLElement} strengthBar - Barra de fortaleza
 * @param {HTMLElement} strengthText - Texto de fortaleza
 */
function updatePasswordStrength(input, strengthBar, strengthText) {
    const password = input.value;

    if (password.length === 0) {
        strengthBar.className = 'password-strength-bar';
        strengthText.textContent = '';
        input.classList.remove('valid', 'invalid');
        return;
    }

    const strength = calculatePasswordStrength(password);
    strengthBar.className = `password-strength-bar ${strength}`;

    const messages = {
        weak: 'Débil',
        medium: 'Media',
        strong: 'Fuerte'
    };

    strengthText.textContent = messages[strength];
    strengthText.className = `password-strength-text ${strength}`;

    // Validar longitud mínima
    if (password.length >= 6) {
        input.classList.remove('invalid');
        input.classList.add('valid');
    } else {
        input.classList.remove('valid');
        input.classList.add('invalid');
    }
}

/**
 * Validar que dos contraseñas coincidan
 * @param {HTMLInputElement} password - Input de contraseña original
 * @param {HTMLInputElement} confirmation - Input de confirmación
 * @returns {boolean|null} true si coinciden, false si no, null si vacío
 */
function validatePasswordMatch(password, confirmation) {
    const value = confirmation.value;

    if (value.length === 0) {
        confirmation.classList.remove('valid', 'invalid');
        return null;
    }

    if (value === password.value) {
        confirmation.classList.remove('invalid');
        confirmation.classList.add('valid');
        return true;
    } else {
        confirmation.classList.remove('valid');
        confirmation.classList.add('invalid');
        return false;
    }
}

/**
 * Toggle de visibilidad de contraseña
 * @param {string} inputId - ID del input de contraseña
 * @param {HTMLElement} iconElement - Elemento del icono
 */
function togglePasswordVisibility(inputId, iconElement) {
    const input = document.getElementById(inputId);

    if (input.type === 'password') {
        input.type = 'text';
        iconElement.classList.remove('ti-eye');
        iconElement.classList.add('ti-eye-off');
    } else {
        input.type = 'password';
        iconElement.classList.remove('ti-eye-off');
        iconElement.classList.add('ti-eye');
    }
}

/**
 * Validar longitud mínima de contraseña
 * @param {HTMLInputElement} input - Input de contraseña
 * @param {number} minLength - Longitud mínima (default: 6)
 */
function validatePasswordLength(input, minLength = 6) {
    const value = input.value;

    if (value.length === 0) {
        input.classList.remove('valid', 'invalid');
        return null;
    }

    if (value.length >= minLength) {
        input.classList.remove('invalid');
        input.classList.add('valid');
        return true;
    } else {
        input.classList.remove('valid');
        input.classList.add('invalid');
        return false;
    }
}

// ====================================================
// VALIDACIÓN DE REQUISITOS DE CONTRASEÑA
// ====================================================

/**
 * Validar requisitos de contraseña y actualizar checklist visual
 * @param {string} password - Contraseña a validar
 * @returns {boolean} true si cumple todos los requisitos
 */
function validatePasswordRequirements(password) {
    const requirements = {
        length: password.length >= 8,
        uppercase: /[A-Z]/.test(password),
        lowercase: /[a-z]/.test(password),
        number: /[0-9]/.test(password),
        special: /[!@#$%^&*()_+\-=\[\]{};':\"\\|,.<>\/?]/.test(password)
    };

    // Actualizar UI si existen los elementos
    const reqLength = document.getElementById('req-length');
    const reqUppercase = document.getElementById('req-uppercase');
    const reqLowercase = document.getElementById('req-lowercase');
    const reqNumber = document.getElementById('req-number');
    const reqSpecial = document.getElementById('req-special');

    if (reqLength) reqLength.classList.toggle('met', requirements.length);
    if (reqUppercase) reqUppercase.classList.toggle('met', requirements.uppercase);
    if (reqLowercase) reqLowercase.classList.toggle('met', requirements.lowercase);
    if (reqNumber) reqNumber.classList.toggle('met', requirements.number);
    if (reqSpecial) reqSpecial.classList.toggle('met', requirements.special);

    // Retornar si todos los requisitos se cumplen
    return Object.values(requirements).every(req => req === true);
}

/**
 * Actualizar fortaleza con requisitos estrictos
 * @param {HTMLInputElement} input - Input de contraseña
 * @param {HTMLElement} strengthBar - Barra de fortaleza
 * @param {HTMLElement} strengthText - Texto de fortaleza
 */
function updatePasswordStrengthWithRequirements(input, strengthBar, strengthText) {
    const password = input.value;

    if (password.length === 0) {
        strengthBar.className = 'password-strength-bar';
        strengthText.textContent = '';
        input.classList.remove('valid', 'invalid');
        return;
    }

    // Validar requisitos
    const meetsRequirements = validatePasswordRequirements(password);

    // Calcular fortaleza
    const strength = calculatePasswordStrength(password);
    strengthBar.className = `password-strength-bar ${strength}`;

    const messages = {
        weak: 'Débil',
        medium: 'Media',
        strong: 'Fuerte'
    };

    strengthText.textContent = messages[strength];
    strengthText.className = `password-strength-text ${strength}`;

    // Validar que cumpla requisitos mínimos
    if (meetsRequirements) {
        input.classList.remove('invalid');
        input.classList.add('valid');
    } else {
        input.classList.remove('valid');
        input.classList.add('invalid');
    }
}

// ====================================================
// MULTI-STEP FORM NAVIGATION
// ====================================================

let currentStep = 1;

/**
 * Mostrar un paso específico del formulario
 * @param {number} step - Número del paso a mostrar
 */
function showStep(step) {
    // Ocultar todos los pasos
    document.querySelectorAll('.form-step').forEach((el, index) => {
        if (index + 1 === step) {
            el.classList.add('active');
            el.style.display = 'block';
        } else {
            el.classList.remove('active');
            el.style.display = 'none';
        }
    });

    // Actualizar indicador de pasos
    document.querySelectorAll('.step').forEach((el, index) => {
        if (index + 1 < step) {
            el.classList.add('completed');
            el.classList.remove('active');
        } else if (index + 1 === step) {
            el.classList.add('active');
            el.classList.remove('completed');
        } else {
            el.classList.remove('active', 'completed');
        }
    });

    currentStep = step;
}

/**
 * Validar el paso actual antes de avanzar
 * @returns {boolean} true si el paso es válido
 */
function validateCurrentStep() {
    if (currentStep === 1) {
        const name = document.getElementById('name');
        const email = document.getElementById('email');
        const password = document.getElementById('reg_password');
        const confirm = document.getElementById('password_confirm');

        // Validar que todos los campos estén llenos
        if (!name.value || !email.value || !password.value || !confirm.value) {
            Swal.fire({
                icon: 'warning',
                title: 'Campos incompletos',
                text: 'Por favor completa todos los campos',
                confirmButtonColor: '#162660'
            });
            return false;
        }

        // Validar email
        if (!validateEmail(email)) {
            Swal.fire({
                icon: 'error',
                title: 'Email inválido',
                text: 'Por favor ingresa un correo electrónico válido',
                confirmButtonColor: '#162660'
            });
            return false;
        }

        // Validar requisitos de contraseña
        if (!validatePasswordRequirements(password.value)) {
            Swal.fire({
                icon: 'error',
                title: 'Contraseña débil',
                text: 'La contraseña debe cumplir todos los requisitos de seguridad',
                confirmButtonColor: '#162660'
            });
            return false;
        }

        // Validar que las contraseñas coincidan
        if (password.value !== confirm.value) {
            Swal.fire({
                icon: 'error',
                title: 'Contraseñas no coinciden',
                text: 'Las contraseñas deben ser idénticas',
                confirmButtonColor: '#162660'
            });
            return false;
        }

        return true;
    }

    return true;
}

/**
 * Avanzar al siguiente paso
 */
function nextStep() {
    if (validateCurrentStep()) {
        showStep(currentStep + 1);
    }
}

/**
 * Retroceder al paso anterior
 */
function prevStep() {
    showStep(currentStep - 1);
}
