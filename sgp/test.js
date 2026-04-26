<script>
function abrirModalConsulta() {
    const modal = document.getElementById('modalConsultaRapida');
    if (!modal) {
        console.error('Error: No se encontr+¦ el modal de auditor+¡a.');
        return;
    }
    
    // Resetear UI
    const input = document.getElementById('inputBuscarPasanteAJAX');
    const sugerencias = document.getElementById('listaSugerencias');
    const estadoCero = document.getElementById('estadoCeroBusqueda');
    const zonaResultados = document.getElementById('zonaResultadosPasante');
    const footer = document.getElementById('audFooterAcciones');
    
    if (input) input.value = '';
    if (sugerencias) sugerencias.style.display = 'none';
    if (estadoCero) estadoCero.style.display = 'block';
    if (zonaResultados) zonaResultados.style.display = 'none';
    if (footer) footer.style.display = 'none';

    modal.classList.add('active');
    if (input) setTimeout(() => input.focus(), 300);
}

function cerrarModalConsulta() {
    const modal = document.getElementById('modalConsultaRapida');
    if (!modal) return;
    modal.classList.remove('active');
    setTimeout(() => {
        // Reset the form
        const inputBuscar = document.getElementById('inputBuscarPasanteAJAX');
        if (inputBuscar) { inputBuscar.value = ''; inputBuscar.placeholder = 'Buscar por nombre o c+®dula...'; }
        const zonaRes = document.getElementById('zonaResultadosPasante');
        if (zonaRes) zonaRes.style.display = 'none';
        const listaSug = document.getElementById('listaSugerencias');
        if (listaSug) listaSug.style.display = 'none';
        const estadoCero = document.getElementById('estadoCeroBusqueda');
        if (estadoCero) estadoCero.style.display = 'block';
        const btnPerfil = document.getElementById('btnIrPerfil');
        if (btnPerfil) btnPerfil.style.display = 'none';
        const btnPdf = document.getElementById('btnExportarPDF');
        if (btnPdf) btnPdf.style.display = 'none';
    }, 350);
}
/* ÔöÇÔöÇ Datos de pasantes (para el select del modal "Nuevo" registro) ÔöÇÔöÇ */
var pasantesActivos = [{"id":50,"nombre":"Arteaga L\u00f3pez, Gabriela"},{"id":51,"nombre":"Blanco Mart\u00ednez, Sof\u00eda"},{"id":38,"nombre":"Castillo D\u00edaz, Gabriel"},{"id":45,"nombre":"D\u00edaz Figueroa, Brayan"},{"id":52,"nombre":"Figueroa P\u00e9rez, Paola"},{"id":46,"nombre":"Flores Mendoza, Mar\u00eda"},{"id":35,"nombre":"Garc\u00eda Silva, Daniel"},{"id":4,"nombre":"gomezlo, jose luis"},{"id":30,"nombre":"Gonz\u00e1lez Morales, Carlos"},{"id":26,"nombre":"Gutierrez, Isabel"},{"id":59,"nombre":"gutierrez, nicoll"},{"id":47,"nombre":"Guti\u00e9rrez N\u00fa\u00f1ez, Ana"},{"id":36,"nombre":"Hern\u00e1ndez Torres, Jos\u00e9"},{"id":32,"nombre":"L\u00f3pez Vargas, Luis"},{"id":33,"nombre":"Mart\u00ednez Medina, Miguel"},{"id":40,"nombre":"Medina Guti\u00e9rrez, Roberto"},{"id":53,"nombre":"Mendoza Garc\u00eda, Karla"},{"id":37,"nombre":"Morales Ramos, Eduardo"},{"id":55,"nombre":"Nuevo, pasante"},{"id":54,"nombre":"N\u00fa\u00f1ez Hern\u00e1ndez, Andreina"},{"id":56,"nombre":"perez, geraldine"},{"id":34,"nombre":"P\u00e9rez Rojas, Rafael"},{"id":23,"nombre":"prieto, yarimar"},{"id":17,"nombre":"prieto, gabriel"},{"id":48,"nombre":"Ram\u00edrez Gonz\u00e1lez, Laura"},{"id":44,"nombre":"Ramos Blanco, Kenner"},{"id":29,"nombre":"rivas, wilfredo"},{"id":31,"nombre":"Rodr\u00edguez Castillo, Andr\u00e9s"},{"id":41,"nombre":"Rojas Ram\u00edrez, Frank"},{"id":42,"nombre":"Silva Su\u00e1rez, Yonathan"},{"id":49,"nombre":"Su\u00e1rez Rodr\u00edguez, Valentina"},{"id":43,"nombre":"Torres Arteaga, Aleixis"},{"id":39,"nombre":"Vargas Flores, Omar"},{"id":12,"nombre":"yepez, maria"},{"id":61,"nombre":"zambrano, hectmaris"}];

