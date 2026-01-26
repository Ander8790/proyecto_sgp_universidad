/**
 * Sidebar Híbrido - JavaScript
 * Lógica del proyecto amigo + Fix para ApexCharts
 */

document.addEventListener('DOMContentLoaded', function () {
    const toggleBtn = document.getElementById('sidebarToggle');
    const sidebar = document.querySelector('.main-sidebar');
    const content = document.querySelector('.content-wrapper');
    const overlay = document.getElementById('sidebarOverlay');

    if (!toggleBtn || !sidebar || !content) return;

    // Función principal de Toggle
    toggleBtn.addEventListener('click', function (e) {
        e.stopPropagation();

        if (window.innerWidth > 992) {
            // === LÓGICA DESKTOP: Colapsar / Expandir ===
            sidebar.classList.toggle('collapsed');
            content.classList.toggle('expanded');

            // Fix para ApexCharts cuando cambia el ancho
            setTimeout(() => {
                window.dispatchEvent(new Event('resize'));
            }, 300);

        } else {
            // === LÓGICA MÓVIL: Mostrar / Ocultar con Overlay ===
            sidebar.classList.toggle('show');
            document.body.classList.toggle('sidebar-open'); // Para mover header
            if (overlay) {
                overlay.classList.toggle('active');
            }
        }
    });

    // Cerrar al hacer click en el Overlay (Móvil)
    if (overlay) {
        overlay.addEventListener('click', function () {
            sidebar.classList.remove('show');
            overlay.classList.remove('active');
            document.body.classList.remove('sidebar-open');
        });
    }

    // Cerrar automáticamente si la ventana se redimensiona a Desktop
    window.addEventListener('resize', function () {
        if (window.innerWidth > 992) {
            sidebar.classList.remove('show');
            if (overlay) {
                overlay.classList.remove('active');
            }
            // Mantenemos el estado 'collapsed' si el usuario lo dejó así
        }
    });

    // Prevenir que clicks dentro del sidebar lo cierren
    sidebar.addEventListener('click', function (e) {
        e.stopPropagation();
    });
});
