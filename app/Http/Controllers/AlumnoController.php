<?php

namespace App\Http\Controllers;

use App\Mail\NotificacionRegistro;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use Illuminate\Http\Request;
use App\Models\User;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\Mail;

class AlumnoController extends Controller
{
    public function index(Request $request)
    {
        /* $alumno = auth()->user()->alumno;
        if ($alumno) {
            $alumno->load('programa', 'ciclo.cursos');
        }
        return view('alumnos.vistasAlumnos.index', compact('alumno')); */

        $alumno = auth()->user()->alumno;
        if ($alumno) {
            $usuario = $alumno->user;
            $alumno->load('programa', 'ciclo.cursos');
            return view('admin.index', compact('alumno', 'usuario'));
        }
    }
    public function ficha(Alumno $alumno){
        return view('alumnos.ficha', compact('alumno'));
    }

    public function mostrarContenido(Request $request)
    {
        $request->validate([
            'alumno_id' => 'required|exists:alumnos,id',
        ]);
        $alumno = Alumno::find($request->alumno_id);
        session(['mostrar_contenido' => true]);
        Mail::to('davidmiranda.puk@gmail.com')->send(new NotificacionRegistro($alumno));
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
    // Usar 'with' para cargar las relaciones de programa y ciclo
    $alumnos = Alumno::with('programa', 'ciclo')->get();
    
    // Obtener los nombres de las columnas de la tabla 'alumnos'
    $campos = Schema::getColumnListing('alumnos');
    
    // Retornar la vista con los datos
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

        if ($userInput === 'Beca') {
            $counter = Alumno::where('num_comprobante', 'like', 'Beca%')->count() + 1;
            $request->merge(['num_comprobante' => 'Beca_' . $counter]);
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
        return view('alumnos.edit', compact('alumno', 'programas', 'ciclos', 'user', 'opcionesBienesVivienda', 'opcionesServicios', 'opcionesHabilidades'));
    }

    public function update(Request $request, Alumno $alumno)
    {
        // Obtener el ID del alumno
        $id = $alumno->id;

        // Validar los datos del formulario
        $validator = \Validator::make($request->all(), Alumno::getValidationRules(true, $id));

        // Realizar verificación adicional antes de actualizar en la base de datos
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
    }
    public function destroy(Alumno $alumno)
    {
        $alumno->delete();

        return redirect()->route('adminAlumnos')->with('success', 'El alumno ha sido eliminado correctamente.');
    }
}
