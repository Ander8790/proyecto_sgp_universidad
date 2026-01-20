<?php
/**
 * Header Component - Full Width Dominant Design
 * Diseño minimalista con mejores prácticas UI/UX
 */
?>
<nav class="main-header navbar navbar-expand navbar-white navbar-light" style="
    position: fixed; 
    top: 0; 
    left: 0; 
    width: 100%; 
    height: 70px; 
    z-index: 1050; 
    border-bottom: 1px solid #e5e7eb;
    margin-left: 0 !important;
    padding: 0 20px;
    background: #ffffff;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.05);">

    <!-- Zona Izquierda: Hamburguesa + Logo SGP -->
    <ul class="navbar-nav align-items-center" style="gap: 16px;">
        <li class="nav-item">
            <a class="nav-link" data-widget="pushmenu" href="javascript:void(0)" role="button" id="menuToggle" 
               style="color: #162660; padding: 8px; display: flex; align-items: center; justify-content: center;">
                <i class="ti ti-menu-2" style="font-size: 24px;"></i>
            </a>
        </li>
        <li class="nav-item">
            <?php
            // Determinar URL del dashboard según rol
            $user_role_id = Session::get('role_id') ?? 0;
            $dashboardUrl = URLROOT . '/dashboard'; // Fallback
            if ($user_role_id == 1) $dashboardUrl = URLROOT . '/admin';
            if ($user_role_id == 2) $dashboardUrl = URLROOT . '/tutor';
            if ($user_role_id == 3) $dashboardUrl = URLROOT . '/pasante';
            ?>
            <a href="<?= $dashboardUrl ?>" class="brand-link" 
               style="padding: 0; display: flex; align-items: center; text-decoration: none;">
                <img src="<?= URLROOT ?>/img/logo.png" alt="SGP Logo" 
                     style="height: 40px; width: auto; object-fit: contain;">
            </a>
        </li>
    </ul>

    <!-- Zona Central: Cintillo Institucional (oculto en móvil) -->
    <div class="institutional-strip mx-auto d-none d-lg-flex align-items-center justify-content-center flex-grow-1" 
         style="height: 100%; padding: 0 20px;">
        <img src="<?= URLROOT ?>/img/cintillo.png" alt="Cintillo Institucional" 
             style="height: 50px; width: auto; max-width: 700px; object-fit: contain;">
    </div>

    <!-- Zona Derecha: Notificaciones + Perfil -->
    <ul class="navbar-nav ml-auto align-items-center" style="gap: 8px;">
        
        <!-- Notificaciones -->
        <li class="nav-item dropdown">
            <a class="nav-link position-relative" data-toggle="dropdown" href="javascript:void(0)" 
               style="padding: 8px 12px; display: flex; align-items: center; border-radius: 8px; transition: background 0.2s;">
                <i class="ti ti-bell" style="font-size: 22px; color: #64748b;"></i>
                <span id="notificationCount" class="badge badge-danger" 
                      style="display: none; position: absolute; top: 6px; right: 8px; font-size: 10px; padding: 2px 5px; border-radius: 10px; min-width: 18px;">0</span>
            </a>
            <div class="dropdown-menu dropdown-menu-lg dropdown-menu-right" style="min-width: 320px; max-height: 400px; overflow-y: auto;">
                <span id="notificationHeader" class="dropdown-item dropdown-header" style="font-weight: 600; color: #1f2937;">
                    Cargando...
                </span>
                <div class="dropdown-divider"></div>
                
                <!-- Dynamic notification list -->
                <div id="notificationList">
                    <div class="dropdown-item text-center" style="padding: 20px;">
                        <i class="ti ti-loader animate-spin" style="font-size: 24px; color: #cbd5e1;"></i>
                        <p style="margin-top: 8px; color: #64748b;">Cargando notificaciones...</p>
                    </div>
                </div>
                
                <div class="dropdown-divider"></div>
                <a href="javascript:void(0)" onclick="markAllNotificationsAsRead()" class="dropdown-item dropdown-footer text-center" style="font-weight: 600; color: #3b82f6;">
                    <i class="ti ti-check-all"></i> Marcar todas como leídas
                </a>
            </div>
        </li>

        <!-- Usuario -->
        <li class="nav-item dropdown">
            <a class="nav-link d-flex align-items-center" data-toggle="dropdown" href="javascript:void(0)" 
               style="padding: 8px 14px; gap: 12px; border-radius: 12px; transition: all 0.3s; background: #f8fafc; border: 1px solid #e2e8f0;">
                <span class="d-none d-md-inline" style="font-size: 0.9rem; color: #1e293b; font-weight: 600;">
                    <?php 
                    $user_name = Session::get('user_name') ?? Session::get('nombres') ?? Session::get('nombre') ?? 'Usuario';
                    echo htmlspecialchars(explode(' ', $user_name)[0]);
                    ?>
                </span>
                <div class="profile-avatar" style="width: 36px; height: 36px; border-radius: 50%; background: linear-gradient(135deg, #162660 0%, #0d1a3d 100%); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.9rem; box-shadow: 0 4px 6px rgba(22, 38, 96, 0.3);">
                    <?php 
                    $user_name = Session::get('user_name') ?? Session::get('nombres') ?? Session::get('nombre') ?? 'Usuario';
                    echo strtoupper(substr($user_name, 0, 1));
                    ?>
                </div>
                <i class="ti ti-chevron-down" style="font-size: 14px; color: #64748b; transition: transform 0.3s;"></i>
            </a>
            <div class="dropdown-menu dropdown-menu-right" style="min-width: 260px; border-radius: 12px; box-shadow: 0 10px 40px rgba(0,0,0,0.15); border: 1px solid #e2e8f0; padding: 8px;">
                <!-- User Info Header -->
                <div class="dropdown-item dropdown-header" style="background: linear-gradient(135deg, #162660 0%, #0d1a3d 100%); border-radius: 8px; padding: 16px; margin-bottom: 8px; border: none;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 48px; height: 48px; border-radius: 50%; background: rgba(255,255,255,0.2); backdrop-filter: blur(10px); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 1.2rem; border: 2px solid rgba(255,255,255,0.3);">
                            <?php 
                            echo strtoupper(substr($user_name, 0, 1));
                            ?>
                        </div>
                        <div style="flex: 1;">
                            <div style="font-weight: 700; color: white; font-size: 0.95rem; margin-bottom: 2px;">
                                <?php echo htmlspecialchars($user_name); ?>
                            </div>
                            <div style="font-size: 0.8rem; color: rgba(255,255,255,0.9); display: flex; align-items: center; gap: 4px;">
                                <i class="ti ti-shield-check" style="font-size: 14px;"></i>
                                <?php
                                $rol_id = Session::get('role_id') ?? Session::get('rol_id') ?? 0;
                                $role = 'Invitado';
                                switch ($rol_id) {
                                    case 1: $role = 'Administrador'; break;
                                    case 2: $role = 'Tutor'; break;
                                    case 3: $role = 'Pasante'; break;
                                    default: $role = Session::get('rol_nombre') ?? 'Invitado'; break;
                                }
                                echo htmlspecialchars($role);
                                ?>
                            </div>
                        </div>
                    </div>
                </div>
                
                <!-- Menu Items -->
                <a href="<?= URLROOT ?>/perfil/ver" class="dropdown-item" style="padding: 12px 16px; border-radius: 8px; transition: all 0.2s; display: flex; align-items: center; gap: 12px; margin-bottom: 4px;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #eff6ff; display: flex; align-items: center; justify-content: center;">
                        <i class="ti ti-user" style="font-size: 18px; color: #3b82f6;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: #1e293b;">Mi Perfil</div>
                        <div style="font-size: 0.75rem; color: #64748b;">Ver y editar información</div>
                    </div>
                </a>
                
                <div class="dropdown-divider" style="margin: 8px 0;"></div>
                
                <a href="javascript:void(0)" class="dropdown-item" 
                   onclick="confirmLogout()"
                   style="padding: 12px 16px; border-radius: 8px; transition: all 0.2s; display: flex; align-items: center; gap: 12px; background: #fef2f2;">
                    <div style="width: 32px; height: 32px; border-radius: 8px; background: #fee2e2; display: flex; align-items: center; justify-content: center;">
                        <i class="ti ti-logout" style="font-size: 18px; color: #dc2626;"></i>
                    </div>
                    <div>
                        <div style="font-size: 0.9rem; font-weight: 600; color: #dc2626;">Cerrar Sesión</div>
                        <div style="font-size: 0.75rem; color: #991b1b;">Salir del sistema</div>
                    </div>
                </a>
            </div>
        </li>
    </ul>
</nav>

<script>
// Logout confirmation with SweetAlert
function confirmLogout() {
    Swal.fire({
        title: '¿Cerrar Sesión?',
        text: '¿Estás seguro que deseas salir del sistema?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#dc2626',
        cancelButtonColor: '#6b7280',
        confirmButtonText: '<i class="ti ti-logout"></i> Sí, cerrar sesión',
        cancelButtonText: '<i class="ti ti-x"></i> Cancelar',
        reverseButtons: true,
        customClass: {
            confirmButton: 'btn btn-danger',
            cancelButton: 'btn btn-secondary'
        }
    }).then((result) => {
        if (result.isConfirmed) {
            // Show loading
            Swal.fire({
                title: 'Cerrando sesión...',
                text: 'Por favor espera',
                icon: 'info',
                allowOutsideClick: false,
                allowEscapeKey: false,
                showConfirmButton: false,
                didOpen: () => {
                    Swal.showLoading();
                }
            });
            
            // Redirect to logout
            setTimeout(() => {
                window.location.href = '<?= URLROOT ?>/auth/logout';
            }, 500);
        }
    });
}
</script>
