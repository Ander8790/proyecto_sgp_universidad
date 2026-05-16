<?php
/**
 * Vista: Dashboard del SuperAdmin — Sala de Control del Director
 */

// Preparar datos para gráficas (PHP → JS)
$actividadSemana      = $data['actividad_semana']      ?? [];
$distribucionAcciones = $data['distribucion_acciones'] ?? [];
$actividadPorUsuario  = $data['actividad_por_usuario'] ?? [];

// Traducciones de acciones (para donut)
$accionesES = [
    'LOGIN' => 'Inicio Sesión', 'LOGOUT' => 'Cierre Sesión',
    'RESET_PASSWORD' => 'Reset Contraseña', 'RESET_PIN' => 'Reset PIN',
    'CREATE_USER' => 'Crear Usuario', 'UPDATE_USER' => 'Modificar Usuario',
    'CREATE_PASANTE' => 'Crear Pasante', 'UPDATE_PASANTE' => 'Modificar Pasante',
    'MARCAR_ASISTENCIA_KIOSCO' => 'Asistencia Kiosco',
    'CREATE_EVALUACION' => 'Crear Evaluación', 'UPDATE_EVALUACION' => 'Editar Evaluación',
    'PERMISO_MODIFICADO' => 'Cambio Permiso', 'AUDIT_PURGE' => 'Purga Bitácora',
];

// Datos para gráfica líneas (últimos 7 días)
$diasLabels = [];
$diasData   = [];
$diasMap    = [];
foreach ($actividadSemana as $row) {
    $diasMap[$row->dia] = (int)$row->total;
}
for ($i = 6; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $diasLabels[] = date('d/m', strtotime($d));
    $diasData[]   = $diasMap[$d] ?? 0;
}

// Datos para gráfica líneas (últimos 30 días)
$actividadMes = $data['actividad_mes'] ?? [];
$mesLabels = [];
$mesData   = [];
$mesMap    = [];
foreach ($actividadMes as $row) {
    $mesMap[$row->dia] = (int)$row->total;
}
for ($i = 29; $i >= 0; $i--) {
    $d = date('Y-m-d', strtotime("-{$i} days"));
    $mesLabels[] = date('d/m', strtotime($d));
    $mesData[]   = $mesMap[$d] ?? 0;
}

// Datos para donut
$donutLabels = [];
$donutData   = [];
foreach ($distribucionAcciones as $row) {
    $donutLabels[] = $accionesES[strtoupper($row->accion)] ?? $row->accion;
    $donutData[]   = (int)$row->total;
}
?>

