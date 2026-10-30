{{-- Campos de un asesor: alta y edición. $a (o null), $prefijo para los id, $conViejos: mostrar lo escrito tras un error. --}}
@php
    $valor = fn (string $campo) => $conViejos ? old($campo) : ($a?->{$campo});
@endphp
<div class="as-campos">
    <div class="as-campo">
        <label for="{{ $prefijo }}-nombres">Nombres <span class="as-req">*</span></label>
        <input type="text" id="{{ $prefijo }}-nombres" name="nombres" class="al-campo" maxlength="120" value="{{ $valor('nombres') }}" required>
    </div>
    <div class="as-campo">
        <label for="{{ $prefijo }}-apellidos">Apellidos <span class="as-req">*</span></label>
        <input type="text" id="{{ $prefijo }}-apellidos" name="apellidos" class="al-campo" maxlength="120" value="{{ $valor('apellidos') }}" required>
    </div>
    <div class="as-campo">
        <label for="{{ $prefijo }}-dni">DNI</label>
        <input type="text" id="{{ $prefijo }}-dni" name="dni" class="al-campo" maxlength="15" inputmode="numeric" value="{{ $valor('dni') }}">
    </div>
    <div class="as-campo">
        <label for="{{ $prefijo }}-telefono">Teléfono</label>
        <input type="tel" id="{{ $prefijo }}-telefono" name="telefono" class="al-campo" maxlength="30" value="{{ $valor('telefono') }}">
    </div>
    <div class="as-campo">
        <label for="{{ $prefijo }}-email">Correo</label>
        <input type="email" id="{{ $prefijo }}-email" name="email" class="al-campo" maxlength="150" value="{{ $valor('email') }}">
    </div>
    <div class="as-campo">
        <label for="{{ $prefijo }}-institucion">Institución</label>
        <input type="text" id="{{ $prefijo }}-institucion" name="institucion" class="al-campo" maxlength="150" placeholder="Si es externo" value="{{ $valor('institucion') }}">
    </div>
</div>
