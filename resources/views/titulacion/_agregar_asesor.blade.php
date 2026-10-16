{{-- Alta rápida de un asesor del catálogo de Titulación (sin cuenta de
     acceso) desde la ficha; al guardar vuelve a esta misma pestaña y el
     asesor ya aparece en todos los selects de asesor y jurado.
     Espera $egresado y $tab. --}}
<details class="ft-desplegable mt-2">
    <summary><i class="fas fa-user-plus mr-1"></i>¿El asesor/a no está en la lista? Agregarlo</summary>
    <form method="POST" action="{{ route('titulacion.asesores.guardar') }}" class="mt-2">
        @csrf
        <input type="hidden" name="volver_a" value="{{ $egresado->id }}">
        <input type="hidden" name="tab" value="{{ $tab }}">
        <div class="form-row">
            <div class="form-group col-md-3 mb-2"><input type="text" name="nombres" class="form-control form-control-sm" placeholder="Nombres *" required></div>
            <div class="form-group col-md-3 mb-2"><input type="text" name="apellidos" class="form-control form-control-sm" placeholder="Apellidos *" required></div>
            <div class="form-group col-md-2 mb-2"><input type="text" name="dni" class="form-control form-control-sm" placeholder="DNI"></div>
            <div class="form-group col-md-4 mb-2"><input type="email" name="email" class="form-control form-control-sm" placeholder="Correo"></div>
            <div class="form-group col-md-8 mb-2"><input type="text" name="institucion" class="form-control form-control-sm" placeholder="Institución (si es externo)"></div>
            <div class="form-group col-md-4 mb-2"><button type="submit" class="btn btn-sm btn-outline-primary btn-block">Agregar asesor/a</button></div>
        </div>
        <small class="text-muted">Después elíjalo en el campo "Asesor/a" o "Agregar jurado". El catálogo completo se gestiona en <a href="{{ route('titulacion.asesores.index') }}">Asesores</a>.</small>
    </form>
</details>
