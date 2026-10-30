@extends('layouts.docente')

@section('titulo', 'Mis alumnos — Docente')

@push('styles')
    @include('docentes.alumnos.partials.estilos')
@endpush

@section('contenido')
    @php $primerNombreDocente = mb_convert_case(mb_strtolower(explode(' ', trim($docente->nombre))[0]), MB_CASE_TITLE); @endphp
    <div class="container-fluid docente-ui-page pk-al px-2 px-md-3 pb-5">
        @include('docentes.partials.ui-header', [
            'kicker'    => 'Alumnos',
            'title'     => 'Mis alumnos',
            'subtitle'  => 'Elige un curso para ver a sus estudiantes y comunicarte con ellos por correo o WhatsApp.',
            'backUrl'   => route('vistaDocente', $docente->id),
            'backLabel' => 'Volver a mis cursos',
        ])

        @foreach (['success' => 'success', 'error' => 'danger'] as $clave => $tipo)
            @if (session($clave))
                <div class="alert alert-{{ $tipo }} alert-dismissible fade show shadow-sm mb-3" role="alert">
                    {{ session($clave) }}
                    <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
                </div>
            @endif
        @endforeach
        @if ($errors->any())
            <div class="alert alert-danger alert-dismissible fade show shadow-sm mb-3" role="alert">
                El mensaje no se envió: {{ $errors->first() }}
                <button type="button" class="close" data-dismiss="alert"><span>&times;</span></button>
            </div>
        @endif

        @if ($cursos->isEmpty())
            <div class="pk-al-card">
                <div class="pk-al-sin-resultados py-5">
                    <i class="fas fa-users"></i>
                    <strong>No tienes cursos asignados</strong>
                    <div class="small mt-1">Cuando te asignen cursos, tus alumnos aparecerán aquí.</div>
                </div>
            </div>
        @else
            <div class="row">
                {{-- Selector de curso: lista en escritorio, desplegable en móvil --}}
                <div class="col-lg-4 col-xl-3 mb-3">
                    <select class="form-control d-lg-none" id="pkAlSelectorCurso" aria-label="Elegir curso"
                        onchange="pkAlMostrarCurso(this.value)">
                        @foreach ($cursos as $item)
                            <option value="{{ $item->curso->id }}" @selected($item->curso->id === $cursoSeleccionado->curso->id)>
                                [{{ $item->curso->ciclo->programa->nombre ?? 'Sin programa' }}] {{ $item->curso->nombre }} — Ciclo {{ $item->curso->ciclo->nombre }} ({{ $item->alumnos->count() }})
                            </option>
                        @endforeach
                    </select>

                    <div class="pk-al-cursos d-none d-lg-flex">
                        @foreach ($cursos as $item)
                            <button type="button" class="pk-al-curso {{ $item->curso->id === $cursoSeleccionado->curso->id ? 'is-activo' : '' }}"
                                data-curso="{{ $item->curso->id }}" onclick="pkAlMostrarCurso({{ $item->curso->id }})">
                                <div class="min-w-0">
                                    <div class="pk-al-curso-nombre">{{ $item->curso->nombre }}</div>
                                    <div class="pk-al-curso-meta">
                                        <span class="pk-al-tipo {{ $item->esPpd ? 'is-ppd' : 'is-fid' }}"><i class="fas {{ $item->esPpd ? 'fa-chalkboard-teacher' : 'fa-graduation-cap' }}"></i> {{ $item->esPpd ? 'PPD' : 'FID' }}</span>
                                        {{ $item->curso->ciclo->programa->nombre }} · Ciclo {{ $item->curso->ciclo->nombre }}
                                    </div>
                                </div>
                                <span class="pk-al-curso-num" title="Alumnos">{{ $item->alumnos->count() }}</span>
                            </button>
                        @endforeach
                    </div>
                </div>

                <div class="col-lg-8 col-xl-9">
                    @foreach ($cursos as $item)
                        @include('docentes.alumnos.partials.lista-panel', [
                            'contactos'     => $item->alumnos,
                            'titulo'        => $item->curso->nombre,
                            'subtitulo'     => $item->curso->ciclo->programa->nombre.' · Ciclo '.$item->curso->ciclo->nombre,
                            'etiquetaTipo'  => $item->esPpd ? 'PPD' : 'FID',
                            'accion'        => route('docente.curso.mensaje', ['docente' => $docente->id, 'curso' => $item->curso->id]),
                            'firma'         => ['nombre' => $primerNombreDocente, 'rol' => 'tu docente de '.$item->curso->nombre, 'rolRef' => 'docente'],
                            'visible'       => $item->curso->id === $cursoSeleccionado->curso->id,
                            'panelId'       => 'pkAlCurso'.$item->curso->id,
                        ])
                    @endforeach
                </div>
            </div>
        @endif
    </div>

    @include('docentes.alumnos.partials.modal-mensaje', [
        'remitenteNombre' => $docente->nombre,
        'remitenteCorreo' => $docente->email ?: $docente->user?->email,
    ])
@endsection

@push('scripts')
    <script>
        function pkAlMostrarCurso(id) {
            id = String(id);
            document.querySelectorAll('.pk-al-panel').forEach(function (p) {
                p.classList.toggle('d-none', p.id !== 'pkAlCurso' + id);
            });
            document.querySelectorAll('.pk-al-curso').forEach(function (b) {
                b.classList.toggle('is-activo', b.dataset.curso === id);
            });
            var selector = document.getElementById('pkAlSelectorCurso');
            if (selector) selector.value = id;

            // La URL refleja el curso abierto: al recargar o volver de un envío se queda en él.
            var url = new URL(window.location.href);
            url.searchParams.set('curso', id);
            window.history.replaceState(null, '', url);
        }
    </script>
@endpush
