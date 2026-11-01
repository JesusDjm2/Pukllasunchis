@extends('ayllu.layout')

@section('ayllu_contenido')
{{-- Selector rápido para cambiar entre mis Comunidades de Curso --}}
@if($cursos->count() > 1)
    <div class="d-flex align-items-center flex-wrap mb-3" style="gap: 0.4rem;">
        <span class="small font-weight-bold text-muted mr-1"><i class="fas fa-exchange-alt mr-1"></i>Mis cursos:</span>
        @foreach($cursos as $cItem)
            <a href="{{ route('ayllu.cursos.show', $cItem->id) }}"
               class="btn btn-sm {{ $cItem->id === $cursoActual->id ? 'btn-dark font-weight-bold' : 'btn-outline-secondary' }}"
               style="border-radius: 999px; font-size: 0.78rem;">
                {{ \Illuminate\Support\Str::limit($cItem->nombre, 32) }}
            </a>
        @endforeach
    </div>
@endif

{{-- Cabecera de la Comunidad del Curso --}}
<div class="ayllu-card" style="border-left: 5px solid #0e9467;">
    <div class="ayllu-card-body">
        <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-between" style="gap: 1rem;">
            <div>
                <span class="badge badge-success mb-1">
                    {{ $cursoActual->ciclo?->programa?->nombre ?? 'Programa Académico' }} &middot; {{ $cursoActual->ciclo?->nombre ?? 'Ciclo' }}
                </span>
                <h3 class="font-weight-bold mb-1" style="font-size: 1.3rem;">
                    <i class="fas fa-book-open mr-1" style="color:#0e9467;"></i>
                    {{ $cursoActual->nombre }}
                </h3>
                <div class="small text-muted">
                    <strong>Docente(s) formador(es):</strong>
                    @if($cursoActual->docentes->isNotEmpty())
                        {{ $cursoActual->docentes->pluck('nombre')->implode(', ') }}
                    @else
                        Por asignar
                    @endif
                    @if($cursoActual->horas || $cursoActual->creditos)
                        &nbsp;&middot;&nbsp; {{ $cursoActual->horas ?? '-' }} hrs / {{ $cursoActual->creditos ?? '-' }} créditos
                    @endif
                </div>
            </div>

            {{-- Tarjeta Conector Gradual Google Classroom (Restricción #5) --}}
            <div class="p-2 rounded border" style="background: var(--ayllu-surface-soft); min-width: 240px;">
                <div class="d-flex align-items-center justify-content-between mb-1">
                    <span class="small font-weight-bold"><i class="fab fa-google mr-1 text-primary"></i>Google Classroom</span>
                    @if($cursoActual->classroom)
                        <span class="badge badge-success" style="font-size:0.65rem;">Nivel 1 · Vinculado</span>
                    @else
                        <span class="badge badge-secondary" style="font-size:0.65rem;">Sin enlace aún</span>
                    @endif
                </div>
                @if($cursoActual->classroom)
                    <div class="d-flex align-items-center justify-content-between flex-wrap" style="gap: 0.4rem;">
                        @if($cursoActual->clave)
                            <small class="text-muted">Clave: <code>{{ $cursoActual->clave }}</code></small>
                        @endif
                        <a href="{{ $cursoActual->classroom }}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-primary font-weight-bold px-2 py-1" style="font-size:0.74rem; border-radius:999px;">
                            Abrir Classroom <i class="fas fa-external-link-alt ml-1"></i>
                        </a>
                    </div>
                @else
                    <small class="text-muted d-block" style="font-size:0.74rem;">
                        La comunidad funciona 100% en Ayllu Puklla. El docente puede añadir el enlace de Classroom cuando lo requiera.
                    </small>
                @endif
            </div>
        </div>

        {{-- Pestañas Internas de la Comunidad del Curso --}}
        <hr class="my-3">
        <div class="nav nav-pills" role="tablist" style="gap: 0.4rem;">
            <a href="{{ route('ayllu.cursos.show', ['curso' => $cursoActual->id, 'tab' => 'comunidad']) }}"
               class="btn btn-sm {{ $tabActiva === 'comunidad' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}"
               style="border-radius: 999px;">
                <i class="fas fa-comments mr-1"></i> 1. Muro y Recursos ({{ $publicacionesCurso->count() }})
            </a>
            <a href="{{ route('ayllu.cursos.show', ['curso' => $cursoActual->id, 'tab' => 'silabo']) }}"
               class="btn btn-sm {{ $tabActiva === 'silabo' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}"
               style="border-radius: 999px;">
                <i class="fas fa-compass mr-1"></i> 2. Sílabo Vivo
            </a>
            <a href="{{ route('ayllu.cursos.show', ['curso' => $cursoActual->id, 'tab' => 'formativa']) }}"
               class="btn btn-sm {{ $tabActiva === 'formativa' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}"
               style="border-radius: 999px;">
                <i class="fas fa-seedling mr-1"></i> 3. Evaluación y Retroalimentación Formativa ({{ $entregasFormativas->count() }})
            </a>
            <a href="{{ route('ayllu.cursos.show', ['curso' => $cursoActual->id, 'tab' => 'proyecto']) }}"
               class="btn btn-sm {{ $tabActiva === 'proyecto' ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }}"
               style="border-radius: 999px;">
                <i class="fas fa-project-diagram mr-1"></i> 4. Producto hacia el Proyecto Anual (Taq’e)
            </a>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     PESTAÑA 1: COMUNIDAD Y RECURSOS DEL CURSO
     ══════════════════════════════════════════════════════════════════ --}}
@if($tabActiva === 'comunidad')
    <div class="row">
        <div class="col-lg-8">
            <div class="ayllu-card">
                <div class="ayllu-card-header">
                    <span class="font-weight-bold small"><i class="fas fa-pen mr-1" style="color:#0e9467;"></i>Publicar en la Comunidad de {{ $cursoActual->nombre }}</span>
                </div>
                <div class="ayllu-card-body">
                    <form action="{{ route('ayllu.publicaciones.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ambito" value="curso">
                        <input type="hidden" name="curso_id" value="{{ $cursoActual->id }}">
                        <div class="form-row">
                            <div class="form-group col-md-5 mb-2">
                                <select name="tipo" class="form-control form-control-sm">
                                    <option value="reflexion">Reflexión / Avance de sesión</option>
                                    <option value="recurso">Material / Lectura / Guía</option>
                                    <option value="pregunta">Consulta para el curso</option>
                                    <option value="practica_eib">Diálogo de saberes EIB</option>
                                    @if($esDocenteDelCurso)
                                        <option value="anuncio">Anuncio Docente del Curso</option>
                                    @endif
                                </select>
                            </div>
                            <div class="form-group col-md-7 mb-2">
                                <input type="text" name="titulo" class="form-control form-control-sm" placeholder="Título del aporte o tema de la sesión">
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <textarea name="contenido" rows="2" class="form-control form-control-sm" placeholder="Comparte dudas, hallazgos de lectura, acuerdos de clase o materiales..." required></textarea>
                        </div>
                        <div class="form-row align-items-center">
                            <div class="col-md-12 mb-2">
                                <input type="url" name="enlace_externo" class="form-control form-control-sm" placeholder="Enlace opcional a Drive / Canva / Documento (https://...)">
                            </div>
                        </div>
                        <div class="d-flex flex-wrap justify-content-between align-items-center mt-1 pt-2" style="border-top: 1px dashed #e6e0d6; gap: 0.5rem;">
                            <div class="custom-control custom-checkbox">
                                <input type="checkbox" class="custom-control-input" id="chkPoliticaMenoresCurso" name="confirmacion_politica_menores" value="1" checked required>
                                <label class="custom-control-label text-muted" for="chkPoliticaMenoresCurso" style="font-size: 0.72rem;">
                                    <i class="fas fa-child text-warning mr-1"></i>Confirmo que el enlace externo no expone rostros ni datos identificables de menores sin consentimiento.
                                </label>
                            </div>
                            <button type="submit" class="btn btn-sm btn-success font-weight-bold px-3" style="border-radius:999px;">
                                <i class="fas fa-paper-plane mr-1"></i> Compartir en el Curso
                            </button>
                        </div>
                    </form>
                </div>
            </div>

            @forelse($publicacionesCurso as $pub)
                @include('ayllu.partials.publicacion_card', ['pub' => $pub])
            @empty
                <div class="ayllu-card">
                    <div class="ayllu-card-body text-center py-4">
                        <p class="text-muted small mb-0">Aún no hay publicaciones en este curso. Inicia el diálogo compartiendo una pregunta o recurso.</p>
                    </div>
                </div>
            @endforelse
        </div>

        <div class="col-lg-4">
            <div class="ayllu-card">
                <div class="ayllu-card-header">
                    <span class="font-weight-bold small"><i class="fas fa-info-circle mr-1"></i>Síntesis del Curso</span>
                </div>
                <div class="ayllu-card-body small">
                    @if($silabo && $silabo->sumilla)
                        <p class="mb-2"><strong>Sumilla:</strong> {{ \Illuminate\Support\Str::limit(strip_tags($silabo->sumilla), 260) }}</p>
                    @elseif($cursoActual->sumilla)
                        <p class="mb-2"><strong>Sumilla:</strong> {{ \Illuminate\Support\Str::limit(strip_tags($cursoActual->sumilla), 260) }}</p>
                    @endif
                    @if($silabo && $silabo->producto_curso)
                        <div class="p-2 rounded mb-2" style="background: var(--ayllu-oro-suave);">
                            <strong><i class="fas fa-gift mr-1" style="color:#9c6b37;"></i>Producto Final del Curso:</strong>
                            <div>{{ $silabo->producto_curso }}</div>
                        </div>
                    @endif
                    <a href="{{ route('ayllu.cursos.show', ['curso' => $cursoActual->id, 'tab' => 'silabo']) }}" class="btn btn-sm btn-outline-primary btn-block font-weight-bold" style="border-radius:999px;">
                        Explorar Sílabo Vivo Completo
                    </a>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     PESTAÑA 2: SÍLABO VIVO (Conectado a silabos, unidades y rubricas)
     ══════════════════════════════════════════════════════════════════ --}}
