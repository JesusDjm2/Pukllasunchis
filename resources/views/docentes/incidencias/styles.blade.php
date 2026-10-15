<style>
    .inc-card {
        border: none;
        border-radius: .75rem;
        box-shadow: 0 2px 10px rgba(0,0,0,.07);
        transition: box-shadow .15s;
    }
    .inc-card:hover { box-shadow: 0 4px 18px rgba(0,0,0,.12); }

    .inc-alumno-name { font-weight: 700; font-size: .93rem; color: #3a3b45; }
    .inc-meta        { font-size: .76rem; color: #858796; }
    .inc-reporte     { font-size: .87rem; color: #444; white-space: pre-wrap; line-height: 1.55; }

    .inc-ciclo-badge {
        font-size: .68rem; font-weight: 700; letter-spacing: .04em;
        background: #eef2ff; color: #4e73df;
        border: 1px solid #c7d2fe;
        border-radius: 20px; padding: 2px 9px;
    }
    .inc-prog-badge {
        font-size: .68rem; font-weight: 600;
        background: #f0fdf4; color: #16a34a;
        border: 1px solid #bbf7d0;
        border-radius: 20px; padding: 2px 9px;
    }

    .inc-img-thumb {
        width: 72px; height: 72px; object-fit: cover;
        border-radius: .45rem; border: 1px solid #dee2e6;
        cursor: pointer; flex-shrink: 0;
        transition: transform .15s, box-shadow .15s;
    }
    .inc-img-thumb:hover { transform: scale(1.05); box-shadow: 0 2px 8px rgba(0,0,0,.18); }

    .inc-date-chip {
        font-size: .72rem; font-weight: 700; color: #6c757d;
        background: #f1f3f9; border-radius: 20px;
        padding: 3px 10px; white-space: nowrap;
    }

    .empty-state { padding: 4rem 1rem; }
    .empty-state i { font-size: 3rem; color: #dee2e6; display: block; margin-bottom: 1rem; }

    {{-- Ficha de detalle --}}
    .inc-foto {
        max-width: 100%; border-radius: .6rem; border: 1px solid #dee2e6;
        cursor: pointer;
    }
    .inc-section-label {
        font-size: .72rem; font-weight: 700; letter-spacing: .08em;
        text-transform: uppercase; color: #858796; margin-bottom: .35rem;
    }
    .inc-cascade-row { display: flex; gap: .75rem; flex-wrap: wrap; }
    .inc-cascade-row .form-group { flex: 1 1 180px; min-width: 160px; }
    .img-preview-wrap { position: relative; display: inline-block; }
    .img-preview-wrap img { max-height: 180px; border-radius: .5rem; border: 1px solid #dee2e6; }
    .img-preview-wrap .btn-remove-img {
        position: absolute; top: 4px; right: 4px;
        background: rgba(220,53,69,.85); color: #fff;
        border: none; border-radius: 50%; width: 22px; height: 22px;
        font-size: .75rem; line-height: 22px; text-align: center; cursor: pointer;
    }
    .inc-dato { padding: .6rem 0; border-bottom: 1px solid #f0f2f8; }
    .inc-dato:last-child { border-bottom: none; }
    .inc-dato-label {
        font-size: .68rem; font-weight: 700; letter-spacing: .05em;
        text-transform: uppercase; color: #858796; display: block; margin-bottom: .15rem;
    }
    .inc-dato-valor { font-size: .88rem; color: #3a3b45; }
    .inc-cuerpo { white-space: pre-wrap; line-height: 1.6; font-size: .9rem; color: #444; }

    .inc-estado {
        font-size: .7rem; font-weight: 700; letter-spacing: .04em;
        border-radius: 20px; padding: 3px 11px;
    }
    .inc-estado--pend { background: #fff7ed; color: #c2410c; border: 1px solid #fed7aa; }
    .inc-estado--aten { background: #eff6ff; color: #1d4ed8; border: 1px solid #bfdbfe; }
</style>
