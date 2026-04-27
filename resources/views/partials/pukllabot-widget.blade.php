@php
    $pukllabotFaqsWidget = \App\Services\PukllaBot\PukllaBotFaqFile::allForWidget();
@endphp
{{-- Preguntas frecuentes: editar storage/app/pukllabot/faqs.json y ejecutar: php artisan pukllabot:ingest --force --}}
{{-- Chat PukllaBot: LLM + RAG (PukllaBotChatController) --}}
<div id="pukllabot-root" class="pukllabot-wrap" data-chat-url="{{ route('pukllabot.chat') }}" data-csrf="{{ csrf_token() }}"
    data-bot-img="{{ asset('img/PukllaBot.png') }}">
    <script type="application/json" id="pukllabot-faqs-json">
{!! json_encode($pukllabotFaqsWidget, JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES | JSON_HEX_TAG | JSON_HEX_AMP) !!}
    </script>
    <button type="button" class="pukllabot-fab" id="pukllabot-fab" aria-label="Abrir PukllaBot" title="PukllaBot">
        <img src="{{ asset('img/PukllaBot.png') }}" alt="PukllaBot" class="pukllabot-fab__img" width="48" height="48" loading="lazy" />
    </button>
    <div class="pukllabot-panel" id="pukllabot-panel" hidden>
        <div class="pukllabot-panel__head">
            <span class="pukllabot-panel__title">PukllaBot</span>
            <button type="button" class="pukllabot-panel__close" id="pukllabot-close" aria-label="Cerrar">&times;</button>
        </div>
        <div class="pukllabot-panel__scope">
            <label for="pukllabot-scope" class="pukllabot-panel__scope-label">Tema de la consulta</label>
            <select id="pukllabot-scope" class="pukllabot-panel__scope-select" aria-label="Elegir tema de la consulta">
                @foreach (config('pukllabot.chat_scopes', []) as $scopeId => $scopeMeta)
                    <option value="{{ $scopeId }}" @selected($scopeId === 'general')>
                        {{ is_array($scopeMeta) ? ($scopeMeta['label'] ?? $scopeId) : $scopeId }}
                    </option>
                @endforeach
            </select>
        </div>
        <div class="pukllabot-faq" id="pukllabot-faq-wrap" hidden>
            <div class="pukllabot-faq__label">Preguntas frecuentes</div>
            <div class="pukllabot-faq__chips" id="pukllabot-faq-chips" role="list"></div>
        </div>
        <div class="pukllabot-panel__messages" id="pukllabot-messages" aria-live="polite"></div>
        <form class="pukllabot-panel__form" id="pukllabot-form" autocomplete="off">
            <input type="text" class="pukllabot-panel__input" id="pukllabot-input" name="message" maxlength="2000" placeholder="Escribe tu pregunta…" />
            <button type="submit" class="pukllabot-panel__send">Enviar</button>
        </form>
    </div>
