{{-- Modal para escribir un correo a uno, varios o todos los alumnos de la lista visible.
     Espera: $remitenteNombre, $remitenteCorreo. Cada lista (.pk-al-panel) lleva en
     data-accion la URL a la que se envía (la del curso o la del ciclo). --}}
<div class="modal fade pk-al" id="pkAlModalMensaje" tabindex="-1" role="dialog" aria-labelledby="pkAlModalTitulo" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg" role="document">
        <form class="modal-content pk-al-modal" method="POST" id="pkAlFormMensaje">
            @csrf
            <div class="modal-header">
                <div>
                    <h5 class="modal-title" id="pkAlModalTitulo"><i class="fas fa-paper-plane mr-2"></i>Escribir a alumnos</h5>
                    <small class="pk-al-modal-para">Para: <strong id="pkAlPara"></strong></small>
                </div>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
            </div>
            <div class="modal-body">
                <div id="pkAlDestinatarios"></div>
                <div class="form-group">
                    <label for="pkAlAsunto" class="pk-al-label">Asunto</label>
                    <input type="text" class="form-control" id="pkAlAsunto" name="asunto" maxlength="150" required
                        value="{{ old('asunto') }}" placeholder="Ej.: Recordatorio de entrega del trabajo final">
                </div>
                <div class="form-group mb-2">
                    <label for="pkAlMensaje" class="pk-al-label">Mensaje</label>
                    <textarea class="form-control" id="pkAlMensaje" name="mensaje" rows="7" maxlength="5000" required
                        placeholder="Escribe aquí tu mensaje…">{{ old('mensaje') }}</textarea>
                    <div class="text-right"><small class="pk-al-contador"><span id="pkAlContador">0</span>/5000</small></div>
                </div>
                <div class="pk-al-nota">
                    <i class="fas fa-info-circle"></i>
                    <div>
                        Se envía desde el correo del sistema con tu nombre como remitente
                        (<strong>{{ $remitenteNombre }}</strong>).
                        @if ($remitenteCorreo)
                            Si el alumno responde, la respuesta llega a <strong>{{ $remitenteCorreo }}</strong>.
                        @endif
                        Cuando son varios alumnos, cada uno lo recibe sin ver los correos de los
                        demás{{ $remitenteCorreo ? ' y a ti te llega una copia' : '' }}.
                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary btn-sm" data-dismiss="modal">Cancelar</button>
                <button type="submit" class="btn btn-primary btn-sm" id="pkAlEnviar">
                    <i class="fas fa-paper-plane mr-1"></i>Enviar
                </button>
            </div>
        </form>
    </div>
</div>

{{-- Foto ampliada --}}
<div id="pkAlFotoModal" class="docente-photo-modal-overlay" role="dialog" aria-modal="true" onclick="pkAlCerrarFoto()">
    <div class="docente-photo-modal-inner" onclick="event.stopPropagation();">
        <button type="button" class="docente-photo-modal-close" onclick="pkAlCerrarFoto()" aria-label="Cerrar">&times;</button>
        <img id="pkAlFotoImg" src="" alt="" oncontextmenu="return false;">
        <p id="pkAlFotoNombre" class="text-white text-center mt-2 mb-0 font-weight-bold" style="font-size:.9rem;text-shadow:0 1px 3px rgba(0,0,0,.6);"></p>
    </div>
</div>

<style>
    .pk-al-modal { border: 0; border-radius: .65rem; background: var(--al-superficie); color: var(--al-texto); }
    .pk-al-modal .modal-header, .pk-al-modal .modal-footer { border-color: var(--al-borde); }
    .pk-al-modal .modal-title { font-size: 1rem; font-weight: 700; color: var(--al-texto); }
    .pk-al-modal .modal-title i { color: var(--al-acento); }
    .pk-al-modal .close { color: var(--al-suave); }
    .pk-al-modal-para { color: var(--al-suave); }
    .pk-al-modal-para strong { color: var(--al-texto); }
    .pk-al-label { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: var(--al-suave); }
    .pk-al-contador { color: var(--al-suave); }
    .pk-al-nota {
        display: flex; gap: .6rem; align-items: flex-start;
        font-size: .78rem; line-height: 1.45; color: var(--al-suave);
        background: var(--al-acento-fondo); border-radius: .5rem; padding: .65rem .8rem;
    }
    .pk-al-nota i { color: var(--al-acento); margin-top: .15rem; }
    .pk-al .min-w-0 { min-width: 0; }
</style>

