<?php
/**
 * Vista: Gestión de Pasantes (Admin)
 * URL: /pasantes  —  Cargada por PasantesController::index()
 */
?>

<style>
.modal-overlay {
    display: none; position: fixed; inset: 0;
    background: rgba(15,23,42,0.7); backdrop-filter: blur(6px);
    z-index: 9999; align-items: center; justify-content: center;
    animation: fadeIn 0.2s ease;
}
.modal-overlay.active { display: flex; }
@keyframes fadeIn { from { opacity:0; } to { opacity:1; } }
@keyframes slideUp { from { transform:translateY(24px);opacity:0; } to { transform:translateY(0);opacity:1; } }

/* Modal box: flexbox para que header sea fijo y body haga scroll */
.modal-box {
    background: white;
    border-radius: 24px;
    width: 90%;
    max-width: 580px;
    max-height: 90vh;
    display: flex;
    flex-direction: column;
    overflow: hidden; /* Clip de esquinas redondeadas sin matar el scroll */
    box-shadow: 0 32px 80px rgba(15,23,42,0.3);
    animation: slideUp 0.3s ease;
}
/* Header: fijo, nunca hace scroll */
.modal-head {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
    padding: 28px 32px;
    display: flex;
    justify-content: space-between;
    align-items: center;
    flex-shrink: 0; /* No se encoge — se queda fijo arriba */
    color: white;
}
.modal-head h2 { font-size:1.3rem; font-weight:700; margin:0; color:white !important; }
.modal-head p  { font-size:0.85rem; margin:4px 0 0; color:rgba(255,255,255,0.8) !important; }
.modal-head * { color: white !important; }
.btn-close-modal {
    background: rgba(255,255,255,0.2);
    border: none;
    color: white !important;
    width: 36px; height: 36px;
    border-radius: 50%;
    cursor: pointer;
    font-size: 1.1rem;
    display: flex; align-items: center; justify-content: center;
    transition: background 0.2s;
    flex-shrink: 0;
}
.btn-close-modal:hover { background: rgba(255,255,255,0.35); }
.btn-close-modal i { color: white !important; }
/* Body: ocupa el espacio restante y hace scroll */
.modal-body {
    padding: 28px 32px;
    overflow-y: auto; /* Este es el que scrollea */
    flex: 1;
}

.form-group { margin-bottom: 20px; }
.form-label {
    display:block; font-size:0.82rem; font-weight:700; color:#374151;
    margin-bottom:8px; text-transform:uppercase; letter-spacing:0.5px;
}
.form-input {
    width:100%; padding:12px 16px; border:2px solid #e5e7eb; border-radius:12px;
    font-size:0.95rem; color:#1e293b; transition: border-color 0.2s, box-shadow 0.2s;
    box-sizing:border-box; background:#fafafa;
}
.form-input:focus {
    outline:none; border-color:#2563eb;
    box-shadow:0 0 0 4px rgba(79,70,229,0.1); background:white;
}

