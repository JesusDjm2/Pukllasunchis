@extends('layouts.docente')

@section('titulo', "Taq'e — Seguimiento")

@section('contenido')
    {{-- Mismo diseño que la vista del alumno (memoria: feedback_diseno_unificado). --}}
    @include('alumnos.partials.ui')
    @include('taqe._estilos')
    @include('partials.flash-swal')
    @php
        $finalizados = $grupos->where('estado', 'finalizado')->count();
        $evidencias = $grupos->sum(fn ($g) => $g->evidencias->count());
    @endphp

    <div class="container-fluid docente-ui-page al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-flask"></i> Taq'e · Ferias de Investigación</div>
                    <h1>Seguimiento institucional</h1>
                    <p class="al-hero-sub">Todos los grupos Taq'e de la institución. Filtra por periodo, programa, ciclo y curso, revisa sus evidencias y atiende sus pedidos de ayuda.</p>
                </div>
            </div>
        </section>

        <div class="al-stats">
            <div class="al-stat"><span class="al-stat-icono"><i class="fas fa-users"></i></span>
                <div><div class="al-stat-valor">{{ $grupos->count() }}</div><div class="al-stat-etiqueta">Grupos</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-oro"><i class="fas fa-images"></i></span>
                <div><div class="al-stat-valor">{{ $evidencias }}</div><div class="al-stat-etiqueta">Evidencias</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-ok"><i class="fas fa-flag-checkered"></i></span>
                <div><div class="al-stat-valor">{{ $finalizados }}</div><div class="al-stat-etiqueta">Con trabajo final</div></div></div>
            <div class="al-stat"><span class="al-stat-icono {{ $solicitudesPendientes->isNotEmpty() ? 'is-mal' : 'is-info' }}"><i class="fas fa-life-ring"></i></span>
                <div><div class="al-stat-valor">{{ $solicitudesPendientes->count() }}</div><div class="al-stat-etiqueta">Pedidos de ayuda</div></div></div>
        </div>

        @include('taqe._solicitudes', ['solicitudes' => $solicitudesPendientes, 'rutaAtender' => 'taqe.investigador.solicitudes.atender'])

        @include('taqe._filtros', ['ruta' => 'taqe.investigador.index', 'total' => $grupos->count()])

        @forelse ($grupos as $grupo)
            @include('taqe._grupo-gestion', [
                'grupo' => $grupo,
                'rutaVer' => 'taqe.investigador.trabajos.ver',
                'rutaZip' => 'taqe.investigador.descargar',
            ])
        @empty
            <div class="al-card"><div class="al-vacio"><i class="fas fa-flask"></i>No hay grupos Taq'e con estos filtros.</div></div>
        @endforelse
    </div>

    @include('taqe._visor')
@endsection
