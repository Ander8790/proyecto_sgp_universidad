<?php
// Vista exclusiva para Tutores
$user_name = $data['user_name'] ?? 'Tutor';
$role = $data['role'] ?? 'Tutor';
?>

<div class="dashboard-bento">
    
    <!-- Banner de Bienvenida (Compacto) - Consistencia Global -->
    <div class="welcome-banner welcome-banner-compact slide-up">
        <div class="welcome-icon">
            <i class="ti ti-sparkles"></i>
        </div>
        
        <div class="welcome-content">
            <div class="welcome-text">
                <h1 class="welcome-title">¡Bienvenido de nuevo, <?= htmlspecialchars(explode(' ', $_SESSION['user_name'] ?? 'Usuario')[0]) ?>!</h1>
                <p class="welcome-subtitle">
                    <i class="ti ti-layout-dashboard"></i>
                    <span>Panel de Control</span>
                    <span class="subtitle-separator">-</span>
                    <span><?= $role ?></span>
                </p>
            </div>
        </div>
        
        <div class="welcome-meta">
            <div class="welcome-date">
                <i class="ti ti-calendar"></i>
                <span id="currentDate"></span>
            </div>
            <div class="welcome-time">
                <i class="ti ti-clock"></i>
                <span id="currentTime"></span>
            </div>
        </div>
    </div>
    <!-- Fila 1: 3 KPIs para Tutor -->
    <div class="card kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">Mis Pasantes</span>
            <div class="kpi-icon">
                <i class="ti ti-users-group"></i>
            </div>
        </div>
        <div class="kpi-value" data-kpi-value="<?= $totalPasantes ?? 0 ?>">0</div>
        <div class="kpi-badge success">
            <i class="ti ti-check"></i> <?= $pasantesActivos ?? 0 ?> activos
        </div>
    </div>

    <div class="card kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">Evaluaciones Pendientes</span>
            <div class="kpi-icon">
                <i class="ti ti-clipboard-check"></i>
            </div>
        </div>
        <div class="kpi-value" data-kpi-value="<?= $evaluacionesPendientes ?? 0 ?>">0</div>
        <?php if (($evaluacionesPendientes ?? 0) > 0): ?>
        <div class="kpi-badge warning">
            <i class="ti ti-alert-circle"></i> Pendientes
        </div>
        <?php endif; ?>
    </div>

    <div class="card kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">Reportes Este Mes</span>
            <div class="kpi-icon">
                <i class="ti ti-file-text"></i>
            </div>
        </div>
        <div class="kpi-value">12</div>
    </div>

    <div class="card kpi-card">
        <div class="kpi-header">
            <span class="kpi-label">Horas Supervisadas</span>
            <div class="kpi-icon">
                <i class="ti ti-clock"></i>
            </div>
        </div>
        <div class="kpi-value">960</div>
        <div class="kpi-badge info">
            <i class="ti ti-trending-up"></i> +120 este mes
        </div>
    </div>

    <!-- Fila 2: Tabla de Pasantes Asignados -->
    <div class="card span-4">
        <h3 style="color: var(--deep-azure); font-size: 1.125rem; font-weight: 600; margin-bottom: 16px;">Mis Pasantes Asignados</h3>
        <table class="modern-table">
            <thead>
                <tr>
                    <th>Pasante</th>
                    <th>Institución</th>
                    <th>Horas Completadas</th>
                    <th>Estado</th>
                </tr>
            </thead>
            <tbody>
                <?php if (!empty($misPasantes)): ?>
                    <?php foreach ($misPasantes as $p):
                        $nombre = trim(($p->nombres ?? '') . ' ' . ($p->apellidos ?? ''));
                        $iniciales = strtoupper(mb_substr($p->nombres ?? 'P', 0, 1) . mb_substr($p->apellidos ?? '', 0, 1));
                        $hCumpl = (int)($p->horas_acumuladas ?? 0);
                        $hMeta  = (int)($p->horas_meta ?? 480);
                        $badgeClass = ($p->estado_pasantia ?? '') === 'Activo' ? 'success' : (($p->estado_pasantia ?? '') === 'Finalizado' ? 'info' : 'warning');
                    ?>
                    <tr>
                        <td>
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <div class="avatar-circle" style="width: 32px; height: 32px; font-size: 0.75rem;"><?= htmlspecialchars($iniciales) ?></div>
                                <span><?= htmlspecialchars($nombre) ?></span>
                            </div>
                        </td>
                        <td style="color: var(--text-muted);"><?= htmlspecialchars($p->institucion ?? '—') ?></td>
                        <td style="color: var(--text-muted);"><?= $hCumpl ?>/<?= $hMeta ?> horas</td>
                        <td><span class="badge <?= $badgeClass ?>"><?= htmlspecialchars($p->estado_pasantia ?? 'N/A') ?></span></td>
                    </tr>
                    <?php endforeach; ?>
                <?php else: ?>
                    <tr>
                        <td colspan="4" style="text-align:center;padding:32px;color:#94a3b8;">
                            <i class="ti ti-users-off" style="font-size:32px;display:block;margin-bottom:8px;"></i>
                            No tienes pasantes asignados aún.
                        </td>
                    </tr>
                <?php endif; ?>
            </tbody>
        </table>
    </div>

</div>

<script>
function confirmLogout() {
    Swal.fire({
        title: '¿Cerrar Sesión?',
        text: "Estás a punto de salir del sistema",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#162660',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, salir'
    }).then((result) => {
        if (result.isConfirmed) window.location.href = '<?= URLROOT ?>/auth/logout';
    });
}
</script>
