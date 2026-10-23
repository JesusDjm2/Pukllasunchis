{{--
    Guías de TI y Tesis — mismo contenido para FID y PPD; solo cambian las carátulas.
    Espera $modalidad = 'FID' | 'PPD'. Diseño compartido .al-ui (pk-ui.css) con pestañas (pk-tabs.js).
--}}
@include('alumnos.partials.ui')
@php
    $esPpd = $modalidad === 'PPD';
    $doc = fn ($archivo, $titulo, $img, $desc) => [
        'url' => asset('pdf/' . $archivo), 'titulo' => $titulo, 'img' => asset('img/min/' . $img), 'desc' => $desc,
        'tipo' => strtoupper(pathinfo($archivo, PATHINFO_EXTENSION)) === 'PDF' ? 'PDF' : 'Word',
    ];
    $secciones = [
        'ti' => ['Trabajo de investigación', 'fa-flask', 'Para el Bachiller: formato, carátula y guías del TI.', [
            $doc('FormatoTI.docx', 'Formato de TI', 'Trabajo-de-investigacion-EESPukllasunchis.png', 'Plantilla con márgenes, fuentes y estructura.'),
            $esPpd
                ? $doc('CaratulaTI-2025 PPD.docx', 'Carátula de TI (PPD)', 'caratula.png', 'Carátula oficial para Profesionalización.')
                : $doc('CaratulaTI-2025.docx', 'Carátula de TI (FID)', 'caratula.png', 'Carátula oficial para Formación Inicial.'),
            $doc('Guia-Trabajo-de-Investigacion.pdf', 'Guía de investigación', 'Trabajo-de-investigacion-EESPukllasunchis.png', 'Pasos y contenido de cada capítulo.'),
            $doc('Guia-de-Investigacion-2025.pdf', 'Guía de redacción', 'Tesis-Pukllasunchis.png', 'Normas de redacción y citas.'),
        ]],
        'tesis' => ['Tesis', 'fa-scroll', 'Para la Licenciatura: formato, carátula y guías de la tesis.', [
            $doc('FormatoTesis.docx', 'Formato de tesis', 'Trabajo-de-investigacion-EESPukllasunchis.png', 'Plantilla con márgenes, fuentes y estructura.'),
            $esPpd
                ? $doc('CaratulaTesis-2025-PPD.docx', 'Carátula de tesis (PPD)', 'caratula.png', 'Carátula oficial para Profesionalización.')
                : $doc('CaratulaTesis-2025.docx', 'Carátula de tesis (FID)', 'caratula.png', 'Carátula oficial para Formación Inicial.'),
            $doc('Guia-de-Elaboracion-de-Tesis.pdf', 'Guía de elaboración de tesis', 'Tesis-Pukllasunchis.png', 'Pasos y contenido de cada capítulo.'),
            $doc('Guia-de-Investigacion-2025.pdf', 'Guía de redacción', 'Bachillerato-Pukllasunchis.png', 'Normas de redacción y citas.'),
        ]],
    ];
    $videos = [
        ['FT-c2DfvNn8', 'Guía 1', 'Carátula'],
        ['HqDAIgnL538', 'Guía 2', 'Formato general'],
        ['d07cUzQLQwE', 'Guía 3', 'Resumen'],
        ['sBopJwQav-o', 'Guía 4', 'Títulos, subtítulos e índice'],
        ['58ODcJy84h8', 'Guía 5', 'Referencias'],
    ];
@endphp

<div class="al-ui">
    <section class="al-hero">
        <div class="al-hero-fila">
            <div class="al-hero-texto">
                <div class="al-kicker">{{ $esPpd ? 'Profesionalización docente' : 'Formación inicial docente' }} · Investigación</div>
                <h1>Guías de TI y Tesis</h1>
                <p class="al-hero-sub">Plantillas Word, guías en PDF y videos paso a paso para tu trabajo de investigación y tu tesis, según las normas institucionales.</p>
            </div>
        </div>
    </section>

    <div>
        <div class="al-tabs" role="tablist" aria-label="Guías" data-al-tabs>
            @foreach ($secciones as $clave => [$titulo, $icono])
                <button type="button" class="al-tab {{ $loop->first ? 'is-activa' : '' }}" role="tab" data-panel="{{ $clave }}">
                    <i class="fas {{ $icono }}"></i> {{ $titulo }}
                </button>
            @endforeach
            <button type="button" class="al-tab" role="tab" data-panel="videos">
                <i class="fas fa-play-circle"></i> Videos <span class="al-badge is-muted">{{ count($videos) }}</span>
            </button>
        </div>

        @foreach ($secciones as $clave => [$titulo, $icono, $intro, $items])
            <section class="al-panel" role="tabpanel" data-panel="{{ $clave }}">
                <p class="al-guias-intro">{{ $intro }}</p>
                <div class="al-guias">
                    @foreach ($items as $it)
                        <article class="al-guia">
                            <a href="{{ $it['url'] }}" target="_blank" rel="noopener" class="al-guia-mini" tabindex="-1" aria-hidden="true">
                                <img src="{{ $it['img'] }}" alt="" loading="lazy">
                                <span class="al-guia-tipo is-{{ strtolower($it['tipo']) }}">{{ $it['tipo'] }}</span>
                            </a>
                            <div class="al-guia-cuerpo">
                                <h3>{{ $it['titulo'] }}</h3>
                                <p>{{ $it['desc'] }}</p>
                                <a href="{{ $it['url'] }}" target="_blank" rel="noopener" class="al-btn al-btn-petroleo al-btn-sm">
                                    <i class="fas {{ $it['tipo'] === 'PDF' ? 'fa-eye' : 'fa-download' }}"></i> {{ $it['tipo'] === 'PDF' ? 'Ver guía' : 'Descargar' }}
                                </a>
                            </div>
                        </article>
                    @endforeach
                </div>
            </section>
        @endforeach

        <section class="al-panel" role="tabpanel" data-panel="videos">
            <p class="al-guias-intro">Videos paso a paso para aplicar correctamente cada formato en tu trabajo.</p>
            <div class="al-videos">
                @foreach ($videos as [$id, $guia, $tema])
                    <figure class="al-video">
                        <div class="al-video-marco">
                            <iframe src="https://www.youtube.com/embed/{{ $id }}" title="{{ $guia }}: {{ $tema }} | EESP Pukllasunchis"
                                loading="lazy" referrerpolicy="strict-origin-when-cross-origin"
                                allow="accelerometer; clipboard-write; encrypted-media; gyroscope; picture-in-picture; web-share" allowfullscreen></iframe>
                        </div>
                        <figcaption><span class="al-badge is-info">{{ $guia }}</span> {{ $tema }}</figcaption>
                    </figure>
                @endforeach
            </div>
        </section>
    </div>
</div>
