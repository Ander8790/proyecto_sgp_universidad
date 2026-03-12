<?php
/**
 * Vista: Evaluaciones de Pasantes — Planilla Digital
 * 14 criterios agrupados por categoría con selector visual 1-5
 */

$evaluaciones  = $data['evaluaciones']  ?? [];
$pasantes      = $data['pasantes']      ?? [];
$tutores       = $data['tutores']       ?? [];
$tutorActualId = $data['tutorActualId'] ?? null;
$total         = $data['total']         ?? 0;

// Definición de criterios agrupados por categoría
$categorias = [
    'Actitudes' => [
        ['campo' => 'criterio_iniciativa', 'label' => 'Iniciativa', 'icon' => 'ti-bulb', 'desc' => 'Capacidad para proponer ideas y actuar proactivamente'],
        ['campo' => 'criterio_interes',    'label' => 'Interés',    'icon' => 'ti-heart', 'desc' => 'Motivación y entusiasmo por las actividades asignadas'],
    ],
    'Competencias' => [
        ['campo' => 'criterio_conocimiento',  'label' => 'Conocimiento',  'icon' => 'ti-book',         'desc' => 'Dominio técnico y teórico en su área'],
        ['campo' => 'criterio_analisis',      'label' => 'Análisis',      'icon' => 'ti-brain',        'desc' => 'Capacidad para analizar problemas y situaciones'],
        ['campo' => 'criterio_comunicacion',  'label' => 'Comunicación',  'icon' => 'ti-message-dots', 'desc' => 'Habilidad para expresar ideas de forma clara'],
        ['campo' => 'criterio_aprendizaje',   'label' => 'Aprendizaje',   'icon' => 'ti-school',       'desc' => 'Velocidad y efectividad para adquirir nuevos conocimientos'],
    ],
    'Valores' => [
        ['campo' => 'criterio_companerismo', 'label' => 'Compañerismo', 'icon' => 'ti-users',     'desc' => 'Relación y apoyo con los compañeros de trabajo'],
        ['campo' => 'criterio_cooperacion',  'label' => 'Cooperación',  'icon' => 'ti-hand-stop', 'desc' => 'Disposición para colaborar en actividades grupales'],
    ],
    'Disciplina' => [
        ['campo' => 'criterio_puntualidad',   'label' => 'Puntualidad',   'icon' => 'ti-clock',        'desc' => 'Cumplimiento de horarios y compromisos'],
        ['campo' => 'criterio_presentacion',  'label' => 'Presentación',  'icon' => 'ti-shirt',        'desc' => 'Aspecto personal y cuidado en la presentación'],
    ],
    'Desempeño' => [
        ['campo' => 'criterio_desarrollo',      'label' => 'Desarrollo',            'icon' => 'ti-code',            'desc' => 'Calidad en el desarrollo de actividades asignadas'],
        ['campo' => 'criterio_analisis_res',     'label' => 'Análisis de Resultados','icon' => 'ti-chart-bar',       'desc' => 'Capacidad para interpretar y evaluar resultados'],
        ['campo' => 'criterio_conclusiones',     'label' => 'Conclusiones',          'icon' => 'ti-clipboard-check', 'desc' => 'Elaboración de conclusiones fundamentadas'],
        ['campo' => 'criterio_recomendacion',    'label' => 'Recomendaciones',       'icon' => 'ti-star',            'desc' => 'Aportes y sugerencias de mejora'],
    ],
];

$catColors = [
    'Actitudes'    => '#f59e0b',
    'Competencias' => '#2563eb',
    'Valores'      => '#10b981',
    'Disciplina'   => '#8b5cf6',
    'Desempeño'    => '#ef4444',
];
?>

