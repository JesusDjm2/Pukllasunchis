@extends('layouts.docente')

@section('titulo', 'Ciclo ' . $ciclo->nombre . ' — Tutor')

@push('styles')
<style>
    .tutor-tabs .nav-link { font-size: .85rem; font-weight: 600; color: #858796; }
    .tutor-tabs .nav-link.active { color: #4e73df; border-bottom: 3px solid #4e73df; }
    .alumno-avatar {
        width: 44px; height: 44px; border-radius: 50%; object-fit: cover;
        border: 2px solid #e3e6f0; cursor: pointer;
        transition: transform .15s, border-color .15s;
    }
    .alumno-avatar:hover { transform: scale(1.1); border-color: #4e73df; }
    .alumno-avatar-placeholder {
        width: 44px; height: 44px; border-radius: 50%;
        background: #e9ecef; display: flex; align-items: center; justify-content: center;
        border: 2px solid #dee2e6; color: #adb5bd; font-size: 1.1rem; flex-shrink: 0;
    }
    .inc-img-thumb {
        width: 64px; height: 64px; object-fit: cover;
        border-radius: .4rem; border: 1px solid #dee2e6; cursor: pointer;
    }
    .inc-reporte { font-size: .85rem; color: #3a3b45; white-space: pre-wrap; }
    .badge-ciclo { font-size: .7rem; font-weight: 700; }
    .detalle-card {
        border: 1px solid #e7eaf3;
        border-radius: .5rem;
        background: #fff;
    }
    .detalle-card-title {
        font-size: .78rem;
        font-weight: 700;
        color: #4e73df;
        text-transform: uppercase;
        letter-spacing: .03em;
    }
    .detalle-k { font-size: .74rem; color: #6c757d; font-weight: 700; }
    .detalle-v { font-size: .83rem; color: #2f3441; }
</style>
@endpush

@section('contenido')
<div class="container-fluid docente-ui-page">
    @include('docentes.partials.ui-header', [
        'kicker'    => 'Tutor · ' . (optional($ciclo->programa)->nombre ?? ''),
        'title'     => 'Ciclo ' . $ciclo->nombre,
        'subtitle'  => 'Listado de alumnos e incidencias del ciclo.',
        'backUrl'   => route('tutor.dashboard'),
        'backLabel' => 'Mis ciclos',
    ])

    {{-- Tabs --}}
    <ul class="nav nav-tabs tutor-tabs border-bottom mb-4" id="tutorTabs">
        <li class="nav-item">
            <a class="nav-link active" data-toggle="tab" href="#tabAlumnos">
                <i class="fas fa-users mr-1"></i> Alumnos
                <span class="badge badge-primary ml-1">{{ $alumnos->count() }}</span>
            </a>
        </li>
        <li class="nav-item">
            <a class="nav-link" data-toggle="tab" href="#tabIncidencias">
                <i class="fas fa-clipboard-list mr-1"></i> Incidencias
                <span class="badge badge-secondary ml-1">{{ $incidencias->count() }}</span>
            </a>
        </li>
    </ul>

    <div class="tab-content pb-5">

        {{-- ── TAB ALUMNOS ── --}}
        <div class="tab-pane fade show active" id="tabAlumnos">
            @if ($alumnos->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-user-slash fa-2x mb-3 text-gray-300 d-block"></i>
                        <p class="mb-0 font-weight-bold">No hay alumnos en este ciclo</p>
                    </div>
                </div>
            @else
                <div class="card border-0 shadow-sm">
                    <div class="table-responsive">
                        <table class="table table-hover mb-0">
                            <thead style="background:#f8f9fc;">
                                <tr>
                                    <th style="width:60px;" class="border-top-0 pl-4">Foto</th>
                                    <th class="border-top-0">Estudiante</th>
                                    <th class="border-top-0 d-none d-md-table-cell">Contacto</th>
                                    <th class="border-top-0 d-none d-md-table-cell">Condición</th>
                                    <th class="border-top-0 text-center">Incidencias</th>
                                    <th class="border-top-0 text-center">Detalle</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $alumno)
                                    @php
                                        $fotoUrl  = ($alumno->user && $alumno->user->foto)
                                            ? asset('img/estudiantes/'.$alumno->user->foto) : null;
                                        $numInc   = $incidencias->where('alumno_id', $alumno->id)->count();
                                    @endphp
                                    <tr>
                                        <td class="pl-4 align-middle">
                                            @if ($fotoUrl)
                                                <img src="{{ $fotoUrl }}" alt="Foto"
                                                     class="alumno-avatar"
                                                     onclick="openPhotoModal('{{ $fotoUrl }}','{{ $alumno->apellidos }}, {{ $alumno->nombres }}')"
                                                     oncontextmenu="return false;">
                                            @else
                                                <div class="alumno-avatar-placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td class="align-middle">
                                            <div class="font-weight-bold" style="font-size:.9rem;">
                                                {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                            </div>
                                            <div class="text-muted small">DNI: {{ $alumno->dni ?? '—' }}</div>
                                            @if ($alumno->user?->beca == 1)
                                                <span class="badge badge-success badge-ciclo">Beca</span>
                                            @endif
                                        </td>
                                        <td class="align-middle d-none d-md-table-cell">
                                            @if ($alumno->email)
                                                <div class="small"><i class="fas fa-envelope fa-xs mr-1 text-muted"></i>{{ $alumno->email }}</div>
                                            @endif
                                            @if ($alumno->numero)
                                                <div class="small"><i class="fas fa-phone fa-xs mr-1 text-muted"></i>{{ $alumno->numero }}</div>
                                            @endif
                                        </td>
                                        <td class="align-middle d-none d-md-table-cell">
                                            @if ($alumno->condicion)
                                                <span class="badge badge-light border badge-ciclo text-muted">{{ $alumno->condicion }}</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            @if ($numInc > 0)
                                                <span class="badge badge-danger">{{ $numInc }}</span>
                                            @else
                                                <span class="text-muted small">—</span>
                                            @endif
                                        </td>
                                        <td class="align-middle text-center">
                                            <button class="btn btn-outline-primary btn-sm" type="button"
                                                data-toggle="collapse" data-target="#detalleAlumno{{ $alumno->id }}"
                                                aria-expanded="false" aria-controls="detalleAlumno{{ $alumno->id }}">
                                                <i class="fas fa-list-alt mr-1"></i> Ver detalles
                                            </button>
                                        </td>
                                    </tr>
                                    <tr class="collapse bg-light" id="detalleAlumno{{ $alumno->id }}">
                                        <td colspan="6" class="p-3">
                                            @php
                                                $siNo = function ($v) {
                                                    if ($v === null || $v === '') {
                                                        return '—';
                                                    }

                                                    return ($v === 1 || $v === '1' || $v === true) ? 'Si' : 'No';
                                                };

                                                $formateaLista = function ($raw, string $sep = ', ') {
                                                    if (is_array($raw)) {
                                                        $items = array_filter(array_map('trim', $raw));
                                                    } else {
                                                        $items = array_filter(array_map('trim', explode($sep, (string) $raw)));
                                                    }

                                                    return count($items) ? implode(', ', $items) : '—';
                                                };

                                                $fechaNac = $alumno->fechaNacimientoResueltaFormateada();
                                                $trabajaTxt = match (true) {
                                                    $alumno->trabajas === 1, $alumno->trabajas === '1' => 'Si',
                                                    $alumno->trabajas === 0, $alumno->trabajas === '0' => 'No',
                                                    default => ($alumno->trabajas !== null && $alumno->trabajas !== '') ? (string) $alumno->trabajas : '—',
                                                };

                                                $seccionesDetalle = [
                                                    [
                                                        'titulo' => 'Datos Personales',
                                                        'campos' => [
                                                            ['k' => 'Nombres', 'v' => $alumno->nombres ?? '—'],
                                                            ['k' => 'Apellidos', 'v' => $alumno->apellidos ?? '—'],
                                                            ['k' => 'DNI', 'v' => $alumno->dni ?? '—'],
                                                            ['k' => 'Genero', 'v' => $alumno->genero ?? '—'],
                                                            ['k' => 'Fecha de nacimiento', 'v' => $fechaNac !== '' ? $fechaNac : '—'],
                                                            ['k' => 'Lugar de nacimiento', 'v' => $alumno->lugar_nacimiento ?? '—'],
                                                            ['k' => 'Estado civil', 'v' => $alumno->estado_civil ?? '—'],
                                                            ['k' => 'Te consideras', 'v' => $alumno->te_consideras ?? '—'],
                                                        ],
                                                    ],
                                                    [
                                                        'titulo' => 'Contacto y Ubicacion',
                                                        'campos' => [
                                                            ['k' => 'Email', 'v' => $alumno->email ?? '—'],
                                                            ['k' => 'Numero', 'v' => $alumno->numero ?? '—'],
                                                            ['k' => 'Numero referencia', 'v' => $alumno->numero_referencia ?? '—'],
                                                            ['k' => 'Departamento', 'v' => $alumno->departamento ?? '—'],
                                                            ['k' => 'Provincia', 'v' => $alumno->provincia ?? '—'],
                                                            ['k' => 'Distrito', 'v' => $alumno->distrito ?? '—'],
                                                            ['k' => 'Direccion', 'v' => $alumno->direccion ?? '—'],
                                                            ['k' => 'Permanencia vivienda', 'v' => $alumno->permanencia_vivienda ?? '—'],
                                                        ],
                                                    ],
                                                    [
                                                        'titulo' => 'Datos Academicos',
                                                        'campos' => [
                                                            ['k' => 'Programa', 'v' => optional($alumno->programa)->nombre ?? '—'],
                                                            ['k' => 'Ciclo', 'v' => optional($alumno->ciclo)->nombre ?? '—'],
                                                            ['k' => 'Condicion', 'v' => $alumno->condicion ?? '—'],
                                                            ['k' => 'Procedencia familiar', 'v' => $alumno->procedencia_familiar ?? '—'],
                                                            ['k' => 'Sector laboral', 'v' => $alumno->sector_laboral ?? '—'],
                                                            ['k' => 'Lengua 1', 'v' => $alumno->lengua_1 ?? '—'],
                                                            ['k' => 'Lengua 2', 'v' => $alumno->lengua_2 ?? '—'],
                                                            ['k' => 'Num. comprobante', 'v' => $alumno->num_comprobante ?? '—'],
                                                        ],
                                                    ],
                                                    [
                                                        'titulo' => 'Aspectos Educativos',
                                                        'campos' => [
                                                            ['k' => 'Estudio/Beca', 'v' => $alumno->estudio_beca ?? '—'],
                                                            ['k' => 'Origen beca', 'v' => $alumno->origen_beca ?? '—'],
                                                            ['k' => 'Postulaciones EESP', 'v' => $alumno->postulaciones_eesp ?? '—'],
                                                            ['k' => 'Postulaciones Inst/Uni', 'v' => $alumno->postulaciones_inst_uni ?? '—'],
                                                            ['k' => 'Postulaciones otros', 'v' => $alumno->postulaciones_otros ?? '—'],
                                                            ['k' => 'Tipo preparacion', 'v' => $alumno->tipo_preparacion ?? '—'],
                                                            ['k' => 'Motivo estudio EESP', 'v' => $alumno->motivo_estudio_eesp ?? '—'],
                                                            ['k' => 'Motivo docencia', 'v' => $alumno->motivo_docencia ?? '—'],
                                                            ['k' => 'Motivo especialidad', 'v' => $alumno->motivo_especialidad ?? '—'],
                                                            ['k' => 'Internet en casa', 'v' => $siNo($alumno->internet)],
                                                            ['k' => 'Lugar de internet', 'v' => $alumno->internet_lugar ?? '—'],
                                                            ['k' => 'Servicio internet', 'v' => $alumno->servicio_internet ?? '—'],
                                                            ['k' => 'Dispositivo internet', 'v' => $alumno->dispositivo_internet ?? '—'],
                                                            ['k' => 'Equipo propio/compartido', 'v' => $siNo($alumno->propio_compartido)],
                                                            ['k' => 'Usa correo', 'v' => $siNo($alumno->correo)],
                                                            ['k' => 'Horas de estudio', 'v' => $alumno->num_hrs_estudio ?? '—'],
                                                            ['k' => 'Forma de estudio', 'v' => $alumno->forma_estudio ?? '—'],
                                                        ],
                                                    ],
                                                    [
                                                        'titulo' => 'Aspectos Familiares y Socioeconomicos',
                                                        'campos' => [
                                                            ['k' => 'Convivientes', 'v' => $alumno->convivientes ?? '—'],
                                                            ['k' => 'Quien mantiene', 'v' => $alumno->quien_mantiene ?? '—'],
                                                            ['k' => 'Dep. menores', 'v' => $alumno->cant_dependientes_child ?? '—'],
                                                            ['k' => 'Dep. adultos mayores', 'v' => $alumno->cant_dependientes_old ?? '—'],
                                                            ['k' => 'Dep. otros', 'v' => $alumno->cant_dependientes_otros ?? '—'],
                                                            ['k' => 'Padre/Madre soltero(a)', 'v' => $siNo($alumno->p_m_soltero)],
                                                            ['k' => 'Num. hijos', 'v' => $alumno->num_hijos ?? '—'],
                                                            ['k' => 'Sector socioeconomico', 'v' => $alumno->sector_socioeconomico ?? '—'],
                                                            ['k' => 'Trabaja', 'v' => $trabajaTxt],
                                                            ['k' => 'Donde trabaja', 'v' => $alumno->donde_trabajas ?? '—'],
                                                            ['k' => 'Ingreso mensual', 'v' => $alumno->ingreso_mensual ?? '—'],
                                                            ['k' => 'Egreso', 'v' => $alumno->egreso ?? '—'],
                                                            ['k' => 'Horas laboradas/sem.', 'v' => $alumno->hrs_laboradas_sem ?? '—'],
                                                            ['k' => 'Ayuda economica', 'v' => $siNo($alumno->ayuda_economica)],
                                                            ['k' => 'Tiempo de ayuda', 'v' => $alumno->tiempo_ayuda ?? '—'],
                                                            ['k' => 'Tipo apoyo formacion', 'v' => $alumno->tipo_apoyo_formacion ?? '—'],
                                                        ],
                                                    ],
                                                    [
                                                        'titulo' => 'Vivienda, Salud y Adicional',
                                                        'campos' => [
                                                            ['k' => 'Tipo vivienda', 'v' => $alumno->tipo_vivienda ?? '—'],
                                                            ['k' => 'Situacion vivienda', 'v' => $alumno->situacion_vivienda ?? '—'],
                                                            ['k' => 'Dormitorios', 'v' => $alumno->dormitorios_vivienda ?? '—'],
                                                            ['k' => 'Banos', 'v' => $alumno->banos_vivienda ?? '—'],
                                                            ['k' => 'Material vivienda', 'v' => $alumno->material_vivienda ?? '—'],
                                                            ['k' => 'Bienes vivienda', 'v' => $formateaLista($alumno->bienes_vivienda)],
                                                            ['k' => 'Horas agua', 'v' => $alumno->hrs_disponibles_agua ?? '—'],
                                                            ['k' => 'Horas desague', 'v' => $alumno->hrs_disponibles_desague ?? '—'],
                                                            ['k' => 'Horas luz', 'v' => $alumno->hrs_disponibles_luz ?? '—'],
                                                            ['k' => 'Otros servicios', 'v' => $formateaLista($alumno->otros_servicios)],
                                                            ['k' => 'Problemas de salud', 'v' => $siNo($alumno->problemas_salud)],
                                                            ['k' => 'Ultima consulta', 'v' => $siNo($alumno->ultima_consulta)],
                                                            ['k' => 'Motivo consulta', 'v' => $alumno->motivo_consulta ?? '—'],
                                                            ['k' => 'Tipo seguro', 'v' => $alumno->tipo_seguro ?? '—'],
                                                            ['k' => 'Familiar con salud delicada', 'v' => $siNo($alumno->familiar_salud)],
                                                            ['k' => 'Frecuencia lectura', 'v' => $alumno->frecuencia_lectura ?? '—'],
                                                            ['k' => 'Acceso lectura', 'v' => $alumno->acceso_lectura ?? '—'],
                                                            ['k' => 'Visitas museos', 'v' => $alumno->visitas_museos ?? '—'],
                                                            ['k' => 'Actividades internet', 'v' => $alumno->actividades_internet ?? '—'],
                                                            ['k' => 'Habilidades', 'v' => $formateaLista(str_replace('-', ',', (string) $alumno->habilidades))],
                                                            ['k' => 'Tiempo libre', 'v' => $siNo($alumno->tiempo_libre)],
                                                        ],
                                                    ],
                                                ];
                                            @endphp
                                            <div class="row">
                                                @foreach ($seccionesDetalle as $seccion)
                                                    <div class="col-lg-4 mb-3">
                                                        <div class="detalle-card h-100 p-3">
                                                            <div class="detalle-card-title mb-2">{{ $seccion['titulo'] }}</div>
                                                            @foreach ($seccion['campos'] as $campo)
                                                                <div class="mb-2">
                                                                    <div class="detalle-k">{{ $campo['k'] }}</div>
                                                                    <div class="detalle-v">{{ $campo['v'] }}</div>
                                                                </div>
                                                            @endforeach
                                                        </div>
                                                    </div>
                                                @endforeach
                                            </div>
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            @endif
        </div>

        {{-- ── TAB INCIDENCIAS ── --}}
        <div class="tab-pane fade" id="tabIncidencias">
            @if ($incidencias->isEmpty())
                <div class="card border-0 shadow-sm">
                    <div class="card-body text-center text-muted py-5">
                        <i class="fas fa-check-circle fa-2x mb-3 text-gray-300 d-block"></i>
                        <p class="mb-0 font-weight-bold">Sin incidencias registradas</p>
                        <p class="small mb-0">Los docentes podrán reportar incidencias desde su panel.</p>
                    </div>
                </div>
            @else
                <div class="d-flex flex-column" style="gap:.85rem;">
                    @foreach ($incidencias as $inc)
                        @php
                            $aluCiclo    = optional($inc->alumno)->ciclo;
                            $aluPrograma = optional($aluCiclo)->programa;
                        @endphp
                        <div class="card border-0 shadow-sm">
                            <div class="card-header bg-white border-bottom py-2 px-3 d-flex align-items-center flex-wrap" style="gap:.5rem;">
                                {{-- Nombre del alumno (sin color alarmante) --}}
                                <span class="font-weight-bold text-gray-800" style="font-size:.88rem;">
                                    {{ optional($inc->alumno)->apellidos }}, {{ optional($inc->alumno)->nombres }}
                                </span>

                                {{-- Programa → Ciclo del alumno --}}
                                @if ($aluPrograma || $aluCiclo)
                                    <span class="text-muted" style="font-size:.78rem;">
                                        {{ $aluPrograma?->nombre }}
                                        @if ($aluPrograma && $aluCiclo) · @endif
                                        {{ $aluCiclo ? 'Ciclo '.$aluCiclo->nombre : '' }}
                                    </span>
                                @endif

                                {{-- Fecha --}}
                                <span class="badge badge-light border text-muted ml-auto" style="font-size:.7rem;">
                                    <i class="fas fa-calendar-alt fa-xs mr-1"></i>
                                    {{ \Carbon\Carbon::parse($inc->fecha)->format('d/m/Y') }}
                                </span>

                                {{-- Docente que reportó --}}
                                <span class="badge badge-light border text-muted" style="font-size:.7rem;">
                                    <i class="fas fa-chalkboard-teacher fa-xs mr-1"></i>
                                    {{ optional($inc->docente)->nombre ?? $inc->nombre_docente ?? '—' }}
                                </span>
                            </div>
                            <div class="card-body py-3 px-3">
                                <p class="inc-reporte mb-0">{{ $inc->reporte }}</p>
                                @if ($inc->imagen)
                                    <div class="mt-2">
                                        <img src="{{ asset('img/incidencias/'.$inc->imagen) }}"
                                             alt="Imagen adjunta"
                                             class="inc-img-thumb"
                                             onclick="openPhotoModal('{{ asset('img/incidencias/'.$inc->imagen) }}', 'Imagen adjunta')"
                                             oncontextmenu="return false;">
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endforeach
                </div>
            @endif
        </div>
    </div>
</div>

{{-- Modal foto --}}
<div id="photoModal" class="docente-photo-modal-overlay" role="dialog" aria-modal="true"
     onclick="closePhotoModal()">
    <div class="docente-photo-modal-inner" onclick="event.stopPropagation();">
        <button type="button" class="docente-photo-modal-close" onclick="closePhotoModal()"
                aria-label="Cerrar">&times;</button>
        <img id="photoModalImg" src="" alt="" oncontextmenu="return false;">
        <p id="photoModalName" class="text-white text-center mt-2 mb-0 font-weight-bold"
           style="font-size:.9rem;text-shadow:0 1px 3px rgba(0,0,0,.6);"></p>
    </div>
</div>
@endsection

@push('scripts')
<script>
function openPhotoModal(src, name) {
    var el = document.getElementById('photoModal');
    document.getElementById('photoModalImg').src = src;
    document.getElementById('photoModalName').textContent = name || '';
    if (el) el.classList.add('is-open');
}
function closePhotoModal() {
    var el = document.getElementById('photoModal');
    if (el) el.classList.remove('is-open');
}
document.addEventListener('keydown', function(e){ if(e.key==='Escape') closePhotoModal(); });
</script>
@endpush
