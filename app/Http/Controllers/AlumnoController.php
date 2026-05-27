<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionRegistro;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Matricula;
use App\Models\PeriodoActual;
use App\Models\PeriodoActualPpd;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        if (! auth()->check()) {
            return redirect()->route('login');
        }

        $alumno = auth()->user()->alumno;

        if ($alumno) {
            $usuario = $alumno->user;
            $alumno->load('programa', 'ciclo.cursos.docentes');
            $periodoActual = PeriodoActual::where('actual', true)->first();
            $yaMatriculado = $periodoActual
                ? $alumno->matriculas()->where('periodo_actual_id', $periodoActual->id)->exists()
                : false;

            /*
             * Cursos del alumno para el período actual:
             *   FUENTE PRIMARIA → ciclo->cursos (todos los cursos del ciclo al que pertenece)
             *   EXTRAS          → alumno_cursos filtrado por periodo_actual_id
             *                     SOLO se añaden si ciclo_id ≠ alumno->ciclo_id
             *                     (cursos de otro ciclo asignados explícitamente por el admin)
             *
             * alumno_cursos NO es la fuente primaria; es únicamente para asignaciones
             * extracurriculares de ciclos distintos al propio del alumno.
             */
            // Base: todos los cursos del ciclo propio (ya cargados vía eager load)
            $cursosBase = $alumno->ciclo ? $alumno->ciclo->cursos : collect();

            // Extras: cursos de OTROS ciclos asignados para el período actual
            $cursosExtra = collect();
            if ($periodoActual) {
                $cursosExtra = $alumno->cursosDelPeriodo($periodoActual->id)
                    ->get()
                    ->filter(fn ($c) => $c->ciclo_id !== $alumno->ciclo_id);
            }

            // Unión sin duplicados
            $cursosDelAlumno = $cursosBase->merge($cursosExtra)->unique('id')->values();

            return view('alumnos.vistasAlumnos.index', compact(
                'alumno', 'usuario', 'periodoActual', 'yaMatriculado', 'cursosDelAlumno'
            ));
        } else {
            return view('alumnos.vistasAlumnos.index');
        }
    }

    public function ficha(Alumno $alumno)
    {
        $periodoActual = PeriodoActual::where('actual', true)->first();
        $cursosAsignados = $periodoActual
            ? $alumno->cursosDelPeriodo($periodoActual->id)->with('ciclo')->get()
            : collect();

        return view('alumnos.ficha', compact('alumno', 'periodoActual', 'cursosAsignados'));
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
            if (! $alumno) {
                return redirect()->back()->with('error', 'Alumno no encontrado.');
            }
        } elseif ($user->hasRole('alumnoB')) {
            $alumno = ppd::find($alumno_id);
            if (! $alumno) {
                return redirect()->back()->with('error', 'AlumnoB no encontrado.');
            }
        } else {
            return redirect()->back()->with('error', 'Rol no autorizado.');
        }

        session(['mostrar_contenido' => true]);
        Mail::to(config('services.notificaciones.emails'))
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

    public function store(Request $request)
    {
        // Validar los datos del formulario
        $validator = Validator::make($request->all(), Alumno::getValidationRules());

        // Realizar verificación adicional antes de almacenar en la base de datos
        $numero = $request->input('numero');
        $numero_referencia = $request->input('numero_referencia');
        $lengua_1 = $request->input('lengua_1');
        $lengua_2 = $request->input('lengua_2');

        $userInput = $request->input('num_comprobante');

        if (stripos($userInput, 'Beca') !== false) {
            $counter = Alumno::where('num_comprobante', 'like', 'Beca%')->count() + 1;
            $request->merge(['num_comprobante' => 'Beca_'.$counter]);
        }

        if (stripos($userInput, 'AMANTANI') !== false) {
            $counter = Alumno::where('num_comprobante', 'like', 'AMANTANI%')->count() + 1;
            $request->merge(['num_comprobante' => 'AMANTANI_'.$counter]);
        }

        /* if (strtolower($userInput) === 'con deuda') */
        if (stripos($userInput, 'deuda') !== false) {
            $counter = Alumno::where('num_comprobante', 'like', 'Deudor%')->count() + 1;
            $request->merge(['num_comprobante' => 'Deudor_'.$counter]);
        }

        $bienes_vivienda = $request->input('bienes_vivienda', []);
        if (empty($bienes_vivienda)) {
            return redirect()->back()->withInput()->withErrors(['bienes_vivienda' => 'Debe seleccionar al menos un bien de vivienda.']);
        }

        $otros_servicios = $request->input('otros_servicios', []);
        if (empty($otros_servicios)) {
            return redirect()->back()->withInput()->withErrors(['otros_servicios' => 'Debe seleccionar al menos un Servicio Adicionales en Vivienda.']);
        }

        $habilidades = $request->input('habilidades', []);
        if (empty($habilidades)) {
            return redirect()->back()->withInput()->withErrors(['habilidades' => 'Debe seleccionar al menos una opción en Habilidades.']);
        }

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
                'bienes_vivienda' => $bienes_vivienda,
                'otros_servicios' => $otros_servicios,
                'habilidades' => $habilidades,
            ]
        );

        // Manejo de la imagen
        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = Str::uuid() . '.' . $foto->getClientOriginalExtension();
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

            // Crear registro de matrícula vinculado al periodo actual
            $periodoActual = PeriodoActual::where('actual', true)->first();
            if ($periodoActual) {
                Matricula::updateOrCreate(
                    ['alumno_id' => $nuevoAlumno->id, 'periodo_actual_id' => $periodoActual->id],
                    ['fecha_completado' => now(), 'estado' => 'matriculado']
                );
            }

            // Enviar email de notificación automáticamente al completar el formulario
            try {
                Mail::to(config('services.notificaciones.emails'))
                    ->send(new NotificacionRegistro($nuevoAlumno));
            } catch (\Exception $e) {
                Log::error('Error enviando email de matrícula (store): ' . $e->getMessage());
            }

            return redirect()->route('alumnos.index')->with('success', '¡Matrícula completada exitosamente! Se ha enviado una notificación al administrador.');
        } else {
            return redirect()->route('index')->with('success', 'Has sido registrado exitosamente, le enviaremos un correo con sus credenciales de acceso.');
        }
    }

    public function show($id)
    {
        $alumno = Alumno::with([
            'user',
            'programa',
            'ciclo.cursos',
            'cursos.ciclo',
            'periodo.curso',
            'periodo.periodoActual',
        ])->find($id);

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

    public function edit(Request $request, Alumno $alumno)
    {
        $programas = Programa::all();
        $user = auth()->user();
        // Los casts automáticamente convierten JSON a array, no necesitamos explode
        $departamentos = Departamento::with('provincias.distritos')->get();
        $departamentosData = [];
        foreach ($departamentos as $dep) {
            $departamentosData[$dep->nombre] = [
                'provincia' => [],
            ];

            foreach ($dep->provincias as $prov) {
                $departamentosData[$dep->nombre]['provincia'][$prov->nombre] =
                    $prov->distritos->pluck('nombre')->toArray();
            }
        }
        // 3. Para autoselección en edición
        $provinciasData = Provincia::where('departamento_id', $alumno->departamento_id)->pluck('nombre')->toArray();
        $distritosData = Distrito::where('provincia_id', $alumno->provincia_id)->pluck('nombre')->toArray();
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
        // Los casts automáticamente convierten JSON a array
        $opcionesServicios = [
            'Empleado(a) doméstico',
            'Servicio de teléfono',
            'Servicio de cable',
            'Servicio de Internet',
            'Ninguna de las anteriores',
        ];
        // Los casts automáticamente convierten JSON a array
        $opcionesHabilidades = [
            'Música (Instrumentos, canto)',
            'Artes pláticas (Pintura, Escultura, etc)',
            'Danzas (Danzas folklóricas, Ballet, Etc)',
            'Literatura (Poesía, Cuentos, etc)',
            'Otros',
        ];

        // 🔹 Ubicación de ejemplo (solo algunos departamentos con provincias y distritos reales para demo)

        $programaIdParaCiclos = (int) $request->old('programa_id', $alumno->programa_id);
        $ciclosPorPrograma = $programaIdParaCiclos
            ? Ciclo::where('programa_id', $programaIdParaCiclos)->orderBy('id')->get()
            : collect();

        return view('alumnos.edit', compact('alumno',
            'programas',
            'ciclosPorPrograma',
            'user', 'opcionesBienesVivienda', 'opcionesServicios', 'opcionesHabilidades', 'departamentosData', 'provinciasData', 'distritosData'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        $alumno = Alumno::findOrFail($alumno->id);

        $rules = [
            'genero' => 'required|string',
            'numero' => 'required|string|max:20',
            'numero_referencia' => 'required|string|max:20',
            'fecha_nacimiento' => 'required|date',
            'num_hijos' => 'nullable|integer|min:0',
            'trabajas' => 'required|string',
            'departamento' => 'required|string|max:255',
            'provincia' => 'required|string|max:255',
            'distrito' => 'required|string|max:255',
            'direccion' => 'required|string|max:255',
        ];
        if (! $request->user()->hasRole('admin')) {
            $rules['num_comprobante'] = 'required|string|max:50';
        }
        if ($request->user()->hasRole('admin')) {
            $rules['programa_id'] = 'required|exists:programas,id';
            $rules['ciclo_id'] = 'required|exists:ciclos,id';
        }
        $request->validate($rules);

        if ($request->user()->hasRole('admin')) {
            $ciclo = Ciclo::findOrFail($request->input('ciclo_id'));
            if ((int) $ciclo->programa_id !== (int) $request->input('programa_id')) {
                return redirect()->back()->withInput()->withErrors([
                    'ciclo_id' => 'El ciclo seleccionado no pertenece al programa indicado.',
                ]);
            }
        }

        $data = $request->except(['programa_id', 'ciclo_id']);
        if ($request->user()->hasRole('admin')) {
            unset($data['num_comprobante']);
            $data['programa_id'] = (int) $request->input('programa_id');
            $data['ciclo_id'] = (int) $request->input('ciclo_id');
        }
        $alumno->update($data);

        if ($request->user()->hasRole('admin')) {
            return redirect()->route('adminAlumnos')->with('success', 'Datos registrados correctamente.');
        }

        return redirect()->route('alumnos.index', $alumno)->with('success', 'Datos registrados correctamente.');
    }

    public function destroy(Alumno $alumno)
    {
        $alumno->delete();

        return redirect()->route('adminAlumnos')->with('success', 'El alumno ha sido eliminado correctamente.');
    }

    public function calificaciones($id)
    {
        $alumno = Alumno::with(['ciclo.cursos'])->findOrFail($id);

        // Traemos todos los periodos del alumno, con curso y periodoActual
        $periodos = $alumno->periodo()
            ->with(['curso.ciclo.programa', 'periodoActual'])
            ->get();

        // Agrupamos por el nombre del PeriodoActual (ej. 2024-I, 2024-II, etc.)
        $periodosAgrupados = $periodos->groupBy(function ($p) {
            return optional($p->periodoActual)->nombre ?? 'Sin periodo';
        })->sortKeys();

        /*
         * Cursos para "Período actual":
         *   PRIMARIO → ciclo->cursos (todos los cursos del ciclo propio)
         *   EXTRAS   → alumno_cursos del período actual de OTRO ciclo
         */
        $periodoActual = PeriodoActual::where('actual', true)->first();

        $cursosBase  = $alumno->ciclo ? $alumno->ciclo->cursos : collect();
        $cursosExtra = collect();
        if ($periodoActual) {
            $cursosExtra = $alumno->cursosDelPeriodo($periodoActual->id)
                ->get()
                ->filter(fn ($c) => $c->ciclo_id !== $alumno->ciclo_id);
        }
        $cursosDelAlumno = $cursosBase->merge($cursosExtra)->unique('id')->values();

        return view(
            'alumnos.vistasAlumnos.calificaciones',
            compact('alumno', 'periodosAgrupados', 'cursosDelAlumno')
        );
    }

    public function estadisticas(\Illuminate\Http\Request $request)
    {
        $tab = $request->input('tab', 'fid_periodo');

        // ── Períodos FID ───────────────────────────────────────────────────
        $periodos      = PeriodoActual::orderBy('id', 'desc')->get();
        $periodoActual = PeriodoActual::where('actual', true)->first();

        // ── Períodos PPD ───────────────────────────────────────────────────
        $periodosPpd      = PeriodoActualPpd::orderBy('id', 'desc')->get();
        $periodoActualPpd = PeriodoActualPpd::where('actual', true)->first();

        // ── Período seleccionado ───────────────────────────────────────────
        $periodoId    = $request->input('periodo_id');
        $periodoPpdId = $request->input('periodo_ppd_id');

        $periodoSeleccionado = $periodoId
            ? PeriodoActual::find($periodoId)
            : ($periodoActual ?? $periodos->first());

        $periodoPpdSeleccionado = $periodoPpdId
            ? PeriodoActualPpd::find($periodoPpdId)
            : ($periodoActualPpd ?? $periodosPpd->first());

        // ── Helper: orden romano ───────────────────────────────────────────
        $romanoAInt = fn ($r) => [
            'I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V'  => 5,
            'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10,
        ][strtoupper(trim($r))] ?? 0;

        // ── Helper: calcula estadísticas de cualquier colección ────────────
        $computeStats = function ($collection) use ($romanoAInt) {
            $totalAlumnos = $collection->count();
            $hoy          = now();

            $porPrograma = $collection
                ->filter(fn ($a) => $a->programa && ! str_contains(strtolower($a->programa->nombre), 'egresados'))
                ->groupBy(fn ($a) => $a->programa?->nombre ?? 'Sin programa')
                ->map->count()
                ->sortByDesc(fn ($v) => $v);

            $porCiclo = $collection
                ->filter(fn ($a) => $a->ciclo && ! str_contains(strtolower($a->ciclo->nombre), 'egresados'))
                ->groupBy(fn ($a) => $a->ciclo?->nombre ?? 'Sin ciclo')
                ->map->count()
                ->sortBy(fn ($count, $nombre) => $romanoAInt($nombre));

            $generos = $collection
                ->filter(fn ($a) => ! empty($a->genero))
                ->groupBy(fn ($a) => ucfirst(strtolower($a->genero)))
                ->map->count();

            $edad_18_25 = $collection->filter(function ($a) use ($hoy) {
                if (empty($a->fecha_nacimiento)) {
                    return false;
                }
                try {
                    $age = $hoy->diffInYears(\Carbon\Carbon::parse($a->fecha_nacimiento));
                } catch (\Throwable $e) {
                    return false;
                }

                return $age >= 18 && $age <= 25;
            })->count();

            $edad_26_35 = $collection->filter(function ($a) use ($hoy) {
                if (empty($a->fecha_nacimiento)) {
                    return false;
                }
                try {
                    $age = $hoy->diffInYears(\Carbon\Carbon::parse($a->fecha_nacimiento));
                } catch (\Throwable $e) {
                    return false;
                }

                return $age >= 26 && $age <= 35;
            })->count();

            $sectorSocio    = $collection->filter(fn ($a) => ! empty($a->sector_socioeconomico))->groupBy('sector_socioeconomico')->map->count();
            $sectores       = $sectorSocio;
            $procedencia    = $collection->filter(fn ($a) => ! empty($a->procedencia_familiar))->groupBy('procedencia_familiar')->map->count()->sortByDesc(fn ($v) => $v);
            $sectorLaboral  = $collection->filter(fn ($a) => ! empty($a->sector_laboral))->groupBy('sector_laboral')->map->count()->sortByDesc(fn ($v) => $v);
            $teConsideras   = $collection->filter(fn ($a) => ! empty($a->te_consideras))->groupBy('te_consideras')->map->count();
            $lenguas        = $collection->filter(fn ($a) => trim($a->lengua_1 ?? '') !== '')->groupBy('lengua_1')->map->count()->sortByDesc(fn ($v) => $v);
            $estadoCivil    = $collection->filter(fn ($a) => ! empty($a->estado_civil))->groupBy('estado_civil')->map->count();
            $quienMantiene  = $collection->filter(fn ($a) => trim($a->quien_mantiene ?? '') !== '')->groupBy('quien_mantiene')->map->count()->sortByDesc(fn ($v) => $v);
            $ingresoMensual = $collection->filter(fn ($a) => trim($a->ingreso_mensual ?? '') !== '')->groupBy('ingreso_mensual')->map->count();
            $trabajo        = $collection->filter(fn ($a) => ! empty($a->trabajas))->groupBy('trabajas')->map->count();

            return compact(
                'totalAlumnos', 'porPrograma', 'porCiclo', 'generos',
                'edad_18_25', 'edad_26_35', 'sectorSocio', 'sectores',
                'procedencia', 'sectorLaboral', 'teConsideras', 'lenguas',
                'estadoCivil', 'quienMantiene', 'ingresoMensual', 'trabajo'
            );
        };

        // ── Carga de datos según pestaña activa ────────────────────────────
        switch ($tab) {
            case 'fid_todos':
                $collection             = Alumno::with(['programa', 'ciclo'])->get();
                $periodoSeleccionado    = null;
                $periodoPpdSeleccionado = null;
                break;

            case 'ppd_periodo':
                $periodoSeleccionado = null;
                if ($periodoPpdSeleccionado) {
                    $ppdIds     = DB::table('periodo_ppds')
                        ->where('periodo_actual_ppd_id', $periodoPpdSeleccionado->id)
                        ->distinct()->pluck('alumno_id');
                    $collection = ppd::with(['programa', 'ciclo'])->whereIn('id', $ppdIds)->get();
                } else {
                    $collection = collect();
                }
                break;

            case 'ppd_todos':
                $collection             = ppd::with(['programa', 'ciclo'])->get();
                $periodoSeleccionado    = null;
                $periodoPpdSeleccionado = null;
                break;

            default: // fid_periodo
                $tab                    = 'fid_periodo';
                $periodoPpdSeleccionado = null;
                if ($periodoSeleccionado) {
                    $pid        = $periodoSeleccionado->id;
                    $collection = Alumno::with(['programa', 'ciclo'])
                        ->whereHas('matriculas', fn ($m) => $m->where('periodo_actual_id', $pid))
                        ->get();
                } else {
                    $collection = collect();
                }
                break;
        }

        extract($computeStats($collection));

        return view('admin.demograficos.alumnos', compact(
            'tab',
            'periodos', 'periodoSeleccionado',
            'periodosPpd', 'periodoPpdSeleccionado',
            'porPrograma', 'porCiclo', 'generos',
            'edad_18_25', 'edad_26_35',
            'sectores', 'procedencia', 'sectorLaboral',
            'teConsideras', 'lenguas', 'estadoCivil', 'sectorSocio',
            'quienMantiene', 'trabajo', 'ingresoMensual',
            'totalAlumnos'
        ));
    }

    //Fortmatos de video para Ti y Tesis
    public function formatos()
    {
        return view('alumnos.formatos.formato');
    }

    public function editarDatos()
    {
        $alumno = auth()->user()->alumno;
        if (! $alumno) {
            return redirect()->route('alumnos.index');
        }

        $periodoActual = PeriodoActual::where('actual', true)->first();
        $matriculaActual = $periodoActual
            ? $alumno->matriculas()->where('periodo_actual_id', $periodoActual->id)->first()
            : null;
        $formularioHabilitado = $periodoActual?->formulario_habilitado ?? false;

        return view('alumnos.vistasAlumnos.actualizar-datos', compact('alumno', 'periodoActual', 'matriculaActual', 'formularioHabilitado'));
    }

    public function actualizarDatos(Request $request)
    {
        $alumno = auth()->user()->alumno;
        if (! $alumno) {
            return redirect()->route('alumnos.index');
        }

        $periodoActual = PeriodoActual::where('actual', true)->first();
        $matriculaActual = $periodoActual
            ? $alumno->matriculas()->where('periodo_actual_id', $periodoActual->id)->first()
            : null;

        $esBecado = (bool) auth()->user()->beca;

        $rules = [
            'foto'               => 'nullable|image|mimes:jpg,jpeg,png,webp|max:3048',
            'numero'             => 'required|string|max:20',
            'numero_referencia'  => 'required|string|max:20',
            'direccion'          => 'required|string|max:255',
            'departamento'       => 'nullable|string|max:100',
            'provincia'          => 'nullable|string|max:100',
            'distrito'           => 'nullable|string|max:100',
            'estado_civil'       => 'required|string',
            'p_m_soltero'        => 'required|boolean',
            'num_hijos'          => 'required|integer|min:0',
            'sector_socioeconomico' => 'required|string',
            'trabajas'           => 'required|string',
            'donde_trabajas'     => 'nullable|string|max:255',
            'ingreso_mensual'    => 'nullable|string|max:100',
            'egreso'             => 'required|string|max:100',
            'hrs_laboradas_sem'  => 'required|integer|min:0',
            'sector_laboral'     => 'nullable|string|max:100',
            'ayuda_economica'    => 'required|boolean',
            'tiempo_ayuda'       => 'required|string|max:100',
            'tipo_apoyo_formacion' => 'required|string|max:100',
            'convivientes'       => 'required|string|max:255',
            'quien_mantiene'     => 'required|string|max:255',
            'cant_dependientes_child' => 'required|string|max:50',
            'cant_dependientes_old'   => 'required|string|max:50',
            'cant_dependientes_otros' => 'required|string|max:50',
            'tipo_vivienda'      => 'required|string',
            'situacion_vivienda' => 'required|string',
            'dormitorios_vivienda' => 'required|integer|min:0',
            'banos_vivienda'     => 'required|integer|min:0',
            'material_vivienda'  => 'required|string',
            'hrs_disponibles_agua'    => 'required|integer|min:0',
            'hrs_disponibles_desague' => 'required|integer|min:0',
            'hrs_disponibles_luz'     => 'required|integer|min:0',
            'tipo_seguro'        => 'required|string',
            'bienes_vivienda'    => 'required|array|min:1',
            'otros_servicios'    => 'nullable|array',
            'estudio_beca'       => 'required',
            'permanencia_vivienda' => 'required|string',
        ];

        if (! $alumno->alumnoTieneFechaNacimientoCapturada()) {
            $rules['fecha_nacimiento'] = 'required|date';
        }
        if (! $alumno->genero) {
            $rules['genero'] = 'required|string';
        }

        $formularioHabilitado = $periodoActual?->formulario_habilitado ?? false;
        $requiereVoucher = $formularioHabilitado && ! $esBecado;
        if ($requiereVoucher) {
            $rules['comprobante'] = 'required|string|max:100';
        }

        $request->validate($rules);

        if ($request->numero === $request->numero_referencia) {
            return redirect()->back()->withInput()->withErrors(['numero' => 'El celular y el celular de emergencia deben ser diferentes.']);
        }

        $campos = [
            'numero', 'numero_referencia', 'direccion', 'departamento', 'provincia', 'distrito',
            'estado_civil', 'p_m_soltero', 'num_hijos', 'sector_socioeconomico',
            'trabajas', 'donde_trabajas', 'ingreso_mensual', 'egreso', 'hrs_laboradas_sem',
            'sector_laboral', 'ayuda_economica', 'tiempo_ayuda', 'tipo_apoyo_formacion',
            'convivientes', 'quien_mantiene', 'cant_dependientes_child', 'cant_dependientes_old', 'cant_dependientes_otros',
            'tipo_vivienda', 'situacion_vivienda', 'dormitorios_vivienda', 'banos_vivienda', 'material_vivienda',
            'hrs_disponibles_agua', 'hrs_disponibles_desague', 'hrs_disponibles_luz',
            'tipo_seguro', 'estudio_beca', 'permanencia_vivienda',
        ];

        if (! $alumno->alumnoTieneFechaNacimientoCapturada() && $request->filled('fecha_nacimiento')) {
            $campos[] = 'fecha_nacimiento';
        }
        if (! $alumno->genero && $request->filled('genero')) {
            $campos[] = 'genero';
        }

        $datos = $request->only($campos);
        $datos['bienes_vivienda'] = $request->input('bienes_vivienda', []);
        $datos['otros_servicios'] = $request->input('otros_servicios', []);

        $alumno->update($datos);

        if ($request->hasFile('foto')) {
            $foto = $request->file('foto');
            $nombreFoto = Str::uuid() . '.' . $foto->getClientOriginalExtension();
            $foto->move(public_path('img/estudiantes'), $nombreFoto);
            auth()->user()->update(['foto' => $nombreFoto]);
        }

        if ($periodoActual && $formularioHabilitado) {
            $comprobante = $request->filled('comprobante') ? $request->comprobante : null;
            Matricula::updateOrCreate(
                ['alumno_id' => $alumno->id, 'periodo_actual_id' => $periodoActual->id],
                array_filter(['fecha_completado' => now(), 'estado' => 'matriculado', 'comprobante' => $comprobante])
            );

            try {
                Mail::to(config('services.notificaciones.emails'))
                    ->send(new NotificacionRegistro($alumno));
            } catch (\Exception $e) {
                Log::error('Error enviando email de matrícula (actualizarDatos): ' . $e->getMessage());
            }
        } elseif ($matriculaActual && $request->filled('comprobante')) {
            $matriculaActual->update(['comprobante' => $request->comprobante]);
        }

        return redirect()->route('alumnos.index')->with('success', '¡Ficha de matrícula completada correctamente!');
    }
}
