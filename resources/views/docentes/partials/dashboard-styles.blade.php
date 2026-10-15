    <style>
        /* ── Alertas (SweetAlert2) con el mismo look de las tarjetas del dashboard:
           mismo radio, misma sombra suave y azul institucional (#4e73df). ── */
        .docente-swal-popup {
            border-radius: 0.65rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.12);
            font-family: inherit;
        }

        .docente-swal-popup .swal2-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #4b4f5c;
        }

        .docente-swal-popup .swal2-html-container {
            font-size: 0.9rem;
            color: #5a5c69;
        }

        .docente-swal-popup .swal2-confirm {
            border-radius: 0.4rem !important;
            font-weight: 600;
        }

        .docente-swal-popup .swal2-cancel {
            border-radius: 0.4rem !important;
            font-weight: 600;
        }

        .docente-swal-toast {
            border-radius: 0.5rem;
            box-shadow: 0 0.15rem 1.75rem 0 rgba(58, 59, 69, 0.12);
        }

        .docente-swal-toast .swal2-title {
            font-size: 0.85rem;
            font-weight: 600;
            color: #4b4f5c;
        }

        /* ── Hero / cabecera del dashboard docente ──
           El look de la tarjeta (fondo, sombra, acento izquierdo) viene de
           las clases globales .docente-ui-card .docente-ui-hero (definidas
           en layouts/docente.blade.php y reutilizadas por ui-header.blade.php
           en el resto de vistas del docente), así todas comparten un único
           diseño de encabezado. Aquí solo van los extras propios de esta
           página (saludo, badge de periodo, interruptor FID/PPD). ── */
        .docente-dash-hero h1 {
            font-size: 1.3rem;
            font-weight: 700;
            color: #4b4f5c;
            margin-bottom: 0.4rem;
        }

        .docente-dash-hero .badge-periodo {
            display: inline-block;
            background: #eef1fb;
            color: #2e3f8f;
            font-weight: 600;
            font-size: 0.75rem;
            padding: 0.35rem 0.7rem;
            border-radius: 999px;
        }

        .docente-dash-toggle {
            display: inline-flex;
            background: #f4f6fb;
            border-radius: 999px;
            padding: 0.25rem;
            gap: 0.25rem;
        }

        .docente-dash-toggle .btn {
            border: 0;
            border-radius: 999px;
            font-weight: 600;
            font-size: 0.8rem;
            padding: 0.42rem 1.05rem;
            color: #5a5c69;
            background: transparent;
            transition: background-color .18s ease, color .18s ease, box-shadow .18s ease;
        }

        .docente-dash-toggle .btn.active {
            background: #4e73df;
            color: #fff;
            box-shadow: 0 3px 8px rgba(78, 115, 223, 0.35);
        }

        .docente-dash-toggle .btn .badge {
            margin-left: 0.35rem;
            background: rgba(90, 92, 105, 0.12);
            color: #5a5c69;
        }

        .docente-dash-toggle .btn.active .badge {
            background: rgba(255, 255, 255, 0.25);
            color: #fff;
        }

        /* ── Tarjetas de curso ── */
        .docente-curso-card {
            border: 0;
            border-radius: 0.65rem;
            box-shadow: 0 0.15rem 1.5rem rgba(58, 59, 69, 0.1);
            transition: transform .18s ease, box-shadow .18s ease;
        }

        .docente-curso-card:hover {
            transform: translateY(-2px);
            box-shadow: 0 0.6rem 1.75rem rgba(58, 59, 69, 0.16);
        }

        .docente-curso-index {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 1.9rem;
            height: 1.9rem;
            border-radius: 50%;
            background: #eef1fb;
            color: #4e73df;
            font-weight: 800;
            font-size: 0.85rem;
            flex-shrink: 0;
        }

        .docente-curso-nombre {
            font-size: 1.05rem;
            font-weight: 700;
            color: #2e3448;
            margin: 0 0 0.15rem;
        }

        .docente-curso-meta {
            font-size: 0.82rem;
            color: #858796;
            margin: 0;
        }

        .docente-curso-stats {
            display: flex;
            flex-wrap: wrap;
            gap: 0.4rem;
            margin-top: 0.6rem;
        }

        .docente-curso-stat {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.72rem;
            font-weight: 600;
            color: #5a5c69;
            background: #f4f6fb;
            border-radius: 999px;
            padding: 0.25rem 0.65rem;
        }

        .docente-curso-stat i {
            color: #4e73df;
            font-size: 0.72rem;
        }

        .docente-curso-sections {
            margin-top: 1rem;
            padding-top: 1rem;
            border-top: 1px solid #edf0f7;
        }

        .docente-curso-section-title {
            display: flex;
            align-items: center;
            gap: 0.4rem;
            font-size: 0.72rem;
            letter-spacing: 0.03em;
            text-transform: uppercase;
            font-weight: 700;
            color: #858796;
            margin-bottom: 0.6rem;
        }

        .docente-curso-section-title i {
            color: #4e73df;
        }

        .docente-curso-status {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.7rem;
            font-weight: 700;
            padding: 0.15rem 0.55rem;
            border-radius: 999px;
            margin-left: auto;
        }

        .docente-curso-status.is-ok {
            background: rgba(28, 200, 138, 0.14);
            color: #0e9b6a;
        }

        .docente-curso-status.is-pending {
            background: rgba(246, 194, 62, 0.18);
            color: #b3860a;
        }

        .docente-curso-comp-chip {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            padding: 0.28rem 0.7rem;
            border-radius: 999px;
            background: #eef1fb;
            border: 1px solid rgba(78, 115, 223, 0.22);
            color: #2e3f8f !important;
            font-size: 0.76rem;
            font-weight: 600;
            text-decoration: none !important;
            margin: 0 0.3rem 0.3rem 0;
            transition: transform .15s ease, background-color .15s ease;
        }

        .docente-curso-comp-chip:hover {
            background: #dde3fa;
            transform: translateY(-1px);
        }

        .docente-curso-comp-chip.is-a-calificar {
            background: #fff6e6;
            border-color: rgba(217, 119, 6, 0.35);
            color: #9a5b00 !important;
        }

        .docente-curso-comp-chip.is-a-calificar:hover {
            background: #ffedc7;
        }

        .docente-comp-chip-star {
            color: #d97706;
            font-size: 0.68rem;
        }

        .docente-curso-empty-comp {
            font-size: 0.8rem;
            color: #a7abba;
            font-style: italic;
        }

        .docente-curso-silabo-actions,
        .docente-curso-classroom-form {
            font-size: 0.85rem;
        }

        .docente-curso-silabo-actions .btn,
        .docente-curso-classroom-form .btn {
            font-size: 0.78rem;
        }

        .docente-curso-silabo-hint {
            font-size: 0.78rem;
            color: #858796;
        }

        .docente-curso-file-btn {
            display: inline-flex;
            align-items: center;
            gap: 0.35rem;
        }

        .docente-curso-file-name {
            font-size: 0.72rem;
            color: #858796;
            margin-top: 0.3rem;
            word-break: break-all;
        }

        .docente-classroom-group .input-group-text {
            background-color: #f8f9fc;
            color: #9a9ca8;
            border-right: 0;
        }

        .docente-classroom-input {
            transition: background-color 0.25s ease, border-color 0.25s ease, box-shadow 0.25s ease;
        }

        .docente-classroom-input[readonly] {
            background-color: #f8f9fc;
            color: #5a5c69;
            cursor: default;
        }

        .docente-classroom-input:not([readonly]) {
            background-color: #fff;
            border-color: #4e73df;
            box-shadow: 0 0 0 0.15rem rgba(78, 115, 223, 0.12);
        }

        .docente-classroom-actions {
            display: flex;
            align-items: center;
        }

        .docente-classroom-hint {
            font-size: 0.68rem;
            color: #b7b9c8;
            margin-left: 0.6rem;
        }

        .docente-silabo-opcion {
            display: flex;
            flex-direction: column;
            align-items: flex-start;
            gap: 0.4rem;
            padding: 0.65rem 0.75rem;
            border: 1px solid #e3e6f0;
            border-radius: 0.5rem;
            margin-bottom: 0.5rem;
            background: #fbfbfe;
        }

        .docente-silabo-opcion-titulo {
            font-size: 0.82rem;
            font-weight: 700;
            color: #3a3b45;
        }

        .docente-silabo-opcion-desc {
            font-size: 0.72rem;
            color: #858796;
            line-height: 1.4;
        }

        .docente-silabo-opcion-divisor {
            display: flex;
            align-items: center;
            color: #b7b9c8;
            font-size: 0.68rem;
            font-weight: 700;
            text-transform: uppercase;
            margin: 0.35rem 0;
        }

        .docente-silabo-opcion-divisor::before,
        .docente-silabo-opcion-divisor::after {
            content: '';
            flex: 1;
            border-bottom: 1px dashed #e3e6f0;
        }

        .docente-silabo-opcion-divisor span {
            padding: 0 0.6rem;
        }

        .docente-silabo-pdf-nota {
            display: inline-flex;
            align-items: center;
            gap: 0.3rem;
            font-size: 0.68rem;
            color: #b78103;
            background: #fef6e3;
            padding: 0.2rem 0.55rem;
            border-radius: 20px;
            margin-top: 0.4rem;
        }

        .docente-curso-cabecera {
            display: grid;
            grid-template-columns: minmax(0, 1fr) auto minmax(0, 1fr);
            align-items: center;
            column-gap: 1.25rem;
            row-gap: 0.5rem;
            border-radius: 0.65rem;
            padding: 0.5rem 0.6rem;
            margin: -0.5rem -0.6rem 0.4rem -0.6rem;
            transition: background-color 0.3s ease, box-shadow 0.3s ease;
        }

        .docente-curso-cabecera:hover {
            background: linear-gradient(180deg, rgba(78, 115, 223, 0.05) 0%, rgba(78, 115, 223, 0.015) 100%);
            box-shadow: inset 0 -1px 0 rgba(78, 115, 223, 0.12);
        }

        .docente-curso-cabecera-info {
            min-width: 0;
        }

        .docente-curso-cabecera-progreso {
            justify-self: center;
            min-width: 0;
        }

        .docente-curso-cabecera-accion {
            justify-self: end;
            text-align: right;
            flex-shrink: 0;
        }

        .docente-cabecera-progreso-titulo {
            display: block;
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.045em;
            color: #b7b9c8;
            text-align: center;
            margin-bottom: 0.3rem;
        }

        .docente-cabecera-progreso-hint {
            display: inline-flex;
            align-items: center;
            font-size: 0.68rem;
            color: #b78103;
            background: #fef6e3;
            padding: 0.25rem 0.6rem;
            border-radius: 20px;
            white-space: nowrap;
        }

        .docente-cabecera-progreso-grid {
            display: flex;
            gap: 1.1rem;
        }

        .docente-cabecera-progreso-item {
            display: flex;
            flex-direction: column;
            align-items: center;
            gap: 0.2rem;
        }

        .docente-cabecera-progreso-label {
            font-size: 0.6rem;
            font-weight: 700;
            text-transform: uppercase;
            letter-spacing: 0.03em;
            white-space: nowrap;
        }

        .docente-cabecera-progreso-item .docente-cal-progress {
            width: 4.6rem;
        }

        .docente-cabecera-progreso-pct {
            font-size: 0.68rem;
            font-weight: 700;
            color: #5a5c69;
        }

        @media (max-width: 767.98px) {
            .docente-curso-cabecera {
                grid-template-columns: 1fr;
                justify-items: start;
            }

            .docente-curso-cabecera-progreso,
            .docente-curso-cabecera-accion {
                justify-self: start;
                text-align: left;
                width: 100%;
            }

            .docente-cabecera-progreso-titulo {
                text-align: left;
            }
        }

        .docente-btn-header-accion {
            position: relative;
            overflow: hidden;
            border-radius: 2rem;
            font-weight: 600;
            letter-spacing: 0.01em;
            padding-left: 1rem;
            padding-right: 1rem;
            box-shadow: 0 2px 6px rgba(78, 115, 223, 0.18);
            transition: transform 0.28s cubic-bezier(0.34, 1.56, 0.64, 1),
                box-shadow 0.28s ease, filter 0.28s ease;
        }

        .docente-btn-header-accion.btn-outline-primary {
            box-shadow: none;
        }

        .docente-btn-header-accion::after {
            content: '';
            position: absolute;
            top: 0;
            left: -75%;
            width: 45%;
            height: 100%;
            background: linear-gradient(115deg, transparent, rgba(255, 255, 255, 0.45), transparent);
            transform: skewX(-20deg);
            transition: left 0.6s ease;
            pointer-events: none;
        }

        .docente-btn-header-accion i {
            display: inline-block;
            transition: transform 0.3s ease;
        }

        .docente-btn-header-accion:hover {
            transform: translateY(-2px);
            filter: brightness(1.04);
        }

        .docente-btn-header-accion.btn-primary:hover {
            box-shadow: 0 10px 20px -4px rgba(78, 115, 223, 0.35);
        }

        .docente-btn-header-accion.btn-outline-primary:hover {
            box-shadow: 0 6px 14px -4px rgba(78, 115, 223, 0.22);
        }

        .docente-btn-header-accion:hover::after {
            left: 125%;
        }

        .docente-btn-header-accion:hover i {
            transform: rotate(-10deg) scale(1.12);
        }

        .docente-btn-header-accion:active {
            transform: translateY(0);
            filter: brightness(0.98);
        }

        .docente-cal-select-link {
            font-size: 0.7rem;
            color: #858796;
            text-decoration: underline;
        }

        .docente-cal-select-link:hover {
            color: #4e73df;
        }

        .docente-cal-progress {
            height: 7px;
            border-radius: 999px;
            overflow: hidden;
            background: rgba(0, 0, 0, 0.06);
        }

        .docente-cal-progress .progress-bar {
            transition: width 0.9s cubic-bezier(.22, 1, .36, 1);
        }

        .docente-cal-progress-p1 .progress-bar {
            background: linear-gradient(90deg, #4e73df, #6f8ff0);
        }

        .docente-cal-progress-p2 .progress-bar {
            background: linear-gradient(90deg, #1cc88a, #3ddba4);
        }

        .docente-cal-progress-p3 .progress-bar {
            background: linear-gradient(90deg, #36b9cc, #59d4e6);
        }

        .docente-cal-progress-ppd .progress-bar {
            background: linear-gradient(90deg, #e5973a, #f5b45f);
        }


        @media (prefers-reduced-motion: reduce) {
            .docente-cal-progress .progress-bar {
                transition: none;
            }
        }

        .docente-curso-classroom-form .form-control-sm {
            font-size: 0.82rem;
        }

        .docente-cal-empty {
            padding: 3rem 1rem;
            text-align: center;
        }

        .docente-cal-empty i {
            font-size: 2.25rem;
            color: #d1d7e6;
            margin-bottom: .75rem;
            display: block;
        }

        /* ── Tarjetas de resumen del dashboard ── */
        a.docente-dash-metrica {
            display: flex;
            align-items: center;
            gap: .75rem;
            height: 100%;
            padding: .8rem 1rem;
            border-radius: .7rem;
            color: inherit;
            text-decoration: none !important;
            transition: transform .15s ease, box-shadow .15s ease;
        }

        a.docente-dash-metrica:hover {
            transform: translateY(-2px);
            box-shadow: 0 .3rem 1.1rem rgba(58, 59, 69, .22) !important;
        }

        .docente-dash-metrica-icono {
            width: 40px;
            height: 40px;
            flex-shrink: 0;
            border-radius: .6rem;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #fff;
            font-size: 1rem;
        }

        .docente-dash-metrica-cuerpo {
            display: flex;
            flex-direction: column;
            line-height: 1.2;
            min-width: 0;
        }

        .docente-dash-metrica-valor {
            font-size: 1.3rem;
            font-weight: 700;
            color: #3a3b45;
        }

        .docente-dash-metrica-titulo {
            font-size: .78rem;
            font-weight: 700;
            color: #5a5c69;
            white-space: nowrap;
            overflow: hidden;
            text-overflow: ellipsis;
        }

        .docente-dash-metrica-detalle {
            font-size: .7rem;
            color: #858796;
        }

        body.dark-mode .docente-dash-metrica-valor,
        body.dim-mode .docente-dash-metrica-valor,
        body.dark-mode .docente-dash-metrica-titulo,
        body.dim-mode .docente-dash-metrica-titulo {
            color: #e3e6f0;
        }

        @media (max-width: 575.98px) {
            a.docente-dash-metrica {
                padding: .65rem .7rem;
                gap: .55rem;
            }

            .docente-dash-metrica-valor {
                font-size: 1.05rem;
            }

            .docente-dash-metrica-titulo {
                font-size: .7rem;
            }

            .docente-dash-metrica-icono {
                width: 34px;
                height: 34px;
                font-size: .85rem;
            }
        }
    </style>
