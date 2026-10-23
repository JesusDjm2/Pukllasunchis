{{-- Fila de curso en la ficha PPD. Espera $curso (con docentes) y $es (estado del sílabo). --}}
<li class="al-curso">
    <span class="al-curso-num">{{ $loop->iteration ?? '' }}</span>
    <div class="al-curso-texto">
        <a href="{{ route('curso.show', $curso->id) }}" class="al-curso-nombre">{{ $curso->nombre }}</a>
        <div class="al-curso-meta">
            @if ($curso->docentes->isNotEmpty())
                <i class="fas fa-chalkboard-teacher"></i>{{ $curso->docentes->pluck('nombre')->filter()->join(', ') }}
            @else
                <i class="fas fa-chalkboard-teacher"></i><span class="is-falta">Docente por asignar</span>
            @endif
        </div>
    </div>
    <div class="al-curso-accion">
        @if ($es['tipo'] === 'ok')
            <a href="{{ $es['url'] }}" target="_blank" rel="noopener" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-eye"></i> Sílabo</a>
        @else
            <span class="al-badge {{ $es['tipo'] === 'pronto' ? 'is-warn' : 'is-muted' }}">{{ $es['texto'] }}</span>
        @endif
    </div>
</li>