@if($tabActiva === 'silabo')
    <div class="ayllu-card">
        <div class="ayllu-card-header">
            <div>
                <span class="font-weight-bold"><i class="fas fa-compass mr-1 text-primary"></i>Sílabo Vivo del Curso</span>
                @if($silabo)
                    <span class="badge badge-success ml-2">Semestre {{ $silabo->periodo ?? ($periodoActual->nombre ?? 'Vigente') }}</span>
                @endif
            </div>
            @if($silabo)
                <a href="{{ route('silabo.pdf', $silabo->id) }}" target="_blank" class="btn btn-sm btn-outline-danger font-weight-bold" style="border-radius:999px;">
                    <i class="fas fa-file-pdf mr-1"></i> Descargar PDF Oficial
                </a>
            @endif
        </div>
        <div class="ayllu-card-body">
            @if($silabo)
                <div class="row mb-3">
                    <div class="col-md-6 mb-2">
                        <div class="p-3 rounded border h-100" style="background: var(--ayllu-surface-soft);">
                            <h6 class="font-weight-bold text-primary mb-1"><i class="fas fa-bullseye mr-1"></i>Producto del Curso</h6>
                            <p class="small mb-2">{{ $silabo->producto_curso ?: 'Definido en las unidades de aprendizaje.' }}</p>
                            <h6 class="font-weight-bold text-primary mb-1"><i class="fas fa-link mr-1"></i>Vinculación con el Proyecto Integrador</h6>
                            <p class="small mb-0">{{ $silabo->vinculacion_pi ?: ($silabo->proyecto_integrador ?: 'Articulado al Proyecto Anual del ciclo.') }}</p>
                        </div>
                    </div>
                    <div class="col-md-6 mb-2">
                        <div class="p-3 rounded border h-100" style="background: var(--ayllu-surface-soft);">
                            <h6 class="font-weight-bold text-success mb-1"><i class="fas fa-Hands-helping mr-1"></i>Modelos Metodológicos y Recursos</h6>
                            <p class="small mb-1"><strong>Metodología:</strong> {{ \Illuminate\Support\Str::limit(strip_tags($silabo->modelos_metodologicos), 180) ?: 'Diálogo de saberes, aprendizaje situado e investigación formativa.' }}</p>
                            <p class="small mb-0"><strong>Recursos:</strong> {{ \Illuminate\Support\Str::limit(strip_tags($silabo->recursos), 150) ?: 'Comunidad Ayllu Puklla, Biblioteca Virtual y trabajo de campo.' }}</p>
                        </div>
                    </div>
                </div>

                {{-- Unidades del Sílabo Vivo --}}
                <h5 class="font-weight-bold mb-3" style="font-size: 1rem;">
                    <i class="fas fa-layer-group mr-1" style="color:#cd9244;"></i> Unidades de Aprendizaje y Situaciones Auténticas ({{ $silabo->unidades->count() }})
                </h5>
                <div class="row">
                    @forelse($silabo->unidades as $idx => $unidad)
                        <div class="col-md-6 mb-3">
                            <div class="p-3 rounded border h-100" style="border-left: 4px solid #cd9244 !important;">
                                <div class="d-flex justify-content-between align-items-start mb-1">
                                    <span class="badge badge-warning text-dark">Unidad {{ $idx + 1 }}</span>
                                    <small class="text-muted">{{ $unidad->duracion }}</small>
                                </div>
                                <h6 class="font-weight-bold mb-2">{{ $unidad->titulo }}</h6>
                                @if($unidad->situacion_autentica)
                                    <p class="small mb-1"><strong>Situación auténtica:</strong> {{ $unidad->situacion_autentica }}</p>
                                @endif
                                @if($unidad->desempenos_especificos)
                                    <p class="small mb-1 text-muted"><strong>Desempeños:</strong> {{ \Illuminate\Support\Str::limit(strip_tags($unidad->desempenos_especificos), 160) }}</p>
                                @endif
                                @if($unidad->evidencia_proceso || $unidad->evidencia_final)
                                    <div class="p-2 rounded mt-2 small" style="background: var(--ayllu-oro-suave);">
                                        <strong>Evidencia esperada:</strong> {{ $unidad->evidencia_proceso ?: $unidad->evidencia_final }}
                                    </div>
                                @endif
                                <div class="mt-2 text-right">
                                    <a href="{{ route('ayllu.cursos.show', ['curso' => $cursoActual->id, 'tab' => 'formativa', 'unidad_id' => $unidad->id]) }}"
                                       class="btn btn-xs btn-outline-primary font-weight-bold" style="font-size:0.75rem; border-radius:999px;">
                                        <i class="fas fa-upload mr-1"></i> Entregar avance de esta unidad
                                    </a>
                                </div>
                            </div>
                        </div>
                    @empty
                        <div class="col-12">
                            <p class="text-muted small">El sílabo aún no tiene unidades detalladas cargadas.</p>
                        </div>
                    @endforelse
                </div>

                {{-- Rúbricas de Evaluación del Sílabo --}}
                @if($silabo->rubricas->isNotEmpty())
                    <h5 class="font-weight-bold mt-3 mb-2" style="font-size: 1rem;">
                        <i class="fas fa-table mr-1 text-info"></i> Rúbricas y Criterios Formativos del Curso
                    </h5>
                    <div class="table-responsive">
                        <table class="table table-sm table-bordered small">
                            <thead class="thead-light">
                                <tr>
                                    <th>Criterio / Capacidad</th>
                                    <th>En Inicio</th>
                                    <th>En Proceso</th>
                                    <th>Logrado</th>
                                    <th>Destacado</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($silabo->rubricas as $rub)
                                    <tr>
                                        <td class="font-weight-bold">{{ $rub->criterio }}</td>
                                        <td>{{ $rub->en_inicio }}</td>
                                        <td>{{ $rub->en_proceso }}</td>
                                        <td>{{ $rub->logrado }}</td>
                                        <td>{{ $rub->destacado }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                @endif
            @else
                <div class="text-center py-4">
                    <i class="fas fa-file-signature fa-2x text-muted mb-2"></i>
                    <p class="text-muted mb-0">El docente formador aún está estructurando el sílabo de este período.</p>
                </div>
            @endif
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     PESTAÑA 3: EVALUACIÓN Y RETROALIMENTACIÓN FORMATIVA (Plano 2)
     ══════════════════════════════════════════════════════════════════ --}}
