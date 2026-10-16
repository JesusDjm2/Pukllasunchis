{{-- Barras "Calificado" de un curso FID por periodo. $pct llega de CursoController@index (null si no es FID). --}}
@if (! is_null($pct))
    @foreach ([1 => 'Parcial 1', 2 => 'Parcial 2', 3 => 'Desempeño'] as $n => $etiqueta)
        <li class="curso-pct-fid curso-pct-fid-p{{ $n }}" title="Avance de calificación — {{ $etiqueta }}">
            <span class="curso-pct-fid-label">{{ $etiqueta }}:</span>
            <div class="progress flex-fill curso-pct-fid-bar">
                <div class="progress-bar" role="progressbar" style="width:{{ $pct[$n] }}%;"
                    aria-valuenow="{{ $pct[$n] }}" aria-valuemin="0" aria-valuemax="100"></div>
            </div>
            <span class="curso-pct-fid-val">{{ number_format($pct[$n], 2) }}%</span>
        </li>
    @endforeach
@endif
