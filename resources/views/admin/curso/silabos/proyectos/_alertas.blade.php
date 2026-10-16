{{-- Alertas de Proyectos Integradores, dibujadas en la página (no con JavaScript): éxito, advertencia,
     error y errores de validación. Requiere alumnos/partials/ui (.al-aviso). --}}
@if (session('success'))
    <div class="al-aviso is-ok-aviso" role="status"><i class="fas fa-check-circle"></i><div>{{ session('success') }}</div></div>
@endif
@if (session('warning'))
    <div class="al-aviso is-warn" role="status"><i class="fas fa-exclamation-triangle"></i><div>{{ session('warning') }}</div></div>
@endif
@if (session('error'))
    <div class="al-aviso is-mal" role="alert"><i class="fas fa-times-circle"></i><div>{{ session('error') }}</div></div>
@endif
@if ($errors->any())
    <div class="al-aviso is-mal" role="alert"><i class="fas fa-times-circle"></i>
        <div>
            <strong>Revisa estos datos antes de guardar:</strong>
            <ul class="mb-0 pl-3 mt-1">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    </div>
@endif
@once
    <style>
        .al-aviso.is-ok-aviso { background: var(--al-ok-f); color: var(--al-texto); border-color: rgba(14, 148, 103, .3); }
        .al-aviso.is-ok-aviso > i { color: var(--al-ok); }
    </style>
@endonce
