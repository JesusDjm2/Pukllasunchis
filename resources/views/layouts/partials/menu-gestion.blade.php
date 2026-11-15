{{--
    Menú lateral de gestión, compartido por layouts.admin y layouts.superadmin para que nunca
    vuelvan a divergir. Cada opción se muestra según el rol (y las rutas que ese rol puede abrir).
    El grupo de la página actual queda abierto y su opción resaltada.

    Diseño: menú lateral compartido .pk-sb (public/css/pk-sidebar.css); los colores salen de
    pk-tokens.css según el rol. Para agregar una opción: súmala a su grupo aquí, con el patrón de
    ruta en $grupos si va dentro de un submenú.
--}}
@php
    $menuUser = auth()->user();
    $esSA = $menuUser?->hasRole('super-admin');
    $esAdmin = $esSA || $menuUser?->hasRole('admin');
    $esAdminB = $menuUser?->hasRole('adminB');

    // Patrones de ruta de cada submenú: si la página actual coincide, el submenú se abre.
    $grupos = [
        'mgAcademico' => ['programa.*', 'ciclo.*', 'curso.*', 'periodoactual.*', 'periodos.*', 'competencias.*', 'capacidades.*', 'estandares.*', 'enfoques.*', 'proyectos.*'],
        'mgSeguimiento' => ['expediente.panel*', 'admin.seguimiento*', 'alumnos.demograficos', 'titulacion.*'],
        'mgAdmision' => ['admin-fids.*', 'regulares.*', 'postulantes.ppd.*'],
        'mgComunicacion' => ['bolsa-trabajo.*', 'admin.comunicados.*', 'admin.minkarikuy.*', 'trabajo.*', 'listaPostulantes', 'bitacora.*', 'admin.taqe.*', 'admin.reclamos.*'],
    ];
    $abierto = fn (string $id) => request()->routeIs(...$grupos[$id]);
    $activo = fn (...$patrones) => request()->routeIs(...$patrones) ? 'active' : '';
    $linkActivo = fn (...$patrones) => request()->routeIs(...$patrones) ? 'is-activo' : '';
@endphp

