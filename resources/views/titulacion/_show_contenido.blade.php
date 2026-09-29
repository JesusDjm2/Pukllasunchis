{{-- Contenido del detalle de un egresado/alumno activo — compartido entre
     la página completa (titulacion.show, para acceso directo/bookmark) y el
     acordeón de cada fila en /titulacion. $enModal quita el padding/borde
     de página completa; $enAcordeon además oculta el encabezado con el
     nombre (ya se ve en la fila de la tabla que lo contiene). --}}
@php
    $enModal = $enModal ?? false;
    $enAcordeon = $enAcordeon ?? false;
@endphp

<div class="{{ $enModal ? '' : 'container-fluid bg-white' }}">
    @unless ($enAcordeon)
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 {{ $enModal ? '' : 'pt-3 pb-3' }}"
            style="{{ $enModal ? '' : 'border-bottom: 1px dashed #80808078' }}">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-user-graduate mr-2" style="color:#4e73df;"></i>{{ $egresado->apellidos }}, {{ $egresado->name }}
                </h4>
                <small class="text-muted">{{ $egresado->programa->nombre ?? 'Sin programa' }} @if($egresado->ciclo) &middot; {{ $egresado->ciclo->nombre }} @endif</small>
            </div>
            @unless ($enModal)
                <a href="{{ route('titulacion.index') }}" class="btn btn-sm btn-outline-secondary"><i class="fa fa-arrow-left fa-sm mr-1"></i>Volver al listado</a>
            @endunless
        </div>
    @endunless

    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show" role="alert">
            {{ Session::get('success') }}
            <a type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></a>
        </div>
    @endif
    @if ($errors->any())
        <div class="alert alert-danger">
            <ul class="mb-0">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    {{-- ── Estado de titulación ─────────────────────────────── --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light font-weight-bold"><i class="fas fa-flag mr-1"></i>Expediente y estado de titulación</div>
        <div class="card-body">
            <form method="POST" action="{{ route('titulacion.estado.actualizar', $egresado->id) }}" class="form-inline">
                @csrf @method('PUT')
                <input type="text" name="numero_expediente" class="form-control form-control-sm mr-2 mb-2" style="width:170px"
                       placeholder="N° expediente (0001-T/PUK)" title="Ej.: 0001-T/PUK o 0013-T/PUK-PPD"
                       value="{{ $seguimiento->numero_expediente }}">
                <select name="estado_titulacion_id" class="form-control form-control-sm mr-2 mb-2">
                    @foreach ($estados as $estado)
                        <option value="{{ $estado->id }}" {{ $seguimiento->estado_titulacion_id === $estado->id ? 'selected' : '' }}>{{ $estado->nombre }}</option>
                    @endforeach
                </select>
                <input type="text" name="observaciones" class="form-control form-control-sm mr-2 mb-2" style="min-width:280px"
                       placeholder="Observaciones" value="{{ $seguimiento->observaciones }}">
                <button type="submit" class="btn btn-sm btn-primary mb-2">Guardar</button>
            </form>
            <small class="text-muted d-block mt-1">
                Última actualización: {{ $seguimiento->updated_at?->format('d/m/Y H:i') ?? '—' }}
                @if ($seguimiento->actualizadoPor) por {{ $seguimiento->actualizadoPor->name }} @endif
            </small>
        </div>
    </div>

    {{-- ── Orden del proceso: PTI -> TI -> Tesis ──────────────── --}}
    @include('titulacion._stepper')

    {{-- ── PTI ───────────────────────────────────────────── --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light font-weight-bold"><i class="fas fa-file-alt mr-1"></i>① PTI (Plan de Trabajo de Investigación)</div>
        <div class="card-body">
                    <form method="POST" action="{{ route('titulacion.pti.guardar', $egresado->id) }}">
                        @csrf
                        <div class="form-group">
                            <label class="small font-weight-bold">Título</label>
                            <textarea name="titulo" class="form-control form-control-sm" rows="2">{{ $seguimiento->pti?->titulo ?? '' }}</textarea>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold">Asesor/a</label>
                                <select name="asesor_id" class="form-control form-control-sm">
                                    <option value="">—</option>
                                    @foreach ($docentes as $docente)
                                        <option value="{{ $docente->id }}" {{ ($seguimiento->pti?->asesor_id ?? null) === $docente->id ? 'selected' : '' }}>{{ $docente->name }} {{ $docente->apellidos }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold">Estado</label>
                                <select name="estado" class="form-control form-control-sm">
                                    @foreach (['pendiente','aprobado','vencido'] as $op)
                                        <option value="{{ $op }}" {{ ($seguimiento->pti?->estado ?? 'pendiente') === $op ? 'selected' : '' }}>{{ ucfirst($op) }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                        <div class="form-row">
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold">Fecha PTI</label>
                                <input type="date" name="fecha_pti" class="form-control form-control-sm" value="{{ $seguimiento->pti?->fecha_pti?->format('Y-m-d') }}">
                            </div>
                            <div class="form-group col-md-6">
                                <label class="small font-weight-bold">Fecha vencimiento</label>
                                <input type="date" name="fecha_vencimiento" class="form-control form-control-sm" value="{{ $seguimiento->pti?->fecha_vencimiento?->format('Y-m-d') }}">
                            </div>
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">RD PTI</label>
                            <input type="text" name="rd_pti" class="form-control form-control-sm" value="{{ $seguimiento->pti?->rd_pti ?? '' }}">
                        </div>
                        <div class="form-group">
                            <label class="small font-weight-bold">Link ITI</label>
                            <input type="text" name="link_iti" class="form-control form-control-sm" value="{{ $seguimiento->pti?->link_iti ?? '' }}">
                        </div>
                        <button type="submit" class="btn btn-sm btn-primary">Guardar PTI</button>
                    </form>
                </div>
            </div>

    {{-- ── TI + Tesis: mismo registro/tabla de siempre (titulacion_tesis,
         un solo form/endpoint, cero cambios de datos) — separado en dos
         secciones visuales porque son dos etapas distintas del proceso
         real: primero TI (da el grado de Bachiller), después Tesis (da el
         título profesional). --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light font-weight-bold"><i class="fas fa-book mr-1"></i>② TI (Trabajo de Investigación) → ③ Tesis</div>
        <div class="card-body">
            <form method="POST" action="{{ route('titulacion.tesis.guardar', $egresado->id) }}">
                @csrf
                <div class="form-group">
                    <label class="small font-weight-bold">Título</label>
                    <textarea name="titulo" class="form-control form-control-sm" rows="2">{{ $seguimiento->tesis?->titulo ?? '' }}</textarea>
                </div>
                <div class="form-row">
                    <div class="form-group col-md-6">
                        <label class="small font-weight-bold">Asesor/a</label>
                        <select name="asesor_id" class="form-control form-control-sm">
                            <option value="">—</option>
                            @foreach ($docentes as $docente)
                                <option value="{{ $docente->id }}" {{ ($seguimiento->tesis?->asesor_id ?? null) === $docente->id ? 'selected' : '' }}>{{ $docente->name }} {{ $docente->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="form-group col-md-6">
                        <label class="small font-weight-bold">Estado</label>
                        <select name="estado" class="form-control form-control-sm">
                            @foreach (['pendiente','en_repositorio','aprobado'] as $op)
                                <option value="{{ $op }}" {{ ($seguimiento->tesis?->estado ?? 'pendiente') === $op ? 'selected' : '' }}>{{ ucfirst(str_replace('_',' ',$op)) }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>

                <div class="titulacion-etapa">
                    <div class="titulacion-etapa__titulo">② TI — Trabajo de Investigación (grado de Bachiller)</div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Fecha exposición</label>
                            <input type="date" name="fecha_exposicion" class="form-control form-control-sm" value="{{ $seguimiento->tesis?->fecha_exposicion?->format('Y-m-d') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">RD TI</label>
                            <input type="text" name="rd_ti" class="form-control form-control-sm" value="{{ $seguimiento->tesis?->rd_ti ?? '' }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Bachiller SUNEDU</label>
                            <input type="text" name="bachiller_sunedu" class="form-control form-control-sm" value="{{ $seguimiento->tesis?->bachiller_sunedu ?? '' }}">
                        </div>
                    </div>
                </div>

                <div class="titulacion-etapa">
                    <div class="titulacion-etapa__titulo">③ Tesis — Título Profesional</div>
                    <div class="form-row">
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">Fecha sustentación</label>
                            <input type="date" name="fecha_sustentacion" class="form-control form-control-sm" value="{{ $seguimiento->tesis?->fecha_sustentacion?->format('Y-m-d') }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">RD Extensión</label>
                            <input type="text" name="rd_extension" class="form-control form-control-sm" value="{{ $seguimiento->tesis?->rd_extension ?? '' }}">
                        </div>
                        <div class="form-group col-md-4">
                            <label class="small font-weight-bold">RD Tesis</label>
                            <input type="text" name="rd_tesis" class="form-control form-control-sm" value="{{ $seguimiento->tesis?->rd_tesis ?? '' }}">
                        </div>
                    </div>
                    <div class="form-group">
                        <label class="small font-weight-bold">URL Repositorio</label>
                        <input type="text" name="url_repositorio" class="form-control form-control-sm" value="{{ $seguimiento->tesis?->url_repositorio ?? '' }}">
                    </div>
                </div>

                <button type="submit" class="btn btn-sm btn-primary">Guardar TI/Tesis</button>
            </form>
        </div>
    </div>

    {{-- ── Actas y Jurados ───────────────────────────────────── --}}
    <div class="card shadow-sm mb-3">
        <div class="card-header bg-light font-weight-bold"><i class="fas fa-file-signature mr-1"></i>Actas y Jurados</div>
        <div class="card-body">
            @forelse ($seguimiento->actas as $acta)
                <div class="border rounded p-2 mb-2">
                    <div class="d-flex justify-content-between align-items-start">
                        <div>
                            <strong>{{ ucfirst($acta->tipo) }}</strong> — Acta N° {{ $acta->numero_acta ?? '—' }}
                            <span class="text-muted small d-block">{{ $acta->fecha?->format('d/m/Y') ?? 'Sin fecha' }} · Promedio: {{ $acta->promedio ?? '—' }}</span>
                            <span class="text-muted small d-block">{{ $acta->titulo_trabajo }}</span>
                            <span class="text-muted small d-block">Asesor: {{ $acta->asesor?->name }} {{ $acta->asesor?->apellidos }}</span>
                        </div>
                        <form method="POST" action="{{ route('titulacion.actas.eliminar', $acta->id) }}" onsubmit="return confirm('¿Eliminar esta acta y sus jurados?');">
                            @csrf @method('DELETE')
                            <button type="submit" class="btn btn-sm btn-outline-danger"><i class="fa fa-trash fa-xs"></i></button>
                        </form>
                    </div>
                    <div class="mt-2">
                        <span class="small font-weight-bold text-secondary">Jurados:</span>
                        @forelse ($acta->jurados as $jurado)
                            <span class="badge badge-light border mr-1">
                                {{ $jurado->rol }}: {{ $jurado->docente?->name }} {{ $jurado->docente?->apellidos }}
                                <form method="POST" action="{{ route('titulacion.jurados.eliminar', $jurado->id) }}" class="d-inline" onsubmit="return confirm('¿Quitar este jurado?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger" title="Quitar"><i class="fa fa-times fa-xs"></i></button>
                                </form>
                            </span>
                        @empty
                            <span class="text-muted small">Ninguno</span>
                        @endforelse
                        <form method="POST" action="{{ route('titulacion.jurados.guardar', $acta->id) }}" class="form-inline mt-2">
                            @csrf
                            <select name="docente_id" class="form-control form-control-sm mr-1 mb-1" required>
                                <option value="">Agregar jurado...</option>
                                @foreach ($docentes as $docente)
                                    <option value="{{ $docente->id }}">{{ $docente->name }} {{ $docente->apellidos }}</option>
                                @endforeach
                            </select>
                            <select name="rol" class="form-control form-control-sm mr-1 mb-1" required>
                                <option value="presidente">Presidente</option>
                                <option value="jurado1">Jurado 1</option>
                                <option value="jurado2">Jurado 2</option>
                                <option value="jurado3">Jurado 3</option>
                            </select>
                            <button type="submit" class="btn btn-sm btn-outline-primary mb-1">Agregar</button>
                        </form>
                    </div>
                </div>
            @empty
                <p class="text-muted small">Sin actas registradas.</p>
            @endforelse

            <form method="POST" action="{{ route('titulacion.actas.guardar', $egresado->id) }}" class="border-top pt-3 mt-2">
                @csrf
                <div class="form-row">
                    <div class="form-group col-md-2">
                        <label class="small font-weight-bold">Tipo</label>
                        <select name="tipo" class="form-control form-control-sm" required>
                            <option value="expo">Exposición</option>
                            <option value="tesis">Tesis</option>
                        </select>
                    </div>
                    <div class="form-group col-md-2">
                        <label class="small font-weight-bold">N° Acta</label>
                        <input type="text" name="numero_acta" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-2">
                        <label class="small font-weight-bold">Fecha</label>
                        <input type="date" name="fecha" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-2">
                        <label class="small font-weight-bold">Promedio</label>
                        <input type="number" step="0.01" min="0" max="20" name="promedio" class="form-control form-control-sm">
                    </div>
                    <div class="form-group col-md-4">
                        <label class="small font-weight-bold">Asesor/a</label>
                        <select name="asesor_id" class="form-control form-control-sm">
                            <option value="">—</option>
                            @foreach ($docentes as $docente)
                                <option value="{{ $docente->id }}">{{ $docente->name }} {{ $docente->apellidos }}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="form-group">
                    <label class="small font-weight-bold">Título del trabajo</label>
                    <input type="text" name="titulo_trabajo" class="form-control form-control-sm">
                </div>
                <button type="submit" class="btn btn-sm btn-primary">Agregar Acta</button>
            </form>
        </div>
    </div>

    <div class="row">
        {{-- ── Resoluciones ──────────────────────────────────── --}}
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light font-weight-bold"><i class="fas fa-gavel mr-1"></i>Resoluciones</div>
                <div class="card-body">
                    <ul class="list-unstyled mb-3">
                        @forelse ($seguimiento->resoluciones as $res)
                            <li class="d-flex justify-content-between align-items-center border-bottom py-1">
                                <span class="small"><strong>{{ $res->tipo }}</strong> — {{ $res->numero_rd ?? '—' }} ({{ $res->fecha?->format('d/m/Y') ?? 'sin fecha' }})</span>
                                <form method="POST" action="{{ route('titulacion.resoluciones.eliminar', $res->id) }}" onsubmit="return confirm('¿Eliminar esta resolución?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger"><i class="fa fa-trash fa-xs"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="text-muted small">Sin resoluciones registradas.</li>
                        @endforelse
                    </ul>
                    <form method="POST" action="{{ route('titulacion.resoluciones.guardar', $egresado->id) }}" class="form-inline">
                        @csrf
                        <select name="tipo" class="form-control form-control-sm mr-1 mb-1" required>
                            <option value="PTI">PTI</option>
                            <option value="Exposicion">Exposición</option>
                            <option value="Extension">Extensión</option>
                            <option value="Tesis">Tesis</option>
                        </select>
                        <input type="text" name="numero_rd" class="form-control form-control-sm mr-1 mb-1" placeholder="N° RD" style="max-width:140px">
                        <input type="date" name="fecha" class="form-control form-control-sm mr-1 mb-1">
                        <button type="submit" class="btn btn-sm btn-outline-primary mb-1">Agregar</button>
                    </form>
                </div>
            </div>
        </div>

        {{-- ── Documentos ────────────────────────────────────── --}}
        <div class="col-lg-6 mb-3">
            <div class="card shadow-sm h-100">
                <div class="card-header bg-light font-weight-bold"><i class="fas fa-paperclip mr-1"></i>Documentos</div>
                <div class="card-body">
                    <ul class="list-unstyled mb-3">
                        @forelse ($seguimiento->documentos as $doc)
                            <li class="d-flex justify-content-between align-items-center border-bottom py-1">
                                <span class="small"><strong>{{ $doc->tipo }}</strong> @if($doc->url) — <a href="{{ $doc->url }}" target="_blank" rel="noopener">enlace</a> @endif</span>
                                <form method="POST" action="{{ route('titulacion.documentos.eliminar', $doc->id) }}" onsubmit="return confirm('¿Eliminar este documento?');">
                                    @csrf @method('DELETE')
                                    <button type="submit" class="btn btn-link btn-sm p-0 text-danger"><i class="fa fa-trash fa-xs"></i></button>
                                </form>
                            </li>
                        @empty
                            <li class="text-muted small">Sin documentos registrados.</li>
                        @endforelse
                    </ul>
                    <form method="POST" action="{{ route('titulacion.documentos.guardar', $egresado->id) }}" class="form-inline">
                        @csrf
                        <select name="tipo" class="form-control form-control-sm mr-1 mb-1" required>
                            <option value="ITI">ITI</option>
                            <option value="Dictamen">Dictamen</option>
                            <option value="Tesis">Tesis</option>
                            <option value="Acta">Acta</option>
                        </select>
                        <input type="text" name="url" class="form-control form-control-sm mr-1 mb-1" placeholder="URL" style="min-width:200px">
                        <button type="submit" class="btn btn-sm btn-outline-primary mb-1">Agregar</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
