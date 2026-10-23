{{-- Copiar enlace y descargar el QR como PNG (se dibuja el SVG en un canvas, sin depender de GD/Imagick). --}}
@push('scripts')
<script>
(function () {
    function copiar(texto) {
        if (navigator.clipboard && window.isSecureContext) return navigator.clipboard.writeText(texto);
        return new Promise(function (resolve, reject) {
            var area = document.createElement('textarea');
            area.value = texto; area.setAttribute('readonly', ''); area.style.position = 'fixed'; area.style.opacity = '0';
            document.body.appendChild(area); area.select();
            try { document.execCommand('copy') ? resolve() : reject(); } catch (e) { reject(e); }
            document.body.removeChild(area);
        });
    }

    document.querySelectorAll('[data-mk-copiar]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var icono = btn.querySelector('i'), texto = btn.querySelector('span');
            copiar(btn.dataset.mkCopiar).then(function () {
                icono.className = 'fas fa-check';
                if (texto && texto.textContent) texto.textContent = '¡Copiado!';
                setTimeout(function () { icono.className = 'far fa-copy'; if (texto && texto.textContent) texto.textContent = 'Copiar'; }, 1800);
            }).catch(function () { window.prompt('Copia este enlace:', btn.dataset.mkCopiar); });
        });
    });

    document.querySelectorAll('[data-mk-descargar]').forEach(function (btn) {
        btn.addEventListener('click', function () {
            var caja = document.querySelector('[data-mk-qr="' + btn.dataset.mkDescargar + '"]');
            var svg = caja && caja.querySelector('svg');
            if (!svg) return;

            var lado = 1200, margen = 60;
            var datos = new XMLSerializer().serializeToString(svg);
            var img = new Image();
            img.onload = function () {
                var canvas = document.createElement('canvas');
                canvas.width = canvas.height = lado;
                var ctx = canvas.getContext('2d');
                ctx.fillStyle = '#ffffff';
                ctx.fillRect(0, 0, lado, lado);
                ctx.imageSmoothingEnabled = false;
                ctx.drawImage(img, margen, margen, lado - margen * 2, lado - margen * 2);
                var a = document.createElement('a');
                a.download = 'qr-minkarikuy-' + (caja.dataset.mkNombre || btn.dataset.mkDescargar) + '.png';
                a.href = canvas.toDataURL('image/png');
                a.click();
            };
            img.src = 'data:image/svg+xml;charset=utf-8,' + encodeURIComponent(datos);
        });
    });
})();
</script>
@endpush
