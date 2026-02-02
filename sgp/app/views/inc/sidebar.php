<?php
/**
 * Sidebar - Navegación Lateral del Sistema
 * 
 * MIDDLEWARE "LA JAULA":
 * Si el usuario tiene requiere_cambio_clave = 1, NO se renderiza el sidebar.
 * Esto obliga al usuario a completar el wizard antes de acceder al sistema.
 * 
 * RAZÓN TÉCNICA:
 * Un usuario "enjaulado" solo debe ver el wizard, sin acceso a otras secciones.
 * Al suprimir sidebar y navbar, garantizamos que no pueda navegar a otras páginas.
 */

// ============================================
// MIDDLEWARE: Verificar Estado del Usuario
// ============================================
if (Session::get('requiere_cambio_clave') == 1) {
    // Usuario está "enjaulado" → NO renderizar sidebar
    return;
}

// ============================================
// ADAPTADOR DE COMPATIBILIDAD
// ============================================
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

/**
 * Helper: Determinar si un link está activo
 * 
 * @param string $url URL a verificar
 * @return string Clase CSS 'active' si coincide, '' si no
 */
function isActive($url) {
    $current = $_SERVER['REQUEST_URI'];
    return strpos($current, $url) !== false ? 'active' : '';
}
?>

<aside class="main-sidebar">
    <nav class="sidebar-nav">
        <ul class="nav-list">
            <!-- Dashboard -->
            <li>
                <a href="<?= $dashboardUrl ?>" 
                   class="nav-link <?= isActive($rol_id == 1 ? '/admin' : ($rol_id == 2 ? '/tutor' : '/pasante')) ?>"
                   data-tooltip="Inicio">
                    <i class="ti ti-home"></i>
                    <span class="menu-text">Inicio</span>
                </a>
            </li>
            
            <?php if ($role == 'Administrador'): ?>
            <!-- Menú Administrador -->
            <li>
                <a href="<?= URLROOT ?>/users" 
                   class="nav-link <?= isActive('/users') ?>"
                   data-tooltip="Usuarios">
                    <i class="ti ti-users"></i>
                    <span class="menu-text">Usuarios</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/pasantes" 
                   class="nav-link <?= isActive('/pasantes') ?>"
                   data-tooltip="Pasantes">
                    <i class="ti ti-user-check"></i>
                    <span class="menu-text">Pasantes</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/asistencias" 
                   class="nav-link <?= isActive('/asistencias') ?>"
                   data-tooltip="Asistencias">
                    <i class="ti ti-calendar-stats"></i>
                    <span class="menu-text">Asistencias</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/reportes" 
                   class="nav-link <?= isActive('/reportes') ?>"
                   data-tooltip="Informes">
                    <i class="ti ti-file-analytics"></i>
                    <span class="menu-text">Informes</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/analiticas" 
                   class="nav-link <?= isActive('/analiticas') ?>"
                   data-tooltip="Analíticas">
                    <i class="ti ti-chart-dots"></i>
                    <span class="menu-text">Analíticas</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/backup" 
                   class="nav-link <?= isActive('/backup') ?>"
                   data-tooltip="Respaldos">
                    <i class="ti ti-database"></i>
                    <span class="menu-text">Respaldos</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/configuracion" 
                   class="nav-link <?= isActive('/configuracion') ?>"
                   data-tooltip="Configuración">
                    <i class="ti ti-settings"></i>
                    <span class="menu-text">Configuración</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role == 'Tutor'): ?>
            <!-- Menú Tutor -->
            <li>
                <a href="<?= URLROOT ?>/tutor/pasantes" 
                   class="nav-link <?= isActive('/tutor/pasantes') ?>"
                   data-tooltip="Mis Pasantes">
                    <i class="ti ti-users-group"></i>
                    <span class="menu-text">Mis Pasantes</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/tutor/asistencias" 
                   class="nav-link <?= isActive('/tutor/asistencias') ?>"
                   data-tooltip="Asistencias">
                    <i class="ti ti-clock-check"></i>
                    <span class="menu-text">Asistencias</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/evaluaciones" 
                   class="nav-link <?= isActive('/evaluaciones') ?>"
                   data-tooltip="Evaluaciones">
                    <i class="ti ti-star"></i>
                    <span class="menu-text">Evaluaciones</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/analiticas" 
                   class="nav-link <?= isActive('/analiticas') ?>"
                   data-tooltip="Analíticas">
                    <i class="ti ti-chart-dots"></i>
                    <span class="menu-text">Analíticas</span>
                </a>
            </li>
            <?php endif; ?>

            <?php if ($role == 'Pasante'): ?>
            <!-- Menú Pasante -->
            <li>
                <a href="<?= URLROOT ?>/pasante/asistencia" 
                   class="nav-link <?= isActive('/pasante/asistencia') ?>"
                   data-tooltip="Mi Asistencia">
                    <i class="ti ti-clock"></i>
                    <span class="menu-text">Mi Asistencia</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/bitacora" 
                   class="nav-link <?= isActive('/bitacora') ?>"
                   data-tooltip="Bitácora">
                    <i class="ti ti-file-text"></i>
                    <span class="menu-text">Bitácora</span>
                </a>
            </li>
            <li>
                <a href="<?= URLROOT ?>/analiticas" 
                   class="nav-link <?= isActive('/analiticas') ?>"
                   data-tooltip="Analíticas">
                    <i class="ti ti-chart-dots"></i>
                    <span class="menu-text">Analíticas</span>
                </a>
            </li>
            <?php endif; ?>
        </ul>
    </nav>

    <div class="sidebar-footer">
        <a href="<?= URLROOT ?>/auth/logout" class="btn-logout" onclick="event.preventDefault(); confirmLogout();" title="Cerrar Sesión">
            <i class="ti ti-power"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>
