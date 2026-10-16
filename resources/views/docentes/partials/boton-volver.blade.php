{{-- Botón "Volver" del panel docente: bien visible (antes era un outline gris que se perdía).
     Espera: $url, $label. --}}
@once
    <style>
        .docente-ui-volver {
            display: inline-flex;
            align-items: center;
            gap: .55rem;
            padding: .4rem 1rem .4rem .4rem;
            border-radius: 999px;
            background: #4e73df;
            color: #fff !important;
            font-weight: 700;
            font-size: .82rem;
            text-decoration: none !important;
            box-shadow: 0 4px 12px rgba(78, 115, 223, .32);
            transition: background-color .15s ease, transform .15s ease, box-shadow .15s ease;
        }
        .docente-ui-volver-icono {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.75rem;
            height: 1.75rem;
            border-radius: 50%;
            background: rgba(255, 255, 255, .22);
            transition: transform .15s ease;
        }
        .docente-ui-volver:hover,
        .docente-ui-volver:focus {
            background: #2e59d9;
            box-shadow: 0 6px 16px rgba(78, 115, 223, .42);
            outline: none;
        }
        .docente-ui-volver:hover .docente-ui-volver-icono { transform: translateX(-3px); }
        .docente-ui-volver:focus-visible { box-shadow: 0 0 0 .2rem rgba(78, 115, 223, .45); }
        body.dark-mode .docente-ui-volver { background: #5b7fe6; box-shadow: none; }
        body.dim-mode .docente-ui-volver { background: var(--lm-acento-intenso); box-shadow: none; }
        body.dim-mode .docente-ui-volver:hover { background: var(--lm-acento); }
        @media (max-width: 767.98px) {
            .docente-ui-volver { width: 100%; justify-content: center; }
        }
    </style>
@endonce
<a href="{{ $url }}" class="docente-ui-volver">
    <span class="docente-ui-volver-icono"><i class="fas fa-arrow-left"></i></span>{{ $label }}
</a>
