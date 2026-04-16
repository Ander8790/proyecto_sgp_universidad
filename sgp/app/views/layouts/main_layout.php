<!DOCTYPE html>
<html lang="es">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SGP-FIX [sesión_inactividad / 1.1] CSRF meta tag para fetch() JS -->
    <?php echo CsrfHelper::meta(); ?>
    <title>SGP - Sistema de Gestión de Pasantías</title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/img/favicon.png">


    <!-- CSS Assets (Local) -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/fonts.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <!-- SGP Bento Navy Theme for Swal -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/swal-bento-navy.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/bootstrap-icons/bootstrap-icons.min.css">

    <!-- CSS Modular (Nuevo Sistema Organizado) -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/variables.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/base.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/animations.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notifications.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/modal-universal.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/datatables-sgp.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/flatpickr/flatpickr.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/flatpickr-sgp.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/choices/choices.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/choices-sgp.css">
    <!-- DataTables Global (usado en Usuarios, Asistencias, Reportes) -->
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.css">
    <!-- DataTables Buttons (Excel/PDF/Print — Usuarios y Asignaciones) -->
    <link rel="stylesheet" href="<?= URLROOT ?>/assets/libs/datatables/buttons/buttons.dataTables.min.css">
    <!-- Modal Asignación CSS (componente reutilizable) -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/modal-asignacion.css">

    <!-- IMPORTANTE: style.css debe cargar AL FINAL para tener prioridad sobre otros frameworks -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sidebar.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/mobile-dock.css?v=6">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/topbar.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/loading.css">

    <!-- Scripts Core (JQuery debe cargar ANTES del contenido por si las vistas inyectan scripts) -->
    <script src="<?= URLROOT ?>/js/jquery.min.js"></script>
    <script src="<?= URLROOT ?>/js/sweetalert2.min.js"></script>
    <script src="<?= URLROOT ?>/js/notyf.min.js"></script>
    <script src="<?= URLROOT ?>/js/notification-service.js"></script>

    <!-- DataTables Global JS (Debe ir en el head ANTES del contenido porque las vistas inyectan plugins de botones) -->
    <script src="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.js"></script>
    <!-- DataTables Buttons + dependencias (JSZip, pdfmake) — carga única para todo el sistema -->
    <script src="<?= URLROOT ?>/assets/libs/datatables/buttons/dataTables.buttons.min.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/datatables/buttons/jszip.min.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/datatables/buttons/pdfmake.min.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/datatables/buttons/vfs_fonts.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/datatables/buttons/buttons.html5.min.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/datatables/buttons/buttons.print.min.js"></script>

    <!-- Global JavaScript Constants -->
    <script>
        // Exponer constantes de PHP al entorno JavaScript
        const URLROOT = <?= json_encode(URLROOT, JSON_UNESCAPED_SLASHES) ?>; // [FIX-C3]
    </script>
</head>

