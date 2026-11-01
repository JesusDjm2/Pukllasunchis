@extends('ayllu.layout')

@section('ayllu_contenido')
<style>
    .ayllu-pi-hero {
        background: linear-gradient(135deg, #233d2c 0%, #35583f 58%, #6e3b23 100%);
        color: #fff;
        border-radius: 14px;
        padding: 1.25rem 1.35rem;
        margin-bottom: 1rem;
        box-shadow: 0 6px 18px rgba(22, 28, 34, 0.12);
        max-width: 100%;
        overflow: hidden;
    }
    .ayllu-pi-hero .badge,
    .ayllu-pi-aporte-card .badge {
        white-space: normal;
        text-align: left;
        line-height: 1.35;
        max-width: 100%;
        overflow-wrap: anywhere;
    }
    .ayllu-pi-year-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 0.65rem;
        margin-bottom: 1rem;
    }
    .ayllu-pi-year-card {
        display: block;
        text-decoration: none !important;
        background: #fff;
        border: 1.5px solid #dfd7c8;
        border-radius: 12px;
        padding: 0.75rem 0.85rem;
        color: #161c22;
        transition: all 0.15s ease;
    }
    .ayllu-pi-year-card:hover {
        border-color: #9e3d22;
        transform: translateY(-1px);
    }
    .ayllu-pi-year-card.active {
        background: #fbf6ef;
        border-color: #9e3d22;
        box-shadow: 0 0 0 2px rgba(158, 61, 34, 0.18);
    }
    .ayllu-pi-filter-bar {
        background: #fff;
        border: 1px solid #e6e0d6;
        border-radius: 12px;
        padding: 0.9rem 1rem;
        margin-bottom: 1rem;
    }
    .ayllu-pi-subnav {
        display: flex;
        flex-wrap: wrap;
        gap: 0.4rem;
        margin-bottom: 1rem;
    }
    .ayllu-pi-subnav a {
        text-decoration: none !important;
        padding: 0.42rem 0.85rem;
        border-radius: 999px;
        font-size: 0.8rem;
        font-weight: 700;
        border: 1px solid #d8d0c5;
        background: #fff;
        color: #2b353e;
    }
    .ayllu-pi-subnav a.active {
        background: #233d2c;
        border-color: #233d2c;
        color: #fff;
    }
    .ayllu-pi-aporte-card {
        background: #fff;
        border: 1px solid #e3ddd2;
        border-left: 5px solid #35583f;
        border-radius: 12px;
        padding: 1rem 1.1rem;
        margin-bottom: 0.85rem;
    }
    .ayllu-pi-aporte-card.prog-INICIAL { border-left-color: #d97706; }
    .ayllu-pi-aporte-card.prog-PRIMARIA { border-left-color: #2563eb; }
    .ayllu-pi-aporte-card.prog-PRIMARIA_EIB { border-left-color: #15803d; }
    .ayllu-pi-aporte-card.prog-FORMACION_GENERAL { border-left-color: #6d28d9; }
    .ayllu-pi-anomaly-pill {
        display: inline-flex;
        align-items: center;
        gap: 0.3rem;
        background: #fff3cd;
        color: #7c2d12;
        border: 1px solid #f59e0b;
        border-radius: 6px;
        padding: 0.15rem 0.5rem;
        font-size: 0.74rem;
        font-weight: 700;
    }
    .ayllu-pi-empty-field {
        color: #6b7280;
        font-style: italic;
        background: #f3f4f6;
        padding: 0.12rem 0.45rem;
        border-radius: 4px;
        font-size: 0.78rem;
    }
    @media (max-width: 767.98px) {
        .ayllu-pi-year-grid {
            grid-template-columns: 1fr;
        }
        .ayllu-pi-hero {
            padding: 0.95rem;
        }
        .ayllu-pi-hero h2 {
            font-size: 1.05rem;
        }
    }
</style>

{{-- ══════════════════════════════════════════════════════════════════
     ENCABEZADO DE LA MESA DOCENTE Y ARTICULACIÓN DE PROYECTOS ANUALES
     ══════════════════════════════════════════════════════════════════ --}}
<div class="ayllu-pi-hero">
    <div class="d-flex flex-wrap justify-content-between align-items-start" style="gap: 0.75rem;">
        <div>
            <span class="badge badge-light text-dark font-weight-bold mb-2" style="font-size: 0.72rem;">
                <i class="fas fa-project-diagram mr-1 text-warning"></i>
                AYLLU PUKLLA · MESA DE PROYECTOS INTEGRADORES ANUALES (TINKUY YACHAY)
            </span>
            <h2 class="h4 font-weight-bold mb-1">
                Año {{ $anioFormativo }} (Ciclos {{ implode('–', $ciclosDelAnio) }}):
                {{ $definicionActiva?->denominacion_original ?? 'Proyecto Integrador Anual' }}
            </h2>
            <p class="small mb-2" style="color: rgba(255,255,255,0.92); max-width: 820px;">
                <strong>Propósito en matriz:</strong> {{ $definicionActiva?->proposito_original }}
            </p>
            <div class="d-flex flex-wrap align-items-center" style="gap: 0.5rem; font-size: 0.78rem;">
                <span class="badge badge-warning text-dark px-2 py-1">
                    <i class="fas fa-gift mr-1"></i>
                    <strong>Producto integrador anual:</strong> {{ $definicionActiva?->producto_integrador }}
                </span>
                <span class="badge badge-dark px-2 py-1" style="background: rgba(0,0,0,0.32);">
                    Ejecución: {{ $ejecucion?->cohorte ?? ('Cohorte ' . $anioLectivo) }} ({{ $periodoActual?->nombre ?? $anioLectivo }})
                </span>
                <span class="badge badge-dark px-2 py-1" style="background: rgba(0,0,0,0.32);">
                    Fuente adoptada: v{{ $definicionActiva?->version_numero ?? 1 }} ({{ $definicionActiva?->archivo_origen }})
                </span>
            </div>
        </div>
        <div class="text-right">
            @if($esCoordinador)
                <span class="badge badge-success px-2 py-1 d-block mb-1">
                    <i class="fas fa-user-shield mr-1"></i> Vista de Coordinación y Mesa Docente
                </span>
            @elseif($perteneceMesaCiclo)
                <span class="badge badge-info px-2 py-1 d-block mb-1">
                    <i class="fas fa-chalkboard-teacher mr-1"></i> Integrante de Mesa Docente (Ciclo {{ $cicloSeleccionado }})
                </span>
            @elseif($esEstudianteDelCiclo)
                <span class="badge badge-primary px-2 py-1 d-block mb-1">
                    <i class="fas fa-user-graduate mr-1"></i> Vista de Estudiante (Ciclo {{ $cicloSeleccionado }})
                </span>
            @else
                <span class="badge badge-secondary px-2 py-1 d-block mb-1">
                    <i class="fas fa-eye mr-1"></i> Consulta Curricular Documentada
                </span>
            @endif
            <small class="d-block" style="color: rgba(255,255,255,0.8); font-size: 0.72rem;">
                114 posiciones documentales conciliadas · 4 años (I–VIII)
            </small>
        </div>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     1. SELECTOR DE LOS 4 AÑOS FORMATIVOS (I–VIII) Y BARRA DE FILTROS
     ══════════════════════════════════════════════════════════════════ --}}
<div class="ayllu-pi-year-grid" role="navigation" aria-label="Selección de Año Formativo">
    @php
        $titulosBrevesAnio = [
            1 => ['ciclos' => 'I–II', 'rotulo' => 'Escuela y territorio', 'entradas' => 20],
            2 => ['ciclos' => 'III–IV', 'rotulo' => 'Prácticas de crianza interculturales', 'entradas' => 30],
            3 => ['rotulo' => 'Saber y Conocer: Formas de Aprendizaje', 'ciclos' => 'V–VI', 'entradas' => 36],
            4 => ['rotulo' => 'Rituales escolares y transformación', 'ciclos' => 'VII–VIII', 'entradas' => 28],
        ];
    @endphp
    @foreach($titulosBrevesAnio as $numAnio => $infoAnio)
        @php
            $defCard = $resumenCuatroAnios[$numAnio] ?? null;
            $primerCicloAnio = \App\Http\Controllers\Ayllu\AylluMesaProyectoController::MAPA_ANIO_CICLOS[$numAnio][0];
        @endphp
        <a href="{{ route('ayllu.proyecto', ['anio' => $numAnio, 'ciclo' => $primerCicloAnio, 'programa' => $programaFiltro, 'vista' => $vistaActiva]) }}"
           class="ayllu-pi-year-card {{ (int) $anioFormativo === $numAnio ? 'active' : '' }}">
            <div class="d-flex justify-content-between align-items-center mb-1">
                <span class="badge {{ (int) $anioFormativo === $numAnio ? 'badge-danger' : 'badge-secondary' }}">
                    Año {{ $numAnio }} · Ciclos {{ $infoAnio['ciclos'] }}
                </span>
                <small class="text-muted font-weight-bold">{{ $infoAnio['entradas'] }} entradas</small>
            </div>
            <div class="font-weight-bold small mb-1" style="line-height: 1.25;">
                {{ $infoAnio['rotulo'] }}
            </div>
            <div class="text-muted" style="font-size: 0.72rem; line-height: 1.2;">
                <i class="fas fa-bullseye mr-1 text-success"></i>{{ \Illuminate\Support\Str::limit($defCard?->producto_integrador, 68) }}
            </div>
        </a>
    @endforeach
</div>

<div class="ayllu-pi-filter-bar">
    <form action="{{ route('ayllu.proyecto') }}" method="GET" class="row align-items-end">
        <input type="hidden" name="vista" value="{{ $vistaActiva }}">
        <input type="hidden" name="foco" value="ciclo">

        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <label for="selCicloPi" class="small font-weight-bold mb-1 d-block">
                <i class="fas fa-layer-group mr-1 text-primary"></i>Ciclo del Año {{ $anioFormativo }}
            </label>
            <div class="btn-group btn-group-sm d-flex" role="group" aria-label="Ciclos del año">
                @foreach($ciclosDelAnio as $cicItem)
                    <a href="{{ route('ayllu.proyecto', ['anio' => $anioFormativo, 'ciclo' => $cicItem, 'programa' => $programaFiltro, 'vista' => $vistaActiva, 'foco' => 'ciclo']) }}"
                       class="btn {{ $cicloSeleccionado === $cicItem ? 'btn-primary font-weight-bold' : 'btn-outline-secondary' }} w-100">
                        Ciclo {{ $cicItem }}
                    </a>
                @endforeach
            </div>
        </div>

        <div class="col-6 col-md-3 mb-2 mb-md-0">
            <label for="selSaltoCiclo" class="small font-weight-bold mb-1">Cambiar a otro ciclo (I–VIII)</label>
            <select id="selSaltoCiclo" name="ciclo" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach(['I' => 1, 'II' => 1, 'III' => 2, 'IV' => 2, 'V' => 3, 'VI' => 3, 'VII' => 4, 'VIII' => 4] as $cRom => $aNum)
                    <option value="{{ $cRom }}" {{ $cicloSeleccionado === $cRom ? 'selected' : '' }}>
                        Ciclo {{ $cRom }} (Año {{ $aNum }})
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-4 mb-2 mb-md-0">
            <label for="selProgramaPi" class="small font-weight-bold mb-1">
                <i class="fas fa-graduation-cap mr-1 text-success"></i>Programa documentado (+ Formación General común)
            </label>
            <select id="selProgramaPi" name="programa" class="form-control form-control-sm" onchange="this.form.submit()">
                @foreach($programasDocumentados as $codProg => $labelProg)
                    <option value="{{ $codProg }}" {{ $programaFiltro === $codProg ? 'selected' : '' }}>
                        {{ $labelProg }}
                    </option>
                @endforeach
            </select>
        </div>

        <div class="col-12 col-md-2 text-right">
            <button type="submit" class="btn btn-sm btn-dark btn-block font-weight-bold">
                <i class="fas fa-filter mr-1"></i> Aplicar
            </button>
        </div>
    </form>

    {{-- Continuidad anual entre los dos ciclos del año seleccionado --}}
    <div class="row mt-3 pt-2" style="border-top: 1px dashed #e6e0d6;">
        @foreach($ciclosDelAnio as $cicPar)
            @php
                $apCicloPar = $aportesAnio->where('ciclo_romano', $cicPar);
                $integradosPar = $apCicloPar->where('estado_desarrollo', 'integrado_al_producto_anual')->count();
                $enDesarrolloPar = $apCicloPar->whereIn('estado_desarrollo', ['en_desarrollo', 'para_revision_compartida', 'revisado'])->count();
                $sinRegistrarPar = $apCicloPar->where('estado_desarrollo', 'sin_registrar')->count();
            @endphp
            <div class="col-md-6 mb-2 mb-md-0">
                <div class="p-2 rounded d-flex flex-wrap justify-content-between align-items-center"
                     style="background: {{ $cicloSeleccionado === $cicPar ? '#eef6f1' : '#f8f6f2' }}; border: 1px solid {{ $cicloSeleccionado === $cicPar ? '#2e6b4e' : '#e6e0d6' }};">
                    <div>
                        <span class="badge {{ $cicloSeleccionado === $cicPar ? 'badge-success' : 'badge-secondary' }} mr-1">
                            Continuidad Anual · Ciclo {{ $cicPar }}
                        </span>
                        <strong class="small">{{ $apCicloPar->count() }} aportes en el filtro actual</strong>
                    </div>
                    <div class="small">
                        <span class="badge badge-light border" title="Sin registrar">{{ $sinRegistrarPar }} sin reg.</span>
                        <span class="badge badge-info" title="En desarrollo o revisión">{{ $enDesarrolloPar }} en proc.</span>
                        <span class="badge badge-success" title="Integrados al producto anual">{{ $integradosPar }} integrados</span>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     SUBNAVEGACIÓN DE RECORRIDOS DE LA MESA DOCENTE
     ══════════════════════════════════════════════════════════════════ --}}
