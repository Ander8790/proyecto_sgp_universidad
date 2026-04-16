<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <style>
        body { font-family: 'Helvetica', 'Arial', sans-serif; font-size: 10px; color: #333; line-height: 1.4; margin: 0; padding: 0; }
        .header { text-align: center; border-bottom: 2px solid #162660; padding-bottom: 10px; margin-bottom: 20px; position: relative; }
        .logo-placeholder { position: absolute; left: 0; top: 0; width: 60px; height: 60px; background: #f0f0f0; border: 1px dashed #ccc; }
        .title { font-size: 14px; font-weight: bold; color: #162660; text-transform: uppercase; }
        .subtitle { font-size: 11px; margin-top: 5px; color: #666; }
        
        .section-title { background: #f1f5f9; padding: 5px 10px; font-weight: bold; border: 1px solid #cbd5e1; margin-top: 15px; text-transform: uppercase; letter-spacing: 0.5px; }
        
        table { width: 100%; border-collapse: collapse; margin-top: 10px; }
        th, td { border: 1px solid #cbd5e1; padding: 6px 8px; text-align: left; }
        th { background: #f8fafc; font-weight: bold; color: #162660; }
        
        .table-eval { margin-top: 20px; }
        .table-eval th { text-align: center; font-size: 9px; }
        .table-eval td { font-size: 9px; }
        .col-score { width: 30px; text-align: center; font-weight: bold; font-size: 12px; }
        
        .info-grid { display: block; margin-top: 10px; }
        .info-box { display: inline-block; width: 48%; vertical-align: top; margin-bottom: 10px; }
        .label { font-weight: bold; color: #64748b; font-size: 8px; text-transform: uppercase; }
        .value { border-bottom: 1px solid #e2e8f0; padding: 2px 0; font-size: 10px; min-height: 14px; }
        
        .footer-signatures { margin-top: 50px; }
        .sig-box { display: inline-block; width: 30%; text-align: center; padding-top: 40px; border-top: 1px solid #000; margin: 0 1.5%; }
        .sig-label { font-size: 8px; font-weight: bold; margin-top: 5px; }
        
        .page-break { page-break-after: always; }
    </style>
</head>
<body>
    <div class="header">
        <div class="title">Instituto de Salud Pública</div>
        <div class="subtitle">Despacho del Director General | Gestión de Recursos Humanos</div>
        <div class="subtitle" style="font-weight: bold; margin-top: 10px;">INSTRUMENTO DE EVALUACIÓN DEL PASANTE</div>
    </div>

    <div class="section-title">I. Datos de Identificación</div>
    <div class="info-grid">
        <div class="info-box">
            <div class="label">Nombres y Apellidos del Pasante</div>
            <div class="value"><?= htmlspecialchars($eval->nombres . ' ' . $eval->apellidos) ?></div>
        </div>
        <div class="info-box" style="margin-left: 4%;">
            <div class="label">Cédula de Identidad</div>
            <div class="value"><?= htmlspecialchars($eval->cedula) ?></div>
        </div>
        <div class="info-box">
            <div class="label">Departamento / Área de Adscripción</div>
            <div class="value"><?= htmlspecialchars($eval->departamento) ?></div>
        </div>
        <div class="info-box" style="margin-left: 4%;">
            <div class="label">Periodo Evaluado / Lapso Académico</div>
            <div class="value"><?= htmlspecialchars($eval->lapso_academico ?: 'N/A') ?></div>
        </div>
        <div class="info-box">
            <div class="label">Tutor Empresarial (ISP)</div>
            <div class="value"><?= htmlspecialchars($eval->tutor_nombres . ' ' . $eval->tutor_apellidos) ?></div>
        </div>
        <div class="info-box" style="margin-left: 4%;">
            <div class="label">Fecha de Evaluación</div>
            <div class="value"><?= date('d/m/Y', strtotime($eval->fecha_evaluacion)) ?></div>
        </div>
    </div>

    <div class="section-title">II. Criterios de Evaluación (Rango 1 - 5)</div>
    <table class="table-eval">
        <thead>
            <tr>
                <th style="text-align: left;">Factores a Evaluar</th>
                <th>1 (Def)</th>
                <th>2 (Reg)</th>
                <th>3 (Bue)</th>
                <th>4 (MB)</th>
                <th>5 (Exc)</th>
            </tr>
        </thead>
        <tbody>
            <?php 
            $criterios = [
                'Iniciativa y creatividad en las tareas' => 'criterio_iniciativa',
                'Interés por aprender y superarse' => 'criterio_interes',
                'Conocimiento técnico aplicado' => 'criterio_conocimiento',
                'Capacidad de análisis de problemas' => 'criterio_analisis',
                'Comunicación oral y escrita' => 'criterio_comunicacion',
                'Rapidez de aprendizaje' => 'criterio_aprendizaje',
                'Compañerismo y relaciones humanas' => 'criterio_companerismo',
                'Cooperación con el equipo' => 'criterio_cooperacion',
                'Puntualidad y asistencia' => 'criterio_puntualidad',
                'Presentación personal y orden' => 'criterio_presentacion',
                'Calidad en el desarrollo de actividades' => 'criterio_desarrollo',
                'Análisis e interpretación de resultados' => 'criterio_analisis_res',
                'Capacidad para llegar a conclusiones' => 'criterio_conclusiones',
                'Habilidad para realizar recomendaciones' => 'criterio_recomendacion'
            ];

            foreach ($criterios as $label => $campo): ?>
                <tr>
                    <td><?= $label ?></td>
                    <?php for($i=1; $i<=5; $i++): ?>
                        <td class="col-score"><?= ($eval->$campo == $i) ? 'X' : '' ?></td>
                    <?php endfor; ?>
                </tr>
            <?php endforeach; ?>
            <tr style="background: #f8fafc; font-weight: bold;">
                <td style="text-align: right; font-size: 11px;">PROMEDIO FINAL DE DESEMPEÑO:</td>
                <td colspan="5" style="text-align: center; font-size: 14px; color: #162660;"><?= number_format((float)$eval->promedio_final, 2) ?> / 5.00</td>
            </tr>
        </tbody>
    </table>

    <div class="section-title">III. Observaciones del Tutor</div>
    <div style="border: 1px solid #cbd5e1; padding: 10px; min-height: 80px; font-size: 10px; margin-top: 5px;">
        <?= !empty($eval->observaciones) ? nl2br(htmlspecialchars($eval->observaciones)) : 'Sin observaciones adicionales por parte del tutor evaluador.' ?>
    </div>

    <div class="footer-signatures">
        <div class="sig-box">
            <div class="sig-label">Evaluador (Sello y Firma)</div>
            <div style="font-size: 7px; margin-top: 10px;">Tutor Interno ISP</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Pasante (Firma)</div>
            <div style="font-size: 7px; margin-top: 10px;">Conformidad del Evaluado</div>
        </div>
        <div class="sig-box">
            <div class="sig-label">Recursos Humanos</div>
            <div style="font-size: 7px; margin-top: 10px;">Recepción Institucional</div>
        </div>
    </div>
</body>
</html>
