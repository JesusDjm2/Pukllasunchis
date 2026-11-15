<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\Asesor;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\Participante;
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
        $seguimiento->load(['pti.asesor', 'pti.asesorExterno', 'tesis', 'resoluciones', 'actas.jurados.docente', 'actas.jurados.asesorExterno', 'participantes']);
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
            'genero_aparte' => 'nullable|in:f,m',
            'genero_resto' => 'nullable|in:f,m',
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
                'cargo' => "$base|in:Asesor,Asesora",
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

        $integrantes = User::with(['programa', 'seguimientoTitulacion'])->whereIn('id', $data['integrantes'])->orderBy('apellidos')->orderBy('name')->get();
        // El expediente tampoco se edita: es el de cada integrante, en el mismo orden que sus nombres en la RD.
        if (array_key_exists('expedientes', $campos)) {
            $data['expedientes'] = $integrantes->map(fn (User $u) => $u->seguimientoTitulacion?->numero_expediente)->filter()->unique()->implode(', ');
        }
        // Retiro / incorporación: quién sale o entra y quién forma el grupo.
        $cambioGrupo = in_array($plantilla, PlantillasRd::CAMBIAN_GRUPO, true) ? $this->cambioDeGrupo($def['tipo'], $egresado, $integrantes) : null;
        if (is_string($cambioGrupo)) {
            return back()->withInput()->withErrors(['integrantes' => $cambioGrupo]);
        }
        if ($cambioGrupo) {
            $data['afectados'] = PlantillasRd::listaApellidos($cambioGrupo['aparte']->map(fn ($u) => $this->apellidosNombre($u)));
        }

        $fecha = Carbon::parse($data['fecha']);
        $numero = str_pad($data['numero_rd'], 3, '0', STR_PAD_LEFT);
        $numeroRd = $numero.'-'.$fecha->year;

        $pdf = Pdf::loadView($def['vista'], $this->datosPdf($def, $data, $integrantes, $fecha, $numero, $cambioGrupo))->setPaper('a4');
        $nombreDescarga = 'RD '.$numeroRd.' '.$def['titulo'].'.pdf';

        if ($data['accion'] === 'previa') {
            return $pdf->stream($nombreDescarga);
        }

        $url = ArchivosRd::guardarContenido($pdf->output());
        DB::transaction(function () use ($integrantes, $def, $numeroRd, $fecha, $url, $data, $cambioGrupo, $egresado) {
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
            if ($cambioGrupo) {
                $this->aplicarCambioDeGrupo($def['tipo'], $cambioGrupo, $egresado);
            }
        });

        return redirect()->route('titulacion.index', ['ver' => $egresado->id, 'tab' => 'proceso'])
            ->with('success', 'RD N° '.$numeroRd.' ('.$def['titulo'].') emitida y registrada para '.$integrantes->count().' integrante(s).')
            ->with('rd_pdf', $url);
    }

    /**
     * Sube la versión firmada de una RD (PDF) o registra su enlace (Drive u otro): cambia el archivo
     * de esa RD en todo el grupo. La RD en sí (número y fecha) se conserva.
     */
    public function subir(Request $request, Resolucion $resolucion)
    {
        $request->validate([
            'pdf' => 'required_without:archivo|nullable|file|mimes:pdf|max:10240',
            'archivo' => 'nullable|url|max:500',
        ], [
            'pdf.required_without' => 'Seleccione el PDF de la resolución o pegue su enlace.',
            'pdf.mimes' => 'El archivo debe ser un PDF.',
            'pdf.max' => 'El PDF no debe pesar más de 10 MB.',
            'archivo.url' => 'El enlace debe ser completo, por ejemplo https://drive.google.com/…',
        ]);

        $url = $request->hasFile('pdf') ? ArchivosRd::guardarSubido($request->file('pdf')) : $request->input('archivo');
        $clave = GrupoTitulacion::claveRd($resolucion->numero_rd);
        $mismas = Resolucion::where('tipo', $resolucion->tipo)->whereNotNull('numero_rd')->get()
            ->filter(fn ($r) => $r->id === $resolucion->id || ($clave !== '' && GrupoTitulacion::claveRd($r->numero_rd) === $clave));
        foreach ($mismas as $r) {
            $r->update(['archivo' => $url]);
        }

        $userId = $resolucion->seguimiento->user_id;

        return redirect()->route('titulacion.index', ['ver' => $userId, 'tab' => $request->input('tab', 'proceso')])
            ->with('success', ($request->hasFile('pdf') ? 'PDF' : 'Enlace').' de la RD '.$resolucion->numero_rd.' guardado'.($mismas->count() > 1 ? ' para los '.$mismas->count().' integrantes del grupo.' : '.'));
    }

    /** Abre un PDF guardado: gestores y super-admin, o el propio estudiante si la RD es suya. */
    public function archivo(string $nombre)
    {
        $ruta = ArchivosRd::ruta($nombre);
        abort_unless((bool) $ruta, 404);

        $user = auth()->user();
        $autorizado = $user->hasAnyRole(['super-admin', 'admin', 'gestor-titulacion', 'secretaria'])
            || Resolucion::where('archivo', 'like', '%'.$nombre)
                ->whereHas('seguimiento', fn ($q) => $q->where('user_id', $user->id))->exists();
        abort_unless($autorizado, 403);

        return response()->file($ruta, ['Content-Type' => 'application/pdf', 'Content-Disposition' => 'inline; filename="RD.pdf"']);
    }

    // ── Auxiliares ──────────────────────────────────────────────────────

    /**
     * Quién sale o entra en una RD de retiro / incorporación ('aparte') y quién forma el resto del
     * grupo ('resto'), o el motivo por el que no se puede emitir.
     *  - Retiro: se genera desde la ficha de quien se retira; los demás marcados continúan.
     *  - Incorporación: se genera desde la ficha de un integrante del grupo que recibe; quien no
     *    pertenecía a ese grupo (se agregó con el buscador) es quien se incorpora.
     */
    private function cambioDeGrupo(string $tipo, User $titular, $integrantes): array|string
    {
        $seguimiento = $this->seguimiento($titular)->load(['actas', 'resoluciones']);

        if ($tipo === 'RetiroGrupo') {
            $resto = $integrantes->reject(fn (User $u) => $u->id === $titular->id)->values();
            if ($resto->isEmpty()) {
                return 'Marque a los integrantes que continúan: esta RD se genera desde la ficha de quien se retira.';
            }
            if ($seguimiento->actas->contains(fn ($a) => filled($a->numero_acta))) {
                return $titular->apellidos.', '.$titular->name.' ya tiene un acta registrada con su grupo: esta RD corresponde a la etapa del PTI.';
            }

            return ['aparte' => $integrantes->where('id', $titular->id)->values(), 'resto' => $resto];
        }

        $equipo = GrupoTitulacion::companeros($seguimiento)->pluck('id')->push($titular->id);
        $entran = $integrantes->whereNotIn('id', $equipo)->values();
        if ($entran->isEmpty()) {
            return 'Agregue con el buscador a quien se incorpora: esta RD se genera desde la ficha de un integrante del grupo que lo recibe.';
        }
        $conGrupo = $entran->filter(fn (User $u) => $u->seguimientoTitulacion
            && GrupoTitulacion::companeros($u->seguimientoTitulacion->load(['actas', 'resoluciones']))->isNotEmpty());
        if ($conGrupo->isNotEmpty()) {
            return 'Todavía pertenece a otro grupo: '.$conGrupo->map(fn (User $u) => $u->apellidos.', '.$u->name)->implode('; ').'. Primero emita su RD de retiro desde su ficha.';
        }

        return ['aparte' => $entran, 'resto' => $integrantes->whereIn('id', $equipo)->values()];
    }

    /** Lo que la RD cambia en el grupo. Ninguna RD se elimina: las de quien se retira dejan de agrupar. */
    private function aplicarCambioDeGrupo(string $tipo, array $cambio, User $titular): void
    {
        if ($tipo === 'RetiroGrupo') {
            $seguimiento = $this->seguimiento($titular);
            $seguimiento->resoluciones()->whereIn('tipo', GrupoTitulacion::TIPOS_RD_GRUPALES)->update(['agrupa' => false]);
            $seguimiento->update(['grupo_id' => null]);
            // Renunció a este PTI: queda en su historial; sigue con un PTI nuevo o al incorporarse a otro grupo.
            $seguimiento->pti()->first()?->update(['estado' => 'vencido']);

            return;
        }

        $origen = $this->seguimiento($titular);
        $ptiGrupo = $origen->pti()->first();
        $rdGrupo = $origen->resoluciones()->where('tipo', 'PTI')->whereNotNull('numero_rd')->get()
            ->sortByDesc(fn ($r) => GrupoTitulacion::claveRd($r->numero_rd) === GrupoTitulacion::claveRd($ptiGrupo?->rd_pti))->first();
        $grupoId = $origen->grupo_id ?: $origen->id;
        foreach ($cambio['resto'] as $u) {
            $this->seguimiento($u)->update(['grupo_id' => $grupoId]);
        }

        foreach ($cambio['aparte'] as $u) {
            $seguimiento = $this->seguimiento($u);
            $seguimiento->resoluciones()->whereIn('tipo', GrupoTitulacion::TIPOS_RD_GRUPALES)->update(['agrupa' => false]);
            $seguimiento->grupo_id = $grupoId;
            if (blank($seguimiento->numero_expediente)) {
                $seguimiento->numero_expediente = \App\Services\Titulacion\Expediente::siguiente((bool) $u->programa?->esPpd());
            }
            $seguimiento->save();

            $anterior = $seguimiento->pti()->first();
            if ($anterior && $anterior->estado !== 'vencido') {
                $anterior->update(['estado' => 'vencido']);
            }
            // Entra al PTI del grupo: mismo título, RD, asesor y cómputo de la vigencia.
            $seguimiento->ptis()->create([
                'numero' => (int) $seguimiento->ptis()->withTrashed()->max('numero') + 1,
                'estado' => 'pendiente',
            ] + ($ptiGrupo?->only(['etapa', 'titulo', 'fecha_pti', 'fecha_vencimiento', 'rd_pti', 'asesor_id', 'asesor_externo_id', 'rd_ampliacion', 'fecha_ampliacion', 'vencimiento_ampliado']) ?? []));
            if ($rdGrupo) {
                $seguimiento->resoluciones()->create([
                    'tipo' => 'PTI', 'numero_rd' => $rdGrupo->numero_rd, 'fecha' => $rdGrupo->fecha, 'archivo' => $rdGrupo->archivo,
                    'detalle' => 'RD del PTI del grupo al que se incorporó',
                ]);
            }
        }
    }

    /** "Illatupa Díaz, Lali" */
    private function apellidosNombre(User $u): string
    {
        return $this->nombrePropio(trim($u->apellidos.', '.$u->name, ' ,'));
    }

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

        // Asesor(a) vigente del trabajo: el 1.º del TI; si no hay, el del TI/Tesis o el del PTI (datos importados).
        $asesorActual = $seguimiento->participantes->where('etapa', 'ti')->where('funcion', 'asesor')->sortBy('id')->first()?->asesorNombre()
            ?? $tesis?->asesorNombre() ?? $pti?->asesorNombre();
        $rdPti = $this->soloNumeroRd($pti?->rd_pti ?? $rd('PTI'));
        $fechaRdPti = $seguimiento->resoluciones->where('tipo', 'PTI')
            ->first(fn ($r) => $rdPti !== '' && GrupoTitulacion::claveRd($r->numero_rd) === GrupoTitulacion::claveRd($rdPti))?->fecha
            ?? $pti?->fecha_pti;

        $jurado = $actaTesis?->jurados->map(fn ($j) => [
            'presidente' => 'Presidente(a)', 'jurado1' => 'Primer Dictaminante', 'jurado2' => 'Primer Replicante y secretario', 'jurado3' => 'Jurado',
        ][$j->rol] ?? ucfirst((string) $j->rol))->values();

        return [
            'expedientes' => $preseleccion->map(fn ($u) => $u->seguimientoTitulacion?->numero_expediente)->filter()->unique()->implode(', '),
            'numero_informe' => '',
            'titulo' => ($esTesis ? ($tesis?->titulo ?: $pti?->titulo) : $pti?->titulo) ?? '',
            'titulo_nuevo' => '',
            // En el cambio de asesor, el nuevo se elige; el vigente pasa a «anterior».
            'asesor' => $plantilla === 'cambio_asesor' ? null : $pti?->asesorValor(),
            'asesor_nombre' => $plantilla === 'cambio_asesor' ? '' : $this->nombrePropio($pti?->asesorNombre()),
            'plazo_ampliacion' => '30 días calendario',
            'nueva_fecha_limite' => $pti?->fecha_vencimiento ? Carbon::parse($pti->fecha_vencimiento)->addDays(30)->toDateString() : '',
            'asesor_cargo' => 'Asesor',
            'asesor_anterior' => $this->nombrePropio($asesorActual),
            'asesor_anterior_cargo' => 'Asesor',
            'fecha_rd_referida' => $fechaRdPti ? Carbon::parse($fechaRdPti)->toDateString() : '',
            'acompanante' => '',
            'asesor_designado' => $this->nombrePropio($asesorActual),
            'rd_reglamento' => '129-2026-DG/EESP-PUKLLASUNCHIS',
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
            'CambioAsesor' => ' · Nuevo(a) asesor(a): '.$data['asesor_nombre'].' (antes: '.$data['asesor_anterior'].')',
            'RetiroGrupo' => ' · Se retira: '.$data['afectados'].' · Modifica la RD '.$data['rd_referida'],
            'IncorporacionGrupo' => ' · Se incorpora: '.$data['afectados'].' · Modifica la RD '.$data['rd_referida'],
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

        if ($tipo === 'AmpliacionPti' && ($pti = $seguimiento->pti()->first())) {
            $pti->rd_ampliacion = $numeroRd;
            $pti->fecha_ampliacion = $fecha->toDateString();
            if (filled($data['nueva_fecha_limite'] ?? null)) {
                $pti->vencimiento_ampliado = $data['nueva_fecha_limite'];
            }
            $pti->save();

            return;
        }

        // El nuevo asesor reemplaza a los asesores del TI (uno por uno, para que quede en la auditoría) y
        // pasa a ser el asesor principal del trabajo. Si se escribió un nombre sin elegirlo de la lista,
        // solo queda la RD: el asesor se agrega a mano en el TI.
        if ($tipo === 'CambioAsesor' && filled($data['asesor'] ?? null)) {
            $seguimiento->participantes()->where('etapa', 'ti')->where('funcion', 'asesor')->get()->each->delete();
            $seguimiento->participantes()->create(Participante::columnasAsesor($data['asesor'], 'docente_id') + [
                'etapa' => 'ti', 'funcion' => 'asesor', 'cargo' => null,
            ]);
            Tesis::updateOrCreate(['seguimiento_id' => $seguimiento->id], Tesis::columnasAsesor($data['asesor']));

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

    private function datosPdf(array $def, array $data, $integrantes, Carbon $fecha, string $numero, ?array $cambioGrupo = null): array
    {
        $expedientes = trim((string) ($data['expedientes'] ?? ''));

        // Retiro / incorporación: cada grupo de personas concuerda según su propia ficha.
        $grupoPdf = [];
        if ($cambioGrupo) {
            $lista = fn ($personas) => PlantillasRd::listaApellidos($personas->map(fn ($u) => $this->apellidosNombre($u)));
            // Género: el que se corrigió en el formulario o, si no, el de la ficha de cada persona.
            $letra = fn ($personas, ?string $elegido) => $elegido ?: PlantillasRd::generoSugerido($personas)[0];
            $letraAparte = $letra($cambioGrupo['aparte'], $data['genero_aparte'] ?? null);
            $letraResto = $letra($cambioGrupo['resto'], $data['genero_resto'] ?? null);
            $parte = fn ($personas, string $g) => [
                'nombres' => $lista($personas),
                'mayus' => PlantillasRd::listaApellidos($personas->map(fn ($u) => mb_strtoupper($this->apellidosNombre($u)))),
                'g' => PlantillasRd::gramatica($g.($personas->count() > 1 ? 'p' : '1')),
                'varios' => $personas->count() > 1,
            ];
            $letraTodos = $letraAparte === 'f' && $letraResto === 'f' ? 'f' : 'm';
            $esPpd = (bool) $integrantes->first()?->programa?->esPpd();
            $grupoPdf = [
                'aparte' => $parte($cambioGrupo['aparte'], $letraAparte),
                'resto' => $parte($cambioGrupo['resto'], $letraResto),
                // Como en los modelos: quien se retira va primero; quien se incorpora, al final.
                'todos' => $parte($def['tipo'] === 'RetiroGrupo'
                    ? $cambioGrupo['aparte']->concat($cambioGrupo['resto'])
                    : $cambioGrupo['resto']->concat($cambioGrupo['aparte']), $letraTodos),
                'esPpd' => $esPpd,
                'programaCompleto' => ($esPpd ? 'Profesionalización Docente en ' : '').$data['programa'],
                'asesorDesignado' => trim((string) ($data['asesor_designado'] ?? '')),
                'reglamento' => trim((string) ($data['rd_reglamento'] ?? '')),
            ];
        }

        return $grupoPdf + [
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
            'plazoAmpliacion' => trim((string) ($data['plazo_ampliacion'] ?? '')),
            'nuevaFechaLimite' => filled($data['nueva_fecha_limite'] ?? null) ? Carbon::parse($data['nueva_fecha_limite']) : null,
            'asesorCargo' => $data['asesor_cargo'] ?? 'Asesor',
            'asesorNombre' => $data['asesor_nombre'] ?? '',
            'textoAdicional' => trim((string) ($data['texto_adicional'] ?? '')),
            'rdReferida' => trim((string) ($data['rd_referida'] ?? '')),
            'fechaRdReferida' => filled($data['fecha_rd_referida'] ?? null) ? Carbon::parse($data['fecha_rd_referida']) : null,
            'asesorAnterior' => trim((string) ($data['asesor_anterior'] ?? '')),
            'asesorAnteriorCargo' => $data['asesor_anterior_cargo'] ?? 'Asesor',
            'acompanante' => trim((string) ($data['acompanante'] ?? ''), " ;.\t"),
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
