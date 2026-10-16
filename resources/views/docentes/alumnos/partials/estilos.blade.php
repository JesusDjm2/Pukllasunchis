{{-- Estilos de las listas de contacto de alumnos (docente por curso y tutor por ciclo).
     Mismo lenguaje que las tarjetas de /Dashboard-Docente: radios .65rem, chips redondeados,
     azul institucional. Los modos oscuro/tenue se resuelven aquí con variables locales. --}}
<style>
    .pk-al {
        --al-superficie: #fff;
        --al-superficie-2: #f7f8fc;
        --al-borde: #edf0f7;
        --al-texto: #2e3448;
        --al-suave: #858796;
        --al-acento: #4e73df;
        --al-acento-fondo: #eef1fb;
        --al-wa: #128c4a;
        --al-wa-fondo: #e7f8ee;
    }
    body.dark-mode .pk-al {
        --al-superficie: #23262f;
        --al-superficie-2: #1d2028;
        --al-borde: rgba(255, 255, 255, .08);
        --al-texto: #e6e8ef;
        --al-suave: #9aa0b0;
        --al-acento: #8ea6f0;
        --al-acento-fondo: rgba(142, 166, 240, .14);
        --al-wa: #5fd896;
        --al-wa-fondo: rgba(37, 211, 102, .12);
    }
    body.dim-mode .pk-al {
        --al-superficie: var(--lm-superficie);
        --al-superficie-2: var(--lm-superficie-2);
        --al-borde: var(--lm-borde);
        --al-texto: var(--lm-texto);
        --al-suave: var(--lm-suave);
        --al-acento: var(--lm-acento);
        --al-acento-fondo: var(--lm-acento-fondo);
        --al-wa: var(--lm-menta);
        --al-wa-fondo: var(--lm-menta-fondo);
    }

    /* ── Selector de cursos (columna izquierda) ── */
    .pk-al-cursos { display: flex; flex-direction: column; gap: .4rem; }
    .pk-al-curso {
        display: flex; align-items: center; gap: .65rem;
        width: 100%; text-align: left;
        padding: .7rem .8rem;
        border: 1px solid transparent; border-radius: .65rem;
        background: var(--al-superficie);
        box-shadow: 0 .1rem .9rem rgba(58, 59, 69, .07);
        color: var(--al-texto);
        transition: border-color .15s ease, transform .15s ease, box-shadow .15s ease;
    }
    .pk-al-curso:hover { transform: translateY(-1px); box-shadow: 0 .4rem 1.2rem rgba(58, 59, 69, .12); }
    .pk-al-curso:focus { outline: none; border-color: var(--al-acento); }
    .pk-al-curso.is-activo { border-color: var(--al-acento); background: var(--al-acento-fondo); }
    .pk-al-curso-nombre { font-weight: 700; font-size: .84rem; line-height: 1.25; }
    .pk-al-curso-meta { font-size: .72rem; color: var(--al-suave); }
    .pk-al-curso-num {
        margin-left: auto; flex-shrink: 0;
        min-width: 2rem; text-align: center;
        font-weight: 800; font-size: .75rem;
        padding: .2rem .5rem; border-radius: 999px;
        background: var(--al-acento-fondo); color: var(--al-acento);
    }
    .pk-al-curso.is-activo .pk-al-curso-num { background: var(--al-acento); color: #fff; }
    .pk-al-tipo {
        font-size: .6rem; font-weight: 800; letter-spacing: .05em;
        padding: .1rem .4rem; border-radius: 999px; vertical-align: middle;
        background: var(--al-superficie-2); color: var(--al-suave);
    }
    .pk-al-tipo i { font-size: .58rem; margin-right: .1rem; }
    /* FID = azul institucional, PPD = naranja (el mismo de la barra de progreso PPD del dashboard). */
    .pk-al-tipo.is-fid { background: rgba(78, 115, 223, .13); color: #2e4fb8; }
    .pk-al-tipo.is-ppd { background: rgba(229, 151, 58, .16); color: #b5651d; }
    body.dark-mode .pk-al-tipo.is-fid { background: rgba(142, 166, 240, .16); color: #a9bbf5; }
    body.dark-mode .pk-al-tipo.is-ppd { background: rgba(245, 180, 95, .16); color: #f5b45f; }
    body.dim-mode .pk-al-tipo.is-fid { background: var(--lm-lavanda-fondo); color: var(--lm-lavanda-claro); }
    body.dim-mode .pk-al-tipo.is-ppd { background: var(--lm-durazno-fondo); color: var(--lm-durazno); }

    /* ── Tarjeta del curso / ciclo ── */
    .pk-al-card {
        border: 0; border-radius: .65rem;
        background: var(--al-superficie);
        box-shadow: 0 .15rem 1.5rem rgba(58, 59, 69, .1);
        overflow: hidden;
    }
    .pk-al-head { padding: 1rem 1.15rem; border-bottom: 1px solid var(--al-borde); }
    .pk-al-titulo { font-size: 1.05rem; font-weight: 700; color: var(--al-texto); margin: 0; }
    .pk-al-subtitulo { font-size: .8rem; color: var(--al-suave); margin: .1rem 0 0; }
    .pk-al-stats { display: flex; flex-wrap: wrap; gap: .4rem; margin-top: .6rem; }
    .pk-al-stat {
        display: inline-flex; align-items: center; gap: .3rem;
        font-size: .72rem; font-weight: 600;
        padding: .25rem .65rem; border-radius: 999px;
        background: var(--al-superficie-2); color: var(--al-suave);
    }
    .pk-al-stat i { color: var(--al-acento); }

    .pk-al-toolbar {
        display: flex; flex-wrap: wrap; align-items: center; gap: .5rem;
        padding: .7rem 1.15rem; background: var(--al-superficie-2);
        border-bottom: 1px solid var(--al-borde);
    }
    .pk-al-buscar { position: relative; flex: 1 1 220px; max-width: 360px; }
    .pk-al-buscar i { position: absolute; left: .7rem; top: 50%; transform: translateY(-50%); color: var(--al-suave); font-size: .8rem; }
    .pk-al-buscar input {
        width: 100%; padding: .4rem .7rem .4rem 2rem;
        border: 1px solid var(--al-borde); border-radius: 999px;
        background: var(--al-superficie); color: var(--al-texto); font-size: .82rem;
    }
    .pk-al-buscar input:focus { outline: none; border-color: var(--al-acento); }
    .pk-al-todos { font-size: .78rem; font-weight: 600; color: var(--al-suave); margin: 0; cursor: pointer; user-select: none; }
    .pk-al-toolbar .btn { border-radius: 999px; font-weight: 600; font-size: .78rem; }

    /* ── Fila de alumno ── */
    .pk-al-lista { max-height: 68vh; overflow-y: auto; }
    .pk-al-fila {
        display: grid;
        grid-template-columns: auto 42px minmax(0, 1.1fr) minmax(0, 1.4fr) auto;
        align-items: center; gap: .85rem;
        padding: .7rem 1.15rem;
        transition: background-color .12s ease;
    }
    .pk-al-item { border-bottom: 1px solid var(--al-borde); }
    .pk-al-detalle { padding: .2rem 1.15rem 1rem; background: var(--al-superficie-2); }
    .pk-al-detalle .detalle-card { background: var(--al-superficie); border-color: var(--al-borde); }
    .pk-al-fila:hover { background: var(--al-superficie-2); }
    .pk-al-fila.is-elegido { background: var(--al-acento-fondo); }
    .pk-al-fila input[type=checkbox] { width: 1rem; height: 1rem; cursor: pointer; accent-color: var(--al-acento); }
    .pk-al-fila.sin-check { grid-template-columns: 42px minmax(0, 1.1fr) minmax(0, 1.4fr) auto; }

    .pk-al-avatar {
        width: 42px; height: 42px; border-radius: 50%; object-fit: cover; cursor: zoom-in;
        border: 2px solid var(--al-borde);
    }
    .pk-al-iniciales {
        width: 42px; height: 42px; border-radius: 50%;
        display: flex; align-items: center; justify-content: center;
        font-weight: 800; font-size: .8rem; letter-spacing: .02em;
        background: var(--al-acento-fondo); color: var(--al-acento);
    }
    .pk-al-nombre { font-weight: 700; font-size: .85rem; color: var(--al-texto); line-height: 1.25; }
    .pk-al-etiquetas { display: flex; flex-wrap: wrap; gap: .25rem; margin-top: .25rem; }
    .pk-al-etq { font-size: .64rem; font-weight: 700; padding: .12rem .45rem; border-radius: 999px; }
    .pk-al-etq.ok { background: rgba(28, 200, 138, .14); color: #0e9b6a; }
    .pk-al-etq.warn { background: rgba(246, 194, 62, .2); color: #a87c06; }
    .pk-al-etq.info { background: rgba(54, 185, 204, .15); color: #1f8a9a; }
    .pk-al-etq.muted { background: var(--al-superficie-2); color: var(--al-suave); }

    .pk-al-contacto { font-size: .78rem; line-height: 1.5; min-width: 0; }
    .pk-al-dato { display: flex; align-items: center; gap: .4rem; color: var(--al-texto); min-width: 0; }
    .pk-al-dato > i { width: .9rem; text-align: center; color: var(--al-suave); font-size: .72rem; flex-shrink: 0; }
    .pk-al-dato a { color: var(--al-acento); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pk-al-dato .pk-al-ref { font-size: .66rem; font-weight: 700; color: var(--al-suave); text-transform: uppercase; letter-spacing: .03em; }
    .pk-al-vacio { color: var(--al-suave); font-style: italic; }

    .pk-al-acciones { display: flex; gap: .35rem; justify-content: flex-end; }
    .pk-al-btn {
        display: inline-flex; align-items: center; justify-content: center; gap: .3rem;
        height: 32px; min-width: 32px; padding: 0 .6rem;
        border-radius: 999px; border: 0;
        font-size: .74rem; font-weight: 700;
        text-decoration: none !important;
        transition: transform .12s ease, filter .12s ease;
    }
    .pk-al-btn:hover { transform: translateY(-1px); filter: brightness(.96); }
    .pk-al-btn.correo { background: var(--al-acento-fondo); color: var(--al-acento); }
    .pk-al-btn.wa { background: var(--al-wa-fondo); color: var(--al-wa); }
    .pk-al-btn.tel { background: var(--al-superficie-2); color: var(--al-suave); }
    .pk-al-btn.mini { height: 24px; min-width: 24px; padding: 0 .4rem; font-size: .7rem; }

    .pk-al-sin-resultados { padding: 2.5rem 1rem; text-align: center; color: var(--al-suave); }
    .pk-al-sin-resultados i { font-size: 1.8rem; display: block; margin-bottom: .6rem; opacity: .5; }

    @media (max-width: 991.98px) {
        .pk-al-fila, .pk-al-fila.sin-check {
            grid-template-columns: auto 42px minmax(0, 1fr);
            row-gap: .45rem;
        }
        .pk-al-fila.sin-check { grid-template-columns: 42px minmax(0, 1fr); }
        .pk-al-fila .pk-al-contacto { grid-column: 2 / -1; }
        .pk-al-fila .pk-al-acciones { grid-column: 2 / -1; justify-content: flex-start; }
        .pk-al-fila.sin-check .pk-al-contacto,
        .pk-al-fila.sin-check .pk-al-acciones { grid-column: 1 / -1; }
        .pk-al-lista { max-height: none; }
    }
</style>
