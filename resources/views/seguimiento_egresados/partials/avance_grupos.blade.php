@if(isset($avancePorGrupo))
<section style="padding:16px;margin:12px;background:white;border:1px solid #c8d5ec;border-radius:10px;color:#25344b">
<h2>Avance de seguimiento · FID / PPD</h2>
<div style="overflow-x:auto"><table style="width:100%"><thead><tr><th>Grupo</th><th>Asignados únicos</th><th>Contactados</th><th>Actualizados</th><th>Pendientes</th><th>Avance</th></tr></thead><tbody>
@foreach($avancePorGrupo as $grupo => $dato)
<tr><th>{{ $grupo === 'POR_VERIFICAR' ? 'Por verificar' : $grupo }}</th><td>{{ $dato['personas'] }}</td><td>{{ $dato['contactados'] }}</td><td>{{ $dato['actualizados'] }}</td><td>{{ $dato['pendientes'] }}</td><td>{{ $dato['avance_pct'] === null ? 'No calculable' : $dato['avance_pct'].' %' }} @if($dato['avance_pct'] !== null)<progress max="100" value="{{ $dato['avance_pct'] }}" aria-label="Avance {{ $grupo }}"></progress>@endif</td></tr>
@endforeach
</tbody></table></div><small>Cada porcentaje usa los asignados de su propio grupo. Las personas con ambos egresos pueden aparecer en ambas columnas; el total conjunto no se obtiene sumándolas.</small>
</section>
@endif
