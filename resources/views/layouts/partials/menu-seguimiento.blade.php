@php
$permisosSeguimiento = app(\App\Services\SeguimientoEgresado\SeguimientoAutorizacionService::class);
$coordSeguimiento = $permisosSeguimiento->esPersonalGestion();
$entrevistaSeguimiento = $permisosSeguimiento->esDocenteOEntrevistador();
@endphp
@if($coordSeguimiento || $entrevistaSeguimiento || auth()->user()->condicion === 'Egresado' || auth()->user()->hasRole('egresado'))
<div class="pk-sb-titulo">Red Puklla de Egresados</div>
@if($coordSeguimiento)<li class="nav-item"><a class="nav-link pk-sb-link" href="{{ route('seguimiento.panel.coordinacion') }}"><i class="fas fa-chart-line mr-2"></i>Coordinación de egresados</a></li>@endif
@if($entrevistaSeguimiento || $coordSeguimiento)<li class="nav-item"><a class="nav-link pk-sb-link" href="{{ route('seguimiento-egresados.mis-asignaciones') }}"><i class="fas fa-phone mr-2"></i>Mis entrevistas</a></li>@endif
@if(auth()->user()->condicion === 'Egresado' || auth()->user()->hasRole('egresado'))<li class="nav-item"><a class="nav-link pk-sb-link" href="{{ route('seguimiento.mi-espacio') }}"><i class="fas fa-user mr-2"></i>Mi espacio</a></li><li class="nav-item"><a class="nav-link pk-sb-link" href="{{ route('egresado.intranet') }}">Mi cuenta y titulación</a></li>@endif
@endif
