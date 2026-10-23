<x-mail::message>
# Nueva solicitud de informes

Una persona ha enviado una consulta desde el formulario de la página de inicio.

<x-mail::panel>
**Nombre:** {{ $datos['nombre'] }}<br>
**Correo:** {{ $datos['email'] }}<br>
**WhatsApp:** {{ $datos['telefono'] }}<br>
**Programa de interés:** {{ $datos['programa_texto'] }}
</x-mail::panel>

**Consulta:**

{{ filled($datos['mensaje'] ?? null) ? $datos['mensaje'] : 'Sin mensaje adicional.' }}

<x-mail::button :url="$whatsapp" color="success">
Responder por WhatsApp
</x-mail::button>

Puede responder este correo directamente: la respuesta llegará a {{ $datos['email'] }}.

Este mensaje fue generado automáticamente — EESP Pukllasunchis.
</x-mail::message>
