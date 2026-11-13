@extends('red_puklla.layout')
@section('red-contenido')
<section class="rp-card"><h2>Moderación y cuidado de la comunidad</h2><p>Revisa los reportes antes de decidir. Ocultar, retirar y restaurar requieren motivo y conservan trazabilidad. Facilitar un ayllu no concede estas funciones.</p>@forelse($registros as $m)<article class="rp-message"><p>{{ $m->created_at }} · {{ $m->tipo }} #{{ $m->referencia_id }} · {{ $m->accion }} · Usuario institucional #{{ $m->actor_id }}</p><p class="rp-body">{{ $m->motivo }}</p><a href="{{ route($m->tipo==='publicacion'?'red.publicacion':'red.oportunidad',['id'=>$m->referencia_id]) }}">Revisar contenido y actuar →</a></article>@empty<p>No hay reportes ni intervenciones registradas.</p>@endforelse{{ $registros->links() }}</section>
@endsection
