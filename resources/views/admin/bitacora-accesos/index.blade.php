@extends('layouts.superadmin')
@section('titulo', 'Bitácora de accesos')
@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid al-ui pb-4">
        @include('admin.bitacora-accesos._tabs', ['activo' => 'bitacora.index', 'sub' => 'Historial de inicios de sesión de todos los usuarios: cuándo entraron, cuándo salieron y cuánto duró la sesión.'])

        <article class="al-card">
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas fa-history"></i></span>
                <div>
                    <h2>Inicios de sesión</h2>
                    <p>Del más reciente al más antiguo.</p>
                </div>
                <span class="al-card-cab-extra"><span class="al-badge is-muted">{{ number_format($sesiones->total()) }} registros</span></span>
            </div>

            @if ($sesiones->isEmpty())
                <div class="al-vacio"><i class="fas fa-folder-open"></i>No hay accesos registrados todavía.</div>
            @else
                <div class="al-tabla-marco">
                    <table class="al-tabla">
                        <thead>
                            <tr><th class="al-num">#</th><th>Usuario</th><th>Rol</th><th>Entró</th><th>Salió / Estado</th><th>Duración</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($sesiones as $sesion)
                                @php $conectado = $sesion->estaConectadoAhora(); @endphp
                                <tr>
                                    <td class="al-num">{{ $sesiones->firstItem() + $loop->index }}</td>
                                    <td class="al-correo">{{ $sesion->email_snapshot }}</td>
                                    <td>{{ $sesion->rol_snapshot ?? '—' }}</td>
                                    <td class="text-nowrap">{{ $sesion->login_at->format('d/m/Y H:i') }}</td>
                                    <td class="text-nowrap">
                                        @if ($conectado)
                                            <span class="al-badge is-ok"><i class="fas fa-circle" style="font-size:.5rem"></i> Conectado ahora</span>
                                        @elseif ($sesion->logout_at)
                                            {{ $sesion->logout_at->format('d/m/Y H:i') }}
                                            @if ($sesion->logout_tipo === 'expirado')
                                                <span class="al-badge is-muted">expiró</span>
                                            @endif
                                        @else
                                            <span class="al-suave">—</span>
                                        @endif
                                    </td>
                                    <td>{{ $sesion->duracion ?? '—' }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($sesiones->hasPages())
                    <div class="al-paginacion">{{ $sesiones->links() }}</div>
                @endif
            @endif
        </article>
    </div>
@endsection
