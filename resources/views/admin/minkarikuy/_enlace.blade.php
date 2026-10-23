{{-- Enlace del registro con botón para copiarlo. --}}
@if ($item->url)
    <div class="mk-enlace">
        <i class="fas fa-link"></i>
        <a href="{{ $item->url }}" target="_blank" rel="noopener" title="{{ $item->url }}">{{ $item->url }}</a>
        <button type="button" class="al-btn al-btn-borde al-btn-sm" data-mk-copiar="{{ $item->url }}">
            <i class="far fa-copy"></i> <span>Copiar</span>
        </button>
    </div>
@endif
