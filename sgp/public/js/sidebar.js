/**
 * Sidebar Professional - JavaScript
 * Sistema de Gestión de Pasantes (SGP)
 * 
 * Características:
 * - Desktop: Collapsed/Expanded con persistencia LocalStorage
 * - Mobile: Overlay con backdrop blur
 * - Transiciones suaves y sin bugs
 * - Fix para ApexCharts resize
 */

document.addEventListener('DOMContentLoaded', function () {
    // Modificación: Seleccionar ambos botones (Desktop y Mobile)
    const toggleBtns = document.querySelectorAll('#sidebarToggle, #sidebarCollapseToggle');
    const sidebar = document.querySelector('.main-sidebar');
    const content = document.querySelector('.content-wrapper');
    const overlay = document.getElementById('sidebarOverlay');

    if (toggleBtns.length === 0 || !sidebar || !content) {
        console.warn('⚠️ Sidebar: Elementos no encontrados');
        return;
    }

    // ============================================
    // PERSISTENCIA DE ESTADO (LocalStorage)
    // ============================================
    const STORAGE_KEY = 'sgp_sidebar_collapsed';

    function getSavedState() {
        return localStorage.getItem(STORAGE_KEY) === 'true';
    }

    function saveState(isCollapsed) {
        localStorage.setItem(STORAGE_KEY, isCollapsed);
        console.log('💾 Estado guardado:', isCollapsed ? 'collapsed' : 'expanded');
    }

    // ============================================
    // INICIALIZACIÓN: Restaurar estado guardado
    // ============================================
    function initializeSidebar() {
        if (window.innerWidth > 992 && getSavedState()) {
            // Restaurar estado collapsed en desktop
            document.body.classList.add('sidebar-collapsed');
            sidebar.classList.add('collapsed');
            console.log('✅ Sidebar inicializado en modo collapsed');
        } else {
            console.log('✅ Sidebar inicializado en modo expanded');
        }
    }

    // Ejecutar inicialización
    initializeSidebar();

    // ============================================
    // TOGGLE PRINCIPAL
    // ============================================
    // ============================================
    // TOGGLE PRINCIPAL (Unified Handler)
    // ============================================
    function handleSidebarToggle(e) {
        e.stopPropagation();

        if (window.innerWidth > 992) {
            // === DESKTOP: Collapsed State ===
            const isCollapsed = sidebar.classList.toggle('collapsed');
            document.body.classList.toggle('sidebar-collapsed', isCollapsed);

            // Guardar preferencia del usuario
            saveState(isCollapsed);

            console.log('🖥️ Desktop toggle:', isCollapsed ? 'collapsed' : 'expanded');

            // Fix para gráficos ApexCharts y otros elementos responsivos
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 350); // Tiempo de transición CSS (0.3s + margen)

        } else {
            // === MOBILE: Overlay State ===
            const isOpen = sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open', isOpen);

            if (overlay) {
                overlay.classList.toggle('active', isOpen);
            }

            console.log('📱 Mobile toggle:', isOpen ? 'open' : 'closed');
        }
    }

    // Attach listener to all toggle buttons
    toggleBtns.forEach(btn => {
        btn.addEventListener('click', handleSidebarToggle);
    });

    // ============================================
    // CERRAR CON OVERLAY (Móvil)
    // ============================================
    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
            console.log('📱 Sidebar cerrado por overlay');
        });
    }

    // ============================================
    // RESPONSIVE: Limpiar estado móvil al cambiar a desktop
    // ============================================
    let resizeTimer;
    window.addEventListener('resize', function () {
        clearTimeout(resizeTimer);
        resizeTimer = setTimeout(() => {
            const isDesktop = window.innerWidth > 992;

            if (isDesktop) {
                // Limpiar clases móviles
                sidebar.classList.remove('show');
                document.body.classList.remove('sidebar-open');
                if (overlay) {
                    overlay.classList.remove('active');
                }

                // Restaurar estado collapsed si estaba guardado
                if (getSavedState()) {
                    sidebar.classList.add('collapsed');
                    document.body.classList.add('sidebar-collapsed');
                } else {
                    sidebar.classList.remove('collapsed');
                    document.body.classList.remove('sidebar-collapsed');
                }

                console.log('🖥️ Cambio a desktop');
            } else {
                // En móvil, siempre expandido por defecto (cuando está visible)
                sidebar.classList.remove('collapsed');
                document.body.classList.remove('sidebar-collapsed');

                console.log('📱 Cambio a mobile');
            }
        }, 250);
    });

    // ============================================
    // PREVENIR CIERRE AL CLICK INTERNO
    // ============================================
    sidebar.addEventListener('click', function (e) {
        e.stopPropagation();
    });

    // ============================================
    // CERRAR SIDEBAR EN MOBILE AL HACER CLICK EN LINK
    // ============================================
    const navLinks = sidebar.querySelectorAll('.nav-link');
    navLinks.forEach(link => {
        link.addEventListener('click', function (e) {
            // ✅ FIX: Evitar que el click se propague al sidebar y lo expanda
            e.stopPropagation();

            if (window.innerWidth <= 991) {
                // Cerrar sidebar en mobile después de click
                setTimeout(() => {
                    sidebar.classList.remove('show');
                    document.body.classList.remove('sidebar-open');
                    if (overlay) {
                        overlay.classList.remove('active');
                    }
                }, 150); // Pequeño delay para feedback visual
            }
        });
    });

    console.log('✅ Sidebar.js cargado correctamente');

    // ============================================
    // FIX: PREVENIR EXPANSIÓN AL CLICKEAR ITEMS
    // ============================================
    const sidebarItems = document.querySelectorAll('.sidebar-link');
    sidebarItems.forEach(item => {
        item.addEventListener('click', function (e) {
            e.stopPropagation();
        });
    });
});
