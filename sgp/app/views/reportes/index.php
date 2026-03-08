<?php
/**
 * Vista: Informes y Reportes — Generación real con DomPDF + TCPDF
 */
$departamentos = $data['departamentos'] ?? [];
?>

<style>
.rep-card {
    background: white;
    border-radius: 18px;
    padding: 28px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
}
.rep-label {
    font-size: 0.82rem;
    color: #64748b;
    font-weight: 600;
    display: block;
    margin-bottom: 8px;
}
.rep-input {
    width: 100%;
    padding: 11px 14px;
    border: 1.5px solid #e2e8f0;
    border-radius: 10px;
    font-size: 0.9rem;
    color: #334155;
    box-sizing: border-box;
    transition: border-color 0.2s;
}
.rep-input:focus { outline: none; border-color: #162660; }

/* PDF Action Cards */
.pdf-action-card {
    display: flex;
    align-items: center;
    gap: 16px;
    background: #f8fafc;
    border: 1.5px solid #e2e8f0;
    border-radius: 14px;
    padding: 16px 20px;
    margin-bottom: 14px;
    text-decoration: none;
    transition: all 0.2s ease;
    cursor: pointer;
}
.pdf-action-card:hover {
    background: white;
    border-color: #162660;
    box-shadow: 0 4px 16px rgba(22,38,96,0.12);
    transform: translateX(4px);
}
.pdf-icon {
    width: 46px; height: 46px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.4rem;
    flex-shrink: 0;
}
.pdf-info { flex: 1; }
.pdf-title { font-weight: 700; color: #1e293b; font-size: 0.92rem; margin-bottom: 2px; }
.pdf-sub   { font-size: 0.76rem; color: #94a3b8; }
.pdf-badge {
    font-size: 0.7rem;
    font-weight: 700;
    padding: 4px 10px;
    border-radius: 20px;
    flex-shrink: 0;
}
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">

    <!-- ===== BANNER ===== -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:24px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:14px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:12px;">
                <i class="ti ti-file-analytics" style="font-size:30px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.8rem;font-weight:800;margin:0;">Informes y Reportes</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;">Generación de reportes PDF con datos reales del sistema</p>
            </div>
        </div>
        <div style="z-index:1;background:rgba(255,255,255,0.12);border-radius:12px;padding:10px 18px;color:rgba(255,255,255,0.9);font-size:0.82rem;font-weight:600;text-align:right;">
            <i class="ti ti-calendar"></i> <?= date('d M Y') ?><br>
            <span style="opacity:0.7;">DomPDF · TCPDF · DataTables</span>
        </div>
    </div>

    <div style="display:grid;grid-template-columns:1fr 1fr;gap:24px;">

        <!-- ===== PANEL IZQUIERDO: Generador por período ===== -->
        <div class="rep-card slide-up">
            <h3 style="font-size:1.05rem;font-weight:700;color:#1e293b;margin:0 0 22px;display:flex;align-items:center;gap:8px;">
                <i class="ti ti-settings" style="color:#162660;"></i> Configurar & Generar
            </h3>

            <!-- Fechas -->
            <div style="display:grid;grid-template-columns:1fr 1fr;gap:14px;margin-bottom:18px;">
                <div>
                    <label class="rep-label">Fecha Inicio</label>
                    <input type="date" id="repFechaInicio" class="rep-input" value="<?= date('Y-m-01') ?>">
                </div>
                <div>
                    <label class="rep-label">Fecha Fin</label>
                    <input type="date" id="repFechaFin" class="rep-input" value="<?= date('Y-m-d') ?>">
                </div>
            </div>

            <!-- Departamento -->
            <div style="margin-bottom:24px;">
                <label class="rep-label">Departamento</label>
                <select id="repDepto" class="rep-input">
                    <option value="todos">Todos los departamentos</option>
                    <?php foreach ($departamentos as $d): ?>
                    <option value="<?= $d->id ?>"><?= htmlspecialchars($d->nombre) ?></option>
                    <?php endforeach; ?>
                </select>
            </div>

            <!-- === ACCIONES PDF === -->
            <p style="font-size:0.78rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;">
                Informes del Período Seleccionado
            </p>

            <!-- PDF Asistencias (TCPDF) -->
            <div class="pdf-action-card" onclick="descargarPdf('asistencias')">
                <div class="pdf-icon" style="background:#fef2f2;color:#ef4444;">
                    <i class="ti ti-file-type-pdf"></i>
                </div>
                <div class="pdf-info">
                    <div class="pdf-title">Informe de Asistencias</div>
                    <div class="pdf-sub">Listado tabular completo por período · TCPDF</div>
                </div>
                <span class="pdf-badge" style="background:#fee2e2;color:#ef4444;">PDF</span>
            </div>

            <!-- PDF Nómina (TCPDF) -->
            <div class="pdf-action-card" onclick="descargarPdf('nomina')">
                <div class="pdf-icon" style="background:#f0fdf4;color:#16a34a;">
                    <i class="ti ti-users"></i>
                </div>
                <div class="pdf-info">
                    <div class="pdf-title">Nómina de Pasantes</div>
                    <div class="pdf-sub">Listado general con estado y horas · TCPDF</div>
                </div>
                <span class="pdf-badge" style="background:#dcfce7;color:#16a34a;">PDF</span>
            </div>

            <!-- Divisor -->
            <hr style="border:none;border-top:1.5px solid #f1f5f9;margin:18px 0 16px;">

            <p style="font-size:0.78rem;font-weight:700;color:#94a3b8;text-transform:uppercase;letter-spacing:0.08em;margin-bottom:12px;">
                Kardex Individual (DomPDF)
            </p>

            <!-- Campo de cédula para kardex -->
            <div style="display:flex;gap:10px;align-items:flex-end;">
                <div style="flex:1;">
                    <label class="rep-label">Cédula del Pasante</label>
                    <input type="text" id="kardexCedula" placeholder="Ej: V-12345678" class="rep-input">
                    <div id="kardexPasanteInfo" style="font-size:0.75rem;color:#64748b;margin-top:4px;display:none;"></div>
                </div>
                <button onclick="buscarPasante()" style="padding:11px 16px;background:#f1f5f9;border:1.5px solid #e2e8f0;border-radius:10px;cursor:pointer;color:#475569;font-weight:600;white-space:nowrap;transition:all 0.2s;" onmouseover="this.style.background='#e2e8f0'" onmouseout="this.style.background='#f1f5f9'">
                    <i class="ti ti-search"></i> Buscar
                </button>
            </div>

            <div class="pdf-action-card" style="margin-top:12px;" onclick="descargarKardex()">
                <div class="pdf-icon" style="background:#eff6ff;color:#162660;">
                    <i class="ti ti-id-badge-2"></i>
                </div>
                <div class="pdf-info">
                    <div class="pdf-title">Reporte de Pasantía</div>
                    <div class="pdf-sub">Informe visual: evaluaciones + asistencias + progreso · DomPDF</div>
                </div>
                <span class="pdf-badge" style="background:#dbeafe;color:#162660;">Visual</span>
            </div>
        </div>

        <!-- ===== PANEL DERECHO: Info + Acesos rápidos ===== -->
        <div>

            <!-- Tarjeta explicativa de librerías -->
            <div class="rep-card slide-up" style="margin-bottom:20px;animation-delay:0.1s;">
                <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0 0 18px;display:flex;align-items:center;gap:8px;">
                    <i class="ti ti-library" style="color:#6366f1;"></i> Motor de Generación
                </h3>
                <div style="display:flex;flex-direction:column;gap:12px;">

                    <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;background:#fef2f2;border-radius:12px;border:1px solid #fee2e2;">
                        <div style="width:36px;height:36px;background:#ef4444;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="ti ti-file-type-pdf" style="color:white;"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:0.85rem;color:#1e293b;">DomPDF v2</div>
                            <div style="font-size:0.76rem;color:#64748b;margin-top:2px;">Convierte HTML a PDF. Ideal para reportes de pasantía con diseño visual, logos e imágenes.</div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;background:#f0fdf4;border-radius:12px;border:1px solid #bbf7d0;">
                        <div style="width:36px;height:36px;background:#16a34a;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="ti ti-table" style="color:white;"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:0.85rem;color:#1e293b;">TCPDF v6</div>
                            <div style="font-size:0.76rem;color:#64748b;margin-top:2px;">Genera PDFs tabulares de alta precisión. Filas alternadas, encabezados de tabla, datos masivos.</div>
                        </div>
                    </div>

                    <div style="display:flex;align-items:flex-start;gap:12px;padding:12px;background:#eff6ff;border-radius:12px;border:1px solid #bfdbfe;">
                        <div style="width:36px;height:36px;background:#3b82f6;border-radius:10px;display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                            <i class="ti ti-eye" style="color:white;"></i>
                        </div>
                        <div>
                            <div style="font-weight:700;font-size:0.85rem;color:#1e293b;">pdf.js (Mozilla)</div>
                            <div style="font-size:0.76rem;color:#64748b;margin-top:2px;">Visor de PDFs inline en el navegador. Previsualiza los reportes generados sin abrir nueva pestaña.</div>
                        </div>
                    </div>

                </div>
            </div>

            <!-- Accesos rápidos -->
            <div class="rep-card slide-up" style="animation-delay:0.2s;">
                <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0 0 18px;display:flex;align-items:center;gap:8px;">
                    <i class="ti ti-bolt" style="color:#f59e0b;"></i> Descargas Rápidas
                </h3>

                <a href="<?= URLROOT ?>/reportes/pdfNomina" target="_blank" class="pdf-action-card" style="text-decoration:none;">
                    <div class="pdf-icon" style="background:#fef3c7;color:#d97706;">
                        <i class="ti ti-users"></i>
                    </div>
                    <div class="pdf-info">
                        <div class="pdf-title">Nómina Completa Ahora</div>
                        <div class="pdf-sub">Todos los pasantes · todos los departamentos</div>
                    </div>
                    <i class="ti ti-external-link" style="color:#94a3b8;"></i>
                </a>

                <a href="<?= URLROOT ?>/reportes/pdfAsistencias?inicio=<?= date('Y-m-01') ?>&fin=<?= date('Y-m-d') ?>" target="_blank" class="pdf-action-card" style="text-decoration:none;">
                    <div class="pdf-icon" style="background:#fdf4ff;color:#9333ea;">
                        <i class="ti ti-calendar-stats"></i>
                    </div>
                    <div class="pdf-info">
                        <div class="pdf-title">Asistencias de Este Mes</div>
                        <div class="pdf-sub"><?= date('F Y') ?> · todos los departamentos</div>
                    </div>
                    <i class="ti ti-external-link" style="color:#94a3b8;"></i>
                </a>
            </div>
        </div>

    </div>
</div>

<script>
const URLROOT = '<?= URLROOT ?>';
let kardexPasanteId = null;

/**
 * Genera y descarga un PDF según el tipo seleccionado
 */
function descargarPdf(tipo) {
    const inicio = document.getElementById('repFechaInicio').value;
    const fin    = document.getElementById('repFechaFin').value;
    const depto  = document.getElementById('repDepto').value;

    if (!inicio || !fin) {
        if (typeof NotificationService !== 'undefined') {
            NotificationService.warning('Selecciona las fechas de inicio y fin.');
        }
        return;
    }

    let url = '';
    if (tipo === 'asistencias') {
        url = `${URLROOT}/reportes/pdfAsistencias?inicio=${inicio}&fin=${fin}&depto=${depto}`;
    } else if (tipo === 'nomina') {
        url = `${URLROOT}/reportes/pdfNomina`;
    }

    if (url) {
        if (typeof NotificationService !== 'undefined') {
            NotificationService.info('Generando PDF, un momento...');
        }
        // Abrir en nueva pestaña para forzar descarga
        window.open(url, '_blank');
    }
}

/**
 * Busca un pasante por cédula para obtener su ID
 */
function buscarPasante() {
    const cedula = document.getElementById('kardexCedula').value.trim();
    if (!cedula) {
        if (typeof NotificationService !== 'undefined') NotificationService.warning('Ingresa la cédula del pasante.');
        return;
    }

    fetch(`${URLROOT}/users/buscarPorCedula`, {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded', 'X-Requested-With': 'XMLHttpRequest' },
        body: `cedula=${encodeURIComponent(cedula)}`
    })
    .then(r => r.json())
    .then(data => {
        const info = document.getElementById('kardexPasanteInfo');
        info.style.display = 'block';
        if (data.success && data.pasante) {
            kardexPasanteId = data.pasante.id;
            info.style.color = '#16a34a';
            info.innerHTML = `✅ ${data.pasante.nombres} ${data.pasante.apellidos}`;
        } else {
            kardexPasanteId = null;
            info.style.color = '#ef4444';
            info.innerHTML = '❌ Pasante no encontrado';
        }
    })
    .catch(() => {
        // Fallback: intentar kardex directamente con la cédula
        kardexPasanteId = 'cedula:' + cedula;
        const info = document.getElementById('kardexPasanteInfo');
        info.style.display = 'block';
        info.style.color = '#f59e0b';
        info.innerHTML = '⚠️ Verificando en el servidor...';
    });
}

/**
 * Descarga el kardex del pasante buscado
 */
function descargarKardex() {
    if (!kardexPasanteId) {
        if (typeof NotificationService !== 'undefined') {
            NotificationService.warning('Busca primero a un pasante usando su cédula.');
        }
        return;
    }
    if (typeof NotificationService !== 'undefined') {
        NotificationService.info('Generando Reporte de Pasantía PDF...');
    }
    window.open(`${URLROOT}/reportes/pdfKardex?id=${kardexPasanteId}`, '_blank');
}

// Inicializar formato seleccionado
document.addEventListener('DOMContentLoaded', function() {
    // Si Choices.js está disponible, inicializar selects
    if (typeof Choices !== 'undefined') {
        new Choices(document.getElementById('repDepto'), { searchEnabled: true, itemSelectText: '' });
    }
});
</script>
