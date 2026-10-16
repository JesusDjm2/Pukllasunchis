<?php

/*
|--------------------------------------------------------------------------
| Proceso de titulación
|--------------------------------------------------------------------------
| Guías/instructivos por etapa que ven egresados y alumnos en su línea de
| tiempo ("Mi proceso de titulación"). Mientras una esté vacía, la etapa
| simplemente no muestra el enlace. Basta con pegar el enlace (Drive,
| PDF público, página del sitio) en el .env y correr `php artisan config:clear`.
*/

return [
    'guias' => [
        'pti' => env('TITULACION_GUIA_PTI'),
        'ti' => env('TITULACION_GUIA_TI'),
        'tesis' => env('TITULACION_GUIA_TESIS'),
    ],
];
