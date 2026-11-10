@extends('layouts.docente')

@section('titulo', "Investigaciones — " . $curso->nombre)

@section('contenido')
    {{-- Mismo diseño que la vista del alumno (memoria: feedback_diseno_unificado). Solo lectura. --}}
    @include('alumnos.partials.ui')
    @include('taqe._estilos')

    <div class="container-fluid docente-ui-page al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-flask"></i> Investigaciones · Ferias de Investigación</div>
                    <h1>{{ $curso->nombre }}</h1>
                    <div class="al-chips">
                        <span class="al-chip"><i class="fas fa-graduation-cap"></i>{{ $curso->ciclo->programa->nombre ?? '—' }}</span>
                        <span class="al-chip"><i class="fas fa-layer-group"></i>Ciclo {{ $curso->ciclo->nombre ?? '—' }}</span>
                        <span class="al-chip"><i class="fas fa-eye"></i>Solo lectura</span>
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('vistaDocente', ['docente' => $docente->id]) }}" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Mis cursos</a>
                </div>
            </div>
        </section>

        <div class="al-stats">
            <div class="al-stat"><span class="al-stat-icono"><i class="fas fa-users"></i></span>
                <div><div class="al-stat-valor">{{ $grupos->count() }}</div><div class="al-stat-etiqueta">Grupos</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-oro"><i class="fas fa-images"></i></span>
                <div><div class="al-stat-valor">{{ $grupos->sum(fn ($g) => $g->evidencias->count()) }}</div><div class="al-stat-etiqueta">Evidencias</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-ok"><i class="fas fa-flag-checkered"></i></span>
                <div><div class="al-stat-valor">{{ $grupos->where('estado', 'finalizado')->count() }}</div><div class="al-stat-etiqueta">Con trabajo final</div></div></div>
        </div>

        @forelse ($grupos as $grupo)
            @include('taqe._grupo-gestion', ['grupo' => $grupo, 'mostrarContexto' => false])
        @empty
            <div class="al-card"><div class="al-vacio"><i class="fas fa-flask"></i>Aún no hay grupos de Investigaciones en este curso.</div></div>
        @endforelse
    </div>
@endsection
