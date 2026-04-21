@extends('layouts.docente')

@section('titulo', 'Nueva Incidencia — Docente')

@push('styles')
<style>
    .inc-card { border: none; border-radius: .75rem; box-shadow: 0 2px 12px rgba(0,0,0,.08); }
    .inc-section-label {
        font-size: .72rem; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: #858796; margin-bottom: .35rem;
    }
    .inc-cascade-row { display: flex; gap: .75rem; flex-wrap: wrap; }
    .inc-cascade-row .form-group { flex: 1 1 180px; min-width: 160px; }
    .img-preview-wrap { position: relative; display: inline-block; }
    .img-preview-wrap img { max-height: 180px; border-radius: .5rem; border: 1px solid #dee2e6; }
    .img-preview-wrap .btn-remove-img {
        position: absolute; top: 4px; right: 4px;
        background: rgba(220,53,69,.85); color: #fff;
        border: none; border-radius: 50%; width: 22px; height: 22px;
        font-size: .75rem; line-height: 22px; text-align: center; cursor: pointer;
    }
</style>
@endpush

@section('contenido')
<div class="container-fluid docente-ui-page">
    @include('docentes.partials.ui-header', [
        'kicker'    => 'Incidencias',
        'title'     => 'Registrar nueva incidencia',
        'subtitle'  => 'Complete los datos para reportar una incidencia sobre un estudiante.',
        'backUrl'   => route('vistaDocente', $docente->id),
        'backLabel' => 'Mi panel',
    ])

    @if (Session::has('success'))
        <div class="alert alert-success alert-dismissible fade show shadow-sm mb-3" role="alert">
            {{ Session::get('success') }}
            <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
        </div>
    @endif

    <div class="row justify-content-center pb-5">
        <div class="col-12 col-lg-8">
            <div class="card inc-card">
                <div class="card-body p-4">
                    <form action="{{ route('docente.incidencias.store', $docente->id) }}"
                          method="POST" enctype="multipart/form-data" id="incForm">
                        @csrf

                        {{-- Docente (solo lectura) --}}
                        <div class="form-group">
                            <div class="inc-section-label">Docente</div>
                            <input type="text" class="form-control bg-light"
                                   value="{{ $docente->nombre }}" readonly>
                        </div>

                        {{-- Cascada Programa → Ciclo → Alumno --}}
                        <div class="inc-section-label mt-3">Selección de estudiante</div>
                        <div class="inc-cascade-row">
                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">Programa</label>
                                <select name="programa_id" id="selPrograma"
                                        class="form-control @error('programa_id') is-invalid @enderror">
                                    <option value="">— Programa —</option>
                                    @foreach ($programas as $p)
                                        <option value="{{ $p->id }}" {{ old('programa_id') == $p->id ? 'selected' : '' }}>
                                            {{ $p->nombre }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('programa_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">Ciclo</label>
                                <select name="ciclo_id" id="selCiclo"
                                        class="form-control @error('ciclo_id') is-invalid @enderror" disabled>
                                    <option value="">— Ciclo —</option>
                                </select>
                                @error('ciclo_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>

                            <div class="form-group">
                                <label class="small font-weight-bold mb-1">Alumno</label>
                                <select name="alumno_id" id="selAlumno"
                                        class="form-control @error('alumno_id') is-invalid @enderror" disabled>
                                    <option value="">— Alumno —</option>
                                </select>
                                @error('alumno_id')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                        </div>

                        {{-- Fecha --}}
                        <div class="form-group mt-2">
                            <label class="inc-section-label">Fecha</label>
                            <input type="date" name="fecha" id="fecha"
                                   class="form-control @error('fecha') is-invalid @enderror"
                                   value="{{ old('fecha', now()->format('Y-m-d')) }}">
                            @error('fecha')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Reporte --}}
                        <div class="form-group">
                            <label class="inc-section-label">Reporte de incidencia</label>
                            <textarea name="reporte" id="reporte" rows="5"
                                      class="form-control @error('reporte') is-invalid @enderror"
                                      placeholder="Describa detalladamente la incidencia observada…">{{ old('reporte') }}</textarea>
                            @error('reporte')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        {{-- Imagen opcional --}}
                        <div class="form-group">
                            <label class="inc-section-label">
                                Imagen adjunta <span class="text-muted font-weight-normal">(opcional, máx. 3 MB)</span>
                            </label>
                            <div class="custom-file">
                                <input type="file" name="imagen" id="incImagen" accept="image/*"
                                       class="custom-file-input @error('imagen') is-invalid @enderror">
                                <label class="custom-file-label" for="incImagen">Seleccionar imagen…</label>
                                @error('imagen')
                                    <div class="invalid-feedback">{{ $message }}</div>
                                @enderror
                            </div>
                            <div id="imgPreviewWrap" class="mt-2 d-none img-preview-wrap">
                                <img id="imgPreview" src="" alt="Vista previa">
                                <button type="button" class="btn-remove-img" id="btnRemoveImg"
                                        aria-label="Quitar imagen">&times;</button>
                            </div>
                        </div>

                        <div class="d-flex justify-content-end mt-3">
                            <a href="{{ route('vistaDocente', $docente->id) }}"
                               class="btn btn-outline-secondary mr-2">Cancelar</a>
                            <button type="submit" class="btn btn-primary px-4">
                                <i class="fas fa-paper-plane mr-1"></i> Registrar incidencia
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
(function () {
    var urlCiclos  = '{{ route("api.ciclos", ":p") }}';
    var urlAlumnos = '{{ route("api.alumnos", ":c") }}';

    var selPrograma = document.getElementById('selPrograma');
    var selCiclo    = document.getElementById('selCiclo');
    var selAlumno   = document.getElementById('selAlumno');

    function resetSelect(sel, placeholder) {
        sel.innerHTML = '<option value="">' + placeholder + '</option>';
        sel.disabled  = true;
    }

    selPrograma.addEventListener('change', function () {
        resetSelect(selCiclo, '— Ciclo —');
        resetSelect(selAlumno, '— Alumno —');
        if (!this.value) return;

        fetch(urlCiclos.replace(':p', this.value))
            .then(function (r) { return r.json(); })
            .then(function (ciclos) {
                ciclos.forEach(function (c) {
                    var opt = document.createElement('option');
                    opt.value = c.id; opt.textContent = c.nombre;
                    selCiclo.appendChild(opt);
                });
                selCiclo.disabled = ciclos.length === 0;
            });
    });

    selCiclo.addEventListener('change', function () {
        resetSelect(selAlumno, '— Alumno —');
        if (!this.value) return;

        fetch(urlAlumnos.replace(':c', this.value))
            .then(function (r) { return r.json(); })
            .then(function (alumnos) {
                alumnos.forEach(function (a) {
                    var opt = document.createElement('option');
                    opt.value = a.id; opt.textContent = a.nombre;
                    selAlumno.appendChild(opt);
                });
                selAlumno.disabled = alumnos.length === 0;
            });
    });

    // Imagen preview
    var inputImg   = document.getElementById('incImagen');
    var previewWrap = document.getElementById('imgPreviewWrap');
    var preview    = document.getElementById('imgPreview');
    var btnRemove  = document.getElementById('btnRemoveImg');
    var fileLabel  = document.querySelector('.custom-file-label');

    inputImg.addEventListener('change', function () {
        var file = this.files[0];
        if (!file) return;
        fileLabel.textContent = file.name;
        var reader = new FileReader();
        reader.onload = function (e) {
            preview.src = e.target.result;
            previewWrap.classList.remove('d-none');
        };
        reader.readAsDataURL(file);
    });

    btnRemove.addEventListener('click', function () {
        inputImg.value = '';
        fileLabel.textContent = 'Seleccionar imagen…';
        preview.src = '';
        previewWrap.classList.add('d-none');
    });
})();
</script>
@endpush
