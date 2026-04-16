<?php
/**
 * Tutor — Asistencias de Mis Pasantes (Diaria / Semanal)
 */
$esDiaria   = $vista === 'diaria';
$esSemanal  = $vista === 'semanal';
$totalActivos = $totalActivos ?? 0;
$presentes    = $presentes    ?? 0;
$ausentes     = $ausentes     ?? 0;
$justificados = $justificados ?? 0;
$porcAsistencia = $totalActivos > 0 ? round(($presentes + $justificados) / $totalActivos * 100) : 0;
?>
<style>
/* ── Layout ── */
.ta-bento { display:flex; flex-direction:column; gap:20px; width:100%; }

/* ── Banner ── */
.ta-banner {
    background:linear-gradient(135deg,#172554 0%,#1e3a8a 50%,#2563eb 100%);
    border-radius:20px; padding:28px 36px; position:relative; overflow:hidden;
    display:flex; align-items:center; justify-content:space-between;
}
.ta-banner::before {
    content:''; position:absolute; top:-40px; right:-40px;
    width:220px; height:220px; background:rgba(255,255,255,0.05); border-radius:50%;
}
.ta-banner-left { display:flex; align-items:center; gap:14px; z-index:1; }
.ta-banner-icon { background:rgba(255,255,255,0.15); border-radius:14px; padding:13px; }
.ta-banner-title { color:#fff; font-size:1.6rem; font-weight:700; margin:0; }
.ta-banner-sub { color:rgba(255,255,255,0.7); margin:4px 0 0; font-size:.88rem; }
.ta-banner-right { display:flex; z-index:1; align-items:center; }
.ta-banner-controls {
    background:rgba(0,0,0,0.15); backdrop-filter:blur(10px);
    border:1px solid rgba(255,255,255,0.15); border-radius:50px;
    padding:8px 18px; display:flex; align-items:center; gap:10px;
}
.ta-view-toggle { display:flex; gap:.35rem; background:rgba(255,255,255,0.15); padding:4px; border-radius:8px; }
.ta-view-btn {
    padding:.38rem .85rem; border-radius:6px; font-size:.8rem; font-weight:600;
    border:none; cursor:pointer; background:none; color:rgba(255,255,255,0.75); transition:all .15s;
    text-decoration:none;
}
.ta-view-btn.active { background:rgba(255,255,255,0.25); color:#fff; box-shadow:0 1px 3px rgba(0,0,0,.2); }
.ta-reg-btn {
    padding:.45rem .9rem; border-radius:8px; font-size:.8rem; font-weight:600;
    border:none; cursor:pointer; background:rgba(255,255,255,0.2); color:#fff;
    transition:background .15s; display:flex; align-items:center; gap:5px;
}
.ta-reg-btn:hover { background:rgba(255,255,255,0.3); }

/* ── KPI Cards ── */
.ta-kpis { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
@media(max-width:900px){ .ta-kpis{grid-template-columns:repeat(2,1fr);} }
@media(max-width:540px){ .ta-kpis{grid-template-columns:1fr;} }
.ta-kpi {
    background:#fff; border-radius:16px; padding:20px 22px;
    box-shadow:0 2px 12px rgba(0,0,0,0.06);
    display:flex; justify-content:space-between; align-items:center;
    transition:all 0.3s cubic-bezier(0.4,0,0.2,1);
}
.ta-kpi:hover { transform:translateY(-3px); }
.ta-kpi.var-p { border-left:4px solid #10b981; }
.ta-kpi.var-a { border-left:4px solid #ef4444; }
.ta-kpi.var-j { border-left:4px solid #f59e0b; }
.ta-kpi.var-t { border-left:4px solid #6366f1; }
.ta-kpi:hover.var-p { box-shadow:0 10px 22px rgba(16,185,129,.22); }
.ta-kpi:hover.var-a { box-shadow:0 10px 22px rgba(239,68,68,.22); }
.ta-kpi:hover.var-j { box-shadow:0 10px 22px rgba(245,158,11,.22); }
.ta-kpi:hover.var-t { box-shadow:0 10px 22px rgba(99,102,241,.22); }
.ta-kpi-info p { color:#64748b; font-size:.78rem; font-weight:600; text-transform:uppercase; letter-spacing:.5px; margin:0 0 6px; }
.ta-kpi-info h2 { font-size:2.2rem; font-weight:800; margin:0; line-height:1; }
.ta-kpi.var-p .ta-kpi-info h2 { color:#10b981; }
.ta-kpi.var-a .ta-kpi-info h2 { color:#ef4444; }
.ta-kpi.var-j .ta-kpi-info h2 { color:#f59e0b; }
.ta-kpi.var-t .ta-kpi-info h2 { color:#6366f1; }
.ta-kpi-icon { width:44px; height:44px; border-radius:12px; display:flex; align-items:center; justify-content:center; font-size:1.3rem; flex-shrink:0; }
.ta-kpi.var-p .ta-kpi-icon { background:#d1fae5; color:#10b981; }
.ta-kpi.var-a .ta-kpi-icon { background:#fee2e2; color:#ef4444; }
.ta-kpi.var-j .ta-kpi-icon { background:#fef3c7; color:#f59e0b; }
.ta-kpi.var-t .ta-kpi-icon { background:#ede9fe; color:#6366f1; }

/* ── Content card ── */
.ta-card { background:#fff; border-radius:16px; padding:22px 24px; box-shadow:0 2px 12px rgba(0,0,0,0.06); }
/* DIARIA */
.ta-date-bar { display:flex; align-items:center; gap:.75rem; margin-bottom:1.25rem; flex-wrap:wrap; }
.ta-date-bar input[type=date] { padding:.5rem .85rem; border:1px solid #e2e8f0; border-radius:8px; background:#fff; color:#1e293b; font-size:.88rem; }
.ta-date-bar input[type=date]:focus { outline:none; border-color:#10b981; }
.ta-apply-btn { padding:.45rem .9rem; border-radius:8px; font-size:.8rem; font-weight:600; border:none; cursor:pointer; background:#10b981; color:#fff; transition:background .15s; }
.ta-apply-btn:hover { background:#059669; }
.ta-table { width:100%; border-collapse:collapse; }
.ta-table th { padding:.75rem 1rem; font-size:.75rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#64748b; background:#f8fafc; text-align:left; border-bottom:1px solid #e2e8f0; }
.ta-table td { padding:.75rem 1rem; font-size:.87rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; color:#1e293b; }
.ta-table tr:last-child td { border-bottom:none; }
.ta-table tr:hover td { background:#f8faff; }
.estado-badge { display:inline-block; padding:.18rem .65rem; border-radius:999px; font-size:.72rem; font-weight:600; }
.estado-badge.presente   { background:rgba(16,185,129,.15); color:#10b981; }
.estado-badge.ausente    { background:rgba(239,68,68,.15);  color:#ef4444; }
.estado-badge.justificado{ background:rgba(245,158,11,.15); color:#f59e0b; }
.estado-badge.sinmarcar  { background:rgba(100,116,139,.15);color:#64748b; }
.ta-mark-btn { padding:.3rem .65rem; border-radius:6px; font-size:.75rem; font-weight:600; border:none; cursor:pointer; background:#ede9fe; color:#6366f1; transition:all .15s; }
.ta-mark-btn:hover { background:#6366f1; color:#fff; }
/* SEMANAL */
.ta-week-nav { display:flex; align-items:center; justify-content:space-between; margin-bottom:1.25rem; }
.ta-week-nav a { text-decoration:none; color:#64748b; padding:.38rem .85rem; border:1px solid #e2e8f0; border-radius:8px; font-size:.83rem; font-weight:600; transition:all .15s; }
.ta-week-nav a:hover { border-color:#10b981; color:#10b981; }
.ta-week-title { font-weight:700; color:#162660; font-size:.95rem; }
.ta-heat-section { margin-bottom:1.5rem; }
.ta-heat-depto {
    font-size:.88rem; font-weight:700; color:#162660; margin-bottom:.6rem;
    padding:.4rem .9rem; background:rgba(16,185,129,.08); border-radius:6px;
    border-left:3px solid #10b981; cursor:pointer; user-select:none;
    display:flex; align-items:center; justify-content:space-between;
    transition:background .15s;
}
.ta-heat-depto:hover { background:rgba(16,185,129,.14); }
.ta-heat-depto .ta-chevron { transition:transform .2s; font-size:.85rem; color:#10b981; }
.ta-heat-section.ta-collapsed .ta-heat-depto .ta-chevron { transform:rotate(-90deg); }
.ta-heat-section.ta-collapsed .ta-heat-table { display:none; }
.ta-heat-table { width:100%; border-collapse:collapse; background:#fff; border:1px solid #e2e8f0; border-radius:10px; overflow:hidden; }
.ta-heat-table th { padding:.55rem .75rem; font-size:.73rem; font-weight:600; text-transform:uppercase; letter-spacing:.04em; color:#64748b; text-align:center; background:#f8fafc; border-bottom:1px solid #e2e8f0; }
.ta-heat-table th:first-child { text-align:left; }
.ta-heat-table td { padding:.55rem .75rem; font-size:.82rem; border-bottom:1px solid #f1f5f9; text-align:center; color:#1e293b; }
.ta-heat-table td:first-child { text-align:left; font-weight:500; }
.ta-heat-table tr:last-child td { border-bottom:none; }
.ta-cell-p { background:rgba(16,185,129,.15); color:#10b981; font-weight:700; border-radius:6px; padding:.2rem .45rem; }
.ta-cell-a { background:rgba(239,68,68,.15);  color:#ef4444; font-weight:700; border-radius:6px; padding:.2rem .45rem; }
.ta-cell-j { background:rgba(245,158,11,.15); color:#f59e0b; font-weight:700; border-radius:6px; padding:.2rem .45rem; }
.ta-cell-dash { color:#94a3b8; }
/* Modal */
.ta-modal-back { display:none; position:fixed; inset:0; background:rgba(0,0,0,.5); z-index:1050; align-items:center; justify-content:center; }
.ta-modal-back.open { display:flex; }
.ta-modal { background:#fff; border-radius:16px; padding:1.75rem; width:100%; max-width:460px; box-shadow:0 20px 60px rgba(0,0,0,.25); }
.ta-modal-title { font-size:1.1rem; font-weight:700; margin-bottom:1rem; color:#162660; }
.ta-form-row { margin-bottom:.9rem; }
.ta-form-row label { display:block; font-size:.8rem; font-weight:600; color:#64748b; margin-bottom:.35rem; }
.ta-form-row select, .ta-form-row input, .ta-form-row textarea { width:100%; padding:.6rem 1rem; border:1px solid #e2e8f0; border-radius:8px; background:#fff; color:#1e293b; font-size:.9rem; }
.ta-form-row select:focus, .ta-form-row input:focus, .ta-form-row textarea:focus { outline:none; border-color:#10b981; }
.ta-modal-actions { display:flex; gap:.75rem; margin-top:1rem; }
.ta-modal-actions button { flex:1; padding:.55rem; border-radius:8px; font-size:.87rem; font-weight:600; border:none; cursor:pointer; }
.ta-btn-cancel { background:#f1f5f9; color:#475569; }
.ta-btn-cancel:hover { background:#e2e8f0; }
.ta-btn-save { background:#10b981; color:#fff; }
.ta-btn-save:hover { background:#059669; }
</style>

<div class="ta-bento">

    <!-- ══ BANNER ══ -->
    <div class="ta-banner">
        <div class="ta-banner-left">
            <div class="ta-banner-icon">
                <i class="ti ti-clock-check" style="font-size:28px;color:white;"></i>
            </div>
            <div>
                <h1 class="ta-banner-title">Asistencias</h1>
                <p class="ta-banner-sub"><i class="ti ti-users-group"></i> Mis Pasantes · Control de Asistencia</p>
            </div>
        </div>
        <div class="ta-banner-right">
            <div class="ta-banner-controls">
                <div class="ta-view-toggle">
                    <a href="<?= URLROOT ?>/tutor/asistencias?vista=diaria" class="ta-view-btn <?= $esDiaria ? 'active' : '' ?>">
                        <i class="ti ti-calendar-day"></i> Diaria
                    </a>
                    <a href="<?= URLROOT ?>/tutor/asistencias?vista=semanal&semana=<?= $paramsUrl['semana'] ?>&anio=<?= $paramsUrl['anio'] ?>" class="ta-view-btn <?= $esSemanal ? 'active' : '' ?>">
                        <i class="ti ti-calendar-week"></i> Semanal
                    </a>
                </div>
                <div style="width:1px;height:24px;background:rgba(255,255,255,0.2);"></div>
                <button class="ta-reg-btn" onclick="document.getElementById('modalManual').classList.add('open')">
                    <i class="ti ti-plus"></i> Registrar Manual
                </button>
            </div>
        </div>
    </div>

    <!-- ══ KPIs ══ -->
    <div class="ta-kpis">
        <div class="ta-kpi var-p">
            <div class="ta-kpi-info">
                <p>Presentes</p>
                <h2><?= $presentes ?></h2>
            </div>
            <div class="ta-kpi-icon"><i class="ti ti-user-check"></i></div>
        </div>
        <div class="ta-kpi var-a">
            <div class="ta-kpi-info">
                <p>Ausentes</p>
                <h2><?= $ausentes ?></h2>
            </div>
            <div class="ta-kpi-icon"><i class="ti ti-user-x"></i></div>
        </div>
        <div class="ta-kpi var-j">
            <div class="ta-kpi-info">
                <p>Justificados</p>
                <h2><?= $justificados ?></h2>
            </div>
            <div class="ta-kpi-icon"><i class="ti ti-user-exclamation"></i></div>
        </div>
        <div class="ta-kpi var-t">
            <div class="ta-kpi-info">
                <p>% Asistencia</p>
                <h2><?= $porcAsistencia ?>%</h2>
            </div>
            <div class="ta-kpi-icon"><i class="ti ti-chart-pie"></i></div>
        </div>
    </div>

    <?php if ($esDiaria): ?>
    <!-- ── VISTA DIARIA ── -->
    <div class="ta-card">
    <div class="ta-date-bar">
        <input type="date" id="filtroFecha" value="<?= htmlspecialchars($paramsUrl['fecha']) ?>" max="<?= $hoy ?>">
        <button class="ta-apply-btn" onclick="filtrarFecha()"><i class="ti ti-filter"></i> Aplicar</button>
    </div>
    <div style="overflow-x:auto;">
        <table class="ta-table">
            <thead>
                <tr>
                    <th>Pasante</th>
                    <th>Depto.</th>
                    <th>Hora</th>
                    <th>Estado</th>
                    <th>Método</th>
                </tr>
            </thead>
            <tbody>
                <?php if (empty($registros) && empty($sinMarcar)): ?>
                <tr><td colspan="5" style="text-align:center; color:#94a3b8; padding:2.5rem;">
    <i class="ti ti-calendar-off" style="font-size:2rem;display:block;margin-bottom:8px;opacity:.5;"></i>
    Sin registros para esta fecha.
</td></tr>
                <?php endif; ?>
                <?php foreach ($registros as $r):
                    $est = strtolower($r->estado ?? '');
                    $estClass = str_contains($est, 'presente') ? 'presente' : (str_contains($est, 'ausente') ? 'ausente' : 'justificado');
                ?>
                <tr>
                    <td><strong><?= htmlspecialchars(($r->apellidos ?? '') . ', ' . ($r->nombres ?? '')) ?></strong><br><small style="color:var(--text-muted);">V-<?= htmlspecialchars($r->cedula ?? '') ?></small></td>
                    <td><?= htmlspecialchars($r->departamento_nombre ?? '—') ?></td>
                    <td><?= $r->hora_registro ? date('g:i A', strtotime($r->hora_registro)) : '—' ?></td>
                    <td><span class="estado-badge <?= $estClass ?>"><?= ucfirst($r->estado ?? '') ?></span></td>
                    <td style="color:var(--text-muted);"><?= htmlspecialchars($r->metodo ?? '—') ?></td>
                </tr>
                <?php endforeach; ?>
                <?php foreach ($sinMarcar as $sm): ?>
                <tr>
                    <td><strong><?= htmlspecialchars(($sm->apellidos ?? '') . ', ' . ($sm->nombres ?? '')) ?></strong><br><small style="color:var(--text-muted);">V-<?= htmlspecialchars($sm->cedula ?? '') ?></small></td>
                    <td><?= htmlspecialchars($sm->departamento_nombre ?? '—') ?></td>
                    <td>—</td>
                    <td><span class="estado-badge sinmarcar">Sin Marcar</span></td>
                    <td>
                        <button class="ta-mark-btn" onclick="abrirRegistroRapido(<?= $sm->id ?>, '<?= htmlspecialchars(($sm->nombres ?? '') . ' ' . ($sm->apellidos ?? '')) ?>')"><i class="ti ti-check"></i> Marcar</button>
                    </td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div>
    </div><!-- /.ta-card -->

    <?php elseif ($esSemanal): ?>
    <!-- ── VISTA SEMANAL ── -->
    <div class="ta-card">
    <div class="ta-week-nav">
        <a href="<?= $navSemana['ant_url'] ?? '#' ?>">← Anterior</a>
        <span class="ta-week-title"><?= htmlspecialchars($navSemana['texto'] ?? '') ?></span>
        <a href="<?= $navSemana['sig_url'] ?? '#' ?>">Siguiente →</a>
    </div>
    <?php foreach ($datosSemanales as $depto => $pasantesDepto): ?>
    <div class="ta-heat-section" onclick="taSemToggle(this)">
        <div class="ta-heat-depto">
            <?= htmlspecialchars($depto) ?>
            <span style="display:flex;align-items:center;gap:8px;">
                <span style="font-size:.75rem;font-weight:600;color:#64748b;background:#e2e8f0;border-radius:20px;padding:2px 8px;"><?= count($pasantesDepto) ?></span>
                <i class="ti ti-chevron-down ta-chevron"></i>
            </span>
        </div>
        <table class="ta-heat-table">
            <thead>
                <tr>
                    <th>Pasante</th>
                    <th>Lun</th><th>Mar</th><th>Mié</th><th>Jue</th><th>Vie</th>
                    <th>P</th><th>A</th><th>J</th>
                </tr>
            </thead>
            <tbody>
                <?php foreach ($pasantesDepto as $pid => $pd): ?>
                <tr>
                    <td><?= htmlspecialchars($pd['nombre']) ?></td>
                    <?php for ($d=1; $d<=5; $d++):
                        $letra = $pd['dias'][$d] ?? '-';
                        $cls = $letra === 'P' ? 'ta-cell-p' : ($letra === 'A' ? 'ta-cell-a' : ($letra === 'J' ? 'ta-cell-j' : 'ta-cell-dash'));
                    ?>
                    <td><span class="<?= $cls ?>"><?= $letra ?></span></td>
                    <?php endfor; ?>
                    <td style="font-weight:600; color:#10b981;"><?= $pd['totales']['P'] ?></td>
                    <td style="font-weight:600; color:#ef4444;"><?= $pd['totales']['A'] ?></td>
                    <td style="font-weight:600; color:#f59e0b;"><?= $pd['totales']['J'] ?></td>
                </tr>
                <?php endforeach; ?>
            </tbody>
        </table>
    </div><!-- /.ta-heat-section -->
    <?php endforeach; ?>
    <?php if (empty($datosSemanales)): ?>
        <p style="text-align:center; color:#94a3b8; padding:2rem;">Sin datos para esta semana.</p>
    <?php endif; ?>
    </div><!-- /.ta-card -->
    <?php endif; ?>

</div><!-- /.ta-bento -->

<!-- Modal Registro Manual -->
<div class="ta-modal-back" id="modalManual">
    <div class="ta-modal">
        <h3 class="ta-modal-title">Registrar Asistencia Manual</h3>
        <div class="ta-form-row">
            <label>Pasante</label>
            <select id="selectPasante">
                <option value="">Seleccionar…</option>
                <?php foreach ($misPasantes as $p): ?>
                <option value="<?= $p->id ?>"><?= htmlspecialchars(($p->apellidos ?? '') . ', ' . ($p->nombres ?? '')) ?> — V-<?= $p->cedula ?></option>
                <?php endforeach; ?>
            </select>
        </div>
        <div class="ta-form-row">
            <label>Fecha</label>
            <input type="date" id="manualFecha" value="<?= $hoy ?>" max="<?= $hoy ?>">
        </div>
        <div class="ta-form-row">
            <label>Estado</label>
            <select id="manualEstado" onchange="toggleMotivo()">
                <option value="Presente">Presente</option>
                <option value="Ausente">Ausente</option>
                <option value="Justificado">Justificado</option>
            </select>
        </div>
        <div class="ta-form-row" id="motivoRow" style="display:none;">
            <label>Motivo de Justificación</label>
            <textarea id="manualMotivo" rows="2" placeholder="Ej: Cita médica, diligencia personal…"></textarea>
        </div>
        <div class="ta-modal-actions">
            <button class="ta-btn-cancel" onclick="document.getElementById('modalManual').classList.remove('open')">Cancelar</button>
            <button class="ta-btn-save" id="btnGuardarManual" onclick="guardarManual()"><i class="ti ti-check"></i> Guardar</button>
        </div>
    </div>
</div>

<script>
function filtrarFecha() {
    const f = document.getElementById('filtroFecha').value;
    if (f) window.location.href = `${URLROOT}/tutor/asistencias?vista=diaria&fecha=${f}`;
}
function toggleMotivo() {
    const e = document.getElementById('manualEstado').value;
    document.getElementById('motivoRow').style.display = e === 'Justificado' ? 'block' : 'none';
}
function abrirRegistroRapido(pid, nombre) {
    document.getElementById('selectPasante').value = pid;
    document.getElementById('modalManual').classList.add('open');
}
async function guardarManual() {
    const pid    = document.getElementById('selectPasante').value;
    const fecha  = document.getElementById('manualFecha').value;
    const estado = document.getElementById('manualEstado').value;
    const motivo = document.getElementById('manualMotivo').value.trim();
    if (!pid) return Swal.fire('', 'Selecciona un pasante.', 'warning');
    if (!fecha) return Swal.fire('', 'Selecciona una fecha.', 'warning');
    if (estado === 'Justificado' && !motivo) return Swal.fire('', 'El motivo es obligatorio para Justificado.', 'warning');
    const btn = document.getElementById('btnGuardarManual');
    btn.disabled = true;
    const csrf = document.querySelector('meta[name="csrf-token"]')?.content || '';
    const fd = new FormData();
    fd.append('pasante_id', pid);
    fd.append('fecha', fecha);
    fd.append('estado', estado);
    fd.append('motivo_justificacion', motivo);
    fd.append('csrf_token', csrf);
    try {
        const res = await fetch(`${URLROOT}/asistencias/registro_manual`, { method:'POST', headers:{'X-CSRF-TOKEN':csrf}, body:fd });
        const data = await res.json();
        if (data.success) {
            Swal.fire('<i class="ti ti-circle-check"></i> Éxito', data.message, 'success').then(() => location.reload());
        } else {
            Swal.fire('Error', data.message, 'error');
        }
    } catch(e) { Swal.fire('Error', 'Error de red.', 'error'); }
    finally { btn.disabled = false; }
}
document.getElementById('modalManual').addEventListener('click', function(e) {
    if (e.target === this) this.classList.remove('open');
});
// Acordeón semanal por departamento
function taSemToggle(section) {
    section.classList.toggle('ta-collapsed');
}
</script>
