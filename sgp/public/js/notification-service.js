class SGPToastService {
    constructor() {
        this.container = null;
    }

    _ensureContainer() {
        if (this.container) return;
        this.container = document.getElementById('sgp-toast-container');
        if (!this.container) {
            this.container = document.createElement('div');
            this.container.id = 'sgp-toast-container';
            document.body.appendChild(this.container);
        }
    }

    show(type, title, message, duration = 5000) {
        this._ensureContainer();
        const toast = document.createElement('div');
        toast.className = `sgp-toast toast-${type}`;

        let iconClass = 'ti-info-circle';
        if (type === 'success') iconClass = 'ti-circle-check';
        if (type === 'error') iconClass = 'ti-alert-circle';
        if (type === 'warning') iconClass = 'ti-alert-triangle';

        toast.innerHTML = `
            <i class="ti ${iconClass} toast-icon"></i>
            <div class="toast-body">
                <h4 class="toast-title">${title}</h4>
                <p class="toast-msg">${message}</p>
            </div>
            <button class="toast-close"><i class="ti ti-x"></i></button>
            <div class="toast-progress"><div class="toast-progress-bar" style="animation-duration: ${duration}ms"></div></div>
        `;

        this.container.appendChild(toast);

        // Animación de entrada
        setTimeout(() => toast.classList.add('toast-show'), 10);

        // Lógica para cerrar
        const removeToast = () => {
            toast.classList.remove('toast-show');
            toast.classList.add('toast-hide');
            setTimeout(() => toast.remove(), 400); // Esperar que termine la animación
        };

        // Cerrar con botón o por tiempo
        toast.querySelector('.toast-close').addEventListener('click', removeToast);
        const timeoutId = setTimeout(removeToast, duration);

        // Opcional: Pausar barra al pasar el mouse
        toast.addEventListener('mouseenter', () => {
            clearTimeout(timeoutId);
            toast.querySelector('.toast-progress-bar').style.animationPlayState = 'paused';
        });
    }

    success(msg, title = '¡Éxito!') { this.show('success', title, msg); }
    error(msg, title = 'Error') { this.show('error', title, msg); }
    warning(msg, title = 'Atención') { this.show('warning', title, msg); }
    info(msg, title = 'Información') { this.show('info', title, msg); }
}

// Instancia global
window.NotificationService = new SGPToastService();
