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

class PostulantesRegularController extends Controller
{
    public function form()
    {
        $departamentos = Departamento::orderBy('nombre')->get();
        $admision = AdminFid::where('estado', true)->first();
        if (! $admision) {
            return redirect()
                ->route('index')
                ->with('success', '
        Nuestro proceso de admisión FID '.date('Y').' ha concluido.
        Estaremos publicando nuevas convocatorias a finales de este año.
        Síguenos en nuestras redes sociales.       
    ');
        }

        return view('alumnos.postulantes.regulares.formulario', compact('departamentos'));
    }
    /* public function form()
    {
        $departamentos = Departamento::orderBy('nombre')->get();
        $admision = AdminFid::where('estado', true)->first();
         if (!$admision) {
        return redirect()->route('index');
        }
        return view('alumnos.postulantes.regulares.formulario', compact('departamentos'));
    } */

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
        foreach ($postulantes as $postulante) {
            $postulante->faltantes = $this->calcularCamposFaltantes($postulante);
        }

        return view('alumnos.postulantes.regulares.index', compact('postulantes', 'admision'));
    }

    private function calcularCamposFaltantes($postulante)
    {
        $camposRequeridos = [
            'email', 'programa', 'apellidos', 'nombres', 'dni',
            'genero',            'direccion',
            'numero',            'fecha_nacimiento',
            'lugar_nacimiento',            'distrito_nacimiento',
            'provincia_nacimiento',            'departamento_nacimiento',
            'contacto',            'colegio',
            'codigo_colegio',
            'gestion_colegio',            'direccion_colegio',
            'distrito_colegio',
            'provincia_colegio',            'departamento_colegio',
            'ano_termino_colegio',            'promedio_colegio',
            'lengua_1',            'lengua_2',
            'etnicoidad',            'nivel_quechua_hablado',
            'nivel_quechua_escrito',            'estado_civil',
            'num_hijos',            'trabajas',
            'donde_trabajas',            'cargo_trabajas',
            'describe_eespp',
        ];

        $faltantes = [];
        foreach ($camposRequeridos as $campo) {
            if (empty($postulante->$campo) && $postulante->$campo !== false && $postulante->$campo !== 0) {
                if (in_array($campo, ['genero', 'trabajas'])) {
                    if (is_null($postulante->$campo)) {
                        $faltantes[] = $campo;
                    }
                } else {
                    $faltantes[] = $campo;
                }
            }
        }

        return $faltantes;
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
            'programa' => 'required|string',
            'estudio_beca' => 'required|boolean',
            'apellidos' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'dni' => 'required',
            'genero' => 'required|boolean',
            'direccion' => 'required|string|max:255',
            'numero' => 'required|string',
            'fecha_nacimiento' => 'required|date',
            'lugar_nacimiento' => 'required|string|max:255',
            'departamento_nacimiento' => 'required|string|max:255',
            'provincia_nacimiento' => 'required|string|max:255',
            'distrito_nacimiento' => 'required|string|max:255',
            'contacto' => 'required|string|max:255',

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
            'etnicoidad' => 'nullable|string',

            'nivel_quechua_hablado' => 'nullable|string|in:Básico,Intermedio,Avanzado',
            'nivel_quechua_escrito' => 'nullable|string|in:Básico,Intermedio,Avanzado',

            'estado_civil' => 'required|string|in:soltero,casado,divorciado,viudo',
            'num_hijos' => 'required|integer|min:0',
            'trabajas' => 'required|boolean',
            'donde_trabajas' => 'nullable|string|max:255',
            'cargo_trabajas' => 'nullable|string|max:255',
            'describe_eespp' => 'required|string',

            'dni_pdf' => 'required|file|mimes:pdf|max:4048', // Máx. 2MB
            'partida_nacimiento_pdf' => 'nullable|file|mimes:pdf|max:4048', // Máx. 2MB
            'certificado_secundaria_pdf' => 'nullable|file|mimes:pdf|max:4048', // Máx. 2MB
            'foto' => 'required|file|mimes:jpg,png|max:4048', // Solo JPG o PNG, máx. 2MB

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

        return view('alumnos.postulantes.regulares.edit', compact('postulante', 'departamentos'));
    }

