@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Proyectos Integradores')

@section('contenido')
    @include('alumnos.partials.ui')
    @php
        $romanos = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10];
        // "Ciclos I y II" a partir de los ciclos asignados (sin repetir el mismo número de varios programas)
        $rangoCiclos = function ($ciclos) use ($romanos) {
            $nums = $ciclos->map(fn ($c) => trim(preg_replace('/^Ciclo\s+/i', '', explode(' - ', $c->nombre)[0])))
                ->unique()->sortBy(fn ($n) => $romanos[$n] ?? 99)->values();

            return $nums->isEmpty() ? null : ($nums->count() === 1 ? 'Ciclo '.$nums[0] : 'Ciclos '.$nums->slice(0, -1)->implode(', ').' y '.$nums->last());
        };
    @endphp

    <div class="container-fluid al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-project-diagram"></i> Gestión académica</div>
                    <h1>Proyectos Integradores</h1>
                    <p class="al-hero-sub">Cada ciclo trabaja un Proyecto Integrador. Es el que aparece en los sílabos{{ \App\Models\Proyecto::taqeActivo() ? " y el que define en qué proyecto forma sus grupos Taq'e cada alumno" : '' }}.</p>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('proyectos.create') }}" class="al-btn al-btn-oro"><i class="fas fa-plus"></i> Nuevo proyecto</a>
                </div>
            </div>
        </section>

        @include('admin.curso.silabos.proyectos._alertas')

        @php $sinProyecto = \App\Models\Ciclo::whereNull('proyecto_id')->where('nombre', 'not like', 'Egresados%')->with('programa')->get(); @endphp
        @if ($sinProyecto->isNotEmpty())
            <div class="al-aviso is-warn"><i class="fas fa-exclamation-triangle"></i>
                <div><strong>{{ $sinProyecto->count() }} ciclo(s) activo(s) sin Proyecto Integrador.</strong> {{ \App\Models\Proyecto::taqeActivo() ? "Sus alumnos no pueden formar grupos Taq'e y sus" : 'Sus' }} sílabos no muestran proyecto:
                    <div class="d-flex flex-wrap mt-1" style="gap:.3rem;">
                        @foreach ($sinProyecto as $c)
                            <span class="al-badge is-warn">{{ $c->programa->nombre ?? '—' }} · {{ $c->nombre }}</span>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif

        @forelse ($proyectos as $proyecto)
            <article class="al-card">
                <div class="al-card-cab">
                    <span class="al-card-cab-icono"><i class="fas fa-project-diagram"></i></span>
                    <div style="flex:1; min-width:0;">
                        <h2>{{ $proyecto->nombre }}</h2>
                        @if ($proyecto->producto)
                            <p><i class="fas fa-box-open mr-1"></i>{{ $proyecto->producto }}</p>
                        @endif
                    </div>
                    <div class="al-card-cab-extra">
                        @if ($rango = $rangoCiclos($proyecto->ciclos))
                            <span class="al-badge is-info"><i class="fas fa-layer-group"></i> {{ $rango }}</span>
                        @else
                            <span class="al-badge is-warn"><i class="fas fa-exclamation-triangle"></i> Sin ciclos</span>
                        @endif
                        @if (\App\Models\Proyecto::taqeActivo())
                            <span class="al-badge is-muted" title="Grupos Taq'e en este proyecto"><i class="fas fa-flask"></i> {{ $proyecto->taqe_grupos_count }}</span>
                        @endif
                    </div>
                </div>
                <div class="al-card-cuerpo">
                    <p class="pi-descripcion">{{ \Illuminate\Support\Str::limit(trim(preg_replace('/\s+/', ' ', html_entity_decode(strip_tags($proyecto->descripcionHtml())))), 380) }}</p>
                    @if ($proyecto->ciclos->isNotEmpty())
                        <div class="pi-ciclos">
                            @foreach ($proyecto->ciclos->groupBy(fn ($c) => $c->programa->nombre ?? 'Sin programa') as $programa => $ciclos)
                                <span><b>{{ $programa }}:</b> {{ $ciclos->pluck('nombre')->implode(', ') }}</span>
                            @endforeach
                        </div>
                    @endif
                    <div class="pi-ciclos">
                        @if ($proyecto->docentes->isNotEmpty())
                            @foreach ($proyecto->docentes->sortBy(fn ($d) => [$d->pivot->rol === 'encargado' ? 0 : 1, $d->nombre])->groupBy('pivot.rol') as $rol => $lista)
                                <span><b><i class="fas fa-user-tie"></i> {{ $rol === 'encargado' ? 'Encargado(s)' : 'Organizador(es)' }}:</b> {{ $lista->pluck('nombre')->implode(', ') }}</span>
                            @endforeach
                        @else
                            <span class="text-warning"><i class="fas fa-user-tie"></i> Sin encargados ni organizadores</span>
                        @endif
                    </div>
                    <div class="pi-acciones">
                        <a href="{{ route('proyectos.show', $proyecto) }}" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-eye"></i> Ver</a>
                        <a href="{{ route('proyectos.edit', $proyecto) }}" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-pen"></i> Editar</a>
                        <form action="{{ route('proyectos.destroy', $proyecto) }}" method="POST"
                            data-confirm="¿Eliminar el Proyecto Integrador &quot;{{ $proyecto->nombre }}&quot;? Sus {{ $proyecto->ciclos->count() }} ciclo(s) quedarán sin proyecto (los sílabos dejarán de mostrarlo){{ $proyecto->taqe_grupos_count ? ' y sus '.$proyecto->taqe_grupos_count.' grupo(s) Taq\'e quedarán sin proyecto' : '' }}." data-confirm-danger>
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="al-btn al-btn-sm pi-eliminar"><i class="fas fa-trash-alt"></i> Eliminar</button>
                        </form>
                    </div>
                </div>
            </article>
        @empty
            <div class="al-card"><div class="al-vacio"><i class="fas fa-project-diagram"></i>Todavía no hay Proyectos Integradores. Crea el primero con "Nuevo proyecto".</div></div>
        @endforelse
    </div>

    @include('admin.curso.silabos.proyectos._estilos')
@endsection
