@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Nuevo Proyecto Integrador')

@section('contenido')
    @include('alumnos.partials.ui')
    @include('admin.curso.silabos.proyectos._estilos')

    <div class="container-fluid al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-project-diagram"></i> Proyectos Integradores</div>
                    <h1>{{ $proyecto->exists ? $proyecto->nombre : 'Nuevo proyecto' }}</h1>
                    <p class="al-hero-sub">Define el proyecto, su producto, su descripción y los ciclos que lo trabajan.</p>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('proyectos.index') }}" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </section>

        @include('admin.curso.silabos.proyectos._alertas')

        @include('admin.curso.silabos.proyectos._form', ['accion' => route('proyectos.store'), 'metodo' => 'POST', 'titulo' => 'Nuevo Proyecto Integrador'])
    </div>
@endsection
