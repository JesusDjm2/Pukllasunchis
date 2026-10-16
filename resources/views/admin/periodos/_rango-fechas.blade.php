{{-- Rango "inicio → cierre" en una sola celda. Un lado vacío se muestra como "—". --}}
<div class="per-rango">
    @if ($inicio || $cierre)
        <span>{{ $inicio ? \Carbon\Carbon::parse($inicio)->translatedFormat('d M. Y') : '—' }}</span>
        <i class="fas fa-arrow-right fa-xs per-rango-flecha"></i>
        <span>{{ $cierre ? \Carbon\Carbon::parse($cierre)->translatedFormat('d M. Y') : '—' }}</span>
    @else
        <span class="per-rango-vacio">Sin fechas</span>
    @endif
</div>
