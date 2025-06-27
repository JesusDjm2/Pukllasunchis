<?php

namespace App\Http\Controllers;

use App\Models\Calificacion;
use App\Models\Calificacionesppd;
use App\Models\Ciclo;
use App\Models\Competencia;
use App\Models\Curso;
use App\Models\Docente;
use App\Models\ppd;
use App\Models\Programa;
use Illuminate\Http\Request;

class PpdController extends Controller
{
    public function index(Request $request)
    {
        $alumno = auth()->user()->alumnoB;
        if ($alumno) {
            $alumno->load('programa', 'ciclo.cursos.docentes');
        }
        return view('alumnos.ppd.index', compact('alumno'));
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
    /* public function calificacionesppd($alumno)
    {
        $alumno = auth()->user()->alumnoB;
        if ($alumno) {
            $alumno->load('programa', 'ciclo.cursos.docentes');
        }
        return view('alumnos.ppd.calificaciones', compact('alumno'));

    } */
    /* public function calificacionesppd($alumno)
    {
        $alumno = auth()->user()->alumnoB;

        if ($alumno) {
            $alumno->load('programa');

            $ciclosConCursos = Ciclo::where('programa_id', $alumno->programa_id)
                ->whereHas('cursos.calificaciones', function ($q) use ($alumno) {
                    $q->where('ppd_id', $alumno->id); // ← Aquí usamos correctamente la relación
                })
                ->with([
                    'cursos' => function ($q) use ($alumno) {
                        $q->whereHas('calificaciones', function ($q2) use ($alumno) {
                            $q2->where('ppd_id', $alumno->id);
                        })->with([
                                    'competencias',
                                    'calificaciones' => function ($q3) use ($alumno) {
                                        $q3->where('ppd_id', $alumno->id);
                                    }
                                ]);
                    }
                ])
                ->orderBy('nombre')
                ->get();
        } else {
            $ciclosConCursos = collect(); // En caso no haya alumno logueado
        }

        return view('alumnos.ppd.calificaciones', compact('alumno', 'ciclosConCursos'));
    } */
    public function calificacionesppd()
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
            $request->merge(['num_comprobante' => 'Beca_' . $counter]);
        }

        if (stripos($userInput, 'AMANTANI') !== false) {
            $counter = ppd::where('num_comprobante', 'like', 'AMANTANI%')->count() + 1;
            $request->merge(['num_comprobante' => 'AMANTANI_' . $counter]);
        }

        /* if (strtolower($userInput) === 'con deuda') */
        if (stripos($userInput, 'deuda') !== false) {
            $counter = ppd::where('num_comprobante', 'like', 'Deudor%')->count() + 1;
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
        $alumno = ppd::with('user')->find($id);
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

        return view('ppd.index', [
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

        foreach ($request->input('alumnos') as $data) {
            $proceso = $data['proceso'] ?? [];
            $final = $data['final'] ?? [];
            $promedios = $data['promedios'] ?? [];
            $pp = $pf = $pg = [];

            foreach (range(0, 2) as $i) {
                $key = array_keys($proceso)[$i] ?? null;

                if ($key) {
                    $pp["pp_c" . ($i + 1) . "_1"] = $proceso[$key]['indicador_1'] ?? null;
                    $pp["pp_c" . ($i + 1) . "_2"] = $proceso[$key]['indicador_2'] ?? null;
                    $pp["pp_c" . ($i + 1) . "_3"] = $proceso[$key]['indicador_3'] ?? null;
                    $pp["pp_c" . ($i + 1) . "_4"] = $proceso[$key]['indicador_4'] ?? null;

                    $pf["pf_c" . ($i + 1) . "_1"] = $final[$key]['indicador_1'] ?? null;
                    $pf["pf_c" . ($i + 1) . "_2"] = $final[$key]['indicador_2'] ?? null;
                    $pf["pf_c" . ($i + 1) . "_3"] = $final[$key]['indicador_3'] ?? null;
                }
            }
            Calificacionesppd::updateOrCreate(
                [
                    'ppd_id' => $data['alumno_id'],
                    'curso_id' => $curso->id,
                ],
                array_merge([
                    'nombre' => $data['nombre'] ?? 'Periodo 1',
                    'fecha' => $data['fecha'] ?? now(),
                    'nivel_desempeno' => $data['nivel_desempeno'] ?? null,
                    'calificacion_curso' => $data['calificacion_curso'] ?? null,
                    'calificacion_sistema' => $data['calificacion_sistema'] ?? null,
                    'observaciones' => $data['observaciones'] ?? null,
                ], $pp, $pf, $pg)
            );
        }

        $competenciasSeleccionadas = Competencia::whereIn('id', $request->input('competencias'))->get();
        $alumnos = $curso->ciclo->alumnosB()
            ->with([
                'user.alumnoB.calificaciones' => function ($query) use ($curso) {
                    $query->where('curso_id', $curso->id);
                }
            ])
            ->whereHas('user.roles', function ($query) {
                $query->where('name', '!=', 'inhabilitado');
            })
            ->orderBy('apellidos')
            ->get();

        /* session()->flash('success', '¡Las calificaciones se guardaron exitosamente!'); */
        session()->flash('success', "¡Felicidades $primerNombre! Las calificaciones se guardaron exitosamente.");

        return view('docentes.calificaciones.alumnosppd', [
            'curso' => $curso,
            'docente' => $docente,
            'competenciasSeleccionadas' => $competenciasSeleccionadas,
            'alumnos' => $alumnos,
        ]);
    }
}
