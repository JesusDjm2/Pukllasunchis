@extends('layouts.docente')

@section('titulo', 'Panel de Tutor')

@section('contenido')
<div class="container-fluid docente-ui-page">
    <div class="card docente-ui-card docente-ui-hero mb-3 mb-md-4">
        <div class="card-body p-3 p-md-4">
            <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between">
                <div class="mb-2 mb-md-0">
                    <p class="docente-ui-kicker mb-1">Panel de Tutor</p>
                    <h1 class="docente-ui-title mb-0">
                        ¡Bienvenido, {{ auth()->user()->name }} {{ auth()->user()->apellidos }}!
                    </h1>
                    <p class="docente-ui-subtitle mb-0 mt-2">Estás ingresando como <strong>Tutor</strong>.</p>
                </div>
                @role('docente')
                    @if(auth()->user()->docente)
                        <div class="flex-shrink-0">
                            <a href="{{ route('vistaDocente', ['docente' => auth()->user()->docente->id]) }}"
                               class="btn btn-outline-secondary btn-sm btn-block d-md-inline-block">
                                <i class="fas fa-arrow-left mr-1"></i> Ir a mi panel de Docente
                            </a>
                        </div>
                    @endif
                @endrole
            </div>
        </div>
    </div>

    {{-- Ciclos asignados --}}
    @if ($ciclos->isEmpty())
        <div class="card shadow-sm border-0">
            <div class="card-body text-center text-muted py-5">
                <i class="fas fa-chalkboard fa-2x mb-3 text-gray-300 d-block"></i>
                <p class="mb-0 font-weight-bold">Aún no tienes ciclos asignados</p>
                <p class="small mb-0">Un administrador debe asignarte los ciclos que supervisarás.</p>
            </div>
        </div>
    @else
        <h6 class="text-uppercase text-muted font-weight-bold mb-3" style="font-size:.72rem;letter-spacing:.08em;">
            <i class="fas fa-layer-group mr-1"></i> Mis ciclos asignados
        </h6>
        <div class="row">
            @foreach ($ciclos as $ciclo)
                <div class="col-sm-6 col-lg-4 mb-3">
                    <a href="{{ route('tutor.ciclo', $ciclo->id) }}"
                       class="card border-0 shadow-sm h-100 text-decoration-none"
                       style="transition:box-shadow .15s,transform .15s;"
                       onmouseover="this.style.boxShadow='0 4px 18px rgba(0,0,0,.12)';this.style.transform='translateY(-2px)'"
                       onmouseout="this.style.boxShadow='';this.style.transform=''">
                        <div class="card-body d-flex align-items-center">
                            <div class="rounded-circle d-flex align-items-center justify-content-center mr-3 flex-shrink-0"
                                 style="width:48px;height:48px;background:linear-gradient(135deg,#4e73df,#224abe);">
                                <i class="fas fa-users text-white"></i>
                            </div>
                            <div>
                                <div class="font-weight-bold text-gray-800" style="font-size:.95rem;">
                                    Ciclo {{ $ciclo->nombre }}
                                </div>
                                <div class="text-muted small">
                                    {{ optional($ciclo->programa)->nombre ?? '—' }}
                                </div>
                            </div>
                            <i class="fas fa-chevron-right text-gray-400 ml-auto"></i>
                        </div>
                    </a>
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
