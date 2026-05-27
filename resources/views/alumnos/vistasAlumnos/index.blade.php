@extends('layouts.alumno')
@section('titulo', 'Ficha técnica')
@section('contenido')
    @php
        $alumno = auth()->user()->alumno ?? auth()->user()->alumnoB;
        $headerActions = '';
        if (auth()->user()->alumno) {
            $headerActions =
                '<a class="btn btn-sm btn-info shadow-sm mr-1" href="' .
                e(route('ficha-matricula', ['alumno' => $alumno->id])) .
                '"><i class="fas fa-file-alt mr-1"></i> Ficha de matrícula (PDF)</a>';

            if (isset($yaMatriculado) && $yaMatriculado) {
                $headerActions .=
                    '<span class="badge badge-success px-2 py-1 align-middle" style="font-size:12px;border-radius:6px;">' .
                    '<i class="fas fa-check-circle mr-1"></i>Matriculado · ' .
                    e(optional($periodoActual)->nombre) .
                    '</span>';
            } elseif (isset($periodoActual) && $periodoActual?->formulario_habilitado) {
                $headerActions .=
                    '<a class="btn btn-sm btn-warning shadow-sm" href="' .
                    e(route('alumnos.editarDatos')) .
                    '"><i class="fas fa-graduation-cap mr-1"></i> Completar ficha de matrícula</a>';
            }
        }
    @endphp
    @include('partials.alumno-page-header', [
        'title'    => 'Ficha técnica',
        'subtitle' => 'Tus datos personales, programa, ciclo y cursos del semestre.',
        'actions'  => $headerActions,
    ])

    {{-- Flash de éxito --}}
    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show mb-3" role="alert">
            {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert" aria-label="Cerrar">
                <span aria-hidden="true">&times;</span>
            </button>
        </div>
    @endif

    {{-- Aviso de matrícula pendiente --}}
    @if (auth()->user()->alumno && isset($periodoActual) && $periodoActual && $periodoActual->formulario_habilitado && isset($yaMatriculado) && !$yaMatriculado)
        <div class="alert alert-warning d-flex align-items-center shadow-sm mb-4 py-2 border-0">
            <i class="fas fa-exclamation-circle fa-lg mr-3 flex-shrink-0"></i>
            <span>El período <strong>{{ $periodoActual->nombre }}</strong> está abierto.
                Completa tu ficha de matrícula usando el botón del encabezado.</span>
        </div>
    @endif

    @if (auth()->user()->alumno)

        {{-- ① Datos personales --}}
        <div class="alumno-shell mb-3 p-0 overflow-hidden">
            <div class="alumno-ficha-section-header">
                <i class="fas fa-user-circle mr-2"></i> Datos personales
            </div>
            <div class="alumno-ficha-profile-wrap">

                {{-- Foto de perfil --}}
                <div class="alumno-ficha-photo">
                    @if ($alumno->user && $alumno->user->foto)
                        <div class="alumno-photo-wrap text-center">
                            <img src="{{ asset('img/estudiantes/' . $alumno->user->foto) }}"
                                 alt="Foto de {{ $alumno->nombres }}"
                                 class="img-fluid alumno-ficha-foto-img">
                        </div>
                    @else
                        <div class="alumno-photo-placeholder mx-auto">
                            <i class="fa fa-user fa-3x mb-2"></i>
                            <small>Sin foto</small>
                        </div>
                    @endif
                </div>

                {{-- Datos --}}
                <div class="alumno-ficha-data">
                    <dl class="alumno-data-list">
                        <div class="alumno-data-row">
                            <dt>Nombre completo</dt>
                            <dd>{{ $alumno->nombres }} {{ $alumno->apellidos }}</dd>
                        </div>
                        <div class="alumno-data-row">
                            <dt>DNI</dt>
                            <dd>{{ $alumno->dni }}</dd>
                        </div>
                        <div class="alumno-data-row">
                            <dt>Correo</dt>
                            <dd>{{ $alumno->email }}</dd>
                        </div>
                        <div class="alumno-data-row">
                            <dt>Teléfono</dt>
                            <dd>{{ $alumno->numero }}</dd>
                        </div>
                        <div class="alumno-data-row">
                            <dt>Núm. de referencia</dt>
                            <dd>{{ $alumno->numero_referencia }}</dd>
                        </div>
                        <div class="alumno-data-row">
                            <dt>Domicilio</dt>
                            <dd>{{ $alumno->departamento }} — {{ $alumno->provincia }} — {{ $alumno->distrito }},
                                {{ $alumno->direccion }}</dd>
                        </div>
                    </dl>
                </div>

            </div>
        </div>

        {{-- ② Programa y ciclo --}}
        <div class="alumno-shell mb-3 p-0 overflow-hidden">
            <div class="alumno-ficha-section-header">
                <i class="fas fa-graduation-cap mr-2"></i> Programa y ciclo
            </div>
            <div class="alumno-prog-ciclo-wrap px-4 py-3">
                <div class="alumno-prog-item">
                    <span class="alumno-prog-label">Programa</span>
                    <span class="alumno-prog-value">{{ $alumno->programa->nombre }}</span>
                </div>
                @if ($alumno->ciclo)
                    <div class="alumno-prog-divider d-none d-md-block"></div>
                    <div class="alumno-prog-item">
                        <span class="alumno-prog-label">Ciclo</span>
                        <span class="alumno-prog-value alumno-ciclo-mono">{{ $alumno->ciclo->nombre }}</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- ③ Cursos del semestre --}}
        <div class="alumno-shell mb-3 p-0 overflow-hidden">
            <div class="alumno-ficha-section-header">
                <i class="fas fa-book-open mr-2"></i> Cursos del semestre
            </div>
            <div class="px-2 py-1">
                @php
                    $cursosAMostrar = $cursosDelAlumno ?? collect();
                @endphp
                <ul class="alumno-curso-list m-0">
                    @forelse ($cursosAMostrar as $curso)
                        <li class="alumno-curso-item">
                            <div class="alumno-curso-nombre">
                                <a href="{{ route('curso.show', $curso->id) }}">
                                    {{ $curso->nombre }}
                                </a>
                                @if ($curso->ciclo_id != $alumno->ciclo_id)
                                    <span class="badge badge-info ml-1">
                                        Ciclo {{ $curso->ciclo->nombre ?? '–' }}
                                    </span>
                                @endif
                            </div>
                            <div class="alumno-curso-accion">
                                @if (!str_contains($curso->cc ?? '', 'Extracurricular'))
                                    @php
                                        $silaboObj   = $curso->relacionsilabo ?? null;
                                        $periodoSilabo = $silaboObj->periodo ?? null;
                                    @endphp
                                    @if ($silaboObj || $curso->silabo)
                                        @if ($periodoSilabo === ($periodoActual->nombre ?? null))
                                            @php
                                                $sílaboURL = $silaboObj
                                                    ? route('silabos.show', $silaboObj->id)
                                                    : asset('docentes/silabo/' . $curso->silabo);
                                            @endphp
                                            <a href="{{ $sílaboURL }}" target="_blank"
                                               class="alumno-silabo-btn">
                                                <i class="fa fa-eye mr-1"></i> Sílabo
                                            </a>
                                        @else
                                            <span class="alumno-no-silabo">Sin sílabo disponible</span>
                                        @endif
                                    @else
                                        <span class="alumno-no-silabo">Sin sílabo</span>
                                    @endif
                                @else
                                    <span class="alumno-no-silabo">No disponible</span>
                                @endif
                            </div>
                        </li>
                    @empty
                        <li class="alumno-curso-empty">
                            <i class="fas fa-info-circle mr-1"></i>
                            No hay cursos registrados para este período.
                        </li>
                    @endforelse
                </ul>
            </div>
        </div>

        {{-- ④ Cursos a cargo (pendientes) --}}
        @if (isset($alumno->user->pendiente))
            <div class="alumno-shell alumno-pendiente-shell mb-3">
                <div class="d-flex align-items-start">
                    <i class="fas fa-exclamation-triangle fa-lg mr-3 mt-1 flex-shrink-0 text-danger"></i>
                    <div>
                        <div class="font-weight-bold mb-1">Curso(s) a cargo</div>
                        <small class="text-muted d-block mb-2">
                            Es responsabilidad del estudiante solicitar la subsanación de cursos pendientes.
                        </small>
                        <ul class="mb-0 pl-3">
                            @foreach (explode(',', $alumno->user->pendiente) as $cp)
                                <li>{{ trim($cp) }}</li>
                            @endforeach
                        </ul>
                    </div>
                </div>
            </div>
        @endif

    @else
        <div class="alumno-shell text-center py-5">
            <p class="mb-3 text-muted">Aún no tienes ficha FID registrada en el sistema.</p>
            <a class="btn btn-primary btn-lg shadow-sm" href="{{ route('vistAlumno') }}">
                <i class="fas fa-edit mr-2"></i> Completar formulario de datos
            </a>
        </div>
    @endif

@endsection
