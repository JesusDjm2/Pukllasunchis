<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cursos PPD a sincronizar (opcional)
    |--------------------------------------------------------------------------
    |
    | Lista de IDs de curso separados por comas en .env, por ejemplo:
    | PPD_PERIODOS_SYNC_CURSO_IDS=32,33
    |
    | Si está vacío o no se define, se sincronizan todos los cursos con datos
    | en calificacionesppds.
    |
    */
    'cursos_sincronizacion_limitados' => array_values(array_filter(array_map(
        'intval',
        array_filter(explode(',', (string) env('PPD_PERIODOS_SYNC_CURSO_IDS', '')))
    ))),

];
