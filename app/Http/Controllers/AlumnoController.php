<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionRegistro;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\PeriodoActual;
use App\Models\ppd;
use App\Models\Programa;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Facades\Schema;

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
            // Obtener el periodo actual marcado como "actual"
            $periodoActual = PeriodoActual::where('actual', true)->first();

            return view('alumnos.vistasAlumnos.index', compact('alumno', 'usuario', 'periodoActual'));
        } else {
            return view('alumnos.vistasAlumnos.index');
        }
    }  

    public function ficha(Alumno $alumno)
    {
        $periodoActual = PeriodoActual::where('actual', true)->first();
        return view('alumnos.ficha', compact('alumno', 'periodoActual'));
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
        Mail::to([
            'davidmiranda.puk@gmail.com',
            'cobranzas.eesp@pukllavirtual.edu.pe',
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

        // 🔹 Ubicación de ejemplo (solo algunos departamentos con provincias y distritos reales para demo)

        return view('alumnos.edit', compact('alumno', 
        'programas', 
        'ciclos', 
        'user', 'opcionesBienesVivienda', 'opcionesServicios', 'opcionesHabilidades', 'departamentosData', 'provinciasData', 'distritosData'));
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

    public function estadisticas()
    {
        $alumnos = Alumno::all();
        $totalAlumnos = Alumno::count();

        // --- Programas (sin "egresados") ---
        $porPrograma = Alumno::with('programa')
            ->get()
            ->filter(fn ($a) => $a->programa && ! str_contains(strtolower($a->programa->nombre), 'egresados'))
            ->groupBy(fn ($a) => $a->programa?->nombre ?? 'Sin programa')
            ->map->count();

        // --- Ciclos (sin "egresados", ordenados I, II, III...) ---
        $romanoAInt = fn ($r) => ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10][strtoupper(trim($r))] ?? 0;

        $porCiclo = Alumno::with('ciclo')
            ->get()
            ->filter(fn ($a) => $a->ciclo && ! str_contains(strtolower($a->ciclo->nombre), 'egresados'))
            ->groupBy(fn ($a) => $a->ciclo?->nombre ?? 'Sin ciclo')
            ->map->count()
            ->sortBy(fn ($count, $nombre) => $romanoAInt($nombre));

        // --- Género ---
        $generos = Alumno::select('genero')
            ->whereNotNull('genero')
            ->get()
            ->groupBy(fn ($a) => ucfirst(strtolower($a->genero)))
            ->map->count();

        // --- Rango de edades ---
        $edad_18_25 = Alumno::edadEntre(18, 25)->count();
        $edad_26_35 = Alumno::edadEntre(26, 35)->count();

        // --- Sector socioeconómico ---
        $sectores = Alumno::select('sector_socioeconomico')
            ->groupBy('sector_socioeconomico')
            ->selectRaw('sector_socioeconomico, COUNT(*) as total')
            ->pluck('total', 'sector_socioeconomico');

        // --- NUEVOS DATOS DEMOGRÁFICOS ---
        $procedencia = Alumno::select('procedencia_familiar')->whereNotNull('procedencia_familiar')
            ->get()->groupBy('procedencia_familiar')->map->count();

        $sectorLaboral = Alumno::select('sector_laboral')->whereNotNull('sector_laboral')
            ->get()->groupBy('sector_laboral')->map->count();

        $teConsideras = Alumno::select('te_consideras')->whereNotNull('te_consideras')
            ->get()->groupBy('te_consideras')->map->count();

        $lenguas = Alumno::select('lengua_1')
            ->get()
            ->filter(fn ($a) => trim($a->lengua_1 ?? '') !== '')
            ->groupBy('lengua_1')
            ->map->count();

        $estadoCivil = Alumno::select('estado_civil')->whereNotNull('estado_civil')
            ->get()->groupBy('estado_civil')->map->count();

        $sectorSocio = Alumno::select('sector_socioeconomico')->whereNotNull('sector_socioeconomico')
            ->get()->groupBy('sector_socioeconomico')->map->count();

        $quienMantiene = Alumno::select('quien_mantiene')
            ->get()
            ->filter(fn ($a) => trim($a->quien_mantiene ?? '') !== '')
            ->groupBy('quien_mantiene')
            ->map->count();

        $ingresoMensual = Alumno::select('ingreso_mensual')
            ->get()
            ->filter(fn ($a) => trim($a->ingreso_mensual ?? '') !== '')
            ->groupBy('ingreso_mensual')
            ->map->count();

        $trabajo = Alumno::select('trabajas')->whereNotNull('trabajas')
            ->get()->groupBy('trabajas')->map->count();

        return view('admin.demograficos.alumnos', compact(
            'porPrograma',
            'porCiclo',
            'generos',
            'edad_18_25',
            'edad_26_35',
            'sectores',
            'procedencia',
            'sectorLaboral',
            'teConsideras',
            'lenguas',
            'estadoCivil',
            'sectorSocio',
            'quienMantiene',
            'trabajo',
            'ingresoMensual',
            'alumnos',
            'totalAlumnos'
        ));
    }

    //Fortmatos de video para Ti y Tesis
    public function formatos()
    {
        return view('alumnos.formatos.formato');
    }
}
