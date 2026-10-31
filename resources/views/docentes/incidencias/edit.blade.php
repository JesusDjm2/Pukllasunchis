@extends('layouts.docente')

@section('titulo', 'Editar Incidencia N° ' . $incidencia->id)

@push('styles')
    @include('docentes.incidencias.styles')
@endpush

@section('contenido')
<div class="container-fluid docente-ui-page">
    @include('docentes.partials.ui-header', [
        'kicker'    => 'Incidencias',
        'title'     => 'Editar incidencia N° ' . $incidencia->id,
        'subtitle'  => 'Puedes corregir la fecha, el reporte y la imagen. El estudiante y el ciclo no se modifican desde aquí.',
        'backUrl'   => route('docente.incidencias.show', [$incidencia->docente_id, $incidencia->id]),
        'backLabel' => 'Ver ficha',
    ])

    <div class="card inc-card">
        <div class="card-body py-4 px-4">
            @if (session('success'))
                <div class="alert alert-success alert-sm">{{ session('success') }}</div>
            @endif

            <form action="{{ route('docente.incidencias.update', [$incidencia->docente_id, $incidencia->id]) }}"
                  method="POST" enctype="multipart/form-data" id="incEditForm">
                @csrf
                @method('PUT')

                <div class="row mb-3">
                    <div class="col-sm-6">
                        <div class="inc-dato">
                            <span class="inc-dato-label">Estudiante</span>
                            <span class="inc-dato-valor">
                                {{ $incidencia->alumno ? $incidencia->alumno->apellidos . ', ' . $incidencia->alumno->nombres : '—' }}
                            </span>
                        </div>
                    </div>
                    <div class="col-sm-6">
                        <div class="inc-dato">
                            <span class="inc-dato-label">Ciclo / programa</span>
                            <span class="inc-dato-valor">
                                {{ optional($incidencia->ciclo)->nombre ?? '—' }}
                                @if ($incidencia->ciclo?->programa)
                                    · {{ $incidencia->ciclo->programa->nombre }}
                                @endif
                            </span>
                        </div>
                    </div>
                </div>

                {{-- Fecha --}}
                <div class="form-group mt-2">
                    <label class="inc-section-label">Fecha</label>
                    <input type="date" name="fecha" id="fecha"
                           class="form-control @error('fecha') is-invalid @enderror"
                           value="{{ old('fecha', optional($incidencia->fecha)->format('Y-m-d')) }}">
                    @error('fecha')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Reporte --}}
                <div class="form-group">
                    <label class="inc-section-label">Reporte de incidencia</label>
                    <textarea name="reporte" id="reporte" rows="5"
                              class="form-control @error('reporte') is-invalid @enderror">{{ old('reporte', $incidencia->reporte) }}</textarea>
                    @error('reporte')
                        <div class="invalid-feedback">{{ $message }}</div>
                    @enderror
                </div>

                {{-- Imagen --}}
                <div class="form-group">
                    <label class="inc-section-label">
                        Imagen adjunta <span class="text-muted font-weight-normal">(opcional, máx. 3 MB)</span>
                    </label>

                    @if ($incidencia->imagen)
                        <div class="mb-2 d-flex align-items-center" style="gap:.6rem;">
                            <img src="{{ asset('img/incidencias/' . $incidencia->imagen) }}"
                                 alt="Imagen actual" class="inc-img-thumb">
                            <span class="inc-meta">Imagen actual. Sube otra para reemplazarla.</span>
                        </div>
                    @endif

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
                    <a href="{{ route('docente.incidencias.show', [$incidencia->docente_id, $incidencia->id]) }}"
                       class="btn btn-outline-secondary mr-2">Cancelar</a>
                    <button type="submit" class="btn btn-primary px-4">
                        <i class="fas fa-save mr-1"></i> Guardar cambios
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endsection

@push('scripts')
<script>
    const input = document.getElementById('incImagen');
    const wrap  = document.getElementById('imgPreviewWrap');
    const prev  = document.getElementById('imgPreview');

    input.addEventListener('change', function () {
        const file = this.files[0];
        if (!file) return;
        prev.src = URL.createObjectURL(file);
        wrap.classList.remove('d-none');
        document.querySelector('.custom-file-label').textContent = file.name;
    });

    document.getElementById('btnRemoveImg').addEventListener('click', function () {
        input.value = '';
        prev.src = '';
        wrap.classList.add('d-none');
        document.querySelector('.custom-file-label').textContent = 'Seleccionar imagen…';
    });
</script>
@endpush
