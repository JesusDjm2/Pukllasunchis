{{-- Todos los datos de TI y Tesis en un solo formulario (titulacion.tesis.guardar), para corregir
     datos importados. Campos vacíos con el paso anterior pendiente quedan deshabilitados ($bloqCampo). --}}
<form method="POST" action="{{ route('titulacion.tesis.guardar', $egresado->id) }}">
    @csrf
    <div class="form-row">
        <div class="form-group col-md-8">
            <label class="small font-weight-bold">Título</label>
            <textarea name="titulo" class="form-control form-control-sm" rows="2">{{ $tesis?->titulo ?? '' }}</textarea>
        </div>
        <div class="form-group col-md-4">
            <label class="small font-weight-bold">Asesor/a</label>
            {!! $selectDocente('asesor_id', $tesis?->asesorValor()) !!}
            <label class="small font-weight-bold mt-2">Repositorio</label>
            <select name="estado" class="form-control form-control-sm">
                @foreach (['pendiente' => 'Pendiente', 'en_repositorio' => 'En repositorio', 'aprobado' => 'Aprobado'] as $op => $etiqueta)
                    <option value="{{ $op }}" @selected(($tesis?->estado ?? 'pendiente') === $op)>{{ $etiqueta }}</option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="titulacion-etapa">
        <div class="titulacion-etapa__titulo">② TI — Trabajo de Investigación (grado de Bachiller)</div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">Fecha exposición</label>
                <input type="date" name="fecha_exposicion" {!! $bloqCampo('fecha_exposicion') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_exposicion?->format('Y-m-d') }}">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">RD TI</label>
                <input type="text" name="rd_ti" {!! $bloqCampo('rd_ti') !!} class="form-control form-control-sm" value="{{ $tesis?->rd_ti ?? '' }}">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">Bachiller: inscripción SUNEDU</label>
                <input type="date" name="fecha_bachiller_sunedu" {!! $bloqCampo('fecha_bachiller_sunedu') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_bachiller_sunedu?->format('Y-m-d') }}">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">Bachiller: N° registro SUNEDU</label>
                <input type="text" name="bachiller_sunedu" {!! $bloqCampo('bachiller_sunedu') !!} class="form-control form-control-sm" value="{{ $tesis?->bachiller_sunedu ?? '' }}">
            </div>
        </div>
    </div>

    <div class="titulacion-etapa">
        <div class="titulacion-etapa__titulo">③ Tesis — Título de Licenciado</div>
        <div class="form-row">
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">Fecha sustentación</label>
                <input type="date" name="fecha_sustentacion" {!! $bloqCampo('fecha_sustentacion') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_sustentacion?->format('Y-m-d') }}">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">RD Extensión</label>
                <input type="text" name="rd_extension" {!! $bloqCampo('rd_extension') !!} class="form-control form-control-sm" value="{{ $tesis?->rd_extension ?? '' }}">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">Extensión hasta</label>
                <input type="date" name="fecha_extension" {!! $bloqCampo('fecha_extension') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_extension?->format('Y-m-d') }}">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">RD Tesis</label>
                <input type="text" name="rd_tesis" {!! $bloqCampo('rd_tesis') !!} class="form-control form-control-sm" value="{{ $tesis?->rd_tesis ?? '' }}">
            </div>
        </div>
        <div class="form-row">
            <div class="form-group col-md-6">
                <label class="small font-weight-bold">URL Repositorio</label>
                <input type="text" name="url_repositorio" {!! $bloqCampo('url_repositorio') !!} class="form-control form-control-sm" value="{{ $tesis?->url_repositorio ?? '' }}">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">Licenciatura: inscripción SUNEDU</label>
                <input type="date" name="fecha_titulo_sunedu" {!! $bloqCampo('fecha_titulo_sunedu') !!} class="form-control form-control-sm" value="{{ $tesis?->fecha_titulo_sunedu?->format('Y-m-d') }}">
            </div>
            <div class="form-group col-md-3">
                <label class="small font-weight-bold">Licenciatura: N° registro SUNEDU</label>
                <input type="text" name="titulo_sunedu" {!! $bloqCampo('titulo_sunedu') !!} class="form-control form-control-sm" value="{{ $tesis?->titulo_sunedu ?? '' }}">
            </div>
        </div>
    </div>

    <button type="submit" class="btn btn-sm btn-primary">Guardar TI/Tesis</button>
</form>
@include('titulacion._agregar_asesor', ['tab' => 'tesis'])
