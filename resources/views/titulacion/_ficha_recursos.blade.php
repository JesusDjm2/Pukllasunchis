{{-- Recursos de la ficha de titulación que van UNA vez por página (ver _show_contenido).
     Espera $docentes y $asesores. --}}
@once
    <script>
        // Mensaje con formato al enviar una RD (CKEditor, ya cargado en el layout). Cada ficha trae
        // varios formularios ocultos y llega por AJAX: el editor nace cuando su campo se hace visible.
        // divarea (sin iframe): la ficha se mueve entre la fila y el panel lateral, y un iframe se vaciaría.
        (function () {
            if (!window.IntersectionObserver) return;
            var visible = new IntersectionObserver(function (entradas) {
                entradas.forEach(function (e) {
                    if (!e.isIntersecting || !window.CKEDITOR || e.target.dataset.editorListo) return;
                    e.target.dataset.editorListo = '1';
                    visible.unobserve(e.target);
                    // CKEditor oculta el campo: con «required» el navegador bloquearía el envío. Lo valida el servidor.
                    e.target.removeAttribute('required');
                    CKEDITOR.replace(e.target, {
                        language: 'es', height: 230, extraPlugins: 'divarea', removeButtons: '', removePlugins: 'elementspath,image,maximize,about', resize_enabled: false,
                        toolbar: [['Bold', 'Italic', 'Underline'], ['NumberedList', 'BulletedList'], ['Link', 'Unlink'], ['RemoveFormat']],
                        allowedContent: 'p br strong em u ul ol li; a[!href,target,rel]', linkShowAdvancedTab: false, linkShowTargetTab: false,
                    });
                });
            });
            function buscar() {
                document.querySelectorAll('textarea.js-editor-rd:not([data-editor-listo])').forEach(function (t) { visible.observe(t); });
            }
            document.addEventListener('DOMContentLoaded', function () {
                buscar();
                new MutationObserver(buscar).observe(document.body, { childList: true, subtree: true });
            });
            document.addEventListener('submit', function (e) {
                if (!window.CKEDITOR) return;
                Object.keys(CKEDITOR.instances).forEach(function (n) {
                    var inst = CKEDITOR.instances[n];
                    if (e.target.contains(inst.element.$)) inst.updateElement();
                });
            }, true);
        })();
    </script>
    {{-- Diseño de la ficha (cabecera, línea de tiempo y panel del paso): hoja propia del módulo. --}}
    <link rel="stylesheet" href="{{ asset('css/pk-titulacion.css') }}?v={{ filemtime(public_path('css/pk-titulacion.css')) }}">
    <script>
        // Línea de tiempo de la ficha: al elegir un paso se muestra solo su panel. Delegado en el
        // documento para que funcione también en las fichas que llegan por AJAX.
        document.addEventListener('click', function (e) {
            var boton = e.target.closest('[data-tp-ir]');
            if (!boton) return;
            var tp = boton.closest('[data-tp]');
            if (!tp) return;
            var clave = boton.dataset.tpIr;
            tp.querySelectorAll('[data-tp-ir]').forEach(function (b) { b.classList.toggle('is-visible', b.dataset.tpIr === clave); });
            var panel = null;
            tp.querySelectorAll('[data-tp-det]').forEach(function (d) {
                var es = d.dataset.tpDet === clave;
                d.hidden = !es;
                if (es) panel = d;
            });
            if (panel) {
                panel.classList.remove('tp-entra'); void panel.offsetWidth; panel.classList.add('tp-entra');
                // Llevar el panel a la vista si quedó fuera: en pantallas angostas está debajo de la
                // línea, y en escritorio arriba (p. ej. al elegir un acceso del pie habiendo bajado).
                var caja = panel.closest('.tl-panel__cuerpo');
                var arriba = caja ? caja.getBoundingClientRect().top : 0;
                var r = panel.getBoundingClientRect();
                if (window.matchMedia('(max-width: 991.98px)').matches || r.top < arriba || r.top > window.innerHeight * 0.6) {
                    panel.scrollIntoView({ behavior: 'smooth', block: 'start' });
                }
            }
        });
        // Tesis › Enlaces de los dictaminantes: agregar otra fila o quitar una (la última se vacía, no se borra).
        document.addEventListener('click', function (e) {
            var agregar = e.target.closest('[data-tp-enlace-agregar]');
            var quitar = e.target.closest('[data-tp-enlace-quitar]');
            if (!agregar && !quitar) return;
            var caja = (agregar ? agregar.parentElement : quitar.closest('form')).querySelector('[data-tp-enlaces]');
            var renumerar = function () {
                caja.querySelectorAll('.tp-enlaces__n').forEach(function (n, i) { n.textContent = i + 1; });
            };
            if (agregar) {
                var fila = caja.lastElementChild.cloneNode(true);
                fila.querySelector('input').value = '';
                var ir = fila.querySelector('.tp-enlaces__ir');
                if (ir) ir.remove();
                caja.appendChild(fila);
                renumerar();
                fila.querySelector('input').focus();
                return;
            }
            var filaQuitar = quitar.closest('.tp-enlaces__fila');
            if (caja.children.length > 1) filaQuitar.remove(); else filaQuitar.querySelector('input').value = '';
            renumerar();
        });

        // Buscador de docentes (asesores/jurado): filtra el select siguiente mientras se escribe;
        // la lista se carga del <template> como al enfocar el select. Enter elige el primero.
        document.addEventListener('input', function (e) {
            var buscar = e.target.closest('[data-tp-buscar]');
            if (!buscar) return;
            var sel = buscar.parentElement.querySelector('select.js-docentes');
            if (!sel) return;
            if (!sel.dataset.cargado) sel.dispatchEvent(new Event('focusin', { bubbles: true }));
            var q = buscar.value.trim().toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
            var primero = null, visibles = 0;
            sel.querySelectorAll('option').forEach(function (op) {
                if (!op.value) { op.hidden = q !== ''; return; }
                var texto = op.textContent.toLowerCase().normalize('NFD').replace(/[̀-ͯ]/g, '');
                var ok = q.split(/\s+/).every(function (p) { return texto.indexOf(p) !== -1; });
                op.hidden = !ok;
                if (ok) { visibles++; if (!primero) primero = op; }
            });
            sel.size = q ? Math.min(Math.max(visibles, 2), 7) : 0;
            if (primero && q) sel.value = primero.value;
        });
        document.addEventListener('keydown', function (e) {
            var buscar = e.target.closest && e.target.closest('[data-tp-buscar]');
            if (!buscar || e.key !== 'Enter') return;
            e.preventDefault();
            var sel = buscar.parentElement.querySelector('select.js-docentes');
            if (sel && sel.value) { sel.size = 0; buscar.value = sel.options[sel.selectedIndex].textContent.trim(); }
        });
        document.addEventListener('change', function (e) {
            if (!e.target.matches || !e.target.matches('select.js-docentes')) return;
            var buscar = e.target.parentElement.querySelector('[data-tp-buscar]');
            if (!buscar || !e.target.size) return;
            e.target.size = 0;
            buscar.value = e.target.options[e.target.selectedIndex]?.textContent.trim() || '';
        });
        // Al guardar desde un panel, volver a ese mismo paso.
        document.addEventListener('submit', function (e) {
            var det = e.target.closest('[data-tp-det]');
            if (!det || e.target.querySelector('input[name=volver_paso]')) return;
            var campo = document.createElement('input');
            campo.type = 'hidden'; campo.name = 'volver_paso'; campo.value = det.dataset.tpDet;
            e.target.appendChild(campo);
        }, true);
    </script>
    {{-- Lista completa de docentes: UNA copia por página; cada select .js-docentes
         la carga al enfocarlo (antes se repetía ~5 veces por fila × 30 filas). --}}
    <template id="tpl-docentes">
        <optgroup label="Docentes">
            @foreach ($docentes as $docente)
                <option value="{{ $docente->id }}">{{ $docente->name }} {{ $docente->apellidos }}</option>
            @endforeach
        </optgroup>
        @if (collect($asesores ?? [])->where('activo', true)->isNotEmpty())
            <optgroup label="Asesores registrados en Titulación">
                @foreach (collect($asesores)->where('activo', true) as $asesorCat)
                    <option value="a{{ $asesorCat->id }}">{{ $asesorCat->nombreCompleto() }}{{ $asesorCat->institucion ? ' — '.$asesorCat->institucion : '' }}</option>
                @endforeach
            </optgroup>
        @endif
    </template>
    <script>
        (function () {
            function cargar(e) {
                var sel = e.target;
                if (!sel.classList || !sel.classList.contains('js-docentes') || sel.dataset.cargado) return;
                var tpl = document.getElementById('tpl-docentes');
                var vacio = document.createElement('option');
                vacio.value = '';
                vacio.textContent = sel.dataset.vacio || '—';
                sel.innerHTML = '';
                sel.appendChild(vacio);
                sel.appendChild(tpl.content.cloneNode(true));
                sel.value = sel.dataset.sel || '';
                sel.dataset.cargado = '1';
            }
            ['focusin', 'mousedown', 'touchstart'].forEach(function (ev) {
                document.addEventListener(ev, cargar, true);
            });
        })();
    </script>
    <style>
        .ft-ficha { background: #fff; border: 1px solid #e3e6f0; border-radius: .6rem; padding: .75rem 1rem 1rem; }
        .ft-ficha .mt-tabs__nav { margin-left: -1rem; margin-right: -1rem; padding: 0 .75rem; }
        .ft-alertas { display: grid; gap: .35rem; }
        .ft-alerta { display: flex; align-items: center; gap: .35rem; margin: 0; padding: .45rem .7rem; border-radius: .45rem; font-size: .83rem; cursor: pointer; border: 1px solid transparent; }
        .ft-alerta--danger { background: #fdecea; border-color: #f5c2bd; color: #8a1f14; }
        .ft-alerta--warning { background: #fff8e6; border-color: #f6dc8e; color: #6b5000; }
        .ft-alerta--secondary { background: #f8f9fc; border-color: #e3e6f0; color: #4a5568; }
        .ft-alerta:hover { filter: brightness(.97); }
        /* Avisos legibles en los temas oscuros: fondo tenue de su color y texto claro. */
        .dark-mode .ft-alerta--warning { background: rgba(210, 153, 34, .14); border-color: rgba(210, 153, 34, .45); color: #f2cc60; }
        .dark-mode .ft-alerta--danger { background: rgba(248, 81, 73, .13); border-color: rgba(248, 81, 73, .45); color: #ffa198; }
        .dim-mode .ft-alerta--warning { background: var(--lm-durazno-fondo, #3d2a26); border-color: rgba(245, 197, 144, .4); color: var(--lm-durazno, #f5c590); }
        .dim-mode .ft-alerta--danger { background: var(--lm-coral-fondo, #43262c); border-color: rgba(243, 154, 166, .4); color: var(--lm-coral, #f39aa6); }
        .dark-mode .ft-alerta strong, .dim-mode .ft-alerta strong { color: inherit; filter: brightness(1.12); }
        .ft-seccion { display: flex; align-items: center; gap: .45rem; margin-bottom: .6rem; padding-bottom: .35rem; border-bottom: 1px solid #e3e6f0; font-size: .8rem; font-weight: 700; text-transform: uppercase; letter-spacing: .04em; color: #4e73df; }
        .ft-seccion__meta { margin-left: auto; font-size: .72rem; font-weight: 600; text-transform: none; letter-spacing: 0; color: #8892a6; }
        .dark-mode .ft-seccion, .dim-mode .ft-seccion { border-color: rgba(255, 255, 255, .12); color: inherit; }
        .ft-subcaja { margin: .25rem 0 1rem; padding: .6rem .8rem .2rem; border: 1px dashed #f6c23e; border-radius: .5rem; background: #fffbeb; }
        .ft-subcaja__titulo { font-size: .8rem; font-weight: 700; color: #92400e; margin-bottom: .35rem; }
        .ft-desplegable > summary { cursor: pointer; font-size: .8rem; font-weight: 600; color: #4e73df; list-style: none; display: inline-flex; align-items: center; }
        .ft-desplegable > summary::-webkit-details-marker { display: none; }
        .ft-desplegable > summary::before { content: '\25B8'; margin-right: .35rem; transition: transform .15s; }
        .ft-desplegable[open] > summary::before { transform: rotate(90deg); }
        .ft-desplegable[open] { padding: .5rem .7rem; border: 1px solid #e3e6f0; border-radius: .5rem; background: #f8f9fc; }
        .ft-desplegable--accion > summary { color: #1cc88a; }
        .ft-pti-historial { display: grid; gap: .45rem; }
        .ft-pti-historial__item { padding: .55rem .75rem; border: 1px solid #e3e6f0; border-left: 4px solid #d1d5db; border-radius: .5rem; font-size: .85rem; }
        .ft-pti-historial__item.is-vigente { border-left-color: #1cc88a; background: #f0fdf4; }
        .ft-rd-linea { list-style: none; margin: 0; padding: 0 0 0 .2rem; position: relative; }
        .ft-rd-linea::before { content: ''; position: absolute; left: .95rem; top: .4rem; bottom: .4rem; width: 2px; background: #e3e6f0; }
        .ft-rd-linea__item { position: relative; display: flex; gap: .7rem; padding: .35rem 0; }
        .ft-rd-linea__punto { position: relative; z-index: 1; flex-shrink: 0; width: 1.6rem; height: 1.6rem; border-radius: 50%; color: #fff; display: flex; align-items: center; justify-content: center; font-size: .7rem; box-shadow: 0 0 0 3px #fff; }
        .ft-rd-linea__cuerpo { flex: 1; min-width: 0; padding: .35rem .6rem; border: 1px solid #e3e6f0; border-radius: .5rem; background: #fff; }
        .ft-rd-linea__fecha { font-size: .75rem; font-weight: 700; color: #4a5568; }
        .ft-rd-linea__numero { word-break: break-word; color: #1e293b; }
        .dark-mode .ft-subcaja, .dim-mode .ft-subcaja { background: rgba(246, 194, 62, .08); }
        .dark-mode .ft-subcaja__titulo, .dim-mode .ft-subcaja__titulo { color: #f6c23e; }
        .dark-mode .ft-desplegable[open], .dim-mode .ft-desplegable[open],
        .dark-mode .ft-pti-historial__item, .dim-mode .ft-pti-historial__item,
        .dark-mode .ft-rd-linea__cuerpo, .dim-mode .ft-rd-linea__cuerpo { background: rgba(255, 255, 255, .03); border-color: rgba(255, 255, 255, .12); }
        .dark-mode .ft-pti-historial__item.is-vigente, .dim-mode .ft-pti-historial__item.is-vigente { background: rgba(28, 200, 138, .08); }
        .dark-mode .ft-rd-linea::before, .dim-mode .ft-rd-linea::before { background: rgba(255, 255, 255, .12); }
        .dark-mode .ft-rd-linea__punto, .dim-mode .ft-rd-linea__punto { box-shadow: 0 0 0 3px transparent; }
        .dark-mode .ft-rd-linea__fecha, .dim-mode .ft-rd-linea__fecha,
        .dark-mode .ft-rd-linea__numero, .dim-mode .ft-rd-linea__numero { color: inherit; }
        .ft-vigencia { padding: .6rem .8rem; border: 1px solid #e3e6f0; border-radius: .5rem; background: #f8f9fc; }
        .dark-mode .ft-ficha, .dim-mode .ft-ficha { background: transparent; border-color: rgba(255, 255, 255, .12); }
        .dark-mode .ft-alerta--secondary, .dim-mode .ft-alerta--secondary,
        .dark-mode .ft-vigencia, .dim-mode .ft-vigencia { background: rgba(255, 255, 255, .04); border-color: rgba(255, 255, 255, .12); color: inherit; }

        /* Etapas del proceso (pestaña Proceso) */
        .ft-etapa { border: 1px solid #e3e6f0; border-left: 4px solid #d1d5db; border-radius: .6rem; padding: .7rem .9rem .75rem; margin-bottom: .75rem; }
        .ft-etapa--en_proceso { border-left-color: #4e73df; }
        .ft-etapa--completo { border-left-color: #1cc88a; }
        .ft-etapa--otros { border-left-color: #858796; background: #fcfcfd; }
        .ft-etapa__h { display: flex; align-items: center; gap: .5rem; margin-bottom: .5rem; }
        /* Acordeón por etapas: summary como cabecera; plegada = una línea. */
        details.ft-etapa > summary { list-style: none; cursor: pointer; flex-wrap: wrap; }
        details.ft-etapa > summary::-webkit-details-marker { display: none; }
        details.ft-etapa:not([open]) > summary { margin-bottom: 0; }
        details.ft-etapa:not([open]) { padding-bottom: .7rem; }
        .ft-etapa__chev { color: #94a3b8; font-size: .75rem; transition: transform .2s; margin-left: .25rem; }
        details.ft-etapa[open] > summary .ft-etapa__chev { transform: rotate(180deg); }
        .ft-etapa__resumen { order: 10; flex-basis: 100%; padding-left: 2.05rem; font-size: .76rem; color: #64748b; }
        details.ft-etapa[open] > summary .ft-etapa__resumen { display: none; }
        .ft-etapa--bloqueada { border-style: dashed; border-left-style: solid; background: transparent; opacity: .75; padding: .55rem .9rem; }
        .ft-etapa--bloqueada .ft-etapa__h { margin-bottom: 0; flex-wrap: wrap; }
        .ft-etapa--bloqueada .ft-etapa__n { background: #e5e7eb; color: #94a3b8; }
        .ft-etapa__lock { font-size: .75rem; color: #94a3b8; }
        .dark-mode .ft-etapa--bloqueada .ft-etapa__n, .dim-mode .ft-etapa--bloqueada .ft-etapa__n { background: rgba(255, 255, 255, .08); }
        .ft-etapa__n { width: 1.55rem; height: 1.55rem; border-radius: 50%; display: inline-flex; align-items: center; justify-content: center; font-size: .72rem; font-weight: 700; background: #eef2ff; color: #4e73df; flex-shrink: 0; }
        .ft-etapa--completo .ft-etapa__n { background: #1cc88a; color: #fff; }
        /* Pasos numerados del TI (informe, exposición, asesores, RD, jurado, repositorio, SUNEDU) */
        .ft-ti-paso { display: flex; gap: .65rem; padding: .55rem 0; border-top: 1px dashed #e3e6f0; }
        .ft-ti-paso:first-of-type { border-top: 0; }
        .ft-ti-paso__n { width: 1.35rem; height: 1.35rem; border-radius: 50%; flex-shrink: 0; display: inline-flex; align-items: center; justify-content: center;
            font-size: .68rem; font-weight: 700; background: #eef2ff; color: #4e73df; margin-top: .1rem; }
        .ft-ti-paso.is-hecho .ft-ti-paso__n { background: #1cc88a; color: #fff; }
        .ft-ti-paso__cuerpo { flex: 1; min-width: 0; }
        .ft-ti-paso__cuerpo > .ft-desplegable:first-child, .ft-ti-paso__cuerpo > .ft-paso-bloqueado:first-child { margin-top: 0 !important; }
        .ft-ti-paso__titulo { font-size: .84rem; font-weight: 700; color: #1e293b; }
        .ft-espera { font-size: .82rem; padding: .5rem .75rem; border-radius: .5rem; background: #eff6ff; border: 1px solid #bfdbfe; color: #1e40af; }
        .ft-participantes { list-style: none; padding: 0; margin: .3rem 0 0; }
        .ft-participantes li { display: flex; align-items: center; gap: .45rem; font-size: .82rem; padding: .2rem 0; }
        .dark-mode .ft-ti-paso, .dim-mode .ft-ti-paso { border-color: rgba(255, 255, 255, .12); }
        .dark-mode .ft-ti-paso__n, .dim-mode .ft-ti-paso__n { background: rgba(255, 255, 255, .08); color: inherit; }
        .dark-mode .ft-ti-paso.is-hecho .ft-ti-paso__n, .dim-mode .ft-ti-paso.is-hecho .ft-ti-paso__n { background: #1cc88a; color: #fff; }
        .dark-mode .ft-ti-paso__titulo, .dim-mode .ft-ti-paso__titulo { color: inherit; }
        .dark-mode .ft-espera, .dim-mode .ft-espera { background: rgba(59, 130, 246, .12); border-color: rgba(59, 130, 246, .35); color: #93c5fd; }
        .ft-etapa__titulo { font-weight: 700; font-size: .92rem; color: #1e293b; }
        .ft-etapa__meta { font-size: .74rem; color: #8892a6; }
        .ft-etapa__datos { display: grid; grid-template-columns: repeat(auto-fit, minmax(190px, 1fr)); gap: .25rem 1rem; margin: 0 0 .5rem; font-size: .84rem; }
        .ft-etapa__datos dt { font-size: .7rem; font-weight: 600; color: #8892a6; text-transform: uppercase; letter-spacing: .02em; }
        .ft-etapa__datos dd { margin: 0; color: #1e293b; word-break: break-word; }
        .ft-rd-slot { display: flex; flex-wrap: wrap; align-items: center; gap: .4rem .75rem; padding: .45rem .6rem; margin-top: .4rem; border: 1px dashed #d1d5db; border-radius: .5rem; background: #fafbfc; }
        .ft-rd-slot.is-registrada { border-style: solid; border-color: #c7e9d9; background: #f3fbf7; }
        .ft-rd-slot__info { display: flex; flex-wrap: wrap; align-items: baseline; gap: .2rem .5rem; flex: 1; min-width: 220px; font-size: .83rem; }
        .ft-rd-slot__label { font-weight: 700; color: #4a5568; }
        .ft-rd-slot__numero { font-weight: 700; color: #1e293b; word-break: break-word; }
        .ft-rd-slot__meta { color: #6c757d; font-size: .78rem; }
        .ft-rd-slot__pendiente { color: #8892a6; font-style: italic; }
        .ft-rd-slot__acciones { display: flex; flex-wrap: wrap; gap: .35rem; }
        .dark-mode .ft-etapa, .dim-mode .ft-etapa { border-color: rgba(255, 255, 255, .12); background: transparent; }
        .dark-mode .ft-etapa--en_proceso, .dim-mode .ft-etapa--en_proceso { border-left-color: #4e73df; }
        .dark-mode .ft-etapa--completo, .dim-mode .ft-etapa--completo { border-left-color: #1cc88a; }
        .dark-mode .ft-etapa__titulo, .dim-mode .ft-etapa__titulo,
        .dark-mode .ft-etapa__datos dd, .dim-mode .ft-etapa__datos dd,
        .dark-mode .ft-rd-slot__numero, .dim-mode .ft-rd-slot__numero,
        .dark-mode .ft-rd-slot__label, .dim-mode .ft-rd-slot__label { color: inherit; }
        .dark-mode .ft-rd-slot, .dim-mode .ft-rd-slot { background: rgba(255, 255, 255, .03); border-color: rgba(255, 255, 255, .15); }
        .dark-mode .ft-rd-slot.is-registrada, .dim-mode .ft-rd-slot.is-registrada { background: rgba(28, 200, 138, .07); border-color: rgba(28, 200, 138, .3); }
    </style>

    {{-- Vista previa de los correos de aviso en un popup (sin salir de la lista).
         El enlace conserva target=_blank por si el JS no carga. --}}
    <div class="modal fade" id="modalVistaAviso" tabindex="-1" role="dialog" aria-labelledby="modalVistaAvisoTitulo" aria-hidden="true">
        <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
            <div class="modal-content">
                <div class="modal-header py-2">
                    <h6 class="modal-title font-weight-bold" id="modalVistaAvisoTitulo"><i class="fas fa-envelope-open-text mr-1"></i><span>Vista previa del correo</span></h6>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body p-0" style="background:#f4f6fb;">
                    <div class="js-vista-aviso-cargando text-center text-muted small py-5"><i class="fas fa-spinner fa-spin mr-1"></i>Cargando…</div>
                    <iframe title="Vista previa del correo" class="d-block w-100 border-0" style="height:70vh;"></iframe>
                </div>
                <div class="modal-footer py-2">
                    <small class="text-muted mr-auto">Así lo recibirá la persona. Desde aquí no se envía nada.</small>
                    <button type="button" class="btn btn-sm btn-outline-secondary" data-dismiss="modal">Cerrar</button>
                </div>
            </div>
        </div>
    </div>
    <script>
        // Cabecera de la ficha: DNI, celular y correo se copian con un clic (✓ verde un momento).
        // Delegado en document: la ficha también llega por AJAX al abrir la fila.
        document.addEventListener('click', function (e) {
            var boton = e.target.closest('.js-tc-copiar');
            if (!boton || !boton.dataset.copiar || !navigator.clipboard) return;
            navigator.clipboard.writeText(boton.dataset.copiar).then(function () {
                var icono = boton.querySelector('i');
                var antes = icono.className;
                boton.classList.add('is-copiado');
                icono.className = 'fas fa-check';
                setTimeout(function () { boton.classList.remove('is-copiado'); icono.className = antes; }, 1600);
            });
        });

        document.addEventListener('click', function (e) {
            var enlace = e.target.closest('.js-vista-aviso');
            if (!enlace || !window.jQuery || !jQuery.fn.modal) return;
            e.preventDefault();
            var modal = document.getElementById('modalVistaAviso');
            var iframe = modal.querySelector('iframe');
            var cargando = modal.querySelector('.js-vista-aviso-cargando');
            modal.querySelector('#modalVistaAvisoTitulo span').textContent = enlace.dataset.titulo || 'Vista previa del correo';
            cargando.style.display = '';
            iframe.style.display = 'none';
            iframe.onload = function () { cargando.style.display = 'none'; iframe.style.display = ''; };
            iframe.src = enlace.href;
            // El bloque vive dentro de una fila del acordeón (puede estar oculta): el modal va al body.
            if (modal.parentNode !== document.body) document.body.appendChild(modal);
            jQuery(modal).modal('show');
        });
        // Al cerrar, vaciar el iframe para que no quede el correo anterior al abrir otro.
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery) jQuery('#modalVistaAviso').on('hidden.bs.modal', function () { this.querySelector('iframe').src = 'about:blank'; });
        });
    </script>
    <script>
        // "Siguiente paso" y otros atajos: abren el desplegable indicado (y su pestaña).
        document.addEventListener('click', function (e) {
            var boton = e.target.closest('.js-abrir');
            if (!boton) return;
            e.preventDefault();
            var destino = document.getElementById(boton.dataset.abrir);
            if (!destino) return;
            if (boton.dataset.tab) {
                var radio = document.getElementById(boton.dataset.tab);
                if (radio) radio.checked = true;
            }
            for (var d = destino; d; d = d.parentElement && d.parentElement.closest('details')) d.open = true;
            destino.scrollIntoView({ behavior: 'smooth', block: 'center' });
            destino.classList.add('ft-resaltado');
            setTimeout(function () { destino.classList.remove('ft-resaltado'); }, 1600);
            var campo = destino.querySelector('input:not([type=hidden]), select, textarea');
            if (campo) setTimeout(function () { campo.focus({ preventScroll: true }); }, 350);
        });
        // Historial: el lápiz / "Registrar" / "Nueva…" abre su formulario dentro de la misma
        // tarjeta; un segundo clic (o Cancelar) lo cierra. Todos los botones que apuntan al mismo
        // panel quedan sincronizados (aria-expanded) para lectores de pantalla.
        document.addEventListener('click', function (e) {
            var boton = e.target.closest('[data-ft-toggle]');
            if (!boton) return;
            var panel = document.getElementById(boton.dataset.ftToggle);
            if (!panel) return;
            e.preventDefault();
            var abrir = panel.hidden;
            panel.hidden = !abrir;
            document.querySelectorAll('[data-ft-toggle="' + panel.id + '"]').forEach(function (b) {
                if (b.hasAttribute('aria-expanded')) b.setAttribute('aria-expanded', abrir ? 'true' : 'false');
                b.classList.toggle('is-activo', abrir);
            });
            var tarjeta = panel.closest('.ft-rd-linea__cuerpo, .ft-acta');
            if (tarjeta) tarjeta.classList.toggle('is-editando', abrir);
            if (abrir) {
                var campo = panel.querySelector('input:not([type=hidden]):not([type=file]), select, textarea');
                if (campo) campo.focus({ preventScroll: true });
                panel.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
            } else if (!boton.hasAttribute('aria-expanded')) {
                // Cancelar: el foco vuelve al botón que lo abrió.
                var origen = document.querySelector('[data-ft-toggle="' + panel.id + '"][aria-expanded]');
                if (origen) origen.focus();
            }
        });
        // "Guardado" como aviso pequeño en la esquina (si las alertas del sistema están cargadas).
        document.addEventListener('DOMContentLoaded', function () {
            if (!window.PkAlert) return;
            document.querySelectorAll('.js-flash-toast').forEach(function (el) {
                PkAlert.toast(el.dataset.toast);
                el.remove();
            });
        });
    </script>
    <style>
        .ft-siguiente { display: flex; align-items: center; flex-wrap: wrap; gap: .75rem; padding: .7rem .9rem; border: 1px solid #c7d2fe; border-radius: .6rem; background: #eef2ff; }
        .ft-siguiente__icono { width: 2.1rem; height: 2.1rem; border-radius: 50%; background: #4e73df; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; font-size: .85rem; }
        .ft-siguiente__texto { flex: 1 1 14rem; min-width: 0; font-size: .9rem; line-height: 1.3; }
        .ft-siguiente__eti { display: block; font-size: .68rem; font-weight: 700; text-transform: uppercase; letter-spacing: .05em; color: #4e73df; }
        .ft-siguiente--completo { border-color: #a7f3d0; background: #ecfdf5; }
        .ft-siguiente--completo .ft-siguiente__icono { background: #1cc88a; }
        .ft-siguiente--completo .ft-siguiente__eti { color: #15803d; }
        .dark-mode .ft-siguiente { background: rgba(88, 166, 255, .1); border-color: rgba(88, 166, 255, .35); }
        .dark-mode .ft-siguiente__eti { color: #79c0ff; }
        .dim-mode .ft-siguiente { background: var(--lm-lavanda-fondo, #352a40); border-color: rgba(200, 162, 232, .4); }
        .dim-mode .ft-siguiente__icono { background: var(--lm-lavanda, #c8a2e8); color: #241a24; }
        .dim-mode .ft-siguiente__eti { color: var(--lm-lavanda, #c8a2e8); }
        .dark-mode .ft-siguiente--completo, .dim-mode .ft-siguiente--completo { background: rgba(28, 200, 138, .1); border-color: rgba(28, 200, 138, .35); }
        .dark-mode .ft-siguiente--completo .ft-siguiente__eti, .dim-mode .ft-siguiente--completo .ft-siguiente__eti { color: #6ee7b7; }
        .ft-paso > summary { color: #4a5568; }
        .ft-rd-subir { margin: -.25rem 0 .6rem; }
        .ft-etapa--otros .ft-etapa__chev { margin-left: auto; }
        .ft-rd-subir:not([open]) { display: none; }
        /* Pasos y RD que todavía no corresponden (ReglasProceso): candado y motivo, sin acción. */
        .ft-paso-bloqueado { font-size: .8rem; font-weight: 600; color: #94a3b8; padding: .15rem 0; }
        .ft-paso-bloqueado span { font-weight: 400; }
        .ft-rd-slot.is-bloqueada { opacity: .7; border-style: dashed; }
        .ft-rd-slot.is-bloqueada .ft-rd-slot__pendiente { color: #94a3b8; font-style: normal; }
        .dark-mode .ft-paso-bloqueado, .dim-mode .ft-paso-bloqueado, .dark-mode .ft-rd-slot.is-bloqueada .ft-rd-slot__pendiente, .dim-mode .ft-rd-slot.is-bloqueada .ft-rd-slot__pendiente { color: rgba(255, 255, 255, .5); }
        .dark-mode .ft-paso > summary, .dim-mode .ft-paso > summary { color: inherit; }
        .ft-resaltado { animation: ft-resaltar 1.6s ease-out; }
        @keyframes ft-resaltar { 0%, 30% { box-shadow: 0 0 0 3px rgba(78, 115, 223, .45); } 100% { box-shadow: 0 0 0 3px transparent; } }
        @media (prefers-reduced-motion: reduce) { .ft-resaltado { animation: none; } }
    </style>
@endonce
