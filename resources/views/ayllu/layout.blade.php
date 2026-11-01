@php
    $u = auth()->user();
    if ($u?->hasRole('super-admin')) {
        $parentLayout = 'layouts.superadmin';
    } elseif ($u?->hasRole('docente') && $u?->docente) {
        $parentLayout = 'layouts.docente';
    } elseif ($u?->hasAnyRole(['admin', 'adminB', 'gestor-titulacion', 'tutor'])) {
        $parentLayout = 'layouts.admin';
    } elseif ($u?->hasRole('alumnoB') || $u?->alumnoB) {
        $parentLayout = 'layouts.profesionalizacion';
        $alumno = $ppd ?? $u?->alumnoB;
    } else {
        $parentLayout = 'layouts.alumno';
    }
    $seccionActiva = $seccionActiva ?? 'inicio';
@endphp
@extends($parentLayout)
@section('titulo', 'Ayllu Puklla — Un espacio para aprender y construir comunidad')

@section('contenido')
<link rel="stylesheet" href="{{ asset('css/ayllu-puklla.css') }}?v={{ filemtime(public_path('css/ayllu-puklla.css')) }}">

<div class="container-fluid px-2 px-md-3 ayllu-wrapper">
    <div class="ayllu-top-strip"></div>

    {{-- Cabecera Institucional Cálida de Ayllu Puklla --}}
    <div class="ayllu-hero-banner">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 0.85rem;">
            <div>
                <div class="ayllu-kicker">
                    <i class="fas fa-sun"></i>
                    <span>EESP Pukllasunchis · Cusco · {{ $etiquetaRol ?? 'Comunidad' }}</span>
                </div>
                <h1 class="ayllu-hero-title">
                    Ayllu Puklla — Un espacio para aprender y construir comunidad
                </h1>
                <p class="ayllu-hero-sub">
                    {{ $aylluPerfil->saludo_intercultural ?? 'Allillanchu · Kuska yachasunchis (Crianza mutua, diálogo de saberes y formación docente EIB)' }}
                    @if($programa)
                        &nbsp;&middot;&nbsp; <strong>{{ $programa->nombre }}</strong>
                    @endif
                    @if($ciclo)
                        ({{ $ciclo->nombre }})
                    @endif
                </p>
            </div>
            <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem;">
                <a href="{{ route('ayllu.perfil') }}" class="btn btn-sm font-weight-bold text-white" style="background: rgba(255,255,255,0.16); border: 1px solid rgba(255,255,255,0.3); border-radius: 999px;">
                    <i class="fas fa-user-circle mr-1"></i> Mi Perfil & Portafolio
                </a>
                <a href="{{ route('ayllu.privado') }}" class="btn btn-sm font-weight-bold" style="background: #f6d39c; color: #253449; border-radius: 999px;">
                    <i class="fas fa-lock mr-1"></i> Notas, Asistencia y Trámites
                </a>
            </div>
        </div>
    </div>

    {{-- Navegación Horizontal de Módulos de Ayllu Puklla --}}
    <nav class="ayllu-subnav" aria-label="Navegación Ayllu Puklla">
        <a href="{{ route('ayllu.inicio') }}" class="ayllu-subnav-link {{ $seccionActiva === 'inicio' ? 'is-active' : '' }}">
            <i class="fas fa-home"></i>
            <span>Inicio</span>
        </a>
        @if($cursos->isNotEmpty())
            <a href="{{ route('ayllu.cursos.show', $cursos->first()->id) }}" class="ayllu-subnav-link {{ $seccionActiva === 'cursos' ? 'is-active' : '' }}">
                <i class="fas fa-book-reader"></i>
                <span>Comunidades de Curso ({{ $cursos->count() }})</span>
            </a>
        @endif
        <a href="{{ route('ayllu.proyecto') }}" class="ayllu-subnav-link {{ $seccionActiva === 'proyecto' ? 'is-active' : '' }}">
            <i class="fas fa-project-diagram"></i>
            <span>Proyecto Anual (Taq’e) & Portafolio</span>
        </a>
        <a href="{{ route('ayllu.grupos') }}" class="ayllu-subnav-link {{ $seccionActiva === 'grupos' ? 'is-active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Grupos / Ayllus</span>
        </a>
        <a href="{{ route('ayllu.calendario') }}" class="ayllu-subnav-link {{ $seccionActiva === 'calendario' ? 'is-active' : '' }}">
            <i class="far fa-calendar-alt"></i>
            <span>Calendario Vivo</span>
        </a>
        <a href="{{ route('ayllu.chat') }}" class="ayllu-subnav-link {{ $seccionActiva === 'chat' ? 'is-active' : '' }}">
            <i class="far fa-comments"></i>
            <span>Chat Académico</span>
        </a>
        <a href="{{ route('ayllu.privado') }}" class="ayllu-subnav-link is-privado {{ $seccionActiva === 'privado' ? 'is-active' : '' }}">
            <i class="fas fa-shield-alt"></i>
            <span>Mi Espacio Privado</span>
        </a>
    </nav>

    {{-- Alertas de retroalimentación del sistema --}}
    @if(session('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius:12px;">
            <i class="fas fa-check-circle mr-1"></i> {{ session('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if(session('error'))
        <div class="alert alert-danger alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius:12px;">
            <i class="fas fa-exclamation-triangle mr-1"></i> {{ session('error') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif
    @if($errors->any())
        <div class="alert alert-warning alert-dismissible fade show shadow-sm border-0" role="alert" style="border-radius:12px;">
            <strong><i class="fas fa-info-circle mr-1"></i> Verifica los campos:</strong>
            <ul class="mb-0 pl-3 mt-1">
                @foreach($errors->all() as $err)
                    <li>{{ $err }}</li>
                @endforeach
            </ul>
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar"><span aria-hidden="true">&times;</span></button>
        </div>
    @endif

    {{-- Contenido específico de cada vista de Ayllu Puklla --}}
    @yield('ayllu_contenido')
</div>

{{-- Barra de Navegación Inferior Móvil (< 768px) --}}
<nav class="ayllu-bottom-nav" aria-label="Navegación móvil Ayllu Puklla">
    <a href="{{ route('ayllu.inicio') }}" class="ayllu-bottom-nav-item {{ $seccionActiva === 'inicio' ? 'is-active' : '' }}">
        <i class="fas fa-home"></i>
        <span>Inicio</span>
    </a>
    @if($cursos->isNotEmpty())
        <a href="{{ route('ayllu.cursos.show', $cursos->first()->id) }}" class="ayllu-bottom-nav-item {{ $seccionActiva === 'cursos' ? 'is-active' : '' }}">
            <i class="fas fa-book-reader"></i>
            <span>Cursos</span>
        </a>
    @else
        <a href="{{ route('ayllu.grupos') }}" class="ayllu-bottom-nav-item {{ $seccionActiva === 'grupos' ? 'is-active' : '' }}">
            <i class="fas fa-users"></i>
            <span>Ayllus</span>
        </a>
    @endif
    <a href="{{ route('ayllu.proyecto') }}" class="ayllu-bottom-nav-item {{ $seccionActiva === 'proyecto' ? 'is-active' : '' }}">
        <i class="fas fa-seedling"></i>
        <span>Proyecto</span>
    </a>
    <a href="{{ route('ayllu.chat') }}" class="ayllu-bottom-nav-item {{ in_array($seccionActiva, ['chat', 'calendario']) ? 'is-active' : '' }}">
        <i class="far fa-comments"></i>
        <span>Diálogo</span>
    </a>
    <a href="{{ route('ayllu.privado') }}" class="ayllu-bottom-nav-item {{ $seccionActiva === 'privado' ? 'is-active' : '' }}">
        <i class="fas fa-lock"></i>
        <span>Privado</span>
    </a>
</nav>
@endsection
