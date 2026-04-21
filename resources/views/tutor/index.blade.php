@extends('layouts.docente')

@section('titulo', 'Panel de Tutor')

@section('contenido')
<div class="container-fluid">
    <div class="d-sm-flex align-items-center justify-content-between mb-4"
         style="border-bottom: 1px dashed #4848fc78; padding-bottom:1em">
        <h3 class="font-weight-bold text-primary">Panel de Tutor</h3>
    </div>

    <div class="mb-4">
        <h5 class="font-weight-bold text-gray-800">
            ¡Bienvenido, {{ auth()->user()->name }} {{ auth()->user()->apellidos }}!
        </h5>
        <p class="text-muted mb-0">Estás ingresando como <strong>Tutor</strong>.</p>
    </div>

    @role('docente')
        @if(auth()->user()->docente)
            <a href="{{ route('vistaDocente', ['docente' => auth()->user()->docente->id]) }}"
               class="btn btn-outline-primary mb-4">
                <i class="fas fa-arrow-left mr-1"></i> Ir a mi panel de Docente
            </a>
        @endif
    @endrole

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
