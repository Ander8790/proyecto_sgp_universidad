<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>SGP - Sistema de Gestión de Pasantías</title>
    
    
    <!-- CSS Assets (Absolute URLs) -->
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/css/bootstrap.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/tabler-icons.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/sweetalert2.min.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/notyf.min.css">
    <!-- IMPORTANTE: style.css debe cargar AL FINAL para tener prioridad sobre otros frameworks -->
    <link rel="stylesheet" href="<?= URLROOT ?>/css/style.css">
    <link rel="stylesheet" href="<?= URLROOT ?>/css/loading.css">
    
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
            <!-- Vista Específica (contenido dinámico) -->
            <?php 
            // La variable $content contiene la ruta al archivo de vista específica
            if (isset($content) && file_exists($content)) {
                require $content;
            } else {
                echo '<div class="smart-card"><h3>Error: Vista no encontrada</h3></div>';
            }
            ?>
        </div>

        <!-- Footer -->
        <?php require APPROOT . '/views/inc/footer.php'; ?>
    </div>

    <!-- JavaScript Assets (Al final del body) -->
    <script src="<?= URLROOT ?>/js/jquery.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.6.2/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= URLROOT ?>/js/sweetalert2.min.js"></script>
    <script src="<?= URLROOT ?>/js/notyf.min.js"></script>
    <script src="<?= URLROOT ?>/js/apexcharts.min.js"></script>
    
    <!-- Sidebar Toggle Script -->
    <script>
        // Sidebar Toggle Functionality
        const menuToggle = document.getElementById('menuToggle');
        const sidebar = document.querySelector('.sidebar');
        const body = document.body;

        if (menuToggle && sidebar) {
            menuToggle.addEventListener('click', function() {
                // Toggle sidebar collapse class on body
                body.classList.toggle('sidebar-collapse');
                
                // Toggle active class on button for animation
                this.classList.toggle('active');
            });
        }

        // ===== LOADING SPINNER HELPER FUNCTIONS =====
        
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

</body>
</html>
