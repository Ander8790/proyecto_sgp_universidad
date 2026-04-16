<?php
// Mi Asistencia — Pasante SGP v3 Premium (Tabs: Semanal / Mensual / Total)
$pasante     = $data['pasante']     ?? null;
$asistencias = $data['asistencias'] ?? [];
$proRata     = $data['proRata']     ?? null;

$estadoPasantia = $pasante->estado_pasantia ?? 'Pendiente';
$sinAsignar     = in_array($estadoPasantia, ['Sin Asignar', 'Pendiente', '']);

$diasValidos  = (int)($proRata->dias_presentes  ?? 0);
$horasAcum    = (int)($proRata->horas_mostradas ?? 0);
$horasMeta    = (int)($proRata->horas_meta      ?? 1440);
$pct          = $horasMeta > 0 ? min(100, round($horasAcum / $horasMeta * 100)) : 0;

$cPresentes   = 0; $cAusentes = 0; $cJustificados = 0;
foreach ($asistencias as $a) {
    $est = $a->estado ?? '';
    if ($est === 'Presente')      $cPresentes++;
    elseif ($est === 'Ausente')   $cAusentes++;
    elseif ($est === 'Justificado') $cJustificados++;
}
$totalReg  = count($asistencias);
$porcAsist = $totalReg > 0 ? round(($cPresentes + $cJustificados) / $totalReg * 100) : 0;

// Construir mapa fecha → estado para el calendario mensual
$actMap = [];
foreach ($asistencias as $a) {
    if (!empty($a->fecha)) {
        $actMap[$a->fecha] = [
            'estado' => $a->estado ?? '',
            'hora'   => $a->hora_registro ?? '',
            'metodo' => $a->metodo ?? '',
        ];
    }
}

// Semana actual (lun → dom)
$hoy         = new DateTime();
$diaSemana   = (int)$hoy->format('N'); // 1=lun … 7=dom
$inicioSem   = (clone $hoy)->modify('-' . ($diaSemana - 1) . ' days');
$diasSemana  = [];
for ($i = 0; $i < 7; $i++) {
    $d = (clone $inicioSem)->modify("+$i days");
    $diasSemana[] = $d;
}

// Mes actual — grilla de calendario
$anioMes = (int)date('Y'); $mesMes = (int)date('m');
$primero = new DateTime("$anioMes-$mesMes-01");
$ultimoDia = (int)$primero->format('t');
$primerDow = (int)$primero->format('N'); // 1=lun

$nombDias = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
$nombMeses = ['','Enero','Febrero','Marzo','Abril','Mayo','Junio','Julio','Agosto','Septiembre','Octubre','Noviembre','Diciembre'];
?>
<style>
/* ── Base ── */
@keyframes fadeUp { from{opacity:0;transform:translateY(16px)} to{opacity:1;transform:translateY(0)} }
.as-wrap { display:flex; flex-direction:column; gap:20px; animation:fadeUp .4s ease; }

