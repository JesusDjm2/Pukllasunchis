@extends(\App\Http\Controllers\Titulacion\TitulacionController::layout())
@section('contenido')
    <style>
        /* Barra de chips de estado + filtros: una sola fila en PC (>=992px),
           apilada en pantallas chicas. Los chips se vuelven una tira con
           scroll horizontal en móvil en vez de romper en varias líneas —
           patrón estándar de "filter chips" (tabs horizontales scrolleables). */
        .titulacion-toolbar {
            display: flex;
            flex-direction: column;
            gap: .75rem;
            margin-bottom: 1rem;
        }

        .titulacion-chips {
            display: flex;
            gap: .5rem;
            overflow-x: auto;
            overflow-y: hidden;
            padding: .125rem .125rem .5rem;
            -webkit-overflow-scrolling: touch;
            scrollbar-width: thin;
        }

        .titulacion-chips::-webkit-scrollbar {
            height: 6px;
        }

        .titulacion-chips::-webkit-scrollbar-thumb {
            background: #d1d5db;
            border-radius: 3px;
        }

        .titulacion-chip {
            display: inline-flex;
            flex-direction: column;
            align-items: flex-start;
            justify-content: center;
            flex-shrink: 0;
            min-width: 6.75rem;
            padding: .5rem .8rem;
            border-radius: .6rem;
            background: #fff;
            border: 1px solid #e3e6f0;
            text-decoration: none;
            transition: box-shadow .15s ease, transform .15s ease, border-color .15s ease, background-color .15s ease;
        }

        .titulacion-chip:hover {
            box-shadow: 0 .2rem .6rem rgba(31, 41, 55, .1);
            transform: translateY(-1px);
            text-decoration: none;
            border-color: #c7d2fe;
        }

        .titulacion-chip__count {
            font-size: 1.2rem;
            font-weight: 700;
            line-height: 1.15;
            color: #2d3748;
        }

        .titulacion-chip__label {
            font-size: .66rem;
            font-weight: 600;
            color: #8892a6;
            text-transform: uppercase;
            letter-spacing: .03em;
            white-space: nowrap;
            max-width: 9rem;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .titulacion-chip--active {
            background: #eef2ff;
            border-color: #4e73df;
        }

        .titulacion-chip--active .titulacion-chip__count { color: #4e73df; }
        .titulacion-chip--active .titulacion-chip__label { color: #4e73df; }

        .titulacion-chip--warning.titulacion-chip--active {
            background: #fff8e6;
            border-color: #f6c23e;
        }

        .titulacion-chip--warning .titulacion-chip__count { color: #dda60a; }
        .titulacion-chip--warning.titulacion-chip--active .titulacion-chip__label { color: #b98b06; }

        .titulacion-chip--primary .titulacion-chip__count { color: #4e73df; }

        .titulacion-filters {
            display: flex;
            flex-wrap: wrap;
            align-items: flex-end;
            gap: .6rem;
            flex-shrink: 0;
        }

        .titulacion-filters .form-group-sm { min-width: 9rem; }
        .titulacion-filters .form-group-sm.is-search { min-width: 13rem; flex: 1 1 13rem; }

        /* Flechita del acordeón: gira 180° al abrir. Bootstrap 4 agrega/quita
           la clase .collapsed en el <tr data-toggle="collapse"> solo, según
           el estado del collapse que apunta — no hace falta JS propio. */
        .titulacion-chevron {
            transition: transform .2s ease;
        }
        .titulacion-fila-toggle:not(.collapsed) .titulacion-chevron {
            transform: rotate(180deg);
        }

        @media (min-width: 992px) {
            .titulacion-toolbar {
                flex-direction: row;
                align-items: flex-start;
                justify-content: space-between;
            }
            .titulacion-chips {
                flex-wrap: nowrap;
                overflow-x: auto;
                padding-bottom: .125rem;
                margin-right: 1rem;
            }
            .titulacion-filters { flex-wrap: nowrap; }
            .titulacion-filters .form-group-sm.is-search { flex: 0 0 15rem; }
        }
    </style>
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between flex-wrap mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <div class="mb-2 mb-sm-0">
                <h4 class="mb-0 font-weight-bold" style="color:#1e293b;">
                    <i class="fas fa-user-graduate mr-2" style="color:#4e73df;"></i>Titulación de Egresados
                </h4>
                <small class="text-muted">
                    {{ $totalEgresados }} registrados &nbsp;&middot;&nbsp;
                    {{ $totalConSeguimiento }} con seguimiento iniciado
                    @if ($totalPendientes > 0)
                        &nbsp;&middot;&nbsp; <span class="text-warning font-weight-bold">{{ $totalPendientes }} pendientes de importación</span>
                    @endif
                    @if ($totalActivosElegibles > 0)
                        &nbsp;&middot;&nbsp; <span class="text-primary font-weight-bold">{{ $totalActivosElegibles }} activos en Ciclo VIII+ (pueden iniciar)</span>
                    @endif
                </small>
            </div>
            @if ($totalPendientes > 0)
                <a href="{{ route('titulacion.crear-cuentas-masivo.preview') }}" class="btn btn-sm btn-warning">
                    <i class="fas fa-users mr-1"></i>Crear cuentas pendientes
                </a>
            @endif
        </div>

        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert">
                {{ Session::get('success') }}
                <a type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            </div>
        @endif
        @if (Session::has('error'))
            <div class="alert alert-warning alert-dismissible fade show" role="alert">
                {{ Session::get('error') }}
                <a type="button" class="close" data-dismiss="alert" aria-label="Close"><span aria-hidden="true">&times;</span></a>
            </div>
        @endif

        @php
            $qFiltroBase = array_filter(['search' => request('search')], fn ($v) => $v !== null && $v !== '');
        @endphp
        <div class="titulacion-toolbar">
            <div class="titulacion-chips">
                @foreach ($estados as $estado)
                    @php
                        $activo = (int) request('estado_titulacion_id') === $estado->id && request('estado') !== 'pendientes';
                        $urlEstado = route('titulacion.index', $qFiltroBase + ['estado_titulacion_id' => $estado->id]);
                    @endphp
                    <a href="{{ $urlEstado }}" class="titulacion-chip {{ $activo ? 'titulacion-chip--active' : '' }}" title="{{ $estado->nombre }}">
                        <span class="titulacion-chip__count">{{ $conteoPorEstado[$estado->id] ?? 0 }}</span>
                        <span class="titulacion-chip__label">{{ $estado->nombre }}</span>
                    </a>
                @endforeach
                @if ($totalPendientes > 0)
                    @php
                        $activoPendientes = request('estado') === 'pendientes';
                        $urlPendientes = route('titulacion.index', $qFiltroBase + ['estado' => 'pendientes']);
                    @endphp
                    <a href="{{ $urlPendientes }}" class="titulacion-chip titulacion-chip--warning {{ $activoPendientes ? 'titulacion-chip--active' : '' }}">
                        <span class="titulacion-chip__count">{{ $totalPendientes }}</span>
                        <span class="titulacion-chip__label">Pendientes</span>
                    </a>
                @endif
                @if ($totalActivosElegibles > 0)
                    @php
                        $activoActivos = request('estado') === 'activos';
                        $urlActivos = route('titulacion.index', $qFiltroBase + ['estado' => 'activos']);
                    @endphp
                    <a href="{{ $urlActivos }}" class="titulacion-chip titulacion-chip--primary {{ $activoActivos ? 'titulacion-chip--active' : '' }}">
                        <span class="titulacion-chip__count">{{ $totalActivosElegibles }}</span>
                        <span class="titulacion-chip__label">Ciclo VIII+ activos</span>
                    </a>
                @endif
                @if (request()->filled('estado_titulacion_id') || request()->filled('estado'))
                    <a href="{{ route('titulacion.index', $qFiltroBase) }}" class="titulacion-chip" title="Quitar filtro de estado">
                        <span class="titulacion-chip__count"><i class="fas fa-times-circle fa-sm text-muted"></i></span>
                        <span class="titulacion-chip__label">Quitar filtro</span>
                    </a>
                @endif
            </div>

            <form method="GET" action="{{ route('titulacion.index') }}" class="titulacion-filters">
                {{-- El filtro de estado ya se elige con los chips de arriba; los
                     demás filtros conservan ese filtro activo vía estos hidden. --}}
                @if (request()->filled('estado_titulacion_id'))
                    <input type="hidden" name="estado_titulacion_id" value="{{ request('estado_titulacion_id') }}">
                @endif
                @if (request('estado') === 'pendientes')
                    <input type="hidden" name="estado" value="pendientes">
                @endif
                <div class="form-group-sm is-search">
                    <label class="small font-weight-bold text-secondary mb-1">Buscar</label>
                    <input type="text" name="search" class="form-control form-control-sm" value="{{ request('search') }}" placeholder="Nombre, DNI, Acta o RD...">
                </div>
                <div class="form-group-sm">
                    <label class="small font-weight-bold text-secondary mb-1">Programa</label>
                    <select name="programa_id" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($programasFiltro as $prog)
                            <option value="{{ $prog->id }}" {{ (int) request('programa_id') === $prog->id ? 'selected' : '' }}>{{ $prog->nombre }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-sm">
                    <label class="small font-weight-bold text-secondary mb-1">Año</label>
                    <select name="anio" class="form-control form-control-sm">
                        <option value="">Todos</option>
                        @foreach ($aniosFiltro as $anio)
                            <option value="{{ $anio }}" {{ request('anio') === $anio ? 'selected' : '' }}>{{ $anio }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="form-group-sm" style="min-width:auto;">
                    <button type="submit" class="btn btn-sm btn-primary"><i class="fas fa-filter mr-1"></i>Buscar</button>
                    @if (request()->filled('search') || request()->filled('estado_titulacion_id') || request()->filled('estado') || request()->filled('programa_id') || request()->filled('anio'))
                        <a href="{{ route('titulacion.index') }}" class="btn btn-sm btn-outline-secondary">Limpiar</a>
                    @endif
                </div>
            </form>
        </div>

        <div class="table-responsive">
            <table class="table table-hover" style="font-size: 14px">
                <thead class="thead-dark">
                    <tr>
                        <th style="width:24px;"></th>
                        <th style="white-space:nowrap;">N° Expediente</th>
                        <th>Exalumno(a)</th>
                        <th>DNI</th>
                        <th>Programa</th>
                        <th>Año</th>
                        <th>Estado</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($egresados as $i => $item)
                        @php
                            $filaClase = match ($item->tipo) {
                                'registrado' => '',
                                'activo_elegible' => 'table-primary',
                                default => 'table-warning',
                            };
                        @endphp
                        {{-- "collapsed" desde el inicio: Bootstrap solo la agrega tras el
                             primer clic, y sin ella la flecha arrancaba girada. --}}
                        <tr class="{{ $filaClase }} titulacion-fila-toggle collapsed" aria-expanded="false"
                            data-toggle="collapse" data-target="#detalle-{{ $item->row_id }}" style="cursor:pointer;" title="Ver más datos">
                            <td class="text-center text-muted"><i class="fas fa-chevron-down fa-xs titulacion-chevron"></i></td>
                            <td style="white-space:nowrap;">
                                @if ($item->expediente ?? null)
                                    <strong>{{ \App\Services\Titulacion\Expediente::mostrar($item->expediente) }}</strong>
                                @else
                                    <span class="text-muted">—</span>
                                @endif
                            </td>
                            <td><strong>{{ $item->nombre }}</strong></td>
                            <td>{{ $item->dni ?: '—' }}</td>
                            <td>{{ $item->programa }}</td>
                            <td>{{ $item->anio ?? '—' }}</td>
                            <td><span class="badge badge-{{ $item->estado_badge }}">{{ $item->estado_label }}</span></td>
                        </tr>
                        <tr class="collapse" id="detalle-{{ $item->row_id }}">
                            <td colspan="7" class="bg-light">
                                @if ($item->tipo === 'registrado')
                                    <div class="small mb-3">
                                        <strong><i class="fas fa-users mr-1"></i>Integrantes del mismo grupo (misma acta):</strong>
                                        @forelse ($item->integrantes_grupo as $integrante)
                                            <span class="badge badge-info mr-1">{{ $integrante->apellidos }}, {{ $integrante->name }}</span>
                                        @empty
                                            <span class="text-muted">Ninguno — presentó solo(a), o su acta aún no vincula a nadie más.</span>
                                        @endforelse
                                    </div>
                                    @include('titulacion._show_contenido', [
                                        'egresado' => $item->user,
                                        'seguimiento' => $item->seguimiento,
                                        'estados' => $estados,
                                        'docentes' => $docentes,
                                        'enModal' => true,
                                        'enAcordeon' => true,
                                    ])
                                @elseif ($item->tipo === 'activo_elegible')
                                    <div class="row mb-2">
                                        <div class="col-md-3 small"><strong>DNI:</strong> {{ $item->dni ?: '—' }}</div>
                                        <div class="col-md-3 small"><strong>Nombre completo:</strong> {{ $item->nombre }}</div>
                                        <div class="col-md-3 small"><strong>Programa:</strong> {{ $item->programa }}</div>
                                        <div class="col-md-3 small"><strong>Ciclo actual:</strong> {{ $item->anio ?? '—' }}</div>
                                    </div>
                                    <p class="small text-muted mb-2">
                                        Alumno(a) activo(a) con matrícula regular — todavía no es egresado(a), pero ya está en un
                                        ciclo desde el cual la institución permite iniciar el PTI. Al iniciar, queda en estado
                                        "PTI en proceso" y aparece junto a los demás en la lista de arriba.
                                    </p>
                                    <form method="POST" action="{{ $item->accion_url }}" class="d-inline"
                                          onsubmit="return confirm('¿Iniciar el proceso de titulación para {{ $item->nombre }}?');">
                                        @csrf
                                        <button type="submit" class="btn btn-sm {{ $item->accion_clase }}">
                                            <i class="fa fa-play fa-sm"></i> {{ $item->accion_label }}
                                        </button>
                                    </form>
                                @else
                                    {{-- La acción vive aquí (antes en una columna "Acciones"): la fila
                                         entera ya abre este detalle, así que es el paso natural. --}}
                                    <a href="{{ $item->accion_url }}" class="btn btn-sm {{ $item->accion_clase }} mb-3">
                                        <i class="fa fa-user-plus fa-sm"></i> {{ $item->accion_label }}
                                    </a>
                                    <p class="small font-weight-bold text-secondary mb-2">
                                        <i class="fas fa-file-import mr-1"></i>Viene de {{ $item->filas_origen->count() }} archivo(s) importado(s):
                                    </p>
                                    @foreach ($item->filas_origen as $fila)
                                        <div class="border rounded p-2 mb-2 bg-white">
                                            <div class="small font-weight-bold mb-1">
                                                <span class="badge badge-light border">{{ $fila['origen'] }}</span>
                                                <span class="text-muted">{{ $fila['archivo'] }} — importado {{ $fila['fecha'] }}</span>
                                            </div>
                                            <div class="row">
                                                @foreach ($fila['datos'] as $campo => $valor)
                                                    <div class="col-md-4 small mb-1">
                                                        <strong>{{ $campo }}:</strong> {{ \Illuminate\Support\Str::limit($valor, 80) }}
                                                    </div>
                                                @endforeach
                                            </div>
                                        </div>
                                    @endforeach
                                @endif
                            </td>
                        </tr>
                    @empty
                        <tr><td colspan="7" class="text-center">No se encontraron exalumnos con estos filtros.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{ $egresados->links() }}
    </div>

    @if (request()->filled('ver'))
        {{-- Al volver de guardar algo dentro del acordeón (POST -> redirect a
             /titulacion?ver={id}), se reabre solo el acordeón de esa fila y se
             hace scroll hasta ahí — todo servido por el servidor, sin AJAX. Si
             la persona quedó en otra página de la paginación, la fila
             simplemente no está en el DOM y esto no hace nada (degrada bien). --}}
        <script>
            document.addEventListener('DOMContentLoaded', function () {
                var fila = document.getElementById('detalle-reg{{ (int) request('ver') }}');
                if (!fila) return;
                $(fila).collapse('show');
                fila.scrollIntoView({ behavior: 'smooth', block: 'center' });
            });
        </script>
    @endif
@endsection
