<?php
/**
 * Vista: Bitácora de Auditoría
 * Descripción: Interfaz moderna para visualizar logs del sistema
 * Layout: main_layout.php (con sidebar y header)
 */
?>

<!-- Link al CSS específico de bitácora -->
<link rel="stylesheet" href="<?= URLROOT ?>/css/bitacora.css">

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">
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
                <select id="filterAction" class="filter-select no-choices" style="width: 100%;">
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
                <input type="text" id="filterDateFrom" class="filter-select no-choices" autocomplete="off" placeholder="dd/mm/aaaa" style="width: 100%;">
            </div>
            
            <div class="col-md-3">
                <label style="font-weight: 600; color: var(--color-primary); margin-bottom: 8px; display: block;">
                    <i class="ti ti-calendar"></i> Hasta
                </label>
                <input type="text" id="filterDateTo" class="filter-select no-choices" autocomplete="off" placeholder="dd/mm/aaaa" style="width: 100%;">
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
        <div id="bitacora-grid"></div>
    </div>
</div>

<!-- ══════════════════════════════════════════════════════
     MODAL PREMIUM: Detalle de Registro de Auditoría
     ══════════════════════════════════════════════════════ -->
<div id="auditModal" class="modal">
    <div class="modal-content" style="max-width: 680px;">
        <!-- Header con gradiente -->
        <div class="modal-header" style="background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); margin: -36px -36px 28px -36px; padding: 24px 32px; border-radius: 20px 20px 0 0;">
            <div style="display:flex; align-items:center; gap:14px;">
                <div style="width:44px;height:44px;background:rgba(255,255,255,0.15);border-radius:12px;display:flex;align-items:center;justify-content:center;font-size:1.4rem;color:white;">
                    <i class="ti ti-shield-check"></i>
                </div>
                <div>
                    <h2 style="font-size:1.2rem;font-weight:700;color:white;margin:0;">Detalle de Auditoría</h2>
                    <p style="color:rgba(255,255,255,0.7);font-size:0.82rem;margin:3px 0 0;">Registro de actividad del sistema</p>
                </div>
            </div>
            <button class="modal-close" onclick="closeAuditModal()" style="background:rgba(255,255,255,0.15);color:white;border:none;">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <!-- Sección: Info del Usuario -->
        <div id="auditUserSection" style="display:flex;align-items:center;gap:16px;padding:18px;background:#f8fafc;border-radius:14px;margin-bottom:20px;">
            <div id="auditAvatar" style="width:52px;height:52px;border-radius:50%;background:linear-gradient(135deg,#1e3a8a,#3b82f6);display:flex;align-items:center;justify-content:center;font-weight:800;color:white;font-size:1.2rem;flex-shrink:0;box-shadow:0 4px 12px rgba(30,58,138,0.25);">?</div>
            <div style="flex:1;">
                <div id="auditUserName" style="font-weight:700;color:#1e293b;font-size:1rem;"></div>
                <div id="auditUserEmail" style="color:#94a3b8;font-size:0.82rem;margin-top:2px;"></div>
            </div>
            <div id="auditBadge" class="badge-action"></div>
        </div>

        <!-- Grid de metadata -->
        <div style="display:grid;grid-template-columns:1fr 1fr 1fr;gap:12px;margin-bottom:20px;">
            <div style="background:#f8fafc;border-radius:12px;padding:14px;">
                <div style="color:#94a3b8;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;"><i class="ti ti-table"></i> Tabla</div>
                <div id="auditTabla" style="font-weight:600;color:#1e293b;font-size:0.88rem;font-family:monospace;"></div>
            </div>
            <div style="background:#f8fafc;border-radius:12px;padding:14px;">
                <div style="color:#94a3b8;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;"><i class="ti ti-world"></i> IP</div>
                <div id="auditIp" style="font-weight:600;color:#1e293b;font-size:0.88rem;font-family:monospace;"></div>
            </div>
            <div style="background:#f8fafc;border-radius:12px;padding:14px;">
                <div style="color:#94a3b8;font-size:0.72rem;font-weight:700;text-transform:uppercase;letter-spacing:0.6px;margin-bottom:6px;"><i class="ti ti-calendar"></i> Fecha</div>
                <div id="auditFecha" style="font-weight:600;color:#1e293b;font-size:0.84rem;"></div>
            </div>
        </div>

        <!-- ID del registro -->
        <div style="display:flex;align-items:center;gap:8px;margin-bottom:14px;">
            <span style="font-size:0.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;">Registro ID:</span>
            <code id="auditRecordId" style="background:#eff6ff;color:#2563eb;padding:3px 10px;border-radius:6px;font-size:0.82rem;font-weight:700;"></code>
        </div>

        <!-- Detalles JSON (si existen) -->
        <div id="auditJsonSection" style="display:none;">
            <div style="font-size:0.8rem;font-weight:700;color:#64748b;text-transform:uppercase;letter-spacing:0.5px;margin-bottom:10px;"><i class="ti ti-braces"></i> Datos del Cambio</div>
            <div id="auditJsonContent" class="json-viewer" style="max-height:200px;background:#0f172a;color:#e2e8f0;border-radius:12px;padding:16px;font-size:0.8rem;line-height:1.6;"></div>
        </div>

        <!-- Acciones del modal -->
        <div style="display:flex;justify-content:flex-end;gap:10px;margin-top:24px;padding-top:20px;border-top:1px solid #f1f5f9;">
            <button onclick="closeAuditModal()" style="padding:10px 20px;border:1.5px solid #e2e8f0;background:white;border-radius:10px;color:#64748b;font-weight:600;font-size:0.88rem;cursor:pointer;transition:all 0.15s;">
                <i class="ti ti-x"></i> Cerrar
            </button>
            <button id="auditPrintBtn" onclick="printAuditModal()" style="padding:10px 20px;background:linear-gradient(135deg,#dc2626,#b91c1c);border:none;border-radius:10px;color:white;font-weight:600;font-size:0.88rem;cursor:pointer;transition:all 0.15s;box-shadow:0 4px 12px rgba(220,38,38,0.25);display:flex;align-items:center;gap:8px;">
                <i class="ti ti-file-type-pdf"></i> Exportar PDF
            </button>
        </div>
    </div>
