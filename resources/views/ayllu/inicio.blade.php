@extends('ayllu.layout')

@section('ayllu_contenido')
<div class="row">
    {{-- Columna Principal (Muro Comunitario, Comunicados Oficiales y Minkarikuy) --}}
    <div class="col-lg-8">
        {{-- Aviso Pedagógico de Separación de Planos --}}
        <div class="ayllu-plano-banner ayllu-plano-social">
            <div>
                <i class="fas fa-seedling mr-1" style="color:#9c6b37;"></i>
                <strong>Plano 1 · Comunidad y Diálogo de Saberes:</strong>
                Este espacio es para aprender juntos, compartir recursos y anunciar novedades. Las evaluaciones formativas están en cada curso y tus notas oficiales son estrictamente privadas.
            </div>
        </div>

        {{-- Evento Minkarikuy Activo (Reutilizado de tabla minkarikuys) --}}
        @if($minkarikuy)
            <div class="ayllu-card" style="border-left: 5px solid #832128;">
                <div class="ayllu-card-body py-3 d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.75rem;">
                    <div>
                        <span class="badge badge-danger mb-1"><i class="fas fa-fire mr-1"></i>Minkarikuy Activo · Encuentro Institucional</span>
                        <h5 class="font-weight-bold mb-1" style="color: var(--ayllu-text);">{{ $minkarikuy->nombre }}</h5>
                        <small class="text-muted"><i class="far fa-clock mr-1"></i>{{ $minkarikuy->cuando }}</small>
                    </div>
                    @if($minkarikuy->url)
                        <a href="{{ $minkarikuy->url }}" target="_blank" rel="noopener noreferrer" class="btn btn-sm text-white font-weight-bold px-3" style="background:#832128; border-radius:999px;">
                            Participar <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    @endif
                </div>
            </div>
        @endif

        {{-- Caja de Publicación Comunitaria (Respeta Decisión A3 sobre permisos en muro general) --}}
        <div class="ayllu-card">
            <div class="ayllu-card-header">
                <span class="font-weight-bold" style="font-size: 0.92rem;">
                    <i class="fas fa-feather-alt mr-1" style="color:#cd9244;"></i>
                    Compartir en Ayllu Puklla
                </span>
                <small class="text-muted">
                    @if($puedePublicarMuroGeneral)
                        Puedes publicar en toda la EESP, en tus cursos o en grupos
                    @else
                        Publica libremente en tus Comunidades de Curso, en tu Ciclo o en Grupos
                    @endif
                </small>
            </div>
            <div class="ayllu-card-body">
                <form action="{{ route('ayllu.publicaciones.store') }}" method="POST">
                    @csrf
                    <div class="form-row">
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">¿Dónde deseas compartir?</label>
                            <select name="ambito" id="selectAmbitoPub" class="form-control form-control-sm" onchange="actualizarSelectorDestino(this.value)">
                                @if($puedePublicarMuroGeneral)
                                    <option value="institucional">🌐 Muro General EESP Pukllasunchis</option>
                                @endif
                                @if($cursos->isNotEmpty())
                                    <option value="curso">📚 Comunidad de uno de mis Cursos</option>
                                @endif
                                @if($ciclo)
                                    <option value="ciclo">🎓 Comunidad de mi Ciclo ({{ $ciclo->nombre }})</option>
                                @endif
                                <option value="grupo">🤝 Grupo / Ayllu Temático</option>
                            </select>
                        </div>
                        <div class="form-group col-md-6 mb-2">
                            <label class="small font-weight-bold text-muted mb-1">Tipo de aporte</label>
                            <select name="tipo" class="form-control form-control-sm">
                                <option value="reflexion">Rimay · Reflexión o Crónica</option>
                                <option value="recurso">Yachay · Recurso Pedagógico</option>
                                <option value="practica_eib">Práctica EIB & Diálogo de Saberes</option>
                                <option value="pregunta">Tapukuy · Consulta Académica</option>
                                <option value="minka">Mink’a · Convocatoria / Iniciativa</option>
                                @if($puedePublicarMuroGeneral)
                                    <option value="anuncio">Willakuy · Anuncio Institucional</option>
                                @endif
                            </select>
                        </div>
                    </div>

                    <div id="wrapSelectCurso" class="form-group mb-2" style="display: {{ (!$puedePublicarMuroGeneral && $cursos->isNotEmpty()) ? 'block' : 'none' }};">
                        <label class="small font-weight-bold text-muted mb-1">Selecciona el Curso</label>
                        <select name="curso_id" class="form-control form-control-sm">
                            @foreach($cursos as $c)
                                <option value="{{ $c->id }}">{{ $c->nombre }} ({{ $c->ciclo?->nombre }})</option>
                            @endforeach
                        </select>
                    </div>

                    <div id="wrapSelectGrupo" class="form-group mb-2" style="display: none;">
                        <label class="small font-weight-bold text-muted mb-1">Selecciona el Grupo / Ayllu</label>
                        <select name="ayllu_grupo_id" class="form-control form-control-sm">
                            @foreach($grupos as $g)
                                <option value="{{ $g->id }}">{{ $g->nombre }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <input type="text" name="titulo" class="form-control form-control-sm" placeholder="Título breve de tu publicación (opcional)" maxlength="180">
                    </div>

                    <div class="form-group mb-2">
                        <textarea name="contenido" rows="3" class="form-control form-control-sm" placeholder="Comparte un aprendizaje, recurso, pregunta o reflexión con la comunidad..." required maxlength="5000"></textarea>
                    </div>

                    <div class="collapse" id="opcionesAvanzadasPub">
                        <div class="form-group mb-2">
                            <input type="text" name="resumen_quechua" class="form-control form-control-sm" placeholder="Runasimipi (Saludo o síntesis en quechua/lengua originaria - opcional)" maxlength="1000">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6 mb-2">
                                <input type="url" name="enlace_externo" class="form-control form-control-sm" placeholder="https://... Enlace a Drive, video o documento">
                            </div>
                            <div class="form-group col-md-6 mb-2">
                                <input type="text" name="etiquetas_texto" class="form-control form-control-sm" placeholder="Etiquetas separadas por coma: EIB, Quechua, Taq'e">
                            </div>
                        </div>
                    </div>

                    <div class="d-flex align-items-center justify-content-between flex-wrap mt-2">
                        <button class="btn btn-sm btn-link text-muted p-0 font-weight-bold" type="button" data-toggle="collapse" data-target="#opcionesAvanzadasPub">
                            <i class="fas fa-language mr-1" style="color:#cd9244;"></i> + Añadir síntesis en Quechua / Enlace / Etiquetas
                        </button>
                        <button type="submit" class="btn btn-sm text-white font-weight-bold px-4" style="background: #cd9244; border-radius: 999px;">
                            <i class="fas fa-paper-plane mr-1"></i> Publicar en Comunidad
                        </button>
                    </div>
                </form>
            </div>
        </div>

        {{-- Filtros del Feed de Inicio --}}
        <div class="d-flex align-items-center justify-content-between flex-wrap mb-3" style="gap: 0.5rem;">
            <div class="btn-group btn-group-sm flex-wrap" role="group" aria-label="Filtrar publicaciones">
                <a href="{{ route('ayllu.inicio', ['ambito' => 'todos']) }}" class="btn {{ $filtroAmbito === 'todos' ? 'btn-dark' : 'btn-outline-secondary' }}" style="border-radius: 999px 0 0 999px;">
                    Todo mi Ayllu
                </a>
                <a href="{{ route('ayllu.inicio', ['ambito' => 'institucional']) }}" class="btn {{ $filtroAmbito === 'institucional' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Anuncios EESP
                </a>
                <a href="{{ route('ayllu.inicio', ['ambito' => 'mis-cursos']) }}" class="btn {{ $filtroAmbito === 'mis-cursos' ? 'btn-dark' : 'btn-outline-secondary' }}">
                    Mis Cursos
                </a>
                <a href="{{ route('ayllu.inicio', ['ambito' => 'grupos']) }}" class="btn {{ $filtroAmbito === 'grupos' ? 'btn-dark' : 'btn-outline-secondary' }}" style="border-radius: 0 999px 999px 0;">
                    Grupos / Talleres
                </a>
            </div>
            <small class="text-muted"><i class="fas fa-shield-alt mr-1"></i>Moderación comunitaria activa</small>
        </div>

        {{-- Listado de Publicaciones --}}
        @forelse($publicaciones as $pub)
            @include('ayllu.partials.publicacion_card', ['pub' => $pub])
        @empty
            <div class="ayllu-card">
                <div class="ayllu-card-body text-center py-5">
                    <i class="fas fa-seedling fa-2x mb-2" style="color:#cd9244;"></i>
                    <h6 class="font-weight-bold">Aún no hay publicaciones en este filtro</h6>
                    <p class="text-muted small mb-0">Sé la primera persona en compartir un recurso, reflexión o pregunta con tu comunidad.</p>
                </div>
            </div>
        @endforelse

        <div class="mt-3">
            {{ $publicaciones->links() }}
        </div>
    </div>

    {{-- Columna Lateral Derecha (Comunicados Oficiales, Mis Cursos con Sílabo Vivo, Proyecto Anual y Bóveda Privada) --}}
    <div class="col-lg-4">
        {{-- 1. Comunicados Institucionales Oficiales (Reutilizados de tabla comunicados) --}}
        <div class="ayllu-card">
            <div class="ayllu-card-header">
                <span class="font-weight-bold" style="font-size: 0.9rem;">
                    <i class="fas fa-bullhorn mr-1 text-warning"></i> Comunicados Oficiales
                </span>
                @if(Route::has('alumno.comunicados'))
                    <a href="{{ route('alumno.comunicados') }}" class="small font-weight-bold" style="color:#9c6b37;">Ver todos</a>
                @endif
            </div>
            <div class="ayllu-card-body py-2">
                @forelse($comunicados as $com)
                    <div class="py-2 {{ !$loop->last ? 'border-bottom' : '' }}">
                        <div class="font-weight-bold" style="font-size: 0.85rem;">{{ $com->titulo }}</div>
                        <small class="text-muted d-block">
                            {{ $com->fecha_publicacion?->format('d/m/Y') ?? $com->created_at?->format('d/m/Y') }}
                            @if($com->descripcion)
                                &middot; {{ \Illuminate\Support\Str::limit($com->descripcion, 70) }}
                            @endif
                        </small>
                    </div>
                @empty
                    <p class="text-muted small mb-0 py-2">No hay comunicados recientes registrados.</p>
                @endforelse
            </div>
        </div>

        {{-- 2. Mis Cursos como Comunidades con Sílabo Vivo --}}
        <div class="ayllu-card">
            <div class="ayllu-card-header">
                <span class="font-weight-bold" style="font-size: 0.9rem;">
                    <i class="fas fa-book-reader mr-1" style="color:#0e9467;"></i> Mis Comunidades de Curso
                </span>
                <span class="badge badge-pill badge-light">{{ $cursos->count() }}</span>
            </div>
            <div class="ayllu-card-body py-2">
                @forelse($cursos->take(6) as $c)
                    @php
                        $tieneSilabo = $c->relacionsilabo !== null;
                        $unidadesCount = $c->relacionsilabo?->unidades?->count() ?? 0;
                        $tieneClassroom = !empty($c->classroom);
                    @endphp
                    <div class="py-2 {{ !$loop->last ? 'border-bottom' : '' }} d-flex align-items-center justify-content-between" style="gap: 0.5rem;">
                        <div style="min-width: 0;">
                            <a href="{{ route('ayllu.cursos.show', $c->id) }}" class="font-weight-bold d-block text-truncate" style="font-size: 0.86rem; color: var(--ayllu-text);">
                                {{ $c->nombre }}
                            </a>
                            <small class="text-muted d-block">
                                {{ $c->ciclo?->nombre ?? 'Ciclo' }}
                                @if($tieneSilabo)
                                    &middot; <span class="text-success font-weight-bold">Sílabo Vivo ({{ $unidadesCount }} unid.)</span>
                                @else
                                    &middot; <span>Sílabo en preparación</span>
                                @endif
                                @if($tieneClassroom)
                                    &middot; <i class="fab fa-google text-primary" title="Enlace Classroom disponible"></i>
                                @endif
                            </small>
                        </div>
                        <a href="{{ route('ayllu.cursos.show', $c->id) }}" class="btn btn-sm btn-outline-secondary" style="border-radius:999px; font-size:0.74rem;">
                            Entrar
                        </a>
                    </div>
                @empty
                    <p class="text-muted small mb-0 py-2">No tienes cursos asignados en este momento.</p>
                @endforelse
            </div>
        </div>

        {{-- 3. Proyecto Anual Integrador (Taq'e) --}}
        <div class="ayllu-card" style="border-left: 4px solid #2563eb;">
            <div class="ayllu-card-header">
                <span class="font-weight-bold" style="font-size: 0.9rem;">
                    <i class="fas fa-project-diagram mr-1 text-primary"></i> Proyecto Anual Integrador
                </span>
                <a href="{{ route('ayllu.proyecto') }}" class="small font-weight-bold">Articular</a>
            </div>
            <div class="ayllu-card-body">
                @if($proyectoIntegrador)
                    <h6 class="font-weight-bold mb-1" style="font-size: 0.9rem;">{{ $proyectoIntegrador->nombre }}</h6>
                    @if($proyectoIntegrador->producto)
                        <small class="d-block text-muted mb-2">
                            <strong>Producto anual:</strong> {{ $proyectoIntegrador->producto }}
                        </small>
                    @endif
                @else
                    <p class="text-muted small mb-2">Explora los Proyectos Integradores de cada ciclo y vincula los productos de tus cursos hacia Taq’e.</p>
                @endif
                <a href="{{ route('ayllu.proyecto') }}" class="btn btn-sm btn-outline-primary btn-block font-weight-bold" style="border-radius:999px;">
                    <i class="fas fa-flask mr-1"></i> Ver Productos de Curso y Portafolio
                </a>
            </div>
        </div>

        {{-- 4. Resumen Privado (Plano 3: Notas Oficiales, Asistencia y Trámites) --}}
        <div class="ayllu-card" style="border-left: 4px solid #832128;">
            <div class="ayllu-card-header">
                <span class="font-weight-bold" style="font-size: 0.9rem;">
                    <i class="fas fa-lock mr-1" style="color:#832128;"></i> Mi Espacio Privado
                </span>
                <span class="badge badge-light border">Solo visible para ti</span>
            </div>
            <div class="ayllu-card-body">
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span class="text-muted">Asistencia registrada:</span>
                    <strong>
                        @if($resumenPrivado['porcentaje_asistencia'] !== null)
                            {{ $resumenPrivado['porcentaje_asistencia'] }}% ({{ $resumenPrivado['asistencias_efectivas'] }}/{{ $resumenPrivado['asistencias_total'] }})
                        @else
                            Sin registros aún
                        @endif
                    </strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 small">
                    <span class="text-muted">Estado de matrícula:</span>
                    <strong class="text-right">{{ $resumenPrivado['matricula_estado'] }}</strong>
                </div>
                <div class="d-flex justify-content-between align-items-center mb-3 small">
                    <span class="text-muted">Evidencias en Portafolio EIB:</span>
                    <strong>{{ $resumenPrivado['portafolio_count'] }} curadas</strong>
                </div>
                <a href="{{ route('ayllu.privado') }}" class="btn btn-sm text-white btn-block font-weight-bold" style="background:#832128; border-radius:999px;">
                    <i class="fas fa-shield-alt mr-1"></i> Abrir Notas Oficiales y Trámites
                </a>
            </div>
        </div>
    </div>
</div>

<script>
    function actualizarSelectorDestino(valor) {
        var wrapCurso = document.getElementById('wrapSelectCurso');
        var wrapGrupo = document.getElementById('wrapSelectGrupo');
        if (wrapCurso) wrapCurso.style.display = (valor === 'curso') ? 'block' : 'none';
        if (wrapGrupo) wrapGrupo.style.display = (valor === 'grupo') ? 'block' : 'none';
    }
</script>
@endsection
