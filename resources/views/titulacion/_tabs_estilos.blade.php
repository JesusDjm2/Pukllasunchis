{{-- Pestañas solo con CSS (radios), compartidas por la vista del egresado/
     alumno (_avance_alumno) y la ficha del gestor (_show_contenido). Sin JS a
     propósito: /egresado no carga Bootstrap JS y en el panel el JS de popups ya
     dio problemas. Estructura esperada, todo hermano dentro de .mt-tabs:
       <input type="radio" class="mt-tabs__radio" value="CLAVE">  (uno por pestaña)
       <div class="mt-tabs__nav"> <label class="mt-tabs__tab mt-tabs__tab--CLAVE"> …
       <div class="mt-tabs__panel mt-tabs__panel--CLAVE"> …
     Para una clave nueva, agregarla a $clavesTabs. --}}
@once
    @php
        $clavesTabs = ['avance', 'datos', 'grupo', 'resumen', 'pti', 'avisos', 'tesis', 'actas', 'resoluciones', 'proceso', 'historial'];
    @endphp
    <style>
        .mt-tabs__radio { position: absolute; opacity: 0; pointer-events: none; }
        .mt-tabs__nav { display: flex; gap: .25rem; border-bottom: 1px solid #e3e6f0; margin-bottom: 1rem; overflow-x: auto; overflow-y: hidden; scrollbar-width: thin; }
        .mt-tabs__tab { margin: 0 0 -1px; padding: .5rem .85rem; font-size: .85rem; font-weight: 600; color: #6c757d; cursor: pointer; border: 1px solid transparent; border-radius: .45rem .45rem 0 0; white-space: nowrap; user-select: none; }
        .mt-tabs__tab:hover { color: #4e73df; }
        .mt-tabs__count { display: inline-block; min-width: 1.2rem; padding: 0 .3rem; margin-left: .2rem; border-radius: 999px; background: #e9ecef; font-size: .7rem; text-align: center; }
        .mt-tabs__count--alerta { background: #f6c23e; color: #5c4400; }
        .mt-tabs__panel { display: none; }
        @foreach ($clavesTabs as $clave)
            .mt-tabs__radio[value="{{ $clave }}"]:checked ~ .mt-tabs__panel--{{ $clave }} { display: block; }
            .mt-tabs__radio[value="{{ $clave }}"]:checked ~ .mt-tabs__nav .mt-tabs__tab--{{ $clave }} { color: #4e73df; background: #fff; border-color: #e3e6f0 #e3e6f0 #fff; }
        @endforeach
        .mt-tabs__radio:focus-visible ~ .mt-tabs__nav { outline: 2px solid #c7d2fe; outline-offset: 2px; }

        .mt-subtitulo { font-size: .72rem; font-weight: 700; text-transform: uppercase; letter-spacing: .03em; color: #8892a6; margin-bottom: .25rem; }
        .mt-datos { margin: 0; font-size: .85rem; }
        .mt-datos dt { font-size: .72rem; font-weight: 600; color: #8892a6; margin-top: .45rem; }
        .mt-datos dd { margin: 0; color: #1e293b; word-break: break-word; }
        .mt-grupo { display: grid; gap: .5rem; }
        .mt-grupo__item { display: flex; gap: .75rem; align-items: flex-start; padding: .6rem .75rem; border: 1px solid #e3e6f0; border-radius: .55rem; font-size: .88rem; flex-wrap: wrap; background: #fff; }
        .mt-grupo__item--yo { background: #f8f9fc; }
        .mt-grupo__avatar { width: 2.2rem; height: 2.2rem; border-radius: 50%; background: #eef2ff; color: #4e73df; font-weight: 700; font-size: .8rem; display: flex; align-items: center; justify-content: center; flex-shrink: 0; }

        .dark-mode .mt-tabs__tab, .dim-mode .mt-tabs__tab { background: transparent !important; }
        .dark-mode .mt-tabs__count, .dim-mode .mt-tabs__count { background: rgba(255, 255, 255, .14); color: inherit; }
        .dark-mode .mt-tabs__count--alerta { background: rgba(210, 153, 34, .3); color: #f2cc60; }
        .dim-mode .mt-tabs__count--alerta { background: rgba(245, 197, 144, .28); color: var(--lm-durazno, #f5c590); }
        .dark-mode .mt-tabs__nav, .dim-mode .mt-tabs__nav,
        .dark-mode .mt-grupo__item, .dim-mode .mt-grupo__item { border-color: rgba(255, 255, 255, .12); background: transparent; }
        .dark-mode .mt-grupo__item--yo, .dim-mode .mt-grupo__item--yo { background: rgba(255, 255, 255, .04); }
        .dark-mode .mt-datos dd, .dim-mode .mt-datos dd { color: inherit; }
    </style>
@endonce