/* ── Header ── */
.as-hero {
    background: linear-gradient(135deg,#0f172a 0%,#1e3a8a 50%,#0d9488 100%);
    border-radius: 22px; padding: 28px 36px;
    display: flex; align-items: center; gap: 18px;
    position: relative; overflow: hidden;
}
.as-hero::before { content:''; position:absolute; top:-50px; right:-50px; width:220px; height:220px; background:rgba(255,255,255,.04); border-radius:50%; }

/* ── KPI Grid ── */
.as-kpi-grid { display:grid; grid-template-columns:repeat(4,1fr); gap:16px; }
@media(max-width:900px){ .as-kpi-grid{grid-template-columns:repeat(2,1fr);} }
@media(max-width:560px){ .as-kpi-grid{grid-template-columns:1fr 1fr;} }

.as-kpi {
    background:#fff; border-radius:18px; padding:20px;
    box-shadow:0 2px 14px rgba(0,0,0,.07);
    display:flex; justify-content:space-between; align-items:flex-start;
    transition:transform .2s,box-shadow .2s;
    border-top: 3px solid transparent;
}
.as-kpi:hover { transform:translateY(-4px); }
.as-kpi.g { border-top-color:#10b981; } .as-kpi.g:hover { box-shadow:0 10px 24px rgba(16,185,129,.2); }
.as-kpi.r { border-top-color:#ef4444; } .as-kpi.r:hover { box-shadow:0 10px 24px rgba(239,68,68,.2); }
.as-kpi.y { border-top-color:#f59e0b; } .as-kpi.y:hover { box-shadow:0 10px 24px rgba(245,158,11,.2); }
.as-kpi.b { border-top-color:#6366f1; } .as-kpi.b:hover { box-shadow:0 10px 24px rgba(99,102,241,.2); }
.as-kpi-lbl { font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.06em; color:#94a3b8; margin-bottom:6px; }
.as-kpi-num { font-size:2.4rem; font-weight:900; line-height:1; }
.as-kpi-ico { width:42px; height:42px; border-radius:11px; display:flex; align-items:center; justify-content:center; font-size:1.2rem; flex-shrink:0; }

/* ── Card ── */
.as-card { background:#fff; border-radius:20px; padding:24px; box-shadow:0 2px 14px rgba(0,0,0,.07); }
.as-card-ttl { font-size:1rem; font-weight:700; color:#0f172a; display:flex; align-items:center; gap:8px; margin-bottom:18px; }
.as-card-ttl i { color:#2563eb; font-size:1.1rem; }

/* ── Tabs ── */
.as-tabs { display:flex; gap:6px; background:#f1f5f9; border-radius:14px; padding:4px; }
.as-tab {
    flex:1; padding:9px 16px; border:none; border-radius:10px; cursor:pointer;
    font-size:.83rem; font-weight:700; color:#64748b; background:transparent;
    transition:all .2s; display:flex; align-items:center; justify-content:center; gap:6px;
}
.as-tab.active { background:#fff; color:#2563eb; box-shadow:0 2px 8px rgba(0,0,0,.08); }
.as-tab:hover:not(.active) { background:rgba(255,255,255,.5); color:#2563eb; }

/* ── Tab Panels ── */
.as-panel { display:none; }
.as-panel.active { display:block; animation:fadeUp .3s ease; }

/* ── SEMANAL ── */
.as-week-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:10px; }
@media(max-width:700px){ .as-week-grid{grid-template-columns:repeat(4,1fr);} }
@media(max-width:480px){ .as-week-grid{grid-template-columns:repeat(2,1fr);} }

.as-week-day {
    border-radius:16px; padding:16px 10px; text-align:center;
    display:flex; flex-direction:column; align-items:center; gap:8px;
    border: 2px solid transparent;
    transition: transform .2s, box-shadow .2s;
}
.as-week-day:hover { transform:translateY(-3px); }
.as-week-day.hoy { border-color:#2563eb; }
.as-week-day .dow { font-size:.7rem; font-weight:800; text-transform:uppercase; letter-spacing:.08em; }
.as-week-day .day-num { font-size:1.5rem; font-weight:900; line-height:1; }
.as-week-day .day-ico { width:32px; height:32px; border-radius:8px; display:flex; align-items:center; justify-content:center; font-size:1rem; }
.as-week-day .day-lbl { font-size:.65rem; font-weight:700; border-radius:20px; padding:2px 8px; }

/* ── MENSUAL (Calendario) ── */
.as-cal { width:100%; }
.as-cal-head { display:grid; grid-template-columns:repeat(7,1fr); gap:2px; margin-bottom:8px; }
.as-cal-head span { text-align:center; font-size:.7rem; font-weight:800; color:#94a3b8; text-transform:uppercase; letter-spacing:.06em; padding:6px 0; }
.as-cal-grid { display:grid; grid-template-columns:repeat(7,1fr); gap:4px; }
.as-cal-cell {
    aspect-ratio:1; border-radius:10px; display:flex;
    flex-direction:column; align-items:center; justify-content:center;
    font-size:.78rem; font-weight:700; cursor:default;
    transition:transform .15s, box-shadow .15s;
    position:relative;
}
.as-cal-cell:hover:not(.empty):not(.filler) { transform:scale(1.12); z-index:2; }
.as-cal-cell.filler { background:transparent; }
.as-cal-cell.empty  { background:#f8fafc; color:#cbd5e1; }
.as-cal-cell.Presente    { background:#dcfce7; color:#15803d; }
.as-cal-cell.Ausente     { background:#fee2e2; color:#dc2626; }
.as-cal-cell.Justificado { background:#dbeafe; color:#1d4ed8; }
.as-cal-cell.hoy         { outline:2px solid #2563eb; outline-offset:2px; }
.as-cal-cell .dot { width:5px; height:5px; border-radius:50%; background:currentColor; margin-top:2px; }

/* ── TOTAL (Tabla) ── */
.as-tbl { width:100%; border-collapse:collapse; }
.as-tbl th { padding:10px 14px; font-size:.72rem; font-weight:700; text-transform:uppercase; letter-spacing:.05em; color:#64748b; background:#f8fafc; text-align:left; border-bottom:2px solid #e2e8f0; white-space:nowrap; }
.as-tbl td { padding:12px 14px; font-size:.85rem; border-bottom:1px solid #f1f5f9; vertical-align:middle; }
.as-tbl tbody tr:hover { background:#f8fafc; }
.as-badge { display:inline-flex; align-items:center; gap:4px; padding:3px 10px; border-radius:6px; font-size:.72rem; font-weight:700; }
.as-metodo { display:inline-flex; align-items:center; gap:4px; padding:3px 8px; border-radius:6px; font-size:.72rem; font-weight:600; }

/* ── Progress bar ── */
.as-bar { height:8px; background:#e2e8f0; border-radius:999px; overflow:hidden; margin:.3rem 0; }
.as-bar-fill { height:100%; border-radius:999px; transition:width .8s cubic-bezier(.4,0,.2,1); }

/* ── Responsive ── */
@media(max-width:768px) {
    .as-hero { padding:20px 22px; }
    .as-tab span.tab-txt { display:none; }
}
</style>

<div class="as-wrap">

<!-- ── HERO ── -->
<div class="as-hero">
    <div style="background:rgba(255,255,255,.15);border-radius:14px;padding:14px;z-index:1;flex-shrink:0;">
        <i class="ti ti-calendar-check" style="font-size:28px;color:#fff;"></i>
    </div>
    <div style="z-index:1;">
        <h1 style="color:#fff;font-size:1.6rem;font-weight:800;margin:0 0 4px;">Mi Asistencia</h1>
        <p style="color:rgba(255,255,255,.7);margin:0;font-size:.88rem;">
            <i class="ti ti-list-check"></i> Registro completo de marcajes y justificaciones
        </p>
    </div>
    <div style="margin-left:auto;z-index:1;text-align:right;flex-shrink:0;">
        <div style="font-size:2rem;font-weight:900;color:#fff;line-height:1;"><?= $porcAsist ?>%</div>
        <div style="font-size:.75rem;color:rgba(255,255,255,.7);font-weight:600;">asistencia total</div>
    </div>
</div>

<?php if ($sinAsignar): ?>
<div style="background:#fff;border-radius:20px;padding:60px 30px;text-align:center;box-shadow:0 2px 14px rgba(0,0,0,.06);border:2px dashed #e2e8f0;">
    <i class="ti ti-calendar-off" style="font-size:3.5rem;color:#cbd5e1;display:block;margin-bottom:16px;"></i>
    <h3 style="color:#1e293b;margin:0 0 8px;font-weight:800;">Sin registros aún</h3>
    <p style="color:#94a3b8;max-width:400px;margin:0 auto;">Tu pasantía aún no ha sido activada. Contacta al equipo de Telemática.</p>
</div>
<?php else: ?>

<!-- ── KPI CARDS ── -->
<div class="as-kpi-grid">
    <?php $kpis = [
        ['lbl'=>'Presentes',   'num'=>$cPresentes,  'cls'=>'g', 'color'=>'#10b981', 'ibg'=>'#d1fae5', 'icon'=>'ti-check'],
        ['lbl'=>'Ausentes',    'num'=>$cAusentes,   'cls'=>'r', 'color'=>'#ef4444', 'ibg'=>'#fee2e2', 'icon'=>'ti-x'],
        ['lbl'=>'Justificados','num'=>$cJustificados,'cls'=>'y','color'=>'#f59e0b', 'ibg'=>'#fef3c7', 'icon'=>'ti-file-description'],
        ['lbl'=>'% Asistencia','num'=>$porcAsist.'%','cls'=>'b','color'=>'#6366f1', 'ibg'=>'#ede9fe', 'icon'=>'ti-percentage'],
    ];
    foreach ($kpis as $k): ?>
    <div class="as-kpi <?= $k['cls'] ?>">
        <div>
            <div class="as-kpi-lbl"><?= $k['lbl'] ?></div>
            <div class="as-kpi-num" style="color:<?= $k['color'] ?>;" <?= is_int($k['num']) ? "data-kpi-value=\"{$k['num']}\"" : '' ?>><?= $k['num'] ?></div>
        </div>
        <div class="as-kpi-ico" style="background:<?= $k['ibg'] ?>;color:<?= $k['color'] ?>;">
            <i class="ti <?= $k['icon'] ?>"></i>
        </div>
    </div>
    <?php endforeach; ?>
</div>

<!-- ── PROGRESO DE PASANTÍA ── -->
<div class="as-card">
    <div class="as-card-ttl"><i class="ti ti-trending-up"></i> Progreso de Pasantía
        <span style="margin-left:auto;font-size:.78rem;color:#94a3b8;font-weight:500;">Meta: <?= $horasMeta ?>h · <?= number_format($horasMeta/8) ?> días</span>
    </div>
    <div style="display:flex;justify-content:space-between;font-size:.8rem;color:#64748b;margin-bottom:5px;">
        <span><?= $diasValidos ?> días válidos · <?= $horasAcum ?>h acumuladas</span>
        <strong style="color:#2563eb;"><?= $pct ?>%</strong>
    </div>
    <div class="as-bar"><div class="as-bar-fill" style="width:<?= $pct ?>%;background:linear-gradient(90deg,#2563eb,#0d9488);"></div></div>
    <div style="display:flex;justify-content:space-between;font-size:.72rem;color:#94a3b8;margin-top:4px;">
        <span>0h</span><span><?= $horasMeta ?>h</span>
    </div>
</div>

<!-- ── TABS ── -->
<div class="as-card" style="padding:20px 24px;">
    <div style="display:flex;justify-content:space-between;align-items:center;margin-bottom:18px;flex-wrap:wrap;gap:12px;">
        <div class="as-card-ttl" style="margin-bottom:0;"><i class="ti ti-calendar"></i> Detalle de Registros</div>
        <div class="as-tabs">
            <button class="as-tab active" onclick="asTab('semanal',this)">
                <i class="ti ti-calendar-week"></i> <span class="tab-txt">Semanal</span>
            </button>
            <button class="as-tab" onclick="asTab('mensual',this)">
                <i class="ti ti-calendar-month"></i> <span class="tab-txt">Mensual</span>
            </button>
            <button class="as-tab" onclick="asTab('total',this)">
                <i class="ti ti-table"></i> <span class="tab-txt">Total</span>
            </button>
        </div>
    </div>

    <!-- ── PANEL SEMANAL ── -->
    <div id="panel-semanal" class="as-panel active">
        <div style="text-align:center;font-size:.78rem;color:#64748b;margin-bottom:16px;font-weight:600;">
            Semana del <?= $inicioSem->format('d/m') ?> al <?= (clone $inicioSem)->modify('+6 days')->format('d/m/Y') ?>
        </div>
        <div class="as-week-grid">
            <?php
            $dowNames = ['Lun','Mar','Mié','Jue','Vie','Sáb','Dom'];
            $cfgEst = [
                'Presente'    => ['bg'=>'#dcfce7','txt'=>'#15803d','ibg'=>'#bbf7d0','icon'=>'ti-check','lbl_bg'=>'#f0fdf4'],
                'Ausente'     => ['bg'=>'#fee2e2','txt'=>'#dc2626','ibg'=>'#fecaca','icon'=>'ti-x','lbl_bg'=>'#fff1f2'],
                'Justificado' => ['bg'=>'#dbeafe','txt'=>'#1d4ed8','ibg'=>'#bfdbfe','icon'=>'ti-file-description','lbl_bg'=>'#eff6ff'],
            ];
            foreach ($diasSemana as $i => $d):
                $fStr  = $d->format('Y-m-d');
                $esHoy = $fStr === $hoy->format('Y-m-d');
                $reg   = $actMap[$fStr] ?? null;
                $est   = $reg['estado'] ?? null;
                $c     = $est ? ($cfgEst[$est] ?? null) : null;
                $esFin = $d->format('N') >= 6; // sáb/dom
            ?>
            <div class="as-week-day<?= $esHoy ? ' hoy' : '' ?>"
                 style="background:<?= $c ? $c['bg'] : ($esFin ? '#fafafa' : '#f8fafc') ?>;<?= $esHoy ? 'background:'.($c ? $c['bg'] : '#eff6ff').';' : '' ?>">
                <div class="dow" style="color:<?= $c ? $c['txt'] : ($esFin ? '#cbd5e1' : '#64748b') ?>;">
                    <?= $dowNames[$i] ?>
                </div>
                <div class="day-num" style="color:<?= $c ? $c['txt'] : ($esFin ? '#cbd5e1' : '#1e293b') ?>;">
                    <?= (int)$d->format('d') ?>
                </div>
                <?php if ($c): ?>
                <div class="day-ico" style="background:<?= $c['ibg'] ?>;color:<?= $c['txt'] ?>;">
                    <i class="ti <?= $c['icon'] ?>"></i>
                </div>
                <div class="day-lbl" style="background:<?= $c['lbl_bg'] ?>;color:<?= $c['txt'] ?>;">
                    <?= $est ?>
                </div>
                <?php elseif ($esHoy): ?>
                <div style="font-size:.65rem;font-weight:700;color:#2563eb;background:#eff6ff;padding:2px 8px;border-radius:20px;">Hoy</div>
                <?php else: ?>
                <div style="font-size:.65rem;color:#cbd5e1;font-weight:600;"><?= $esFin ? 'Libre' : '—' ?></div>
                <?php endif; ?>
            </div>
            <?php endforeach; ?>
        </div>
    </div>

    <!-- ── PANEL MENSUAL ── -->
    <div id="panel-mensual" class="as-panel">
        <div style="text-align:center;font-size:.9rem;font-weight:800;color:#0f172a;margin-bottom:14px;">
            <?= $nombMeses[$mesMes] ?> <?= $anioMes ?>
        </div>
        <div class="as-cal-head">
            <?php foreach ($nombDias as $n): ?>
            <span><?= $n ?></span>
            <?php endforeach; ?>
        </div>
        <div class="as-cal-grid">
            <?php
            // Celdas vacías antes del día 1
            for ($i = 1; $i < $primerDow; $i++): ?>
            <div class="as-cal-cell filler"></div>
            <?php endfor; ?>
            <?php for ($day = 1; $day <= $ultimoDia; $day++):
                $fStr  = sprintf('%04d-%02d-%02d', $anioMes, $mesMes, $day);
                $esHoy = $fStr === $hoy->format('Y-m-d');
                $reg   = $actMap[$fStr] ?? null;
                $est   = $reg['estado'] ?? null;
                $cls   = $est ?: 'empty';
            ?>
            <div class="as-cal-cell <?= $cls ?><?= $esHoy ? ' hoy' : '' ?>" title="<?= $day . '/' . $mesMes . ($est ? " — $est" : '') ?>">
                <?= $day ?>
                <?php if ($est): ?><div class="dot"></div><?php endif; ?>
            </div>
            <?php endfor; ?>
        </div>
        <!-- Leyenda -->
        <div style="display:flex;gap:14px;justify-content:center;margin-top:14px;font-size:.72rem;font-weight:600;color:#64748b;flex-wrap:wrap;">
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;border-radius:3px;background:#dcfce7;display:inline-block;"></span>Presente</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;border-radius:3px;background:#fee2e2;display:inline-block;"></span>Ausente</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;border-radius:3px;background:#dbeafe;display:inline-block;"></span>Justificado</span>
            <span style="display:flex;align-items:center;gap:5px;"><span style="width:12px;height:12px;border-radius:3px;background:#f8fafc;border:1px solid #e2e8f0;display:inline-block;"></span>Sin registro</span>
        </div>
    </div>

    <!-- ── PANEL TOTAL ── -->
    <div id="panel-total" class="as-panel">
        <?php if (empty($asistencias)): ?>
        <div style="text-align:center;padding:48px;color:#94a3b8;">
            <i class="ti ti-calendar-off" style="font-size:2.5rem;display:block;margin-bottom:12px;opacity:.4;"></i>
            Sin registros de asistencia aún.
        </div>
        <?php else: ?>
        <div style="overflow-x:auto;">
            <table class="as-tbl">
                <thead>
                    <tr>
                        <th>Fecha</th>
                        <th>Hora</th>
                        <th>Método</th>
                        <th>Justificación</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                <?php
                $cfgBadge = [
                    'Presente'    => ['bg'=>'#dcfce7','color'=>'#15803d','icon'=>'ti-check'],
                    'Ausente'     => ['bg'=>'#fee2e2','color'=>'#dc2626','icon'=>'ti-x'],
                    'Justificado' => ['bg'=>'#dbeafe','color'=>'#1d4ed8','icon'=>'ti-file-description'],
                ];
                $cfgMetodo = [
                    'Kiosco' => ['bg'=>'#f0f4ff','color'=>'#4338ca','icon'=>'ti-device-tablet'],
                    'Manual' => ['bg'=>'#fef3c7','color'=>'#d97706','icon'=>'ti-pencil'],
                    'AutoFill'=>['bg'=>'#f0fdf4','color'=>'#15803d','icon'=>'ti-refresh'],
                ];
                foreach (array_reverse($asistencias) as $a):
                    $est = $a->estado ?? 'Presente';
                    $bc  = $cfgBadge[$est]  ?? ['bg'=>'#f1f5f9','color'=>'#64748b','icon'=>'ti-minus'];
                    $met = $a->metodo ?? 'Kiosco';
                    $mc  = $cfgMetodo[$met] ?? ['bg'=>'#f1f5f9','color'=>'#64748b','icon'=>'ti-help'];
                    $fecha = !empty($a->fecha) ? date('d/m/Y', strtotime($a->fecha)) : '—';
                    $hora  = !empty($a->hora_registro) ? date('g:i A', strtotime($a->hora_registro)) : '—';
                    $motivo = htmlspecialchars($a->motivo_justificacion ?? '');
                ?>
                <tr>
                    <td style="font-weight:700;color:#0f172a;white-space:nowrap;"><?= $fecha ?></td>
                    <td style="font-variant-numeric:tabular-nums;color:#475569;"><?= $hora ?></td>
                    <td>
                        <span class="as-metodo" style="background:<?= $mc['bg'] ?>;color:<?= $mc['color'] ?>;">
                            <i class="ti <?= $mc['icon'] ?>"></i> <?= $met ?>
                        </span>
                    </td>
                    <td style="max-width:200px;overflow:hidden;text-overflow:ellipsis;white-space:nowrap;color:#64748b;font-size:.8rem;" title="<?= $motivo ?>">
                        <?php if ($motivo): ?>
                        <?= $motivo ?>
                        <?php if (!empty($a->ruta_evidencia)): ?>
                        &nbsp;<a href="<?= URLROOT . htmlspecialchars($a->ruta_evidencia) ?>" target="_blank" title="Ver evidencia"
                                 style="color:#2563eb;font-size:.75rem;"><i class="ti ti-paperclip"></i></a>
                        <?php endif; ?>
                        <?php else: ?><span style="color:#cbd5e1;">—</span><?php endif; ?>
                    </td>
                    <td>
                        <span class="as-badge" style="background:<?= $bc['bg'] ?>;color:<?= $bc['color'] ?>;">
                            <i class="ti <?= $bc['icon'] ?>"></i> <?= $est ?>
                        </span>
                    </td>
                </tr>
                <?php endforeach; ?>
                </tbody>
            </table>
        </div>
        <div style="margin-top:12px;font-size:.78rem;color:#94a3b8;text-align:right;">
            <?= $totalReg ?> registros en total
        </div>
        <?php endif; ?>
    </div>
</div>

<?php endif; ?>
</div><!-- /as-wrap -->

<script>
function asTab(name, btn) {
    document.querySelectorAll('.as-panel').forEach(p => p.classList.remove('active'));
    document.querySelectorAll('.as-tab').forEach(b => b.classList.remove('active'));
    document.getElementById('panel-' + name).classList.add('active');
    btn.classList.add('active');
}
</script>
