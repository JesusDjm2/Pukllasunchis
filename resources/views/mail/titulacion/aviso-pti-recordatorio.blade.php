<x-mail::message>
{{ $saludo }}:

Desde la Unidad de Investigación de la EESP, le saludamos cordialmente y nos dirigimos para recordarle que el plazo de vigencia de su Plan de Trabajo de Investigación (PTI) es de 2 años, desde el momento en que se inscribe.

Revisando nuestro registro, vemos que su PTI fue inscrito en **{{ $inscripcion ?? 'la fecha registrada' }}**; por lo tanto, a la fecha de este mensaje le queda una vigencia de **{{ $tiempoRestante }}**, hasta que venza definitivamente, es decir, aproximadamente a **finales de {{ $vence }}**.

Esto significa que, si desea continuar con el mismo tema de investigación, usted tiene ese plazo para concluir todo su proceso de licenciatura y no únicamente su bachillerato.

Por lo tanto, es necesario y urgente que avance con las actividades correspondientes tanto de su Trabajo de Investigación o Tesis, ya que, de no concluir los trámites de titulación antes de que venza su PTI, se verá en la obligación de presentar un nuevo PTI, con un tema diferente al avanzado, según el Reglamento de Investigación Art. 6°.

Le recomendamos acercarse a revisar personalmente su situación y buscar las alternativas más adecuadas para su caso. Por favor, avísenos con anticipación por este medio la hora en que vendrá.

En caso de dudas o para retomar asesorías, estaremos atendiendo por diferentes medios:

- **Vía WhatsApp:** escríbanos al {{ $whatsapp }} o directamente en este enlace: [https://wa.me/51{{ $whatsapp }}](https://wa.me/51{{ $whatsapp }})
- **Presencial:** martes y miércoles de 3 p. m. a 5 p. m.

Agradecemos su atención y compromiso con el cumplimiento de los plazos establecidos.

Atentamente,

**Unidad de Investigación**<br>
EESP Pukllasunchis
</x-mail::message>
