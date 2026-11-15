<x-mail::message>
{{ $saludo }}:

{{ $mensaje }}

**Datos de su solicitud**

- **Institución Educativa:** {{ $carta->institucion }}
- **Director(a):** {{ $carta->director }}
- **Título del trabajo:** “{{ $carta->titulo }}”

Puede ver el estado de su solicitud en su intranet.

<x-mail::button :url="$enlace">
Ver mi solicitud
</x-mail::button>

Atentamente,

**Secretaría**<br>
EESP Pukllasunchis
</x-mail::message>
