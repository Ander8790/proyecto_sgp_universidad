<?php
/**
 * Header - Barra Superior del Sistema
 * 
 * MIDDLEWARE "LA JAULA":
 * Si el usuario tiene requiere_cambio_clave = 1, NO se renderiza el header.
 * Esto complementa la supresión del sidebar para "enjaular" al usuario en el wizard.
 */

// ============================================
// MIDDLEWARE: Verificar Estado del Usuario
// ============================================
if (Session::get('requiere_cambio_clave') == 1) {
    // Usuario está "enjaulado" → NO renderizar header
    return;
}
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light">
    
    <!-- ZONA IZQUIERDA: Hamburguesa + Logo SGP -->
    <ul class="navbar-nav align-items-center">
        <!-- Toggle Desktop - Colapsar/Expandir Sidebar (Desktop Only) -->
        <li class="nav-item d-none d-lg-block">
            <button id="sidebarCollapseToggle" class="btn-toggle" aria-label="Colapsar/Expandir sidebar" title="Colapsar sidebar">
                <i class="ti ti-menu-2"></i>
            </button>
        </li>
        
        <!-- Hamburguesa Mobile (Mobile Only) -->
        <li class="nav-item d-lg-none">
            <button id="sidebarToggle" class="btn-toggle" aria-label="Toggle sidebar">
                <i class="ti ti-menu-2"></i>
            </button>
        </li>
        
        <!-- Logo SGP (Siempre visible) -->
        <li class="nav-item ml-2">
            <?php
            $user_role_id = Session::get('role_id') ?? 0;
            $dashboardUrl = URLROOT . '/dashboard';
            if ($user_role_id == 1) $dashboardUrl = URLROOT . '/admin';
            if ($user_role_id == 2) $dashboardUrl = URLROOT . '/tutor';
            if ($user_role_id == 3) $dashboardUrl = URLROOT . '/pasante';
            ?>
            <a href="<?= $dashboardUrl ?>" class="brand-link d-flex align-items-center text-decoration-none">
                <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" 
                     style="height: 40px; width: auto; object-fit: contain;">
            </a>
        </li>
    </ul>

    <!-- ZONA CENTRAL: Logo Institucional -->
    <div class="institutional-strip mx-auto d-none d-lg-flex align-items-center justify-content-center">
        <img src="<?= URLROOT ?>/img/gobe.png" 
             alt="Gobernación de Bolívar - Salud" 
             style="height: 52px; width: auto; max-width: 320px; object-fit: contain;">
    </div>

    <!-- ZONA DERECHA: Notificaciones + Perfil -->
    <ul class="navbar-nav ml-auto align-items-center" style="gap: 8px;">
        
        <!-- Notificaciones -->
        <li class="nav-item dropdown">
            <a class="nav-link position-relative header-icon-btn" data-toggle="dropdown" href="#" aria-label="Notificaciones">
                <i class="ti ti-bell"></i>
                <span id="notificationCount" class="badge badge-danger notification-badge" style="display: none;">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right">
                <span class="dropdown-item dropdown-header">Notificaciones</span>
                <div class="dropdown-divider"></div>
                <div id="notificationList">
                    <div class="dropdown-item text-center p-3">
                        <p class="text-muted mb-0">Sin notificaciones nuevas</p>
                    </div>
                </div>
            </div>
        </li>

        <!-- Perfil Usuario -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center user-profile-link" data-toggle="dropdown" href="#" aria-label="Perfil de usuario">
                <div class="profile-avatar">
                    <?php 
                    $user_name = Session::get('user_name') ?? Session::get('nombres') ?? 'Usuario';
                    echo strtoupper(substr($user_name, 0, 1));
                    ?>
                </div>
                <div class="d-none d-md-block ml-2 text-left">
                    <span class="d-block font-weight-bold line-height-1" style="font-size: 0.9rem; color: #1e293b;">
                        <?php echo htmlspecialchars(explode(' ', $user_name)[0]); ?>
                    </span>
                    <small class="text-muted d-block" style="font-size: 0.75rem;">
                        <?php
                        $rol_id = Session::get('role_id') ?? 0;
                        echo ($rol_id == 1) ? 'Administrador' : (($rol_id == 2) ? 'Tutor' : 'Pasante');
                        ?>
                    </small>
                </div>
                <i class="ti ti-chevron-down ml-2" style="font-size: 0.8rem; color: #64748b;"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right profile-dropdown">
                <!-- Header del Dropdown -->
                <div class="dropdown-header d-flex align-items-center p-3 mb-2" style="background: linear-gradient(135deg, #162660 0%, #0d1a3d 100%); border-radius: 8px;">
                    <div class="profile-avatar mr-3" style="width: 40px; height: 40px; background: rgba(255,255,255,0.2); border: 2px solid rgba(255,255,255,0.3);">
                        <?= strtoupper(substr($user_name, 0, 1)) ?>
                    </div>
                    <div class="text-white text-left overflow-hidden">
                        <div class="font-weight-bold text-truncate"><?= htmlspecialchars($user_name) ?></div>
                        <div class="small opacity-75">Conectado</div>
                    </div>
                </div>

                <a href="<?= URLROOT ?>/perfil/ver" class="dropdown-item">
                    <i class="ti ti-user mr-2 text-primary"></i> Mi Perfil
                </a>
                
                <div class="dropdown-divider"></div>
                
                <a href="javascript:void(0)" onclick="confirmLogout()" class="dropdown-item text-danger">
                    <i class="ti ti-logout mr-2"></i> Cerrar Sesión
                </a>
            </div>
        </li>
    </ul>
</nav>

<script>
/**
 * Confirmar Logout con SweetAlert
 * 
 * PROPÓSITO:
 * Mostrar confirmación antes de cerrar sesión para evitar cierres accidentales.
 */
function confirmLogout() {
    Swal.fire({
        title: '¿Cerrar Sesión?',
        text: '¿Estás seguro que deseas salir?',
        icon: 'question',
        showCancelButton: true,
        confirmButtonColor: '#162660',
        cancelButtonColor: '#6c757d',
        confirmButtonText: 'Sí, salir',
        cancelButtonText: 'Cancelar'
    }).then((result) => {
        if (result.isConfirmed) {
            window.location.href = '<?= URLROOT ?>/auth/logout';
        }
    });
}
</script>