<nav class="ayllu-pi-subnav" aria-label="Vistas del Proyecto Integrador">
    <a href="{{ route('ayllu.proyecto', ['anio' => $anioFormativo, 'ciclo' => $cicloSeleccionado, 'programa' => $programaFiltro, 'vista' => 'matriz']) }}"
       class="{{ $vistaActiva === 'matriz' ? 'active' : '' }}">
        <i class="fas fa-th-list mr-1"></i> 1. Matriz de Cursos ({{ $aportesCicloActivo->count() }})
    </a>
    <a href="{{ route('ayllu.proyecto', ['anio' => $anioFormativo, 'ciclo' => $cicloSeleccionado, 'programa' => $programaFiltro, 'vista' => 'integracion']) }}"
       class="{{ $vistaActiva === 'integracion' ? 'active' : '' }}">
        <i class="fas fa-puzzle-piece mr-1"></i> 2. Integración Anual y Síntesis
    </a>
    <a href="{{ route('ayllu.proyecto', ['anio' => $anioFormativo, 'ciclo' => $cicloSeleccionado, 'programa' => $programaFiltro, 'vista' => 'bitacora']) }}"
       class="{{ $vistaActiva === 'bitacora' ? 'active' : '' }}">
        <i class="fas fa-book-open mr-1"></i> 3. Bitácora y Articulación ({{ $bitacorasVisibles->count() }})
    </a>
    <a href="{{ route('ayllu.proyecto', ['anio' => $anioFormativo, 'ciclo' => $cicloSeleccionado, 'programa' => $programaFiltro, 'vista' => 'hitos']) }}"
       class="{{ $vistaActiva === 'hitos' ? 'active' : '' }}">
        <i class="fas fa-flag-checkered mr-1"></i> 4. Ruta de Hitos y Revisiones
    </a>
    <a href="{{ route('ayllu.proyecto', ['anio' => $anioFormativo, 'ciclo' => $cicloSeleccionado, 'programa' => $programaFiltro, 'vista' => 'fuentes']) }}"
       class="{{ $vistaActiva === 'fuentes' ? 'active' : '' }}">
        <i class="fas fa-file-excel mr-1"></i> 5. Fuentes, Versiones y Calidad ({{ $todasAnomaliasCompetencias->count() }} alertas)
    </a>
    @if($esCoordinador)
        <a href="{{ route('ayllu.proyecto', ['anio' => $anioFormativo, 'ciclo' => $cicloSeleccionado, 'programa' => $programaFiltro, 'vista' => 'coordinacion']) }}"
           class="{{ $vistaActiva === 'coordinacion' ? 'active' : '' }}">
            <i class="fas fa-sliders-h mr-1"></i> 6. Coordinación y Asignaciones
        </a>
    @endif
    <a href="{{ route('ayllu.proyecto', ['anio' => $anioFormativo, 'ciclo' => $cicloSeleccionado, 'programa' => $programaFiltro, 'vista' => 'portafolio']) }}"
       class="{{ $vistaActiva === 'portafolio' ? 'active' : '' }}">
        <i class="fas fa-folder-open mr-1"></i> Mi Portafolio y Equipos Taq’e
    </a>
