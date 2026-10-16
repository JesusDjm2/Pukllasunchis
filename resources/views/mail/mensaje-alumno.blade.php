<x-mail::message>
# {{ $asunto }}

{{-- El texto lo escribe el docente: se escapa y va en una sola línea HTML para que el
     parser de Markdown no lo reinterprete (sangrías como bloques de código, asteriscos…). --}}
<div style="font-size:15px;line-height:1.6;color:#3d4852;">{!! str_replace(["\r\n", "\n", "\r"], '', nl2br(e($mensaje))) !!}</div>

<x-mail::panel>
**{{ $remitenteNombre }}**<br>
{{ $contexto }}
@if ($remitenteCorreo)
<br>Para responder, contesta este correo o escribe a {{ $remitenteCorreo }}.
@endif
</x-mail::panel>

Mensaje enviado desde el sistema académico de la EESP Pukllasunchis.
</x-mail::message>
