<?php

namespace App\Http\Controllers;

use App\Mail\ConfirmacionInscripcionPpdMail;
use App\Models\AdminPpd;
use App\Models\Ciclo;
use App\Models\Departamento;
use App\Models\PostulantesPpd;
use App\Models\ppd;
use App\Models\Programa;
use Spatie\Permission\Models\Role; 
use App\Models\User;
use Hash;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;

class PostulantesPpdController extends Controller
{
    public function index()
    {
        /*  $postulantes = PostulantesPpd::orderBy('apellidos')->get(); */
        $postulantes = PostulantesPpd::masRecientes()->get();
        $postulantesPorPrograma = PostulantesPpd::selectRaw('programa, COUNT(*) as total')
            ->groupBy('programa')
            ->orderBy('programa')
            ->get();
        $totalPostulantes = $postulantesPorPrograma->sum('total');

        return view('alumnos.postulantes.admision.ppd.postulantes.index', compact('postulantes', 'postulantesPorPrograma', 'totalPostulantes'));
    }

    public function create(AdminPpd $admin_ppd)
    {
        if ($admin_ppd->estado === false || $admin_ppd->estado == 0) {
            $mensaje = 'Este año hemos terminado con nuestros procesos de Admisión para el programa de Profesionalización Docente, publicaremos nueva fechas por nuestras redes sociales! '.
                      'Facebook: eesp.pukllasunchis | Instagram: eesp.pukllasunchis | TikTok: @eesp.pukllasunchis';

            return redirect()->route('index')->with('success', $mensaje);
        }
        $departamentos = Departamento::orderBy('nombre')->get();

        return view('alumnos.postulantes.admision.ppd.postulantes.create',
            compact('admin_ppd', 'departamentos')
        );
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:postulantes_ppds,email',
            'programa' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'dni' => 'required|string|max:15|unique:postulantes_ppds,dni',
            'genero' => 'nullable|string',

            'estadoCivil' => 'required|string',
            'vecesPostulo' => 'required|string',
            'fecha_nacimiento' => 'required|string',
            'lugar_nacimiento' => 'required|string',
            'departamento_nacimiento' => 'required|string',
            'provincia_nacimiento' => 'required|string',
            'distrito_nacimiento' => 'required|string',
            'edad' => 'required|string',
            'hijos' => 'required|string',
            'lengua_1' => 'required|string',
            'lengua_2' => 'nullable|string',
            'trabaja' => 'nullable|string',
            'lugar_trabajo' => 'nullable|string',
            'cargo' => 'nullable|string',
            'opinionEespp' => 'nullable|string',
            'carrera' => 'required|string',

            'domicilio' => 'nullable|string',
            'telefono' => 'required|string',

            'tipo_institucion' => 'nullable|string',
            'nombre_institucion' => 'nullable|string',
            'gestion_institucion' => 'nullable|string',
            'direccion_institucion' => 'nullable|string',
            'departamento_institucion' => 'nullable|string',
            'provincia_institucion' => 'nullable|string',
            'distrito_institucion' => 'nullable|string',

            'anio_conclusion' => 'nullable|integer',
            'medio_conocimiento' => 'nullable|string',

            // Archivos
            'dni_adjunto' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'certificado' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'foto' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            'titulo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'voucher' => 'required|file|mimes:jpg,jpeg,png,pdf|max:4096',
        ]);

        // =========================
        // SUBIDA DE ARCHIVOS
        // =========================
        $uploadPath = public_path('img/admin/ppd/');
        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileFields = [
            'dni_adjunto',
            'certificado',
            'foto',
            'titulo',
            'voucher',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                $file = $request->file($field);

                $filename = time().'_'.$field.'.'.$file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);

                $data[$field] = 'img/admin/ppd/'.$filename;
            }
        }
        PostulantesPpd::create($data);

        return redirect()->route('index')->with('success', 'Inscripcion creada con éxito, pronto recibirás un correo de respuesta hacia el correo registrado.');
    }

    public function show($id)
    {
        $postulante = PostulantesPpd::findOrFail($id);

        return view('alumnos.postulantes.admision.ppd.postulantes.show', compact('postulante'));
    }

    public function edit(AdminPpd $admin_ppd, PostulantesPpd $postulante)
    {
        $departamentos = Departamento::all();

        return view(
            'alumnos.postulantes.admision.ppd.postulantes.edit',
            compact('admin_ppd', 'postulante', 'departamentos')
        );
    }

    public function update(Request $request, AdminPpd $admin_ppd, PostulantesPpd $postulante)
    {
        $data = $request->validate([
            'email' => 'required|email|unique:postulantes_ppds,email,'.$postulante->id,
            'programa' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'nombres' => 'required|string|max:255',
            'dni' => 'required|string|max:15|unique:postulantes_ppds,dni,'.$postulante->id,
            'genero' => 'nullable|string',

            'estadoCivil' => 'required|string',
            'vecesPostulo' => 'required|string',
            'fecha_nacimiento' => 'required|string',
            'lugar_nacimiento' => 'required|string',
            'departamento_nacimiento' => 'required|string',
            'provincia_nacimiento' => 'required|string',
            'distrito_nacimiento' => 'required|string',
            'edad' => 'required|string',
            'hijos' => 'required|string',
            'lengua_1' => 'required|string',
            'lengua_2' => 'nullable|string',
            'trabaja' => 'nullable|string',
            'lugar_trabajo' => 'nullable|string',
            'cargo' => 'nullable|string',
            'opinionEespp' => 'nullable|string',
            'carrera' => 'required|string',

            'domicilio' => 'nullable|string',
            'telefono' => 'required|string',

            'tipo_institucion' => 'nullable|string',
            'nombre_institucion' => 'nullable|string',
            'gestion_institucion' => 'nullable|string',
            'direccion_institucion' => 'nullable|string',
            'departamento_institucion' => 'nullable|string',
            'provincia_institucion' => 'nullable|string',
            'distrito_institucion' => 'nullable|string',

            'anio_conclusion' => 'nullable|integer',
            'medio_conocimiento' => 'nullable|string',

            // Archivos (ahora opcionales en update)
            'dni_adjunto' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'certificado' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'foto' => 'nullable|file|mimes:jpg,jpeg,png|max:4096',
            'titulo' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096',
            'voucher' => 'nullable|file|mimes:jpg,jpeg,png,pdf|max:4096', // Cambiado a nullable
        ]);

        $uploadPath = public_path('img/admin/ppd/');
        if (! file_exists($uploadPath)) {
            mkdir($uploadPath, 0755, true);
        }

        $fileFields = [
            'dni_adjunto',
            'certificado',
            'foto',
            'titulo',
            'voucher',
        ];

        foreach ($fileFields as $field) {
            if ($request->hasFile($field)) {
                // Eliminar archivo anterior si existe
                if ($postulante->$field && file_exists(public_path($postulante->$field))) {
                    unlink(public_path($postulante->$field));
                }

                $file = $request->file($field);
                $filename = time().'_'.$field.'.'.$file->getClientOriginalExtension();
                $file->move($uploadPath, $filename);

                $data[$field] = 'img/admin/ppd/'.$filename;
            }
        }

        dd($data);
        // Actualizar el postulante
        $postulante->update($data);

        return redirect()
            ->route('postulantes.ppd.index', $admin_ppd)
            ->with('success', 'Postulante actualizado correctamente.');
    }

    public function destroy(AdminPpd $admin_ppd, PostulantesPpd $postulante)
    {
        $postulante->delete();

        return redirect()
            ->route('postulantes.ppd.index', $admin_ppd)
            ->with('success', 'Postulante eliminado correctamente.');
    }

    public function updateApto(Request $request, $id)
    {
        $postulante = PostulantesPpd::findOrFail($id);

        $request->validate([
            'apto' => 'nullable|boolean',
            'apto2' => 'nullable|boolean',
        ]);

        // Actualizar solo el campo que se envió
        if ($request->has('apto')) {
            $postulante->apto = $request->apto;
        }

        if ($request->has('apto2')) {
            $postulante->apto2 = $request->apto2;
        }

        $postulante->save();

        return response()->json([
            'success' => true,
            'message' => 'Estado actualizado',
            'apto' => $postulante->apto,
            'apto2' => $postulante->apto2,
            'apto_completo' => $postulante->apto && $postulante->apto2,
        ]);
    }

    public function enviarCorreo($postulanteId)
    {
        $postulante = PostulantesPpd::findOrFail($postulanteId);
        $adminPpd = AdminPpd::activos()->firstOrFail();

        // Validar que esté apto
        if (! $postulante->apto || ! $postulante->apto2) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'error' => 'El postulante no está verificado en ambos campos',
                ], 400);
            }

            return back()->with('error', 'El postulante no está verificado en ambos campos');
        }

        if (! $postulante->constancia) {
            $postulante->constancia = PostulantesPpd::generarConstancia();
            $postulante->save();
        }

        try {
            Mail::to($postulante->email)
                ->cc('davidmiranda.puk@gmail.com')
                ->send(new ConfirmacionInscripcionPpdMail($postulante, $adminPpd));

            $postulante->enviado = true;
            $postulante->save();

            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'success' => 'Correo enviado correctamente a '.$postulante->email,
                    'enviado' => true,
                ]);
            }

            return back()->with(
                'success', 'Correo enviado correctamente a '.$postulante->email
            );

        } catch (\Exception $e) {
            if (request()->ajax() || request()->wantsJson()) {
                return response()->json([
                    'error' => 'Error al enviar el correo: '.$e->getMessage(),
                ], 500);
            }

            return back()->with('error', 'Error al enviar el correo: '.$e->getMessage());
        }
    }

    public function seleccionMasiva()
    {
        // Obtener solo postulantes que NO han sido convertidos aún
        // Asumiendo que agregamos un campo 'convertido' a la tabla
        $postulantes = PostulantesPpd::where('convertido', false)
            ->orWhereNull('convertido')
            ->orderBy('apellidos')
            ->get();

        // Contar por programa para el resumen
        $postulantesPorPrograma = PostulantesPpd::select('programa', DB::raw('count(*) as total'))
            ->groupBy('programa')
            ->get();

        $totalPostulantes = $postulantes->count();

        return view('alumnos.postulantes.admision.ppd.postulantes.seleccionar', compact('postulantes', 'postulantesPorPrograma', 'totalPostulantes'));
    }

    public function convertirMasivo(Request $request)
    {
        $request->validate([
            'postulantes' => 'required|array',
            'postulantes.*' => 'exists:postulantes_ppds,id',
        ]);

        $resultados = [
            'exitos' => [],
            'errores' => [],
        ];

        DB::beginTransaction();

        try {
            $programaInicial = Programa::where('nombre', 'LIKE', '%Inicial%')
                ->where('nombre', 'LIKE', '%PPD%')
                ->first();

            $programaPrimaria = Programa::where('nombre', 'LIKE', '%Primaria%')
                ->where('nombre', 'LIKE', '%PPD%')
                ->first();

            if (! $programaInicial || ! $programaPrimaria) {
                throw new \Exception('No se encontraron los programas Inicial PPD o Primaria PPD en el sistema');
            }

            foreach ($request->postulantes as $postulanteId) {
                $postulante = PostulantesPpd::find($postulanteId);
                $usuarioExistente = User::where('dni', $postulante->dni)->first();
                if ($usuarioExistente) {
                    $resultados['errores'][] = "{$postulante->apellidos}, {$postulante->nombres} - Ya existe usuario con ese DNI";

                    continue;
                }
                $programaAsignado = null;
                $sufijoPrograma = '';
                $textoPrograma = strtoupper($postulante->programa);
                if (str_contains($textoPrograma, 'INICIAL')) {
                    $programaAsignado = $programaInicial;
                    $sufijoPrograma = 'ini';
                } elseif (str_contains($textoPrograma, 'PRIMARIA')) {
                    $programaAsignado = $programaPrimaria;
                    $sufijoPrograma = 'pri';
                }
                if ($programaAsignado === null) {
                    $resultados['errores'][] = "{$postulante->apellidos}, {$postulante->nombres} - No se pudo determinar el programa (texto: '{$postulante->programa}')";

                    continue;
                }

                $primerCiclo = Ciclo::where('programa_id', $programaAsignado->id)
                    ->orderBy('id', 'asc')
                    ->first();

                if ($primerCiclo === null) {
                    $resultados['errores'][] = "{$postulante->apellidos}, {$postulante->nombres} - No se encontró un ciclo para el programa {$programaAsignado->nombre}";

                    continue;
                }

                $emailGenerado = $this->generarEmailPostulante($postulante, $sufijoPrograma);

                $emailExistente = User::where('email', $emailGenerado)->first();
                if ($emailExistente) {
                    $resultados['errores'][] = "{$postulante->apellidos}, {$postulante->nombres} - El email generado {$emailGenerado} ya existe en el sistema";

                    continue;
                }

                $usuario = User::create([
                    'name' => $postulante->nombres,
                    'apellidos' => $postulante->apellidos,
                    'dni' => $postulante->dni,
                    'email' => $emailGenerado, // Usar el email generado
                    'password' => Hash::make($postulante->dni),
                    'estadoCivil' => $postulante->estadoCivil,
                    'fecha_nacimiento' => $postulante->fecha_nacimiento,
                    'edad' => $postulante->edad,
                    'hijos' => $postulante->hijos,
                    'lengua_1' => $postulante->lengua_1,
                    'lengua_2' => $postulante->lengua_2,
                    'domicilio' => $postulante->domicilio,
                    'telefono' => $postulante->telefono,
                    'foto' => $postulante->foto,
                    'dni_adjunto' => $postulante->dni_adjunto,
                    'condicion' => 'activo',
                    'perfil' => 'postulante',
                    'beca' => false,
                    'programa_id' => $programaAsignado->id,
                    'ciclo_id' => $primerCiclo->id,
                ]);

                // Asignar rol
                $usuario->assignRole('alumnob');
                $postulante->convertido = true;
                $postulante->save();

                $resultados['exitos'][] = "{$postulante->apellidos}, {$postulante->nombres} - Usuario creado exitosamente en {$programaAsignado->nombre} (Email: {$emailGenerado})";
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proceso completado',
                'resultados' => $resultados,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error en el proceso: '.$e->getMessage(),
            ], 500);
        }
    }

    private function generarEmailPostulante($postulante, $sufijoPrograma)
    {
        $primerApellido = trim(explode(' ', $postulante->apellidos)[0]);
        $primerApellido = $this->limpiarTexto($primerApellido);
        $nombres = explode(' ', trim($postulante->nombres));
        $primerNombre = $this->limpiarTexto($nombres[0]);
        $email = strtolower($primerApellido.$primerNombre).'.ppd'.$sufijoPrograma.'@pukllavirtual.edu.pe';

        return $email;
    }

    private function limpiarTexto($texto)
    {
        $texto = strtolower($texto);
        $reemplazos = [
            'á' => 'a', 'é' => 'e', 'í' => 'i', 'ó' => 'o', 'ú' => 'u',
            'à' => 'a', 'è' => 'e', 'ì' => 'i', 'ò' => 'o', 'ù' => 'u',
            'ä' => 'a', 'ë' => 'e', 'ï' => 'i', 'ö' => 'o', 'ü' => 'u',
            'ñ' => 'n',
            'ç' => 'c',
        ];

        $texto = strtr($texto, $reemplazos);
        $texto = preg_replace('/[^a-z]/', '', $texto);

        return $texto;
    }

    /* public function convertirMasivo(Request $request)
    {
        $request->validate([
            'postulantes' => 'required|array',
            'postulantes.*' => 'exists:postulantes_ppds,id',
        ]);

        $resultados = [
            'exitos' => [],
            'errores' => [],
        ];

        DB::beginTransaction();

        try {
            $programaInicial = Programa::where('nombre', 'LIKE', '%Inicial%')
                ->where('nombre', 'LIKE', '%PPD%')
                ->first();

            $programaPrimaria = Programa::where('nombre', 'LIKE', '%Primaria%')
                ->where('nombre', 'LIKE', '%PPD%')
                ->first();

            if (! $programaInicial || ! $programaPrimaria) {
                throw new \Exception('No se encontraron los programas Inicial PPD o Primaria PPD en el sistema');
            }

            foreach ($request->postulantes as $postulanteId) {
                $postulante = PostulantesPpd::find($postulanteId);
                $usuarioExistente = User::where('email', $postulante->email)
                    ->orWhere('dni', $postulante->dni)
                    ->first();

                if ($usuarioExistente) {
                    $resultados['errores'][] = "{$postulante->apellidos}, {$postulante->nombres} - Ya existe usuario con ese email o DNI";

                    continue;
                }
                $programaAsignado = null;
                $textoPrograma = strtoupper($postulante->programa);

                if (str_contains($textoPrograma, 'INICIAL')) {
                    $programaAsignado = $programaInicial;
                } elseif (str_contains($textoPrograma, 'PRIMARIA')) {
                    $programaAsignado = $programaPrimaria;
                }

                if ($programaAsignado === null) {
                    $resultados['errores'][] = "{$postulante->apellidos}, {$postulante->nombres} - No se pudo determinar el programa (texto: '{$postulante->programa}')";

                    continue;
                }
                $primerCiclo = Ciclo::where('programa_id', $programaAsignado->id)
                    ->orderBy('id', 'asc')
                    ->first();

                if ($primerCiclo === null) {
                    $resultados['errores'][] = "{$postulante->apellidos}, {$postulante->nombres} - No se encontró un ciclo para el programa {$programaAsignado->nombre}";

                    continue;
                }

                $usuario = User::create([
                    'name' => $postulante->nombres,
                    'apellidos' => $postulante->apellidos,
                    'dni' => $postulante->dni,
                    'email' => $postulante->email,
                    'password' => Hash::make($postulante->dni),
                    'estadoCivil' => $postulante->estadoCivil,
                    'fecha_nacimiento' => $postulante->fecha_nacimiento,
                    'edad' => $postulante->edad,
                    'hijos' => $postulante->hijos,
                    'lengua_1' => $postulante->lengua_1,
                    'lengua_2' => $postulante->lengua_2,
                    'domicilio' => $postulante->domicilio,
                    'telefono' => $postulante->telefono,
                    'foto' => $postulante->foto,
                    'dni_adjunto' => $postulante->dni_adjunto,
                    'condicion' => 'activo',
                    'perfil' => 'postulante',
                    'beca' => false,
                    'programa_id' => $programaAsignado->id,
                    'ciclo_id' => $primerCiclo->id,
                ]);

                // Asignar rol
                $usuario->assignRole('alumnob');
                $postulante->convertido = true;
                $postulante->save();

                $resultados['exitos'][] = "{$postulante->apellidos}, {$postulante->nombres} - Usuario creado exitosamente en {$programaAsignado->nombre}";
            }

            DB::commit();

            return response()->json([
                'success' => true,
                'message' => 'Proceso completado',
                'resultados' => $resultados,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();

            return response()->json([
                'success' => false,
                'message' => 'Error en el proceso: '.$e->getMessage(),
            ], 500);
        }
    } */
}
