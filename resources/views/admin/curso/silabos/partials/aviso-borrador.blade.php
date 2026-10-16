{{-- Aviso discreto (no modal) para recuperar el borrador local; lo muestra partials/borrador. --}}
<div id="silaboAvisoBorrador" class="silabo-aviso" role="status" hidden>
    <span class="silabo-aviso-icono"><i class="fas fa-history"></i></span>
    <div class="silabo-aviso-cuerpo">
        <strong>Encontramos un avance sin guardar</strong>
        <span class="js-aviso-texto"></span>
    </div>
    <div class="silabo-aviso-acciones">
        <button type="button" class="btn btn-sm silabo-btn-recuperar js-aviso-recuperar"><i class="fas fa-undo-alt mr-1"></i>Recuperar</button>
        <button type="button" class="btn btn-sm btn-link silabo-btn-descartar js-aviso-descartar">Descartar</button>
    </div>
</div>
