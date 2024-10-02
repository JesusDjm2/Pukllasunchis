@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-2"
            style="border-bottom: 1px dashed #80808078">
            <h2 class="mb-0 text-uppercase font-weight-bold text-primary">Cursos:</h2>
            <a href="{{ route('curso.create') }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Crear nuevo Curso <i class="fa fa-plus fa-sm"></i>
            </a>
        </div>
        <div class="row bg-white">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>
            <div class="col-lg-2 mb-4 mt-2">
                <button class="btn btn-sm btn-primary" id="todos">
                    Todos: {{ $cant }}
                </button>
            </div>
            <div class="col-lg-2 mb-4 mt-2">
                <button class="btn btn-sm btn-success" id="inicial">
                    Inicial: {{ $inicial }}
                </button>
            </div>
            <div class="col-lg-2 mb-4 mt-2">
                <div class="btn btn-sm btn-info" id="eib">
                    Primaria EIB: {{ $EIB }}
                </div>
            </div>
            <div class="col-lg-6 mb-4 mt-2">
                <input type="text" class="form-control form-control-sm" id="buscador" placeholder="Buscar curso...">
            </div>
            <div class="col-lg-12" id="tablaCursos">
                <div id="tablaCursos" class="table-responsive table-bordered">
                    <table class="table table-hover" style="font-size: 14px">
                        <thead style="background: #205070; color:#fff">
                            <tr>
                                <td>Datos del Curso</td>
                                <td>Competencias</td>
                                <td>Acciones</td>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursos as $curso)
                                <tr>
                                    <td>
                                        <span class="font-weight-bold" style="font-size: 16px">{{ $curso->nombre }}</span>
                                        <ul>
                                            <li><span class="font-weight-bold">Programa/Ciclo:</span>
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    {{ 'EIB' }}
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
                                                    {{ 'Inicial' }}
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif - {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><span class="font-weight-bold">CC:</span> {{ $curso->cc }}</li>
                                            <li><span class="font-weight-bold">Horas:</span> {{ $curso->horas }}</li>
                                            <li><span class="font-weight-bold">Créditos:</span> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            @if ($curso->competencias->isNotEmpty())
                                                @foreach ($curso->competencias as $competencia)
                                                    <li>{{ $competencia->nombre }}</li>
                                                @endforeach
                                            @else
                                                <p>No hay competencias asignadas a este curso.</p>
                                            @endif

                                        </ul>
                                    </td>
                                    <td>
                                        <a href="{{ route('curso.show', $curso->id) }}" class="btn btn-sm btn-info mb-1"
                                            title="Ver Curso"><i class="fa fa-eye fa-sm"></i> </a>
                                        <a href="{{ route('curso.edit', $curso->id) }}" class="btn btn-sm btn-primary mb-1"
                                            title="Editar Curso"><i class="fa fa-sm fa-pen"></i> </a>
                                        <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger mb-1"
                                                onclick="return confirm('¿Estás seguro de eliminar este curso?')"
                                                title="Eliminar Curso">
                                                <i class="fa fa-sm fa-trash"></i>
                                            </button>
                                        </form>
                                        
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-12">
                <div id="tablaInicial" class="table-responsive" style="display: none;">
                    <table class="table table-hover" style="font-size: 15px">
                        <thead style="background: #205070; color:#fff">
                            <tr>
                                <th>Datos del Curso</th>
                                <th>Competencias</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursosInicial as $curso)
                                <tr>
                                    <td><span class="font-weight-bold" style="font-size: 16px">{{ $curso->nombre }}</span>
                                        <ul>
                                            <li><span class="font-weight-bold">Programa/Ciclo:</span>
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    {{ 'EIB' }}
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
                                                    {{ 'Inicial' }}
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif - {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><span class="font-weight-bold">CC:</span> {{ $curso->cc }}</li>
                                            <li><span class="font-weight-bold">Horas:</span> {{ $curso->horas }}</li>
                                            <li><span class="font-weight-bold">Créditos:</span> {{ $curso->creditos }}</li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach ($curso->competencias as $competencia)
                                                <li>{{ $competencia->nombre }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>
                                        <a href="{{ route('curso.show', $curso->id) }}" class="btn btn-sm btn-info"
                                            title="Ver Curso"><i class="fa fa-eye fa-sm"></i> </a>
                                        <a href="{{ route('curso.edit', $curso->id) }}" class="btn btn-sm btn-primary"
                                            title="Editar Curso"><i class="fa fa-sm fa-pen"></i> </a>
                                        <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Estás seguro de eliminar este curso?')"
                                                title="Eliminar Curso">
                                                <i class="fa fa-sm fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
            <div class="col-lg-12">
                <div id="tablaEib" class="table-responsive" style="display: none">
                    <table class="table table-hover" style="font-size: 14px">
                        <thead style="background: #205070; color:#fff">
                            <tr>
                                <th>Datos del Curso</th>
                                <th>Competencias</th>
                                <th>Comp. a Calificar</th>
                                <th>Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($cursosEib as $curso)
                                <tr>
                                    <td><span class="font-weight-bold"
                                            style="font-size: 16px">{{ $curso->nombre }}</span>
                                        <ul>
                                            <li><span class="font-weight-bold">Programa/Ciclo:</span>
                                                @if (str_contains($curso->ciclo->programa->nombre, 'Programa Primaria EIB'))
                                                    {{ 'EIB' }}
                                                @elseif(str_contains($curso->ciclo->programa->nombre, 'Programa Inicial'))
                                                    {{ 'Inicial' }}
                                                @else
                                                    {{ $curso->ciclo->programa->nombre }}
                                                @endif - {{ $curso->ciclo->nombre }}
                                            </li>
                                            <li><span class="font-weight-bold">CC:</span> {{ $curso->cc }}</li>
                                            <li><span class="font-weight-bold">Horas:</span> {{ $curso->horas }}</li>
                                            <li><span class="font-weight-bold">Créditos:</span> {{ $curso->creditos }}
                                            </li>
                                        </ul>
                                    </td>
                                    <td>
                                        <ul>
                                            @foreach ($curso->competencias as $competencia)
                                                <li>{{ $competencia->nombre }}</li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td>Vista ejemplo</td>
                                    <td>
                                        <a href="{{ route('curso.show', $curso->id) }}" class="btn btn-sm btn-info"
                                            title="Ver Curso"><i class="fa fa-eye fa-sm"></i> </a>
                                        <a href="{{ route('curso.edit', $curso->id) }}" class="btn btn-sm btn-primary"
                                            title="Editar Curso"><i class="fa fa-sm fa-pen"></i> </a>
                                        <form action="{{ route('curso.destroy', $curso->id) }}" method="POST"
                                            style="display:inline">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-danger"
                                                onclick="return confirm('¿Estás seguro de eliminar este curso?')"
                                                title="Eliminar Curso">
                                                <i class="fa fa-sm fa-trash"></i></button>
                                        </form>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            $('#inicial').click(function() {
                $('#tablaCursos').hide();
                $('#tablaEib').hide();
                $('#tablaInicial').show();
            });

            $('#todos').click(function() {
                $('#tablaEib').hide();
                $('#tablaInicial').hide();
                $('#tablaCursos').show();
            });

            $('#eib').click(function() {
                $('#tablaInicial').hide();
                $('#tablaCursos').hide();
                $('#tablaEib').show();
            });
        });
    </script>
    <script>
        var input = document.getElementById("buscador");
        input.addEventListener("input", function() {
            var filtro = input.value.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g, "").split(" ");
            var filas = document.querySelectorAll("table tbody tr");
            filas.forEach(function(fila) {
                var textoFila = fila.textContent.toLowerCase().normalize("NFD").replace(/[\u0300-\u036f]/g,
                    "");
                var coincidencia = true;
                filtro.forEach(function(palabra) {
                    if (textoFila.indexOf(palabra) === -1) {
                        coincidencia = false;
                    }
                });
                if (coincidencia) {
                    fila.style.display = "";
                } else {
                    fila.style.display = "none";
                }
            });
        });
    </script>
@endsection
