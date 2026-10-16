@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', $proyecto->nombre)

@section('contenido')
    @include('alumnos.partials.ui')
    @include('admin.curso.silabos.proyectos._estilos')

    <div class="container-fluid al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-project-diagram"></i> Proyecto Integrador</div>
                    <h1>{{ $proyecto->nombre }}</h1>
                    <div class="al-chips">
                        @if ($proyecto->producto)
                            <span class="al-chip"><i class="fas fa-box-open"></i>{{ $proyecto->producto }}</span>
                        @endif
                        <span class="al-chip"><i class="fas fa-layer-group"></i>{{ $proyecto->ciclos->count() }} ciclo(s)</span>
                        @if (\App\Models\Proyecto::taqeActivo())
                            <span class="al-chip"><i class="fas fa-flask"></i>{{ $proyecto->taqe_grupos_count }} grupo(s) Taq'e</span>
                        @endif
                    </div>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('proyectos.edit', $proyecto) }}" class="al-btn al-btn-oro"><i class="fas fa-pen"></i> Editar</a>
                    <a href="{{ route('proyectos.index') }}" class="al-btn al-btn-claro"><i class="fas fa-arrow-left"></i> Volver</a>
                </div>
            </div>
        </section>

        @include('admin.curso.silabos.proyectos._alertas')

        <div class="row">
            <div class="col-xl-8">
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-align-left"></i></span>
                        <div><h2>Descripción</h2><p>{{ \App\Models\Proyecto::taqeActivo() ? "Así la ven los estudiantes en Taq'e." : 'Tal como se muestra en los sílabos.' }}</p></div>
                    </div>
                    <div class="al-card-cuerpo pi-descripcion-completa">{!! $proyecto->descripcionHtml() !!}</div>
                </div>
            </div>
            <div class="col-xl-4">
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-layer-group"></i></span>
                        <div><h2>Ciclos</h2><p>Quiénes trabajan este proyecto.</p></div>
                    </div>
                    <div class="al-card-cuerpo">
                        @forelse ($proyecto->ciclos->groupBy(fn ($c) => $c->programa->nombre ?? 'Sin programa') as $programa => $ciclos)
                            <div class="mb-2">
                                <div class="pi-programa-titulo">{{ $programa }}</div>
                                <div class="d-flex flex-wrap" style="gap:.3rem;">
                                    @foreach ($ciclos as $c)
                                        <span class="al-badge is-info">Ciclo {{ $c->nombre }}</span>
                                    @endforeach
                                </div>
                            </div>
                        @empty
                            <div class="al-aviso is-warn mb-0"><i class="fas fa-exclamation-triangle"></i><div>Sin ciclos asignados: ningún alumno puede trabajar este proyecto todavía.</div></div>
                        @endforelse
                    </div>
                </div>
                <div class="al-card">
                    <div class="al-card-cab">
                        <span class="al-card-cab-icono"><i class="fas fa-user-tie"></i></span>
                        <div><h2>Encargados y organizadores</h2><p>Docentes que acompañan el proyecto.</p></div>
                    </div>
                    <div class="al-card-cuerpo">
                        @if ($proyecto->docentes->isNotEmpty())
                            <div class="pi-equipo">
                                @foreach ($proyecto->docentes->sortBy(fn ($d) => [$d->pivot->rol === 'encargado' ? 0 : 1, $d->nombre]) as $d)
                                    <div class="pi-equipo-fila">
                                        <span>{{ $d->nombre }}</span>
                                        <span class="al-badge {{ $d->pivot->rol === 'encargado' ? 'is-warn' : 'is-info' }}">{{ $d->pivot->rol === 'encargado' ? 'Encargado' : 'Organizador' }}</span>
                                    </div>
                                @endforeach
                            </div>
                        @else
                            <div class="al-aviso is-warn mb-0"><i class="fas fa-exclamation-triangle"></i>
                                <div>Aún no hay docentes a cargo. <a href="{{ route('proyectos.edit', $proyecto) }}">Asígnalos al editar</a>.</div></div>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
