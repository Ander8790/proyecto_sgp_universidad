<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <!-- SGP-FIX-v2 [sesión_inactividad / 1.1] CSRF meta tag para fetch() JS -->
    <?php echo CsrfHelper::meta(); ?>
    <title>SGP - Sistema de Gestión de Pasantías</title>
    <link rel="icon" type="image/png" href="<?= URLROOT ?>/img/favicon.png">
    
    
    <!-- CSS Assets (Local) -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css">
    
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
    <!-- Modal Asignación CSS (componente reutilizable) -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/modal-asignacion.css">
    
    <!-- IMPORTANTE: style.css debe cargar AL FINAL para tener prioridad sobre otros frameworks -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sidebar.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/topbar.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/loading.css">
    
    <!-- Scripts Core (JQuery debe cargar ANTES del contenido por si las vistas inyectan scripts) -->
    <script src="<?= URLROOT ?>/js/jquery.min.js"></script>
    <script src="<?= URLROOT ?>/js/sweetalert2.min.js"></script>
    <script src="<?= URLROOT ?>/js/notyf.min.js"></script>
    <script src="<?= URLROOT ?>/js/notification-service.js"></script>
    
    <!-- DataTables Global JS (Debe ir en el head ANTES del contenido porque las vistas inyectan plugins de botones) -->
    <script src="<?= URLROOT ?>/assets/libs/datatables/jquery.dataTables.min.js"></script>
    
    <!-- Global JavaScript Constants -->
    <script>
        // Exponer constantes de PHP al entorno JavaScript
        const URLROOT = '<?= URLROOT ?>';
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
    <script src="<?= URLROOT ?>/js/modern-charts.js"></script>
    <script src="<?= URLROOT ?>/js/notifications.js?v=2"></script>
    <script src="<?= URLROOT ?>/js/sidebar.js"></script>
    <script src="<?= URLROOT ?>/js/validation.js?v=2"></script>
    <script src="<?= URLROOT ?>/js/modal-universal.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/flatpickr/flatpickr.min.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/flatpickr/flatpickr-es.js"></script>
    <script src="<?= URLROOT ?>/js/flatpickr-init.js"></script>
    <script src="<?= URLROOT ?>/assets/libs/choices/choices.min.js"></script>
    <script src="<?= URLROOT ?>/js/choices-init.js"></script>
    <!-- SGP-PJAX: Navegación fluida sin recarga de página -->
    <script src="<?= URLROOT ?>/js/sgp-pjax.js"></script>
    
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
        (function() {
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
                    var observer = new IntersectionObserver(function(entries) {
                        entries.forEach(function(entry) {
                            if (entry.isIntersecting) {
                                var el = entry.target;
                                var target = parseInt(el.dataset.kpiValue, 10) || 0;
                                animateCounter(el, target, 900);
                                observer.unobserve(el);
                            }
                        });
                    }, { threshold: 0.3 });
                    els.forEach(function(el) { observer.observe(el); });
                } else {
                    // Fallback sin IntersectionObserver
                    els.forEach(function(el) {
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

    <!-- SGP-FIX-v2 [sesión_inactividad] Aviso de inactividad + interceptor fetch 401 -->
    <script>
    (function () {
        // Guard: no ejecutar en páginas sin el layout autenticado (login, registro, etc.)
        if (!document.getElementById('sgp-content') && !document.querySelector('.sidebar')) return;
        var TIMEOUT_MS     = 600000;
        var WARN_BEFORE_MS = 120000;
        var warnTimer, logoutTimer;

        function resetTimers() {
            clearTimeout(warnTimer);
            clearTimeout(logoutTimer);
            warnTimer   = setTimeout(mostrarAviso,  TIMEOUT_MS - WARN_BEFORE_MS);
            logoutTimer = setTimeout(cerrarSesion,  TIMEOUT_MS);
        }

        function mostrarAviso() {
            if (typeof Swal === 'undefined') return;
            Swal.fire({
                icon: 'warning',
                title: 'Sesión por vencer',
                text:  'Tu sesión cerrará en 2 minutos por inactividad. ¿Deseas continuar?',
                confirmButtonText: 'Sí, continuar',
                showCancelButton:  true,
                cancelButtonText:  'Cerrar sesión',
                timer:             120000,
                timerProgressBar:  true
            }).then(function (result) {
                if (result.isConfirmed) { keepAlive(); } else { cerrarSesion(); }
            });
        }

        function keepAlive() {
            var token = document.querySelector('meta[name=csrf-token]');
            fetch(URLROOT + '/auth/keepalive', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN':     token ? token.getAttribute('content') : '',
                    'X-Requested-With': 'XMLHttpRequest'
                }
            })
            .then(function (r) { if (r.ok) resetTimers(); })
            .catch(function ()  { resetTimers(); });
        }

        function cerrarSesion() {
            window.location.href = URLROOT + '/auth/logout?razon=inactividad';
        }

        // Interceptar respuestas 401 en fetch para manejar sesión expirada en PJAX
        var _fetch = window.fetch;
        window.fetch = function () {
            return _fetch.apply(this, arguments).then(function (response) {
                if (response.status === 401) {
                    response.clone().json().then(function (data) {
                        if (data && data.reason === 'session_expired') {
                            window.location.href = data.redirect || (URLROOT + '/auth/login');
                        }
                    }).catch(function () {});
                }
                return response;
            });
        };

        // Reiniciar timers en cualquier interacción del usuario
        ['click', 'keydown', 'mousemove', 'scroll', 'touchstart'].forEach(function (evt) {
            document.addEventListener(evt, resetTimers, { passive: true });
        });

        resetTimers();
    }());
    </script>
</body>
</html>