<style>
/* ===== MODAL (Gold Standard) ===== */
.modal {
    display: none; position: fixed; z-index: 1100; inset: 0;
    background: rgba(15, 23, 42, 0.65); backdrop-filter: blur(6px);
    animation: fadeIn 0.3s; align-items: center; justify-content: center;
}
.modal.active { display: flex; }
.modal-content {
    background: white; border-radius: 24px; max-width: 700px; width: 94%;
    max-height: 92vh; display: flex; flex-direction: column; overflow: hidden;
    animation: slideUp 0.3s; box-shadow: 0 32px 80px rgba(15, 23, 42, 0.3);
}
@keyframes fadeIn { from { opacity: 0; } to { opacity: 1; } }
@keyframes slideUp { from { transform: translateY(24px); opacity: 0; } to { transform: translateY(0); opacity: 1; } }
.modal-header {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%);
    padding: 24px 28px; display: flex; justify-content: space-between;
    align-items: center; flex-shrink: 0; color: white;
}
.modal-header-info { display: flex; align-items: center; gap: 14px; }
.modal-header-icon {
    background: rgba(255,255,255,0.15); border-radius: 12px;
    width: 44px; height: 44px; display: flex; align-items: center;
    justify-content: center; font-size: 1.3rem; flex-shrink: 0;
}
.modal-title { font-size: 1.3rem; font-weight: 700; color: white !important; margin: 0; }
.modal-subtitle { font-size: 0.82rem; color: rgba(255,255,255,0.75); margin: 3px 0 0; }
.modal-body-scroll { padding: 28px; overflow-y: auto; flex: 1; }
.modal-close {
    background: rgba(255,255,255,0.15); border: none; color: white;
    width: 36px; height: 36px; display: flex; align-items: center;
    justify-content: center; border-radius: 50%; cursor: pointer;
    font-size: 1.1rem; transition: background 0.2s; flex-shrink: 0;
}
.modal-close:hover { background: rgba(255,255,255,0.3); }
.modal-close i { color: white !important; }

/* ===== STAR RATING ===== */
.star-rating { display: flex; gap: 4px; flex-direction: row-reverse; justify-content: flex-end; }
.star-rating input { display: none; }
.star-rating label {
    cursor: pointer; font-size: 1.6rem; color: #e2e8f0;
    transition: all 0.15s; line-height: 1;
}
.star-rating label:hover,
.star-rating label:hover ~ label,
.star-rating input:checked ~ label {
    color: #f59e0b;
    transform: scale(1.15);
}

