/*
 * Pestañas del sistema de diseño compartido (.al-tabs / .al-panel, ver pk-ui.css).
 *   <div class="al-tabs" role="tablist" data-al-tabs>
 *       <button class="al-tab" role="tab" data-panel="cursos">…</button>
 *   </div>
 *   <section class="al-panel" role="tabpanel" data-panel="cursos">…</section>
 * La pestaña elegida queda en el #hash (se puede compartir el enlace y al volver
 * atrás se conserva). Flechas ← → / Inicio / Fin para moverse con el teclado.
 */
(function () {
    function iniciar(lista) {
        var tabs = Array.prototype.slice.call(lista.querySelectorAll('.al-tab[data-panel]'));
        if (!tabs.length) return;
        var raiz = lista.parentNode;
        var paneles = Array.prototype.slice.call(raiz.querySelectorAll('.al-panel[data-panel]'));
        var nombres = tabs.map(function (t) { return t.dataset.panel; });
        // Enlaces que abren una pestaña (<a href="#crear" data-al-ir>) estén donde estén en la página
        // (p. ej. indicadores encima de las pestañas): se marcan .is-activa junto con su pestaña.
        var enlaces = Array.prototype.slice.call(document.querySelectorAll('[data-al-ir]')).filter(function (a) {
            return nombres.indexOf((a.getAttribute('href') || '').replace('#', '')) !== -1;
        });

        function activar(nombre, guardar) {
            var tab = tabs.filter(function (t) { return t.dataset.panel === nombre; })[0] || tabs[0];
            tabs.forEach(function (t) {
                var on = t === tab;
                t.classList.toggle('is-activa', on);
                t.setAttribute('aria-selected', on ? 'true' : 'false');
                t.tabIndex = on ? 0 : -1;
            });
            paneles.forEach(function (p) { p.hidden = p.dataset.panel !== tab.dataset.panel; });
            enlaces.forEach(function (a) {
                var on = a.getAttribute('href') === '#' + tab.dataset.panel;
                a.classList.toggle('is-activa', on);
                if (on) a.setAttribute('aria-current', 'true'); else a.removeAttribute('aria-current');
            });
            if (guardar && history.replaceState) history.replaceState(null, '', '#' + tab.dataset.panel);
            return tab;
        }

        tabs.forEach(function (t, i) {
            t.addEventListener('click', function () { activar(t.dataset.panel, true); });
            t.addEventListener('keydown', function (e) {
                var destino = { ArrowRight: i + 1, ArrowLeft: i - 1, Home: 0, End: tabs.length - 1 }[e.key];
                if (destino === undefined) return;
                e.preventDefault();
                var sig = tabs[(destino + tabs.length) % tabs.length];
                activar(sig.dataset.panel, true).focus();
            });
        });

        // Sin #hash válido abre la que venga marcada .is-activa en el HTML (o la primera).
        var inicial = (tabs.filter(function (t) { return t.classList.contains('is-activa'); })[0] || tabs[0]).dataset.panel;
        function desdeHash() {
            var h = location.hash.replace('#', '');
            activar(tabs.some(function (t) { return t.dataset.panel === h; }) ? h : inicial, false);
        }
        desdeHash();
        window.addEventListener('hashchange', desdeHash);

        enlaces.forEach(function (a) {
            a.addEventListener('click', function (e) {
                e.preventDefault();
                activar(a.getAttribute('href').replace('#', ''), true);
                lista.scrollIntoView({ behavior: 'smooth', block: 'start' });
            });
        });
    }

    function todo() { document.querySelectorAll('[data-al-tabs]').forEach(iniciar); }
    if (document.readyState === 'loading') document.addEventListener('DOMContentLoaded', todo);
    else todo();
})();
