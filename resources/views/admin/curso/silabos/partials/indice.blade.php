{{-- Índice de secciones del sílabo. Se arma solo a partir de los .silabo-section de la página:
     marca la sección visible y cuáles ya tienen contenido (punto verde). --}}
<nav id="silaboIndice" class="silabo-indice" aria-label="Secciones del sílabo">
    <div class="silabo-indice-titulo">Secciones</div>
    <ol class="silabo-indice-lista"></ol>
    <div class="silabo-indice-progreso">
        <div class="silabo-indice-barra"><span></span></div>
        <small class="js-indice-progreso"></small>
    </div>
</nav>
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var nav = document.getElementById('silaboIndice');
        if (!nav) return;

        // El encabezado del layout (alertas, usuario, horario) es sticky y puede crecer a 2
        // líneas: su altura real (--sf-tope) define desde dónde se fija el índice y dónde
        // queda el título de una sección al saltar a ella.
        var topbar = document.querySelector('.topbar');
        function medirTope() {
            var fijo = topbar && getComputedStyle(topbar).position === 'sticky';
            document.documentElement.style.setProperty('--sf-tope', (fijo ? topbar.offsetHeight : 0) + 'px');
        }
        medirTope();
        window.addEventListener('resize', medirTope);
        if (topbar && 'ResizeObserver' in window) new ResizeObserver(medirTope).observe(topbar);
        var lista = nav.querySelector('.silabo-indice-lista');
        var secciones = Array.prototype.slice.call(document.querySelectorAll('.silabo-section'));
        if (!secciones.length) return;

        var items = secciones.map(function(sec, i) {
            if (!sec.id) sec.id = 'silabo-sec-' + (i + 1);
            var eyebrow = sec.querySelector('.silabo-section-eyebrow');
            var titulo = sec.querySelector('.silabo-section-header h4');
            var numero = eyebrow ? eyebrow.textContent.replace(/secci[oó]n/i, '').trim() : String(i + 1);
            var li = document.createElement('li');
            li.innerHTML = '<a href="#' + sec.id + '"><span class="silabo-indice-num"></span><span class="silabo-indice-nombre"></span><span class="silabo-indice-estado"></span></a>';
            li.querySelector('.silabo-indice-num').textContent = numero;
            li.querySelector('.silabo-indice-nombre').textContent = titulo ? titulo.textContent.trim() : 'Sección ' + numero;
            li.querySelector('a').addEventListener('click', function(e) {
                e.preventDefault();
                sec.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
            lista.appendChild(li);
            return { sec: sec, li: li, informativa: sec.hasAttribute('data-informativa') };
        });

        // ── Sección visible ──
        if ('IntersectionObserver' in window) {
            var visibles = {};
            var obs = new IntersectionObserver(function(entradas) {
                entradas.forEach(function(en) { visibles[en.target.id] = en.isIntersecting ? en.intersectionRatio : 0; });
                var mejor = null, ratio = 0;
                items.forEach(function(it) {
                    var r = visibles[it.sec.id] || 0;
                    if (r > ratio) { ratio = r; mejor = it; }
                });
                if (mejor) items.forEach(function(it) { it.li.classList.toggle('is-actual', it === mejor); });
            }, { threshold: [0, 0.15, 0.4, 0.75], rootMargin: '-15% 0px -45% 0px' });
            items.forEach(function(it) { obs.observe(it.sec); });
        }

        // ── ¿La sección tiene contenido? ──
        function tieneContenido(sec) {
            var campos = sec.querySelectorAll('textarea, input[type="text"], select');
            for (var i = 0; i < campos.length; i++) {
                var c = campos[i];
                if (c.readOnly || c.disabled || c.id === 'enfoque_id') continue;
                var valor = c.value;
                if (c.classList.contains('ckeditor') && typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[c.id]) {
                    valor = CKEDITOR.instances[c.id].getData();
                }
                if (String(valor).replace(/<[^>]*>|&nbsp;|\s/g, '') !== '') return true;
            }
            return false;
        }

        function actualizar() {
            var conContenido = 0, total = 0;
            items.forEach(function(it) {
                if (it.informativa) { it.li.classList.add('is-info'); return; }
                total++;
                var ok = tieneContenido(it.sec);
                it.li.classList.toggle('is-completa', ok);
                if (ok) conContenido++;
            });
            nav.querySelector('.silabo-indice-barra span').style.width = (total ? Math.round(conContenido / total * 100) : 0) + '%';
            nav.querySelector('.js-indice-progreso').textContent = conContenido + ' de ' + total + ' secciones con contenido';
        }

        var espera;
        function programar() { clearTimeout(espera); espera = setTimeout(actualizar, 600); }
        var form = document.getElementById('silaboForm');
        if (form) {
            form.addEventListener('input', programar);
            form.addEventListener('change', programar);
            form.addEventListener('click', programar);
            form.addEventListener('silabo:restaurado', programar);
        }
        if (typeof CKEDITOR !== 'undefined') {
            CKEDITOR.on('instanceReady', function(evt) { evt.editor.on('change', programar); programar(); });
        }
        actualizar();
    });
</script>
