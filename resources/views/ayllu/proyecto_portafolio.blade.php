@extends('ayllu.layout')

@section('ayllu_contenido')
{{-- Encabezado del Proyecto Anual Integrador + Portafolio Docente EIB --}}
<div class="ayllu-card p-4" style="border-top: 4px solid #33445a;">
    <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 0.75rem;">
        <div>
            <span class="ayllu-badge ayllu-badge-taqe mb-2">
                <i class="fas fa-seedling mr-1"></i> Articulación Curricular EIB · Taq'e
            </span>
            <h2 class="h4 font-weight-bold mb-1" style="color: #161c22;">
                Proyecto Anual Integrador &amp; Portafolio Docente EIB
            </h2>
            <p class="small text-muted mb-0">
                Aquí convergen los productos de tus cursos hacia el Proyecto Anual Integrador de tu ciclo y tu Portafolio Docente de Formación Inicial / Continua.
            </p>
        </div>
        <div class="d-flex flex-wrap" style="gap: 0.45rem;">
            <a href="{{ route('taqe.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 999px;">
                <i class="fas fa-folder-tree mr-1"></i> Abrir Repositorio Taq'e Oficial
            </a>
            <a href="{{ route('ayllu.perfil') }}" class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 999px;">
                <i class="fas fa-id-badge mr-1"></i> Ver Mi Perfil EIB
            </a>
        </div>
    </div>
</div>

{{-- Ficha del Proyecto Integrador del Ciclo --}}
<div class="ayllu-card p-4">
    <h3 class="h6 font-weight-bold mb-3" style="color: #161c22;">
        <i class="fas fa-compass text-warning mr-1"></i>
        1. Proyecto Integrador de Ciclo ({{ $etiquetaCiclo ?: 'Articulación Institucional' }})
    </h3>

    @if($proyectoActual)
        <div class="p-3 rounded mb-3" style="background: #f5f2ed; border-left: 4px solid #c78b3a;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                <h4 class="h6 font-weight-bold mb-0" style="color: #161c22;">
                    {{ $proyectoActual->nombre }}
                </h4>
                @if($proyectoActual->producto)
                    <span class="badge badge-warning text-dark">
                        Producto anual: {{ $proyectoActual->producto }}
                    </span>
                @endif
            </div>
            <p class="small mb-2" style="color: #36414b; white-space: pre-line;">{{ $proyectoActual->descripcion ?? 'Proyecto integrador anual articulado con la práctica preprofesional e investigación formativa.' }}</p>
            @if($proyectoActual->proposito)
                <div class="small text-muted">
                    <strong>Propósito pedagógico:</strong> {{ $proyectoActual->proposito }}
                </div>
            @endif
        </div>
    @endif

    {{-- Mapa de cómo tributa cada curso al Proyecto Integrador --}}
    <h4 class="small font-weight-bold text-uppercase text-muted mb-2">
        ¿Cómo tributan mis cursos actuales al Proyecto Anual Integrador?
    </h4>
    <div class="row">
        @forelse($cursos as $cursoItem)
            @php
                $silaboCurso = $cursoItem->relacionsilabo;
            @endphp
            <div class="col-md-6 mb-3">
                <div class="p-3 rounded h-100" style="border: 1px solid #e6e0d6; background: #fff;">
                    <div class="d-flex justify-content-between align-items-start mb-1">
                        <a href="{{ route('ayllu.cursos.show', $cursoItem->id) }}" class="font-weight-bold small" style="color: #161c22;">
                            {{ $cursoItem->nombre }}
                        </a>
                        <span class="badge badge-light border" style="font-size: 0.68rem;">
                            {{ $cursoItem->ciclo?->nombre ?? 'Curso' }}
                        </span>
                    </div>
                    <div class="small text-muted mb-2">
                        <strong>Producto de curso:</strong>
                        {{ $silaboCurso?->producto_curso ?: 'Definido en el sílabo vivo del curso' }}
                    </div>
                    @if($silaboCurso?->proyecto_integrador_nombre || $silaboCurso?->descripcion_proyecto_integrador)
                        <div class="small p-2 rounded" style="background: #f5f2ed; color: #33445a; font-size: 0.76rem;">
                            <i class="fas fa-link mr-1 text-warning"></i>
                            {{ \Illuminate\Support\Str::limit($silaboCurso->descripcion_proyecto_integrador ?: $silaboCurso->proyecto_integrador_nombre, 140) }}
                        </div>
                    @endif
                    <div class="mt-2 d-flex justify-content-end">
                        <a href="{{ route('ayllu.cursos.show', ['curso' => $cursoItem->id, 'tab' => 'formativa']) }}" class="small font-weight-bold" style="color: #9e3d22; font-size: 0.76rem;">
                            Subir avance formativo <i class="fas fa-arrow-right ml-1"></i>
                        </a>
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <p class="small text-muted mb-0">No hay cursos asignados en este momento.</p>
            </div>
        @endforelse
    </div>
