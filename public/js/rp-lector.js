/*
 * Ricardo Palma — pantalla de corrección del acta escaneada (lecturas/editar)
 * y aviso de espera al subir el PDF (importar/index).
 *
 * Las comprobaciones repiten las del servidor (ActaFisicaRpImport::desdeActa):
 * puntaje divisible entre créditos, nota 0–20, suma = puntaje total del acta,
 * total ÷ créditos = promedio del acta (truncado a 2 decimales, o redondeado).
 */
(function () {
    'use strict';

    // ── Importar: aviso mientras se sube el PDF ──
    document.querySelectorAll('.js-rp-subir-escaneo').forEach(function (form) {
        form.addEventListener('submit', function () {
            var btn = form.querySelector('button[type=submit]');
            if (btn) {
                btn.disabled = true;
                btn.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Subiendo PDF…';
            }
        });
    });

    var raiz = document.getElementById('rpLector');
    if (!raiz) return;

    // ── Lectura con IA vía SSE (Server-Sent Events) sin timeout ──
    var progresoIa = document.getElementById('rpLectorProgresoIa');
    var formLector = document.getElementById('rpLectorFormulario');

    if (raiz.dataset.necesitaLectura === '1' && raiz.dataset.stream) {
        iniciarStreamLectura(raiz.dataset.stream);
    }

    function iniciarStreamLectura(url) {
        var elTitulo = document.getElementById('rpLectorProgresoTitulo');
        var elFase = document.getElementById('rpLectorProgresoFase');
        var elBarra = document.getElementById('rpLectorProgresoBarra');
        var elTiempo = document.getElementById('rpLectorProgresoTiempo');
        var elAcciones = document.getElementById('rpLectorAccionesProgreso');

        var segundosTranscurridos = 0;
        var timer = setInterval(function () {
            segundosTranscurridos++;
            var m = Math.floor(segundosTranscurridos / 60);
            var s = segundosTranscurridos % 60;
            if (elTiempo) {
                elTiempo.textContent = (m < 10 ? '0' : '') + m + ':' + (s < 10 ? '0' : '') + s;
            }
            if (elBarra) {
                var pct = Math.min(92, 15 + Math.round(segundosTranscurridos * 1.5));
                elBarra.style.width = pct + '%';
            }
        }, 1000);

        var eventSource = new EventSource(url);

        function finalizarStream() {
            if (timer) clearInterval(timer);
            if (eventSource) {
                eventSource.close();
                eventSource = null;
            }
        }

        var btnCancelar = raiz.querySelector('.js-rp-cancelar-ia');
        if (btnCancelar) {
            btnCancelar.addEventListener('click', function () {
                finalizarStream();
                if (progresoIa) progresoIa.style.display = 'none';
                if (formLector) formLector.style.display = '';
            });
        }

        eventSource.addEventListener('iniciado', function (e) {
            try {
                var d = JSON.parse(e.data);
                if (elFase) elFase.textContent = d.mensaje || 'Conectando con la IA...';
            } catch (err) {}
        });

        eventSource.addEventListener('progreso', function (e) {
            try {
                var d = JSON.parse(e.data);
                if (elFase && d.fase) elFase.textContent = d.fase;
            } catch (err) {}
        });

        eventSource.addEventListener('completado', function (e) {
            finalizarStream();
            if (elBarra) elBarra.style.width = '100%';
            if (elTitulo) elTitulo.innerHTML = '<span class="text-success"><i class="fas fa-check-circle"></i> ¡Lectura completada con éxito!</span>';
            if (elFase) elFase.textContent = 'Cargando datos en la tabla...';
            setTimeout(function () {
                window.location.reload();
            }, 600);
        });

        eventSource.addEventListener('error', function (e) {
            var msg = 'Ocurrió un inconveniente al comunicarse con la IA.';
            try {
                if (e.data) {
                    var d = JSON.parse(e.data);
                    if (d.mensaje) msg = d.mensaje;
                }
            } catch (err) {}

            finalizarStream();
            if (elBarra) {
                elBarra.className = 'progress-bar bg-danger';
                elBarra.style.width = '100%';
            }
            if (elTitulo) elTitulo.innerHTML = '<span class="text-danger"><i class="fas fa-exclamation-triangle"></i> No se pudo completar la lectura automática</span>';
            if (elFase) elFase.innerHTML = '<span class="text-danger">' + msg + '</span>';

            if (elAcciones) {
                elAcciones.innerHTML = '<button type="button" class="al-btn al-btn-sm al-btn-oro mr-2 js-rp-reintentar-stream"><i class="fas fa-redo"></i> Reintentar con IA</button>' +
                    '<button type="button" class="al-btn al-btn-sm al-btn-claro js-rp-cancelar-ia"><i class="fas fa-pen"></i> Llenar manualmente</button>';

                var btnReintentar = elAcciones.querySelector('.js-rp-reintentar-stream');
                if (btnReintentar) {
                    btnReintentar.addEventListener('click', function () {
                        var formReleer = document.querySelector('.js-rp-releer');
                        if (formReleer) {
                            formReleer.submit();
                        } else {
                            window.location.reload();
                        }
                    });
                }
                var btnManual = elAcciones.querySelector('.js-rp-cancelar-ia');
                if (btnManual) {
                    btnManual.addEventListener('click', function () {
                        if (progresoIa) progresoIa.style.display = 'none';
                        if (formLector) formLector.style.display = '';
                    });
                }
            }
        });
    }

    var datos = JSON.parse(document.getElementById('rpLectorDatos').textContent);
    datos.encabezado = datos.encabezado || {};
    datos.asignaturas = datos.asignaturas || [];
    datos.alumnos = datos.alumnos || [];
    datos.alumnos.forEach(function (a) { a.dudas = a.dudas || []; a.valores = a.valores || []; });

    var thead = raiz.querySelector('[data-cabecera]');
    var tbody = raiz.querySelector('[data-cuerpo]');
    var MAX_ASIG = 15;

    // ── Utilidades ──
    function txt(v) { return (v === null || v === undefined) ? '' : String(v).trim(); }
    function num(v) { v = txt(v).replace(',', '.'); return v !== '' && !isNaN(v) ? parseFloat(v) : null; }
    function esc(s) {
        return txt(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
    }
    function fmt2(n) { return n.toFixed(2).replace('.', ','); }
    function el(tag, clase, html) { var e = document.createElement(tag); if (clase) e.className = clase; if (html !== undefined) e.innerHTML = html; return e; }

    function enPuntaje() { return datos.en_puntaje !== false; }

    // ── Validación de una fila → { estado, celdas: {clave: 'mal'}, notas: [], mensajes: [] } ──
    function evaluar(al, idx) {
        var r = { estado: 'ok', celdas: {}, notas: [], mensajes: [], suma: null, prom: null, vacia: false };
        var nombre = txt(al.nombre);
        var hayAlgo = nombre || al.valores.some(function (v) { return txt(v) !== ''; });
        if (!hayAlgo) { r.vacia = true; r.estado = 'vacia'; return r; }

        function mal(clave, msg) { r.celdas[clave] = msg; r.mensajes.push(msg); }

        if (!nombre) mal('nombre', 'Falta el nombre.');
        else if (nombre.indexOf(',') < 0) mal('nombre', 'Separa apellidos y nombres con una coma (APELLIDOS,Nombres).');

        var suma = 0, conNota = 0;
        datos.asignaturas.forEach(function (asig, i) {
            var v = txt(al.valores[i]);
            r.notas[i] = null;
            if (v === '' || !txt(asig.nombre)) return;
            var n = num(v);
            if (n === null) { mal('v' + i, 'Casilla ' + (i + 1) + ': «' + v + '» no es un número.'); return; }
            var cred = parseInt(asig.creditos, 10);
            var nota = n;
            if (enPuntaje()) {
                if (!(cred >= 1)) { mal('v' + i, 'La asignatura ' + (i + 1) + ' no tiene créditos.'); return; }
                nota = n / cred;
                if (Math.abs(nota - Math.round(nota)) > 0.001) {
                    mal('v' + i, 'Casilla ' + (i + 1) + ': ' + v + ' no se divide exacto entre ' + cred + ' créditos.');
                    return;
                }
                nota = Math.round(nota);
                suma += n;
            } else {
                suma += n * (cred >= 1 ? cred : 0);
            }
            if (nota < 0 || nota > 20) { mal('v' + i, 'Casilla ' + (i + 1) + ': da nota ' + nota + ', fuera de 0 a 20.'); return; }
            r.notas[i] = nota;
            conNota++;
        });

        if (conNota) {
            r.suma = suma;
            var total = num(al.total_acta);
            if (txt(al.total_acta) !== '' && (total === null || Math.abs(total - suma) > 0.001)) {
                mal('total', 'Las casillas suman ' + suma + ' y el acta dice ' + txt(al.total_acta) + '.');
            }
            var div = parseInt(al.creditos_acta, 10) || sumaCreditos();
            if (div > 0) {
                var exacto = suma / div;
                r.prom = Math.floor(exacto * 100 + 1e-9) / 100;
                var p = num(al.promedio_acta);
                if (txt(al.promedio_acta) !== '' && !r.celdas.total &&
                    (p === null || (Math.abs(r.prom - p) > 0.0001 && Math.abs(Math.round(exacto * 100) / 100 - p) > 0.0001))) {
                    mal('promedio', suma + ' ÷ ' + div + ' = ' + fmt2(r.prom) + ', pero el acta dice ' + txt(al.promedio_acta) + '.');
                }
            }
        } else if (!txt(al.obs) && !Object.keys(r.celdas).length) {
            mal('obs', 'No tiene notas ni observación.');
        }

        if (r.mensajes.length) r.estado = 'mal';
        else if (al.dudas.length) r.estado = 'duda';
        return r;
    }

    function sumaCreditos() {
        return datos.asignaturas.reduce(function (s, a) { return s + (txt(a.nombre) ? (parseInt(a.creditos, 10) || 0) : 0); }, 0);
    }

    // ── Cabecera de la tabla ──
    function pintarCabecera() {
        var f = '<tr><th class="rp-lec-fija-1">N°</th><th class="rp-lec-fija-2">Matrícula</th>' +
            '<th class="rp-lec-fija-3">Apellidos y nombres</th>';
        datos.asignaturas.forEach(function (a, i) {
            f += '<th class="rp-lec-asig">' +
                '<div class="rp-lec-asig-n"><span>' + (i + 1) + '</span>' +
                ' <button type="button" class="rp-lec-x" data-quitar-asig="' + i + '" title="Quitar asignatura">×</button></div>' +
                '<textarea class="al-campo rp-lec-asig-nombre" rows="2" data-asig="' + i + '" data-campo="nombre" placeholder="Asignatura">' + esc(a.nombre) + '</textarea>' +
                '<div class="rp-lec-asig-cred"><input class="al-campo rp-lec-cred" data-asig="' + i + '" data-campo="creditos" value="' + esc(a.creditos) + '" inputmode="numeric" title="Créditos"> <span>crd</span></div>' +
                '</th>';
        });
        f += '<th class="rp-lec-th-calc">Suma</th>' +
            '<th class="rp-lec-th-acta">Total<br>del acta</th>' +
            '<th class="rp-lec-th-cred">Créd.</th>' +
            '<th class="rp-lec-th-calc">Promedio</th>' +
            '<th class="rp-lec-th-acta">Prom.<br>del acta</th>' +
            '<th class="rp-lec-th-obs">Observación</th>' +
            '<th class="rp-lec-th-acc"></th></tr>';
        thead.innerHTML = f;
    }

    // ── Cuerpo ──
    function pintarCuerpo() {
        tbody.innerHTML = '';
        datos.alumnos.forEach(function (al, i) { tbody.appendChild(filaHtml(al, i)); });
        actualizarTodo();
    }

    function campo(clave, valor, clase, extra) {
        return '<input class="al-campo ' + (clase || '') + '" data-clave="' + clave + '" value="' + esc(valor) + '" autocomplete="off"' + (extra || '') + '>';
    }

    function filaHtml(al, i) {
        var tr = el('tr');
        tr.dataset.i = i;
        var h = '<td class="rp-lec-fija-1"><span class="rp-lec-estado"></span><span class="rp-lec-num">' + (i + 1) + '</span></td>' +
            '<td class="rp-lec-fija-2" data-celda="matricula">' + campo('matricula', al.matricula, 'rp-lec-mat') + '</td>' +
            '<td class="rp-lec-fija-3" data-celda="nombre">' + campo('nombre', al.nombre, 'rp-lec-nombre') + '</td>';
        datos.asignaturas.forEach(function (a, j) {
            h += '<td data-celda="v' + j + '">' + campo('v' + j, al.valores[j], 'rp-lec-casilla', ' inputmode="numeric"') +
                '<small class="rp-lec-nota" data-nota="' + j + '"></small></td>';
        });
        h += '<td class="rp-lec-calc" data-suma></td>' +
            '<td data-celda="total">' + campo('total_acta', al.total_acta, 'rp-lec-casilla', ' inputmode="numeric"') + '</td>' +
            '<td data-celda="creditos">' + campo('creditos_acta', al.creditos_acta, 'rp-lec-casilla rp-lec-chica', ' inputmode="numeric"') + '</td>' +
            '<td class="rp-lec-calc" data-prom></td>' +
            '<td data-celda="promedio">' + campo('promedio_acta', al.promedio_acta, 'rp-lec-casilla rp-lec-prom') + '</td>' +
            '<td data-celda="obs">' + campo('obs', al.obs, 'rp-lec-obs') + '</td>' +
            '<td class="rp-lec-acciones">' +
            '<button type="button" class="rp-lec-btn" data-revisada title="Marcar la fila como revisada (quita las dudas de la IA)"><i class="fas fa-check-double"></i></button>' +
            '<button type="button" class="rp-lec-btn is-peligro" data-quitar-fila title="Quitar fila"><i class="fas fa-times"></i></button></td>';
        tr.innerHTML = h;
        return tr;
    }

    function actualizarFila(tr) {
        var i = +tr.dataset.i, al = datos.alumnos[i], r = evaluar(al, i);
        tr.className = 'is-' + r.estado;
        tr.querySelector('.rp-lec-estado').title = r.mensajes.join('\n') || (r.estado === 'duda' ? 'La IA no estaba segura de algunas casillas: revísalas con el escaneo.' : '');
        tr.querySelectorAll('[data-celda]').forEach(function (td) {
            var clave = td.dataset.celda;
            td.classList.toggle('is-mal', !!r.celdas[clave]);
            td.classList.toggle('is-duda', !r.celdas[clave] && al.dudas.indexOf(clave) >= 0);
            var input = td.querySelector('input');
            if (input) input.title = r.celdas[clave] || '';
        });
        tr.querySelectorAll('[data-nota]').forEach(function (s) {
            var n = r.notas[+s.dataset.nota];
            s.textContent = (n === null || n === undefined || !enPuntaje()) ? '' : n;
            s.classList.toggle('is-mal', n !== null && n < 11);
        });
        tr.querySelector('[data-suma]').textContent = r.suma === null ? '' : r.suma;
        var prom = tr.querySelector('[data-prom]');
        prom.textContent = r.prom === null ? '' : fmt2(r.prom);
        prom.classList.toggle('is-mal', r.prom !== null && r.prom < 11);
        return r;
    }

    function actualizarTodo() {
        var c = { ok: 0, mal: 0, duda: 0 };
        var mats = [];
        tbody.querySelectorAll('tr').forEach(function (tr) {
            var r = actualizarFila(tr);
            if (r.estado === 'ok') c.ok++;
            if (r.estado === 'mal') c.mal++;
            var al = datos.alumnos[+tr.dataset.i];
            c.duda += r.estado === 'mal' ? 0 : al.dudas.length;
            mats.push({ tr: tr, m: txt(al.matricula) });
        });
        // Matrícula correlativa: aviso suave si se salta un número (¿falta una fila?)
        mats.forEach(function (x, k) {
            var prev = k ? mats[k - 1].m : '';
            var salto = /^\d+$/.test(x.m) && /^\d+$/.test(prev) && parseInt(x.m, 10) !== parseInt(prev, 10) + 1;
            var td = x.tr.querySelector('[data-celda="matricula"]');
            td.classList.toggle('is-salto', salto);
            td.title = salto ? 'La matrícula no sigue a la anterior (' + prev + '): ¿falta o sobra una fila?' : '';
        });
        Object.keys(c).forEach(function (k) { raiz.querySelector('[data-cuenta="' + k + '"]').textContent = c[k]; });
        avisosCabecera();
        return c;
    }

    function avisosCabecera() {
        var avisos = [];
        var e = datos.encabezado;
        if (!/^\d{4}\s*[-\/ ]?\s*(II|I|2|1)$/i.test(txt(e.periodo))) avisos.push(['mal', 'El año académico debe tener la forma 1993-I o 1993-II.']);
        if (!txt(e.programa)) avisos.push(['mal', 'Elige la especialidad.']);
        if (!txt(e.semestre)) avisos.push(['mal', 'Falta el semestre.']);
        datos.asignaturas.forEach(function (a, i) {
            if (txt(a.nombre) && !(parseInt(a.creditos, 10) >= 1)) avisos.push(['mal', 'La asignatura ' + (i + 1) + ' no tiene créditos.']);
        });
        // Créditos: la suma debe dar el «N° de créditos matriculados» que más se repite
        var frec = {};
        datos.alumnos.forEach(function (a) { var c = txt(a.creditos_acta); if (/^\d+$/.test(c)) frec[c] = (frec[c] || 0) + 1; });
        var masComun = Object.keys(frec).sort(function (a, b) { return frec[b] - frec[a]; })[0];
        var suma = sumaCreditos();
        if (enPuntaje() && masComun && +masComun !== suma) {
            avisos.push(['warn', 'Los créditos de las asignaturas suman ' + suma + ', pero el acta dice ' + masComun +
                ' créditos matriculados. Los créditos no están impresos: el sistema los dedujo de los puntajes; corrígelos si hace falta.']);
        } else if (enPuntaje() && masComun) {
            avisos.push(['ok', 'Los créditos suman ' + suma + ', igual que en el acta.']);
        }
        raiz.querySelector('[data-avisos-cab]').innerHTML = avisos.map(function (a) {
            var ic = { mal: 'times-circle', warn: 'exclamation-triangle', ok: 'check-circle' }[a[0]];
            return '<div class="rp-lec-aviso is-' + a[0] + '"><i class="fas fa-' + ic + '"></i> ' + esc(a[1]) + '</div>';
        }).join('');
        return avisos.filter(function (a) { return a[0] === 'mal'; }).length;
    }

    // ── Encabezado ──
    raiz.querySelectorAll('[data-enc]').forEach(function (inp) {
        inp.value = txt(datos.encabezado[inp.dataset.enc]);
        inp.addEventListener('input', function () { datos.encabezado[inp.dataset.enc] = inp.value; cambio(); avisosCabecera(); });
        inp.addEventListener('change', function () { datos.encabezado[inp.dataset.enc] = inp.value; cambio(); avisosCabecera(); });
    });
    var selPuntaje = raiz.querySelector('[data-en-puntaje]');
    selPuntaje.value = enPuntaje() ? '1' : '0';
    selPuntaje.addEventListener('change', function () { datos.en_puntaje = selPuntaje.value === '1'; cambio(); actualizarTodo(); });
    if (txt(datos.encabezado.texto_especialidad)) {
        raiz.querySelector('[data-texto-especialidad]').textContent = 'En el acta dice: «' + datos.encabezado.texto_especialidad + '». Revisa que coincida con el escaneo.';
    }

    // ── Edición en la tabla ──
    thead.addEventListener('input', function (ev) {
        var t = ev.target;
        if (t.dataset.asig === undefined) return;
        datos.asignaturas[+t.dataset.asig][t.dataset.campo] = t.value;
        cambio();
        actualizarTodo();
    });
    thead.addEventListener('click', function (ev) {
        var b = ev.target.closest('[data-quitar-asig]');
        if (!b) return;
        var i = +b.dataset.quitarAsig;
        confirmar('¿Quitar la asignatura ' + (i + 1) + ' y sus casillas?', function () {
            datos.asignaturas.splice(i, 1);
            datos.alumnos.forEach(function (a) {
                a.valores.splice(i, 1);
                a.dudas = a.dudas.filter(function (d) { return d !== 'v' + i; }).map(function (d) {
                    var m = /^v(\d+)$/.exec(d);
                    return m && +m[1] > i ? 'v' + (+m[1] - 1) : d;
                });
            });
            cambio(); pintarCabecera(); pintarCuerpo();
        });
    });

    tbody.addEventListener('input', function (ev) {
        var t = ev.target, tr = t.closest('tr');
        if (!t.dataset.clave || !tr) return;
        var al = datos.alumnos[+tr.dataset.i], clave = t.dataset.clave;
        var m = /^v(\d+)$/.exec(clave);
        if (m) al.valores[+m[1]] = t.value; else al[clave] = t.value;
        // Al tocar una casilla dudosa se da por revisada
        var duda = { total_acta: 'total', promedio_acta: 'promedio' }[clave] || clave;
        al.dudas = al.dudas.filter(function (d) { return d !== duda; });
        cambio();
        actualizarTodo();
    });
    tbody.addEventListener('click', function (ev) {
        var tr = ev.target.closest('tr');
        if (!tr) return;
        var i = +tr.dataset.i;
        if (ev.target.closest('[data-revisada]')) {
            datos.alumnos[i].dudas = [];
            cambio(); actualizarTodo();
        } else if (ev.target.closest('[data-quitar-fila]')) {
            confirmar('¿Quitar a ' + (txt(datos.alumnos[i].nombre) || 'esta fila') + ' del acta?', function () {
                datos.alumnos.splice(i, 1);
                cambio(); pintarCuerpo();
            });
        }
    });
    // Enter / flechas arriba-abajo: misma casilla del alumno siguiente o anterior
    tbody.addEventListener('keydown', function (ev) {
        var t = ev.target;
        if (!t.dataset.clave) return;
        var dir = (ev.key === 'Enter' || ev.key === 'ArrowDown') ? 1 : ev.key === 'ArrowUp' ? -1 : 0;
        if (!dir) return;
        ev.preventDefault();
        var tr = t.closest('tr'), dest = dir > 0 ? tr.nextElementSibling : tr.previousElementSibling;
        var inp = dest && dest.querySelector('[data-clave="' + t.dataset.clave + '"]');
        if (inp) { inp.focus(); inp.select(); }
    });

    raiz.querySelector('.js-rp-agregar-alumno').addEventListener('click', function () {
        datos.alumnos.push({ matricula: '', nombre: '', valores: datos.asignaturas.map(function () { return ''; }), total_acta: '', creditos_acta: '', promedio_acta: '', obs: '', dudas: [] });
        cambio(); pintarCuerpo();
        var ult = tbody.lastElementChild && tbody.lastElementChild.querySelector('[data-clave="matricula"]');
        if (ult) ult.focus();
    });
    raiz.querySelector('.js-rp-agregar-asignatura').addEventListener('click', function () {
        if (datos.asignaturas.length >= MAX_ASIG) { aviso('El acta tiene como máximo ' + MAX_ASIG + ' asignaturas.'); return; }
        datos.asignaturas.push({ nombre: '', creditos: '' });
        datos.alumnos.forEach(function (a) { a.valores.push(''); });
        cambio(); pintarCabecera(); pintarCuerpo();
    });

    // Ir al siguiente problema
    raiz.querySelectorAll('.js-rp-ir').forEach(function (b) {
        var ultimo = -1;
        b.addEventListener('click', function () {
            var sel = b.dataset.ir === 'mal' ? 'td.is-mal' : 'td.is-duda';
            var celdas = Array.prototype.slice.call(tbody.querySelectorAll(sel));
            if (!celdas.length) return;
            ultimo = (ultimo + 1) % celdas.length;
            var inp = celdas[ultimo].querySelector('input');
            celdas[ultimo].scrollIntoView({ block: 'center', inline: 'center', behavior: 'smooth' });
            if (inp) setTimeout(function () { inp.focus(); inp.select(); }, 250);
        });
    });

    // Mostrar / ocultar el escaneo
    raiz.querySelector('.js-rp-ocultar-pdf').addEventListener('click', function () { escaneo(false); });
    raiz.querySelector('.js-rp-mostrar-pdf').addEventListener('click', function () { escaneo(true); });
    function escaneo(ver) {
        raiz.classList.toggle('sin-pdf', !ver);
        raiz.querySelector('.js-rp-mostrar-pdf').hidden = ver;
        try { localStorage.setItem('rpLectorSinPdf', ver ? '0' : '1'); } catch (e) { /* sin almacenamiento */ }
    }
    try { if (localStorage.getItem('rpLectorSinPdf') === '1') escaneo(false); } catch (e) { /* sin almacenamiento */ }

    // ── Autoguardado del borrador ──
    var estado = raiz.querySelector('[data-estado-guardado]');
    var temporizador = null, pendiente = false;
    function cambio() {
        pendiente = true;
        estado.textContent = 'Cambios sin guardar…';
        clearTimeout(temporizador);
        temporizador = setTimeout(guardar, 1500);
    }
    function guardar() {
        var cuerpo = new FormData();
        cuerpo.append('_method', 'PUT');
        cuerpo.append('datos', JSON.stringify(datos));
        pendiente = false;
        return fetch(raiz.dataset.guardar, {
            method: 'POST',
            headers: { 'X-CSRF-TOKEN': raiz.dataset.csrf, 'Accept': 'application/json' },
            body: cuerpo,
            credentials: 'same-origin'
        }).then(function (r) { return r.json(); }).then(function (j) {
            estado.textContent = j.ok ? 'Borrador guardado ' + j.hora : 'No se pudo guardar el borrador';
        }).catch(function () {
            pendiente = true;
            estado.textContent = 'Sin conexión: el borrador no se guardó';
        });
    }
    window.addEventListener('beforeunload', function (ev) {
        if (pendiente && !enviando) { ev.preventDefault(); ev.returnValue = ''; }
    });

    // ── Cargar el acta ──
    var enviando = false;
    var formCargar = raiz.querySelector('.js-rp-cargar');
    formCargar.addEventListener('submit', function (ev) {
        ev.preventDefault();
        var c = actualizarTodo();
        var malCab = avisosCabecera();
        if (malCab || c.mal) {
            aviso((malCab ? 'Completa el encabezado del acta. ' : '') +
                (c.mal ? 'Hay ' + c.mal + ' fila(s) que no cuadran (en rojo). Pasa el cursor sobre el ícono de la fila para ver el motivo.' : ''),
                'Todavía no se puede cargar');
            return;
        }
        var enviar = function () {
            enviando = true;
            clearTimeout(temporizador);
            formCargar.querySelector('[name=datos]').value = JSON.stringify(datos);
            formCargar.querySelector('button').disabled = true;
            formCargar.submit();
        };
        if (c.duda) {
            confirmar('Quedan ' + c.duda + ' casilla(s) en amarillo que la IA leyó con duda y nadie revisó. ¿Cargar el acta igual?', enviar, 'Sí, cargar');
        } else {
            confirmar('Se cargarán ' + c.ok + ' alumno(s) de esta acta. Si el acta ya existe, sus notas se actualizan.', enviar, 'Sí, cargar');
        }
    });

    document.querySelectorAll('.js-rp-releer').forEach(function (f) {
        f.addEventListener('submit', function (ev) {
            ev.preventDefault();
            confirmar('Se volverá a leer el PDF con la IA y se perderán las correcciones hechas en esta pantalla.', function () {
                enviando = true;
                f.querySelector('button').disabled = true;
                f.querySelector('button').innerHTML = '<i class="fas fa-spinner fa-spin"></i> Leyendo…';
                f.submit();
            }, 'Sí, volver a leer');
        });
    });
    document.querySelectorAll('.js-rp-descartar').forEach(function (f) {
        f.addEventListener('submit', function (ev) {
            ev.preventDefault();
            confirmar('Se borrarán el PDF subido y todo lo corregido. No se toca ninguna acta ya cargada.', function () { enviando = true; f.submit(); }, 'Sí, descartar', true);
        });
    });

    // ── Alertas del sistema (PkAlert) con respaldo nativo ──
    function confirmar(texto, si, boton, peligro) {
        if (window.PkAlert) {
            var fn = peligro ? PkAlert.confirmarEliminar : PkAlert.confirmar;
            fn({ title: '¿Continuar?', text: texto, confirmButtonText: boton || 'Sí, continuar' }).then(function (ok) {
                if (ok) si();
            });
        } else if (window.confirm(texto)) {
            si();
        }
    }
    function aviso(texto, titulo) {
        if (window.PkAlert) PkAlert.aviso(texto, titulo); else window.alert(texto);
    }

    pintarCabecera();
    pintarCuerpo();
})();
