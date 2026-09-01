@extends('layouts.docente')

@section('titulo', 'Alumnos — Docente')

@push('styles')
<style>
    .alumnos-tabs .nav-link { font-size: .85rem; font-weight: 600; color: #858796; }
    .alumnos-tabs .nav-link.active { color: #4e73df; border-bottom: 3px solid #4e73df; }
    .alumno-row td { vertical-align: middle; padding-top: .4rem; padding-bottom: .4rem; }
    .alumno-avatar {
        width: 38px; height: 38px;
        border-radius: 50%;
        object-fit: cover;
        border: 2px solid #e3e6f0;
        cursor: pointer;
        transition: transform .15s, border-color .15s;
    }
    .alumno-avatar:hover { transform: scale(1.08); border-color: #4e73df; }
    .alumno-avatar-placeholder {
        width: 38px; height: 38px;
        border-radius: 50%;
        background: #e9ecef;
        display: flex; align-items: center; justify-content: center;
        border: 2px solid #dee2e6;
        color: #adb5bd;
        font-size: 1rem;
        flex-shrink: 0;
    }
    .alumno-nombre { font-weight: 600; font-size: .82rem; color: #3a3b45; line-height: 1.25; }
    .alumno-meta   { font-size: .71rem; color: #858796; }
    .curso-pill    { font-size: .68rem; font-weight: 600; letter-spacing: .03em; padding: .28em .5em; }
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
    /* Cada curso desplegado hace scroll propio en vez de estirar toda la
       página cuando tiene muchos alumnos — evita listas kilométricas. */
    .acordeon-scroll {
        max-height: 460px;
        overflow-y: auto;
    }
</style>
@endpush

@section('contenido')
    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker'    => 'Alumnos',
            'title'     => 'Alumnos por curso',
            'subtitle'  => 'Despliegue cada curso para ver la lista de estudiantes matriculados.',
            'backUrl'   => route('vistaDocente', $docente->id),
            'backLabel' => 'Volver a cursos',
        ])

        @if (Session::has('success'))
            <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
                {{ Session::get('success') }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if (!$mostrarFid && !$mostrarPpd)
            <div class="card docente-ui-card">
                <div class="card-body text-center text-muted py-5">
                    <i class="fas fa-users fa-2x mb-3 text-gray-300 d-block"></i>
                    <p class="mb-0 font-weight-bold">No tiene cursos asignados</p>
                    <p class="small mb-0">Cuando le asignen cursos, sus alumnos aparecerán aquí.</p>
                </div>
            </div>
        @else
            @if ($mostrarFid && $mostrarPpd)
                {{-- ── Ambos: FID y PPD → tabs ── --}}
                <ul class="nav nav-tabs alumnos-tabs border-bottom mb-4" id="alumnosTabs">
                    <li class="nav-item">
                        <a class="nav-link active" data-toggle="tab" href="#tabAlumnosFid">
                            <i class="fas fa-book mr-1"></i> Alumnos FID
                        </a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" data-toggle="tab" href="#tabAlumnosPpd">
                            <i class="fas fa-graduation-cap mr-1"></i> Alumnos PPD
                        </a>
                    </li>
                </ul>

                <div class="tab-content pb-5">
                    <div class="tab-pane fade show active" id="tabAlumnosFid">
                        @include('docentes.alumnos.partials.acordeon-fid', ['alumnosPorCurso' => $alumnosPorCursoFid])
                    </div>
                    <div class="tab-pane fade" id="tabAlumnosPpd">
                        @include('docentes.alumnos.partials.acordeon-ppd', ['alumnosPorCurso' => $alumnosPorCursoPpd])
                    </div>
                </div>
            @elseif ($mostrarFid)
                {{-- ── Solo FID ── --}}
                @include('docentes.alumnos.partials.acordeon-fid', ['alumnosPorCurso' => $alumnosPorCursoFid])
            @else
                {{-- ── Solo PPD ── --}}
                @include('docentes.alumnos.partials.acordeon-ppd', ['alumnosPorCurso' => $alumnosPorCursoPpd])
            @endif
        @endif
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
