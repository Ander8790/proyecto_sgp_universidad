/**
 * SIDEBAR CONTROLLER - COLLAPSIBLE SIDEBAR
 * Desktop: Collapse/Expand (260px ↔ 80px)
 * Mobile: Overlay toggle
 */

// CRÍTICO: Aplicar estado colapsado ANTES del DOM load para evitar flash
(function () {
    const isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';
    if (isCollapsed && window.innerWidth >= 992) {
        // Agregar clase al body INMEDIATAMENTE
        document.documentElement.classList.add('sidebar-collapsed');
    }
})();

class SidebarController {
    constructor() {
        this.sidebar = document.querySelector('.main-sidebar');
        this.toggleBtn = document.getElementById('sidebarToggle'); // Mobile
        this.menuToggle = document.getElementById('menuToggle'); // Topbar hamburger
        this.collapseToggle = document.getElementById('sidebarCollapseToggle'); // Desktop
        this.overlay = document.getElementById('sidebarOverlay');
        this.body = document.body;

        // Estado colapsado (localStorage)
        this.isCollapsed = localStorage.getItem('sidebarCollapsed') === 'true';

        this.init();
    }

    init() {
        if (!this.sidebar) {
            console.warn('Sidebar not found');
            return;
        }

        // Restaurar estado colapsado en desktop SIN animación
        if (this.isCollapsed && window.innerWidth >= 992) {
            // Aplicar clases sin transición
            this.sidebar.classList.add('no-transition');
            this.sidebar.classList.add('collapsed');
            this.body.classList.add('sidebar-collapsed');

            // Remover no-transition después de un frame
            requestAnimationFrame(() => {
                requestAnimationFrame(() => {
                    this.sidebar.classList.remove('no-transition');
                });
            });

            // Update toggle button title
            if (this.collapseToggle) {
                this.collapseToggle.setAttribute('title', 'Expandir sidebar');
            }
        }

        // Desktop collapse toggle
        if (this.collapseToggle) {
            this.collapseToggle.addEventListener('click', () => this.toggleCollapse());
        }

        // Topbar menu toggle (hamburger button)
        if (this.menuToggle) {
            this.menuToggle.addEventListener('click', () => this.toggleCollapse());
        }

        // Mobile toggle
        if (this.toggleBtn) {
            this.toggleBtn.addEventListener('click', () => this.toggleMobile());
        }

        // Overlay click
        if (this.overlay) {
            this.overlay.addEventListener('click', () => this.closeMobile());
        }

        // Close on nav link click (mobile only)
        const navLinks = this.sidebar.querySelectorAll('.nav-link');
        navLinks.forEach(link => {
            link.addEventListener('click', () => {
                if (window.innerWidth < 992) {
                    this.closeMobile();
                }
            });
        });

        // Resize handler
        window.addEventListener('resize', () => this.handleResize());
    }

    toggleCollapse() {
        if (window.innerWidth >= 992) {
            if (this.sidebar.classList.contains('collapsed')) {
                this.expand();
            } else {
                this.collapse();
            }
        }
    }

    collapse() {
        this.sidebar.classList.add('collapsed');
        this.body.classList.add('sidebar-collapsed');
        this.isCollapsed = true;
        localStorage.setItem('sidebarCollapsed', 'true');

        // Update toggle button title
        if (this.collapseToggle) {
            this.collapseToggle.setAttribute('title', 'Expandir sidebar');
        }

        // Trigger resize para ApexCharts
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 300);
    }

    expand() {
        this.sidebar.classList.remove('collapsed');
        this.body.classList.remove('sidebar-collapsed');
        this.isCollapsed = false;
        localStorage.setItem('sidebarCollapsed', 'false');

        // Update toggle button title
        if (this.collapseToggle) {
            this.collapseToggle.setAttribute('title', 'Colapsar sidebar');
        }

        // Trigger resize para ApexCharts
        setTimeout(() => {
            window.dispatchEvent(new Event('resize'));
        }, 300);
    }

    toggleMobile() {
        if (window.innerWidth < 992) {
            this.sidebar.classList.toggle('show');
            this.overlay.classList.toggle('active');

            // Prevent body scroll when sidebar is open
            if (this.sidebar.classList.contains('show')) {
                this.body.style.overflow = 'hidden';
            } else {
                this.body.style.overflow = '';
            }
        }
    }

    closeMobile() {
        this.sidebar.classList.remove('show');
        this.overlay.classList.remove('active');
        this.body.style.overflow = '';
    }

    handleResize() {
        if (window.innerWidth >= 992) {
            // Desktop: cerrar mobile, restaurar collapse
            this.closeMobile();

            if (this.isCollapsed) {
                this.collapse();
            } else {
                this.expand();
            }
        } else {
            // Mobile: remover collapse, mantener sidebar oculto
            this.sidebar.classList.remove('collapsed');
            this.body.classList.remove('sidebar-collapsed');
        }
    }
}

// Initialize cuando DOM esté listo
document.addEventListener('DOMContentLoaded', () => {
    window.sidebarController = new SidebarController();
});

// Exponer funciones globalmente para compatibilidad
window.toggleSidebar = function () {
    if (window.sidebarController) {
        window.sidebarController.toggleMobile();
    }
};

window.toggleSidebarCollapse = function () {
    if (window.sidebarController) {
        window.sidebarController.toggleCollapse();
    }
};
