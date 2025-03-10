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
                                                <td rowspan="7" class="text-center align-middle">
                                                    <img src="{{ asset($postulante->foto) }}" alt="Foto"
                                                        class="img-thumbnail" width="150">
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
                                            <tr>
                                                <th>Género</th>
                                                <td>{{ $postulante->genero ? 'Masculino' : 'Femenino' }}</td>
                                            </tr>
                                            <tr>
                                                <th>Teléfono</th>
                                                <td>{{ $postulante->numero }}</td>
                                            </tr>
                                            <tr>
                                                <th>Beca</th>
                                                <td>{{ $postulante->estudio_beca ? 'Si' : 'No' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <div class="row mt-3">
                            <!-- Segunda parte con datos en 2 columnas -->
                            <div class="col-lg-6">
                                <div class="table-responsive">
                                    <table class="table table-bordered tabla-sin-eventos">
                                        <tbody>
                                            <tr>
                                                <th>Domicilio</th>
                                                <td>{{ $postulante->direccion }}</td>
                                            </tr>
                                            <tr>
                                                <th>Fecha de Nacimiento</th>
                                                <td>{{ $postulante->fecha_nacimiento->format('d/m/Y') }} →
                                                    {{ $postulante->fecha_nacimiento->age }} años</td>
                                            </tr>
                                            <tr>
                                                <th>Lugar de Nacimiento</th>
                                                <td>{{ $postulante->lugar_nacimiento }}</td>
                                            </tr>
                                            <tr>
                                                <th>Departamento de Nacimiento</th>
                                                <td>{{ $nombre_departamento }}</td>
                                            </tr>
                                            <tr>
                                                <th>Provincia de Nacimiento</th>
                                                <td>{{ $nombre_provincia }}</td>
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
                                                <th>Distrito de Nacimiento</th>
                                                <td>{{ $nombre_distrito }}</td>
                                            </tr>
                                            <tr>
                                                <th>Primera Lengua</th>
                                                <td>{{ $postulante->lengua_1 }}</td>
                                            </tr>
                                            <tr>
                                                <th>Estado Civil</th>
                                                <td>{{ ucfirst($postulante->estado_civil ?? 'N/A') }}</td>
                                            </tr>
                                            <tr>
                                                <th>Segunda Lengua</th>
                                                <td>{{ $postulante->lengua_2 }}</td>
                                            </tr>
                                            <tr>
                                                <th>Número de Hijos</th>
                                                <td>{{ $postulante->num_hijos ?? 'N/A' }}</td>
                                            </tr>
                                        </tbody>
                                    </table>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
                <div class="card mt-3">
                    <div class="card-header bg-dark text-white">
                        <h5>Datos del Colegio y Situación Personal</h5>
                    </div>
                    <div class="card-body">
                        <div class="table-responsive">
                            <table class="table table-bordered tabla-sin-eventos">
                                <tbody>
                                    <tr>
                                        <th>Colegio</th>
                                        <td>{{ $postulante->colegio }}</td>
                                        <th>Código del Colegio</th>
                                        <td>{{ $postulante->codigo_colegio }}</td>
                                    </tr>
                                    <tr>
                                        <th>Gestión</th>
                                        <td>{{ ucfirst($postulante->gestion_colegio) }}</td>
                                        <th>Dirección del Colegio</th>
                                        <td>{{ $postulante->direccion_colegio ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Distrito Colegio</th>
                                        <td>{{ $postulante->distrito_colegio ?? 'N/A' }}</td>
                                        <th>Provincia Colegio</th>
                                        <td>{{ $postulante->provincia_colegio ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Departamento Colegio</th>
                                        <td>{{ $postulante->departamento_colegio ?? 'N/A' }}</td>
                                        <th>Año de Término</th>
                                        <td>{{ $postulante->ano_termino_colegio ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Promedio</th>
                                        <td>{{ $postulante->promedio_colegio ?? 'N/A' }}</td>
                                        <th>¿Trabajas?</th>
                                        <td>{{ $postulante->trabajas ? 'Sí' : 'No' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Dónde Trabajas</th>
                                        <td>{{ $postulante->donde_trabajas ?? 'N/A' }}</td>
                                        <th>Cargo</th>
                                        <td>{{ $postulante->cargo_trabajas ?? 'N/A' }}</td>
                                    </tr>
                                    <tr>
                                        <th>Describe tu EESP</th>
                                        <td colspan="3">{{ $postulante->describe_eespp }}</td>
                                    </tr>
                                </tbody>
                            </table>
                            <div class="table-responsive">
                            </div>
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
                                            <th>DNI</th>
                                            <td>
                                                @if ($postulante->dni_pdf)
                                                    <a href="{{ asset($postulante->dni_pdf) }}" target="_blank">Ver
                                                        Documento</a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>
                                            <th>Partida de Nacimiento</th>
                                            <td>
                                                @if ($postulante->partida_nacimiento_pdf)
                                                    <a href="{{ asset($postulante->partida_nacimiento_pdf) }}"
                                                        target="_blank">Ver
                                                        Documento</a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Certificado de Secundaria</th>
                                            <td>
                                                @if ($postulante->certificado_secundaria_pdf)
                                                    <a href="{{ asset($postulante->certificado_secundaria_pdf) }}"
                                                        target="_blank">Ver
                                                        Documento</a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>
                                            <th>Voucher de Pago</th>
                                            <td>
                                                @if ($postulante->voucher_pago)
                                                    <a href="{{ asset($postulante->voucher_pago) }}" target="_blank">Ver
                                                        Documento</a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Declaración Jurada de Salud</th>
                                            <td>
                                                @if ($postulante->declaracion_jurada_salud_pdf)
                                                    <a href="{{ asset($postulante->declaracion_jurada_salud_pdf) }}"
                                                        target="_blank">Ver Documento</a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>
                                            <th>Declaración Jurada de Documentos</th>
                                            <td>
                                                @if ($postulante->declaracion_jurada_documentos_pdf)
                                                    <a href="{{ asset($postulante->declaracion_jurada_documentos_pdf) }}"
                                                        target="_blank">Ver Documento</a>
                                                @else
                                                    <em>*Sin archivo adjunto*</em>
                                                @endif
                                            </td>
                                        </tr>
                                        <tr>
                                            <th>Declaración Jurada de Conectividad</th>
                                            <td>
                                                @if ($postulante->declaracion_jurada_conectividad_pdf)
                                                    <a href="{{ asset($postulante->declaracion_jurada_conectividad_pdf) }}"
                                                        target="_blank">Ver Documento</a>
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

        @endsection
