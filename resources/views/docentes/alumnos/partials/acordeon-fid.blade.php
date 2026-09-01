{{-- Espera $docente y $alumnosPorCurso (array [curso_id => Collection<Alumno>]) --}}
<div class="row">
    <div class="col-12">
        @forelse ($alumnosPorCurso as $cursoId => $alumnos)
            @php
                $cursoObj   = $docente->cursos->find($cursoId);
                $totalAlum  = $alumnos->count();
                $cicloNombre   = optional($cursoObj?->ciclo)->nombre ?? null;
                $programaNombre = optional($cursoObj?->ciclo?->programa)->nombre ?? null;
            @endphp
            <div class="card shadow-sm mb-3 border-0">
                {{-- Cabecera del curso --}}
                <div class="card-header p-0 border-0"
                     style="background: linear-gradient(90deg,#4e73df 0%,#224abe 100%);">
                    <button class="btn btn-link text-white accordion-btn-curso py-3 px-4"
                            type="button" data-toggle="collapse"
                            data-target="#colFid{{ $loop->index }}"
                            aria-expanded="false"
                            aria-controls="colFid{{ $loop->index }}">
                        <div class="d-flex align-items-center flex-wrap" style="gap:.5rem;">
                            <i class="fas fa-book mr-2"></i>
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
                <div id="colFid{{ $loop->index }}"
                     class="collapse">
                    @if ($alumnos->isEmpty())
                        <div class="card-body text-muted text-center py-4">
                            <i class="fas fa-user-slash fa-lg mb-2 d-block text-gray-300"></i>
                            No hay alumnos asignados a este curso.
                        </div>
                    @else
                        <div class="table-responsive acordeon-scroll">
                            <table class="table table-hover table-sm mb-0">
                                <thead style="background:#f8f9fc;">
                                    <tr>
                                        <th style="width:52px;" class="border-top-0 pl-3">Foto</th>
                                        <th class="border-top-0">Estudiante</th>
                                        <th class="border-top-0 d-none d-md-table-cell">Ciclo / Info</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach ($alumnos->sortBy('apellidos') as $alumno)
                                        @php
                                            $fotoUrl = ($alumno->user && $alumno->user->foto)
                                                ? asset('img/estudiantes/'.$alumno->user->foto)
                                                : null;
                                            $cicloDistinto = $alumno->ciclo_id
                                                && $cursoObj?->ciclo_id
                                                && $alumno->ciclo_id !== $cursoObj->ciclo_id;
                                        @endphp
                                        <tr class="alumno-row">
                                            <td class="pl-3">
                                                @if ($fotoUrl)
                                                    <img src="{{ $fotoUrl }}"
                                                         alt="Foto {{ $alumno->nombres }}"
                                                         class="alumno-avatar"
                                                         onclick="docenteOpenPhotoModal('{{ $fotoUrl }}', '{{ $alumno->apellidos }}, {{ $alumno->nombres }}')"
                                                         oncontextmenu="return false;">
                                                @else
                                                    <div class="alumno-avatar-placeholder">
                                                        <i class="fas fa-user"></i>
                                                    </div>
                                                @endif
                                            </td>
                                            <td>
                                                <div class="alumno-nombre">
                                                    {{ $alumno->apellidos }}, {{ $alumno->nombres }}
                                                </div>
                                                <div class="alumno-meta mt-1">
                                                    @if ($alumno->email)
                                                        <span><i class="fas fa-envelope fa-xs mr-1"></i>{{ $alumno->email }}</span>
                                                    @endif
                                                    @if ($alumno->numero)
                                                        <span class="ml-2"><i class="fas fa-phone fa-xs mr-1"></i>{{ $alumno->numero }}</span>
                                                    @endif
                                                </div>
                                                @if ($alumno->user?->beca == 1)
                                                    <span class="badge badge-success curso-pill mt-1">Beca</span>
                                                @endif
                                                @if ($alumno->es_inhabilitado)
                                                    <span class="badge badge-warning curso-pill mt-1">Licencia</span>
                                                @endif
                                            </td>
                                            <td class="d-none d-md-table-cell">
                                                @if ($cicloDistinto)
                                                    <span class="badge badge-info curso-pill">
                                                        {{ optional($alumno->ciclo)->nombre ?? '—' }}
                                                    </span>
                                                @endif
                                                @if ($alumno->condicion)
                                                    <span class="badge badge-light curso-pill text-muted border">
                                                        {{ $alumno->condicion }}
                                                    </span>
                                                @endif
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    @endif
                </div>
            </div>
        @empty
            <div class="card docente-ui-card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-users fa-2x mb-3 text-gray-300 d-block"></i>
                    <p class="mb-0 font-weight-bold">No hay cursos FID asignados</p>
                    <p class="small mb-0">Cuando le asignen cursos, aparecerán aquí.</p>
                </div>
            </div>
        @endforelse
    </div>
</div>