    public function update(Request $request, $id)
    {
        $postulante = PostulantesRegular::findOrFail($id); // Buscar al postulante con el ID dado.

        // Reglas de validación base
        $rules = [
            'email' => 'required|email|unique:postulantes_regulars,email,'.$postulante->id,
            'programa' => 'required|string',
            'estudio_beca' => 'required|boolean',
            'apellidos' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'dni' => 'required|unique:postulantes_regulars,dni,'.$postulante->id,
            'nivel_quechua_hablado' => 'nullable|string',
            'nivel_quechua_escrito' => 'nullable|string',
            'genero' => 'required|boolean',
            'direccion' => 'required|string|max:255',
            'numero' => 'required|numeric',
            'fecha_nacimiento' => 'required|date',
            'lugar_nacimiento' => 'nullable|string|max:255',
            'colegio' => 'required|string|max:255',
            'codigo_colegio' => 'required|string|max:50',
            'gestion_colegio' => 'required|string|in:publico,privado',
            'direccion_colegio' => 'nullable|string|max:255',
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
            'voucher_pago' => 'nullable|file|mimes:pdf,jpg,png|max:3048',
            'observaciones' => 'nullable|string|max:1000',
            'etnicoidad' => 'nullable|string',
        ];

        // Agregar validaciones para campos de ubicación SOLO si se enviaron nuevos valores
        if ($request->filled('departamento_nacimiento')) {
            $rules['departamento_nacimiento'] = 'required|string|max:255';
            $rules['provincia_nacimiento'] = 'required|string|max:255';
            $rules['distrito_nacimiento'] = 'required|string|max:255';
        } else {
            // Si no se enviaron, los hacemos opcionales (mantendrán los valores actuales)
            $rules['departamento_nacimiento'] = 'nullable|string|max:255';
            $rules['provincia_nacimiento'] = 'nullable|string|max:255';
            $rules['distrito_nacimiento'] = 'nullable|string|max:255';
        }

        if ($request->filled('departamento_colegio')) {
            $rules['departamento_colegio'] = 'required|string|max:255';
            $rules['provincia_colegio'] = 'required|string|max:255';
            $rules['distrito_colegio'] = 'required|string|max:255';
        } else {
            $rules['departamento_colegio'] = 'nullable|string|max:255';
            $rules['provincia_colegio'] = 'nullable|string|max:255';
            $rules['distrito_colegio'] = 'nullable|string|max:255';
        }

        $validated = $request->validate($rules);

        $data = $validated;

        if (! $request->filled('departamento_nacimiento')) {
            unset($data['departamento_nacimiento'], $data['provincia_nacimiento'], $data['distrito_nacimiento']);
        }

        if (! $request->filled('departamento_colegio')) {
            unset($data['departamento_colegio'], $data['provincia_colegio'], $data['distrito_colegio']);
        }

        // Procesar archivos
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

        // Fusionar los datos validados con las rutas de los documentos
        $data = array_merge($data, $documentPaths);

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
        $postulantesInicial = $this->obtenerPostulantesFiltrados('Educación Inicial');
        $postulantesPrimaria = $this->obtenerPostulantesFiltrados('Educación Primaria EIB');

        return view('alumnos.postulantes.ingresantes', compact('postulantesInicial', 'postulantesPrimaria'));
    }

    private function obtenerPostulantesFiltrados($programa)
    {
        return PostulantesRegular::where('programa', $programa)
            ->where(function ($query) {
                $query->where('estudio_beca', false)
                    ->orWhereNull('estudio_beca');
            })
            ->whereNull('admin_fids_id')
            ->where('convertido', false)
            ->orderBy('apellidos', 'asc')
            ->orderBy('nombres', 'asc')
            ->get();
    }

