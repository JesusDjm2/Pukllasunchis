{{-- QR generado del enlace del registro ($item). data-mk-qr lo usa "Descargar QR" (PNG). --}}
@if ($svg = $item->qrSvg($tamano ?? 220))
    <div class="mk-qr" data-mk-qr="{{ $item->id }}" data-mk-nombre="{{ \Illuminate\Support\Str::slug($item->nombre) }}">{!! $svg !!}</div>
@else
    <div class="mk-qr-vacio"><i class="fas fa-qrcode"></i>Sin enlace</div>
@endif
