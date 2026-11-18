<!doctype html>
<html lang="es">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('titulo','Seguimiento de Egresados · EESP Pukllasunchis')</title>
    <link rel="shortcut icon" type="image/x-icon" href="{{ asset('img/logoiesp.ico') }}">
    <link href="{{ asset('admin/vendor/fontawesome-free/css/all.min.css') }}" rel="stylesheet">
    <link href="{{ asset('admin/css/sb-admin-2.min.css') }}" rel="stylesheet">
    <link rel="stylesheet" href="{{ asset('admin/css/estilos.css') }}">
    <link rel="stylesheet" href="{{ asset('admin/css/darkmode.css') }}?v={{ filemtime(public_path('admin/css/darkmode.css')) }}">
    <link href="{{ asset('css/pk-tokens.css') }}?v={{ filemtime(public_path('css/pk-tokens.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/pk-sidebar.css') }}?v={{ filemtime(public_path('css/pk-sidebar.css')) }}" rel="stylesheet">
    <link href="{{ asset('css/pk-topbar.css') }}?v={{ filemtime(public_path('css/pk-topbar.css')) }}" rel="stylesheet">
    @stack('styles')
</head>
<body id="page-top" class="pk-rol-admin">
<script>
    var _pt = localStorage.getItem('puklla-theme');
    if (_pt === 'dark') document.body.classList.add('dark-mode');
    else if (_pt === 'dim') document.body.classList.add('dim-mode');
</script>
<div id="wrapper">
    @include('layouts.partials.menu-lateral-gestion', ['area' => 'Seguimiento de Egresados', 'iconoArea' => 'fa-user-check'])
    <div id="content-wrapper" class="d-flex flex-column">
        <div id="content">
            @include('layouts.partials.barra-superior', ['area' => 'Seguimiento de Egresados', 'idLogout' => 'pk-sb-base-logout'])
            <main class="p-3">
                @if(session('success'))
                    <div class="alert alert-success">{{ session('success') }}</div>
                @endif
                @if($errors->any())
                    <div class="alert alert-danger">{{ $errors->first() }}</div>
                @endif
                @yield('contenido')
            </main>
        </div>
        @include('layouts.partials.pie-intranet', ['area' => 'Seguimiento de Egresados'])
    </div>
</div>
<script src="{{ asset('admin/vendor/jquery/jquery.min.js') }}"></script>
<script src="{{ asset('admin/vendor/bootstrap/js/bootstrap.bundle.min.js') }}"></script>
<script src="{{ asset('admin/vendor/jquery-easing/jquery.easing.min.js') }}"></script>
<script src="{{ asset('admin/js/sb-admin-2.min.js') }}"></script>
@stack('scripts')
@include('layouts.partials.tema-toggle')
</body>
</html>