<script>
    (function () {
        var modal = document.getElementById('pkAlModalMensaje');
        // El modal vive dentro del contenido; Bootstrap lo necesita al final del body para el backdrop.
        document.addEventListener('DOMContentLoaded', function () { document.body.appendChild(modal); });

        var form = document.getElementById('pkAlFormMensaje');
        var contenedor = document.getElementById('pkAlDestinatarios');
        var para = document.getElementById('pkAlPara');
        var texto = document.getElementById('pkAlMensaje');
        var contador = document.getElementById('pkAlContador');

        function abrir(accion, claves, etiqueta) {
            form.action = accion;
            contenedor.innerHTML = '';
            claves.forEach(function (clave) {
                var input = document.createElement('input');
                input.type = 'hidden';
                input.name = 'destinatarios[]';
                input.value = clave;
                contenedor.appendChild(input);
            });
            para.textContent = etiqueta;
            window.jQuery(modal).modal('show');
            setTimeout(function () { document.getElementById('pkAlAsunto').focus(); }, 350);
        }

        function panelActivo() {
            return document.querySelector('.pk-al-panel:not(.d-none)');
        }

        // Botón "Correo" de una fila.
        window.pkAlEscribirA = function (clave, nombre) {
            var panel = panelActivo();
            if (panel) abrir(panel.dataset.accion, [clave], nombre);
        };

        // Botón de la barra: los elegidos con el check o, si no hay ninguno, todos los que tienen correo.
        window.pkAlEscribirLista = function (boton) {
            var panel = boton.closest('.pk-al-panel');
            var elegidos = panel.querySelectorAll('.pk-al-check:checked');
            var lista = elegidos.length ? elegidos : panel.querySelectorAll('.pk-al-check:not(:disabled)');
            var claves = Array.prototype.map.call(lista, function (c) { return c.value; });
            if (!claves.length) return;

            var etiqueta = elegidos.length === 1
                ? elegidos[0].dataset.nombre
                : (elegidos.length ? claves.length + ' alumnos elegidos' : 'Todos los alumnos (' + claves.length + ')') + ' — ' + panel.dataset.titulo;
            abrir(panel.dataset.accion, claves, etiqueta);
        };

        window.pkAlActualizarSeleccion = function (panel) {
            var n = panel.querySelectorAll('.pk-al-check:checked').length;
            var boton = panel.querySelector('.pk-al-btn-lista');
            if (boton) {
                boton.innerHTML = '<i class="fas fa-paper-plane mr-1"></i>' + (n ? 'Escribir a ' + n + (n === 1 ? ' elegido' : ' elegidos') : 'Escribir a todos');
            }
            var todos = panel.querySelector('.pk-al-check-todos');
            var habilitados = panel.querySelectorAll('.pk-al-check:not(:disabled)').length;
            if (todos) {
                todos.checked = n > 0 && n === habilitados;
                todos.indeterminate = n > 0 && n < habilitados;
            }
            panel.querySelectorAll('.pk-al-check').forEach(function (c) {
                c.closest('.pk-al-fila').classList.toggle('is-elegido', c.checked);
            });
        };

        document.addEventListener('change', function (e) {
            var panel = e.target.closest('.pk-al-panel');
            if (!panel) return;
            if (e.target.classList.contains('pk-al-check-todos')) {
                panel.querySelectorAll('.pk-al-item:not(.d-none) .pk-al-check:not(:disabled)').forEach(function (c) { c.checked = e.target.checked; });
            }
            if (e.target.classList.contains('pk-al-check') || e.target.classList.contains('pk-al-check-todos')) {
                window.pkAlActualizarSeleccion(panel);
            }
        });

        // Búsqueda por nombre, correo o celular.
        document.addEventListener('input', function (e) {
            if (!e.target.classList.contains('pk-al-buscar-input')) return;
            var panel = e.target.closest('.pk-al-panel');
            var q = e.target.value.trim().toLowerCase();
            var visibles = 0;
            panel.querySelectorAll('.pk-al-item').forEach(function (item) {
                var ok = !q || item.dataset.buscar.indexOf(q) !== -1;
                item.classList.toggle('d-none', !ok);
                if (ok) visibles++;
            });
            var vacio = panel.querySelector('.pk-al-sin-coincidencias');
            if (vacio) vacio.classList.toggle('d-none', visibles > 0);
        });

        texto.addEventListener('input', function () { contador.textContent = texto.value.length; });
        contador.textContent = texto.value.length;

        form.addEventListener('submit', function () {
            var boton = document.getElementById('pkAlEnviar');
            boton.disabled = true;
            boton.innerHTML = '<span class="spinner-border spinner-border-sm mr-1"></span>Enviando…';
        });

        window.pkAlFoto = function (src, nombre) {
            document.getElementById('pkAlFotoImg').src = src;
            document.getElementById('pkAlFotoNombre').textContent = nombre || '';
            document.getElementById('pkAlFotoModal').classList.add('is-open');
        };
        window.pkAlCerrarFoto = function () {
            document.getElementById('pkAlFotoModal').classList.remove('is-open');
        };
        document.addEventListener('keydown', function (e) { if (e.key === 'Escape') window.pkAlCerrarFoto(); });
    })();
</script>