@if($tabActiva === 'formativa')
    <div class="ayllu-plano-banner ayllu-plano-formativo">
        <div>
            <i class="fas fa-seedling mr-1 text-primary"></i>
            <strong>Plano 2 · Evaluación y Retroalimentación Formativa:</strong>
            Este espacio es de acompañamiento pedagógico continuo y diálogo de saberes entre estudiante y docente.
            <strong>No modifica ni reemplaza las calificaciones de las actas oficiales.</strong>
        </div>
        @if($esDocenteDelCurso && $docente)
            <a href="{{ $cursoActual->esPpd() ? route('competencias.calificar.ppd', [$docente->id, $cursoActual->id]) : route('competencias.calificar', [$docente->id, $cursoActual->id]) }}"
               class="btn btn-sm btn-outline-dark font-weight-bold" style="border-radius:999px;">
                <i class="fas fa-lock mr-1"></i> Ir al Registro Oficial de Notas
            </a>
        @endif
    </div>

    <div class="row">
        {{-- Formulario para subir avance formativo --}}
        <div class="col-lg-5">
            <div class="ayllu-card">
                <div class="ayllu-card-header">
                    <span class="font-weight-bold small"><i class="fas fa-upload mr-1 text-primary"></i>Registrar Avance / Evidencia Formativa</span>
                </div>
                <div class="ayllu-card-body">
                    <form action="{{ route('ayllu.cursos.entregas.store', $cursoActual->id) }}" method="POST">
                        @csrf
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Título del avance o producto</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" placeholder="Ej. Diario de campo reflexivo / Diseño de sesión EIB" required maxlength="200">
                        </div>

                        @if($silabo && $silabo->unidades->isNotEmpty())
                            <div class="form-group mb-2">
                                <label class="small font-weight-bold mb-1">Unidad del Sílabo Vivo</label>
                                <select name="unidad_id" class="form-control form-control-sm">
                                    <option value="">-- General del curso --</option>
                                    @foreach($silabo->unidades as $uItem)
                                        <option value="{{ $uItem->id }}" {{ (int) request('unidad_id') === $uItem->id ? 'selected' : '' }}>
                                            {{ $uItem->titulo }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        @endif

                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Reflexión pedagógica / Descripción del avance</label>
                            <textarea name="descripcion_reflexion" rows="3" class="form-control form-control-sm" placeholder="¿Qué aprendiste en este avance, cómo dialoga con la realidad EIB y qué dudas tienes para el docente?" required></textarea>
                        </div>

                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Enlace a tu documento / Drive / Canva (opcional)</label>
                            <input type="url" name="enlace_evidencia" class="form-control form-control-sm" placeholder="https://drive.google.com/...">
                        </div>

                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Autoevaluación formativa</label>
                            <select name="autoevaluacion_nivel" class="form-control form-control-sm">
                                @foreach($nivelesFormativos as $k => $niv)
                                    <option value="{{ $k }}" {{ $k === 'en_proceso' ? 'selected' : '' }}>{{ $niv['nombre'] }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="p-2 rounded border mb-2" style="background: var(--ayllu-surface-soft);">
                            <div class="custom-control custom-checkbox mb-1">
                                <input type="checkbox" class="custom-control-input" id="chkAportePi" name="es_aporte_proyecto_integrador" value="1">
                                <label class="custom-control-label small font-weight-bold" for="chkAportePi">
                                    <i class="fas fa-project-diagram text-primary mr-1"></i>Este avance aporta al Proyecto Anual Integrador (Taq’e)
                                </label>
                            </div>
                            @if($taqeGrupos->isNotEmpty())
                                <select name="taqe_grupo_id" class="form-control form-control-sm mt-1">
                                    <option value="">-- Vincular a mi equipo Taq’e (opcional) --</option>
                                    @foreach($taqeGrupos as $tg)
                                        <option value="{{ $tg->id }}">{{ $tg->nombre }}</option>
                                    @endforeach
                                </select>
                            @endif

                            <div class="custom-control custom-checkbox mt-2">
                                <input type="checkbox" class="custom-control-input" id="chkPortafolio" name="en_portafolio" value="1" checked>
                                <label class="custom-control-label small font-weight-bold" for="chkPortafolio">
                                    <i class="fas fa-folder-open text-warning mr-1"></i>Destacar en mi Portafolio Docente EIB
                                </label>
                            </div>

                            <div class="custom-control custom-checkbox mt-2 pt-2" style="border-top: 1px dashed #d5e6dc;">
                                <input type="checkbox" class="custom-control-input" id="chkPoliticaMenoresEntrega" name="confirmacion_politica_menores" value="1" checked required>
                                <label class="custom-control-label text-muted" for="chkPoliticaMenoresEntrega" style="font-size: 0.72rem;">
                                    <i class="fas fa-child text-warning mr-1"></i>Confirmo que la evidencia cumple la política de protección de datos e imagen de niños y niñas.
                                </label>
                            </div>
                        </div>

                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold btn-block" style="border-radius:999px;">
                            <i class="fas fa-paper-plane mr-1"></i> Enviar Avance para Retroalimentación
                        </button>
                    </form>
                </div>
            </div>
        </div>

        {{-- Lista de Entregas Formativas y Diálogo de Retroalimentación --}}
        <div class="col-lg-7">
            @forelse($entregasFormativas as $ent)
                @php
                    $infoAutoeval = $nivelesFormativos[$ent->autoevaluacion_nivel] ?? ['nombre' => 'En proceso', 'badge' => 'info'];
                @endphp
                <div class="ayllu-card">
                    <div class="ayllu-card-header">
                        <div>
                            <strong style="font-size:0.95rem;">{{ $ent->titulo }}</strong>
                            <small class="d-block text-muted">
                                Por {{ $ent->estudiante?->name }} {{ $ent->estudiante?->apellidos }}
                                &middot; {{ $ent->created_at?->diffForHumans() }}
                                @if($ent->unidad)
                                    &middot; <span class="text-primary">{{ $ent->unidad->titulo }}</span>
                                @endif
                            </small>
                        </div>
                        <div class="d-flex align-items-center" style="gap:0.35rem;">
                            <span class="badge badge-{{ $infoAutoeval['badge'] }}">{{ $infoAutoeval['nombre'] }}</span>
                            @if($ent->es_aporte_proyecto_integrador)
                                <span class="badge badge-dark"><i class="fas fa-flask mr-1"></i>Aporte Taq’e</span>
                            @endif
                            @if($ent->en_portafolio)
                                <span class="badge badge-warning text-dark"><i class="fas fa-star mr-1"></i>En Portafolio</span>
                            @endif
                        </div>
                    </div>
                    <div class="ayllu-card-body">
                        <p class="small mb-2" style="white-space: pre-line;">{{ $ent->descripcion_reflexion }}</p>

                        @if($ent->enlace_evidencia)
                            <div class="mb-2">
                                <a href="{{ $ent->enlace_evidencia }}" target="_blank" rel="noopener noreferrer" class="btn btn-xs btn-outline-primary font-weight-bold" style="border-radius:999px; font-size:0.76rem;">
                                    <i class="fas fa-external-link-alt mr-1"></i> Ver archivo / evidencia adjunta
                                </a>
                            </div>
                        @endif

                        {{-- Historial de Retroalimentaciones Formativas --}}
                        @if($ent->retroalimentaciones->isNotEmpty())
                            <div class="mt-3 p-2 rounded" style="background: var(--ayllu-azul-suave); border-left: 3px solid #1d4ed8;">
                                <div class="small font-weight-bold text-primary mb-2">
                                    <i class="fas fa-comments mr-1"></i> Diálogo y Retroalimentación Formativa:
                                </div>
                                @foreach($ent->retroalimentaciones as $retro)
                                    <div class="p-2 mb-2 bg-white rounded border small text-dark">
                                        <div class="d-flex justify-content-between mb-1">
                                            <strong>{{ $retro->autor?->name }} {{ $retro->autor?->apellidos }}</strong>
                                            @if($retro->valoracion_formativa && isset($nivelesFormativos[$retro->valoracion_formativa]))
                                                <span class="badge badge-{{ $nivelesFormativos[$retro->valoracion_formativa]['badge'] }}">
                                                    Valoración docente: {{ $nivelesFormativos[$retro->valoracion_formativa]['nombre'] }}
                                                </span>
                                            @endif
                                        </div>
                                        @if($retro->fortalezas)
                                            <div class="text-success mb-1"><strong><i class="fas fa-check mr-1"></i>Fortalezas:</strong> {{ $retro->fortalezas }}</div>
                                        @endif
                                        @if($retro->sugerencias_mejora)
                                            <div class="text-warning text-dark mb-1"><strong><i class="fas fa-lightbulb mr-1"></i>Sugerencias para seguir creciendo:</strong> {{ $retro->sugerencias_mejora }}</div>
                                        @endif
                                        <div>{{ $retro->comentario_general }}</div>
                                    </div>
                                @endforeach
                            </div>
                        @endif

                        {{-- Formulario para que el Docente retroalimente o el Estudiante responda --}}
                        <div class="mt-2">
                            <button class="btn btn-xs btn-link p-0 font-weight-bold" type="button" data-toggle="collapse" data-target="#form-retro-{{ $ent->id }}">
                                <i class="fas fa-reply mr-1"></i>
                                {{ $esDocenteDelCurso ? 'Brindar retroalimentación formativa' : 'Añadir comentario o respuesta reflexiva' }}
                            </button>
                            <div class="collapse mt-2" id="form-retro-{{ $ent->id }}">
                                <form action="{{ route('ayllu.entregas.retroalimentar', $ent->id) }}" method="POST" class="p-2 rounded border" style="background: var(--ayllu-surface-soft);">
                                    @csrf
                                    @if($esDocenteDelCurso)
                                        <div class="form-row">
                                            <div class="form-group col-md-4 mb-2">
                                                <label class="small font-weight-bold mb-1">Valoración formativa</label>
                                                <select name="valoracion_formativa" class="form-control form-control-sm">
                                                    <option value="">-- Solo comentario --</option>
                                                    @foreach($nivelesFormativos as $k => $niv)
                                                        <option value="{{ $k }}">{{ $niv['nombre'] }}</option>
                                                    @endforeach
                                                </select>
                                            </div>
                                            <div class="form-group col-md-4 mb-2">
                                                <label class="small font-weight-bold mb-1">Fortalezas observadas</label>
                                                <input type="text" name="fortalezas" class="form-control form-control-sm" placeholder="Logros del avance">
                                            </div>
                                            <div class="form-group col-md-4 mb-2">
                                                <label class="small font-weight-bold mb-1">Sugerencias de mejora</label>
                                                <input type="text" name="sugerencias_mejora" class="form-control form-control-sm" placeholder="Próximos pasos">
                                            </div>
                                        </div>
                                    @endif
                                    <div class="form-group mb-2">
                                        <textarea name="comentario_general" rows="2" class="form-control form-control-sm" placeholder="Escribe tu orientación pedagógica o respuesta..." required></textarea>
                                    </div>
                                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold" style="border-radius:999px;">
                                        Guardar retroalimentación
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            @empty
                <div class="ayllu-card">
                    <div class="ayllu-card-body text-center py-5">
                        <i class="fas fa-seedling fa-2x text-primary mb-2"></i>
                        <h6 class="font-weight-bold">Aún no hay entregas formativas registradas en este curso</h6>
                        <p class="text-muted small mb-0">Registra tu primer avance a la izquierda para iniciar el diálogo formativo con tu docente.</p>
                    </div>
                </div>
            @endforelse
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     PESTAÑA 4: APORTE AL PROYECTO ANUAL INTEGRADOR (Taq'e)
     ══════════════════════════════════════════════════════════════════ --}}
@if($tabActiva === 'proyecto')
    <div class="ayllu-card">
        <div class="ayllu-card-header">
            <span class="font-weight-bold"><i class="fas fa-project-diagram mr-1 text-primary"></i>Articulación del Curso con el Proyecto Anual Integrador (Taq’e)</span>
            @if(Route::has('taqe.index'))
                <a href="{{ route('taqe.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius:999px;">
                    Ir al módulo Taq’e oficial
                </a>
            @endif
        </div>
        <div class="ayllu-card-body">
            @if($proyectoCiclo)
                <div class="p-3 rounded mb-3" style="background: var(--ayllu-oro-suave); border-left: 4px solid #cd9244;">
                    <h5 class="font-weight-bold mb-1">{{ $proyectoCiclo->nombre }}</h5>
                    @if($proyectoCiclo->producto)
                        <div class="small mb-2"><strong>Producto del Proyecto Integrador:</strong> {{ $proyectoCiclo->producto }}</div>
                    @endif
                    @if($silabo && $silabo->producto_curso)
                        <div class="small"><strong>¿Qué tributa el curso «{{ $cursoActual->nombre }}»?:</strong> {{ $silabo->producto_curso }}</div>
                    @endif
                </div>
            @endif

            <h6 class="font-weight-bold mb-2">Equipos Taq’e constituidos en este curso ({{ $taqeGruposCurso->count() }})</h6>
            <div class="row">
                @forelse($taqeGruposCurso as $tg)
                    <div class="col-md-6 mb-3">
                        <div class="p-3 rounded border h-100">
                            <div class="d-flex justify-content-between align-items-center mb-1">
                                <strong>{{ $tg->nombre }}</strong>
                                <span class="badge badge-{{ $tg->estaEntregado() ? 'success' : 'info' }}">
                                    {{ $tg->estaEntregado() ? 'Finalizado' : 'En proceso' }}
                                </span>
                            </div>
                            <small class="text-muted d-block mb-2">
                                Integrantes: {{ $tg->alumnosAceptados->map(fn($a) => $a->nombres . ' ' . $a->apellidos)->implode(', ') ?: 'Equipo en formación' }}
                            </small>
                            <small class="d-block">
                                <strong>Evidencias y entregas subidas:</strong> {{ $tg->trabajos->count() }} archivo(s)/enlace(s)
                            </small>
                        </div>
                    </div>
                @empty
                    <div class="col-12">
                        <p class="text-muted small mb-0">Puedes vincular cualquier entrega formativa de este curso con tu Proyecto Anual Integrador desde la pestaña «3. Evaluación y Retroalimentación Formativa».</p>
                    </div>
                @endforelse
            </div>
        </div>
    </div>
@endif
@endsection
