@if(isset($matriz))
<table class="matrix-table">
    <thead>
        <tr>
            <th>{{ $matriz['fila_label'] ?? 'Variable Fila' }} \ {{ $matriz['columna_label'] ?? 'Variable Columna' }}</th>
            @foreach($matriz['valores_columna'] ?? [] as $col)
                <th>{{ $col }}</th>
            @endforeach
            <th class="col-total">Total Fila</th>
        </tr>
    </thead>
    <tbody>
        @foreach($matriz['valores_fila'] ?? [] as $fila)
            <tr>
                <td><strong>{{ $fila }}</strong></td>
                @foreach($matriz['valores_columna'] ?? [] as $col)
                    @php
                        $celda = $matriz['celdas'][$fila][$col] ?? ($matriz['matriz_segura'][$fila][$col] ?? null);
                        $conteo = is_array($celda) ? ($celda['conteo'] ?? $celda['valor'] ?? 0) : ($celda ?? 0);
                        $suprimido = is_array($celda) ? ($celda['suprimido'] ?? false) : false;
                        $pct = is_array($celda) ? ($celda['porcentaje_fila'] ?? null) : null;
                    @endphp
                    <td>
                        @if(!$celda)
                            0
                        @elseif($suprimido)
                            <span class="privacy-masked" title="Supresión por secreto estadístico (&lt; 5)">&lt; 5</span>
                        @else
                            <span class="matrix-cell-val">{{ $conteo }}</span>
                            @if($pct !== null)
                                <span class="matrix-cell-pct">({{ $pct }}%)</span>
                            @endif
                        @endif
                    </td>
                @endforeach
                @php
                    $totFila = $matriz['totales_fila'][$fila] ?? ($matriz['totales_filas'][$fila] ?? null);
                    $totFilaConteo = is_array($totFila) ? ($totFila['conteo'] ?? $totFila['valor'] ?? 0) : ($totFila ?? 0);
                    $totFilaSupr = is_array($totFila) ? ($totFila['suprimido'] ?? false) : false;
                @endphp
                <td class="row-total">
                    @if($totFilaSupr)
                        <span class="privacy-masked">&lt; 5</span>
                    @else
                        {{ $totFilaConteo }}
                    @endif
                </td>
            </tr>
        @endforeach

        <tr style="border-top:2px solid #CBD5E0;background:#EDF2F7;">
            <td><strong>Total Columna</strong></td>
            @foreach($matriz['valores_columna'] ?? [] as $col)
                @php
                    $totCol = $matriz['totales_columna'][$col] ?? ($matriz['totales_columnas'][$col] ?? null);
                    $totColConteo = is_array($totCol) ? ($totCol['conteo'] ?? $totCol['valor'] ?? 0) : ($totCol ?? 0);
                    $totColSupr = is_array($totCol) ? ($totCol['suprimido'] ?? false) : false;
                @endphp
                <td class="col-total">
                    @if($totColSupr)
                        <span class="privacy-masked">&lt; 5</span>
                    @else
                        {{ $totColConteo }}
                    @endif
                </td>
            @endforeach

            @php
                $totGen = $matriz['total_general'] ?? 0;
                $totGenVal = is_array($totGen) ? ($totGen['conteo'] ?? $totGen['valor'] ?? 0) : $totGen;
                $totGenSupr = is_array($totGen) ? ($totGen['suprimido'] ?? false) : ($matriz['suprimido_general'] ?? false);
            @endphp
            <td class="col-total" style="font-size:15px;color:var(--puklla-granate);">
                @if($totGenSupr)
                    <span class="privacy-masked">&lt; 5</span>
                @else
                    {{ $totGenVal }}
                @endif
            </td>
        </tr>
    </tbody>
</table>

@if(!empty($matriz['nota_privacidad']))
    <div style="margin-top:10px;font-size:11px;color:#718096;font-style:italic;">
        🔒 {{ $matriz['nota_privacidad'] }}
    </div>
@endif
@endif
