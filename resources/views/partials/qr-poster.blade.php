{{--
    Póster de QR reutilizable — Tutoría individual / Buzón de sugerencias.
    Variables esperadas: $tipo ('tutoria'|'sugerencia'), $titulo, $descripcion, $url,
    $contexto (opcional, ej. "Programa Inicial · Ciclo V").
--}}
@php
    $qrTipoInfo = [
        'tutoria' => [
            'icono'   => 'fa-hands-helping',
            'etiqueta' => 'Tutoría Individual',
        ],
        'sugerencia' => [
            'icono'   => 'fa-comment-dots',
            'etiqueta' => 'Buzón de Sugerencias',
        ],
    ][$tipo] ?? ['icono' => 'fa-qrcode', 'etiqueta' => 'Código QR'];
@endphp

<style>
    #qrPoster,
    #qrPoster * {
        -webkit-print-color-adjust: exact;
        print-color-adjust: exact;
        color-adjust: exact;
    }
    #qrPoster {
        --qr-gold: #cd9244;
        --qr-ink: #23262b;
        width: 100%;
        max-width: 640px;
        margin: 0 auto;
        background: #fff;
        border-radius: 20px;
        box-shadow: 0 1.25rem 3rem rgba(20, 20, 20, .14);
        overflow: hidden;
        font-family: 'Nunito', -apple-system, sans-serif;
        position: relative;
        border: 1px solid #eee1d1;
    }
    #qrPoster .qrp-band {
        background: linear-gradient(150deg, var(--qr-ink) 0%, #35383f 100%);
        padding: 2.4rem 2rem 1.9rem;
        text-align: center;
        position: relative;
    }
    #qrPoster .qrp-band::after {
        content: '';
        position: absolute; left: 0; right: 0; bottom: 0;
        height: 5px;
        background: linear-gradient(90deg, var(--qr-gold), #e8b978, var(--qr-gold));
    }
    #qrPoster .qrp-logo-chip {
        display: inline-flex;
        align-items: center;
        justify-content: center;
        width: 84px;
        height: 84px;
        border-radius: 50%;
        background: #fff;
        box-shadow: 0 .3rem .9rem rgba(0, 0, 0, .25);
        margin-bottom: 1rem;
        padding: .6rem;
    }
    #qrPoster .qrp-logo { max-width: 100%; max-height: 100%; }
    #qrPoster .qrp-kicker {
        display: inline-flex;
        align-items: center;
        gap: .4rem;
        color: var(--qr-gold);
        font-size: .72rem;
        font-weight: 800;
        letter-spacing: .2em;
        text-transform: uppercase;
        margin-bottom: .5rem;
    }
    #qrPoster .qrp-title {
        color: #fff;
        font-size: 1.7rem;
        font-weight: 800;
        margin: 0;
        line-height: 1.25;
    }
    #qrPoster .qrp-contexto {
        display: inline-block;
        margin-top: .8rem;
        padding: .3rem 1rem;
        border: 1px solid rgba(205, 146, 68, .55);
        background: rgba(205, 146, 68, .12);
        border-radius: 20px;
        color: #f1dfc4;
        font-size: .76rem;
        font-weight: 700;
        letter-spacing: .03em;
    }
    #qrPoster .qrp-body {
        padding: 2.4rem 2.4rem 1.6rem;
        text-align: center;
    }
    #qrPoster .qrp-desc {
        color: #5a5c69;
        font-size: .93rem;
        line-height: 1.65;
        max-width: 30rem;
        margin: 0 auto 1.9rem;
    }
    #qrPoster .qrp-tutor {
        display: block;
        width: fit-content;
        margin: 1.4rem auto 0;
        padding: .4rem 1.1rem;
        background: #f7f4ef;
        border: 1px solid #eee1d1;
        border-radius: 20px;
        color: var(--qr-ink);
        font-size: .82rem;
    }
    #qrPoster .qrp-tutor i {
        color: var(--qr-gold);
    }
    #qrPoster .qrp-qr-frame {
        display: inline-block;
        padding: 1.15rem;
        border: 2px solid var(--qr-gold);
        border-radius: 18px;
        position: relative;
        background: #fff;
    }
    #qrPoster .qrp-qr-frame::before,
    #qrPoster .qrp-qr-frame::after {
        content: '';
        position: absolute;
        width: 24px; height: 24px;
        border: 3px solid var(--qr-gold);
    }
    #qrPoster .qrp-qr-frame::before { top: -9px; left: -9px; border-right: none; border-bottom: none; border-radius: 4px 0 0 0; }
    #qrPoster .qrp-qr-frame::after  { bottom: -9px; right: -9px; border-left: none; border-top: none; border-radius: 0 0 4px 0; }
    #qrPoster .qrp-qr-frame svg { display: block; }
    #qrPoster .qrp-cta {
        margin-top: 1.5rem;
        font-weight: 800;
        color: var(--qr-ink);
        font-size: 1.02rem;
        letter-spacing: .01em;
    }
    #qrPoster .qrp-cta i { color: var(--qr-gold); }
    #qrPoster .qrp-footer {
        border-top: 1px solid #f1ece3;
        padding: 1.1rem 2rem;
        text-align: center;
        font-size: .7rem;
        color: #b3aa9c;
        letter-spacing: .04em;
        text-transform: uppercase;
        font-weight: 600;
    }

    @media print {
        @page { size: A4 portrait; margin: 15mm; }
        .d-print-none { display: none !important; }
        body * { visibility: hidden; }
        #qrPoster, #qrPoster * { visibility: visible; }
        #qrPoster {
            position: absolute;
            top: 0;
            left: 50%;
            transform: translateX(-50%);
            width: 170mm;
            max-width: 170mm;
            box-shadow: none !important;
        }
        #qrPoster .qrp-band { padding: 3rem 2.2rem 2.2rem; }
        #qrPoster .qrp-body { padding: 3rem 2.6rem 2rem; }
    }
