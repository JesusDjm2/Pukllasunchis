@extends('layouts.admin')
@section('contenido')
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
        integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h2 class="h3 mb-0 text-gray-800">
                Alumno: <strong>{{ $alumno->apellidos }}, {{ $alumno->nombres }}</strong>
            </h2>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12 table-responsive">
                <div class="accordion" id="accordionAlumno">
                    {{-- ========================= CARRERA ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header text-center fw-bold" id="headingCarrera">
                            <button class="accordion-button justify-content-center fw-bold bg-secondary text-white"
                                type="button" data-bs-toggle="collapse" data-bs-target="#collapseCarrera"
                                aria-expanded="true" aria-controls="collapseCarrera">
                                🎓 Carrera
                            </button>
                        </h2>
                        <div id="collapseCarrera" class="accordion-collapse collapse show" aria-labelledby="headingCarrera"
                            data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <div class="row align-items-start mb-3">
                                    <div class="col-md-3 text-center">
                                        @if ($alumno->user && $alumno->user->foto )
                                            <div class="card shadow-sm border-0">
                                                <div class="card-body p-2 text-center">
                                                    <img src="{{ asset('img/estudiantes/' . $alumno->user->foto) }}"
                                                        alt="Foto de {{ $alumno->nombres }}"
                                                        class="img-fluid rounded-circle border border-3 mb-2"
                                                        style="width: 200px; height: 200px; object-fit: cover;">
                                                        <p>{{ $alumno->apellidos }}, {{ $alumno->nombres }}</p>
                                                </div>
                                            </div>
                                        @else
                                            <div class="card shadow-sm border-0 text-center">
                                                <div class="card-body p-3">
                                                    <img src="{{ asset('img/default-user.png') }}" alt="Sin foto"
                                                        class="img-fluid rounded-circle border border-3 mb-2"
                                                        style="width: 140px; height: 140px; object-fit: cover;">
                                                    <h6 class="fw-bold text-secondary mt-2">Sin Foto</h6>
                                                </div>
                                            </div>
                                        @endif

                                    </div>
                                    <!-- Datos de Carrera -->
                                    <div class="col-md-9">
                                        <table class="table">
                                            <tbody>
                                                <tr>
                                                    <td class="fw-bold">Programa:</td>
                                                    <td>
                                                        <a style="text-decoration: none"
                                                            href="{{ route('programa.show', ['programa' => $alumno->programa->id]) }}">
                                                            {{ $alumno->programa->nombre }}
                                                        </a>
                                                    </td>
                                                    <td class="fw-bold">Ciclo:</td>
                                                    <td>
                                                        <a style="text-decoration: none"
                                                            href="{{ route('ciclo.show', ['ciclo' => $alumno->ciclo->id]) }}">
                                                            {{ $alumno->ciclo->nombre }}
                                                        </a>
                                                    </td>
                                                </tr>
                                                <tr>
                                                    <td class="fw-bold">Cursos:</td>
                                                    <td colspan="3">
                                                        @if ($alumno->cursos->isNotEmpty())
                                                            @foreach ($alumno->cursos as $curso)
                                                                <li>
                                                                    <a style="text-decoration: none"
                                                                        href="{{ route('curso.show', ['curso' => $curso->id]) }}">{{ $curso->nombre }}</a>
                                                                    @if ($curso->ciclo_id != $alumno->ciclo_id)
                                                                        <span class="badge bg-info text-dark">Ciclo
                                                                            {{ $curso->ciclo->nombre }}</span>
                                                                    @endif
                                                                </li>
                                                            @endforeach
                                                        @else
                                                            @foreach ($alumno->ciclo->cursos as $curso)
                                                                <li style="margin-left: 1.4em">
                                                                    <a style="text-decoration: none"
                                                                        href="{{ route('curso.show', ['curso' => $curso->id]) }}">{{ $curso->nombre }}</a>
                                                                </li>
                                                            @endforeach
                                                        @endif
                                                    </td>
                                                </tr>

                                                @if (isset($alumno->user->pendiente))
                                                    <tr style="background: #ff9090!important">
                                                        <td colspan="2" style="background: none">Curso(s) a cargo:</td>
                                                        <td colspan="2" style="background: none">
                                                            @php $cursos = explode(',', $alumno->user->pendiente); @endphp
                                                            @foreach ($cursos as $curso)
                                                                <li style="margin-left: 1.4em">{{ trim($curso) }}</li>
                                                            @endforeach
                                                        </td>
                                                    </tr>
                                                @endif
                                            </tbody>
                                        </table>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- <div id="collapseCarrera" class="accordion-collapse collapse show" aria-labelledby="headingCarrera"
                            data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Programa:</td>
                                            <td>
                                                <a style="text-decoration: none"
                                                    href="{{ route('programa.show', ['programa' => $alumno->programa->id]) }}">
                                                    {{ $alumno->programa->nombre }}
                                                </a>
                                            </td>
                                            <td class="fw-bold">Ciclo:</td>
                                            <td>
                                                <a style="text-decoration: none"
                                                    href="{{ route('ciclo.show', ['ciclo' => $alumno->ciclo->id]) }}">
                                                    {{ $alumno->ciclo->nombre }}
                                                </a>
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Cursos:</td>
                                            <td colspan="3">
                                                @if ($alumno->cursos->isNotEmpty())
                                                    @foreach ($alumno->cursos as $curso)
                                                        <li>
                                                            <a style="text-decoration: none"
                                                                href="{{ route('curso.show', ['curso' => $curso->id]) }}">{{ $curso->nombre }}</a>
                                                            @if ($curso->ciclo_id != $alumno->ciclo_id)
                                                                <span class="badge bg-info text-dark">Ciclo
                                                                    {{ $curso->ciclo->nombre }}</span>
                                                            @endif
                                                        </li>
                                                    @endforeach
                                                @else
                                                    @foreach ($alumno->ciclo->cursos as $curso)
                                                        <li style="margin-left: 1.4em"><a style="text-decoration: none"
                                                                href="{{ route('curso.show', ['curso' => $curso->id]) }}">{{ $curso->nombre }}</a>
                                                        </li>
                                                    @endforeach
                                                @endif
                                            </td>
                                        </tr>
                                        @if (isset($alumno->user->pendiente))
                                            <tr style="background: #ff9090!important">
                                                <td colspan="2" style="background: none">Curso(s) a cargo:</td>
                                                <td colspan="2" style="background: none">
                                                    @php $cursos = explode(',', $alumno->user->pendiente); @endphp
                                                    @foreach ($cursos as $curso)
                                                        <li style="margin-left: 1.4em">{{ trim($curso) }}</li>
                                                    @endforeach
                                                </td>
                                            </tr>
                                        @endif
                                    </tbody>
                                </table>
                            </div>
                        </div> --}}
                    </div>
                    {{-- ========================= DATOS PERSONALES ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header text-center fw-bold" id="headingDatos">
                            <button class="accordion-button collapsed fw-bold bg-secondary text-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseDatos" aria-expanded="false"
                                aria-controls="collapseDatos">
                                👤 Datos Personales
                            </button>
                        </h2>
                        <div id="collapseDatos" class="accordion-collapse collapse" aria-labelledby="headingDatos"
                            data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold" colspan="2">Nombre Completo:</td>
                                            <td colspan="2">{{ $alumno->nombres }} {{ $alumno->apellidos }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Email:</td>
                                            <td>{{ $alumno->email }}</td>
                                            <td class="fw-bold">DNI:</td>
                                            <td>{{ $alumno->dni }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Género:</td>
                                            <td>{{ $alumno->genero ?? '—' }}</td>
                                            <td class="fw-bold">Fecha de nacimiento:</td>
                                            <td>
                                                @php $fechaNacFmtShow = $alumno->fechaNacimientoResueltaFormateada(); @endphp
                                                @if ($fechaNacFmtShow !== '')
                                                    {{ $fechaNacFmtShow }}
                                                    @if ($alumno->edad !== null)
                                                        <span class="text-muted">({{ $alumno->edad }}
                                                            {{ $alumno->edad === 1 ? 'año' : 'años' }})</span>
                                                    @endif
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Número:</td>
                                            <td>{{ $alumno->numero }}</td>
                                            <td class="fw-bold">Número de referencia:</td>
                                            <td>{{ $alumno->numero_referencia }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Lugar de nacimiento:</td>
                                            <td>{{ $alumno->lugar_nacimiento ?? '—' }}</td>
                                            <td class="fw-bold">Permanencia en la vivienda:</td>
                                            <td>{{ $alumno->permanencia_vivienda ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Departamento:</td>
                                            <td>{{ $alumno->departamento ?? '—' }}</td>
                                            <td class="fw-bold">Provincia:</td>
                                            <td>{{ $alumno->provincia ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Distrito:</td>
                                            <td>{{ $alumno->distrito ?? '—' }}</td>
                                            <td class="fw-bold">Dirección:</td>
                                            <td>{{ $alumno->direccion ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Procedencia familiar:</td>
                                            <td>{{ $procedencia[$alumno->procedencia_familiar] ?? $alumno->procedencia_familiar ?? '—' }}
                                            </td>
                                            <td class="fw-bold">Sector laboral:</td>
                                            <td>{{ $alumno->sector_laboral ? $alumno->sector_laboral : '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Usuario del sistema:</td>
                                            <td colspan="3">
                                                @if ($alumno->user)
                                                    {{ $alumno->user->email }}
                                                    @if ($alumno->user->name)
                                                        <span class="text-muted">({{ $alumno->user->name }})</span>
                                                    @endif
                                                @else
                                                    <span class="text-muted">Sin cuenta vinculada</span>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Te consideras:</td>
                                            <td>{{ $consideras[$alumno->te_consideras] ?? $alumno->te_consideras }}</td>
                                            <td class="fw-bold">Lengua 1:</td>
                                            <td>{{ $alumno->lengua_1 }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Lengua 2:</td>
                                            <td>{{ $alumno->lengua_2 }}</td>
                                            <td class="fw-bold">Estado Civil:</td>
                                            <td>{{ $alumno->estado_civil }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Eres padre/madre soltero(a):</td>
                                            <td>{{ $alumno->p_m_soltero ? 'Sí' : 'No' }}</td>
                                            <td class="fw-bold">Cantidad de hijos(a):</td>
                                            <td>{{ $alumno->num_hijos }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Sector socioeconómico:</td>
                                            <td>{{ $sector[$alumno->sector_socioeconomico] ?? $alumno->sector_socioeconomico }}
                                            </td>
                                            @if (stristr($alumno->num_comprobante, 'Beca'))
                                                <td class="fw-bold text-white bg-success">N° de Comprobante:</td>
                                                <td class="bg-success text-white">Beca</td>
                                            @else
                                                <td class="fw-bold">N° de Comprobante:</td>
                                                <td>{{ $alumno->num_comprobante }}</td>
                                            @endif
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= ASPECTOS FAMILIARES ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header text-center fw-bold" id="headingFamilia">
                            <button class="accordion-button collapsed fw-bold bg-secondary text-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseFamilia" aria-expanded="false"
                                aria-controls="collapseFamilia">
                                🏠 Aspectos Familiares
                            </button>
                        </h2>
                        <div id="collapseFamilia" class="accordion-collapse collapse" aria-labelledby="headingFamilia"
                            data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Con quienes vive:</td>
                                            <td>{{ $alumno->convivientes }}</td>
                                            <td class="fw-bold">Quién mantiene su hogar:</td>
                                            <td>{{ $alumno->quien_mantiene }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Dependientes menores:</td>
                                            <td>{{ $alumno->cant_dependientes_child }}</td>
                                            <td class="fw-bold">Dependientes tercera edad:</td>
                                            <td>{{ $alumno->cant_dependientes_old }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Dependientes otros:</td>
                                            <td>{{ $alumno->cant_dependientes_otros }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= ASPECTOS EDUCATIVOS ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header text-center fw-bold" id="headingEducacion">
                            <button class="accordion-button collapsed fw-bold bg-secondary text-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseEducacion" aria-expanded="false"
                                aria-controls="collapseEducacion">
                                📚 Aspectos Educativos
                            </button>
                        </h2>
                        <div id="collapseEducacion" class="accordion-collapse collapse"
                            aria-labelledby="headingEducacion" data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Estudio/Beca:</td>
                                            <td>{{ $alumno->estudio_beca }}</td>
                                            <td class="fw-bold">Origen Beca:</td>
                                            <td>{{ $alumno->origen_beca }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Postulaciones EESP:</td>
                                            <td>{{ $alumno->postulaciones_eesp }}</td>
                                            <td class="fw-bold">Postulaciones Inst/Uni:</td>
                                            <td>{{ $alumno->postulaciones_inst_uni }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Postulaciones Otros:</td>
                                            <td>{{ $alumno->postulaciones_otros }}</td>
                                            <td class="fw-bold">Tipo de Preparación:</td>
                                            <td>{{ $alumno->tipo_preparacion }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Motivo para estudiar en EESP:</td>
                                            <td>{{ $alumno->motivo_estudio_eesp }}</td>
                                            <td class="fw-bold">Motivo para estudiar docencia:</td>
                                            <td>{{ $alumno->motivo_docencia }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Motivo por el cual elegiste tu especialidad:</td>
                                            <td>{{ $alumno->motivo_especialidad }}</td>
                                            <td class="fw-bold">¿Tienes acceso a internet en casa?</td>
                                            <td>{{ $alumno->internet ? 'Sí' : 'No' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Si no, ¿dónde se conecta?</td>
                                            <td>{{ $alumno->internet_lugar }}</td>
                                            <td class="fw-bold">Principal servicio:</td>
                                            <td>{{ $alumno->servicio_internet }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Dispositivo:</td>
                                            <td>{{ $alumno->dispositivo_internet }}</td>
                                            <td class="fw-bold">Uso:</td>
                                            <td>{{ $alumno->propio_compartido ? 'Propio' : 'Compartido' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Usa correo electrónico:</td>
                                            <td>{{ $alumno->correo ? 'Sí' : 'No' }}</td>
                                            <td class="fw-bold">Horas de estudio:</td>
                                            <td>{{ $alumno->num_hrs_estudio }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Forma de estudio:</td>
                                            <td colspan="3">{{ $alumno->forma_estudio }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= ASPECTOS SOCIOECONÓMICOS ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSocio">
                            <button class="accordion-button collapsed fw-bold bg-secondary text-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseSocio" aria-expanded="false"
                                aria-controls="collapseSocio">
                                💰 Aspectos Socioeconómicos
                            </button>
                        </h2>
                        <div id="collapseSocio" class="accordion-collapse collapse" aria-labelledby="headingSocio"
                            data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">¿Actualmente trabaja?:</td>
                                            <td>
                                                @php
                                                    $tr = $alumno->trabajas;
                                                    $trabTxt = match (true) {
                                                        $tr === 1 || $tr === '1' => 'Sí',
                                                        $tr === 0 || $tr === '0' => 'No',
                                                        default => $tr !== null && $tr !== '' ? (string) $tr : '—',
                                                    };
                                                @endphp
                                                {{ $trabTxt }}
                                            </td>
                                            <td class="fw-bold">Lugar de trabajo:</td>
                                            <td>{{ $alumno->donde_trabajas ?? '—' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Ingreso mensual:</td>
                                            <td>{{ $alumno->ingreso_mensual }}</td>
                                            <td class="fw-bold">Egreso:</td>
                                            <td>{{ $alumno->egreso }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Horas laboradas/semana:</td>
                                            <td>{{ $alumno->hrs_laboradas_sem }}</td>
                                            <td class="fw-bold">¿Recibe ayuda económica?:</td>
                                            <td>{{ $alumno->ayuda_economica ? 'Sí' : 'No' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Tiempo de ayuda:</td>
                                            <td>{{ $alumno->tiempo_ayuda }}</td>
                                            <td class="fw-bold">Tipo de apoyo:</td>
                                            <td>{{ $alumno->tipo_apoyo_formacion }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= ASPECTOS VIVIENDA ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingVivienda">
                            <button class="accordion-button collapsed fw-bold bg-secondary text-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseVivienda" aria-expanded="false"
                                aria-controls="collapseVivienda">
                                🏡 Aspectos Vivienda
                            </button>
                        </h2>
                        <div id="collapseVivienda" class="accordion-collapse collapse" aria-labelledby="headingVivienda"
                            data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Tipo de Vivienda:</td>
                                            <td>{{ $alumno->tipo_vivienda }}</td>
                                            <td class="fw-bold">Situación:</td>
                                            <td>{{ $alumno->situacion_vivienda }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Dormitorios:</td>
                                            <td>{{ $alumno->dormitorios_vivienda }}</td>
                                            <td class="fw-bold">Baños:</td>
                                            <td>{{ $alumno->banos_vivienda }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Material:</td>
                                            <td>{{ $alumno->material_vivienda }}</td>
                                            <td class="fw-bold">Bienes:</td>
                                            <td>
                                                @php
                                                    $bienesRaw = $alumno->bienes_vivienda;
                                                    $bienesList = is_array($bienesRaw)
                                                        ? $bienesRaw
                                                        : array_filter(array_map('trim', explode(',', (string) $bienesRaw)));
                                                @endphp
                                                @if (count($bienesList))
                                                    <ul class="mb-0 ps-3">
                                                        @foreach ($bienesList as $b)
                                                            <li>{{ $b }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Horas Agua:</td>
                                            <td>{{ $alumno->hrs_disponibles_agua }}</td>
                                            <td class="fw-bold">Horas Desagüe:</td>
                                            <td>{{ $alumno->hrs_disponibles_desague }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Horas Luz:</td>
                                            <td>{{ $alumno->hrs_disponibles_luz }}</td>
                                            <td class="fw-bold">Otros servicios:</td>
                                            <td>
                                                @php
                                                    $otrosRaw = $alumno->otros_servicios;
                                                    $otrosList = is_array($otrosRaw)
                                                        ? $otrosRaw
                                                        : array_filter(array_map('trim', explode(',', (string) $otrosRaw)));
                                                @endphp
                                                @if (count($otrosList))
                                                    <ul class="mb-0 ps-3">
                                                        @foreach ($otrosList as $o)
                                                            <li>{{ $o }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= ASPECTOS SALUD ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingSalud">
                            <button class="accordion-button collapsed fw-bold bg-secondary text-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseSalud" aria-expanded="false"
                                aria-controls="collapseSalud">
                                ❤️ Aspectos Salud
                            </button>
                        </h2>
                        <div id="collapseSalud" class="accordion-collapse collapse" aria-labelledby="headingSalud"
                            data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Problemas de salud:</td>
                                            <td>{{ $alumno->problemas_salud ? 'Sí' : 'No' }}</td>
                                            <td class="fw-bold">Última consulta:</td>
                                            <td>{{ $alumno->ultima_consulta ? 'Sí' : 'No' }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Motivo de la consulta:</td>
                                            <td>{{ $alumno->motivo_consulta }}</td>
                                            <td class="fw-bold">Tipo de seguro:</td>
                                            <td>{{ $alumno->tipo_seguro }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Familiar con problemas de salud:</td>
                                            <td>{{ $alumno->familiar_salud ? 'Sí' : 'No' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= ASPECTOS CULTURALES ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingCultura">
                            <button class="accordion-button collapsed fw-bold bg-secondary text-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseCultura" aria-expanded="false"
                                aria-controls="collapseCultura">
                                🎭 Aspectos Culturales
                            </button>
                        </h2>
                        <div id="collapseCultura" class="accordion-collapse collapse" aria-labelledby="headingCultura"
                            data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Frecuencia de lectura:</td>
                                            <td>{{ $alumno->frecuencia_lectura }}</td>
                                            <td class="fw-bold">Acceso a lectura:</td>
                                            <td>{{ $alumno->acceso_lectura }}</td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">Visitas a museos:</td>
                                            <td>{{ $alumno->visitas_museos }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                    {{-- ========================= ASPECTOS ADICIONALES ========================= --}}
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="headingAdicional">
                            <button class="accordion-button collapsed fw-bold bg-secondary text-white" type="button"
                                data-bs-toggle="collapse" data-bs-target="#collapseAdicional" aria-expanded="false"
                                aria-controls="collapseAdicional">
                                🌟 Aspectos Adicionales
                            </button>
                        </h2>
                        <div id="collapseAdicional" class="accordion-collapse collapse"
                            aria-labelledby="headingAdicional" data-bs-parent="#accordionAlumno">
                            <div class="accordion-body">
                                <table class="table">
                                    <tbody>
                                        <tr>
                                            <td class="fw-bold">Actividades en Internet:</td>
                                            <td>{{ $alumno->actividades_internet }}</td>
                                            <td class="fw-bold">Habilidades:</td>
                                            <td>
                                                @php
                                                    $habRaw = $alumno->habilidades;
                                                    $habList = is_array($habRaw)
                                                        ? $habRaw
                                                        : array_filter(preg_split('/[-,]/', (string) $habRaw) ?: []);
                                                @endphp
                                                @if (count($habList))
                                                    <ul class="mb-0 ps-3">
                                                        @foreach ($habList as $h)
                                                            <li>{{ trim($h) }}</li>
                                                        @endforeach
                                                    </ul>
                                                @else
                                                    —
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <td class="fw-bold">¿Dispone de tiempo libre?:</td>
                                            <td>{{ $alumno->tiempo_libre ? 'Sí' : 'No' }}</td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>



                <div class="row mt-4">
                    <div class="col-lg-12 table-responsive">
                        <table class="table bg-white align-middle">
                            <tbody>
                                <tr>
                                    <td colspan="4" class="table-dark text-center fw-bold">PERIODOS ANTERIORES
                                    </td>
                                </tr>
                                @php
                                    $periodosAgrupados = $alumno->periodo->groupBy(
                                        fn($p) => $p->periodoActual->nombre ?? 'Sin periodo definido',
                                    );
                                @endphp

                                @forelse ($periodosAgrupados as $nombrePeriodo => $periodos)
                                    {{-- Encabezado de periodo --}}
                                    <tr class="table-secondary text-center">
                                        <td colspan="4" class="fw-bold text-uppercase">
                                            📅 {{ $nombrePeriodo }}
                                        </td>
                                    </tr>

                                    {{-- Encabezado de columnas --}}
                                    <tr class="table-light fw-bold">
                                        <td>Curso(s)</td>
                                        <td>Valoración</td>
                                        <td>Calificación</td>
                                        <td>Calificación Sistema</td>
                                    </tr>

                                    {{-- Filas de datos --}}
                                    @foreach ($periodos as $p)
                                        <tr>
                                            <td>
                                                @if ($p->curso)
                                                    <a style="text-decoration: none"
                                                        href="{{ route('curso.show', ['curso' => $p->curso->id]) }}">
                                                        {{ $p->curso->nombre }}
                                                    </a>
                                                @else
                                                    N/A
                                                @endif
                                            </td>
                                            <td>{{ $p->valoracion_curso ?? '-' }}</td>
                                            <td>{{ $p->calificacion_curso ?? '-' }}</td>
                                            <td>{{ $p->calificacion_sistema ?? '-' }}</td>
                                        </tr>
                                    @endforeach
                                @empty
                                    <tr>
                                        <td colspan="4" class="text-center text-muted fst-italic">
                                            No hay calificaciones registradas aún.
                                        </td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
