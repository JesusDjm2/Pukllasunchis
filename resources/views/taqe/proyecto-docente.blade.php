@php
    $usuario = auth()->user();
    $layout = $usuario->hasRole('super-admin') ? 'layouts.superadmin' : ($usuario->hasRole('admin') ? 'layouts.admin' : 'layouts.docente');
    $finalizados = $grupos->where('estado', 'finalizado')->count();
    $evidencias = $grupos->sum(fn ($g) => $g->evidencias->count());
    $finales = $grupos->sum(fn ($g) => $g->finales->count());
@endphp
@extends($layout)

@section('titulo', "Taq'e — " . $proyecto->nombre)

@section('contenido')
    {{-- Mismo diseño que la vista del alumno (memoria: feedback_diseno_unificado). Solo lectura. --}}
    @include('alumnos.partials.ui')
    @include('taqe._estilos')

    <div class="container-fluid docente-ui-page al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-project-diagram"></i> Taq'e · Proyecto Integrador</div>
                    <h1>{{ $proyecto->nombre }}</h1>
                    <div class="al-chips">
                        @if ($proyecto->producto)
                            <span class="al-chip"><i class="fas fa-box-open"></i>{{ $proyecto->producto }}</span>
                        @endif
                        @if ($proyecto->ciclos->isNotEmpty())
                            <span class="al-chip"><i class="fas fa-layer-group"></i>Ciclos {{ $proyecto->ciclos->pluck('nombre')->unique()->implode(', ') }}</span>
                        @endif
                        @if ($rol !== 'admin')
                            <span class="al-chip"><i class="fas fa-user-tie"></i>Eres {{ $rol === 'encargado' ? 'encargado' : 'organizador' }}</span>
                        @endif
                        <span class="al-chip"><i class="fas fa-eye"></i>Solo lectura</span>
                    </div>
                </div>
                <div class="al-hero-acciones">
                    @if ($rol === 'admin')
                        <a href="{{ route('proyectos.show', $proyecto) }}" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Proyecto</a>
                    @elseif ($usuario->docente)
                        <a href="{{ route('vistaDocente', ['docente' => $usuario->docente->id]) }}" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Mis cursos</a>
                    @endif
                </div>
            </div>
        </section>

        <div class="al-stats">
            <div class="al-stat"><span class="al-stat-icono"><i class="fas fa-users"></i></span>
                <div><div class="al-stat-valor">{{ $grupos->count() }}</div><div class="al-stat-etiqueta">Grupos</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-oro"><i class="fas fa-images"></i></span>
                <div><div class="al-stat-valor">{{ $evidencias }}</div><div class="al-stat-etiqueta">Evidencias</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-info"><i class="fas fa-file-alt"></i></span>
                <div><div class="al-stat-valor">{{ $finales }}</div><div class="al-stat-etiqueta">Archivos del final</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-ok"><i class="fas fa-flag-checkered"></i></span>
                <div><div class="al-stat-valor">{{ $finalizados }}</div><div class="al-stat-etiqueta">Entregados</div></div></div>
        </div>

        @forelse ($grupos as $grupo)
            @include('taqe._grupo-gestion', [
                'grupo' => $grupo,
                'rutaVer' => 'taqe.proyecto.trabajos.ver',
                'rutaZip' => 'taqe.proyecto.descargar',
            ])
        @empty
            <div class="al-card"><div class="al-vacio"><i class="fas fa-flask"></i>Aún no hay grupos Taq'e en este Proyecto Integrador.</div></div>
        @endforelse
    </div>

    @include('taqe._visor')
@endsection
