@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid bg-white">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h1 class="h3 mb-0 text-gray-800">Filtrar alumnos por campos</h1>
            <a href="{{ url()->previous() }}" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row">
            <div class="col-lg-12 mb-3">
                <input type="text" id="inputBuscar" class="form-control" placeholder="Buscar por texto...">
            </div>
            <div class="col-lg-12">
                <div class="table-responsive">
                    <table id="tablaNumeros" class="table table-hover" style="font-size: 14px">
                        <thead style="background: #205070; color:#fff">
                            <tr>
                                <td class="font-weight-bold">Nombre</td>
                                <td class="font-weight-bold">DNI</td>
                                
                                <td class="font-weight-bold">
                                    <select id="selectCampo" class="form-control form-control-sm campoSelect">
                                        <option value="" selected disabled>Filtrar por:</option>
                                        <!-- Opciones de campos se generarán dinámicamente en JavaScript -->
                                    </select>
                                </td>
                                <td>Programa</td>
                                <td>Ciclo</td>
                                <td>Ver</td>
                            </tr>
                        </thead>
                        <tbody id="tbodyAlumnos">
                            <!-- Los datos de los alumnos se cargarán dinámicamente en JavaScript -->
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script>
        $(document).ready(function() {
            var alumnos = @json($alumnos);
            var campos = @json($campos);
            var routeShowAlumno = "{{ route('alumnos.show', ['alumno' => ':id']) }}";
    
            var selectCampo = $('#selectCampo');
            $.each(campos, function(index, campo) {
                selectCampo.append('<option value="' + campo + '">' + campo + '</option>');
            });
    
            selectCampo.on('change', function() {
                var campoSeleccionado = $(this).val();
                actualizarTabla(campoSeleccionado);
            });
    
            function actualizarTabla(campo) {
                var tbody = $('#tbodyAlumnos');
                tbody.empty();
    
                $.each(alumnos, function(index, alumno) {
                    var fila = '<tr><td>' + alumno.apellidos + ', ' + alumno.nombres + '</td>';
                    fila += '<td>' + alumno.dni + '</td>';
                    fila += '<td>' + alumno[campo] + '</td>';
                    
                    // Agregar los campos de programa y ciclo
                    fila += '<td>' + (alumno.programa ? alumno.programa.nombre : '') + '</td>';
                    fila += '<td>' + (alumno.ciclo ? alumno.ciclo.nombre : '') + '</td>';
    
                    fila += '<td><a href="' + routeShowAlumno.replace(':id', alumno.id) +
                        '" class="btn btn-info btn-sm" title="Ver registro completo"><i class="fa fa-eye fa-sm"></i></a></td></tr>';
                    tbody.append(fila);
                });
            }
    
            var primerCampo = campos[0];
            actualizarTabla(primerCampo);
        });
    
    </script>
    <script>
        $(document).ready(function() {
            // Función para remover las tildes de una cadena
            function quitarTildes(cadena) {
                var mapaTildes = {
                    'á': 'a',
                    'é': 'e',
                    'í': 'i',
                    'ó': 'o',
                    'ú': 'u',
                    'Á': 'A',
                    'É': 'E',
                    'Í': 'I',
                    'Ó': 'O',
                    'Ú': 'U'
                };

                return cadena.replace(/[áéíóúÁÉÍÓÚ]/g, function(letra) {
                    return mapaTildes[letra];
                });
            }

            // Función para filtrar la tabla cuando cambia el valor del campo de búsqueda
            $('#inputBuscar').on('input', function() {
                var valorBusqueda = quitarTildes($(this).val()
                    .toLowerCase()); // Obtener el valor de búsqueda sin tildes en minúsculas
                var palabrasClave = valorBusqueda.split(
                    ' '); // Dividir el valor de búsqueda en palabras clave
                $('#tablaNumeros tbody tr').each(function() {
                    var filaVisible = false;
                    var filaTexto = quitarTildes($(this).text()
                        .toLowerCase()); // Obtener el texto de la fila sin tildes en minúsculas
                    palabrasClave.forEach(function(palabra) {
                        if (filaTexto.includes(palabra)) {
                            filaVisible = true;
                        } else {
                            filaVisible = false;
                            return false; // Si alguna palabra clave no coincide, no es necesario seguir buscando
                        }
                    });
                    if (filaVisible) {
                        $(this).show(); // Mostrar la fila si todas las palabras clave coinciden
                    } else {
                        $(this).hide(); // Ocultar la fila si alguna palabra clave no coincide
                    }
                });
            });
        });
    </script>
@endsection
