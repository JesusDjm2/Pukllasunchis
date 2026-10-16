{{--
    Borrador local del sílabo (crear y editar): red de seguridad para sesiones largas.
    El avance se guarda en el navegador del docente (localStorage), no en el servidor, y se
    ofrece recuperarlo con un aviso discreto (#silaboAvisoBorrador) al reabrir el formulario.

    Espera:
      $claveBorrador    clave en localStorage (una por curso al crear, una por sílabo al editar)
      $clavesObsoletas  claves que ya no sirven y se borran al cargar (p. ej. el borrador de
                        "crear" de este curso, cuando el sílabo ya existe)
      $conNombresOcultos  true en editar: cada enfoque lleva inputs ocultos nombres[] y descripciones[]

    Reglas (corrigen dos problemas reales):
      · Antes se borraba al presionar Guardar, ANTES de saber si el guardado funcionó: con
        sesión expirada (419) o sin conexión se perdía todo. Ahora, al enviar, solo se marca
        "enviado"; se descarta cuando el formulario vuelve a cargar con esos mismos datos
        (o sea, el servidor sí los guardó).
      · Antes la foto "sin cambios" se tomaba antes de que CKEditor reformateara su HTML, así
        que todo formulario parecía modificado y la alerta salía casi siempre. Ahora la foto
        se toma con los editores listos, solo se guarda si el docente escribió algo y la
        comparación ignora diferencias de espacios.
--}}
<script>
    document.addEventListener('DOMContentLoaded', function() {
        var form = document.getElementById('silaboForm');
        if (!form) return;

        var STORAGE_KEY = @json($claveBorrador);
        var OBSOLETAS = @json($clavesObsoletas ?? []);
        var CON_NOMBRES_OCULTOS = @json((bool) ($conNombresOcultos ?? false));
        var SKIP_NAMES = ['_token', '_method', 'curso_id', 'docente_id'];
        var DIAS_VIGENCIA = 60;

        var aviso = document.getElementById('silaboAvisoBorrador');
        var estado = document.getElementById('silaboEstadoBorrador');

        OBSOLETAS.forEach(function(k) { try { localStorage.removeItem(k); } catch (e) {} });

        // ── Lectura del formulario ──────────────────────────────────────────────
        function isArrayField(name) { return name.slice(-2) === '[]'; }

        function escapeHtml(str) {
            return String(str == null ? '' : str)
                .replace(/&/g, '&amp;').replace(/</g, '&lt;').replace(/>/g, '&gt;').replace(/"/g, '&quot;');
        }

        function fieldValue(row, selector) {
            var el = row.querySelector(selector);
            return el ? el.value : '';
        }

        function collectDraft() {
            if (typeof CKEDITOR !== 'undefined') {
                Object.keys(CKEDITOR.instances).forEach(function(name) {
                    try { CKEDITOR.instances[name].updateElement(); } catch (e) {}
                });
            }

            var fields = {};
            form.querySelectorAll('input[name], textarea[name], select[name]').forEach(function(el) {
                var name = el.name;
                if (!name || isArrayField(name) || SKIP_NAMES.indexOf(name) !== -1 || el.type === 'hidden') return;
                fields[name] = el.value;
            });

            var enfoques = [];
            document.querySelectorAll('#tablaEnfoques tr').forEach(function(row) {
                var nombreEl = row.querySelector('td:first-child input[type="text"]');
                var descEl = row.querySelector('td:first-child small');
                enfoques.push({
                    id: fieldValue(row, '[name="enfoques[]"]'),
                    nombre: CON_NOMBRES_OCULTOS ? fieldValue(row, '[name="nombres[]"]') : (nombreEl ? nombreEl.value : ''),
                    descripcion: CON_NOMBRES_OCULTOS ? fieldValue(row, '[name="descripciones[]"]') : (descEl ? descEl.textContent : ''),
                    observables: fieldValue(row, '[name="observables[]"]'),
                    concretas: fieldValue(row, '[name="concretas[]"]'),
                });
            });

            var unidades = [];
            document.querySelectorAll('#contenedor-tablas .unidad').forEach(function(row) {
                unidades.push({
                    titulo: fieldValue(row, '[name="titulo_unidad[]"]'),
                    situacion: fieldValue(row, '[name="situacion_aprendizaje[]"]'),
                    duracion: fieldValue(row, '[name="duracion[]"]'),
                    desempeno: fieldValue(row, '[name="desempeno_especifico[]"]'),
                    ejes: fieldValue(row, '[name="ejes_tematicos[]"]'),
                    evidenciaProceso: fieldValue(row, '[name="evidencia_proceso[]"]'),
                    evidenciaFinal: fieldValue(row, '[name="evidencia_final[]"]'),
                });
            });

            var rubricas = [];
            document.querySelectorAll('#tabla-rubricas tbody tr').forEach(function(row) {
                rubricas.push({
                    criterio: fieldValue(row, '[name="criterio[]"]'),
                    destacado: fieldValue(row, '[name="destacado[]"]'),
                    logrado: fieldValue(row, '[name="logrado[]"]'),
                    proceso: fieldValue(row, '[name="proceso[]"]'),
                    inicio: fieldValue(row, '[name="inicio[]"]'),
                });
            });

            return { fields: fields, enfoques: enfoques, unidades: unidades, rubricas: rubricas };
        }

        // Firma para comparar: sin diferencias de espacios, &nbsp; ni saltos de línea, que el
        // servidor (TrimStrings) y CKEditor cambian sin que el docente haya tocado nada.
        function signature(draft) {
            return JSON.stringify(draft, function(k, v) {
                return typeof v === 'string' ? v.replace(/&nbsp;| /g, ' ').replace(/\s+/g, ' ').trim() : v;
            });
        }

        // ── Almacenamiento ──────────────────────────────────────────────────────
        function leer() {
            try {
                var raw = localStorage.getItem(STORAGE_KEY);
                var r = raw ? JSON.parse(raw) : null;
                if (!r || !r.data) return null;
                if (Date.now() - (r.savedAt || 0) > DIAS_VIGENCIA * 864e5) { borrar(); return null; }
                return r;
            } catch (e) { return null; }
        }

        function escribir(extra) {
            try {
                var r = Object.assign({ v: 2, savedAt: Date.now(), data: collectDraft() }, extra || {});
                localStorage.setItem(STORAGE_KEY, JSON.stringify(r));
                return r;
            } catch (e) { return null; }
        }

        function borrar() { try { localStorage.removeItem(STORAGE_KEY); } catch (e) {} }

        function hora(ts) {
            try {
                var d = new Date(ts), hoy = new Date();
                var mismoDia = d.toDateString() === hoy.toDateString();
                return mismoDia
                    ? d.toLocaleTimeString('es-PE', { hour: '2-digit', minute: '2-digit' })
                    : d.toLocaleString('es-PE', { day: '2-digit', month: 'short', hour: '2-digit', minute: '2-digit' });
            } catch (e) { return ''; }
        }

        function mostrarEstado(texto, tipo) {
            if (!estado) return;
            estado.className = 'silabo-estado' + (tipo ? ' is-' + tipo : '');
            estado.querySelector('.silabo-estado-texto').textContent = texto;
        }

        // ── Restaurar ───────────────────────────────────────────────────────────
        function setStaticField(name, value) {
            var el = document.getElementById(name);
            if (el && el.classList.contains('ckeditor') && typeof CKEDITOR !== 'undefined' && CKEDITOR.instances[name]) {
                CKEDITOR.instances[name].setData(value);
                return;
            }
            var field = form.querySelector('[name="' + name + '"]');
            if (field && !(field.readOnly && !CON_NOMBRES_OCULTOS)) field.value = value;
        }

        function growRowsByClicking(btn, countRows, target) {
            var guard = 0;
            while (countRows() < target && guard < 500) { btn.click(); guard++; }
        }

        function restoreEnfoques(rows) {
            var tabla = document.getElementById('tablaEnfoques');
            if (!tabla || !rows.length) return;
            tabla.innerHTML = '';
            rows.forEach(function(r) {
                if (!r.id && !r.nombre) return;
                var tr = document.createElement('tr');
                tr.setAttribute('data-id', r.id);
                tr.innerHTML =
                    '<td>' +
                        '<input type="hidden" name="enfoques[]" value="' + escapeHtml(r.id) + '">' +
                        (CON_NOMBRES_OCULTOS
                            ? '<input type="hidden" name="nombres[]" value="' + escapeHtml(r.nombre) + '">' +
                              '<input type="hidden" name="descripciones[]" value="' + escapeHtml(r.descripcion) + '">'
                            : '') +
                        '<div>' +
                            '<input style="font-weight:800; border:none; background:transparent" type="text" class="form-control form-control-sm" value="' + escapeHtml(r.nombre) + '" readonly>' +
                            '<small style="display:block; color:gray;">' + escapeHtml(r.descripcion) + '</small>' +
                        '</div>' +
                    '</td>' +
                    '<td><textarea name="observables[]" class="form-control form-control-sm" rows="3"></textarea></td>' +
                    '<td><textarea name="concretas[]" class="form-control form-control-sm" rows="3"></textarea></td>' +
                    '<td class="text-center" id="acciones"><button type="button" class="btn btn-sm btn-danger eliminarEnfoque">Eliminar</button></td>';
                tr.querySelector('[name="observables[]"]').value = r.observables;
                tr.querySelector('[name="concretas[]"]').value = r.concretas;
                tabla.appendChild(tr);
            });
        }

        function restoreFilas(rows, btnId, contar, filasSelector, campos) {
            if (!rows.length) return;
            var btn = document.getElementById(btnId);
            if (!btn) return;
            growRowsByClicking(btn, contar, rows.length);
            var filas = document.querySelectorAll(filasSelector);
            rows.forEach(function(r, i) {
                var row = filas[i];
                if (!row) return;
                Object.keys(campos).forEach(function(clave) {
                    var el = row.querySelector('[name="' + campos[clave] + '[]"]');
                    if (el) el.value = r[clave] == null ? '' : r[clave];
                });
            });
        }

        function restoreDraft(draft) {
            Object.keys(draft.fields || {}).forEach(function(name) { setStaticField(name, draft.fields[name]); });
            restoreEnfoques(draft.enfoques || []);
            restoreFilas(draft.unidades || [], 'btn-agregar',
                function() { return document.querySelectorAll('#contenedor-tablas .unidad').length; },
                '#contenedor-tablas .unidad',
                { titulo: 'titulo_unidad', situacion: 'situacion_aprendizaje', duracion: 'duracion', desempeno: 'desempeno_especifico',
                  ejes: 'ejes_tematicos', evidenciaProceso: 'evidencia_proceso', evidenciaFinal: 'evidencia_final' });
            restoreFilas(draft.rubricas || [], 'btn-agregar-fila',
                function() { return document.querySelectorAll('#tabla-rubricas tbody tr').length; },
                '#tabla-rubricas tbody tr',
                { criterio: 'criterio', destacado: 'destacado', logrado: 'logrado', proceso: 'proceso', inicio: 'inicio' });
            form.dispatchEvent(new CustomEvent('silabo:restaurado'));
        }

        // ── Arranque: esperar a que CKEditor termine de cargar ─────────────────
        function cuandoEditoresListos(cb) {
            if (typeof CKEDITOR === 'undefined') { cb(); return; }
            var listo = false;
            function revisar() {
                if (listo) return;
                var instancias = Object.keys(CKEDITOR.instances).map(function(n) { return CKEDITOR.instances[n]; });
                var esperados = form.querySelectorAll('textarea.ckeditor').length;
                if (instancias.length >= esperados && instancias.every(function(i) { return i.status === 'ready'; })) {
                    listo = true;
                    cb();
                }
            }
            CKEDITOR.on('instanceReady', function() { setTimeout(revisar, 0); });
            setTimeout(revisar, 50);
            // Respaldo: si algún editor no carga (sin conexión al CDN), arrancar igual.
            setTimeout(function() { if (!listo) { listo = true; cb(); } }, 6000);
        }

        cuandoEditoresListos(function() {
            var firmaInicial = signature(collectDraft());
            var tocado = false;
            var guardadoEn = null;
            var espera = null;

            function guardar() {
                if (!tocado) return;
                var data = collectDraft();
                if (signature(data) === firmaInicial) { borrar(); mostrarEstado('Sin cambios pendientes', ''); return; }
                var r = escribir();
                if (r) { guardadoEn = r.savedAt; mostrarEstado('Borrador guardado en este equipo · ' + hora(r.savedAt), 'ok'); }
            }

            function programar() {
                tocado = true;
                mostrarEstado('Cambios sin guardar…', 'pendiente');
                clearTimeout(espera);
                espera = setTimeout(guardar, 1500);
            }

            // ── ¿Hay un borrador anterior? ──
            var previo = leer();
            if (previo) {
                if (signature(previo.data) === firmaInicial) {
                    // El formulario ya trae esos datos: el último guardado sí llegó al servidor.
                    borrar();
                } else if (aviso) {
                    aviso.querySelector('.js-aviso-texto').innerHTML = previo.enviadoAt
                        ? 'Tu último intento de guardar (<b>' + hora(previo.enviadoAt) + '</b>) no se completó — por ejemplo, porque la sesión expiró. El avance quedó a salvo en este equipo.'
                        : 'Tienes cambios de <b>' + hora(previo.savedAt) + '</b> que no se guardaron en el sistema. Quedaron a salvo en este equipo.';
                    aviso.hidden = false;
                    aviso.querySelector('.js-aviso-recuperar').addEventListener('click', function() {
                        restoreDraft(previo.data);
                        aviso.hidden = true;
                        tocado = true;
                        guardar();
                    });
                    aviso.querySelector('.js-aviso-descartar').addEventListener('click', function() {
                        borrar();
                        aviso.hidden = true;
                        mostrarEstado('Borrador descartado', '');
                    });
                }
            }

            // ── Autoguardado: al escribir (con espera) y cada 20 s de respaldo ──
            form.addEventListener('input', programar);
            form.addEventListener('change', programar);
            // Agregar/quitar unidades, criterios o enfoques también cambia el contenido.
            form.addEventListener('click', function(e) {
                if (e.target.closest('#btn-agregar, #btn-agregar-fila, #agregarEnfoque, .btn-eliminar, .btn-eliminar-fila, .eliminarEnfoque')) {
                    setTimeout(programar, 0);
                }
            });
            if (typeof CKEDITOR !== 'undefined') {
                Object.keys(CKEDITOR.instances).forEach(function(n) { CKEDITOR.instances[n].on('change', programar); });
            }
            setInterval(guardar, 20000);

            // Al enviar NO se borra: se marca como enviado. Si el servidor lo guarda, la próxima
            // carga del formulario trae estos mismos datos y el borrador se descarta solo.
            form.addEventListener('submit', function() {
                tocado = true;
                escribir({ enviadoAt: Date.now() });
                mostrarEstado('Guardando en el sistema…', 'pendiente');
            });

            var hayAviso = !!(aviso && !aviso.hidden);
            mostrarEstado(hayAviso ? 'Hay un borrador para recuperar' : 'Sin cambios pendientes', hayAviso ? 'pendiente' : '');
        });
    });
</script>
