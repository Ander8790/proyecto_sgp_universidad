<?php
/**
 * Vista: Bitácora de Auditoría
 * Descripción: Interfaz moderna para visualizar logs del sistema
 * Layout: main_layout.php (con sidebar y header)
 */
?>

<!-- Link al CSS específico de bitácora -->
<link rel="stylesheet" href="<?= URLROOT ?>/css/bitacora.css">

<div class="dashboard-container">
    <!-- BANNER MODERNO (Estilo Usuario/Backup) -->
    <div class="welcome-banner">
        <div class="welcome-icon">
            <i class="ti ti-file-analytics"></i>
        </div>
        
        <div class="welcome-content">
            <div class="welcome-text">
                <h1 class="welcome-title">Bitácora de Auditoría</h1>
                <p class="welcome-subtitle">
                    <i class="ti ti-shield-lock"></i>
                    <span>Registro de Actividades</span>
                    <span class="subtitle-separator">-</span>
                    <span>Sistema de Auditoría</span>
                </p>
            </div>
        </div>
        
        <div class="welcome-meta">
            <div class="welcome-stats">
                <i class="ti ti-database"></i>
                <span><?= number_format($total_logs) ?></span> registros
            </div>
        </div>
        
        <button onclick="exportLogs()" class="btn btn-stitch-gold" 
                style="position: absolute; right: 20px; top: 50%; transform: translateY(-50%);">
            <i class="ti ti-file-download"></i>
            Exportar CSV
        </button>
    </div>
    
    <!-- Filtros -->
    <div class="filters-card">
        <div class="row g-3 align-items-end">
            <div class="col-md-4">
                <label style="font-weight: 600; color: var(--color-primary); margin-bottom: 8px; display: block;">
                    <i class="ti ti-activity"></i> Acción
                </label>
                <select id="filterAction" class="filter-select" style="width: 100%;">
                    <option value="">Todas las acciones</option>
                    <?php foreach ($actions as $action): ?>
                        <option value="<?= htmlspecialchars($action) ?>">
                            <?= htmlspecialchars($action) ?>
                        </option>
                    <?php endforeach; ?>
                </select>
            </div>
            
            <div class="col-md-3">
                <label style="font-weight: 600; color: var(--color-primary); margin-bottom: 8px; display: block;">
                    <i class="ti ti-calendar"></i> Desde
                </label>
                <input type="date" id="filterDateFrom" class="filter-select" style="width: 100%;">
            </div>
            
            <div class="col-md-3">
                <label style="font-weight: 600; color: var(--color-primary); margin-bottom: 8px; display: block;">
                    <i class="ti ti-calendar"></i> Hasta
                </label>
                <input type="date" id="filterDateTo" class="filter-select" style="width: 100%;">
            </div>
            
            <div class="col-md-2 d-flex gap-2">
                <button onclick="applyFilters()" class="btn-export" style="flex: 1;">
                    <i class="ti ti-filter"></i> Filtrar
                </button>
                <button onclick="clearFilters()" class="btn-clear">
                    <i class="ti ti-filter-off"></i>
                </button>
            </div>
        </div>
    </div>
    
    <!-- Tabla de Logs -->
    <div class="table-card">
        <div class="table-responsive">
            <table id="logsTable" class="table table-hover align-middle mb-0" style="width:100%">
                <thead>
                    <tr>
                        <th class="px-4 py-3"><i class="ti ti-hash" style="margin-right: 8px;"></i>ID</th>
                        <th class="px-4 py-3"><i class="ti ti-user" style="margin-right: 8px;"></i>Usuario</th>
                        <th class="px-4 py-3"><i class="ti ti-activity" style="margin-right: 8px;"></i>Acción</th>
                        <th class="px-4 py-3"><i class="ti ti-table" style="margin-right: 8px;"></i>Tabla</th>
                        <th class="px-4 py-3"><i class="ti ti-world" style="margin-right: 8px;"></i>IP</th>
                        <th class="px-4 py-3"><i class="ti ti-calendar" style="margin-right: 8px;"></i>Fecha</th>
                        <th class="px-4 py-3 text-center"><i class="ti ti-info-circle" style="margin-right: 8px;"></i>Detalles</th>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($logs as $log): ?>
                    <tr>
                        <td class="px-4 py-3"><?= $log->id ?></td>
                        <td class="px-4 py-3">
                            <div class="fw-medium text-dark">
                                <?= htmlspecialchars($log->usuario_nombre ?: 'Sistema') ?>
                            </div>
                            <div class="small text-muted">
                                <?= htmlspecialchars($log->usuario_email ?: 'N/A') ?>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="badge-action badge-<?= $log->accion ?>">
                                <?= htmlspecialchars($log->accion) ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-muted">
                                <?= htmlspecialchars($log->tabla_afectada ?: '-') ?>
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <code style="font-size: 0.85rem; color: #6B7280;">
                                <?= htmlspecialchars($log->ip_address) ?>
                            </code>
                        </td>
                        <td class="px-4 py-3">
                            <div class="small">
                                <?= date('d/m/Y', strtotime($log->created_at)) ?>
                            </div>
                            <div class="small text-muted">
                                <?= date('H:i:s', strtotime($log->created_at)) ?>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-center">
                            <?php if ($log->detalles): ?>
                                <button onclick='showDetails(<?= json_encode($log->detalles) ?>)' class="btn-details">
                                    <i class="ti ti-eye"></i> Ver
                                </button>
                            <?php else: ?>
                                <span class="text-muted">-</span>
                            <?php endif; ?>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>
