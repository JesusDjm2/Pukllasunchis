@php $layout = auth()->user()?->hasRole('super-admin') ? 'layouts.superadmin' : 'layouts.admin'; @endphp
@extends($layout)
@section('titulo', 'Mink\'arikuy')
@section('contenido')
    @include('alumnos.partials.ui')
    @include('admin.minkarikuy._estilos')

    @php
        $publicado = $registros->firstWhere('activo', true);
        $otros = $registros->reject(fn ($r) => $publicado && $r->id === $publicado->id);
    @endphp

    <div class="container-fluid al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-people-carry"></i> Gestión interna · Comunicación</div>
                    <h1>Mink'arikuy</h1>
                    <p class="al-hero-sub">Encuentros y conversatorios con su enlace. El sistema genera el código QR a partir del enlace: descárgalo para publicarlo o imprime el afiche. El registro publicado aparece en la página web.</p>
                </div>
                <div class="al-hero-acciones">
                    <a href="{{ route('admin.minkarikuy.create') }}" class="al-btn al-btn-oro"><i class="fas fa-plus"></i> Nuevo Mink'arikuy</a>
                </div>
            </div>
        </section>

        @include('admin.curso.silabos.proyectos._alertas')

        {{-- ── Publicado en la página web ── --}}
        @if ($publicado)
            <article class="al-card">
                <div class="mk-publicado">
                    @include('admin.minkarikuy._qr', ['item' => $publicado, 'tamano' => 260])
                    <div style="min-width:0;">
                        <span class="mk-etiqueta">Publicado en la página web</span>
                        <h2>{{ $publicado->nombre }}</h2>
                        <div class="mk-cuando"><i class="far fa-calendar-alt mr-1"></i>{{ $publicado->cuando }}</div>
                        @include('admin.minkarikuy._enlace', ['item' => $publicado])
                        @include('admin.minkarikuy._acciones', ['item' => $publicado, 'grande' => true])
                    </div>
                </div>
            </article>
        @elseif ($registros->isNotEmpty())
            <div class="al-aviso is-warn"><i class="fas fa-eye-slash"></i>
                <div><strong>Ningún Mink'arikuy está publicado.</strong> La página web no muestra la opción Mink'arikuy hasta que publiques uno (botón <i class="fas fa-globe-americas"></i> de cada tarjeta).</div>
            </div>
        @endif

        {{-- ── Los demás ── --}}
        @if ($otros->isNotEmpty())
            <div class="mk-seccion">{{ $publicado ? 'Anteriores y próximos' : 'Registros' }} · {{ $otros->count() }}</div>
            <div class="mk-grid">
                @foreach ($otros as $item)
                    <article class="al-card mk-item">
                        @include('admin.minkarikuy._qr', ['item' => $item, 'tamano' => 160])
                        <div class="mk-item-cuerpo">
                            <h3>{{ $item->nombre }}</h3>
                            <div class="mk-cuando"><i class="far fa-calendar-alt mr-1"></i>{{ $item->cuando }}</div>
                            @include('admin.minkarikuy._acciones', ['item' => $item, 'grande' => false])
                        </div>
                    </article>
                @endforeach
            </div>
        @endif

        @if ($registros->isEmpty())
            <div class="al-card"><div class="al-vacio"><i class="fas fa-people-carry"></i>Todavía no hay Mink'arikuy. Crea el primero con "Nuevo Mink'arikuy": solo necesitas el nombre, la fecha, la hora y el enlace.</div></div>
        @endif
    </div>

    @include('admin.minkarikuy._scripts')
@endsection
