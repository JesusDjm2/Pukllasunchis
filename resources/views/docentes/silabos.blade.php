@extends('layouts.docente')

@section('contenido')
    <div class="container mt-4 text-center">
        <h3 class="text-center mb-4 text-primary font-weight-bold">Repositorio de Sílabos Pukllasunchis</h3>
        <a class="btn btn-primary btn-sm mb-2" target="_blank"
                href="https://drive.google.com/drive/folders/1LjW2BYFC2HhmqXLPhTuYDIBcsRGpyHFG?usp=sharing">Ver en
                Drive</a>
        <div class="mb-3">
            <input type="text" id="buscador" class="form-control" placeholder="Buscar curso..." onkeyup="filtrarCursos()">
        </div>

        @if ($cursos->isEmpty())
            <div class="alert alert-info text-center">No hay sílabos disponibles.</div>
        @else
            <div class="table-responsive">
                <table class="table table-bordered table-striped text-center align-middle">
                    <thead class="thead-dark">
                        <tr>
                            <th>#</th>
                            <th>Curso</th>
                            <th>Programa - Ciclo</th>
                            <th>Acciones</th>
                        </tr>
                    </thead>
                    <tbody id="tabla-cursos">
                        @php $contador = 1; @endphp
                        @foreach ($cursos as $curso)
                            @if ($curso->silabo)
                                <tr class="curso-row">
                                    <td>{{ $contador++ }}</td>
                                    <td class="font-weight-bold curso-nombre">{{ $curso->nombre }}</td>
                                    <td>
                                        {{ $curso->ciclo && $curso->ciclo->programa
                                            ? (str_contains($curso->ciclo->programa->nombre, 'Inicial')
                                                ? 'Prog. Inicial'
                                                : (str_contains($curso->ciclo->programa->nombre, 'EIB')
                                                    ? 'Prog. EIB'
                                                    : $curso->ciclo->programa->nombre))
                                            : 'Sin programa asignado' }}
                                        -
                                        {{ $curso->ciclo ? $curso->ciclo->nombre : 'Sin ciclo asignado' }}
                                    </td>
                                    <td>
                                        <a class="btn btn-success btn-sm"
                                            href="{{ asset('docentes/silabo/' . $curso->silabo) }}" target="_blank"
                                            title="Ver Sílabo">
                                            <i class="fa fa-eye"></i> Ver
                                        </a>
                                    </td>
                                </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

    <script>
        function normalizarTexto(texto) {
            return texto.normalize("NFD").replace(/[\u0300-\u036f]/g, "").toLowerCase();
        }

        function filtrarCursos() {
            let input = normalizarTexto(document.getElementById("buscador").value);
            let filas = document.querySelectorAll(".curso-row");

            filas.forEach(fila => {
                let nombreCurso = normalizarTexto(fila.querySelector(".curso-nombre").textContent);
                fila.style.display = nombreCurso.includes(input) ? "" : "none";
            });
        }
    </script>

@endsection
