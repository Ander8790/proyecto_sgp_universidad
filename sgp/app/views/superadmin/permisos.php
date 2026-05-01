<?php
/**
 * SuperAdmin — Gestión de Permisos (Master-Detail AJAX)
 * Vista: superadmin/permisos.php
 */
require_once APPROOT . '/views/inc/header.php';
?>

<style>
/* ── Cards de Rol ─────────────────────────────────── */
.rol-cards-grid {
    display: grid;
    grid-template-columns: repeat(3, 1fr);
    gap: 20px;
    margin-bottom: 28px;
}
.rol-card {
    background: white;
    border-radius: 18px;
    padding: 28px 24px;
    box-shadow: 0 2px 12px rgba(0,0,0,0.06);
    border: 2.5px solid transparent;
    cursor: pointer;
    transition: all 0.25s ease;
    display: flex;
    flex-direction: column;
    align-items: center;
    gap: 14px;
    position: relative;
    overflow: hidden;
}
.rol-card::before {
    content: '';
    position: absolute;
    inset: 0;
    opacity: 0;
    transition: opacity 0.25s;
}
.rol-card:hover { transform: translateY(-4px); box-shadow: 0 12px 28px rgba(0,0,0,0.10); }
.rol-card.active { border-color: var(--rc-color); box-shadow: 0 8px 24px var(--rc-shadow); }
.rol-card.active::before { opacity: 1; }
.rol-card-icon {
    width: 64px; height: 64px;
    border-radius: 18px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.8rem;
    transition: transform 0.25s;
}
.rol-card:hover .rol-card-icon { transform: scale(1.08); }
.rol-card-title { font-size: 1.1rem; font-weight: 700; color: #1e293b; }
.rol-card-sub   { font-size: 0.8rem; color: #64748b; text-align: center; }
.rol-card-count {
    font-size: 2rem; font-weight: 800;
    line-height: 1;
}
.rol-card-badge {
    font-size: 0.7rem; font-weight: 700;
    padding: 4px 12px; border-radius: 50px;
    text-transform: uppercase; letter-spacing: .5px;
}

/* ── Panel Master-Detail ──────────────────────────── */
.md-layout { display: grid; grid-template-columns: 320px 1fr; gap: 20px; align-items: start; }
.md-panel { background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden; }
.md-panel-header {
    padding: 16px 20px;
    border-bottom: 1px solid #f1f5f9;
    font-weight: 700; font-size: .9rem; color: #1e293b;
    display: flex; align-items: center; gap: 8px;
}
.md-user-list { max-height: 520px; overflow-y: auto; }
.md-user-item {
    display: flex; align-items: center; gap: 14px;
    padding: 14px 20px;
    cursor: pointer;
    border-bottom: 1px solid #f8fafc;
    transition: background .15s;
}
.md-user-item:hover  { background: #f8fafc; }
.md-user-item.active { background: #eff6ff; border-left: 3px solid #2563eb; }
.md-avatar {
    width: 40px; height: 40px; border-radius: 50%;
    background: linear-gradient(135deg,#1e3a8a,#3b82f6);
    display: flex; align-items: center; justify-content: center;
    font-weight: 800; color: white; font-size: .95rem;
    flex-shrink: 0;
}
.md-user-name  { font-weight: 600; font-size: .88rem; color: #1e293b; }
.md-user-email { font-size: .72rem; color: #94a3b8; margin-top: 1px; }
.md-override-dot {
    width: 8px; height: 8px; border-radius: 50%;
    background: #a855f7; margin-left: auto; flex-shrink: 0;
}

/* ── Panel de Permisos ────────────────────────────── */
.perms-empty {
    display: flex; flex-direction: column; align-items: center; justify-content: center;
    padding: 60px 20px; color: #94a3b8; gap: 12px;
}
.perms-empty i { font-size: 3rem; }
.perms-empty p { font-size: .9rem; font-weight: 500; }

/* Bento Grid y Tarjetas */
.perms-bento-grid {
    display: grid;
    grid-template-columns: repeat(auto-fill, minmax(280px, 1fr));
    gap: 16px;
    padding: 16px;
    background: #f8fafc;
    border-radius: 0 0 16px 16px;
}
.perm-bento-card {
    background: white;
    border-radius: 16px;
    padding: 18px;
    border: 1px solid #f1f5f9;
    box-shadow: 0 4px 12px rgba(0,0,0,0.03);
    display: flex;
    flex-direction: column;
    gap: 14px;
    transition: all 0.2s;
}
.perm-bento-card:hover {
    box-shadow: 0 8px 16px rgba(0,0,0,0.06);
    transform: translateY(-2px);
}
.perm-bento-header {
    display: flex;
    align-items: center;
    gap: 12px;
}
.perm-bento-icon {
    width: 42px; height: 42px;
    border-radius: 12px;
    display: flex; align-items: center; justify-content: center;
    font-size: 1.3rem; color: var(--mod-color, #2563eb);
    background: var(--mod-bg, #eff6ff);
    flex-shrink: 0;
}
.perm-bento-title {
    font-size: .95rem; font-weight: 700; color: #1e293b; line-height: 1.2;
}
.perm-bento-group {
    font-size: .7rem; color: #64748b; font-weight: 600; letter-spacing: .5px; margin-top: 2px; text-transform: none;
}
.perm-action-list {
    display: flex;
    flex-wrap: wrap;
    gap: 8px;
}
/* Chips Interactivos */
.perm-chip {
    padding: 6px 12px;
    border-radius: 50px;
    font-size: .75rem;
    font-weight: 600;
    cursor: pointer;
    display: inline-flex;
    align-items: center;
    gap: 6px;
    transition: all 0.2s;
    user-select: none;
    border: 1px solid transparent;
}
.perm-chip.enabled {
    background: var(--mod-bg, #eff6ff);
    color: var(--mod-color, #2563eb);
    border-color: rgba(0,0,0,0.05);
}
.perm-chip.disabled {
    background: #f8fafc;
    color: #94a3b8;
    border: 1px dashed #cbd5e1;
    opacity: 0.8;
}
.perm-chip i { font-size: 1rem; }
.perm-override-dot {
    width: 6px; height: 6px; border-radius: 50%;
    background: #f59e0b;
    box-shadow: 0 0 0 2px white;
    margin-left: 2px;
}

/* Btn Reset */
.btn-reset-user {
    display: flex; align-items: center; gap: 6px;
    padding: 8px 16px; border-radius: 10px;
    border: 1.5px solid #fecaca; background: #fef2f2;
    color: #ef4444; font-size: .8rem; font-weight: 700;
    cursor: pointer; transition: all .2s;
}
.btn-reset-user:hover { background: #fee2e2; border-color: #fca5a5; }

/* Loading */
.md-loading { display: flex; align-items: center; justify-content: center; padding: 40px; }
.spinner { width: 32px; height: 32px; border: 3px solid #e2e8f0; border-top-color: #2563eb; border-radius: 50%; animation: spin .7s linear infinite; }
@keyframes spin { to { transform: rotate(360deg); } }

@media (max-width: 900px) {
    .rol-cards-grid { grid-template-columns: 1fr; }
    .md-layout { grid-template-columns: 1fr; }
}
</style>

<div class="dashboard-container" style="width:100%;max-width:100%;padding:0;">

    <!-- BANNER -->
    <div class="pasantes-banner" style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:28px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:14px;">
                <i class="ti ti-shield-cog" style="font-size:32px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.8rem;font-weight:700;margin:0;">Gestión de Permisos</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;display:flex;align-items:center;gap:10px;">
                    Control granular de accesos por usuario
                    <span style="background:linear-gradient(135deg,#7c3aed,#a855f7);border-radius:50px;padding:4px 14px;color:white;font-weight:700;font-size:0.8rem;">SUPERADMIN</span>
                </p>
            </div>
        </div>
        <div style="display:flex;gap:10px;z-index:1;">
            <a href="<?= URLROOT ?>/superadmin" style="display:flex;align-items:center;gap:8px;padding:10px 18px;background:rgba(255,255,255,0.15);border:1px solid rgba(255,255,255,0.2);border-radius:12px;color:white;text-decoration:none;font-size:.88rem;font-weight:600;transition:all .2s;"
               onmouseover="this.style.background='rgba(255,255,255,0.25)'" onmouseout="this.style.background='rgba(255,255,255,0.15)'">
                <i class="ti ti-arrow-left"></i> Dashboard
            </a>
        </div>
    </div>

    <!-- PASO 1: Seleccionar Rol -->
    <div style="margin-bottom:8px;">
        <div style="background:linear-gradient(to right,#f8fafc,#edf2f9);border:1px solid #e2e8f0;border-left:4px solid #2563eb;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 6px rgba(0,0,0,0.02);">
            <div style="background:white;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(37,99,235,0.15);flex-shrink:0;">
                <i class="ti ti-hand-click" style="color:#2563eb;font-size:1.1rem;"></i>
            </div>
            <div>
                <h4 style="margin:0 0 2px;color:#1e293b;font-size:.9rem;font-weight:700;">Paso 1 — Selecciona el grupo a gestionar</h4>
                <p style="margin:0;color:#64748b;font-size:.82rem;line-height:1.5;">Elige el tipo de rol. Los módulos disponibles se filtran automáticamente según el rol seleccionado.</p>
            </div>
        </div>
        <div class="rol-cards-grid">
            <!-- Card Administrador -->
            <div class="rol-card" id="card-admin" data-rol="1" style="--rc-color:#8b5cf6; --rc-shadow:rgba(139,92,246,0.2);" onclick="selectRol(1, this)">
                <div class="rol-card-icon" style="background:#f5f3ff;color:#7c3aed;">
                    <i class="ti ti-user-shield"></i>
                </div>
                <div style="text-align:center;">
                    <div class="rol-card-count" style="color:#7c3aed;"><?= $data['kpis']['total_admins'] > 0 ? $data['kpis']['total_admins'] : '—' ?></div>
                    <div class="rol-card-title">Administradores</div>
                    <div class="rol-card-sub">Gestiona qué puede hacer cada admin del sistema</div>
                </div>
                <span class="rol-card-badge" style="background:#f5f3ff;color:#7c3aed;">Rol 1</span>
            </div>

            <!-- Card Tutor -->
            <div class="rol-card" id="card-tutor" data-rol="2" style="--rc-color:#10b981; --rc-shadow:rgba(16,185,129,0.2);" onclick="selectRol(2, this)">
                <div class="rol-card-icon" style="background:#ecfdf5;color:#059669;">
                    <i class="ti ti-user-check"></i>
                </div>
                <div style="text-align:center;">
                    <div class="rol-card-count" style="color:#059669;"><?= $data['kpis']['total_tutores'] ?? '—' ?></div>
                    <div class="rol-card-title">Tutores</div>
                    <div class="rol-card-sub">Amplía o restringe funciones del tutor académico</div>
                </div>
                <span class="rol-card-badge" style="background:#ecfdf5;color:#059669;">Rol 2</span>
            </div>

            <!-- Card Pasante -->
            <div class="rol-card" id="card-pasante" data-rol="3" style="--rc-color:#f59e0b; --rc-shadow:rgba(245,158,11,0.2);" onclick="selectRol(3, this)">
                <div class="rol-card-icon" style="background:#fffbeb;color:#d97706;">
                    <i class="ti ti-users-group"></i>
                </div>
                <div style="text-align:center;">
                    <div class="rol-card-count" style="color:#d97706;"><?= $data['kpis']['total_pasantes'] ?? '—' ?></div>
                    <div class="rol-card-title">Pasantes</div>
                    <div class="rol-card-sub">Controla el acceso al portal y funciones del pasante</div>
                </div>
                <span class="rol-card-badge" style="background:#fffbeb;color:#d97706;">Rol 3</span>
            </div>
        </div>
    </div>

    <!-- PASO 2: Master-Detail (oculto hasta seleccionar rol) -->
    <div id="md-section" style="display:none;">
        <div style="background:linear-gradient(to right,#f8fafc,#edf2f9);border:1px solid #e2e8f0;border-left:4px solid #7c3aed;border-radius:12px;padding:14px 20px;margin-bottom:20px;display:flex;align-items:center;gap:14px;box-shadow:0 4px 6px rgba(0,0,0,0.02);">
            <div style="background:white;width:36px;height:36px;border-radius:50%;display:flex;align-items:center;justify-content:center;box-shadow:0 2px 8px rgba(124,58,237,0.15);flex-shrink:0;">
                <i class="ti ti-shield-cog" style="color:#7c3aed;font-size:1.1rem;"></i>
            </div>
            <div>
                <h4 style="margin:0 0 2px;color:#1e293b;font-size:.9rem;font-weight:700;">Paso 2 — Selecciona un usuario y configura sus permisos</h4>
                <p style="margin:0;color:#64748b;font-size:.82rem;line-height:1.5;">Activa o desactiva permisos individuales haciendo clic en cada chip. Los cambios se guardan <strong style="color:#10b981;">automáticamente</strong>.</p>
            </div>
        </div>
        <div class="md-layout">

            <!-- Lista de usuarios (izquierda) -->
            <div class="md-panel">
                <div class="md-panel-header">
                    <i class="ti ti-users" style="color:#2563eb;font-size:1.1rem;"></i>
                    <span id="md-list-title">Usuarios</span>
                    <span id="md-list-count" style="margin-left:auto;font-size:.75rem;color:#94a3b8;font-weight:600;"></span>
                </div>
                <div id="md-user-list" class="md-user-list">
                    <div class="md-loading"><div class="spinner"></div></div>
                </div>
            </div>

            <!-- Panel de permisos (derecha) -->
            <div class="md-panel">
                <div class="md-panel-header" id="perms-header" style="justify-content:space-between;">
                    <div style="display:flex;align-items:center;gap:8px;">
                        <i class="ti ti-shield-lock" style="color:#a855f7;font-size:1.1rem;"></i>
                        <span id="perms-title">Selecciona un usuario</span>
                    </div>
                    <button id="btn-reset-global" class="btn-reset-user" style="display:none;" onclick="resetCurrentUser()">
                        <i class="ti ti-refresh"></i> Restablecer al Rol
                    </button>
                </div>
                <div id="perms-body">
                    <div class="perms-empty">
                        <i class="ti ti-arrow-left"></i>
                        <p>Elige un usuario de la lista para ver y editar sus permisos</p>
                    </div>
                </div>
            </div>
        </div>
    </div>

</div>

<script>
(function () {
    'use strict';
    const URLROOT = '<?= URLROOT ?>';

    // Traducciones de acciones
    const accionLabels = {
        'CHANGE_PASANTE_STATUS': 'Cambiar Estado de Pasante',
        'CREATE_USER': 'Crear Usuarios',
        'DELETE_USER': 'Eliminar Usuarios',
        'LOGIN': 'Iniciar Sesión',
        'LOGOUT': 'Cerrar Sesión',
        'RESET_PASSWORD': 'Restablecer Contraseñas',
        'RESET_PIN': 'Restablecer PIN',
        'TOGGLE_USER_STATUS': 'Alternar Estado de Usuarios',
        'UPDATE_PROFILE': 'Editar Perfil',
        'UPDATE_USER': 'Modificar Usuarios',
        'CREATE_PASANTE': 'Registrar Pasantes',
        'UPDATE_PASANTE': 'Modificar Pasantes',
        'DELETE_PASANTE': 'Eliminar Pasantes',
        'CREATE_EVALUACION': 'Crear Evaluaciones',
        'UPDATE_EVALUACION': 'Editar Evaluaciones',
        'DELETE_EVALUACION': 'Eliminar Evaluaciones',
        'EXPORT_CSV': 'Exportar CSV',
        'UPDATE_CONFIG': 'Cambiar Configuración',
        'VIEW_BITACORA': 'Ver Bitácora',
        'VIEW_ANALITICAS': 'Ver Analíticas',
        'VIEW_REPORTES': 'Ver Reportes',
    };

    let currentUserId  = null;
    let currentRolId   = null;
    let currentModulos = null;
    let usersCache     = {};

    // ── Seleccionar card de rol ──────────────────────
    window.selectRol = function (rolId, card) {
        document.querySelectorAll('.rol-card').forEach(c => c.classList.remove('active'));
        card.classList.add('active');
        currentRolId = rolId;
        currentUserId = null;

        document.getElementById('md-section').style.display = 'block';
        document.getElementById('md-section').scrollIntoView({ behavior: 'smooth', block: 'start' });

        const titles = { 1: 'Administradores', 2: 'Tutores', 3: 'Pasantes' };
        document.getElementById('md-list-title').textContent = titles[rolId] || 'Usuarios';
        document.getElementById('perms-title').textContent = 'Selecciona un usuario';
        document.getElementById('btn-reset-global').style.display = 'none';
        document.getElementById('perms-body').innerHTML = `
            <div class="perms-empty">
                <i class="ti ti-arrow-left"></i>
                <p>Elige un usuario de la lista para ver y editar sus permisos</p>
            </div>`;

        loadUsersByRol(rolId);
    };

    // ── Cargar usuarios por rol ──────────────────────
    function loadUsersByRol(rolId) {
        const list = document.getElementById('md-user-list');
        list.innerHTML = '<div class="md-loading"><div class="spinner"></div></div>';

        fetch(`${URLROOT}/superadmin/getUsersByRol?rol_id=${rolId}`)
            .then(r => r.json())
            .then(res => {
                if (!res.success) { list.innerHTML = '<p style="padding:20px;color:#ef4444;">Error al cargar usuarios.</p>'; return; }
                currentModulos = res.modulos;
                usersCache = {};
                res.usuarios.forEach(u => { usersCache[u.id] = u; });

                const count = res.usuarios.length;
                document.getElementById('md-list-count').textContent = count + ' usuario' + (count !== 1 ? 's' : '');

                if (!count) {
                    list.innerHTML = '<p style="padding:20px;color:#94a3b8;font-size:.88rem;">No hay usuarios en este rol.</p>';
                    return;
                }

                list.innerHTML = res.usuarios.map(u => {
                    const nombre = (u.nombre_completo || '').trim() || u.correo;
                    const inicial = nombre.charAt(0).toUpperCase();
                    const hasOverride = u.permisos && Object.values(u.permisos).some(p => p.es_override);
                    return `
                    <div class="md-user-item" data-uid="${u.id}" onclick="selectUser(${u.id})">
                        <div class="md-avatar">${inicial}</div>
                        <div style="flex:1;min-width:0;">
                            <div class="md-user-name" style="white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">${nombre}</div>
                            <div class="md-user-email">${u.correo}</div>
                        </div>
                        ${hasOverride ? '<div class="md-override-dot" title="Tiene permisos personalizados"></div>' : ''}
                    </div>`;
                }).join('');
            })
            .catch(() => { list.innerHTML = '<p style="padding:20px;color:#ef4444;">Error de red.</p>'; });
    }

    // ── Seleccionar usuario ──────────────────────────
    window.selectUser = function (uid) {
        currentUserId = uid;
        document.querySelectorAll('.md-user-item').forEach(el => el.classList.remove('active'));
        const item = document.querySelector(`.md-user-item[data-uid="${uid}"]`);
        if (item) item.classList.add('active');

        const u = usersCache[uid];
        if (!u) return;
        const nombre = (u.nombre_completo || '').trim() || u.correo;
        document.getElementById('perms-title').textContent = nombre;
        document.getElementById('btn-reset-global').style.display = 'flex';

        renderPermisos(u.permisos || {}, currentModulos);
    };

    // ── Renderizar permisos del usuario ─────────────
    function renderPermisos(permisos, modulos) {
        const body = document.getElementById('perms-body');
        if (!modulos || !Object.keys(modulos).length) {
            body.innerHTML = '<div class="perms-empty"><i class="ti ti-lock-question"></i><p>No hay módulos configurados.</p></div>';
            return;
        }

        const colorRules = {
            'Administración': { bg: '#f5f3ff', color: '#7c3aed', icon: 'ti-shield-lock' },
            'Pasantías':      { bg: '#ecfdf5', color: '#10b981', icon: 'ti-users-group' },
            'Informes':       { bg: '#eff6ff', color: '#3b82f6', icon: 'ti-chart-pie' },
            'Sistema':        { bg: '#fef2f2', color: '#ef4444', icon: 'ti-server-cog' },
            'Académico':      { bg: '#fffbeb', color: '#f59e0b', icon: 'ti-book' },
            'Tutor':          { bg: '#ecfeff', color: '#06b6d4', icon: 'ti-user-check' },
        };
        const defaultRule = { bg: '#f1f5f9', color: '#64748b', icon: 'ti-folder' };

        let html = '<div class="perms-bento-grid">';
        for (const [grupo, modulosGrupo] of Object.entries(modulos)) {
            for (const [modId, acciones] of Object.entries(modulosGrupo)) {
                if (!acciones.length) continue;
                
                const rule = colorRules[grupo] || defaultRule;
                const modName = acciones[0].modulo_nombre || 'Módulo';
                const modIcon = acciones[0].icono || rule.icon;
                
                let chipsHtml = '';
                for (const accion of acciones) {
                    const perm    = permisos[accion.clave] || {};
                    const checked = perm.habilitado === true;
                    const isOverride = !!perm.es_override;
                    const label = accionLabels[accion.clave?.toUpperCase()] || accion.accion_nombre;
                    
                    const chipClass = checked ? 'enabled' : 'disabled';
                    const iconClass = checked ? 'ti-check' : 'ti-x';
                    
                    chipsHtml += `
                    <div class="perm-chip ${chipClass}" 
                         data-clave="${accion.clave}" 
                         data-habilitado="${checked}"
                         onclick="togglePermiso(this)">
                        <i class="ti ${iconClass}"></i>
                        ${label}
                        ${isOverride ? '<span class="perm-override-dot" title="Permiso personalizado"></span>' : ''}
                    </div>`;
                }
                
                html += `
                <div class="perm-bento-card" style="--mod-bg:${rule.bg}; --mod-color:${rule.color};">
                    <div class="perm-bento-header">
                        <div class="perm-bento-icon"><i class="ti ${modIcon}"></i></div>
                        <div>
                            <div class="perm-bento-title">${modName}</div>
                            <div class="perm-bento-group">${grupo}</div>
                        </div>
                    </div>
                    <div class="perm-action-list">
                        ${chipsHtml}
                    </div>
                </div>`;
            }
        }
        html += '</div>';
        body.innerHTML = html;
    }

    // ── Guardar permiso al hacer clic en un Chip ────────────
    window.togglePermiso = function(chip) {
        if (!currentUserId) return;
        
        const clave = chip.dataset.clave;
        const habilitadoActual = chip.dataset.habilitado === 'true';
        const nuevoHabilitado = !habilitadoActual;
        
        // Optimistic UI update
        chip.dataset.habilitado = nuevoHabilitado;
        if (nuevoHabilitado) {
            chip.classList.remove('disabled');
            chip.classList.add('enabled');
            chip.querySelector('i').className = 'ti ti-check';
        } else {
            chip.classList.remove('enabled');
            chip.classList.add('disabled');
            chip.querySelector('i').className = 'ti ti-x';
        }

        fetch(`${URLROOT}/superadmin/savePermiso`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ usuario_id: parseInt(currentUserId), clave, habilitado: nuevoHabilitado })
        })
        .then(r => r.json())
        .then(res => {
            if (res.success) {
                if (window.NotificationService) window.NotificationService.success(res.message);
                if (usersCache[currentUserId]) {
                    if (!usersCache[currentUserId].permisos) usersCache[currentUserId].permisos = {};
                    usersCache[currentUserId].permisos[clave] = { habilitado: nuevoHabilitado, es_override: true };
                }
                const item = document.querySelector(`.md-user-item[data-uid="${currentUserId}"]`);
                if (item && !item.querySelector('.md-override-dot')) {
                    item.insertAdjacentHTML('beforeend', '<div class="md-override-dot" title="Tiene permisos personalizados"></div>');
                }
                if (!chip.querySelector('.perm-override-dot')) {
                    chip.insertAdjacentHTML('beforeend', '<span class="perm-override-dot" title="Permiso personalizado"></span>');
                }
            } else {
                if (window.NotificationService) window.NotificationService.error(res.message);
                revertChip(chip, habilitadoActual);
            }
        })
        .catch(() => { revertChip(chip, habilitadoActual); });
    };

    function revertChip(chip, state) {
        chip.dataset.habilitado = state;
        if (state) {
            chip.classList.remove('disabled');
            chip.classList.add('enabled');
            chip.querySelector('i').className = 'ti ti-check';
        } else {
            chip.classList.remove('enabled');
            chip.classList.add('disabled');
            chip.querySelector('i').className = 'ti ti-x';
        }
    }

    // ── Restablecer permisos del usuario actual ──────
    window.resetCurrentUser = function () {
        if (!currentUserId) return;
        const nombre = (usersCache[currentUserId]?.nombre_completo || '').trim() || 'este usuario';

        Swal.fire({
            title: '¿Restablecer permisos?',
            html: `<p>Los permisos de <strong>${nombre}</strong> volverán a los valores por defecto de su rol.</p>`,
            icon: 'warning',
            showCancelButton: true,
            confirmButtonText: 'Sí, restablecer',
            cancelButtonText: 'Cancelar',
            confirmButtonColor: '#2563eb'
        }).then(result => {
            if (!result.isConfirmed) return;
            fetch(`${URLROOT}/superadmin/resetUsuario`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ usuario_id: parseInt(currentUserId) })
            })
            .then(r => r.json())
            .then(res => {
                if (res.success) {
                    if (window.NotificationService) window.NotificationService.success(res.message);
                    // Recargar usuarios del rol actual
                    setTimeout(() => loadUsersByRol(currentRolId), 800);
                } else {
                    if (window.NotificationService) window.NotificationService.error(res.message);
                }
            });
        });
    };
})();
</script>

<?php require_once APPROOT . '/views/layouts/footer.php'; ?>
