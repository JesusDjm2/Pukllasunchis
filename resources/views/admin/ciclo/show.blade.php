@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
            style="border-bottom: 1px dashed #80808078">
            <h5 class="mb-0 font-weight-bold text-uppercase" style="color: #4e73df; font-size:25px">
                {{ $ciclo->programa->nombre }} - {{ $ciclo->nombre }} </h5><small class="text-info">Registros:
                {{ $alumnos->count() }} </small>
            <a href="{{ route('ciclo.index') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row bg-white">
            @if (str_contains($ciclo->nombre, 'Egresados'))
                <div class="col-lg-12 mt-0">
                    <h5 class="font-weight-bold">Alumnos: 42</h5>
                    <div class="table-responsive">
                        <table class="table table-striped table-bordered">
                            <thead class="thead-dark">
                                <tr>
                                    <th scope="col">#</th> <!-- Columna de índice -->
                                    <th scope="col">Nombres</th>
                                    <th scope="col">Programa</th>
                                    <th scope="col">Teléfono</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $alumno)
                                    <tr>
                                        <td>{{ $loop->iteration }}</td> <!-- Número de fila -->
                                        <td>{{ $alumno->nombres }}, {{ $alumno->apellidos }}</td>
                                        <td>{{ $ciclo->programa->nombre }}</td>
                                        <td>{{ $alumno->numero }}</td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="col-lg-12">
                    <h5 class="font-weight-bold">Cursos:</h5>
                </div>
                <div class="col-lg-12">
                    @if (Session::has('success'))
                        <div class="alert alert-info alert-dismissible fade show" role="alert">
                            {{ Session::get('success') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                    @if (session('error'))
                        <div class="alert alert-danger">
                            {{ session('error') }}
                            <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                    @endif
                </div>
                @php
                    // Ordenar por nombre
                    $cursosOrdenados = $ciclo->cursos->sortBy('nombre');

                    // Devuelve color hex para cada cc (normalizado)
                    function badgeColorForCC($cc)
                    {
                        $key = Str::ascii(strtolower(trim($cc ?? '')));

                        return match ($key) {
                            'formacion practica e investigacion' => '#FFD966', // amarillo
                            'formacion especifica' => '#D9B8FF', // morado claro
                            'electivo' => '#FFB266', // naranja
                            'extracurricular' => '#ff000087', // verde menta
                            'formacion general' => '#4e73df', // gris claro (sin énfasis)
                            default => '#EFEFEF', // fallback
                        };
                    }

                    // Determina si un color hex es "oscuro" (para elegir color de texto)
                    function isDarkHex($hex)
                    {
                        $hex = ltrim($hex, '#');
                        if (strlen($hex) === 3) {
                            $hex = $hex[0] . $hex[0] . $hex[1] . $hex[1] . $hex[2] . $hex[2];
                        }
                        $r = hexdec(substr($hex, 0, 2));
                        $g = hexdec(substr($hex, 2, 2));
                        $b = hexdec(substr($hex, 4, 2));
                        // Luminancia aproximada (rec. ITU)
                        $lum = 0.2126 * $r + 0.7152 * $g + 0.0722 * $b;
                        return $lum < 140; // umbral: 140 (ajustable)
                    }
                @endphp

                @foreach ($cursosOrdenados as $curso)
                    @php
                        $badgeBg = badgeColorForCC($curso->cc);
                        $badgeTextColor = isDarkHex($badgeBg) ? '#FFFFFF' : '#000000';
                    @endphp

                    <div class="col-lg-4 mb-2 mt-1">
                        <div class="card shadow-sm border-0" style="height: 175px;">
                            <div class="card-body text-center d-flex flex-column justify-content-between">
                                <div>
                                    <a href="{{ route('curso.show', $curso->id) }}" class="text-primary"
                                        style="color: inherit;">
                                        <h5 class="fw-bold" style="font-size: 18px">{{ $curso->nombre }}</h5>
                                    </a>
                                    <small class="badge"
                                        style="background-color: {{ $badgeBg }}; color: {{ $badgeTextColor }}; font-size:0.75rem; padding:0.35rem 0.5rem;">
                                        {{ $curso->cc }}
                                    </small>
                                </div>
                                {{-- <div class="mt-2">
                                    @foreach ($curso->docentes as $docente)
                                        <small class="text-info fw-semibold">
                                            {{ $docente->nombre }}{{ !$loop->last ? ',' : '' }}
                                        </small>
                                    @endforeach
                                </div> --}}
                                @if ($curso->docentes->isNotEmpty())
                                    <div>
                                        @foreach ($curso->docentes as $docente)
                                            <small class="text-info fw-semibold">
                                                {{ $docente->nombre }}{{ !$loop->last ? ',' : '' }}
                                            </small>
                                        @endforeach
                                    </div>
                                @endif

                                <div>
                                    <a href="{{ route('curso.show', $curso->id) }}" class="btn btn-sm btn-primary"
                                        title="Ver Curso">
                                        <i class="fa fa-eye fa-sm"></i> Ver Curso
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach


                {{-- @foreach ($cursosOrdenados as $curso)
                    @php
                        // Colores por tipo de componente curricular
                        $bgColor = match ($curso->cc) {
                            'Formacion Práctica e Investigación' => '#ffecb3',
                            'Formacion Específica' => '#d9b8ff9c',
                            'Electivo' => '#FFB266',
                            'Extracurricular' => '#ffddddbd',
                            default => '#efefef63',
                        };

                        $textColor = 'black'; // Legibilidad en todos los casos
                    @endphp

                    <div class="col-lg-4 mb-2 mt-1">
                        <div class="card shadow-sm border-0"
                            style="height: 175x; background-color: {{ $bgColor }}; color: {{ $textColor }};">
                            <div class="card-body text-center d-flex flex-column justify-content-between">
                                <div>
                                    <a href="{{ route('curso.show', $curso->id) }}" class="text-dark"
                                        style="color: inherit;">
                                        <h5 class="fw-bold" style="font-size: 18px">{{ $curso->nombre }}</h5>
                                    </a>
                                    <small class="badge bg-dark text-light px-2 py-1" style="font-size: 0.75rem;">
                                        {{ $curso->cc }}
                                    </small>
                                </div>
                                <div class="mt-2">
                                    @foreach ($curso->docentes as $docente)
                                        <small class="text-dark font-weight-bold">
                                            {{ $docente->nombre }}{{ !$loop->last ? ',' : '' }}
                                        </small>
                                    @endforeach
                                </div>
                                <div class="mt-2">
                                    <a href="{{ route('curso.show', $curso->id) }}" class="btn btn-sm btn-primary"
                                        title="Ver Curso">
                                        <i class="fa fa-eye fa-sm"></i> Ver Curso
                                    </a>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach --}}


                <div class="col-lg-12 mt-4">
                    <h5 class="font-weight-bold">Alumnos:</h5>
                    <form action="{{ route('ciclo.updateAlumnos') }}" method="POST">
                        @csrf
                        @method('PUT')
                        <input type="hidden" name="ciclo_id" value="{{ $ciclo->id }}">
                        <div class="form-group">
                            <label for="nuevo_ciclo">Seleccionar Nuevo Ciclo:</label>
                            <select name="nuevo_ciclo_id" id="nuevo_ciclo" class="form-control" required>
                                @foreach ($ciclosDisponibles as $cicloDisponible)
                                    @if ($cicloDisponible->id != $ciclo->id)
                                        <option value="{{ $cicloDisponible->id }}">{{ $cicloDisponible->nombre }}</option>
                                    @endif
                                @endforeach
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="alumnos"><strong> Seleccionar Alumnos para cambiar de ciclo:</strong></label>
                            <div class="table-responsive">
                                <table class="table table-bordered table-hover">
                                    <thead class="thead-dark">
                                        <tr>
                                            <th scope="col">#</th>
                                            <th scope="col">Seleccionar</th>
                                            <th scope="col">Nombre y Apellido</th>
                                            <th scope="col">DNI</th>
                                            <th scope="col">Pendiente</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alumnos as $alumno)
                                            @if ($alumno->user)
                                                @php
                                                    $esResaltado =
                                                        $alumno->user->hasRole('inhabilitado') &&
                                                        in_array($alumno->user->perfil, ['Licencia', 'Sin reserva']);
                                                @endphp
                                                {{-- <tr>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="text-center">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="alumnos[]"
                                                                value="{{ $alumno->user->id }}"
                                                                id="alumno{{ $alumno->id }}"
                                                                style="width: 15px;height: 15px; cursor: pointer;">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <label class="form-check-label" for="alumno{{ $alumno->id }}">
                                                            {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                        </label>
                                                    </td>
                                                    <td>{{ $alumno->dni }}</td>
                                                    <td>{{ $alumno->user->pendiente ?? '---' }}</td>
                                                </tr> --}}
                                                <tr
                                                    @if ($esResaltado) style="background-color: #fdcbbf" @endif>
                                                    <td>{{ $loop->iteration }}</td>
                                                    <td class="text-center">
                                                        <div class="form-check">
                                                            <input class="form-check-input" type="checkbox" name="alumnos[]"
                                                                value="{{ $alumno->user->id }}"
                                                                id="alumno{{ $alumno->id }}"
                                                                style="width: 15px;height: 15px; cursor: pointer;">
                                                        </div>
                                                    </td>
                                                    <td>
                                                        <label class="form-check-label" for="alumno{{ $alumno->id }}">
                                                            {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                        </label>
                                                        @if ($esResaltado)
                                                            <span
                                                                class="badge bg-secondary text-white ms-2">{{ $alumno->user->perfil }}</span>
                                                        @endif
                                                    </td>
                                                    <td>{{ $alumno->dni }}</td>
                                                    <td>{{ $alumno->user->pendiente ?? '---' }}</td>
                                                </tr>
                                            @endif
                                        @endforeach

                                        @foreach ($alumnosB as $alumno)
                                            <tr>
                                                <td class="text-center">
                                                    <div class="form-check">
                                                        <input class="form-check-input" type="checkbox" name="alumnos[]"
                                                            value="{{ $alumno->id }}" id="alumnoB{{ $alumno->id }}"
                                                            style="width: 15px;height: 15px; cursor: pointer;">
                                                    </div>
                                                </td>
                                                <td>
                                                    <label class="form-check-label" for="alumnoB{{ $alumno->id }}">
                                                        {{ $alumno->apellidos }}, {{ $alumno->name }}
                                                    </label>
                                                </td>
                                                <td>{{ $alumno->dni }}</td>
                                                <td>{{ $alumno->pendiente ?? '---' }}</td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                </table>
                            </div>
                        </div>
                        <button type="submit" class="btn btn-primary">Cambiar Ciclo</button>
                    </form>
                </div>
            @endif
        </div>
    </div>
@endsection