/* ÔöÇÔöÇ Abrir modal pre-seleccionado (desde lista "Sin Marcar") ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
function abrirModalManual(pasanteId, nombre) {
    const inputVisible = document.getElementById('buscadorPasante');
    const inputOculto = document.getElementById('manual-pasante-id');
    const btnLimpiar = document.getElementById('btnLimpiarPasante');

    if (inputOculto) inputOculto.value = pasanteId;
    if (inputVisible) {
        inputVisible.value = nombre;
        inputVisible.readOnly = true; 
        inputVisible.style.background = '#f1f5f9'; 
    }
    if (btnLimpiar) btnLimpiar.style.display = 'none';

    const estadoEl = document.getElementById('manual-estado');
    if (estadoEl) {
        estadoEl.value = 'Presente';
        toggleMotivo('Presente');
    }

    const modal = document.getElementById('modal-manual');
    if (modal) modal.style.display = 'flex';
}

/* ÔöÇÔöÇ Abrir modal vac+¡o (bot+¦n "Registro Manual" del banner) ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
function abrirModalManualNuevo() {
    if (pasantesActivos.length === 0) {
        Swal.fire({ icon: 'info', title: 'Sin pasantes activos', text: 'No hay pasantes con pasant+¡a activa para registrar.', confirmButtonColor: '#162660' });
        return;
    }

    const inputVisible = document.getElementById('buscadorPasante');
    const inputOculto = document.getElementById('manual-pasante-id');
    const btnLimpiar = document.getElementById('btnLimpiarPasante');

    if (inputOculto) inputOculto.value = '';
    if (inputVisible) {
        inputVisible.value = '';
        inputVisible.readOnly = false;
        inputVisible.style.background = '#fff';
    }
    if (btnLimpiar) btnLimpiar.style.display = 'none';

    const estadoEl = document.getElementById('manual-estado');
    if (estadoEl) {
        estadoEl.value = 'Presente';
        toggleMotivo('Presente');
    }

    const modal = document.getElementById('modal-manual');
    if (modal) modal.style.display = 'flex';
}

/* ÔöÇÔöÇ Cerrar modal ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
function cerrarModal() {
    document.getElementById('modal-manual').style.display = 'none';
    document.getElementById('form-manual').reset();
    document.getElementById('div-motivo').style.display = 'none';
    document.getElementById('div-evidencia').style.display = 'none';
    clearEvidencia();
    
    // Restaurar los inputs
    const inputVisible = document.getElementById('buscadorPasante');
    if (inputVisible) {
        inputVisible.readOnly = false;
        inputVisible.style.background = '#fff';
    }
}

/* ÔöÇÔöÇ Toggle campo motivo + evidencia ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
function toggleMotivo(estado) {
    const isJust = estado === 'Justificado';
    document.getElementById('div-motivo').style.display    = isJust ? 'block' : 'none';
    document.getElementById('div-evidencia').style.display = isJust ? 'block' : 'none';
    document.getElementById('manual-motivo').required      = isJust;
}

/* ÔöÇÔöÇ Helpers para el dropzone de evidencia ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
function showEvidenciaPreview(file) {
    if (!file) return;
    const preview = document.getElementById('dz-preview');
    const icon    = document.getElementById('dz-file-icon');
    const name    = document.getElementById('dz-file-name');
    const size    = document.getElementById('dz-file-size');
    const isPdf   = file.type === 'application/pdf';
    icon.className = isPdf ? 'ti ti-file-type-pdf' : 'ti ti-photo';
    icon.style.color = isPdf ? '#dc2626' : '#2563eb';
    name.textContent = file.name;
    size.textContent = (file.size / 1024 / 1024).toFixed(2) + ' MB';
    preview.style.display = 'flex';
    document.getElementById('dropzone-evidencia').style.borderColor = '#2563eb';
    document.getElementById('dropzone-evidencia').style.background  = '#eff6ff';
}

function clearEvidencia(e) {
    if (e) e.stopPropagation();
    document.getElementById('input-evidencia').value = '';
    const preview = document.getElementById('dz-preview');
    if (preview) preview.style.display = 'none';
    const dz = document.getElementById('dropzone-evidencia');
    if (dz) { dz.style.borderColor = '#bfdbfe'; dz.style.background = '#f0f9ff'; }
}

function handleDrop(e) {
    e.preventDefault();
    document.getElementById('dropzone-evidencia').classList.remove('dz-hover');
    const file = e.dataTransfer.files[0];
    if (!file) return;
    const allowed = ['image/jpeg','image/png','image/webp','application/pdf'];
    if (!allowed.includes(file.type)) {
        Swal.fire({ icon: 'error', title: 'Formato no soportado', text: 'Use JPG, PNG o PDF.', confirmButtonColor: '#162660' });
        return;
    }
    if (file.size > 5 * 1024 * 1024) {
        Swal.fire({ icon: 'error', title: 'Archivo muy grande', text: 'El archivo supera el l+¡mite de 5 MB.', confirmButtonColor: '#162660' });
        return;
    }
    // Asignar al input
    const dt = new DataTransfer();
    dt.items.add(file);
    document.getElementById('input-evidencia').files = dt.files;
    showEvidenciaPreview(file);
}

/* ÔöÇÔöÇ Submit del form lateral ÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇöÔÇö */
function enviarManual(e) {
    e.preventDefault();
    const form = e.target;
    const fd   = new FormData(form);
    const btn  = document.getElementById('btn-submit-manual');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> Guardando...'; }

    fetch('http://localhost./asistencias/registro_manual', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: '-íRegistrado!', text: data.message || 'Asistencia registrada correctamente.', confirmButtonColor: '#162660' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo registrar.', confirmButtonColor: '#162660' });
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy"></i> Guardar'; }
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#162660' });
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy"></i> Guardar'; }
        });
}

