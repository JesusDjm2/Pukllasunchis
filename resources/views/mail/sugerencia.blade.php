<x-mail::message>
# Nueva sugerencia recibida

Un alumno ha enviado una sugerencia a través del buzón de sugerencias de tutoría.

<x-mail::panel>
**Alumno:** {{ $sugerencia->alumno ? $sugerencia->alumno->apellidos.', '.$sugerencia->alumno->nombres : ($sugerencia->nombre_alumno ?? '—') }}<br>
@if ($sugerencia->alumno)
**Contacto:** {{ $sugerencia->alumno->numero ?? '—' }}<br>
@endif
**Ciclo:** {{ $sugerencia->ciclo?->nombre ?? '—' }}<br>
**Programa:** {{ $sugerencia->ciclo?->programa?->nombre ?? '—' }}
</x-mail::panel>

**Sugerencia:**

{{ $sugerencia->mensaje }}

<x-mail::button :url="url('/login')" color="primary">
Ver en el sistema
</x-mail::button>

Este mensaje fue generado automáticamente — EESP Pukllasunchis.
</x-mail::message>