@if ($esAdmin)
    {{-- ══ ACADÉMICO ══ --}}
    <div class="pk-sb-titulo">Académico</div>

    <li class="nav-item">
        <a class="nav-link pk-sb-link {{ $abierto('mgAcademico') ? 'is-activo' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#mgAcademico"
           aria-expanded="{{ $abierto('mgAcademico') ? 'true' : 'false' }}" aria-controls="mgAcademico" title="Gestión académica">
            <span class="pk-sb-icono"><i class="fas fa-book"></i></span><span class="pk-sb-texto">Gestión académica</span>
        </a>
        <div id="mgAcademico" class="collapse {{ $abierto('mgAcademico') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <h6 class="collapse-header">Plan de estudios</h6>
                <a class="collapse-item {{ $activo('programa.*') }}" href="{{ route('programa.index') }}">Programas</a>
                <a class="collapse-item {{ $activo('ciclo.*') }}" href="{{ route('ciclo.index') }}">Ciclos</a>
                <a class="collapse-item {{ $activo('curso.*') }}" href="{{ route('curso.index') }}">Cursos</a>
                <a class="collapse-item {{ $activo('periodoactual.*', 'periodos.*') }}" href="{{ route('periodoactual.index') }}">Periodos</a>
                <h6 class="collapse-header mt-2">Diseño curricular</h6>
                <a class="collapse-item {{ $activo('competencias.*') }}" href="{{ route('competencias.index') }}">Competencias</a>
                <a class="collapse-item {{ $activo('capacidades.*') }}" href="{{ route('capacidades.index') }}">Capacidades</a>
                <a class="collapse-item {{ $activo('estandares.*') }}" href="{{ route('estandares.index') }}">Estándares</a>
                <a class="collapse-item {{ $activo('enfoques.*') }}" href="{{ route('enfoques.index') }}">Enfoques</a>
                <a class="collapse-item {{ $activo('proyectos.*') }}" href="{{ route('proyectos.index') }}">Proyectos integradores</a>
            </div>
        </div>
    </li>
    <li class="nav-item">
        <a class="nav-link pk-sb-link {{ $linkActivo('docente.*') }}" href="{{ route('docente.index') }}" title="Docentes">
            <span class="pk-sb-icono"><i class="fas fa-chalkboard-teacher"></i></span><span class="pk-sb-texto">Docentes</span>
        </a>
    </li>
    @if ($esSA)
        <li class="nav-item">
            <a class="nav-link pk-sb-link {{ $linkActivo('ce.cursos.*') }}" href="{{ route('ce.cursos.index') }}" title="Cursos asincrónicos">
                <span class="pk-sb-icono"><i class="fas fa-play-circle"></i></span><span class="pk-sb-texto">Cursos asincrónicos</span>
            </a>
        </li>
    @endif

    {{-- ══ ESTUDIANTES ══ --}}
    <div class="pk-sb-titulo">Estudiantes</div>

    <li class="nav-item">
        <a class="nav-link pk-sb-link {{ $linkActivo('admin') }}" href="{{ route('admin') }}" title="Estudiantes FID y PPD, docentes y cuentas: fichas, matrículas y roles">
            <span class="pk-sb-icono"><i class="fas fa-address-book"></i></span><span class="pk-sb-texto">Directorio</span>
        </a>
    </li>
    <li class="nav-item">
        <a class="nav-link pk-sb-link {{ $abierto('mgSeguimiento') ? 'is-activo' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#mgSeguimiento"
           aria-expanded="{{ $abierto('mgSeguimiento') ? 'true' : 'false' }}" aria-controls="mgSeguimiento" title="Seguimiento">
            <span class="pk-sb-icono"><i class="fas fa-user-check"></i></span><span class="pk-sb-texto">Seguimiento</span>
        </a>
        <div id="mgSeguimiento" class="collapse {{ $abierto('mgSeguimiento') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item {{ $activo('expediente.panel*') }}" href="{{ route('expediente.panel') }}"
                   title="Licencias, retiros, reincorporaciones, desaprobados y repitencia">Alertas académicas</a>
                <a class="collapse-item {{ $activo('admin.seguimiento*') }}" href="{{ route('admin.seguimiento') }}"
                   title="Incidencias reportadas, solicitudes de tutoría y sugerencias del buzón QR">Incidencias y tutorías</a>
                <a class="collapse-item {{ $activo('alumnos.demograficos') }}" href="{{ route('alumnos.demograficos') }}">Datos demográficos</a>
                @if ($esSA && Route::has('titulacion.index'))
                    <a class="collapse-item {{ $activo('titulacion.*') }}" href="{{ route('titulacion.index') }}">Titulación</a>
                @endif
            </div>
        </div>
    </li>

    {{-- ══ ADMISIÓN ══ --}}
    <div class="pk-sb-titulo">Admisión</div>

    <li class="nav-item">
        <a class="nav-link pk-sb-link {{ $abierto('mgAdmision') ? 'is-activo' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#mgAdmision"
           aria-expanded="{{ $abierto('mgAdmision') ? 'true' : 'false' }}" aria-controls="mgAdmision" title="Admisión">
            <span class="pk-sb-icono"><i class="fas fa-user-plus"></i></span><span class="pk-sb-texto">Admisión</span>
        </a>
        <div id="mgAdmision" class="collapse {{ $abierto('mgAdmision') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                <a class="collapse-item {{ $activo('admin-fids.*') }}" href="{{ route('admin-fids.index') }}" title="Procesos de admisión por año">Convocatorias</a>
                <a class="collapse-item {{ $activo('regulares.*') }}" href="{{ route('regulares.index') }}">Postulantes FID</a>
                <a class="collapse-item {{ $activo('postulantes.ppd.*') }}" href="{{ route('postulantes.ppd.index') }}">Postulantes PPD</a>
            </div>
        </div>
    </li>

    {{-- ══ ACERVO HISTÓRICO: IESP RICARDO PALMA ══ --}}
    <div class="pk-sb-titulo">Histórico</div>

    {{-- Un solo enlace: las secciones (actas, estudiantes, carreras, carga) son pestañas dentro del módulo --}}
    <li class="nav-item">
        <a class="nav-link pk-sb-link {{ $linkActivo('rp.*') }}" href="{{ route('rp.dashboard') }}" title="IESP Ricardo Palma (1995-2015)">
            <span class="pk-sb-icono"><i class="fas fa-archive"></i></span><span class="pk-sb-texto">Ricardo Palma</span>
        </a>
    </li>
