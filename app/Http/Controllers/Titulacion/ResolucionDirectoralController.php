<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\Asesor;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Tesis;
use App\Models\User;
use App\Services\Titulacion\ArchivosRd;
use App\Services\Titulacion\GrupoTitulacion;
use App\Services\Titulacion\PlantillasRd;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Resoluciones Directorales generadas por el sistema (ver PlantillasRd): formulario con los
 * datos ya rellenados, vista previa, emisión (PDF + registro de la RD para cada integrante
 * del grupo) y carga de la versión firmada.
 */
class ResolucionDirectoralController extends Controller
{
    public function crear(User $egresado, string $plantilla)
    {
        abort_unless(PlantillasRd::existe($plantilla), 404);
        if (! PlantillasRd::disponible($plantilla)) {
            return back()->with('error', 'Todavía no está cargado el modelo oficial de «'.PlantillasRd::PLANTILLAS[$plantilla]['titulo'].'».');
        }

        $seguimiento = $this->seguimiento($egresado);
        $seguimiento->load(['pti.asesor', 'pti.asesorExterno', 'tesis', 'resoluciones', 'actas.jurados.docente', 'actas.jurados.asesorExterno']);
        if ($motivo = \App\Services\Titulacion\ReglasProceso::validarRd($seguimiento, PlantillasRd::PLANTILLAS[$plantilla]['tipo'])) {
            return redirect()->route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'proceso'])->with('error', $motivo);
        }
        $companeros = GrupoTitulacion::companeros($seguimiento);
        $preseleccion = collect([$egresado])->merge($companeros)
            ->each(fn (User $u) => $u->loadMissing(['seguimientoTitulacion', 'alumno:id,user_id,genero', 'alumnoB:id,user_id,genero']));

        // Para sumar integrantes: estudiantes y egresados del mismo programa.
        $candidatos = User::with(['seguimientoTitulacion.resoluciones', 'seguimientoTitulacion.pti', 'seguimientoTitulacion.ptis', 'seguimientoTitulacion.tesis', 'seguimientoTitulacion.actas', 'alumno:id,user_id,genero', 'alumnoB:id,user_id,genero'])
            ->where('programa_id', $egresado->programa_id)
            ->whereHas('roles', fn ($q) => $q->whereIn('name', ['alumno', 'alumnoB', 'inhabilitado']))
            ->orderBy('apellidos')->orderBy('name')
            ->get(['id', 'name', 'apellidos', 'dni', 'programa_id', 'condicion', 'genero']);

        $def = PlantillasRd::PLANTILLAS[$plantilla];
        $campos = PlantillasRd::campos($plantilla);
        $anio = (int) now()->year;
        $asesores = Asesor::orderBy('apellidos')->orderBy('nombres')->get();
        $docentes = User::role('docente')->orderBy('apellidos')->orderBy('name')->get(['id', 'name', 'apellidos']);

        $valores = [
            'numero_rd' => PlantillasRd::numeroSugerido($anio),
            'fecha' => now()->toDateString(),
            'genero' => PlantillasRd::generoSugerido($preseleccion),
            'programa' => PlantillasRd::programaOficial($egresado->programa),
            'lema' => PlantillasRd::lema($anio),
        ] + $this->valoresIniciales($plantilla, $seguimiento, $preseleccion);

        return view('titulacion.rd.crear', compact(
            'egresado', 'seguimiento', 'plantilla', 'def', 'campos', 'preseleccion', 'candidatos', 'valores', 'asesores', 'docentes'
        ));
    }

    public function emitir(Request $request, User $egresado, string $plantilla)
    {
        abort_unless(PlantillasRd::existe($plantilla) && PlantillasRd::disponible($plantilla), 404);
        $def = PlantillasRd::PLANTILLAS[$plantilla];
        if ($motivo = \App\Services\Titulacion\ReglasProceso::validarRd($this->seguimiento($egresado), $def['tipo'])) {
            return redirect()->route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'proceso'])->with('error', $motivo);
        }
        $campos = PlantillasRd::campos($plantilla);

        $reglas = [
            'integrantes' => 'required|array|min:1',
            'integrantes.*' => 'integer|exists:users,id',
            'numero_rd' => ['required', 'regex:/^\d{1,4}$/'],
            'fecha' => 'required|date',
            'genero' => 'required|in:'.implode(',', array_keys(PlantillasRd::GENEROS)),
            'programa' => 'required|string|max:150',
            'lema' => 'required|string|max:200',
            'accion' => 'required|in:previa,emitir',
        ];
        $nombres = ['numero_rd' => 'N° de RD'];
        foreach ($campos as $campo => [$etiqueta, $tipo, $obligatorio]) {
            $base = $obligatorio ? 'required' : 'nullable';
            if ($tipo === 'asesor') {
                $reglas['asesor'] = 'nullable|string|max:20';
                $reglas['asesor_nombre'] = 'required|string|max:200';
                $reglas['asesor_cargo'] = 'required|in:Asesor,Asesora';
                $nombres['asesor_nombre'] = 'nombre del asesor';
                continue;
            }
            $reglas[$campo] = match ($tipo) {
                'numero' => [$base, 'regex:/^\d{1,4}$/'],
                'fecha' => "$base|date",
                'hora' => "$base|date_format:H:i",
                'area' => "$base|string|max:1500",
                default => "$base|string|max:500",
            };
            $nombres[$campo] = mb_strtolower($etiqueta);
        }

        $data = $request->validate($reglas, [
            'integrantes.required' => 'Marque al menos un integrante.',
            'numero_rd.regex' => 'El N° de RD es solo el número (ej.: 45).',
            'numero_informe.regex' => 'El N° de informe es solo el número (ej.: 12).',
        ], $nombres);

        // El titular (desde cuya ficha se genera) va siempre. En las RD de etapas posteriores al PTI,
        // cada integrante debe estar en su paso (ReglasProceso::faltaIntegrante).
        $data['integrantes'] = collect($data['integrantes'])->map(fn ($id) => (int) $id)->push($egresado->id)->unique()->values()->all();
        $noPueden = User::with(['seguimientoTitulacion.pti', 'seguimientoTitulacion.ptis', 'seguimientoTitulacion.tesis', 'seguimientoTitulacion.actas', 'seguimientoTitulacion.resoluciones'])
            ->whereIn('id', $data['integrantes'])->get()
            ->map(fn (User $u) => ($falta = \App\Services\Titulacion\ReglasProceso::faltaIntegrante($u->seguimientoTitulacion, $def['tipo']))
                ? $u->apellidos.', '.$u->name.' (falta: '.$falta.')' : null)
            ->filter();
        if ($noPueden->isNotEmpty()) {
            return back()->withInput()->withErrors(['integrantes' => 'No pueden figurar en esta RD todavía: '.$noPueden->implode('; ').'.']);
        }

        // El Programa de Estudios no se edita: es el del alumno (si tiene uno), aunque se altere el formulario.
        $data['programa'] = PlantillasRd::programaOficial($egresado->programa) ?: $data['programa'];

        $integrantes = User::with('programa')->whereIn('id', $data['integrantes'])->orderBy('apellidos')->orderBy('name')->get();
        $fecha = Carbon::parse($data['fecha']);
        $numero = str_pad($data['numero_rd'], 3, '0', STR_PAD_LEFT);
        $numeroRd = $numero.'-'.$fecha->year;

        $pdf = Pdf::loadView($def['vista'], $this->datosPdf($def, $data, $integrantes, $fecha, $numero))->setPaper('a4');
        $nombreDescarga = 'RD '.$numeroRd.' '.$def['titulo'].'.pdf';

        if ($data['accion'] === 'previa') {
            return $pdf->stream($nombreDescarga);
        }

        $url = ArchivosRd::guardarContenido($pdf->output());
        DB::transaction(function () use ($integrantes, $def, $numeroRd, $fecha, $url, $data) {
            foreach ($integrantes as $u) {
                $seguimiento = $this->seguimiento($u);
                $seguimiento->resoluciones()->create([
                    'tipo' => $def['tipo'],
                    'numero_rd' => $numeroRd,
                    'fecha' => $fecha->toDateString(),
                    'archivo' => $url,
                    'detalle' => $this->detalle($def['tipo'], $data, $fecha),
                ]);
                $this->registrarEnEtapa($def['tipo'], $seguimiento, $numeroRd, $fecha, $data);
            }
        });

        return redirect()->route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'proceso'])
            ->with('success', 'RD N° '.$numeroRd.' ('.$def['titulo'].') emitida y registrada para '.$integrantes->count().' integrante(s).')
            ->with('rd_pdf', $url);
    }

    /** Sube la versión firmada de una RD: reemplaza el PDF de esa RD en todo el grupo. */
    public function subir(Request $request, Resolucion $resolucion)
    {
        $request->validate(['pdf' => 'required|file|mimes:pdf|max:10240'], [
            'pdf.required' => 'Seleccione el PDF de la resolución.',
            'pdf.mimes' => 'El archivo debe ser un PDF.',
            'pdf.max' => 'El PDF no debe pesar más de 10 MB.',
        ]);

        $url = ArchivosRd::guardarSubido($request->file('pdf'));
        $clave = GrupoTitulacion::claveRd($resolucion->numero_rd);
        $mismas = Resolucion::where('tipo', $resolucion->tipo)->whereNotNull('numero_rd')->get()
            ->filter(fn ($r) => $r->id === $resolucion->id || ($clave !== '' && GrupoTitulacion::claveRd($r->numero_rd) === $clave));
        foreach ($mismas as $r) {
            $r->update(['archivo' => $url]);
        }

        $userId = $resolucion->seguimiento->user_id;

        return redirect()->route('titulacion.index', ['ver' => $userId, 'tab' => $request->input('tab', 'proceso')])
            ->with('success', 'PDF de la RD '.$resolucion->numero_rd.' subido'.($mismas->count() > 1 ? ' para los '.$mismas->count().' integrantes del grupo.' : '.'));
    }

    /** Abre un PDF guardado: gestores y super-admin, o el propio estudiante si la RD es suya. */
    public function archivo(string $nombre)
    {
        $ruta = ArchivosRd::ruta($nombre);
        abort_unless((bool) $ruta, 404);

        $user = auth()->user();
        $autorizado = $user->hasAnyRole(['super-admin', 'admin', 'gestor-titulacion'])
            || Resolucion::where('archivo', 'like', '%'.$nombre)
                ->whereHas('seguimiento', fn ($q) => $q->where('user_id', $user->id))->exists();
        abort_unless($autorizado, 403);

        return response()->file($ruta, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="RD.pdf"']);
    }

    // ── Auxiliares ──────────────────────────────────────────────────────

    private function seguimiento(User $user): SeguimientoTitulacion
    {
        return SeguimientoTitulacion::firstOrCreate(
            ['user_id' => $user->id],
            ['estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti')]
        );
    }

    /** Datos propios de cada plantilla, tomados de lo ya registrado en el sistema. */
    private function valoresIniciales(string $plantilla, SeguimientoTitulacion $seguimiento, $preseleccion): array
    {
        $pti = $seguimiento->pti;
        $tesis = $seguimiento->tesis;
        $actaExpo = $seguimiento->actas->firstWhere('tipo', 'expo');
        $actaTesis = $seguimiento->actas->firstWhere('tipo', 'tesis');
        $rd = fn (string $tipo) => $seguimiento->resoluciones->where('tipo', $tipo)->filter(fn ($r) => filled($r->numero_rd))
            ->sortByDesc(fn ($r) => $r->fecha?->format('Y-m-d') ?? '0000')->first()?->numero_rd;
        $esTesis = in_array($plantilla, ['aprobacion_exposicion', 'fecha_sustentacion', 'aprobacion_sustentacion'], true);

        $jurado = $actaTesis?->jurados->map(fn ($j) => [
            'presidente' => 'Presidente(a)', 'jurado1' => 'Primer Dictaminante', 'jurado2' => 'Primer Replicante y secretario', 'jurado3' => 'Jurado',
        ][$j->rol] ?? ucfirst((string) $j->rol))->values();

        return [
            'expedientes' => $preseleccion->map(fn ($u) => $u->seguimientoTitulacion?->numero_expediente)->filter()->unique()->implode(', '),
            'numero_informe' => '',
            'titulo' => ($esTesis ? ($tesis?->titulo ?: $pti?->titulo) : $pti?->titulo) ?? '',
            'titulo_nuevo' => '',
            'asesor' => $pti?->asesorValor(),
            'asesor_nombre' => $this->nombrePropio($pti?->asesorNombre()),
            'asesor_cargo' => 'Asesor',
            'texto_adicional' => '',
            // Solo "NNN-AAAA": los registros históricos traen nombres de archivo ("RD- 106 PTI …pdf").
            'rd_referida' => $this->soloNumeroRd(match ($plantilla) {
                'aprobacion_sustentacion' => $rd('FechaSustentacion'),
                default => $pti?->rd_pti ?? $rd('PTI'),
            }),
            'acta' => ($plantilla === 'aprobacion_exposicion' ? $actaExpo : $actaTesis)?->numero_acta ?? '',
            'fecha_acto' => match ($plantilla) {
                'aprobacion_exposicion' => ($tesis?->fecha_exposicion ?? $actaExpo?->fecha)?->toDateString(),
                'aprobacion_sustentacion' => ($tesis?->fecha_sustentacion ?? $actaTesis?->fecha)?->toDateString(),
                default => null,
            } ?? '',
            'hora' => '09:00',
            'lugar' => 'en las instalaciones de la institución',
            'jurado' => $actaTesis && $actaTesis->jurados->isNotEmpty()
                ? $actaTesis->jurados->map(fn ($j, $i) => $jurado[$i].': '.$j->asesorNombre())->implode("\n")
                : "Presidente(a): \nPrimer Dictaminante: \nPrimer Replicante y secretario: ",
        ];
    }

    /** Detalle que queda anotado en la RD registrada (se ve en el historial). */
    private function detalle(string $tipo, array $data, Carbon $fecha): string
    {
        return 'Emitida desde el sistema'.match ($tipo) {
            'PTI' => ' · Informe N° '.str_pad($data['numero_informe'], 3, '0', STR_PAD_LEFT).'-'.$fecha->year.'-CI',
            'AnulacionPti' => ' · Deja sin efecto la RD '.$data['rd_referida'],
            'CambioTema' => ' · Nuevo título: '.\Illuminate\Support\Str::limit($data['titulo_nuevo'], 200),
            'FechaSustentacion' => ' · Sustentación: '.Carbon::parse($data['fecha_acto'])->format('d/m/Y').' '.$data['hora'],
            default => '',
        };
    }

    /**
     * Lo que cada RD aprueba también se anota en su etapa (si la etapa aún no tenía otra RD):
     * así el estado del proceso avanza solo. La fecha de sustentación NO se anota: es la fecha
     * programada, y anotarla marcaría la tesis como ya sustentada.
     */
    private function registrarEnEtapa(string $tipo, SeguimientoTitulacion $seguimiento, string $numeroRd, Carbon $fecha, array $data): void
    {
        $mismaRd = fn ($actual) => blank($actual) || GrupoTitulacion::claveRd($actual) === GrupoTitulacion::claveRd($numeroRd);

        if ($tipo === 'PTI') {
            $pti = $seguimiento->pti()->first() ?? new Pti(['seguimiento_id' => $seguimiento->id, 'numero' => 1, 'etapa' => 'bachiller']);
            if (! $mismaRd($pti->rd_pti)) {
                return;
            }
            $pti->fill(['rd_pti' => $numeroRd, 'titulo' => $data['titulo']]);
            if (filled($data['asesor'] ?? null)) {
                $pti->fill(Pti::columnasAsesor($data['asesor']));
            }
            $pti->fecha_pti ??= $fecha->toDateString();
            $pti->save();

            return;
        }

        if ($tipo === 'CambioTema' && ($pti = $seguimiento->pti()->first())) {
            $pti->update(['titulo' => $data['titulo_nuevo']]);

            return;
        }

        if (in_array($tipo, ['Exposicion', 'Tesis'], true)) {
            $tesis = $seguimiento->tesis()->first() ?? new Tesis(['seguimiento_id' => $seguimiento->id]);
            [$campoRd, $campoFecha] = $tipo === 'Exposicion' ? ['rd_ti', 'fecha_exposicion'] : ['rd_tesis', 'fecha_sustentacion'];
            if (! $mismaRd($tesis->{$campoRd})) {
                return;
            }
            $tesis->{$campoRd} = $numeroRd;
            $tesis->{$campoFecha} ??= filled($data['fecha_acto'] ?? null) ? Carbon::parse($data['fecha_acto'])->toDateString() : null;
            $tesis->titulo = filled($tesis->titulo) ? $tesis->titulo : ($data['titulo'] ?? null);
            $tesis->save();
        }
    }

    private function datosPdf(array $def, array $data, $integrantes, Carbon $fecha, string $numero): array
    {
        $expedientes = trim((string) ($data['expedientes'] ?? ''));

        return [
            'numero' => $numero,
            'anio' => $fecha->year,
            'fechaLarga' => PlantillasRd::fechaLarga($fecha),
            'g' => PlantillasRd::gramatica($data['genero']),
            'nombres' => PlantillasRd::listaNombres($integrantes->map(fn ($u) => $this->nombrePropio(trim($u->name.' '.$u->apellidos)))),
            'programa' => $data['programa'],
            'expedientes' => $expedientes,
            'variosExpedientes' => str_contains($expedientes, ','),
            'informe' => filled($data['numero_informe'] ?? null) ? str_pad($data['numero_informe'], 3, '0', STR_PAD_LEFT) : '',
            'titulo' => trim((string) ($data['titulo'] ?? ''), " \"“”"),
            'tituloNuevo' => trim((string) ($data['titulo_nuevo'] ?? ''), " \"“”"),
            'asesorCargo' => $data['asesor_cargo'] ?? 'Asesor',
            'asesorNombre' => $data['asesor_nombre'] ?? '',
            'textoAdicional' => trim((string) ($data['texto_adicional'] ?? '')),
            'rdReferida' => trim((string) ($data['rd_referida'] ?? '')),
            'acta' => trim((string) ($data['acta'] ?? '')),
            'fechaActo' => filled($data['fecha_acto'] ?? null) ? Carbon::parse($data['fecha_acto']) : null,
            'hora' => $data['hora'] ?? '',
            'lugar' => trim((string) ($data['lugar'] ?? '')),
            'jurado' => collect(preg_split('/\r\n|\n/', (string) ($data['jurado'] ?? '')))
                ->map(fn ($l) => array_map('trim', array_pad(explode(':', $l, 2), 2, '')))
                ->filter(fn ($l) => $l[0] !== '' || $l[1] !== '')->values()->all(),
            'lema' => $data['lema'],
            'iniciales' => $def['iniciales'],
            'cierre' => $def['cierre'],
        ];
    }

    /** "RD- 106-2021 PTI ….pdf" → "106-2021"; sin año reconocible, vacío (se escribe a mano). */
    private function soloNumeroRd(?string $rd): string
    {
        return preg_match('/(\d{1,4})\s*-\s*(\d{4})/', (string) $rd, $m) ? str_pad($m[1], 3, '0', STR_PAD_LEFT).'-'.$m[2] : '';
    }

    /** "AGUIRRE TINTAYA, NIEVES" → "Aguirre Tintaya, Nieves" (partículas en minúscula). */
    private function nombrePropio(?string $texto): string
    {
        $texto = mb_convert_case(mb_strtolower(trim((string) $texto)), MB_CASE_TITLE);

        return preg_replace_callback('/\b(De|Del|La|Las|Los|Y)\b/u', fn ($m) => mb_strtolower($m[1]), $texto);
    }
}
