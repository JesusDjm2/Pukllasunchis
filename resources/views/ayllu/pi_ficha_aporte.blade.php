@extends('ayllu.layout')

@section('ayllu_contenido')
@php
    $estadoInfo = $estadosDesarrollo[$aporte->estado_desarrollo] ?? $estadosDesarrollo['sin_registrar'];
@endphp

<div class="mb-3 d-flex flex-wrap justify-content-between align-items-center" style="gap: 0.5rem;">
    <a href="{{ route('ayllu.proyecto', ['anio' => $aporte->anio_formativo, 'ciclo' => $aporte->ciclo_romano, 'programa' => $aporte->agrupacion_programa_codigo === 'FORMACION_GENERAL' ? 'TODOS' : $aporte->agrupacion_programa_codigo, 'vista' => 'matriz']) }}"
       class="btn btn-sm btn-outline-secondary font-weight-bold" style="border-radius: 999px;">
        <i class="fas fa-arrow-left mr-1"></i> Volver a la Matriz del Ciclo {{ $aporte->ciclo_romano }}
    </a>
    <div>
        <span class="badge badge-dark mr-1">Posición #{{ $aporte->posicion_global }} / 114</span>
        <span class="badge badge-{{ $estadoInfo['badge'] }} px-2 py-1">{{ $estadoInfo['label'] }}</span>
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     1. FICHA DE PROCEDENCIA DOCUMENTAL Y APORTE PREVISTO EN LA MATRIZ
     ══════════════════════════════════════════════════════════════════ --}}
