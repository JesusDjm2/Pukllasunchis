@if(isset($longitudinalInicial['comparativa_grupos']))
<section id="comparativa-grupos" style="padding:18px;margin:12px;background:white;border:1px solid #c8d5ec;border-radius:10px;color:#25344b">
<h2>FID y PPD · Comparación de seguimiento</h2>
<div style="overflow-x:auto"><table style="width:100%;border-collapse:collapse"><thead><tr><th>Grupo</th><th>Personas</th><th>Con información laboral</th><th>Trabajan</th><th>Empleo / respuestas laborales</th></tr></thead><tbody>
@foreach($longitudinalInicial['comparativa_grupos']['grupos'] as $grupo => $valores)
<tr><th>{{ $grupo === 'POR_VERIFICAR' ? 'Por verificar' : $grupo }}</th><td>{{ $valores['personas']['valor'] }}</td><td>{{ $valores['con_informacion_laboral']['valor'] }}</td><td>{{ $valores['trabajan']['valor'] }}</td><td>{{ $valores['tasa_empleo'] === null ? 'No calculable / protegido' : $valores['tasa_empleo'].' %' }}</td></tr>
@endforeach
</tbody></table></div><p>{{ $longitudinalInicial['comparativa_grupos']['nota'] }}</p>
<details><summary>Distribución por cohorte de cada grupo</summary>
@foreach($longitudinalInicial['comparativa_grupos']['grupos'] as $grupo => $valores)
<h3>{{ $grupo === 'POR_VERIFICAR' ? 'Por verificar' : $grupo }}</h3>
@foreach($valores['cohortes'] as $cohorte => $celda)<p>{{ $cohorte }}: {{ is_array($celda) ? ($celda['valor'] ?? 'Protegido') : $celda }}</p>@endforeach
@endforeach
</details></section>
@endif
