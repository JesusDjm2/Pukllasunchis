{{--
    Fila de integrante reutilizable — misma pieza visual en "Mis grupos de Investigaciones"
    (solo lectura, para todos los miembros), en el modal "Editar integrantes"
    del organizador, y en el panel de gestión del Super Admin
    (taqe/superadmin/index.blade.php).

    Variables esperadas:
    - $integrante   Alumno (con ->pivot->estado ya cargado)
    - $grupo        TaqeGrupo
    - $alumnoActual Alumno autenticado actual, o null (Super Admin no es alumno)
    - $mostrarAcciones bool (default false)
    - $rutaRetirar  string, name() de la ruta DELETE de retiro (default 'taqe.integrantes.retirar')
    - $rutaCambiarOrganizador string|null, name() de la ruta POST para transferir el rol de
      organizador — solo se pasa desde el modal del propio organizador (TaqeController); si
      viene null (Investigador/Super Admin no transfieren, solo consultan/gestionan membresía)
      no se muestra el botón "Hacer organizador".
--}}
@php
    $estado = $integrante->pivot->estado;
    $esOrganizador = $integrante->id === $grupo->creador_alumno_id;
    $iniciales = mb_strtoupper(mb_substr($integrante->nombres, 0, 1) . mb_substr($integrante->apellidos, 0, 1));
    $avatarClase = $esOrganizador ? 'is-organizador' : ($estado === 'pendiente' ? 'is-pendiente' : ($estado === 'rechazado' ? 'is-rechazado' : ''));
@endphp
<div class="taqe-team-row">
    <div class="taqe-team-avatar {{ $avatarClase }}">{{ $iniciales }}</div>
    <div class="taqe-team-body">
        <div class="taqe-team-name">
            <span>{{ $integrante->apellidos }}, {{ $integrante->nombres }}</span>
            @if ($esOrganizador)
                <span class="taqe-team-tag organizador"><i class="fas fa-crown"></i>Organizador</span>
            @endif
            @if ($estado === 'pendiente')
                <span class="taqe-team-tag pendiente"><i class="fas fa-hourglass-half"></i>Invitación pendiente</span>
            @elseif ($estado === 'rechazado')
                <span class="taqe-team-tag rechazado"><i class="fas fa-ban"></i>Rechazó</span>
            @endif
        </div>
    </div>
    @if (($mostrarAcciones ?? false) && $integrante->id !== optional($alumnoActual ?? null)->id && $estado !== 'rechazado')
        <div class="taqe-team-actions">
            @if (!empty($rutaCambiarOrganizador) && !$esOrganizador && $estado === 'aceptado')
                <form action="{{ route($rutaCambiarOrganizador, $grupo->id) }}" method="POST"
                    data-confirm="¿Hacer a {{ $integrante->apellidos }}, {{ $integrante->nombres }} el nuevo organizador? Dejarás de serlo tú.">
                    @csrf
                    <input type="hidden" name="nuevo_organizador_id" value="{{ $integrante->id }}">
                    <button type="submit" class="btn btn-outline-warning btn-sm" title="Hacer organizador">
                        <i class="fas fa-crown"></i>
                    </button>
                </form>
            @endif
            <form action="{{ route($rutaRetirar ?? 'taqe.integrantes.retirar', [$grupo->id, $integrante->id]) }}" method="POST"
                data-confirm="{{ $estado === 'aceptado' ? '¿Retirar a' : '¿Cancelar la invitación de' }} {{ $integrante->apellidos }}, {{ $integrante->nombres }}?" data-confirm-danger>
                @csrf
                @method('DELETE')
                <button type="submit" class="btn btn-outline-danger btn-sm" title="{{ $estado === 'aceptado' ? 'Retirar del grupo' : 'Cancelar invitación' }}">
                    <i class="fas {{ $estado === 'aceptado' ? 'fa-user-minus' : 'fa-times' }}"></i>
                </button>
            </form>
        </div>
    @endif
</div>
