@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@push('styles')
<style>
    .seg-card { border-radius: .85rem; overflow: hidden; }

    .seg-table { font-size: .82rem; margin-bottom: 0; }
    .seg-table thead th {
        background: #f8f9fc;
        color: #5a5c69;
        font-size: .68rem;
        font-weight: 700;
        letter-spacing: .04em;
        text-transform: uppercase;
        border-bottom: 2px solid #e3e6f0;
        border-top: none;
        padding: .85rem 1rem;
        white-space: nowrap;
    }
    .seg-table tbody td {
        padding: .75rem 1rem;
        border-top: 1px solid #eef0f5;
        vertical-align: middle;
    }
    .seg-table tbody tr:hover { background-color: #f7f9fd; }

    .seg-table tr.seg-programa-row td {
        background: #f8f9fc;
        color: #5a5c69;
        font-weight: 700;
        font-size: .68rem;
        letter-spacing: .04em;
        text-transform: uppercase;
        padding: .5rem 1rem;
        border-top: 1px solid #e3e6f0;
    }

    .seg-table tr.seg-programa-row td i {
        color: #4e73df;
    }

    .seg-ciclo-pill {
        display: inline-block; padding: .15rem .55rem; border-radius: 6px;
        background: #eef1fc; color: #4e73df; font-size: .7rem; font-weight: 700;
    }

    .seg-tutores { display: flex; flex-wrap: wrap; gap: .4rem; }
    .seg-tutor-chip {
        display: inline-flex; align-items: center; gap: .35rem;
        padding: .25rem .6rem .25rem .3rem; border-radius: 20px;
        background: #f4f6f9; font-size: .72rem; font-weight: 600; color: #3a3b45;
    }
    .seg-tutor-avatar {
        display: inline-flex; align-items: center; justify-content: center;
        width: 20px; height: 20px; min-width: 20px; border-radius: 50%;
        background: #4e73df; color: #fff; font-weight: 700; font-size: .58rem;
    }
    .seg-sin-tutor { color: #b7b9c8; font-style: italic; font-size: .74rem; }

    .seg-qr-actions { display: flex; gap: .4rem; justify-content: center; }
    .seg-btn-qr {
        display: inline-flex; align-items: center; gap: .35rem;
        border-radius: 20px; font-size: .72rem; font-weight: 600;
        padding: .3rem .85rem;
    }

    .seg-qr-link {
        display: inline-flex; align-items: center; gap: .3rem;
        margin-top: .4rem;
        border: 0; background: none; padding: 0;
        font-size: .68rem; color: #858796; cursor: pointer;
    }
    .seg-qr-link:hover { color: #4e73df; text-decoration: underline; }
    .seg-qr-link.is-copiado { color: #17a673; }
</style>
@endpush

@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-qrcode mr-2"></i> Códigos QR por Ciclo
            </h5>
            <small class="text-muted">Cada QR notifica únicamente al tutor o tutores asignados a ese ciclo</small>
        </div>
        <a href="{{ route('admin.seguimiento') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left fa-sm mr-1"></i> Volver a Seguimiento
        </a>
    </div>

    @if ($ciclos->isEmpty())
        <div class="text-center text-muted py-5">
            <i class="fa fa-folder-open fa-3x mb-3"></i>
            <p>Todavía no hay ciclos con tutor asignado.</p>
        </div>
    @else
        <div class="card seg-card shadow-sm border-0">
            <div class="table-responsive">
                <table class="table seg-table table-hover">
                    <thead>
                        <tr>
                            <th>Ciclo</th>
                            <th>Tutor(es)</th>
                            <th class="text-center">QR Tutoría</th>
                            <th class="text-center">QR Sugerencias</th>
                        </tr>
                    </thead>
                    <tbody>
                        @php $programaAnterior = null; @endphp
                        @foreach ($ciclos as $ciclo)
                            @php $programaActual = optional($ciclo->programa)->nombre ?? 'Sin programa'; @endphp
                            @if ($programaActual !== $programaAnterior)
                                <tr class="seg-programa-row">
                                    <td colspan="4"><i class="fa fa-graduation-cap mr-1"></i> {{ $programaActual }}</td>
                                </tr>
                                @php $programaAnterior = $programaActual; @endphp
                            @endif
                            <tr>
                                <td><span class="seg-ciclo-pill">Ciclo {{ $ciclo->nombre }}</span></td>
                                <td>
                                    @if ($ciclo->tutores->isEmpty())
                                        <span class="seg-sin-tutor">Sin tutor asignado</span>
                                    @else
                                        <div class="seg-tutores">
                                            @foreach ($ciclo->tutores as $tutor)
                                                @php $nombreCorto = $tutor->nombreCorto(); @endphp
                                                <span class="seg-tutor-chip">
                                                    <span class="seg-tutor-avatar">{{ mb_strtoupper(mb_substr($nombreCorto, 0, 1)) }}</span>
                                                    {{ $nombreCorto }}
                                                </span>
                                            @endforeach
                                        </div>
                                    @endif
                                </td>
                                <td class="text-center">
                                    <div class="seg-qr-actions">
                                        <button type="button" class="btn btn-outline-primary seg-btn-qr qrp-ver-btn"
                                            data-panel-url="{{ route('admin.tutorias.qr', ['ciclo' => $ciclo->id, 'panel' => 1]) }}"
                                            data-print-url="{{ route('admin.tutorias.qr', ['ciclo' => $ciclo->id, 'print' => 1]) }}"
                                            data-titulo="QR Tutoría — Ciclo {{ $ciclo->nombre }} ({{ optional($ciclo->programa)->nombre }})">
                                            <i class="fa fa-qrcode"></i> Ver
                                        </button>
                                    </div>
                                    @role('super-admin')
                                        <button type="button" class="seg-qr-link seg-copy-link"
                                            data-url="{{ route('tutorias.public.create', ['ciclo' => $ciclo->id]) }}">
                                            <i class="fa fa-link"></i> Copiar enlace
                                        </button>
                                    @endrole
                                </td>
                                <td class="text-center">
                                    <div class="seg-qr-actions">
                                        <button type="button" class="btn btn-outline-primary seg-btn-qr qrp-ver-btn"
                                            data-panel-url="{{ route('admin.sugerencias.qr', ['ciclo' => $ciclo->id, 'panel' => 1]) }}"
                                            data-print-url="{{ route('admin.sugerencias.qr', ['ciclo' => $ciclo->id, 'print' => 1]) }}"
                                            data-titulo="QR Sugerencias — Ciclo {{ $ciclo->nombre }} ({{ optional($ciclo->programa)->nombre }})">
                                            <i class="fa fa-qrcode"></i> Ver
                                        </button>
                                    </div>
                                    @role('super-admin')
                                        <button type="button" class="seg-qr-link seg-copy-link"
                                            data-url="{{ route('sugerencias.public.create', ['ciclo' => $ciclo->id]) }}">
                                            <i class="fa fa-link"></i> Copiar enlace
                                        </button>
                                    @endrole
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif
</div>

{{-- ── Popup del QR: previsualiza, imprime y descarga sin salir de esta vista ── --}}
<div class="modal fade" id="qrCicloModal" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered" role="document" style="max-width: 640px;">
        <div class="modal-content">
            <div class="modal-header py-2">
                <h6 class="modal-title" id="qrCicloModalTitulo">Código QR</h6>
                <button type="button" class="close" id="qrCicloModalCerrar" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body text-center" id="qrCicloModalBody" style="min-height: 220px;">
                <div class="text-muted py-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
(function () {
    var modalBody    = document.getElementById('qrCicloModalBody');
    var modalTitulo  = document.getElementById('qrCicloModalTitulo');

    // Cierre explícito del popup: en esta página conviven Bootstrap 4 (jQuery,
    // usado para abrir el modal) y Bootstrap 5, y el dismiss automático por
    // data-dismiss no siempre queda enganchado a la instancia correcta.
    document.getElementById('qrCicloModalCerrar').addEventListener('click', function () {
        $('#qrCicloModal').modal('hide');
    });

    // Copiar enlace (solo visible para super-admin).
    document.querySelectorAll('.seg-copy-link').forEach(function (btn) {
        var textoOriginal = btn.innerHTML;
        btn.addEventListener('click', function () {
            navigator.clipboard.writeText(btn.dataset.url).then(function () {
                btn.innerHTML = '<i class="fa fa-check"></i> Copiado';
                btn.classList.add('is-copiado');
                setTimeout(function () {
                    btn.innerHTML = textoOriginal;
                    btn.classList.remove('is-copiado');
                }, 1800);
            });
        });
    });

    document.querySelectorAll('.qrp-ver-btn').forEach(function (btn) {
        btn.addEventListener('click', function () {
            modalTitulo.textContent = btn.dataset.titulo;
            modalBody.dataset.printUrl = btn.dataset.printUrl;
            modalBody.innerHTML = '<div class="text-muted py-5"><i class="fa fa-spinner fa-spin fa-2x"></i></div>';
            $('#qrCicloModal').modal('show');

            fetch(btn.dataset.panelUrl)
                .then(function (r) { return r.text(); })
                .then(function (html) { modalBody.innerHTML = html; })
                .catch(function () {
                    modalBody.innerHTML = '<div class="text-danger py-5">No se pudo cargar el código QR.</div>';
                });
        });
    });

    // El botón "Imprimir" del póster, dentro del popup, abre la versión completa
    // en una pestaña nueva (imprimir directamente desde dentro del modal no es confiable).
    document.addEventListener('click', function (e) {
        var imprimirBtn = e.target.closest('[data-qrp-imprimir]');
        if (!imprimirBtn || !modalBody.contains(imprimirBtn)) return;
        if (modalBody.dataset.printUrl) {
            window.open(modalBody.dataset.printUrl, '_blank');
        }
    });

    // Descargar imagen (html2canvas) — funciona igual dentro del popup.
    document.addEventListener('click', function (e) {
        var descargarBtn = e.target.closest('#qrpDescargarBtn');
        if (!descargarBtn || !modalBody.contains(descargarBtn)) return;

        var poster = document.getElementById('qrPoster');
        if (!poster) return;

        var original = descargarBtn.innerHTML;
        descargarBtn.disabled = true;
        descargarBtn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Generando…';

        html2canvas(poster, { scale: 3, backgroundColor: '#ffffff', useCORS: true })
            .then(function (canvas) {
                var link = document.createElement('a');
                link.download = 'qr-codigo.png';
                link.href = canvas.toDataURL('image/png');
                link.click();
            })
            .catch(function () {
                alert('No se pudo generar la imagen. Intenta abrir la versión completa para imprimir.');
            })
            .finally(function () {
                descargarBtn.disabled = false;
                descargarBtn.innerHTML = original;
            });
    });
})();
</script>
@endpush
