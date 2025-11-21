@extends('layouts.alumno')
@section('contenido')
    <div class="container-fluid bg-white pt-2">
        <div class="row">
            <div class="col-12 mb-3">
                {{-- <h4 class="mt-3 font-weight-bold text-primary">
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
                @endif --}}
            </div>
            {{-- @if (auth()->user()->postulante)
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
            @endif --}}
            <div class="col-lg-12">
                <h4 class="mt-3 font-weight-bold text-primary">
                    Oportunidades laborales:
                </h4>
            </div>

            <!-- Botones Elegantes -->
            <div class="col-lg-12 mt-3">
                <div class="table-responsive">
                    <table class="table table-bordered table-hover">
                        <thead class="thead-dark">
                            <tr>
                                <th scope="col">Mes</th>
                                <th scope="col">Opción Laboral</th>
                                <th scope="col">Detalles</th>
                                <th scope="col">Acciones</th>
                            </tr>
                        </thead>
                        <tbody>                        
                            <tr>
                                <td>Noviembre</td>
                                <td>📢 CUNAMAS: Convocatoria 2025 – Acompañantes Técnicos, Especialistas en Nutrición,
                                    Abogados y Otros</td>
                                <td>
                                    El Programa Nacional CUNA MÁS anuncia su convocatoria vigente con un total de <strong>34
                                        vacantes</strong>
                                    disponibles en diversas regiones del país.<br><br>

                                    <strong>Regiones:</strong><br>
                                    Amazonas, Arequipa, Cajamarca, Cusco, La Libertad, Lambayeque, Lima, Loreto, Madre de
                                    Dios, Moquegua,
                                    Pasco, Piura, Puno, San Martín, Tumbes y Ucayali.<br><br>

                                    <strong>Fecha de publicación:</strong> 15/11/2025<br>
                                    <strong>Vigente hasta:</strong> 28/11/2025<br>
                                    <strong>Salario:</strong> Entre S/. 1,764.19 y S/. 9,264.19 Soles<br><br>

                                    <a href="https://www.portaltrabajos.pe/2025/11/cunamas-acompanantes-tecnicos-especialistas-nutricion.html?m=1"
                                        target="_blank">
                                        👉 Ver convocatoria completa
                                    </a>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#cunamas">
                                        Más Info
                                    </button>
                                </td>
                            </tr>
                            <div class="modal fade" id="cunamas" tabindex="-1" role="dialog"
                                aria-labelledby="cunamasLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title" id="cunamasLabel">
                                                Convocatoria CUNAMAS – 34 Vacantes 2025
                                            </h3>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>
                                        <div class="modal-body">
                                            <p>
                                                El Programa Nacional CUNA MÁS invita a profesionales de distintas
                                                especialidades
                                                a postular a las vacantes disponibles en varias regiones del país.
                                            </p>

                                            <p><strong>Vacantes:</strong> 34</p>

                                            <p><strong>Regiones:</strong><br>
                                                Amazonas, Arequipa, Cajamarca, Cusco, La Libertad, Lambayeque, Lima, Loreto,
                                                Madre de Dios, Moquegua, Pasco, Piura, Puno, San Martín, Tumbes, Ucayali.
                                            </p>

                                            <p><strong>Fechas:</strong><br>
                                                Publicación: 15/11/2025<br>
                                                Vigente hasta: 28/11/2025
                                            </p>

                                            <p><strong>Rango salarial:</strong> S/. 1,764.19 a S/. 9,264.19 Soles</p>

                                            <p>
                                                Puedes acceder a toda la información y requisitos en el siguiente
                                                enlace:<br>
                                                <a href="https://www.portaltrabajos.pe/2025/11/cunamas-acompanantes-tecnicos-especialistas-nutricion.html?m=1"
                                                    target="_blank">
                                                    👉 Ir a la convocatoria oficial
                                                </a>
                                            </p>
                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                data-dismiss="modal">Cerrar</button>
                                            <a href="https://www.portaltrabajos.pe/2025/11/cunamas-acompanantes-tecnicos-especialistas-nutricion.html?m=1"
                                                target="_blank" class="btn btn-sm btn-primary">Aplicar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <tr>
                                <td>Noviembre</td>
                                <td>📢 Convocatoria Docente 2026 – Colegio Pukllasunchis</td>
                                <td>
                                    El Colegio Pukllasunchis invita a docentes a participar en su proceso de selección
                                    2026.<br><br>

                                    <strong>Perfiles requeridos:</strong>
                                    <ul>
                                        <li>Docente de Nivel Primario</li>
                                        <li>Docente de Quechua – Secundaria</li>
                                        <li>Docente de Inglés – Secundaria</li>
                                        <li>Docente de Música – Inicial, Primaria y Secundaria</li>
                                    </ul>

                                    <strong>Convocatoria:</strong><br>
                                    Inicio: 18/11/2025<br>
                                    Cierre: 21/11/2025<br><br>

                                    <a href="{{ asset('pdf/bolsa-de-trabajo/Convocatoria-docente-Colegio-Pukllasunchis.pdf') }}"
                                        target="_blank">👉 Ver convocatoria completa (PDF)</a>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#puk">
                                        Más Info
                                    </button>
                                </td>
                            </tr>
                            <div class="modal fade" id="puk" tabindex="-1" role="dialog" aria-labelledby="pukLabel"
                                aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <h3 class="modal-title" id="pukLabel">
                                                Convocatoria Docente 2026 – Colegio Pukllasunchis
                                            </h3>
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <p>
                                                Proceso de selección para docentes en diversas especialidades.
                                                Toda la información detallada se encuentra en el PDF adjunto.
                                            </p>

                                            <p><strong>Perfiles solicitados:</strong></p>
                                            <ul>
                                                <li>Docente de Primaria</li>
                                                <li>Docente de Quechua – Secundaria</li>
                                                <li>Docente de Inglés – Secundaria</li>
                                                <li>Docente de Música – Inicial, Primaria y Secundaria</li>
                                            </ul>

                                            <p><strong>Fechas:</strong><br>
                                                Inicio: 18/11/2025<br>
                                                Cierre: 21/11/2025
                                            </p>

                                            <p>
                                                Enviar CV documentado a:<br>
                                                <strong>convocatoria.puk@gmail.com</strong>
                                            </p>

                                            <a href="#" target="_blank">👉 Descargar PDF completo</a>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                data-dismiss="modal">Cerrar</button>
                                            <a href="#" target="_blank" class="btn btn-sm btn-primary">Aplicar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <tr>
                                <td>Noviembre</td>
                                <td>📢 Innova Schools – Convocatoria Docente 2026 (Juliaca y Cusco)</td>
                                <td>
                                    Vacantes para Juliaca y Cusco en diversas especialidades.<br><br>

                                    <strong>Sedes y especialidades:</strong>
                                    <ul>
                                        <li>Juliaca – 3er grado</li>
                                        <li>Juliaca – Inglés volante</li>
                                        <li>Cusco Larapa – Comunicación secundaria</li>
                                        <li>Cusco Huancaro – DPSC + Ciencias primaria</li>
                                    </ul>

                                    <strong>Postulación:</strong><br>
                                    Enviar CV documentado al <strong>914711598</strong> (solo WhatsApp).
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#innova">
                                        Más Info
                                    </button>
                                </td>
                            </tr>
                            <div class="modal fade" id="innova" tabindex="-1" role="dialog"
                                aria-labelledby="innovaLabel" aria-hidden="true">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <img src="{{ asset('pdf/bolsa-de-trabajo/Docente-para-juliaca.jpeg') }}"
                                                width="100%" alt="Convocatoria Innova Schools 2026">

                                            <p class="mt-3">
                                                Vacantes disponibles para las sedes de Juliaca y Cusco.
                                                Para postular, envía tu CV documentado al número indicado en la imagen.
                                            </p>
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                data-dismiss="modal">Cerrar</button>
                                            <a href="#" target="_blank" class="btn btn-sm btn-primary">Aplicar</a>
                                        </div>
                                    </div>
                                </div>
                            </div>

                            <tr>
                                <td>Noviembre</td>
                                <td>📢 Enseña Perú – Programa de Liderazgo (PDL) 2025</td>
                                <td>
                                    Gran oportunidad para jóvenes profesionales que deseen enseñar por dos años en
                                    escuelas públicas de distintas regiones del país.<br><br>

                                    <strong>Convocatoria ampliada hasta:</strong> 8 de noviembre (23:59 h)<br><br>

                                    El Programa de Liderazgo busca profesionales de cualquier carrera que deseen
                                    contribuir al país desde el aula, liderando procesos de transformación educativa
                                    sostenible en comunidades principalmente rurales.<br><br>

                                    <strong>Requisitos principales:</strong>
                                    <ul>
                                        <li>Ser profesional técnico o universitario (o culminar estudios hasta dic. 2025).
                                        </li>
                                        <li>Disponibilidad para trabajar a tiempo completo (2026–2027).</li>
                                        <li>Disponibilidad para cambio de residencia.</li>
                                        <li>Participación obligatoria en ENFOCO (enero–febrero 2026).</li>
                                    </ul>

                                    <strong>Postula aquí:</strong><br>
                                    <a href="https://ensenaperu.org/postula" target="_blank">👉
                                        https://ensenaperu.org/postula</a><br><br>

                                    <strong>Historia inspiradora:</strong><br>
                                    <a href="https://n9.cl/152jy" target="_blank">🎥 Ver historia de Miguel</a>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#pdl">
                                        Ver Imagen
                                    </button>
                                </td>
                            </tr>
                            <div class="modal fade" id="pdl" tabindex="-1" role="dialog"
                                aria-labelledby="pdlLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <img src="{{ asset('pdf/bolsa-de-trabajo/Programa-de-Liderazgo.png') }}"
                                                class="img-fluid" alt="Programa de Liderazgo Enseña Perú">
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                data-dismiss="modal">Cerrar</button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <tr>
                                <td>Noviembre</td>
                                <td>🏫 Colegio Gauss – Oportunidad Laboral 2026</td>

                                <td>
                                    El Colegio Gauss (Urcos) está incorporando a su equipo de trabajo a profesionales
                                    competentes, motivados y con capacidad creativa y de liderazgo, comprometidos con los
                                    objetivos institucionales.<br><br>

                                    <strong>Vacantes:</strong>
                                    <ul>
                                        <li>Docentes de Inicial, Primaria y Secundaria.</li>
                                        <li>Psicólogos, auxiliares y secretaria.</li>
                                    </ul>

                                    <strong>Dirección:</strong> Prolong. Av. Mariano Santos Q-6 (Urcos)<br>
                                    <strong>Entrega de CV:</strong> 03 al 28 de noviembre 2025<br>
                                    <strong>Entrevistas:</strong> 01 al 06 de diciembre 2025<br>
                                    <strong>Contacto:</strong> 989 407 315
                                </td>

                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#gauss">
                                        Ver Imagen
                                    </button>
                                </td>
                            </tr>
                            <div class="modal fade" id="gauss" tabindex="-1" role="dialog"
                                aria-labelledby="gaussLabel" aria-hidden="true">

                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <img src="{{ asset('pdf/bolsa-de-trabajo/Colegio-gauss-oportunidad-laboral.jpeg') }}"
                                                class="img-fluid" alt="Oportunidad Laboral Colegio Gauss">
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                data-dismiss="modal">Cerrar</button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <tr>
                                <td>Agosto</td>
                                <td>🎙️ Casting de Voz – Proyecto de Doblaje al Quechua</td>
                                <td>
                                    Se buscan actores de voz para un proyecto de doblaje al quechua realizado en
                                    Cusco.<br><br>

                                    <strong>Requisitos:</strong>
                                    <ul>
                                        <li>Varones entre 20 y 60 años.</li>
                                        <li>Leer y hablar bien el quechua.</li>
                                        <li>Residir en Cusco.</li>
                                    </ul>

                                    <strong>Contacto por WhatsApp:</strong><br>
                                    <span style="font-size: 1.2em; font-weight:bold;">918592861</span>
                                </td>

                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#castingvoz">
                                        Ver Imagen
                                    </button>
                                </td>
                            </tr>
                            <div class="modal fade" id="castingvoz" tabindex="-1" role="dialog"
                                aria-labelledby="castingvozLabel" aria-hidden="true">
                                <div class="modal-dialog modal-lg" role="document">
                                    <div class="modal-content">

                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal"
                                                aria-label="Close">
                                                <span aria-hidden="true">&times;</span>
                                            </button>
                                        </div>

                                        <div class="modal-body">
                                            <img src="{{ asset('pdf/bolsa-de-trabajo/casting-voz-quechua.jpg') }}"
                                                class="img-fluid" alt="Casting voz Quechua">
                                        </div>

                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-sm btn-secondary"
                                                data-dismiss="modal">Cerrar</button>
                                        </div>

                                    </div>
                                </div>
                            </div>

                            <!--Oportunidades previas-->
                            <tr>
                                <td>Agosto</td>
                                <td>📢 Convocatoria de Capacitación Especializada – Estrategias Didácticas 2025</td>
                                <td>SEl Gobierno Regional Cusco, a través de la Gerencia Regional de Educación y el Proyecto
                                    CREE, invita a los docentes de las 14 UGEL de la región Cusco a participar en la
                                    Capacitación Especializada en Estrategias Didácticas para fortalecer competencias en la
                                    enseñanza de lenguas originarias y lengua extranjera.<br>
                                    <strong>Lenguas incluidas:</strong><br>
                                    <ul>
                                        <li>Quechua – Módulo I-II: Habilidades comunicativas y Estrategias.</li>
                                        <li>Asháninka y Matsigenka – Módulo I-II: Estrategias.</li>
                                        <li>Inglés – Módulo I-II: Habilidades comunicativas y Estrategias.</li>
                                    </ul>
                                    <strong>Requisitos:</strong><br>
                                    <ul>
                                        <li>Ser docente de la Educación Básica Regular (EBR).</li>
                                    </ul>
                                    <strong>Fechas importantes::</strong><br>
                                    <ul>
                                        <li>nscripciones: del 11 al 30 de junio de 2025 (en plataforma virtual PLADEC).</li>
                                        <li>Inicio: 01 de julio de 2025</li>
                                    </ul>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#Trabajoagos1">
                                        Más Info
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>Agosto</td>
                                <td>📢 Oportunidad laboral en CUNAMAS - Urubamba </td>
                                <td>¿Conoces a algún colega psicólogo o profesional afín que esté interesado en trabajar
                                    como Acompañante Técnico en el programa CUNAMAS del MIDIS?
                                    CUNAMAS trabaja con población de 6 a 36 meses, acompañando los procesos de cuidado y
                                    desarrollo en las cuna.<br>
                                    <strong>Detalles:</strong>
                                    <ul>
                                        <li>📍 Lugar: Urubamba</li>
                                        <li>📆 Horario: Lunes a viernes, de 8:00 a.m. a 5:00 p.m.</li>
                                        <li>💰 Honorarios: S/. 3,000 (a todo costo, por Recibo por Honorarios)</li>
                                    </ul>
                                    Interesados/as pueden comunicarse 952351727 para más información.
                                </td>
                                <td>--
                                    {{-- <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#modalTrabajo1">
                                        Más Info
                                    </button> --}}
                                </td>
                            </tr>
                            <tr>
                                <td>1</td>
                                <td>📢 Profesores y Voluntarios para el Año Escolar 2025 – Yuyay Qente, Cusco</td>
                                <td><strong>Detalles:</strong><br>
                                    <ul>
                                        <li>Institución: Yuyay Qente, escuela rural integral.</li>
                                        <li>Ubicación: Fundo Mistiana, distrito de Kosñipata, Paucartambo, Cusco.</li>
                                        <li>Enfoque educativo: Metodologías Montessori, Waldorf y Aprendizaje Basado en
                                            Proyectos, con fuerte conexión a la Amazonía y la restauración ecológica.</li>
                                    </ul>
                                    <strong>Docente de Primaria/Inicial</strong><br>
                                    <ul>
                                        <li>Profesoras tituladas o estudiantes en su último año de prácticas
                                            preprofesionales.</li>
                                        <li>Compromiso con metodologías activas y la educación positiva.</li>
                                    </ul>
                                    <strong>Contacto:</strong><br>
                                    <ul>
                                        <li>Correo: yuyayqente@gmail.com</li>
                                        <li>Enviar CV para más información.</li>
                                        <li>Instagram: @yuyayqente</li>
                                    </ul>
                                    <strong>Inscripciones:</strong>
                                    <ul>
                                        <li><a
                                                href="https://campusvirtual.gereducusco.gob.pe">www.campusvirtual.gereducusco.gob.pe</a>
                                        </li>
                                    </ul>
                                </td>
                                <td>
                                    <button type="button" class="btn btn-primary btn-sm" data-toggle="modal"
                                        data-target="#Trabajoagos2">
                                        Más Info
                                    </button>
                                </td>
                            </tr>
                            <tr>
                                <td>2</td>
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
                                <td>Estudiantes de la carrera profesional de educación Inicialpara eld esarrollo de práticas
                                    pre-profesionales.</td>
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

            <!-- Modal Agosto -->
            <div class="modal fade" id="Trabajoagos1" tabindex="-1" role="dialog" aria-labelledby="Trabajoagos1Label"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h3 class="modal-title" id="Trabajoagos1Label">Prácticas Profesionales I.E. 50813 de Zona
                                Rural
                            </h3>
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img src="{{ asset('img/convocatorias/oportunidad-01.webp') }}" width="100%"
                                alt="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="#" class="btn btn-sm btn-primary">Aplicar</a>
                        </div>
                    </div>
                </div>
            </div>
            <!-- Modal Agosto 2 -->
            <div class="modal fade" id="Trabajoagos2" tabindex="-1" role="dialog" aria-labelledby="Trabajoagos1Label"
                aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            {{-- <h3 class="modal-title" id="Trabajoagos2Label">Prácticas Profesionales I.E. 50813 de Zona
                                Rural
                            </h3> --}}
                            <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                                <span aria-hidden="true">&times;</span>
                            </button>
                        </div>
                        <div class="modal-body">
                            <img src="{{ asset('img/convocatorias/convocatoria-05.jpeg') }}" width="100%"
                                alt="">
                        </div>
                        <div class="modal-footer">
                            <button type="button" class="btn btn-sm btn-secondary" data-dismiss="modal">Cerrar</button>
                            <a href="#" class="btn btn-sm btn-primary">Aplicar</a>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Modal 1 -->
            <div class="modal fade" id="modalTrabajo1" tabindex="-1" role="dialog"
                aria-labelledby="modalTrabajo1Label" aria-hidden="true">
                <div class="modal-dialog" role="document">
                    <div class="modal-content">
                        <div class="modal-header">
                            <h5 class="modal-title" id="modalTrabajo1Label">Prácticas Profesionales I.E. 50813 de Zona
                                Rural
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
