{{-- Marco mínimo para el egresado FID: su intranet (/egresado) es una página suelta, sin el menú de
     la intranet de alumno (que asume matrícula activa). Mismas secciones que los layouts del alumno. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1, shrink-to-fit=no">
    <title>@yield('titulo', 'Mi cuenta') — EESPP Pukllasunchis</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet" type="text/css">
    <link href="https://fonts.googleapis.com/css?family=Nunito:400,600,700,800" rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
</head>
<body style="background:#f5f7fa;">
    <div class="container py-4" style="max-width:960px;">
        @yield('contenido')
    </div>
</body>
</html>
