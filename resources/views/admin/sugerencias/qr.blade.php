@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4 d-print-none"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-qrcode mr-2"></i> QR — Buzón de Sugerencias
            </h5>
            <small class="text-muted">Imprime este código para que los alumnos lo escaneen</small>
        </div>
        <a href="{{ route('admin.seguimiento.qr') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left fa-sm mr-1"></i> Volver
        </a>
    </div>

    @include('partials.qr-poster', [
        'tipo'        => $tipo,
        'titulo'      => $titulo,
        'descripcion' => $descripcion,
        'url'         => $url,
        'contexto'    => $contexto ?? null,
        'tutor'       => $tutor ?? null,
    ])
</div>

@if (request('print'))
    @push('scripts')
    <script>window.addEventListener('load', function () { window.print(); });</script>
    @endpush
@endif
@endsection