<div class="ayllu-card p-4 mb-3" style="border-top: 4px solid #35583f;">
    <div class="d-flex flex-wrap justify-content-between align-items-start mb-2" style="gap: 0.75rem;">
        <div>
            <span class="badge badge-info mb-1">
                Año {{ $aporte->anio_formativo }} · Ciclo {{ $aporte->ciclo_romano }} · {{ $aporte->agrupacion_programa_original }}
                @if($aporte->es_aporte_comun)
                    (Formación General común al ciclo)
                @endif
            </span>
            <h2 class="h4 font-weight-bold mb-1" style="color: #161c22; white-space: pre-line;">{{ $aporte->curso_fuente_original }}</h2>
            <div class="small text-muted">
                <strong>Proyecto Anual:</strong> {{ $aporte->definicion?->denominacion_original }}
                · <strong>Producto Integrador Anual:</strong> {{ $aporte->producto_integrador_fuente }}
            </div>
        </div>
        <div class="p-2 rounded small" style="background: #f5f2ed; border: 1px solid #e6e0d6; font-size: 0.75rem;">
            <div><strong>Archivo fuente:</strong> {{ $aporte->archivo_fuente }}</div>
            <div><strong>Hoja y celdas:</strong> {{ $aporte->hoja_fuente }} · Curso: <code>{{ $aporte->celda_curso }}</code> | Comp.: <code>{{ $aporte->celda_competencias }}</code> | Prod.: <code>{{ $aporte->celda_producto }}</code></div>
            <div><strong>Variante normalizada:</strong> {{ $aporte->curso_fuente_normalizado }}</div>
        </div>
    </div>

    <div class="row mt-3">
        <div class="col-md-4 mb-2">
            <div class="p-3 rounded h-100" style="background: #fbfaf8; border: 1px solid #e6e0d6;">
                <div class="small font-weight-bold text-uppercase text-muted mb-1">Competencias en la Matriz ({{ $aporte->celda_competencias }})</div>
                @if($aporte->es_anomalia_competencia)
                    <div class="p-2 rounded small" style="background: #fff3cd; border: 1px solid #f59e0b; color: #7c2d12;">
                        <strong><i class="fas fa-exclamation-triangle mr-1"></i>Anomalía numérica de 5 dígitos detectada:</strong>
                        <div>Valor bruto en XML: <code>{{ $aporte->competencias_fuente_bruto }}</code></div>
                        <div>Formato Excel: <code>{{ $aporte->competencias_formato_excel }}</code></div>
                        <div class="mt-1" style="font-size: 0.73rem;">
                            {{ $aporte->detalle_anomalia_competencia['observacion'] ?? 'Señalada para revisión institucional sin reconstruir competencias por suposición.' }}
                        </div>
                    </div>
                @elseif($aporte->competencias_fuente_mostrar === 'No consignado en la fuente')
                    <span class="badge badge-light border text-muted font-italic">No consignado en la fuente</span>
                @else
                    <div class="h6 font-weight-bold mb-0">{{ $aporte->competencias_fuente_mostrar }}</div>
                @endif
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="p-3 rounded h-100" style="background: #fbfaf8; border: 1px solid #e6e0d6;">
                <div class="small font-weight-bold text-uppercase text-muted mb-1">
                    Estrategias / Acciones Formativas {{ $aporte->celda_estrategias ? '(' . $aporte->celda_estrategias . ')' : '' }}
                </div>
                @if($aporte->estrategias_mostrar === 'No consignado en la fuente')
                    <span class="badge badge-light border text-muted font-italic">No consignado en la fuente</span>
                @else
                    <div class="small" style="white-space: pre-line;">{{ $aporte->estrategias_mostrar }}</div>
                @endif
            </div>
        </div>

        <div class="col-md-4 mb-2">
            <div class="p-3 rounded h-100" style="background: #f5f2ed; border: 1px solid #dfd7c8; border-left: 4px solid #9e3d22;">
                <div class="small font-weight-bold text-uppercase text-muted mb-1">
                    Producto Previsto que Aporta ({{ $aporte->celda_producto }})
                </div>
                @if($aporte->producto_previsto_mostrar === 'No consignado en la fuente')
                    <span class="badge badge-light border text-muted font-italic">No consignado en la fuente</span>
                @else
                    <div class="small font-weight-bold" style="white-space: pre-line; color: #161c22;">{{ $aporte->producto_previsto_mostrar }}</div>
                @endif
            </div>
        </div>
    </div>

    @if(!empty($aporte->observaciones_calidad))
        <div class="mt-2 p-2 rounded small" style="background: #fff8eb; border: 1px dashed #d97706; font-size: 0.76rem;">
            <strong><i class="fas fa-clipboard-check mr-1 text-warning"></i>Notas de calidad documental y trazabilidad:</strong>
            <ul class="mb-0 pl-3 mt-1">
                @foreach($aporte->observaciones_calidad as $obs)
                    <li>{{ $obs }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- Correspondencia con el catálogo institucional y docentes asignados actualmente --}}
    <div class="mt-3 pt-3 d-flex flex-wrap justify-content-between align-items-center" style="border-top: 1px solid #e6e0d6; gap: 0.5rem;">
        <div class="small">
            <strong>Correspondencia con el catálogo institucional (<code>cursos</code>):</strong>
            @if($aporte->cursoInstitucional)
                <a href="{{ route('ayllu.cursos.show', $aporte->cursoInstitucional->id) }}" class="font-weight-bold text-primary">
                    <i class="fas fa-external-link-alt mr-1"></i>{{ $aporte->cursoInstitucional->nombre }}
                </a>
                @if($aporte->cursoInstitucional->docentes->isNotEmpty())
                    · <strong>Docentes asignados actualmente en <code>curso_docente</code>:</strong>
                    {{ $aporte->cursoInstitucional->docentes->map(fn($d) => trim($d->nombre . ' ' . ($d->apellidos ?? '')))->implode(', ') }}
                @else
                    · <span class="text-muted">Sin docente asignado en el período actual</span>
                @endif
            @else
                <span class="text-muted">{{ $aporte->correspondencia_sugerida_json['nota'] ?? 'Pendiente de confirmación por coordinación' }}</span>
            @endif
        </div>

        @if($aporte->versionIntegrada)
            <div class="badge badge-success px-3 py-2">
                <i class="fas fa-check-circle mr-1"></i>
                Integrado al producto anual: v{{ $aporte->versionIntegrada->numero_version }}
                (validado por {{ $aporte->integradoPor?->name ?? 'Coordinación' }})
            </div>
        @endif
    </div>
</div>

{{-- ══════════════════════════════════════════════════════════════════
     2. AJUSTE OPERATIVO DE LA MESA Y REGISTRO DE NUEVA VERSIÓN DE EVIDENCIA
     ══════════════════════════════════════════════════════════════════ --}}
