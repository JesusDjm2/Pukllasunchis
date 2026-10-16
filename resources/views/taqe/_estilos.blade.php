{{--
    Estilos de Taq'e compartidos por todas sus vistas: viven en public/css/pk-taqe.css.
    Se apoyan en el sistema .al-ui (alumnos/partials/ui): incluir ambos.
--}}
@once
    <link rel="stylesheet" href="{{ asset('css/pk-taqe.css') }}?v={{ filemtime(public_path('css/pk-taqe.css')) }}">
@endonce