@endif

@if ($esAdmin || $esAdminB)
    {{-- ══ GESTIÓN INTERNA ══ (comunicación institucional + control del sistema) --}}
    <div class="pk-sb-titulo">Gestión interna</div>

    <li class="nav-item">
        <a class="nav-link pk-sb-link {{ $abierto('mgComunicacion') ? 'is-activo' : 'collapsed' }}" href="#" data-toggle="collapse" data-target="#mgComunicacion"
           aria-expanded="{{ $abierto('mgComunicacion') ? 'true' : 'false' }}" aria-controls="mgComunicacion" title="Gestión interna">
            <span class="pk-sb-icono"><i class="fas fa-briefcase"></i></span><span class="pk-sb-texto">Gestión interna</span>
        </a>
        <div id="mgComunicacion" class="collapse {{ $abierto('mgComunicacion') ? 'show' : '' }}" data-parent="#accordionSidebar">
            <div class="py-2 collapse-inner rounded">
                @if ($esAdmin)
                    <h6 class="collapse-header">Comunicación</h6>
                    <a class="collapse-item {{ $activo('admin.comunicados.*') }}" href="{{ route('admin.comunicados.index') }}">Comunicados</a>
                    <a class="collapse-item {{ $activo('bolsa-trabajo.*') }}" href="{{ route('bolsa-trabajo.ofertas.index') }}">Bolsa de trabajo</a>
                    <a class="collapse-item {{ $activo('admin.minkarikuy.*') }}" href="{{ route('admin.minkarikuy.index') }}">Mink'arikuy</a>
                    <a class="collapse-item {{ $activo('admin.reclamos.*') }}" href="{{ route('admin.reclamos.todas') }}">Libro de reclamaciones</a>
                @endif
                @if ($esAdminB)
                    <h6 class="collapse-header {{ $esAdmin ? 'mt-2' : '' }}">Bolsa (registros)</h6>
                    <a class="collapse-item {{ $activo('trabajo.index') }}" href="{{ route('trabajo.index') }}">Lista de registros</a>
                    <a class="collapse-item {{ $activo('trabajo.create') }}" href="{{ route('trabajo.create') }}">Registrar nuevo</a>
                    <a class="collapse-item {{ $activo('listaPostulantes') }}" href="{{ route('listaPostulantes') }}">Postulantes</a>
                @endif
                @if ($esSA)
                    {{-- Solo super admin: sus rutas exigen role:super-admin --}}
                    <h6 class="collapse-header mt-2">Control del sistema</h6>
                    <a class="collapse-item {{ $activo('bitacora.*') }}" href="{{ route('bitacora.index') }}">Bitácora de accesos</a>
                    @if (Route::has('admin.taqe.index'))
                        <a class="collapse-item {{ $activo('admin.taqe.*') }}" href="{{ route('admin.taqe.index') }}">Investigaciones — Gestión</a>
                    @endif
                    @if (Route::has('pukllabot.reindexar'))
                        {{-- Tras editar storage/app/pukllabot (enlaces, FAQ): el chat responde con lo nuevo. --}}
                        <form method="POST" action="{{ route('pukllabot.reindexar') }}" class="m-0"
                              onsubmit="return confirm('¿Reindexar el conocimiento de PukllaBot con los textos actuales?');">
                            @csrf
                            <button type="submit" class="collapse-item border-0 bg-transparent w-100 text-left">Reindexar PukllaBot</button>
                        </form>
                    @endif
                @endif
            </div>
        </div>
    </li>
@endif