/* ===== FORM ===== */
.input-modern {
    width: 100%; padding: 12px 16px; border: 2px solid #e5e7eb;
    border-radius: 12px; font-size: 0.9rem; transition: all 0.2s;
    background: white; color: #1e293b; font-weight: 500; box-sizing: border-box;
}
.input-modern:focus { outline: none; border-color: #2563eb; box-shadow: 0 0 0 4px rgba(37,99,235,0.1); }

/* ===== CATEGORY CARDS ===== */
.eval-category {
    border-radius: 16px; padding: 20px; margin-bottom: 20px;
    border: 1px solid #f1f5f9; background: #fafbfc;
}
.eval-category-header {
    display: flex; align-items: center; gap: 10px; margin-bottom: 16px;
    padding-bottom: 12px; border-bottom: 2px solid;
}
.eval-criterio {
    display: flex; align-items: center; justify-content: space-between;
    padding: 12px 0; border-bottom: 1px solid #f1f5f9; gap: 16px;
}
.eval-criterio:last-child { border-bottom: none; }

/* ===== RESPONSIVE ===== */
@media (max-width: 768px) {
    .eval-stats-grid { grid-template-columns: 1fr 1fr !important; }
    .eval-criterio { flex-direction: column; align-items: flex-start; }
}
</style>

<div class="dashboard-container" style="width: 100%; max-width: 100%; padding: 0;">

    <!-- ===== BANNER ===== -->
    <div style="background: linear-gradient(135deg, #172554 0%, #1e3a8a 50%, #2563eb 100%); border-radius: 20px; padding: 32px 40px; margin-bottom: 28px; position: relative; overflow: hidden; display: flex; align-items: center; justify-content: space-between;">
        <div style="position: absolute; top: -40px; right: -40px; width: 220px; height: 220px; background: rgba(255,255,255,0.04); border-radius: 50%;"></div>
        <div style="position: absolute; bottom: -60px; left: 30%; width: 160px; height: 160px; background: rgba(255,255,255,0.03); border-radius: 50%;"></div>
        <div>
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 10px;">
                    <i class="ti ti-star" style="font-size: 28px; color: white;"></i>
                </div>
                <div>
                    <h1 style="color: white; font-size: 1.8rem; font-weight: 700; margin: 0;">Evaluaciones de Pasantes</h1>
                    <p style="color: rgba(255,255,255,0.7); margin: 0; font-size: 0.9rem; display: flex; align-items: center; gap: 8px;">
                        <i class="ti ti-clipboard-check"></i>
                        Planilla Digital de Evaluación — 14 Criterios
                        <span style="background: rgba(255,255,255,0.2); color: white; padding: 2px 12px; border-radius: 20px; font-size: 0.8rem; font-weight: 600;">
                            <?= $total ?> evaluaciones
                        </span>
                    </p>
                </div>
            </div>
        </div>
        <div style="display: flex; gap: 12px; z-index: 1;">
            <button onclick="abrirModalEvaluacion()"
                style="background: white; color: #162660; border: none; padding: 12px 24px; border-radius: 12px; font-weight: 700; cursor: pointer; display: flex; align-items: center; gap: 8px; font-size: 0.95rem; box-shadow: 0 4px 12px rgba(0,0,0,0.2); transition: all 0.25s; flex-shrink: 0;"
                onmouseover="this.style.transform='translateY(-3px)';this.style.boxShadow='0 8px 24px rgba(0,0,0,0.3)'"
                onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='0 4px 12px rgba(0,0,0,0.2)'">
                <i class="ti ti-plus" style="font-size: 1.1rem;"></i>
                Nueva Evaluación
            </button>
        </div>
    </div>

    <!-- ===== KPIs (Interactivos) ===== -->
    <div class="eval-stats-grid" style="display: grid; grid-template-columns: repeat(3, 1fr); gap: 20px; margin-bottom: 28px;">
        <?php
            $promedioGlobal = 0;
            if (!empty($evaluaciones)) {
                $sumaPromedios = array_sum(array_map(fn($e) => (float)($e->promedio_final ?? 0), $evaluaciones));
                $promedioGlobal = round($sumaPromedios / count($evaluaciones), 2);
            }
        ?>
        <?php foreach ([
            ['label' => 'Total Evaluaciones', 'id' => 'kpi-total', 'value' => $total,           'sub' => 'realizadas',         'color' => '#1e3a8a', 'icon' => 'ti-file-analytics', 'filter' => ''],
            ['label' => 'Promedio Global',     'id' => 'kpi-prom',  'value' => $promedioGlobal . '/5', 'sub' => 'rendimiento general', 'color' => '#f59e0b', 'icon' => 'ti-star', 'filter' => ''],
            ['label' => 'Pasantes Evaluados',  'id' => 'kpi-users', 'value' => count(array_unique(array_map(fn($e) => $e->pasante_id, $evaluaciones))), 'sub' => 'con evaluación', 'color' => '#16a34a', 'icon' => 'ti-users', 'filter' => ''],
        ] as $s): ?>
        <div class="kpi-card" 
             style="border-left: 4px solid <?= $s['color'] ?>; cursor: pointer; transition: transform 0.2s, box-shadow 0.2s;"
             onmouseover="this.style.transform='translateY(-5px)';this.style.boxShadow='0 10px 20px rgba(0,0,0,0.1)'"
             onmouseout="this.style.transform='translateY(0)';this.style.boxShadow='none'">
            <div class="kpi-info">
                <p class="kpi-label"><?= $s['label'] ?></p>
                <h2 class="kpi-value" style="color: <?= $s['color'] ?>;"><?= $s['value'] ?></h2>
                <p class="kpi-sub"><?= $s['sub'] ?></p>
            </div>
            <div class="kpi-icon-box" style="background: <?= $s['color'] ?>18;">
                <i class="ti <?= $s['icon'] ?>" style="color: <?= $s['color'] ?>;"></i>
            </div>
        </div>
        <?php endforeach; ?>
    </div>

    <!-- ===== TABLA DE EVALUACIONES ===== -->
    <div style="background: white; border-radius: 16px; box-shadow: 0 2px 12px rgba(0,0,0,0.06); overflow: hidden;">
        <div style="padding: 20px 24px; border-bottom: 1px solid #f1f5f9; display: flex; justify-content: space-between; align-items: center;">
            <h3 style="font-size: 1rem; font-weight: 700; color: #1e293b; margin: 0;">
                <i class="ti ti-list" style="color: #162660;"></i> Historial de Evaluaciones
            </h3>
        </div>
        <div style="overflow-x: auto;">
            <table style="width: 100%; border-collapse: collapse;">
                <thead>
                    <tr style="background: #f8fafc;">
                        <?php foreach (['Pasante', 'Tutor Evaluador', 'Fecha', 'Lapso', 'Promedio', 'Acciones'] as $h): ?>
                        <th style="padding: 14px 20px; text-align: left; font-size: 0.8rem; color: #64748b; font-weight: 600; text-transform: uppercase; letter-spacing: 0.5px;"><?= $h ?></th>
                        <?php endforeach; ?>
                    </tr>
                </thead>
                <tbody>
                    <?php foreach ($evaluaciones as $ev):
                        $pNom = htmlspecialchars(($ev->pasante_nombres ?? '') . ' ' . ($ev->pasante_apellidos ?? ''));
                        $tNom = htmlspecialchars(($ev->tutor_nombres ?? '') . ' ' . ($ev->tutor_apellidos ?? ''));
                        $prom = (float)($ev->promedio_final ?? 0);
                        $promColor = $prom >= 4 ? '#10b981' : ($prom >= 3 ? '#f59e0b' : '#ef4444');
                        $promBg = $prom >= 4 ? '#dcfce7' : ($prom >= 3 ? '#fef3c7' : '#fee2e2');
                        $iniciales = strtoupper(substr($ev->pasante_nombres ?? '?', 0, 1) . substr($ev->pasante_apellidos ?? '', 0, 1));
                    ?>
                    <tr style="border-bottom: 1px solid #f1f5f9; transition: all 0.2s;"
                        onmouseover="this.style.background='#f8fafc'" onmouseout="this.style.background='white'">
                        <td style="padding: 16px 20px;">
                            <div style="display: flex; align-items: center; gap: 12px;">
                                <div style="width: 38px; height: 38px; border-radius: 50%; background: linear-gradient(135deg, #162660, #3b82f6); display: flex; align-items: center; justify-content: center; color: white; font-weight: 700; font-size: 0.85rem; flex-shrink: 0;">
                                    <?= $iniciales ?>
                                </div>
                                <div>
                                    <span style="font-weight: 600; color: #1e293b; font-size: 0.9rem;"><?= $pNom ?></span>
                                    <div style="font-size: 0.75rem; color: #94a3b8;"><?= htmlspecialchars($ev->pasante_cedula ?? '') ?></div>
                                </div>
                            </div>
                        </td>
                        <td style="padding: 16px 20px; color: #1e293b; font-size: 0.85rem; font-weight: 500;"><?= $tNom ?></td>
                        <td style="padding: 16px 20px; color: #64748b; font-size: 0.85rem;"><?= date('d/m/Y', strtotime($ev->fecha_evaluacion)) ?></td>
                        <td style="padding: 16px 20px; color: #64748b; font-size: 0.85rem;"><?= htmlspecialchars($ev->lapso_academico ?? '—') ?></td>
                        <td style="padding: 16px 20px;">
                            <span style="background: <?= $promBg ?>; color: <?= $promColor ?>; padding: 6px 14px; border-radius: 20px; font-size: 0.9rem; font-weight: 800;">
                                <?= number_format($prom, 1) ?>/5
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <div class="d-flex justify-content-center" style="gap: 12px;">
                                <button onclick="verEvaluacion(<?= (int)$ev->id ?>)"
                                        class="btn btn-sm border-0 shadow-sm transition-all" 
                                        data-bs-toggle="tooltip" title="Ver detalle" 
                                        style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #2563eb; color: #ffffff; border-radius: 6px !important;">
                                    <i class="ti ti-eye fs-5 text-white"></i>
                                </button>
                                
                                <a href="<?= URLROOT ?>/reportes/evaluacion_pdf/<?= $ev->id ?>" 
                                   target="_blank"
                                   class="btn btn-sm border-0 shadow-sm transition-all" 
                                   data-bs-toggle="tooltip" title="Exportar PDF" 
                                   style="width: 32px; height: 32px; padding: 0; display: inline-flex; align-items: center; justify-content: center; background-color: #dc2626; color: #ffffff; border-radius: 6px !important;">
                                    <i class="ti ti-file-type-pdf fs-5 text-white"></i>
                                </a>
                            </div>
                        </td>
                    </tr>
                    <?php endforeach; ?>
                </tbody>
            </table>
        </div>
    </div>

</div><!-- /dashboard-container -->

<!-- ===== MODAL DE EVALUACIÓN (Gold Standard + Planilla Digital) ===== -->
<div id="modalEvaluacion" class="modal">
    <div class="modal-content">
        <div class="modal-header">
            <div class="modal-header-info">
                <div class="modal-header-icon">
                    <i class="ti ti-star"></i>
                </div>
                <div>
                    <h2 class="modal-title">Nueva Evaluación</h2>
                    <p class="modal-subtitle">Planilla digital — 14 criterios de evaluación</p>
                </div>
            </div>
            <button class="modal-close" onclick="cerrarModalEval()">
                <i class="ti ti-x"></i>
            </button>
        </div>

        <div class="modal-body-scroll">
            <form id="formEvaluacion" onsubmit="submitEvaluacion(event)">

                <!-- Pasante y Tutor -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 18px;">
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                            <i class="ti ti-user" style="margin-right: 6px;"></i>Pasante *
                        </label>
                        <select name="pasante_id" required class="input-modern">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($pasantes as $p): ?>
                            <option value="<?= (int)$p->id ?>"><?= htmlspecialchars(($p->nombres ?? '') . ' ' . ($p->apellidos ?? '')) ?></option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                            <i class="ti ti-school" style="margin-right: 6px;"></i>Tutor *
                        </label>
                        <select name="tutor_id" required class="input-modern">
                            <option value="">Seleccionar...</option>
                            <?php foreach ($tutores as $t): ?>
                            <option value="<?= (int)$t->id ?>" <?= ($tutorActualId == $t->id) ? 'selected' : '' ?>>
                                <?= htmlspecialchars(($t->nombres ?? '') . ' ' . ($t->apellidos ?? '')) ?>
                            </option>
                            <?php endforeach; ?>
                        </select>
                    </div>
                </div>

                <!-- Fecha y Lapso -->
                <div style="display: grid; grid-template-columns: 1fr 1fr; gap: 14px; margin-bottom: 24px;">
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                            <i class="ti ti-calendar" style="margin-right: 6px;"></i>Fecha *
                        </label>
                        <input type="date" name="fecha_evaluacion" required class="input-modern" value="<?= date('Y-m-d') ?>">
                    </div>
                    <div>
                        <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                            <i class="ti ti-tag" style="margin-right: 6px;"></i>Lapso Académico
                        </label>
                        <input type="text" name="lapso_academico" class="input-modern" placeholder="Ej: 2025-II">
                    </div>
                </div>

                <!-- ===== CRITERIOS POR CATEGORÍA ===== -->
                <?php foreach ($categorias as $catNombre => $criterios):
                    $catColor = $catColors[$catNombre] ?? '#64748b';
                ?>
                <div class="eval-category">
                    <div class="eval-category-header" style="border-color: <?= $catColor ?>;">
                        <div style="background: <?= $catColor ?>18; border-radius: 10px; padding: 8px; display: flex; align-items: center; justify-content: center;">
                            <i class="ti ti-category" style="color: <?= $catColor ?>; font-size: 1.1rem;"></i>
                        </div>
                        <h4 style="margin: 0; color: <?= $catColor ?>; font-size: 1rem; font-weight: 700;"><?= $catNombre ?></h4>
                    </div>

                    <?php foreach ($criterios as $crit): ?>
                    <div class="eval-criterio">
                        <div style="flex: 1;">
                            <div style="display: flex; align-items: center; gap: 8px; margin-bottom: 4px;">
                                <i class="ti <?= $crit['icon'] ?>" style="color: <?= $catColor ?>; font-size: 1rem;"></i>
                                <span style="font-weight: 600; color: #1e293b; font-size: 0.9rem;"><?= $crit['label'] ?></span>
                            </div>
                            <span style="font-size: 0.78rem; color: #94a3b8;"><?= $crit['desc'] ?></span>
                        </div>
                        <div class="star-rating">
                            <?php for ($i = 5; $i >= 1; $i--): ?>
                            <input type="radio" name="<?= $crit['campo'] ?>" value="<?= $i ?>" id="<?= $crit['campo'] ?>_<?= $i ?>" required>
                            <label for="<?= $crit['campo'] ?>_<?= $i ?>" title="<?= $i ?>/5">★</label>
                            <?php endfor; ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
                <?php endforeach; ?>

                <!-- Promedio en tiempo real -->
                <div style="background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 100%); border: 2px solid #c7d2fe; border-radius: 16px; padding: 20px; margin-bottom: 20px; text-align: center;">
                    <p style="margin: 0 0 4px; font-size: 0.85rem; color: #64748b; font-weight: 600;">PROMEDIO CALCULADO</p>
                    <span id="promedioDisplay" style="font-size: 2.5rem; font-weight: 800; color: #162660;">0.00</span>
                    <span style="font-size: 1.2rem; color: #94a3b8; font-weight: 600;"> / 5</span>
                </div>

                <!-- Observaciones -->
                <div style="margin-bottom: 24px;">
                    <label style="display: block; font-weight: 600; color: #374151; margin-bottom: 8px; font-size: 0.9rem;">
                        <i class="ti ti-notes" style="margin-right: 6px;"></i>Observaciones Generales
                    </label>
                    <textarea name="observaciones" rows="3" class="input-modern" placeholder="Comentarios adicionales sobre el desempeño..." style="resize: vertical;"></textarea>
                </div>

                <!-- Botones -->
                <div style="display: flex; gap: 12px;">
                    <button type="button" onclick="cerrarModalEval()" style="flex: 1; padding: 14px; border: 1.5px solid #e2e8f0; border-radius: 12px; background: white; color: #64748b; font-weight: 600; cursor: pointer; font-size: 0.9rem;">
                        Cancelar
                    </button>
                    <button type="submit" id="btnGuardarEval"
                        style="flex: 2; padding: 14px; background: linear-gradient(135deg, #172554 0%, #1e3a8a 100%); border: none; border-radius: 12px; color: white; font-weight: 700; cursor: pointer; display: flex; align-items: center; justify-content: center; gap: 8px; font-size: 0.95rem;">
                        <i class="ti ti-check"></i> Guardar Evaluación
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

<!-- MODAL DETALLE DE EVALUACIÓN (Read-only) -->
<div id="modalDetalleEval" class="sgp-modal-overlay">
    <div class="sgp-modal" style="max-width: 650px; border-radius: 20px;">
        <div class="sgp-modal-header" style="padding: 24px;">
            <div style="display: flex; align-items: center; gap: 12px;">
                <div style="background: rgba(255,255,255,0.15); border-radius: 12px; padding: 10px;">
                    <i class="ti ti-file-analytics" style="font-size: 1.5rem; color: white;"></i>
                </div>
                <div>
                    <h3 style="margin: 0; font-size: 1.2rem;">Detalle de Evaluación</h3>
                    <p id="detalleEvalSubtitulo" style="margin: 2px 0 0; font-size: 0.8rem; opacity: 0.8;">Cargando información...</p>
                </div>
            </div>
            <button class="sgp-modal-close" onclick="cerrarModalDetalle()"><i class="ti ti-x"></i></button>
        </div>
        <div class="sgp-modal-body" id="bodyDetalleEval" style="padding: 24px; max-height: 70vh; overflow-y: auto;">
            <!-- Contenido dinámico -->
            <div style="text-align: center; padding: 40px;">
                <i class="ti ti-loader sgp-spin" style="font-size: 2rem; color: #1e3a8a;"></i>
            </div>
        </div>
        <div style="padding: 16px 24px; border-top: 1px solid #f1f5f9; display: flex; justify-content: flex-end; background: #f8fafc; border-bottom-left-radius: 20px; border-bottom-right-radius: 20px;">
            <button onclick="cerrarModalDetalle()" class="btn-banner-secondary" style="color:#64748b; border-color:#e2e8f0; background:white;">Cerrar</button>
        </div>
    </div>
</div>

<script>
// ── Modal Registro ──────────────────────────────────────────
function abrirModalEvaluacion() {
    document.getElementById('formEvaluacion').reset();
    document.getElementById('promedioDisplay').textContent = '0.00';
    <?php if ($tutorActualId): ?>
    document.querySelector('[name="tutor_id"]').value = '<?= $tutorActualId ?>';
    <?php endif; ?>
    document.getElementById('modalEvaluacion').classList.add('active');
    document.body.style.overflow = 'hidden';
}

function cerrarModalEval() {
    document.getElementById('modalEvaluacion').classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('modalEvaluacion').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalEval();
});

