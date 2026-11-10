{{-- ── WIDGET DE FECHA Y HORA INSTITUCIONAL (CUSCO, PERÚ - GMT-5) ── --}}
<div class="topbar-clock-widget d-none d-md-inline-flex align-items-center ml-2"
     id="topbarClockWidget"
     title="Hora oficial de Cusco, Perú (GMT-5) · Sincronizada en tiempo real">
    <div class="topbar-clock-indicator">
        <i class="far fa-clock topbar-clock-icon"></i>
        <span class="topbar-clock-pulse"></span>
    </div>
    <div class="topbar-clock-body">
        <span class="topbar-clock-date" id="topbarClockDate">
            {{ \Carbon\Carbon::now('America/Lima')->locale('es')->isoFormat('ddd, D [de] MMM') }}
        </span>
        <span class="topbar-clock-sep">·</span>
        <span class="topbar-clock-time" id="topbarClockTime">
            {{ \Carbon\Carbon::now('America/Lima')->format('h:i:s A') }}
        </span>
        <span class="topbar-clock-zone d-none d-xl-inline-block">Cusco</span>
    </div>
</div>

<script>
    (function () {
        function actualizarRelojTopbar() {
            var dateEl = document.getElementById('topbarClockDate');
            var timeEl = document.getElementById('topbarClockTime');
            if (!dateEl || !timeEl) return;

            var ahora = new Date();
            var fechaStr = ahora.toLocaleDateString('es-PE', {
                timeZone: 'America/Lima',
                weekday: 'short',
                day: 'numeric',
                month: 'short'
            });

            // Normalizar: Primera letra de cada palabra en mayúscula y quitar puntos (ej: "Mar, 10 Nov")
            fechaStr = fechaStr.replace(/\b[a-záéíóú]/g, function (l) { return l.toUpperCase(); }).replace(/\./g, '');

            var horaStr = ahora.toLocaleTimeString('es-PE', {
                timeZone: 'America/Lima',
                hour: '2-digit',
                minute: '2-digit',
                second: '2-digit',
                hour12: true
            });

            dateEl.textContent = fechaStr;
            timeEl.textContent = horaStr;
        }

        if (document.readyState === 'loading') {
            document.addEventListener('DOMContentLoaded', function () {
                actualizarRelojTopbar();
                setInterval(actualizarRelojTopbar, 1000);
            });
        } else {
            actualizarRelojTopbar();
            setInterval(actualizarRelojTopbar, 1000);
        }
    })();
</script>
