<style>
    .table-container {
        background: white;
        border-radius: 12px;
        padding: 24px;
        box-shadow: 0 2px 8px rgba(0,0,0,0.05);
    }

    .table-container table {
        width: 100%;
        border-collapse: collapse;
    }

    .table-container thead th {
        background: #F9FAFB;
        color: var(--color-primary);
        font-weight: 600;
        padding: 12px 16px;
        text-align: left;
        font-size: 14px;
        border-bottom: 2px solid #E5E7EB;
    }

    .table-container tbody tr {
        border-bottom: 1px solid #F3F4F6;
        transition: background 0.2s;
    }

    .table-container tbody tr:hover {
        background: #F9FAFB;
    }

    .table-container tbody td {
        padding: 16px;
        font-size: 14px;
        color: #374151;
    }

    .btn-action {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 36px;
        height: 36px;
        border: none;
        border-radius: 8px;
        cursor: pointer;
        transition: all 0.2s;
        margin: 0 4px;
    }

    .btn-edit {
        background: #3B82F6;
        color: white;
    }

    .btn-edit:hover {
        background: #2563EB;
        transform: translateY(-2px);
    }

    .btn-delete {
        background: #EF4444;
        color: white;
    }

    .btn-delete:hover {
        background: #DC2626;
        transform: translateY(-2px);
    }
</style>

<div class="dashboard-container">
    <!-- Header -->
    <div style="display: flex; justify-content: space-between; align-items: center; margin-bottom: 32px;">
        <div>
            <h1 style="color: var(--color-primary); font-size: 1.75rem; font-weight: 700; margin: 0 0 8px 0;">
                <i class="ti ti-database"></i> Respaldo de Base de Datos
            </h1>
            <p style="color: var(--text-body); margin: 0;">Gestiona los respaldos de la base de datos del sistema</p>
        </div>
        <button onclick="createBackup()" class="btn-primary">
            <i class="ti ti-plus"></i> Crear Nuevo Backup
        </button>
    </div>

    <!-- Flash Messages -->
    <?php if (Session::hasFlash('success')): ?>
    <div style="background: rgba(16, 185, 129, 0.1); border-left: 4px solid #10B981; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
        <p style="color: #059669; margin: 0; font-weight: 500;">
            <i class="ti ti-circle-check"></i> <?= Session::getFlash('success') ?>
        </p>
    </div>
    <?php endif; ?>

    <?php if (Session::hasFlash('error')): ?>
    <div style="background: rgba(239, 68, 68, 0.1); border-left: 4px solid #EF4444; padding: 16px; border-radius: 8px; margin-bottom: 24px;">
        <p style="color: #DC2626; margin: 0; font-weight: 500;">
            <i class="ti ti-alert-circle"></i> <?= Session::getFlash('error') ?>
        </p>
    </div>
    <?php endif; ?>

    <!-- Backups Table -->
    <div class="table-container">
        <table>
            <thead>
                <tr>
                    <th>Nombre del Archivo</th>
                    <th>Fecha de Creación</th>
                    <th>Tamaño</th>
                    <th style="text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($backups)): ?>
                <tr>
                    <td colspan="4" style="text-align: center; padding: 40px; color: var(--text-body);">
                        <i class="ti ti-database-off" style="font-size: 48px; display: block; margin-bottom: 16px; opacity: 0.3;"></i>
                        No hay respaldos disponibles. Crea tu primer backup.
                    </td>
                </tr>
                <?php else: ?>
                    <?php foreach ($backups as $backup): ?>
                    <tr>
                        <td>
                            <i class="ti ti-file-database" style="margin-right: 8px; color: var(--color-primary);"></i>
                            <?= htmlspecialchars($backup['filename']) ?>
                        </td>
                        <td><?= htmlspecialchars($backup['date']) ?></td>
                        <td><?= htmlspecialchars($backup['size']) ?></td>
                        <td style="text-align: center;">
                            <a href="<?= URLROOT ?>/backup/download/<?= urlencode($backup['filename']) ?>" 
                               class="btn-action btn-edit" 
                               title="Descargar">
                                <i class="ti ti-download"></i>
                            </a>
                            <button onclick="deleteBackup('<?= htmlspecialchars($backup['filename']) ?>')" 
                                    class="btn-action btn-delete" 
                                    title="Eliminar">
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
            confirmButtonText: 'Sí, crear respaldo',
            cancelButtonText: 'Cancelar',
            showLoaderOnConfirm: true,
            preConfirm: () => {
                // Show loading overlay
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
    
    // Delete Backup
    function deleteBackup(filename) {
        Swal.fire({
            title: '¿Eliminar Respaldo?',
            text: 'Esta acción no se puede deshacer',
            icon: 'warning',
            showCancelButton: true,
            confirmButtonColor: '#EF4444',
            cancelButtonColor: '#6B7280',
            confirmButtonText: 'Sí, eliminar',
            cancelButtonText: 'Cancelar'
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
                        notyf.success(data.message);
                        setTimeout(() => location.reload(), 1500);
                    } else {
                        notyf.error(data.message);
                    }
                })
                .catch(error => {
                    hideLoading();
                    notyf.error('Error al eliminar el respaldo');
                });
            }
        });
    }
</script>
