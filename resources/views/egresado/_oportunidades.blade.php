{{-- Oportunidades para el egresado: últimas ofertas de la bolsa de trabajo y comunicados recientes, con enlace a cada
     sección. Compartido por los paneles de egresado FID y PPD. Espera $ofertas y $comunicados. --}}
<div class="al-card">
    <div class="al-card-cab">
        <span class="al-card-cab-icono"><i class="fas fa-briefcase"></i></span>
        <div><h2>Bolsa de trabajo</h2><p>Ofertas laborales publicadas por instituciones educativas y empleadores.</p></div>
        <div class="al-card-cab-extra"><a href="{{ route('postulante.index') }}" class="al-btn al-btn-oro al-btn-sm"><i class="fas fa-search"></i> Ver todas</a></div>
    </div>
    <ul class="al-cursos">
        @forelse ($ofertas as $o)
            <li class="al-curso">
                <span class="al-curso-num"><i class="fas fa-briefcase"></i></span>
                <div class="al-curso-texto">
                    <div class="al-curso-nombre">{{ $o->nombre }}</div>
                    <div class="al-curso-meta">
                        <i class="far fa-calendar"></i>{{ ($o->fecha_publicacion ?? $o->created_at)?->format('d/m/Y') }}
                        @if ($o->nombre_publicador) · {{ \Illuminate\Support\Str::limit($o->nombre_publicador, 60) }}@endif
                    </div>
                </div>
                <div class="al-curso-accion">
                    @if ($o->vigente)<span class="al-badge is-ok">Vigente</span>@else<span class="al-badge is-muted">Anterior</span>@endif
                </div>
            </li>
        @empty
            <li class="al-vacio"><i class="fas fa-info-circle"></i>Todavía no hay ofertas publicadas.</li>
        @endforelse
    </ul>
</div>

<div class="al-card">
    <div class="al-card-cab">
        <span class="al-card-cab-icono"><i class="fas fa-bullhorn"></i></span>
        <div><h2>Comunicados recientes</h2><p>Avisos de la escuela.</p></div>
        <div class="al-card-cab-extra"><a href="{{ route('alumno.comunicados') }}" class="al-btn al-btn-borde al-btn-sm">Ver comunicados</a></div>
    </div>
    <ul class="al-cursos">
        @forelse ($comunicados as $c)
            <li class="al-curso">
                <span class="al-curso-num"><i class="fas fa-bullhorn"></i></span>
                <div class="al-curso-texto">
                    <div class="al-curso-nombre">{{ $c->titulo }}</div>
                    @if ($c->fecha_publicacion)<div class="al-curso-meta"><i class="far fa-calendar"></i>{{ \Carbon\Carbon::parse($c->fecha_publicacion)->format('d/m/Y') }}</div>@endif
                </div>
            </li>
        @empty
            <li class="al-vacio"><i class="fas fa-info-circle"></i>No hay comunicados recientes.</li>
        @endforelse
    </ul>
</div>