</nav>

{{-- ══════════════════════════════════════════════════════════════════
     VISTA 1: MATRIZ DE CURSOS Y APORTES DEL CICLO SELECCIONADO
     ══════════════════════════════════════════════════════════════════ --}}
@if($vistaActiva === 'matriz')
    <div class="ayllu-card p-3 mb-3" style="background: #fcfaf6;">
        <div class="d-flex flex-wrap justify-content-between align-items-center" style="gap: 0.5rem;">
            <div class="small">
                <strong>Denominación en matriz (Ciclo {{ $cicloSeleccionado }}):</strong>
                {{ $definicionActiva?->denominacion_ciclo_par ?? $definicionActiva?->denominacion_original }}
                @if($definicionActiva?->ambito_exploracion)
                    · <span class="text-muted">{{ $definicionActiva->ambito_exploracion }}</span>
                @endif
            </div>
            <div class="small text-muted">
                Mostrando <strong>{{ $aportesCicloActivo->count() }}</strong> posiciones documentales del <strong>Ciclo {{ $cicloSeleccionado }}</strong>
                @if($programaFiltro !== 'TODOS')
                    para el filtro <strong>{{ $programasDocumentados[$programaFiltro] ?? $programaFiltro }}</strong> (sin mezclar Primaria con Primaria EIB)
                @endif
            </div>
        </div>
    </div>

    @forelse($aportesCicloActivo as $ap)
        @php
            $estadoInfo = $estadosDesarrollo[$ap->estado_desarrollo] ?? $estadosDesarrollo['sin_registrar'];
            $perm = $permisosPorAporte[$ap->id] ?? ['ver_mesa' => false, 'editar' => false, 'registrar_evidencia' => false];
            $evidenciasAp = $evidenciasVisiblesPorAporte[$ap->id] ?? collect();
            $ultimaVersion = $evidenciasAp->first();
        @endphp
        <div class="ayllu-pi-aporte-card prog-{{ $ap->agrupacion_programa_codigo }}">
            <div class="d-flex flex-wrap justify-content-between align-items-start mb-2" style="gap: 0.5rem;">
                <div>
                    <span class="badge badge-dark mr-1" title="Posición global en las 4 matrices">#{{ $ap->posicion_global }}</span>
                    <span class="badge badge-light border mr-1" title="Coordenada en hoja de cálculo">
                        <i class="fas fa-table mr-1 text-success"></i>{{ $ap->archivo_fuente }} · {{ $ap->hoja_fuente }} · Celda {{ $ap->celda_curso }}
                    </span>
                    <span class="badge badge-info mr-1">
                        {{ $ap->agrupacion_programa_original }}
                        @if($ap->es_aporte_comun)
                            (Común al ciclo)
                        @endif
                    </span>
                    @if(!empty($ap->anotaciones_autor_historico))
                        <span class="badge badge-light border text-muted" title="Anotación en documento histórico; no asigna docente vigente automáticamente">
                            <i class="fas fa-history mr-1"></i>Nota en fuente: {{ implode(', ', $ap->anotaciones_autor_historico) }}
                        </span>
                    @endif
                </div>
                <div class="d-flex align-items-center" style="gap: 0.4rem;">
                    <span class="badge badge-{{ $estadoInfo['badge'] }} px-2 py-1">
                        {{ $estadoInfo['label'] }}
                    </span>
                    @if($ultimaVersion)
                        <span class="badge badge-outline-dark border px-2 py-1">
                            v{{ $ultimaVersion->numero_version }}
                        </span>
                    @endif
                </div>
            </div>

            <div class="row">
                <div class="col-lg-4 mb-2 mb-lg-0">
                    <h3 class="h6 font-weight-bold mb-1" style="color: #161c22; white-space: pre-line;">{{ $ap->curso_fuente_original }}</h3>

                    <div class="small mb-1">
                        <strong>Competencias (celda {{ $ap->celda_competencias }}):</strong>
                        @if($ap->es_anomalia_competencia)
                            <span class="ayllu-pi-anomaly-pill" title="{{ $ap->detalle_anomalia_competencia['observacion'] ?? '' }}">
                                <i class="fas fa-exclamation-triangle"></i>
                                Valor bruto: {{ $ap->competencias_fuente_bruto }} (formato Excel: {{ $ap->competencias_formato_excel }} · Para revisión)
                            </span>
                        @elseif($ap->competencias_fuente_mostrar === 'No consignado en la fuente')
                            <span class="ayllu-pi-empty-field">No consignado en la fuente</span>
                        @else
                            <span class="font-weight-bold text-dark">{{ $ap->competencias_fuente_mostrar }}</span>
                        @endif
                    </div>

                    <div class="small text-muted">
                        <strong>Catálogo institucional:</strong>
                        @if($ap->cursoInstitucional)
                            <a href="{{ route('ayllu.cursos.show', $ap->cursoInstitucional->id) }}" class="font-weight-bold text-primary">
                                {{ $ap->cursoInstitucional->nombre }}
                            </a>
                            @if($ap->cursoInstitucional->docentes->isNotEmpty())
                                <span class="d-block" style="font-size: 0.74rem;">
                                    Docente(s) en catálogo: {{ $ap->cursoInstitucional->docentes->map(fn($d) => trim($d->nombre . ' ' . ($d->apellidos ?? '')))->implode(', ') }}
                                </span>
                            @endif
                        @elseif($ap->agrupacion_programa_codigo === 'PRIMARIA')
                            <span>Antecedente documental de Primaria (conservado sin renombrar como Primaria EIB)</span>
                        @else
                            <span>Correspondencia pendiente de confirmación por coordinación</span>
                        @endif
                    </div>
                </div>

                <div class="col-lg-4 mb-2 mb-lg-0">
                    <div class="small font-weight-bold text-uppercase text-muted mb-1" style="font-size: 0.7rem;">
                        Estrategias / Acciones Formativas {{ $ap->celda_estrategias ? '(celda ' . $ap->celda_estrategias . ')' : '' }}
                    </div>
                    @if($ap->estrategias_mostrar === 'No consignado en la fuente')
                        <div class="ayllu-pi-empty-field">No consignado en la fuente</div>
                    @else
                        <div class="small" style="white-space: pre-line; color: #2b353e;">{{ $ap->estrategias_mostrar }}</div>
                    @endif
                </div>

                <div class="col-lg-4">
                    <div class="small font-weight-bold text-uppercase text-muted mb-1" style="font-size: 0.7rem;">
                        Producto previsto que aporta (celda {{ $ap->celda_producto }})
                    </div>
                    @if($ap->producto_previsto_mostrar === 'No consignado en la fuente')
                        <div class="ayllu-pi-empty-field mb-2">No consignado en la fuente</div>
                    @else
                        <div class="p-2 rounded small mb-2" style="background: #f5f2ed; color: #161c22; white-space: pre-line; border-left: 3px solid #9e3d22;">{{ $ap->producto_previsto_mostrar }}</div>
                    @endif

                    @if($ap->ajuste_operativo_mesa)
                        <div class="p-2 rounded small mb-2" style="background: #eef6f1; color: #1b4332;">
                            <strong><i class="fas fa-handshake mr-1"></i>Acuerdo operativo de la mesa:</strong>
                            {{ $ap->ajuste_operativo_mesa }}
                        </div>
                    @endif

                    <div class="d-flex flex-wrap justify-content-between align-items-center mt-2" style="gap: 0.4rem;">
                        <small class="text-muted">
                            {{ $evidenciasAp->count() }} versión(es) visible(s)
                            @if($ap->versionIntegrada)
                                · <strong class="text-success">Integrada: v{{ $ap->versionIntegrada->numero_version }}</strong>
                            @endif
                        </small>

                        @if($perm['ver_mesa'])
                            <a href="{{ route('ayllu.pi.aportes.show', $ap->id) }}"
                               class="btn btn-sm {{ $perm['editar'] ? 'btn-primary' : 'btn-outline-primary' }} font-weight-bold"
                               style="border-radius: 999px; font-size: 0.76rem;">
                                <i class="fas {{ $perm['editar'] ? 'fa-edit' : 'fa-folder-open' }} mr-1"></i>
                                {{ $perm['editar'] ? 'Gestionar mi aporte / versiones' : 'Abrir ficha del aporte' }}
                            </a>
                        @endif
                    </div>
                </div>
            </div>
        </div>
    @empty
        <div class="ayllu-card p-4 text-center">
            <p class="text-muted mb-0">No se encontraron entradas para la combinación seleccionada.</p>
        </div>
    @endforelse
