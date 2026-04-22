<x-mail::message>
# Nueva incidencia registrada

Se ha registrado una nueva incidencia en el sistema EESP Pukllasunchis.

<x-mail::panel>
**Alumno:** {{ $incidencia->alumno?->apellidos }}, {{ $incidencia->alumno?->nombres }}
**Ciclo:** {{ $incidencia->ciclo?->nombre ?? '—' }}
**Programa:** {{ $incidencia->ciclo?->programa?->nombre ?? '—' }}
**Fecha:** {{ $incidencia->fecha?->format('d/m/Y') }}
**Reportado por:** {{ $incidencia->docente ? ($incidencia->docente->user->apellidos.', '.$incidencia->docente->user->name) : ($incidencia->nombre_docente ?? '—') }}
</x-mail::panel>

**Reporte:**

{{ $incidencia->reporte }}

@if ($incidencia->imagen)
*(La incidencia incluye una imagen adjunta. Ingresa al sistema para verla.)*
@endif

<x-mail::button :url="url('/login')" color="primary">
Ver en el sistema
</x-mail::button>

Este mensaje fue generado automáticamente — EESP Pukllasunchis.
</x-mail::message>
