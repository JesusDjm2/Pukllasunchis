{{-- Ficha de una persona para el acordeón de /titulacion, pedida al abrir la fila
     (TitulacionController@ficha). Sin recursos compartidos: ya están en la lista. --}}
@include('titulacion._show_contenido', [
    'enModal' => true,
    'enAcordeon' => true,
    'sinRecursos' => true,
])