<x-mail::message>
# Constancia de {{ $reclamo->tipo_reclamacion }}

Hemos recibido tu {{ strtolower($reclamo->tipo_reclamacion) }} en el Libro de Reclamaciones de {{ config('libro_reclamaciones.razon_social') }}.

<x-mail::panel>
**N° de reclamo:** {{ $reclamo->numero_reclamo }}<br>
**Fecha:** {{ $reclamo->created_at->format('d/m/Y H:i') }}<br>
**Reclamante:** {{ $reclamo->nombre }}<br>
**Condición:** {{ $reclamo->condicion_reclamante }}<br>
**Tipo:** {{ $reclamo->tipo_reclamacion }}<br>
**Tipo de servicio:** {{ $reclamo->tipo_servicio }}<br>
**Área involucrada:** {{ $reclamo->area_involucrada }}
@if ($reclamo->programa)<br>
**Programa:** {{ $reclamo->programa }}
@endif
</x-mail::panel>

**Tu pedido:**

{{ $reclamo->pedido }}

Adjuntamos la constancia en PDF con el detalle completo de tu solicitud. Nuestro equipo la revisará y se pondrá en contacto contigo a través del correo o teléfono registrado.

Este mensaje fue generado automáticamente — {{ config('libro_reclamaciones.razon_social') }}.
</x-mail::message>
