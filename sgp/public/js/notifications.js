/**
 * Notifications UI Handler
 * Sistema de Gestión de Pasantes (SGP)
 * 
 * Maneja la carga, visualización e interacción con notificaciones en tiempo real
 */

(function () {
    'use strict';

    // ==================== CONFIGURACIÓN ====================
    const CONFIG = {
        REFRESH_INTERVAL: 60000, // 60 segundos
        ENDPOINTS: {
            GET_UNREAD: URLROOT + '/notifications/getUnread',
            MARK_AS_READ: URLROOT + '/notifications/markAsRead/',
            MARK_ALL_READ: URLROOT + '/notifications/markAllAsRead'
        }
    };

    // ==================== ELEMENTOS DOM ====================
    const elements = {
        btn: null,
        badge: null,
        dropdown: null,
        list: null,
        markAllBtn: null
    };

    // ==================== ICONOS POR TIPO ====================
    const NOTIFICATION_ICONS = {
        'usuario_creado': 'ti-user-plus',
        'perfil_actualizado': 'ti-user-check',
        'password_reset': 'ti-key',
        'sistema': 'ti-bell',
        'alerta_sistema': 'ti-alert-triangle',
        'alerta_urgente': 'ti-urgent',
        'alerta_exito': 'ti-circle-check',
        'info': 'ti-info-circle',
        'solicitud_pin': 'ti-lock-question',
        'solicitud_recovery': 'ti-help',
        'evaluacion_nueva': 'ti-star',
        'asignacion_nueva': 'ti-link',
        'cambio_estado': 'ti-switch-horizontal',
        'warning': 'ti-alert-triangle',
        'success': 'ti-circle-check',
        'default': 'ti-bell'
    };

    // ==================== INICIALIZACIÓN ====================
    function init() {
        // Cachear elementos DOM
        elements.btn = document.getElementById('notificationBtn');
        elements.badge = document.getElementById('notificationBadge');
        elements.dropdown = document.getElementById('notificationsDropdown');
        elements.list = document.getElementById('notificationList');
        elements.markAllBtn = document.getElementById('markAllReadBtn');

        // ✨ MEJORADO: Validación silenciosa para páginas sin topbar (login, register, etc.)
        if (!elements.btn || !elements.dropdown) {
            // No mostrar warning en páginas de autenticación
            // console.warn('⚠️ Elementos de notificaciones no encontrados en el DOM');
            return;
        }

        // Cargar notificaciones iniciales
        loadNotifications();

        // Configurar eventos
        setupEventListeners();

        // Auto-refresh periódico
        setInterval(loadNotifications, CONFIG.REFRESH_INTERVAL);
    }

    // ==================== EVENT LISTENERS ====================
    function setupEventListeners() {
        // Toggle dropdown al hacer clic en el botón
        elements.btn.addEventListener('click', function (e) {
            e.stopPropagation();
            toggleDropdown();

            // ✨ Pausar animación de badge cuando dropdown está abierto
            if (elements.dropdown.classList.contains('show')) {
                elements.badge.style.animation = 'none';
            } else {
                elements.badge.style.animation = '';
            }
        });

        // Marcar todas como leídas
        if (elements.markAllBtn) {
            elements.markAllBtn.addEventListener('click', function (e) {
                e.preventDefault();
                e.stopPropagation();
                markAllAsRead();
            });
        }

        // Event delegation para notificaciones (en lugar de onclick inline)
        elements.list.addEventListener('click', function (e) {
            const link = e.target.closest('.notification-link');
            if (link) {
                e.preventDefault();
                const item = link.closest('.notification-item');
                const id = item.dataset.id;
                const targetUrl = link.getAttribute('href');

                // Dynamic system notifications (sys_*) → navigate directly
                if (id && id.toString().startsWith('sys_')) {
                    if (targetUrl && targetUrl !== '#') {
                        window.location.href = targetUrl;
                    }
                    return;
                }

                markNotificationAsRead(id, targetUrl);
            }
        });

        // Cerrar dropdown al hacer clic fuera (con early return para performance)
        document.addEventListener('click', function (e) {
            // Early return si el dropdown no está visible
            if (!elements.dropdown.classList.contains('show')) return;

            if (!elements.dropdown.contains(e.target) && !elements.btn.contains(e.target)) {
                closeDropdown();
            }
        });
    }

    // ==================== CARGAR NOTIFICACIONES ====================
    function loadNotifications() {
        fetch(CONFIG.ENDPOINTS.GET_UNREAD, {
            headers: {
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => {
                // Validar status HTTP
                if (!response.ok) {
                    throw new Error(`HTTP ${response.status}: ${response.statusText}`);
                }

                const contentType = response.headers.get('content-type');
                if (!contentType || !contentType.includes('application/json')) {
                    console.error('⚠️ Expected JSON but received:', contentType);
                    throw new Error('Respuesta del servidor no es válida (No JSON)');
                }
                return response.json();
            })
            .then(data => {
                // Si el middleware detecta sesión expirada y envía redirección
                if (data && data.session_expired && data.redirect) {
                    console.warn('🔄 Sesión expirada detectada vía API');
                    window.location.href = data.redirect;
                    return;
                }

                if (data.success) {
                    updateBadge(data.count);
                    renderNotifications(data.notifications);
                } else {
                    showEmptyState();
                }
            })
            .catch(error => {
                // Ignorar errores si la página se está descargando (navigating away)
                if (error.name === 'AbortError') return;

                console.error('❌ Error al cargar notificaciones:', error);
                showErrorState();
            });
    }

    // ==================== ACTUALIZAR BADGE ====================
    function updateBadge(count) {
        if (!elements.badge) return;

        const hadNotifications = elements.badge.style.display !== 'none';
        const previousCount = parseInt(elements.badge.textContent) || 0;

        if (count > 0) {
            elements.badge.textContent = count > 99 ? '99+' : count;
            elements.badge.style.display = 'flex'; // flex para centrar contenido

            // ✨ Agregar clase para animaciones
            elements.btn.classList.add('has-notifications');

            // ✨ Trigger animación si hay nuevas notificaciones
            if (count > previousCount && hadNotifications) {
                triggerBellAnimation();
            }

            // ✨ NUEVO: Shake automático cada 30 segundos
            if (!window.notificationShakeInterval) {
                window.notificationShakeInterval = setInterval(() => {
                    if (elements.btn.classList.contains('has-notifications')) {
                        triggerBellAnimation();
                    }
                }, 30000); // 30 segundos
            }
        } else {
            elements.badge.style.display = 'none';
            // ✨ Remover clase de animaciones
            elements.btn.classList.remove('has-notifications');

            // ✨ NUEVO: Limpiar intervalo de shake
            if (window.notificationShakeInterval) {
                clearInterval(window.notificationShakeInterval);
                window.notificationShakeInterval = null;
            }
        }
    }

    // ==================== TRIGGER ANIMACIÓN DE CAMPANA ====================
    /**
     * Fuerza la animación de shake en la campana
     * Útil cuando llega una nueva notificación
     */
    function triggerBellAnimation() {
        // Remover clase para resetear animación
        elements.btn.classList.remove('has-notifications');

        // Force reflow (trick para reiniciar animación CSS)
        void elements.btn.offsetWidth;

        // Agregar clase de nuevo para trigger animación
        elements.btn.classList.add('has-notifications');
    }

    // ==================== RENDERIZAR NOTIFICACIONES ====================
    function renderNotifications(notifications) {
        if (!elements.list) return;

        if (!notifications || notifications.length === 0) {
            showEmptyState();
            return;
        }

        let html = '<ul class="notifications-list">';

        notifications.forEach(notification => {
            const icon = NOTIFICATION_ICONS[notification.tipo] || NOTIFICATION_ICONS.default;
            const url = sanitizeUrl(notification.url); // ✅ Sanitizar URL para prevenir XSS

            html += `
                <li class="notification-item ${notification.leido == 0 ? 'unread' : ''}" 
                    data-id="${notification.id}">
                    <a href="${url}" class="notification-link">
                        <div class="notification-icon">
                            <i class="ti ${icon}"></i>
                        </div>
                        <div class="notification-content">
                            <div class="notification-title">${escapeHtml(notification.titulo)}</div>
                            <div class="notification-message">${escapeHtml(notification.mensaje)}</div>
                            <div class="notification-time">
                                <i class="ti ti-clock"></i> ${notification.time_ago}
                            </div>
                        </div>
                        ${notification.leido == 0 ? '<span class="unread-indicator"></span>' : ''}
                    </a>
                </li>
            `;
        });

        html += '</ul>';
        elements.list.innerHTML = html;
    }

    // ==================== ESTADOS VACÍOS Y ERROR ====================
    function showEmptyState() {
        if (!elements.list) return;

        elements.list.innerHTML = `
            <div class="notifications-empty">
                <i class="ti ti-bell-off"></i>
                <p>Estás al día</p>
                <span>No tienes notificaciones nuevas</span>
            </div>
        `;
    }

    function showErrorState() {
        if (!elements.list) return;

        elements.list.innerHTML = `
            <div class="notifications-empty">
                <i class="ti ti-alert-circle"></i>
                <p>Error al cargar</p>
                <span>No se pudieron cargar las notificaciones</span>
            </div>
        `;
    }

    // ==================== MARCAR COMO LEÍDA ====================
    /**
     * Marcar notificación como leída (función privada, no global)
     * @param {number} id - ID de la notificación
     * @param {string} targetUrl - URL de destino
     */
    function markNotificationAsRead(id, targetUrl) {
        fetch(CONFIG.ENDPOINTS.MARK_AS_READ + id, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // ✅ Optimistic update: actualizar UI inmediatamente
                    const item = document.querySelector(`[data-id="${id}"]`);
                    if (item) {
                        item.classList.remove('unread');
                        const indicator = item.querySelector('.unread-indicator');
                        if (indicator) indicator.remove();
                    }

                    // Recargar para sincronizar con servidor
                    loadNotifications();

                    // Si hay URL válida, redirigir
                    if (targetUrl && targetUrl !== '#') {
                        setTimeout(() => window.location.href = targetUrl, 300);
                    }
                }
            })
            .catch(error => {
                console.error('❌ Error al marcar notificación:', error);
                if (typeof NotificationService !== 'undefined') {
                    NotificationService.error('Error al marcar notificación');
                }
            });
    }

    // ==================== MARCAR TODAS COMO LEÍDAS ====================
    function markAllAsRead() {
        fetch(CONFIG.ENDPOINTS.MARK_ALL_READ, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            }
        })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    loadNotifications();
                    if (typeof NotificationService !== 'undefined') {
                        NotificationService.success('Todas las notificaciones marcadas como leídas');
                    }
                }
            })
            .catch(error => {
                console.error('❌ Error al marcar todas:', error);
            });
    }

    // ==================== TOGGLE DROPDOWN ====================
    function toggleDropdown() {
        elements.dropdown.classList.toggle('show');
        elements.btn.classList.toggle('active');
    }

    function closeDropdown() {
        elements.dropdown.classList.remove('show');
        elements.btn.classList.remove('active');
    }

    // ==================== UTILIDADES ====================
    function escapeHtml(text) {
        const div = document.createElement('div');
        div.textContent = text;
        return div.innerHTML;
    }

    /**
     * Sanitizar URL para prevenir XSS
     * Bloquea URLs con javascript:, data:, vbscript:
     */
    function sanitizeUrl(url) {
        if (!url || url === '#') return '#';

        // Rechazar URLs peligrosas
        const dangerous = /^(javascript|data|vbscript):/i;
        if (dangerous.test(url)) {
            console.warn('⚠️ URL peligrosa bloqueada:', url);
            return '#';
        }

        return url;
    }

    // ==================== AUTO-INIT ====================
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', init);
    } else {
        init();
    }

})();
