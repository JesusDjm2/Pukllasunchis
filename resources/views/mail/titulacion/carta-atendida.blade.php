<x-mail::message>
@if ($carta->estado === 'emitida')
{{-- Texto de Secretaría tal cual (ya trae su saludo «Estimados/as:»). --}}
{!! nl2br(e($carta->observacion)) !!}

**Adjuntos:** Carta de Presentación (Word) y modelo de Carta de Aceptación (Word). También puede descargarlos desde su intranet.
@else
{{ $saludo }}:

Revisamos su solicitud de carta de aceptación para **{{ $carta->institucion }}** y necesitamos que corrija lo siguiente:

> {{ $carta->observacion }}

Puede volver a enviarla desde su intranet; sus datos ya estarán escritos.
@endif

<x-mail::button :url="$enlace">
{{ $carta->estado === 'emitida' ? 'Ver en mi intranet' : 'Corregir y enviar de nuevo' }}
</x-mail::button>

Atentamente,

**Secretaría**<br>
EESP Pukllasunchis
</x-mail::message>
