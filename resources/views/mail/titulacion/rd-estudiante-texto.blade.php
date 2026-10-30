{{ $saludo }}:

{!! $mensajeTexto !!}

@if ($adjunta)
DOCUMENTO ADJUNTO: {{ $nombrePdf }}
La Resolución Directoral está adjunta a este correo, en formato PDF. Le recomendamos descargarla y conservarla.
@elseif ($enlaceRd)
DOCUMENTO: la Resolución Directoral está disponible en este enlace. Le recomendamos descargarla y conservarla.
{{ $enlaceRd }}
@endif

Atentamente,

{{ $firma }}
Escuela de Educación Superior Pedagógica Pukllasunchis