/* ÔöÇÔöÇ Env+¡o AJAX compartido ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
function enviarRegistroManual(pasanteId, estado, motivo) {
    const btn = document.getElementById('btn-submit-manual');
    if (btn) { btn.disabled = true; btn.innerHTML = '<i class="ti ti-loader ti-spin"></i> Guardando...'; }

    const fd = new FormData();
    fd.append('pasante_id', pasanteId);
    fd.append('fecha', '2026-04-21');
    fd.append('estado', estado);
    fd.append('motivo_justificacion', motivo);

    fetch('http://localhost./asistencias/registro_manual', { method: 'POST', body: fd })
        .then(r => r.json())
        .then(data => {
            if (data.success) {
                Swal.fire({ icon: 'success', title: '-íRegistrado!', text: data.message || 'Asistencia registrada correctamente.', confirmButtonColor: '#162660' })
                    .then(() => location.reload());
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: data.message || 'No se pudo registrar.', confirmButtonColor: '#162660' });
                if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy"></i> Guardar'; }
            }
        })
        .catch(() => {
            Swal.fire({ icon: 'error', title: 'Error de red', text: 'No se pudo conectar con el servidor.', confirmButtonColor: '#162660' });
            if (btn) { btn.disabled = false; btn.innerHTML = '<i class="ti ti-device-floppy"></i> Guardar'; }
        });
}

/* ÔöÇÔöÇ Modal de detalle ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
function verDetalle(id, info) {
    // Iniciales para el avatar
    const parts = info.nombre.split(',').map(s => s.trim());
    let iniciales = '?';
    if(parts.length === 2 && parts[0].length > 0 && parts[1].length > 0) {
        iniciales = (parts[1].charAt(0) + parts[0].charAt(0)).toUpperCase();
    } else if (info.nombre.length > 0) {
        iniciales = info.nombre.substring(0, 2).toUpperCase();
    }

    // Llenar datos
    document.getElementById('detalle-avatar').textContent = iniciales;
    document.getElementById('detalle-nombre').textContent = info.nombre;
    document.getElementById('detalle-cedula').textContent = 'C.I. ' + info.cedula;
    document.getElementById('detalle-depto').textContent = info.depto;
    document.getElementById('detalle-hora').textContent = info.hora;
    document.getElementById('detalle-metodo').textContent = info.metodo;
    
    // Configurar color del estado
    const estadoEl = document.getElementById('detalle-estado');
    estadoEl.textContent = info.estado;
    if(info.estado === 'Presente') estadoEl.style.color = '#10b981';
    else if(info.estado === 'Justificado') estadoEl.style.color = '#2563eb';
    else estadoEl.style.color = '#ef4444';

    // Motivo si existe
    const motivoContainer = document.getElementById('detalle-motivo-container');
    if (info.motivo) {
        document.getElementById('detalle-motivo').textContent = info.motivo;
        motivoContainer.style.display = 'block';
    } else {
        motivoContainer.style.display = 'none';
    }

    // Bot+¦n Almanaque
    const btnAlmanaque = document.getElementById('btnModalAlmanaque');
    if (btnAlmanaque) {
        if (info.pasante_id > 0) {
            btnAlmanaque.href = 'http://localhost./asistencias/almanaque/' + info.pasante_id;
            btnAlmanaque.style.display = 'flex';
        } else {
            btnAlmanaque.style.display = 'none';
        }
    }

    // Mostrar modal
    document.getElementById('modal-detalle').classList.add('active');
}

function cerrarModalDetalle() {
    document.getElementById('modal-detalle').classList.remove('active');
}

/* ÔöÇÔöÇ Exportar CSV ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
function exportarCSV() {
    let csv = 'Pasante,C+®dula,Departamento,Hora,M+®todo,Estado\n';
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","02:35 PM","Manual","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","02:35 PM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:29 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:28 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:22 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:22 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:19 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:16 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:03 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:03 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","07:52 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","07:50 AM","Kiosco","Presente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","07:25 PM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:28 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:26 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:09 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:07 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:06 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","07:57 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","07:57 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","07:56 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","07:51 AM","Kiosco","Presente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:26 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:26 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:24 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:19 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:19 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:16 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:09 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:09 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:06 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:03 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","07:56 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:56 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","07:51 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","07:51 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:25 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:13 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:12 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:01 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","07:51 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:50 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:28 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:20 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:12 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:06 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:06 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:04 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","07:53 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:53 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","07:51 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:24 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:24 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:20 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:20 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:12 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:09 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:04 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","07:55 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","07:54 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","07:53 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","07:52 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","07:52 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:10 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:06 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:06 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:03 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:01 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","07:56 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","07:55 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","07:53 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","07:52 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","07:52 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","07:51 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:28 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:28 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:28 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:28 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:24 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:19 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:16 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:16 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:07 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:04 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:01 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","07:54 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:24 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:22 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:21 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:10 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:00 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","07:55 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","07:53 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","07:50 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","07:50 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:26 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:24 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:19 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","07:53 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","07:52 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:50 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:25 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:24 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:24 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:22 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:11 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:10 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:07 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:04 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:03 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","07:59 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:56 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:23 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:22 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:20 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:12 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:10 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:09 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:07 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:05 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:04 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","07:57 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","07:57 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","07:57 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:28 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:26 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:21 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:16 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:11 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:06 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:04 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","07:55 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","07:55 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","07:52 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","07:51 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","07:51 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","07:51 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","07:51 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:26 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:26 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:22 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:21 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:16 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:12 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:10 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:02 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:00 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:57 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","07:55 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","07:52 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:27 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:26 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:24 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:20 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:19 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:16 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:07 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:01 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","07:58 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:56 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","07:55 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","07:55 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","07:53 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","07:51 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:28 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:25 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:22 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:21 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:19 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:18 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:14 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:13 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:07 AM","Kiosco","Presente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:01 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","07:59 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","07:57 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","07:56 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","07:53 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","07:52 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","07:51 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","07:51 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","12:00 AM","","Ausente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:28 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:25 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:21 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:16 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:12 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","08:11 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:06 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","08:03 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","08:03 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","08:03 AM","Kiosco","Presente"\n`;
        csv += `"Del Carmen, Yarima","30342976","Redes y Telecomunicaciones","08:02 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","08:00 AM","Kiosco","Presente"\n`;
        csv += `"rivas, wilfredo","30587335","Atenci+¦n al Usuario","08:00 AM","Manual","Ausente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","07:59 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","07:57 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","07:57 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","07:55 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","07:51 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","07:51 AM","Kiosco","Presente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","05:17 PM","Manual","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","05:13 PM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:26 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:24 AM","Kiosco","Presente"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","08:24 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:19 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:17 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","08:15 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:11 AM","Kiosco","Presente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","08:10 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:06 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","08:01 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:01 AM","Kiosco","Presente"\n`;
        csv += `"prieto, gabriel","31342972","Soporte T+®cnico","08:00 AM","Kiosco","Presente"\n`;
        csv += `"prieto, yarimar","15020928","Soporte T+®cnico","08:00 AM","Manual","Justificado"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","07:56 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","07:56 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","07:56 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","07:56 AM","Kiosco","Presente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","07:50 AM","Kiosco","Presente"\n`;
        csv += `"yepez, maria","30342977","Atenci+¦n al Usuario","02:13 AM","Kiosco","Presente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"rivas, wilfredo","30587335","Atenci+¦n al Usuario","02:49 PM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","01:44 PM","Kiosco","Presente"\n`;
        csv += `"Mendoza Garc+¡a, Karla","27199383","Atenci+¦n al Usuario","08:24 AM","Kiosco","Presente"\n`;
        csv += `"L+¦pez Vargas, Luis","27108642","Reparaciones Electr+¦nicas","08:23 AM","Kiosco","Presente"\n`;
        csv += `"Garc+¡a Silva, Daniel","27121605","Redes y Telecomunicaciones","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Arteaga L+¦pez, Gabriela","27186420","Soporte T+®cnico","08:18 AM","Kiosco","Presente"\n`;
        csv += `"Ramos Blanco, Kenner","27160494","Reparaciones Electr+¦nicas","08:17 AM","Kiosco","Presente"\n`;
        csv += `"Morales Ramos, Eduardo","27130247","Atenci+¦n al Usuario","08:16 AM","Kiosco","Presente"\n`;
        csv += `"Rojas Ram+¡rez, Frank","27147531","Atenci+¦n al Usuario","08:10 AM","Kiosco","Presente"\n`;
        csv += `"Su+írez Rodr+¡guez, Valentina","27182099","Atenci+¦n al Usuario","08:08 AM","Kiosco","Presente"\n`;
        csv += `"Castillo D+¡az, Gabriel","27134568","Soporte T+®cnico","08:06 AM","Kiosco","Presente"\n`;
        csv += `"Figueroa P+®rez, Paola","27195062","Reparaciones Electr+¦nicas","08:05 AM","Kiosco","Presente"\n`;
        csv += `"Rodr+¡guez Castillo, Andr+®s","27104321","Redes y Telecomunicaciones","08:04 AM","Kiosco","Presente"\n`;
        csv += `"Silva Su+írez, Yonathan","27151852","Soporte T+®cnico","08:03 AM","Kiosco","Presente"\n`;
        csv += `"D+¡az Figueroa, Brayan","27164815","Atenci+¦n al Usuario","08:03 AM","Kiosco","Presente"\n`;
        csv += `"P+®rez Rojas, Rafael","27117284","Soporte T+®cnico","08:01 AM","Kiosco","Presente"\n`;
        csv += `"gomezlo, jose luis","30342975","Soporte T+®cnico","08:00 AM","Kiosco","Presente"\n`;
        csv += `"Del Carmen, Yarima","30342976","Redes y Telecomunicaciones","08:00 AM","Manual","Ausente"\n`;
        csv += `"Mart+¡nez Medina, Miguel","27112963","Atenci+¦n al Usuario","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Flores Mendoza, Mar+¡a","27169136","Soporte T+®cnico","07:58 AM","Kiosco","Presente"\n`;
        csv += `"Guti+®rrez N+¦+¦ez, Ana","27173457","Redes y Telecomunicaciones","07:58 AM","Kiosco","Presente"\n`;
        csv += `"N+¦+¦ez Hern+índez, Andreina","27203704","Soporte T+®cnico","07:57 AM","Kiosco","Presente"\n`;
        csv += `"Vargas Flores, Omar","27138889","Redes y Telecomunicaciones","07:55 AM","Kiosco","Presente"\n`;
        csv += `"Gonz+ílez Morales, Carlos","27100000","Soporte T+®cnico","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Hern+índez Torres, Jos+®","27125926","Reparaciones Electr+¦nicas","07:54 AM","Kiosco","Presente"\n`;
        csv += `"Torres Arteaga, Aleixis","27156173","Redes y Telecomunicaciones","07:52 AM","Kiosco","Presente"\n`;
        csv += `"Gutierrez, Isabel","28694068","Atenci+¦n al Usuario","12:00 AM","","Ausente"\n`;
        csv += `"Medina Guti+®rrez, Roberto","27143210","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Ausente"\n`;
        csv += `"Ram+¡rez Gonz+ílez, Laura","27177778","Reparaciones Electr+¦nicas","12:00 AM","Kiosco","Justificado"\n`;
        csv += `"Blanco Mart+¡nez, Sof+¡a","27190741","Redes y Telecomunicaciones","12:00 AM","Kiosco","Ausente"\n`;
    
    const blob = new Blob(['\ufeff' + csv], { type: 'text/csv;charset=utf-8;' });
    const url  = URL.createObjectURL(blob);
    const a    = document.createElement('a');
    a.href = url;
    a.download = 'asistencias_2026-04-21.csv';
    a.click();
    URL.revokeObjectURL(url);

    NotificationService.success('Archivo CSV descargado correctamente');
}

/* ÔöÇÔöÇ Cerrar modal si clic fuera ÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇÔöÇ */
document.getElementById('modal-manual').addEventListener('click', function(e) {
    if (e.target === this) cerrarModal();
});
/* ÔöÇÔöÇ L+¦gica del Combo-Box de Pasantes (Buscador en Tiempo Real) ÔöÇÔöÇ */
document.addEventListener('DOMContentLoaded', () => {
    const inputBuscador = document.getElementById('buscadorPasante');
    const inputOculto = document.getElementById('manual-pasante-id');
    const resultadosDiv = document.getElementById('resultadosPasante');
    const btnLimpiar = document.getElementById('btnLimpiarPasante');

    if (inputBuscador && resultadosDiv) {
        // Filtrar al escribir
        inputBuscador.addEventListener('input', function() {
            const query = this.value.toLowerCase().trim();
            resultadosDiv.innerHTML = ''; // Limpiar resultados
            
            // Si el input est+í vac+¡o, ocultar dropdown y limpiar selecci+¦n
            if (query === '') {
                resultadosDiv.style.display = 'none';
                btnLimpiar.style.display = 'none';
                inputOculto.value = '';
                return;
            }

            // Filtrar pasantes activos
            const filtrados = pasantesActivos.filter(p => p.nombre.toLowerCase().includes(query));

            if (filtrados.length === 0) {
                resultadosDiv.innerHTML = `
                <div style="padding: 24px; text-align: center;">
                    <i class="ti ti-ghost" style="font-size: 2.5rem; color: #cbd5e1; display: block; margin-bottom: 8px;"></i>
                    <p style="margin: 0; color: #64748b; font-size: 0.9rem; font-weight: 500;">
                        No se encontr+¦ ning+¦n pasante activo llamado <strong style="color: #1e293b;">"${query}"</strong>.
                    </p>
                    <p style="margin: 6px 0 0; color: #94a3b8; font-size: 0.8rem;">
                        <strong style="color: #64748b;">Verifica si el nombre est+í bien escrito</strong> o si el pasante ya fue asignado.
                    </p>
                </div>`;
            } else {
                // Renderizar opciones
                filtrados.forEach(p => {
                    const item = document.createElement('div');
                    item.style.cssText = 'padding: 12px 16px; cursor: pointer; transition: background 0.2s; border-bottom: 1px solid #f1f5f9; font-size: 0.9rem; color: #1e293b; font-weight: 500; display: flex; align-items: center; gap: 8px;';
                    item.innerHTML = `<div style="width: 28px; height: 28px; border-radius: 50%; background: #eff6ff; color: #2563eb; display: flex; align-items: center; justify-content: center; font-size: 0.7rem; font-weight: 700;">${p.nombre.substring(0,2).toUpperCase()}</div> ${p.nombre}`;
                    
                    // Hover effect via JS events since inline hover is tricky
                    item.addEventListener('mouseenter', () => item.style.background = '#f8fafc');
                    item.addEventListener('mouseleave', () => item.style.background = 'white');

                    // Al hacer clic en un resultado
                    item.addEventListener('click', () => {
                        inputBuscador.value = p.nombre;
                        inputOculto.value = p.id;
                        resultadosDiv.style.display = 'none';
                        btnLimpiar.style.display = 'flex';
                    });
                    resultadosDiv.appendChild(item);
                });
            }
            resultadosDiv.style.display = 'block';
        });

        // Limpiar selecci+¦n
        btnLimpiar.addEventListener('click', () => {
            inputBuscador.value = '';
            inputOculto.value = '';
            btnLimpiar.style.display = 'none';
            resultadosDiv.style.display = 'none';
            inputBuscador.focus();
        });

        // Ocultar dropdown si se hace clic afuera
        document.addEventListener('click', (e) => {
            if (!inputBuscador.contains(e.target) && !resultadosDiv.contains(e.target)) {
                resultadosDiv.style.display = 'none';
            }
        });

        // Mostrar todo al enfocar el input vac+¡o (opcional, para ver la lista inicial)
        inputBuscador.addEventListener('focus', function() {
            if (this.value === '') {
                // Trigger a search with empty string to show all initially, or leave it empty until typing. Let's force an empty search.
                this.dispatchEvent(new Event('input'));
            } else {
               resultadosDiv.style.display = 'block';
            }
        });
    }
});
</script>
