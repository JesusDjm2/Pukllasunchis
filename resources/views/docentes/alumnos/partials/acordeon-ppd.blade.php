{{-- Espera $docente y $alumnosPorCurso (array [curso_id => Collection<User>]) --}}
@php
    $alumnosPorCurso = collect($alumnosPorCurso)->sortBy(function ($alumnos, $cursoId) use ($docente) {
        $curso = $docente->cursos->find($cursoId);
        preg_match('/\d+/', optional($curso?->ciclo)->nombre ?? '', $m);
        return $m[0] ?? 0;
    });
    $hayAlumnos = $alumnosPorCurso->filter(fn ($a) => $a->isNotEmpty())->isNotEmpty();
@endphp

<div class="row">
    <div class="col-12">
        @forelse ($alumnosPorCurso as $cursoId => $alumnos)
            @if ($alumnos->isEmpty()) @continue @endif
            @php
                $cursoObj       = $docente->cursos->find($cursoId);
                $cicloNombre    = optional($cursoObj?->ciclo)->nombre ?? null;
                $programaNombre = optional($cursoObj?->ciclo?->programa)->nombre ?? null;
                $totalAlum      = $alumnos->count();
            @endphp
            <div class="card shadow-sm mb-3 border-0">
                {{-- Cabecera del curso --}}
                <div class="card-header p-0 border-0"
                     style="background: linear-gradient(90deg,#1cc88a 0%,#13855c 100%);">
                    <button class="btn btn-link text-white accordion-btn-curso py-3 px-4"
                            type="button" data-toggle="collapse"
                            data-target="#colPpd{{ $loop->index }}"
                            aria-expanded="false"
                            aria-controls="colPpd{{ $loop->index }}">
                        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                            <i class="fas fa-graduation-cap mr-2"></i>
                            <span class="font-weight-bold" style="font-size:.95rem;">
                                {{ $cursoObj?->nombre ?? 'Curso' }}
                            </span>
                            @if($programaNombre || $cicloNombre)
                                <span class="text-white-50" style="font-size:.8rem;">
                                    — {{ $programaNombre }} {{ $cicloNombre ? '· '.$cicloNombre : '' }}
                                </span>
                            @endif
                            <span class="curso-count-badge ml-auto">
                                <i class="fas fa-users fa-xs mr-1"></i>{{ $totalAlum }}
                            </span>
                        </div>
                    </button>
                </div>

                {{-- Lista de alumnos --}}
                <div id="colPpd{{ $loop->index }}"
                     class="collapse">
                    <div class="table-responsive acordeon-scroll">
                        <table class="table table-hover table-sm mb-0">
                            <thead style="background:#f8f9fc;">
                                <tr>
                                    <th style="width:52px;" class="border-top-0 pl-3">Foto</th>
                                    <th class="border-top-0">Estudiante</th>
                                    <th class="border-top-0 d-none d-md-table-cell">Ciclo / Programa</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach ($alumnos as $alumno)
                                    @php
                                        /* $alumno es User model.
                                           Los alumnos PPD guardan la ruta completa (img/admin/ppd/archivo.jpg)
                                           al convertirse desde postulante; los FID y los editados manualmente
                                           guardan solo el nombre de archivo dentro de img/estudiantes/. */
                                        $fotoUrl = $alumno->foto
                                            ? asset(str_contains($alumno->foto, '/') ? $alumno->foto : 'img/estudiantes/'.$alumno->foto)
                                            : null;
                                        $numAlumno = optional($alumno->alumnoB)->numero ?? null;
                                        $fullName  = trim($alumno->apellidos.', '.$alumno->name);
                                    @endphp
                                    <tr class="alumno-row">
                                        <td class="pl-3">
                                            @if ($fotoUrl)
                                                <img src="{{ $fotoUrl }}"
                                                     alt="Foto {{ $alumno->name }}"
                                                     class="alumno-avatar"
                                                     onclick="docenteOpenPhotoModal('{{ $fotoUrl }}', '{{ $fullName }}')"
                                                     oncontextmenu="return false;">
                                            @else
                                                <div class="alumno-avatar-placeholder">
                                                    <i class="fas fa-user"></i>
                                                </div>
                                            @endif
                                        </td>
                                        <td>
                                            <div class="alumno-nombre">{{ $fullName }}</div>
                                            <div class="alumno-meta mt-1">
                                                @if ($alumno->email)
                                                    <span><i class="fas fa-envelope fa-xs mr-1"></i>{{ $alumno->email }}</span>
                                                @endif
                                                @if ($numAlumno)
                                                    <span class="ml-2"><i class="fas fa-phone fa-xs mr-1"></i>{{ $numAlumno }}</span>
                                                @endif
                                            </div>
                                            @if ($alumno->beca == 1)
                                                <span class="badge badge-success curso-pill mt-1">Beca</span>
                                            @endif
                                            @if ($alumno->es_inhabilitado)
                                                <span class="badge badge-warning curso-pill mt-1">Licencia</span>
                                            @endif
                                        </td>
                                        <td class="d-none d-md-table-cell">
                                            @if ($alumno->ciclo)
                                                <span class="badge badge-success curso-pill">
                                                    {{ $alumno->ciclo->nombre }}
                                                </span>
                                            @endif
                                            @if ($alumno->programa)
                                                <span class="badge badge-light curso-pill text-muted border mt-1">
                                                    {{ $alumno->programa->nombre }}
                                                </span>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        @empty
        @endforelse

        @if (!$hayAlumnos)
            <div class="card docente-ui-card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-user-graduate fa-2x mb-3 text-gray-300 d-block"></i>
                    <p class="mb-0 font-weight-bold">No hay alumnos PPD para mostrar</p>
                    <p class="small mb-0">Cuando haya matrículas en sus cursos PPD, aparecerán aquí.</p>
                </div>
            </div>
        @endif
    </div>
</div>
