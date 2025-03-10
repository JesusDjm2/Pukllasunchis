@extends('layouts.admin')
@section('contenido')
    <div class="container-fluid">
        <div class="d-sm-flex align-items-center justify-content-between mb-4">
            <h2 class="h3 mb-0 text-gray-800">
                Alumno: <strong>{{ explode(' ', $alumno->apellidos)[0] }} {{ explode(' ', $alumno->nombres)[0] }}</strong>
            </h2>
            <a href="javascript:history.go(-1)" class="d-none d-sm-inline-block btn btn-sm btn-primary shadow-sm">
                Volver
            </a>
        </div>
        <div class="row"> 
            <div class="col-lg-12 table-responsive">
                <table class="table table-bordered bg-white table-striped">
                    <tbody>
                        <tr>
                            <td colspan="4" class="table-dark text-center">Carrera</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Programa:</td>
                            <td><a
                                    href="{{ route('programa.show', ['programa' => $alumno->programa->id]) }}">{{ $alumno->programa->nombre }}</a>
                            </td>
                            <td class="font-weight-bold">Ciclo:</td>
                            <td><a
                                    href="{{ route('ciclo.show', ['ciclo' => $alumno->ciclo->id]) }}">{{ $alumno->ciclo->nombre }}</a>
                            </td>
                        </tr>
                        <tr>
                            <td colspan="2" class="font-weight-bold">Cursos:</td>
                            <td colspan="2">
                                @foreach ($alumno->ciclo->cursos as $curso)
                                    <li>
                                        <a
                                            href="{{ route('curso.show', ['curso' => $curso->id]) }}">{{ $curso->nombre }}</a>
                                    </li>
                                @endforeach
                            </td>
                        </tr>
                        {{-- @if (isset($alumno->user->pendiente))
                            <tr class="bg-danger text-white">
                                <td colspan="2">Curso a cargo:</td>
                                <td colspan="2">
                                    {{ $alumno->user->pendiente }}
                                </td>
                            </tr>
                        @endif --}}
                        @if (isset($alumno->user->pendiente))
                            <tr class="bg-danger text-white">
                                <td colspan="2">Curso(s) a cargo:</td>
                                <td colspan="2">
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
                            <td colspan="4" class="table-dark text-center">Datos Personales</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold" colspan="2">Nombre Completo:</td>
                            <td colspan="2">{{ $alumno->nombres }} {{ $alumno->apellidos }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Email:</td>
                            <td>{{ $alumno->email }}</td>
                            <td class="font-weight-bold">DNI:</th>
                            <td>{{ $alumno->dni }}</td>
                        </tr>

                        <tr>
                            <td class="font-weight-bold">Número:</td>
                            <td>{{ $alumno->numero }}</td>
                            <td class="font-weight-bold">Número de referencia:</td>
                            <td>{{ $alumno->numero_referencia }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Procedencia Familiar:</td>
                            <td>{{ $procedencia[$alumno->procedencia_familiar] }}</td>
                            <td class="font-weight-bold">Domicilio:</td>
                            <td>{{ $alumno->direccion }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Te consideras:</td>
                            <td>{{ $consideras[$alumno->te_consideras] }}</td>
                            <td class="font-weight-bold">Lengua 1:</td>
                            <td>{{ $alumno->lengua_1 }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Lengua 2:</td>
                            <td>{{ $alumno->lengua_2 }}</td>
                            <td class="font-weight-bold">Estado Civil:</td>
                            <td>{{ $alumno->estado_civil }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Eres padre/madre soltero(a):</td>
                            <td>{{ $alumno->p_m_soltero == 1 ? 'Sí' : 'No' }}</td>
                            <td class="font-weight-bold">Cantidad de hijos(a):</td>
                            <td>{{ $alumno->num_hijos }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Sector socioeconómico:</td>
                            <td>{{ $sector[$alumno->sector_socioeconomico] }}</td>
                            @if (stristr($alumno->num_comprobante, 'Beca'))
                                <td class="font-weight-bold text-white bg-success">N° de Comprobante:</td>
                                <td class="bg-success text-white">Beca</td>
                            @else
                                <td class="font-weight-bold">N° de Comprobante:</td>
                                <td>{{ $alumno->num_comprobante }}</td>
                            @endif
                            {{-- <td class="bg-success text-white">{{ stristr($alumno->num_comprobante, 'Beca') ? 'Beca' : $alumno->num_comprobante }}
                            </td>
                            <td>{{$alumno->num_comprobante}}</td> --}}
                        </tr>
                        <tr>
                            <td colspan="4" class="table-dark text-center">Aspectos Familiares</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Con quienes vive:</td>
                            <td>{{ $alumno->convivientes }}</td>
                            <td class="font-weight-bold">Quien mantiene su hogar:</td>
                            <td>{{ $alumno->quien_mantiene }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dependientes en el hogar(menores):</td>
                            <td>{{ $alumno->cant_dependientes_child }}</td>

                            <td class="font-weight-bold">Dependientes en el hogar(tercera edad):</td>
                            <td>{{ $alumno->cant_dependientes_old }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dependientes en el hogar(otros):</td>
                            <td>{{ $alumno->cant_dependientes_otros }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="table-dark text-center">Aspectos Educativos</td>
                        </tr>
                        <!-- Aspectos Educativos -->
                        <tr>
                            <td class="font-weight-bold">Estudio/Beca:</td>
                            <td>{{ $alumno->estudio_beca }}</td>
                            <td class="font-weight-bold">Origen Beca:</td>
                            <td>{{ $alumno->origen_beca }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Postulaciones EESP:</td>
                            <td>{{ $alumno->postulaciones_eesp }}</td>
                            <td class="font-weight-bold">Postulaciones Inst/Uni:</td>
                            <td>{{ $alumno->postulaciones_inst_uni }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Postulaciones Otros:</td>
                            <td>{{ $alumno->postulaciones_otros }}</td>
                            <td class="font-weight-bold">Tipo de Preparación:</td>
                            <td>{{ $alumno->tipo_preparacion }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Motivo para estudiar en EESP:</td>
                            <td>{{ $alumno->motivo_estudio_eesp }}</td>
                            <td class="font-weight-bold">Motivo para estudiar docencia:</td>
                            <td>{{ $alumno->motivo_docencia }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Motivo por el cual elegiste tu especialidad:</td>
                            <td>{{ $alumno->motivo_especialidad }}</td>
                            <td class="font-weight-bold">¿Tienes acceso a internet en casa?</td>
                            <td>{{ $alumno->internet == 1 ? 'Sí' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">¿En caso la respuesta anterior haya sido No, ¿desde qué lugar se
                                conecta Ud. a internet?</td>
                            <td>{{ $alumno->internet_lugar }}</td>
                            <td class="font-weight-bold">Principal servicio de internet usado:</td>
                            <td>{{ $alumno->servicio_internet }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dispositivo de Internet usado:</td>
                            <td>{{ $alumno->dispositivo_internet }}</td>
                            <td class="font-weight-bold">Es de uso propio o compartido:</td>
                            <td>{{ $alumno->propio_compartido == 1 ? 'Propio' : 'Compartido' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Usas correo electrónico:</td>
                            <td>{{ $alumno->correo == 1 ? 'Si' : 'No' }}</td>
                            <td class="font-weight-bold">Número de Horas de Estudio:</td>
                            <td>{{ $alumno->num_hrs_estudio }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold"> Forma que prefiere estudiar:</td>
                            <td>{{ $alumno->forma_estudio }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="table-dark text-center">Aspectos Socioeconómicos</td>
                        </tr>
                        <!-- Aspectos Socioeconómicos -->
                        <tr>
                            <td class="font-weight-bold">¿Actualmente Trabaja?:</td>
                            <td>{{ $alumno->trabajas == 1 ? 'Si' : 'No' }}</td>
                            <td class="font-weight-bold">Lugar de Trabajo:</td>
                            <td>{{ $alumno->donde_trabajas }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Ingreso Mensual:</td>
                            <td>{{ $alumno->ingreso_mensual }}</td>
                            <td class="font-weight-bold">Egreso:</td>
                            <td>{{ $alumno->egreso }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Horas Laboradas por Semana:</td>
                            <td>{{ $alumno->hrs_laboradas_sem }}</td>
                            <td class="font-weight-bold">¿Recibes Ayuda Económica?:</td>
                            <td>{{ $alumno->ayuda_economica == 1 ? 'Si' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Tiempo de Ayuda Económica:</td>
                            <td>{{ $alumno->tiempo_ayuda }}</td>
                            <td class="font-weight-bold">Tipo de Apoyo a la Formación:</td>
                            <td>{{ $alumno->tipo_apoyo_formacion }}</td>
                        </tr>
                        <tr>
                            <td colspan="4" class="table-dark text-center">Aspectos Vivienda</td>
                        </tr>
                        <!-- Aspectos de la Vivienda -->
                        <tr>
                            <td class="font-weight-bold">Tipo de Vivienda:</td>
                            <td>{{ $alumno->tipo_vivienda }}</td>
                            <td class="font-weight-bold">Situación de la Vivienda:</td>
                            <td>{{ $alumno->situacion_vivienda }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Dormitorios en la Vivienda:</td>
                            <td>{{ $alumno->dormitorios_vivienda }}</td>
                            <td class="font-weight-bold">Baños en la Vivienda:</td>
                            <td>{{ $alumno->banos_vivienda }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Material de la Vivienda:</td>
                            <td>{{ $alumno->material_vivienda }}</td>
                            <td class="font-weight-bold">Bienes en la Vivienda:</td>
                            <td>{{ $alumno->bienes_vivienda }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Horas Disponibles de Agua:</td>
                            <td>{{ $alumno->hrs_disponibles_agua }}</td>
                            <td class="font-weight-bold">Horas Disponibles de Desagüe:</td>
                            <td>{{ $alumno->hrs_disponibles_desague }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Horas Disponibles de Luz:</td>
                            <td>{{ $alumno->hrs_disponibles_luz }}</td>
                            <td class="font-weight-bold">Otros Servicios:</td>
                            <td>{{ $alumno->otros_servicios }}</td>
                        </tr>
                        <!-----Aspectos Salud-------->
                        <tr>
                            <td colspan="4" class="table-dark text-center">Aspectos Salud</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Problemas de Salud:</td>
                            <td>{{ $alumno->problemas_salud ? 'Sí' : 'No' }}</td>
                            <td class="font-weight-bold">Última Consulta:</td>
                            <td>{{ $alumno->ultima_consulta ? 'Sí' : 'No' }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Motivo de la Consulta:</td>
                            <td>{{ $alumno->motivo_consulta }}</td>
                            <td class="font-weight-bold">Tipo de Seguro:</td>
                            <td>{{ $alumno->tipo_seguro }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Familiar con Problemas de Salud:</td>
                            <td>{{ $alumno->familiar_salud ? 'Sí' : 'No' }}</td>
                        </tr>
                        <!-----Aspectos Culturales-------->
                        <tr>
                            <td colspan="4" class="table-dark text-center">Aspectos Culturales</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Frecuencia de Lectura:</td>
                            <td>{{ $alumno->frecuencia_lectura }}</td>
                            <td class="font-weight-bold">Acceso a Lectura:</td>
                            <td>{{ $alumno->acceso_lectura }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Visitas a Museos:</td>
                            <td>{{ $alumno->visitas_museos }}</td>
                        </tr>
                        <!-----Aspectos Adicionales-------->
                        <tr>
                            <td colspan="4" class="table-dark text-center">Aspectos Adicionales</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold">Actividades en Internet:</td>
                            <td>{{ $alumno->actividades_internet }}</td>
                            <td class="font-weight-bold">Habilidades:</td>
                            <td>{{ $alumno->habilidades }}</td>
                        </tr>
                        <tr>
                            <td class="font-weight-bold"> Considera que dispone de tiempo libre para realizar diversas
                                actividades que le gusten:</td>
                            <td>{{ $alumno->tiempo_libre ? 'Sí' : 'No' }}</td>
                        </tr>

                    </tbody>
                </table>
            </div>
        </div>
    </div>
@endsection
