@extends('layouts.seguimiento')
@section('titulo', 'Bandeja restringida de conciliación histórica · Seguimiento de Egresados')
@section('contenido')
<div class="container-fluid py-3">
    <div class="mb-2">
        <a href="{{ route('seguimiento.panel.coordinacion') }}" class="btn btn-sm btn-outline-primary">← Volver a Coordinación de egresados</a>
    </div>
    <h1 class="h4 mb-2">Bandeja restringida de conciliación histórica (2026 / Promociones)</h1>
    <p class="text-muted small">
        Las coincidencias por nombre nunca vinculan automáticamente un registro histórico. Toda resolución exige justificación y queda auditada con revisor, fecha y versión.
        Los textos sensibles de bienestar permanecen segregados y no se muestran en esta bandeja.
    </p>

    <div class="card p-3 mb-3">
        <h2 class="h6">Resumen por fuente y estado de conciliación</h2>
        <div class="table-responsive">
            <table class="table table-sm table-bordered mb-0">
                <thead>
                    <tr>
                        <th>Fuente</th>
                        <th>Estado</th>
                        <th>Registros</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($resumenPorFuente as $fila)
                        <tr>
                            <td><code>{{ $fila->fuente_codigo }}</code></td>
                            <td>{{ $fila->estado_conciliacion }}</td>
                            <td><strong>{{ $fila->total }}</strong></td>
                        </tr>
                    @empty
                        <tr><td colspan="3">Sin lotes cargados en este entorno.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    @foreach($registros as $reg)
        @php($cands = $candidaturas->get($reg->id, collect()))
        <article class="card p-3 mb-3">
            <div class="d-flex justify-content-between align-items-center">
                <strong>{{ $reg->fuente_codigo }} · Fila {{ $reg->fila_origen }}</strong>
                <span class="badge badge-info">{{ $reg->estado_conciliacion }}</span>
            </div>
            <p class="small text-muted mb-2">Corte: {{ $reg->fecha_fuente ?: 'Fecha exacta desconocida' }} · Hash: <code>{{ substr($reg->registro_hash, 0, 16) }}…</code></p>

            @if($cands->isNotEmpty())
                <p class="mb-1"><strong>Candidatos institucionales detectados para revisión humana:</strong></p>
                <ul class="small mb-2">
                    @foreach($cands as $c)
                        <li>
                            Usuario #{{ $c->user_id_candidato }} ({{ $c->name }} {{ $c->apellidos }} · Condición actual: {{ $c->condicion ?? 'No consignada' }})
                            — Método: <code>{{ $c->metodo }}</code> ({{ $c->confianza }} · {{ $c->estado_revision }})
                        </li>
                    @endforeach
                </ul>
            @else
                <p class="small text-muted mb-2">Sin candidatos propuestos en el padrón de usuarios.</p>
            @endif

            <form method="POST" action="{{ route('seguimiento.panel.conciliacion.resolver', ['registro' => $reg->id]) }}" class="form-inline flex-wrap">
                @csrf
                <select name="accion" class="form-control form-control-sm mr-2 mb-1" required>
                    <option value="aprobar">Aprobar vínculo</option>
                    <option value="rechazar">Rechazar / Dejar sin correspondencia</option>
                </select>
                <select name="user_id_validado" class="form-control form-control-sm mr-2 mb-1">
                    <option value="">— Seleccionar candidato —</option>
                    @foreach($cands as $c)
                        <option value="{{ $c->user_id_candidato }}">#{{ $c->user_id_candidato }} · {{ $c->name }} {{ $c->apellidos }}</option>
                    @endforeach
                </select>
                <select name="trayectoria_tipo" class="form-control form-control-sm mr-2 mb-1">
                    <option value="">Trayectoria automática según evidencia</option>
                    <option value="FID">FID</option>
                    <option value="PPD">PPD</option>
                    <option value="TRANSVERSAL">Transversal a la persona</option>
                    <option value="PENDIENTE">Trayectoria pendiente</option>
                </select>
                <input type="text" name="motivo" class="form-control form-control-sm mr-2 mb-1" placeholder="Justificación de la decisión (mín. 5 caracteres)" required minlength="5">
                <button type="submit" class="btn btn-sm btn-primary mb-1">Registrar decisión</button>
            </form>
        </article>
    @endforeach

    {{ $registros->links() }}
</div>
@endsection