@endif

{{-- ══════════════════════════════════════════════════════════════════
     VISTA 2: INTEGRACIÓN ANUAL Y SÍNTESIS COMUNITARIA
     ══════════════════════════════════════════════════════════════════ --}}
@if($vistaActiva === 'integracion')
    <div class="ayllu-card p-4 mb-3">
        <h3 class="h6 font-weight-bold mb-1">
            <i class="fas fa-puzzle-piece text-success mr-1"></i>
            Estado de Integración al Producto Anual · Año {{ $anioFormativo }} (Ciclos {{ implode(' y ', $ciclosDelAnio) }})
        </h3>
        <p class="small text-muted mb-3">
            Distingue el <strong>producto previsto en la matriz</strong>, las <strong>versiones de evidencia registradas</strong> y la <strong>versión específica validada e integrada</strong> al producto integrador anual («{{ $definicionActiva?->producto_integrador }}»). Ningún estado se fabrica al importar.
        </p>

        @foreach($ciclosDelAnio as $cicInt)
            <h4 class="small font-weight-bold text-uppercase mt-3 mb-2" style="color: #9e3d22;">
                Ciclo {{ $cicInt }} ({{ $aportesAnio->where('ciclo_romano', $cicInt)->count() }} aportes en el filtro)
            </h4>
            <div class="table-responsive">
                <table class="table table-sm table-bordered small bg-white">
                    <thead class="thead-light">
                        <tr>
                            <th>Pos. / Celda</th>
                            <th>Programa</th>
                            <th>Curso en Matriz</th>
                            <th>Producto Previsto</th>
                            <th>Estado Actual</th>
                            <th>Versión Integrada / Validación</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($aportesAnio->where('ciclo_romano', $cicInt) as $apInt)
                            @php
                                $estInt = $estadosDesarrollo[$apInt->estado_desarrollo] ?? $estadosDesarrollo['sin_registrar'];
                            @endphp
                            <tr>
                                <td class="font-weight-bold">#{{ $apInt->posicion_global }} ({{ $apInt->celda_curso }})</td>
                                <td>{{ $apInt->agrupacion_programa_original }}</td>
                                <td>
                                    @if(!empty($permisosPorAporte[$apInt->id]['ver_mesa']))
                                        <a href="{{ route('ayllu.pi.aportes.show', $apInt->id) }}" class="font-weight-bold">
                                            {{ $apInt->curso_fuente_normalizado }}
                                        </a>
                                    @else
                                        {{ $apInt->curso_fuente_normalizado }}
                                    @endif
                                </td>
                                <td>{{ \Illuminate\Support\Str::limit($apInt->producto_previsto_mostrar, 95) }}</td>
                                <td>
                                    <span class="badge badge-{{ $estInt['badge'] }}">{{ $estInt['label'] }}</span>
                                </td>
                                <td>
                                    @if($apInt->versionIntegrada)
                                        <strong class="text-success">v{{ $apInt->versionIntegrada->numero_version }}: {{ $apInt->versionIntegrada->titulo_version }}</strong>
                                        <div class="text-muted" style="font-size: 0.72rem;">
                                            Validado por: {{ $apInt->integradoPor?->name ?? 'Coordinación' }}
                                            ({{ $apInt->integrado_at?->format('d/m/Y H:i') }})
                                        </div>
                                    @else
                                        <span class="text-muted">Pendiente de integración anual</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endforeach
    </div>

    {{-- Síntesis comunitaria con revisión editorial institucional --}}
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="ayllu-card p-3 h-100">
                <h4 class="h6 font-weight-bold mb-2">
                    <i class="fas fa-bullhorn text-warning mr-1"></i>
                    Síntesis Publicadas y en Revisión Editorial (Ciclos {{ implode('–', $ciclosDelAnio) }})
                </h4>
                <p class="small text-muted mb-2">
                    Compartir una síntesis con el ciclo o la comunidad Ayllu Puklla pasa por revisión editorial institucional y <strong>no amplía los permisos de los archivos originales restringidos</strong>.
                </p>

                @forelse($sintesisComunitarias as $sint)
                    <div class="p-3 rounded mb-2" style="border: 1px solid #e6e0d6; background: #fbfaf8;">
                        <div class="d-flex justify-content-between align-items-center mb-1">
                            <strong class="small">{{ $sint->titulo }}</strong>
                            <span class="badge {{ $sint->estado_editorial === 'aprobada_publicada' ? 'badge-success' : ($sint->estado_editorial === 'pendiente_revision_editorial' ? 'badge-warning' : 'badge-secondary') }}">
                                {{ strtoupper(str_replace('_', ' ', $sint->estado_editorial)) }}
                            </span>
                        </div>
                        <p class="small mb-1 text-muted">{{ $sint->resumen_sintesis }}</p>
                        <div class="small text-muted" style="font-size: 0.73rem;">
                            Autoría reconocida: <strong>{{ $sint->autoria_reconocida }}</strong>
                            · Audiencia: <strong>{{ $sint->audiencia_objetivo }}</strong>
                            · Ciclo {{ $sint->ciclo_romano }}
                        </div>

                        @if($esCoordinador && $sint->estado_editorial === 'pendiente_revision_editorial')
                            <form action="{{ route('ayllu.pi.sintesis.revisar', $sint->id) }}" method="POST" class="mt-2 pt-2" style="border-top: 1px dashed #d8d0c5;">
                                @csrf
                                <div class="form-row align-items-center">
                                    <div class="col-md-5 mb-1 mb-md-0">
                                        <select name="estado_editorial" class="form-control form-control-sm">
                                            <option value="aprobada_publicada">Aprobar y publicar síntesis</option>
                                            <option value="rechazada">Observar / Rechazar</option>
                                        </select>
                                    </div>
                                    <div class="col-md-5 mb-1 mb-md-0">
                                        <input type="text" name="observacion_editorial" class="form-control form-control-sm" placeholder="Nota editorial (opcional)">
                                    </div>
                                    <div class="col-md-2">
                                        <button type="submit" class="btn btn-sm btn-success btn-block font-weight-bold">Validar</button>
                                    </div>
                                </div>
                            </form>
                        @endif
                    </div>
                @empty
                    <div class="p-3 rounded text-center" style="background: #f5f2ed;">
                        <span class="small text-muted">Aún no se han registrado síntesis comunitarias para este año.</span>
                    </div>
                @endforelse
            </div>
        </div>

        @if($perteneceMesaCiclo && $ejecucion)
            <div class="col-lg-6 mb-3">
                <div class="ayllu-card p-3 h-100">
                    <h4 class="h6 font-weight-bold mb-2">
                        <i class="fas fa-paper-plane text-primary mr-1"></i>
                        Proponer Síntesis para el Ciclo o Comunidad Ayllu Puklla
                    </h4>
                    <form action="{{ route('ayllu.pi.sintesis.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ejecucion_id" value="{{ $ejecucion->id }}">
                        <div class="form-row">
                            <div class="form-group col-6 mb-2">
                                <label class="small font-weight-bold mb-1">Ciclo</label>
                                <select name="ciclo_romano" class="form-control form-control-sm">
                                    @foreach($ciclosDelAnio as $cS)
                                        <option value="{{ $cS }}" {{ $cicloSeleccionado === $cS ? 'selected' : '' }}>Ciclo {{ $cS }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-6 mb-2">
                                <label class="small font-weight-bold mb-1">Audiencia objetivo</label>
                                <select name="audiencia_objetivo" class="form-control form-control-sm">
                                    <option value="ciclo_completo">Ciclo completo (orientaciones y avances al ciclo)</option>
                                    <option value="comunidad_ayllu">Comunidad Ayllu Puklla (muro comunitario previa revisión)</option>
                                </select>
                            </div>
                        </div>
                        <input type="hidden" name="programa_codigo" value="{{ $programaFiltro }}">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Aporte vinculado (opcional)</label>
                            <select name="aporte_curso_id" class="form-control form-control-sm">
                                <option value="">-- Síntesis transversal del ciclo --</option>
                                @foreach($aportesCicloActivo as $apOpt)
                                    <option value="{{ $apOpt->id }}">#{{ $apOpt->posicion_global }} · {{ $apOpt->curso_fuente_normalizado }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Título de la síntesis</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" required maxlength="220" placeholder="Ej. Avance colectivo del Mapa Parlante del Ciclo {{ $cicloSeleccionado }}">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Autoría reconocida (docentes, estudiantes y sabios comunitarios)</label>
                            <input type="text" name="autoria_reconocida" class="form-control form-control-sm" required maxlength="255" placeholder="Ej. Equipos de Ciclo {{ $cicloSeleccionado }} y sabios de la comunidad de...">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Resumen pedagógico para compartir</label>
                            <textarea name="resumen_sintesis" rows="3" class="form-control form-control-sm" required placeholder="Síntesis cuidada del hallazgo o producto. Los archivos internos mantienen su restricción original."></textarea>
                        </div>
                        <div class="p-2 rounded small mb-2" style="background: #eef6f1; color: #1b4332; font-size: 0.74rem;">
                            <i class="fas fa-shield-alt mr-1"></i>
                            <strong>Aviso de visibilidad antes de enviar:</strong> Esta síntesis ingresará como <em>Pendiente de revisión editorial</em>. Ningún archivo privado vinculado cambiará sus permisos originales.
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold btn-block" style="border-radius: 999px;">
                            Enviar a Revisión Editorial Institucional
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     VISTA 3: BITÁCORA CRONOLÓGICA Y ACUERDOS DE ARTICULACIÓN ENTRE CURSOS
     ══════════════════════════════════════════════════════════════════ --}}
