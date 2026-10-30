{{-- Alta o edición de una RD. Espera $egresado; $res (Resolucion|null). Opcionales (alta desde el
     casillero de una etapa): $tipoFijo (no se elige), $numeroInicial, $fechaInicial. --}}
@php
    $res = $res ?? null;
    // Alta: un tipo de RD cuyo paso anterior falta no se puede elegir (ReglasProceso::REQUISITO_RD).
    $faltaTipo = fn (string $tipo) => (! $res && isset($avance))
        ? \App\Services\Titulacion\ReglasProceso::falta($avance, \App\Services\Titulacion\ReglasProceso::REQUISITO_RD[$tipo] ?? null)
        : null;
    $tipoFijo = $tipoFijo ?? null;
    $tipoInicial = $res?->tipo ?? $tipoFijo ?? collect(array_keys(\App\Models\Titulacion\Resolucion::TIPOS))->first(fn ($t) => ! $faltaTipo($t));
@endphp
<form method="POST" action="{{ $res ? route('titulacion.resoluciones.actualizar', $res->id) : route('titulacion.resoluciones.guardar', $egresado->id) }}" enctype="multipart/form-data">
<fieldset class="ft-area" @disabled(! \App\Services\Titulacion\AreasTitulacion::puedeRuta('titulacion.resoluciones.actualizar'))>
    @csrf
    @if ($res) @method('PUT') @endif
    <div class="form-row">
        <div class="form-group col-md-6 mb-2">
            <label class="small font-weight-bold mb-0">Tipo</label>
            @if ($tipoFijo)
                <input type="hidden" name="tipo" value="{{ $tipoFijo }}">
                <div class="form-control form-control-sm" style="background:transparent;">{{ \App\Models\Titulacion\Resolucion::TIPOS[$tipoFijo] ?? $tipoFijo }}</div>
            @else
            <select name="tipo" class="form-control form-control-sm" required>
                @foreach (\App\Models\Titulacion\Resolucion::TIPOS as $clave => $nombre)
                    @php $faltaEste = $faltaTipo($clave); @endphp
                    <option value="{{ $clave }}" @selected($tipoInicial === $clave) @disabled($faltaEste)>{{ $nombre }}{{ $faltaEste ? ' — falta: '.$faltaEste : '' }}</option>
                @endforeach
            </select>
            @endif
        </div>
        <div class="form-group col-md-3 mb-2">
            <label class="small font-weight-bold mb-0">N° RD *</label>
            <input type="text" name="numero_rd" class="form-control form-control-sm" value="{{ $res?->numero_rd ?? ($numeroInicial ?? '') }}" required>
        </div>
        <div class="form-group col-md-3 mb-2">
            <label class="small font-weight-bold mb-0">Fecha</label>
            <input type="date" name="fecha" class="form-control form-control-sm" value="{{ $res?->fecha?->format('Y-m-d') ?? (isset($fechaInicial) ? $fechaInicial?->format('Y-m-d') : '') }}">
        </div>
        <div class="form-group col-md-6 mb-2">
            <label class="small font-weight-bold mb-0">PDF de la RD</label>
            <input type="file" name="pdf" accept="application/pdf" class="form-control-file form-control-sm">
            <input type="url" name="archivo" class="form-control form-control-sm mt-1" placeholder="…o pegue un enlace (https://drive.google.com/…)" value="{{ $res?->archivo }}">
        </div>
        <div class="form-group col-md-6 mb-2">
            <label class="small font-weight-bold mb-0">Detalle / motivo</label>
            <input type="text" name="detalle" class="form-control form-control-sm" maxlength="500" placeholder="Ej.: cambia de asesor a … / nueva fecha de sustentación …" value="{{ $res?->detalle }}">
        </div>
    </div>
    <button type="submit" class="btn btn-sm {{ $res ? 'btn-primary' : 'btn-outline-primary' }}">{{ $res ? 'Guardar cambios' : 'Agregar resolución' }}</button>
</fieldset>
</form>
