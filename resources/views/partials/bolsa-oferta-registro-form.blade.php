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
    <style>
        .bolsa-oferta-registro-form .form-group {
            margin-bottom: 1.35rem;
        }

        .bolsa-oferta-registro-form label {
            font-weight: 600;
            font-size: 13px;
            color: #444;
            margin-bottom: 0.4rem;
        }

        .bolsa-oferta-registro-form .bolsa-form-separador {
            border: none;
            border-top: 1px solid #ececec;
            margin: 1.6rem 0 1.6rem;
        }

        .bolsa-oferta-registro-form .bolsa-form-envio {
            margin-top: 2rem;
            text-align: center;
        }
    </style>
    <div class="bolsa-oferta-registro-form">
        <div class="form-group">
            <label for="{{ $prefix }}_nombre">Se requiere:</label>
            <input type="text" class="form-control form-control-sm" id="{{ $prefix }}_nombre" name="nombre"
                value="{{ old('nombre') }}" required maxlength="255">
        </div>
        <div class="form-group">
            <label for="{{ $prefix }}_detalles">Detalles del anuncio:</label>
            <textarea class="form-control" id="{{ $prefix }}_detalles" name="detalles" rows="5">{{ old('detalles') }}</textarea>
        </div>
        <div class="form-group">
            <label for="{{ $prefix }}_numero_correo">N° de contacto/correo:</label>
            <input type="text" class="form-control form-control-sm" id="{{ $prefix }}_numero_correo"
                name="numero_correo" value="{{ old('numero_correo') }}" maxlength="50">
        </div>
        <div class="form-group">
            <label for="{{ $prefix }}_imagen">Imagen <small>(opcional)</small>:</label>
            <input type="file" class="form-control form-control-sm" id="{{ $prefix }}_imagen" name="imagen"
                accept=".jpg,.jpeg,.png,.webp">
        </div>
        

        <div class="row">
            <div class="form-group col-6">
                <label for="{{ $prefix }}_nombre_publicador">Nombre de quién publica:</label>
                <input type="text" class="form-control form-control-sm" id="{{ $prefix }}_nombre_publicador"
                    name="nombre_publicador" value="{{ old('nombre_publicador') }}" required maxlength="255">
            </div>
            <div class="form-group col-6">
                <label for="{{ $prefix }}_telefono_publicador">Teléfono de quíen publica <small>(opcional)</small>:</label>
                <input type="text" class="form-control form-control-sm" id="{{ $prefix }}_telefono_publicador"
                    name="telefono_publicador" value="{{ old('telefono_publicador') }}" maxlength="50">
            </div>
        </div>
        <div class="form-group">
            <label for="{{ $prefix }}_relacion_publicador">Relación con la institución <small>(opcional)</small>:</label>
            <select class="form-control form-control-sm" id="{{ $prefix }}_relacion_publicador" name="relacion_publicador">
                <option value="">Seleccionar...</option>
                @foreach (['Soy de la Asociación Pukllasunchis', 'Alumno de la EESPPP', 'Externo a la Asociación'] as $opcion)
                    <option value="{{ $opcion }}" {{ old('relacion_publicador') === $opcion ? 'selected' : '' }}>
                        {{ $opcion }}</option>
                @endforeach
            </select>
        </div>

        <div class="bolsa-form-envio">
            <button type="submit" class="boxed-btn3">Enviar registro</button>
        </div>
    </div>
</form>
