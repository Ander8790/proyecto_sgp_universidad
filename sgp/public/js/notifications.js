/**
 * Notifications JavaScript
 * Handles real-time notification loading and updates
 */

// Notification icon mapping
const notificationIcons = {
    'usuario_creado': 'ti-user-plus',
    'perfil_actualizado': 'ti-user-check',
    'password_reset': 'ti-key',
    'sistema': 'ti-bell'
};

/**
 * Load unread notifications
 */
function loadNotifications() {
    fetch(URLROOT + '/notifications/getUnread')
        .then(response => {
            // Check if response is JSON before parsing
            const contentType = response.headers.get('content-type');
            if (contentType && contentType.includes('application/json')) {
                return response.json();
            } else {
                // Return empty data if not JSON (notifications not available)
                return { success: false, count: 0, notifications: [] };
            }
        })
        .then(data => {
            if (data && data.success) {
                updateNotificationBadge(data.count);
                renderNotifications(data.notifications);
            } else {
                // Notifications not available - show empty state
                updateNotificationBadge(0);
                renderNotifications([]);
            }
        })
        .catch(error => {
            // Network error or other issue - fail silently
            updateNotificationBadge(0);
            renderNotifications([]);
        });
}

/**
 * Update notification badge count
 */
function updateNotificationBadge(count) {
    const badge = document.getElementById('notificationCount');
    const header = document.getElementById('notificationHeader');

    if (count > 0) {
        badge.textContent = count > 99 ? '99+' : count;
        badge.style.display = 'block';
        if (header) {
            header.textContent = count + (count === 1 ? ' Notificación' : ' Notificaciones');
        }
    } else {
        badge.style.display = 'none';
        if (header) {
            header.textContent = 'Sin notificaciones';
        }
    }
}

/**
 * Render notifications list
 */
function renderNotifications(notifications) {
    const container = document.getElementById('notificationList');

    if (!container) return;

    if (notifications.length === 0) {
        container.innerHTML = `
            <div class="dropdown-item text-center" style="padding: 20px;">
                <i class="ti ti-bell-off" style="font-size: 32px; color: #cbd5e1;"></i>
                <p style="margin-top: 8px; color: #64748b;">No tienes notificaciones</p>
            </div>
        `;
        return;
    }

    let html = '';
    notifications.forEach(notification => {
        const icon = notificationIcons[notification.tipo] || 'ti-bell';
        const url = notification.url || 'javascript:void(0)';

        html += `
            <a href="${url}" class="dropdown-item notification-item" 
               data-id="${notification.id}"
               onclick="markNotificationAsRead(${notification.id})"
               style="padding: 12px 16px; border-left: 3px solid #3b82f6; transition: all 0.2s;">
                <div class="d-flex align-items-start">
                    <i class="ti ${icon} mr-3" style="font-size: 20px; color: #3b82f6; margin-top: 2px;"></i>
                    <div class="flex-grow-1">
                        <div style="font-size: 0.9rem; font-weight: 600; color: #1f2937; margin-bottom: 4px;">
                            ${notification.titulo}
                        </div>
                        <div style="font-size: 0.85rem; color: #64748b; margin-bottom: 4px;">
                            ${notification.mensaje}
                        </div>
                        <small class="text-muted" style="font-size: 0.75rem;">
                            <i class="ti ti-clock" style="font-size: 12px;"></i> ${notification.time_ago}
                        </small>
                    </div>
                </div>
            </a>
        `;
    });

    container.innerHTML = html;
}

/**
 * Mark notification as read
 */
function markNotificationAsRead(id) {
    fetch(URLROOT + '/notifications/markAsRead/' + id, {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                // Reload notifications after marking as read
                setTimeout(loadNotifications, 300);
            }
        })
        .catch(error => {
            console.error('Error marking notification:', error);
        });
}

/**
 * Mark all notifications as read
 */
function markAllNotificationsAsRead() {
    fetch(URLROOT + '/notifications/markAllAsRead', {
        method: 'POST'
    })
        .then(response => response.json())
        .then(data => {
            if (data.success) {
                loadNotifications();
            }
        })
        .catch(error => {
            console.error('Error marking all notifications:', error);
        });
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', function () {
    loadNotifications();

    // Auto-refresh every 30 seconds
    setInterval(loadNotifications, 30000);
});
