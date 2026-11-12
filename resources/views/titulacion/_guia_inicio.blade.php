{{-- "Tu proceso de titulación" para el alumno que YA puede iniciar (FID desde el Ciclo IX,
     PPD vigente) pero todavía no tiene proceso registrado: qué etapas tiene y en qué orden
     (los mismos pasos que AvanceTitulacion usa para calcular el avance), y a quién acudir.
     Cuando el gestor inicia su proceso, la intranet muestra en su lugar _avance_alumno. --}}
@php
    $vacio = new \App\Models\Titulacion\SeguimientoTitulacion();
    foreach (['ptis' => collect(), 'resoluciones' => collect(), 'actas' => collect(), 'pti' => null, 'tesis' => null] as $rel => $valor) {
        $vacio->setRelation($rel, $valor);
    }
    $etapasGuia = (new \App\Services\Titulacion\AvanceTitulacion($vacio))->lineaDeTiempo();
    $descripcionEtapa = [
        'pti' => 'Plan de tu investigación: lo inscribes y la Dirección lo aprueba con una RD. Tiene una vigencia de '.\App\Services\Titulacion\VigenciaPti::ANIOS_VIGENCIA.' años.',
        'ti' => 'Desarrollas y expones tu Trabajo de Investigación; con su aprobación obtienes el grado de Bachiller.',
        'tesis' => 'Sustentas tu Tesis; con su aprobación obtienes el Título de Licenciado(a).',
    ];
@endphp
<div class="tg-guia mb-3">
    <div class="tg-guia__head">
        <span class="tg-guia__icono"><i class="fas fa-user-graduate"></i></span>
        <div>
            <div class="tg-guia__titulo">Tu proceso de titulación</div>
            <div class="tg-guia__sub">Ya puedes iniciarlo. Estos son los pasos, en orden: cada uno se habilita cuando se registra el anterior.</div>
        </div>
    </div>
    {{-- Paso 1: la solicitud de aprobación del PTI (MiSolicitudPtiController), con el estado de la última enviada. --}}
    @php
        $solicitudPti = auth()->check() ? \App\Models\Titulacion\Solicitud::ptiDe(auth()->user())->first() : null;
        [$spTexto, $spBoton] = match ($solicitudPti?->estado) {
            'recibida' => ['El Área de Investigación recibió tu solicitud del '.$solicitudPti->created_at->format('d/m/Y').' y la está revisando.', 'Ver mi solicitud'],
            'observada' => ['El Área de Investigación observó tu solicitud: corrígela y envíala de nuevo.', 'Corregir y enviar'],
            'atendida' => ['Tu solicitud fue atendida: pronto verás aquí tu proceso de titulación.', 'Ver mi solicitud'],
            default => ['inscribir tu Plan de Trabajo de Investigación (PTI): envía tu solicitud de aprobación con tu PTI y el comprobante de pago.', 'Solicitar aprobación del PTI'],
        };
    @endphp
    <div class="tg-guia__inicio d-flex align-items-center flex-wrap" style="gap:.5rem;">
        <span class="flex-grow-1"><i class="fas fa-flag mr-1"></i><strong>{{ $solicitudPti ? 'Tu solicitud de PTI:' : 'Tu primer paso:' }}</strong> {{ $spTexto }}</span>
        <a href="{{ route('mi-titulacion.solicitud-pti') }}" class="btn btn-sm btn-primary">{{ $spBoton }} <i class="fas fa-arrow-right fa-xs ml-1"></i></a>
    </div>
    @include('titulacion._tramites_ayuda', ['bloque' => \App\Services\Titulacion\GuiaTramites::bloque('pti'), 'clavePersona' => 'u'.auth()->id()])
    <ol class="tg-guia__etapas">
        @foreach ($etapasGuia as $clave => $etapa)
            <li class="tg-guia__etapa">
                <div class="tg-guia__etapa-t"><span class="tg-guia__n">{{ $loop->iteration }}</span>{{ $etapa['titulo'] }}</div>
                @isset($descripcionEtapa[$clave])
                    <div class="tg-guia__desc">{{ $descripcionEtapa[$clave] }}</div>
                @endisset
                <ul class="tg-guia__pasos">
                    @foreach ($etapa['hitos'] as $hito)
                        <li><i class="far fa-circle fa-xs mr-1"></i>{{ rtrim($hito['falta'], '.') }}</li>
                    @endforeach
                </ul>
            </li>
        @endforeach
    </ol>
    <div class="tg-guia__contacto">
        <i class="fas fa-hands-helping mr-1"></i><strong>Unidad de Investigación</strong>
        · <i class="fab fa-whatsapp mr-1"></i><a href="https://wa.me/51972577170" target="_blank" rel="noopener" title="Escribir por WhatsApp">+51 972 577 170</a>
        · <i class="fas fa-map-marker-alt fa-xs mr-1"></i>Siete Diablitos 222, San Blas, Cusco
    </div>
</div>
@once
    <style>
        .tg-guia { border: 1px solid #c7d2fe; border-radius: .8rem; background: #fff; padding: 1rem 1.1rem; }
        .tg-guia__head { display: flex; gap: .8rem; align-items: center; margin-bottom: .75rem; }
        .tg-guia__icono { width: 2.4rem; height: 2.4rem; border-radius: 50%; background: #4e73df; color: #fff; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }
        .tg-guia__titulo { font-weight: 800; font-size: 1.05rem; color: #1e293b; }
        .tg-guia__sub { font-size: .82rem; color: #64748b; }
        .tg-guia__inicio { font-size: .86rem; padding: .55rem .75rem; border-radius: .55rem; background: #eef2ff; color: #3730a3; margin-bottom: .85rem; }
        .tg-guia__etapas { list-style: none; padding: 0; margin: 0; display: grid; grid-template-columns: repeat(auto-fit, minmax(14rem, 1fr)); gap: .75rem; }
        .tg-guia__etapa { border: 1px solid #e3e6f0; border-radius: .6rem; padding: .65rem .75rem; }
        .tg-guia__etapa-t { font-weight: 700; font-size: .9rem; color: #1e293b; display: flex; align-items: center; gap: .45rem; }
        .tg-guia__n { width: 1.4rem; height: 1.4rem; border-radius: 50%; background: #e0e7ff; color: #4338ca; font-size: .72rem; font-weight: 800; display: inline-flex; align-items: center; justify-content: center; }
        .tg-guia__desc { font-size: .76rem; color: #64748b; margin: .3rem 0 .4rem; }
        .tg-guia__pasos { list-style: none; padding: 0; margin: 0; font-size: .82rem; color: #334155; }
        .tg-guia__pasos li { padding: .12rem 0; }
        .tg-guia__pasos i { color: #94a3b8; }
        .tg-guia__contacto { font-size: .8rem; color: #64748b; margin-top: .85rem; }
        .dark-mode .tg-guia, .dim-mode .tg-guia { background: rgba(255, 255, 255, .03); border-color: rgba(165, 180, 252, .3); }
        .dark-mode .tg-guia__titulo, .dim-mode .tg-guia__titulo, .dark-mode .tg-guia__etapa-t, .dim-mode .tg-guia__etapa-t, .dark-mode .tg-guia__pasos, .dim-mode .tg-guia__pasos { color: inherit; }
        .dark-mode .tg-guia__inicio, .dim-mode .tg-guia__inicio { background: rgba(165, 180, 252, .12); color: #c7d2fe; }
        .dark-mode .tg-guia__etapa, .dim-mode .tg-guia__etapa { border-color: rgba(255, 255, 255, .12); }
    </style>
@endonce
