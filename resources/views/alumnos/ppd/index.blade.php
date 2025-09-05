@extends('layouts.profesionalizacion')
@section('contenido')
    <div class="container-fluid pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-1"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="mb-2 text-primary font-weight-bold">Ficha Técnica: </h3>

            <form id="notificar-form" action="{{ route('mostrar-contenido') }}" method="POST" style="display: none;">
                @csrf
                <input type="hidden" name="alumno_id" value="{{ optional(auth()->user()->alumnoB)->id }}">
            </form>
            @if (auth()->user()->alumnoB)
                @if (!Session::has('mostrar_contenido'))
                    <button type="button" class="btn btn-info btn-sm mb-2" id="mostrar-contenido">
                        Notificar que he terminado con mi matrícula. <i class="fa fa-smile"></i>
                    </button>
                @endif
                <a href="{{ route('ppd.edit', auth()->user()->alumnoB->id) }}" class="btn btn-sm btn-primary"
                    title="Editar">
                    Completar Matrícula <i class="fa fa-edit"></i>
                </a>
            @endif
        </div>
        <div class="row bg-white" id="contenido-alumno">
            <div class="col-12">
                @if (Session::has('success'))
                    <div class="alert alert-success alert-dismissible fade show text-center" role="alert">
                        {{ Session::get('success') }}
                        <a type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </a>
                    </div>
                @endif
            </div>
            @if (auth()->user()->alumnoB)
                <div class="col-lg-12">
                    <div class="p-2 table-responsive">
                        <table class="table table-bordered">
                            <tbody>
                                <tr>
                                    <td colspan="4" class="table-dark">Información del Programa</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Programa:</th>
                                    <td>
                                        {{ $alumno->user->programa->nombre }}
                                    </td>
                                </tr>


                                <tr>
                                    <td class="font-weight-bold">Cursos del Programa:</td>
                                    <td>
                                        @foreach ($cursos as $curso)                                        
                                            <li class="d-flex align-items-center justify-content-between curso-item"
                                                style="border-bottom: 1px dashed rgba(128, 128, 128, 0.526)">

                                                <a href="{{ route('curso.show', $curso->id) }}" class="mr-2">
                                                    {{ $loop->iteration }}. {{ $curso->nombre }}
                                                </a>
                                                @if ($curso->docentes->count())
                                                    <small class="text-muted d-block">
                                                        Docente(s): <strong>{{ $curso->docentes->pluck('nombre')->join(', ') }}</strong>
                                                    </small>
                                                @else
                                                    <small class="text-muted d-block">
                                                        Docente(s): <strong>No asignado</strong>
                                                    </small>
                                                @endif

                                                <div class="d-flex align-items-center">
                                                    @if ($curso->silabo)
                                                        <a class="btn btn-success btn-sm d-inline-block"
                                                            href="{{ asset('docentes/silabo/' . $curso->silabo) }}"
                                                            target="_blank" title="Ver Sílabo">
                                                            Ver sílabos <i class="fa fa-eye fa-sm"></i>
                                                        </a>
                                                    @endif
                                                </div>
                                            </li>
                                        @endforeach
                                    </td>
                                </tr>

                                @if (isset($alumno->user->pendiente))
                                    <tr class="bg-danger text-white">
                                        <td>Curso(s) a cargo: <br> <small>*Es responsabilidad del estudiante solicitar la
                                                subsanación de cursos pendientes.</small></td>
                                        <td>
                                            @php
                                                $cursos = explode(',', $alumno->user->pendiente);
                                            @endphp
                                            @foreach ($cursos as $curso)
                                                <li>{{ trim($curso) }}</li>
                                            @endforeach
                                        </td>
                                    </tr>
                                @endif
                                <tr>
                                    <td colspan="4" class="table-dark">Datos Personales</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Nombre Completo:</th>
                                    <td>{{ $alumno->nombres }} {{ $alumno->apellidos }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">DNI:</th>
                                    <td>{{ $alumno->dni }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Correo:</th>
                                    <td>{{ $alumno->email }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Número:</th>
                                    <td>{{ $alumno->numero }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Número de referencia:</th>
                                    <td>{{ $alumno->numero_referencia }}</td>
                                </tr>
                                <tr>
                                    <td class="font-weight-bold">Domicilio:</th>
                                    <td>{{ $alumno->departamento }} - {{ $alumno->provincia }} - {{ $alumno->distrito }},
                                        {{ $alumno->direccion }}</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            @else
                <div class="col-lg-12">
                    <a href="{{ route('formPPD') }}" class="btn btn-primary btn-sm">Por favor complete su formulario</a>
                </div>
            @endif
        </div>
    </div>
    {{-- Script para enviar correo de notificación --}}
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const boton = document.getElementById('mostrar-contenido');
            if (boton) {
                boton.addEventListener('click', function() {
                    this.style.display = 'none';
                    document.getElementById('notificar-form').submit();
                });
            }
        });
    </script>
    {{-- <script>
        document.addEventListener('DOMContentLoaded', function() {
            const alumnoId = {{ optional(auth()->user()->alumnoB)->id ?? 'null' }};
            const rol = 'alumnoB';

            const boton = document.getElementById('mostrar-contenido');
            if (boton) {
                boton.addEventListener('click', function() {
                    this.style.display = 'none';

                    fetch("{{ route('mostrar-contenido') }}", {
                            method: "POST",
                            headers: {
                                "X-CSRF-TOKEN": document.querySelector('meta[name="csrf-token"]')
                                    .getAttribute('content'),
                                "Content-Type": "application/json",
                            },
                            body: JSON.stringify({
                                alumno_id: alumnoId,
                                rol: rol
                            }),
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                alert('Correo enviado correctamente.');
                            } else {
                                alert(data.message || 'Error al enviar el correo.');
                            }
                        })
                        .catch(error => {
                            console.error('Error:', error);
                            alert('Error al enviar el correo.');
                        });
                });
            }
        });
    </script> --}}
    <style>
        .curso-item {
            transition: background-color 0.3s ease;
        }

        .curso-item:hover {
            background-color: #e7e7e7;
        }
    </style>
@endsection
