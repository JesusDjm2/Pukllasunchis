@extends('layouts.docente')

@section('titulo', 'Repositorio de sílabos')

@section('contenido')
    @php
        $cursosAgrupados = $cursos->isEmpty()
            ? collect()
            : $cursos->groupBy(function ($curso) {
                return $curso->relacionsilabo ? $curso->relacionsilabo->periodo : 'Sílabo sin periodo';
            });
    @endphp

    <div class="container-fluid docente-ui-page">
        @include('docentes.partials.ui-header', [
            'kicker' => 'Documentos',
            'title' => 'Repositorio de sílabos',
            'subtitle' => 'Consulte sílabos por periodo. Use el buscador para filtrar por nombre de curso.',
            'backUrl' => route('vistaDocente', ['docente' => $docente->id]),
            'backLabel' => 'Mis cursos',
        ])

        <div class="card docente-ui-card mb-4">
            <div class="card-body p-3 p-md-4">
                <div class="d-flex flex-column flex-md-row align-items-md-center justify-content-md-between">
                    <a class="btn btn-primary btn-sm mb-3 mb-md-0" target="_blank" rel="noopener noreferrer"
                        href="https://drive.google.com/drive/folders/1LjW2BYFC2HhmqXLPhTuYDIBcsRGpyHFG?usp=sharing">
                        <i class="fab fa-google-drive mr-1"></i> Abrir carpeta en Drive
                    </a>
                    <div class="flex-grow-1 ml-md-3" style="max-width: 28rem;">
                        <label for="buscadorSilabos" class="sr-only">Buscar curso</label>
                        <input type="search" id="buscadorSilabos" class="form-control form-control-sm"
                            placeholder="Buscar por nombre de curso…" autocomplete="off" oninput="docenteFiltrarSilabos()">
                    </div>
                </div>
            </div>
        </div>

        @if ($cursos->isEmpty())
            <div class="alert alert-info shadow-sm text-center mb-0">No hay sílabos disponibles.</div>
        @else
            <div class="accordion docente-ui-accordion" id="accordionSilabosDocente">
                @foreach ($cursosAgrupados as $periodo => $grupoCursos)
                    @php $sid = 'silabo-period-' . $loop->index; @endphp
                    <div class="card docente-silabo-period-card">
                        <div class="card-header" id="heading-{{ $sid }}">
                            <h2 class="mb-0 h6">
                                <button class="btn btn-link collapsed" type="button" data-toggle="collapse"
                                    data-target="#collapse-{{ $sid }}" aria-expanded="false"
                                    aria-controls="collapse-{{ $sid }}">
                                    <i class="far fa-calendar-alt mr-2"></i> Periodo: {{ $periodo }}
                                </button>
                            </h2>
                        </div>
                        <div id="collapse-{{ $sid }}" class="collapse" aria-labelledby="heading-{{ $sid }}"
                            data-parent="#accordionSilabosDocente">
                            <div class="card-body p-0">
                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover table-sm mb-0 align-middle">
                                        <thead class="thead-dark">
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Curso</th>
                                                <th scope="col">Programa — Ciclo</th>
                                                <th scope="col">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $contador = 1; @endphp
                                            @foreach ($grupoCursos as $curso)
                                                @if ($curso->silabo || $curso->relacionsilabo)
                                                    <tr>
                                                        <td>{{ $contador++ }}</td>
                                                        <td class="text-left curso-nombre">
                                                            <strong>{{ $curso->nombre }}</strong>
                                                            <ul class="mb-0 small pl-3">
                                                                <li>Horas: {{ $curso->horas }}</li>
                                                                <li>Créditos: {{ $curso->creditos }}</li>
                                                                <li>CC: {{ $curso->cc }}</li>
                                                            </ul>
                                                        </td>
                                                        <td class="small">
                                                            {{ $curso->ciclo && $curso->ciclo->programa
                                                                ? (str_contains($curso->ciclo->programa->nombre, 'Inicial')
                                                                    ? 'Programa Inicial'
                                                                    : (str_contains($curso->ciclo->programa->nombre, 'EIB')
                                                                        ? 'Programa EIB'
                                                                        : $curso->ciclo->programa->nombre))
                                                                : 'Sin programa asignado' }}
                                                            —
                                                            {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                                        </td>
                                                        <td>
                                                            @if ($curso->silabo)
                                                                <a class="btn btn-success btn-sm mb-1"
                                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                                    target="_blank" rel="noopener noreferrer" title="Ver PDF">
                                                                    <i class="fa fa-file-pdf"></i> PDF
                                                                </a>
                                                            @endif
                                                            @if ($curso->relacionsilabo)
                                                                <a href="{{ route('silabos.show', ['silabo' => $curso->relacionsilabo->id]) }}"
                                                                    class="btn btn-info btn-sm mb-1" title="Ver sílabo en sistema">
                                                                    <i class="fa fa-eye"></i> Ver
                                                                </a>
                                                            @endif
                                                        </td>
                                                    </tr>
                                                @endif
                                            @endforeach
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
@endsection

@push('scripts')
    <script>
        function docenteNormalizarTexto(texto) {
            return texto.normalize('NFD').replace(/[\u0300-\u036f]/g, '').toLowerCase();
        }

        function docenteFiltrarSilabos() {
            var inputEl = document.getElementById('buscadorSilabos');
            if (!inputEl) return;
            var input = docenteNormalizarTexto(inputEl.value);
            document.querySelectorAll('.docente-silabo-period-card').forEach(function(card) {
                var filas = card.querySelectorAll('tbody tr');
                var hay = false;
                filas.forEach(function(fila) {
                    var cel = fila.querySelector('.curso-nombre');
                    if (!cel) return;
                    var nombre = docenteNormalizarTexto(cel.textContent);
                    if (nombre.indexOf(input) !== -1) {
                        fila.style.display = '';
                        hay = true;
                    } else {
                        fila.style.display = 'none';
                    }
                });
                card.style.display = hay ? '' : 'none';
                if (hay) {
                    var col = card.querySelector('.collapse');
                    if (col && typeof jQuery !== 'undefined') {
                        jQuery(col).collapse('show');
                    } else if (col) {
                        col.classList.add('show');
                    }
                }
            });
        }
    </script>
@endpush
