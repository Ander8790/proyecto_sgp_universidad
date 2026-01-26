<?php
// === ADAPTADOR DE COMPATIBILIDAD ===
$user_name = Session::get('user_name') ?? Session::get('nombres') ?? Session::get('nombre') ?? 'Usuario';
$rol_id = Session::get('role_id') ?? Session::get('rol_id') ?? 0;
$role = 'Invitado';

switch ($rol_id) {
    case 1: $role = 'Administrador'; break;
    case 2: $role = 'Tutor'; break;
    case 3: $role = 'Pasante'; break;
    default: 
        $role = Session::get('rol_nombre') ?? 'Invitado';
        break;
}

// Determinar URL del dashboard según rol
$dashboardUrl = URLROOT . '/dashboard';
if ($rol_id == 1) $dashboardUrl = URLROOT . '/admin';
if ($rol_id == 2) $dashboardUrl = URLROOT . '/tutor';
if ($rol_id == 3) $dashboardUrl = URLROOT . '/pasante';

// Función helper para determinar si un link está activo
function isActive($url) {
    $current = $_SERVER['REQUEST_URI'];
    return strpos($current, $url) !== false ? 'active' : '';
}
?>

<aside class="main-sidebar">
    <!-- Navegación Principal -->
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <!-- Dashboard -->
            <li>
                <a href="<?= $dashboardUrl ?>" class="nav-link <?= isActive($rol_id == 1 ? '/admin' : ($rol_id == 2 ? '/tutor' : '/pasante')) ?>">
                    <i class="ti ti-home"></i>
                    <span class="menu-text">Inicio</span>
                </a>
            </li>
            
            <?php if ($role == 'Administrador'): ?>
            <!-- Menú Administrador -->
            <li>
                <a href="<?= URLROOT ?>/users" class="nav-link <?= isActive('/users') ?>">
                    <i class="ti ti-users"></i>
                    <span class="menu-text">Usuarios</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/pasantes" class="nav-link <?= isActive('/pasantes') ?>">
                    <i class="ti ti-user-check"></i>
                    <span class="menu-text">Pasantes</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/asistencias" class="nav-link <?= isActive('/asistencias') ?>">
                    <i class="ti ti-calendar-stats"></i>
                    <span class="menu-text">Asistencias</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/reportes" class="nav-link <?= isActive('/reportes') ?>">
                    <i class="ti ti-file-analytics"></i>
                    <span class="menu-text">Reportes</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/backup" class="nav-link <?= isActive('/backup') ?>">
                    <i class="ti ti-database"></i>
                    <span class="menu-text">Respaldos</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/configuracion" class="nav-link <?= isActive('/configuracion') ?>">
                    <i class="ti ti-settings"></i>
                    <span class="menu-text">Configuración</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role == 'Tutor'): ?>
            <!-- Menú Tutor -->
            <li>
                <a href="<?= URLROOT ?>/tutor/pasantes" class="nav-link <?= isActive('/tutor/pasantes') ?>">
                    <i class="ti ti-users-group"></i>
                    <span class="menu-text">Mis Pasantes</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/tutor/asistencias" class="nav-link <?= isActive('/tutor/asistencias') ?>">
                    <i class="ti ti-clock-check"></i>
                    <span class="menu-text">Asistencias</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/evaluaciones" class="nav-link <?= isActive('/evaluaciones') ?>">
                    <i class="ti ti-star"></i>
                    <span class="menu-text">Evaluaciones</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role == 'Pasante'): ?>
            <!-- Menú Pasante -->
            <li>
                <a href="<?= URLROOT ?>/pasante/asistencia" class="nav-link <?= isActive('/pasante/asistencia') ?>">
                    <i class="ti ti-clock"></i>
                    <span class="menu-text">Mi Asistencia</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/bitacora" class="nav-link <?= isActive('/bitacora') ?>">
                    <i class="ti ti-file-text"></i>
                    <span class="menu-text">Bitácora</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <!-- Footer con Logout + Copyright -->
    <div class="sidebar-footer">
        <a href="<?= URLROOT ?>/auth/logout" class="btn-logout" onclick="event.preventDefault(); confirmLogout();" title="Cerrar Sesión">
            <i class="ti ti-power"></i>
            <span>Cerrar Sesión</span>
        </a>
        
    </div>
</aside>
