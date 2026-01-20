<?php
// === ADAPTADOR DE COMPATIBILIDAD V1 ===
// Usar la clase Session en lugar de acceso directo a $_SESSION
$user_name = Session::get('user_name') ?? Session::get('nombres') ?? Session::get('nombre') ?? 'Usuario';

// 2. Definir Rol (Convertir ID numérico a String para la vista V1)
$rol_id = Session::get('role_id') ?? Session::get('rol_id') ?? 0;
$role = 'Invitado'; // Default

switch ($rol_id) {
    case 1: $role = 'Administrador'; break;
    case 2: $role = 'Tutor'; break;
    case 3: $role = 'Pasante'; break;
    default: 
        // Fallback: Si viene el string directo en la sesión
        $role = Session::get('rol_nombre') ?? 'Invitado';
        break;
}
// === FIN ADAPTADOR ===
?>
<aside class="sidebar" style="padding-top: 10px;">
    <div class="sidebar-menu-wrapper">
        <nav>
            <div class="menu-label">Principal</div>
            <?php
            // Determinar URL del dashboard según rol
            $dashboardUrl = URLROOT . '/dashboard'; // Fallback
            if ($rol_id == 1) $dashboardUrl = URLROOT . '/admin';
            if ($rol_id == 2) $dashboardUrl = URLROOT . '/tutor';
            if ($rol_id == 3) $dashboardUrl = URLROOT . '/pasante';
            ?>
            <a href="<?= $dashboardUrl ?>" class="active"><i class="ti ti-home"></i> <span>Inicio</span></a>
            
            <?php if ($role == 'Administrador'): ?>
            <div class="menu-label">Gestión</div>
            <a href="<?= URLROOT ?>/users"><i class="ti ti-users"></i> <span>Usuarios</span></a>
            <a href="<?= URLROOT ?>/backup"><i class="ti ti-database"></i> <span>Respaldos</span></a>
            <a href="<?= URLROOT ?>/reports"><i class="ti ti-report"></i> <span>Reportes</span></a>
            <a href="<?= URLROOT ?>/settings"><i class="ti ti-settings"></i> <span>Configuración</span></a>
            <?php endif; ?>

            <?php if ($role == 'Tutor'): ?>
            <div class="menu-label">Supervisión</div>
            <a href="<?= URLROOT ?>/interns"><i class="ti ti-users-group"></i> <span>Mis Pasantes</span></a>
            <a href="<?= URLROOT ?>/evaluations"><i class="ti ti-clipboard-check"></i> <span>Evaluaciones</span></a>
            <?php endif; ?>

            <?php if ($role == 'Pasante'): ?>
            <div class="menu-label">Mi Pasantía</div>
            <a href="<?= URLROOT ?>/attendance"><i class="ti ti-clock"></i> <span>Asistencias</span></a>
            <a href="<?= URLROOT ?>/logbook"><i class="ti ti-file-text"></i> <span>Bitácora</span></a>
            <?php endif; ?>
        </nav>
    </div>
    
    <div class="sidebar-footer">
        <a href="<?= URLROOT ?>/auth/logout" class="btn-logout" onclick="event.preventDefault(); confirmLogout();" title="Cerrar Sesión">
            <i class="ti ti-power"></i>
            <span>Cerrar Sesión</span>
        </a>
    </div>
</aside>
