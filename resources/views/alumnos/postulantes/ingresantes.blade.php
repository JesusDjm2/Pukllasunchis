@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="text-primary font-weight-bold">Seleccionar Ingresantes:</h3>
            <div class="mb-3">
                <button type="button" class="btn btn-primary btn-sm" onclick="mostrarTabla('tablaInicial')">Mostrar
                    Inicial</button>
                <button type="button" class="btn btn-success btn-sm" onclick="mostrarTabla('tablaPrimaria')">Mostrar
                    Primaria</button>
            </div>
            <a href="{{ route('regulares.index') }}" class="btn btn-sm btn-danger">Volver</a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                @if ($errors->any())
                    <div class="alert alert-danger">
                        <ul>
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </div>
        </div>

        <form action="{{ route('postulantes.guardarIngresantes') }}" method="POST">
            @csrf
            <div id="tablaInicial" class="tabla-programa">
                <h4 class="text-primary">Educación Inicial</h4>
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Seleccionar</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Programa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($postulantesInicial as $postulante)
                            <tr>
                                <td>
                                    <input type="checkbox" name="postulantesSeleccionados[]" value="{{ $postulante->id }}">
                                </td>
                                <td>{{ $postulante->apellidos }}, {{ $postulante->nombres }}</td>
                                <td>{{ $postulante->dni }}</td>
                                <td>{{ $postulante->programa }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <!-- Tabla para Educación Primaria (oculta por defecto) -->
            <div id="tablaPrimaria" class="tabla-programa" style="display: none;">
                <h4 class="text-success">Educación Primaria EIB</h4>
                <table class="table table-bordered">
                    <thead class="thead-dark">
                        <tr>
                            <th>Seleccionar</th>
                            <th>Nombre</th>
                            <th>DNI</th>
                            <th>Programa</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($postulantesPrimaria as $postulante)
                            <tr>
                                <td>
                                    <input type="checkbox" name="postulantesSeleccionados[]" value="{{ $postulante->id }}">
                                </td>
                                <td>{{ $postulante->apellidos }}, {{ $postulante->nombres }}</td>
                                <td>{{ $postulante->dni }}</td>
                                <td>{{ $postulante->programa }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>

            <button type="submit" class="btn btn-success">Guardar Ingresantes</button>
        </form>
    </div>

    <script>
        function mostrarTabla(idTabla) {
            document.querySelectorAll('.tabla-programa').forEach(tabla => {
                tabla.style.display = 'none';
            });

            document.getElementById(idTabla).style.display = 'block';
        }
    </script>
@endsection
