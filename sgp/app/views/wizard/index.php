<?php
/**
 * wizard/index.php — SGP Onboarding (4 pasos)
 *
 * FASE 1: Eliminado campo "direccion"
 * FASE 2: Paso 3 muestra Tarjeta de Identidad (nombres/apellidos del admin)
 *         + solo inputs de Teléfono, Fecha Nacimiento, Género
 * FASE 3: Lógica Camaleónica:
 *   - requiere_cambio_clave == 1 → 4 pasos normales (creado por Admin)
 *   - requiere_cambio_clave == 0 → aterriza en paso 3, pasos 1 y 2 marcados ✓
 *   - rol_id == 3 (Pasante)     → paso 4: institución + PIN
 *   - rol_id == 1|2 (Admin/Tutor) → paso 4: solo departamento
 */

$creadoPorAdmin = ($data['user']->requiere_cambio_clave ?? 1) == 1;
$rolId          = (int)($data['user']->rol_id ?? 0);
$userNombres    = htmlspecialchars($data['user']->nombres   ?? '');
$userApellidos  = htmlspecialchars($data['user']->apellidos ?? '');
$userCedula     = htmlspecialchars($data['user']->cedula    ?? 'No registrada');
$userCorreo     = htmlspecialchars($data['user']->correo    ?? '');

// Estado inicial de los indicadores
$si1Class = $creadoPorAdmin ? 'active' : 'completed';
$si2Class = $creadoPorAdmin ? ''       : 'completed';
$si3Class = $creadoPorAdmin ? ''       : 'active';

