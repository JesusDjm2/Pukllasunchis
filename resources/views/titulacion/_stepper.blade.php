{{-- Indicador PTI -> TI (Bachiller) -> Tesis (Licenciatura), calculado de los
     datos registrados (App\Services\Titulacion\AvanceTitulacion): cada etapa se
     completa con su RD (PTI) o su grado inscrito en SUNEDU (TI, Tesis). Lo usan
     el panel de gestores (_show_contenido), la tarjeta del alumno y la intranet
     de egresado. Espera $seguimiento. --}}
@php
    $etapasAvance = $seguimiento->avance()->etapas();
@endphp
<div class="titulacion-stepper mb-3">
    @foreach ([['pti', 'PTI', '1'], ['ti', 'TI · Bachiller', '2'], ['tesis', 'Tesis · Licenciatura', '3']] as [$clave, $label, $n])
        @php $etapa = $etapasAvance[$clave]; @endphp
        <div class="titulacion-stepper__paso titulacion-stepper__paso--{{ $etapa['estado'] }}"
             title="{{ collect($etapa['hitos'])->map(fn ($h) => ($h['logrado'] ? '✓ ' : '○ ').$h['label'])->implode("\n") }}">
            <span class="titulacion-stepper__circulo">
                {{ $n }}
            </span>
            <span>
                <span class="titulacion-stepper__label">{{ $label }}</span>
                @if ($etapa['resumen'])
                    <span class="titulacion-stepper__resumen">{{ $etapa['resumen'] }}</span>
                @endif
            </span>
        </div>
        @if (! $loop->last) <span class="titulacion-stepper__linea"></span> @endif
    @endforeach
</div>

{{-- @once: este parcial se incluye una vez POR FILA en el acordeón de
     /titulacion (hasta 30 veces por página) — sin @once el <style> se
     repetiría igual de veces, puro peso muerto en el HTML. --}}
@once
    <style>
        .titulacion-stepper { display: flex; align-items: center; flex-wrap: wrap; row-gap: .5rem; }
        .titulacion-stepper__paso { display: flex; align-items: center; gap: .4rem; }
        .titulacion-stepper__circulo {
            width: 1.6rem; height: 1.6rem; border-radius: 50%;
            display: flex; align-items: center; justify-content: center;
            font-size: .72rem; font-weight: 700; flex-shrink: 0;
            background: #e9ecef; color: #6c757d; border: 2px solid #e9ecef;
        }
        .titulacion-stepper__label { display: block; font-size: .78rem; font-weight: 600; color: #6c757d; white-space: nowrap; line-height: 1.2; }
        .titulacion-stepper__resumen { display: block; font-size: .68rem; color: #8892a6; white-space: nowrap; line-height: 1.2; }
        .titulacion-stepper__linea { flex: 0 0 2rem; height: 2px; background: #e9ecef; margin: 0 .5rem; }
        .titulacion-stepper__paso--en_proceso .titulacion-stepper__circulo { background: #eef2ff; color: #4e73df; border-color: #4e73df; }
        .titulacion-stepper__paso--en_proceso .titulacion-stepper__label { color: #4e73df; }
        .titulacion-stepper__paso--completo .titulacion-stepper__circulo { background: #1cc88a; color: #fff; border-color: #1cc88a; }
        .titulacion-stepper__paso--completo .titulacion-stepper__label { color: #1cc88a; }
        .titulacion-etapa { border-left: 3px solid #e3e6f0; padding-left: .75rem; margin-bottom: 1rem; }
        .titulacion-etapa__titulo { font-size: .74rem; font-weight: 700; text-transform: uppercase; letter-spacing: .02em; color: #8892a6; margin-bottom: .5rem; }
    </style>
@endonce
