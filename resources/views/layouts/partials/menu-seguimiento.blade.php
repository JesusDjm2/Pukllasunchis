@php
$permisosSeguimiento = app(\App\Services\SeguimientoEgresado\SeguimientoAutorizacionService::class);
$coordSeguimiento = $permisosSeguimiento->esPersonalGestion();
$entrevistaSeguimiento = $permisosSeguimiento->esDocenteOEntrevistador();
$esEgresadoSb = auth()->user()->condicion === 'Egresado' || auth()->user()->hasRole('egresado');
$enPanelCoord = request()->routeIs('seguimiento.panel.*', 'seguimiento.campanas.*', 'seguimiento.observatorio-conectado*', 'seguimiento.laboratorio*');
$enEntrevistas = request()->routeIs('seguimiento-egresados.mis-asignaciones*', 'seguimiento.docente.mis-asignaciones*');
@endphp
@if($coordSeguimiento || $entrevistaSeguimiento || $esEgresadoSb)
<div class="pk-sb-titulo">Funciones de seguimiento</div>
@if($coordSeguimiento)
<li class="nav-item">
    <a class="nav-link pk-sb-link {{ $enPanelCoord ? 'is-activo' : '' }}" @if($enPanelCoord) aria-current="page" @endif href="{{ route('seguimiento.panel.coordinacion') }}">
        <span class="pk-sb-icono"><i class="fas fa-chart-line"></i></span>
        <span class="pk-sb-texto">Seguimiento de egresados<small>Coordinación de egresados</small></span>
    </a>
</li>
@endif
@if($entrevistaSeguimiento || $coordSeguimiento)
<li class="nav-item">
    <a class="nav-link pk-sb-link {{ $enEntrevistas ? 'is-activo' : '' }}" @if($enEntrevistas) aria-current="page" @endif href="{{ route('seguimiento-egresados.mis-asignaciones') }}">
        <span class="pk-sb-icono"><i class="fas fa-phone"></i></span>
        <span class="pk-sb-texto">Mis entrevistas</span>
    </a>
</li>
@endif
@if($esEgresadoSb)
<li class="nav-item">
    <a class="nav-link pk-sb-link {{ request()->routeIs('seguimiento.mi-espacio') ? 'is-activo' : '' }}" @if(request()->routeIs('seguimiento.mi-espacio')) aria-current="page" @endif href="{{ route('seguimiento.mi-espacio') }}">
        <span class="pk-sb-icono"><i class="fas fa-user-check"></i></span>
        <span class="pk-sb-texto">Actualizar mis datos y solicitar apoyo</span>
    </a>
</li>
<li class="nav-item">
    <a class="nav-link pk-sb-link {{ request()->routeIs('egresado.intranet') ? 'is-activo' : '' }}" @if(request()->routeIs('egresado.intranet')) aria-current="page" @endif href="{{ route('egresado.intranet') }}">
        <span class="pk-sb-icono"><i class="fas fa-user-graduate"></i></span>
        <span class="pk-sb-texto">Mi trayectoria y titulación</span>
    </a>
</li>
@endif
@endif
