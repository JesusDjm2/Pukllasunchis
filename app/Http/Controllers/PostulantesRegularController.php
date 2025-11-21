<?php

namespace App\Http\Controllers;

use App\Exports\PostulantesRegularExport;
use App\Mail\ConfirmacionInscripcionMail;
use App\Models\AdminFid;
use App\Models\Ciclo;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\PostulantesRegular;
use App\Models\Programa;
use App\Models\Provincia;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;
use Maatwebsite\Excel\Facades\Excel;
use Spatie\Permission\Contracts\Role;

class PostulantesRegularController extends Controller
{
    public function form()
    {
        $departamentos = Departamento::orderBy('nombre')->get();

        return view('alumnos.postulantes.regulares.formulario', compact('departamentos'));
    }

    public function getProvincias($departamentoId)
    {
        $provincias = Provincia::where('departamento_id', $departamentoId)->orderBy('nombre')->get();

        return response()->json($provincias);
    }

    public function getDistritos($provinciaId)
    {
        $distritos = Distrito::where('provincia_id', $provinciaId)->orderBy('nombre')->get();

        return response()->json($distritos);
    }
    public function index()
    {
        $postulantes = PostulantesRegular::whereNull('admin_fids_id')->get();
        $admision = AdminFid::where('estado', true)->first();

        return view('alumnos.postulantes.regulares.index', compact('postulantes', 'admision'));
    }
    public function create()
    {
        return view('alumnos.postulantes.regulares.create');
    }
    public function toggleObservacion($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        $postulante->observaciones = ! $postulante->observaciones;
        $postulante->save();
        return back();
    }
    public function store(Request $request)
    {
        $validated = $request->validate([
            'email' => 'required|email|unique:postulantes_regulars,email', 
            'programa' => 'required|string', // El programa debe ser una cadena no vacía
            'estudio_beca' => 'required|boolean',
            'apellidos' => 'required|string|max:255', // Apellidos deben ser una cadena y no exceder 255 caracteres
            'nombres' => 'required|string|max:255', 
            'dni' => 'required|unique:postulantes_regulars,dni', 
            'genero' => 'required|boolean', 
            'direccion' => 'nullable|string|max:255',
            'numero' => 'required|string', 
            'fecha_nacimiento' => 'required|date', // La fecha de nacimiento debe ser válida
            'lugar_nacimiento' => 'nullable|string|max:255', 
            'distrito_nacimiento' => 'nullable|string|max:255', // Distrito de nacimiento opcional
            'provincia_nacimiento' => 'nullable|string|max:255', 
            'departamento_nacimiento' => 'nullable|string|max:255',
            'contacto' => 'nullable|string|max:255',

            'colegio' => 'required|string|max:255', // Nombre del colegio
            'codigo_colegio' => 'required|string|max:50', // Código del colegio
            'gestion_colegio' => 'required|string|in:publico,privado', // Gestión del colegio debe ser público o privado
            'direccion_colegio' => 'nullable|string|max:255', // Dirección del colegio opcional
            'distrito_colegio' => 'nullable|string|max:255', // Distrito del colegio opcional
            'provincia_colegio' => 'nullable|string|max:255', // Provincia del colegio opcional
            'departamento_colegio' => 'nullable|string|max:255', // Departamento del colegio opcional
            'ano_termino_colegio' => 'required|digits:4|integer|min:1900|max:'.date('Y'), // Año de término debe ser de 4 dígitos y válido
            'promedio_colegio' => 'required|numeric|between:0,20', // Promedio debe ser un número entre 0 y 20
            'lengua_1' => 'required|string|max:255',
            'lengua_2' => 'nullable|string|max:255',

            'nivel_quechua_hablado' => 'nullable|string|in:Básico,Intermedio,Avanzado',
            'nivel_quechua_escrito' => 'nullable|string|in:Básico,Intermedio,Avanzado',

            'estado_civil' => 'required|string|in:soltero,casado,divorciado,viudo', // Estado civil debe ser una opción válida
            'num_hijos' => 'required|integer|min:0', // Número de hijos, mínimo 0
            'trabajas' => 'required|boolean', // Trabajas debe ser booleano
            'donde_trabajas' => 'nullable|string|max:255', // Dónde trabajas es opcional
            'cargo_trabajas' => 'nullable|string|max:255', // Cargo en el trabajo es opcional
            'describe_eespp' => 'required|string',

            'dni_pdf' => 'required|file|mimes:pdf|max:4048', // Máx. 2MB
            'partida_nacimiento_pdf' => 'nullable|file|mimes:pdf|max:4048', // Máx. 2MB
            'certificado_secundaria_pdf' => 'nullable|file|mimes:pdf|max:4048', // Máx. 2MB
            'foto' => 'required|file|mimes:jpg,png|max:4048', // Solo JPG o PNG, máx. 2MB
            'declaracion_jurada_salud_pdf' => 'nullable|file|mimes:pdf|max:3048', // Máx. 2MB
            'declaracion_jurada_documentos_pdf' => 'nullable|file|mimes:pdf|max:3048', // Máx. 2MB
            'declaracion_jurada_conectividad_pdf' => 'nullable|file|mimes:pdf|max:3048', // Máx. 2MB
            /* 'voucher_pago' => 'nullable|file|mimes:pdf,jpg,png|max:4120', */
            'voucher_pago' => 'nullable|file|mimes:pdf,jpg,png|max:4120|required_if:estudio_beca,0',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $uploadPath = public_path('postulantes/regulares/');

        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $documentPaths = [];
        $fileFields = [
            'dni_pdf',
            'partida_nacimiento_pdf',
            'certificado_secundaria_pdf',
            'foto',
            'declaracion_jurada_salud_pdf',
            'declaracion_jurada_documentos_pdf',
            'declaracion_jurada_conectividad_pdf',
            'voucher_pago',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = $file->getClientOriginalName();
                $file->move($uploadPath, $filename);
                $documentPaths[$field] = 'postulantes/regulares/'.$filename;
            }
        }

        $data = array_merge($validated, $documentPaths);

        PostulantesRegular::create($data);

        return redirect()->route('index')->with('success', 'Inscripcion creada con éxito, pronto recibirás un correo de respuesta hacia el correo registrado.');
    }
    public function show($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        return view('alumnos.postulantes.regulares.show', compact('postulante'));
    }
    public function edit($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        $departamentos = Departamento::orderBy('nombre')->get();
        $provincias = Provincia::where('departamento_id', $postulante->departamento_nacimiento)->get();
        $distritos = Distrito::where('provincia_id', $postulante->provincia_nacimiento)->get();
        return view('alumnos.postulantes.regulares.edit', compact('postulante', 'departamentos', 'provincias', 'distritos'));
    }

    public function update(Request $request, $id)
    {
        $postulante = PostulantesRegular::findOrFail($id); // Buscar al postulante con el ID dado.

        $validated = $request->validate([
            'email' => 'required|email|unique:postulantes_regulars,email,'.$postulante->id, // Aseguramos que el email sea único, excluyendo el postulante actual
            'programa' => 'required|string',
            'estudio_beca' => 'required|boolean',
            'apellidos' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'dni' => 'required|unique:postulantes_regulars,dni,'.$postulante->id, // Aseguramos que el DNI sea único, excluyendo el postulante actual
            'genero' => 'required|boolean',
            'direccion' => 'required|string|max:255',
            'numero' => 'required|numeric',
            'fecha_nacimiento' => 'required|date',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'distrito_nacimiento' => 'nullable|string|max:255',
            'provincia_nacimiento' => 'nullable|string|max:255',
            'departamento_nacimiento' => 'nullable|string|max:255',
            'colegio' => 'required|string|max:255',
            'codigo_colegio' => 'required|string|max:50',
            'gestion_colegio' => 'required|string|in:publico,privado',
            'direccion_colegio' => 'nullable|string|max:255',
            'distrito_colegio' => 'nullable|string|max:255',
            'provincia_colegio' => 'nullable|string|max:255',
            'departamento_colegio' => 'nullable|string|max:255',
            'ano_termino_colegio' => 'required|digits:4|integer|min:1900|max:'.date('Y'),
            'promedio_colegio' => 'nullable|numeric|between:0,20',
            'lengua_1' => 'required|string|max:255',
            'lengua_2' => 'nullable|string|max:255',
            'estado_civil' => 'nullable|string|in:soltero,casado,divorciado,viudo',
            'num_hijos' => 'nullable|integer|min:0',
            'trabajas' => 'nullable|boolean',
            'donde_trabajas' => 'nullable|string|max:255',
            'cargo_trabajas' => 'nullable|string|max:255',
            'describe_eespp' => 'required|string|max:500',

            'dni_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'partida_nacimiento_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'certificado_secundaria_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'foto' => 'nullable|file|mimes:jpg,png|max:3048',
            'declaracion_jurada_salud_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'declaracion_jurada_documentos_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'declaracion_jurada_conectividad_pdf' => 'nullable|file|mimes:pdf|max:3048',
            'voucher_pago' => 'nullable|file|mimes:pdf,jpg,png|max:3048',
            'observaciones' => 'nullable|string|max:1000',
        ]);

        $uploadPath = public_path('postulantes/regulares/');

        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0777, true);
        }