</div>

<!-- Modal de Detalles -->
<div id="detailsModal" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <h2 class="modal-title">
                <i class="ti ti-info-circle"></i> Detalles del Registro
            </h2>
            <button class="modal-close" onclick="closeDetailsModal()">
                <i class="ti ti-x"></i>
            </button>
        </div>
        <div id="detailsContent" class="json-viewer"></div>
    </div>
</div>

<!-- DataTables CSS & JS -->
<link rel="stylesheet" href="<?= URLROOT ?>/css/dataTables.dataTables.min.css">
<script src="<?= URLROOT ?>/js/jquery.min.js"></script>
<script src="<?= URLROOT ?>/js/dataTables.min.js"></script>

<script>
// Inicializar DataTable
let table;

$(document).ready(function() {
    table = $('#logsTable').DataTable({
        language: {
            url: '<?= URLROOT ?>/js/i18n/es-ES.json'
        },
        order: [[0, 'desc']], // Ordenar por ID descendente
        pageLength: 25,
        responsive: true
    });
});

// Aplicar Filtros (AJAX)
function applyFilters() {
    const action = document.getElementById('filterAction').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;
    
    // Mostrar skeleton loading
    showLoading();
    
    fetch('<?= URLROOT ?>/bitacora/filter', {
        method: 'POST',
        headers: {
            'Content-Type': 'application/x-www-form-urlencoded',
        },
        body: new URLSearchParams({
            accion: action,
            fecha_desde: dateFrom,
            fecha_hasta: dateTo
        })
    })
    .then(response => response.json())
    .then(data => {
        if (data.success) {
            // Limpiar tabla
            table.clear();
            
            // Agregar nuevos datos
            data.data.forEach(log => {
                table.row.add([
                    log.id,
                    `<div class="fw-medium text-dark">${log.usuario_nombre || 'Sistema'}</div>
                     <div class="small text-muted">${log.usuario_email || 'N/A'}</div>`,
                    `<span class="badge-action badge-${log.accion}">${log.accion}</span>`,
                    `<span class="text-muted">${log.tabla_afectada || '-'}</span>`,
                    `<code style="font-size: 0.85rem; color: #6B7280;">${log.ip_address}</code>`,
                    `<div class="small">${formatDate(log.created_at)}</div>
                     <div class="small text-muted">${formatTime(log.created_at)}</div>`,
                    log.detalles 
                        ? `<button onclick='showDetails(${JSON.stringify(log.detalles)})' class="btn-details">
                               <i class="ti ti-eye"></i> Ver
                           </button>`
                        : '<span class="text-muted">-</span>'
                ]);
            });
            
            table.draw();
            hideLoading();
            
            if (typeof NotificationService !== 'undefined') {
                NotificationService.success(`${data.count} registros encontrados`);
            }
        } else {
            hideLoading();
            if (typeof NotificationService !== 'undefined') {
                NotificationService.error('Error al filtrar registros');
            }
        }
    })
    .catch(error => {
        console.error('Error:', error);
        hideLoading();
        if (typeof NotificationService !== 'undefined') {
            NotificationService.error('Error de conexión');
        }
    });
}

// Limpiar Filtros
function clearFilters() {
    document.getElementById('filterAction').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    
    // Recargar página
    location.reload();
}

// Exportar a CSV
function exportLogs() {
    const action = document.getElementById('filterAction').value;
    const dateFrom = document.getElementById('filterDateFrom').value;
    const dateTo = document.getElementById('filterDateTo').value;
    
    const params = new URLSearchParams({
        accion: action,
        fecha_desde: dateFrom,
        fecha_hasta: dateTo
    });
    
    window.location.href = `<?= URLROOT ?>/bitacora/export?${params.toString()}`;
}

// Mostrar Modal de Detalles
function showDetails(details) {
    const modal = document.getElementById('detailsModal');
    const content = document.getElementById('detailsContent');
    
    // Formatear JSON
    const formatted = JSON.stringify(JSON.parse(details), null, 2);
    content.textContent = formatted;
    
    modal.classList.add('active');
}

// Cerrar Modal
function closeDetailsModal() {
    document.getElementById('detailsModal').classList.remove('active');
}

// Cerrar modal al hacer clic fuera
document.getElementById('detailsModal').addEventListener('click', function(e) {
    if (e.target === this) {
        closeDetailsModal();
    }
});

// Helpers
function formatDate(datetime) {
    const date = new Date(datetime);
    return date.toLocaleDateString('es-ES');
}

function formatTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleTimeString('es-ES');
}
</script>
