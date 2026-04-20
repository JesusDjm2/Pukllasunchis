@extends('layouts.docente')

@section('titulo', 'Alumnos PPD — Docente')

@push('styles')
<style>
    .alumno-row td { vertical-align: middle; }
    .alumno-avatar {
        width: 58px; height: 58px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e3e6f0;
        cursor: pointer;
        transition: transform .15s, border-color .15s;
    }
    .alumno-avatar:hover { transform: scale(1.08); border-color: #1cc88a; }
    .alumno-avatar-placeholder {
        width: 58px; height: 58px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #dee2e6;
        color: #adb5bd;
        font-size: 1.4rem;
        flex-shrink: 0;
    }
    .alumno-nombre { font-weight: 600; font-size: .92rem; color: #3a3b45; line-height: 1.3; }
    .alumno-meta   { font-size: .78rem; color: #858796; }
    .curso-pill    { font-size: .72rem; font-weight: 600; letter-spacing: .03em; }
    .accordion-btn-curso { text-align: left; width: 100%; }
    .accordion-btn-curso:focus { box-shadow: none; }
    .curso-count-badge {
        background: rgba(255,255,255,.22);
        border-radius: 20px;
        padding: 2px 10px;
        font-size: .75rem;
        font-weight: 700;
        letter-spacing: .02em;
    }
</style>
@endpush

@section('contenido')
    @php
        $alumnosPorCurso = collect($alumnosPorCurso)->sortBy(function ($alumnos, $cursoId) use ($docente) {
            $curso = $docente->cursos->find($cursoId);
            preg_match('/\d+/', optional($curso?->ciclo)->nombre ?? '', $m);
            return $m[0] ?? 0;
        });
    @endphp

    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker'    => 'Alumnos PPD',
            'title'     => 'Alumnos por curso',
            'subtitle'  => 'Profesionalización pedagógica docente.',
            'backUrl'   => route('calificar', $docente->id),
            'backLabel' => 'Calificaciones',
        ])

        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
                {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        <div class="row pb-5">
            <div class="col-12">
                @php $hayAlumnos = collect($alumnosPorCurso)->filter(fn($a) => $a->isNotEmpty())->isNotEmpty(); @endphp

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
                                    aria-expanded="{{ $loop->first ? 'true' : 'false' }}"
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
                             class="collapse {{ $loop->first ? 'show' : '' }}">
                            <div class="table-responsive">
                                <table class="table table-hover mb-0">
                                    <thead style="background:#f8f9fc;">
                                        <tr>
                                            <th style="width:72px;" class="border-top-0 pl-4">Foto</th>
                                            <th class="border-top-0">Estudiante</th>
                                            <th class="border-top-0 d-none d-md-table-cell">Ciclo / Programa</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($alumnos as $alumno)
                                            @php
                                                /* $alumno es User model */
                                                $fotoUrl   = $alumno->foto
                                                    ? asset('img/estudiantes/'.$alumno->foto)
                                                    : null;
                                                $numAlumno = optional($alumno->alumnoB)->numero ?? null;
                                                $fullName  = trim($alumno->apellidos.', '.$alumno->name);
                                            @endphp
                                            <tr class="alumno-row">
                                                <td class="pl-4">
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
    </div>

    {{-- Modal foto --}}
    <div id="docentePhotoModal" class="docente-photo-modal-overlay" role="dialog" aria-modal="true"
         aria-label="Vista ampliada de foto" onclick="docenteClosePhotoModal()">
        <div class="docente-photo-modal-inner" onclick="event.stopPropagation();">
            <button type="button" class="docente-photo-modal-close" onclick="docenteClosePhotoModal()"
                    aria-label="Cerrar">&times;</button>
            <img id="docentePhotoModalImg" src="" alt="" oncontextmenu="return false;">
            <p id="docentePhotoModalName" class="text-white text-center mt-2 mb-0 font-weight-bold"
               style="font-size:.9rem;text-shadow:0 1px 3px rgba(0,0,0,.6);"></p>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function docenteOpenPhotoModal(src, name) {
        var el  = document.getElementById('docentePhotoModal');
        var img = document.getElementById('docentePhotoModalImg');
        var lbl = document.getElementById('docentePhotoModalName');
        if (!el || !img) return;
        img.src = src;
        img.alt = name || 'Foto del estudiante';
        if (lbl) lbl.textContent = name || '';
        el.classList.add('is-open');
    }
    function docenteClosePhotoModal() {
        var el = document.getElementById('docentePhotoModal');
        if (el) el.classList.remove('is-open');
    }
    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') docenteClosePhotoModal();
    });
</script>
@endpush