</div>

{{-- Mis Grupos Taq'e y Evidencias Articuladas --}}
<div class="ayllu-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <h3 class="h6 font-weight-bold mb-0" style="color: #161c22;">
            <i class="fas fa-users-cog text-primary mr-1"></i>
            2. Equipos de Investigación / Proyecto Taq'e y Aportes desde Cursos
        </h3>
        <a href="{{ route('taqe.index') }}" class="small font-weight-bold" style="color: #33445a;">
            Gestionar grupos en Taq'e <i class="fas fa-external-link-alt ml-1"></i>
        </a>
    </div>

    @if($taqeGrupos->isNotEmpty())
        <div class="row mb-3">
            @foreach($taqeGrupos as $tg)
                <div class="col-md-6 mb-2">
                    <div class="p-3 rounded" style="background: #eef4fa; border-left: 4px solid #33445a;">
                        <div class="font-weight-bold small" style="color: #161c22;">
                            {{ $tg->nombre_grupo }}
                        </div>
                        <div class="text-muted" style="font-size: 0.76rem;">
                            {{ $tg->titulo_investigacion ?: 'Investigación / Proyecto Integrador en curso' }}
                        </div>
                        <div class="mt-2">
                            <a href="{{ route('taqe.show', $tg->id) }}" class="btn btn-xs btn-sm btn-primary py-0 px-2" style="font-size: 0.73rem; border-radius: 999px;">
                                Abrir espacio de trabajo Taq'e
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    <h4 class="small font-weight-bold text-uppercase text-muted mb-2">
        Avances de cursos que tributan al Proyecto Integrador ({{ $aportesCursos->count() }})
    </h4>

    @forelse($aportesCursos as $aporte)
        <div class="p-3 rounded mb-2" style="border: 1px solid #e6e0d6; background: #fff;">
            <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                <div>
                    <span class="ayllu-badge ayllu-badge-taqe mr-1">Aporte a Proyecto Integrador</span>
                    <strong class="small" style="color: #161c22;">{{ $aporte->titulo }}</strong>
                </div>
                <span class="small text-muted" style="font-size: 0.74rem;">
                    Curso: <a href="{{ route('ayllu.cursos.show', $aporte->curso_id) }}" class="font-weight-bold">{{ $aporte->curso?->nombre }}</a>
                    · {{ $aporte->created_at?->format('d/m/Y') }}
                </span>
            </div>
            <p class="small mb-2 text-muted">{{ $aporte->descripcion_reflexion }}</p>
            <div class="d-flex flex-wrap justify-content-between align-items-center">
                <div class="small">
                    <span class="badge badge-light border">
                        Autoevaluación: {{ $nivelesFormativos[$aporte->autoevaluacion_nivel]['label'] ?? $aporte->autoevaluacion_nivel }}
                    </span>
                    @if($aporte->taqeGrupo)
                        <span class="badge badge-info ml-1">
                            Equipo Taq'e: {{ $aporte->taqeGrupo->nombre_grupo }}
                        </span>
                    @endif
                </div>
                @if($aporte->enlace_evidencia)
                    <a href="{{ $aporte->enlace_evidencia }}" target="_blank" rel="noopener" class="small font-weight-bold" style="color: #9e3d22;">
                        <i class="fas fa-external-link-alt mr-1"></i> Ver evidencia
                    </a>
                @endif
            </div>
        </div>
    @empty
        <div class="p-3 rounded text-center" style="background: #f5f2ed;">
            <p class="small text-muted mb-0">
                Aún no has marcado avances formativos como aporte al Proyecto Integrador. Puedes hacerlo desde la pestaña <strong>Evaluación Formativa</strong> de cualquiera de tus cursos.
            </p>
        </div>
    @endforelse
</div>

{{-- Portafolio Docente EIB del Estudiante --}}
<div class="ayllu-card p-4">
    <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
        <div>
            <h3 class="h6 font-weight-bold mb-0" style="color: #161c22;">
                <i class="fas fa-book-reader text-success mr-1"></i>
                3. Mi Portafolio Docente EIB (Evidencias y Reflexiones Curadas)
            </h3>
            <p class="small text-muted mb-0">
                Selección longitudinal de tus mejores producciones pedagógicas, reflexiones interculturales y retroalimentaciones docentes.
            </p>
        </div>
        <span class="ayllu-badge ayllu-badge-formativo">
            {{ $miPortafolio->count() }} evidencias curadas
        </span>
    </div>

    @forelse($miPortafolio as $item)
        <div class="p-3 rounded mb-3" style="border: 1px solid #d5e6dc; background: #fbfdfc;">
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-1">
                <div>
                    <h4 class="h6 font-weight-bold mb-0" style="color: #161c22;">
                        {{ $item->titulo }}
                    </h4>
                    <div class="small text-muted">
                        Curso: <strong>{{ $item->curso?->nombre }}</strong>
                        @if($item->curso?->ciclo)
                            · {{ $item->curso->ciclo->nombre }}
                        @endif
                        · {{ $item->created_at?->format('d/m/Y') }}
                    </div>
                </div>
                <form action="{{ route('ayllu.entregas.portafolio', $item->id) }}" method="POST">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-secondary py-0 px-2" style="font-size: 0.74rem; border-radius: 999px;">
                        <i class="fas fa-bookmark mr-1 text-success"></i> Retirar del portafolio
                    </button>
                </form>
            </div>

            <p class="small mb-2" style="color: #2f3b44;">{{ $item->descripcion_reflexion }}</p>

            @if($item->reflexion_portafolio)
                <div class="p-2 rounded mb-2 small" style="background: #eef6f1; color: #205239;">
                    <strong><i class="fas fa-feather-alt mr-1"></i> Reflexión metacognitiva para mi Portafolio EIB:</strong>
                    {{ $item->reflexion_portafolio }}
                </div>
            @endif

            @if($item->enlace_evidencia)
                <div class="mb-2">
                    <a href="{{ $item->enlace_evidencia }}" target="_blank" rel="noopener" class="btn btn-sm btn-outline-primary py-1 px-3" style="border-radius: 999px; font-size: 0.76rem;">
                        <i class="fas fa-external-link-alt mr-1"></i> Abrir documento / evidencia en Drive
                    </a>
                </div>
            @endif

            @if($item->retroalimentaciones->isNotEmpty())
                <div class="mt-2 pt-2 border-top">
                    <div class="small font-weight-bold text-muted mb-1">Acompañamiento docente recibido:</div>
                    @foreach($item->retroalimentaciones as $retro)
                        <div class="small p-2 rounded mb-1" style="background: #f5f2ed;">
                            <strong>{{ $retro->autor?->name }}:</strong> {{ $retro->comentario_general }}
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    @empty
        <div class="p-4 rounded text-center" style="background: #f5f2ed;">
            <i class="fas fa-folder-open fa-2x text-muted mb-2"></i>
            <p class="small text-muted mb-0">
                Tu Portafolio Docente EIB aún no tiene evidencias seleccionadas. Al registrar una entrega formativa en tus cursos, activa la casilla <strong>«Incluir en mi Portafolio Docente EIB»</strong>.
            </p>
        </div>
    @endforelse
</div>
@endsection
