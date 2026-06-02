@extends('layouts.home')
@section('metas')
    <title>Nuestra Plana Docente - EESPP Pukllasunchis</title>
    <meta name="description" content="Conoce al equipo de docentes de la Escuela de Educación Superior Pedagógica Privada Pukllasunchis.">
    <meta name="keywords" content="plana docente, docentes, EESP Pukllasunchis, educación intercultural">
@endsection
@section('contenido')

<style>
    .bradcam_area.plana-docente-bg {
        background-image: url('{{ asset('img/fondos/plana-docente-2.png') }}') !important;
        background-size: cover !important;
        background-position: center !important;
    }

    /* ── Grid de docentes ── */
    .pd-section { padding: 4rem 0 5rem; background: #f8f9fc; }

    .pd-card {
        background: #fff;
        border-radius: 1rem;
        box-shadow: 0 4px 24px rgba(13,33,55,.09);
        overflow: hidden;
        display: flex;
        flex-direction: column;
        height: 100%;
        transition: transform .25s ease, box-shadow .25s ease;
    }
    .pd-card:hover {
        transform: translateY(-5px);
        box-shadow: 0 12px 40px rgba(13,33,55,.16);
    }

    /* Foto / Avatar */
    .pd-photo-wrap {
        position: relative;
        height: 220px;
        overflow: hidden;
        background: linear-gradient(135deg, #0d2137, #1a4a6e);
    }
    .pd-photo-wrap img {
        width: 100%; height: 100%;
        object-fit: cover; object-position: top;
        display: block;
    }
    .pd-avatar-ph {
        width: 100%; height: 100%;
        display: flex; align-items: center; justify-content: center;
    }
    .pd-avatar-ph span {
        width: 88px; height: 88px; border-radius: 50%;
        background: rgba(255,255,255,.12);
        border: 3px solid rgba(232,184,75,.5);
        display: flex; align-items: center; justify-content: center;
        font-size: 2rem; font-weight: 800; color: #e8b84b;
        letter-spacing: .02em;
    }
    .pd-photo-tag {
        position: absolute; bottom: .75rem; left: .75rem;
        background: rgba(13,33,55,.82);
        color: #e8b84b;
        font-size: .67rem; font-weight: 700;
        letter-spacing: .1em; text-transform: uppercase;
        padding: .2rem .65rem; border-radius: 2rem;
        backdrop-filter: blur(4px);
    }

    /* Body */
    .pd-body { padding: 1.4rem 1.5rem 1.6rem; display: flex; flex-direction: column; flex: 1; }
    .pd-nombre {
        font-size: 1.08rem; font-weight: 800;
        color: #0d2137; line-height: 1.3;
        margin-bottom: .45rem;
    }
    .pd-chips { display: flex; flex-wrap: wrap; gap: .35rem; margin-bottom: .9rem; }
    .pd-chip {
        font-size: .67rem; font-weight: 700;
        letter-spacing: .06em; text-transform: uppercase;
        padding: .18rem .65rem; border-radius: 2rem;
    }
    .pd-chip-cargo    { background: #e8f0fe; color: #1a56db; }
    .pd-chip-esp      { background: #fef3c7; color: #92400e; }
    .pd-descripcion {
        font-size: .875rem; color: #4b5563;
        line-height: 1.65; flex: 1;
        margin-bottom: 1.2rem;
    }

    /* Botón CV */
    .pd-btn-cv {
        display: inline-flex; align-items: center; gap: .45rem;
        background: linear-gradient(135deg, #c9873f 0%, #e8b84b 100%);
        color: #0d2137 !important;
        font-weight: 800; font-size: .82rem;
        letter-spacing: .03em;
        padding: .6rem 1.4rem; border-radius: 3rem;
        border: none; cursor: pointer;
        box-shadow: 0 4px 16px rgba(201,135,63,.35);
        transition: transform .2s ease, box-shadow .2s ease, filter .2s ease;
        text-decoration: none !important;
        align-self: flex-start;
        position: relative; overflow: hidden;
    }
    .pd-btn-cv::before {
        content: '';
        position: absolute; top: 0; left: -100%; width: 60%; height: 100%;
        background: linear-gradient(90deg, transparent, rgba(255,255,255,.28), transparent);
        transition: left .4s ease;
    }
    .pd-btn-cv:hover { transform: translateY(-2px); box-shadow: 0 8px 24px rgba(201,135,63,.45); filter: brightness(1.06); }
    .pd-btn-cv:hover::before { left: 160%; }
    .pd-btn-cv.disabled-cv {
        background: #e5e7eb; color: #9ca3af !important;
        box-shadow: none; cursor: default;
    }
    .pd-btn-cv.disabled-cv:hover { transform: none; filter: none; box-shadow: none; }

    /* ── Modal CV ── */
    #modalCV .modal-content { border-radius: .85rem; overflow: hidden; border: none; }
    #modalCV .modal-header {
        background: linear-gradient(135deg, #0d2137, #1a4a6e);
        color: #fff; border: none; padding: 1rem 1.5rem;
    }
    #modalCV .modal-title { font-weight: 700; font-size: 1rem; }
    #modalCV .modal-header .close { color: #e8b84b; opacity: 1; text-shadow: none; font-size: 1.4rem; }
    #modalCV .modal-body { padding: 0; background: #f1f5f9; }
    #cv-iframe { width: 100%; height: 78vh; border: none; display: block; }
    #cv-no-disponible {
        display: none;
        padding: 3rem; text-align: center; color: #6b7280;
    }
    #cv-no-disponible svg { margin-bottom: 1rem; }
</style>

{{-- Breadcrumb --}}
<div class="bradcam_area bradcam_overlay plana-docente-bg">
    <div class="container">
        <div class="row">
            <div class="col-lg-12">
                <div class="bradcam_text">
                    <h3 class="tituloAnimado">Nuestra Plana Docente</h3>
                    <p class="slideUp">
                        <a href="{{ route('index') }}">Inicio /</a>
                        <a href="{{ route('informacion') }}"> Información Institucional /</a>
                        Plana Docente
                    </p>
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Grid de docentes --}}
<section class="pd-section">
    <div class="container">
        <div class="row">
            @forelse ($docentes as $i => $doc)
                @php
                    $iniciales = collect(explode(' ', $doc['nombre']))
                        ->map(fn($p) => strtoupper(substr($p, 0, 1)))
                        ->take(2)->implode('');
                @endphp
                <div class="col-lg-4 col-md-6 mb-4 pd-card-col">
                    <div class="pd-card">

                        {{-- Foto o avatar --}}
                        <div class="pd-photo-wrap">
                            @if (!empty($doc['foto']))
                                <img src="{{ asset($doc['foto']) }}" alt="{{ $doc['nombre'] }}">
                            @else 
                                <div class="pd-avatar-ph">
                                    <span>{{ $iniciales }}</span>
                                </div>
                            @endif
                            {{-- <span class="pd-photo-tag">Docente</span> --}}
                        </div>

                        {{-- Info --}}
                        <div class="pd-body">
                            <h3 class="pd-nombre">{{ $doc['nombre'] }}</h3>
                            <div class="pd-chips">
                                <span class="pd-chip pd-chip-cargo">{{ $doc['cargo'] }}</span>
                                <span class="pd-chip pd-chip-esp">{{ $doc['especialidad'] }}</span>
                            </div>
                            <p class="pd-descripcion">{{ $doc['descripcion'] }}</p>

                            @if (!empty($doc['cv']))
                                <button class="pd-btn-cv"
                                    onclick="abrirCV('{{ asset($doc['cv']) }}', '{{ $doc['nombre'] }}')">
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                        <line x1="16" y1="13" x2="8" y2="13"/>
                                        <line x1="16" y1="17" x2="8" y2="17"/>
                                    </svg>
                                    Ver CV
                                </button>
                            @else
                                <button class="pd-btn-cv disabled-cv" disabled>
                                    <svg width="15" height="15" viewBox="0 0 24 24" fill="none"
                                        stroke="currentColor" stroke-width="2.5"
                                        stroke-linecap="round" stroke-linejoin="round">
                                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                                        <polyline points="14 2 14 8 20 8"/>
                                    </svg>
                                    CV no disponible
                                </button>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-12 text-center py-5">
                    <p class="text-muted">No hay docentes registrados.</p>
                </div>
            @endforelse
        </div>
    </div>
</section>

{{-- Modal para ver CV en PDF --}}
<div class="modal fade" id="modalCV" tabindex="-1" role="dialog" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <h5 class="modal-title text-white" id="modalCVLabel">Curriculum Vitae</h5>
                <button type="button" class="close" data-dismiss="modal" aria-label="Cerrar">
                    <span aria-hidden="true">&times;</span>
                </button>
            </div>
            <div class="modal-body">
                <iframe id="cv-iframe" src="" title="CV Docente"></iframe>
                <div id="cv-no-disponible">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none"
                        stroke="#d1d5db" stroke-width="1.5" stroke-linecap="round" stroke-linejoin="round">
                        <path d="M14 2H6a2 2 0 00-2 2v16a2 2 0 002 2h12a2 2 0 002-2V8z"/>
                        <polyline points="14 2 14 8 20 8"/>
                    </svg>
                    <p>El CV de este docente aún no está disponible.</p>
                </div>
            </div>
        </div>
    </div>
</div>


<script>
(function () {
    if (typeof gsap === 'undefined' || typeof ScrollTrigger === 'undefined') return;
    if (window.matchMedia('(prefers-reduced-motion: reduce)').matches) return;

    gsap.registerPlugin(ScrollTrigger);

    gsap.from('.pd-card-col', {
        y: 50,
        opacity: 0,
        duration: 0.7,
        stagger: 0.12,
        ease: 'power3.out',
        scrollTrigger: {
            trigger: '.pd-section',
            start: 'top 82%',
        }
    });

}());

function abrirCV(url, nombre) {
    document.getElementById('modalCVLabel').textContent = 'CV — ' + nombre;
    if (url) {
        document.getElementById('cv-iframe').src = url;
        document.getElementById('cv-iframe').style.display = 'block';
        document.getElementById('cv-no-disponible').style.display = 'none';
    } else {
        document.getElementById('cv-iframe').src = '';
        document.getElementById('cv-iframe').style.display = 'none';
        document.getElementById('cv-no-disponible').style.display = 'block';
    }
    $('#modalCV').modal('show');
}

document.getElementById('modalCV').addEventListener('hidden.bs.modal', function () {
    document.getElementById('cv-iframe').src = '';
});
</script>

@endsection
