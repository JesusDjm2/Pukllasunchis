@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('titulo', 'Carnet estudiante — ' . ($alumno->apellidos ?? '') . ', ' . ($alumno->nombres ?? ''))
@section('contenido')
    <style>
        #carnet .carnet-colgador-guia-print {
            display: none;
        }

        @media print {
            #carnet .carnet-colgador-guia-print {
                display: block;
                position: absolute;
                top: 0.65em;
                left: 50%;
                transform: translateX(-50%);
                z-index: 20;
                width: 26px;
                height: 9px;
                box-sizing: border-box;
                border: 1.25pt dashed #1a1a1a;
                border-radius: 4px;
                background: transparent;
                -webkit-print-color-adjust: exact;
                print-color-adjust: exact;
            }
        }
    </style>
    <div class="container-fluid bg-white py-4">
        <div class="d-flex flex-wrap align-items-center justify-content-between mb-3">
            <div>
                <h4 class="text-primary mb-1">Carnets del estudiante</h4>
                <p class="small text-muted mb-0">Vista previa de carnet académico y carnet de biblioteca. La fotografía proviene del usuario vinculado al alumno.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <a href="{{ route('adminAlumnos', request()->only(['search', 'search_page', 'with_user', 'programa_id', 'ciclo_id'])) }}"
                    class="btn btn-outline-secondary btn-sm ml-1">Volver al listado</a>
            </div>
        </div>
        <div class="row">
            <div class="col-lg-6 mb-4">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0 text-primary">Carnet Estudiante</h6>
                        <button type="button" class="btn btn-primary btn-sm shadow-sm js-btn-descargar-carnet"
                            data-carnet-id="carnet-estudiante" data-download-name="carnet_estudiante_{{ $dni }}"
                            data-label="Carnet Estudiante">
                            <i class="fas fa-download fa-sm mr-1"></i> Descargar PNG
                        </button>
                    </div>
                    @include('alumnos.partials.carnet-estudiante-inner', [
                        'carnetId' => 'carnet-estudiante',
                        'cabeceraTitulo' => 'ID Estudiante',
                        'outerBackground' => '#e8dcc8',
                        'primaryBackground' => '#b96328',
                        'labelColor' => '#c41e3a',
                    ])
                </div>
            </div>
            <div class="col-lg-6 mb-4">
                <div class="border rounded p-3 bg-light h-100">
                    <div class="d-flex align-items-center justify-content-between mb-2">
                        <h6 class="mb-0 text-info">Carnet Biblioteca</h6>
                        <button type="button" class="btn btn-info btn-sm shadow-sm js-btn-descargar-carnet"
                            data-carnet-id="carnet-biblioteca" data-download-name="biblioteca_{{ $dni }}"
                            data-label="Carnet Biblioteca">
                            <i class="fas fa-download fa-sm mr-1"></i> Descargar PNG
                        </button>
                    </div>
                    @include('alumnos.partials.carnet-estudiante-inner', [
                        'carnetId' => 'carnet-biblioteca',
                        'cabeceraTitulo' => 'Carnet Biblioteca',
                        'outerBackground' => '#dceef2',
                        'primaryBackground' => '#1f6f8b',
                        'labelColor' => '#20ade9',
                    ])
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script>
        (function() {
            var botones = document.querySelectorAll('.js-btn-descargar-carnet');
            if (!botones.length) return;

            function descargarCarnet(btn) {
                var carnetId = btn.getAttribute('data-carnet-id');
                var el = carnetId ? document.getElementById(carnetId) : null;
                var baseName = btn.getAttribute('data-download-name') || 'carnet';
                var label = btn.getAttribute('data-label') || 'carnet';
                if (!el) {
                    alert('No se encontró la vista del ' + label + '.');
                    return;
                }
                if (typeof html2canvas === 'undefined') {
                    alert('No se pudo cargar la librería para generar la imagen. Compruebe su conexión e intente de nuevo.');
                    return;
                }
                btn.disabled = true;
                var prevHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status"></span> Generando…';
                var bg = window.getComputedStyle(el).backgroundColor || '#ffffff';

                html2canvas(el, {
                    scale: 3,
                    useCORS: true,
                    backgroundColor: bg,
                    logging: false
                }).then(function(canvas) {
                    var a = document.createElement('a');
                    a.download = baseName + '.png';
                    a.href = canvas.toDataURL('image/png');
                    document.body.appendChild(a);
                    a.click();
                    a.remove();
                }).catch(function() {
                    alert('No se pudo generar la imagen. Intente con otro navegador o más zoom.');
                }).finally(function() {
                    btn.disabled = false;
                    btn.innerHTML = prevHtml;
                });
            }

            botones.forEach(function(btn) {
                btn.addEventListener('click', function() {
                    descargarCarnet(btn);
                });
            });

            var params = new URLSearchParams(window.location.search);
            var autoDownload = params.get('auto_download') === '1';
            if (!autoDownload) return;
            var tipo = (params.get('tipo') || '').toLowerCase();
            var selector = '.js-btn-descargar-carnet[data-carnet-id="carnet-estudiante"]';
            if (tipo === 'biblioteca') {
                selector = '.js-btn-descargar-carnet[data-carnet-id="carnet-biblioteca"]';
            }
            var autoBtn = document.querySelector(selector);
            if (autoBtn) {
                setTimeout(function() {
                    descargarCarnet(autoBtn);
                }, 250);
            }
        })();
    </script>
@endsection