<style>
.sa-dash-grid-top { display:grid; grid-template-columns:repeat(5,1fr); gap:18px; margin-bottom:24px; }
.sa-kpi { background:white; border-radius:16px; padding:20px; box-shadow:0 2px 10px rgba(0,0,0,0.05); border-left:4px solid var(--kc); transition:all .25s; }
.sa-kpi:hover { transform:translateY(-3px); box-shadow:0 10px 22px rgba(0,0,0,0.09); }
.sa-kpi-label { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#64748b; margin-bottom:8px; }
.sa-kpi-val   { font-size:2.2rem; font-weight:800; color:var(--kc); line-height:1; }
.sa-kpi-sub   { font-size:.75rem; color:#94a3b8; margin-top:4px; }

.sa-charts-row { display:grid; grid-template-columns:2fr 1fr; gap:20px; margin-bottom:24px; }
.sa-card { background:white; border-radius:16px; padding:22px; box-shadow:0 2px 10px rgba(0,0,0,0.05); }
.sa-card-title { font-size:1rem; font-weight:700; color:#1e293b; margin-bottom:18px; display:flex; align-items:center; gap:8px; }

.sa-bottom-row { display:grid; grid-template-columns:1fr 1fr; gap:20px; align-items:start; }

/* Tabla de supervisión */
.sup-table { width:100%; border-collapse:collapse; }
.sup-table th { font-size:.7rem; font-weight:700; text-transform:uppercase; letter-spacing:.5px; color:#64748b; padding:8px 14px; border-bottom:2px solid #f1f5f9; text-align:left; }
.sup-table td { padding:12px 14px; border-bottom:1px solid #f8fafc; font-size:.84rem; color:#334155; vertical-align:middle; }
.sup-table tr:hover td { background:#f8fafc; }
.rol-badge-small { font-size:.68rem; font-weight:700; padding:3px 10px; border-radius:20px; }
.rol-badge-small.admin { background:#eff6ff; color:#2563eb; }
.rol-badge-small.tutor { background:#ecfdf5; color:#059669; }
.bar-mini { height:6px; border-radius:3px; background:#e2e8f0; overflow:hidden; margin-top:4px; }
.bar-mini-fill { height:100%; border-radius:3px; background:linear-gradient(90deg,#3b82f6,#8b5cf6); }

/* Links de acceso rápido */
.quick-link { display:flex; align-items:center; gap:14px; padding:14px 16px; border:1.5px solid #e2e8f0; border-radius:12px; text-decoration:none; transition:all .2s; margin-bottom:12px; }
.quick-link:last-child { margin-bottom:0; }
.quick-link-icon { width:42px; height:42px; border-radius:10px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
.quick-link-text h4 { margin:0; font-size:.9rem; color:#1e293b; font-weight:700; }
.quick-link-text p  { margin:2px 0 0; font-size:.73rem; color:#64748b; }

/* Botones toggle de gráfica y paginación */
.btn-chart-toggle { background:transparent; border:1px solid #e2e8f0; color:#64748b; font-size:.75rem; font-weight:700; padding:4px 10px; border-radius:6px; cursor:pointer; transition:all .2s; }
.btn-chart-toggle:hover { background:#f8fafc; }
.btn-chart-toggle.active { background:#eff6ff; border-color:#3b82f6; color:#2563eb; }

.pagination-controls { display:flex; align-items:center; justify-content:space-between; margin-top:16px; font-size:.8rem; color:#64748b; }
.btn-page { background:white; border:1px solid #e2e8f0; border-radius:6px; padding:4px 12px; cursor:pointer; font-weight:600; color:#475569; transition:all .2s; }
.btn-page:hover:not(:disabled) { background:#f8fafc; border-color:#cbd5e1; }
.btn-page:disabled { opacity:.5; cursor:not-allowed; }

/* iOS Timeline Feed */
.ios-timeline { position: relative; padding-left: 24px; margin-top: 10px; max-height: 380px; overflow-y: auto; padding-right: 4px; }
.ios-timeline::-webkit-scrollbar { width: 4px; }
.ios-timeline::-webkit-scrollbar-track { background: transparent; }
.ios-timeline::-webkit-scrollbar-thumb { background: #e2e8f0; border-radius: 4px; }
.ios-timeline::before { content: ''; position: absolute; left: 7px; top: 6px; bottom: 0; width: 2px; background: #e2e8f0; border-radius: 2px; }
.ios-feed-item { position: relative; padding-bottom: 20px; }
.ios-feed-item:last-child { padding-bottom: 0; }
.ios-dot { position: absolute; left: -24px; top: 3px; width: 16px; height: 16px; border-radius: 50%; border: 3px solid white; box-shadow: 0 0 0 1px rgba(0,0,0,0.06); z-index: 2; }
.ios-feed-content { background: white; border-radius: 12px; padding: 0; }
.ios-feed-header { display: flex; justify-content: space-between; align-items: center; margin-bottom: 6px; }
.ios-feed-time { font-size: .7rem; font-weight: 600; color: #94a3b8; }
.ios-feed-user { font-size: .78rem; font-weight: 600; color: #475569; display: flex; align-items: center; gap: 6px; }
.ios-feed-user i { font-size: 1.1rem; color: #94a3b8; }
.ios-badge { padding: 3px 10px; border-radius: 20px; font-size: 0.68rem; font-weight: 700; color: white; letter-spacing: 0.2px; box-shadow: 0 2px 4px rgba(0,0,0,0.05); }

@media (max-width:1100px) {
    .sa-dash-grid-top { grid-template-columns:repeat(3,1fr); }
    .sa-charts-row    { grid-template-columns:1fr; }
    .sa-bottom-row    { grid-template-columns:1fr; }
}
@media (max-width:640px) {
    .sa-dash-grid-top { grid-template-columns:repeat(2,1fr); gap:10px; }
    .sa-kpi           { padding:14px 16px; }
    .sa-kpi-val       { font-size:1.7rem; }
    .sa-kpi-label     { font-size:.64rem; letter-spacing:.3px; }
    .sa-kpi-sub       { font-size:.68rem; }
    /* 5ª KPI — span 2 cols, layout horizontal tipo "resumen" */
    .sa-dash-grid-top .sa-kpi:last-child {
        grid-column: 1 / -1;
        flex-direction: row;
        align-items: center;
        justify-content: space-between;
        gap: 16px;
        border-left: none;
        border-top: 4px solid var(--kc);
        padding: 12px 18px;
    }
    .sa-dash-grid-top .sa-kpi:last-child .sa-kpi-val { font-size: 2rem; }
    .sa-dash-grid-top .sa-kpi:last-child > div:first-child { flex: 1; }
    .pasantes-banner  { padding:18px 20px !important; }
    .pasantes-banner > div:last-child { display:none !important; }
    .sa-card          { padding:16px 14px; }
    .sa-card-title    { font-size:.88rem; margin-bottom:12px; }
    .quick-link       { padding:11px 12px; gap:10px; margin-bottom:8px; }
    .quick-link-icon  { width:36px; height:36px; font-size:1.1rem; }
    /* Tabla supervisión: ocultar Acciones y Última Actividad en móvil */
    .sup-table th:nth-child(3),
    .sup-table td:nth-child(3),
    .sup-table th:nth-child(4),
    .sup-table td:nth-child(4) { display:none; }
    /* Feed reciente: badge + hora en fila con wrap */
    .ios-feed-header { flex-wrap:wrap; gap:4px; }
    .ios-feed-time   { width:100%; margin-top:2px; }
    /* quick-link descripción: ocultar en muy pequeño para condensar */
    .quick-link-text p { display:none; }
    /* Tabla Usuario: email en fuente menor */
    .sup-table td:first-child div > div:last-child { font-size:.63rem; }
}
</style>

<div class="dashboard-container" style="width:100%;max-width:100%;padding:0;">

    <!-- BANNER -->
    <div class="pasantes-banner" style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:30px 40px;margin-bottom:24px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-40px;right:-40px;width:220px;height:220px;background:rgba(255,255,255,0.04);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:14px;">
                <i class="ti ti-shield-cog" style="font-size:32px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.75rem;font-weight:700;margin:0;">Sala de Control — SuperAdmin</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:.88rem;display:flex;align-items:center;gap:10px;">
                    Supervisión global del sistema y actividad de usuarios
                    <span style="background:linear-gradient(135deg,#7c3aed,#a855f7);border-radius:50px;padding:4px 14px;color:white;font-weight:700;font-size:.75rem;letter-spacing:.5px;">SUPERADMIN</span>
                </p>
            </div>
        </div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="text-align:right;">
                <div style="font-size:.75rem;color:rgba(255,255,255,0.6);text-transform:uppercase;letter-spacing:.5px;">Hoy</div>
                <div style="font-size:1.4rem;font-weight:800;color:white;"><?= $data['kpis_audit']['hoy'] ?? 0 ?> eventos</div>
            </div>
        </div>
    </div>

    <!-- KPIs PRINCIPALES -->
    <div class="sa-dash-grid-top">
        <?php
        $kpis = [
            ['label'=>'Usuarios Activos',  'val'=>$data['total_activos'],            'color'=>'#3b82f6', 'icon'=>'ti-users',        'sub'=>'Cuentas habilitadas'],
            ['label'=>'Administradores',   'val'=>$data['stats_roles']['Administrador'], 'color'=>'#8b5cf6','icon'=>'ti-user-shield', 'sub'=>'Staff del sistema'],
            ['label'=>'Tutores',           'val'=>$data['stats_roles']['Tutor'],      'color'=>'#10b981', 'icon'=>'ti-user-check',   'sub'=>'Guías académicos'],
            ['label'=>'Pasantes',          'val'=>$data['stats_roles']['Pasante'],    'color'=>'#f59e0b', 'icon'=>'ti-users-group',  'sub'=>'En formación'],
            ['label'=>'Eventos Hoy',       'val'=>$data['kpis_audit']['hoy'] ?? 0,   'color'=>'#ef4444', 'icon'=>'ti-activity',     'sub'=>'Acciones registradas', 'onclick'=>'abrirModalEventosHoy()'],
        ];
        foreach ($kpis as $k):
            $esClickable = !empty($k['onclick']);
        ?>
        <div class="sa-kpi" style="--kc:<?= $k['color'] ?>;<?= $esClickable ? 'cursor:pointer;transition:transform .15s,box-shadow .15s;' : '' ?>"
             <?= $esClickable ? 'onclick="' . htmlspecialchars($k['onclick']) . '" title="Ver eventos de hoy"' : '' ?>
             <?= $esClickable ? 'onmouseover="this.style.transform=\'translateY(-2px)\';this.style.boxShadow=\'0 8px 24px rgba(239,68,68,.18)\'"' : '' ?>
             <?= $esClickable ? 'onmouseout="this.style.transform=\'\';this.style.boxShadow=\'\'"' : '' ?>>
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <div class="sa-kpi-label"><?= $k['label'] ?></div>
                <i class="ti <?= $k['icon'] ?>" style="color:<?= $k['color'] ?>;font-size:1.3rem;opacity:.6;"></i>
            </div>
            <div class="sa-kpi-val"><?= $k['val'] ?></div>
            <div class="sa-kpi-sub"><?= $k['sub'] ?><?= $esClickable ? ' <i class="ti ti-arrow-up-right" style="font-size:.65rem;opacity:.5;"></i>' : '' ?></div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- GRÁFICAS -->
    <div class="sa-charts-row">
        <!-- Actividad del Sistema -->
        <div class="sa-card">
            <div class="sa-card-title" style="justify-content:space-between;display:flex;">
                <div><i class="ti ti-chart-line" style="color:#3b82f6;"></i> Actividad del Sistema</div>
                <div style="display:flex;gap:6px;">
                    <button class="btn-chart-toggle active" onclick="toggleChart(this, '7')">7 Días</button>
                    <button class="btn-chart-toggle" onclick="toggleChart(this, '30')">30 Días</button>
                </div>
            </div>
            <div id="chartLinea"></div>
        </div>

        <!-- Distribución donut -->
        <div class="sa-card" style="min-height: 340px; display: flex; flex-direction: column;">
            <div class="sa-card-title">
                <i class="ti ti-chart-donut" style="color:#8b5cf6;"></i>
                Distribución de Acciones (30 días)
            </div>
            <div id="chartDonut" style="flex:1; display:flex; align-items:center;"></div>
        </div>
    </div>

    <!-- PARTE INFERIOR -->
    <div class="sa-bottom-row">

        <!-- Supervisión: Actividad por usuario -->
        <div class="sa-card">
            <div class="sa-card-title">
                <i class="ti ti-eye" style="color:#ef4444;"></i>
                Actividad por Usuario — Últimos 30 días
                <a href="<?= URLROOT ?>/bitacora" style="margin-left:auto;font-size:.78rem;color:#2563eb;font-weight:600;text-decoration:none;">Ver bitácora completa →</a>
            </div>
            <?php if (empty($actividadPorUsuario)): ?>
                <p style="color:#94a3b8;font-size:.88rem;text-align:center;padding:24px;">Sin actividad registrada.</p>
            <?php else:
                $maxAcciones = max(array_column($actividadPorUsuario, 'total_acciones'));
            ?>
            <div style="max-height:350px;overflow-y:auto;overflow-x:auto;border-radius:10px;border:1px solid #f1f5f9;">
            <table class="sup-table">
                <thead>
                    <tr>
                        <th>Usuario</th>
                        <th>Rol</th>
                        <th>Acciones</th>
                        <th>Última actividad</th>
                    </tr>
                </thead>
                <tbody id="userTableBody">
                <?php foreach ($actividadPorUsuario as $row):
                    $nombre = trim($row->nombre) ?: $row->correo;
                    $inicial = strtoupper(substr($nombre, 0, 1));
                    $isAdmin = strtolower($row->rol) === 'administrador';
                    $pct = $maxAcciones > 0 ? round(($row->total_acciones / $maxAcciones) * 100) : 0;
                    $ultimaAccion = $row->ultima_accion ? date('d/m/Y H:i', strtotime($row->ultima_accion)) : '—';
                ?>
                <tr class="user-row">
                    <td>
                        <div style="display:flex;align-items:center;gap:10px;">
                            <div style="width:32px;height:32px;border-radius:50%;background:linear-gradient(135deg,#1e3a8a,#3b82f6);display:flex;align-items:center;justify-content:center;font-weight:800;color:white;font-size:.8rem;flex-shrink:0;"><?= $inicial ?></div>
                            <div>
                                <div style="font-weight:600;color:#1e293b;font-size:.84rem;"><?= htmlspecialchars($nombre) ?></div>
                                <div style="font-size:.7rem;color:#94a3b8;"><?= htmlspecialchars($row->correo) ?></div>
                            </div>
                        </div>
                    </td>
                    <td><span class="rol-badge-small <?= $isAdmin ? 'admin' : 'tutor' ?>"><?= htmlspecialchars($row->rol) ?></span></td>
                    <td>
                        <div style="font-weight:700;color:#334155;"><?= $row->total_acciones ?></div>
                        <div class="bar-mini"><div class="bar-mini-fill" style="width:<?= $pct ?>%;"></div></div>
                    </td>
                    <td style="font-size:.78rem;color:#64748b;"><?= $ultimaAccion ?></td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
            </div>

            <div class="pagination-controls">
                <span id="userPageInfo">Mostrando 1 - 8 de X</span>
                <div style="display:flex;gap:6px;">
                    <button class="btn-page" id="btnUserPrev" onclick="changeUserPage(-1)"><i class="ti ti-chevron-left"></i></button>
                    <button class="btn-page" id="btnUserNext" onclick="changeUserPage(1)"><i class="ti ti-chevron-right"></i></button>
                </div>
            </div>
            <?php endif; ?>
        </div>

        <!-- Accesos rápidos -->
        <div style="display:flex;flex-direction:column;gap:20px;">

            <!-- Accesos directos -->
            <div class="sa-card">
                <div class="sa-card-title"><i class="ti ti-apps" style="color:#2563eb;"></i> Módulos Rápidos</div>
                <a href="<?= URLROOT ?>/superadmin/permisos" class="quick-link"
                   onmouseover="this.style.borderColor='#a855f7';this.style.background='#faf5ff'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='transparent'">
                    <div class="quick-link-icon" style="background:#f5f3ff;color:#7c3aed;"><i class="ti ti-shield-lock"></i></div>
                    <div class="quick-link-text"><h4>Gestión de Permisos</h4><p>Control granular de accesos RBAC</p></div>
                </a>
                <a href="<?= URLROOT ?>/bitacora" class="quick-link"
                   onmouseover="this.style.borderColor='#ef4444';this.style.background='#fef2f2'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='transparent'">
                    <div class="quick-link-icon" style="background:#fef2f2;color:#ef4444;"><i class="ti ti-file-analytics"></i></div>
                    <div class="quick-link-text"><h4>Bitácora de Auditoría</h4><p>Historial completo de actividad</p></div>
                </a>
                <a href="<?= URLROOT ?>/users" class="quick-link"
                   onmouseover="this.style.borderColor='#3b82f6';this.style.background='#eff6ff'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='transparent'">
                    <div class="quick-link-icon" style="background:#eff6ff;color:#3b82f6;"><i class="ti ti-users"></i></div>
                    <div class="quick-link-text"><h4>Directorio de Usuarios</h4><p>Administrar cuentas del sistema</p></div>
                </a>
                <a href="<?= URLROOT ?>/backup" class="quick-link"
                   onmouseover="this.style.borderColor='#10b981';this.style.background='#ecfdf5'" onmouseout="this.style.borderColor='#e2e8f0';this.style.background='transparent'">
                    <div class="quick-link-icon" style="background:#ecfdf5;color:#10b981;"><i class="ti ti-database"></i></div>
                    <div class="quick-link-text"><h4>Respaldos DB</h4><p>Copias de seguridad del sistema</p></div>
                </a>
            </div>

        </div>
    </div>

</div>

<!-- ══════════════════════════════════════════════════════
     MODAL: Eventos de Hoy (Bitácora)
     ══════════════════════════════════════════════════════ -->
<div id="modal-eventos-hoy" class="sgp-modal-overlay"
     onclick="if(event.target===this)cerrarModalEventosHoy()">
    <div class="sgp-modal" style="width:min(700px,95vw);max-height:88vh;display:flex;flex-direction:column;overflow:hidden;padding:0;">

        <!-- Cabecera -->
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;align-items:center;justify-content:space-between;flex-shrink:0;">
            <div style="display:flex;align-items:center;gap:12px;">
                <div style="width:38px;height:38px;border-radius:10px;background:#fef2f2;display:flex;align-items:center;justify-content:center;">
                    <i class="ti ti-activity" style="color:#ef4444;font-size:1.15rem;"></i>
                </div>
                <div>
                    <div style="font-weight:700;color:#1e293b;font-size:1rem;">Eventos del Día</div>
                    <div id="eventos-hoy-fecha" style="font-size:.73rem;color:#94a3b8;"></div>
                </div>
            </div>
            <button onclick="cerrarModalEventosHoy()"
                    style="border:none;background:none;cursor:pointer;width:32px;height:32px;border-radius:8px;display:flex;align-items:center;justify-content:center;color:#94a3b8;"
                    onmouseover="this.style.background='#f1f5f9'" onmouseout="this.style.background='none'">
                <i class="ti ti-x" style="font-size:1rem;"></i>
            </button>
        </div>

        <!-- Subencabezado -->
        <div id="eventos-hoy-contador" style="padding:12px 24px 0;font-size:.8rem;color:#64748b;flex-shrink:0;"></div>

        <!-- Lista de eventos (scrollable) -->
        <div id="eventos-hoy-lista" style="overflow-y:auto;padding:16px 24px 20px;flex:1;">
            <div style="text-align:center;color:#94a3b8;padding:48px 0;font-size:.9rem;">
                <i class="ti ti-loader-2" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>
                Cargando eventos...
            </div>
        </div>

        <!-- Pie -->
        <div style="padding:14px 24px;border-top:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;flex-shrink:0;">
            <a href="<?= URLROOT ?>/bitacora"
               style="color:#2563eb;font-size:.82rem;font-weight:600;text-decoration:none;display:flex;align-items:center;gap:5px;">
                <i class="ti ti-external-link" style="font-size:.9rem;"></i> Ver bitácora completa
            </a>
            <button onclick="cerrarModalEventosHoy()"
                    style="padding:8px 20px;border-radius:8px;border:1px solid #e2e8f0;background:white;color:#64748b;font-size:.84rem;font-weight:600;cursor:pointer;">
                Cerrar
            </button>
        </div>
    </div>
</div>
<!-- ══════════════════════════════════════════════════════ -->

<?php
$lineasLabels = json_encode($diasLabels);
$lineasData   = json_encode($diasData);
$mesLabelsJ   = json_encode($mesLabels);
$mesDataJ     = json_encode($mesData);
$donutLabelsJ = json_encode($donutLabels);
$donutDataJ   = json_encode($donutData);
?>
<script>
var chartActividad;
var dataSemana = { labels: <?= $lineasLabels ?>, series: <?= $lineasData ?> };
var dataMes    = { labels: <?= $mesLabelsJ ?>, series: <?= $mesDataJ ?> };

// PJAX-safe: si DOMContentLoaded ya disparó (navegación PJAX), corre inmediato
function _sgpInitSuperAdminCharts() {
    if (typeof ApexCharts === 'undefined') return;

    const isMobile = window.innerWidth <= 640;

    // Gráfica de líneas — Actividad
    const elLinea = document.getElementById('chartLinea');
    if (elLinea && !elLinea._apexInited) {
        elLinea._apexInited = true;
        chartActividad = new ApexCharts(elLinea, {
            chart: {
                type: 'area',
                height: isMobile ? 160 : 180,
                toolbar: { show: false },
                responsive: [{ breakpoint: 640, options: { chart: { height: 160 } } }]
            },
            series: [{ name: 'Eventos', data: dataSemana.series }],
            xaxis: { categories: dataSemana.labels, labels: { style: { colors: '#94a3b8', fontSize: '11px' } }, axisBorder: { show: false }, axisTicks: { show: false } },
            yaxis: { labels: { style: { colors: '#94a3b8', fontSize: '11px' } }, min: 0 },
            stroke: { curve: 'smooth', width: 2.5 },
            fill: { type: 'gradient', gradient: { shadeIntensity: 1, opacityFrom: 0.25, opacityTo: 0, stops: [0, 100] } },
            colors: ['#3b82f6'],
            dataLabels: { enabled: false },
            grid: { borderColor: '#f1f5f9', strokeDashArray: 4 },
            tooltip: { theme: 'light' },
        });
        chartActividad.render();
    }

    // Gráfica donut — Distribución de acciones
    const elDonut = document.getElementById('chartDonut');
    const donutData = <?= $donutDataJ ?>;
    if (elDonut && !elDonut._apexInited) {
        elDonut._apexInited = true;
        if (donutData.length > 0) {
            new ApexCharts(elDonut, {
                chart: {
                    type: 'donut',
                    height: isMobile ? 240 : 280,
                    toolbar: { show: false },
                    responsive: [{ breakpoint: 640, options: { chart: { height: 220 }, legend: { position: 'bottom' } } }]
                },
                series: donutData,
                labels: <?= $donutLabelsJ ?>,
                colors: ['#3b82f6','#8b5cf6','#10b981','#f59e0b','#ef4444','#06b6d4'],
                plotOptions: { pie: { donut: { size: '68%', labels: { show: true, total: { show: true, label: 'Total', color: '#64748b', fontSize: '13px' } } } } },
                dataLabels: { enabled: false },
                legend: { position: 'bottom', fontSize: '11.5px', markers: { width: 10, height: 10 }, itemMargin: { horizontal: 8, vertical: 4 } },
                tooltip: { theme: 'light' },
            }).render();
        } else {
            elDonut.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:40px;font-size:.88rem;">Sin datos en los últimos 30 días.</p>';
        }
    }
}

function _sgpInitSuperAdminPagination() {
    initPagination('user-row', 8, 'userPageInfo', 'btnUserPrev', 'btnUserNext', (dir) => userPage += dir);
}

// Patrón PJAX-safe: corre inmediato si DOM ya listo, o espera DOMContentLoaded
if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', function () {
        requestAnimationFrame(_sgpInitSuperAdminCharts);
        _sgpInitSuperAdminPagination();
    });
} else {
    requestAnimationFrame(_sgpInitSuperAdminCharts);
    _sgpInitSuperAdminPagination();
}

// Función para cambiar la data de la gráfica de líneas
window.toggleChart = function(btn, range) {
    document.querySelectorAll('.btn-chart-toggle').forEach(b => b.classList.remove('active'));
    btn.classList.add('active');
    
    if(!chartActividad) return;
    const targetData = range === '30' ? dataMes : dataSemana;
    
    chartActividad.updateOptions({
        xaxis: { categories: targetData.labels }
    });
    chartActividad.updateSeries([{
        data: targetData.series
    }]);
};

// ==========================================
// Lógica de Paginación JS genérica
// ==========================================
var userPage = 1;

function initPagination(rowClass, itemsPerPage, infoId, btnPrevId, btnNextId, pageUpdater) {
    const rows = document.querySelectorAll('.' + rowClass);
    if (!rows.length) return;
    
    const total = rows.length;
    const totalPages = Math.ceil(total / itemsPerPage);
    
    window['render' + rowClass] = function(page) {
        const start = (page - 1) * itemsPerPage;
        const end   = start + itemsPerPage;
        
        rows.forEach((row, i) => {
            row.style.display = (i >= start && i < end) ? '' : 'none';
        });
        
        const shown = Math.min(end, total);
        document.getElementById(infoId).textContent = `Mostrando ${start + 1} - ${shown} de ${total}`;
        document.getElementById(btnPrevId).disabled = page === 1;
        document.getElementById(btnNextId).disabled = page === totalPages;
    };
    
    window['change' + (rowClass === 'user-row' ? 'UserPage' : 'FeedPage')] = function(dir) {
        let currentPage = rowClass === 'user-row' ? userPage : feedPage;
        currentPage += dir;
        if(currentPage < 1) currentPage = 1;
        if(currentPage > totalPages) currentPage = totalPages;
        
        if (rowClass === 'user-row') userPage = currentPage;
        else feedPage = currentPage;
        
        window['render' + rowClass](currentPage);
    };
    
    window['render' + rowClass](1);
}

// ══════════════════════════════════════════════════════
// Modal: Eventos de Hoy
// ══════════════════════════════════════════════════════
var EVENTOS_LABELS = {
    'LOGIN':'Inicio de Sesión','LOGOUT':'Cierre de Sesión',
    'RESET_PASSWORD':'Reset Contraseña','RESET_PIN':'Reset PIN',
    'CREATE_USER':'Usuario Creado','UPDATE_USER':'Usuario Modificado',
    'DELETE_USER_PERMANENT':'Usuario Eliminado',
    'TOGGLE_USER_STATUS':'Cambio de Estado',
    'UPDATE_PROFILE':'Perfil Editado',
    'CREATE_PASANTE':'Pasante Creado','UPDATE_PASANTE':'Pasante Modificado',
    'DELETE_PASANTE':'Pasante Eliminado',
    'MARCAR_ASISTENCIA_KIOSCO':'Asistencia Kiosco',
    'PERMISO_MODIFICADO':'Permiso Modificado',
    'PERMISOS_RESET':'Permisos Restablecidos',
    'CREATE_EVALUACION':'Evaluación Creada',
    'UPDATE_EVALUACION':'Evaluación Editada',
    'DELETE_EVALUACION':'Evaluación Eliminada',
    'EXPORT_CSV':'Exportación CSV',
    'UPDATE_CONFIG':'Configuración Editada',
    'CAMBIO_ESTADO_PASANTE':'Cambio Estado Pasante',
    'MARCAR_ASISTENCIA':'Asistencia Marcada',
    'UPDATE_ASISTENCIA':'Asistencia Modificada',
    'SESSION_MAINTENANCE':'Mant. Sesiones',
    'SYNC_FERIADOS_API':'Sincronización Feriados',
    'MAINTENANCE':'Mantenimiento',
};

function eventoColor(accion) {
    const u = (accion || '').toUpperCase();
    if (u.includes('LOGIN'))       return '#10b981';
    if (u.includes('LOGOUT'))      return '#94a3b8';
    if (u.includes('FAIL'))        return '#f59e0b';
    if (u.includes('DELETE'))      return '#ef4444';
    if (u.includes('ASISTENCIA'))  return '#3b82f6';
    if (u.includes('UPDATE') || u.includes('MODIFICAD') || u.includes('RESET')) return '#8b5cf6';
    if (u.includes('CREATE') || u.includes('REGISTRAD')) return '#0ea5e9';
    return '#94a3b8';
}

window.abrirModalEventosHoy = function() {
    const modal = document.getElementById('modal-eventos-hoy');
    modal.classList.add('active');
    document.body.style.overflow = 'hidden';

    // Reset mientras carga
    document.getElementById('eventos-hoy-fecha').textContent = '';
    document.getElementById('eventos-hoy-contador').innerHTML = '';
    document.getElementById('eventos-hoy-lista').innerHTML =
        '<div style="text-align:center;color:#94a3b8;padding:48px 0;font-size:.9rem;"><i class="ti ti-loader-2" style="font-size:1.5rem;display:block;margin-bottom:8px;"></i>Cargando eventos...</div>';

    fetch('<?= URLROOT ?>/superadmin/eventosHoy', {
        headers: { 'X-Requested-With': 'XMLHttpRequest' }
    })
    .then(r => r.json())
    .then(function(json) {
        if (!json.success) {
            document.getElementById('eventos-hoy-lista').innerHTML =
                '<p style="text-align:center;color:#ef4444;padding:48px 0;">' + (json.message || 'Error al cargar.') + '</p>';
            return;
        }
        document.getElementById('eventos-hoy-fecha').textContent = json.fecha;
        const t = json.total;
        document.getElementById('eventos-hoy-contador').innerHTML =
            '<strong style="color:#ef4444;">' + t + '</strong> acción' + (t !== 1 ? 'es' : '') + ' registrada' + (t !== 1 ? 's' : '') + ' hoy';
        renderEventosHoy(json.eventos);
    })
    .catch(function() {
        document.getElementById('eventos-hoy-lista').innerHTML =
            '<p style="text-align:center;color:#ef4444;padding:48px 0;">Error de conexión. Intenta de nuevo.</p>';
    });
};

window.cerrarModalEventosHoy = function() {
    document.getElementById('modal-eventos-hoy').classList.remove('active');
    document.body.style.overflow = '';
};

function renderEventosHoy(eventos) {
    const lista = document.getElementById('eventos-hoy-lista');
    if (!eventos || eventos.length === 0) {
        lista.innerHTML = '<p style="text-align:center;color:#94a3b8;padding:48px 0;">Sin eventos registrados hoy.</p>';
        return;
    }

    lista.innerHTML = eventos.map(function(ev) {
        const accionUpper = (ev.accion || '').toUpperCase();
        const label  = EVENTOS_LABELS[accionUpper] || ev.accion;
        const color  = eventoColor(ev.accion);
        const hora   = ev.created_at ? ev.created_at.substring(11, 16) : '';
        const nombre = (ev.usuario_nombre || '').trim() || ev.correo || 'Sistema';
        const tabla  = ev.tabla_afectada
            ? '<span style="font-size:.68rem;color:#cbd5e1;margin:0 2px;">·</span><span style="font-size:.68rem;color:#94a3b8;">' + ev.tabla_afectada + '</span>'
            : '';

        return '<div style="display:flex;gap:12px;padding:10px 0;border-bottom:1px solid #f8fafc;">'
            + '<div style="display:flex;flex-direction:column;align-items:center;flex-shrink:0;padding-top:5px;">'
            + '<div style="width:9px;height:9px;border-radius:50%;background:' + color + ';flex-shrink:0;"></div>'
            + '<div style="width:1px;flex:1;background:#f1f5f9;margin-top:4px;"></div>'
            + '</div>'
            + '<div style="flex:1;min-width:0;padding-bottom:4px;">'
            + '<div style="display:flex;align-items:center;gap:6px;flex-wrap:wrap;">'
            + '<span style="background:' + color + '22;color:' + color + ';border-radius:6px;padding:2px 9px;font-size:.7rem;font-weight:700;">' + label + '</span>'
            + '<span style="font-size:.7rem;color:#94a3b8;">' + hora + '</span>'
            + tabla
            + '</div>'
            + '<div style="font-size:.76rem;color:#475569;margin-top:3px;display:flex;align-items:center;gap:4px;">'
            + '<i class="ti ti-user-circle" style="font-size:.82rem;color:#94a3b8;"></i>'
            + nombre
            + (ev.ip_address ? '<span style="font-size:.68rem;color:#cbd5e1;margin:0 2px;">·</span><span style="font-size:.68rem;color:#94a3b8;">IP: ' + ev.ip_address + '</span>' : '')
            + '</div>'
            + '</div>'
            + '</div>';
    }).join('');
}

// Cerrar con Escape
document.addEventListener('keydown', function(e) {
    if (e.key === 'Escape') cerrarModalEventosHoy();
});
</script>