    public function guardarIngresantes(Request $request)
    {
        try {
            $request->validate([
                'postulantesSeleccionados' => 'required|array',
                'postulantesSeleccionados.*' => 'exists:postulantes_regulars,id',
            ]);

            $postulantes = PostulantesRegular::whereIn('id', $request->postulantesSeleccionados)
                ->where('convertido', false) // Solo los no convertidos
                ->get();

            if ($postulantes->isEmpty()) {
                return redirect()->back()
                    ->with('warning', 'No hay postulantes pendientes por convertir.')
                    ->withInput();
            }

            $resultado = [
                'exitosos' => 0,
                'duplicados' => [],
                'errores' => [],
            ];

            foreach ($postulantes as $postulante) {
                try {
                    // Validar duplicado por DNI
                    if (User::where('dni', $postulante->dni)->exists()) {
                        $resultado['duplicados'][] = "{$postulante->nombres} {$postulante->apellidos} (DNI: {$postulante->dni})";

                        continue;
                    }

                    // Buscar programa (excluyendo PPD)
                    $programa = $this->buscarPrograma($postulante->programa);
                    if (! $programa) {
                        $resultado['errores'][] = "Programa '{$postulante->programa}' no válido - {$postulante->nombres} {$postulante->apellidos}";

                        continue;
                    }

                    // Obtener ciclo inicial
                    $ciclo = $this->obtenerPrimerCiclo($programa->id);
                    if (! $ciclo) {
                        $resultado['errores'][] = "No hay ciclo para {$postulante->programa} - {$postulante->nombres} {$postulante->apellidos}";

                        continue;
                    }

                    // Crear usuario
                    $user = $this->crearUsuario($postulante, $programa, $ciclo);

                    // MARCAR COMO CONVERTIDO
                    $postulante->update([
                        'convertido' => true,
                        'fecha_conversion' => now(), // Si tienes este campo
                    ]);

                    $resultado['exitosos']++;

                } catch (\Exception $e) {
                    $resultado['errores'][] = "{$postulante->nombres} {$postulante->apellidos}: {$e->getMessage()}";
                    \Log::error("Error al convertir postulante ID {$postulante->id}: {$e->getMessage()}");
                }
            }

            // Mensaje final
            return $this->retornarRespuesta($resultado);

        } catch (\Exception $e) {
            \Log::error('Error en guardarIngresantes: '.$e->getMessage());

            return redirect()->back()
                ->with('error', 'Error al procesar la solicitud')
                ->withInput();
        }
    }

    private function buscarPrograma($nombrePrograma)
    {
        // Evitar programas PPD
        if (stripos($nombrePrograma, 'PPD') !== false) {
            return null;
        }
        $programa = Programa::where('nombre', $nombrePrograma)
            ->where('nombre', 'NOT LIKE', '%PPD%')
            ->first();

        if (! $programa && stripos($nombrePrograma, 'Inicial') !== false) {
            $programa = Programa::where('nombre', 'LIKE', '%Inicial%')
                ->where('nombre', 'NOT LIKE', '%PPD%')
                ->first();
        }
        if (! $programa && stripos($nombrePrograma, 'Primaria') !== false) {
            $programa = Programa::where('nombre', 'LIKE', '%Primaria%')
                ->where('nombre', 'NOT LIKE', '%PPD%')
                ->first();
        }
        if (! $programa) {
            $programa = Programa::where('nombre', 'LIKE', "%{$nombrePrograma}%")
                ->where('nombre', 'NOT LIKE', '%PPD%')
                ->first();
        }

        return $programa;
    }

    private function crearUsuario($postulante, $programa, $ciclo)
    {
        $email = $this->generarEmailUnico($postulante, $programa->nombre);

        $userData = [
            'name' => $postulante->nombres,
            'apellidos' => $postulante->apellidos,
            'dni' => $postulante->dni,
            'condicion' => 'Regular',
            'pendiente' => 'No',
            'perfil' => 'Estudiante',
            'beca' => false,
            'email' => $email,
            'password' => Hash::make($postulante->dni),
            'foto' => null,
            'estadoCivil' => $postulante->estado_civil,
            'fecha_nacimiento' => $postulante->fecha_nacimiento,
            'edad' => $postulante->edad,
            'hijos' => $postulante->num_hijos ?? 0,
            'lengua_1' => $postulante->lengua_1,
            'lengua_2' => $postulante->lengua_2,
            'domicilio' => $postulante->direccion,
            'telefono' => $postulante->numero,
            'etnicoidad' => $postulante->etnicoidad,
            'genero' => $postulante->genero,
            'programa_id' => $programa->id,
            'ciclo_id' => $ciclo->id,
        ];

        $user = User::create($userData);
        $user->assignRole('alumno');
        \Log::info("Usuario creado: {$user->email}");
        return $user;
    }