@if($vistaActiva === 'bitacora')
    <div class="row">
        <div class="col-lg-7 mb-3">
            <div class="ayllu-card p-3 mb-3">
                <h3 class="h6 font-weight-bold mb-2">
                    <i class="fas fa-stream text-primary mr-1"></i>
                    Bitácora Cronológica del Proyecto (Avances, Dificultades y Acuerdos)
                </h3>
                <p class="small text-muted mb-3">
                    Las entradas respetan la audiencia seleccionada: las deliberaciones de <strong>Mesa docente del ciclo</strong> son exclusivas para los docentes y coordinadores asignados al ciclo.
                </p>

                @forelse($bitacorasVisibles as $bit)
                    @php
                        $audBit = $audienciasCatalogo[$bit->audiencia] ?? ['label' => $bit->audiencia, 'badge' => 'secondary'];
                    @endphp
                    <div class="p-3 rounded mb-2" style="border: 1px solid #e6e0d6; background: #fff;">
                        <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                            <div>
                                <span class="badge badge-dark mr-1">Ciclo {{ $bit->ciclo_romano }}</span>
                                <span class="badge badge-{{ $audBit['badge'] }} mr-1">{{ $audBit['label'] }}</span>
                                <strong class="small">{{ $bit->titulo }}</strong>
                            </div>
                            <small class="text-muted">
                                {{ $bit->autor?->name }} · {{ $bit->created_at?->format('d/m/Y H:i') }}
                            </small>
                        </div>
                        <p class="small mb-1" style="white-space: pre-line;">{{ $bit->contenido }}</p>
                        @if($bit->aporte)
                            <div class="small text-muted" style="font-size: 0.73rem;">
                                Curso: <strong>{{ $bit->aporte->curso_fuente_normalizado }}</strong> ({{ $bit->aporte->celda_curso }})
                            </div>
                        @endif
                    </div>
                @empty
                    <div class="p-3 rounded text-center" style="background: #f5f2ed;">
                        <span class="small text-muted">Aún no hay entradas registradas en la bitácora visible para tu perfil en este año.</span>
                    </div>
                @endforelse
            </div>

            {{-- Dependencias de Articulación Acordadas entre Cursos --}}
            <div class="ayllu-card p-3">
                <h4 class="h6 font-weight-bold mb-2">
                    <i class="fas fa-exchange-alt text-success mr-1"></i>
                    Acuerdos de Articulación entre Cursos (Insumos y Entregas Cruzadas)
                </h4>
                @forelse($articulacionesAnio as $art)
                    <div class="p-2 rounded mb-2 small" style="border: 1px solid #d5e6dc; background: #fbfdfc;">
                        <div>
                            <strong>Entrega:</strong> {{ $art->aporteOrigen?->curso_fuente_normalizado }} ({{ $art->aporteOrigen?->celda_curso }})
                            <i class="fas fa-arrow-right mx-1 text-success"></i>
                            <strong>Recibe:</strong> {{ $art->aporteDestino?->curso_fuente_normalizado }} ({{ $art->aporteDestino?->celda_curso }})
                        </div>
                        <div class="mt-1">
                            <strong>Insumo acordado:</strong> {{ $art->insumo_requerido }}
                            @if($art->fecha_acordada)
                                · <span class="badge badge-light border">Fecha acordada: {{ $art->fecha_acordada->format('d/m/Y') }}</span>
                            @endif
                        </div>
                        @if($art->criterio_articulacion)
                            <div class="text-muted mt-1">{{ $art->criterio_articulacion }}</div>
                        @endif
                    </div>
                @empty
                    <div class="p-3 rounded text-center" style="background: #f5f2ed;">
                        <span class="small text-muted">No se han registrado dependencias cruzadas en esta ejecución (las dependencias se acuerdan en la mesa, no se inventan al importar).</span>
                    </div>
                @endforelse
            </div>
        </div>

        @if($perteneceMesaCiclo && $ejecucion)
            <div class="col-lg-5 mb-3">
                <div class="ayllu-card p-3 mb-3">
                    <h4 class="h6 font-weight-bold mb-2">
                        <i class="fas fa-pen-fancy text-primary mr-1"></i>
                        Registrar Avance, Dificultad o Acuerdo en Bitácora
                    </h4>
                    <form action="{{ route('ayllu.pi.bitacora.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ejecucion_id" value="{{ $ejecucion->id }}">
                        <input type="hidden" name="programa_codigo" value="{{ $programaFiltro }}">
                        <div class="form-row">
                            <div class="form-group col-5 mb-2">
                                <label class="small font-weight-bold mb-1">Ciclo</label>
                                <select name="ciclo_romano" class="form-control form-control-sm">
                                    @foreach($ciclosDelAnio as $cB)
                                        <option value="{{ $cB }}" {{ $cicloSeleccionado === $cB ? 'selected' : '' }}>Ciclo {{ $cB }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-7 mb-2">
                                <label class="small font-weight-bold mb-1">Tipo de registro</label>
                                <select name="tipo_entrada" class="form-control form-control-sm">
                                    <option value="acuerdo_mesa">Acuerdo de Mesa Docente</option>
                                    <option value="avance">Avance de articulación</option>
                                    <option value="dificultad">Dificultad / Necesidad de apoyo</option>
                                    <option value="observacion_calidad">Observación curricular</option>
                                </select>
                            </div>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Audiencia (¿quién verá este registro?)</label>
                            <select name="audiencia" class="form-control form-control-sm">
                                <option value="mesa_docente_ciclo">Mesa docente del ciclo (reservado a docentes y coordinación del ciclo)</option>
                                <option value="ciclo_completo">Ciclo completo (orientación visible también para estudiantes del ciclo)</option>
                                <option value="borrador_personal">Borrador personal (solo visible para mí)</option>
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Aporte de curso relacionado (opcional)</label>
                            <select name="aporte_curso_id" class="form-control form-control-sm">
                                <option value="">-- General de la mesa del ciclo --</option>
                                @foreach($aportesCicloActivo as $apB)
                                    <option value="{{ $apB->id }}">#{{ $apB->posicion_global }} · {{ $apB->curso_fuente_normalizado }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Título</label>
                            <input type="text" name="titulo" class="form-control form-control-sm" required maxlength="220" placeholder="Ej. Acuerdo de guía única de entrevista para trabajo de campo">
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Detalle del acuerdo, avance o dificultad</label>
                            <textarea name="contenido" rows="3" class="form-control form-control-sm" required></textarea>
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold btn-block" style="border-radius: 999px;">
                            Guardar en Bitácora
                        </button>
                    </form>
                </div>

                <div class="ayllu-card p-3">
                    <h4 class="h6 font-weight-bold mb-2">
                        <i class="fas fa-link text-success mr-1"></i>
                        Acordar Dependencia entre Dos Cursos
                    </h4>
                    <form action="{{ route('ayllu.pi.articulaciones.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="ejecucion_id" value="{{ $ejecucion->id }}">
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Curso que entrega el insumo</label>
                            <select name="aporte_origen_id" class="form-control form-control-sm" required>
                                @foreach($aportesAnio as $apO)
                                    <option value="{{ $apO->id }}">Ciclo {{ $apO->ciclo_romano }} · {{ $apO->curso_fuente_normalizado }} ({{ $apO->agrupacion_programa_original }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Curso que recibe / necesita el insumo</label>
                            <select name="aporte_destino_id" class="form-control form-control-sm" required>
                                @foreach($aportesAnio as $apD)
                                    <option value="{{ $apD->id }}" {{ $loop->index === 1 ? 'selected' : '' }}>Ciclo {{ $apD->ciclo_romano }} · {{ $apD->curso_fuente_normalizado }} ({{ $apD->agrupacion_programa_original }})</option>
                                @endforeach
                            </select>
                        </div>
                        <div class="form-group mb-2">
                            <label class="small font-weight-bold mb-1">Insumo acordado</label>
                            <input type="text" name="insumo_requerido" class="form-control form-control-sm" required placeholder="Ej. Testimonios transcritos en quechua y castellano">
                        </div>
                        <div class="form-row">
                            <div class="form-group col-7 mb-2">
                                <label class="small font-weight-bold mb-1">Criterio de articulación</label>
                                <input type="text" name="criterio_articulacion" class="form-control form-control-sm" placeholder="Criterio compartido sin duplicar notas">
                            </div>
                            <div class="form-group col-5 mb-2">
                                <label class="small font-weight-bold mb-1">Fecha acordada</label>
                                <input type="date" name="fecha_acordada" class="form-control form-control-sm">
                            </div>
                        </div>
                        <button type="submit" class="btn btn-sm btn-success font-weight-bold btn-block" style="border-radius: 999px;">
                            Registrar Acuerdo de Articulación
                        </button>
                    </form>
                </div>
            </div>
        @endif
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     VISTA 4: RUTA DE HITOS Y REVISIONES (Criterio #13: 5 hitos exclusivos de Año 1)
     ══════════════════════════════════════════════════════════════════ --}}
@if($vistaActiva === 'hitos')
    <div class="ayllu-card p-4">
        <h3 class="h6 font-weight-bold mb-2">
            <i class="fas fa-flag-checkered text-danger mr-1"></i>
            Ruta de Hitos y Revisiones · Año {{ $anioFormativo }} (Ciclos {{ implode('–', $ciclosDelAnio) }})
        </h3>

        @if((int) $anioFormativo === 1 && !empty($definicionActiva?->hitos_documentados))
            <p class="small text-muted mb-3">
                Los siguientes <strong>cinco hitos del Proyecto Integrador 1 (Ciclos I–II)</strong> provienen literalmente de los dos documentos Word institucionales («2020 - 2024 Proyectos integradores.docx» y «Copia de 03 Proyectos INTEGRADORES.docx»). Los plazos relativos de la fuente se muestran separados de las fechas operativas aprobadas para el período {{ $periodoActual?->nombre ?? $anioLectivo }}.
            </p>

            @if($esCoordinador && $ejecucion)
                <form action="{{ route('ayllu.pi.ejecuciones.hitos', $ejecucion->id) }}" method="POST">
                    @csrf
            @endif

            <div class="table-responsive">
                <table class="table table-sm table-bordered small bg-white">
                    <thead class="thead-light">
                        <tr>
                            <th>Fase / Hito Documentado (Año 1)</th>
                            <th>Descripción en Fuente Word</th>
                            <th>Plazo Relativo Documental (Inmutable)</th>
                            <th>Fecha Operativa Aprobada ({{ $ejecucion?->anio_lectivo ?? '2026' }})</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach($definicionActiva->hitos_documentados as $hito)
                            @php
                                $ordenH = $hito['orden'] ?? $loop->iteration;
                                $fechaOp = $ejecucion?->fechas_hitos_operativos[$ordenH] ?? null;
                            @endphp
                            <tr>
                                <td class="font-weight-bold">{{ $hito['titulo'] }}</td>
                                <td>{{ $hito['descripcion'] }}</td>
                                <td>
                                    <span class="badge badge-light border">{{ $hito['plazo_documental'] }}</span>
                                </td>
                                <td>
                                    @if($esCoordinador && $ejecucion)
                                        <input type="date" name="fechas_operativas[{{ $ordenH }}]" value="{{ $fechaOp }}" class="form-control form-control-sm">
                                    @elseif($fechaOp)
                                        <span class="badge badge-success">{{ $fechaOp }}</span>
                                    @else
                                        <span class="text-muted">Pendiente de programación por coordinación</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            @if($esCoordinador && $ejecucion)
                    <div class="text-right mt-2">
                        <button type="submit" class="btn btn-sm btn-primary font-weight-bold px-4" style="border-radius: 999px;">
                            Guardar Fechas Operativas del Período
                        </button>
                    </div>
                </form>
            @endif
        @else
            <div class="p-3 rounded mb-3" style="background: #fff8eb; border-left: 4px solid #d97706;">
                <h4 class="h6 font-weight-bold mb-1" style="color: #7c2d12;">
                    <i class="fas fa-info-circle mr-1"></i>
                    Nota de fidelidad documental (Año {{ $anioFormativo }} · Ciclos {{ implode('–', $ciclosDelAnio) }})
                </h4>
                <p class="small mb-0" style="color: #4b5563;">
                    En las fuentes documentales («2020 - 2024 Proyectos integradores.docx» y «Copia de 03 Proyectos INTEGRADORES.docx»), la secuencia de cinco fases con plazos relativos está documentada únicamente para el <strong>Proyecto Integrador 1 (Ciclos I–II)</strong>. Conforme a los principios de integridad curricular, dicha secuencia no se copia ni se presenta como calendario documentado de los Años 2, 3 o 4. La mesa docente del Año {{ $anioFormativo }} acuerda sus fechas operativas en la pestaña de Bitácora y Articulación.
                </p>
            </div>
        @endif
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     VISTA 5: FUENTES, VERSIONES DOCUMENTALES Y ALERTAS DE CALIDAD (9 anomalías)
     ══════════════════════════════════════════════════════════════════ --}}
