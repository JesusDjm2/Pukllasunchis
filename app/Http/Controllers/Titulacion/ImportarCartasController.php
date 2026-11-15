<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Services\Titulacion\ImportarCartasFormulario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Importar las solicitudes de Carta de Presentación de los formularios de Google (super-admin): se sube el
 * Excel de respuestas, se ve la vista previa (a quién se reconoció y cómo) y recién al confirmar se guardan
 * como cartas aprobadas, sin enviar correos. Ver ImportarCartasFormulario.
 */
class ImportarCartasController extends Controller
{
    private const CARPETA = 'titulacion/importaciones';

    public function form()
    {
        return view('titulacion.carta.importar', ['filas' => null, 'token' => null]);
    }

    public function previa(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xlsx,xls|max:20480'], [
            'excel.required' => 'Elija el Excel de respuestas de los formularios.',
            'excel.mimes' => 'Debe ser un archivo de Excel (.xlsx).',
        ]);
        $token = (string) Str::uuid();
        $request->file('excel')->storeAs(self::CARPETA, 'cartas-'.$token.'.xlsx', 'local');

        return $this->mostrar($token);
    }

    public function vista(string $token)
    {
        return $this->mostrar($token);
    }

    public function aplicar(Request $request)
    {
        $data = $request->validate(['token' => 'required|uuid', 'confirmar' => 'accepted'], ['confirmar.accepted' => 'Marque la casilla de confirmación.']);
        $ruta = $this->ruta($data['token']);
        abort_unless($ruta, 404, 'El archivo subido ya no está: vuelva a subir el Excel.');

        [$respuestas, $filas] = ImportarCartasFormulario::importar(ImportarCartasFormulario::leer($ruta));
        Storage::disk('local')->delete(self::CARPETA.'/cartas-'.$data['token'].'.xlsx');

        return redirect()->route('titulacion.cartas.index', ['origen' => 'formulario'])
            ->with('success', "Importación lista: {$respuestas} solicitudes del formulario de Google ({$filas} registros, uno por integrante) quedaron como aprobadas. No se envió ningún correo.");
    }

    private function mostrar(string $token)
    {
        $ruta = $this->ruta($token);
        if (! $ruta) {
            return redirect()->route('titulacion.cartas.importar')->with('error', 'El archivo subido ya no está: vuelva a subirlo.');
        }

        return view('titulacion.carta.importar', ['filas' => ImportarCartasFormulario::leer($ruta), 'token' => $token]);
    }

    private function ruta(string $token): ?string
    {
        $archivo = self::CARPETA.'/cartas-'.$token.'.xlsx';

        return Str::isUuid($token) && Storage::disk('local')->exists($archivo) ? Storage::disk('local')->path($archivo) : null;
    }
}
