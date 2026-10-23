{{--
    Fila de un usuario en el panel /Administrador.
    $u    = User (con roles, programa, ciclo, alumno, alumnoB, docente.cursos, tutorCiclos precargados)
    $tipo = fid | ppd | inhabilitados | docentes | tutores | admins | titulacion | superadmins | bolsa
--}}
@php
    $esEstudiante = in_array($tipo, ['fid', 'ppd', 'inhabilitados'], true);
    $ficha = $u->alumno ?? $u->alumnoB;
    $pendientes = collect($u->cursosPendientes());
    $motivosCanonicos = ['Deuda', 'Sin matrícula', 'Licencia', 'Retirado'];
    $motivo = in_array($u->perfil, $motivosCanonicos, true) ? $u->perfil : 'otro';
    $esInhabilitado = $esEstudiante && $u->roles->contains('name', 'inhabilitado');
    $iconoMotivo = ['Licencia' => 'fa-pause-circle', 'Retirado' => 'fa-user-slash', 'Deuda' => 'fa-coins', 'Sin matrícula' => 'fa-file-signature'][$u->perfil] ?? 'fa-ban';
    $iniciales = mb_strtoupper(mb_substr(trim((string) $u->name), 0, 1) . mb_substr(trim((string) $u->apellidos), 0, 1));
    $nombre = $esEstudiante ? trim($u->apellidos . ', ' . $u->name, ', ') : trim($u->name . ' ' . $u->apellidos);
    $cursosDocente = $tipo === 'docentes' ? ($u->docente?->cursos ?? collect())->sortBy('nombre') : collect();
    $buscar = \Illuminate\Support\Str::lower(\Illuminate\Support\Str::ascii(implode(' ', [
        $u->name, $u->apellidos, $u->dni, $u->email, $ficha?->numero, $ficha?->numero_referencia,
        $cursosDocente->pluck('nombre')->implode(' '),
    ])));
    $tonoAvatar = ['inhabilitados' => 'is-red', 'superadmins' => 'is-violet', 'ppd' => 'is-amber', 'docentes' => 'is-cyan', 'tutores' => 'is-green'][$tipo] ?? '';

    // Matrícula del periodo elegido (ver App\Services\EstadoMatricula; viene calculada del controlador)
    $mat = $esEstudiante ? ($matriculas[$u->id] ?? null) : null;
    $esPpdMat = $mat?->modalidad === 'ppd';
    $matId = $mat?->matricula?->id;
