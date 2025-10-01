@extends('layouts.docente')

@section('contenido')
   <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-LN+7fdVzj6u52u30Kp6M/trliBMCMKTyK833zpbD+pXdCLuTusPj697FH4R/5mcr" crossorigin="anonymous">

    <div class="container mt-4 text-center">
        <h3 class="text-center mb-4 text-primary font-weight-bold">Repositorio de Sílabos Pukllasunchis</h3>
        <a class="btn btn-primary btn-sm mb-2" target="_blank"
            href="https://drive.google.com/drive/folders/1LjW2BYFC2HhmqXLPhTuYDIBcsRGpyHFG?usp=sharing">Ver en
            Drive</a>
        <div class="mb-3">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar por nombre de curso..."
                onkeyup="filtrarCursos()">
        </div>

        @if ($cursos->isEmpty())
            <div class="alert alert-info text-center">No hay sílabos disponibles.</div>
        @else
            @php
                // Agrupamos cursos según el periodo del silabo o "Sin periodo disponible"
                $cursosAgrupados = $cursos->groupBy(function ($curso) {
                    return $curso->relacionsilabo ? $curso->relacionsilabo->periodo : 'Sílabo sin periodo';
                });
            @endphp
            <div class="accordion" id="accordionPeriodos">
                @foreach ($cursosAgrupados as $periodo => $grupoCursos)
                    <div class="accordion-item">
                        <h2 class="accordion-header" id="heading-{{ Str::slug($periodo) }}">
                            <button class="accordion-button collapsed" type="button" data-bs-toggle="collapse"
                                data-bs-target="#collapse-{{ Str::slug($periodo) }}" aria-expanded="false"
                                aria-controls="collapse-{{ Str::slug($periodo) }}">
                                📅 Periodo: {{ $periodo }}
                            </button>
                        </h2>
                        <div id="collapse-{{ Str::slug($periodo) }}" class="accordion-collapse collapse"
                            aria-labelledby="heading-{{ Str::slug($periodo) }}" data-bs-parent="#accordionPeriodos">
                            <div class="accordion-body">

                                <div class="table-responsive">
                                    <table class="table table-bordered table-hover align-middle">
                                        <thead class="table-dark">
                                            <tr>
                                                <th scope="col">#</th>
                                                <th scope="col">Curso</th>
                                                <th scope="col">Programa - Ciclo</th>
                                                <th scope="col">Acciones</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            @php $contador = 1; @endphp
                                            @foreach ($grupoCursos as $curso)
                                                @if ($curso->silabo || $curso->relacionsilabo)
                                                    <tr>
                                                        <td>{{ $contador++ }}</td>
                                                        
                                                        <td class="text-start curso-nombre">
                                                            <strong>{{ $curso->nombre }}</strong>
                                                            <ul class="mb-0 small">
                                                                <li>Horas: {{ $curso->horas }}</li>
                                                                <li>Créditos: {{ $curso->creditos }}</li>
                                                                <li>CC: {{ $curso->cc }}</li>
                                                            </ul>
                                                        </td>
                                                        <td>
                                                            {{ $curso->ciclo && $curso->ciclo->programa
                                                                ? (str_contains($curso->ciclo->programa->nombre, 'Inicial')
                                                                    ? 'Programa Inicial'
                                                                    : (str_contains($curso->ciclo->programa->nombre, 'EIB')
                                                                        ? 'Programa EIB'
                                                                        : $curso->ciclo->programa->nombre))
                                                                : 'Sin programa asignado' }}
                                                            -
                                                            {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                                        </td>
                                                        <td>
                                                            @if ($curso->silabo)
                                                                <a class="btn btn-success btn-sm"
                                                                    href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                                    target="_blank" title="Ver Sílabo">
                                                                    <i class="fa fa-file-pdf"></i> Ver PDF
                                                                </a>
                                                            @endif
                                                            @if ($curso->relacionsilabo)
                                                                <a href="{{ route('silabos.show', ['silabo' => $curso->relacionsilabo->id]) }}"
                                                                    class="btn btn-info btn-sm" title="Ver Sílabo">
                                                                    <i class="fa fa-eye"></i> Ver Sílabo
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

    <script>
        function normalizarTexto(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        function filtrarCursos() {
            let input = normalizarTexto(document.getElementById("buscador").value);
            let acordeones = document.querySelectorAll(".accordion-item");

            acordeones.forEach(acordeon => {
                let filas = acordeon.querySelectorAll("tbody tr");
                let hayCoincidencia = false;

                filas.forEach(fila => {
                    let nombreCurso = normalizarTexto(fila.querySelector(".curso-nombre").textContent);
                    if (nombreCurso.includes(input)) {
                        fila.style.display = "";
                        hayCoincidencia = true;
                    } else {
                        fila.style.display = "none";
                    }
                });

                // Mostrar/ocultar acordeón según coincidencia
                acordeon.style.display = hayCoincidencia ? "" : "none";

                // Si hay coincidencias, abrir el acordeón automáticamente
                if (hayCoincidencia) {
                    let collapse = acordeon.querySelector(".accordion-collapse");
                    let bsCollapse = new bootstrap.Collapse(collapse, {
                        toggle: false
                    });
                    bsCollapse.show();
                }
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.7/dist/js/bootstrap.bundle.min.js"
        integrity="sha384-ndDqU0Gzau9qJ1lfW4pNLlhNTkCfHzAVBReH9diLvGRem5+R9g2FzA8ZGN954O5Q" crossorigin="anonymous">
    </script>
@endsection
