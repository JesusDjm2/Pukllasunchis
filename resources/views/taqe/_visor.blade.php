{{--
    Visor emergente de archivos Taq'e (imagen, PDF, video, audio; Word: abrir/descargar), común a
    todas las vistas. Cada enlace "ver" lleva class="js-tq-previa" + data-tipo, data-nombre y
    data-descargar; sin JS el enlace abre la pestaña nueva. Requiere taqe/_estilos.
--}}
@once
    <div class="modal fade al-ui" id="tqPrevia" tabindex="-1" role="dialog" aria-labelledby="tqPreviaTitulo" aria-hidden="true">
        <div class="modal-dialog modal-xl modal-dialog-centered" role="document">
            <div class="modal-content tq-modal">
                <div class="modal-header">
                    <div style="min-width:0;">
                        <h5 class="modal-title text-truncate" id="tqPreviaTitulo"></h5>
                        <small class="tq-previa-tipo"></small>
                    </div>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
                </div>
                <div class="modal-body tq-previa-cuerpo" id="tqPreviaCuerpo"></div>
                <div class="modal-footer">
                    <a href="#" class="al-btn al-btn-borde al-btn-sm" id="tqPreviaDescargar"><i class="fas fa-download"></i> Descargar</a>
                    <a href="#" target="_blank" rel="noopener" class="al-btn al-btn-petroleo al-btn-sm" id="tqPreviaAbrir"><i class="fas fa-external-link-alt"></i> Abrir en nueva ventana</a>
                </div>
            </div>
        </div>
    </div>
<script>
    // ── Vista previa en popup: imagen, PDF, video o audio; Word y otros, solo abrir/descargar ──
    (function () {
        var NOMBRES = { imagen: 'Imagen', pdf: 'PDF', word: 'Documento Word', audio: 'Audio', video: 'Video', enlace: 'Enlace externo', otro: 'Archivo' };

        function escapar(s) {
            return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
        }

        // YouTube se puede ver dentro del visor; el resto de enlaces se abre en otra pestaña.
        function idYoutube(url) {
            var m = String(url).match(/(?:youtube\.com\/(?:watch\?(?:.*&)?v=|shorts\/|embed\/)|youtu\.be\/)([\w-]{11})/);
            return m ? m[1] : null;
        }

        function aviso(icono, titulo, texto) {
            return '<div class="tq-previa-aviso"><i class="fas ' + icono + '"></i><b>' + titulo + '</b>' + texto + '</div>';
        }

        function contenido(tipo, url) {
            if (tipo === 'imagen') return '<img src="' + url + '" alt="">';
            if (tipo === 'pdf') return '<iframe src="' + url + '#view=FitH" title="Vista previa del PDF"></iframe>';
            if (tipo === 'video') {
                return '<video src="' + url + '" controls playsinline preload="metadata"></video>';
            }
            if (tipo === 'audio') return '<audio src="' + url + '" controls preload="metadata"></audio>';
            if (tipo === 'enlace') {
                var yt = idYoutube(url);
                if (yt) return '<iframe src="https://www.youtube-nocookie.com/embed/' + yt + '" title="Video de YouTube" allow="accelerometer; encrypted-media; picture-in-picture" allowfullscreen></iframe>';
                var host = url; try { host = new URL(url).hostname.replace(/^www\./, ''); } catch (e) {}
                return aviso('fa-link', escapar(host), '<span style="word-break:break-all;">' + escapar(url) + '</span><br>Este enlace se abre en el sitio donde está publicado.');
            }
            return aviso('fa-file-word', 'Vista previa no disponible', 'Los documentos de Word no se pueden mostrar aquí. Ábrelo en una nueva ventana o descárgalo.');
        }

        document.addEventListener('click', function (e) {
            var enlace = e.target.closest('.js-tq-previa');
            if (!enlace || !window.jQuery) return; // sin jQuery/Bootstrap: el enlace abre la pestaña nueva como siempre
            e.preventDefault();

            var tipo = enlace.dataset.tipo || 'otro';
            var url = enlace.getAttribute('href');
            document.getElementById('tqPreviaTitulo').textContent = enlace.dataset.nombre || 'Archivo';
            document.querySelector('#tqPrevia .tq-previa-tipo').textContent = NOMBRES[tipo] || 'Archivo';
            document.getElementById('tqPreviaAbrir').href = url;
            var descargar = document.getElementById('tqPreviaDescargar');
            descargar.href = enlace.dataset.descargar || url;
            descargar.style.display = tipo === 'enlace' ? 'none' : ''; // un enlace no se descarga
            document.getElementById('tqPreviaAbrir').innerHTML = tipo === 'enlace'
                ? '<i class="fas fa-external-link-alt"></i> Abrir enlace'
                : '<i class="fas fa-external-link-alt"></i> Abrir en nueva ventana';

            var cuerpo = document.getElementById('tqPreviaCuerpo');
            cuerpo.innerHTML = contenido(tipo, url);
            var medio = cuerpo.querySelector('video, audio');
            if (medio) {
                // Formatos que el navegador no reproduce (p. ej. .avi o algunos .mov).
                medio.addEventListener('error', function () {
                    cuerpo.innerHTML = aviso('fa-video-slash', 'Tu navegador no puede reproducir este formato',
                        'Ábrelo en una nueva ventana o descárgalo para verlo con tu reproductor.');
                });
            }
            jQuery('#tqPrevia').modal('show');
        });

        // Al cerrar se vacía: detiene el video/audio y libera la conexión.
        document.addEventListener('DOMContentLoaded', function () {
            if (window.jQuery) jQuery('#tqPrevia').on('hidden.bs.modal', function () { document.getElementById('tqPreviaCuerpo').innerHTML = ''; });
        });
    })();
</script>
@endonce
