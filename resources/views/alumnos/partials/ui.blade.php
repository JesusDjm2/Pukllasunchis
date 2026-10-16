{{--
    Sistema de diseño compartido (.al-ui): los estilos viven en public/css/pk-ui.css (caché del
    navegador, un solo lugar para todos los roles). Incluir este parcial en la vista y envolver el
    contenido en un contenedor .al-ui.
--}}
@once
    <link rel="stylesheet" href="{{ asset('css/pk-ui.css') }}?v={{ filemtime(public_path('css/pk-ui.css')) }}">
@endonce
