@extends('layouts.superadmin')
@section('titulo', 'Intentos fallidos')
@section('contenido')
    @include('alumnos.partials.ui')

    <div class="container-fluid al-ui pb-4">
        @include('admin.bitacora-accesos._tabs', ['activo' => 'bitacora.fallidos', 'sub' => 'Inicios de sesión con correo o contraseña incorrectos. Varios fallos seguidos en una misma cuenta suelen ser una contraseña olvidada; muchos en poco tiempo, alguien probando una cuenta ajena.'])

        <div class="al-stats">
            <div class="al-stat">
                <span class="al-stat-icono is-mal"><i class="fas fa-exclamation-triangle"></i></span>
                <div><div class="al-stat-valor">{{ number_format($resumen['total']) }}</div><div class="al-stat-etiqueta">Intentos registrados</div></div>
            </div>
            <div class="al-stat">
                <span class="al-stat-icono is-oro"><i class="fas fa-clock"></i></span>
                <div><div class="al-stat-valor">{{ number_format($resumen['ultimas24']) }}</div><div class="al-stat-etiqueta">En las últimas 24 h</div></div>
            </div>
            <a class="al-stat {{ $cuenta === 'real' ? 'is-activa' : '' }}" href="{{ route('bitacora.fallidos', array_filter(['cuenta' => $cuenta === 'real' ? null : 'real', 'q' => $buscar])) }}" title="Ver solo estos">
                <span class="al-stat-icono is-info"><i class="fas fa-user-check"></i></span>
                <div><div class="al-stat-valor">{{ number_format($resumen['reales']) }}</div><div class="al-stat-etiqueta">En cuentas que existen</div></div>
            </a>
            <a class="al-stat {{ $cuenta === 'inexistente' ? 'is-activa' : '' }}" href="{{ route('bitacora.fallidos', array_filter(['cuenta' => $cuenta === 'inexistente' ? null : 'inexistente', 'q' => $buscar])) }}" title="Ver solo estos">
                <span class="al-stat-icono"><i class="fas fa-user-slash"></i></span>
                <div><div class="al-stat-valor">{{ number_format($resumen['inexistentes']) }}</div><div class="al-stat-etiqueta">Con correos que no existen</div></div>
            </a>
        </div>

        @if ($insistentes->isNotEmpty())
            <article class="al-card">
                <div class="al-card-cab">
                    <span class="al-card-cab-icono"><i class="fas fa-redo"></i></span>
                    <div>
                        <h2>Correos con más fallos esta semana</h2>
                        <p>3 o más intentos en los últimos 7 días. Si es una cuenta real, conviene confirmar con la persona si olvidó su contraseña.</p>
                    </div>
                </div>
                <div class="al-tabla-marco">
                    <table class="al-tabla">
                        <thead>
                            <tr><th>Correo intentado</th><th>Cuenta</th><th>Intentos</th><th>Último</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($insistentes as $fila)
                                @php $usuario = $usuariosInsistentes->get($fila->user_id); @endphp
                                <tr>
                                    <td class="al-correo"><a href="{{ route('bitacora.fallidos', ['q' => $fila->email_intentado]) }}" title="Ver todos sus intentos">{{ $fila->email_intentado }}</a></td>
                                    <td>
                                        @if ($usuario)
                                            <span class="al-badge is-info"><i class="fas fa-user"></i> {{ trim($usuario->name . ' ' . $usuario->apellidos) }}</span>
                                        @else
                                            <span class="al-badge is-muted">No existe</span>
                                        @endif
                                    </td>
                                    <td><span class="al-badge {{ $fila->veces >= 10 ? 'is-mal' : 'is-warn' }}">{{ $fila->veces }}</span></td>
                                    <td class="text-nowrap"><small>{{ \Illuminate\Support\Carbon::parse($fila->ultimo)->diffForHumans() }}</small></td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </article>
        @endif

        <article class="al-card">
            <div class="al-card-cab">
                <span class="al-card-cab-icono"><i class="fas fa-list"></i></span>
                <div>
                    <h2>Todos los intentos</h2>
                    <p>Del más reciente al más antiguo.</p>
                </div>
                <span class="al-card-cab-extra"><span class="al-badge is-muted">{{ number_format($intentos->total()) }} {{ $buscar !== '' || $cuenta !== '' ? 'con este filtro' : 'en total' }}</span></span>
            </div>

            <form class="al-filtros" method="GET" action="{{ route('bitacora.fallidos') }}" role="search">
                <input type="search" name="q" value="{{ $buscar }}" class="al-campo" placeholder="Buscar por correo…" aria-label="Buscar por correo">
                <select name="cuenta" class="al-campo" aria-label="Tipo de cuenta">
                    <option value="">Todas las cuentas</option>
                    <option value="real" @selected($cuenta === 'real')>Solo cuentas que existen</option>
                    <option value="inexistente" @selected($cuenta === 'inexistente')>Solo correos que no existen</option>
                </select>
                <button type="submit" class="al-btn al-btn-petroleo al-btn-sm"><i class="fas fa-search"></i> Filtrar</button>
                @if ($buscar !== '' || $cuenta !== '')
                    <a href="{{ route('bitacora.fallidos') }}" class="al-btn al-btn-borde al-btn-sm"><i class="fas fa-times"></i> Quitar filtros</a>
                @endif
            </form>

            @if ($intentos->isEmpty())
                <div class="al-vacio">
                    <i class="fas fa-shield-alt"></i>
                    {{ $buscar !== '' || $cuenta !== '' ? 'Ningún intento coincide con el filtro.' : 'No hay intentos fallidos registrados.' }}
                </div>
            @else
                <div class="al-tabla-marco">
                    <table class="al-tabla">
                        <thead>
                            <tr><th class="al-num">#</th><th>Correo intentado</th><th>Cuenta</th><th>Fecha</th></tr>
                        </thead>
                        <tbody>
                            @foreach ($intentos as $intento)
                                <tr>
                                    <td class="al-num">{{ $intentos->firstItem() + $loop->index }}</td>
                                    <td class="al-correo">{{ $intento->email_intentado }}</td>
                                    <td>
                                        @if ($intento->usuario)
                                            <span class="al-badge is-info"><i class="fas fa-user"></i> {{ trim($intento->usuario->name . ' ' . $intento->usuario->apellidos) }}</span>
                                        @elseif ($intento->user_id)
                                            <span class="al-badge is-muted">Cuenta eliminada</span>
                                        @else
                                            <span class="al-badge is-muted">No existe</span>
                                        @endif
                                    </td>
                                    <td class="text-nowrap">
                                        {{ $intento->created_at->format('d/m/Y H:i') }}
                                        <br><small>{{ $intento->created_at->diffForHumans() }}</small>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                @if ($intentos->hasPages())
                    <div class="al-paginacion">{{ $intentos->links() }}</div>
                @endif
            @endif
        </article>
    </div>
@endsection
