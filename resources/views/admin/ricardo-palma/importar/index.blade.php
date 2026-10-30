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
                        <i class="fas fa-download"></i> Descargar Plantilla Excel
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
                                    <strong>Formato flexible:</strong> Puedes utilizar las tablas extraídas por IA a partir de escaneos o la plantilla descargable con las columnas requeridas (PERIODO, PROGRAMA, SEMESTRE, NRO_MATRICULA, APELLIDOS, NOMBRES, CURSO, NOTA).
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
                            <h2>Guía de Columnas de la Plantilla</h2>
                            <p>Estructura de datos admitida para la carga</p>
                        </div>
                    </div>
                    <div class="al-tabla-marco">
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
