@extends('layouts.superadmin')
@section('titulo', 'Conectados ahora')
@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid al-ui pb-4">
        @include('admin.bitacora-accesos._tabs', ['activo' => 'bitacora.activos', 'sub' => 'Usuarios con una sesión activa en este momento, ordenados por su última actividad.'])

        <article class="al-card">
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas fa-signal"></i></span>
                <div>
                    <h2>Conectados ahora</h2>
                    <p>Sesiones con actividad dentro del tiempo de vida de la sesión.</p>
                </div>
                <span class="al-card-cab-extra"><span class="al-badge is-ok">{{ $conectados->count() }} {{ $conectados->count() === 1 ? 'usuario' : 'usuarios' }}</span></span>
            </div>

            @if ($conectados->isEmpty())
                <div class="al-vacio"><i class="fas fa-moon"></i>No hay ningún usuario conectado en este momento.</div>
            @else
                <div class="al-tabla-marco">
                    <table class="al-tabla">
                        <thead>
                            <tr><th class="al-num">#</th><th>Usuario</th><th>Rol</th><th>Última actividad</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($conectados as $i => $item)
                                <tr>
                                    <td class="al-num">{{ $i + 1 }}</td>
                                    <td>
                                        @if ($item['usuario'])
                                            <strong>{{ trim($item['usuario']->name . ' ' . $item['usuario']->apellidos) }}</strong>
                                            <br><small>{{ $item['usuario']->email }}</small>
                                        @else
                                            <span class="al-suave">Usuario eliminado</span>
                                        @endif
                                    </td>
                                    <td>{{ $item['usuario']?->roles->pluck('name')->implode(', ') ?: '—' }}</td>
                                    <td class="text-nowrap">{{ $item['ultima_actividad']->diffForHumans() }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            @endif
        </article>
    </div>
@endsection
