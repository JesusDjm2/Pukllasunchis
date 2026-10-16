{{-- Barra "Calificado" de un curso PPD. $pct llega de CursoController@index (null si no es PPD). --}}
@if (! is_null($pct))
    <li class="curso-pct-ppd" title="Avance de calificación del curso (casillas llenas)">
        <span class="curso-pct-ppd-label">Calificado:</span>
        <div class="progress flex-fill curso-pct-ppd-bar">
            <div class="progress-bar" role="progressbar" style="width:{{ $pct }}%;"
                aria-valuenow="{{ $pct }}" aria-valuemin="0" aria-valuemax="100"></div>
        </div>
        <span class="curso-pct-ppd-val">{{ number_format($pct, 2) }}%</span>
    </li>
@endif