@if($vistaActiva === 'fuentes')
    <div class="ayllu-card p-4 mb-3">
        <h3 class="h6 font-weight-bold mb-2">
            <i class="fas fa-code-branch text-primary mr-1"></i>
            Versiones y Diferencias Documentales Preservadas · Año {{ $anioFormativo }} (Ciclos {{ implode('–', $ciclosDelAnio) }})
        </h3>
        <p class="small text-muted mb-3">
            Se conservan íntegras las diferencias entre las matrices anuales (`.xlsx`) y los dos documentos de fundamentación (`.docx`), sin fundirlas en una versión única artificial.
        </p>

        @foreach($versionesProyectoAnio as $verDef)
            <div class="p-3 rounded mb-2" style="border: 1px solid {{ (int) $definicionActiva?->id === (int) $verDef->id ? '#2e6b4e' : '#e6e0d6' }}; background: {{ (int) $definicionActiva?->id === (int) $verDef->id ? '#f3faf6' : '#fff' }};">
                <div class="d-flex flex-wrap justify-content-between align-items-center mb-1">
                    <div>
                        <span class="badge {{ (int) $definicionActiva?->id === (int) $verDef->id ? 'badge-success' : 'badge-secondary' }} mr-1">
                            v{{ $verDef->version_numero }} · {{ $verDef->codigo_fuente }}
                        </span>
                        <strong class="small">{{ $verDef->denominacion_original }}</strong>
                    </div>
                    <small class="text-muted">
                        Fuente: <strong>{{ $verDef->archivo_origen }}</strong> ({{ $verDef->referencia_ubicacion }})
                    </small>
                </div>
                <div class="small mb-1">
                    <strong>Propósito:</strong> {{ $verDef->proposito_original }}
                </div>
                <div class="small mb-1">
                    <strong>Producto integrador consignado:</strong> {{ $verDef->producto_integrador }}
                    · <strong>Competencias declaradas:</strong> {{ $verDef->competencias_declaradas }}
                </div>
                @if(!empty($verDef->diferencias_documentales))
                    <div class="mt-2 p-2 rounded small" style="background: #f8f6f2; font-size: 0.75rem;">
                        <strong>Contraste entre fuentes documentales:</strong>
                        <ul class="mb-0 pl-3 mt-1">
                            @foreach($verDef->diferencias_documentales as $kDif => $valDif)
                                @if(is_array($valDif))
                                    <li>
                                        <strong>{{ $valDif['archivo'] ?? $kDif }}:</strong>
                                        @foreach($valDif as $subK => $subV)
                                            @if($subK !== 'archivo')
                                                <span><em>{{ $subK }}:</em> {{ $subV }}; </span>
                                            @endif
                                        @endforeach
                                    </li>
                                @else
                                    <li>{{ $valDif }}</li>
                                @endif
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        @endforeach
    </div>

    {{-- Las 9 anomalías numéricas de 5 dígitos en Competencias --}}
    <div class="ayllu-card p-4 mb-3">
        <h3 class="h6 font-weight-bold mb-2">
            <i class="fas fa-exclamation-triangle text-warning mr-1"></i>
            Registro de las 9 Celdas de Competencias con Valores Numéricos Anómalos de 5 Dígitos
        </h3>
        <p class="small text-muted mb-2">
            Estas nueve celdas contienen valores numéricos de cinco dígitos en el XML de Excel compatibles con conversiones automáticas a fecha (`numFmtId=164/165`, formato `d, m, yy`). Se preserva el valor bruto exacto y su procedencia para revisión institucional sin reconstruir competencias por suposición (Criterio #4).
        </p>
        <div class="table-responsive">
            <table class="table table-sm table-bordered small bg-white">
                <thead class="thead-light">
                    <tr>
                        <th>#</th>
                        <th>Año / Ciclo</th>
                        <th>Archivo / Hoja / Celda</th>
                        <th>Curso o Módulo en Matriz</th>
                        <th>Valor Bruto XML</th>
                        <th>Formato Excel</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($todasAnomaliasCompetencias as $idxAnom => $anomAp)
                        <tr>
                            <td>{{ $idxAnom + 1 }}</td>
                            <td>Año {{ $anomAp->anio_formativo }} · Ciclo {{ $anomAp->ciclo_romano }}</td>
                            <td class="font-weight-bold">{{ $anomAp->archivo_fuente }} · {{ $anomAp->hoja_fuente }} · {{ $anomAp->celda_competencias }}</td>
                            <td>{{ $anomAp->curso_fuente_original }}</td>
                            <td><code>{{ $anomAp->competencias_fuente_bruto }}</code></td>
                            <td><code>{{ $anomAp->competencias_formato_excel }}</code></td>
                            <td><span class="badge badge-warning text-dark">Señalada para revisión</span></td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    {{-- Conciliación documental de las 114 posiciones --}}
    <div class="ayllu-card p-4">
        <h3 class="h6 font-weight-bold mb-2">
            <i class="fas fa-check-double text-success mr-1"></i>
            Conciliación Documental de las 114 Posiciones de Matriz
        </h3>
        <div class="row small">
            <div class="col-md-6">
                <ul class="mb-2">
                    <li><strong>Código de lote:</strong> <code>{{ $resumenConciliacion['codigo_lote'] }}</code></li>
                    <li><strong>Total posiciones importadas:</strong> {{ $resumenConciliacion['total_entradas'] }} / 114</li>
                    <li><strong>Celdas de competencias vacías («No consignado en la fuente»):</strong> {{ $resumenConciliacion['campos_vacios']['competencias_no_consignadas'] ?? 0 }}</li>
                    <li><strong>Celdas de estrategias vacías («No consignado en la fuente»):</strong> {{ $resumenConciliacion['campos_vacios']['estrategias_no_consignadas'] ?? 0 }}</li>
                    <li><strong>Celdas de producto vacías («No consignado en la fuente»):</strong> {{ $resumenConciliacion['campos_vacios']['productos_no_consignados'] ?? 0 }}</li>
                </ul>
            </div>
            <div class="col-md-6">
                <strong>Distribución verificada por ciclo:</strong>
                <div class="d-flex flex-wrap mt-1" style="gap: 0.35rem;">
                    @foreach($resumenConciliacion['conteo_por_ciclo'] as $cKey => $cCount)
                        <span class="badge badge-light border px-2 py-1">Ciclo {{ $cKey }}: {{ $cCount }}</span>
                    @endforeach
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     VISTA 6: PANEL DE COORDINACIÓN (Versiones, Importación, Asignaciones y Auditoría)
     ══════════════════════════════════════════════════════════════════ --}}