@endphp
<div class="up-row" data-id="{{ $u->id }}" data-buscar="{{ $buscar }}" data-beca="{{ $u->beca == 1 ? 1 : 0 }}" data-pend="{{ $pendientes->count() }}"
    data-motivo="{{ $motivo }}" data-inhab="{{ $esInhabilitado ? 1 : 0 }}" data-egresado="{{ $u->condicion === 'Egresado' || $u->perfil === 'Egresado' ? 1 : 0 }}"
    @if ($mat) data-mat="{{ $mat->estado }}" data-voucher="{{ $mat->voucher_verificado ? 1 : 0 }}" data-enviada="{{ $mat->ficha_enviada_at ? 1 : 0 }}"
        data-mod="{{ $mat->modalidad }}" data-matid="{{ $matId }}" data-enviada-at="{{ $mat->ficha_enviada_at?->format('d/m/Y') }}" @endif>
    <div class="up-row-main">
        @if ($esEstudiante)
            <input type="checkbox" class="up-check js-sel" aria-label="Seleccionar a {{ $nombre }}">
        @endif
        @if ($esEstudiante && $u->foto)
            {{-- Clic: la foto en grande (visor compartido pk-foto.js) --}}
            <button type="button" class="up-avatar-btn" data-foto="{{ asset('img/estudiantes/' . $u->foto) }}"
                data-foto-titulo="{{ $nombre }}" data-foto-sub="DNI {{ $u->dni ?: '—' }}" title="Ver foto">
                <img src="{{ asset('img/estudiantes/' . $u->foto) }}" alt="Foto de {{ $nombre }}" class="up-avatar" loading="lazy">
            </button>
        @else
            <div class="up-avatar {{ $tonoAvatar }}" aria-hidden="true">{{ $iniciales }}</div>
        @endif

        <div class="up-id">
            <div class="up-name">
                @if ($esEstudiante)
                    <a href="{{ route('expediente.show', $u) }}" class="up-name-link" title="Ver expediente">{{ $nombre }}</a>
                @else
                    {{ $nombre }}
                @endif
                @if ($tipo === 'superadmins')
                    <span class="up-chip is-violet"><i class="fas fa-crown"></i> SA</span>
                @endif
                @if ($tipo === 'fid' && $u->beca == 1)
                    <span class="up-chip is-green">Beca</span>
                @endif
                @if ($u->condicion === 'Egresado')
                    <span class="up-chip is-violet">Egresado{{ isset($promocionesPpd[$u->id]) ? ' · Promoción '.$promocionesPpd[$u->id] : '' }}</span>
                @elseif ($u->condicion === 'Reincorporación')
                    <span class="up-chip is-green" title="Condición: Reincorporación"><i class="fas fa-undo-alt"></i> Reincorporado(a)</span>
                @elseif ($tipo === 'ppd' && isset($promocionesPpd[$u->id]))
                    <span class="up-chip is-amber" title="Promoción (año del programa PPD)">PPD {{ $promocionesPpd[$u->id] }}</span>
                @endif
                @if ($tipo === 'inhabilitados')
                    <button type="button" class="up-chip is-red js-motivo" title="Cambiar motivo de inhabilitación"
                        data-id="{{ $u->id }}" data-nombre="{{ $nombre }}" data-perfil="{{ $u->perfil }}">
                        {{ $u->perfil ?: 'Sin motivo' }} <i class="fas fa-pen"></i>
                    </button>
                @elseif ($esInhabilitado)
                    {{-- En FID/PPD también: quien conserva el rol de alumno (p. ej. por Licencia) seguía
                         viéndose como un alumno más, sin rastro de su inhabilitación. --}}
                    <button type="button" class="up-chip is-red js-motivo" title="Inhabilitado por {{ $u->perfil ?: 'motivo sin registrar' }} — clic para cambiar el motivo"
                        data-id="{{ $u->id }}" data-nombre="{{ $nombre }}" data-perfil="{{ $u->perfil }}">
                        <i class="fas {{ $iconoMotivo }}"></i> Inhabilitado · {{ $u->perfil ?: 'sin motivo' }}
                    </button>
                @endif
            </div>
            <div class="up-meta">
                <span>DNI {{ $u->dni ?: '—' }}</span>
                <span>{{ $u->email }}</span>
                @if ($esEstudiante)
                    <span title="Teléfono"><i class="fas fa-phone-alt fa-xs"></i> {{ $ficha?->numero ?: '—' }}</span>
                    {{-- El n.º de referencia a veces trae nombre y parentesco: se recorta y se ve completo al pasar el mouse --}}
                    <span class="up-ref" title="N.º de referencia: {{ $ficha?->numero_referencia ?: '—' }}">N.º ref. {{ $ficha?->numero_referencia ?: '—' }}</span>
                @endif
            </div>
        </div>

        <div class="up-side">
            @if ($mat && $mat->estado !== 'no_aplica')
                {{-- El JS de _panel lo repinta tras verificar voucher / enviar ficha (pintarMatricula) --}}
                <span class="up-mat js-mat"></span>
            @endif
            @if (in_array($tipo, ['fid', 'inhabilitados'], true))
                @if ($pendientes->isNotEmpty())
                    <button type="button" class="up-chip is-amber js-detalle" aria-expanded="false">
                        <i class="fas fa-hourglass-half"></i> {{ $pendientes->count() }} {{ $pendientes->count() === 1 ? 'pendiente' : 'pendientes' }}
                        <i class="fas fa-chevron-down up-chev"></i>
                    </button>
                @else
                    <span class="up-chip is-muted">Sin pendientes</span>
                @endif
            @endif

            @if ($tipo === 'docentes')
                @if ($cursosDocente->isNotEmpty())
                    <button type="button" class="up-chip is-cyan js-detalle" aria-expanded="false">
                        <i class="fas fa-book"></i> {{ $cursosDocente->count() }} {{ $cursosDocente->count() === 1 ? 'curso' : 'cursos' }}
                        <i class="fas fa-chevron-down up-chev"></i>
                    </button>
                @else
                    <span class="up-chip is-muted">Sin cursos</span>
                @endif
            @endif

            @if ($tipo === 'tutores')
                @forelse ($u->tutorCiclos as $c)
                    <span class="up-chip is-green">
                        {{ $c->programa?->esInicial() ? 'INI' : ($c->programa?->eib ? 'EIB' : ($c->programa->nombre ?? '')) }} · {{ $c->nombre }}
                    </span>
                @empty
                    <span class="up-chip is-muted">Sin ciclos</span>
                @endforelse
            @endif
        </div>

        <div class="up-actions">
            <a href="{{ route('adminEdit', ['id' => $u->id]) }}" class="up-icon-btn js-drawer" title="Cuenta y roles"
                data-titulo="Cuenta y roles · {{ $nombre }}"><i class="fas fa-pen"></i></a>
            <div class="up-menu-wrap">
                <button type="button" class="up-icon-btn js-menu" aria-haspopup="true" aria-expanded="false" title="Más acciones">
                    <i class="fas fa-ellipsis-h"></i>
                </button>
                <div class="up-menu" role="menu">
                    @if ($esEstudiante)
                        <a role="menuitem" href="{{ route('expediente.show', $u) }}"><i class="fas fa-folder-open"></i> Ver expediente</a>
                    @endif
                    @if ($mat?->voucher_imagen)
                        <a role="menuitem" href="{{ $mat->voucher_imagen }}" target="_blank" rel="noopener"><i class="fas fa-receipt"></i> Ver voucher{{ $mat->voucher ? ' · ' . $mat->voucher : '' }}</a>
                    @endif
                    @if ($mat?->ficha)
                        {{-- Las opciones de matrícula las arma el JS de _panel al abrir el menú (armarMenuMatricula),
                             con plantillas de URL definidas una sola vez: así cada fila no repite ~8 URLs. --}}
                        <div class="js-menu-mat" data-mod="{{ $mat->modalidad }}" data-ficha="{{ $mat->ficha->id }}"
                            data-matid="{{ $matId }}" data-foto="{{ $u->foto ? 1 : 0 }}" data-nombre="{{ $nombre }}"></div>
                    @endif
                    @if (in_array($tipo, ['fid', 'inhabilitados'], true))
                        <a role="menuitem" href="{{ route('asignar.cursos', ['id' => $u->id]) }}" class="js-drawer" data-titulo="Asignar cursos · {{ $nombre }}"><i class="fas fa-book-open"></i> Asignar cursos</a>
                    @endif
                    @if ($tipo === 'docentes')
                        <a role="menuitem" href="{{ route('asignar', ['id' => $u->id]) }}" class="js-drawer" data-titulo="Asignar curso · {{ $nombre }}"><i class="fas fa-plus"></i> Asignar curso</a>
                    @endif
                    @if ($tipo === 'tutores')
                        <a role="menuitem" href="{{ route('admin.tutor.ciclos', $u->id) }}" class="js-drawer" data-titulo="Asignar ciclos · {{ $nombre }}"><i class="fas fa-layer-group"></i> Asignar ciclos</a>
                    @endif
                    @if ($tipo === 'inhabilitados')
                        <button type="button" role="menuitem" class="js-motivo" data-id="{{ $u->id }}" data-nombre="{{ $nombre }}"
                            data-perfil="{{ $u->perfil }}"><i class="fas fa-ban"></i> Cambiar motivo</button>
                    @endif
                    <a role="menuitem" href="{{ route('adminEdit', ['id' => $u->id]) }}" class="js-drawer" data-titulo="Cuenta y roles · {{ $nombre }}"><i class="fas fa-user-cog"></i> Cuenta y roles</a>
                    <div class="up-menu-sep"></div>
                    <button type="button" role="menuitem" class="is-danger js-eliminar"
                        data-url="{{ route('adminDestroy', ['id' => $u->id]) }}" data-nombre="{{ $nombre }}">
                        <i class="fas fa-trash"></i> Eliminar
                    </button>
                </div>
            </div>
        </div>
    </div>

    @if ($pendientes->isNotEmpty() && in_array($tipo, ['fid', 'inhabilitados'], true))
        <ol class="up-detail">
            @foreach ($pendientes as $p)
                <li>{{ $p }}</li>
            @endforeach
        </ol>
    @endif
    @if ($cursosDocente->isNotEmpty())
        <ul class="up-detail is-plain">
            @foreach ($cursosDocente as $curso)
                <li>
                    <span class="up-chip {{ $curso->esPpd() ? 'is-amber' : 'is-green' }}">{{ $curso->esPpd() ? 'PPD' : 'FID' }}</span>
                    {{ $curso->nombre }} <span class="up-muted">· {{ $curso->ciclo->programa->nombre ?? '' }} · {{ $curso->ciclo->nombre ?? '' }}</span>
                </li>
            @endforeach
        </ul>
    @endif
</div>