</div>

<!-- Grid.js CSS & JS locales (estilos en bitacora.css) -->
<link rel="stylesheet" href="<?= URLROOT ?>/css/gridjs-mermaid.min.css">
<script src="<?= URLROOT ?>/js/gridjs.umd.js"></script>

<script>
let grid;

document.addEventListener('DOMContentLoaded', () => {
    grid = new gridjs.Grid({
        columns: [
            { id: 'id', name: 'ID', width: '70px',
                formatter: (cell) => gridjs.html(`<span style="color:#94a3b8; font-size:0.8rem; font-weight:600;">#${cell}</span>`)
            },
            {
                id: 'usuario',
                name: 'Usuario',
                formatter: (cell, row) => {
                    const nombre  = row.cells[1].data.nombre;
                    const email   = row.cells[1].data.email;
                    const initial = nombre.charAt(0).toUpperCase();
                    // Usar colores distintos basados en inicial para diversidad visual
                    const colors = {
                        A:'#1e3a8a,#3b82f6', B:'#5b21b6,#8b5cf6', C:'#065f46,#10b981',
                        D:'#92400e,#f59e0b', E:'#7c3aed,#a78bfa', F:'#b91c1c,#f87171',
                        G:'#0e7490,#22d3ee', H:'#1e3a8a,#60a5fa', default:'#1e3a8a,#3b82f6'
                    };
                    const grad = colors[initial] || colors.default;
                    return gridjs.html(`
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div class="gj-avatar" style="background:linear-gradient(135deg,${grad});">${initial}</div>
                            <div>
                                <div style="font-weight:600;color:#1e293b;font-size:0.88rem;">${nombre}</div>
                                <div style="font-size:0.76rem;color:#94a3b8;margin-top:1px;">${email}</div>
                            </div>
                        </div>
                    `);
                }
            },
            {
                id: 'accion',
                name: 'Acción',
                formatter: (cell) => {
                    // Mapeo de acción a clase CSS existente en bitacora.css
                    const cssClass = `badge-action badge-${cell}`;
                    return gridjs.html(`<span class="${cssClass}">${cell}</span>`);
                }
            },
            {
                id: 'tabla_afectada',
                name: 'Tabla',
                formatter: (cell) => gridjs.html(`
                    <span style="font-size:0.82rem;color:#64748b;background:#f8fafc;padding:4px 10px;border-radius:6px;font-family:monospace;">${cell || '—'}</span>
                `)
            },
            {
                id: 'ip_address',
                name: 'Red / IP',
                formatter: (cell) => gridjs.html(`
                    <code style="background:#f1f5f9;padding:5px 10px;border-radius:6px;color:#475569;font-size:0.78rem;letter-spacing:0.3px;">${cell}</code>
                `)
            },
            {
                id: 'fecha',
                name: 'Fecha',
                formatter: (cell) => {
                    if (!cell) return gridjs.html('<span style="color:#cbd5e1">—</span>');
                    const date = new Date(cell);
                    const d = date.toLocaleDateString('es-ES', {day: '2-digit', month: 'short', year: 'numeric'});
                    const t = date.toLocaleTimeString('es-ES', {hour: '2-digit', minute: '2-digit'});
                    return gridjs.html(`
                        <div style="font-size:0.87rem;color:#334155;font-weight:600;">${d}</div>
                        <div style="font-size:0.74rem;color:#94a3b8;margin-top:2px;">${t}</div>
                    `);
                }
            },
            {
                id: 'detalles',
                name: 'Acciones',
                sort: false,
                width: '110px',
                formatter: (cell) => {
                    if (!cell) return gridjs.html('<span style="color:#e2e8f0;">—</span>');
                    // Escapar el JSON del log completo para el atributo onclick
                    const safe = cell.replace(/\\/g, '\\\\').replace(/'/g, "\\'").replace(/"/g, '&quot;');
                    let logObj;
                    try { logObj = JSON.parse(cell); } catch(e) { logObj = {}; }
                    const logId = logObj.id || '';
                    return gridjs.html(`
                        <div style="display:flex;align-items:center;gap:6px;">
                            <button class="gj-eye-btn" onclick="openAuditModal('${safe}')" title="Ver detalle completo">
                                <i class="ti ti-eye"></i>
                            </button>
                            <button class="gj-pdf-btn" onclick="exportRowPdf(${logId})" title="Exportar como PDF">
                                <i class="ti ti-file-type-pdf"></i>
                            </button>
                        </div>
                    `);
                }
            }
        ],
        pagination: {
            enabled: true,
            limit: 10,
            server: {
                // prev = el string estático de server.url, que Grid.js pasa correctamente aquí
                url: (prev, page, limit) => `${prev}&limit=${limit}&offset=${page * limit}`
            }
        },
        search: false,
        sort: true,
        language: {
            'search': { placeholder: 'Buscar...' },
            'pagination': {
                previous: 'Anterior',
                next: 'Siguiente',
                showing: 'Mostrando',
                results: () => 'registros'
            },
            loading: 'Cargando...',
            noRecordsFound: 'No se encontraron registros',
            error: 'Ocurrió un error obteniendo los datos'
        },
        server: {
            // IMPORTANTE: server.url DEBE ser un string estático en esta versión de Grid.js.
            // Si es una función, Grid.js la convierte a string y la usa como URL (bug conocido).
            url: `<?= URLROOT ?>/bitacora/apiGrid?accion=&fecha_desde=&fecha_hasta=`,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            then: (data) => {
                if (!data || !Array.isArray(data.data)) return [];
                return data.data.map(log => [
                    log.id,
                    { nombre: log.usuario_nombre || 'Sistema', email: log.usuario_email || 'N/A' },
                    log.accion,
                    log.tabla_afectada || '-',
                    log.ip_address,
                    log.created_at,
                    // Pasamos el objeto completo del log como JSON para los botones de acción
                    JSON.stringify(log)
                ]);
            },
            total: data => data.total || 0
        }
    }).render(document.getElementById('bitacora-grid'));
});

