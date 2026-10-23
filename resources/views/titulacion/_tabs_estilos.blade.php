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
        $clavesTabs = ['avance', 'datos', 'grupo', 'resumen', 'pti', 'avisos', 'tesis', 'actas', 'resoluciones', 'proceso', 'historial', 'tramites'];
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
        .mt-grupo__avatar--foto { object-fit: cover; }
        .mt-grupo__cuerpo { flex: 1; min-width: 0; }
        .mt-grupo__cab { display: flex; align-items: flex-start; justify-content: space-between; gap: .5rem; }
        .mt-grupo__datos { list-style: none; margin: .35rem 0 0; padding: 0; display: flex; flex-wrap: wrap; gap: .2rem 1rem; font-size: .8rem; color: #4a5568; }
        .mt-grupo__datos li { display: inline-flex; align-items: center; gap: .35rem; min-width: 0; max-width: 100%; }
        .mt-grupo__datos i { width: .9rem; text-align: center; color: #94a3b8; }
        .mt-grupo__acciones { display: flex; flex-wrap: wrap; gap: .35rem; margin-top: .5rem; }
        .mt-grupo__btn { display: inline-flex; align-items: center; gap: .35rem; min-height: 2rem; padding: .25rem .7rem; border: 1px solid #e3e6f0; border-radius: 999px; font-size: .78rem; font-weight: 600; color: #4e73df; background: transparent; text-decoration: none !important; }
        .mt-grupo__btn:hover, .mt-grupo__btn:focus-visible { background: #eef2ff; border-color: #c7d2fe; }
        .mt-grupo__btn:focus-visible { outline: 2px solid #4e73df; outline-offset: 1px; }
        .mt-grupo__btn--wa { color: #fff; background: #25d366; border-color: #25d366; }
        .mt-grupo__btn--wa:hover, .mt-grupo__btn--wa:focus-visible { color: #fff; background: #1ebe5b; border-color: #1ebe5b; }
        .mt-grupo-barra { display: flex; flex-wrap: wrap; align-items: center; justify-content: space-between; gap: .5rem; margin-bottom: .6rem; }
        .mt-grupo-barra__acciones { display: flex; flex-wrap: wrap; gap: .35rem; }
        .mt-grupo-aviso { display: flex; gap: .5rem; align-items: flex-start; margin-bottom: .6rem; padding: .5rem .75rem; border: 1px solid #fde68a; border-radius: .5rem; background: #fffbeb; color: #92400e; font-size: .8rem; }
        .mt-grupo-aviso i { margin-top: .15rem; }
        .mt-grupo-privacidad { display: flex; gap: .4rem; align-items: flex-start; margin: .6rem 0 0; font-size: .74rem; color: #8892a6; }
        .mt-grupo-privacidad i { margin-top: .15rem; }
        @media (max-width: 575.98px) {
            .mt-grupo__btn { flex: 1 1 auto; justify-content: center; }
            .mt-grupo-barra__acciones, .mt-grupo-barra__acciones .btn { width: 100%; }
        }

        .dark-mode .mt-tabs__tab, .dim-mode .mt-tabs__tab { background: transparent !important; }
        .dark-mode .mt-tabs__count, .dim-mode .mt-tabs__count { background: rgba(255, 255, 255, .14); color: inherit; }
        .dark-mode .mt-tabs__count--alerta { background: rgba(210, 153, 34, .3); color: #f2cc60; }
        .dim-mode .mt-tabs__count--alerta { background: rgba(245, 197, 144, .28); color: var(--lm-durazno, #f5c590); }
        .dark-mode .mt-tabs__nav, .dim-mode .mt-tabs__nav,
        .dark-mode .mt-grupo__item, .dim-mode .mt-grupo__item { border-color: rgba(255, 255, 255, .12); background: transparent; }
        .dark-mode .mt-grupo__item--yo, .dim-mode .mt-grupo__item--yo { background: rgba(255, 255, 255, .04); }
        .dark-mode .mt-datos dd, .dim-mode .mt-datos dd { color: inherit; }
        .dark-mode .mt-grupo__datos, .dim-mode .mt-grupo__datos { color: inherit; }
        .dark-mode .mt-grupo__btn:not(.mt-grupo__btn--wa), .dim-mode .mt-grupo__btn:not(.mt-grupo__btn--wa) { border-color: rgba(255, 255, 255, .18); color: inherit; }
        .dark-mode .mt-grupo__btn:not(.mt-grupo__btn--wa):hover, .dim-mode .mt-grupo__btn:not(.mt-grupo__btn--wa):hover { background: rgba(255, 255, 255, .08); }
        .dark-mode .mt-grupo-aviso, .dim-mode .mt-grupo-aviso { background: rgba(246, 194, 62, .1); border-color: rgba(246, 194, 62, .35); color: #f6c23e; }
    </style>
@endonce
