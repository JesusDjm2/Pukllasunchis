@extends('layouts.superadmin')

@section('titulo', "Investigaciones — Gestión")

@section('contenido')
    {{-- Mismo diseño que la vista del alumno (memoria: feedback_diseno_unificado). --}}
    @include('alumnos.partials.ui')
    @include('taqe._estilos')
    @include('partials.flash-swal')

    <div class="container-fluid al-ui">
        <section class="al-hero">
            <div class="al-hero-fila">
                <div class="al-hero-texto">
                    <div class="al-kicker"><i class="fas fa-flask"></i> Investigaciones · Gestión interna</div>
                    <h1>Grupos de investigación</h1>
                    <p class="al-hero-sub">Supervisa y gestiona cualquier grupo de la institución: agrega o retira integrantes, revisa y descarga sus archivos, o elimina un grupo completo.</p>
                </div>
            </div>
        </section>

        <div class="al-stats">
            <div class="al-stat"><span class="al-stat-icono"><i class="fas fa-users"></i></span>
                <div><div class="al-stat-valor">{{ $grupos->count() }}</div><div class="al-stat-etiqueta">Grupos</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-info"><i class="fas fa-user-check"></i></span>
                <div><div class="al-stat-valor">{{ $totalIntegrantesAceptados }}</div><div class="al-stat-etiqueta">Integrantes</div></div></div>
            <div class="al-stat"><span class="al-stat-icono is-ok"><i class="fas fa-flag-checkered"></i></span>
                <div><div class="al-stat-valor">{{ $totalConTrabajoFinal }}</div><div class="al-stat-etiqueta">Con trabajo final</div></div></div>
            <div class="al-stat"><span class="al-stat-icono {{ $solicitudesPendientes->isNotEmpty() ? 'is-mal' : 'is-oro' }}"><i class="fas fa-life-ring"></i></span>
                <div><div class="al-stat-valor">{{ $solicitudesPendientes->count() }}</div><div class="al-stat-etiqueta">Pedidos de ayuda</div></div></div>
        </div>

        @include('taqe._solicitudes', ['solicitudes' => $solicitudesPendientes, 'rutaAtender' => 'admin.taqe.solicitudes.atender'])

        @include('taqe._filtros', ['ruta' => 'admin.taqe.index', 'total' => $grupos->count()])

        @forelse ($grupos as $grupo)
            @include('taqe._grupo-gestion', [
                'grupo' => $grupo,
                'rutaVer' => 'admin.taqe.trabajos.ver',
                'rutaEliminar' => 'admin.taqe.trabajos.destroy',
                'rutaZip' => 'admin.taqe.descargar',
                'superAdmin' => true,
            ])
        @empty
            <div class="al-card"><div class="al-vacio"><i class="fas fa-flask"></i>No hay grupos de Investigaciones con estos filtros.</div></div>
        @endforelse
    </div>

    @include('taqe._visor')
@endsection

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function () {
        // Cada modal "Integrantes" carga su lista de compañeros disponibles recién al abrirse
        // (puede haber muchos grupos en esta página). Bootstrap 4 emite show.bs.modal solo por
        // jQuery: antes se escuchaba como evento nativo y la lista quedaba en "Cargando…".
        if (!window.jQuery) return;
        var escapar = function (s) {
            return String(s).replace(/[&<>"]/g, function (c) { return { '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;' }[c]; });
        };

        jQuery('[id^="modalGestionar"]').on('show.bs.modal', function () {
            var form = this.querySelector('.taqe-sa-form-invitar');
            var lista = form.querySelector('[data-lista-companeros]');
            if (lista.dataset.cargado) return;

            fetch('{{ url('admin/taqe') }}/' + form.dataset.grupo + '/alumnos-disponibles', {
                headers: { 'X-Requested-With': 'XMLHttpRequest' },
            })
                .then(function (r) { return r.json(); })
                .then(function (companeros) {
                    lista.dataset.cargado = '1';
                    if (!companeros.length) {
                        lista.innerHTML = '<span class="tq-nota">No hay más alumnos disponibles en este curso.</span>';
                        return;
                    }
                    lista.innerHTML = companeros.map(function (c) {
                        return '<label><input type="checkbox" name="integrantes[]" value="' + parseInt(c.id, 10) + '"> ' + escapar(c.nombre) + '</label>';
                    }).join('');
                })
                .catch(function () {
                    lista.innerHTML = '<span class="text-danger small">No se pudo cargar la lista. Intenta de nuevo.</span>';
                });
        });
    });
</script>
@endpush
