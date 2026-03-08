<?php
// Calculate total size
$totalBytes = 0;
foreach ($backups as $backup) {
    $filepath = APPROOT . '/storage/backups/' . $backup['filename'];
    if (file_exists($filepath)) {
        $totalBytes += filesize($filepath);
    }
}

// Format total size (inline)
$units = ['B', 'KB', 'MB', 'GB'];
$bytes = max($totalBytes, 0);
$pow = floor(($bytes ? log($bytes) : 0) / log(1024));
$pow = min($pow, count($units) - 1);
$bytes /= pow(1024, $pow);
$totalSizeFormatted = round($bytes, 2) . ' ' . $units[$pow];
?>


<style>
    /* ==================== STATS CARDS ==================== */
    .stats-grid {
        display: grid;
        grid-template-columns: repeat(auto-fit, minmax(280px, 1fr));
        gap: 24px;
        margin-bottom: 32px;
    }

    .stat-card {
        background: white;
        border-radius: 16px;
        padding: 24px;
        display: flex;
        align-items: center;
        gap: 20px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        transition: all 0.3s cubic-bezier(0.4, 0, 0.2, 1);
        border: 1px solid #F3F4F6;
    }

    .stat-card:hover {
        transform: translateY(-4px);
        box-shadow: 0 8px 24px rgba(0,0,0,0.12);
    }

    .stat-icon {
        width: 64px;
        height: 64px;
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        color: white;
        font-size: 28px;
        flex-shrink: 0;
    }

    .stat-content {
        flex: 1;
    }

    .stat-content h3 {
        font-size: 2rem;
        font-weight: 700;
        color: #162660;
        margin: 0 0 4px 0;
        line-height: 1;
    }

    .stat-content p {
        color: #6B7280;
        margin: 0;
        font-size: 0.875rem;
        font-weight: 500;
    }

    /* ==================== TABLE CONTAINER ==================== */
    .table-container {
        background: white;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
        border: 1px solid #F3F4F6;
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container thead th {
        background: linear-gradient(135deg, #F9FAFB 0%, #F3F4F6 100%);
        color: #162660;
        font-weight: 600;
        padding: 16px;
        text-align: left;
        font-size: 14px;
        border-bottom: 2px solid #E5E7EB;
        text-transform: uppercase;
        letter-spacing: 0.5px;
    }

    .table-container tbody tr {
        border-bottom: 1px solid #F3F4F6;
        transition: all 0.2s ease;
    }

    .table-container tbody tr:hover {
        background: #F9FAFB;
        transform: scale(1.01);
    }

    .table-container tbody td {
        padding: 18px 16px;
        font-size: 14px;
        color: #374151;
    }

    /* ==================== ACTION BUTTONS ==================== */
    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 40px;
        height: 40px;
        border: none;
        border-radius: 10px;
        cursor: pointer;
        transition: all 0.2s cubic-bezier(0.4, 0, 0.2, 1);
        margin: 0 4px;
        font-size: 18px;
    }

    .btn-download {
        background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(59, 130, 246, 0.3);
    }

    .btn-download:hover {
        background: linear-gradient(135deg, #2563EB 0%, #1D4ED8 100%);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(59, 130, 246, 0.4);
    }

    .btn-restore {
        background: linear-gradient(135deg, #10B981 0%, #059669 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(16, 185, 129, 0.3);
    }

    .btn-restore:hover {
        background: linear-gradient(135deg, #059669 0%, #047857 100%);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(16, 185, 129, 0.4);
    }

    .btn-delete {
        background: linear-gradient(135deg, #EF4444 0%, #DC2626 100%);
        color: white;
        box-shadow: 0 2px 8px rgba(239, 68, 68, 0.3);
    }

    .btn-delete:hover {
        background: linear-gradient(135deg, #DC2626 0%, #B91C1C 100%);
        transform: translateY(-2px) scale(1.05);
        box-shadow: 0 4px 12px rgba(239, 68, 68, 0.4);
    }

    /* ==================== BANNER MODERNO (Estilo Usuario) ==================== */
    .welcome-banner {
        background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
        border-radius: 20px;
        padding: 32px 40px;
        margin-bottom: 32px;
        box-shadow: 0 10px 30px rgba(22, 38, 96, 0.15);
        display: flex;
        justify-content: space-between;
        align-items: center;
        color: white;
        position: relative;
        overflow: hidden;
    }
    
    /* Círculo decorativo */
    .welcome-banner::before {
        content: '';
        position: absolute;
        top: -50%;
        right: -10%;
        width: 400px;
        height: 400px;
        background: rgba(255, 255, 255, 0.05);
        border-radius: 50%;
        pointer-events: none;
        z-index: 0;
    }
    
    /* Franja decorativa lateral — oculta para diseño limpio con gradiente índigo */
    .welcome-banner::after {
        display: none;
    }
    
    .welcome-content {
        display: flex;
        align-items: center;
        gap: 20px;
        z-index: 2;
    }
    
    .welcome-icon {
        width: 60px;
        height: 60px;
        background: rgba(255, 255, 255, 0.15);
        border-radius: 16px;
        display: flex;
        align-items: center;
        justify-content: center;
        font-size: 28px;
        animation: pulse 2s ease-in-out infinite;
    }
    
    @keyframes pulse {
        0%, 100% { transform: scale(1); }
        50% { transform: scale(1.05); }
    }
    
    .welcome-text h1.welcome-title {
        font-size: 2rem;
        font-weight: 700;
        margin: 0 0 6px 0;
        color: white;
    }
    
    .welcome-text p.welcome-subtitle {
        font-size: 1.05rem;
        opacity: 0.9;
        margin: 0;
        display: flex;
        align-items: center;
        gap: 8px;
    }
    
    .subtitle-separator {
        opacity: 0.5;
        margin: 0 4px;
    }
    
    .welcome-meta {
        z-index: 2;
    }
    
    .welcome-stats {
        display: flex;
        align-items: center;
        gap: 8px;
        font-size: 1.1rem;
        font-weight: 600;
    }
    
    .welcome-stats i {
        font-size: 24px;
    }
    
    .btn-stitch-gold {
        background-color: #ffffff !important;
        color: #162660 !important;
        font-weight: 700;
        border: none;
        padding: 12px 24px;
        border-radius: 12px;
        display: flex;
        align-items: center;
        gap: 8px;
        box-shadow: 0 4px 6px -1px rgba(0, 0, 0, 0.2);
        transition: all 0.2s ease;
        cursor: pointer;
        z-index: 10; /* Por encima de todo */
    }
    
    .btn-stitch-gold:hover {
        background-color: #f8fafc !important;
        transform: translateY(-2px);
        box-shadow: 0 10px 15px -3px rgba(0, 0, 0, 0.3);
    }

    /* ==================== EMPTY STATE ==================== */
    .empty-state {
        text-align: center;
        padding: 60px 40px;
        color: #6B7280;
    }

    .empty-state i {
        font-size: 64px;
        display: block;
        margin-bottom: 16px;
        opacity: 0.3;
        color: #162660;
    }

    .empty-state p {
        font-size: 1.1rem;
        margin: 0;
    }

    /* ==================== FILE ICON ==================== */
    .file-icon {
        margin-right: 12px;
        color: #162660;
        font-size: 20px;
    }
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">
    <!-- BANNER MODERNO (Estilo Usuario) -->
    <div class="welcome-banner welcome-banner-compact mb-4">
        <div class="welcome-icon">
            <i class="ti ti-database"></i>
        </div>
        
        <div class="welcome-content">
            <div class="welcome-text">
                <h1 class="welcome-title">Respaldo de Base de Datos</h1>
                <p class="welcome-subtitle">
                    <i class="ti ti-shield-check"></i>
                    <span>Gestión de Respaldos</span>
                    <span class="subtitle-separator">-</span>
                    <span>Restauraciones</span>
                </p>
            </div>
        </div>
        
        <div class="welcome-meta">
            <div class="welcome-stats">
                <i class="ti ti-file-database"></i>
                <span><?= count($backups) ?></span> respaldos
            </div>
        </div>
        
        <button onclick="createBackup()" class="btn btn-stitch-gold shadow-sm" style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
            <i class="ti ti-plus"></i>
            Crear Nuevo Backup
        </button>
    </div>

    <!-- Flash Messages -->
    <?php if (Session::hasFlash('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10B981; padding: 16px; border-radius: 12px; margin-bottom: 24px;">
        <p style="color: #059669; margin: 0; font-weight: 500;">
            <i class="ti ti-circle-check"></i> <?= Session::getFlash('success') ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #EF4444; padding: 16px; border-radius: 12px; margin-bottom: 24px;">
        <p style="color: #DC2626; margin: 0; font-weight: 500;">
            <i class="ti ti-alert-circle"></i> <?= Session::getFlash('error') ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Statistics Cards -->
    <div class="stats-grid">
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #3B82F6 0%, #2563EB 100%);">
                <i class="ti ti-database"></i>
            </div>
            <div class="stat-content">
                <h3><?= count($backups) ?></h3>
                <p>Respaldos Totales</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #10B981 0%, #059669 100%);">
                <i class="ti ti-clock"></i>
            </div>
            <div class="stat-content">
                <h3><?= !empty($backups) ? date('d/m/Y', $backups[0]['timestamp']) : 'N/A' ?></h3>
                <p>Último Respaldo</p>
            </div>
        </div>
        
        <div class="stat-card">
            <div class="stat-icon" style="background: linear-gradient(135deg, #F59E0B 0%, #D97706 100%);">
                <i class="ti ti-file-database"></i>
            </div>
            <div class="stat-content">
                <h3><?= $totalSizeFormatted ?></h3>
                <p>Tamaño Total</p>
            </div>
        </div>
    </div>

    <!-- Backups Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th><i class="ti ti-file-text" style="margin-right: 8px;"></i>Nombre del Archivo</th>
                    <th><i class="ti ti-calendar" style="margin-right: 8px;"></i>Fecha de Creación</th>
                    <th><i class="ti ti-database-export" style="margin-right: 8px;"></i>Tamaño</th>
                    <th style="text-align: center;"><i class="ti ti-settings" style="margin-right: 8px;"></i>Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backups)): ?>
                <tr>
                    <td colspan="4" class="empty-state">
                        <i class="ti ti-database-off"></i>
                        <p>No hay respaldos disponibles. Crea tu primer backup para comenzar.</p>
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td>
                            <i class="ti ti-file-database file-icon"></i>
                            <strong><?= htmlspecialchars($backup['filename']) ?></strong>
                        </td>
                        <td><?= htmlspecialchars($backup['date']) ?></td>
                        <td><strong><?= htmlspecialchars($backup['size']) ?></strong></td>
                        <td style="text-align: center;">
                            <button onclick="downloadBackup('<?= htmlspecialchars($backup['filename']) ?>')" 
                                    class="btn-action btn-download" 
                                    title="Descargar respaldo">
                                <i class="ti ti-download"></i>
                            </button>
                            <button onclick="restoreBackup('<?= htmlspecialchars($backup['filename']) ?>')" 
                                    class="btn-action btn-restore" 
                                    title="Restaurar base de datos">
                                <i class="ti ti-refresh"></i>
                            </button>
                            <button onclick="deleteBackup('<?= htmlspecialchars($backup['filename']) ?>')" 
                                    class="btn-action btn-delete" 
                                    title="Eliminar respaldo">
                                <i class="ti ti-trash"></i>
                            </button>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                <?php endif; ?>
            </tbody>
        </table>
    </div>
</div>

<script src="<?= URLROOT ?>/js/notyf.min.js"></script>

<script>
    // Initialize Notyf
    const notyf = new Notyf({
        duration: 3000,
        position: { x: 'right', y: 'top' },
        ripple: true
    });
    
    // Create Backup
    function createBackup() {
        Swal.fire({
            title: '¿Crear Respaldo?',
            text: 'Se creará un respaldo completo de la base de datos',
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#162660',
            cancelButtonColor: '#6B7280',
            confirmButtonText: '<i class="ti ti-check"></i> Sí, crear respaldo',
            cancelButtonText: '<i class="ti ti-x"></i> Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                showLoading('Creando respaldo...');
                
                return fetch('<?= URLROOT ?>/backup/create', {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (!data.success) {
                        throw new Error(data.message);
                    }
                    return data;
                })
                .catch(error => {
                    hideLoading();
                    Swal.showValidationMessage(`Error: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Respaldo Creado!',
                    text: result.value.message,
                    confirmButtonColor: '#162660'
                }).then(() => {
                    location.reload();
                });
            }
        });
    }
    
    // Download Backup
    function downloadBackup(filename) {
        window.location.href = '<?= URLROOT ?>/backup/download/' + encodeURIComponent(filename);
    }
    
    // Restore Backup
    function restoreBackup(filename) {
        Swal.fire({
            title: '⚠️ ¿Restaurar Base de Datos?',
            html: `
                <div style="text-align: left; margin: 20px 0;">
                    <p style="margin-bottom: 16px; font-size: 1.05rem;">Esta acción sobrescribirá <strong>TODOS los datos actuales</strong> de la base de datos.</p>
                    <div style="background: #FEF3C7; border-left: 4px solid #F59E0B; padding: 16px; border-radius: 8px;">
                        <strong style="color: #D97706; display: block; margin-bottom: 8px;">⚠️ ADVERTENCIA CRÍTICA:</strong>
                        <ul style="margin: 0; padding-left: 20px; color: #92400E;">
                            <li>Se perderán todos los cambios recientes</li>
                            <li>Esta acción NO se puede deshacer</li>
                            <li>Se recomienda crear un respaldo antes</li>
                            <li>Todos los usuarios serán desconectados</li>
                        </ul>
                    </div>
                    <p style="margin-top: 16px; font-weight: 600; background: #F3F4F6; padding: 12px; border-radius: 8px;">
                        📁 Archivo: <code style="color: #162660;">${filename}</code>
                    </p>
                </div>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: '<i class="ti ti-refresh"></i> Sí, restaurar ahora',
            cancelButtonText: '<i class="ti ti-x"></i> Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                showLoading('Restaurando base de datos...');
                
                return fetch(`<?= URLROOT ?>/backup/restore/${encodeURIComponent(filename)}`, {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (!data.success) {
                        throw new Error(data.message);
                    }
                    return data;
                })
                .catch(error => {
                    hideLoading();
                    Swal.showValidationMessage(`Error: ${error}`);
                });
            },
            allowOutsideClick: () => !Swal.isLoading()
        }).then((result) => {
            if (result.isConfirmed) {
                Swal.fire({
                    icon: 'success',
                    title: '¡Base de Datos Restaurada!',
                    html: `
                        <p>${result.value.message}</p>
                        <div style="background: #DBEAFE; padding: 12px; border-radius: 8px; margin-top: 16px;">
                            <i class="ti ti-info-circle" style="color: #3B82F6;"></i>
                            <span style="color: #1E40AF;">La página se recargará automáticamente</span>
                        </div>
                    `,
                    confirmButtonColor: '#162660',
                    timer: 3000
                }).then(() => {
                    location.reload();
                });
            }
        });
    }
    
    // Delete Backup
    function deleteBackup(filename) {
        Swal.fire({
            title: '¿Eliminar Respaldo?',
            html: `
                <p>Esta acción no se puede deshacer</p>
                <p style="margin-top: 12px; background: #F3F4F6; padding: 12px; border-radius: 8px;">
                    <strong>Archivo:</strong> <code>${filename}</code>
                </p>
            `,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: '<i class="ti ti-trash"></i> Sí, eliminar',
            cancelButtonText: '<i class="ti ti-x"></i> Cancelar'
        }).then((result) => {
            if (result.isConfirmed) {
                showLoading('Eliminando respaldo...');
                
                fetch('<?= URLROOT ?>/backup/delete/' + encodeURIComponent(filename), {
                    method: 'POST',
                    headers: {
                        'X-Requested-With': 'XMLHttpRequest'
                    }
                })
                .then(response => response.json())
                .then(data => {
                    hideLoading();
                    if (data.success) {
                        NotificationService.success(data.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        NotificationService.error(data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    NotificationService.error('Error al eliminar el respaldo');
                });
            }
        });
    }
</script>