// ── Modal Detalle ───────────────────────────────────────────
function cerrarModalDetalle() {
    document.getElementById('modalDetalleEval').classList.remove('active');
    document.body.style.overflow = '';
}

document.getElementById('modalDetalleEval').addEventListener('click', function(e) {
    if (e.target === this) cerrarModalDetalle();
});

// ── Cálculo de Promedio en Tiempo Real ─────────────────────
document.getElementById('formEvaluacion').addEventListener('change', function() {
    var radios = this.querySelectorAll('input[type="radio"]:checked');
    if (radios.length === 0) return;
    var suma = 0;
    radios.forEach(function(r) { suma += parseInt(r.value); });
    var promedio = (suma / 14).toFixed(2);
    var display = document.getElementById('promedioDisplay');
    display.textContent = promedio;
    // Color dinámico
    var prom = parseFloat(promedio);
    if (prom >= 4) display.style.color = '#10b981';
    else if (prom >= 3) display.style.color = '#f59e0b';
    else display.style.color = '#ef4444';
});

// ── Enviar ─────────────────────────────────────────────────
async function submitEvaluacion(e) {
    e.preventDefault();

    // Validar que los 14 criterios estén llenados
    var radios = document.querySelectorAll('#formEvaluacion input[type="radio"]:checked');
    if (radios.length < 14) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'warning', title: 'Criterios incompletos', text: 'Debes evaluar los 14 criterios antes de guardar.', confirmButtonColor: '#162660' });
        }
        return;
    }

    var btn = document.getElementById('btnGuardarEval');
    btn.disabled = true;
    btn.innerHTML = '<i class="ti ti-loader"></i> Guardando...';

    var fd = new FormData(document.getElementById('formEvaluacion'));

    try {
        var resp = await fetch('<?= URLROOT ?>/evaluaciones/guardar', { method: 'POST', body: fd });
        var json = await resp.json();

        if (json.success) {
            cerrarModalEval();
            if (typeof Swal !== 'undefined') {
                await Swal.fire({
                    icon: 'success',
                    title: '¡Evaluación Guardada!',
                    html: '<p>' + json.message + '</p>',
                    confirmButtonColor: '#162660',
                });
            }
            window.location.reload();
        } else {
            if (typeof Swal !== 'undefined') {
                Swal.fire({ icon: 'error', title: 'Error', text: json.message, confirmButtonColor: '#162660' });
            }
            btn.disabled = false;
            btn.innerHTML = '<i class="ti ti-check"></i> Guardar Evaluación';
        }
    } catch (err) {
        if (typeof Swal !== 'undefined') {
            Swal.fire({ icon: 'error', title: 'Error de conexión', text: 'Intenta de nuevo.', confirmButtonColor: '#162660' });
        }
        btn.disabled = false;
        btn.innerHTML = '<i class="ti ti-check"></i> Guardar Evaluación';
    }
}

