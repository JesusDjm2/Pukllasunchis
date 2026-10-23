@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('titulo', 'Nuevo Mink\'arikuy')
@section('contenido')
    @include('alumnos.partials.ui')
    @include('admin.minkarikuy._estilos')

    <div class="container-fluid al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-people-carry"></i> Gestión interna · Mink'arikuy</div>
                    <h1>Nuevo Mink'arikuy</h1>
                    <p class="al-hero-sub">Completa los datos: el código QR se genera solo con el enlace.</p>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('admin.minkarikuy.index') }}" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </section>

        @include('admin.curso.silabos.proyectos._alertas')
        @include('admin.minkarikuy._form')
    </div>

    @include('admin.minkarikuy._scripts')
@endsection
