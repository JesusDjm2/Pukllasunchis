{{-- Clasificación del programa (modalidad / nivel / EIB). El sistema usa estos
     campos —no el nombre— para distinguir FID de PPD. $programa es opcional. --}}
@php $programa = $programa ?? null; @endphp
<div class="row">
    <div class="col-md-5 mb-4">
        <label class="pg-label" for="modalidad">Modalidad</label>
        <select id="modalidad" name="modalidad" class="pg-input @error('modalidad') is-invalid @enderror" required>
            <option value="">— Seleccionar —</option>
            @foreach (\App\Enums\Modalidad::cases() as $mod)
                <option value="{{ $mod->value }}" {{ old('modalidad', $programa?->modalidad?->value) === $mod->value ? 'selected' : '' }}>
                    {{ $mod->value }} — {{ $mod->etiqueta() }}
                </option>
            @endforeach
        </select>
        @error('modalidad')
            <div class="invalid-feedback d-block mt-1" style="font-size:.78rem;">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-4 mb-4">
        <label class="pg-label" for="nivel">Nivel</label>
        <select id="nivel" name="nivel" class="pg-input @error('nivel') is-invalid @enderror">
            <option value="">— Ninguno —</option>
            @foreach (\App\Enums\NivelPrograma::cases() as $niv)
                <option value="{{ $niv->value }}" {{ old('nivel', $programa?->nivel?->value) === $niv->value ? 'selected' : '' }}>
                    {{ $niv->etiqueta() }}
                </option>
            @endforeach
        </select>
        @error('nivel')
            <div class="invalid-feedback d-block mt-1" style="font-size:.78rem;">{{ $message }}</div>
        @enderror
    </div>
    <div class="col-md-3 mb-4 d-flex align-items-end">
        <div class="custom-control custom-checkbox mb-2">
            <input type="hidden" name="eib" value="0">
            <input type="checkbox" class="custom-control-input" id="eib" name="eib" value="1"
                   {{ old('eib', $programa?->eib) ? 'checked' : '' }}>
            <label class="custom-control-label pg-label mb-0" for="eib">Intercultural Bilingüe (EIB)</label>
        </div>
    </div>
</div>