        $documentPaths = [];
        $fileFields = [
            'dni_pdf',
            'partida_nacimiento_pdf',
            'certificado_secundaria_pdf',
            'foto',
            'declaracion_jurada_salud_pdf',
            'declaracion_jurada_documentos_pdf',
            'declaracion_jurada_conectividad_pdf',
            'voucher_pago',
        ];
        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time().'_'.$file->getClientOriginalName();
                $file->move($uploadPath, $filename);
                $documentPaths[$field] = 'postulantes/regulares/'.$filename;

                // Eliminar el archivo anterior si existe
                if (! empty($postulante->$field) && file_exists(public_path($postulante->$field))) {
                    unlink(public_path($postulante->$field));
                }
            } else {
                // Mantener el archivo anterior si no se subió uno nuevo
                $documentPaths[$field] = $postulante->$field;
            }
        }

        /* foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);
                $filename = time() . '_' . $file->getClientOriginalName(); // Añadimos un prefijo con el timestamp para evitar conflictos de nombres
                $file->move($uploadPath, $filename);
                $documentPaths[$field] = 'postulantes/regulares/' . $filename;

                // Eliminar archivo anterior si existe (para evitar acumular archivos no necesarios)
                if ($postulante->$field) {
                    $previousFile = public_path($postulante->$field);
                    if (file_exists($previousFile)) {
                        unlink($previousFile);
                    }
                }
            }
        } */

        // Fusionamos los campos validados con las rutas de los documentos (si se subieron)
        $data = array_merge($validated, $documentPaths);

        // Actualizamos los datos del postulante
        $postulante->update($data);

        return redirect()->route('regulares.index')->with('success', 'Inscripción actualizada con éxito.');
    }

    public function destroy($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        $postulante->delete();

        return redirect()->back()->with('success', 'Postulante eliminado correctamente.');
    }

    public function exportarCSV()
    {
        return Excel::download(new PostulantesRegularExport, 'postulantes.csv');
    }

    public function crearIngresantes()
    {
        $postulantesInicial = PostulantesRegular::where('programa', 'Educación Inicial')
            ->orderBy('apellidos', 'asc')
            ->get();

        $postulantesPrimaria = PostulantesRegular::where('programa', 'Educación Primaria EIB')
            ->orderBy('apellidos', 'asc')
            ->get();

        return view('alumnos.postulantes.ingresantes', compact('postulantesInicial', 'postulantesPrimaria'));
    }

    public function guardarIngresantes(Request $request)
    {
        $request->validate([
            'postulantesSeleccionados' => 'required|array',
            'postulantesSeleccionados.*' => 'exists:postulantes_regulars,id',
        ]);

        $postulantesSeleccionados = $request->input('postulantesSeleccionados');

        $postulantes = PostulantesRegular::whereIn('id', $postulantesSeleccionados)->get();

        foreach ($postulantes as $postulante) {
            $programa = Programa::where('nombre', $postulante->programa)->first();

            if (! $programa) {
                return back()->with('error', "El programa '{$postulante->programa}' no existe en la base de datos.");
            }
            $ciclo = Ciclo::where('nombre', 'I')->where('programa_id', $programa->id)->first();

            if (! $ciclo) {
                return back()->with('error', "No se encontró el ciclo 'I' para el programa '{$postulante->programa}'.");
            }
            $user = User::create([
                'name' => $postulante->nombres,
                'apellidos' => $postulante->apellidos,
                'dni' => $postulante->dni,
                'condicion' => $postulante->estudio_beca ? 'Regular' : 'Beca',
                'pendiente' => 'No',
                'perfil' => 'Estudiante',
                'beca' => ! $postulante->estudio_beca,
                'email' => $postulante->email,
                'password' => Hash::make($postulante->dni),
            ]);
            $role = Role::where('name', 'alumno')->first();
            if ($role) {
                $user->assignRole($role);
            }
            $user->programa()->associate($programa->id);
            $user->ciclo()->associate($ciclo->id);
            $user->save();
        }

        return redirect()->route('admin')->with('success', 'Ingresantes guardados correctamente.');
    }

    public function enviarCorreo($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        $adminFid = AdminFid::where('estado', 'si')->first();

        if (! $postulante->constancia) {
            $postulante->constancia = PostulantesRegular::generarConstancia();
            $postulante->save();
        }

        Mail::to($postulante->email)
            ->cc('davidmiranda.puk@gmail.com')
            ->send(new ConfirmacionInscripcionMail($postulante, $adminFid));

        return back()->with('success', 'Correo enviado correctamente a '.$postulante->email);
    }
}
