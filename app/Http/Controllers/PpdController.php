<?php

namespace App\Http\Controllers;

use App\Models\Calificacionesppd;
use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\User;
use Illuminate\Http\Request;

class PpdController extends Controller
{
    public function index(Request $request)
    {
        $alumno = auth()->user()->alumnoB;

        if ($alumno) {
            $alumno->load([
                'user.programa.ciclos.cursos',
                'ciclo.cursos.docentes' => fn ($q) => $q->orderBy('nombre', 'asc'),
            ]);

            // Aplanamos y ordenamos los cursos de todos los ciclos
            $cursos = $alumno->user
                ->programa
                ->ciclos
                ->flatMap->cursos
                ->sortBy('nombre')
                ->values();
        } else {
            $cursos = collect();
        }

        return view('alumnos.ppd.index', compact('alumno', 'cursos'));
    }

    public function form()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $user = auth()->user();
        if ($user) {
            return view('alumnos.vistasAlumnos.formulario', compact('programas', 'ciclos', 'user'));
        }

        return view('alumnos.vistasAlumnos.postulantes');
    }

    public function create()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $user = auth()->user();

        return view('alumnos.vistasAlumnos.formulario', compact('user', 'programas', 'ciclos'));
    }

    /* public function calificacionesppd()
    {
        $alumno = auth()->user()->alumnoB;
        if (!$alumno || !$alumno->ciclo) {
            return view('alumnos.ppd.calificaciones')->with('mensaje', 'No tienes ciclo asignado.');
        }
        $programaId = $alumno->ciclo->programa_id;
        $ciclosConCursos = Ciclo::where('programa_id', $programaId)
            ->with([
                'cursos' => function ($query) use ($alumno) {
                    $query->with([
                        'competencias',
                        'docentes',
                        'calificacionesppd' => function ($q) use ($alumno) {
                            $q->where('ppd_id', $alumno->id);
                        }
                    ]);
                }
            ])
            ->orderBy('nombre')
            ->get();
        return view('alumnos.ppd.calificaciones', compact('alumno', 'ciclosConCursos'));
    } */
    public function calificacionesppd()
    {
        $alumno = auth()->user()->alumnoB;
        if (! $alumno || ! $alumno->ciclo) {
            return view('alumnos.ppd.calificaciones')->with('mensaje', 'No tienes ciclo asignado.');
        }
        $programaId = $alumno->ciclo->programa_id;
        $ciclosConCursos = Ciclo::where('programa_id', $programaId)
            ->with([
                'cursos' => function ($query) use ($alumno) {
                    $query->with([
                        'competencias',
                        'docentes',
                        'calificacionesppd' => function ($q) use ($alumno) {
                            $q->where('ppd_id', $alumno->id);
                        },
                    ]);
                },
            ])
            ->orderBy('nombre')
            ->get();

        // 👇 Aplanamos todos los cursos y los ordenamos por nombre
        $cursos = $ciclosConCursos
            ->flatMap->cursos
            ->sortBy('nombre')
            ->values();

        return view('alumnos.ppd.calificaciones', compact('alumno', 'cursos'));
    }

    public function store(Request $request)
    {
        $validator = \Validator::make($request->all(), ppd::getValidationRules());
        if ($validator->fails()) {
            return back()->withErrors($validator)->withInput();
        }
        $numero = $request->input('numero');
        $numero_referencia = $request->input('numero_referencia');
        $lengua_1 = $request->input('lengua_1');
        $lengua_2 = $request->input('lengua_2');

        $userInput = $request->input('num_comprobante');

        if (stripos($userInput, 'Beca') !== false) {
            $counter = ppd::where('num_comprobante', 'like', 'Beca%')->count() + 1;
            $request->merge(['num_comprobante' => 'Beca_'.$counter]);
        }

        if (stripos($userInput, 'AMANTANI') !== false) {
            $counter = ppd::where('num_comprobante', 'like', 'AMANTANI%')->count() + 1;
            $request->merge(['num_comprobante' => 'AMANTANI_'.$counter]);
        }

        /* if (strtolower($userInput) === 'con deuda') */
        if (stripos($userInput, 'deuda') !== false) {
            $counter = ppd::where('num_comprobante', 'like', 'Deudor%')->count() + 1;
            $request->merge(['num_comprobante' => 'Deudor_'.$counter]);
        }

        $bienes_vivienda = $request->input('bienes_vivienda', []);
        if (empty($bienes_vivienda)) {
            return redirect()->back()->withInput()->withErrors(['bienes_vivienda' => 'Debe seleccionar al menos un bien de vivienda.']);
        }

        $bienes = implode(',', $bienes_vivienda);

        $otros_servicios = $request->input('otros_servicios', []);
        if (empty($otros_servicios)) {
            return redirect()->back()->withInput()->withErrors(['otros_servicios' => 'Debe seleccionar al menos un Servicio Adicionales en Vivienda.']);
        }
        $otrosServicios = implode(',', $otros_servicios);

        $habilidades = $request->input('habilidades', []);
        if (empty($habilidades)) {
            return redirect()->back()->withInput()->withErrors(['habilidades' => 'Debe seleccionar al menos una opción en Habilidades.']);
        }
        $talentos = implode('-', $habilidades);

        // Verificar que los valores de 'numero' y 'numero_referencia' sean diferentes
        if ($numero === $numero_referencia) {
            return redirect()->back()->withInput()->withErrors(['numero' => 'El campo Número y Número de referencia deben ser diferentes.']);
        }

        // Verificar que los valores de 'lengua_1' y 'lengua_2' sean diferentes
        if ($lengua_1 === $lengua_2) {
            return redirect()->back()->withInput()->withErrors(['lengua_1' => 'La Lengua 1 y Lengua 2 deben ser diferentes.']);
        }

        // Comprobar si hay errores en la validación
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // Crear el nuevo registro en la base de datos
        $nuevoAlumno = ppd::create(
            $request->only([
                'email',
                'dni',
                'nombres',
                'apellidos',
                'numero',
                'numero_referencia',
                'user_id',
                'programa_id',
                'ciclo_id',

                'procedencia_familiar',
                'sector_laboral',
                'lugar_nacimiento',
                'permanencia_vivienda',
                'direccion',
                'te_consideras',
                'lengua_1',
                'lengua_2',
                'estado_civil',
                'p_m_soltero',
                'num_hijos',
                'sector_socioeconomico',
                'num_comprobante',
                // Características Familiares
                'convivientes',
                'quien_mantiene',
                'cant_dependientes_child',
                'cant_dependientes_old',
                'cant_dependientes_otros',
                //Aspectos Educativos
                'carrera_procedencia',
                'ano_culminaste',
                'institucion_procedencia',
                'gestion_institucion',
                'direccion_institucion',
                'dep_dist_prov_institucion',

                'estudio_beca',
                'origen_beca',
                'postulaciones_eesp',
                'postulaciones_inst_uni',
                'postulaciones_otros',
                'tipo_preparacion',
                'motivo_estudio_eesp',
                'motivo_docencia',
                'motivo_especialidad',
                'internet',
                'internet_lugar',
                'servicio_internet',
                'dispositivo_internet',
                'propio_compartido',
                'correo',
                'num_hrs_estudio',
                'forma_estudio',
                //Aspectos Socieconómicos
                'trabajas',
                'donde_trabajas',
                'ingreso_mensual',
                'egreso',
                'hrs_laboradas_sem',
                'ayuda_economica',
                'tiempo_ayuda',
                'tipo_apoyo_formacion',
                //Aspectos Vivienda
                'tipo_vivienda',
                'situacion_vivienda',
                'dormitorios_vivienda',
                'banos_vivienda',
                'material_vivienda',
                'hrs_disponibles_agua',
                'hrs_disponibles_desague',
                'hrs_disponibles_luz',
                //Aspectos Salud
                'problemas_salud',
                'ultima_consulta',
                'motivo_consulta',
                'tipo_seguro',
                'familiar_salud',
                //Aspectos Culturales
                'frecuencia_lectura',
                'acceso_lectura',
                'visitas_museos',
                //Adicionales
                'actividades_internet',
                'tiempo_libre',
            ]) + [
                'bienes_vivienda' => $bienes,
                'otros_servicios' => $otrosServicios,
                'habilidades' => $talentos,
            ]
        );

        // Asociar por email si el usuario está autenticado
        if (auth()->check()) {
            ppd::asociarPorEmail($nuevoAlumno->email);

            return redirect()->route('ppd.index')->with('success', 'Alumno registrado exitosamente!');
        } else {
            return redirect()->route('index')->with('success', 'Has sido registrado exitosamente, le enviaremos un correo con sus credenciales de acceso.');
        }
    }

    public function show($id)
    {
        /* $alumno = ppd::with(['user', 'ciclo.cursos'])->findOrFail($id); */
        $alumno = ppd::with('programa.ciclos.cursos')->findOrFail($id);

        $procedencia = [
            'vivo_en_comunidad' => 'Yo aún vivo en la comunidad',
            'padres_viven_en_comunidad' => 'Mis padres aún viven en la comunidad',
            'abuelos_viven_en_comunidad' => 'Mis abuelos aún viven en la comunidad',
            'no_vivimos_en_comunidad' => 'Ya no vivimos en la comunidad',
            'familia_de_zona_urbana' => 'Procedemos de una zona urbana',
            'otra' => 'Otra',
        ];
        $consideras = [
            'quechua' => 'Quechua',
            'aymara' => 'Aymara',
            'nativo_amazonia' => 'Nativo o indígena de la Amazonía',
            'negro_moreno_zambo' => 'Negro/Moreno/Zambo mulato/Pueblo afroperuano o afrodescendiente',
            'blanco' => 'Blanco',
            'mestizo' => 'Mestizo',
            'otro_pueblo_indigena' => 'Perteneciente o parte de otro pueblo indígena u originario',
            'no_sabe_no_responde' => 'No sabe/No responde',
            'otro' => 'Otro',
        ];
        $sector = [
            'popular' => 'Popular',
            'medio' => 'Medio',
            'medio_alto' => 'Medio Alto',
            'alto' => 'Alto',
        ];

        return view('alumnos.ppd.show', [
            'alumno' => $alumno,
            'procedencia' => $procedencia,
            'consideras' => $consideras,
            'sector' => $sector,
        ]);
    }

    public function calificar(Request $request)
    {
        $request->validate([
            'curso_id' => 'required|exists:cursos,id',
            'docente_id' => 'required|exists:docentes,id',
            'alumnos' => 'required|array',

            'alumnos.*.proceso.*.indicador_1' => 'nullable|integer|min:0|max:20',
            'alumnos.*.proceso.*.indicador_2' => 'nullable|integer|min:0|max:20',
            'alumnos.*.proceso.*.indicador_3' => 'nullable|integer|min:0|max:20',
            'alumnos.*.proceso.*.indicador_4' => 'nullable|integer|min:0|max:20',

            'alumnos.*.final.*.indicador_1' => 'nullable|integer|min:0|max:20',
            'alumnos.*.final.*.indicador_2' => 'nullable|integer|min:0|max:20',
            'alumnos.*.final.*.indicador_3' => 'nullable|integer|min:0|max:20',

            'alumnos.*.nivel_desempeno' => 'nullable|string',
            'alumnos.*.calificacion_curso' => 'nullable|string',
            'alumnos.*.calificacion_sistema' => 'nullable|string',

            'alumnos.*.observaciones' => 'nullable|string|max:1000',
        ]);

        $curso = Curso::findOrFail($request->curso_id);
        $docente = Docente::findOrFail($request->docente_id);
        $primerNombre = explode(' ', trim($docente->nombre))[0];

        // 🔥 Guardado / actualización de calificaciones
        foreach ($request->input('alumnos', []) as $alumnoId => $data) {
            // ignoramos si no tiene matrícula (ppd_id)
            if (empty($data['ppd_id'])) {
                continue;
            }

            $ppdId = $data['ppd_id'];
            $proceso = $data['proceso'] ?? [];
            $final = $data['final'] ?? [];
            $pp = $pf = [];

            // Competencias 1 a 3
            foreach ([1, 2, 3] as $c) {
                $pp["pp_c{$c}_1"] = $proceso["c{$c}"]['indicador_1'] ?? null;
                $pp["pp_c{$c}_2"] = $proceso["c{$c}"]['indicador_2'] ?? null;
                $pp["pp_c{$c}_3"] = $proceso["c{$c}"]['indicador_3'] ?? null;
                $pp["pp_c{$c}_4"] = $proceso["c{$c}"]['indicador_4'] ?? null;

                $pf["pf_c{$c}_1"] = $final["c{$c}"]['indicador_1'] ?? null;
                $pf["pf_c{$c}_2"] = $final["c{$c}"]['indicador_2'] ?? null;
                $pf["pf_c{$c}_3"] = $final["c{$c}"]['indicador_3'] ?? null;
            }

            Calificacionesppd::updateOrCreate(
                [
                    'ppd_id' => $ppdId,
                    'curso_id' => $curso->id,
                ],
                array_merge([
                    'nombre' => $data['nombre'] ?? 'Periodo 1',
                    'fecha' => $data['fecha'] ?? now(),
                    'nivel_desempeno' => $data['nivel_desempeno'] ?? null,
                    'calificacion_curso' => $data['calificacion_curso'] ?? null,
                    'calificacion_sistema' => $data['calificacion_sistema'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                ], $pp, $pf)
            );
        }

        // 🔥 Recuperar alumnos con la misma lógica de calificarCursoPPD
        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();

        $alumnos = User::whereHas('roles', function ($q) {
            $q->whereIn('name', ['alumnoB', 'inhabilitado']);
        })
            ->whereHas('programa.ciclos.cursos', function ($query) use ($curso) {
                $query->where('id', $curso->id);
            })
            ->with(['programa.ciclos.cursos', 'roles', 'alumnoB'])
            ->orderBy('apellidos')
            ->get()
            ->map(function ($alumno) {
                $alumno->es_inhabilitado = $alumno->roles->contains('name', 'inhabilitado');
                $alumno->tiene_ppd = $alumno->alumnoB !== null;

                return $alumno;
            });

        session()->flash('success', "¡Felicidades $primerNombre! Las calificaciones se guardaron exitosamente.");

        return view('docentes.calificaciones.alumnosppd', [
            'curso' => $curso,
            'docente' => $docente,
            'competenciasSeleccionadas' => $competenciasSeleccionadas,
            'alumnos' => $alumnos,
        ]);
    }

    public function edit($id)
    {
        $alumno = ppd::find($id);
        $user = auth()->user();
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $alumno->bienes_vivienda = explode(',', $alumno->bienes_vivienda);
        $opcionesBienesVivienda = [
            'Cocina a gas',
            'Cocina eléctrica',
            'Aspiradora',
            'Televisor',
            'DVD',
            'Mini componente',
            'Cámara de video',
            'Computadora',
            'Horno microondas',
            'Lavadora',
            'Secadora de ropa',
            'Automóvil',
            'Bicicleta',
            'Motocicleta',
            'Juego de video',
            'Refrigeradora',
            'Ninguna de las anteriores',
        ];
        $alumno->otros_servicios = explode(',', $alumno->otros_servicios);
        $opcionesServicios = [
            'Empleado(a) doméstico',
            'Servicio de teléfono',
            'Servicio de cable',
            'Servicio de Internet',
            'Ninguna de las anteriores',
        ];
        $alumno->habilidades = explode('-', $alumno->habilidades);
        $opcionesHabilidades = [
            'Música (Instrumentos, canto)',
            'Artes pláticas (Pintura, Escultura, etc)',
            'Danzas (Danzas folklóricas, Ballet, Etc)',
            'Literatura (Poesía, Cuentos, etc)',
            'Otros',
        ];
        $departamentosData = [
            'Amazonas' => [
                'provincia' => [
                    'Chachapoyas' => ['Chachapoyas', 'Asunción', 'Balsas', 'Cheto', 'Chiliquin', 'Chuquibamba', 'Granada', 'Huancas', 'Jalca Grande', 'Leimebamba', 'Levanto', 'Magdalena', 'Mariscal Castilla', 'Molinopampa', 'Montevideo', 'Olleros', 'Quinjalca', 'San Francisco de Daguas', 'San Isidro de Maino', 'Soloco', 'Sonche'],
                    'Bagua' => ['Bagua', 'La Peca', 'Aramango', 'Copallín', 'El Parco', 'Imaza'],
                    'Bongará' => ['Jumbilla', 'Chisquilla', 'Churuja', 'Corosha', 'Cuispes', 'Florida', 'Pedro Ruiz Gallo', 'Recta', 'San Carlos', 'Shipasbamba', 'Valera', 'Yambrasbamba'],
                    'Condorcanqui' => ['El Cenepa', 'Nieva', 'Río Santiago'],
                    'Luya' => [
                        'Camporredondo',
                        'Cocabamba',
                        'Colcamar',
                        'Conila',
                        'Inguilpata',
                        'Lámud',
                        'Longuita',
                        'Lonya Chico',
                        'Luya',
                        'Luya Viejo',
                        'María',
                        'Ocalli',
                        'Ocumal',
                        'Pisuquía',
                        'Providencia',
                        'San Cristóbal',
                        'San Francisco del Yeso',
                        'San Jerónimo',
                        'San Juan de Lopecancha',
                        'Santa Catalina',
                        'Santo Tomás',
                        'Tingo',
                        'Trita',
                    ],
                    'Rodríguez de Mendoza' => [
                        'San Nicolás',
                        'Chirimoto',
                        'Cochamal',
                        'Huambo',
                        'Limabamba',
                        'Longar',
                        'Mariscal Benavides',
                        'Mílpuc',
                        'Omia',
                        'Santa Rosa',
                        'Totora',
                        'Vista Alegre',
                    ],
                    'Utcubamba' => ['Bagua Grande', 'Cajaruro', 'Cumba', 'El Milagro', 'Jamalca', 'Lonya Grande', 'Yamón'],

                ],
            ],
            'Anchash' => [
                'provincia' => [
                    'Huancavelica' => ['Huancavelica', 'Acobamba', 'Angaraes', 'Castrovirreyna', 'Chanchamayo', 'Huancayo', 'Huaytara', 'Tayacaja'],
                ],
            ],
            'Apurímac' => [
                'provincia' => [
                    'Abancay' => ['Abancay', 'Chacoche', 'Circa', 'Curahuasi', 'Huanipaca', 'Lambrama', 'Pichirhua', 'San Pedro de Cachora', 'Tamburco'],
                    'Andahuaylas' => ['Andahuaylas', 'Andarapa', 'Chiara', 'Huancarama', 'Huancaray', 'Huayana', 'Kishuara', 'Pacobamba', 'Pacucha', 'Pampachiri', 'Pomacocha', 'San Antonio de Cachi', 'San Jerónimo', 'San Miguel de Chaccrampa', 'Santa María de Chicmo', 'Talavera', 'Tumay Huaraca', 'Turpo', 'Kaquiabamba', 'José María Arguedas'],
                    'Antabamba' => ['Antabamba', 'El Oro', 'Huaquirca', 'Juan Espinoza Medrano', 'Oropesa', 'Pachaconas', 'Sabaino'],
                    'Aymaraes' => ['Chalhuanca', 'Capaya', 'Caraybamba', 'Chapimarca', 'Colcabamba', 'Cotaruse', 'Huayllo', 'Justo Apu Sahuaraura', 'Lucre', 'Pocohuanca', 'San Juan de Chacña', 'Sañayca', 'Soraya', 'Tapairihua', 'Tintay', 'Toraya', 'Yanaca'],
                    'Cotabambas' => ['Tambobamba', 'Cotabambas', 'Coyllurqui', 'Haquira', 'Mara', 'Challhuahuacho'],
                    'Chincheros' => ['Chincheros', 'Anco_Huallo', 'Cocharcas', 'Huaccana', 'Ocobamba', 'Ongoy', 'Uranmarca', 'Ranracancha', 'Rocchacc', 'El Porvenir', 'Los Chankas'],
                    'Grau' => ['Chuquibambilla', 'Curpahuasi', 'Gamarra', 'Huayllati', 'Mamara', 'Micaela Bastidas', 'Pataypampa', 'Progreso', 'San Antonio', 'Santa Rosa', 'Turpay', 'Vilcabamba', 'Virundo', 'Curasco'],
                ],
            ],
            'Ayacucho' => [
                'provincia' => [
                    'Huamanga' => ['Ayacucho', 'Acocro', 'Acos Vinchos', 'Carmen Alto', 'Chiara', 'Ocros', 'Pacaycasa', 'Quinua', 'San José de Ticllas', 'San Juan Bautista', 'Santiago de Pischa', 'Socos', 'Tambillo', 'Vinchos', 'Jesús Nazareno', 'Andrés Avelino Cáceres Dorregaray'],
                    'Cangallo' => ['Cangallo', 'Chuschi', 'Los Morochucos', 'María Parado de Bellido', 'Paras', 'Totos'],
                    'Huanca Sancos' => ['Sancos', 'Carapo', 'Sacsamarca', 'Santiago de Lucanamarca'],
                    'Huanta' => ['Huanta', 'Ayahuanco', 'Huamanguilla', 'Iguain', 'Luricocha', 'Santillana', 'Sivia', 'Llochegua', 'Canayre', 'Uchuraccay', 'Pucacolpa', 'Chaca'],
                    'La Mar' => ['San Miguel', 'Anco', 'Ayna', 'Chilcas', 'Chungui', 'Luis Carranza', 'Santa Rosa', 'Tambo', 'Samugari', 'Anchihuay', 'Oronccoy'],
                    'Lucanas' => ['Puquio', 'Aucara', 'Cabana', 'Carmen Salcedo', 'Chaviña', 'Chipao', 'Huac-Huas', 'Laramate', 'Leoncio Prado', 'Llauta', 'Lucanas', 'Ocaña', 'Otoca', 'Saisa', 'San Cristóbal', 'San Juan', 'San Pedro', 'San Pedro de Palco', 'Sancos', 'Santa Ana de Huaycahuacho', 'Santa Lucia'],
                    'Parinacochas' => ['Coracora', 'Chumpi', 'Coronel Castañeda', 'Pacapausa', 'Pullo', 'Puyusca', 'San Francisco de Ravacayco', 'Upahuacho'],
                    'Páucar del Sara Sara' => ['Pausa', 'Colta', 'Corculla', 'Lampa', 'Marcabamba', 'Oyolo', 'Pararca', 'San Javier de Alpabamba', 'San José de Ushua', 'Sara Sara'],
                    'Sucre' => ['Querobamba', 'Belén', 'Chalcos', 'Chilcayoc', 'Huacaña', 'Morcolla', 'Paico', 'San Pedro de Larcay', 'San Salvador de Quije', 'Santiago de Paucaray', 'Soras'],
                    'Víctor Fajardo' => ['Huancapi', 'Alcamenca', 'Apongo', 'Asquipata', 'Canaria', 'Cayara', 'Colca', 'Huamanquiquia', 'Huancaraylla', 'Huaya', 'Sarhua', 'Vilcanchos'],
                    'Vilcas Huamán' => ['Vilcas Huamán', 'Accomarca', 'Carhuanca', 'Concepción', 'Huambalpa', 'Independencia', 'Saurama', 'Vischongo'],
                ],
            ],
            'Arequipa' => [
                'provincia' => [
                    'Arequipa' => ['Arequipa', 'Alto Selva Alegre', 'Cayma', 'Cerro Colorado', 'Characato', 'Chiguata', 'Jacobo Hunter', 'José Luis Bustamante y Rivero', 'La Joya', 'Mariano Melgar', 'Miraflores', 'Mollebaya', 'Paucarpata', 'Pocsi', 'Polobaya', 'Quequeña', 'Sabandia', 'Sachaca', 'San Juan de Siguas', 'San Juan de Tarucani', 'Santa Isabel de Siguas', 'Santa Rita de Siguas', 'Socabaya', 'Tiabaya', 'Uchumayo', 'Vitor', 'Yanahuara', 'Yarabamba', 'Yura'],
                    'Camana' => ['Camaná', 'José María Quimper', 'Mariano Nicolás Valcárcel', 'Mariscal Cáceres', 'Nicolás de Pierola', 'Ocoña', 'Quilca', 'Samuel Pastor'],
                    'Caraveli' => ['Caravelí', 'Acarí', 'Atico', 'Atiquipa', 'Bella Unión', 'Cahuacho', 'Chala', 'Chaparra', 'Huanuhuanu', 'Jaqui', 'Lomas', 'Quicacha', 'Yauca'],
                    'Castilla' => ['Aplao', 'Andagua', 'Ayo', 'Chachas', 'Chilcaymarca', 'Choco', 'Huancarqui', 'Machaguay', 'Orcopampa', 'Pampacolca', 'Tipan', 'Uñon', 'Uraca', 'Viraco'],
                    'Caylloma' => ['Chivay', 'Achoma', 'Cabanaconde', 'Callalli', 'Caylloma', 'Coporaque', 'Huambo', 'Huanca', 'Ichupampa', 'Lari', 'Lluta', 'Maca', 'Madrigal', 'San Antonio de Chuca', 'Sibayo', 'Tapay', 'Tisco', 'Tuti', 'Yanque', 'Majes'],
                    'Condesuyos' => ['Chuquibamba', 'Andaray', 'Cayarani', 'Chichas', 'Iray', 'Río Grande', 'Salamanca', 'Yanaquihua'],
                    'Islay' => ['Mollendo', 'Cocachacra', 'Dean Valdivia', 'Islay', 'Mejia', 'Punta de Bombón'],
                    'La Union' => ['Cotahuasi', 'Alca', 'Charcana', 'Huaynacotas', 'Pampamarca', 'Puyca', 'Quechualla', 'Sayla', 'Tauria', 'Tomepampa', 'Toro'],
                ],
            ],
            'Cusco' => [
                'provincia' => [
                    'Acomayo' => ['Acomayo', 'Acopia', 'Acos', 'Mosoc Llacta', 'Pomacanchi', 'Rondocan'],
                    'Anta' => ['Anta', 'Ancahuasi', 'Cachimayo', 'Chinchaypujio', 'Huarocondo', 'Limatambo', 'Mollepata', 'Pucyura', 'Zurite'],
                    'Calca' => ['Calca', 'Coya', 'Lamay', 'Lares', 'Pisac', 'San Salvador', 'Taray', 'Yanatile'],
                    'Canas' => ['Yanaoca', 'Checca', 'Kunturkanki', 'Langui', 'Layo', 'Pampamarca', 'Quehue', 'Tupac Amaru'],
                    'Canchis' => ['Sicuani', 'Checacupe', 'Combapata', 'Marangani', 'Pitumarca', 'San Pablo', 'San Pedro'],
                    'Chumbivilcas' => ['Santo Tomás', 'Capacmarca', 'Chamaca', 'Colquemarca', 'Livitaca', 'Llusco', 'Quiñota', 'Velille'],
                    'Cusco' => ['Cusco', 'Ccorca', 'Poroy', 'San Jerónimo', 'San Sebastián', 'Santiago', 'Saylla', 'Wanchaq'],
                    'Espinar' => ['Espinar', 'Condoroma', 'Coporaque', 'Ocoruro', 'Pallpata', 'Pichigua', 'Suykutambo', 'Alto Pichigua'],
                    'La Convención' => ['Quillabamba', 'Echarate', 'Huayopata', 'Maranura', 'Ocobamba', 'Quellouno', 'Santa Ana', 'Santa Teresa', 'Vilcabamba'],
                    'Paruro' => ['Paruro', 'Accha', 'Ccapi', 'Colcha', 'Huanoquite', 'Omacha', 'Paccaritambo', 'Pillpinto', 'Yaurisque'],
                    'Paucartambo' => ['Paucartambo', 'Caicay', 'Challabamba', 'Colquepata', 'Huancarani', 'Kosñipata'],
                    'Quispicanchi' => ['Urcos', 'Andahuaylillas', 'Camanti', 'Ccarhuayo', 'Ccatca', 'Cusipata', 'Huaro', 'Lucre', 'Marcapata', 'Ocongate', 'Oropesa', 'Quiquijana'],
                    'Urubamba' => ['Urubamba', 'Chinchero', 'Huayllabamba', 'Machupicchu', 'Maras', 'Ollantaytambo', 'Yucay'],
                ],
            ],
            'Lima' => [
                'provincia' => [
                    'Lima' => ['Lima', 'Ancon', 'Ate', 'Barranco', 'Breña', 'Carabayllo', 'Chaclacayo', 'Chorrillos', 'Cieneguilla', 'Comas', 'El Agustino', 'Independencia', 'Jesus Maria', 'La Molina', 'La Victoria', 'Lince', 'Los Olivos', 'Lurigancho', 'Lurin', 'Magdalena del Mar', 'Miraflores', 'Pachacamac', 'Pucusana', 'Pueblo Libre', 'Puente Piedra', 'Punta Hermosa', 'Punta Negra', 'Rimac', 'San Bartolo', 'San Borja', 'San Isidro', 'San Juan de Lurigancho', 'San Juan de Miraflores', 'San Luis', 'San Martin de Porres', 'San Miguel', 'Santa Anita', 'Santa Maria del Mar', 'Santa Rosa', 'Santiago de Surco', 'Surquillo', 'Villa El Salvador', 'Villa Maria del Triunfo'],
                    'Barranca' => ['Barranca', 'Paramonga', 'Pativilca', 'Supe', 'Supe Puerto'],
                    'Cajatambo' => ['Cajatambo', 'Copa', 'Gorgor', 'Huancapon', 'Manas'],
                    'Canta' => ['Canta', 'Arahuay', 'Huamantanga', 'Huaros', 'Lachaqui', 'San Buenaventura', 'Santa Rosa de Quives'],
                    'Cañete' => ['San Vicente de Cañete', 'Asia', 'Calango', 'Cerro Azul', 'Chilca', 'Coayllo', 'Imperial', 'Lunahuana', 'Mala', 'Nuevo Imperial', 'Pacaran', 'Quilmana', 'San Antonio', 'San Luis', 'Santa Cruz de Flores', 'Zúñiga'],
                    'Huaral' => ['Huaral', 'Atavillos Alto', 'Atavillos Bajo', 'Aucallama', 'Chancay', 'Ihuari', 'Lampian', 'Pacaraos', 'San Miguel de Acos', 'Santa Cruz de Andamarca', 'Sumbilca', 'Veintisiete de Noviembre'],
                    'Huarochiri' => ['Matucana', 'Antioquia', 'Callahuanca', 'Carampoma', 'Chicla', 'Cuenca', 'Huachupampa', 'Huanza', 'Huarochiri', 'Lahuaytambo', 'Langa', 'Laraos', 'Mariatana', 'Ricardo Palma', 'San Andrés de Tupicocha', 'San Antonio', 'San Bartolomé', 'San Damian', 'San Juan de Iris', 'San Juan de Tantaranche', 'San Lorenzo de Quinti', 'San Mateo', 'San Mateo de Otao', 'San Pedro de Casta', 'San Pedro de Huancayre', 'Sangallaya', 'Santa Cruz de Cocachacra', 'Santa Eulalia', 'Santiago de Anchucaya', 'Santiago de Tuna', 'Santo Domingo de los Olleros', 'Surco'],
                    'Huaura' => ['Huacho', 'Ambar', 'Caleta de Carquin', 'Checras', 'Hualmay', 'Huaura', 'Leoncio Prado', 'Paccho', 'Santa Leonor', 'Santa Maria', 'Sayan', 'Vegueta'],
                    'Oyon' => ['Oyon', 'Andajes', 'Caujul', 'Cochamarca', 'Navan', 'Pachangara'],
                    'Yauyos' => ['Yauyos', 'Alis', 'Ayauca', 'Ayaviri', 'Azángaro', 'Cacra', 'Carania', 'Catahuasi', 'Chocos', 'Cochas', 'Colonia', 'Hongos', 'Huampara', 'Huancaya', 'Huangascar', 'Huantan', 'Huañec', 'Laraos', 'Lincha', 'Madean', 'Miraflores', 'Omas', 'Putinza', 'Quinches', 'Quinocay', 'San Joaquin', 'San Pedro de Pilas', 'Tanta', 'Tauripampa', 'Tomas', 'Tupe', 'Viñac', 'Vitis'],
                ],
            ],
            'Madre de Dios' => [
                'provincia' => [
                    'Tambopata' => ['Tambopata', 'Inambari', 'Las Piedras', 'Laberinto'],
                    'Manu' => ['Manu', 'Fitzcarrald', 'Madre de Dios', 'Huepetuhe'],
                    'Tahuamanu' => ['Iñapari', 'Iberia', 'Tahuamanu'],
                ],
            ],
            'Pasco' => [
                'provincia' => [
                    'Pasco' => ['Chaupimarca', 'Huachón', 'Huariaca', 'Huayllay', 'Ninacaca', 'Pallanchacra', 'Paucartambo', 'San Francisco de Asís de Yarusyacán', 'Simón Bolívar', 'Ticlacayan', 'Tinyahuarco', 'Vicco', 'Yanacancha'],
                    'Daniel Alcides Carrión' => ['Yanahuanca', 'Chacayan', 'Goyllarisquizga', 'Paucar', 'San Pedro de Pillao', 'Santa Ana de Tusi', 'Tapuc', 'Vilcabamba'],
                    'Oxapampa' => ['Oxapampa', 'Chontabamba', 'Huancabamba', 'Palcazu', 'Pozuzo', 'Puerto Bermúdez', 'Villa Rica', 'Constitución'],
                ],
            ],
            'Puno' => [
                'provincia' => [
                    'Puno' => ['Puno', 'Acora', 'Amantani', 'Atuncolla', 'Capachica', 'Chucuito', 'Coata', 'Huata', 'Mañazo', 'Paucarcolla', 'Pichacani', 'Plateria', 'San Antonio', 'Tiquillaca', 'Vilque'],
                    'Azángaro' => ['Azángaro', 'Achaya', 'Arapa', 'Asillo', 'Caminaca', 'Chupa', 'Jose Domingo Choquehuanca', 'Muñani', 'Potoni', 'Saman', 'San Anton', 'San Jose', 'San Juan de Salinas', 'Santiago de Pupuja', 'Tirapata'],
                    'Carabaya' => ['Macusani', 'Ajoyani', 'Ayapata', 'Coasa', 'Corani', 'Crucero', 'Ituata', 'Ollachea', 'San Gaban', 'Usicayos'],
                    'Chucuito' => ['Juli', 'Desaguadero', 'Huacullani', 'Kelluyo', 'Pisacoma', 'Pomata', 'Zepita'],
                    'El Collao' => ['Ilave', 'Capazo', 'Pilcuyo', 'Santa Rosa', 'Conduriri'],
                    'Huancane' => ['Huancane', 'Cojata', 'Huatasani', 'Inchupalla', 'Pusi', 'Rosaspata', 'Taraco', 'Vilque Chico'],
                    'Lampa' => ['Lampa', 'Cabanilla', 'Calapuja', 'Nicasio', 'Ocuviri', 'Palca', 'Paratia', 'Pucara', 'Santa Lucia', 'Vilavila'],
                    'Melgar' => ['Ayaviri', 'Antauta', 'Cupi', 'Llalli', 'Macari', 'Nuñoa', 'Orurillo', 'Santa Rosa', 'Umachiri'],
                    'Moho' => ['Moho', 'Conima', 'Huayrapata', 'Tilali'],
                    'San Antonio de Putina' => ['Putina', 'Ananea', 'Pedro Vilca Apaza', 'Quilcapuncu', 'Sina'],
                    'San Roman' => ['Juliaca', 'Cabana', 'Cabanillas', 'Caracoto'],
                    'Sandia' => ['Sandia', 'Cuyocuyo', 'Limbani', 'Patambuco', 'Phara', 'Quiaca', 'San Juan del Oro', 'Yanahuaya', 'Alto Inambari', 'San Pedro de Putina Punco'],
                    'Yunguyo' => ['Yunguyo', 'Anapia', 'Copani', 'Cuturapi', 'Ollaraya', 'Tinicachi', 'Unicachi'],
                ],
            ],
        ];

        return view('alumnos.ppd.edit', compact('alumno', 'programas', 'ciclos', 'user', 'opcionesBienesVivienda', 'opcionesServicios', 'opcionesHabilidades', 'departamentosData'));
    }

    public function update(Request $request, ppd $profesionalización_docente)
    {
        $alumno = $profesionalización_docente;
        $request->validate([
            'genero' => 'required|string',
            'numero' => 'required|string|max:20',
            'numero_referencia' => 'required|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'num_hijos' => 'nullable|integer|min:0',
            'num_comprobante' => 'required|string|max:50',
            'trabajas' => 'required|string',
            'departamento' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'distrito' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
        ]);
        $alumno->update($request->all());

        return redirect()->route('ppd.index')->with('success', 'Datos registrados correctamente.');
    }

    public function formatos()
    {
        return view('alumnos.formatos.formato-ppd');
    }
}