    private function generarEmailUnico($postulante, $programaNombre)
    {
        $primerNombre = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', trim($postulante->nombres))[0]));
        $primerApellido = strtolower(preg_replace('/[^a-zA-Z]/', '', explode(' ', trim($postulante->apellidos))[0]));

        // Abreviatura del programa
        $abrev = match (true) {
            stripos($programaNombre, 'Inicial') !== false => 'ini',
            stripos($programaNombre, 'Primaria') !== false => 'eib',
            default => 'gen'
        };

        $año = date('Y');
        $baseEmail = "{$primerNombre}{$primerApellido}.{$abrev}{$año}@pukllavirtual.edu.pe";
        $email = $baseEmail;
        $contador = 1;

        while (User::where('email', $email)->exists()) {
            $email = "{$primerNombre}{$primerApellido}{$contador}.{$abrev}{$año}@pukllavirtual.edu.pe";
            $contador++;
        }

        return $email;
    }

    private function retornarRespuesta($resultado)
    {
        $mensaje = "✅ {$resultado['exitosos']} ingresantes convertidos correctamente.";

        if (! empty($resultado['duplicados'])) {
            $mensaje .= ' ⚠️ '.count($resultado['duplicados']).' ya existían en el sistema.';
        }

        if (! empty($resultado['errores'])) {
            $mensaje .= ' ❌ '.count($resultado['errores']).' errores.';
        }

        $tipo = $resultado['exitosos'] > 0 ? 'success' : 'error';

        return redirect()->back()
            ->with($tipo, $mensaje)
            ->with('duplicados', $resultado['duplicados'])
            ->with('errores_detallados', $resultado['errores'])
            ->withInput();
    }

    private function obtenerPrimerCiclo($programaId)
    {
        // Buscar ciclo I directamente
        $ciclo = Ciclo::where('programa_id', $programaId)
            ->whereRaw('UPPER(nombre) = ?', ['I'])
            ->first();

        return $ciclo ?? Ciclo::where('programa_id', $programaId)
            ->orderBy('id')
            ->first();
    }

    public function updateApto(Request $request, $id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        $postulante->apto = (int) $request->apto;
        $postulante->setAttribute('apto', (int) $request->apto);
        $postulante->save();

        return response()->json([
            'success' => true,
            'apto' => $postulante->apto,
        ]);
    }

    public function updateApto2(Request $request, $id)
    {
        \Log::info('updateApto2 llamado', [
            'id' => $id,
            'apto2' => $request->apto2,
            'method' => $request->method(),
            'all' => $request->all(),
        ]);

        try {
            $postulante = PostulantesRegular::findOrFail($id);
            $postulante->apto2 = (int) $request->apto2;
            $postulante->save();

            \Log::info('updateApto2 exitoso', [
                'id' => $id,
                'nuevo_valor' => $postulante->apto2,
            ]);

            return response()->json([
                'success' => true,
                'apto2' => $postulante->apto2,
            ]);
        } catch (\Exception $e) {
            \Log::error('updateApto2 error: '.$e->getMessage());

            return response()->json([
                'success' => false,
                'message' => 'Error: '.$e->getMessage(),
            ], 500);
        }
    }

    public function enviarCorreo($id)
    {
        $postulante = PostulantesRegular::findOrFail($id);
        if (! $postulante->apto || ! $postulante->apto2) {
            return redirect()->back()->with('error', 'El postulante aún no está completamente verificado.');
        }
        $adminFid = AdminFid::where('estado', 'si')->first();

        if (! $postulante->constancia) {
            $postulante->constancia = PostulantesRegular::generarConstancia();
            $postulante->save();
        }

        Mail::to($postulante->email)
            ->cc('davidmiranda.puk@gmail.com')
            ->send(new ConfirmacionInscripcionMail($postulante, $adminFid));

        $postulante->observaciones = true;
        $postulante->save();

        return back()->with('success', 'Correo enviado correctamente a '.$postulante->email);
    }
}
