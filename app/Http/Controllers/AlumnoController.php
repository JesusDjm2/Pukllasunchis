<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionRegistro;
use App\Models\Alumno;
use App\Models\Calificacion;
use App\Models\Ciclo;
use App\Models\ppd;
use App\Models\Programa;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        if (!auth()->check()) {
            return redirect()->route('login');
        }

        $alumno = auth()->user()->alumno;

        if ($alumno) {
            $usuario = $alumno->user;
            $alumno->load('programa', 'ciclo.cursos.docentes');

            return view('alumnos.vistasAlumnos.index', compact('alumno', 'usuario'));
        } else {
            return view('alumnos.vistasAlumnos.index');
        }
    }
    public function ficha(Alumno $alumno)
    {
        return view('alumnos.ficha', compact('alumno'));
    }
    public function mostrarContenido(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|integer',
        ]);

        $user = Auth::user();
        $alumno_id = $request->input('alumno_id');

        if ($user->hasRole('alumno')) {
            $alumno = Alumno::find($alumno_id);
            if (!$alumno) {
                return redirect()->back()->with('error', 'Alumno no encontrado.');
            }
        } elseif ($user->hasRole('alumnoB')) {
            $alumno = ppd::find($alumno_id);
            if (!$alumno) {
                return redirect()->back()->with('error', 'AlumnoB no encontrado.');
            }
        } else {
            return redirect()->back()->with('error', 'Rol no autorizado.');
        }

        session(['mostrar_contenido' => true]);
        Mail::to([
            'davidmiranda.puk@gmail.com',
            'cobranzas.eesp@pukllavirtual.edu.pe'
        ])
            ->send(new NotificacionRegistro($alumno));

        return redirect()->back()->with('success', 'Correo enviado correctamente.');
    }
    public function create()
    {
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $user = auth()->user();
        return view('alumnos.vistasAlumnos.formulario', compact('user', 'programas', 'ciclos'));
    }
    public function filtro(Request $request)
    {
        $alumnos = Alumno::with('programa', 'ciclo')->get();
        $campos = Schema::getColumnListing('alumnos');
        return view('alumnos.filtro', compact('alumnos', 'campos'));
    }
    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validator = \Validator::make($request->all(), Alumno::getValidationRules());

        // Realizar verificación adicional antes de almacenar en la base de datos
        $numero = $request->input('numero');
        $numero_referencia = $request->input('numero_referencia');
        $lengua_1 = $request->input('lengua_1');
        $lengua_2 = $request->input('lengua_2');

        $userInput = $request->input('num_comprobante');

        if (stripos($userInput, 'Beca') !== false) {
            $counter = Alumno::where('num_comprobante', 'like', 'Beca%')->count() + 1;
            $request->merge(['num_comprobante' => 'Beca_' . $counter]);
        }

        if (stripos($userInput, 'AMANTANI') !== false) {
            $counter = Alumno::where('num_comprobante', 'like', 'AMANTANI%')->count() + 1;
            $request->merge(['num_comprobante' => 'AMANTANI_' . $counter]);
        }

        /* if (strtolower($userInput) === 'con deuda') */
        if (stripos($userInput, 'deuda') !== false) {
            $counter = Alumno::where('num_comprobante', 'like', 'Deudor%')->count() + 1;
            $request->merge(['num_comprobante' => 'Deudor_' . $counter]);
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
        $nuevoAlumno = Alumno::create(
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
                'permanencia_vivienda',
                'lugar_nacimiento',
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

        // Manejo de la imagen
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = $foto->getClientOriginalName();
            $foto->move(public_path('img/estudiantes'), $nombreFoto);

            if (auth()->check()) {
                $user = auth()->user();
                $user->foto = $nombreFoto;
                $user->save();
            }
        }

        // Asociar por email si el usuario está autenticado
        if (auth()->check()) {
            Alumno::asociarPorEmail($nuevoAlumno->email);
            return redirect()->route('alumnos.index')->with('success', 'Alumno registrado exitosamente!');
        } else {
            return redirect()->route('index')->with('success', 'Has sido registrado exitosamente, le enviaremos un correo con sus credenciales de acceso.');
        }
    }
    public function show($id)
    {
        /* $alumno = Alumno::with('user')->find($id); */
        $alumno = Alumno::with('user')->find($id);

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

        return view('alumnos.show', [
            'alumno' => $alumno,
            'procedencia' => $procedencia,
            'consideras' => $consideras,
            'sector' => $sector,
        ]);
    }
    public function edit($id)
    {
        $alumno = Alumno::find($id);
        $programas = Programa::all();
        $ciclos = Ciclo::all();
        $user = auth()->user();
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
            'Ninguna de las anteriores'
        ];
        $alumno->otros_servicios = explode(',', $alumno->otros_servicios);
        $opcionesServicios = [
            'Empleado(a) doméstico',
            'Servicio de teléfono',
            'Servicio de cable',
            'Servicio de Internet',
            'Ninguna de las anteriores'
        ];
        $alumno->habilidades = explode('-', $alumno->habilidades);
        $opcionesHabilidades = [
            'Música (Instrumentos, canto)',
            'Artes pláticas (Pintura, Escultura, etc)',
            'Danzas (Danzas folklóricas, Ballet, Etc)',
            'Literatura (Poesía, Cuentos, etc)',
            'Otros'
        ];

        // 🔹 Ubicación de ejemplo (solo algunos departamentos con provincias y distritos reales para demo)
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
                        'Trita'
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
                        'Vista Alegre'
                    ],
                    'Utcubamba' => ['Bagua Grande', 'Cajaruro', 'Cumba', 'El Milagro', 'Jamalca', 'Lonya Grande', 'Yamón'],

                ]
            ],
            'Anchash' => [
                'provincia' => [
                    'Huancavelica' => ['Huancavelica', 'Acobamba', 'Angaraes', 'Castrovirreyna', 'Chanchamayo', 'Huancayo', 'Huaytara', 'Tayacaja'],
                ]
            ],
            'Apurímac' => [
                'provincia' => [
                    'Abancay' => ['Abancay', 'Chacoche', 'Circa', 'Curahuasi', 'Huanipaca', 'Lambrama', 'Pichirhua', 'San Pedro de Cachora', 'Tamburco'],
                    'Andahuaylas' => ['Andahuaylas', 'Andarapa', 'Chiara', 'Huancarama', 'Huancaray', 'Huayana', 'Kishuara', 'Pacobamba', 'Pacucha', 'Pampachiri', 'Pomacocha', 'San Antonio de Cachi', 'San Jerónimo', 'San Miguel de Chaccrampa', 'Santa María de Chicmo', 'Talavera', 'Tumay Huaraca', 'Turpo', 'Kaquiabamba', 'José María Arguedas'],
                    'Antabamba' => ['Antabamba', 'El Oro', 'Huaquirca', 'Juan Espinoza Medrano', 'Oropesa', 'Pachaconas', 'Sabaino'],
                    'Aymaraes' => ['Chalhuanca', 'Capaya', 'Caraybamba', 'Chapimarca', 'Colcabamba', 'Cotaruse', 'Huayllo', 'Justo Apu Sahuaraura', 'Lucre', 'Pocohuanca', 'San Juan de Chacña', 'Sañayca', 'Soraya', 'Tapairihua', 'Tintay', 'Toraya', 'Yanaca'],
                    'Cotabambas' => ['Tambobamba', 'Cotabambas', 'Coyllurqui', 'Haquira', 'Mara', 'Challhuahuacho'],
                    'Chincheros' => ['Chincheros', 'Anco_Huallo', 'Cocharcas', 'Huaccana', 'Ocobamba', 'Ongoy', 'Uranmarca', 'Ranracancha', 'Rocchacc', 'El Porvenir', 'Los Chankas'],
                    'Grau' => ['Chuquibambilla', 'Curpahuasi', 'Gamarra', 'Huayllati', 'Mamara', 'Micaela Bastidas', 'Pataypampa', 'Progreso', 'San Antonio', 'Santa Rosa', 'Turpay', 'Vilcabamba', 'Virundo', 'Curasco']
                ]
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
                    'Vilcas Huamán' => ['Vilcas Huamán', 'Accomarca', 'Carhuanca', 'Concepción', 'Huambalpa', 'Independencia', 'Saurama', 'Vischongo']
                ]
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
                    'La Union' => ['Cotahuasi', 'Alca', 'Charcana', 'Huaynacotas', 'Pampamarca', 'Puyca', 'Quechualla', 'Sayla', 'Tauria', 'Tomepampa', 'Toro']
                ]
            ],
            'Cusco' => [
                'provincia' => [
                    'Acomayo' => ['Acomayo', 'Acopia', 'Acos', 'Mosoc Llacta', 'Pomacanchi', 'Rondocan',],
                    'Anta' => ['Anta', 'Ancahuasi', 'Cachimayo', 'Chinchaypujio', 'Huarocondo', 'Limatambo', 'Mollepata', 'Pucyura', 'Zurite'],
                    'Calca' => ['Calca', 'Coya', 'Lamay', 'Lares', 'Pisac', 'San Salvador', 'Taray', 'Yanatile'],
                    'Canas' => ['Yanaoca', 'Checca', 'Kunturkanki', 'Langui', 'Layo', 'Pampamarca', 'Quehue', 'Tupac Amaru'],
                    'Canchis' => ['Sicuani', 'Checacupe', 'Combapata', 'Marangani', 'Pitumarca', 'San Pablo', 'San Pedro', 'Tinta'],
                    'Chumbivilcas' => ['Santo Tomás', 'Capacmarca', 'Chamaca', 'Colquemarca', 'Livitaca', 'Llusco', 'Quiñota', 'Velille'],
                    'Cusco' => ['Cusco', 'Ccorca', 'Poroy', 'San Jerónimo', 'San Sebastián', 'Santiago', 'Saylla', 'Wanchaq'],
                    'Espinar' => ['Espinar', 'Condoroma', 'Coporaque', 'Ocoruro', 'Pallpata', 'Pichigua', 'Suykutambo', 'Alto Pichigua'],
                    'La Convención' => ['Quillabamba', 'Echarate', 'Huayopata', 'Maranura', 'Ocobamba', 'Quellouno', 'Santa Ana', 'Santa Teresa', 'Vilcabamba'],
                    'Paruro' => ['Paruro', 'Accha', 'Ccapi', 'Colcha', 'Huanoquite', 'Omacha', 'Paccaritambo', 'Pillpinto', 'Yaurisque'],
                    'Paucartambo' => ['Paucartambo', 'Caicay', 'Challabamba', 'Colquepata', 'Huancarani', 'Kosñipata'],
                    'Quispicanchi' => ['Urcos', 'Andahuaylillas', 'Camanti', 'Ccarhuayo', 'Ccatca', 'Cusipata', 'Huaro', 'Lucre', 'Marcapata', 'Ocongate', 'Oropesa', 'Quiquijana'],
                    'Urubamba' => ['Urubamba', 'Chinchero', 'Huayllabamba', 'Machupicchu', 'Maras', 'Ollantaytambo', 'Yucay']
                ]
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
                    'Yauyos' => ['Yauyos', 'Alis', 'Ayauca', 'Ayaviri', 'Azángaro', 'Cacra', 'Carania', 'Catahuasi', 'Chocos', 'Cochas', 'Colonia', 'Hongos', 'Huampara', 'Huancaya', 'Huangascar', 'Huantan', 'Huañec', 'Laraos', 'Lincha', 'Madean', 'Miraflores', 'Omas', 'Putinza', 'Quinches', 'Quinocay', 'San Joaquin', 'San Pedro de Pilas', 'Tanta', 'Tauripampa', 'Tomas', 'Tupe', 'Viñac', 'Vitis']
                ]
            ],
            'Madre de Dios' => [
                'provincia' => [
                    'Tambopata' => ['Tambopata', 'Inambari', 'Las Piedras', 'Laberinto'],
                    'Manu' => ['Manu', 'Fitzcarrald', 'Madre de Dios', 'Huepetuhe'],
                    'Tahuamanu' => ['Iñapari', 'Iberia', 'Tahuamanu']
                ]
            ],
            'Pasco' => [
                'provincia' => [
                    'Pasco' => ['Chaupimarca', 'Huachón', 'Huariaca', 'Huayllay', 'Ninacaca', 'Pallanchacra', 'Paucartambo', 'San Francisco de Asís de Yarusyacán', 'Simón Bolívar', 'Ticlacayan', 'Tinyahuarco', 'Vicco', 'Yanacancha'],
                    'Daniel Alcides Carrión' => ['Yanahuanca', 'Chacayan', 'Goyllarisquizga', 'Paucar', 'San Pedro de Pillao', 'Santa Ana de Tusi', 'Tapuc', 'Vilcabamba'],
                    'Oxapampa' => ['Oxapampa', 'Chontabamba', 'Huancabamba', 'Palcazu', 'Pozuzo', 'Puerto Bermúdez', 'Villa Rica', 'Constitución']
                ]
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
                    'Yunguyo' => ['Yunguyo', 'Anapia', 'Copani', 'Cuturapi', 'Ollaraya', 'Tinicachi', 'Unicachi']
                ]
            ],
        ];
        return view('alumnos.edit', compact('alumno', 'programas', 'ciclos', 'user', 'opcionesBienesVivienda', 'opcionesServicios', 'opcionesHabilidades', 'departamentosData'));
    }
    public function update(Request $request, Alumno $alumno)
    {
        $alumno = Alumno::findOrFail($alumno->id);

        // Validación solo para estos campos
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

        // Actualización de estos campos
        $alumno->update($request->all());

        return redirect()->route('alumnos.index', $alumno)->with('success', 'Datos registrados correctamente.');
    }
    /* public function update(Request $request, Alumno $alumno)
    {
        $id = $alumno->id;
        $validator = \Validator::make($request->all(), Alumno::getValidationRules(true, $id));

        $numero = $request->input('numero');
        $numero_referencia = $request->input('numero_referencia');
        $lengua_1 = $request->input('lengua_1');
        $lengua_2 = $request->input('lengua_2');

        // Verificar que los valores de 'numero' y 'numero_referencia' sean diferentes
        if ($numero === $numero_referencia) {
            return redirect()->back()->withInput()->withErrors(['numero' => 'El campo Número y Número de referencia deben ser diferentes.']);
        }

        // Verificar que los valores de 'lengua_1' y 'lengua_2' sean diferentes
        if ($lengua_1 === $lengua_2) {
            return redirect()->back()->withInput()->withErrors(['lengua_1' => 'La Lengua 1 y Lengua 2 deben ser diferentes.']);
        }
        $userInput = $request->input('num_comprobante');

        if (stripos($userInput, 'Beca') !== false) {
            $counter = Alumno::where('num_comprobante', 'like', 'Beca%')->count() + 1;
            $request->merge(['num_comprobante' => 'Beca_' . $counter]);
        }

        if (stripos($userInput, 'AMANTANI') !== false) {
            $counter = Alumno::where('num_comprobante', 'like', 'AMANTANI%')->count() + 1;
            $request->merge(['num_comprobante' => 'AMANTANI_' . $counter]);
        }

        // Comprobar si hay errores en la validación
        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

        // Actualizar el registro en la base de datos
        $alumno->update(
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
                'permanencia_vivienda',
                'sector_laboral',
                'lugar_nacimiento',
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
                // Aspectos Educativos
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
                // Aspectos Socioeconómicos
                'trabajas',
                'donde_trabajas',
                'ingreso_mensual',
                'egreso',
                'hrs_laboradas_sem',
                'ayuda_economica',
                'tiempo_ayuda',
                'tipo_apoyo_formacion',
                // Aspectos Vivienda
                'tipo_vivienda',
                'situacion_vivienda',
                'dormitorios_vivienda',
                'banos_vivienda',
                'material_vivienda',
                'hrs_disponibles_agua',
                'hrs_disponibles_desague',
                'hrs_disponibles_luz',
                // Aspectos Salud
                'problemas_salud',
                'ultima_consulta',
                'motivo_consulta',
                'tipo_seguro',
                'familiar_salud',
                // Aspectos Culturales
                'frecuencia_lectura',
                'acceso_lectura',
                'visitas_museos',
                // Adicionales
                'actividades_internet',
                'tiempo_libre',
            ]) + [
                'bienes_vivienda' => implode(',', $request->input('bienes_vivienda', [])),
                'otros_servicios' => implode(',', $request->input('otros_servicios', [])),
                'habilidades' => implode('-', $request->input('habilidades', [])),
            ]
        );
        return redirect()->route('alumnos.index', $alumno)->with('success', 'Datos registrados correctamente.');
    } */
    public function destroy(Alumno $alumno)
    {
        $alumno->delete();

        return redirect()->route('adminAlumnos')->with('success', 'El alumno ha sido eliminado correctamente.');
    }

    public function calificaciones($id)
    {
        $alumno = Alumno::with(['ciclo.cursos',])->findOrFail($id);

        // Traemos todos los periodos del alumno, con curso y periodoActual
        $periodos = $alumno->periodo()
            ->with(['curso.ciclo.programa', 'periodoActual'])
            ->get();

        // Agrupamos por el nombre del PeriodoActual (ej. 2024-I, 2024-II, etc.)
        $periodosAgrupados = $periodos->groupBy(function ($p) {
            return optional($p->periodoActual)->nombre ?? 'Sin periodo';
        })->sortKeys();

        // Si además quieres los parciales actuales como en tu otra vista:
        $periodoUno = $alumno->ciclo->cursos->flatMap(function ($curso) use ($alumno) {
            return $curso->periodos()->where('alumno_id', $alumno->id)->get();
        });

        $periodoDos = $alumno->ciclo->cursos->flatMap(function ($curso) use ($alumno) {
            return $curso->periododos()->where('alumno_id', $alumno->id)->get();
        });

        $periodoTres = $alumno->ciclo->cursos->flatMap(function ($curso) use ($alumno) {
            return $curso->periodotres()->where('alumno_id', $alumno->id)->get();
        });

        return view(
            'alumnos.vistasAlumnos.calificaciones',
            compact('alumno', 'periodoUno', 'periodoDos', 'periodoTres', 'periodosAgrupados')
        );
    }

    /* public function calificaciones($id)
    {
        $alumno = Alumno::with(['ciclo.cursos.periodos', 'ciclo.cursos.periododos', 'ciclo.cursos.periodotres'])->findOrFail($id);
        $periodosAgrupados = $alumno->periodo()
            ->with('curso')
            ->get()
            ->groupBy('nombre');

        $periodoUno = $alumno->ciclo->cursos->flatMap(function ($curso) use ($alumno) {
            return $curso->periodos()->where('alumno_id', $alumno->id)->get();
        });
        $periodoDos = $alumno->ciclo->cursos->flatMap(function ($curso) use ($alumno) {
            return $curso->periododos()->where('alumno_id', $alumno->id)->get();
        });
        $periodoTres = $alumno->ciclo->cursos->flatMap(function ($curso) use ($alumno) {
            return $curso->periodotres()->where('alumno_id', $alumno->id)->get();
        });

        return view('alumnos.vistasAlumnos.calificaciones', compact('alumno', 'periodoUno', 'periodoDos', 'periodoTres', 'periodosAgrupados'));
    } */
}
