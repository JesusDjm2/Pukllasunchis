@php
    $grupoSeleccionado = $grupoFormacion ?? request('grupo_formacion', 'TODOS');
@endphp
<form method="GET" class="puklla-grupo-formacion" style="padding:12px 18px;margin:12px;background:#f1f5fc;border:1px solid #c8d5ec;border-radius:10px;color:#25344b">
    @foreach(request()->except('grupo_formacion','page') as $clave => $valor)
        @if(is_scalar($valor))<input type="hidden" name="{{ $clave }}" value="{{ $valor }}">@endif
    @endforeach
    <label for="grupo-formacion-global"><strong>Grupo de egresados</strong></label>
    <select id="grupo-formacion-global" name="grupo_formacion" onchange="this.form.submit()" style="padding:8px;margin:4px">
        @foreach(App\Services\SeguimientoEgresado\GrupoFormacionService::OPCIONES as $codigo => $etiqueta)
        <option value="{{ $codigo }}" @selected($grupoSeleccionado === $codigo)>{{ $etiqueta }}</option>
        @endforeach
    </select>
    <noscript><button type="submit">Aplicar grupo</button></noscript>
    <small style="display:block">FID/PPD es una clasificación académica; no modifica permisos. Una persona con ambos egresos se cuenta una sola vez en el conjunto.</small>
</form>
