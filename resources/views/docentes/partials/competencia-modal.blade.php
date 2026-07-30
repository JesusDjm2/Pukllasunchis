{{-- Popup de detalle de competencia (nombre, descripción, capacidades).
     Cualquier elemento con data-nombre/data-descripcion/data-capacidades y
     onclick="openModal(this)" abre este mismo popup — diseño único y
     reutilizado en todas las vistas del docente (Calificar FID/PPD, Mis
     cursos, etc.) para que no se vea como pantallas de distintos diseños. --}}
<style>
    .docente-competencia-modal-overlay {
        display: none;
        position: fixed;
        inset: 0;
        z-index: 2000;
        background: rgba(11, 18, 32, 0.78);
        align-items: center;
        justify-content: center;
        padding: 1rem;
    }

    .docente-competencia-modal-overlay.is-open {
        display: flex;
    }

    .docente-competencia-modal-card {
        width: min(92vw, 620px);
        max-height: 85vh;
        overflow-y: auto;
        background: #fff;
        border-radius: 0.75rem;
        box-shadow: 0 1.25rem 3rem rgba(0, 0, 0, 0.3);
    }

    .docente-competencia-modal-head {
        display: flex;
        align-items: flex-start;
        justify-content: space-between;
        gap: 1rem;
        padding: 1.1rem 1.3rem;
        background: linear-gradient(120deg, #2b3a8f 0%, #4e73df 100%);
        color: #fff;
        border-top-left-radius: 0.75rem;
        border-top-right-radius: 0.75rem;
    }

    .docente-competencia-modal-head h4 {
        margin: 0;
        font-size: 1.05rem;
        font-weight: 700;
    }

    .docente-competencia-modal-close {
        border: 0;
        background: rgba(255, 255, 255, 0.16);
        color: #fff;
        width: 1.9rem;
        height: 1.9rem;
        line-height: 1;
        border-radius: 50%;
        font-size: 1.1rem;
        flex-shrink: 0;
        cursor: pointer;
        transition: background-color .15s ease;
    }

    .docente-competencia-modal-close:hover {
        background: rgba(255, 255, 255, 0.3);
    }

    .docente-competencia-modal-body {
        padding: 1.25rem 1.3rem 1.5rem;
    }

    .docente-competencia-modal-label {
        font-size: 0.7rem;
        letter-spacing: 0.04em;
        text-transform: uppercase;
        font-weight: 700;
        color: #858796;
        margin-bottom: 0.35rem;
    }

    .docente-competencia-modal-body p {
        color: #4b4f5c;
        text-align: justify;
        line-height: 1.55;
    }

    .docente-competencia-modal-body .docente-competencia-modal-capacidades {
        margin-bottom: 0;
    }

    body.dark-mode .docente-competencia-modal-card,
    body.dim-mode .docente-competencia-modal-card {
        background: #161b22;
    }

    body.dark-mode .docente-competencia-modal-body p,
    body.dim-mode .docente-competencia-modal-body p {
        color: #c9d1d9;
    }

    body.dark-mode .docente-competencia-modal-label,
    body.dim-mode .docente-competencia-modal-label {
        color: #8b949e;
    }
</style>

<div id="competenciaModal" class="docente-competencia-modal-overlay" onclick="closeModal(event)">
    <div class="docente-competencia-modal-card" onclick="event.stopPropagation();">
        <div class="docente-competencia-modal-head">
            <h4 id="competenciaNombre"></h4>
            <button type="button" class="docente-competencia-modal-close" onclick="closeModal(event)"
                aria-label="Cerrar">&times;</button>
        </div>
        <div class="docente-competencia-modal-body">
            <p class="docente-competencia-modal-label mb-1">Descripción</p>
            <p id="competenciaDescripcion"></p>
            <p class="docente-competencia-modal-label mb-1">Capacidades</p>
            <p id="competenciaCapacidades" class="docente-competencia-modal-capacidades"></p>
        </div>
    </div>
</div>

<script>
    function openModal(element) {
        var nombre = element.getAttribute('data-nombre');
        var descripcion = element.getAttribute('data-descripcion');
        var capacidades = element.getAttribute('data-capacidades');

        var modal = document.getElementById('competenciaModal');
        if (!modal) return;

        document.getElementById('competenciaNombre').innerText = nombre || '';
        document.getElementById('competenciaDescripcion').innerText = descripcion || '';
        document.getElementById('competenciaCapacidades').innerHTML = capacidades || '';

        modal.classList.add('is-open');
        document.body.style.overflow = 'hidden';
    }

    function closeModal(event) {
        if (event) event.stopPropagation();
        var modal = document.getElementById('competenciaModal');
        if (!modal) return;
        modal.classList.remove('is-open');
        document.body.style.overflow = '';
    }

    document.addEventListener('keydown', function(e) {
        if (e.key === 'Escape') closeModal();
    });
</script>
