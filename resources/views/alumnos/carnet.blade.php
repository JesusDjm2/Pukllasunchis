@extends('layouts.admin')
@section('titulo', 'Carnet estudiante — ' . ($alumno->apellidos ?? '') . ', ' . ($alumno->nombres ?? ''))
@section('contenido')
    <style>
        /* Guía de perforación: solo al imprimir (no afecta vista previa ni PNG con html2canvas). */
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
                <h4 class="text-primary mb-1">Carnet de estudiante</h4>
                <p class="small text-muted mb-0">Vista previa. La fotografía proviene del usuario vinculado al alumno.</p>
            </div>
            <div class="mt-2 mt-md-0">
                <button type="button" id="btnDescargarCarnetPng" class="btn btn-primary btn-sm shadow-sm">
                    <i class="fas fa-download fa-sm mr-1"></i> Descargar imagen (PNG)
                </button>
                <a href="{{ route('adminAlumnos', request()->only(['search', 'search_page', 'with_user', 'programa_id', 'ciclo_id'])) }}"
                    class="btn btn-outline-secondary btn-sm ml-1">Volver al listado</a>
            </div>
        </div>
        <div class="border rounded p-4 bg-light d-inline-block">
            @include('alumnos.partials.carnet-estudiante-inner')
        </div>
    </div>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/html2canvas/1.4.1/html2canvas.min.js" crossorigin="anonymous"
        referrerpolicy="no-referrer"></script>
    <script>
        (function() {
            var btn = document.getElementById('btnDescargarCarnetPng');
            var el = document.getElementById('carnet');
            var baseName = {!! json_encode(\Illuminate\Support\Str::slug('carnet-' . $dni, '_')) !!};

            if (!btn || !el) return;

            btn.addEventListener('click', function() {
                if (typeof html2canvas === 'undefined') {
                    alert('No se pudo cargar la librería para generar la imagen. Compruebe su conexión e intente de nuevo.');
                    return;
                }
                btn.disabled = true;
                var prevHtml = btn.innerHTML;
                btn.innerHTML = '<span class="spinner-border spinner-border-sm mr-1" role="status"></span> Generando…';

                html2canvas(el, {
                    scale: 2,
                    useCORS: true,
                    backgroundColor: '#e8dcc8',
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
            });
        })();
    </script>
@endsection