// Aplicar Filtros — reconstruimos la URL con los valores de los filtros y actualizamos el grid
function applyFilters() {
    const action = encodeURIComponent(document.getElementById('filterAction').value || '');
    const dfrom  = encodeURIComponent(document.getElementById('filterDateFrom').value || '');
    const dto    = encodeURIComponent(document.getElementById('filterDateTo').value || '');
    
    const newUrl = `<?= URLROOT ?>/bitacora/apiGrid?accion=${action}&fecha_desde=${dfrom}&fecha_hasta=${dto}`;
    
    grid.updateConfig({
        server: {
            url: newUrl,
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            then: (data) => {
                if (!data || !Array.isArray(data.data)) return [];
                return data.data.map(log => [
                    log.id,
                    { nombre: log.usuario_nombre || 'Sistema', email: log.usuario_email || 'N/A' },
                    log.accion,
                    log.tabla_afectada || '-',
                    log.ip_address,
                    log.created_at,
                    log.detalles
                ]);
            },
            total: data => data.total || 0
        },
        pagination: {
            enabled: true,
            limit: 10,
            server: {
                url: (prev, page, limit) => `${prev}&limit=${limit}&offset=${page * limit}`
            }
        }
    }).forceRender();
}

// Limpiar Filtros
function clearFilters() {
    document.getElementById('filterAction').value = '';
    document.getElementById('filterDateFrom').value = '';
    document.getElementById('filterDateTo').value = '';
    applyFilters();
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

// ── Modal Premium de Auditoría ────────────────────────
function openAuditModal(rawJson) {
    let log;
    try {
        // rawJson viene con &quot; en lugar de " por el HTML escaping
        const decoded = rawJson.replace(/&quot;/g, '"').replace(/&#39;|&apos;/g, "'");
        log = JSON.parse(decoded);
    } catch(e) {
        console.error('Error al parsear log:', e);
        return;
    }

    // ── Avatar con inicial e inicial colorida ──
    const nombre = log.usuario_nombre || 'Sistema';
    const email  = log.usuario_email  || 'N/A';
    const initial = nombre.charAt(0).toUpperCase();
    const colors = {
        A:'#1e3a8a,#3b82f6', B:'#5b21b6,#8b5cf6', C:'#065f46,#10b981',
        D:'#92400e,#f59e0b', E:'#7c3aed,#a78bfa', F:'#b91c1c,#f87171',
        G:'#0e7490,#22d3ee', default:'#1e3a8a,#3b82f6'
    };
    const grad = colors[initial] || colors.default;

    document.getElementById('auditAvatar').textContent    = initial;
    document.getElementById('auditAvatar').style.background = `linear-gradient(135deg,${grad})`;
    document.getElementById('auditUserName').textContent  = nombre;
    document.getElementById('auditUserEmail').textContent = email;

    // ── Badge de acción ──
    const badge = document.getElementById('auditBadge');
    badge.textContent  = log.accion || '—';
    badge.className    = `badge-action badge-${log.accion}`;

    // ── Metadata ──
    document.getElementById('auditTabla').textContent    = log.tabla_afectada || '—';
    document.getElementById('auditIp').textContent       = log.ip_address || '—';
    document.getElementById('auditRecordId').textContent = `#${log.id || '—'}`;

    if (log.created_at) {
        const d = new Date(log.created_at);
        document.getElementById('auditFecha').textContent =
            d.toLocaleDateString('es-ES', {weekday:'short', day:'2-digit', month:'long', year:'numeric'})
            + ' ' + d.toLocaleTimeString('es-ES', {hour:'2-digit', minute:'2-digit'});
    }

    // ── JSON de detalles (si existen) ──
    const jsonSection = document.getElementById('auditJsonSection');
    if (log.detalles) {
        try {
            const pretty = JSON.stringify(JSON.parse(log.detalles), null, 2);
            document.getElementById('auditJsonContent').textContent = pretty;
        } catch {
            document.getElementById('auditJsonContent').textContent = log.detalles;
        }
        jsonSection.style.display = 'block';
    } else {
        jsonSection.style.display = 'none';
    }

    // Guardar el log actual para el PDF
    document.getElementById('auditModal').dataset.logId = log.id || '';

    document.getElementById('auditModal').classList.add('active');
}

function closeAuditModal() {
    document.getElementById('auditModal').classList.remove('active');
}

// Cerrar al click en overlay
document.getElementById('auditModal').addEventListener('click', function(e) {
    if (e.target === this) closeAuditModal();
});

// Imprimir / PDF desde el modal (Print Dialog del navegador)
function printAuditModal() {
    window.print();
}

// Exportar PDF individual de una fila (endpoint server-side)
function exportRowPdf(logId) {
    if (!logId) return;
    window.open(`<?= URLROOT ?>/bitacora/exportPdfRow/${logId}`, '_blank');
}


// Helpers
function formatDate(datetime) {
    const date = new Date(datetime);
    return date.toLocaleDateString('es-ES');
}

function formatTime(datetime) {
    const date = new Date(datetime);
    return date.toLocaleTimeString('es-ES');
}
// ── Flatpickr para fechas del filtro ────────────────
const fpConfig = {
    locale: 'es',
    dateFormat: 'Y-m-d',          // Lo que readeas con .value (para el backend)
    altInput: true,                // Muestra un input formateado al usuario
    altFormat: 'd/m/Y',           // Formato visual: 05/03/2026
    allowInput: true,
    monthSelectorType: 'dropdown', // Selector de mes como dropdown (no flechas)
    disableMobile: true,           // Forzar siempre el calendario de Flatpickr
    onReady: function(_, __, fp) {
        // Aplicar clase no-choices al altInput generado para que Choices no lo toque
        if (fp.altInput) {
            fp.altInput.classList.add('no-choices');
            fp.altInput.setAttribute('autocomplete', 'off');
        }
    }
};

// Necesitamos que flatpickr esté disponible (cargado en main_layout.php)
if (typeof flatpickr !== 'undefined') {
    flatpickr('#filterDateFrom', fpConfig);
    flatpickr('#filterDateTo', fpConfig);
}
</script>
