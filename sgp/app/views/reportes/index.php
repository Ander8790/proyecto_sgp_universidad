<?php
/**
 * Vista: Informes y Reportes (Mockup Alta Fidelidad)
 * FASE 4 - Datos hardcoded para demostración
 */
?>

<div class="dashboard-container">

    <!-- ===== BANNER ===== -->
    <div style="background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); border-radius: 20px; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between;">
        <div style="position: absolute; top: -30px; right: -30px; width: 200px; height: 200px; background: rgba(255,255,255,0.05); border-radius: 50%;"></div>
        <div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 10px;">
                    <i class="ti ti-file-analytics" style="font-size: 28px; color: white;"></i>
                </div>
                <div>
                    <h1 style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;">Informes y Reportes</h1>
                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem;">Generación de reportes del sistema de pasantías</p>
                </div>
            </div>
        </div>
    </div>

    <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 24px;">

        <!-- ===== GENERADOR DE REPORTES ===== -->
        <div style="background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 12px rgba(0,0,0,0.06);">
            <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0 0 24px; display: flex; align-items: center; gap: 8px;">
                <i class="ti ti-settings" style="color: #2563eb;"></i> Configurar Reporte
            </h3>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 0.85rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 8px;">Tipo de Reporte</label>
                <select id="tipoReporte" style="width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; color: #334155;">
                    <option value="asistencias">📊 Reporte de Asistencias</option>
                    <option value="pasantes">👥 Listado de Pasantes</option>
                    <option value="progreso">📈 Progreso de Horas</option>
                    <option value="departamentos">🏢 Por Departamento</option>
                    <option value="mensual">📅 Resumen Mensual</option>
                </select>
            </div>

            <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 16px; margin-bottom: 20px;">
                <div>
                    <label style="font-size: 0.85rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 8px;">Fecha Inicio</label>
                    <input type="date" value="<?= date('Y-m-01') ?>" style="width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; box-sizing: border-box;">
                </div>
                <div>
                    <label style="font-size: 0.85rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 8px;">Fecha Fin</label>
                    <input type="date" value="<?= date('Y-m-d') ?>" style="width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; box-sizing: border-box;">
                </div>
            </div>

            <div style="margin-bottom: 20px;">
                <label style="font-size: 0.85rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 8px;">Departamento</label>
                <select style="width: 100%; padding: 12px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; font-size: 0.9rem; color: #334155;">
                    <option>Todos los departamentos</option>
                    <option>Soporte Técnico</option>
                    <option>Redes y Telecomunicaciones</option>
                    <option>Desarrollo de Software</option>
                    <option>Administración</option>
                </select>
            </div>

            <div style="margin-bottom: 24px;">
                <label style="font-size: 0.85rem; color: #64748b; font-weight: 600; display: block; margin-bottom: 8px;">Formato de Salida</label>
                <div style="display: flex; gap: 12px;">
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; flex: 1; transition: all 0.2s;" id="fmtPdf">
                        <input type="radio" name="formato" value="pdf" checked onchange="seleccionarFormato('pdf')">
                        <i class="ti ti-file-type-pdf" style="color: #ef4444; font-size: 1.2rem;"></i>
                        <span style="font-weight: 600; font-size: 0.9rem;">PDF</span>
                    </label>
                    <label style="display: flex; align-items: center; gap: 8px; cursor: pointer; padding: 10px 16px; border: 1.5px solid #e2e8f0; border-radius: 10px; flex: 1; transition: all 0.2s;" id="fmtExcel">
                        <input type="radio" name="formato" value="excel" onchange="seleccionarFormato('excel')">
                        <i class="ti ti-file-type-xls" style="color: #16a34a; font-size: 1.2rem;"></i>
                        <span style="font-weight: 600; font-size: 0.9rem;">Excel</span>
                    </label>
                </div>
            </div>

            <button onclick="generarReporte()" style="width: 100%; padding: 14px; background: linear-gradient(135deg, #172554 0%, #1e3a8a 100%); color: white; border: none; border-radius: 12px; font-weight: 700; font-size: 1rem; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 10px; transition: all 0.2s; box-shadow: 0 4px 14px rgba(23,37,84,0.35);" onmouseover="this.style.transform='translateY(-2px)';this.style.boxShadow='0 8px 20px rgba(23,37,84,0.45)'" onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 14px rgba(23,37,84,0.35)'">
                <i class="ti ti-download"></i> Generar y Descargar Reporte
            </button>
        </div>

        <!-- ===== REPORTES RECIENTES ===== -->
        <div>
            <div style="background: white; border-radius: 16px; padding: 28px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); margin-bottom: 20px;">
                <h3 style="font-size: 1.1rem; font-weight: 700; color: #1e293b; margin: 0 0 20px; display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-history" style="color: #2563eb;"></i> Reportes Recientes
                </h3>
                <?php
                $reportes = [
                    ['nombre' => 'Asistencias - Febrero 2026', 'fecha' => '17/02/2026', 'tipo' => 'PDF', 'color' => '#ef4444', 'icon' => 'ti-file-type-pdf'],
                    ['nombre' => 'Listado de Pasantes Activos', 'fecha' => '15/02/2026', 'tipo' => 'Excel', 'color' => '#16a34a', 'icon' => 'ti-file-type-xls'],
                    ['nombre' => 'Progreso de Horas - Q1', 'fecha' => '10/02/2026', 'tipo' => 'PDF', 'color' => '#ef4444', 'icon' => 'ti-file-type-pdf'],
                    ['nombre' => 'Reporte por Departamento', 'fecha' => '05/02/2026', 'tipo' => 'Excel', 'color' => '#16a34a', 'icon' => 'ti-file-type-xls'],
                ];
                foreach ($reportes as $r): ?>
                <div style="display: flex; align-items: center; gap: 14px; padding: 14px 0; border-bottom: 1px solid #f1f5f9;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='transparent'">
                    <div style="background: <?= $r['color'] ?>18; border-radius: 10px; padding: 10px; flex-shrink: 0;">
                        <i class="ti <?= $r['icon'] ?>" style="font-size: 1.3rem; color: <?= $r['color'] ?>;"></i>
                    </div>
                    <div style="flex: 1;">
                        <p style="margin: 0; font-weight: 600; color: #1e293b; font-size: 0.9rem;"><?= $r['nombre'] ?></p>
                        <p style="margin: 2px 0 0; color: #94a3b8; font-size: 0.8rem;"><?= $r['fecha'] ?> · <?= $r['tipo'] ?></p>
                    </div>
                    <button onclick="descargarReporte()" style="background: #f1f5f9; border: none; padding: 8px 12px; border-radius: 8px; cursor: pointer; color: #475569; font-size: 0.85rem; transition: all 0.2s;" title="Descargar">
                        <i class="ti ti-download"></i>
                    </button>
                </div>
                <?php endforeach; ?>
            </div>

            <!-- Resumen rápido -->
            <div style="background: linear-gradient(135deg, #eff6ff, #dbeafe); border-radius: 16px; padding: 24px; border: 1px solid #bfdbfe;">
                <h4 style="font-size: 0.95rem; font-weight: 700; color: #2563eb; margin: 0 0 16px; display: flex; align-items: center; gap: 8px;">
                    <i class="ti ti-chart-bar"></i> Resumen del Mes
                </h4>
                <?php
                $resumen = [
                    ['label' => 'Total registros de asistencia', 'valor' => '1,240'],
                    ['label' => 'Promedio de asistencia', 'valor' => '87.3%'],
                    ['label' => 'Pasantes con 100% progreso', 'valor' => '2'],
                    ['label' => 'Horas totales acumuladas', 'valor' => '18,450 hrs'],
                ];
                foreach ($resumen as $item): ?>
                <div style="display: flex; justify-content: space-between; align-items: center; padding: 8px 0; border-bottom: 1px solid rgba(79,70,229,0.1);">
                    <span style="color: #475569; font-size: 0.85rem;"><?= $item['label'] ?></span>
                    <span style="font-weight: 700; color: #2563eb; font-size: 0.9rem;"><?= $item['valor'] ?></span>
                </div>
                <?php endforeach; ?>
            </div>
        </div>
    </div>
