@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)

@section('titulo', 'Carga Masiva de Actas - IESP Ricardo Palma')

@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid py-3 al-ui rp-modulo">
        <header class="al-hero mb-3">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker">
                        <a href="{{ route('rp.dashboard') }}" class="al-btn al-btn-sm al-btn-claro mb-2">
                            <i class="fas fa-arrow-left"></i> Dashboard Ricardo Palma
                        </a>
                    </div>
                    <h1>Carga Masiva de Actas Consolidadas</h1>
                    <p class="al-hero-sub">
                        Importación por lotes en formato Excel (.xlsx) o CSV. El sistema registra y actualiza automáticamente estudiantes, periodos, asignaturas y calificaciones históricas.
                    </p>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('rp.importar.plantilla') }}" class="al-btn al-btn-oro">
                        <i class="fas fa-download"></i> Descargar plantilla del acta
                    </a>
                </div>
            </div>
        </header>

        @include('admin.ricardo-palma._nav')

        {{-- Resultado de la última carga --}}
        @if (session('rp_resultado'))
            @include('admin.ricardo-palma.importar._resultado', ['r' => session('rp_resultado')])
        @endif
        @if (Session::has('error'))
            <div class="al-aviso is-mal mb-3">
                <i class="fas fa-times-circle"></i>
                <div>{{ Session::get('error') }}</div>
            </div>
        @endif
        @if (Session::has('success'))
            <div class="al-aviso is-ok mb-3">
                <i class="fas fa-check-circle"></i>
                <div>{{ Session::get('success') }}</div>
            </div>
        @endif
        @if ($errors->any())
            <div class="al-aviso is-mal mb-3">
                <i class="fas fa-times-circle"></i>
                <div>@foreach ($errors->all() as $e) <div>{{ $e }}</div> @endforeach</div>
            </div>
        @endif

        {{-- Acta escaneada: la IA la lee y se corrige en pantalla antes de cargarla --}}
        <div class="al-card mb-4">
            <div class="al-card-cab">
                <div class="al-card-cab-icono">
                    <i class="fas fa-file-pdf"></i>
                </div>
                <div>
                    <h2>Subir acta escaneada (PDF)</h2>
                    <p>La IA lee el acta y la muestra en una tabla editable junto al escaneo. Nada se guarda hasta que la revises y la cargues.</p>
                </div>
            </div>
            <div class="al-card-cuerpo">
                @if ($iaConfigurada)
                    <form action="{{ route('rp.lecturas.subir') }}" method="POST" enctype="multipart/form-data" class="d-flex flex-wrap align-items-center js-rp-subir-escaneo" style="gap: .75rem;">
                        @csrf
                        <input type="file" name="escaneo" class="al-campo p-2" accept="application/pdf,.pdf" required style="height: auto; flex: 1 1 280px;">
                        <button type="submit" class="al-btn al-btn-oro py-2">
                            <i class="fas fa-magic"></i> Leer acta con IA
                        </button>
                    </form>
                    <div class="al-suave mt-2" style="font-size: .78rem;">
                        Un acta por PDF (todas sus páginas), escaneada a color y a 300 ppp. Máximo 14 MB. La lectura tarda entre 1 y 2 minutos.
                    </div>
                @else
                    <div class="al-aviso is-mal mb-0">
                        <i class="fas fa-key"></i>
                        <div>Falta configurar <code>GEMINI_API_KEY</code> en el archivo <code>.env</code> del servidor para leer actas escaneadas.</div>
                    </div>
                @endif

                @if ($borradores)
                    @php
                        $fallidos = array_filter($borradores, fn($b) => !empty($b['error_lectura']) || ($b['alumnos'] ?? 0) === 0);
                    @endphp
                    <div class="d-flex flex-wrap align-items-center justify-content-between mt-3 mb-2" style="gap: .5rem;">
                        <div class="rp-resultado-titulo m-0">Actas escaneadas sin cargar ({{ count($borradores) }})</div>
                        <div class="d-flex align-items-center flex-wrap" style="gap: .5rem;">
                            @if (count($fallidos) > 0)
                                <form method="POST" action="{{ route('rp.lecturas.limpiar') }}" onsubmit="return confirm('¿Seguro que deseas eliminar los {{ count($fallidos) }} borradores fallidos o vacíos?');" class="m-0">
                                    @csrf
                                    @method('DELETE')
                                    <input type="hidden" name="solo_fallidos" value="1">
                                    <button type="submit" class="al-btn al-btn-sm al-btn-rojo" title="Eliminar solo borradores con error o sin alumnos">
                                        <i class="fas fa-trash-alt"></i> Borrar {{ count($fallidos) }} fallidos
                                    </button>
                                </form>
                            @endif
                            <form method="POST" action="{{ route('rp.lecturas.limpiar') }}" onsubmit="return confirm('¿Seguro que deseas eliminar todos los borradores pendientes de escaneo?');" class="m-0">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="al-btn al-btn-sm al-btn-borde text-muted" title="Eliminar todos los borradores no cargados">
                                    <i class="fas fa-trash"></i> Eliminar todos
                                </button>
                            </form>
                        </div>
                    </div>
                    <div class="rp-borradores">
                        @foreach ($borradores as $b)
                            @php
                                $esFallido = !empty($b['error_lectura']) || ($b['alumnos'] ?? 0) === 0;
                            @endphp
                            <div class="rp-borrador {{ $esFallido ? 'is-fallido' : '' }}">
                                <i class="fas fa-file-pdf {{ $esFallido ? 'text-danger' : 'al-suave' }}"></i>
                                <div>
                                    <strong>{{ $b['archivo'] ?? 'escaneo.pdf' }}</strong>
                                    <span class="al-suave ml-1">{{ $b['titulo'] ?: 'Sin encabezado' }} · {{ $b['alumnos'] }} alumno(s)</span>
                                    <span class="al-suave ml-1">{{ $b['usuario'] ?? '' }} · {{ \Illuminate\Support\Carbon::parse($b['actualizado'])->format('d/m/Y H:i') }}</span>
                                    @if (! empty($b['error_lectura']))
                                        <span class="al-badge is-mal ml-1" title="{{ $b['error_lectura'] }}">La IA no pudo leerla</span>
                                    @endif
                                </div>
                                <div class="rp-borrador-acciones">
                                    @if (! $esFallido)
                                        <a href="{{ route('rp.lecturas.editar', $b['id']) }}" class="al-btn al-btn-sm al-btn-azul" title="Seguir revisando y corregir esta acta">
                                            <i class="fas fa-pen"></i> Seguir corrigiendo
                                        </a>
                                    @else
                                        <a href="{{ route('rp.lecturas.editar', $b['id']) }}" class="al-btn al-btn-sm al-btn-borde" title="Abrir editor">
                                            <i class="fas fa-eye"></i> Ver
                                        </a>
                                    @endif
                                    <form method="POST" action="{{ route('rp.lecturas.descartar', $b['id']) }}" onsubmit="return confirm('¿Eliminar este borrador?');" class="d-inline m-0">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="al-btn al-btn-sm al-btn-rojo" title="Eliminar este borrador">
                                            <i class="fas fa-trash-alt"></i> Borrar
                                        </button>
                                    </form>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="row">
            {{-- Formulario de Subida --}}
            <div class="col-lg-5 mb-4">
                <div class="al-card h-100 mb-0">
                    <div class="al-card-cab">
                        <div class="al-card-cab-icono">
                            <i class="fas fa-upload"></i>
                        </div>
                        <div>
                            <h2>Subir Archivo Excel o CSV</h2>
                            <p>Procesamiento automatizado por lotes</p>
                        </div>
                    </div>
                    <div class="al-card-cuerpo">
                        <form action="{{ route('rp.importar.acta') }}" method="POST" enctype="multipart/form-data">
                            @csrf
                            <div class="mb-3">
                                <label class="font-weight-bold mb-2" style="font-size: .85rem; color: var(--al-texto);">
                                    Seleccionar archivo (.xlsx, .xls o .csv):
                                </label>
                                <input type="file" name="archivo" class="al-campo w-100 p-2" accept=".xlsx,.xls,.csv" required style="height: auto;">
                                <div class="al-suave mt-1" style="font-size: .75rem;">
                                    Tamaño máximo permitido: 20 MB.
                                </div>
                            </div>

                            <div class="al-aviso is-info mb-4">
                                <i class="fas fa-info-circle"></i>
                                <div style="font-size: .82rem; line-height: 1.4;">
                                    <strong>Se reconocen dos formatos:</strong> la plantilla con forma de acta (recomendada) y la tabla de una fila por alumno y curso
                                    (<a href="{{ route('rp.importar.plantilla', ['formato' => 'filas']) }}">descargar plantilla por filas</a>), útil para tablas extraídas con IA.
                                </div>
                            </div>

                            <button type="submit" class="al-btn al-btn-oro w-100 justify-content-center py-2">
                                <i class="fas fa-cogs"></i> Procesar e Importar Calificaciones
                            </button>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Instrucciones y Formato --}}
            <div class="col-lg-7 mb-4">
                <div class="al-card h-100 mb-0">
                    <div class="al-card-cab">
                        <div class="al-card-cab-icono">
                            <i class="fas fa-list-ol"></i>
                        </div>
                        <div>
                            <h2>Cómo llenar la plantilla del acta</h2>
                            <p>Tiene la misma forma que el acta física escaneada</p>
                        </div>
                    </div>
                    <div class="al-card-cuerpo" style="font-size: .85rem; line-height: 1.5;">
                        <ol class="pl-3 mb-3">
                            <li><strong>Encabezado:</strong> año académico (<code>1997-II</code>), semestre (<code>DÉCIMO</code>, <code>X</code> o <code>10</code>), fecha del acta, especialidad, turno y sección. El n.º de acta es opcional.</li>
                            <li><strong>Asignaturas:</strong> el nombre de cada una en su columna (1 a 15), en el orden del acta, y debajo sus <strong>créditos</strong>.</li>
                            <li><strong>Alumnos:</strong> una fila por alumno, como en el acta: matrícula y <code>APELLIDOS,Nombres</code> (separados por coma). En las casillas se escribe el <strong>puntaje</strong> tal como figura (ej.: <code>196</code> = nota 14 × 14 créditos); si el acta trae notas, se cambia «Casillas con» a <em>NOTA</em>.</li>
                            <li><strong>Cotejar:</strong> puntaje total, créditos y promedio se calculan solos (truncado a 2 decimales, como el acta). Si no coinciden con el escaneo, hay una casilla mal copiada. En naranja: puntaje que no se divide exacto entre los créditos.</li>
                            <li>Las observaciones que empiezan con <code>RETIRADO/A</code> o <code>LICENCIA</code> marcan al alumno así. Sexo y DNI (no están en el acta) son opcionales.</li>
                        </ol>
                        <p class="al-suave mb-0">Puedes copiar la hoja <code>ACTA</code> dentro del mismo archivo para cargar varias actas a la vez. Si vuelves a cargar una acta, sus notas se actualizan.</p>
                    </div>
                    <details class="px-3 pb-3">
                        <summary class="al-suave" style="cursor: pointer; font-size: .82rem;">Columnas de la plantilla por filas (formato anterior)</summary>
                    <div class="al-tabla-marco mt-2">
                        <table class="al-tabla">
                            <thead>
                                <tr>
                                    <th>Columna</th>
                                    <th>Requerido</th>
                                    <th>Descripción / Ejemplo</th>
                                </tr>
                            </thead>
                            <tbody>
                                <tr>
                                    <td><code>PERIODO</code></td>
                                    <td><span class="al-badge is-mal">Obligatorio</span></td>
                                    <td class="al-suave">Ej: <code>1996-II</code>, <code>2006-I</code> (1995 al 2015)</td>
                                </tr>
                                <tr>
                                    <td><code>PROGRAMA</code></td>
                                    <td><span class="al-badge is-mal">Obligatorio</span></td>
                                    <td class="al-suave">Código o nombre: <code>INICIAL</code>, <code>PRIMARIA</code>, <code>SEC-COM</code>, <code>SEC-MAT</code></td>
                                </tr>
                                <tr>
                                    <td><code>SEMESTRE</code></td>
                                    <td><span class="al-badge is-mal">Obligatorio</span></td>
                                    <td class="al-suave">Número del 1 al 10 (Semestres I al X)</td>
                                </tr>
                                <tr>
                                    <td><code>NRO_MATRICULA</code></td>
                                    <td><span class="al-badge is-warn">Recomendado</span></td>
                                    <td class="al-suave">Número de matrícula del acta física (ej: <code>351</code>, <code>01-06</code>)</td>
                                </tr>
                                <tr>
                                    <td><code>DNI</code></td>
                                    <td><span class="al-badge is-muted">Opcional</span></td>
                                    <td class="al-suave">DNI del estudiante (si se dispone)</td>
                                </tr>
                                <tr>
                                    <td><code>APELLIDOS</code></td>
                                    <td><span class="al-badge is-mal">Obligatorio</span></td>
                                    <td class="al-suave">Apellidos del estudiante</td>
                                </tr>
                                <tr>
                                    <td><code>NOMBRES</code></td>
                                    <td><span class="al-badge is-mal">Obligatorio</span></td>
                                    <td class="al-suave">Nombres del estudiante</td>
                                </tr>
                                <tr>
                                    <td><code>CURSO</code></td>
                                    <td><span class="al-badge is-mal">Obligatorio</span></td>
                                    <td class="al-suave">Nombre de la asignatura oficial</td>
                                </tr>
                                <tr>
                                    <td><code>CREDITOS</code></td>
                                    <td><span class="al-badge is-muted">Opcional</span></td>
                                    <td class="al-suave">Créditos del curso (por defecto 2 si se omite)</td>
                                </tr>
                                <tr>
                                    <td><code>NOTA</code></td>
                                    <td><span class="al-badge is-muted">Opcional</span></td>
                                    <td class="al-suave">Calificación de 0 a 20 (dejar vacío si es RETIRADO)</td>
                                </tr>
                                <tr>
                                    <td><code>OBSERVACION</code></td>
                                    <td><span class="al-badge is-muted">Opcional</span></td>
                                    <td class="al-suave">Ej: <code>APROBADO</code>, <code>RETIRADO</code>, <code>LICENCIA</code></td>
                                </tr>
                                <tr>
                                    <td><code>NRO_ACTA</code></td>
                                    <td><span class="al-badge is-muted">Opcional</span></td>
                                    <td class="al-suave">Número del acta física (ej: <code>002906</code>). Si se corrige y se vuelve a cargar, se actualiza.</td>
                                </tr>
                                <tr>
                                    <td><code>TURNO</code> / <code>SECCION</code></td>
                                    <td><span class="al-badge is-muted">Opcional</span></td>
                                    <td class="al-suave">Por defecto <code>TARDE</code> y <code>UNICA</code>. Junto con periodo, programa y semestre identifican el acta.</td>
                                </tr>
                                <tr>
                                    <td><code>FECHA_VISACION</code></td>
                                    <td><span class="al-badge is-muted">Opcional</span></td>
                                    <td class="al-suave">Fecha de visación: el «Fecha:» del pie del acta (ej: <code>19/08/2006</code>). También se acepta la columna <code>FECHA_EMISION</code>.</td>
                                </tr>
                                <tr>
                                    <td><code>OBS_ALUMNO</code></td>
                                    <td><span class="al-badge is-muted">Opcional</span></td>
                                    <td class="al-suave">Observación del alumno en el acta (ej: <code>RETIRADA R.D. N° 86-03-ISPP-RP-C</code>, <code>BAJO RENDIMIENTO ACADÉMICO</code>). Si empieza con RETIRAD o LICENCIA, el alumno cuenta como retirado o con licencia aunque tenga notas.</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                    </details>
                </div>
            </div>
        </div>

        {{-- Historial de Importaciones --}}
        <div class="al-card mt-3">
            <div class="al-card-cab">
                <div class="al-card-cab-icono">
                    <i class="fas fa-history"></i>
                </div>
                <div>
                    <h2>Historial de Lotes Importados</h2>
                    <p>Auditoría de cargas masivas ejecutadas en el sistema</p>
                </div>
            </div>
            <div class="al-tabla-marco">
                <table class="al-tabla">
                    <thead>
                        <tr>
                            <th>Fecha</th>
                            <th>Archivo</th>
                            <th>Usuario</th>
                            <th class="text-center">Total Filas</th>
                            <th class="text-center">Procesadas</th>
                            <th class="text-center">Errores</th>
                            <th class="text-center">Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($importaciones as $imp)
                            <tr>
                                <td class="al-suave">{{ $imp->created_at->format('d/m/Y H:i') }}</td>
                                <td class="font-weight-bold">{{ $imp->archivo_nombre }}</td>
                                <td>{{ $imp->usuario?->name ?: 'Sistema' }}</td>
                                <td class="text-center al-suave">{{ $imp->total_filas }}</td>
                                <td class="text-center">
                                    <span class="al-badge is-ok">{{ $imp->filas_exitosas }}</span>
                                </td>
                                <td class="text-center">
                                    <span class="al-badge {{ $imp->filas_con_error > 0 ? 'is-mal' : 'is-muted' }}">
                                        {{ $imp->filas_con_error }}
                                    </span>
                                </td>
                                <td class="text-center">
                                    <span class="al-badge {{ $imp->estado === 'COMPLETADO' ? 'is-ok' : 'is-warn' }}">
                                        {{ $imp->estado }}
                                    </span>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7">
                                    <div class="al-vacio">
                                        <i class="fas fa-folder-open"></i>
                                        No hay historial de importaciones realizadas.
                                    </div>
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($importaciones->hasPages())
                <div class="al-paginacion">
                    {{ $importaciones->links() }}
                </div>
            @endif
        </div>
    </div>
@endsection

@push('scripts')
    <script src="{{ asset('js/rp-lector.js') }}?v={{ @filemtime(public_path('js/rp-lector.js')) }}"></script>
@endpush
