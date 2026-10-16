{{-- Barra fija inferior del formulario de sílabo: estado del borrador local + Guardar.
     Va DENTRO del <form> (el botón es type="submit"). Espera: $textoGuardar. --}}
<div class="silabo-barra">
    <div id="silaboEstadoBorrador" class="silabo-estado" aria-live="polite">
        <span class="silabo-estado-punto"></span>
        <span class="silabo-estado-texto">Sin cambios pendientes</span>
    </div>
    <span class="silabo-barra-ayuda d-none d-lg-inline">
        <i class="fas fa-shield-alt"></i> Mientras escribes se guarda una copia en este equipo
    </span>
    <button type="submit" class="btn silabo-btn-guardar">
        <i class="fas fa-save mr-1"></i> {{ $textoGuardar }}
    </button>
</div>