</div>

<script>
function generarReporte() {
    const tipo = document.getElementById('tipoReporte').value;
    const nombres = {
        asistencias: 'Reporte de Asistencias',
        pasantes: 'Listado de Pasantes',
        progreso: 'Progreso de Horas',
        departamentos: 'Reporte por Departamento',
        mensual: 'Resumen Mensual'
    };
    
    Swal.fire({
        title: '⏳ Generando Reporte...',
        html: `<p>Procesando: <b>${nombres[tipo]}</b></p>`,
        timer: 2000,
        timerProgressBar: true,
        showConfirmButton: false,
        didOpen: () => { Swal.showLoading(); }
    }).then(() => {
        Swal.fire({
            icon: 'success',
            title: '¡Reporte Generado!',
            html: `<p>El reporte <b>${nombres[tipo]}</b> ha sido generado exitosamente.</p><p style="color:#64748b;font-size:0.85rem;">La descarga comenzará automáticamente.</p>`,
            confirmButtonColor: '#2563eb',
            confirmButtonText: 'Entendido'
        });
    });
}

function descargarReporte() {
    if (typeof notyf !== 'undefined') {
        NotificationService.success('Iniciando descarga del reporte...');
    }
}

function seleccionarFormato(fmt) {
    document.getElementById('fmtPdf').style.borderColor = fmt === 'pdf' ? '#ef4444' : '#e2e8f0';
    document.getElementById('fmtExcel').style.borderColor = fmt === 'excel' ? '#16a34a' : '#e2e8f0';
}
</script>
