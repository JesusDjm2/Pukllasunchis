{{-- Membrete común de las Resoluciones Directorales (PDF con dompdf). Cada plantilla lo
     extiende y llena 'cuerpo'. Espera: $lema, $numero, $anio, $fechaLarga, $iniciales. --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>@yield('titulo', 'Resolución Directoral')</title>
    <style>
        @page { margin: 3.1cm 2.3cm 3.3cm 2.6cm; }
        body { font-family: "Times New Roman", Times, serif; font-size: 11.5pt; line-height: 1.32; color: #111; }

        .membrete { position: fixed; top: -2.6cm; left: -0.4cm; }
        .membrete img { height: 2.05cm; }

        /* Pie: la imagen (franja + árbol) es más ancha que la hoja y se ancla a la derecha:
           el árbol queda en el borde y se pierde parte del lado izquierdo de la franja. */
        .pie { position: fixed; right: -2.3cm; bottom: -3.45cm; width: 24cm; height: 5.37cm; }
        .pie img { display: block; width: 24cm; height: 5.37cm; }
        .pie__datos { position: fixed; right: -0.25cm; bottom: -2.9cm; text-align: right; font-family: Helvetica, Arial, sans-serif; font-size: 8pt; line-height: 1.45; color: #fff; font-weight: bold; }
        .pie__datos img { width: 8pt; height: 8pt; margin-right: 3pt; vertical-align: -1pt; }
        .iniciales { position: fixed; left: 0; bottom: -0.95cm; font-family: Helvetica, Arial, sans-serif; font-size: 5.5pt; line-height: 1.15; color: #444; }

        .lema { text-align: center; margin: 0 0 .7em; line-height: 1.25; }
        .titulo-rd { font-weight: bold; text-decoration: underline; margin: 0 0 .7em; }
        .fecha-rd { text-align: right; margin: 0 0 .7em; }
        .seccion { font-weight: bold; margin: .55em 0 .3em; padding-left: 1.4cm; }
        p { text-align: justify; margin: 0 0 .45em; text-indent: 1.4cm; }
        p.sin-sangria { text-indent: 0; }
        .centro { text-align: center; text-indent: 0; }
        .firma { margin-top: .7em; }
    </style>
</head>
<body>
    <div class="membrete"><img src="{{ public_path('img/logo-iesp-pukllasunchis.png') }}" alt="EESPP Pukllasunchis"></div>

    <div class="pie">
        <img src="{{ public_path('img/rd/pie-resolucion.png') }}" alt="">
    </div>
    {{-- Íconos (ubicación, teléfono, web) como SVG embebido: dompdf no carga Font Awesome. --}}
    @php
        $icono = fn (string $d) => 'data:image/svg+xml;base64,'.base64_encode('<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 24 24" width="24" height="24"><path fill="#ffffff" d="'.$d.'"/></svg>');
    @endphp
    <div class="pie__datos">
        <img src="{{ $icono('M12 2C8.13 2 5 5.13 5 9c0 5.25 7 13 7 13s7-7.75 7-13c0-3.87-3.13-7-7-7zm0 9.5a2.5 2.5 0 1 1 0-5 2.5 2.5 0 0 1 0 5z') }}" alt="">Siete Diablitos 222, San Blas, Cusco<br>
        <img src="{{ $icono('M6.62 10.79a15.05 15.05 0 0 0 6.59 6.59l2.2-2.2a1 1 0 0 1 1.01-.24c1.12.37 2.33.57 3.58.57a1 1 0 0 1 1 1V20a1 1 0 0 1-1 1A17 17 0 0 1 3 4a1 1 0 0 1 1-1h3.5a1 1 0 0 1 1 1c0 1.25.2 2.45.57 3.57a1 1 0 0 1-.25 1.02l-2.2 2.2z') }}" alt="">(84) 23 79 18 - 26 14 31<br>
        <img src="{{ $icono('M12 2a10 10 0 1 0 0 20 10 10 0 0 0 0-20zm6.93 6h-2.95a15.6 15.6 0 0 0-1.38-3.56A8.03 8.03 0 0 1 18.93 8zM12 4.04c.83 1.2 1.48 2.53 1.91 3.96h-3.82c.43-1.43 1.08-2.76 1.91-3.96zM4.26 14a8.2 8.2 0 0 1 0-4h3.38a16.5 16.5 0 0 0 0 4H4.26zm.82 2h2.95c.32 1.25.78 2.45 1.38 3.56A8 8 0 0 1 5.08 16zm2.95-8H5.08a8 8 0 0 1 4.33-3.56A15.6 15.6 0 0 0 8.03 8zM12 19.96c-.83-1.2-1.48-2.53-1.91-3.96h3.82c-.43 1.43-1.08 2.76-1.91 3.96zM14.34 14H9.66a14.7 14.7 0 0 1 0-4h4.68a14.7 14.7 0 0 1 0 4zm.25 5.56c.6-1.11 1.06-2.31 1.38-3.56h2.95a8.03 8.03 0 0 1-4.33 3.56zM16.36 14a16.5 16.5 0 0 0 0-4h3.38a8.2 8.2 0 0 1 0 4h-3.38z') }}" alt="">www.eesppukllasunchis.edu.pe
    </div>
    <div class="iniciales">{!! collect($iniciales)->map(fn ($i) => e($i))->implode('<br>') !!}</div>

    <div class="lema">
        “{{ $lema }}”<br>
        “CUSCO CAPITAL HISTÓRICA DEL PERÚ”
    </div>

    <div class="titulo-rd">RESOLUCIÓN DIRECTORAL Nº {{ $numero }}-{{ $anio }}-DG/EESP-“PUKLLASUNCHIS”-CUSCO</div>

    <div class="fecha-rd">Cusco, {{ $fechaLarga }} del {{ $anio }}</div>

    @yield('cuerpo')

    {{-- Cierre según el modelo de cada RD (PlantillasRd::PLANTILLAS[…]['cierre']). --}}
    @switch($cierre ?? 'formal')
        @case('mayusculas')
            <p class="centro firma">REGÍSTRESE, COMUNÍQUESE Y ARCHÍVESE</p>
            @break
        @case('simple')
            <p class="centro firma" style="margin-bottom:0;">Regístrese, comuníquese y archívese</p>
            <p class="centro">Atentamente.</p>
            @break
        @case('simple_coma')
            <p class="centro firma" style="margin-bottom:0;">Regístrese, comuníquese y archívese</p>
            <p class="centro">Atentamente,</p>
            @break
        @default
            <p class="centro firma">REGÍSTRESE, COMUNÍQUESE Y ARCHÍVESE</p>
            <p class="centro">Atentamente.</p>
    @endswitch
</body>
</html>
