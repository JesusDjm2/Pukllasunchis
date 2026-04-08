@extends('layouts.admin')
@section('titulo', 'Detalles del Postulante')
@section('contenido')
    <style>
        .tabla-sin-eventos {
            pointer-events: none;
            /* Desactiva los eventos en la tabla */
        }
    </style>
    <div class="container-fluid bg-white pt-2">
        <div class="d-sm-flex align-items-center justify-content-between mb-4 pt-3 pb-3"
            style="border-bottom: 1px dashed #80808078">
            <h3 class="text-primary font-weight-bold">Detalles del Postulante:</h3>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-danger shadow-sm">Volver</a>
        </div>
        <div class="row">
            <div class="col-lg-12">
                <div class="card">
                    <div class="card-header bg-dark text-white">
                        <h5 class="font-weight-bold">Datos Personales</h5>
                    </div>
                    <div class="card-body">
                        <div class="row">
                            <div class="col-lg-12">
                                <div class="table-responsive">
                                    <table class="table table-bordered tabla-sin-eventos">
                                        <tbody>
                                            <tr>
                                                <th>Nombre</th>
                                                <td>{{ $postulante->nombres }} {{ $postulante->apellidos }}</td>
                                                {{-- <td rowspan="7" class="text-center align-middle">
                                                    <img src="{{ asset($postulante->foto) }}" alt="Foto"
                                                        class="img-thumbnail" width="150">
                                                </td> --}}

                                                <td rowspan="7" class="text-center align-middle">
                                                    @if ($postulante->foto)
                                                        <img src="{{ asset($postulante->foto) }}" alt="Foto"
                                                            class="img-thumbnail" width="150">
                                                    @else
                                                        <em>*Sin foto adjunta*</em>
                                                    @endif
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>DNI</th>
                                                <td>{{ $postulante->dni }}</td>
                                            </tr>
                                            <tr>
                                                <th>Email</th>
                                                <td>{{ $postulante->email }}</td>
                                            </tr>
                                            <tr>
                                                <th>Programa</th>
                                                <td>{{ $postulante->programa }}</td>
                                            </tr>
                                            <th>Género</th>
                                            <td>{{ $postulante->genero ?? 'No especificado' }}</td>
                                            <tr>
                                                <th>Teléfono</th>
                                                <td>{{ $postulante->telefono }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <div class="col-lg-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered tabla-sin-eventos">
                                        <tbody>
                                            <tr>
                                                <th>Domicilio</th>
                                                <td>{{ $postulante->domicilio }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha de Nacimiento</th>
                                                <td>
                                                    {{ \Carbon\Carbon::parse($postulante->fecha_nacimiento)->locale('es')->translatedFormat('d \d\e F \d\e\l Y') }}
                                                    → {{ $postulante->edad }} años
                                                </td>
                                            </tr>
                                            <tr>
                                                <th>Lugar de Nacimiento</th>
                                                <td>{{ $postulante->lugar_nacimiento }}</td>
                                            </tr>
                                            <tr>
                                                <th>Número de Hijos</th>
                                                <td>{{ $postulante->hijos ?? 'N/A' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Estado Civil</th>
                                                <td>{{ ucfirst($postulante->estadoCivil ?? 'N/A') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Departamento de Nacimiento</th>
                                                <td>{{ $postulante->departamento_nacimiento }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>

                            <div class="col-lg-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered tabla-sin-eventos">
                                        <tbody>

                                            <tr>
                                                <th>Provincia de Nacimiento</th>
                                                <td>{{ $postulante->provincia_nacimiento }}</td>
                                            </tr>
                                            <tr>
                                                <th>Distrito de Nacimiento</th>
                                                <td>{{ $postulante->distrito_nacimiento }}</td>
                                            </tr>
                                            <tr>
                                                <th>Primera Lengua</th>
                                                <td>{{ $postulante->lengua_1 }}</td>
                                            </tr>
                                            <tr>
                                                <th>Segunda Lengua</th>
                                                <td>{{ $postulante->lengua_2 }}</td>
                                            </tr>
                                            {{-- <tr>
                                                <th>Nivel de Quechua Escrito</th>
                                                <td>{{ $postulante->nivel_quechua_hablado ?? '—' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Nivel de Quechua Hablado</th>
                                                <td>{{ $postulante->nivel_quechua_escrito ?? '—' }}</td>
                                            </tr> --}}
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>

                <div class="card mt-3">
                    <div class="card-header bg-dark text-white">
                        <h5 class="font-weight-bold">Información Laboral y Académica</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered tabla-sin-eventos">
                                <tbody>
                                    <th>Carrera a la que postula:</th>
                                    <td>{{ $postulante->carrera ?? 'N/A' }}</td>
                                    <tr>
                                        <th>¿Trabaja?</th>
                                        <td>{{ $postulante->trabaja ? 'Sí' : 'No' }}</td>
                                        <th>Lugar de Trabajo</th>
                                        <td>{{ $postulante->lugar_trabajo ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Cargo</th>
                                        <td>{{ $postulante->cargo ?? 'N/A' }}</td>

                                    </tr>
                                    <tr>
                                        <th>Opinión sobre la EESPP</th>
                                        <td colspan="3">{{ $postulante->opinionEespp ?? 'N/A' }}</td>
                                    </tr>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header bg-dark text-white">
                        <h5>Datos de la Institución de Origen</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered tabla-sin-eventos">
                                <tbody>

                                    <tr>
                                        <th>Tipo de Institución</th>
                                        <td>{{ $postulante->tipo_institucion ?? 'N/A' }}</td>

                                        <th>Nombre de la Institución</th>
                                        <td>{{ $postulante->nombre_institucion ?? 'N/A' }}</td>

                                        <th>Gestión</th>
                                        <td>{{ ucfirst($postulante->gestion_institucion) ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Dirección</th>
                                        <td colspan="5">
                                            {{ $postulante->direccion_institucion ?? 'N/A' }}
                                        </td>
                                    </tr>

                                    <tr>
                                        <th>Departamento</th>
                                        <td>{{ $postulante->departamento_institucion ?? 'N/A' }}</td>

                                        <th>Provincia</th>
                                        <td>{{ $postulante->provincia_institucion ?? 'N/A' }}</td>

                                        <th>Distrito</th>
                                        <td>{{ $postulante->distrito_institucion ?? 'N/A' }}</td>
                                    </tr>

                                    <tr>
                                        <th>Año de Conclusión</th>
                                        <td>{{ $postulante->anio_conclusion ?? 'N/A' }}</td>

                                        <th>Medio por el que conoció la EESP</th>
                                        <td colspan="3">
                                            {{ $postulante->medio_conocimiento ?? 'N/A' }}
                                        </td>
                                    </tr>

                                </tbody>
                            </table>
                        </div>
                    </div>

                    <div class="card mt-3">
                        <div class="card-header bg-dark text-white">
                            <h5>Documentos Adjuntos</h5>
                        </div>
                        <div class="card-body">
                            <div class="table-responsive">
                                <table class="table table-bordered">
                                    <tbody>
                                        <tr>
                                            <th>DNI (Adjunto)</th>
                                            <td>
                                                @if ($postulante->dni_adjunto)
                                                    <a href="{{ asset($postulante->dni_adjunto) }}" target="_blank">
                                                        Ver Documento
                                                    </a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>

                                            <th>Certificado</th>
                                            <td>
                                                @if ($postulante->certificado)
                                                    <a href="{{ asset($postulante->certificado) }}" target="_blank">
                                                        Ver Documento
                                                    </a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>
                                        </tr>

                                        <tr>
                                            <th>Voucher de Pago</th>
                                            <td colspan="3">
                                                @if ($postulante->voucher)
                                                    <a href="{{ asset($postulante->voucher) }}" target="_blank">
                                                        Ver Documento
                                                    </a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>
                            </div>
                        </div>
                    </div>

                </div>
            </div>
        </div>
    </div>
@endsection
