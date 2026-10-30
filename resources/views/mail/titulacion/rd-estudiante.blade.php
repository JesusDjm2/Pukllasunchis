<x-mail::message>
{{ $saludo }}:

{!! $mensaje !!}

@if ($adjunta)
<x-mail::panel>
**Documento adjunto:** {{ $nombrePdf }}

La Resolución Directoral está adjunta a este correo, en formato PDF. Le recomendamos descargarla y conservarla.
</x-mail::panel>
@elseif ($enlaceRd)
<x-mail::panel>
**Documento:** la Resolución Directoral está disponible en el siguiente enlace. Le recomendamos descargarla y conservarla.
</x-mail::panel>

<x-mail::button :url="$enlaceRd">Abrir la Resolución Directoral</x-mail::button>
@endif

Atentamente,

**{{ $firma }}**<br>
Escuela de Educación Superior Pedagógica Pukllasunchis
</x-mail::message>
