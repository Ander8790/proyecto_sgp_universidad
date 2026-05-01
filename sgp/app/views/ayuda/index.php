<?php
/**
 * Vista: Manual de Usuario — SGP (Diseño SPA anti-scroll)
 */
$rolId    = Session::get('role_id') ?? Session::get('rol_id') ?? 0;
$esAdmin   = ($rolId == 1 || $rolId == 0);
$esTutor   = $rolId == 2;
$esPasante = ($rolId == 3 || $rolId == 0); // El SuperAdmin ve TODO
?>
<style>
/* ══ Manual SGP — Diseño SPA ═══════════════════════════════════ */
.man-wrap { width:100%; padding:20px 24px; }

/* Banner */
.man-banner {
    background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);
    border-radius:22px; padding:28px 36px; margin-bottom:22px;
    display:flex; align-items:center; justify-content:space-between; gap:16px;
    position:relative; overflow:hidden; box-shadow:0 12px 32px rgba(30,58,138,.2);
}
.man-banner::before { content:''; position:absolute; top:-60px; right:-60px; width:280px; height:280px; background:rgba(255,255,255,.05); border-radius:50%; }
.man-btn-pdf {
    display:inline-flex; align-items:center; gap:8px;
    background:#dc2626; border:none; color:white;
    padding:10px 20px; border-radius:12px; font-size:.88rem; font-weight:700;
    cursor:pointer; transition:all .2s; white-space:nowrap; z-index:1;
    box-shadow:0 4px 14px rgba(220,38,38,.4);
}
.man-btn-pdf:hover { background:#b91c1c; transform:translateY(-2px); box-shadow:0 6px 20px rgba(220,38,38,.5); }

/* Search */
.man-search { position:relative; margin-bottom:20px; }
.man-search input {
    width:100%; padding:12px 44px 12px 44px; border:2px solid #e2e8f0;
    border-radius:14px; font-size:.95rem; background:#f8fafc;
    color:#0d1424; outline:none; transition:all .2s; font-family:inherit; box-sizing:border-box;
}
.man-search input:focus { border-color:#2563eb; background:white; box-shadow:0 0 0 4px rgba(37,99,235,.07); }
.man-search .si { position:absolute; left:15px; top:50%; transform:translateY(-50%); color:#94a3b8; }
.man-search .sc { position:absolute; right:12px; top:50%; transform:translateY(-50%); background:none; border:none; cursor:pointer; color:#94a3b8; display:none; font-size:1.1rem; }

/* Layout 2 columnas */
.man-layout { display:grid; grid-template-columns:230px 1fr; gap:20px; align-items:start; }
@media(max-width:860px){ .man-layout{ grid-template-columns:1fr; } }

/* TOC */
.man-toc { background:white; border-radius:18px; border:1px solid #e2e8f0; padding:16px; position:sticky; top:75px; }
.man-toc h4 { font-size:.68rem; font-weight:800; color:#64748b; text-transform:uppercase; letter-spacing:.8px; margin:0 0 10px; display:flex; align-items:center; gap:6px; }
.toc-link { display:flex; align-items:center; gap:8px; padding:8px 10px; border-radius:10px; color:#475569; font-size:.82rem; font-weight:600; text-decoration:none; transition:all .2s; cursor:pointer; }
.toc-link:hover { background:#f1f5f9; color:#1e293b; }
.toc-link.activo { background:#eff6ff; color:#2563eb; }
.toc-link i { font-size:.95rem; flex-shrink:0; width:18px; }
.toc-sep { height:1px; background:#f1f5f9; margin:6px 0; }

/* Panel de contenido */
.man-panel { min-width:0; }

/* Secciones SPA — una a la vez */
.man-sec { display:none; background:white; border-radius:20px; border:1px solid #e2e8f0; overflow:hidden; }
.man-sec.active { display:block; animation:manIn .22s ease; }
@keyframes manIn { from{opacity:0;transform:translateY(6px)} to{opacity:1;transform:translateY(0)} }

/* Cabecera de sección */
.man-sec-hd { padding:22px 26px 18px; display:flex; align-items:center; gap:14px; border-bottom:1px solid #f1f5f9; }
.man-sec-icon { width:44px; height:44px; border-radius:13px; display:flex; align-items:center; justify-content:center; font-size:1.25rem; flex-shrink:0; }
.man-sec-title { font-size:1.1rem; font-weight:800; color:#0d1424; margin:0; }
.man-sec-sub { font-size:.78rem; color:#64748b; margin:3px 0 0; }

/* Cuerpo de sección */
.man-sec-body { padding:22px 26px; }

/* Elementos de contenido */
.man-p { color:#475569; font-size:.9rem; line-height:1.75; margin:0 0 14px; }
.man-h3 { font-size:.8rem; font-weight:800; color:#1e293b; text-transform:uppercase; letter-spacing:.5px; margin:18px 0 10px; display:flex; align-items:center; gap:7px; }
.man-h3 i { color:#2563eb; }
.man-steps { counter-reset:step; display:flex; flex-direction:column; gap:10px; margin-bottom:16px; }
.man-step { display:flex; gap:12px; align-items:flex-start; }
.man-step::before { counter-increment:step; content:counter(step); width:24px; height:24px; min-width:24px; background:#2563eb; color:white; border-radius:50%; font-size:.75rem; font-weight:800; display:flex; align-items:center; justify-content:center; margin-top:2px; }
.man-step p { color:#475569; font-size:.88rem; line-height:1.6; margin:0; }
.man-step strong { color:#1e293b; }
.man-tip { background:#eff6ff; border:1px solid #bfdbfe; border-radius:12px; padding:12px 16px; margin-bottom:14px; display:flex; gap:10px; align-items:flex-start; }
.man-tip i { color:#2563eb; font-size:1.1rem; margin-top:2px; flex-shrink:0; }
.man-tip p { margin:0; font-size:.85rem; color:#1e40af; line-height:1.6; }
.man-warn { background:#fffbeb; border:1px solid #fde68a; border-radius:12px; padding:12px 16px; margin-bottom:14px; display:flex; gap:10px; align-items:flex-start; }
.man-warn i { color:#d97706; font-size:1.1rem; margin-top:2px; flex-shrink:0; }
.man-warn p { margin:0; font-size:.85rem; color:#92400e; line-height:1.6; }
.man-table { width:100%; border-collapse:collapse; font-size:.85rem; margin-bottom:16px; }
.man-table th { background:#f8fafc; padding:9px 13px; text-align:left; font-size:.72rem; text-transform:uppercase; letter-spacing:.5px; color:#64748b; font-weight:700; border-bottom:2px solid #f1f5f9; }
.man-table td { padding:9px 13px; border-bottom:1px solid #f8fafc; color:#475569; }
.man-table tr:last-child td { border-bottom:none; }
.man-badge { display:inline-block; padding:2px 9px; border-radius:100px; font-size:.72rem; font-weight:700; }
.mb-green { background:#dcfce7; color:#166534; }
.mb-blue  { background:#dbeafe; color:#1e40af; }
.mb-amber { background:#fef3c7; color:#92400e; }
.mb-slate { background:#f1f5f9; color:#475569; }
.man-kbd { display:inline-block; background:#f1f5f9; border:1px solid #cbd5e1; border-radius:5px; padding:2px 8px; font-size:.78rem; font-family:monospace; color:#374151; }

/* Navegación prev/next */
.man-nav { display:flex; justify-content:space-between; align-items:center; margin-top:16px; gap:12px; }
.man-nav-btn { display:flex; align-items:center; gap:8px; padding:10px 18px; border-radius:12px; border:1.5px solid #e2e8f0; background:white; color:#475569; font-weight:600; font-size:.85rem; cursor:pointer; transition:all .2s; }
.man-nav-btn:hover:not(:disabled) { border-color:#2563eb; color:#2563eb; background:#eff6ff; }
.man-nav-btn:disabled { opacity:.3; cursor:default; pointer-events:none; }
.man-nav-info { font-size:.78rem; color:#94a3b8; text-align:center; flex:1; }

/* Sin resultados */
#manNoResults { display:none; text-align:center; padding:60px 20px; color:#94a3b8; background:white; border-radius:18px; border:1px solid #f1f5f9; }

/* Modo búsqueda: muestra todas las secciones y ocupa el grid completo */
.man-panel.search-mode { grid-column: 1 / -1; }
.man-panel.search-mode .man-sec { display:block; margin-bottom:16px; }
.man-panel.search-mode .man-sec:last-child { margin-bottom:0; }
.man-panel.search-mode .man-nav { display:none; }

/* ══ Print / PDF ═══════════════════════════════════════════════ */
@media print {
    .man-sec { display:block !important; margin-bottom:28px; break-inside:avoid; }
    .man-banner { background:#172554 !important; -webkit-print-color-adjust:exact; print-color-adjust:exact; }
    .man-btn-pdf, .man-nav, .man-search, .man-toc,
    .main-sidebar, .topbar, #sidebarOverlay, .mobile-dock { display:none !important; }
    .man-layout { display:block !important; }
    .man-wrap { padding:0 !important; }
    .man-sec-body { overflow:visible !important; }
    .man-table th, .man-tip, .man-warn { -webkit-print-color-adjust:exact; print-color-adjust:exact; }
}
</style>

<div class="man-wrap">

<!-- BANNER -->
<div class="man-banner">
    <div style="display:flex;align-items:center;gap:18px;z-index:1;">
        <div style="background:rgba(255,255,255,.15);border-radius:16px;padding:14px;backdrop-filter:blur(4px);">
            <i class="ti ti-lifebuoy" style="font-size:32px;color:white;"></i>
        </div>
        <div>
            <h1 style="color:white;font-size:1.7rem;font-weight:800;margin:0;line-height:1.2;">Manual de Usuario</h1>
            <p style="color:rgba(255,255,255,.75);margin:5px 0 0;font-size:.88rem;">Sistema de Gestión de Pasantías — ISP Bolívar</p>
        </div>
    </div>
    <div style="z-index:1;display:flex;align-items:center;gap:12px;flex-wrap:wrap;">
        <div style="background:rgba(255,255,255,.12);border-radius:10px;padding:8px 16px;color:rgba(255,255,255,.8);font-size:.8rem;">
            <i class="ti ti-tag" style="margin-right:4px;"></i> v2.0 · 2026
        </div>
        <button class="man-btn-pdf" onclick="window.open('<?= URLROOT ?>/ayuda/pdf','_blank')">
            <i class="ti ti-file-type-pdf" style="font-size:1.1rem;"></i> Descargar PDF
        </button>
    </div>
</div>

<!-- BÚSQUEDA -->
<div class="man-search">
    <i class="ti ti-search si"></i>
    <input type="text" id="manSearch" placeholder="Buscar en el manual… Ej: crear pasante, asistencia, constancia" oninput="filtrarManual(this.value)">
    <button class="sc" id="manSearchClear" onclick="limpiarBusqueda()"><i class="ti ti-x"></i></button>
</div>
<div id="manNoResults">
    <i class="ti ti-search" style="font-size:3rem;display:block;margin-bottom:12px;opacity:.3;"></i>
    <div style="font-weight:700;">Sin resultados para esa búsqueda</div>
    <div style="font-size:.85rem;margin-top:6px;">Intenta con otra palabra clave</div>
</div>

<div class="man-layout">

<!-- ── TABLA DE CONTENIDO ──────────────────────────────────── -->
<div class="man-toc" id="manToc">
    <h4><i class="ti ti-list"></i> Contenido</h4>

    <a class="toc-link" data-sec="sec-inicio" onclick="mostrarSeccion('sec-inicio')"><i class="ti ti-home"></i> Inicio / Dashboard</a>
    <?php if ($esAdmin || $esTutor): ?>
    <a class="toc-link" data-sec="sec-pasantes" onclick="mostrarSeccion('sec-pasantes')"><i class="ti ti-user-check"></i> Pasantes</a>
    <a class="toc-link" data-sec="sec-asistencias" onclick="mostrarSeccion('sec-asistencias')"><i class="ti ti-calendar-stats"></i> Asistencias</a>
    <?php endif; ?>
    <?php if ($esAdmin): ?>
    <a class="toc-link" data-sec="sec-usuarios" onclick="mostrarSeccion('sec-usuarios')"><i class="ti ti-users"></i> Usuarios</a>
    <a class="toc-link" data-sec="sec-periodos" onclick="mostrarSeccion('sec-periodos')"><i class="ti ti-calendar-month"></i> Períodos</a>
    <a class="toc-link" data-sec="sec-asignaciones" onclick="mostrarSeccion('sec-asignaciones')"><i class="ti ti-link"></i> Asignaciones</a>
    <a class="toc-link" data-sec="sec-evaluaciones" onclick="mostrarSeccion('sec-evaluaciones')"><i class="ti ti-star"></i> Evaluaciones</a>
    <a class="toc-link" data-sec="sec-reportes" onclick="mostrarSeccion('sec-reportes')"><i class="ti ti-printer"></i> Reportes</a>
    <a class="toc-link" data-sec="sec-actividades" onclick="mostrarSeccion('sec-actividades')"><i class="ti ti-briefcase"></i> Act. Extras</a>
    <a class="toc-link" data-sec="sec-analiticas" onclick="mostrarSeccion('sec-analiticas')"><i class="ti ti-chart-dots"></i> Analíticas</a>
    <a class="toc-link" data-sec="sec-respaldos" onclick="mostrarSeccion('sec-respaldos')"><i class="ti ti-database"></i> Respaldos</a>
    <a class="toc-link" data-sec="sec-bitacora" onclick="mostrarSeccion('sec-bitacora')"><i class="ti ti-clipboard-list"></i> Bitácora</a>
    <a class="toc-link" data-sec="sec-configuracion" onclick="mostrarSeccion('sec-configuracion')"><i class="ti ti-settings"></i> Configuración</a>
    <?php endif; ?>
    <?php if ($esPasante): ?>
    <div class="toc-sep"></div>
    <a class="toc-link" data-sec="sec-pasante-asist" onclick="mostrarSeccion('sec-pasante-asist')"><i class="ti ti-clock-check"></i> Mi Asistencia</a>
    <a class="toc-link" data-sec="sec-pasante-eval" onclick="mostrarSeccion('sec-pasante-eval')"><i class="ti ti-star"></i> Mis Evaluaciones</a>
    <a class="toc-link" data-sec="sec-pasante-const" onclick="mostrarSeccion('sec-pasante-const')"><i class="ti ti-file-certificate"></i> Mi Constancia</a>
    <?php endif; ?>
    <div class="toc-sep"></div>
    <a class="toc-link" data-sec="sec-tooltips" onclick="mostrarSeccion('sec-tooltips')"><i class="ti ti-help-circle"></i> Íconos de Ayuda</a>
    <a class="toc-link" data-sec="sec-accesos" onclick="mostrarSeccion('sec-accesos')"><i class="ti ti-keyboard"></i> Atajos y Accesos</a>
</div>

<!-- ── PANEL PRINCIPAL ──────────────────────────────────────── -->
<div class="man-panel" id="manPanel">

<!-- ═══ INICIO / DASHBOARD ═══ -->
<div class="man-sec" id="sec-inicio" data-keywords="inicio dashboard panel principal kpi estadísticas resumen">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#eff6ff;color:#2563eb;"><i class="ti ti-home"></i></div>
        <div><p class="man-sec-title">Inicio / Dashboard</p><p class="man-sec-sub">Panel principal con indicadores del sistema</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">El dashboard es la primera pantalla que verás al iniciar sesión. Muestra un resumen del estado general del sistema en tiempo real.</p>
        <div class="man-h3"><i class="ti ti-layout-dashboard"></i> Indicadores (KPIs)</div>
        <table class="man-table">
            <thead><tr><th>Indicador</th><th>Descripción</th></tr></thead>
            <tbody>
                <tr><td><strong>Pasantes Activos</strong></td><td>Total de pasantes con pasantía en curso en el período actual</td></tr>
                <tr><td><strong>Asistencia Hoy</strong></td><td>Porcentaje de presentes registrados en el día</td></tr>
                <tr><td><strong>Período Activo</strong></td><td>Nombre y progreso del período académico vigente</td></tr>
                <tr><td><strong>Evaluaciones Pendientes</strong></td><td>Pasantes sin evaluación completada</td></tr>
            </tbody>
        </table>
        <div class="man-tip"><i class="ti ti-info-circle"></i><p>Los datos del dashboard se actualizan en tiempo real. Si no ves cambios, recarga la página con <span class="man-kbd">F5</span>.</p></div>
    </div>
</div>

<?php if ($esAdmin || $esTutor): ?>
<!-- ═══ PASANTES ═══ -->
<div class="man-sec" id="sec-pasantes" data-keywords="pasantes crear nuevo registro cédula carrera institución horas asignar departamento">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#fef3c7;color:#d97706;"><i class="ti ti-user-check"></i></div>
        <div><p class="man-sec-title">Pasantes</p><p class="man-sec-sub">Registro y gestión de estudiantes en pasantía</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">El módulo de pasantes centraliza la información académica y personal de cada estudiante en práctica.</p>
        <div class="man-h3"><i class="ti ti-user-plus"></i> Registrar un pasante</div>
        <div class="man-steps">
            <div class="man-step"><p>Ve a <strong>Pasantes</strong> y haz clic en <strong>+ Nuevo Pasante</strong>.</p></div>
            <div class="man-step"><p>Completa los datos personales: cédula, nombres, apellidos, correo y teléfono.</p></div>
            <div class="man-step"><p>Los campos <strong>correo</strong> y <strong>cédula</strong> se verifican en tiempo real — si ya existen, aparece un indicador rojo.</p></div>
            <div class="man-step"><p>Completa los datos académicos: institución, carrera, tutor asignado, departamento y horas meta.</p></div>
            <div class="man-step"><p>Haz clic en <strong>Registrar Pasante</strong>.</p></div>
        </div>
        <div class="man-tip"><i class="ti ti-info-circle"></i><p>Las horas meta por defecto son <strong>1440 horas</strong> para pasantía Regular y <strong>360 horas</strong> para Corta. Puedes ajustarlas en la ficha del pasante.</p></div>
        <div class="man-h3"><i class="ti ti-eye"></i> Ficha del pasante</div>
        <p class="man-p">Al hacer clic en un pasante accedes a su ficha completa: datos personales, estado de horas, historial de asistencias, evaluaciones y acciones rápidas.</p>
    </div>
</div>

<!-- ═══ ASISTENCIAS ═══ -->
<div class="man-sec" id="sec-asistencias" data-keywords="asistencia registrar presente ausente tardanza almanaque justificado hora entrada">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#ecfdf5;color:#059669;"><i class="ti ti-calendar-stats"></i></div>
        <div><p class="man-sec-title">Asistencias</p><p class="man-sec-sub">Registro diario de entradas y salidas</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">El módulo de asistencias es el núcleo operativo del sistema. Registra la asistencia diaria de cada pasante y calcula las horas acumuladas automáticamente.</p>
        <div class="man-h3"><i class="ti ti-check"></i> Registrar asistencia</div>
        <div class="man-steps">
            <div class="man-step"><p>Abre el módulo <strong>Asistencias</strong>.</p></div>
            <div class="man-step"><p>Selecciona la fecha (por defecto es el día de hoy).</p></div>
            <div class="man-step"><p>Para cada pasante marca el estado: <span class="man-badge mb-green">Presente</span> <span class="man-badge mb-amber">Tardanza</span> <span class="man-badge mb-slate">Ausente</span> <span class="man-badge mb-blue">Justificado</span>.</p></div>
            <div class="man-step"><p>Haz clic en <strong>Guardar</strong>.</p></div>
        </div>
        <div class="man-tip"><i class="ti ti-info-circle"></i><p>El sistema cuenta <strong>8 horas</strong> por cada día marcado como Presente o Tardanza. Las tardanzas no penalizan el conteo de horas.</p></div>
        <div class="man-h3"><i class="ti ti-calendar-event"></i> Almanaque mensual</div>
        <p class="man-p">Cada pasante tiene un almanaque individual que muestra su historial de asistencias en formato calendario. Accede desde la tarjeta del pasante → botón <strong>Almanaque</strong>.</p>
        <div class="man-h3"><i class="ti ti-pencil"></i> Justificar ausencia</div>
        <p class="man-p">Al marcar a un pasante como <strong>Justificado</strong>, puedes agregar un motivo en el campo de observaciones. Este motivo aparecerá en el reporte de asistencias.</p>
    </div>
</div>
<?php endif; ?>

<?php if ($esAdmin): ?>
<!-- ═══ USUARIOS ═══ -->
<div class="man-sec" id="sec-usuarios" data-keywords="usuarios crear usuario contraseña rol admin tutor pasante registro cuenta">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#f0fdf4;color:#059669;"><i class="ti ti-users"></i></div>
        <div><p class="man-sec-title">Gestión de Usuarios</p><p class="man-sec-sub">Cuentas, roles y accesos al sistema</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">Desde este módulo el administrador puede crear, editar y desactivar cuentas del sistema. Existen tres roles: <span class="man-badge mb-blue">Administrador</span> <span class="man-badge mb-green">Tutor</span> <span class="man-badge mb-amber">Pasante</span></p>
        <div class="man-h3"><i class="ti ti-user-plus"></i> Crear un nuevo usuario</div>
        <div class="man-steps">
            <div class="man-step"><p>Ve a <strong>Usuarios</strong> en el menú lateral.</p></div>
            <div class="man-step"><p>Haz clic en el botón <strong>+ Nuevo Usuario</strong>.</p></div>
            <div class="man-step"><p>Completa: cédula, nombre, apellido, correo, rol y contraseña temporal.</p></div>
            <div class="man-step"><p>Haz clic en <strong>Guardar</strong>. El usuario recibirá un correo si el sistema tiene SMTP configurado.</p></div>
        </div>
        <div class="man-warn"><i class="ti ti-alert-triangle"></i><p>La cédula y el correo deben ser únicos en el sistema. Si ya existen, verás un mensaje de error en rojo.</p></div>
        <div class="man-h3"><i class="ti ti-lock"></i> Roles y permisos</div>
        <table class="man-table">
            <thead><tr><th>Rol</th><th>Acceso</th></tr></thead>
            <tbody>
                <tr><td><span class="man-badge mb-blue">Administrador</span></td><td>Acceso total al sistema: usuarios, pasantes, períodos, reportes, configuración</td></tr>
                <tr><td><span class="man-badge mb-green">Tutor</span></td><td>Asistencias, evaluaciones, sus pasantes asignados y reportes básicos</td></tr>
                <tr><td><span class="man-badge mb-amber">Pasante</span></td><td>Solo su perfil, asistencia personal, evaluaciones recibidas y constancia</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ PERÍODOS ═══ -->
<div class="man-sec" id="sec-periodos" data-keywords="período cohorte académico crear activo planificado cerrar activar pasantes asignar">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#fdf4ff;color:#7c3aed;"><i class="ti ti-calendar-month"></i></div>
        <div><p class="man-sec-title">Períodos Académicos</p><p class="man-sec-sub">Gestión de cohortes y ciclos de pasantía</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">Los períodos agrupan a los pasantes en cohortes. Cada período tiene un ciclo de vida: <span class="man-badge mb-amber">Planificado</span> → <span class="man-badge mb-green">Activo</span> → <span class="man-badge mb-slate">Cerrado</span></p>
        <div class="man-h3"><i class="ti ti-plus"></i> Crear un período</div>
        <div class="man-steps">
            <div class="man-step"><p>Haz clic en <strong>+ Nuevo Período</strong>.</p></div>
            <div class="man-step"><p>Selecciona el tipo: <strong>Regular</strong> (9 meses) o <strong>Corto</strong> (3 meses / Actividades Extras).</p></div>
            <div class="man-step"><p>Ingresa nombre y fecha de inicio (la fecha de fin se calcula automáticamente).</p></div>
            <div class="man-step"><p>Elige el estado inicial: <strong>Planificado</strong> o <strong>Activo</strong>.</p></div>
        </div>
        <div class="man-warn"><i class="ti ti-alert-triangle"></i><p>Solo puede existir un período Activo por tipo al mismo tiempo. Activar uno cerrará automáticamente el anterior del mismo tipo.</p></div>
        <div class="man-h3"><i class="ti ti-lock"></i> Cerrar un período</div>
        <p class="man-p">Al cerrar un período se marcan todos los pasantes como <strong>Finalizados</strong>. Solo se puede cerrar desde la vista de detalle del período.</p>
        <div class="man-h3"><i class="ti ti-file-certificate"></i> Constancia de culminación</div>
        <p class="man-p">El botón <strong>Constancia</strong> en la tarjeta de cada pasante genera el PDF oficial. Solo está disponible cuando el período está <span class="man-badge mb-slate">Cerrado</span> y el pasante tiene el 100% de sus horas.</p>
    </div>
</div>

<!-- ═══ ASIGNACIONES ═══ -->
<div class="man-sec" id="sec-asignaciones" data-keywords="asignaciones tutor departamento pasante asignar vincular">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#f5f3ff;color:#7c3aed;"><i class="ti ti-link"></i></div>
        <div><p class="man-sec-title">Asignaciones</p><p class="man-sec-sub">Vincular pasantes con tutores y departamentos</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">Las asignaciones definen la relación Pasante ↔ Tutor ↔ Departamento. Un pasante debe estar asignado para que su tutor pueda verlo.</p>
        <div class="man-steps">
            <div class="man-step"><p>Ve a <strong>Asignaciones</strong>.</p></div>
            <div class="man-step"><p>Usa el buscador para encontrar al pasante.</p></div>
            <div class="man-step"><p>Selecciona el <strong>tutor</strong> y el <strong>departamento</strong> destino.</p></div>
            <div class="man-step"><p>Guarda. El tutor verá al pasante en su módulo desde ese momento.</p></div>
        </div>
        <div class="man-warn"><i class="ti ti-alert-triangle"></i><p>Un pasante sin asignación no aparece en el listado de ningún tutor, aunque sí exista en el sistema.</p></div>
    </div>
</div>

<!-- ═══ EVALUACIONES ═══ -->
<div class="man-sec" id="sec-evaluaciones" data-keywords="evaluaciones calificar rendimiento planilla ISP ítems criterios">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#fdf2f8;color:#ec4899;"><i class="ti ti-star"></i></div>
        <div><p class="man-sec-title">Evaluaciones</p><p class="man-sec-sub">Planilla institucional de 14 criterios ISP</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">El módulo de evaluaciones usa la planilla oficial del ISP Bolívar con 14 criterios de desempeño calificados del 1 al 5.</p>
        <div class="man-steps">
            <div class="man-step"><p>Ve a <strong>Evaluaciones</strong>.</p></div>
            <div class="man-step"><p>Selecciona al pasante a evaluar.</p></div>
            <div class="man-step"><p>Completa los 14 ítems de la planilla (puntualidad, responsabilidad, etc.).</p></div>
            <div class="man-step"><p>Guarda. La puntuación total se calcula automáticamente y queda visible en la ficha del pasante.</p></div>
        </div>
        <div class="man-tip"><i class="ti ti-info-circle"></i><p>La planilla PDF puede generarse desde el módulo <strong>Reportes → Evaluaciones</strong>.</p></div>
    </div>
</div>

<!-- ═══ REPORTES ═══ -->
<div class="man-sec" id="sec-reportes" data-keywords="reportes PDF excel exportar imprimir constancia evaluación asistencia nómina kardex">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#fef2f2;color:#dc2626;"><i class="ti ti-printer"></i></div>
        <div><p class="man-sec-title">Centro de Reportes</p><p class="man-sec-sub">Generación de documentos PDF y Excel</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">El centro de reportes genera todos los documentos oficiales del ISP. Los reportes se abren en una nueva pestaña para previsualización antes de imprimir.</p>
        <table class="man-table">
            <thead><tr><th>Reporte</th><th>Formato</th><th>Descripción</th></tr></thead>
            <tbody>
                <tr><td>Usuarios</td><td>PDF / Excel</td><td>Listado de personal administrativo y tutores</td></tr>
                <tr><td>Pasantes</td><td>PDF / Excel</td><td>Ficha general e instituciones de procedencia</td></tr>
                <tr><td>Control Asistencia</td><td>PDF / Excel</td><td>Planilla trimestral individual (formato ISP)</td></tr>
                <tr><td>Evaluaciones</td><td>PDF</td><td>Planilla oficial de 14 criterios</td></tr>
                <tr><td>Asignaciones</td><td>PDF / Excel</td><td>Relación Pasante - Tutor - Departamento</td></tr>
                <tr><td>Auditoría</td><td>PDF / Excel</td><td>Historial de acciones del sistema</td></tr>
                <tr><td>Ficha Diaria</td><td>PDF</td><td>Actividad grupal del día por departamento</td></tr>
                <tr><td>Constancias</td><td>PDF</td><td>Cartas de culminación y servicio</td></tr>
            </tbody>
        </table>
        <div class="man-tip"><i class="ti ti-info-circle"></i><p>Al abrir un reporte PDF, el navegador lo muestra en el visor. Usa <span class="man-kbd">Ctrl + P</span> para imprimir o el ícono de descarga del visor.</p></div>
    </div>
</div>

<!-- ═══ ACTIVIDADES EXTRAS ═══ -->
<div class="man-sec" id="sec-actividades" data-keywords="actividades extras servicio comunitario pasantía corta participantes crear">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#f0fdf4;color:#059669;"><i class="ti ti-briefcase"></i></div>
        <div><p class="man-sec-title">Actividades Extras</p><p class="man-sec-sub">Servicio comunitario y pasantías cortas</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">Gestiona actividades externas: brigadas comunitarias, mantenimiento y pasantías de corta duración vinculadas a instituciones externas.</p>
        <div class="man-h3"><i class="ti ti-plus"></i> Crear actividad de Servicio Comunitario</div>
        <div class="man-steps">
            <div class="man-step"><p>Ve a <strong>Act. Extras → Servicio Comunitario</strong>.</p></div>
            <div class="man-step"><p>Clic en <strong>+ Nueva Actividad</strong>.</p></div>
            <div class="man-step"><p>Completa nombre, tipo, institución y fechas.</p></div>
            <div class="man-step"><p>Al guardar, el sistema te lleva automáticamente al detalle donde puedes <strong>agregar participantes</strong>.</p></div>
        </div>
        <div class="man-h3"><i class="ti ti-building-bank"></i> Instituciones</div>
        <p class="man-p">Puedes gestionar el catálogo de instituciones externas desde <strong>Act. Extras → Instituciones</strong>. Estas aparecen como opciones al crear actividades o registrar pasantes.</p>
    </div>
</div>

<!-- ═══ ANALÍTICAS ═══ -->
<div class="man-sec" id="sec-analiticas" data-keywords="analíticas gráficas estadísticas tendencias dashboard charts">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#eff6ff;color:#2563eb;"><i class="ti ti-chart-dots"></i></div>
        <div><p class="man-sec-title">Analíticas</p><p class="man-sec-sub">Visualización de tendencias y estadísticas</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">El módulo de analíticas presenta gráficas interactivas sobre el comportamiento del sistema: asistencia por departamento, evolución de horas acumuladas, distribución por institución, entre otros.</p>
        <div class="man-tip"><i class="ti ti-info-circle"></i><p>Pasa el cursor sobre las gráficas para ver valores exactos. Usa los filtros de período para comparar cohortes.</p></div>
    </div>
</div>

<!-- ═══ RESPALDOS ═══ -->
<div class="man-sec" id="sec-respaldos" data-keywords="respaldo backup restaurar base de datos exportar SQL archivo">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#f1f5f9;color:#475569;"><i class="ti ti-database"></i></div>
        <div><p class="man-sec-title">Respaldos</p><p class="man-sec-sub">Copias de seguridad de la base de datos</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">El módulo de respaldos permite crear y restaurar copias de seguridad completas de la base de datos en formato SQL.</p>
        <div class="man-h3"><i class="ti ti-download"></i> Crear respaldo</div>
        <div class="man-steps">
            <div class="man-step"><p>Ve a <strong>Respaldos</strong>.</p></div>
            <div class="man-step"><p>Haz clic en <strong>Crear Respaldo Ahora</strong>.</p></div>
            <div class="man-step"><p>El archivo .sql se genera y guarda en el servidor. También puedes descargarlo.</p></div>
        </div>
        <div class="man-warn"><i class="ti ti-alert-triangle"></i><p>Al <strong>restaurar</strong> un respaldo se sobrescribirán todos los datos actuales. Esta acción es irreversible. El sistema genera un respaldo previo automático antes de restaurar.</p></div>
    </div>
</div>

<!-- ═══ BITÁCORA ═══ -->
<div class="man-sec" id="sec-bitacora" data-keywords="bitácora auditoría historial acciones logs eventos seguridad">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#f1f5f9;color:#64748b;"><i class="ti ti-clipboard-list"></i></div>
        <div><p class="man-sec-title">Bitácora de Auditoría</p><p class="man-sec-sub">Historial completo de acciones del sistema</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">La bitácora registra automáticamente cada acción relevante: inicios de sesión, creaciones, modificaciones y eliminaciones. Es de solo lectura.</p>
        <table class="man-table">
            <thead><tr><th>Campo</th><th>Descripción</th></tr></thead>
            <tbody>
                <tr><td><strong>Fecha/Hora</strong></td><td>Cuándo ocurrió la acción</td></tr>
                <tr><td><strong>Usuario</strong></td><td>Quién realizó la acción</td></tr>
                <tr><td><strong>Acción</strong></td><td>Tipo de evento (login, crear, editar, eliminar)</td></tr>
                <tr><td><strong>Módulo</strong></td><td>En qué sección del sistema ocurrió</td></tr>
                <tr><td><strong>Detalle</strong></td><td>Descripción específica del cambio</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ CONFIGURACIÓN ═══ -->
<div class="man-sec" id="sec-configuracion" data-keywords="configuración sistema institución nombre SMTP correo ajustes">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#f8fafc;color:#475569;"><i class="ti ti-settings"></i></div>
        <div><p class="man-sec-title">Configuración</p><p class="man-sec-sub">Ajustes generales del sistema</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">Permite personalizar el comportamiento general del SGP: nombre de la institución, logo, configuración de correo SMTP y parámetros de pasantía.</p>
        <table class="man-table">
            <thead><tr><th>Ajuste</th><th>Descripción</th></tr></thead>
            <tbody>
                <tr><td><strong>Nombre institución</strong></td><td>Aparece en PDFs y encabezados del sistema</td></tr>
                <tr><td><strong>Horas por defecto</strong></td><td>Horas meta asignadas automáticamente a nuevos pasantes</td></tr>
                <tr><td><strong>SMTP</strong></td><td>Servidor de correo para notificaciones automáticas</td></tr>
            </tbody>
        </table>
    </div>
</div>
<?php endif; ?>

<?php if ($esPasante): ?>
<!-- ═══ MI ASISTENCIA ═══ -->
<div class="man-sec" id="sec-pasante-asist" data-keywords="mi asistencia ver historial días horas estado">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#ecfdf5;color:#059669;"><i class="ti ti-clock-check"></i></div>
        <div><p class="man-sec-title">Mi Asistencia</p><p class="man-sec-sub">Consulta tu historial de asistencias</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">Puedes ver tu historial de asistencias diarias, el total de horas acumuladas y tu progreso hacia las horas requeridas.</p>
        <div class="man-tip"><i class="ti ti-info-circle"></i><p>Si detectas un error en tu registro de asistencia, contacta a tu tutor o al administrador para que lo corrija.</p></div>
    </div>
</div>

<!-- ═══ MIS EVALUACIONES ═══ -->
<div class="man-sec" id="sec-pasante-eval" data-keywords="evaluaciones calificación nota criterios rendimiento ver">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#fdf2f8;color:#ec4899;"><i class="ti ti-star"></i></div>
        <div><p class="man-sec-title">Mis Evaluaciones</p><p class="man-sec-sub">Consulta las evaluaciones recibidas</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">Aquí verás las evaluaciones que tu tutor ha completado para ti. Cada evaluación muestra los 14 criterios con su puntuación y la calificación global.</p>
    </div>
</div>

<!-- ═══ MI CONSTANCIA ═══ -->
<div class="man-sec" id="sec-pasante-const" data-keywords="constancia carta culminación PDF descargar horas completadas">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#f0fdf4;color:#059669;"><i class="ti ti-file-certificate"></i></div>
        <div><p class="man-sec-title">Mi Constancia</p><p class="man-sec-sub">Carta oficial de culminación de pasantías</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">Tu constancia de culminación se habilita automáticamente cuando el período académico ha sido <strong>Cerrado</strong> y has completado el <strong>100%</strong> de tus horas requeridas.</p>
        <div class="man-warn"><i class="ti ti-alert-triangle"></i><p>Si el período aún está activo o no has completado tus horas, el documento no estará disponible. Consulta con el administrador.</p></div>
    </div>
</div>
<?php endif; ?>

<!-- ═══ ÍCONOS DE AYUDA ═══ -->
<div class="man-sec" id="sec-tooltips" data-keywords="tooltips ayuda iconos formularios ayuda en linea requerimientos campo">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#eff6ff;color:#2563eb;"><i class="ti ti-help-circle"></i></div>
        <div><p class="man-sec-title">Íconos de Ayuda en Formularios</p><p class="man-sec-sub">Cómo interpretar los indicadores de cada campo</p></div>
    </div>
    <div class="man-sec-body">
        <p class="man-p">En los formularios del sistema verás íconos <strong style="background:#dbeafe;color:#1e40af;padding:1px 7px;border-radius:50%;font-size:.75rem;">?</strong> junto a ciertos campos. Pasa el cursor sobre ellos para ver ayuda contextual.</p>
        <table class="man-table">
            <thead><tr><th>Indicador</th><th>Significado</th></tr></thead>
            <tbody>
                <tr><td><span style="background:#e0e7ff;color:#4338ca;border-radius:50%;padding:1px 6px;font-size:.75rem;font-weight:800;">?</span></td><td>Información sobre el formato requerido del campo</td></tr>
                <tr><td><span style="color:#059669;font-weight:700;">✔ Disponible</span></td><td>El valor ingresado no existe en el sistema (correo/cédula únicos)</td></tr>
                <tr><td><span style="color:#dc2626;font-weight:700;">✖ Ya registrado</span></td><td>El valor ya existe — debes usar otro</td></tr>
                <tr><td><span style="color:#d97706;font-weight:700;">⏳ Verificando…</span></td><td>El sistema está comprobando el valor en tiempo real</td></tr>
            </tbody>
        </table>
    </div>
</div>

<!-- ═══ ATAJOS Y ACCESOS ═══ -->
<div class="man-sec" id="sec-accesos" data-keywords="atajos teclado keyboard escape cerrar modal accesos rápidos">
    <div class="man-sec-hd">
        <div class="man-sec-icon" style="background:#f1f5f9;color:#475569;"><i class="ti ti-keyboard"></i></div>
        <div><p class="man-sec-title">Atajos y Accesos Rápidos</p><p class="man-sec-sub">Gestos y teclas que agilizan el uso del sistema</p></div>
    </div>
    <div class="man-sec-body">
        <table class="man-table">
            <thead><tr><th>Tecla / Acción</th><th>Efecto</th></tr></thead>
            <tbody>
                <tr><td><span class="man-kbd">Esc</span></td><td>Cierra el modal o panel abierto</td></tr>
                <tr><td><span class="man-kbd">Ctrl + P</span></td><td>Imprime el PDF abierto en el visor</td></tr>
                <tr><td>Clic fuera del modal</td><td>Cierra el modal actual</td></tr>
                <tr><td>Hover sobre <span style="background:#e0e7ff;color:#4338ca;border-radius:50%;padding:1px 6px;font-size:.72rem;font-weight:800;">?</span></td><td>Muestra la ayuda del campo</td></tr>
            </tbody>
        </table>
        <div class="man-tip"><i class="ti ti-device-mobile"></i><p>En dispositivos móviles, el menú lateral se convierte en un <strong>dock inferior</strong> estilo iOS. Toca el botón <strong>Más</strong> para acceder a secciones adicionales.</p></div>
    </div>
</div>

<!-- ── NAVEGACIÓN ──────────────────────────────────────────── -->
<div class="man-nav" id="manNav">
    <button class="man-nav-btn" id="manNavPrev" onclick="navPrev()">
        <i class="ti ti-arrow-left"></i>
        <span id="manNavPrevLabel">Anterior</span>
    </button>
    <span class="man-nav-info" id="manNavInfo"></span>
    <button class="man-nav-btn" id="manNavNext" onclick="navNext()">
        <span id="manNavNextLabel">Siguiente</span>
        <i class="ti ti-arrow-right"></i>
    </button>
</div>

</div><!-- /man-panel -->
</div><!-- /man-layout -->
</div><!-- /man-wrap -->

<script>
(function(){
    const panel  = document.getElementById('manPanel');
    const nav    = document.getElementById('manNav');
    const noRes  = document.getElementById('manNoResults');
    const tocDiv = document.getElementById('manToc');

    // Construir lista de secciones visibles en el DOM
    const SECS = Array.from(document.querySelectorAll('#manPanel .man-sec')).map(s => s.id);
    let current = 0;

    function mostrarSeccion(id) {
        const idx = SECS.indexOf(id);
        if (idx === -1) return;
        current = idx;

        document.querySelectorAll('#manPanel .man-sec').forEach(s => s.classList.remove('active'));
        const el = document.getElementById(id);
        if (el) el.classList.add('active');

        // TOC activo
        document.querySelectorAll('.toc-link').forEach(l => {
            l.classList.toggle('activo', l.getAttribute('data-sec') === id);
        });

        actualizarNav();
    }

    function actualizarNav() {
        const prev  = document.getElementById('manNavPrev');
        const next  = document.getElementById('manNavNext');
        const info  = document.getElementById('manNavInfo');
        const pLbl  = document.getElementById('manNavPrevLabel');
        const nLbl  = document.getElementById('manNavNextLabel');

        prev.disabled = current <= 0;
        next.disabled = current >= SECS.length - 1;

        info.textContent = (current + 1) + ' / ' + SECS.length;

        if (current > 0) {
            const prevTitle = document.getElementById(SECS[current-1])?.querySelector('.man-sec-title')?.textContent || 'Anterior';
            pLbl.textContent = prevTitle.length > 22 ? prevTitle.substring(0,20)+'…' : prevTitle;
        }
        if (current < SECS.length - 1) {
            const nextTitle = document.getElementById(SECS[current+1])?.querySelector('.man-sec-title')?.textContent || 'Siguiente';
            nLbl.textContent = nextTitle.length > 22 ? nextTitle.substring(0,20)+'…' : nextTitle;
        }
    }

    function navPrev() { if (current > 0) mostrarSeccion(SECS[current-1]); }
    function navNext() { if (current < SECS.length-1) mostrarSeccion(SECS[current+1]); }

    // Exponer globalmente
    window.mostrarSeccion = mostrarSeccion;
    window.navPrev = navPrev;
    window.navNext = navNext;

    // Búsqueda
    window.filtrarManual = function(q) {
        const term  = q.toLowerCase().trim();
        const clear = document.getElementById('manSearchClear');
        clear.style.display = term ? 'block' : 'none';

        if (!term) {
            // Limpiar inline styles que la búsqueda haya aplicado antes de restaurar clases CSS
            document.querySelectorAll('#manPanel .man-sec').forEach(s => { s.style.display = ''; });
            panel.classList.remove('search-mode');
            nav.style.display = '';
            tocDiv.style.display = '';
            noRes.style.display = 'none';
            mostrarSeccion(SECS[current]);
            return;
        }

        // Modo búsqueda: ocultar/mostrar vía inline style (mayor especificidad que las clases CSS)
        panel.classList.add('search-mode');
        nav.style.display = 'none';
        tocDiv.style.display = 'none';

        let found = 0;
        document.querySelectorAll('#manPanel .man-sec').forEach(s => {
            const kw   = (s.dataset.keywords || '').toLowerCase();
            const txt  = s.textContent.toLowerCase();
            const match = kw.includes(term) || txt.includes(term);
            s.style.display = match ? 'block' : 'none';
            if (match) found++;
        });
        noRes.style.display = found === 0 ? 'block' : 'none';
    };

    window.limpiarBusqueda = function() {
        document.getElementById('manSearch').value = '';
        filtrarManual('');
    };

    window.imprimirManual = function() {
        // Antes de imprimir, asegura que todas las secciones sean visibles
        const orig = document.querySelectorAll('#manPanel .man-sec');
        orig.forEach(s => s.style.display = 'block');
        window.print();
        // Restaurar después de imprimir
        setTimeout(() => {
            orig.forEach(s => s.style.display = '');
            panel.classList.remove('search-mode');
            mostrarSeccion(SECS[current]);
        }, 500);
    };

    // Init: mostrar primera sección
    if (SECS.length > 0) mostrarSeccion(SECS[0]);
})();
</script>
