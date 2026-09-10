<x-mail::message>
# Nuevo {{ strtolower($reclamo->tipo_reclamacion) }} recibido

Se registró un nuevo {{ strtolower($reclamo->tipo_reclamacion) }} en el Libro de Reclamaciones. Detalle completo a continuación.

<x-mail::panel>
**N° de reclamo:** {{ $reclamo->numero_reclamo }}<br>
**Fecha de registro:** {{ $reclamo->created_at->format('d/m/Y H:i') }}
</x-mail::panel>

## 1. Datos del reclamante

- **Nombre completo:** {{ $reclamo->nombre }}
- **DNI:** {{ $reclamo->dni }}
- **Domicilio:** {{ $reclamo->domicilio }}
- **Teléfono:** {{ $reclamo->telefono }}
- **Correo:** {{ $reclamo->correo }}
- **Condición del reclamante:** {{ $reclamo->condicion_reclamante }}

@if ($reclamo->programa || $reclamo->ciclo || $reclamo->codigo_estudiante)
## 2. Datos académicos

- **Programa:** {{ $reclamo->programa ?? '—' }}
- **Ciclo:** {{ $reclamo->ciclo ?? '—' }}
- **Código de estudiante:** {{ $reclamo->codigo_estudiante ?? '—' }}

@endif
## {{ $reclamo->programa || $reclamo->ciclo || $reclamo->codigo_estudiante ? '3' : '2' }}. Identificación del servicio

- **Tipo de servicio:** {{ $reclamo->tipo_servicio }}
- **Descripción del servicio:** {{ $reclamo->descripcion_servicio ?? '—' }}
- **Área involucrada:** {{ $reclamo->area_involucrada }}
- **Servicio contratado:** {{ $reclamo->servicio_contratado ?? '—' }}

## {{ $reclamo->programa || $reclamo->ciclo || $reclamo->codigo_estudiante ? '4' : '3' }}. Detalle de la reclamación

- **Tipo de reclamación:** {{ $reclamo->tipo_reclamacion }}
- **Descripción de los hechos:** {{ $reclamo->descripcion_hechos }}
- **Pedido:** {{ $reclamo->pedido }}
- **Adjunto:** {{ $reclamo->adjunto ? 'Sí, adjunto a este correo como archivo aparte' : 'Ninguno' }}

## {{ $reclamo->programa || $reclamo->ciclo || $reclamo->codigo_estudiante ? '5' : '4' }}. Consentimientos

- Declaró que la información proporcionada es verdadera: {{ $reclamo->declara_informacion_verdadera ? 'Sí' : 'No' }}
- Autorizó el tratamiento de sus datos personales: {{ $reclamo->autoriza_tratamiento_datos ? 'Sí' : 'No' }}
- Confirmó haber leído el Libro de Reclamaciones: {{ $reclamo->confirma_lectura_libro ? 'Sí' : 'No' }}

Adjuntamos la constancia completa en PDF.

Este mensaje fue generado automáticamente — {{ config('libro_reclamaciones.razon_social') }}.
</x-mail::message>
