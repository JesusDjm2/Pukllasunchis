{{-- Cabecera compacta del formulario de sílabo: reemplaza el encabezado genérico del panel
     ("Sílabos / Crear sílabo / Complete el formulario…") y la tarjeta de portada, que
     repetían lo mismo y ocupaban media pantalla. Espera: $accion ('Nuevo sílabo' |
     'Editando sílabo'), $curso, $periodo (nombre), $backUrl, $backLabel. --}}
<header class="silabo-cabecera">
    @include('docentes.partials.boton-volver', ['url' => $backUrl, 'label' => $backLabel])
    <div class="silabo-cabecera-texto">
        <span class="silabo-cabecera-accion">{{ $accion }}</span>
        <h1 class="silabo-cabecera-curso">{{ $curso->nombre }}</h1>
        <div class="silabo-cabecera-chips">
            <span><i class="fas fa-graduation-cap"></i> {{ $curso->ciclo->programa->nombre }}</span>
            <span><i class="fas fa-layer-group"></i> Ciclo {{ trim(str_ireplace('Ciclo', '', $curso->ciclo->nombre)) }}</span>
            <span><i class="far fa-calendar-alt"></i> Periodo {{ $periodo }}</span>
        </div>
    </div>
    <img class="silabo-cabecera-logo" src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" alt="Logo IESP Pukllasunchis">
</header>
