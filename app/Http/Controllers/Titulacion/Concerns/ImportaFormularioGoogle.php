<?php

namespace App\Http\Controllers\Titulacion\Concerns;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Importar las respuestas de un formulario de Google en la bandeja de un trámite (super-admin): subir el Excel
 * (o CSV) → vista previa (a quién se reconoció y cómo; nada se guarda) → importar, sin correos. El controlador
 * dice qué importador usa (subclase de ImportarFormularioGoogle) y cómo se ve (importacion()).
 */
trait ImportaFormularioGoogle
{
    /** Clase del importador (ImportarFormularioGoogle). */
    abstract protected function importador(): string;

    /**
     * Textos y rutas de la pantalla: kicker, titulo, sub, bandeja (ruta), ruta (prefijo de las rutas de importar),
     * estados (clave => texto de «Quedará»).
     */
    abstract protected function importacion(): array;

    private function carpetaImportacion(): string
    {
        return 'titulacion/importaciones';
    }

    public function importarForm()
    {
        return view('titulacion.solicitudes.importar', ['lectura' => null, 'token' => null, 'cfg' => $this->importacion()]);
    }

    public function importarPrevia(Request $request)
    {
        $request->validate(['excel' => 'required|file|mimes:xlsx,xls,csv,txt|max:20480'], [
            'excel.required' => 'Elija el Excel o CSV de respuestas del formulario.',
            'excel.mimes' => 'Debe ser un archivo de Excel (.xlsx) o CSV.',
        ]);
        $token = (string) Str::uuid();
        // Se conserva el tipo (xlsx o csv): el lector elige cómo leerlo por la extensión.
        $ext = strtolower($request->file('excel')->getClientOriginalExtension()) === 'csv' ? 'csv' : 'xlsx';
        $request->file('excel')->storeAs($this->carpetaImportacion(), $this->prefijoArchivo().$token.'.'.$ext, 'local');

        return redirect()->route($this->importacion()['ruta'].'.vista', $token);
    }

    public function importarVista(string $token)
    {
        $cfg = $this->importacion();
        $ruta = $this->rutaImportacion($token);
        if (! $ruta) {
            return redirect()->route($cfg['ruta'])->with('error', 'El archivo subido ya no está: vuelva a subirlo.');
        }

        return view('titulacion.solicitudes.importar', ['lectura' => ($this->importador())::leer($ruta), 'token' => $token, 'cfg' => $cfg]);
    }

    public function importarAplicar(Request $request)
    {
        $data = $request->validate(['token' => 'required|uuid', 'confirmar' => 'accepted'], ['confirmar.accepted' => 'Marque la casilla de confirmación.']);
        $ruta = $this->rutaImportacion($data['token']);
        abort_unless((bool) $ruta, 404, 'El archivo subido ya no está: vuelva a subir el Excel.');

        $importador = $this->importador();
        $c = $importador::importar($importador::leer($ruta)['filas']);
        Storage::disk('local')->delete($this->carpetaImportacion().'/'.basename($ruta));
        $cfg = $this->importacion();

        return redirect()->route($cfg['bandeja'], ['origen' => 'formulario'])
            ->with('success', "Importación lista: {$c['importadas']} solicitudes del formulario de Google ("
                .($c['atendida'] ?? 0).' atendidas fuera del sistema; '.($c['recibida'] ?? 0).' quedaron por atender). No se envió ningún correo.');
    }

    private function prefijoArchivo(): string
    {
        return Str::slug(($this->importador())::TIPO).'-';
    }

    private function rutaImportacion(string $token): ?string
    {
        if (! Str::isUuid($token)) {
            return null;
        }
        foreach (['xlsx', 'csv'] as $ext) {
            $archivo = $this->carpetaImportacion().'/'.$this->prefijoArchivo().$token.'.'.$ext;
            if (Storage::disk('local')->exists($archivo)) {
                return Storage::disk('local')->path($archivo);
            }
        }

        return null;
    }
}
