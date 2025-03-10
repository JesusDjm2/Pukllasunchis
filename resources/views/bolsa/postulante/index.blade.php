@extends('layouts.bolsa')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="row">
            <div class="col-12 mb-3">
                <h4 class="mt-3 font-weight-bold text-primary">
                    Ficha técnica bolsa de trabajo:
                </h4>
                <p>Espacio para postular a la bolsa de trabajo Pukllasunchis, sube tu CV y datos personales para poder ser
                    publicados en nuestra página web.</p>
                @if (auth()->user()->postulante)
                    <a href="{{ route('postulante.edit', ['postulante' => $user->postulante->id]) }}"
                        class="d-none d-sm-inline-block btn btn-sm btn-success shadow-sm">
                        Editar datos <i class="fa fa-edit fa-sm"></i>
                    </a>
                @endif
                @if (Session::has('success'))
                    <div class="alert alert-info alert-dismissible fade show" role="alert">
                        {{ Session::get('success') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
                @if (session('error'))
                    <div class="alert alert-danger">
                        {{ session('error') }}
                        <button type="button" class="close" data-dismiss="alert" aria-label="Close">
                            <span aria-hidden="true">&times;</span>
                        </button>
                    </div>
                @endif
            </div>
            @if (auth()->user()->postulante)
                <div class="col-lg-12 pb-3 border-bottom d-flex justify-content-center align-items-center pb-2">
                    <div style="height: 180px; width:160px; border:1px solid #80808036">
                        <img src="{{ $user->postulante->img }}" style="object-fit: cover; width:100%; height:100%">
                    </div>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Nombre completo:</label>
                    <p>{{ $user->postulante->nombre }} {{ $user->postulante->apellidos }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Teléfono:</label>
                    <p>{{ $user->postulante->numero }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Email:</label>
                    <p>{{ $user->postulante->email }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Perfil:</label>
                    @if ($user->perfil)
                        <p>{{ $user->perfil }}</p>
                    @else
                        <p>Sin perfil asignado</p>
                    @endif
                </div>

                <div class="col-lg-3 pb-3 pt-3 border-left border-bottom border-right hoverPostulante">
                    <label class="font-weight-bold">Edad:</label>
                    <p>{{ $user->postulante->edad }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Idioma(s):</label>
                    <p>{{ $user->postulante->idioma }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-right hoverPostulante">
                    <label class="font-weight-bold">DNI:</label>
                    <p>{{ $user->postulante->dni }}</p>
                </div>
                <div class="col-lg-3 pb-3 pt-3 border-bottom border-right hoverPostulante">
                    <label class="font-weight-bold">Carrera:</label>
                    <p>{{ $user->postulante->programa->nombre }}</p>
                </div>
                <div class="col-lg-12 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Otros estudios:</label>
                    <p>{{ $user->postulante->otros_estudios }}</p>
                </div>
                <div class="col-lg-12 pb-3 pt-3 border-bottom border-left border-right hoverPostulante">
                    <label class="font-weight-bold">Descripcino breve:</label>
                    <p>{{ $user->postulante->descripcion }}</p>
                </div>
                <div class="col-lg-12">
                    <div class="row">
                        <div class="col-lg-12 text-center">
                            <h4 class="mt-3 mb-2">Sociales</h4>
                        </div>
                        @if (auth()->user()->postulante && auth()->user()->postulante->cv)
                            <div
                                class="col-lg-4 pb-3 pt-3 border-bottom border-right border-left text-center hoverPostulante">
                                <a href="{{ asset(auth()->user()->postulante->cv) }}" target="_blank"
                                    class="btn btn-sm btn-danger">
                                    Ver CV &nbsp;<i class="fas fa-file-pdf fa-1x"></i> </a>
                            </div>
                        @endif
                        <div class="col-lg-4 pb-3 pt-3 border-bottom border-right text-center hoverPostulante">
                            <a href="{{ $user->postulante->facebook }}" target="_blank" class="btn-primary btn-sm">
                                Ver perfil de FaceBook <i class="fa-brands fa-facebook-square"></i></a>
                        </div>
                        <div class="col-lg-4 pb-3 pt-3 border-bottom border-right text-center hoverPostulante">
                            <a href="{{ $user->postulante->linkedin }}" target="_blank" class="btn-info btn-sm">
                                Ver perfil de LinkedIn <i class="fa-brands fa-linkedin"></i>
                            </a>
                        </div>
                    </div>
                </div>
            @else
                <div class="col-lg-12 pb-3 pt-3 border-bottom">
                    <a href="{{ route('postulante.create') }}" class="btn btn-sm btn-primary">
                        Registrar datos
                    </a>
                </div>
            @endif
            <div class="col-lg-12">
                <h4 class="mt-3 font-weight-bold text-primary">
                    Oportunidades laborales:
                </h4>
            </div>

            <!-- Botones Elegantes -->
            <div class="col-lg-12 mt-3">
                {{-- <div class="d-flex justify-content-around">
                    Prácticas Profesionales I.E. 50813 de Zona Rural
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTrabajo1">
                        Más Info
                    </button>

                    <!-- Botón 2 -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTrabajo2">
                        Prácticas Pre- Profesionales C.E.I.P. San Mateo Dreams
                    </button>

                    <!-- Botón 3 -->
                    <button type="button" class="btn btn-primary" data-toggle="modal" data-target="#modalTrabajo3">
                       Solicitud de Docentes Egresados
                    </button>
                </div> --}}
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">#</th>
                                <th scope="col">Opción Laboral</th>
                                <th scope="col">Detalles</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>
                            <!-- Fila 1 -->
                            <tr>
                                <td>1</td>
                                <td>Prácticas Profesionales </td>
                                <td>Solicita estudiantes de la EESP Pukllasunchis para realizar prácticas profesionales en
                                    la I.E. 50813 de Zona Rural</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#modalTrabajo1">
                                        Más Info
                                    </button>
                                </td>
                            </tr>
                            <!-- Fila 2 -->
                            <tr>
                                <td>2</td>
                                <td>Prácticas Pre-Profesionales </td>
                                <td>Estudiantes de la carrera profesional de educación Inicialpara eld esarrollo de práticas pre-profesionales.</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#modalTrabajo2">
                                        Más Info
                                    </button>
                                </td>
                            </tr>
                            <!-- Fila 3 -->
                            <tr>
                                <td>3</td>
                                <td>Solicitud de Docentes Egresados</td>
                                <td>Institución educativa Privado Yachaywasi School, solicita docentes egresados.</td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#modalTrabajo3">
                                        Más Info
                                    </button>
                                </td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            <!-- Modal 1 -->
            <div class="modal fade" id="modalTrabajo1" tabindex="-1" role="dialog" aria-labelledby="modalTrabajo1Label"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTrabajo1Label">Prácticas Profesionales I.E. 50813 de Zona Rural
                            </h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img src="{{ asset('img/blog/Oportunidad-laboral-Pukllasunchis-1.jpeg') }}" width="100%"
                                alt="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="#" class="btn btn-primary">Aplicar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal 2 -->
            <div class="modal fade" id="modalTrabajo2" tabindex="-1" role="dialog"
                aria-labelledby="modalTrabajo2Label" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTrabajo2Label">Prácticas Pre- Profesionales C.E.I.P. San
                                Mateo Dreams</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img src="{{ asset('img/blog/Oportunidad-laboral-Pukllasunchis-2.jpeg') }}" width="100%"
                                alt="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="#" class="btn btn-success">Aplicar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal 3 -->
            <div class="modal fade" id="modalTrabajo3" tabindex="-1" role="dialog"
                aria-labelledby="modalTrabajo3Label" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTrabajo3Label">Solicitud de Docentes Egresados</h5>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <iframe src="{{ asset('img/blog/Solicitud-de-docentes-egresados.pdf') }}" width="100%"
                                height="500px" style="border: none;">
                            </iframe>
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="#" class="btn btn-warning">Aplicar</a>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <script src="https://code.jquery.com/jquery-3.5.1.slim.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@4.5.2/dist/js/bootstrap.bundle.min.js"></script>
@endsection