<div class="row">
    <div class="col-lg-5 mb-3">
        {{-- Ajustes operativos de la mesa docente --}}
        <div class="ayllu-card p-3 mb-3">
            <h3 class="h6 font-weight-bold mb-2">
                <i class="fas fa-handshake text-success mr-1"></i>
                Ajuste Operativo Acordado en la Mesa Docente
            </h3>
            <p class="small text-muted mb-2">
                Se distingue el producto previsto en la matriz (inmutable) de las precisiones operativas acordadas por el docente del curso o la mesa del ciclo.
            </p>

            @if($puedeEditarAporte)
                <form action="{{ route('ayllu.pi.aportes.update', $aporte->id) }}" method="POST">
                    @csrf
                    @method('PUT')
                    <div class="form-group mb-2">
                        <label for="txtAjusteOp" class="small font-weight-bold mb-1">Acuerdo operativo / Contextualización del semestre</label>
                        <textarea id="txtAjusteOp" name="ajuste_operativo_mesa" rows="3" class="form-control form-control-sm" placeholder="Precisa acuerdos de instrumento, fechas de recojo o articulación con otros cursos sin alterar la celda original de la matriz...">{{ $aporte->ajuste_operativo_mesa }}</textarea>
                    </div>

                    @if($esCoordinador)
                        <div class="form-group mb-2">
                            <label for="selCursoCat" class="small font-weight-bold mb-1">Confirmar equivalencia en catálogo institucional (Coordinación)</label>
                            <select id="selCursoCat" name="curso_institucional_id" class="form-control form-control-sm">
                                <option value="">-- Sin enlazar / Mantener como antecedente documental --</option>
                                @foreach($cursosCatalogoCiclo as $cCat)
                                    <option value="{{ $cCat->id }}" {{ (int) $aporte->curso_institucional_id === (int) $cCat->id ? 'selected' : '' }}>
                                        [{{ $cCat->ciclo?->programa?->nombre }}] {{ $cCat->nombre }}
                                    </option>
                                @endforeach
                            </select>
                        </div>
                    @endif

                    <button type="submit" class="btn btn-sm btn-outline-success font-weight-bold btn-block" style="border-radius: 999px;">
                        Guardar Ajuste Operativo
                    </button>
                </form>
            @else
                <div class="p-2 rounded small" style="background: #f5f2ed;">
                    {{ $aporte->ajuste_operativo_mesa ?: 'Aún no se han registrado ajustes operativos adicionales sobre la matriz para este aporte.' }}
                </div>
            @endif
        </div>

        {{-- Formulario para registrar nueva versión de evidencia --}}
        @if($puedeRegistrarEvidencia)
            <div class="ayllu-card p-3">
                <h3 class="h6 font-weight-bold mb-2">
                    <i class="fas fa-cloud-upload-alt text-primary mr-1"></i>
                    Registrar Nueva Versión de Evidencia / Avance
                </h3>
                <form action="{{ route('ayllu.pi.versiones.store', $aporte->id) }}" method="POST" enctype="multipart/form-data">
                    @csrf
                    <div class="form-group mb-2">
                        <label for="inpTituloVer" class="small font-weight-bold mb-1">Título de la versión</label>
                        <input id="inpTituloVer" type="text" name="titulo_version" class="form-control form-control-sm" required maxlength="220" placeholder="Ej. Guía de entrevista validada / Avance de mapa parlante">
                    </div>

                    <div class="form-group mb-2">
                        <label for="selAudienciaVer" class="small font-weight-bold mb-1">Audiencia y visibilidad del registro</label>
                        <select id="selAudienciaVer" name="audiencia" class="form-control form-control-sm" onchange="actualizarAvisoAudiencia(this.value)">
                            @if($puedeEditarAporte)
                                <option value="mesa_docente_ciclo" selected>Mesa docente del ciclo (docentes asignados y coordinación)</option>
                                <option value="borrador_personal">Borrador personal (solo visible para mí)</option>
                                <option value="equipo_estudiantes">Equipo de estudiantes y docentes acompañantes</option>
                                <option value="ciclo_completo">Ciclo completo (orientación/avance visible para el ciclo)</option>
                            @else
                                <option value="equipo_estudiantes" selected>Equipo de estudiantes y docentes acompañantes</option>
                                <option value="borrador_personal">Borrador personal (solo visible para mí)</option>
                            @endif
                        </select>
                        <div id="avisoAudienciaBox" class="mt-1 p-2 rounded small" style="background: #eef6f1; color: #1b4332; font-size: 0.74rem;">
                            <i class="fas fa-eye mr-1"></i>
                            <strong>¿Quién verá este contenido?</strong>
                            <span id="avisoAudienciaTexto">
                                {{ $puedeEditarAporte ? 'Solo los docentes asignados al Ciclo ' . $aporte->ciclo_romano . ' y la coordinación autorizada. No será visible para estudiantes ni docentes de otros ciclos.' : 'Los integrantes de tu equipo y los docentes acompañantes del curso.' }}
                            </span>
                        </div>
                    </div>

                    <div class="form-group mb-2">
                        <label for="txtDescAvance" class="small font-weight-bold mb-1">Descripción del avance y criterios trabajados</label>
                        <textarea id="txtDescAvance" name="descripcion_avance" rows="3" class="form-control form-control-sm" required placeholder="Describe el estado del producto, hallazgos y cómo tributa al producto anual..."></textarea>
                    </div>

                    <div class="form-group mb-2">
                        <label for="inpEnlaceRec" class="small font-weight-bold mb-1">Enlace a recurso autorizado (Drive / Canva / Doc opcional)</label>
                        <input id="inpEnlaceRec" type="url" name="enlace_recurso" class="form-control form-control-sm" placeholder="https://...">
                    </div>

                    <div class="form-group mb-2">
                        <label for="inpArchivoEv" class="small font-weight-bold mb-1">Archivo adjunto protegido en servidor (opcional)</label>
                        <input id="inpArchivoEv" type="file" name="archivo_evidencia" class="form-control-file small">
                        <small class="text-muted d-block" style="font-size: 0.7rem;">
                            Se almacena en disco privado y solo se descarga tras verificar permisos de audiencia y circulación.
                        </small>
                    </div>

                    <div class="form-group mb-2">
                        <label for="txtAutoriaCom" class="small font-weight-bold mb-1">Reconocimiento de autoría comunitaria / Acuerdos de uso</label>
                        <input id="txtAutoriaCom" type="text" name="reconocimiento_autoria_comunitaria" class="form-control form-control-sm" placeholder="Sabios comunitarios, familias o actores cuyos testimonios se reconocen">
                    </div>

                    <div class="form-group mb-2">
                        <label for="selRestriccion" class="small font-weight-bold mb-1">Condición de circulación de saberes y archivos</label>
                        <select id="selRestriccion" name="restriccion_circulacion" class="form-control form-control-sm">
                            <option value="ninguna">Circulación según la audiencia seleccionada</option>
                            <option value="uso_interno_ciclo">Uso formativo interno del ciclo</option>
                            <option value="reserva_comunitaria">Reserva comunitaria (restringe descarga a autoría y mesa docente)</option>
                        </select>
                    </div>

                    <div class="form-group mb-2">
                        <label for="txtNecArt" class="small font-weight-bold mb-1">Necesidades de articulación con otros cursos (opcional)</label>
                        <input id="txtNecArt" type="text" name="necesidades_articulacion" class="form-control form-control-sm" placeholder="Ej. Requiere validación de transcripción quechua con Lengua Indígena">
                    </div>

                    <div class="custom-control custom-checkbox mb-2">
                        <input type="checkbox" class="custom-control-input" id="chkSolRev" name="solicitar_revision_compartida" value="1" checked>
                        <label class="custom-control-label small font-weight-bold" for="chkSolRev">
                            Marcar como «Para revisión compartida» en la mesa del ciclo
                        </label>
                    </div>

                    <div class="custom-control custom-checkbox mb-2 pt-2" style="border-top: 1px dashed #d5e6dc;">
                        <input type="checkbox" class="custom-control-input" id="chkMenoresVer" name="confirmacion_politica_menores" value="1" checked>
                        <label class="custom-control-label text-muted" for="chkMenoresVer" style="font-size: 0.72rem;">
                            <i class="fas fa-child text-warning mr-1"></i>Confirmo el respeto a la protección de imagen/datos de menores y a los acuerdos comunitarios de circulación.
                        </label>
                    </div>

                    <button type="submit" class="btn btn-sm btn-primary font-weight-bold btn-block" style="border-radius: 999px;">
                        <i class="fas fa-save mr-1"></i> Registrar Versión en el Historial
                    </button>
                </form>
            </div>
        @endif
    </div>

    {{-- ══════════════════════════════════════════════════════════════════
         3. HISTORIAL DE VERSIONES, REVISIÓN E INTEGRACIÓN AL PRODUCTO ANUAL
         ══════════════════════════════════════════════════════════════════ --}}
    <div class="col-lg-7 mb-3">
        <div class="ayllu-card p-3 mb-3">
            <h3 class="h6 font-weight-bold mb-2">
                <i class="fas fa-history text-primary mr-1"></i>
                Historial de Versiones de Evidencia ({{ $versionesVisibles->count() }})
            </h3>

            @forelse($versionesVisibles as $ver)
                @php
                    $audInfo = $audienciasCatalogo[$ver->audiencia] ?? ['label' => $ver->audiencia, 'badge' => 'secondary'];
                    $esIntegrada = (int) $aporte->version_integrada_id === (int) $ver->id;
                @endphp
                <div class="p-3 rounded mb-3" style="border: 1.5px solid {{ $esIntegrada ? '#15803d' : '#e6e0d6' }}; background: {{ $esIntegrada ? '#f3faf6' : '#fff' }};">
                    <div class="d-flex flex-wrap justify-content-between align-items-start mb-1" style="gap: 0.5rem;">
                        <div>
                            <span class="badge badge-dark mr-1">Versión v{{ $ver->numero_version }}</span>
                            <span class="badge badge-{{ $audInfo['badge'] }} mr-1">{{ $audInfo['label'] }}</span>
                            <span class="badge badge-light border mr-1">{{ strtoupper(str_replace('_', ' ', $ver->estado_version)) }}</span>
                            @if($esIntegrada)
                                <span class="badge badge-success"><i class="fas fa-star mr-1"></i>Versión integrada al producto anual</span>
                            @endif
                        </div>
                        <small class="text-muted">
                            Por {{ $ver->autor?->name }} ({{ $ver->autoria_tipo }}) · {{ $ver->created_at?->format('d/m/Y H:i') }}
                        </small>
                    </div>

                    <h4 class="h6 font-weight-bold mt-1 mb-1">{{ $ver->titulo_version }}</h4>
                    <p class="small mb-2" style="white-space: pre-line;">{{ $ver->descripcion_avance }}</p>

                    @if($ver->reconocimiento_autoria_comunitaria)
                        <div class="small mb-1" style="color: #7c2d12;">
                            <i class="fas fa-Hands-helping mr-1"></i>
                            <strong>Autoría y saberes comunitarios reconocidos:</strong> {{ $ver->reconocimiento_autoria_comunitaria }}
                            ({{ str_replace('_', ' ', $ver->restriccion_circulacion) }})
                        </div>
                    @endif

                    @if($ver->necesidades_articulacion)
                        <div class="small text-muted mb-2">
                            <strong>Necesidad de articulación:</strong> {{ $ver->necesidades_articulacion }}
                        </div>
                    @endif

                    <div class="d-flex flex-wrap align-items-center mb-2" style="gap: 0.75rem;">
                        @if($ver->enlace_recurso && $ver->restriccion_circulacion !== 'autorizacion_retirada')
                            <a href="{{ $ver->enlace_recurso }}" target="_blank" rel="noopener" class="small font-weight-bold text-primary">
                                <i class="fas fa-external-link-alt mr-1"></i> Abrir enlace de evidencia
                            </a>
                        @endif
                        @if($ver->archivo_privado_path)
                            <a href="{{ route('ayllu.pi.versiones.archivo', $ver->id) }}" class="btn btn-xs btn-sm btn-outline-dark py-0 px-2" style="border-radius: 999px; font-size: 0.74rem;">
                                <i class="fas fa-lock mr-1"></i> Descargar archivo protegido ({{ $ver->archivo_nombre_original ?: 'Evidencia' }})
                            </a>
                        @endif
                    </div>

                    @if($ver->comentario_revision)
                        <div class="p-2 rounded small mb-2" style="background: #eef4fa; border-left: 3px solid #2563eb;">
                            <strong>Revisión ({{ $ver->revisor?->name ?? 'Mesa Docente' }} · {{ $ver->revisado_at?->format('d/m/Y H:i') }}):</strong>
                            <div>{{ $ver->comentario_revision }}</div>
                        </div>
                    @endif

                    {{-- Formulario de Revisión / Devolución a desarrollo / Integración al producto anual --}}
                    @if($puedeRevisarAporte)
                        <form action="{{ route('ayllu.pi.versiones.revisar', $ver->id) }}" method="POST" class="mt-2 pt-2" style="border-top: 1px dashed #d8d0c5;">
                            @csrf
                            <div class="form-row align-items-center">
                                <div class="col-md-4 mb-1 mb-md-0">
                                    <select name="decision" class="form-control form-control-sm">
                                        <option value="revisado">Marcar como Revisado</option>
                                        <option value="devuelto_a_desarrollo">Devolver a desarrollo (ajustar)</option>
                                        @if($puedeIntegrarAnual)
                                            <option value="integrado_al_producto_anual">Validar e Integrar al Producto Anual</option>
                                        @endif
                                    </select>
                                </div>
                                <div class="col-md-5 mb-1 mb-md-0">
                                    <input type="text" name="comentario_revision" class="form-control form-control-sm" required placeholder="Comentario o acuerdo de revisión...">
                                </div>
                                <div class="col-md-3">
                                    <button type="submit" class="btn btn-sm btn-outline-primary btn-block font-weight-bold">
                                        Registrar revisión
                                    </button>
                                </div>
                            </div>
                        </form>
                    @endif

                    {{-- Control de retiro de autorización o cambio de restricción para el autor o coordinador --}}
                    @if((int) $ver->autor_user_id === (int) $user->id || $esCoordinador)
                        <form action="{{ route('ayllu.pi.versiones.restriccion', $ver->id) }}" method="POST" class="mt-2 text-right">
                            @csrf
                            <input type="hidden" name="restriccion_circulacion" value="{{ $ver->restriccion_circulacion === 'autorizacion_retirada' ? 'ninguna' : 'autorizacion_retirada' }}">
                            <button type="submit" class="btn btn-link btn-sm p-0 text-danger" style="font-size: 0.72rem;">
                                <i class="fas fa-user-slash mr-1"></i>
                                {{ $ver->restriccion_circulacion === 'autorizacion_retirada' ? 'Restaurar autorización de circulación' : 'Retirar autorización de circulación / descarga' }}
                            </button>
                        </form>
                    @endif
                </div>
            @empty
                <div class="p-4 rounded text-center" style="background: #f5f2ed;">
                    <p class="small text-muted mb-0">
                        Este aporte se encuentra en estado <strong>Sin registrar</strong> (o no tiene versiones visibles para tu audiencia). Ningún avance ficticio se crea al importar.
                    </p>
                </div>
            @endforelse
        </div>
    </div>
</div>

<script>
function actualizarAvisoAudiencia(valor) {
    var mapa = {
        'borrador_personal': 'Solo tú (y colaboradores expresamente autorizados). Ni otros docentes ni estudiantes podrán verlo.',
        'mesa_docente_ciclo': 'Los docentes asignados al Ciclo {{ $aporte->ciclo_romano }} y la coordinación autorizada. Oculto para estudiantes y docentes de otros ciclos.',
        'equipo_estudiantes': 'Los integrantes del equipo de estudiantes y los docentes acompañantes del curso.',
        'ciclo_completo': 'Docentes y estudiantes matriculados en el Ciclo {{ $aporte->ciclo_romano }}. Los archivos con reserva comunitaria mantienen su restricción.'
    };
    var span = document.getElementById('avisoAudienciaTexto');
    if (span && mapa[valor]) {
        span.textContent = mapa[valor];
    }
}
</script>
@endsection
