{{-- Casilla "aplicar también al grupo" de los pasos del TI/Tesis. Solo aparece si la persona tiene
     compañeros de grupo ($integrantes); marcada por defecto, porque el grupo avanza junto. --}}
@if (($integrantes ?? collect())->isNotEmpty())
    <div class="custom-control custom-checkbox mb-2">
        <input type="checkbox" class="custom-control-input" id="{{ $idCasilla }}" name="aplicar_grupo" value="1" checked>
        <label class="custom-control-label small" for="{{ $idCasilla }}">
            Aplicar también al grupo
            <span class="text-muted">({{ $integrantes->map(fn ($i) => $i->apellidos)->implode(', ') }})</span>
        </label>
    </div>
@endif
