{{-- Tarjeta de un curso PPD. Variables: $curso, $index, $docente, $periodoActual --}}
                        @php
                            $contador = $index + 1;
                            $silaboPdf = $curso->silabosPdf
                                ->where('periodo_actual_id', $periodoActual->id ?? null)
                                ->first();
                            $tieneSilaboPpd = $curso->relacionsilabo || $silaboPdf || $curso->silabo;

                            $competenciasSeleccionadas = $curso->competenciasSeleccionadas;
                            $necesitaSeleccionComp = $curso->competencias->count() > 3 && $competenciasSeleccionadas->isEmpty();
                            $competenciasACalificar = $competenciasSeleccionadas->isNotEmpty()
                                ? $competenciasSeleccionadas
                                : ($curso->competencias->count() <= 3 ? $curso->competencias : collect());
                            $idsACalificar = $competenciasACalificar->pluck('id')->all();
                            $urlCalificarPpd = route('competencias.calificar.ppd', [
                                'docente' => $docente->id,
                                'curso' => $curso->id,
                                'competencias' => $idsACalificar,
                            ]);
                        @endphp
                        <div class="card docente-ui-card docente-curso-card mb-3">
                            <div class="card-body p-3 p-md-4">
                                <div class="docente-curso-cabecera">
                                    <div class="d-flex align-items-start docente-curso-cabecera-info" style="gap:0.65rem;">
                                        <span class="docente-curso-index">{{ $contador }}</span>
                                        <div>
                                            <h3 class="docente-curso-nombre">{{ $curso->nombre }}</h3>
                                            <p class="docente-curso-meta">
                                                {{ $curso->ciclo->programa->nombre ?? 'Sin programa asignado' }}
                                                — {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                            </p>
                                        </div>
                                    </div>

                                    <div class="docente-curso-cabecera-progreso">
                                        @if ($curso->competencias->isNotEmpty())
                                            <span class="docente-cabecera-progreso-titulo">
                                                <i class="fas fa-chart-line mr-1"></i>Calificaciones de parciales
                                            </span>
                                        @endif
                                        @if ($necesitaSeleccionComp)
                                            <span class="docente-cabecera-progreso-hint">
                                                <i class="fas fa-info-circle mr-1"></i>Elige hasta 3 competencias
                                            </span>
                                        @elseif ($curso->competencias->isNotEmpty())
                                            @php $pctPPD = $curso->porcentajePPD(); @endphp
                                            <div class="docente-cabecera-progreso-grid">
                                                <div class="docente-cabecera-progreso-item">
                                                    <span class="docente-cabecera-progreso-label" style="color:#d97706;">Calificado</span>
                                                    <div class="progress docente-cal-progress docente-cal-progress-ppd">
                                                        <div class="progress-bar" role="progressbar" style="width:{{ $pctPPD }}%;"></div>
                                                    </div>
                                                    <span class="docente-cabecera-progreso-pct">{{ number_format($pctPPD, 0) }}%</span>
                                                </div>
                                            </div>
                                        @endif
                                    </div>

                                    <div class="docente-curso-cabecera-accion">
                                        @if ($curso->competencias->isNotEmpty())
                                            @if ($necesitaSeleccionComp)
                                                <a href="{{ route('curso.gestionar.competencias', $curso->id) }}"
                                                    class="btn btn-outline-primary btn-sm docente-btn-header-accion">
                                                    <i class="fas fa-tasks mr-1"></i> Elegir competencias
                                                </a>
                                            @else
                                                <a href="{{ $urlCalificarPpd }}" class="btn btn-primary btn-sm docente-btn-header-accion">
                                                    <i class="fas fa-pen mr-1"></i> Calificar
                                                </a>
                                                @if ($competenciasSeleccionadas->isNotEmpty())
                                                    <a href="{{ route('curso.gestionar.competencias', $curso->id) }}" class="docente-cal-select-link d-block mt-1">
                                                        Cambiar competencias
                                                    </a>
                                                @endif
                                            @endif
                                        @endif
                                    </div>
                                </div>
                                <div class="docente-curso-stats">
                                    <span class="docente-curso-stat"><i class="far fa-clock"></i>
                                        {{ $curso->horas }} horas</span>
                                    <span class="docente-curso-stat"><i class="fas fa-star"></i>
                                        {{ $curso->creditos }} créditos</span>
                                    <span class="docente-curso-stat"><i class="fas fa-tag"></i> {{ $curso->cc }}</span>
                                </div>

                                <div class="row docente-curso-sections">
                                    <div class="col-lg-3 docente-curso-section mb-3 mb-lg-0">
                                        <h4 class="docente-curso-section-title"><i class="fas fa-bullseye"></i>
                                            Competencias</h4>
                                        @if ($curso->competencias->isEmpty())
                                            <p class="docente-curso-empty-comp mb-0">Sin competencias asignadas.</p>
                                        @else
                                            @foreach ($curso->competencias as $competencia)
                                                @php $esACalificar = in_array($competencia->id, $idsACalificar); @endphp
                                                <a href="javascript:void(0)" class="docente-curso-comp-chip {{ $esACalificar ? 'is-a-calificar' : '' }}"
                                                    data-nombre="{{ $competencia->nombre }}"
                                                    data-descripcion="{{ addslashes($competencia->descripcion) }}"
                                                    data-capacidades="{!! addslashes($competencia->capacidades) !!}"
                                                    onclick="openModal(this)"
                                                    title="{{ $esACalificar ? 'Esta competencia se calificará este periodo' : 'Toca para ver la descripción completa' }}">
                                                    @if ($esACalificar)
                                                        <i class="fas fa-star docente-comp-chip-star"></i>
                                                    @endif
                                                    {{ $competencia->nombre }}
                                                </a>
                                            @endforeach
                                        @endif
                                    </div>
                                    <div class="col-lg-5 docente-curso-section mb-3 mb-lg-0">
                                        <h4 class="docente-curso-section-title">
                                            <i class="fas fa-file-pdf"></i> Sílabo
                                            <span
                                                class="docente-curso-status {{ $tieneSilaboPpd ? 'is-ok' : 'is-pending' }}">
                                                <i
                                                    class="fas {{ $tieneSilaboPpd ? 'fa-check-circle' : 'fa-exclamation-circle' }}"></i>
                                                {{ $tieneSilaboPpd ? 'Listo' : 'Pendiente' }}
                                            </span>
                                        </h4>
                                        <div class="docente-curso-silabo-actions">
                                            @if (!str_contains($curso->cc, 'Extracurricular'))
                                                @if (!$curso->relacionsilabo && !$silaboPdf && !$curso->silabo)
                                                    <p class="docente-curso-silabo-hint mb-2">Elige cómo registrarlo:</p>

                                                    <div class="docente-silabo-opcion">
                                                        <div class="docente-silabo-opcion-titulo"><i class="fas fa-laptop-code mr-1"></i>Crear desde el sistema</div>
                                                        <div class="docente-silabo-opcion-desc">Llenas el formulario del sistema paso a paso. Podrás editarlo cuando quieras después.</div>
                                                        <a href="{{ route('silabos.create', ['curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                            class="btn btn-primary btn-sm">
                                                            <i class="fas fa-plus mr-1"></i>Crear sílabo
                                                        </a>

                                                        @if ($curso->silabos->isNotEmpty())
                                                            <div class="docente-silabo-opcion-divisor"><span>o</span></div>
                                                            <div class="docente-silabo-opcion-desc">
                                                                <i class="fas fa-history mr-1"></i>Reusa el contenido de un sílabo tuyo de un periodo pasado para este mismo curso. Podrás ajustarlo antes de darlo por terminado.
                                                            </div>
                                                            <div class="d-flex flex-wrap align-items-center" style="gap:.4rem;">
                                                                <select class="form-control form-control-sm docente-silabo-reuse-select" id="reuse-select-{{ $curso->id }}" style="width:auto; max-width:220px;">
                                                                    @foreach ($curso->silabos->sortByDesc('id') as $sAnterior)
                                                                        <option value="{{ $sAnterior->id }}">{{ optional($sAnterior->periodoActual)->nombre ?? $sAnterior->periodo }}</option>
                                                                    @endforeach
                                                                </select>
                                                                <button type="button" class="btn btn-outline-primary btn-sm" onclick="reusarSilabo({{ $curso->id }})">
                                                                    <i class="fas fa-copy mr-1"></i> Reusar
                                                                </button>
                                                            </div>
                                                        @endif
                                                    </div>

                                                    <div class="docente-silabo-opcion-divisor"><span>o</span></div>

                                                    <div class="docente-silabo-opcion">
                                                        <div class="docente-silabo-opcion-titulo"><i class="fas fa-file-upload mr-1"></i>Subir un PDF ya elaborado</div>
                                                        <div class="docente-silabo-opcion-desc">Adjuntas tu propio archivo. <strong>No podrás editarlo</strong> desde el sistema — para corregirlo deberás volver a subir el PDF actualizado.</div>
                                                        <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                            method="POST" enctype="multipart/form-data">
                                                            @csrf
                                                            <button type="button"
                                                                class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                                onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                                <i class="fas fa-upload"></i> Elegir PDF
                                                            </button>
                                                            <input type="file" id="file-input-{{ $curso->id }}"
                                                                name="silabo" accept=".pdf" style="display: none;"
                                                                onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                            <button type="submit"
                                                                class="btn btn-primary btn-sm ml-1">Guardar</button>
                                                            <div id="file-name-{{ $curso->id }}"
                                                                class="docente-curso-file-name"></div>
                                                        </form>
                                                    </div>
                                                @elseif ($curso->relacionsilabo)
                                                    <a href="{{ route('silabos.show', $curso->relacionsilabo->id) }}"
                                                        class="btn btn-success btn-sm mb-1"><i
                                                            class="fa fa-eye"></i> Ver Sílabo</a>

                                                    <a href="{{ route('silabos.edit', ['silabo' => $curso->relacionsilabo->id, 'curso_id' => $curso->id, 'docente_id' => $docente->id]) }}"
                                                        class="btn btn-warning btn-sm mb-1"><i
                                                            class="fa fa-edit"></i> Editar Sílabo</a>
                                                    <a href="#" class="btn btn-danger btn-sm mb-1" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('silabos.destroy', $curso->relacionsilabo->id) }}"
                                                        title="Eliminar sílabo y volver a elegir cómo registrarlo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                @elseif ($silaboPdf)
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display:none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-success btn-sm" title="Ver Sílabo PDF"
                                                        href="{{ asset('docentes/silabo/' . $silaboPdf->pdf) }}"
                                                        target="_blank">
                                                        <i class="fa fa-eye fa-sm"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                        title="Eliminar sílabo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                    <div class="docente-silabo-pdf-nota">
                                                        <i class="fas fa-info-circle"></i> PDF subido — no editable, solo puedes reemplazarlo
                                                    </div>
                                                @elseif ($curso->silabo)
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-success btn-sm" title="Ver Sílabo"
                                                        href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                        target="_blank">
                                                        <i class="fa fa-eye fa-sm"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                        title="Eliminar sílabo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                    <div class="docente-silabo-pdf-nota">
                                                        <i class="fas fa-info-circle"></i> PDF subido — no editable, solo puedes reemplazarlo
                                                    </div>
                                                @endif
                                            @else
                                                <p class="docente-curso-silabo-hint mb-2">Extracurricular permite
                                                    sólo subir archivo en PDF.</p>
                                                @if ($curso->silabo)
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data"
                                                        style="display:inline-block;">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-sync"></i> Actualizar
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit" class="btn btn-primary btn-sm"
                                                            title="Editar">
                                                            <i class="fa fa-upload fa-sm"></i>
                                                        </button>
                                                    </form>

                                                    <a class="btn btn-success btn-sm" title="Ver Sílabo"
                                                        href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                        target="_blank">
                                                        <i class="fa fa-eye fa-sm"></i>
                                                    </a>
                                                    <a href="#" class="btn btn-danger btn-sm" data-toggle="modal"
                                                        data-target="#confirmDeleteModal"
                                                        data-href="{{ route('cursos.destroySilabo', ['curso' => $curso->id]) }}"
                                                        title="Eliminar sílabo">
                                                        <i class="fa fa-trash fa-sm"></i>
                                                    </a>
                                                @else
                                                    <form action="{{ route('cursos.uploadSilabo', ['curso' => $curso->id]) }}"
                                                        method="POST" enctype="multipart/form-data">
                                                        @csrf
                                                        <button type="button"
                                                            class="btn btn-outline-secondary btn-sm docente-curso-file-btn"
                                                            onclick="document.getElementById('file-input-{{ $curso->id }}').click();">
                                                            <i class="fas fa-upload"></i> Subir PDF
                                                        </button>
                                                        <input type="file" id="file-input-{{ $curso->id }}"
                                                            name="silabo" accept=".pdf" style="display: none;"
                                                            onchange="updateFileName(this, 'file-name-{{ $curso->id }}')">
                                                        <button type="submit"
                                                            class="btn btn-primary btn-sm ml-1">Guardar
                                                            PDF</button>
                                                        <div id="file-name-{{ $curso->id }}"
                                                            class="docente-curso-file-name"></div>
                                                    </form>
                                                @endif
                                            @endif
                                        </div>
                                    </div>
                                    <div class="col-lg-4 docente-curso-section">
                                        <h4 class="docente-curso-section-title"><i class="fab fa-google"></i>
                                            Classroom</h4>
                                        <form action="{{ route('cursos.classroomClaveCRUD', ['curso' => $curso->id]) }}"
                                            method="POST" class="docente-curso-classroom-form" id="classroom-form-{{ $curso->id }}">
                                            @csrf
                                            @method('POST')
                                            <div class="input-group input-group-sm mb-2 docente-classroom-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-link fa-xs"></i></span>
                                                </div>
                                                <input type="text" name="classroom"
                                                    class="form-control form-control-sm docente-classroom-input"
                                                    value="{{ $curso->classroom }}"
                                                    placeholder="Enlace de Classroom" readonly>
                                            </div>
                                            <div class="input-group input-group-sm mb-2 docente-classroom-group">
                                                <div class="input-group-prepend">
                                                    <span class="input-group-text"><i class="fas fa-key fa-xs"></i></span>
                                                </div>
                                                <input type="text" name="clave"
                                                    class="form-control form-control-sm docente-classroom-input"
                                                    value="{{ $curso->clave }}" placeholder="Código Classroom" readonly>
                                            </div>

                                            <div class="docente-classroom-actions" id="classroom-actions-view-{{ $curso->id }}">
                                                <button type="button" class="btn btn-outline-primary btn-sm"
                                                    onclick="habilitarEdicionClassroom({{ $curso->id }})">
                                                    <i class="fas fa-pen mr-1"></i> Editar
                                                </button>
                                                <span class="docente-classroom-hint">Protegido contra ediciones accidentales</span>
                                            </div>

                                            <div class="docente-classroom-actions d-none justify-content-between" id="classroom-actions-edit-{{ $curso->id }}">
                                                @php
                                                    $buttonText =
                                                        $curso->classroom || $curso->clave ? 'Actualizar' : 'Subir';
                                                @endphp
                                                <div>
                                                    <button type="submit" class="btn btn-primary btn-sm" disabled>{{ $buttonText }}</button>
                                                    <button type="button" class="btn btn-outline-secondary btn-sm"
                                                        onclick="cancelarEdicionClassroom({{ $curso->id }})">Cancelar</button>
                                                </div>
                                                <button type="button" class="btn btn-outline-danger btn-sm" disabled
                                                    title="Eliminar"
                                                    onclick="confirmarEliminarClassroom('classroom-form-{{ $curso->id }}')">
                                                    <i class="fas fa-trash fa-xs"></i>
                                                </button>
                                            </div>
                                        </form>
                                    </div>
                                </div>

                            </div>
                        </div>
