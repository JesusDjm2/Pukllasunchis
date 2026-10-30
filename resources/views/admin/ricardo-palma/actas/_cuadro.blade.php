{{-- Cuadro estadístico del acta ($cuadro viene de RpActa::matriz()). $clase: clases de la tabla según dónde se muestra. --}}
<div class="al-tabla-marco">
    <table class="{{ $clase }}">
        <thead>
            <tr>
                <th>{{ $titulo ?? '' }}</th>
                <th class="text-center">H</th><th class="text-center">%</th>
                <th class="text-center">M</th><th class="text-center">%</th>
                <th class="text-center">T</th><th class="text-center">%</th>
            </tr>
        </thead>
        <tbody>
            @foreach ($cuadro as $nombre => $f)
                <tr>
                    <td class="font-weight-bold">{{ $nombre }}</td>
                    @foreach (['h', 'h_pct', 'm', 'm_pct', 't', 't_pct'] as $clave)
                        <td class="text-center">{{ $f[$clave] ?: '--' }}</td>
                    @endforeach
                </tr>
            @endforeach
        </tbody>
    </table>
</div>