// Cuál paso mostrar al cargar
$startStep1 = $creadoPorAdmin ? 'block' : 'none';
$startStep2 = 'none';
$startStep3 = $creadoPorAdmin ? 'none' : 'block';
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Configuración de Seguridad | SGP</title>

    <!-- Favicon -->
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/img/favicon.png">

    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/flatpickr-sgp.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/choices/choices.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/choices-sgp.css">

    <style>
        * { margin: 0; padding: 0; box-sizing: border-box; }
        body, html { height: 100% !important; margin: 0 !important; padding: 0 !important;
            font-family: 'Inter', -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif; }
        body.auth-wrapper {
            margin-left: 0 !important; margin-top: 0 !important; padding-left: 0 !important;
            justify-content: flex-start !important; padding-top: 140px !important;
        }
        .content-wrapper { margin-left: 0 !important; }

        /* Hint texto sin cursiva */
        .password-hint {
            font-size: 0.85rem;
            color: #6c757d;
            /* NO font-style italic */
        }

        /* ── Alerta informativa INFO (glassmorphism suave) ──── */
        .info-alert {
            background: linear-gradient(135deg, rgba(37,99,235,0.08), rgba(37,99,235,0.04));
            border: 1.5px solid rgba(37,99,235,0.2);
            border-radius: 12px;
            padding: 14px 16px;
            display: flex;
            gap: 12px;
            align-items: flex-start;
            margin-bottom: 1rem;
        }
        .info-alert-icon { font-size: 1.3rem; color: #2563eb; flex-shrink: 0; margin-top: 2px; }
        .info-alert-body { font-size: 0.84rem; color: #374151; line-height: 1.55; }
        .info-alert-body strong { color: #1d4ed8; }

        /* ── Campo readonly bloqueado ─────────────────────── */
        .input-readonly {
            background: #f1f5f9 !important;
            color: #64748b !important;
            border: 1.5px solid #e2e8f0 !important;
            cursor: not-allowed !important;
            padding: 14px 16px;
            border-radius: 10px;
            width: 100%;
            font-size: 0.95rem;
        }
        .input-readonly-wrap { position: relative; }
        .input-readonly-lock {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            color: #94a3b8; font-size: 1rem;
        }
        .readonly-label {
            display: block; margin-bottom: 6px; font-weight: 600;
            color: #374151; font-size: 0.88rem;
        }
        .readonly-badge {
            display: inline-block; font-size: 0.72rem; background: #e2e8f0;
            color: #64748b; border-radius: 20px; padding: 2px 8px; margin-left: 6px;
            font-weight: 500; vertical-align: middle;
        }

        /* ── Tarjeta de Identidad Sutil (Paso 3) ─────────── */
        .identity-card {
            background: #f8fafc;
            border: 1.5px solid #e2e8f0;
            border-radius: 14px;
            padding: 16px 20px;
            margin-bottom: 1.4rem;
        }
        .identity-card-header {
            display: flex;
            align-items: center;
            gap: 8px;
            font-size: 0.82rem;
            font-weight: 700;
            color: #64748b;
            text-transform: uppercase;
            letter-spacing: 0.04em;
            margin-bottom: 14px;
            padding-bottom: 10px;
            border-bottom: 1px solid #e2e8f0;
        }
        .identity-card-header i { font-size: 1rem; color: #94a3b8; }
        .identity-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 10px 20px;
        }
        .identity-field { }
        .identity-field-label {
            font-size: 0.73rem;
            color: #94a3b8;
            font-weight: 600;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 2px;
        }
        .identity-field-value {
            font-size: 0.95rem;
            font-weight: 600;
            color: #334155;
        }
        .identity-field.full { grid-column: 1 / -1; }

        /* ── Grid 2 columnas para campos del Paso 3 ─────── */
        .step3-grid {
            display: grid;
            grid-template-columns: 1fr 1fr;
            gap: 0 1rem;
        }
        .step3-grid .form-group { margin-bottom: 1rem; }
        .step3-grid .form-group.full-col { grid-column: 1 / -1; }

        /* ── PIN toggle wrapper ───────────────────────────── */
        .pin-wrapper { position: relative; }
        .pin-toggle {
            position: absolute; right: 14px; top: 50%; transform: translateY(-50%);
            background: none; border: none; cursor: pointer;
            color: #6b7280; font-size: 1.1rem; padding: 0;
            transition: color 0.2s;
        }
        .pin-toggle:hover { color: #162660; }

        /* ── Grid para Preguntas de Seguridad (Tarea 2) - ELIMINADO POR BENTO BOX ───── */
        /* Eliminado para usar diseño estándar en vertical acorde a choices.js */

        /* ── 4 pasos en la barra de progreso ────────────── */
        .steps-indicator { gap: 6px; }

        /* ── Indicador completado (check verde) ─────────── */
        .step.completed .step-number {
            background: #10b981 !important;
            border-color: #10b981 !important;
            color: #fff !important;
        }
        .step.completed .step-number::after {
            content: '✓';
        }
    </style>

    <script>
        // ── Toggle contraseñas ──────────────────────────────────────
        window.togglePass = function(fieldId, icon) {
            var input = document.getElementById(fieldId);
            if (!input) return;
            if (input.type === 'password') {
                input.type = 'text';
                icon.classList.replace('ti-eye', 'ti-eye-off');
            } else {
                input.type = 'password';
                icon.classList.replace('ti-eye-off', 'ti-eye');
            }
        };

        // ── Navegación genérica ─────────────────────────────────────
        function showStep(show, hide, fromId, toId) {
            document.getElementById(hide).style.display = 'none';
            var el = document.getElementById(show);
            el.style.display = 'block';
            el.classList.add('slide-up');
            window.scrollTo(0, 0);
            var fromEl = document.getElementById(fromId);
            var toEl   = document.getElementById(toId);
            if (fromEl) { fromEl.classList.remove('active'); fromEl.classList.add('completed'); }
            if (toEl)   { toEl.classList.remove('completed'); toEl.classList.add('active'); }
        }

        function goBack(show, hide, fromId, toId) {
            document.getElementById(hide).style.display = 'none';
            var el = document.getElementById(show);
            el.style.display = 'block';
            el.classList.add('fade-in');
            window.scrollTo(0, 0);
            var fromEl = document.getElementById(fromId);
            var toEl   = document.getElementById(toId);
            if (fromEl) { fromEl.classList.add('active'); fromEl.classList.remove('completed'); }
            if (toEl)   { toEl.classList.remove('active'); }
        }

        // Paso 1 → 2
        window.nextStep = function() {
            if (!window.validarPaso1()) return;
            showStep('step2','step1','si-step1','si-step2');
        };
        window.prevStep = function() {
            goBack('step1','step2','si-step1','si-step2');
        };

        // Paso 2 → 3
        window.nextStep2 = function() {
            if (!window.validarPreguntasSeguridad()) return;
            showStep('step3','step2','si-step2','si-step3');
        };
        window.prevStep2 = function() {
            goBack('step2','step3','si-step2','si-step3');
        };

        // Paso 3 → 4
        window.nextStep3 = function() {
            if (!window.validarPaso3()) return;
            showStep('step4','step3','si-step3','si-step4');
        };
        window.prevStep3 = function() {
            goBack('step3','step4','si-step3','si-step4');
        };

        // ── Validar Paso 1 (contraseña) ─────────────────────────────
        window.validarPaso1 = function() {
            var cur = document.getElementById('current_password');
            var np  = document.getElementById('new_password');
            var cp  = document.getElementById('confirm_password');
            if (!cur || !cur.value.trim()) { NotificationService.warning('Ingresa tu contraseña actual'); cur && cur.focus(); return false; }
            if (!np  || !np.value)         { NotificationService.warning('Ingresa una nueva contraseña'); np && np.focus(); return false; }
            if (np.value.length < 8)       { NotificationService.error('La contraseña debe tener al menos 8 caracteres'); np.focus(); return false; }
            if (!cp || !cp.value)          { NotificationService.warning('Confirma tu nueva contraseña'); cp && cp.focus(); return false; }
            if (np.value !== cp.value)     { NotificationService.error('Las contraseñas no coinciden'); cp.focus(); return false; }
            return true;
        };

        // ── Validar Paso 3 (solo los campos editables) ───────────────
        window.validarPaso3 = function() {
            var telefono = document.getElementById('telefono');
            var fnac     = document.getElementById('fecha_nacimiento');
            var genero   = document.getElementById('genero');
            if (!telefono || !telefono.value.trim())  { NotificationService.warning('El campo Teléfono es obligatorio'); telefono && telefono.focus(); return false; }
            if (!fnac     || !fnac.value)             { NotificationService.warning('La Fecha de Nacimiento es obligatoria'); fnac && fnac.focus(); return false; }
            if (!genero   || !genero.value)           { NotificationService.warning('Selecciona tu género'); genero && genero.focus(); return false; }
            return true;
        };

        // ── Validar Paso 4 (perfil profesional) ────────────────────
        window.validarPaso4 = function() {
            var rolId = parseInt(document.getElementById('wizard_rol_id')?.value || '0');
            if (rolId === 3) {
                var pin  = document.getElementById('pin_asistencia');
                var inst = document.getElementById('institucion_id');
                if (!pin || !/^[0-9]{4}$/.test(pin.value)) { NotificationService.warning('El PIN debe tener exactamente 4 dígitos numéricos.'); pin && pin.focus(); return false; }
                if (!inst || !inst.value) { NotificationService.warning('Selecciona tu institución de procedencia.'); inst && inst.focus(); return false; }
            } else {
                var dept = document.getElementById('departamento_id');
                if (!dept || !dept.value) { NotificationService.warning('Selecciona tu departamento.'); dept && dept.focus(); return false; }
            }
            return true;
        };

        // ── Submit final ────────────────────────────────────────────
        window.submitWizard = function(btn) {
            if (!window.validarPaso4()) return;
            btn.innerHTML = '<i class="ti ti-loader"></i> Guardando...';
            btn.disabled = true;
            document.getElementById('wizardForm').submit();
        };

        // ── Fortaleza contraseña ────────────────────────────────────
        window.actualizarFortaleza = function(input) {
            var pw = input.value;
            var bar = document.getElementById('strengthBar');
            var txt = document.getElementById('strengthText');
            if (!bar || !txt) return;
            if (!pw.length) { bar.className = 'password-strength-bar'; txt.textContent = ''; return; }
            var s = 0;
            if (pw.length >= 8) s++;
            if (/[a-z]/.test(pw)) s++;
            if (/[A-Z]/.test(pw)) s++;
            if (/[0-9]/.test(pw)) s++;
            if (/[!@#$%^&*(),.?":{}<>]/.test(pw)) s++;
            if (s <= 2) { bar.className = 'password-strength-bar weak'; txt.textContent = 'Débil'; txt.style.color = '#ef4444'; }
            else if (s <= 4) { bar.className = 'password-strength-bar medium'; txt.textContent = 'Media'; txt.style.color = '#f59e0b'; }
            else { bar.className = 'password-strength-bar strong'; txt.textContent = 'Fuerte'; txt.style.color = '#10b981'; }
            ['req-length','req-uppercase','req-lowercase','req-number','req-special'].forEach(function(id, i) {
                var el = document.getElementById(id);
                if (!el) return;
                var checks = [pw.length>=8, /[A-Z]/.test(pw), /[a-z]/.test(pw), /[0-9]/.test(pw), /[!@#$%^&*(),.?":{}<>]/.test(pw)];
                el.classList.toggle('met', checks[i]);
            });
        };

        window.validarConfirmacion = function(passInput, confirmInput) {
            if (!confirmInput.value) return null;
            if (passInput.value === confirmInput.value) {
                confirmInput.classList.remove('invalid'); confirmInput.classList.add('valid'); return true;
            } else {
                confirmInput.classList.remove('valid'); confirmInput.classList.add('invalid'); return false;
            }
        };

        window.validarPreguntasSeguridad = function() {
            var p1 = document.getElementById('pregunta_1');
            var p2 = document.getElementById('pregunta_2');
            var p3 = document.getElementById('pregunta_3');
            var r1 = document.getElementById('respuesta_1');
            var r2 = document.getElementById('respuesta_2');
            var r3 = document.getElementById('respuesta_3');
            if (!p1?.value) { NotificationService.warning('Selecciona la Pregunta 1'); p1?.focus(); return false; }
            if (!p2?.value) { NotificationService.warning('Selecciona la Pregunta 2'); p2?.focus(); return false; }
            if (!p3?.value) { NotificationService.warning('Selecciona la Pregunta 3'); p3?.focus(); return false; }
            if (new Set([p1.value, p2.value, p3.value]).size !== 3) {
                NotificationService.error('No puedes seleccionar la misma pregunta más de una vez'); return false; }
            if (!r1?.value.trim()) { NotificationService.warning('Ingresa la Respuesta 1'); r1?.focus(); return false; }
            if (!r2?.value.trim()) { NotificationService.warning('Ingresa la Respuesta 2'); r2?.focus(); return false; }
            if (!r3?.value.trim()) { NotificationService.warning('Ingresa la Respuesta 3'); r3?.focus(); return false; }
            return true;
        };

        window.confirmarSalida = function(e) {
            e.preventDefault();
            Swal.fire({
                title: '¿Estás seguro de que deseas cerrar sesión?',
                icon: 'question', showCancelButton: true,
                confirmButtonColor: '#162660', cancelButtonColor: '#6c757d',
                confirmButtonText: 'Aceptar', cancelButtonText: 'Cancelar', reverseButtons: true
            }).then(r => { if (r.isConfirmed) window.location.href = '<?= URLROOT ?>/auth/logout'; });
        };
    </script>
</head>
<body class="auth-wrapper">
    <?php include_once APPROOT . '/views/layouts/header_strip.php'; ?>

    <div class="auth-card">
        <div class="auth-header">
            <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" class="auth-logo">
            <h1 class="auth-title">Configuración de Seguridad</h1>
            <p class="auth-subtitle">Completa tu perfil de seguridad en 4 sencillos pasos</p>
        </div>

        <!-- ── Indicador de 4 pasos ── -->
        <div class="steps-indicator">
            <div class="step <?= $si1Class ?>" id="si-step1">
                <div class="step-number">1</div>
                <div class="step-label">Contraseña</div>
            </div>
            <div class="step-divider"></div>
            <div class="step <?= $si2Class ?>" id="si-step2">
                <div class="step-number">2</div>
                <div class="step-label">Seguridad</div>
            </div>
            <div class="step-divider"></div>
            <div class="step <?= $si3Class ?>" id="si-step3">
                <div class="step-number">3</div>
                <div class="step-label">Datos</div>
            </div>
            <div class="step-divider"></div>
            <div class="step" id="si-step4">
                <div class="step-number">4</div>
                <div class="step-label">Perfil</div>
            </div>
        </div>

        <?php if (Session::hasFlash('error')): ?>
        <div style="margin-bottom:1.5rem;padding:12px 16px;border-radius:8px;background-color:#fee;border-left:4px solid #dc3545;">
            <i class="ti ti-alert-circle" style="margin-right:8px;"></i>
            <strong>Error:</strong> <?= Session::getFlash('error') ?>
        </div>
        <?php endif; ?>

        <?php if (Session::hasFlash('success')): ?>
        <div style="margin-bottom:1.5rem;padding:12px 16px;border-radius:8px;background-color:#d4edda;border-left:4px solid #28a745;">
            <i class="ti ti-check-circle" style="margin-right:8px;"></i>
            <strong>¡Éxito!</strong> <?= Session::getFlash('success') ?>
        </div>
        <?php endif; ?>

        <form action="<?= URLROOT ?>/wizard/procesar" method="POST" id="wizardForm">

            <!-- ══════════════════════════════════════════════════════
                 PASO 1: CONTRASEÑA
            ═══════════════════════════════════════════════════════ -->
            <div id="step1" style="display:<?= $startStep1 ?>;">
                <div class="form-group has-toggle">
                    <input type="password" name="current_password" id="current_password" class="input-modern" placeholder=" " required>
                    <label for="current_password" class="label-floating">
                        <i class="ti ti-key" style="margin-right:8px;font-size:18px;"></i>Contraseña Actual
                    </label>
                    <i class="ti ti-eye password-toggle" onclick="window.togglePass('current_password', this)"></i>
                </div>
                <span class="password-hint" style="display:block;margin-top:-16px;margin-bottom:16px;">
                    Usa tu contraseña temporal: <strong>Sgp.TuCédula</strong> (Ejemplo: Sgp.12345678)
                </span>

                <div class="form-group has-toggle">
                    <input type="password" name="new_password" id="new_password" class="input-modern" placeholder=" " required oninput="window.actualizarFortaleza(this)">
                    <label for="new_password" class="label-floating">
                        <i class="ti ti-lock" style="margin-right:8px;font-size:18px;"></i>Nueva Contraseña
                    </label>
                    <i class="ti ti-eye password-toggle" onclick="window.togglePass('new_password', this)"></i>
                </div>
                <span class="password-hint" style="display:block;margin-top:-16px;margin-bottom:8px;">
                    Mín. 8 caracteres: mayúscula, minúscula, número y símbolo
                </span>

                <div class="password-strength">
                    <div class="password-strength-bar" id="strengthBar"></div>
                </div>
                <div class="password-strength-text" id="strengthText"></div>

                <div class="password-requirements">
                    <div class="requirement" id="req-length"><i class="ti ti-circle"></i><span>Mínimo 8 caracteres</span></div>
                    <div class="requirement" id="req-uppercase"><i class="ti ti-circle"></i><span>Una letra mayúscula (A-Z)</span></div>
                    <div class="requirement" id="req-lowercase"><i class="ti ti-circle"></i><span>Una letra minúscula (a-z)</span></div>
                    <div class="requirement" id="req-number"><i class="ti ti-circle"></i><span>Un número (0-9)</span></div>
                    <div class="requirement" id="req-special"><i class="ti ti-circle"></i><span>Un carácter especial (!@#$%^&*)</span></div>
                </div>

                <div class="form-group has-toggle" style="margin-top:1rem;">
                    <input type="password" name="confirm_password" id="confirm_password" class="input-modern" placeholder=" " required
                        oninput="window.validarConfirmacion(document.getElementById('new_password'), this)">
                    <label for="confirm_password" class="label-floating">
                        <i class="ti ti-lock-check" style="margin-right:8px;font-size:18px;"></i>Confirmar Nueva Contraseña
                    </label>
                    <i class="ti ti-eye password-toggle" onclick="window.togglePass('confirm_password', this)"></i>
                </div>

                <button type="button" class="btn-primary" onclick="window.nextStep()" style="margin-top:1rem;">
                    Siguiente <i class="ti ti-arrow-right" style="margin-left:8px;"></i>
                </button>
            </div>

            <!-- ══════════════════════════════════════════════════════
                 PASO 2: PREGUNTAS DE SEGURIDAD
            ═══════════════════════════════════════════════════════ -->
            <div id="step2" style="display:<?= $startStep2 ?>;">
                <h3 style="font-size:1rem;margin-bottom:20px;color:var(--color-primary);font-weight:600;">
                    <i class="ti ti-shield-check" style="margin-right:8px;font-size:18px;"></i>Preguntas de Seguridad
                </h3>
                <p style="color:#6c757d;font-size:0.9rem;margin-bottom:1.5rem;">
                    Selecciona 3 preguntas diferentes y proporciona tus respuestas.
                </p>

                <div class="preguntas-container">
                <?php if (!empty($questions)):
                    $lista = $questions ?? [];
                    for ($i = 1; $i <= 3; $i++): ?>
                    <div style="margin-bottom: 1.5rem;">
                        <div class="form-group" style="margin-bottom: 0.8rem;">
                            <label for="pregunta_<?= $i ?>" class="form-label" style="display:block;margin-bottom:.5rem;font-weight:600;color:#374151;">
                                <i class="ti ti-help-circle" style="margin-right:6px;"></i>Pregunta de Seguridad <?= $i ?> *
                            </label>
                            <select name="question_<?= $i ?>" id="pregunta_<?= $i ?>" class="input-modern select-pregunta" required style="cursor:pointer;">
                                <option value="">Seleccione una pregunta...</option>
                                <?php foreach ($lista as $item):
                                    $qid  = is_object($item) ? $item->id      : $item['id'];
                                    $qtxt = is_object($item) ? $item->pregunta : $item['pregunta'];
                                ?>
                                <option value="<?= $qid ?>"><?= htmlspecialchars($qtxt) ?></option>
                                <?php endforeach; ?>
                            </select>
                        </div>
                        <div class="form-group" style="margin-bottom:0;">
                            <input type="text" name="answer_<?= $i ?>" id="respuesta_<?= $i ?>" class="input-modern" placeholder=" " required>
                            <label for="respuesta_<?= $i ?>" class="label-floating">
                                <i class="ti ti-message" style="margin-right:8px;font-size:18px;"></i>Tu Respuesta <?= $i ?>
                            </label>
                        </div>
                    </div>
                    <?php endfor; ?>
                <?php else: ?>
                    <div style="padding:12px 16px;border-radius:8px;background:#fee;border-left:4px solid #dc3545;margin-bottom:1.5rem;">
                        <i class="ti ti-alert-circle"></i> Error: No se pudieron cargar las preguntas de seguridad.
                    </div>
                <?php endif; ?>
                </div>

                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <button type="button" class="btn-secondary" onclick="window.prevStep()" style="flex:1;">
                        <i class="ti ti-arrow-left" style="margin-right:8px;"></i>Atrás
                    </button>
                    <button type="button" class="btn-primary" onclick="window.nextStep2()" style="flex:1;">
                        Siguiente <i class="ti ti-arrow-right" style="margin-left:8px;"></i>
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════
                 PASO 3: DATOS PERSONALES
                 FASE 2: Tarjeta de Identidad + solo campos editables
            ═══════════════════════════════════════════════════════ -->
            <div id="step3" style="display:<?= $startStep3 ?>;">
                <h3 style="font-size:1rem;margin-bottom:6px;color:var(--color-primary);font-weight:600;">
                    <i class="ti ti-user-circle" style="margin-right:8px;font-size:18px;"></i>Datos Personales
                </h3>
                <p style="color:#6c757d;font-size:0.88rem;margin-bottom:1.2rem;">
                    Completa tu información personal. Teléfono, Fecha de Nacimiento y Género son requeridos.
                </p>

                <!-- ── Tarjeta de Identidad Sutil ── -->
                <div class="identity-card">
                    <div class="identity-card-header">
                        <i class="ti ti-lock"></i>
                        Datos oficiales asignados por Administración
                    </div>
                    <div class="identity-grid">
                        <div class="identity-field">
                            <div class="identity-field-label">Nombre(s)</div>
                            <div class="identity-field-value"><?= $userNombres ?: '<span style="color:#94a3b8;">—</span>' ?></div>
                        </div>
                        <div class="identity-field">
                            <div class="identity-field-label">Apellido(s)</div>
                            <div class="identity-field-value"><?= $userApellidos ?: '<span style="color:#94a3b8;">—</span>' ?></div>
                        </div>
                        <div class="identity-field">
                            <div class="identity-field-label">Cédula de Identidad</div>
                            <div class="identity-field-value"><?= $userCedula ?></div>
                        </div>
                        <div class="identity-field">
                            <div class="identity-field-label">Correo Electrónico</div>
                            <div class="identity-field-value" style="font-size:0.85rem;word-break:break-all;"><?= $userCorreo ?></div>
                        </div>
                    </div>
                </div>

                <!-- ── Campos editables en 2 columnas ── -->
                <div class="step3-grid">
                    <!-- Teléfono — ocupa columna completa -->
                    <div class="form-group full-col">
                        <input type="tel" name="telefono" id="telefono" class="input-modern" placeholder=" " required inputmode="numeric">
                        <label for="telefono" class="label-floating">
                            <i class="ti ti-phone" style="margin-right:8px;font-size:18px;"></i>Teléfono *
                        </label>
                        <span class="password-hint" style="display:block;margin-top:6px;">
                            Formato: 0414-1234567
                        </span>
                    </div>

                    <!-- Fecha de Nacimiento -->
                    <div class="form-group">
                        <label class="readonly-label" style="font-weight:600;color:#374151;">
                            <i class="ti ti-calendar" style="margin-right:6px;"></i>Fecha de Nacimiento *
                        </label>
                        <input type="date" name="fecha_nacimiento" id="fecha_nacimiento" class="input-modern" required
                            max="<?= date('Y-m-d', strtotime('-14 years')) ?>"
                            style="padding:14px 16px;">
                    </div>

                    <!-- Género -->
                    <div class="form-group">
                        <label class="readonly-label" style="font-weight:600;color:#374151;">
                            <i class="ti ti-gender-androgyne" style="margin-right:6px;"></i>Género *
                        </label>
                        <select name="genero" id="genero" class="input-modern" required style="cursor:pointer;">
                            <option value="">Selecciona...</option>
                            <option value="M">Masculino</option>
                            <option value="F">Femenino</option>
                            <option value="Otro">Prefiero no decirlo</option>
                        </select>
                    </div>
                </div>

                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <?php if ($creadoPorAdmin): ?>
                    <button type="button" class="btn-secondary" onclick="window.prevStep2()" style="flex:1;">
                        <i class="ti ti-arrow-left" style="margin-right:8px;"></i>Atrás
                    </button>
                    <?php endif; ?>
                    <button type="button" class="btn-primary" onclick="window.nextStep3()" style="flex:<?= $creadoPorAdmin ? '1' : '2' ?>;">
                        Siguiente <i class="ti ti-arrow-right" style="margin-left:8px;"></i>
                    </button>
                </div>
            </div>

            <!-- ══════════════════════════════════════════════════════
                 PASO 4: PERFIL PROFESIONAL — LÓGICA CAMALEÓNICA
                 rol_id == 3  → Institución + PIN
                 rol_id != 3  → solo Departamento
            ═══════════════════════════════════════════════════════ -->
            <div id="step4" style="display:none;">
                <input type="hidden" id="wizard_rol_id" value="<?= $rolId ?>">

                <h3 style="font-size:1rem;margin-bottom:6px;color:var(--color-primary);font-weight:600;">
                    <i class="ti ti-id-badge-2" style="margin-right:8px;font-size:18px;"></i>Perfil Profesional
                </h3>
                <p style="color:#6c757d;font-size:0.88rem;margin-bottom:1.5rem;">
                    <?php if ($rolId === 3): ?>
                        Asocia tu acceso al Registro de Asistencia y tu institución de procedencia.
                    <?php else: ?>
                        Indica el departamento donde ejerces tus funciones.
                    <?php endif; ?>
                </p>

                <?php if ($rolId === 3): ?>
                <!-- ── PASANTE: Institución + PIN ─────────────────── -->
                <div class="form-group" style="margin-bottom:1.2rem;">
                    <label class="form-label" style="display:block;margin-bottom:.5rem;font-weight:600;color:#374151;">
                        <i class="ti ti-school" style="margin-right:6px;"></i>Institución de Procedencia *
                    </label>
                    <select name="institucion_id" id="institucion_id" class="input-modern" required style="cursor:pointer;">
                        <option value="">Selecciona tu liceo / escuela técnica...</option>
                        <?php foreach ($data['instituciones'] as $inst): ?>
                        <option value="<?= (int)$inst->id ?>">
                            <?= htmlspecialchars($inst->nombre) ?>
                            <?php if (!empty($inst->direccion)): ?>(<?= htmlspecialchars($inst->direccion) ?>)<?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($data['instituciones'])): ?>
                    <p style="color:#f59e0b;font-size:0.8rem;margin-top:6px;">
                        <i class="ti ti-alert-triangle"></i> No hay instituciones registradas. Contacta al administrador.
                    </p>
                    <?php endif; ?>
                </div>

                <!-- PIN con toggle ojito -->
                <div class="form-group" style="margin-bottom:1rem;">
                    <label class="form-label" style="display:block;margin-bottom:.5rem;font-weight:600;color:#374151;">
                        <i class="ti ti-device-mobile" style="margin-right:6px;"></i>PIN del Registro de Asistencia *
                    </label>

                    <div class="info-alert">
                        <i class="ti ti-info-circle info-alert-icon"></i>
                        <div class="info-alert-body">
                            Crea un código de <strong>4 dígitos</strong>. Lo usarás <strong>cada día</strong>
                            para marcar tu entrada en el <strong>Registro de Asistencia</strong>.
                            No lo compartas con nadie.
                        </div>
                    </div>

                    <div class="pin-wrapper">
                        <input type="password" name="pin_asistencia" id="pin_asistencia"
                            maxlength="4" pattern="[0-9]{4}" inputmode="numeric"
                            class="input-modern"
                            placeholder="••••"
                            style="letter-spacing:0.6em;font-size:1.4rem;text-align:center;padding-right:48px;"
                            required>
                        <button type="button" class="pin-toggle" id="pin-toggle-btn"
                            onclick="window.togglePass('pin_asistencia', document.getElementById('pin-toggle-icon'))"
                            title="Mostrar/Ocultar PIN">
                            <i class="ti ti-eye" id="pin-toggle-icon"></i>
                        </button>
                    </div>
                    <p style="font-size:0.76rem;color:#9ca3af;margin-top:6px;text-align:center;">
                        Solo números — 4 dígitos
                    </p>
                </div>

                <?php else: ?>
                <!-- ── ADMIN / TUTOR: solo Departamento ──────────── -->
                <div class="form-group" style="margin-bottom:1.2rem;">
                    <label class="form-label" style="display:block;margin-bottom:.5rem;font-weight:600;color:#374151;">
                        <i class="ti ti-building-community" style="margin-right:6px;"></i>Departamento Asignado *
                    </label>
                    <select name="departamento_id" id="departamento_id" class="input-modern" required style="cursor:pointer;">
                        <option value="">Selecciona tu departamento...</option>
                        <?php foreach ($data['departamentos'] as $depto): ?>
                        <option value="<?= (int)$depto->id ?>">
                            <?= htmlspecialchars($depto->nombre) ?>
                            <?php if (!empty($depto->descripcion)): ?> — <?= htmlspecialchars($depto->descripcion) ?><?php endif; ?>
                        </option>
                        <?php endforeach; ?>
                    </select>
                    <?php if (empty($data['departamentos'])): ?>
                    <p style="color:#f59e0b;font-size:0.8rem;margin-top:6px;">
                        <i class="ti ti-alert-triangle"></i> No hay departamentos activos. Contacta al administrador.
                    </p>
                    <?php endif; ?>
                </div>
                <?php endif; ?>

                <!-- Botones Paso 4 -->
                <div style="display:flex;gap:1rem;margin-top:1.5rem;">
                    <button type="button" class="btn-secondary" onclick="window.prevStep3()" style="flex:1;">
                        <i class="ti ti-arrow-left" style="margin-right:8px;"></i>Atrás
                    </button>
                    <button type="button" class="btn-primary" style="flex:1;" onclick="submitWizard(this)">
                        <i class="ti ti-check" style="margin-right:8px;"></i>Finalizar y Entrar
                    </button>
                </div>
            </div><!-- /step4 -->

        </form>

        <div class="auth-footer" style="margin-top:24px;padding-top:20px;border-top:1px solid #E5E7EB;">
            <a href="<?= URLROOT ?>/auth/logout" onclick="confirmarSalida(event); return false;"
                class="auth-link" style="display:flex;align-items:center;justify-content:center;gap:8px;font-size:0.9rem;color:#6B7280;cursor:pointer;">
                <i class="ti ti-logout" style="font-size:18px;"></i>Cerrar Sesión
            </a>
        </div>
    </div>

    <script src="<?= URLROOT ?>/js/sweetalert2.min.js"></script>
    <script src="<?= URLROOT ?>/js/notyf.min.js"></script>
    <script src="<?= URLROOT ?>/js/notification-service.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/flatpickr/flatpickr-es.js"></script>
    <script src="<?= URLROOT ?>/js/flatpickr-init.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/choices/choices.min.js"></script>
    <script src="<?= URLROOT ?>/js/choices-init.js"></script>

    <!-- Tarea 3: Lógica para evitar preguntas duplicadas en tiempo real -->
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        const selects = document.querySelectorAll('.select-pregunta');
        
        function updateOptions() {
            const selectedValues = Array.from(selects).map(s => s.value).filter(v => v !== "");
            
            selects.forEach(select => {
                const options = select.querySelectorAll('option');
                options.forEach(option => {
                    if (option.value === "") return;
                    
                    // Si el valor está seleccionado en OTRO select, deshabilitarlo en este
                    const isSelectedElsewhere = Array.from(selects).some(s => s !== select && s.value === option.value);
                    option.disabled = isSelectedElsewhere;
                    
                    // Opcional: Ocultarlo visualmente si está deshabilitado
                    option.style.display = isSelectedElsewhere ? 'none' : 'block';
                });
            });
        }

        selects.forEach(select => {
            select.addEventListener('change', updateOptions);
        });

        // Ejecutar al inicio por si hay valores precargados
        updateOptions();
    });
    </script>
</body>
</html>