@if($vistaActiva === 'coordinacion' && $esCoordinador)
    <div class="row">
        <div class="col-lg-6 mb-3">
            <div class="ayllu-card p-3 mb-3">
                <h3 class="h6 font-weight-bold mb-2">
                    <i class="fas fa-sync-alt text-primary mr-1"></i>
                    Importación Idempotente y Adopción de Versión Curricular
                </h3>
                <p class="small text-muted mb-2">
                    Repetir una importación idéntica no duplica registros. Cambiar la versión adoptada actualiza la referencia de la ejecución conservando el historial.
                </p>
                <form action="{{ route('ayllu.pi.coordinacion.importar') }}" method="POST" class="mb-3">
                    @csrf
                    <button type="submit" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 999px;">
                        <i class="fas fa-check-circle mr-1"></i> Verificar / Re-ejecutar Conciliación Idempotente (114 entradas)
                    </button>
                </form>

                @if($ejecucion)
                    <form action="{{ route('ayllu.pi.coordinacion.adoptar') }}" method="POST" class="pt-2" style="border-top: 1px dashed #e6e0d6;">
                        @csrf
                        <input type="hidden" name="ejecucion_id" value="{{ $ejecucion->id }}">
                        <label class="small font-weight-bold mb-1">Versión adoptada para el Año {{ $anioFormativo }} ({{ $ejecucion->cohorte }})</label>
                        <div class="form-row">
                            <div class="col-8">
                                <select name="definicion_id" class="form-control form-control-sm">
                                    @foreach($versionesProyectoAnio as $vOpt)
                                        <option value="{{ $vOpt->id }}" {{ (int) $ejecucion->definicion_id === (int) $vOpt->id ? 'selected' : '' }}>
                                            v{{ $vOpt->version_numero }} ({{ $vOpt->codigo_fuente }}) — {{ \Illuminate\Support\Str::limit($vOpt->denominacion_original, 52) }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-4">
                                <button type="submit" class="btn btn-sm btn-dark btn-block font-weight-bold">Adoptar versión</button>
                            </div>
                        </div>
                    </form>
                @endif
            </div>

            <div class="ayllu-card p-3">
                <h4 class="h6 font-weight-bold mb-2">
                    <i class="fas fa-user-check text-success mr-1"></i>
                    Asignar Docente a la Mesa del Ciclo / Aporte
                </h4>
                <form action="{{ route('ayllu.pi.asignaciones.store') }}" method="POST">
                    @csrf
                    <input type="hidden" name="ejecucion_id" value="{{ $ejecucion?->id }}">
                    <div class="form-group mb-2">
                        <label class="small font-weight-bold mb-1">Docente institucional</label>
                        <select name="user_id" class="form-control form-control-sm" required>
                            @foreach($docentesDisponibles as $docItem)
                                <option value="{{ $docItem->user_id }}">
                                    {{ $docItem->nombre }} {{ $docItem->apellidos ?? '' }} ({{ $docItem->email }})
                                </option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-row">
                        <div class="form-group col-5 mb-2">
                            <label class="small font-weight-bold mb-1">Rol en la mesa</label>
                            <select name="rol_mesa" class="form-control form-control-sm">
                                <option value="docente_responsable_aporte">Docente responsable de aporte</option>
                                <option value="coordinador_ciclo">Coordinador de ciclo</option>
                                <option value="coordinador_proyecto">Coordinador de proyecto</option>
                                <option value="acompanante_equipo">Acompañante de equipo</option>
                            </select>
                        </div>
                        <div class="form-group col-7 mb-2">
                            <label class="small font-weight-bold mb-1">Aporte específico (o ciclo general)</label>
                            <select name="aporte_curso_id" class="form-control form-control-sm">
                                <option value="">-- Todo el Ciclo {{ $cicloSeleccionado }} --</option>
                                @foreach($aportesAnio as $apAsig)
                                    <option value="{{ $apAsig->id }}">Ciclo {{ $apAsig->ciclo_romano }} · #{{ $apAsig->posicion_global }} {{ $apAsig->curso_fuente_normalizado }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>
                    <input type="hidden" name="ciclo_romano" value="{{ $cicloSeleccionado }}">
                    <button type="submit" class="btn btn-sm btn-success font-weight-bold btn-block" style="border-radius: 999px;">
                        Asignar en Mesa Docente
                    </button>
                </form>
            </div>
        </div>

        <div class="col-lg-6 mb-3">
            <div class="ayllu-card p-3 mb-3">
                <h4 class="h6 font-weight-bold mb-2">
                    <i class="fas fa-users-cog text-info mr-1"></i>
                    Asignaciones Registradas y Revocación con Trazabilidad
                </h4>
                @forelse($asignacionesMesaActivas as $asig)
                    <div class="p-2 rounded mb-2 small d-flex flex-wrap justify-content-between align-items-center"
                         style="border: 1px solid #e6e0d6; background: {{ $asig->activo ? '#fff' : '#f5f2ed' }};">
                        <div>
                            <strong>{{ $asig->usuario?->name }} {{ $asig->usuario?->apellidos ?? '' }}</strong>
                            · <span class="badge {{ $asig->activo ? 'badge-success' : 'badge-secondary' }}">{{ $asig->activo ? 'ACTIVA' : 'REVOCADA' }}</span>
                            <div class="text-muted" style="font-size: 0.74rem;">
                                Rol: {{ $asig->rol_mesa }} · Ciclo {{ $asig->ciclo_romano }}
                                @if($asig->aporte)
                                    · Aporte #{{ $asig->aporte->posicion_global }} ({{ $asig->aporte->curso_fuente_normalizado }})
                                @endif
                            </div>
                        </div>
                        @if($asig->activo)
                            <form action="{{ route('ayllu.pi.asignaciones.revocar', $asig->id) }}" method="POST">
                                @csrf
                                <input type="hidden" name="motivo_revocacion" value="Cambio de asignación docente en el semestre">
                                <button type="submit" class="btn btn-xs btn-sm btn-outline-danger font-weight-bold py-0 px-2">
                                    Revocar acceso
                                </button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="small text-muted mb-0">Las asignaciones provienen de <code>curso_docente</code> y de las designaciones directas que registres aquí.</p>
                @endforelse
            </div>

            <div class="ayllu-card p-3">
                <h4 class="h6 font-weight-bold mb-2">
                    <i class="fas fa-shield-alt text-dark mr-1"></i>
                    Registro de Auditoría Reciente (Mesa de Proyectos)
                </h4>
                <div class="table-responsive">
                    <table class="table table-sm table-bordered small mb-0 bg-white">
                        <thead class="thead-light">
                            <tr>
                                <th>Fecha</th>
                                <th>Actor</th>
                                <th>Acción</th>
                                <th>Ciclo</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($auditoriaReciente as $evAud)
                                <tr>
                                    <td>{{ $evAud->created_at?->format('d/m H:i') }}</td>
                                    <td>{{ $evAud->actor?->name ?? 'Sistema CLI' }}</td>
                                    <td><code>{{ $evAud->accion }}</code></td>
                                    <td>{{ $evAud->ciclo_romano ?? '-' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endif

{{-- ══════════════════════════════════════════════════════════════════
     VISTA 7: MI PORTAFOLIO DOCENTE EIB Y EQUIPOS TAQ'E (Separado del Proyecto Anual)
     ══════════════════════════════════════════════════════════════════ --}}
@if($vistaActiva === 'portafolio')
    <div class="ayllu-card p-4 mb-3">
        <div class="d-flex flex-wrap justify-content-between align-items-center mb-3">
            <div>
                <h3 class="h6 font-weight-bold mb-0">
                    <i class="fas fa-users text-primary mr-1"></i>
                    Mis Equipos de Trabajo Taq’e
                </h3>
                <span class="small text-muted">Espacios de trabajo estudiantil vinculados al proyecto de tu ciclo.</span>
            </div>
            <a href="{{ route('taqe.index') }}" class="btn btn-sm btn-outline-primary font-weight-bold" style="border-radius: 999px;">
                Ir al módulo Taq’e completo
            </a>
        </div>

        @if($taqeGrupos->isNotEmpty())
            <div class="row">
                @foreach($taqeGrupos as $tg)
                    <div class="col-md-6 mb-2">
                        <div class="p-3 rounded" style="background: #eef4fa; border-left: 4px solid #33445a;">
                            <div class="font-weight-bold small">{{ $tg->nombre ?? $tg->nombre_grupo }}</div>
                            <div class="mt-2">
                                <a href="{{ route('taqe.show', $tg->id) }}" class="btn btn-xs btn-sm btn-primary py-0 px-2" style="border-radius: 999px; font-size: 0.74rem;">
                                    Abrir equipo Taq’e
                                </a>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @else
            <p class="small text-muted mb-0">No tienes grupos Taq’e registrados en este período.</p>
        @endif
    </div>

    <div class="ayllu-card p-4">
        <h3 class="h6 font-weight-bold mb-1">
            <i class="fas fa-folder-open text-warning mr-1"></i>
            Mi Portafolio Docente EIB (Trayectoria Personal entre Cursos y Años)
        </h3>
        <p class="small text-muted mb-3">
            La trayectoria de productos personales a lo largo de tus cursos se conserva en tu portafolio y se distingue de la mesa anual del Proyecto Integrador.
        </p>

        @forelse($misEntregasPortafolio as $itemP)
            <div class="p-3 rounded mb-2" style="border: 1px solid #e6e0d6; background: #fff;">
                <div class="d-flex flex-wrap justify-content-between align-items-center">
                    <div>
                        <strong class="small">{{ $itemP->titulo }}</strong>
                        <span class="badge badge-light border ml-1">{{ $itemP->curso?->nombre }}</span>
                        @if($itemP->en_portafolio)
                            <span class="badge badge-success ml-1">Destacado en mi Portafolio</span>
                        @endif
                    </div>
                    <form action="{{ route('ayllu.entregas.portafolio', $itemP->id) }}" method="POST">
                        @csrf
                        <button type="submit" class="btn btn-xs btn-sm btn-outline-secondary py-0 px-2" style="border-radius: 999px; font-size: 0.74rem;">
                            {{ $itemP->en_portafolio ? 'Retirar del portafolio' : 'Incluir en mi portafolio' }}
                        </button>
                    </form>
                </div>
                <p class="small text-muted mb-0 mt-1">{{ $itemP->descripcion_reflexion }}</p>
            </div>
        @empty
            <div class="p-3 rounded text-center" style="background: #f5f2ed;">
                <span class="small text-muted">Aún no has registrado entregas formativas en tus cursos.</span>
            </div>
        @endforelse
    </div>
@endif
@endsection
