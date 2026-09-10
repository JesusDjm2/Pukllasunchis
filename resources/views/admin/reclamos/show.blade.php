@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('contenido')
<div class="container-fluid bg-white pb-5">

    <div class="d-sm-flex align-items-center justify-content-between mb-4 pb-2 pt-4"
        style="border-bottom: 1px dashed #80808078">
        <div>
            <h5 class="mb-0 font-weight-bold text-primary">
                <i class="fa fa-book mr-2 text-warning"></i> Reclamo N° {{ $reclamo->numero_reclamo }}
            </h5>
            <small class="text-muted">Registrado el {{ $reclamo->created_at->format('d/m/Y H:i') }}</small>
        </div>
        <a href="{{ route('admin.reclamos.todas') }}" class="btn btn-sm btn-secondary">
            <i class="fa fa-arrow-left fa-sm mr-1"></i> Volver al listado
        </a>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light font-weight-bold">1. Datos del reclamante</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Nombre:</strong> {{ $reclamo->nombre }}</div>
                <div class="col-md-2"><strong>DNI:</strong> {{ $reclamo->dni }}</div>
                <div class="col-md-3"><strong>Teléfono:</strong> {{ $reclamo->telefono }}</div>
                <div class="col-md-3"><strong>Correo:</strong> {{ $reclamo->correo }}</div>
                <div class="col-md-8 mt-2"><strong>Domicilio:</strong> {{ $reclamo->domicilio }}</div>
                <div class="col-md-4 mt-2"><strong>Condición:</strong> {{ $reclamo->condicion_reclamante }}</div>
            </div>
        </div>
    </div>

    @if ($reclamo->programa || $reclamo->ciclo || $reclamo->codigo_estudiante)
        <div class="card mb-3">
            <div class="card-header bg-light font-weight-bold">2. Datos académicos</div>
            <div class="card-body">
                <div class="row">
                    <div class="col-md-4"><strong>Programa:</strong> {{ $reclamo->programa ?? '—' }}</div>
                    <div class="col-md-4"><strong>Ciclo:</strong> {{ $reclamo->ciclo ?? '—' }}</div>
                    <div class="col-md-4"><strong>Código de estudiante:</strong> {{ $reclamo->codigo_estudiante ?? '—' }}</div>
                </div>
            </div>
        </div>
    @endif

    <div class="card mb-3">
        <div class="card-header bg-light font-weight-bold">Identificación del servicio</div>
        <div class="card-body">
            <div class="row">
                <div class="col-md-4"><strong>Tipo de servicio:</strong> {{ $reclamo->tipo_servicio }}</div>
                <div class="col-md-4"><strong>Área involucrada:</strong> {{ $reclamo->area_involucrada }}</div>
                <div class="col-md-4"><strong>Servicio contratado:</strong> {{ $reclamo->servicio_contratado ?? '—' }}</div>
                <div class="col-12 mt-2"><strong>Descripción del servicio:</strong> {{ $reclamo->descripcion_servicio ?? '—' }}</div>
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light font-weight-bold">
            Detalle de la reclamación
            <span class="badge {{ $reclamo->tipo_reclamacion === 'Reclamo' ? 'badge-danger' : 'badge-warning' }} float-right">
                {{ $reclamo->tipo_reclamacion }}
            </span>
        </div>
        <div class="card-body">
            <p><strong>Descripción de los hechos:</strong><br>{{ $reclamo->descripcion_hechos }}</p>
            <p><strong>Pedido:</strong><br>{{ $reclamo->pedido }}</p>
            <div class="mb-0">
                <strong>Adjunto:</strong>
                @if ($reclamo->adjunto)
                    @php $ext = strtolower(pathinfo($reclamo->adjunto, PATHINFO_EXTENSION)); @endphp
                    <div class="mt-2">
                        @if (in_array($ext, ['jpg', 'jpeg', 'png']))
                            <a href="{{ asset('reclamos/'.$reclamo->adjunto) }}" target="_blank">
                                <img src="{{ asset('reclamos/'.$reclamo->adjunto) }}" alt="Adjunto del reclamo"
                                    style="max-width: 260px; max-height: 260px; border-radius: 8px; border: 1px solid #dee2e6; object-fit: cover;">
                            </a>
                        @else
                            <a href="{{ asset('reclamos/'.$reclamo->adjunto) }}" target="_blank" class="btn btn-sm btn-outline-primary">
                                <i class="fa fa-file-pdf"></i> Ver archivo adjunto (PDF)
                            </a>
                        @endif
                    </div>
                @else
                    Ninguno
                @endif
            </div>
        </div>
    </div>

    <div class="card mb-3">
        <div class="card-header bg-light font-weight-bold">Consentimientos</div>
        <div class="card-body">
            <ul class="mb-0">
                <li>Declaró que la información proporcionada es verdadera:
                    <strong>{{ $reclamo->declara_informacion_verdadera ? 'Sí' : 'No' }}</strong></li>
                <li>Autorizó el tratamiento de sus datos personales:
                    <strong>{{ $reclamo->autoriza_tratamiento_datos ? 'Sí' : 'No' }}</strong></li>
                <li>Confirmó haber leído el Libro de Reclamaciones:
                    <strong>{{ $reclamo->confirma_lectura_libro ? 'Sí' : 'No' }}</strong></li>
            </ul>
        </div>
    </div>
</div>
@endsection
