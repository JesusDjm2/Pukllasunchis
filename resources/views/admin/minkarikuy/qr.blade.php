@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('titulo', 'Afiche QR — '.$minkarikuy->nombre)
@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4 d-print-none"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-qrcode mr-2"></i> Afiche QR — Mink'arikuy
            </h5>
            <small class="text-muted">Imprímelo o descárgalo como imagen para publicarlo. El QR lleva a: {{ $url }}</small>
        </div>
        <a href="{{ route('admin.minkarikuy.index') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left fa-sm mr-1"></i> Volver
        </a>
    </div>

    @include('partials.qr-poster', [
        'tipo'        => $tipo,
        'titulo'      => $titulo,
        'descripcion' => $descripcion,
        'url'         => $url,
        'contexto'    => $contexto,
    ])
</div>
@endsection
