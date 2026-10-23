<x-mail::message>
# Nueva solicitud de tutoría — {{ $tutoria->urgencia_texto }}

Un alumno quiere conversar individualmente a través del formulario de tutorías.

<x-mail::panel>
**Alumno:** {{ $tutoria->alumno ? $tutoria->alumno->apellidos.', '.$tutoria->alumno->nombres : ($tutoria->nombre_alumno ?? '—') }}<br>
@if ($tutoria->alumno)
**Contacto:** {{ $tutoria->alumno->numero ?? '—' }}<br>
@endif
**Ciclo:** {{ $tutoria->ciclo?->nombre ?? '—' }}<br>
**Programa:** {{ $tutoria->ciclo?->programa?->nombre ?? '—' }}<br>
**¿Te gustaría conversar individualmente con el tutor?** {{ \App\Models\Tutoria::OPCIONES[$tutoria->urgencia][0] ?? $tutoria->urgencia }}
@if ($tutoria->pideOtroDocente())
<br>**Docente con quien desea hablar:** {{ $tutoria->docente_solicitado_nombre ?? '—' }}
@endif
</x-mail::panel>

@if (filled($tutoria->motivo))
**Mensaje del alumno:**

{{ $tutoria->motivo }}
@else
*El alumno no dejó un mensaje adicional.*
@endif

<x-mail::button :url="url('/login')" color="primary">
Ver en el sistema
</x-mail::button>

Este mensaje fue generado automáticamente — EESP Pukllasunchis.
</x-mail::message>
