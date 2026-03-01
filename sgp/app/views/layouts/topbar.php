<!-- =====================================================
     TOPBAR (Header-First con Cintillo Institucional)
     ===================================================== -->
<nav class="main-header">
    <!-- Zona Izquierda: Toggle + Logo SGP -->
    <div class="header-left">
        <!-- Toggle Button -->
        <button class="menu-toggle" id="menuToggle" aria-label="Toggle Menu">
            <span></span>
            <span></span>
            <span></span>
        </button>
        
        <!-- Logo SGP (Imagen) -->
        <a href="<?= URLROOT ?>/dashboard" class="brand-link" style="padding: 0; margin-left: 8px;">
            <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" 
                 class="brand-image" 
                 style="opacity: .8; max-height: 35px; width: auto; margin: 0;">
        </a>
    </div>
    
    <!-- Zona Central: Logo Institucional (oculto en móvil) -->
    <div class="header-center d-none d-md-flex" style="position: absolute; left: 50%; transform: translateX(-50%); height: 100%; align-items: center;">
        <img src="<?= URLROOT ?>/img/gobe.png" 
             alt="Gobernación de Bolívar - Salud" 
             style="max-height: 52px; width: auto; max-width: 320px; object-fit: contain;">
    </div>
    
    <!-- Zona Derecha: Notificaciones + Perfil -->
    <div class="header-right">
        <!-- ========== NOTIFICACIONES ========== -->
        <div class="notifications-wrapper">
            <button class="notification-btn" id="notificationBtn" aria-label="Notificaciones">
                <i class="ti ti-bell"></i>
                <span class="notification-badge" id="notificationBadge" style="display: none;">0</span>
            </button>
            
            <!-- Dropdown de Notificaciones -->
            <div class="notifications-dropdown" id="notificationsDropdown">
                <!-- Header -->
                <div class="notifications-header">
                    <span class="notifications-title">Notificaciones</span>
                    <button class="mark-all-read-btn" id="markAllReadBtn" title="Marcar todas como leídas">
                        <i class="ti ti-checks"></i>
                    </button>
                </div>
                
                <!-- Body (Lista de notificaciones) -->
                <div class="notifications-body" id="notificationList">
                    <!-- Se inyecta dinámicamente vía JS -->
                </div>
                
                <!-- Footer -->
                <div class="notifications-footer">
                    <a href="<?= URLROOT ?>/notificaciones">Ver todas las notificaciones</a>
                </div>
            </div>
        </div>
        
        <!-- ========== PERFIL ========== -->
        <div class="profile-dropdown" onclick="toggleProfileMenu()">
            <div class="profile-avatar">
                <?php 
                $userName = Session::get('user_name') ?? 'Usuario';
                echo strtoupper(substr($userName, 0, 1));
                ?>
            </div>
            <div class="profile-info">
                <div class="profile-name"><?= $userName ?></div>
                <div class="profile-role">
                    <?php
                    $role_id = Session::get('role_id');
                    echo $role_id == 1 ? 'Administrador' : ($role_id == 2 ? 'Tutor' : 'Pasante');
                    ?>
                </div>
            </div>
            <i class="ti ti-chevron-down"></i>
        </div>
        
        <!-- Menú Desplegable del Perfil -->
        <div class="profile-menu" id="profileMenu">
            <a href="<?= URLROOT ?>/perfil/ver" class="profile-menu-item">
                <i class="ti ti-user"></i>
                <span>Mi Perfil</span>
            </a>
            <div class="profile-menu-divider"></div>
            <a href="<?= URLROOT ?>/auth/logout" class="profile-menu-item" onclick="event.preventDefault(); confirmLogout();">
                <i class="ti ti-logout"></i>
                <span>Cerrar Sesión</span>
            </a>
        </div>
    </div>
</nav>

<script>
// Toggle del menú de perfil
function toggleProfileMenu() {
    const menu = document.getElementById('profileMenu');
    menu.classList.toggle('active');
}

// Cerrar menú al hacer click fuera
document.addEventListener('click', function(event) {
    const menu = document.getElementById('profileMenu');
    const profileDropdown = document.querySelector('.profile-dropdown');
    
    if (menu && profileDropdown && !profileDropdown.contains(event.target)) {
        menu.classList.remove('active');
    }
});

// Confirmación de cierre de sesión con SweetAlert
function confirmLogout() {
    Swal.fire({
        title: '¿Estás seguro de que deseas cerrar sesión?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#162660',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Aceptar',
        cancelButtonText: 'Cancelar',
        reverseButtons: true
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= URLROOT ?>/auth/logout';
        }
    });
}
</script>