</style>

@php
    $qrpArchivo = \Illuminate\Support\Str::slug(($qrTipoInfo['etiqueta'] ?? 'qr') . ' ' . ($contexto ?? ''));
@endphp

<div class="d-print-none text-center mb-4" style="display:flex;gap:.6rem;justify-content:center;flex-wrap:wrap;">
    <button type="button" class="btn btn-dark px-4" data-qrp-imprimir style="border-radius:2rem;font-weight:600;">
        <i class="fa fa-print mr-2"></i> Imprimir
    </button>
    <button type="button" id="qrpDescargarBtn" class="btn px-4"
        style="border-radius:2rem;font-weight:600;background:#cd9244;color:#fff;">
        <i class="fa fa-download mr-2"></i> Descargar imagen
    </button>
</div>

<div id="qrPoster">
    <div class="qrp-band">
        <span class="qrp-logo-chip">
            <img src="{{ asset('img/logo-iesp-pukllasunchis.png') }}" alt="Pukllasunchis" class="qrp-logo" crossorigin="anonymous">
        </span>
        <div class="qrp-kicker"><i class="fas {{ $qrTipoInfo['icono'] }}"></i> {{ $qrTipoInfo['etiqueta'] }}</div>
        <h1 class="qrp-title">{{ $titulo }}</h1>
        @if (!empty($contexto))
            <div><span class="qrp-contexto"><i class="fas fa-layer-group mr-1"></i> {{ $contexto }}</span></div>
        @endif
    </div>
    <div class="qrp-body">
        <p class="qrp-desc">{{ $descripcion }}</p>
        <div class="qrp-qr-frame">
            {!! QrCode::size(260)->margin(1)->color(205, 146, 68)->generate($url) !!}
        </div>
        <div class="qrp-cta"><i class="fas fa-mobile-alt mr-2"></i>Escanea con la cámara de tu celular</div>
        @if (!empty($tutor))
            <div class="qrp-tutor"><i class="fas fa-user-tie mr-1"></i>{{ \Illuminate\Support\Str::contains($tutor, ',') ? 'Tutores a cargo' : 'Tutor a cargo' }}: <strong>{{ $tutor }}</strong></div>
        @endif
    </div>
    <div class="qrp-footer">
        EESPP · Escuela de Educación Superior Pedagógica Pukllasunchis · {{ date('Y') }}
    </div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/html2canvas@1.4.1/dist/html2canvas.min.js"></script>
<script>
(function () {
    var imprimirBtn = document.querySelector('[data-qrp-imprimir]');
    if (imprimirBtn) {
        imprimirBtn.addEventListener('click', function () {
            window.print();
        });
    }

    var btn = document.getElementById('qrpDescargarBtn');
    if (!btn) return;

    btn.addEventListener('click', function () {
        var poster = document.getElementById('qrPoster');
        var original = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fa fa-spinner fa-spin mr-2"></i> Generando…';

        html2canvas(poster, {
            scale: 3,
            backgroundColor: '#ffffff',
            useCORS: true,
        }).then(function (canvas) {
            var link = document.createElement('a');
            link.download = 'qr-{{ $qrpArchivo }}.png';
            link.href = canvas.toDataURL('image/png');
            link.click();
        }).catch(function () {
            alert('No se pudo generar la imagen. Intenta imprimir en su lugar, o toma una captura de pantalla.');
        }).finally(function () {
            btn.disabled = false;
            btn.innerHTML = original;
        });
    });
})();
</script>
@endpush
