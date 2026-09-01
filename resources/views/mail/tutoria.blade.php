<x-mail::message>
# Nueva solicitud de tutoría — {{ $tutoria->urgencia }}

Un alumno ha solicitado una atención personalizada a través del formulario de tutorías individuales.

<x-mail::panel>
**Alumno:** {{ $tutoria->alumno ? $tutoria->alumno->apellidos.', '.$tutoria->alumno->nombres : ($tutoria->nombre_alumno ?? '—') }}<br>
@if ($tutoria->alumno)
**Contacto:** {{ $tutoria->alumno->numero ?? '—' }}<br>
@endif
**Ciclo:** {{ $tutoria->ciclo?->nombre ?? '—' }}<br>
**Programa:** {{ $tutoria->ciclo?->programa?->nombre ?? '—' }}<br>
**Nivel de prioridad:** {{ $tutoria->urgencia }}
</x-mail::panel>

**Motivo de la solicitud:**

{{ $tutoria->motivo }}

<x-mail::button :url="url('/login')" color="primary">
Ver en el sistema
</x-mail::button>

Este mensaje fue generado automáticamente — EESP Pukllasunchis.
</x-mail::message>
