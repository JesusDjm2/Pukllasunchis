{{-- Alta o edición de un acta (exposición de TI o sustentación de Tesis).
     Espera $egresado, $selectDocente; $acta (Acta|null). --}}
@php $acta = $acta ?? null; @endphp
<form method="POST" action="{{ $acta ? route('titulacion.actas.actualizar', $acta->id) : route('titulacion.actas.guardar', $egresado->id) }}">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.actas.actualizar'))>
    @csrf
    @if ($acta) @method('PUT') @endif
    <div class="form-row">
        <div class="form-group col-md-3">
            <label class="small font-weight-bold">Tipo</label>
            <select name="tipo" class="form-control form-control-sm" required>
                @php
                    // Alta: el tipo de acta cuyo paso anterior falta no se puede elegir (ReglasProceso::REQUISITO_ACTA).
                    $faltaActa = fn (string $t) => (! ($acta ?? null) && isset($avance))
                        ? \App\Services\Titulacion\ReglasProceso::falta($avance, \App\Services\Titulacion\ReglasProceso::REQUISITO_ACTA[$t]) : null;
                @endphp
                <option value="expo" @selected(($acta?->tipo ?? 'expo') === 'expo') @disabled($faltaActa('expo'))>Exposición (TI){{ $faltaActa('expo') ? ' — falta: '.$faltaActa('expo') : '' }}</option>
                <option value="tesis" @selected($acta?->tipo === 'tesis') @disabled($faltaActa('tesis'))>Sustentación (Tesis){{ $faltaActa('tesis') ? ' — falta: '.$faltaActa('tesis') : '' }}</option>
            </select>
        </div>
        <div class="form-group col-md-2">
            <label class="small font-weight-bold">N° Acta</label>
            <input type="text" name="numero_acta" class="form-control form-control-sm" value="{{ $acta?->numero_acta }}">
        </div>
        <div class="form-group col-md-2">
            <label class="small font-weight-bold">Fecha</label>
            <input type="date" name="fecha" class="form-control form-control-sm" value="{{ $acta?->fecha?->format('Y-m-d') }}">
        </div>
        <div class="form-group col-md-2">
            <label class="small font-weight-bold">Promedio</label>
            <input type="number" step="0.01" min="0" max="20" name="promedio" class="form-control form-control-sm" value="{{ $acta?->promedio }}">
        </div>
        <div class="form-group col-md-3">
            <label class="small font-weight-bold">Asesor/a</label>
            {!! $selectDocente('asesor_id', $acta?->asesorValor()) !!}
        </div>
    </div>
    <div class="form-group">
        <label class="small font-weight-bold">Título del trabajo</label>
        <input type="text" name="titulo_trabajo" class="form-control form-control-sm" value="{{ $acta?->titulo_trabajo }}">
    </div>
    <button type="submit" class="btn btn-sm btn-primary">{{ $acta ? 'Guardar cambios' : 'Agregar acta' }}</button>
</fieldset>
</form>