<body>
    <div class="wrapper">
        <!-- Header (Topbar) -->
        <?php require APPROOT . '/views/inc/header.php'; ?>

        <!-- Sidebar -->
        <?php require APPROOT . '/views/inc/sidebar.php'; ?>

        <!-- Content Wrapper -->
        <div class="content-wrapper">
            <!-- PJAX: Este contenedor es el target de la navegación fluida -->
            <div id="sgp-content" style="transition: opacity 0.18s ease;">
                <?php
                // La variable $content contiene la ruta al archivo de vista específica
                if (isset($content) && file_exists($content)) {
                    require $content;
                } else {
                    echo '<div class="smart-card"><h3>Error: Vista no encontrada</h3></div>';
                }
                ?>
            </div>
        </div>

        <!-- Sidebar Overlay (para móvil) -->
        <div id="sidebarOverlay" class="sidebar-overlay"></div>
    </div>

    <!-- JavaScript Assets (Al final del body) -->
    <script src="<?= URLROOT ?>/js/bootstrap.bundle.min.js"></script>
    <script src="<?= URLROOT ?>/js/apexcharts.min.js"></script>
    <script src="<?= URLROOT ?>/js/echarts.min.js"></script>
    <script src="<?= URLROOT ?>/js/modern-charts.js"></script>
    <script src="<?= URLROOT ?>/js/notifications.js?v=3"></script>
    <script src="<?= URLROOT ?>/js/sidebar.js"></script>
    <script src="<?= URLROOT ?>/js/mobile-dock.js?v=5"></script>
    <script src="<?= URLROOT ?>/js/validation.js?v=2"></script>
    <script src="<?= URLROOT ?>/js/modal-universal.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/flatpickr/flatpickr-es.js"></script>
    <script src="<?= URLROOT ?>/js/flatpickr-init.js?v=3"></script>
    <script src="<?= URLROOT ?>/assets/libs/choices/choices.min.js"></script>
    <script src="<?= URLROOT ?>/js/choices-init.js"></script>
    <!-- SGP-PJAX: Navegación fluida sin recarga de página -->
    <script src="<?= URLROOT ?>/js/sgp-pjax.js?v=3"></script>

    <!-- FIX PARA APEXCHARTS - Previene error "attribute r: A negative value" -->
    <script>

        // Redimensionar gráficas al colapsar sidebar
        const toggleBtn = document.getElementById('sidebarToggle');
        if (toggleBtn) {
            toggleBtn.addEventListener('click', () => {
                setTimeout(() => {
                    window.dispatchEvent(new Event('resize'));
                }, 300);
            });
        }
    </script>

    <!-- ===== KPI COUNTER ANIMATION ===== -->
    <script>
        /**
         * SGP KPI Counter Animation — Premium Dashboard Feel
         * Uso: <h2 class="kpi-animated" data-kpi-value="42">0</h2>
         * Se activa automáticamente al cargar la página vía IntersectionObserver.
         */
        (function () {
            function animateCounter(el, target, duration) {
                var start = 0;
                var startTime = null;
                function step(timestamp) {
                    if (!startTime) startTime = timestamp;
                    var progress = Math.min((timestamp - startTime) / duration, 1);
                    // Ease-out cubic
                    var eased = 1 - Math.pow(1 - progress, 3);
                    el.textContent = Math.floor(eased * target);
                    if (progress < 1) requestAnimationFrame(step);
                    else el.textContent = target;
                }
                requestAnimationFrame(step);
            }

            function initKpiCounters() {
                var els = document.querySelectorAll('[data-kpi-value]');
                if (!els.length) return;

                if ('IntersectionObserver' in window) {
                    var observer = new IntersectionObserver(function (entries) {
                        entries.forEach(function (entry) {
                            if (entry.isIntersecting) {
                                var el = entry.target;
                                var target = parseInt(el.dataset.kpiValue, 10) || 0;
                                animateCounter(el, target, 900);
                                observer.unobserve(el);
                            }
                        });
                    }, { threshold: 0.3 });
                    els.forEach(function (el) { observer.observe(el); });
                } else {
                    // Fallback sin IntersectionObserver
                    els.forEach(function (el) {
                        var target = parseInt(el.dataset.kpiValue, 10) || 0;
                        animateCounter(el, target, 900);
                    });
                }
            }

            document.addEventListener('DOMContentLoaded', initKpiCounters);
        })();
    </script>

    <!-- ===== LOADING SPINNER HELPER FUNCTIONS ===== -->
    <script>
        /**
         * Show loading overlay with optional message
         * @param {string} message - Optional loading message (default: "Cargando...")
         */
        function showLoading(message = 'Cargando...') {
            let overlay = document.getElementById('globalLoadingOverlay');
            if (!overlay) {
                // Create overlay if it doesn't exist
                overlay = document.createElement('div');
                overlay.id = 'globalLoadingOverlay';
                overlay.className = 'loading-overlay';
                overlay.innerHTML = `
                    <div class="loader"></div>
                    <div class="loading-text">${message}</div>
                `;
                document.body.appendChild(overlay);
            } else {
                // Update message
                const textEl = overlay.querySelector('.loading-text');
                if (textEl) textEl.textContent = message;
            }

            // Show overlay with slight delay for smooth animation
            requestAnimationFrame(() => {
                overlay.classList.add('active');
            });
        }

        /**
         * Hide loading overlay
         */
        function hideLoading() {
            const overlay = document.getElementById('globalLoadingOverlay');
            if (overlay) {
                overlay.classList.remove('active');
            }
        }

        /**
         * Set button to loading state
         * @param {HTMLElement} button - Button element
         * @param {string} loadingText - Text to show while loading
         */
        function setButtonLoading(button, loadingText = 'Procesando...') {
            if (!button) return;

            // Store original content
            button.dataset.originalContent = button.innerHTML;

            // Set loading content
            button.innerHTML = `<span class="loader-small"></span> ${loadingText}`;
            button.classList.add('btn-loading');
            button.disabled = true;
        }

        /**
         * Reset button from loading state
         * @param {HTMLElement} button - Button element
         */
        function resetButton(button) {
            if (!button) return;

            // Restore original content
            if (button.dataset.originalContent) {
                button.innerHTML = button.dataset.originalContent;
            }

            button.classList.remove('btn-loading');
            button.disabled = false;
        }

        // Make functions globally available
        window.showLoading = showLoading;
        window.hideLoading = hideLoading;
        window.setButtonLoading = setButtonLoading;
        window.resetButton = resetButton;
    </script>

    <!-- ===== FLASH MESSAGES CENTRALIZADOS =====
         Consume todos los flashes pendientes en sesión y los muestra como toast.
         Las vistas NO necesitan bloques <script> propios para flashes estándar.
         Los flashes consumidos aquí ya no estarán disponibles para las vistas.
    -->
    <?php
    $sgpFlashTypes = ['success' => '¡Éxito!', 'error' => 'Error', 'warning' => 'Atención', 'info' => 'Información'];
    $sgpPendingFlashes = [];
    foreach ($sgpFlashTypes as $fType => $fDefaultTitle) {
        if (Session::hasFlash($fType)) {
            $sgpPendingFlashes[] = ['type' => $fType, 'msg' => Session::getFlash($fType)];
        }
    }
    if (!empty($sgpPendingFlashes)):
        ?>
        <script>
            // [FIX-UI] Despachar flashes centralizados desde Session vía main_layout
            document.addEventListener('DOMContentLoaded', function () {
                if (typeof NotificationService === 'undefined') return;
                <?php foreach ($sgpPendingFlashes as $flash): ?>
                    NotificationService.<?= $flash['type'] ?>(<?= json_encode($flash['msg'], JSON_UNESCAPED_UNICODE) ?>);
                <?php endforeach; ?>
            });
        </script>
    <?php endif; ?>

    <!-- =====================================================================
         SGP [sesión_inactividad v3] — Timer de inactividad + modal Bento UI
         ⚠️  MODO TESTING: TIMEOUT_MS = 10 s. Cambiar a SESSION_TIMEOUT_SECONDS
              para producción (descomentar la línea marcada con [PROD]).
         ===================================================================== -->
    <script>
        (function () {
            'use strict';

            /* ── Guard: solo en páginas con sesión autenticada ── */
            if (!document.getElementById('sgp-content') && !document.querySelector('.sidebar')) return;

            /* ── Constantes de tiempo ─────────────────────────────────────────────
               [TESTING]  10 segundos — activo ahora para verificación inmediata.
               [PROD]     Descomentar la línea PROD y comentar la línea TESTING.    */
            // var TIMEOUT_MS  = 10000;   // [TESTING]
            var TIMEOUT_MS = 1500000; // [PROD] 25 Minutos para Producción
            var MODAL_WAIT_MS = 15000; /* Tiempo que tiene el usuario para reaccionar en el modal */
            var warnTimer;

            /* ── Reiniciar temporizador en cada interacción del usuario ────────── */
            function resetTimer() {
                clearTimeout(warnTimer);
                warnTimer = setTimeout(mostrarAvisoInactividad, TIMEOUT_MS);
            }

            /* ── Modal SweetAlert2 — Bento UI Premium ──────────────────────────── */
            function mostrarAvisoInactividad() {
                if (typeof Swal === 'undefined') {
                    /* Fallback de seguridad si Swal no cargó */
                    window.location.href = URLROOT + '/auth/logout?razon=inactividad';
                    return;
                }

                Swal.fire({
                    icon: 'warning',
                    title: 'Sesión Expirada',
                    html: [
                        '<div style="',
                        'font-size: 1rem;',
                        'color: #475569;',
                        'line-height: 1.6;',
                        'margin-top: 8px;',
                        'padding: 0 4px;',
                        '">',
                        '<span style="',
                        'display: inline-flex;',
                        'align-items: center;',
                        'gap: 6px;',
                        'background: rgba(245, 158, 11, 0.08);',
                        'border: 1px solid rgba(245, 158, 11, 0.25);',
                        'border-radius: 10px;',
                        'padding: 8px 14px;',
                        'margin-bottom: 12px;',
                        'font-size: 0.88rem;',
                        'color: #b45309;',
                        'font-weight: 600;',
                        '">',
                        '⏱ Tu sesión estará a punto de cerrarse',
                        '</span>',
                        '<br>',
                        'Por inactividad, tu sesión expirará para proteger tus datos.',
                        '<br>',
                        '<span style="font-size: 0.85rem; color: #94a3b8; margin-top: 6px; display:block;">',
                        '¿Deseas mantener la sesión activa?',
                        '</span>',
                        '</div>'
                    ].join(''),

                    /* ── Botones ── */
                    showCancelButton: true,
                    confirmButtonText: '✓ Mantener sesión',
                    cancelButtonText: 'Cerrar ahora',
                    confirmButtonColor: '#1d4ed8',
                    cancelButtonColor: '#e2e8f0',

                    /* ── Barra de progreso (15 s para reaccionar) ── */
                    timer: MODAL_WAIT_MS,
                    timerProgressBar: true,

                    /* ── Estilos custom Bento UI (hereda de swal-bento-navy.css) ── */
                    customClass: {
                        popup: 'sgp-swal-inactividad',
                        confirmButton: 'sgp-swal-btn-confirm',
                        cancelButton: 'sgp-swal-btn-cancel'
                    },

                    /* ── Estilos inline del popup para autosuficiencia ── */
                    didOpen: function () {
                        var popup = Swal.getPopup();
                        if (!popup) return;
                        popup.style.borderRadius = '20px';
                        popup.style.padding = '32px 28px 28px';
                        popup.style.boxShadow = '0 25px 60px rgba(0,0,0,0.18), 0 8px 24px rgba(0,0,0,0.10)';
                        popup.style.border = '1px solid rgba(255,255,255,0.12)';
                        popup.style.backdropFilter = 'blur(12px)';

                        /* Estilizar botón cancelar (texto oscuro sobre fondo claro) */
                        var cancelBtn = Swal.getCancelButton();
                        if (cancelBtn) {
                            cancelBtn.style.color = '#475569';
                            cancelBtn.style.fontWeight = '500';
                            cancelBtn.style.border = '1px solid #cbd5e1';
                        }
                    }

                }).then(function (result) {
                    if (result.isConfirmed) {
                        /* Usuario eligió mantener sesión — hacer keep-alive al backend */
                        keepAlive();
                    } else {
                        /* Timer expiró, canceló, o cerró el modal → logout */
                        cerrarSesion();
                    }
                });
            }

            /* ── Keep-alive: renueva last_activity en el servidor ─────────────── */
            function keepAlive() {
                var csrfMeta = document.querySelector('meta[name="csrf-token"]');
                fetch(URLROOT + '/auth/keepalive', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfMeta ? csrfMeta.getAttribute('content') : '',
                        'X-Requested-With': 'XMLHttpRequest',
                        'Content-Type': 'application/json'
                    }
                })
                    .then(function (r) {
                        /* Si el servidor responde OK, reiniciar el temporizador */
                        if (r.ok) { resetTimer(); }
                        else { cerrarSesion(); }
                    })
                    .catch(function () {
                        /* Sin conexión — conservamos la sesión pero reiniciamos el timer */
                        resetTimer();
                    });
            }

            /* ── Logout por inactividad ─────────────────────────────────────────── */
            function cerrarSesion() {
                window.location.href = URLROOT + '/auth/logout?razon=inactividad';
            }

            /* ── Interceptar respuestas 401 de fetch (compatibilidad PJAX) ─────── */
            var _fetchOriginal = window.fetch;
            window.fetch = function () {
                return _fetchOriginal.apply(this, arguments).then(function (response) {
                    if (response.status === 401) {
                        response.clone().json().then(function (data) {
                            if (data && data.reason === 'session_expired') {
                                // Agregar razon=inactividad para que login.php muestre el Swal
                                var dest = data.redirect || (URLROOT + '/auth/login');
                                if (dest.indexOf('?') === -1) dest += '?razon=inactividad';
                                window.location.href = dest;
                            }
                        }).catch(function () { });
                    }
                    return response;
                });
            };

            /* ── Escuchar eventos de actividad del usuario ──────────────────────── */
            ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach(function (evt) {
                document.addEventListener(evt, resetTimer, { passive: true });
            });

            /* ── Arrancar el temporizador al cargar ─────────────────────────────── */
            resetTimer();

        }());
    </script>
</body>

</html>