</div>
<style>
    /* Especificidad #pukllabot-root: evita que estilos globales (Bootstrap, style/estilos.css) pisen el widget */
    #pukllabot-root.pukllabot-wrap{position:fixed;z-index:10050;right:1.2rem;bottom:calc(5.5em + 4.5rem);font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;line-height:normal;box-sizing:border-box;}
    #pukllabot-root *{box-sizing:border-box;}
    #pukllabot-root .pukllabot-fab{margin:0;padding:0;border:0;background:transparent;cursor:pointer;line-height:0;display:block;box-shadow:none;border-radius:0;-webkit-appearance:none;appearance:none;color:inherit;}
    #pukllabot-root .pukllabot-fab:focus-visible{outline:2px solid #2E5397;outline-offset:3px;}
    #pukllabot-root .pukllabot-fab__img{display:block;width:48px;height:48px;object-fit:contain;}
    #pukllabot-root .pukllabot-fab:hover .pukllabot-fab__img{transform:scale(1.04);transition:transform .2s ease}
    #pukllabot-root .pukllabot-panel{position:absolute;right:0;bottom:58px;width:380px;max-width:calc(100vw - 1.25rem);height:480px;max-height:min(80vh,640px);background:#fff;border-radius:10px;box-shadow:0 4px 24px rgba(0,0,0,.18);display:flex;flex-direction:column;overflow:hidden;}
    #pukllabot-root .pukllabot-panel[hidden]{display:none!important}
    #pukllabot-root .pukllabot-panel__head{background:linear-gradient(90deg,#2E5397,#b87a2a);color:#fff;padding:.6rem .85rem;display:flex;justify-content:space-between;align-items:center;font-size:.95rem}
    #pukllabot-root .pukllabot-panel__close{background:transparent;border:0;color:#fff;font-size:1.4rem;line-height:1;cursor:pointer;padding:0 .25rem}
    #pukllabot-root .pukllabot-panel__scope{padding:.45rem .65rem .55rem;border-bottom:1px solid #e8e0d8;background:#f9f7f3}
    #pukllabot-root .pukllabot-panel__scope-label{display:block;font-size:.72rem;text-transform:uppercase;letter-spacing:.03em;color:#5a5a5a;margin-bottom:.25rem}
    #pukllabot-root .pukllabot-panel__scope-select{width:100%;max-width:100%;height:auto;font-size:.82rem;border:1px solid #c8c0b8;border-radius:6px;padding:.4rem .45rem;background-color:#fff;color:#333;cursor:pointer;line-height:1.3}
    #pukllabot-root .pukllabot-panel__scope-select:focus{outline:2px solid #2E5397;outline-offset:0}
    #pukllabot-root .pukllabot-faq{padding:0 .65rem .5rem;border-bottom:1px solid #e8e0d8;background:#fffcf7;max-height:8rem;overflow-y:auto;flex-shrink:0}
    #pukllabot-root .pukllabot-faq[hidden]{display:none!important}
    #pukllabot-root .pukllabot-faq__label{font-size:.7rem;text-transform:uppercase;letter-spacing:.04em;color:#6a5a4a;margin-bottom:.35rem}
    #pukllabot-root .pukllabot-faq__chips{display:flex;flex-wrap:wrap;gap:.35rem}
    #pukllabot-root .pukllabot-faq__chip{background:#fff;border:1px solid #c8b8a8;color:#2E5397;border-radius:999px;padding:.25rem .5rem;font-size:.72rem;line-height:1.2;cursor:pointer;max-width:100%;text-align:left;white-space:normal}
    #pukllabot-root .pukllabot-faq__chip:hover{background:#f0f4fa;border-color:#2E5397}
    #pukllabot-root .pukllabot-panel__messages{flex:1 1 auto;overflow-y:auto;overflow-x:hidden;padding:.75rem;font-size:.9rem;scroll-behavior:smooth;min-height:0;}
    #pukllabot-root .pukllabot-msg{margin-bottom:.75rem;word-break:break-word;line-height:1.45}
    #pukllabot-root .pukllabot-msg--user{white-space:pre-wrap;background:#f0f4fa;padding:.5rem .6rem;border-radius:8px;margin-left:1.5rem}
    #pukllabot-root .pukllabot-msg--bot{white-space:normal;background:#faf8f5;border-left:3px solid #b87a2a;padding:.5rem .65rem;border-radius:4px;margin-right:1rem}
    #pukllabot-root .pukllabot-msg--bot.pukllabot-msg--md{font-size:.88rem}
    /* Tipografía compacta para Markdown (el sitio aplica h1/p grandes; aquí manda el chat) */
    #pukllabot-root .pukllabot-msg--md{font-family:system-ui,-apple-system,"Segoe UI",Roboto,sans-serif;color:#2a2a2a}
    #pukllabot-root .pukllabot-msg--md h1,
    #pukllabot-root .pukllabot-msg--md h2,
    #pukllabot-root .pukllabot-msg--md h3,
    #pukllabot-root .pukllabot-msg--md h4,
    #pukllabot-root .pukllabot-msg--md h5,
    #pukllabot-root .pukllabot-msg--md h6{font-family:inherit;font-weight:700;line-height:1.25;color:#1e3a5f;margin:0.5rem 0 0.35rem;padding:0;letter-spacing:-0.01em}
    #pukllabot-root .pukllabot-msg--md h1:first-child,
    #pukllabot-root .pukllabot-msg--md h2:first-child{margin-top:0}
    #pukllabot-root .pukllabot-msg--md h1{font-size:1.05rem;border-bottom:1px solid #e0d8d0;padding-bottom:0.25rem;margin-bottom:0.4rem}
    #pukllabot-root .pukllabot-msg--md h2{font-size:1rem}
    #pukllabot-root .pukllabot-msg--md h3{font-size:0.95rem;font-weight:600}
    #pukllabot-root .pukllabot-msg--md h4{font-size:0.9rem;font-weight:600}
    #pukllabot-root .pukllabot-msg--md h5,
    #pukllabot-root .pukllabot-msg--md h6{font-size:0.86rem;font-weight:600;text-transform:none}
    #pukllabot-root .pukllabot-msg--md p{margin:0 0 .5rem}
    #pukllabot-root .pukllabot-msg--md p:last-child{margin-bottom:0}
    #pukllabot-root .pukllabot-msg--md ul,
    #pukllabot-root .pukllabot-msg--md ol{margin:.35rem 0 .5rem 1.1rem;padding:0}
    #pukllabot-root .pukllabot-msg--md li{margin-bottom:.25rem}
    #pukllabot-root .pukllabot-msg--md blockquote{margin:0.35rem 0;padding:0.35rem 0.5rem 0.35rem 0.6rem;border-left:3px solid #c8b8a8;background:#f3f0eb;font-size:0.85rem;color:#444}
    #pukllabot-root .pukllabot-msg--md pre{margin:0.4rem 0;padding:0.45rem 0.5rem;overflow-x:auto;font-size:0.78rem;line-height:1.4;background:#f0ece6;border-radius:4px;border:1px solid #e0d8d0}
    #pukllabot-root .pukllabot-msg--md code{font-size:0.86em;padding:0.1em 0.25em;background:#f0ece6;border-radius:3px;font-family:ui-monospace,Consolas,monospace}
    #pukllabot-root .pukllabot-msg--md pre code{padding:0;background:transparent;border:0;font-size:inherit}
    #pukllabot-root .pukllabot-msg--md hr{margin:0.5rem 0;border:0;border-top:1px solid #e0d8d0}
    #pukllabot-root .pukllabot-msg--md table{font-size:0.82rem;border-collapse:collapse;width:100%;max-width:100%;display:block;overflow-x:auto}
    #pukllabot-root .pukllabot-msg--md th,
    #pukllabot-root .pukllabot-msg--md td{border:1px solid #e0d8d0;padding:0.25rem 0.35rem}
    #pukllabot-root .pukllabot-msg--md th{background:#f3f0eb;font-weight:600}
    #pukllabot-root .pukllabot-msg--md strong{color:#1e3a5f}
    #pukllabot-root .pukllabot-msg--md a{color:#2E5397;text-decoration:underline;word-break:break-word}
    #pukllabot-root .pukllabot-msg--err{white-space:pre-wrap;background:#fff3f0;border-left:3px solid #c00;color:#500}
    #pukllabot-root .pukllabot-panel__form{display:flex;align-items:stretch;gap:.4rem;padding:.5rem;border-top:1px solid #e8e0d8;flex-shrink:0;margin:0}
    #pukllabot-root .pukllabot-panel__input{flex:1;min-width:0;border:1px solid #ccc;border-radius:6px;padding:.45rem .55rem;font-size:.9rem;background:#fff;color:#1F1F1F;}
    #pukllabot-root .pukllabot-panel__send{flex:0 0 auto;background:#2E5397;color:#fff;border:0;border-radius:6px;padding:0 .75rem;cursor:pointer;font-size:.88rem;white-space:nowrap}
    #pukllabot-root .pukllabot-panel__send:disabled{opacity:.55;cursor:wait}
    @media (min-width:768px){
        #pukllabot-root .pukllabot-panel{width:400px;height:80vh;max-height:560px;min-height:420px}
    }
