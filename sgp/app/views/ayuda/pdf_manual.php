<?php
/**
 * Manual de Usuario SGP — Documento PDF Profesional
 * URL: /ayuda/pdf
 * Se renderiza como página HTML standalone y dispara window.print()
 */
$fechaDoc  = date('d/m/Y');
$anioDoc   = date('Y');
?><!DOCTYPE html>
<html lang="es">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1">
<title>Manual de Usuario — Registro y Control de Asistencias de Pasantes | ISP Bolívar</title>
<style>
/* ═══════════════════════════════════════════════════════
   RESET & BASE
═══════════════════════════════════════════════════════ */
*, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

@page {
    size: A4 portrait;
    margin: 1.8cm 2.2cm 2.8cm 2.2cm;
}
@page cover-page { margin: 0; }

body {
    font-family: 'Segoe UI', 'Helvetica Neue', Arial, sans-serif;
    font-size: 10.5pt;
    color: #1e293b;
    line-height: 1.68;
    background: white;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}

/* ═══════════════════════════════════════════════════════
   PORTADA
═══════════════════════════════════════════════════════ */
.cover {
    page: cover-page;
    width: 210mm;
    height: 297mm;
    break-after: page;
    display: flex;
    flex-direction: column;
    position: relative;
    overflow: hidden;
    background: linear-gradient(160deg, #0f172a 0%, #1e3a8a 55%, #2563eb 100%);
}

.cover-deco-top {
    position: absolute; top: -80px; right: -80px;
    width: 320px; height: 320px;
    background: rgba(255,255,255,.06); border-radius: 50%;
}
.cover-deco-bot {
    position: absolute; bottom: -100px; left: -60px;
    width: 380px; height: 380px;
    background: rgba(255,255,255,.04); border-radius: 50%;
}

.cover-header {
    padding: 2.2cm 2.5cm 0;
    display: flex;
    align-items: center;
    gap: 14px;
    z-index: 1;
}
.cover-inst-icon {
    width: 52px; height: 52px; border-radius: 14px;
    background: rgba(255,255,255,.15); display: flex;
    align-items: center; justify-content: center;
    font-size: 26px; color: white; font-weight: 900;
    font-family: 'Segoe UI', sans-serif;
}
.cover-inst-name {
    color: rgba(255,255,255,.85);
    font-size: 9.5pt;
    font-weight: 600;
    line-height: 1.4;
    max-width: 280px;
}

.cover-main {
    flex: 1;
    display: flex;
    flex-direction: column;
    align-items: flex-start;
    justify-content: center;
    padding: 0 2.5cm;
    z-index: 1;
}

.cover-tag {
    display: inline-block;
    background: rgba(255,255,255,.18);
    border: 1px solid rgba(255,255,255,.3);
    color: rgba(255,255,255,.9);
    font-size: 8pt;
    font-weight: 700;
    letter-spacing: 2px;
    text-transform: uppercase;
    padding: 5px 14px;
    border-radius: 20px;
    margin-bottom: 20px;
}

.cover-title {
    color: white;
    font-size: 36pt;
    font-weight: 900;
    line-height: 1.1;
    margin-bottom: 10px;
    letter-spacing: -1px;
}

.cover-subtitle {
    color: rgba(255,255,255,.78);
    font-size: 14pt;
    font-weight: 500;
    margin-bottom: 32px;
    line-height: 1.4;
}

.cover-divider {
    width: 60px; height: 4px;
    background: linear-gradient(90deg, #60a5fa, #a78bfa);
    border-radius: 2px;
    margin-bottom: 28px;
}

.cover-meta {
    display: flex;
    gap: 24px;
}
.cover-meta-item {
    background: rgba(255,255,255,.12);
    border: 1px solid rgba(255,255,255,.2);
    padding: 8px 18px;
    border-radius: 10px;
    color: rgba(255,255,255,.9);
    font-size: 8.5pt;
    font-weight: 600;
}
.cover-meta-label {
    font-size: 7pt;
    color: rgba(255,255,255,.55);
    display: block;
    margin-bottom: 2px;
    text-transform: uppercase;
    letter-spacing: 1px;
}

.cover-footer {
    padding: 1.5cm 2.5cm;
    border-top: 1px solid rgba(255,255,255,.15);
    z-index: 1;
    display: flex;
    justify-content: space-between;
    align-items: center;
}
.cover-footer-left {
    color: rgba(255,255,255,.5);
    font-size: 8pt;
}
.cover-classification {
    background: rgba(220,38,38,.25);
    border: 1px solid rgba(220,38,38,.4);
    color: rgba(255,150,150,.95);
    font-size: 7.5pt;
    font-weight: 700;
    letter-spacing: 1.5px;
    text-transform: uppercase;
    padding: 4px 12px;
    border-radius: 6px;
}

/* ═══════════════════════════════════════════════════════
   CONTROL DE DOCUMENTO
═══════════════════════════════════════════════════════ */
.ctrl-page {
    break-after: page;
    padding: 0.4cm 0;
}

/* ═══════════════════════════════════════════════════════
   TABLA DE CONTENIDO
═══════════════════════════════════════════════════════ */
.toc-page { break-after: page; }

.toc-title {
    font-size: 18pt;
    font-weight: 900;
    color: #0f172a;
    margin-bottom: 6px;
}
.toc-line {
    display: flex;
    align-items: baseline;
    margin-bottom: 5px;
    font-size: 9.5pt;
}
.toc-num {
    min-width: 26px;
    color: #2563eb;
    font-weight: 700;
    font-size: 9pt;
}
.toc-text { color: #1e293b; font-weight: 600; }
.toc-dots {
    flex: 1;
    border-bottom: 1px dotted #cbd5e1;
    margin: 0 6px;
    margin-bottom: 3px;
}
.toc-pg { color: #64748b; font-size: 8.5pt; font-weight: 700; min-width: 20px; text-align: right; }
.toc-section {
    margin-top: 18px;
    margin-bottom: 8px;
    font-size: 8pt;
    font-weight: 800;
    color: #64748b;
    text-transform: uppercase;
    letter-spacing: 1.5px;
    border-bottom: 1px solid #e2e8f0;
    padding-bottom: 4px;
}
.toc-sub {
    padding-left: 26px;
    font-weight: 500;
    color: #475569;
}

/* ═══════════════════════════════════════════════════════
   CAPÍTULOS
═══════════════════════════════════════════════════════ */
.chapter {
    break-before: page;
}

.ch-header {
    background: linear-gradient(135deg, #172554 0%, #1e3a8a 60%, #2563eb 100%);
    border-radius: 0 0 20px 20px;
    padding: 22px 26px 20px;
    margin-bottom: 22px;
    display: flex;
    align-items: center;
    gap: 14px;
    -webkit-print-color-adjust: exact;
    print-color-adjust: exact;
}
.ch-num {
    background: rgba(255,255,255,.18);
    color: rgba(255,255,255,.8);
    font-size: 8.5pt;
    font-weight: 800;
    letter-spacing: 1.5px;
    padding: 4px 10px;
    border-radius: 8px;
    text-transform: uppercase;
    min-width: 72px;
    text-align: center;
}
.ch-title {
    color: white;
    font-size: 16pt;
    font-weight: 800;
    line-height: 1.2;
}
.ch-sub {
    color: rgba(255,255,255,.65);
    font-size: 8.5pt;
    margin-top: 3px;
}

/* ═══════════════════════════════════════════════════════
   TIPOGRAFÍA DE CONTENIDO
═══════════════════════════════════════════════════════ */
.doc-p {
    color: #334155;
    font-size: 10pt;
    line-height: 1.75;
    margin-bottom: 12px;
}

.sec-title {
    font-size: 12pt;
    font-weight: 800;
    color: #0f172a;
    margin: 22px 0 8px;
    padding-bottom: 5px;
    border-bottom: 2px solid #e2e8f0;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sec-title .sec-n {
    color: #2563eb;
    font-size: 11pt;
}

.sec-sub {
    font-size: 10.5pt;
    font-weight: 700;
    color: #1e293b;
    margin: 14px 0 6px;
}

/* Pasos numerados */
.steps { counter-reset: step; display: flex; flex-direction: column; gap: 8px; margin: 10px 0 16px; }
.step { display: flex; gap: 12px; align-items: flex-start; }
.step::before {
    counter-increment: step; content: counter(step);
    min-width: 22px; height: 22px;
    background: #2563eb; color: white;
    border-radius: 50%; font-size: 8pt; font-weight: 800;
    display: flex; align-items: center; justify-content: center;
    margin-top: 2px; flex-shrink: 0;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
}
.step p { color: #475569; font-size: 9.5pt; line-height: 1.6; }
.step strong { color: #1e293b; }

/* Lista estándar */
.doc-ul { padding-left: 18px; margin: 8px 0 14px; }
.doc-ul li { color: #475569; font-size: 9.5pt; line-height: 1.65; margin-bottom: 4px; }
.doc-ul li strong { color: #1e293b; }

/* Tablas */
.doc-table {
    width: 100%; border-collapse: collapse;
    margin: 12px 0 18px; font-size: 9pt;
    break-inside: avoid;
}
.doc-table thead tr {
    background: #172554; color: white;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
}
.doc-table th { padding: 8px 12px; text-align: left; font-weight: 700; font-size: 8.5pt; }
.doc-table td { padding: 7px 12px; border-bottom: 1px solid #e2e8f0; vertical-align: top; }
.doc-table tbody tr:nth-child(even) td { background: #f8fafc; }
.doc-table tbody tr:hover td { background: #eff6ff; }

/* Cajas de notas */
.note-box {
    display: flex; gap: 12px; padding: 12px 16px;
    border-radius: 10px; margin: 12px 0 16px; font-size: 9pt;
    break-inside: avoid;
}
.note-box.info { background: #eff6ff; border-left: 4px solid #2563eb; }
.note-box.warn { background: #fffbeb; border-left: 4px solid #f59e0b; }
.note-box.danger { background: #fef2f2; border-left: 4px solid #dc2626; }
.note-box.success { background: #f0fdf4; border-left: 4px solid #16a34a; }
.note-icon { font-size: 14pt; flex-shrink: 0; margin-top: 2px; }
.note-box p { color: #334155; line-height: 1.6; }
.note-box strong { color: #1e293b; }

/* Badge de rol */
.badge {
    display: inline-block; padding: 2px 9px; border-radius: 20px;
    font-size: 7.5pt; font-weight: 700; letter-spacing: .5px;
    -webkit-print-color-adjust: exact; print-color-adjust: exact;
}
.badge-admin { background: #dbeafe; color: #1e40af; }
.badge-tutor { background: #dcfce7; color: #15803d; }
.badge-pasante { background: #f3e8ff; color: #7c3aed; }

/* Inline code */
.kbd {
    background: #f1f5f9; border: 1px solid #cbd5e1;
    padding: 1px 6px; border-radius: 4px;
    font-family: 'Courier New', monospace; font-size: 8.5pt; color: #0f172a;
}

/* Separador */
.sep { height: 1px; background: #e2e8f0; margin: 20px 0; }

/* Figura */
.fig-label {
    font-size: 8pt; color: #64748b; font-style: italic;
    text-align: center; margin-top: 4px; margin-bottom: 14px;
}

/* Glosario */
.glos-entry { margin-bottom: 12px; break-inside: avoid; }
.glos-term { font-weight: 800; color: #1e293b; font-size: 10pt; }
.glos-def { color: #475569; font-size: 9.5pt; padding-left: 14px; margin-top: 2px; }

/* URL/correo */
.doc-link { color: #2563eb; text-decoration: none; font-weight: 600; }

/* ═══════════════════════════════════════════════════════
   ENCABEZADO DE PÁGINA (en contenido)
═══════════════════════════════════════════════════════ */
@page {
    @top-right {
        content: "ISP Bolívar — Manual de Usuario v2.0";
        font-size: 7.5pt;
        color: #94a3b8;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    @bottom-center {
        content: "Página " counter(page);
        font-size: 7.5pt;
        color: #94a3b8;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
    @bottom-left {
        content: "Copyleft <?= $anioDoc ?> Instituto de Salud Pública del Estado Bolívar";
        font-size: 7pt;
        color: #cbd5e1;
        font-family: 'Segoe UI', Arial, sans-serif;
    }
}
@page cover-page {
    @top-right { content: none; }
    @bottom-center { content: none; }
    @bottom-left { content: none; }
}

/* ═══════════════════════════════════════════════════════
   PANTALLA — Previsualización antes de imprimir
═══════════════════════════════════════════════════════ */
@media screen {
    body {
        background: #e2e8f0;
        padding: 20px;
    }
    .cover { border-radius: 12px; box-shadow: 0 20px 60px rgba(0,0,0,.35); height: 297mm; max-width: 210mm; margin: 0 auto 30px; }
    .ctrl-page, .toc-page, .chapter {
        background: white; max-width: 210mm; margin: 0 auto 24px;
        padding: 2cm 2.5cm; border-radius: 8px; box-shadow: 0 4px 20px rgba(0,0,0,.1);
    }
    .ch-header { margin-left: -2.5cm; margin-right: -2.5cm; margin-top: -2cm; border-radius: 0; }
    .print-btn {
        position: fixed; bottom: 28px; right: 28px;
        background: #dc2626; color: white;
        border: none; padding: 12px 22px; border-radius: 12px;
        font-size: 13px; font-weight: 700; cursor: pointer;
        box-shadow: 0 6px 20px rgba(220,38,38,.4);
        display: flex; align-items: center; gap: 8px;
        z-index: 9999; transition: all .2s;
        font-family: inherit;
    }
    .print-btn:hover { background: #b91c1c; transform: translateY(-2px); }
}
@media print {
    .print-btn { display: none !important; }
    .cover { border-radius: 0; }
}
</style>
</head>
<body>

<!-- ════════════════════════════════════════════════════
     BOTÓN FLOTANTE (solo pantalla)
════════════════════════════════════════════════════ -->
<button class="print-btn" onclick="window.print()">
    ⬇ Guardar como PDF
</button>

<!-- ════════════════════════════════════════════════════
     1. PORTADA
════════════════════════════════════════════════════ -->
<div class="cover">
    <div class="cover-deco-top"></div>
    <div class="cover-deco-bot"></div>

    <div class="cover-header">
        <div class="cover-inst-icon">ISP</div>
        <div class="cover-inst-name">Instituto de Salud Pública<br>del Estado Bolívar</div>
    </div>

    <div class="cover-main">
        <span class="cover-tag">Documento Oficial</span>
        <h1 class="cover-title">Manual de<br>Usuario</h1>
        <p class="cover-subtitle">Registro y Control de Asistencias de Pasantes<br>División de Soporte Técnico — ISP Bolívar</p>
        <div class="cover-divider"></div>
        <div class="cover-meta">
            <div class="cover-meta-item">
                <span class="cover-meta-label">Versión</span>
                2.0
            </div>
            <div class="cover-meta-item">
                <span class="cover-meta-label">Fecha</span>
                <?= $fechaDoc ?>
            </div>
            <div class="cover-meta-item">
                <span class="cover-meta-label">Idioma</span>
                Español
            </div>
        </div>
    </div>

    <div class="cover-footer">
        <div class="cover-footer-left">Departamento de Tecnología de la Información &nbsp;·&nbsp; <?= $anioDoc ?></div>
        <div class="cover-classification">Uso Interno</div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     2. CONTROL DEL DOCUMENTO
════════════════════════════════════════════════════ -->
<div class="ctrl-page">
    <div class="sec-title" style="margin-top:0;border-color:#2563eb;">Control del Documento</div>

    <table class="doc-table">
        <thead>
            <tr><th>Campo</th><th>Valor</th></tr>
        </thead>
        <tbody>
            <tr><td><strong>Título</strong></td><td>Manual de Usuario — Aplicación Web para el Registro y Control de Asistencias de Pasantes (SGP)</td></tr>
            <tr><td><strong>Código</strong></td><td>SGP-MAN-001</td></tr>
            <tr><td><strong>Versión</strong></td><td>2.0</td></tr>
            <tr><td><strong>Fecha de emisión</strong></td><td><?= $fechaDoc ?></td></tr>
            <tr><td><strong>Clasificación</strong></td><td>Uso interno — Restringido al personal autorizado</td></tr>
            <tr><td><strong>Elaborado por</strong></td><td>Departamento de Tecnología de la Información</td></tr>
            <tr><td><strong>Revisado por</strong></td><td>Coordinación de Pasantías y Servicios Comunitarios</td></tr>
            <tr><td><strong>Plataforma</strong></td><td>Aplicación Web (PHP/MVC) — Alojada en servidor XAMPP</td></tr>
        </tbody>
    </table>

    <div class="sec-title">Historial de Revisiones</div>
    <table class="doc-table">
        <thead>
            <tr><th>Versión</th><th>Fecha</th><th>Descripción del cambio</th><th>Autor</th></tr>
        </thead>
        <tbody>
            <tr><td>1.0</td><td>2025-01</td><td>Versión inicial del sistema y documentación básica</td><td>Dpto. TI</td></tr>
            <tr><td>1.5</td><td>2025-06</td><td>Incorporación de Kiosco de asistencias y reportes PDF</td><td>Dpto. TI</td></tr>
            <tr><td>2.0</td><td><?= $fechaDoc ?></td><td>Rediseño completo Bento UI, módulos de actividades, exportación Excel, manual SPA</td><td>Dpto. TI</td></tr>
        </tbody>
    </table>

    <div class="note-box info">
        <span class="note-icon">ℹ️</span>
        <p>Este documento es de <strong>uso interno exclusivo</strong> del Instituto de Salud Pública del Estado Bolívar. Su reproducción o distribución fuera de la institución requiere autorización expresa de la Coordinación de TI.</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     3. TABLA DE CONTENIDO
════════════════════════════════════════════════════ -->
<div class="toc-page">
    <p class="toc-title">Tabla de Contenido</p>
    <div class="toc-section">Sección Preliminar</div>

    <div class="toc-line"><span class="toc-num">—</span><span class="toc-text">Control del Documento</span><span class="toc-dots"></span><span class="toc-pg">2</span></div>
    <div class="toc-line"><span class="toc-num">—</span><span class="toc-text">Tabla de Contenido</span><span class="toc-dots"></span><span class="toc-pg">3</span></div>

    <div class="toc-section">Capítulos</div>
    <div class="toc-line"><span class="toc-num">1.</span><span class="toc-text">Introducción</span><span class="toc-dots"></span><span class="toc-pg">4</span></div>
    <div class="toc-line toc-sub"><span class="toc-num">1.1</span><span class="toc-text">Propósito y alcance</span><span class="toc-dots"></span><span class="toc-pg">4</span></div>
    <div class="toc-line toc-sub"><span class="toc-num">1.2</span><span class="toc-text">Audiencia objetivo</span><span class="toc-dots"></span><span class="toc-pg">4</span></div>
    <div class="toc-line toc-sub"><span class="toc-num">1.3</span><span class="toc-text">Convenciones de la documentación</span><span class="toc-dots"></span><span class="toc-pg">4</span></div>

    <div class="toc-line"><span class="toc-num">2.</span><span class="toc-text">Requisitos del Sistema</span><span class="toc-dots"></span><span class="toc-pg">5</span></div>

    <div class="toc-line"><span class="toc-num">3.</span><span class="toc-text">Acceso al Sistema</span><span class="toc-dots"></span><span class="toc-pg">5</span></div>
    <div class="toc-line toc-sub"><span class="toc-num">3.1</span><span class="toc-text">Inicio de sesión</span><span class="toc-dots"></span><span class="toc-pg">5</span></div>
    <div class="toc-line toc-sub"><span class="toc-num">3.2</span><span class="toc-text">Cierre de sesión</span><span class="toc-dots"></span><span class="toc-pg">5</span></div>

    <div class="toc-line"><span class="toc-num">4.</span><span class="toc-text">Interfaz General</span><span class="toc-dots"></span><span class="toc-pg">6</span></div>
    <div class="toc-line"><span class="toc-num">5.</span><span class="toc-text">Panel Principal (Dashboard)</span><span class="toc-dots"></span><span class="toc-pg">7</span></div>
    <div class="toc-line"><span class="toc-num">6.</span><span class="toc-text">Módulo: Gestión de Pasantes</span><span class="toc-dots"></span><span class="toc-pg">8</span></div>
    <div class="toc-line"><span class="toc-num">7.</span><span class="toc-text">Módulo: Asistencias</span><span class="toc-dots"></span><span class="toc-pg">9</span></div>
    <div class="toc-line"><span class="toc-num">8.</span><span class="toc-text">Módulo: Actividades</span><span class="toc-dots"></span><span class="toc-pg">10</span></div>
    <div class="toc-line"><span class="toc-num">9.</span><span class="toc-text">Módulo: Períodos Académicos</span><span class="toc-dots"></span><span class="toc-pg">11</span></div>
    <div class="toc-line"><span class="toc-num">10.</span><span class="toc-text">Módulo: Gestión de Usuarios</span><span class="toc-dots"></span><span class="toc-pg">12</span></div>
    <div class="toc-line"><span class="toc-num">11.</span><span class="toc-text">Módulo: Reportes y Exportaciones</span><span class="toc-dots"></span><span class="toc-pg">13</span></div>
    <div class="toc-line"><span class="toc-num">12.</span><span class="toc-text">Kiosco de Asistencias</span><span class="toc-dots"></span><span class="toc-pg">14</span></div>
    <div class="toc-line"><span class="toc-num">13.</span><span class="toc-text">Perfil de Usuario</span><span class="toc-dots"></span><span class="toc-pg">15</span></div>
    <div class="toc-line"><span class="toc-num">14.</span><span class="toc-text">Bitácora (Pasante)</span><span class="toc-dots"></span><span class="toc-pg">15</span></div>
    <div class="toc-line"><span class="toc-num">15.</span><span class="toc-text">Solución de Problemas Frecuentes</span><span class="toc-dots"></span><span class="toc-pg">16</span></div>
    <div class="toc-line"><span class="toc-num">16.</span><span class="toc-text">Notificaciones de Escritorio</span><span class="toc-dots"></span><span class="toc-pg">17</span></div>

    <div class="toc-section">Apéndices</div>
    <div class="toc-line"><span class="toc-num">A.</span><span class="toc-text">Glosario de Términos</span><span class="toc-dots"></span><span class="toc-pg">18</span></div>
    <div class="toc-line"><span class="toc-num">B.</span><span class="toc-text">Referencias y Normativa</span><span class="toc-dots"></span><span class="toc-pg">19</span></div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 1 — INTRODUCCIÓN
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 1</div>
            <div class="ch-title">Introducción</div>
            <div class="ch-sub">Descripción del sistema, alcance y audiencia</div>
        </div>
    </div>

    <p class="doc-p">El <strong>Sistema de Gestión de Pasantías (SGP)</strong> es una aplicación web institucional desarrollada para el Instituto de Salud Pública del Estado Bolívar. Su propósito central es digitalizar y centralizar la administración de las pasantías y el servicio comunitario, garantizando trazabilidad, eficiencia operativa y cumplimiento de los estándares académicos.</p>

    <div class="sec-title"><span class="sec-n">1.1</span> Propósito y Alcance</div>
    <p class="doc-p">El SGP integra en una sola plataforma los procesos de: registro y control de pasantes, seguimiento de asistencias diarias, gestión de actividades académicas, administración de períodos académicos, generación de reportes certificados y control de acceso por roles.</p>
    <p class="doc-p">El sistema abarca todo el ciclo de vida de la pasantía: desde la inscripción del pasante hasta la emisión de la constancia de culminación, pasando por el registro diario de asistencias, la evaluación y las actividades complementarias.</p>

    <div class="sec-title"><span class="sec-n">1.2</span> Audiencia Objetivo</div>
    <p class="doc-p">Este manual está dirigido a los tres perfiles de usuario que opera el sistema:</p>
    <table class="doc-table">
        <thead><tr><th>Rol</th><th>Descripción</th><th>Módulos principales</th></tr></thead>
        <tbody>
            <tr>
                <td><span class="badge badge-admin">Administrador</span></td>
                <td>Coordinador de Pasantías o personal de TI. Acceso total al sistema.</td>
                <td>Todos los módulos</td>
            </tr>
            <tr>
                <td><span class="badge badge-tutor">Tutor</span></td>
                <td>Docente o profesional asignado al seguimiento del pasante.</td>
                <td>Asistencias, Actividades, Evaluaciones, Reportes</td>
            </tr>
            <tr>
                <td><span class="badge badge-pasante">Pasante</span></td>
                <td>Estudiante realizando su pasantía o servicio comunitario.</td>
                <td>Dashboard, Asistencias, Bitácora, Perfil, Constancia</td>
            </tr>
        </tbody>
    </table>

    <div class="sec-title"><span class="sec-n">1.3</span> Convenciones de la Documentación</div>
    <table class="doc-table">
        <thead><tr><th>Convención</th><th>Significado</th></tr></thead>
        <tbody>
            <tr><td><span class="badge badge-admin">Administrador</span></td><td>Funcionalidad disponible solo para el rol Administrador</td></tr>
            <tr><td><span class="badge badge-tutor">Tutor</span></td><td>Funcionalidad disponible para Tutor y/o Administrador</td></tr>
            <tr><td><span class="badge badge-pasante">Pasante</span></td><td>Funcionalidad disponible para el pasante</td></tr>
            <tr><td><span class="kbd">Botón</span></td><td>Elemento interactivo en la interfaz (botón, tecla, campo)</td></tr>
        </tbody>
    </table>
    <div class="note-box warn">
        <span class="note-icon">⚠️</span>
        <p>Las imágenes de interfaz mostradas en este manual son de referencia. La apariencia exacta puede variar según el período activo, los datos registrados y el rol del usuario.</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 2 — REQUISITOS DEL SISTEMA
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 2</div>
            <div class="ch-title">Requisitos del Sistema</div>
            <div class="ch-sub">Condiciones mínimas y recomendadas para operar el SGP</div>
        </div>
    </div>

    <div class="sec-title"><span class="sec-n">2.1</span> Navegadores Compatibles</div>
    <table class="doc-table">
        <thead><tr><th>Navegador</th><th>Versión mínima</th><th>Versión recomendada</th><th>Estado</th></tr></thead>
        <tbody>
            <tr><td>Google Chrome</td><td>90</td><td>120+</td><td>✅ Totalmente compatible</td></tr>
            <tr><td>Microsoft Edge</td><td>90</td><td>120+</td><td>✅ Totalmente compatible</td></tr>
            <tr><td>Mozilla Firefox</td><td>88</td><td>115+</td><td>✅ Totalmente compatible</td></tr>
            <tr><td>Safari</td><td>14</td><td>16+</td><td>⚠️ Compatible (sin garantía en PDF)</td></tr>
            <tr><td>Internet Explorer</td><td>—</td><td>—</td><td>❌ No soportado</td></tr>
        </tbody>
    </table>

    <div class="sec-title"><span class="sec-n">2.2</span> Configuración de Pantalla y Red</div>
    <ul class="doc-ul">
        <li><strong>Resolución mínima:</strong> 1024 × 768 píxeles</li>
        <li><strong>Resolución recomendada:</strong> 1280 × 800 o superior</li>
        <li><strong>JavaScript:</strong> Habilitado (obligatorio — el sistema depende de JavaScript)</li>
        <li><strong>Cookies:</strong> Habilitadas (para gestión de sesión)</li>
        <li><strong>Conexión:</strong> Red local institucional o Internet de banda ancha (mín. 5 Mbps)</li>
        <li><strong>PDF:</strong> Para imprimir reportes se requiere visor de PDF o impresora configurada</li>
    </ul>

    <div class="note-box info">
        <span class="note-icon">ℹ️</span>
        <p>El <strong>Kiosco de Asistencias</strong> debe operar en un equipo dedicado con Google Chrome en modo pantalla completa (<span class="kbd">F11</span>), conectado a la red local de la institución.</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 3 — ACCESO AL SISTEMA
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 3</div>
            <div class="ch-title">Acceso al Sistema</div>
            <div class="ch-sub">Inicio de sesión, recuperación de contraseña y cierre de sesión</div>
        </div>
    </div>

    <div class="sec-title"><span class="sec-n">3.1</span> Inicio de Sesión</div>
    <p class="doc-p">El SGP utiliza autenticación mediante <strong>Cédula de Identidad</strong> y <strong>Contraseña</strong>. Las credenciales son asignadas por el Administrador del sistema.</p>
    <div class="steps">
        <div class="step"><p>Abrir el navegador e ingresar la <strong>URL del sistema</strong> proporcionada por el Departamento de TI (ejemplo: <span class="kbd">http://sistema.isp-bolivar.edu.ve/sgp</span>).</p></div>
        <div class="step"><p>En el campo <strong>Cédula de Identidad</strong>, ingresar el número de cédula sin puntos ni espacios.</p></div>
        <div class="step"><p>En el campo <strong>Contraseña</strong>, ingresar la contraseña asignada (distingue mayúsculas/minúsculas).</p></div>
        <div class="step"><p>Hacer clic en el botón <span class="kbd">Iniciar Sesión</span>. El sistema validará las credenciales y redirigirá al Dashboard del rol correspondiente.</p></div>
    </div>
    <div class="note-box danger">
        <span class="note-icon">🔒</span>
        <p><strong>Seguridad:</strong> Después de 5 intentos fallidos de inicio de sesión, la cuenta puede quedar bloqueada temporalmente. Si olvida su contraseña, contacte al Administrador del sistema para que la restablezca.</p>
    </div>

    <div class="sec-title"><span class="sec-n">3.2</span> Cierre de Sesión</div>
    <p class="doc-p">Para cerrar la sesión correctamente:</p>
    <div class="steps">
        <div class="step"><p>Localizar el <strong>menú de usuario</strong> en la esquina superior derecha de la barra de navegación (muestra el nombre del usuario y un avatar).</p></div>
        <div class="step"><p>Hacer clic sobre el avatar o nombre de usuario para desplegar el menú desplegable.</p></div>
        <div class="step"><p>Seleccionar la opción <span class="kbd">Cerrar Sesión</span>. El sistema finalizará la sesión y redirigirá a la página de inicio de sesión.</p></div>
    </div>
    <div class="note-box warn">
        <span class="note-icon">⚠️</span>
        <p>No cierre el navegador directamente sin cerrar sesión, especialmente en equipos compartidos. Esto puede dejar la sesión activa y comprometer la seguridad de la cuenta.</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 4 — INTERFAZ GENERAL
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 4</div>
            <div class="ch-title">Interfaz General</div>
            <div class="ch-sub">Descripción de los elementos comunes de la interfaz de usuario</div>
        </div>
    </div>

    <p class="doc-p">La interfaz del SGP adopta un diseño <strong>Bento Box</strong> moderno, con componentes organizados en tarjetas bien delimitadas. La disposición es consistente en todas las vistas del sistema.</p>

    <div class="sec-title"><span class="sec-n">4.1</span> Panel Lateral (Sidebar)</div>
    <p class="doc-p">El panel lateral izquierdo contiene el menú de navegación principal. Sus elementos varían según el rol del usuario autenticado:</p>
    <table class="doc-table">
        <thead><tr><th>Ícono / Sección</th><th>Descripción</th><th>Roles</th></tr></thead>
        <tbody>
            <tr><td>Dashboard</td><td>Panel principal con métricas y resúmenes</td><td>Todos</td></tr>
            <tr><td>Pasantes</td><td>Gestión completa del catálogo de pasantes</td><td><span class="badge badge-admin">Admin</span></td></tr>
            <tr><td>Asistencias</td><td>Registro y consulta de asistencias</td><td>Todos</td></tr>
            <tr><td>Actividades</td><td>Actividades de servicio comunitario y pasantías</td><td><span class="badge badge-admin">Admin</span> <span class="badge badge-tutor">Tutor</span></td></tr>
            <tr><td>Períodos</td><td>Administración de períodos académicos</td><td><span class="badge badge-admin">Admin</span></td></tr>
            <tr><td>Usuarios</td><td>Creación y gestión de cuentas de usuario</td><td><span class="badge badge-admin">Admin</span></td></tr>
            <tr><td>Reportes</td><td>Generación de documentos PDF y Excel</td><td><span class="badge badge-admin">Admin</span> <span class="badge badge-tutor">Tutor</span></td></tr>
            <tr><td>Bitácora</td><td>Registro de actividades del pasante</td><td><span class="badge badge-pasante">Pasante</span></td></tr>
            <tr><td>Ayuda</td><td>Manual de usuario interactivo</td><td>Todos</td></tr>
        </tbody>
    </table>

    <div class="sec-title"><span class="sec-n">4.2</span> Barra Superior (Topbar)</div>
    <p class="doc-p">La barra superior contiene:</p>
    <ul class="doc-ul">
        <li><strong>Selector de Período Académico:</strong> Desplegable para cambiar el contexto entre períodos activos. Afecta todos los datos mostrados en el sistema.</li>
        <li><strong>Campana de Notificaciones:</strong> Muestra alertas y avisos del sistema en tiempo real.</li>
        <li><strong>Menú de Usuario:</strong> Acceso al perfil, configuración y cierre de sesión.</li>
    </ul>

    <div class="sec-title"><span class="sec-n">4.3</span> Navegación en Dispositivos Móviles</div>
    <p class="doc-p">En pantallas menores a 768px, el panel lateral se convierte en un <strong>dock inferior</strong> estilo móvil con íconos de acceso rápido. Las secciones adicionales se agrupan bajo el botón <span class="kbd">⋯ Más</span>.</p>

    <div class="sec-title"><span class="sec-n">4.4</span> Cambio de Período Activo</div>
    <div class="steps">
        <div class="step"><p>Localizar el selector de período en la barra superior, identificado como <span class="kbd">Período Activo</span>.</p></div>
        <div class="step"><p>Hacer clic para desplegar la lista de períodos disponibles.</p></div>
        <div class="step"><p>Seleccionar el período deseado. Todos los módulos del sistema actualizarán sus datos automáticamente.</p></div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 5 — DASHBOARD
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 5</div>
            <div class="ch-title">Panel Principal — Dashboard</div>
            <div class="ch-sub">Visión general del sistema según el rol del usuario</div>
        </div>
    </div>

    <p class="doc-p">El Dashboard es la primera pantalla visible al ingresar al sistema. Muestra un resumen ejecutivo del estado del período académico actual, adaptado al rol del usuario.</p>

    <div class="sec-title"><span class="sec-n">5.1</span> Tarjetas KPI (Administrador / Tutor)</div>
    <p class="doc-p">Las tarjetas en la parte superior del Dashboard muestran métricas clave del período en curso:</p>
    <table class="doc-table">
        <thead><tr><th>Tarjeta</th><th>Descripción</th></tr></thead>
        <tbody>
            <tr><td><strong>Total Pasantes</strong></td><td>Número total de pasantes registrados en el período activo</td></tr>
            <tr><td><strong>En Curso</strong></td><td>Pasantes con estado "Activo" realizando pasantía</td></tr>
            <tr><td><strong>Pendientes</strong></td><td>Pasantes registrados pero sin inicio formal de pasantía</td></tr>
            <tr><td><strong>Culminados</strong></td><td>Pasantes que completaron el 100% de horas requeridas</td></tr>
        </tbody>
    </table>

    <div class="sec-title"><span class="sec-n">5.2</span> Dashboard del Pasante</div>
    <p class="doc-p">El pasante visualiza su progreso personal:</p>
    <ul class="doc-ul">
        <li><strong>Barra de progreso de horas:</strong> Porcentaje de horas acumuladas vs. horas meta</li>
        <li><strong>Estado actual de la pasantía:</strong> Activo / Pendiente / Finalizado</li>
        <li><strong>Asistencias del mes:</strong> Resumen rápido del mes en curso</li>
        <li><strong>Próximas actividades:</strong> Actividades programadas a las que está inscrito</li>
    </ul>

    <div class="note-box success">
        <span class="note-icon">✅</span>
        <p>El indicador de <strong>Constancia de Culminación</strong> se activa automáticamente en el Dashboard del pasante cuando el período está <em>Cerrado</em> y las horas se han completado al 100%.</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 6 — GESTIÓN DE PASANTES
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 6</div>
            <div class="ch-title">Gestión de Pasantes</div>
            <div class="ch-sub"><span class="badge badge-admin" style="-webkit-print-color-adjust:exact;print-color-adjust:exact;">Administrador</span> &nbsp; Catálogo, asignación, estados y eliminación</div>
        </div>
    </div>

    <p class="doc-p">Este módulo permite al Administrador gestionar el ciclo de vida completo de cada pasante dentro del sistema: desde su registro hasta su culminación o baja.</p>

    <div class="sec-title"><span class="sec-n">6.1</span> Consulta Rápida de Pasante</div>
    <p class="doc-p">La barra de búsqueda en la cabecera del módulo permite localizar un pasante en tiempo real por <strong>cédula</strong>, <strong>nombre</strong> o <strong>apellido</strong>. El resultado muestra un panel con sus datos de asignación, estado y progreso de horas.</p>

    <div class="sec-title"><span class="sec-n">6.2</span> Registro de Nuevo Pasante</div>
    <div class="steps">
        <div class="step"><p>Navegar a <strong>Pasantes</strong> en el menú lateral.</p></div>
        <div class="step"><p>Hacer clic en el botón <span class="kbd">+ Nuevo Pasante</span> (esquina superior derecha de la tabla).</p></div>
        <div class="step"><p>Completar el formulario: <strong>Cédula</strong>, <strong>Correo electrónico</strong>, <strong>Nombres y Apellidos</strong>, <strong>Carrera</strong>, <strong>Horas Meta</strong>, y demás campos requeridos.</p></div>
        <div class="step"><p>Asignar <strong>Departamento</strong>, <strong>Tutor</strong> y <strong>Período Académico</strong> mediante los selectores correspondientes.</p></div>
        <div class="step"><p>Hacer clic en <span class="kbd">Guardar</span>. El sistema creará automáticamente la cuenta y generará un PIN de acceso al Kiosco.</p></div>
    </div>

    <div class="sec-title"><span class="sec-n">6.3</span> Estados de Pasantía</div>
    <table class="doc-table">
        <thead><tr><th>Estado</th><th>Descripción</th><th>Transición posible</th></tr></thead>
        <tbody>
            <tr><td><strong>Pendiente</strong></td><td>Registrado pero sin asignación completa o sin inicio formal</td><td>→ Activo</td></tr>
            <tr><td><strong>Activo</strong></td><td>Pasantía en curso — registra asistencias diariamente</td><td>→ Finalizado, Retirado</td></tr>
            <tr><td><strong>Finalizado</strong></td><td>Completó horas requeridas — habilitado para constancia</td><td>—</td></tr>
            <tr><td><strong>Retirado</strong></td><td>Baja anticipada por causas externas</td><td>—</td></tr>
        </tbody>
    </table>
    <div class="note-box warn">
        <span class="note-icon">⚠️</span>
        <p>Para cambiar el estado a <strong>Activo</strong>, el pasante debe tener asignado un Departamento y una Fecha de Inicio de Pasantía. Si no se cumplen estos requisitos, el sistema bloqueará la transición.</p>
    </div>

    <div class="sec-title"><span class="sec-n">6.4</span> Restablecer PIN de Kiosco</div>
    <p class="doc-p">Si un pasante olvida su PIN de acceso al Kiosco de Asistencias, el Administrador puede generar uno nuevo:</p>
    <div class="steps">
        <div class="step"><p>Localizar al pasante en la tabla o mediante la búsqueda rápida.</p></div>
        <div class="step"><p>Hacer clic en el botón de configuración <span class="kbd">⚙</span> de la fila correspondiente.</p></div>
        <div class="step"><p>Seleccionar <strong>Resetear PIN</strong>. El sistema generará un nuevo PIN de 4 dígitos y lo mostrará en pantalla.</p></div>
        <div class="step"><p>Comunicar el nuevo PIN al pasante de forma segura. El PIN solo se muestra una vez.</p></div>
    </div>

    <div class="sec-title"><span class="sec-n">6.5</span> Eliminación Permanente de Pasante</div>
    <div class="note-box danger">
        <span class="note-icon">🚨</span>
        <p><strong>Acción irreversible.</strong> La eliminación de un pasante borra permanentemente todas sus asistencias, evaluaciones, participación en actividades y datos personales. Solo debe ejecutarse para eliminar registros de prueba o duplicados erróneos.</p>
    </div>
    <div class="steps">
        <div class="step"><p>Localizar al pasante en la tabla.</p></div>
        <div class="step"><p>Hacer clic en el botón de eliminar <span class="kbd">🗑</span> (fondo rojo) de la fila.</p></div>
        <div class="step"><p><strong>Primera confirmación:</strong> Leer la advertencia y confirmar la intención de eliminar.</p></div>
        <div class="step"><p><strong>Segunda confirmación:</strong> Escribir exactamente <span class="kbd">ELIMINAR</span> en el campo de texto y confirmar. Si el texto no coincide exactamente, la operación no procede.</p></div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 7 — ASISTENCIAS
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 7</div>
            <div class="ch-title">Módulo de Asistencias</div>
            <div class="ch-sub">Registro, consulta y exportación de asistencias diarias</div>
        </div>
    </div>

    <p class="doc-p">El módulo de asistencias es el núcleo operativo del SGP. Registra la presencia diaria de cada pasante, calcula horas acumuladas y permite a los tutores y administradores hacer seguimiento en tiempo real.</p>

    <div class="sec-title"><span class="sec-n">7.1</span> Vista Mensual — Almanaque del Pasante</div>
    <p class="doc-p">La vista mensual muestra un almanaque calendario con el registro de asistencia de cada día hábil. Los estados posibles son:</p>
    <table class="doc-table">
        <thead><tr><th>Estado</th><th>Significado</th><th>Horas contadas</th></tr></thead>
        <tbody>
            <tr><td>✅ <strong>Presente</strong></td><td>Asistencia completa al día</td><td>8 horas</td></tr>
            <tr><td>🟡 <strong>Tarde</strong></td><td>Llegó tarde pero cumplió la jornada</td><td>8 horas (sin penalización)</td></tr>
            <tr><td>❌ <strong>Ausente</strong></td><td>No asistió sin justificación</td><td>0 horas</td></tr>
            <tr><td>📋 <strong>Justificado</strong></td><td>Ausencia por causa debidamente documentada</td><td>0 horas</td></tr>
        </tbody>
    </table>
    <div class="note-box info">
        <span class="note-icon">ℹ️</span>
        <p>El sistema contabiliza <strong>siempre 8 horas por día de asistencia</strong>, independientemente de la hora de llegada. Los retardos no penalizan las horas acumuladas.</p>
    </div>

    <div class="sec-title"><span class="sec-n">7.2</span> Registro Manual de Asistencia <span class="badge badge-admin">Admin</span> <span class="badge badge-tutor">Tutor</span></div>
    <div class="steps">
        <div class="step"><p>Navegar a <strong>Asistencias</strong> y seleccionar el pasante desde el buscador lateral.</p></div>
        <div class="step"><p>En el almanaque, hacer clic sobre el día a modificar.</p></div>
        <div class="step"><p>Seleccionar el estado correspondiente: Presente, Tarde, Ausente o Justificado.</p></div>
        <div class="step"><p>Hacer clic en <span class="kbd">Guardar</span>. El sistema actualizará el contador de horas automáticamente.</p></div>
    </div>

    <div class="sec-title"><span class="sec-n">7.3</span> Vista de Asistencia Total <span class="badge badge-admin">Admin</span></div>
    <p class="doc-p">La pestaña <strong>Asistencia Total</strong> (accesible desde el módulo de Asistencias) muestra un panel consolidado con el avance de todos los pasantes del período en curso. Desde esta vista se puede:</p>
    <ul class="doc-ul">
        <li>Filtrar pasantes por departamento, estado o tutor</li>
        <li>Ver el progreso de horas de cada pasante en un solo vistazo</li>
        <li>Exportar a <strong>PDF (Nómina Global)</strong> o <strong>Excel</strong> con un clic</li>
        <li>Acceder a la ficha personal individual de cada pasante</li>
    </ul>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 8 — ACTIVIDADES
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 8</div>
            <div class="ch-title">Módulo de Actividades</div>
            <div class="ch-sub">Gestión de servicio comunitario y actividades complementarias</div>
        </div>
    </div>

    <p class="doc-p">El módulo de Actividades gestiona las tareas y compromisos académicos que los pasantes realizan fuera del registro de asistencia diaria. Se organiza en dos categorías principales:</p>
    <table class="doc-table">
        <thead><tr><th>Tipo</th><th>Descripción</th></tr></thead>
        <tbody>
            <tr><td><strong>Servicio Comunitario</strong></td><td>Actividades de impacto social desarrolladas en instituciones externas</td></tr>
            <tr><td><strong>Actividades de Pasantía</strong></td><td>Tareas técnicas o académicas específicas del departamento asignado</td></tr>
        </tbody>
    </table>

    <div class="sec-title"><span class="sec-n">8.1</span> Crear una Actividad <span class="badge badge-admin">Admin</span> <span class="badge badge-tutor">Tutor</span></div>
    <div class="steps">
        <div class="step"><p>Navegar a <strong>Actividades</strong> y seleccionar la pestaña del tipo correspondiente (Servicio o Pasantía).</p></div>
        <div class="step"><p>Hacer clic en <span class="kbd">+ Nueva Actividad</span>.</p></div>
        <div class="step"><p>Completar: <strong>Nombre/Título</strong>, <strong>Tipo</strong>, <strong>Institución</strong>, <strong>Fecha de Inicio</strong> y <strong>Fecha de Fin</strong>.</p></div>
        <div class="step"><p>Hacer clic en <span class="kbd">Guardar Actividad</span>.</p></div>
    </div>

    <div class="sec-title"><span class="sec-n">8.2</span> Gestión de Participantes</div>
    <p class="doc-p">Cada actividad tiene asociada una lista de participantes. Para agregar pasantes a una actividad:</p>
    <div class="steps">
        <div class="step"><p>Abrir la actividad deseada haciendo clic en su nombre en la tabla.</p></div>
        <div class="step"><p>En la sección <strong>Participantes</strong>, hacer clic en <span class="kbd">+ Agregar Participante</span>.</p></div>
        <div class="step"><p>Buscar y seleccionar los pasantes a incluir.</p></div>
        <div class="step"><p>Confirmar la asignación. Los pasantes recibirán una notificación automática en el sistema.</p></div>
    </div>

    <div class="sec-title"><span class="sec-n">8.3</span> Gestión de Instituciones</div>
    <p class="doc-p">El catálogo de instituciones vinculadas a actividades se administra desde la pestaña <strong>Instituciones</strong> dentro del módulo de Actividades. Permite agregar, editar o desactivar instituciones externas.</p>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 9 — PERÍODOS ACADÉMICOS
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 9</div>
            <div class="ch-title">Períodos Académicos</div>
            <div class="ch-sub"><span class="badge badge-admin" style="-webkit-print-color-adjust:exact;print-color-adjust:exact;">Administrador</span> &nbsp; Creación, gestión y cierre de períodos</div>
        </div>
    </div>

    <p class="doc-p">Los períodos académicos son el eje temporal del sistema. Todos los datos de asistencias, evaluaciones y actividades están vinculados a un período específico.</p>

    <div class="sec-title"><span class="sec-n">9.1</span> Tipos de Período</div>
    <table class="doc-table">
        <thead><tr><th>Tipo</th><th>Duración típica</th><th>Descripción</th></tr></thead>
        <tbody>
            <tr><td><strong>Regular</strong></td><td>9 meses</td><td>Período académico ordinario del año escolar</td></tr>
            <tr><td><strong>Intensivo</strong></td><td>3 – 6 meses</td><td>Período de verano u otras modalidades aceleradas</td></tr>
        </tbody>
    </table>

    <div class="sec-title"><span class="sec-n">9.2</span> Ciclo de Vida de un Período</div>
    <p class="doc-p">Un período transita por los siguientes estados en secuencia:</p>
    <div class="steps">
        <div class="step"><p><strong>Planificado:</strong> El período ha sido creado pero aún no está en curso. Se pueden agregar pasantes pero no se registran asistencias.</p></div>
        <div class="step"><p><strong>Activo:</strong> El período está en curso. Los pasantes registran asistencias diariamente y el sistema contabiliza horas.</p></div>
        <div class="step"><p><strong>Cerrado:</strong> El período ha concluido. No se pueden agregar nuevos registros. Se habilita la generación de constancias de culminación para los pasantes que completaron sus horas.</p></div>
    </div>

    <div class="sec-title"><span class="sec-n">9.3</span> Crear y Eliminar Períodos</div>
    <ul class="doc-ul">
        <li><strong>Crear período:</strong> Botón <span class="kbd">+ Nuevo Período</span> → completar nombre, tipo, fechas y descripción.</li>
        <li><strong>Activar período:</strong> Solo puede haber un período <em>Activo</em> a la vez. Al activar uno, se sugiere cerrar el anterior.</li>
        <li><strong>Eliminar período:</strong> Solo se pueden eliminar períodos en estado <em>Planificado</em> o <em>Cerrado</em>. Si el período tiene pasantes asignados, se muestra una advertencia. La eliminación desvincula automáticamente a esos pasantes del período.</li>
    </ul>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 10 — GESTIÓN DE USUARIOS
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 10</div>
            <div class="ch-title">Gestión de Usuarios</div>
            <div class="ch-sub"><span class="badge badge-admin" style="-webkit-print-color-adjust:exact;print-color-adjust:exact;">Administrador</span> &nbsp; Creación, roles, activación y eliminación</div>
        </div>
    </div>

    <p class="doc-p">El módulo de Usuarios permite al Administrador gestionar todas las cuentas del sistema: administradores, tutores y pasantes (estos últimos también se gestionan desde el módulo de Pasantes).</p>

    <div class="sec-title"><span class="sec-n">10.1</span> Crear Nuevo Usuario</div>
    <div class="steps">
        <div class="step"><p>Navegar a <strong>Usuarios</strong> y hacer clic en <span class="kbd">+ Nuevo Usuario</span>.</p></div>
        <div class="step"><p>Completar los datos requeridos: <strong>Cédula</strong>, <strong>Correo electrónico</strong>, <strong>Rol</strong>, <strong>Contraseña inicial</strong>.</p></div>
        <div class="step"><p>El sistema verifica en tiempo real que la cédula y el correo no estén duplicados.</p></div>
        <div class="step"><p>Hacer clic en <span class="kbd">Guardar</span>. El usuario recibirá una notificación interna.</p></div>
    </div>

    <div class="sec-title"><span class="sec-n">10.2</span> Activar / Desactivar Usuario</div>
    <p class="doc-p">El botón de toggle en cada fila de la tabla permite activar o desactivar un usuario sin eliminarlo. Un usuario desactivado no puede iniciar sesión pero sus datos se conservan íntegramente.</p>

    <div class="sec-title"><span class="sec-n">10.3</span> Eliminación Permanente de Usuario</div>
    <div class="note-box danger">
        <span class="note-icon">🚨</span>
        <p><strong>Acción irreversible.</strong> La eliminación de un usuario borra permanentemente su cuenta y todos los datos asociados. Para tutores, se desvinculan automáticamente los pasantes que tenía asignados. Para pasantes, se eliminan todas sus asistencias, evaluaciones y actividades.</p>
    </div>
    <p class="doc-p">El proceso requiere <strong>doble confirmación</strong>: primero una alerta de advertencia, y luego escribir exactamente <span class="kbd">ELIMINAR</span> para confirmar. El sistema protege la cuenta de Administrador raíz para evitar eliminaciones accidentales.</p>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 11 — REPORTES
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 11</div>
            <div class="ch-title">Reportes y Exportaciones</div>
            <div class="ch-sub">Documentos PDF certificados y exportaciones Excel</div>
        </div>
    </div>

    <p class="doc-p">El módulo de Reportes genera documentos oficiales para uso institucional. Todos los PDFs incluyen el logo institucional, datos del pasante y el período académico correspondiente.</p>

    <div class="sec-title"><span class="sec-n">11.1</span> Tipos de Reporte</div>
    <table class="doc-table">
        <thead><tr><th>Reporte</th><th>Descripción</th><th>Acceso</th></tr></thead>
        <tbody>
            <tr><td><strong>Ficha Personal Individual</strong></td><td>Registro detallado de asistencias de un pasante por mes y semana</td><td><span class="badge badge-admin">Admin</span> <span class="badge badge-tutor">Tutor</span></td></tr>
            <tr><td><strong>Nómina Global (PDF)</strong></td><td>Listado consolidado de todos los pasantes del período activo con sus horas</td><td><span class="badge badge-admin">Admin</span></td></tr>
            <tr><td><strong>Nómina Global (Excel)</strong></td><td>Exportación en formato .xlsx de la nómina global para procesamiento externo</td><td><span class="badge badge-admin">Admin</span></td></tr>
            <tr><td><strong>Evaluación ISP</strong></td><td>Planilla de evaluación de desempeño en formato oficial institucional</td><td><span class="badge badge-admin">Admin</span> <span class="badge badge-tutor">Tutor</span></td></tr>
            <tr><td><strong>Constancia de Culminación</strong></td><td>Documento oficial que certifica la finalización exitosa de la pasantía</td><td><span class="badge badge-pasante">Pasante</span></td></tr>
            <tr><td><strong>Exportar Excel Individual</strong></td><td>Historial de asistencias de un pasante específico en formato .xlsx</td><td><span class="badge badge-admin">Admin</span> <span class="badge badge-tutor">Tutor</span></td></tr>
        </tbody>
    </table>

    <div class="sec-title"><span class="sec-n">11.2</span> Generar Ficha Personal</div>
    <div class="steps">
        <div class="step"><p>Desde el módulo de <strong>Asistencias</strong>, buscar y seleccionar el pasante.</p></div>
        <div class="step"><p>Hacer clic en el botón <span class="kbd">PDF</span> (rojo) en la sección de acciones del pasante.</p></div>
        <div class="step"><p>El documento se abrirá en una nueva pestaña del navegador con el visor de PDF.</p></div>
        <div class="step"><p>Usar el botón <span class="kbd">⬇ Descargar</span> del visor o la función de impresión del navegador para obtener el documento físico.</p></div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 12 — KIOSCO
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 12</div>
            <div class="ch-title">Kiosco de Asistencias</div>
            <div class="ch-sub">Terminal de registro autónomo mediante PIN personal</div>
        </div>
    </div>

    <p class="doc-p">El Kiosco de Asistencias es una pantalla de uso exclusivo en el área de entrada de la institución. Permite a los pasantes registrar su asistencia de forma autónoma ingresando su PIN de 4 dígitos, sin necesidad de credenciales de acceso al sistema completo.</p>

    <div class="sec-title"><span class="sec-n">12.1</span> Configuración del Kiosco</div>
    <ul class="doc-ul">
        <li>Instalar un equipo dedicado (PC o tablet) en el área de entrada</li>
        <li>Abrir Google Chrome y navegar a la URL del Kiosco: <span class="kbd">/kiosco</span></li>
        <li>Activar el modo pantalla completa con <span class="kbd">F11</span></li>
        <li>Configurar el inicio de sesión automático de Windows para que el equipo arranque directo al navegador</li>
    </ul>

    <div class="sec-title"><span class="sec-n">12.2</span> Registrar Asistencia — Proceso del Pasante</div>
    <div class="steps">
        <div class="step"><p>El pasante se acerca al Kiosco al llegar a la institución.</p></div>
        <div class="step"><p>Ingresa su <strong>PIN de 4 dígitos</strong> usando el teclado numérico en pantalla.</p></div>
        <div class="step"><p>El sistema valida el PIN y registra la asistencia con la hora exacta.</p></div>
        <div class="step"><p>Se muestra una confirmación en pantalla con el nombre del pasante y el estado registrado (Presente o Tarde según la hora).</p></div>
    </div>
    <div class="note-box info">
        <span class="note-icon">ℹ️</span>
        <p>El Kiosco registra automáticamente el estado <strong>Tarde</strong> si el pasante ingresa después de la hora de entrada establecida para el período. El Administrador puede ajustar el umbral de hora de entrada desde la configuración del período académico.</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 13 — PERFIL
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 13</div>
            <div class="ch-title">Perfil de Usuario</div>
            <div class="ch-sub">Datos personales, contraseña, foto y constancia de culminación</div>
        </div>
    </div>

    <div class="sec-title"><span class="sec-n">13.1</span> Actualizar Datos Personales</div>
    <div class="steps">
        <div class="step"><p>Hacer clic en el <strong>avatar o nombre de usuario</strong> en la barra superior.</p></div>
        <div class="step"><p>Seleccionar <strong>Mi Perfil</strong>.</p></div>
        <div class="step"><p>Editar los campos disponibles: nombre, apellidos, teléfono, institución de procedencia.</p></div>
        <div class="step"><p>Hacer clic en <span class="kbd">Guardar Cambios</span>.</p></div>
    </div>

    <div class="sec-title"><span class="sec-n">13.2</span> Cambiar Contraseña</div>
    <p class="doc-p">Desde el perfil, la sección <strong>Seguridad</strong> permite cambiar la contraseña actual. Se requiere ingresar la contraseña actual como verificación de identidad, seguida de la nueva contraseña (mínimo 8 caracteres).</p>

    <div class="sec-title"><span class="sec-n">13.3</span> Constancia de Culminación <span class="badge badge-pasante">Pasante</span></div>
    <p class="doc-p">El botón <span class="kbd">Descargar Constancia</span> se habilita automáticamente en el perfil del pasante cuando se cumplen <strong>ambas condiciones</strong>:</p>
    <ul class="doc-ul">
        <li>El período académico del pasante tiene estado <strong>Cerrado</strong></li>
        <li>El pasante ha completado el <strong>100%</strong> de sus horas meta</li>
    </ul>
    <p class="doc-p">Si alguna condición no se cumple, el botón no aparecerá. Consultar con el Administrador para verificar el estado del período y las horas registradas.</p>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 14 — BITÁCORA
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 14</div>
            <div class="ch-title">Bitácora</div>
            <div class="ch-sub"><span class="badge badge-pasante" style="-webkit-print-color-adjust:exact;print-color-adjust:exact;">Pasante</span> &nbsp; Registro personal de actividades y aprendizajes</div>
        </div>
    </div>

    <p class="doc-p">La Bitácora es un espacio personal del pasante para registrar sus actividades diarias, aprendizajes, observaciones y reflexiones durante la pasantía. Funciona como un diario de campo digital.</p>

    <div class="sec-title"><span class="sec-n">14.1</span> Registrar una Entrada</div>
    <div class="steps">
        <div class="step"><p>Navegar a <strong>Bitácora</strong> en el menú lateral.</p></div>
        <div class="step"><p>Hacer clic en <span class="kbd">+ Nueva Entrada</span>.</p></div>
        <div class="step"><p>Ingresar el <strong>título</strong> y el <strong>contenido</strong> de la entrada.</p></div>
        <div class="step"><p>Hacer clic en <span class="kbd">Guardar Entrada</span>.</p></div>
    </div>

    <p class="doc-p">Las entradas de la bitácora son visibles por el tutor asignado y el administrador, quienes pueden usarlas como insumo para la evaluación de desempeño.</p>
</div>

<!-- ════════════════════════════════════════════════════
     CAP. 15 — SOLUCIÓN DE PROBLEMAS
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 15</div>
            <div class="ch-title">Solución de Problemas Frecuentes</div>
            <div class="ch-sub">Guía de diagnóstico y resolución de incidencias comunes</div>
        </div>
    </div>

    <table class="doc-table">
        <thead><tr><th>Problema</th><th>Causa probable</th><th>Solución</th></tr></thead>
        <tbody>
            <tr>
                <td>No puedo iniciar sesión</td>
                <td>Contraseña incorrecta o cuenta desactivada</td>
                <td>Verificar que la cédula y contraseña son correctas. Contactar al Administrador para reactivar la cuenta si está desactivada.</td>
            </tr>
            <tr>
                <td>No aparece el botón de Constancia de Culminación</td>
                <td>El período aún está Activo o las horas no están al 100%</td>
                <td>Solicitar al Administrador verificar el estado del período y el total de horas registradas.</td>
            </tr>
            <tr>
                <td>El estado "Activo" no se puede asignar al pasante</td>
                <td>Falta departamento o fecha de inicio en el perfil del pasante</td>
                <td>El Administrador debe completar la asignación de departamento y fecha de inicio desde el módulo de Pasantes.</td>
            </tr>
            <tr>
                <td>El Kiosco no registra la asistencia</td>
                <td>PIN incorrecto, pasante desactivado o sin período activo</td>
                <td>Verificar que el pasante tenga estado "Activo" y que su PIN esté vigente. Resetear el PIN desde el panel de administración.</td>
            </tr>
            <tr>
                <td>El PDF no se genera o muestra error</td>
                <td>El navegador bloqueó la ventana emergente o hay error de servidor</td>
                <td>Permitir ventanas emergentes (pop-ups) para el dominio del sistema en la configuración del navegador.</td>
            </tr>
            <tr>
                <td>No veo datos en el Dashboard</td>
                <td>No hay un período activo seleccionado</td>
                <td>Usar el selector de período en la barra superior para elegir el período correspondiente.</td>
            </tr>
            <tr>
                <td>La exportación Excel descarga un archivo vacío</td>
                <td>No hay pasantes registrados en el período o filtro aplicado sin resultados</td>
                <td>Verificar que el período tiene pasantes activos y remover cualquier filtro de búsqueda.</td>
            </tr>
            <tr>
                <td>El sistema muestra "Error 403 — Acceso denegado"</td>
                <td>El usuario intenta acceder a una sección fuera de su rol</td>
                <td>Verificar el rol del usuario. Si el acceso es legítimo, contactar al Administrador para ajustar los permisos.</td>
            </tr>
            <tr>
                <td>Las notificaciones de escritorio no aparecen</td>
                <td>Permiso del navegador bloqueado o notificaciones de Windows desactivadas</td>
                <td>Verificar el permiso en el ícono del candado de la barra de URL → Notificaciones → Permitir. También revisar Configuración de Windows → Sistema → Notificaciones y comprobar que Chrome/Edge están habilitados.</td>
            </tr>
        </tbody>
    </table>

    <div class="note-box info">
        <span class="note-icon">📞</span>
        <p>Si el problema persiste después de aplicar las soluciones indicadas, contactar al <strong>Departamento de Tecnología de la Información</strong> del instituto, describiendo el error exacto, la acción que lo generó y el rol del usuario afectado.</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     CAPÍTULO 16 — NOTIFICACIONES DE ESCRITORIO
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Capítulo 16</div>
            <div class="ch-title">Notificaciones de Escritorio</div>
            <div class="ch-sub">Avisos automáticos en Windows vía navegador — exclusivo Administrador</div>
        </div>
    </div>

    <p>El sistema SGP incluye un módulo de notificaciones de escritorio que envía avisos emergentes directamente al escritorio de Windows a través del navegador web, sin necesidad de instalar ninguna aplicación adicional. Esta función está disponible exclusivamente para usuarios con rol <strong>Administrador</strong> o <strong>SuperAdministrador</strong> y requiere que el sistema esté abierto con sesión activa.</p>

    <div class="sec-title"><span class="sec-n">16.1</span> Activación del Permiso</div>
    <p>Al iniciar sesión como Administrador por primera vez, el navegador mostrará automáticamente un cuadro de diálogo solicitando permiso para mostrar notificaciones. Es necesario hacer clic en <strong>Permitir</strong> para habilitar la función. Esta solicitud aparece una sola vez por navegador y dispositivo.</p>
    <p>Si el permiso fue bloqueado accidentalmente, puede restablecerse desde el ícono del candado en la barra de direcciones del navegador → <em>Configuración del sitio</em> → <em>Notificaciones</em> → seleccionar <strong>Permitir</strong>.</p>

    <div class="sec-title"><span class="sec-n">16.2</span> Tipos de Notificaciones</div>
    <table class="doc-table">
        <thead><tr><th>Tipo</th><th>Disparador</th><th>Hora</th><th>Mensaje ejemplo</th></tr></thead>
        <tbody>
            <tr>
                <td><strong>Asistencia registrada</strong></td>
                <td>Un pasante marca asistencia desde el Kiosco público</td>
                <td>Tiempo real (cada 30 seg)</td>
                <td><em>"Juan Pérez — 08:32 AM"</em></td>
            </tr>
            <tr>
                <td><strong>Pasantes sin asignar</strong></td>
                <td>Existen pasantes activos sin tutor asignado</td>
                <td>12:00 PM (una vez al día)</td>
                <td><em>"Tienes 3 pasantes sin tutor asignado"</em></td>
            </tr>
            <tr>
                <td><strong>Próximo día feriado</strong></td>
                <td>Hay un feriado registrado en los próximos 7 días</td>
                <td>Al iniciar sesión (una vez al día)</td>
                <td><em>"En 3 días: Día de las Madres (10/05). No habrá asistencia ese día."</em></td>
            </tr>
        </tbody>
    </table>

    <div class="sec-title"><span class="sec-n">16.3</span> Condiciones de Funcionamiento</div>
    <p>Las notificaciones respetan automáticamente las reglas del sistema:</p>
    <ul style="margin:8px 0 8px 20px;line-height:1.9;">
        <li>No se envían los <strong>sábados ni domingos</strong>.</li>
        <li>No se envían en <strong>días feriados</strong> registrados en el módulo de días feriados.</li>
        <li>Se detienen si la <strong>sesión se cierra</strong> por inactividad (25 minutos) o cierre manual.</li>
        <li>La notificación de asistencia solo se activa cuando el pasante marca desde el <strong>Kiosco público</strong>, no desde el panel administrativo.</li>
        <li>Cada notificación periódica (mediodía, feriado) se muestra <strong>una sola vez por día</strong> aunque el usuario inicie sesión varias veces.</li>
    </ul>

    <div class="note-box warning">
        <span class="note-icon">⚠️</span>
        <p>Las notificaciones de escritorio requieren que la sesión esté activa en el navegador. No funcionan cuando el navegador está cerrado (esto requeriría HTTPS y Service Workers, disponibles en implementación con dominio propio).</p>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     APÉNDICE A — GLOSARIO
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Apéndice A</div>
            <div class="ch-title">Glosario de Términos</div>
            <div class="ch-sub">Definiciones de los términos técnicos y académicos del sistema</div>
        </div>
    </div>

    <div class="glos-entry">
        <div class="glos-term">Asistencia</div>
        <div class="glos-def">Registro diario de la presencia de un pasante en su lugar de pasantía. Cada asistencia válida contabiliza 8 horas hacia las horas meta.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Bitácora</div>
        <div class="glos-def">Diario de campo digital donde el pasante registra sus actividades, aprendizajes y observaciones durante el proceso de pasantía.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Constancia de Culminación</div>
        <div class="glos-def">Documento oficial emitido por el sistema que certifica que el pasante ha completado satisfactoriamente las horas requeridas en el período académico.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Departamento Asignado</div>
        <div class="glos-def">Unidad organizativa o área funcional de la institución donde el pasante desarrolla sus actividades de pasantía bajo la supervisión de un tutor.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Horas Meta</div>
        <div class="glos-def">Total de horas de pasantía que el pasante debe completar para aprobar el período. Varía según el tipo de período (Regular: generalmente 280–320 horas; Intensivo: 120–180 horas).</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Ficha Personal</div>
        <div class="glos-def">Reporte PDF que muestra el historial completo de asistencias de un pasante, organizado por semanas y meses, con resumen de horas acumuladas y porcentaje de avance.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Kiosco de Asistencias</div>
        <div class="glos-def">Módulo web de acceso público (sin autenticación completa) que permite a los pasantes registrar su asistencia ingresando su PIN de 4 dígitos en una pantalla táctil o PC dedicada.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Nómina Global</div>
        <div class="glos-def">Reporte consolidado en PDF o Excel que lista todos los pasantes activos del período con su resumen de horas, estado y datos de asignación.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Pasante</div>
        <div class="glos-def">Estudiante universitario o de educación técnica que realiza sus prácticas profesionales o servicio comunitario bajo la tutela de la institución.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Período Académico</div>
        <div class="glos-def">Ciclo temporal que enmarca un conjunto de pasantías. Puede ser Regular (año escolar completo) o Intensivo (período vacacional). Todos los datos del sistema están contextualizados por período.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">PIN de Asistencia</div>
        <div class="glos-def">Código numérico personal de 4 dígitos asignado a cada pasante, utilizado exclusivamente para el registro de asistencia en el Kiosco. Se almacena cifrado en la base de datos.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Retardo / Tarde</div>
        <div class="glos-def">Estado de asistencia que indica que el pasante llegó fuera del horario establecido. En el SGP, un retardo no penaliza las horas acumuladas: se contabilizan las 8 horas completas del día.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Rol</div>
        <div class="glos-def">Perfil de acceso asignado a cada cuenta de usuario que determina los módulos visibles y las acciones permitidas. Los roles del sistema son: Administrador, Tutor y Pasante.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Semana Relativa</div>
        <div class="glos-def">Numeración de semanas calculada a partir de la fecha de inicio del período académico, no del año calendario. La semana 1 es la primera semana completa desde el inicio del período.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Servicio Comunitario</div>
        <div class="glos-def">Actividad de responsabilidad social realizada por los pasantes en beneficio de instituciones o comunidades externas, como parte de los requisitos académicos del programa.</div>
    </div>
    <div class="glos-entry">
        <div class="glos-term">Tutor</div>
        <div class="glos-def">Docente o profesional de la institución encargado del seguimiento y evaluación del desempeño del pasante durante su proceso. Tiene acceso a los módulos de asistencia, evaluación y reportes de sus pasantes asignados.</div>
    </div>
</div>

<!-- ════════════════════════════════════════════════════
     APÉNDICE B — REFERENCIAS
════════════════════════════════════════════════════ -->
<div class="chapter">
    <div class="ch-header">
        <div>
            <div class="ch-num">Apéndice B</div>
            <div class="ch-title">Referencias y Normativa</div>
            <div class="ch-sub">Marco legal, técnico y académico del sistema</div>
        </div>
    </div>

    <div class="sec-title" style="margin-top:0;">Marco Legal y Académico</div>
    <ul class="doc-ul">
        <li><strong>Reglamento de Pasantías y Servicio Comunitario</strong> — Instituto de Salud Pública del Estado Bolívar. Coordinación Académica, vigente.</li>
        <li><strong>Ley Orgánica de Educación (LOE)</strong> — República Bolivariana de Venezuela. Gaceta Oficial Extraordinaria N.° 5.929, agosto 2009.</li>
        <li><strong>Ley del Servicio Comunitario del Estudiante de Educación Superior</strong> — G.O. N.° 38.272, septiembre 2005.</li>
        <li><strong>Reglamento sobre Pasantías Estudiantiles</strong> — CNU / OPSU, Venezuela.</li>
    </ul>

    <div class="sec-title">Referencias Técnicas</div>
    <ul class="doc-ul">
        <li><strong>PHP 8.x — Manual oficial:</strong> <a class="doc-link" href="https://www.php.net/manual/es/">https://www.php.net/manual/es/</a></li>
        <li><strong>MySQL 8.0 Reference Manual:</strong> <a class="doc-link" href="https://dev.mysql.com/doc/">https://dev.mysql.com/doc/</a></li>
        <li><strong>SweetAlert2 — Documentación:</strong> <a class="doc-link" href="https://sweetalert2.github.io/">https://sweetalert2.github.io/</a></li>
        <li><strong>Tabler Icons:</strong> <a class="doc-link" href="https://tabler-icons.io/">https://tabler-icons.io/</a></li>
        <li><strong>OWASP Top 10 — Seguridad en aplicaciones web:</strong> <a class="doc-link" href="https://owasp.org/www-project-top-ten/">https://owasp.org/</a></li>
    </ul>

    <div class="sec-title">Estándares de Documentación</div>
    <ul class="doc-ul">
        <li><strong>IEEE Std 1063-2001</strong> — Standard for Software User Documentation.</li>
        <li><strong>ISO/IEC 26514:2008</strong> — Systems and software engineering — Requirements for designers and developers of user documentation.</li>
    </ul>

    <div class="sep"></div>

    <div style="text-align:center; padding:24px 0; color:#64748b;">
        <p style="font-size:10pt; font-weight:700; color:#1e293b; margin-bottom:6px;">Registro y Control de Asistencias de Pasantes — SGP v2.0</p>
        <p style="font-size:9pt;">Instituto de Salud Pública del Estado Bolívar</p>
        <p style="font-size:9pt; margin-top:4px;">Departamento de Tecnología de la Información &nbsp;·&nbsp; <?= $fechaDoc ?></p>
        <p style="font-size:8pt; margin-top:12px; color:#94a3b8;">Documento de uso interno. Prohibida su reproducción o distribución sin autorización.</p>
    </div>
</div>

<script>
// Auto-abrir diálogo de impresión al cargar la página (solo si no está ya en modo print)
if (typeof window !== 'undefined') {
    window.addEventListener('load', function() {
        // Pequeño delay para asegurar que los estilos cargaron
        setTimeout(function() { window.print(); }, 800);
    });
}
</script>
</body>
</html>