// ── Ver Detalle ─────────────────────────────────────────────
async function verEvaluacion(id) {
    document.getElementById('modalDetalleEval').classList.add('active');
    document.body.style.overflow = 'hidden';
    const body = document.getElementById('bodyDetalleEval');
    const subtitulo = document.getElementById('detalleEvalSubtitulo');
    
    body.innerHTML = '<div style="text-align: center; padding: 40px;"><i class="ti ti-loader sgp-spin" style="font-size: 2rem; color: #1e3a8a;"></i></div>';

    try {
        const resp = await fetch('<?= URLROOT ?>/evaluaciones/obtenerDetalleAjax/' + id);
        const data = await resp.json();

        if (data.success) {
            const ev = data.evaluacion;
            subtitulo.innerText = `Pasante: ${ev.pasante_nombre} • Fecha: ${ev.fecha_formateada}`;
            
            let itemsHtml = '';
            // Agrupar criterios por categorías (igual que en el PHP)
            const categorias = [
                { label: 'Actitudes y Comportamiento', color: '#3b82f6', items: [
                    { label: 'Interés por el aprendizaje', icon: 'ti-bulb', val: ev.c1 },
                    { label: 'Responsabilidad y puntualidad', icon: 'ti-clock', val: ev.c2 },
                    { label: 'Iniciativa y proactividad', icon: 'ti-rocket', val: ev.c3 }
                ]},
                { label: 'Competencias Técnicas', color: '#059669', items: [
                    { label: 'Conocimientos aplicados', icon: 'ti-cpu', val: ev.c4 },
                    { label: 'Calidad del trabajo', icon: 'ti-certificate', val: ev.c5 },
                    { label: 'Cumplimiento de tareas', icon: 'ti-list-check', val: ev.c6 }
                ]},
                { label: 'Valores e Integridad', color: '#d97706', items: [
                    { label: 'Ética y honestidad', icon: 'ti-shield-check', val: ev.c7 },
                    { label: 'Respeto y compañerismo', icon: 'ti-users', val: ev.c8 },
                    { label: 'Adaptabilidad a la cultura', icon: 'ti-adjustments-horizontal', val: ev.c9 }
                ]},
                { label: 'Disciplina y Organización', color: '#7c3aed', items: [
                    { label: 'Orden en el puesto de trabajo', icon: 'ti-layout-grid', val: ev.c10 },
                    { label: 'Uso de recursos institucionales', icon: 'ti-building', val: ev.c11 },
                    { label: 'Disposición hacia la institución', icon: 'ti-heart', val: ev.c12 }
                ]},
                { label: 'Desempeño Global', color: '#1e3a8a', items: [
                    { label: 'Capacidad de análisis', icon: 'ti-microscope', val: ev.c13 },
                    { label: 'Integración al equipo', icon: 'ti-brand-hipchat', val: ev.c14 }
                ]}
            ];

            categorias.forEach(cat => {
                itemsHtml += `
                    <div style="border-bottom: 2px solid ${cat.color}20; padding-bottom: 8px; margin-bottom: 12px; margin-top: 16px;">
                        <span style="font-size: 0.75rem; font-weight: 800; color: ${cat.color}; letter-spacing: 0.5px; text-transform: uppercase;">${cat.label}</span>
                    </div>
                    <div style="display: grid; grid-template-columns: 1fr; gap: 8px;">
                `;
                cat.items.forEach(it => {
                    itemsHtml += `
                        <div style="display: flex; justify-content: space-between; align-items: center; padding: 10px 14px; background: #f8fafc; border-radius: 12px; border: 1px solid #f1f5f9;">
                            <div style="display: flex; align-items: center; gap: 10px;">
                                <i class="ti ${it.icon}" style="color: ${cat.color};"></i>
                                <span style="font-size: 0.85rem; font-weight: 600; color: #334155;">${it.label}</span>
                            </div>
                            <div style="display: flex; color: #f59e0b; font-size: 1rem;">
                                ${'★'.repeat(it.val)}${'☆'.repeat(5 - it.val)}
                            </div>
                        </div>
                    `;
                });
                itemsHtml += `</div>`;
            });

            body.innerHTML = `
                <div style="background: linear-gradient(135deg, #eff6ff 0%, #f5f3ff 100%); border: 2px solid #c7d2fe; border-radius: 16px; padding: 20px; margin-bottom: 20px; text-align: center;">
                    <p style="margin: 0 0 4px; font-size: 0.8rem; color: #64748b; font-weight: 700;">PROMEDIO FINAL</p>
                    <span style="font-size: 2.2rem; font-weight: 900; color: #162660;">${ev.promedio}</span>
                    <span style="font-size: 1.1rem; color: #94a3b8; font-weight: 700;"> / 5</span>
                </div>
                ${itemsHtml}
                <div style="margin-top: 20px; padding: 16px; background: #fffbeb; border: 1px solid #fde68a; border-radius: 12px;">
                    <label style="display: block; font-weight: 800; color: #92400e; font-size: 0.75rem; text-transform: uppercase; margin-bottom: 8px;">Observaciones del Tutor</label>
                    <p style="margin: 0; font-size: 0.9rem; color: #78350f; line-height: 1.5;">${ev.observaciones || 'Sin observaciones registradas.'}</p>
                </div>
            `;
        } else {
            body.innerHTML = `<div style="text-align: center; padding: 40px; color: #dc2626;">Error: ${data.message}</div>`;
        }
    } catch (e) {
        body.innerHTML = '<div style="text-align: center; padding: 40px; color: #dc2626;">Error de conexión al obtener detalles.</div>';
    }
}

