{{--
    Formulario público de registro de oferta (bolsa de trabajo).
    Variables: $prefix (string, ids únicos), $redirectTo ('bolsa'|'index')
--}}
@php
    $prefix = $prefix ?? 'bolsa_form';
    $redirectTo = $redirectTo ?? 'bolsa';
    $listadoAnio = $listadoAnio ?? null;
    $listadoMes = $listadoMes ?? null;
@endphp
<form action="{{ route('bolsa-trabajo.ofertas.store') }}" method="POST" enctype="multipart/form-data"
    id="{{ $prefix }}_form">
    @csrf
    <input type="hidden" name="form_context" value="bolsa_oferta">
    <input type="hidden" name="redirect_to" value="{{ $redirectTo }}">
    @if ($listadoAnio !== null && $listadoAnio !== '')
        <input type="hidden" name="bolsa_listado_anio" value="{{ $listadoAnio }}">
    @endif
    @if ($listadoMes !== null && $listadoMes !== '')
        <input type="hidden" name="bolsa_listado_mes" value="{{ $listadoMes }}">
    @endif
    <div class="form-group">
        <label for="{{ $prefix }}_nombre">Nombre</label>
        <input type="text" class="form-control" id="{{ $prefix }}_nombre" name="nombre"
            value="{{ old('nombre') }}" required maxlength="255">
    </div>
    <div class="form-group">
        <label for="{{ $prefix }}_detalles">Detalles</label>
        {{-- Sin required: con TinyMCE el textarea queda oculto y el navegador puede bloquear el submit sin aviso. --}}
        <textarea class="form-control" id="{{ $prefix }}_detalles" name="detalles" rows="8">{{ old('detalles') }}</textarea>
    </div>
    <div class="form-group">
        <label for="{{ $prefix }}_imagen">Imagen (opcional)</label>
        <input type="file" class="form-control-file" id="{{ $prefix }}_imagen" name="imagen"
            accept=".jpg,.jpeg,.png,.webp">
    </div>
    <div class="form-row">
        <div class="form-group col-md-6">
            <label for="{{ $prefix }}_inicio">Fecha inicio</label>
            <input type="date" class="form-control" id="{{ $prefix }}_inicio" name="fecha_inicio"
                value="{{ old('fecha_inicio') }}" required>
        </div>
        <div class="form-group col-md-6">
            <label for="{{ $prefix }}_fin">Fecha fin</label>
            <input type="date" class="form-control" id="{{ $prefix }}_fin" name="fecha_fin"
                value="{{ old('fecha_fin') }}" required>
        </div>
    </div>
    <button type="submit" class="boxed-btn3">Enviar registro</button>
</form>
