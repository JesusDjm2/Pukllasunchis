<?php

namespace App\Http\Controllers;

use App\Models\Postulante;
use App\Models\Programa;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PostulanteController extends Controller
{
    public function index()
    {
        $user = auth()->user();
        $alumno = $user->alumno;
        return view('bolsa.postulante.index', compact('user', 'alumno'));
    }
    public function create()
    {
        $user = auth()->user();
        $programas = Programa::all();
        return view('bolsa.postulante.create', compact('user', 'programas'));
    }
    public function lista()
    {
        $postulantes = Postulante::all();
        $cantidad = $postulantes->count();
        return view('bolsa.lista', compact('postulantes', 'cantidad'));
    }
    public function store(Request $request)
    {
        $user = auth()->user();
        // Validar datos del formulario
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'apellidos' => 'required|string',
            'dni' => 'required|string',
            /* 'email' => 'required|string|email',  */
            'email' => [
                'required',
                'string',
                'email',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value === $user->email) {
                        $fail('No puedes usar tu correo institucional como correo para la Bolsa de trabajo.');
                    }
                },
            ],
            'programa_id' => 'required|exists:programas,id',
            'edad' => 'required|integer',
            'idioma' => 'required|string',
            'numero' => 'nullable|string',
            'cv' => 'required|file|mimes:pdf|max:6096',
            'otros_estudios' => 'nullable|string',
            'img' => 'nullable|image|mimes:jpeg,png,jpg|max:2048',
            'facebook' => 'nullable|string',
            'linkedin' => 'nullable|string',
            'descripcion' => 'required|string|max:400',
        ]);

        // Procesar la subida del CV
        if ($request->hasFile('cv')) {
            $cv = $request->file('cv');
            $rutaCv = public_path("postulantes/cv/");
            $nombreCv = $cv->getClientOriginalName();
            $cv->move($rutaCv, $nombreCv);
            $cvPath = "postulantes/cv/$nombreCv";
        }

        // Procesar la subida de la imagen
        if ($request->hasFile('img')) {
            $img = $request->file('img');
            $rutaImg = public_path("img/postulantes/");
            $nombreImg = $img->getClientOriginalName();
            $img->move($rutaImg, $nombreImg);
            $imgPath = "img/postulantes/$nombreImg";
        }


        $postulante = new Postulante([
            'nombre' => $validatedData['nombre'],
            'apellidos' => $validatedData['apellidos'],
            'dni' => $validatedData['dni'],
            'email' => $validatedData['email'],
            'programa_id' => $validatedData['programa_id'],
            'edad' => $validatedData['edad'],
            'idioma' => $validatedData['idioma'],
            'numero' => $validatedData['numero'],
            'otros_estudios' => $validatedData['otros_estudios'],
            'facebook' => $validatedData['facebook'],
            'linkedin' => $validatedData['linkedin'],
            'descripcion' => $validatedData['descripcion'],
            'cv' => $cvPath ?? null,
            'img' => $imgPath ?? null,
        ]);

        // Guardar el nuevo postulante en la base de datos
        $postulante->save();

        // Asociar el postulante al usuario actual
        auth()->user()->postulante()->save($postulante);

        // Redirigir a la vista de detalles del postulante creado
        return redirect()->route('postulante.index', $postulante->id)
            ->with('success', '¡Postulante registrado correctamente!');
    }
    public function show(Postulante $postulante)
    {
        return view('bolsa.postulante.show', compact('postulante'));
    }
    public function edit($id)
    {
        $user = auth()->user();
        $postulante = Postulante::findOrFail($id);
        $programas = Programa::all();
        return view('bolsa.postulante.edit', compact('postulante', 'programas', 'user'));
    }
    public function update(Request $request, $id)
    {
        $user = auth()->user();
        // Validar datos del formulario
        $validatedData = $request->validate([
            'nombre' => 'required|string',
            'apellidos' => 'required|string',
            'dni' => 'required|string',
            /* 'email' => 'required|string|email', */
            'email' => [
                'required',
                'string',
                'email',
                function ($attribute, $value, $fail) use ($user) {
                    if ($value === $user->email) {
                        $fail('No puedes usar tu correo institucional como correo para la Bolsa de trabajo.');
                    }
                },
            ],
            'programa_id' => 'required|exists:programas,id',
            'edad' => 'required|integer',
            'idioma' => 'required|string',
            'numero' => 'required|string',
            'cv' => 'nullable|file|mimes:pdf|max:6096',
            'otros_estudios' => 'nullable|string',
            'img' => 'nullable|image|mimes:jpeg,png,jpg|max:4048',
            'facebook' => 'nullable|string',
            'linkedin' => 'nullable|string',
            'descripcion' => 'required|string|max:400',
        ]);

        // Obtener el postulante existente por ID
        $postulante = Postulante::findOrFail($id);

        // Actualizar los datos del postulante con los datos validados del formulario
        $postulante->nombre = $validatedData['nombre'];
        $postulante->apellidos = $validatedData['apellidos'];
        $postulante->dni = $validatedData['dni'];
        $postulante->email = $validatedData['email'];
        $postulante->programa_id = $validatedData['programa_id'];
        $postulante->edad = $validatedData['edad'];
        $postulante->idioma = $validatedData['idioma'];
        $postulante->numero = $validatedData['numero'];
        $postulante->otros_estudios = $validatedData['otros_estudios'];
        $postulante->facebook = $validatedData['facebook'];
        $postulante->linkedin = $validatedData['linkedin'];
        $postulante->descripcion = $validatedData['descripcion'];

        // Procesar la subida del CV si se proporciona uno nuevo
        if ($request->hasFile('cv')) {
            if ($postulante->cv) {
                $rutaCvAnterior = public_path($postulante->cv);
                if (file_exists($rutaCvAnterior)) {
                    unlink($rutaCvAnterior);
                }
            }
            $cv = $request->file('cv');
            $rutaCv = public_path("postulantes/cv/");
            $nombreCv = $cv->getClientOriginalName();
            $cv->move($rutaCv, $nombreCv);
            $postulante->cv = "postulantes/cv/$nombreCv";
        }

        // Procesar la subida de la imagen si se proporciona una nueva
        if ($request->hasFile('img')) {
            if ($postulante->img) {
                $rutaImagenAnterior = public_path($postulante->img);
                if (file_exists($rutaImagenAnterior)) {
                    unlink($rutaImagenAnterior); // Eliminar la imagen anterior del sistema de archivos
                }
            }
            $img = $request->file('img');
            $rutaImg = public_path("img/postulantes/");
            $nombreImg = $img->getClientOriginalName();
            $img->move($rutaImg, $nombreImg);
            $postulante->img = "img/postulantes/$nombreImg";
        }


        // Guardar los cambios en el postulante
        $postulante->save();

        // Redirigir a la vista de detalles del postulante actualizado
        /* return redirect()->route('postulante.index')
            ->with('success', '¡Datos del postulante actualizados correctamente!'); */
        $user = auth()->user();

        // Verificar el rol del usuario y redirigir en consecuencia
        if ($user->hasRole('admin') || $user->hasRole('adminB')) {
            // Redirigir a la ruta bolsa.index si el usuario tiene rol admin o adminB
            return redirect()->route('listaPostulantes')
                ->with('success', '¡Datos del postulante actualizados correctamente!');
        } else {
            // Redirigir a la ruta postulante.index si el usuario no tiene rol admin o adminB
            return redirect()->route('postulante.index')
                ->with('success', '¡Datos del postulante actualizados correctamente!');
        }
    }
}
