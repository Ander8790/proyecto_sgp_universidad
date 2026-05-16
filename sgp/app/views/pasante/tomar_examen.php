<?php
/* ══════════════════════════════════════════════════════
   Tomar Examen — Quiz paso a paso — Bento UI v1
   Variables: $examen, $preguntas[], $intentoId
   SECURITY: es_correcta is stripped before sending to JS
   ══════════════════════════════════════════════════════ */
$examen    = $data['examen']    ?? null;
$preguntas = $data['preguntas'] ?? [];
$intentoId = (int)($data['intentoId'] ?? 0);

// Build sanitized frontend data — NEVER expose es_correcta
$preguntas_js = [];
foreach ($preguntas as $preg) {
    $opciones_js = [];
    foreach ($preg->opciones as $op) {
        $opciones_js[] = [
            'id'    => (int)$op->id,
            'texto' => $op->texto,
            // es_correcta intentionally omitted
        ];
    }
    $preguntas_js[] = [
        'id'       => (int)$preg->id,
        'orden'    => (int)$preg->orden,
        'enunciado'=> $preg->enunciado,
        'puntos'   => (float)$preg->puntos,
        'opciones' => $opciones_js,
    ];
}

$totalPreg = count($preguntas_js);
?>
<style>
/* ── keyframes ── */
@keyframes qzFadeUp{from{opacity:0;transform:translateY(18px)}to{opacity:1;transform:translateY(0)}}
@keyframes qzSlideIn{from{opacity:0;transform:translateX(30px)}to{opacity:1;transform:translateX(0)}}
@keyframes qzSlideOut{from{opacity:1;transform:translateX(0)}to{opacity:0;transform:translateX(-30px)}}
@keyframes qzPulse{0%,100%{transform:scale(1)}50%{transform:scale(1.04)}}
@keyframes qzSpinCw{from{transform:rotate(0)}to{transform:rotate(360deg)}}
@keyframes qzBounceIn{0%{opacity:0;transform:scale(.4)}60%{transform:scale(1.1)}80%{transform:scale(.95)}100%{opacity:1;transform:scale(1)}}
@keyframes qzFadeIn{from{opacity:0}to{opacity:1}}
@keyframes qzCountUp{from{opacity:0;transform:translateY(12px)}to{opacity:1;transform:translateY(0)}}

/* ── quiz wrap ── */
.qz-wrap{display:flex;flex-direction:column;gap:20px;max-width:760px;margin:0 auto;animation:qzFadeUp .4s ease both}

