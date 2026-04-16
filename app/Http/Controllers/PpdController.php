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
    /* public function index(Request $request)
    {
        $alumno = auth()->user()->alumnoB;
        if ($alumno) {
            $alumno->load([
                'user.programa.ciclos.cursos',
                'ciclo.cursos.docentes' => fn ($q) => $q->orderBy('nombre', 'asc'),
            ]);
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
 */
    public function index(Request $request)
    {
        $alumno = auth()->user()->alumnoB;

        if ($alumno) {
            // Obtener todos los cursos del programa a través de los ciclos
            $programa = $alumno->user->programa;

            $cursos = Curso::whereHas('ciclo', function ($query) use ($programa) {
                $query->where('programa_id', $programa->id);
            })
                ->with(['ciclo', 'docentes' => fn ($q) => $q->orderBy('nombre')])
                ->orderBy('ciclo_id')
                ->orderBy('nombre')
                ->get();

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
        // 1. VALIDACIÓN PRINCIPAL (usando las reglas del modelo)
        $validator = \Validator::make($request->all(), ppd::getValidationRules());

        if ($validator->fails()) {
            \Log::info('Errores de validación principal:', $validator->errors()->toArray());

            return back()->withErrors($validator)->withInput();
        }

        // 2. VALIDACIONES ADICIONALES (las que no están en las reglas)
        $erroresExtra = [];

        // Verificar que número y referencia sean diferentes
        if ($request->numero === $request->numero_referencia) {
            $erroresExtra['numero'] = 'El campo Número y Número de referencia deben ser diferentes.';
        }

        // Verificar arrays vacíos - CORRECCIÓN IMPORTANTE
        // Verificar si el campo existe Y está vacío
        if (! $request->has('bienes_vivienda') || empty($request->bienes_vivienda)) {
            $erroresExtra['bienes_vivienda'] = 'Debe seleccionar al menos un bien de vivienda.';
        }

        if (! $request->has('otros_servicios') || empty($request->otros_servicios)) {
            $erroresExtra['otros_servicios'] = 'Debe seleccionar al menos un Servicio Adicional en Vivienda.';
        }

        if (! $request->has('habilidades') || empty($request->habilidades)) {
            $erroresExtra['habilidades'] = 'Debe seleccionar al menos una opción en Habilidades.';
        }

        // Si hay errores extra, devolver
        if (! empty($erroresExtra)) {
            \Log::info('Errores extra:', $erroresExtra);

            return back()->withInput()->withErrors($erroresExtra);
        }

        // 3. PROCESAR DATOS ESPECIALES
        try {
            // Procesar número de comprobante
            $userInput = $request->input('num_comprobante');

            if ($userInput) { // Verificar que no sea null
                if (stripos($userInput, 'Beca') !== false) {
                    $counter = ppd::where('num_comprobante', 'like', 'Beca%')->count() + 1;
                    $request->merge(['num_comprobante' => 'Beca_'.$counter]);
                } elseif (stripos($userInput, 'AMANTANI') !== false) {
                    $counter = ppd::where('num_comprobante', 'like', 'AMANTANI%')->count() + 1;
                    $request->merge(['num_comprobante' => 'AMANTANI_'.$counter]);
                } elseif (stripos($userInput, 'deuda') !== false) {
                    $counter = ppd::where('num_comprobante', 'like', 'Deudor%')->count() + 1;
                    $request->merge(['num_comprobante' => 'Deudor_'.$counter]);
                }
            }

            // Procesar arrays a strings - Verificar que existan antes de implode
            $bienes = $request->has('bienes_vivienda') ? implode(',', $request->bienes_vivienda) : '';
            $otrosServicios = $request->has('otros_servicios') ? implode(',', $request->otros_servicios) : '';
            $talentos = $request->has('habilidades') ? implode('-', $request->habilidades) : '';

            // 4. PREPARAR DATOS PARA CREACIÓN
            $camposPermitidos = (new ppd)->getFillable();
            $datosCreacion = $request->only($camposPermitidos);

            // Agregar los arrays procesados
            $datosCreacion['bienes_vivienda'] = $bienes;
            $datosCreacion['otros_servicios'] = $otrosServicios;
            $datosCreacion['habilidades'] = $talentos;

            // Log para depuración
            \Log::info('Datos a crear:', $datosCreacion);

            // 5. CREAR REGISTRO
            $nuevoAlumno = ppd::create($datosCreacion);

            // 6. REDIRECCIONAR
            if (auth()->check()) {
                if (method_exists(ppd::class, 'asociarPorEmail')) {
                    ppd::asociarPorEmail($nuevoAlumno->email);
                }

                return redirect()->route('ppd.index')->with('success', 'Alumno registrado exitosamente!');
            } else {
                return redirect()->route('index')->with('success', 'Has sido registrado exitosamente. Te enviaremos un correo con tus credenciales de acceso.');
            }

        } catch (\Exception $e) {
            // Log del error para debugging
            \Log::error('Error al registrar alumno PPD: '.$e->getMessage());
            \Log::error('Stack trace: '.$e->getTraceAsString());
            \Log::error('Datos que causaron error:', $request->all());

            // Mensaje amigable para el usuario
            return back()
                ->withInput()
                ->with('error', 'Ocurrió un error al procesar el formulario: '.$e->getMessage());
        }
    }

    /* public function store(Request $request)
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

        if ($numero === $numero_referencia) {
            return redirect()->back()->withInput()->withErrors(['numero' => 'El campo Número y Número de referencia deben ser diferentes.']);
        }

        if ($lengua_1 === $lengua_2) {
            return redirect()->back()->withInput()->withErrors(['lengua_1' => 'La Lengua 1 y Lengua 2 deben ser diferentes.']);
        }

        if ($validator->fails()) {
            return redirect()->back()->withInput()->withErrors($validator);
        }

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

                'convivientes',
                'quien_mantiene',
                'cant_dependientes_child',
                'cant_dependientes_old',
                'cant_dependientes_otros',
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
                'trabajas',
                'donde_trabajas',
                'ingreso_mensual',
                'egreso',
                'hrs_laboradas_sem',
                'ayuda_economica',
                'tiempo_ayuda',
                'tipo_apoyo_formacion',
                'tipo_vivienda',
                'situacion_vivienda',
                'dormitorios_vivienda',
                'banos_vivienda',
                'material_vivienda',
                'hrs_disponibles_agua',
                'hrs_disponibles_desague',
                'hrs_disponibles_luz',
                'problemas_salud',
                'ultima_consulta',
                'motivo_consulta',
                'tipo_seguro',
                'familiar_salud',
                'frecuencia_lectura',
                'acceso_lectura',
                'visitas_museos',
                'actividades_internet',
                'tiempo_libre',
            ]) + [
                'bienes_vivienda' => $bienes,
                'otros_servicios' => $otrosServicios,
                'habilidades' => $talentos,
            ]
        );

        if (auth()->check()) {
            ppd::asociarPorEmail($nuevoAlumno->email);

            return redirect()->route('ppd.index')->with('success', 'Alumno registrado exitosamente!');
        } else {
            return redirect()->route('index')->with('success', 'Has sido registrado exitosamente, le enviaremos un correo con sus credenciales de acceso.');
        }
    } */

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
            ->whereDoesntHave('alumnoB', function ($query) {
                $query->where('guardado', true);
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
