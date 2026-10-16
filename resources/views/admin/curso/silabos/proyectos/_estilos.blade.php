{{-- Estilos propios del CRUD de Proyectos Integradores (sobre alumnos/partials/ui). --}}
@once
<style>
    .pi-descripcion { font-size: .88rem; color: var(--al-texto); line-height: 1.55; margin-bottom: .75rem; }
    .pi-descripcion-completa { font-size: .92rem; color: var(--al-texto); line-height: 1.65; }
    .pi-descripcion-completa p:last-child { margin-bottom: 0; }
    .pi-ciclos { display: flex; flex-wrap: wrap; gap: .3rem 1.2rem; font-size: .78rem; color: var(--al-suave); margin-bottom: .85rem; }
    .pi-ciclos b { color: var(--al-texto); font-weight: 700; }
    .pi-acciones { display: flex; flex-wrap: wrap; gap: .4rem; }
    .pi-acciones form { display: inline; }
    .pi-eliminar { background: transparent; color: var(--al-mal) !important; border-color: rgba(192, 57, 43, .3); }
    .pi-eliminar:hover { background: var(--al-mal-f); }

    /* Formulario */
    .pi-campo { margin-bottom: 1.1rem; }
    .pi-campo > label { font-size: .7rem; font-weight: 800; letter-spacing: .08em; text-transform: uppercase; color: var(--al-suave); margin-bottom: .35rem; display: block; }
    .pi-campo .form-control { border-radius: 10px; border-color: var(--al-borde); background: var(--al-fondo); color: var(--al-texto); }
    .pi-campo .form-control:focus { border-color: var(--al-oro); box-shadow: 0 0 0 .18rem rgba(205, 146, 68, .18); }
    .pi-campo small { color: var(--al-suave); }
    .pi-campo .is-invalid { border-color: var(--al-mal) !important; }
    .pi-campo .invalid-feedback { display: block; }
    .pi-programa { border: 1px solid var(--al-borde); border-radius: 12px; padding: .75rem .9rem; margin-bottom: .75rem; }
    .pi-programa-titulo { font-size: .78rem; font-weight: 800; color: var(--al-texto); margin-bottom: .5rem; }
    .pi-ciclos-opciones { display: flex; flex-wrap: wrap; gap: .4rem; }
    .pi-ciclo { position: relative; }
    .pi-ciclo input { position: absolute; opacity: 0; pointer-events: none; }
    .pi-ciclo label {
        margin: 0; cursor: pointer; display: inline-flex; flex-direction: column; align-items: flex-start; gap: .05rem;
        padding: .35rem .75rem; border-radius: 10px; border: 1px solid var(--al-borde); background: var(--al-fondo); font-size: .8rem; font-weight: 700; color: var(--al-texto);
    }
    .pi-ciclo label small { font-weight: 600; font-size: .64rem; color: var(--al-suave); }
    .pi-ciclo input:checked + label { background: var(--al-petroleo); border-color: var(--al-petroleo); color: #fff; }
    .pi-ciclo input:checked + label small { color: rgba(255, 255, 255, .75); }
    .pi-ciclo input:focus-visible + label { box-shadow: 0 0 0 3px rgba(51, 68, 90, .25); }
    .pi-ciclo label.is-otro small { color: var(--al-warn); }
    /* Docentes a cargo */
    .pi-docentes { border: 1px solid var(--al-borde); border-radius: 12px; overflow: hidden; }
    .pi-docentes-cab { display: flex; align-items: center; gap: .6rem; padding: .6rem .75rem; border-bottom: 1px solid var(--al-borde); background: var(--al-fondo); }
    .pi-docentes-cab .form-control { flex: 1; }
    .pi-docentes-cuenta { flex-shrink: 0; font-size: .74rem; font-weight: 800; color: var(--al-petroleo); background: rgba(51, 68, 90, .08); border-radius: 999px; padding: .25rem .65rem; }
    .pi-docentes-lista { max-height: 340px; overflow-y: auto; }
    .pi-docente { display: flex; align-items: center; justify-content: space-between; gap: .75rem; padding: .5rem .75rem; border-bottom: 1px solid var(--al-borde); }
    .pi-docente:last-child { border-bottom: 0; }
    .pi-docente.is-activo { background: rgba(205, 146, 68, .08); box-shadow: inset 3px 0 0 var(--al-oro); }
    .pi-docente-datos { min-width: 0; display: flex; flex-direction: column; }
    .pi-docente-datos strong { font-size: .84rem; color: var(--al-texto); }
    .pi-docente-datos small { font-size: .72rem; color: var(--al-suave); overflow: hidden; text-overflow: ellipsis; white-space: nowrap; }
    .pi-docente select { width: 150px; flex-shrink: 0; }
    .pi-equipo { display: flex; flex-direction: column; gap: .45rem; }
    .pi-equipo-fila { display: flex; align-items: center; justify-content: space-between; gap: .5rem; font-size: .84rem; color: var(--al-texto); }
    @media (max-width: 575px) { .pi-docente { flex-wrap: wrap; } .pi-docente select { width: 100%; } }
    .pi-barra { display: flex; justify-content: flex-end; gap: .5rem; padding: 1rem 1.25rem; border-top: 1px solid var(--al-borde); }
</style>
@endonce
