/**
 * Animaciones sutiles (GSAP) para el módulo "Calificar" del panel docente.
 * Se carga únicamente desde las vistas de docentes.calificaciones.*.
 * Todo tiene fallback sin animación si GSAP no está disponible.
 */
(function () {
    'use strict';

    var hasGsap = typeof window.gsap !== 'undefined';

    document.addEventListener('DOMContentLoaded', function () {
        var page = document.querySelector('.docente-ui-page');
        if (!page) return;

        entrada(page);
        animarAlertas(page);
        agitarInvalidos(page);
        protegerEnvios(page);
    });

    /* ── Restaura los botones "congelados" en estado de carga ──
       Si el usuario envía un formulario (botón deshabilitado, texto
       "Guardando…"/"Abriendo…") y luego vuelve con el botón "atrás" del
       navegador, éste puede restaurar la página desde el bfcache tal como
       quedó justo antes de navegar — con el botón aún deshabilitado y ese
       texto, sin volver a cargarla del servidor. El evento "pageshow" con
       persisted=true detecta exactamente ese caso y revierte el botón. ── */
    window.addEventListener('pageshow', function (event) {
        if (!event.persisted) return;

        document.querySelectorAll('button.is-guardando').forEach(function (btn) {
            if (btn.dataset.textoOriginal) {
                btn.innerHTML = btn.dataset.textoOriginal;
            }
            btn.disabled = false;
            btn.classList.remove('is-guardando');
        });
    });

    /* ── Entrada escalonada del encabezado y las tarjetas ── */
    function entrada(page) {
        var header = page.querySelector('.docente-ui-toolbar');
        var cards = page.querySelectorAll('.docente-ui-card, .docente-cal-toolbar, .docente-cal-savebar');

        if (!hasGsap) {
            if (header) header.classList.add('is-visible');
            cards.forEach(function (c) { c.classList.add('is-visible'); });
            return;
        }

        var tl = gsap.timeline({ defaults: { ease: 'power2.out' } });
        if (header) {
            tl.from(header, { opacity: 0, y: -12, duration: 0.45 });
        }
        if (cards.length) {
            tl.from(cards, { opacity: 0, y: 14, duration: 0.4, stagger: 0.07 }, '-=0.2');
        }
    }

    /* ── Alertas (éxito / error) entrando con vida ── */
    function animarAlertas(page) {
        var alertas = page.querySelectorAll('.alert');
        if (!alertas.length) return;

        if (!hasGsap) {
            alertas.forEach(function (a) { a.classList.add('is-visible'); });
            return;
        }

        gsap.from(alertas, {
            opacity: 0,
            y: -10,
            scale: 0.98,
            duration: 0.4,
            stagger: 0.08,
            ease: 'back.out(1.6)',
        });
    }

    /* ── Shake sutil en campos marcados como inválidos + scroll al primero ── */
    function agitarInvalidos(page) {
        var invalidos = page.querySelectorAll('.is-invalid');
        if (!invalidos.length) return;

        invalidos[0].scrollIntoView({ behavior: 'smooth', block: 'center' });

        if (!hasGsap) return;

        gsap.fromTo(
            invalidos,
            { x: -4 },
            { x: 0, duration: 0.45, ease: 'elastic.out(1, 0.35)', stagger: 0.01, delay: 0.3 }
        );
    }

    /* ── Evita doble envío: deshabilita el botón y muestra spinner al enviar ──
       El texto por defecto es neutro ("Procesando…") porque este mismo
       listener se engancha a TODOS los formularios de la página, y no todos
       guardan algo (p. ej. el botón "Calificar" del selector de curso solo
       navega a la siguiente pantalla). Cada botón puede indicar su propio
       texto con data-loading-text="Guardando…", "Abriendo…", etc. ── */
    function protegerEnvios(page) {
        var forms = page.querySelectorAll('form');
        forms.forEach(function (form) {
            form.addEventListener('submit', function () {
                var btn = form.querySelector('button[type="submit"]');
                if (!btn || btn.disabled) return;

                var textoCarga = btn.dataset.loadingText || 'Procesando…';
                btn.dataset.textoOriginal = btn.innerHTML;
                btn.disabled = true;
                btn.classList.add('is-guardando');
                btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-2" role="status" aria-hidden="true"></span>' + textoCarga;
            });
        });
    }

    /* ── Utilidad reusada por index.blade.php y alumnos.blade.php para las
       pestañas FID/PPD y Parcial1/Parcial2/Desempeño: crossfade en vez de
       display:none/block instantáneo. Con fallback sin GSAP. ── */
    window.calificarCrossfade = function (mostrar, ocultarLista) {
        ocultarLista = ocultarLista || [];

        if (!hasGsap) {
            ocultarLista.forEach(function (el) { if (el) el.style.display = 'none'; });
            if (mostrar) mostrar.style.display = 'block';
            return;
        }

        ocultarLista.forEach(function (el) {
            if (!el || el.style.display === 'none') return;
            gsap.to(el, {
                opacity: 0,
                duration: 0.15,
                onComplete: function () { el.style.display = 'none'; },
            });
        });

        if (mostrar) {
            mostrar.style.opacity = 0;
            mostrar.style.display = 'block';
            gsap.to(mostrar, { opacity: 1, duration: 0.25, delay: 0.1, ease: 'power1.out' });
        }
    };

    window.calificarAgitarInvalidos = function () {
        var page = document.querySelector('.docente-ui-page');
        if (page) agitarInvalidos(page);
    };
})();