// Inicializar DataTables y Tooltips
$(document).ready(function() {
    // Si tienes un ID en la tabla, úsalo. Si no, usa la clase .table
    var $tablaEval = $('table');
    var tableInstance;
    if ($tablaEval.length && !$.fn.DataTable.isDataTable($tablaEval)) {
        tableInstance = $tablaEval.DataTable({
            language: { 
                url: '<?= URLROOT ?>/assets/libs/datatables/es-ES.json',
                emptyTable: '<div style="text-align: center; padding: 48px 20px; color: #94a3b8;"><i class="ti ti-clipboard-off" style="font-size: 48px; display: block; margin-bottom: 12px;"></i>No hay evaluaciones registradas aún.<br><span style="font-size: 0.85rem;">Presiona "Nueva Evaluación" para comenzar.</span></div>'
            },
            pageLength: 10,
            responsive: true,
            dom: '<"top"f>rt<"bottom"ip><"clear">',
            columnDefs: [{ orderable: false, targets: 5 }]
        });
    } else if ($tablaEval.length && $.fn.DataTable.isDataTable($tablaEval)) {
        tableInstance = $tablaEval.DataTable();
        tableInstance.draw(false);
    }

    // Tooltips
    var tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'))
    var tooltipList = tooltipTriggerList.map(function (tooltipTriggerEl) {
        return new bootstrap.Tooltip(tooltipTriggerEl)
    });
});
</script>
