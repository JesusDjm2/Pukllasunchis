<x-mail::message>
# Solicitud de carta de aceptación

Llegó una nueva solicitud desde el sistema para la emisión de la **Carta de Presentación** de un Trabajo de Investigación.

<x-mail::table>
| | |
|:--|:--|
| **Grupo de investigación** | {{ $carta->grupo }} |
| **Correo institucional** | {{ $carta->correo }} |
| **Celular** | {{ $carta->celular }} |
| **Programa de Estudios** | {{ $carta->programa_estudios }} |
| **Programa de Formación** | {{ $formacion }} |
| **Institución Educativa** | {{ $carta->institucion }} |
| **Director(a)** | {{ $carta->director }} |
| **Título del trabajo** | {{ $carta->titulo }} |
</x-mail::table>

El comprobante de pago (S/ 10) va adjunto. El estudiante declaró bajo juramento que la información registrada es verdadera.

<x-mail::button :url="$enlaceFicha">
Abrir la ficha y subir la carta
</x-mail::button>

Si responde este correo, la respuesta llega al estudiante.

**Sistema de titulación**<br>
EESP Pukllasunchis
</x-mail::message>
