{{-- Acciones de un registro ($item). $grande: botones con texto (registro publicado) o solo iconos. --}}
<div class="mk-acciones">
    @if ($item->url)
        <button type="button" class="al-btn al-btn-oro al-btn-sm {{ $grande ? '' : 'mk-btn-icono' }}" data-mk-descargar="{{ $item->id }}" title="Descargar el QR en PNG">
            <i class="fas fa-download"></i>{!! $grande ? ' Descargar QR' : '' !!}
        </button>
        <a href="{{ route('admin.minkarikuy.qr', $item) }}" class="al-btn al-btn-borde al-btn-sm {{ $grande ? '' : 'mk-btn-icono' }}" title="Afiche con el QR para imprimir o descargar">
            <i class="fas fa-print"></i>{!! $grande ? ' Afiche' : '' !!}
        </a>
        @unless ($grande)
            <button type="button" class="al-btn al-btn-borde al-btn-sm mk-btn-icono" data-mk-copiar="{{ $item->url }}" title="Copiar enlace">
                <i class="far fa-copy"></i><span class="d-none"></span>
            </button>
        @endunless
    @endif
    <a href="{{ route('admin.minkarikuy.edit', $item) }}" class="al-btn al-btn-petroleo al-btn-sm {{ $grande ? '' : 'mk-btn-icono' }}" title="Editar">
        <i class="fas fa-pen"></i>{!! $grande ? ' Editar' : '' !!}
    </a>
    <form action="{{ route('admin.minkarikuy.activar', $item) }}" method="POST">
        @csrf
        <button type="submit" class="al-btn al-btn-borde al-btn-sm {{ $grande ? '' : 'mk-btn-icono' }}"
            title="{{ $item->activo ? 'Quitar de la página web' : 'Publicar en la página web (reemplaza al publicado)' }}">
            <i class="fas {{ $item->activo ? 'fa-eye-slash' : 'fa-globe-americas' }}"></i>{!! $grande ? ($item->activo ? ' Quitar de la web' : ' Publicar') : '' !!}
        </button>
    </form>
    <form action="{{ route('admin.minkarikuy.destroy', $item) }}" method="POST"
        data-confirm="Se eliminará &quot;{{ $item->nombre }}&quot;{{ $item->activo ? ' y dejará de mostrarse en la página web' : '' }}." data-confirm-danger data-confirm-titulo="¿Eliminar este Mink'arikuy?">
        @csrf
        @method('DELETE')
        <button type="submit" class="al-btn al-btn-sm mk-eliminar {{ $grande ? '' : 'mk-btn-icono' }}" title="Eliminar">
            <i class="fas fa-trash-alt"></i>{!! $grande ? ' Eliminar' : '' !!}
        </button>
    </form>
</div>