</style>
<script src="https://cdn.jsdelivr.net/npm/marked@12.0.0/marked.min.js" crossorigin></script>
<script src="https://cdn.jsdelivr.net/npm/dompurify@3.1.6/dist/purify.min.js" crossorigin></script>
<script>
(function(){
    const root = document.getElementById('pukllabot-root');
    if (!root) return;
    const url = root.dataset.chatUrl;
    const token = root.dataset.csrf;
    const fab = document.getElementById('pukllabot-fab');
    const panel = document.getElementById('pukllabot-panel');
    const closeBtn = document.getElementById('pukllabot-close');
    const form = document.getElementById('pukllabot-form');
    const input = document.getElementById('pukllabot-input');
    const scopeSelect = document.getElementById('pukllabot-scope');
    const box = document.getElementById('pukllabot-messages');
    const faqWrap = document.getElementById('pukllabot-faq-wrap');
    const faqChips = document.getElementById('pukllabot-faq-chips');
    const faqJsonEl = document.getElementById('pukllabot-faqs-json');
    let faqData = {};
    if (faqJsonEl && faqJsonEl.textContent) {
        try { faqData = JSON.parse(faqJsonEl.textContent); } catch (e) { faqData = {}; }
    }
    const meta = document.querySelector('meta[name="csrf-token"]');
    const csrf = (meta && meta.content) ? meta.content : token;
    function addMsg(text, role){
        const d = document.createElement('div');
        const isUser = role === 'user';
        const isErr = role === 'err';
        d.className = 'pukllabot-msg pukllabot-msg--' + (isUser ? 'user' : (isErr ? 'err' : 'bot'));
        if (!isUser && !isErr && typeof text === 'string' && window.marked && window.DOMPurify) {
            try {
                const raw = window.marked.parse(text, { breaks: true, gfm: true });
                d.innerHTML = window.DOMPurify.sanitize(raw, { USE_PROFILES: { html: true } });
                d.classList.add('pukllabot-msg--md');
            } catch (e) {
                d.textContent = text;
            }
        } else {
            d.textContent = text;
        }
        box.appendChild(d);
        box.scrollTop = box.scrollHeight;
    }
    function renderFaqChips(){
        if (!faqChips || !faqWrap) return;
        const s = (scopeSelect && scopeSelect.value) ? scopeSelect.value : 'general';
        const list = (faqData && faqData[s]) ? faqData[s] : [];
        faqChips.innerHTML = '';
        if (!list.length) { faqWrap.hidden = true; return; }
        faqWrap.hidden = false;
        list.forEach(function(item){
            const q = (item && item.q) ? String(item.q) : '';
            if (!q) return;
            const a = (item && item.a) ? String(item.a).trim() : '';
            const b = document.createElement('button');
            b.type = 'button';
            b.className = 'pukllabot-faq__chip';
            b.setAttribute('role', 'listitem');
            b.textContent = q.length > 80 ? (q.slice(0, 77) + '…') : q;
            b.title = q;
            b.addEventListener('click', function(){
                addMsg(q, 'user');
                if (a) {
                    addMsg(a, 'bot');
                    if (input) input.value = '';
                } else if (input) {
                    input.value = q;
                    input.focus();
                }
            });
            faqChips.appendChild(b);
        });
    }
    fab.addEventListener('click', function(){
        panel.hidden = !panel.hidden;
        if (!panel.hidden){ renderFaqChips(); setTimeout(function(){ input.focus(); }, 80); }
    });
    closeBtn.addEventListener('click', function(){ panel.hidden = true; });
    if (scopeSelect) scopeSelect.addEventListener('change', renderFaqChips);
    form.addEventListener('submit', async function(e){
        e.preventDefault();
        const t = (input.value || '').trim();
        if (!t) return;
        addMsg(t, 'user');
        input.value = '';
        const btn = form.querySelector('button[type="submit"]');
        btn.disabled = true;
        try {
            const scope = scopeSelect && scopeSelect.value ? scopeSelect.value : 'general';
            const res = await fetch(url, { method: 'POST', headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': csrf, 'Accept': 'application/json' }, body: JSON.stringify({ message: t, scope: scope }) });
            const data = await res.json().catch(function(){ return {}; });
            if (!res.ok) { addMsg(data.error || ('Error ' + res.status), 'err'); return; }
            if (data.reply) addMsg(data.reply, 'bot');
            else addMsg('No hubo respuesta del servidor.', 'err');
        } catch (x) { addMsg('Error de conexión. Revisa la red o la configuración del chat.', 'err'); }
        finally { btn.disabled = false; }
    });
})();
</script>
