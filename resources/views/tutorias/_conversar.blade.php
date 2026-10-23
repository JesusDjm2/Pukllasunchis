{{--
    Campos "¿Te gustaría conversar individualmente con el tutor…?" + docente (si elige
    "No") + motivo opcional. Se usa en el formulario del QR y en la intranet del alumno,
    con el mismo estilo de campos de cada formulario.
    Parámetros: $docentesElegibles (Tutoria::docentesElegibles()), $idForm (prefijo de ids),
    $labelClass (clase de los <label>), $filasMotivo (opcional).
--}}
@php
    $idForm = $idForm ?? 'tut';
    $labelClass = $labelClass ?? 'font-weight-bold small';
    $elegida = old('urgencia');
    $docenteElegido = (int) old('docente_id');
    $esOtro = $elegida === \App\Models\Tutoria::OTRO_DOCENTE;
@endphp

<div data-tut-conversar>
    <div class="form-group">
        <label for="{{ $idForm }}-opcion" class="{{ $labelClass }}">
            ¿Te gustaría conversar individualmente con el tutor sobre alguna situación? <span class="text-danger">*</span>
        </label>
        <select name="urgencia" id="{{ $idForm }}-opcion" required
            class="form-control @error('urgencia') is-invalid @enderror">
            <option value="">— Elige una opción —</option>
            @foreach (\App\Models\Tutoria::OPCIONES as $clave => [$texto])
                <option value="{{ $clave }}" @selected($elegida === $clave)>{{ $texto }}</option>
            @endforeach
        </select>
        @error('urgencia')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group" data-tut-docentes @unless ($esOtro) hidden @endunless>
        <label for="{{ $idForm }}-docente" class="{{ $labelClass }}">
            ¿Con qué docente te gustaría conversar? <span class="text-danger">*</span>
        </label>
        <select name="docente_id" id="{{ $idForm }}-docente" class="form-control @error('docente_id') is-invalid @enderror"
            @if ($esOtro) required @endif>
            <option value="">— Elige un docente —</option>
            @foreach ($docentesElegibles as $grupo => $docentes)
                <optgroup label="{{ $grupo }}">
                    @foreach ($docentes as $d)
                        <option value="{{ $d['id'] }}" @selected($docenteElegido === $d['id'])>
                            {{ $d['nombre'] }}{{ $d['detalle'] ? ' — '.$d['detalle'] : '' }}
                        </option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
        @error('docente_id')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>

    <div class="form-group">
        <label for="{{ $idForm }}-motivo" class="{{ $labelClass }}">
            Motivo <span class="text-muted font-weight-normal">(opcional)</span>
        </label>
        <textarea name="motivo" id="{{ $idForm }}-motivo" rows="{{ $filasMotivo ?? 3 }}" maxlength="3000"
            class="form-control @error('motivo') is-invalid @enderror"
            placeholder="Si quieres, cuéntanos brevemente de qué te gustaría conversar…">{{ old('motivo') }}</textarea>
        @error('motivo')
            <div class="invalid-feedback">{{ $message }}</div>
        @enderror
    </div>
</div>

@once
    <script>
        // La lista de docentes solo aparece con "No, deseo hablar con otro docente".
        document.addEventListener('DOMContentLoaded', function () {
            document.querySelectorAll('[data-tut-conversar]').forEach(function (bloque) {
                var opcion = bloque.querySelector('select[name="urgencia"]');
                var caja = bloque.querySelector('[data-tut-docentes]');
                var docente = caja.querySelector('select');
                opcion.addEventListener('change', function () {
                    var esOtro = opcion.value === @json(\App\Models\Tutoria::OTRO_DOCENTE);
                    caja.hidden = !esOtro;
                    docente.required = esOtro;
                    if (!esOtro) docente.value = '';
                });
            });
        });
    </script>
@endonce