.horas-wrapper { display:flex; gap:10px; align-items:center; }
.horas-wrapper .form-input { flex:1; }
.btn-reset-horas {
    white-space:nowrap; padding:10px 14px; background:#eff6ff;
    color:#2563eb; border:2px solid #bfdbfe; border-radius:10px;
    font-size:0.8rem; font-weight:700; cursor:pointer; transition:all 0.2s;
    display:flex; align-items:center; gap:6px;
}
.btn-reset-horas:hover { background:#2563eb; color:white; border-color:#2563eb; }

.jornada-grid { display:grid; grid-template-columns:1fr 1fr; gap:12px; }
.jornada-option {
    border:2px solid #e5e7eb; border-radius:12px; padding:14px;
    cursor:pointer; transition:all 0.2s; text-align:center; background:#fafafa;
}
.jornada-option:hover { border-color:#2563eb; background:#eff6ff; }
.jornada-option.selected {
    border-color:#2563eb; background:linear-gradient(135deg,#eff6ff,#dbeafe);
    box-shadow:0 0 0 3px rgba(79,70,229,0.15);
}
.jornada-option input[type="radio"] { display:none; }
.jornada-icon  { font-size:1.6rem; display:block; margin-bottom:6px; }
.jornada-label { font-weight:700; color:#1e293b; font-size:0.9rem; display:block; }
.jornada-sub   { color:#6b7280; font-size:0.78rem; }

.proyeccion-card {
    display:none; background:linear-gradient(135deg,#eff6ff,#dbeafe);
    border:1.5px solid #bfdbfe; border-radius:14px; padding:18px 20px;
    margin-top:16px; animation:slideUp 0.3s ease;
}
.proyeccion-card.visible { display:block; }
.proy-title {
    font-size:0.78rem; font-weight:700; color:#2563eb; text-transform:uppercase;
    letter-spacing:0.5px; margin-bottom:10px; display:flex; align-items:center; gap:6px;
}
.proy-dato {
    display:flex; justify-content:space-between; align-items:center;
    padding:6px 0; border-bottom:1px solid rgba(79,70,229,0.1); font-size:0.88rem;
}
.proy-dato:last-child { border-bottom:none; }
.proy-dato span:first-child { color:#6b7280; }
.proy-dato strong { color:#1e3a8a; font-weight:700; }
.proy-highlight {
    background:#2563eb; color:white; padding:10px 14px; border-radius:10px;
    margin-top:10px; font-size:0.9rem; text-align:center; font-weight:600;
}
.modal-divider { border:none; border-top:1px solid #f1f5f9; margin:24px 0; }
.btn-submit {
    width:100%; padding:14px;
    background:linear-gradient(135deg,#172554 0%,#2563eb 100%);
    color:white; border:none; border-radius:12px; font-size:1rem;
    font-weight:700; cursor:pointer; display:flex; align-items:center;
    justify-content:center; gap:10px; transition:all 0.2s;
    box-shadow:0 4px 16px rgba(79,70,229,0.35);
}
.btn-submit:hover { transform:translateY(-2px); box-shadow:0 8px 24px rgba(79,70,229,0.45); }

.progress-wrap { min-width:140px; }
.progress-track { background:#f1f5f9; border-radius:100px; height:7px; overflow:hidden; margin-top:5px; }
.progress-fill  { height:100%; border-radius:100px; background:linear-gradient(90deg,#2563eb,#1d4ed8); transition:width 0.5s ease; }
.progress-label { font-size:0.78rem; color:#64748b; font-weight:600; }
.progress-pct   { float:right; font-weight:700; color:#1e3a8a; }
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">

    <!-- BANNER ESTANDARIZADO SGP -->
    <div style="background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);border-radius:20px;padding:32px 40px;margin-bottom:28px;position:relative;overflow:hidden;display:flex;align-items:center;justify-content:space-between;">
        <div style="position:absolute;top:-30px;right:-30px;width:200px;height:200px;background:rgba(255,255,255,0.05);border-radius:50%;"></div>
        <div style="display:flex;align-items:center;gap:16px;z-index:1;">
            <div style="background:rgba(255,255,255,0.15);border-radius:14px;padding:14px;">
                <i class="ti ti-user-check" style="font-size:32px;color:white;"></i>
            </div>
            <div>
                <h1 style="color:white;font-size:1.8rem;font-weight:700;margin:0;">Gestión de Pasantes</h1>
                <p style="color:rgba(255,255,255,0.7);margin:4px 0 0;font-size:0.9rem;display:flex;align-items:center;">
                    <span>Registro, seguimiento y proyección de pasantías</span>
                    <span style="display:inline-block; background:rgba(255,255,255,0.15); backdrop-filter:blur(10px); -webkit-backdrop-filter:blur(10px); border:1px solid rgba(255,255,255,0.1); border-radius:50px; padding:4px 14px; margin-left:12px; color:white; font-weight:700; font-size:0.8rem; box-shadow:0 4px 6px rgba(0,0,0,0.05); white-space:nowrap;">
                        <?= $total ?> pasantes
                    </span>
                </p>
            </div>
        </div>
        <div style="display:flex; z-index:1; align-items:center;">
            <!-- Contenedor Glassmorphism para el botón -->
            <div style="background: rgba(0, 0, 0, 0.15); backdrop-filter: blur(10px); -webkit-backdrop-filter: blur(10px); border: 1px solid rgba(255, 255, 255, 0.1); border-radius: 14px; padding: 6px; box-shadow: 0 4px 6px rgba(0,0,0,0.05);">
                <button onclick="SGPModal.buscar({rol: 3})" style="background:white;color:#1e3a8a;border:none;padding:12px 24px;border-radius:10px;font-weight:700;cursor:pointer;display:flex;align-items:center;gap:8px;font-size:0.95rem;transition:all 0.2s;" onmouseover="this.style.transform='translateY(-2px)'" onmouseout="this.style.transform='none'">
                    <i class="ti ti-search" style="font-size: 1.1rem;"></i> Consulta Rápida
                </button>
            </div>
        </div>
    </div>


    <!-- KPI CARDS ESTANDARIZADAS -->
    <div style="display:grid;grid-template-columns:repeat(4,1fr);gap:20px;margin-bottom:28px;">
        <?php
        $kpis = [
            ['label' => 'Total Pasantes',  'val' => $total,      'color' => '#2563eb', 'boxShadow' => 'rgba(37,99,235,0.15)', 'icon' => 'ti-users',        'sub' => 'registrados'],
            ['label' => 'En Curso',        'val' => $enCurso,    'color' => '#10b981', 'boxShadow' => 'rgba(16,185,129,0.15)', 'icon' => 'ti-player-play',  'sub' => 'pasantías activas'],
            ['label' => 'Pendientes',      'val' => $pendientes, 'color' => '#f59e0b', 'boxShadow' => 'rgba(245,158,11,0.15)', 'icon' => 'ti-clock',        'sub' => 'por formalizar'],
            ['label' => 'Culminados',      'val' => $culminados, 'color' => '#8b5cf6', 'boxShadow' => 'rgba(139,92,246,0.15)', 'icon' => 'ti-medal',        'sub' => 'este período'],
        ];
        foreach ($kpis as $k): ?>
        <div style="background:white;border-radius:16px;padding:22px;box-shadow:0 2px 12px rgba(0,0,0,0.06);border-left:4px solid <?= $k['color'] ?>;transition:all 0.3s;" onmouseover="this.style.transform='translateY(-4px)';this.style.boxShadow='0 12px 25px <?= $k['boxShadow'] ?>'" onmouseout="this.style.transform='none';this.style.boxShadow='0 2px 12px rgba(0,0,0,0.06)'">
            <div style="display:flex;justify-content:space-between;align-items:flex-start;">
                <p style="color:#64748b;font-size:0.82rem;margin:0 0 8px;font-weight:600;text-transform:uppercase;letter-spacing:0.5px;"><?= $k['label'] ?></p>
                <i class="ti <?= $k['icon'] ?>" style="color:<?= $k['color'] ?>;font-size:1.4rem;opacity:0.7;"></i>
            </div>
            <h2 style="font-size:2.4rem;font-weight:800;color:<?= $k['color'] ?>;margin:0;"><?= $k['val'] ?></h2>
            <p style="color:#94a3b8;font-size:0.8rem;margin:4px 0 0;"><?= $k['sub'] ?></p>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- TABLA -->
    <div style="background:white;border-radius:16px;box-shadow:0 2px 12px rgba(0,0,0,0.06);overflow:hidden;">
        <div style="padding:20px 24px;border-bottom:1px solid #f1f5f9;display:flex;justify-content:space-between;align-items:center;">
            <h3 style="font-size:1rem;font-weight:700;color:#1e293b;margin:0;">
                <i class="ti ti-list-check" style="color:#2563eb;margin-right:6px;"></i>Pasantes Registrados
            </h3>
            <span style="background:#eff6ff;color:#2563eb;padding:4px 14px;border-radius:20px;font-size:0.8rem;font-weight:600;"><?= $total ?> pasantes</span>
        </div>

        <?php if (empty($pasantes)): ?>
        <div style="padding:60px;text-align:center;color:#94a3b8;">
            <i class="ti ti-users-off" style="font-size:3rem;display:block;margin-bottom:12px;"></i>
            <p style="font-size:1rem;font-weight:600;">No hay pasantes registrados aún.</p>
            <button onclick="abrirModal()" style="background:#2563eb;color:white;border:none;padding:10px 20px;border-radius:10px;font-weight:700;cursor:pointer;margin-top:8px;">
                <i class="ti ti-plus"></i> Agregar el primero
            </button>
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table style="width:100%;border-collapse:collapse;">
                <thead>
                    <tr style="background:#f8fafc;">
                        <?php foreach (['Pasante','Cédula','Institución','Departamento','Progreso (Días)','Estado','Acciones'] as $th): ?>
                        <th style="padding:14px 20px;text-align:left;font-size:0.78rem;color:#64748b;font-weight:700;text-transform:uppercase;letter-spacing:0.5px;"><?= $th ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                <?php foreach ($pasantes as $p):
                    $nombres  = htmlspecialchars(($p->apellidos ?? '') . ', ' . ($p->nombres ?? ''));
                    $cedula   = htmlspecialchars($p->cedula ?? '—');
                    $inst     = htmlspecialchars($p->institucion_procedencia ?? '—');
                    $depto    = htmlspecialchars($p->departamento_nombre ?? 'Sin asignar');
                    $estado   = $p->estado_pasantia ?? 'Pendiente';
                    $horasAcum = (int)($p->horas_acumuladas ?? 0);
                    $horasMeta = (int)($p->horas_meta ?? 1440);
                    // Días: 1 día = 8h
                    $diasAcum = (int)ceil($horasAcum / 8);
                    $diasTotal= (int)ceil($horasMeta / 8);
                    $pct = $horasMeta > 0 ? min(100, round(($horasAcum / $horasMeta) * 100)) : 0;

                    $estadoMap = [
                        'Activo'     => ['bg' => '#eff6ff', 'color' => '#2563eb'],
                        'Pendiente'  => ['bg' => '#fef9c3', 'color' => '#ca8a04'],
                        'Finalizado' => ['bg' => '#dcfce7', 'color' => '#16a34a'],
                        'Retirado'   => ['bg' => '#fee2e2', 'color' => '#dc2626'],
                    ];
                    $cfg = $estadoMap[$estado] ?? ['bg' => '#f1f5f9', 'color' => '#64748b'];
                    $inicial = strtoupper(substr($p->nombres ?? 'P', 0, 1));
                ?>
                <tr style="border-bottom:1px solid #f1f5f9;transition:background 0.2s;" onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                    <td style="padding:16px 20px;">
                        <div style="display:flex;align-items:center;gap:12px;">
                            <div style="width:38px;height:38px;border-radius:50%;background:linear-gradient(135deg,#172554,#2563eb);display:flex;align-items:center;justify-content:center;color:white;font-weight:800;font-size:0.85rem;flex-shrink:0;">
                                <?= $inicial ?>
                            </div>
                            <span style="font-weight:600;color:#1e293b;font-size:0.9rem;"><?= $nombres ?></span>
                        </div>
                    </td>
                    <td style="padding:16px 20px;color:#64748b;font-size:0.85rem;"><?= $cedula ?></td>
                    <td style="padding:16px 20px;color:#64748b;font-size:0.85rem;"><?= $inst ?></td>
                    <td style="padding:16px 20px;color:#64748b;font-size:0.85rem;"><?= $depto ?></td>
                    <td style="padding:16px 20px;">
                        <div class="progress-wrap">
                            <div class="progress-label">
                                Día <?= $diasAcum ?> / <?= $diasTotal ?>
                                <span class="progress-pct"><?= $pct ?>%</span>
                            </div>
                            <div class="progress-track">
                                <div class="progress-fill" style="width:<?= $pct ?>%;"></div>
                            </div>
                        </div>
                    </td>
                    <td style="padding:16px 20px;">
                        <span style="background:<?= $cfg['bg'] ?>;color:<?= $cfg['color'] ?>;padding:5px 14px;border-radius:20px;font-size:0.78rem;font-weight:700;white-space:nowrap;">
                            <?= $estado ?>
                        </span>
                    </td>
                    <td style="padding:16px 20px;">
                        <div style="display:flex;gap:6px;">
                            <button onclick="SGPModal.verUsuario(<?= $p->id ?>)" title="Ver perfil" style="width:32px;height:32px;border:none;border-radius:8px;background:#eff6ff;color:#2563eb;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='#dbeafe'" onmouseout="this.style.background='#eff6ff'">
                                <i class="ti ti-eye" style="font-size:0.9rem;"></i>
                            </button>
                            <button onclick="abrirModalEditar(<?= $p->id ?>)" title="Editar asignación" style="width:32px;height:32px;border:none;border-radius:8px;background:#f0fdf4;color:#16a34a;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='#dcfce7'" onmouseout="this.style.background='#f0fdf4'">
                                <i class="ti ti-pencil" style="font-size:0.9rem;"></i>
                            </button>
                            <button onclick="cambiarEstado(<?= $p->id ?>, this.dataset.nombre)" data-nombre="<?= htmlspecialchars($p->nombres ?? '', ENT_QUOTES) ?>" title="Cambiar estado" style="width:32px;height:32px;border:none;border-radius:8px;background:#fefce8;color:#ca8a04;cursor:pointer;display:flex;align-items:center;justify-content:center;transition:all 0.2s;" onmouseover="this.style.background='#fef9c3'" onmouseout="this.style.background='#fefce8'">
                                <i class="ti ti-switch-horizontal" style="font-size:0.9rem;"></i>
                            </button>
                        </div>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <?php endif; ?>
    </div>

</div><!-- /dashboard-container -->


<!-- ======= MODAL: NUEVA ASIGNACIÓN INTELIGENTE ======= -->
<div id="modalAsignacion" class="modal-overlay">
    <div class="modal-box">
        <div class="modal-head">
            <div>
                <h2><i class="ti ti-user-plus" style="margin-right:8px;"></i>Nueva Asignación</h2>
                <p>Calculadora de proyección inteligente</p>
            </div>
            <button class="btn-close-modal" onclick="cerrarModal()"><i class="ti ti-x"></i></button>
        </div>
        <div class="modal-body">

            <div class="form-group">
                <label class="form-label"><i class="ti ti-search"></i> Buscar Pasante (por nombre o cédula)</label>
                <input type="text" class="form-input" id="inp-nombre" placeholder="Ej: García Rodríguez, María o V-28456123">
            </div>

            <div style="display:grid;grid-template-columns:1fr 1fr;gap:16px;">
                <div class="form-group">
                    <label class="form-label"><i class="ti ti-building"></i> Departamento</label>
                    <select class="form-input" id="inp-depto">
                        <option value="">Seleccionar...</option>
                        <option>Soporte Técnico</option>
                        <option>Redes y Telecomunicaciones</option>
                        <option>Desarrollo de Software</option>
                        <option>Administración</option>
                    </select>
                </div>
                <div class="form-group">
                    <label class="form-label"><i class="ti ti-building-hospital"></i> Institución</label>
                    <input type="text" class="form-input" id="inp-inst" placeholder="Ej: H. Ruiz Páez">
                </div>
            </div>

            <hr class="modal-divider">

            <!-- Horas Meta -->
            <div class="form-group">
                <label class="form-label"><i class="ti ti-target"></i> Horas Meta</label>
                <div class="horas-wrapper">
                    <input type="number" class="form-input" id="inp-horas" value="1440" min="1" oninput="recalcular()">
                    <button class="btn-reset-horas" onclick="resetHoras()">
                        <i class="ti ti-refresh"></i> Estándar (1440h)
                    </button>
                </div>
            </div>

            <!-- Jornada (estática — política institucional: siempre Tiempo Completo) -->
            <div class="form-group">
                <label class="form-label"><i class="ti ti-clock"></i> Jornada Diaria</label>
                <div class="jornada-grid" style="grid-template-columns:1fr;">
                    <div class="jornada-option selected" style="cursor:default;pointer-events:none;">
                        <span class="jornada-icon">🕗</span>
                        <span class="jornada-label">Tiempo Completo</span>
                        <span class="jornada-sub">8 horas / día</span>
                    </div>
                </div>
            </div>

            <!-- Fecha inicio -->
            <div class="form-group">
                <label class="form-label"><i class="ti ti-calendar"></i> Fecha de Inicio</label>
                <input type="date" class="form-input" id="inp-fecha" oninput="recalcular()">
            </div>

            <!-- TARJETA DE PROYECCIÓN -->
            <div class="proyeccion-card" id="proy-card">
                <div class="proy-title"><i class="ti ti-sparkles"></i> Proyección Inteligente de Culminación</div>
                <div class="proy-dato"><span>📋 Meta total</span><strong id="p-horas">—</strong></div>
                <div class="proy-dato"><span>⏱️ Horas por día</span><strong id="p-hxd">—</strong></div>
                <div class="proy-dato"><span>📆 Días hábiles</span><strong id="p-dias">—</strong></div>
                <div class="proy-dato"><span>🗓️ Fecha de inicio</span><strong id="p-inicio">—</strong></div>
                <div class="proy-highlight">🎓 Culminación estimada: <span id="p-fin">—</span></div>
            </div>

            <hr class="modal-divider">
            <button class="btn-submit" onclick="guardar()">
                <i class="ti ti-check"></i> Confirmar Asignación
            </button>

        </div>
    </div>
</div>

<script>
// Jornada fija por política institucional: siempre 8 h/día
const HORAS_DIA = 8;

function abrirModal() {
    document.getElementById('modalAsignacion').classList.add('active');
    const hoy = new Date().toISOString().split('T')[0];
    document.getElementById('inp-fecha').value = hoy;
    recalcular();
}
function cerrarModal() {
    document.getElementById('modalAsignacion').classList.remove('active');
}
document.getElementById('modalAsignacion').addEventListener('click', e => {
    if (e.target === e.currentTarget) cerrarModal();
});

function resetHoras() { document.getElementById('inp-horas').value = 1440; recalcular(); }

function recalcular() {
    const horas    = parseInt(document.getElementById('inp-horas').value) || 1440;
    const fechaStr = document.getElementById('inp-fecha').value;
    const card     = document.getElementById('proy-card');
    if (!fechaStr) { card.classList.remove('visible'); return; }

    // Jornada fija: 8 h/día (política institucional)
    const dias = Math.ceil(horas / HORAS_DIA);
    const inicio = new Date(fechaStr + 'T12:00:00');
    let fin = new Date(inicio);
    let contados = 0;
    while (contados < dias) {
        fin.setDate(fin.getDate() + 1);
        const d = fin.getDay();
        if (d !== 0 && d !== 6) contados++;
    }

    const fmt = { day:'2-digit', month:'long', year:'numeric' };
    document.getElementById('p-horas').textContent  = horas.toLocaleString() + ' horas';
    document.getElementById('p-hxd').textContent    = HORAS_DIA + ' h / día';
    document.getElementById('p-dias').textContent   = dias.toLocaleString() + ' días hábiles';
    document.getElementById('p-inicio').textContent = inicio.toLocaleDateString('es-VE', fmt);
    document.getElementById('p-fin').textContent    = fin.toLocaleDateString('es-VE', fmt);
    card.classList.add('visible');
}

function guardar() {
    const nombre = document.getElementById('inp-nombre').value.trim();
    const depto  = document.getElementById('inp-depto').value;
    const fecha  = document.getElementById('inp-fecha').value;
    if (!nombre || !depto || !fecha) {
        Swal.fire({ icon:'warning', title:'Campos incompletos',
            text:'Completa nombre, departamento y fecha de inicio.', confirmButtonColor:'#2563eb' });
        return;
    }
    Swal.fire({
        icon:'success', title:'¡Asignación Registrada!',
        html:`<p style="text-align:left;line-height:1.9">
                <b>Pasante:</b> ${nombre}<br>
                <b>Departamento:</b> ${depto}<br>
                <b>Meta:</b> ${document.getElementById('inp-horas').value}h (${HORAS_DIA}h/día)<br>
                <b>Culminación estimada:</b> ${document.getElementById('p-fin').textContent}
              </p>`,
        confirmButtonColor:'#2563eb', confirmButtonText:'Perfecto'
    }).then(() => cerrarModal());
}
// ── Editar Asignación (redirige al módulo de Asignaciones) ──
function abrirModalEditar(pasanteId) {
    window.location.href = URLROOT + '/asignaciones?editar=' + pasanteId;
}

// ── Cambiar Estado del Pasante ──
function cambiarEstado(pasanteId, nombre) {
    if (typeof Swal === 'undefined') return;
    Swal.fire({
        title: 'Cambiar Estado',
        html: '<p>Pasante: <strong>' + (nombre || 'Seleccionado') + '</strong></p>',
        input: 'select',
        inputOptions: {
            'Pendiente':  '⏳ Pendiente',
            'Activo':     '✅ Activo',
            'Finalizado': '🏆 Finalizado',
            'Retirado':   '❌ Retirado'
        },
        inputPlaceholder: 'Selecciona un estado',
        showCancelButton: true,
        confirmButtonText: 'Cambiar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#2563eb',
        inputValidator: function(value) {
            if (!value) return 'Debes seleccionar un estado';
        }
    }).then(async function(result) {
        if (!result.isConfirmed) return;
        try {
            var fd = new FormData();
            fd.append('pasante_id', pasanteId);
            fd.append('estado', result.value);
            var resp = await fetch(URLROOT + '/pasantes/cambiarEstado', {
                method: 'POST',
                body: fd,
                headers: { 'X-Requested-With': 'XMLHttpRequest' }
            });
            var json = await resp.json();
            if (json.success) {
                await Swal.fire({ icon: 'success', title: '¡Estado Actualizado!', text: json.message || 'El estado se cambió correctamente.', confirmButtonColor: '#2563eb' });
                window.location.reload();
            } else {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message || 'No se pudo cambiar el estado.', confirmButtonColor: '#2563eb' });
            }
        } catch (err) {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Intenta de nuevo.', confirmButtonColor: '#2563eb' });
        }
    });
}
</script>