/* ── top bar ── */
.qz-topbar{
    background:#fff;border-radius:18px;padding:18px 22px;
    box-shadow:0 2px 14px rgba(0,0,0,.06);border:1px solid #f1f5f9;
}
.qz-topbar-inner{display:flex;align-items:center;justify-content:space-between;gap:16px;flex-wrap:wrap;margin-bottom:12px}
.qz-exam-title{font-size:.95rem;font-weight:700;color:#1e293b;flex:1;min-width:0;white-space:nowrap;overflow:hidden;text-overflow:ellipsis}
.qz-step-label{font-size:.8rem;font-weight:700;color:#7c3aed;background:#ede9fe;border-radius:999px;padding:4px 12px;flex-shrink:0}

/* progress bar */
.qz-prog-track{height:8px;background:#e2e8f0;border-radius:4px;overflow:hidden}
.qz-prog-fill{
    height:100%;border-radius:4px;
    background:linear-gradient(90deg,#5b21b6,#7c3aed);
    transition:width .4s cubic-bezier(.4,0,.2,1);
}

/* ── question card ── */
.qz-card{
    background:#fff;border-radius:20px;padding:28px;
    box-shadow:0 2px 14px rgba(0,0,0,.06);border:1px solid #f1f5f9;
    animation:qzSlideIn .3s ease both;
}
.qz-q-num{
    display:inline-flex;align-items:center;gap:8px;
    font-size:.75rem;font-weight:700;text-transform:uppercase;letter-spacing:.6px;
    color:#7c3aed;margin-bottom:10px;
}
.qz-q-pts{
    background:#ede9fe;color:#5b21b6;border-radius:999px;
    padding:2px 10px;font-size:.72rem;font-weight:700;margin-left:4px;
}
.qz-enunciado{
    font-size:1.08rem;font-weight:600;color:#1e293b;
    line-height:1.6;margin-bottom:22px;
}

/* ── option buttons ── */
.qz-options{display:flex;flex-direction:column;gap:10px}
.qz-opt{
    display:flex;align-items:center;gap:14px;
    border:2px solid #e2e8f0;border-radius:14px;
    padding:14px 18px;cursor:pointer;
    background:#fff;
    transition:border-color .18s,background .18s,transform .15s,box-shadow .18s;
    text-align:left;width:100%;font-size:.9rem;color:#334155;font-weight:500;
    position:relative;outline:none;
    animation:qzFadeUp .3s ease both;
}
.qz-opt:hover{border-color:#a78bfa;background:#faf5ff;transform:translateX(4px)}
.qz-opt.selected{
    border-color:#7c3aed;background:#f5f3ff;
    box-shadow:0 0 0 3px rgba(124,58,237,.12);
    color:#4c1d95;font-weight:600;
}
.qz-opt.selected .qz-opt-letter{background:#7c3aed;color:#fff;border-color:#7c3aed}

/* letter badge */
.qz-opt-letter{
    width:32px;height:32px;border-radius:50%;border:2px solid #e2e8f0;
    display:flex;align-items:center;justify-content:center;
    font-size:.78rem;font-weight:700;color:#64748b;flex-shrink:0;
    transition:background .18s,color .18s,border-color .18s;
    background:#f8fafc;
}
.qz-opt-text{flex:1;line-height:1.45}

/* check icon (shown when selected) */
.qz-opt-check{
    margin-left:auto;flex-shrink:0;
    color:#7c3aed;font-size:1.1rem;
    opacity:0;transition:opacity .18s;
}
.qz-opt.selected .qz-opt-check{opacity:1}

/* ── nav buttons ── */
.qz-nav{display:flex;align-items:center;justify-content:space-between;gap:12px;flex-wrap:wrap}
.qz-btn{
    display:inline-flex;align-items:center;gap:7px;
    border:none;border-radius:12px;padding:11px 22px;font-size:.88rem;font-weight:700;
    cursor:pointer;transition:opacity .15s,transform .15s,box-shadow .15s;
    line-height:1;
}
.qz-btn:hover{opacity:.88;transform:translateY(-2px);box-shadow:0 4px 12px rgba(0,0,0,.1)}
.qz-btn:disabled{opacity:.4;cursor:not-allowed;transform:none}
.qz-btn-prev{background:#f1f5f9;color:#334155}
.qz-btn-next{background:linear-gradient(135deg,#5b21b6,#7c3aed);color:#fff;box-shadow:0 4px 12px rgba(124,58,237,.3)}
.qz-btn-submit{background:linear-gradient(135deg,#065f46,#059669);color:#fff;box-shadow:0 4px 12px rgba(5,150,105,.3)}

/* unanswered indicator */
.qz-unanswered-hint{font-size:.78rem;color:#d97706;display:flex;align-items:center;gap:5px}

/* ── dot navigation ── */
.qz-dots{display:flex;flex-wrap:wrap;gap:6px;justify-content:center;padding:4px 0}
.qz-dot{
    width:10px;height:10px;border-radius:50%;
    background:#e2e8f0;border:none;cursor:pointer;
    transition:background .2s,transform .2s;padding:0;
}
.qz-dot.answered{background:#7c3aed}
.qz-dot.current{transform:scale(1.4);background:#5b21b6}
.qz-dot:hover{background:#a78bfa}

/* ══ RESULT OVERLAY ══ */
.qz-overlay{
    position:fixed;inset:0;z-index:9999;
    background:rgba(15,10,30,.72);backdrop-filter:blur(6px);
    display:flex;align-items:center;justify-content:center;
    padding:20px;animation:qzFadeIn .3s ease both;
    overflow-y:auto;
}
.qz-result-modal{
    background:#fff;border-radius:28px;
    box-shadow:0 24px 60px rgba(0,0,0,.25);
    max-width:680px;width:100%;
    animation:qzBounceIn .55s cubic-bezier(.34,1.56,.64,1) both;
    overflow:hidden;
}
.qz-result-hero{
    padding:36px 36px 28px;text-align:center;
    position:relative;overflow:hidden;
}
.qz-score-circle{
    width:164px;height:164px;border-radius:50%;
    display:flex;flex-direction:column;align-items:center;justify-content:center;
    margin:0 auto 18px;border:8px solid;
    animation:qzBounceIn .6s .2s cubic-bezier(.34,1.56,.64,1) both;
}
.qz-score-pct{font-size:2.6rem;font-weight:900;line-height:1;letter-spacing:-.5px}
.qz-score-label{font-size:.75rem;font-weight:700;opacity:.7;letter-spacing:.4px}
.qz-verdict{font-size:1.4rem;font-weight:800;margin:0 0 6px}
.qz-pts-text{font-size:.9rem;color:#64748b}

.qz-breakdown{padding:0 28px 28px;display:flex;flex-direction:column;gap:0;max-height:340px;overflow-y:auto}
.qz-br-item{
    display:flex;align-items:flex-start;gap:12px;
    padding:12px 0;border-bottom:1px solid #f1f5f9;
    animation:qzCountUp .3s ease both;
}
.qz-br-item:last-child{border-bottom:none}
.qz-br-icon{width:28px;height:28px;border-radius:50%;display:flex;align-items:center;justify-content:center;font-size:.85rem;flex-shrink:0;margin-top:2px}
.qz-br-q{font-size:.83rem;color:#1e293b;font-weight:600;line-height:1.45;margin-bottom:3px}
.qz-br-ans{font-size:.78rem;color:#64748b;line-height:1.4}
.qz-br-ans strong{font-weight:700}

.qz-result-footer{padding:0 28px 28px;display:flex;gap:10px;justify-content:center;flex-wrap:wrap}

/* scrollbar for breakdown */
.qz-breakdown::-webkit-scrollbar{width:4px}
.qz-breakdown::-webkit-scrollbar-track{background:#f1f5f9;border-radius:2px}
.qz-breakdown::-webkit-scrollbar-thumb{background:#a78bfa;border-radius:2px}

/* loading spinner overlay */
.qz-loading{
    position:fixed;inset:0;z-index:10000;
    background:rgba(15,10,30,.72);backdrop-filter:blur(4px);
    display:none;align-items:center;justify-content:center;
    flex-direction:column;gap:16px;color:#fff;
}
.qz-spinner{
    width:48px;height:48px;border:4px solid rgba(255,255,255,.2);
    border-top-color:#a78bfa;border-radius:50%;
    animation:qzSpinCw .8s linear infinite;
}
</style>

<!-- Loading overlay -->
<div class="qz-loading" id="qzLoading">
    <div class="qz-spinner"></div>
    <span style="font-size:.9rem;font-weight:600;letter-spacing:.3px;">Enviando examen…</span>
</div>

<!-- Result overlay (hidden until submission) -->
<div class="qz-overlay" id="qzResultOverlay" style="display:none;">
    <div class="qz-result-modal" id="qzResultModal">
        <div class="qz-result-hero" id="qzResultHero">
            <!-- filled by JS -->
        </div>
        <div class="qz-breakdown" id="qzBreakdown">
            <!-- filled by JS -->
        </div>
        <div class="qz-result-footer">
            <button class="qz-btn" id="qzFinalizarBtn"
                style="background:linear-gradient(135deg,#4c1d95,#7c3aed);color:#fff;padding:13px 32px;font-size:.95rem;box-shadow:0 4px 14px rgba(124,58,237,.3);"
                onclick="window.location.href='<?= URLROOT ?>/pasante/misExamenes'">
                <i class="ti ti-home"></i>
                Ir a Mis Exámenes
            </button>
        </div>
    </div>
</div>

<div class="qz-wrap">

    <!-- ══ TOP BAR ══ -->
    <div class="qz-topbar">
        <div class="qz-topbar-inner">
            <div style="display:flex;align-items:center;gap:10px;min-width:0;flex:1">
                <div style="width:36px;height:36px;border-radius:10px;background:linear-gradient(135deg,#5b21b6,#7c3aed);display:flex;align-items:center;justify-content:center;flex-shrink:0;">
                    <i class="ti ti-school" style="color:#fff;font-size:1rem;"></i>
                </div>
                <span class="qz-exam-title"><?= htmlspecialchars($examen->titulo ?? 'Examen') ?></span>
            </div>
            <span class="qz-step-label" id="qzStepLabel">Pregunta 1 de <?= $totalPreg ?></span>
        </div>
        <div class="qz-prog-track">
            <div class="qz-prog-fill" id="qzProgFill" style="width:<?= $totalPreg > 0 ? round(1/$totalPreg*100) : 100 ?>%;"></div>
        </div>
        <!-- dot nav -->
        <div class="qz-dots" id="qzDots" style="margin-top:10px;"></div>
    </div>

    <!-- ══ QUESTION CARD ══ -->
    <div class="qz-card" id="qzCard">
        <!-- filled by renderStep() -->
    </div>

    <!-- ══ NAVIGATION ══ -->
    <div class="qz-nav" id="qzNav">
        <button class="qz-btn qz-btn-prev" id="qzBtnPrev" onclick="anterior()" disabled>
            <i class="ti ti-arrow-left"></i>
            Anterior
        </button>
        <span class="qz-unanswered-hint" id="qzUnansweredHint" style="display:none;">
            <i class="ti ti-alert-triangle" style="font-size:.85rem;"></i>
            Pregunta sin respuesta
        </span>
        <div style="display:flex;gap:8px;margin-left:auto;">
            <button class="qz-btn qz-btn-next" id="qzBtnNext" onclick="siguiente()">
                Siguiente
                <i class="ti ti-arrow-right"></i>
            </button>
            <button class="qz-btn qz-btn-submit" id="qzBtnSubmit" style="display:none;" onclick="confirmarEnvio()">
                <i class="ti ti-send"></i>
                Enviar Examen
            </button>
        </div>
    </div>

</div><!-- /qz-wrap -->

<script>
/* ══════════════════════════════════════════════════════
   QUIZ ENGINE
   ══════════════════════════════════════════════════════ */
const QUIZ_DATA   = <?= json_encode($preguntas_js, JSON_UNESCAPED_UNICODE) ?>;
const INTENTO_ID  = <?= $intentoId ?>;
const TOTAL       = QUIZ_DATA.length;
var URLROOT_VAL = (typeof URLROOT !== 'undefined') ? URLROOT : '<?= URLROOT ?>';

let currentStep = 0;           // 0-based index
let respuestas  = {};          // pregunta_id → opcion_id

/* ── init ── */
buildDots();
renderStep();

/* ── render current question ── */
function renderStep() {
    const q        = QUIZ_DATA[currentStep];
    const card     = document.getElementById('qzCard');
    const letters  = ['A','B','C','D','E','F','G','H'];
    const selected = respuestas[q.id] || null;

    const opts = q.opciones.map((op, i) => {
        const isSel  = selected === op.id;
        const letter = letters[i] || String.fromCharCode(65 + i);
        return `
        <button class="qz-opt${isSel ? ' selected' : ''}"
                onclick="seleccionarOpcion(${q.id}, ${op.id}, this)"
                style="animation-delay:${i * 0.05}s">
            <span class="qz-opt-letter">${letter}</span>
            <span class="qz-opt-text">${escHtml(op.texto)}</span>
            <i class="ti ti-circle-check qz-opt-check"></i>
        </button>`;
    }).join('');

    card.style.animation = 'none';
    // force reflow
    void card.offsetWidth;
    card.style.animation = 'qzSlideIn .3s ease both';

    card.innerHTML = `
        <div class="qz-q-num">
            <i class="ti ti-help-circle" style="font-size:1rem;"></i>
            Pregunta ${currentStep + 1}
            <span class="qz-q-pts">${q.puntos} pt${q.puntos !== 1 ? 's' : ''}</span>
        </div>
        <div class="qz-enunciado">${escHtml(q.enunciado)}</div>
        <div class="qz-options" id="qzOpts">${opts}</div>
    `;

    /* progress */
    const pct = Math.round(((currentStep + 1) / TOTAL) * 100);
    document.getElementById('qzProgFill').style.width = pct + '%';
    document.getElementById('qzStepLabel').textContent = `Pregunta ${currentStep + 1} de ${TOTAL}`;

    /* dots */
    updateDots();

    /* nav buttons */
    const btnPrev   = document.getElementById('qzBtnPrev');
    const btnNext   = document.getElementById('qzBtnNext');
    const btnSubmit = document.getElementById('qzBtnSubmit');
    const hint      = document.getElementById('qzUnansweredHint');

    btnPrev.disabled = currentStep === 0;
    const isLast = currentStep === TOTAL - 1;
    btnNext.style.display   = isLast ? 'none' : '';
    btnSubmit.style.display = isLast ? '' : 'none';

    // Hint if current question unanswered
    hint.style.display = respuestas[q.id] ? 'none' : 'flex';
}

/* ── option selection ── */
function seleccionarOpcion(pregId, opcionId, el) {
    respuestas[pregId] = opcionId;
    // deselect all, select clicked
    const opts = document.querySelectorAll('.qz-opt');
    opts.forEach(o => o.classList.remove('selected'));
    el.classList.add('selected');
    // hide unanswered hint
    document.getElementById('qzUnansweredHint').style.display = 'none';
    // update dot
    updateDots();
}

/* ── navigation ── */
function siguiente() {
    if (currentStep < TOTAL - 1) {
        currentStep++;
        renderStep();
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
}

function anterior() {
    if (currentStep > 0) {
        currentStep--;
        renderStep();
        window.scrollTo({top: 0, behavior: 'smooth'});
    }
}

/* ── dots ── */
function buildDots() {
    const container = document.getElementById('qzDots');
    container.innerHTML = '';
    for (let i = 0; i < TOTAL; i++) {
        const btn = document.createElement('button');
        btn.className = 'qz-dot';
        btn.title = `Pregunta ${i + 1}`;
        btn.addEventListener('click', () => { currentStep = i; renderStep(); });
        container.appendChild(btn);
    }
}

function updateDots() {
    const dots = document.querySelectorAll('.qz-dot');
    dots.forEach((d, i) => {
        const q = QUIZ_DATA[i];
        d.classList.toggle('answered', !!respuestas[q.id]);
        d.classList.toggle('current', i === currentStep);
    });
}

/* ── confirm & submit ── */
function confirmarEnvio() {
    const sinResponder = QUIZ_DATA.filter(q => !respuestas[q.id]).length;
    let msg = '¿Estás seguro de enviar el examen?';
    if (sinResponder > 0) {
        msg = `Tienes ${sinResponder} pregunta${sinResponder > 1 ? 's' : ''} sin responder. ¿Deseas enviar de todas formas?`;
    }

    if (typeof Swal !== 'undefined') {
        Swal.fire({
            title: '¿Enviar examen?',
            text: msg,
            icon: 'question',
            showCancelButton: true,
            confirmButtonColor: '#059669',
            cancelButtonColor: '#64748b',
            confirmButtonText: '<i class="ti ti-send"></i> Sí, enviar',
            cancelButtonText: 'Revisar',
            reverseButtons: true,
        }).then(result => {
            if (result.isConfirmed) enviarExamen();
        });
    } else {
        if (confirm(msg)) enviarExamen();
    }
}

/* ── AJAX submission ── */
async function enviarExamen() {
    /* show loading */
    const loading = document.getElementById('qzLoading');
    loading.style.display = 'flex';

    const payload = {
        intento_id: INTENTO_ID,
        respuestas: Object.entries(respuestas).map(([pid, oid]) => ({
            pregunta_id: parseInt(pid),
            opcion_id:   oid,
        }))
    };

    try {
        const res  = await fetch(URLROOT_VAL + '/pasante/enviarExamen', {
            method:  'POST',
            headers: {'Content-Type': 'application/json'},
            body:    JSON.stringify(payload),
        });
        const data = await res.json();
        loading.style.display = 'none';

        if (data.success) {
            mostrarResultado(data);
        } else {
            alertError(data.error || 'Ocurrió un error al enviar el examen.');
        }
    } catch (e) {
        loading.style.display = 'none';
        alertError('Error de conexión. Intenta de nuevo.');
    }
}

/* ── result overlay ── */
function mostrarResultado(data) {
    const aprobado   = data.aprobado;
    const pct        = parseFloat(data.porcentaje).toFixed(1);
    const ptsObt     = parseFloat(data.puntaje_obtenido).toFixed(1);
    const ptsMx      = parseFloat(data.puntaje_maximo).toFixed(1);
    const circleColor= aprobado ? '#059669' : '#dc2626';
    const heroBg     = aprobado
        ? 'linear-gradient(135deg,#d1fae5,#ecfdf5)'
        : 'linear-gradient(135deg,#fee2e2,#fef2f2)';

    /* ── hero ── */
    const pctStr     = pct + '%';
    const pctFontSz  = pctStr.length >= 6 ? '2rem' : (pctStr.length >= 5 ? '2.3rem' : '2.6rem');

    document.getElementById('qzResultHero').innerHTML = `
        <div style="background:${heroBg};border-radius:20px;padding:32px 28px 24px;margin:-36px -36px 0;margin-bottom:20px;">
            <div class="qz-score-circle" style="border-color:${circleColor};color:${circleColor};">
                <span class="qz-score-pct" style="font-size:${pctFontSz};">${pct}%</span>
                <span class="qz-score-label">Puntaje</span>
            </div>
            <div class="qz-verdict" style="color:${circleColor};">
                ${aprobado
                    ? '<i class="ti ti-circle-check" style="margin-right:6px;"></i>¡Aprobado!'
                    : '<i class="ti ti-circle-x" style="margin-right:6px;"></i>No aprobado'}
            </div>
            <div class="qz-pts-text">${ptsObt} / ${ptsMx} puntos</div>
            <div style="margin-top:12px;font-size:.8rem;color:#64748b;">
                Mínimo requerido: <strong>60%</strong>
            </div>
        </div>
        <div style="font-size:.9rem;font-weight:700;color:#1e293b;margin-top:4px;text-align:left;">
            <i class="ti ti-list-details" style="color:#7c3aed;margin-right:6px;"></i>Detalle por pregunta
        </div>
    `;

    /* ── breakdown ── */
    const preguntaMap = {};
    QUIZ_DATA.forEach(q => {
        preguntaMap[q.id] = q;
    });

    const bd = document.getElementById('qzBreakdown');
    let html = '';
    (data.resultados || []).forEach((r, idx) => {
        const q         = preguntaMap[r.pregunta_id] || {};
        const correct   = r.es_correcta;
        const iconBg    = correct ? '#d1fae5' : '#fee2e2';
        const iconColor = correct ? '#059669' : '#dc2626';
        const icon      = correct ? 'ti-check' : 'ti-x';

        /* find chosen option text */
        let textoElegido = '(sin respuesta)';
        if (q.opciones && r.opcion_elegida_id) {
            const opEl = q.opciones.find(o => o.id === r.opcion_elegida_id);
            if (opEl) textoElegido = opEl.texto;
        }

        html += `
        <div class="qz-br-item" style="animation-delay:${idx * 0.04}s">
            <div class="qz-br-icon" style="background:${iconBg};color:${iconColor};">
                <i class="ti ${icon}"></i>
            </div>
            <div style="flex:1;min-width:0;">
                <div class="qz-br-q">${escHtml(q.enunciado || `Pregunta ${r.pregunta_id}`)}</div>
                <div class="qz-br-ans">
                    Tu respuesta: <strong>${escHtml(textoElegido)}</strong>
                    ${!correct ? `<br>Respuesta correcta: <strong style="color:#059669;">${escHtml(r.texto_correcto || '')}</strong>` : ''}
                </div>
            </div>
        </div>`;
    });
    bd.innerHTML = html || '<p style="color:#94a3b8;font-size:.85rem;text-align:center;padding:20px 0;">Sin detalles disponibles.</p>';

    /* show overlay */
    document.getElementById('qzResultOverlay').style.display = 'flex';
    document.body.style.overflow = 'hidden';
}

/* ── helpers ── */
function escHtml(str) {
    if (!str) return '';
    return str.replace(/&/g,'&amp;')
              .replace(/</g,'&lt;')
              .replace(/>/g,'&gt;')
              .replace(/"/g,'&quot;')
              .replace(/'/g,'&#039;');
}

function alertError(msg) {
    if (typeof Swal !== 'undefined') {
        Swal.fire({title:'Error', text: msg, icon:'error', confirmButtonColor:'#7c3aed'});
    } else {
        alert(msg);
    }
}

/* prevent accidental navigation */
window.addEventListener('beforeunload', function(e) {
    const overlay = document.getElementById('qzResultOverlay');
    if (overlay.style.display === 'none' || !overlay.style.display) {
        e.preventDefault();
        e.returnValue = '';
    }
});
</script>
