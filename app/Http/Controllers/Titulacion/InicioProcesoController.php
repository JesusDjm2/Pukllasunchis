<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Ciclo;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Solicitud;
use App\Models\User;
use App\Services\Titulacion\Expediente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

/**
 * Inicio del proceso de titulación: se registran los datos del PTI (sin asesor todavía), se
 * genera el N° de expediente correlativo de cada integrante (FID y PPD con su propia serie) y se
 * arma el grupo con otros estudiantes o egresados que ya existen en el sistema (o se hace
 * individual). Todos quedan con el mismo grupo_id y el mismo PTI.
 */
class InicioProcesoController extends Controller
{
    public function crear(Request $request, User $persona)
    {
        if ($motivo = self::motivoNoPuede($persona)) {
            return redirect()->route('titulacion.index', ['ver' => $persona->id])->with('error', $motivo);
        }

        $titular = self::datosPersona($persona);
        $expedientes = ['fid' => Expediente::siguiente(false), 'ppd' => Expediente::siguiente(true)];
        $grupos = self::gruposPara(self::esPpd($persona), $persona->id);

        // Desde una solicitud de PTI: título, fecha e integrantes reconocidos ya puestos.
        $solicitud = self::solicitudPendiente($request->query('solicitud'));
        $prefill = $solicitud ? [
            'titulo' => trim((string) $solicitud->titulo, " \"“”"),
            'fecha_pti' => $solicitud->created_at->toDateString(),
            'integrantes' => array_values(array_diff($solicitud->idsIntegrantes(), [$persona->id])),
        ] : [];

        return view('titulacion.iniciar', compact('persona', 'titular', 'expedientes', 'grupos', 'solicitud', 'prefill'));
    }

    /** Solicitud de PTI que todavía no tiene proceso (null si no existe o ya se atendió). */
    private static function solicitudPendiente($id): ?Solicitud
    {
        return $id ? Solicitud::deTipo(Solicitud::PTI)->where('estado', '!=', 'atendida')->find((int) $id) : null;
    }

    /**
     * Buscador de integrantes (JSON): solo de la misma modalidad que el titular (FID con FID,
     * PPD con PPD), agrupados por promoción o ciclo. Con ?grupo= lista ese grupo completo sin
     * necesidad de escribir. No aparece quien no puede iniciar por su ciclo; quien ya tiene PTI
     * sí, deshabilitado, para que se entienda por qué no se puede agregar.
     */
    public function buscar(Request $request)
    {
        $titular = User::with(['ciclo.programa', 'programa', 'roles'])->find((int) $request->query('titular'));
        abort_unless($titular, 404);
        $ppd = self::esPpd($titular);

        $texto = trim((string) $request->query('q'));
        $grupo = trim((string) $request->query('grupo'));
        if (mb_strlen($texto) < 2 && $grupo === '') {
            return response()->json([]);
        }
        $excluir = array_map('intval', (array) $request->query('excluir', []));
        $excluir[] = $titular->id;

        $palabras = preg_split('/\s+/', $texto);
        $usuarios = User::query()
            ->where(fn ($q) => $q->where('condicion', 'Egresado')->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['alumno', 'alumnoB'])))
            ->whereNotIn('id', $excluir)
            ->when($grupo !== '', fn ($q) => $grupo === self::SIN_PROMOCION
                ? $q->whereNull('ciclo_id')
                : $q->whereIn('ciclo_id', self::ciclosDelGrupo($grupo, $ppd)))
            ->when(mb_strlen($texto) >= 2, fn ($q) => $q->where(function ($q) use ($palabras, $texto) {
                $q->where('dni', 'like', $texto.'%')->orWhere(function ($q) use ($palabras) {
                    foreach ($palabras as $p) {
                        $q->where(fn ($w) => $w->where('name', 'like', "%{$p}%")->orWhere('apellidos', 'like', "%{$p}%"));
                    }
                });
            }))
            ->with(['ciclo.programa', 'programa', 'roles', 'alumno', 'alumnoB', 'seguimientoTitulacion.pti'])
            ->orderBy('apellidos')
            ->limit($grupo !== '' ? 300 : 80)
            ->get();

        $lista = $usuarios
            ->filter(fn (User $u) => self::esPpd($u) === $ppd)
            ->map(fn (User $u) => self::datosPersona($u) + ['motivo' => self::motivoNoPuede($u), 'grupo' => self::grupoDe($u)])
            // Fuera quien no puede iniciar por su ciclo (o no es estudiante ni egresado). Quien ya tiene
            // PTI solo aparece si se le buscó por nombre o DNI; al recorrer un grupo, no estorba.
            ->filter(fn ($d) => $d['motivo'] === null || ($d['motivo'] === self::MOTIVO_YA_TIENE_PTI && mb_strlen($texto) >= 2))
            ->sortBy([
                fn ($a, $b) => self::ordenGrupo($a['grupo']) <=> self::ordenGrupo($b['grupo']),
                fn ($a, $b) => ($a['motivo'] ? 1 : 0) <=> ($b['motivo'] ? 1 : 0),
                fn ($a, $b) => strcmp($a['nombre'], $b['nombre']),
            ])
            ->values();

        return response()->json($grupo !== '' ? $lista : $lista->take(30));
    }

    private const MOTIVO_YA_TIENE_PTI = 'ya tiene un PTI registrado';

    private const SIN_PROMOCION = 'Egresados (sin promoción)';

    /** Promoción o ciclo con que se agrupa a una persona: "Egresados 2025-I", "Ciclo IX"… */
    public static function grupoDe(User $u): string
    {
        $nombre = trim((string) $u->ciclo?->nombre);
        if ($nombre === '') {
            return self::SIN_PROMOCION;
        }

        return preg_match('/^(Ciclo|Egresados)\b/i', $nombre) ? $nombre : 'Ciclo '.$nombre;
    }

    /** Ciclos en curso primero (I → X), luego egresados del más reciente al más antiguo. */
    private static function ordenGrupo(string $grupo): string
    {
        if (str_starts_with($grupo, 'Egresados')) {
            // Invierte año y semestre para que el más reciente quede primero.
            return preg_match('/(\d{4})(?:-(I{1,2}))?/', $grupo, $m)
                ? '1-'.(9999 - (int) $m[1]).'-'.(9 - strlen($m[2] ?? ''))
                : '2';
        }
        $romano = trim(preg_replace('/^Ciclo\s+/i', '', $grupo));
        $orden = ['I' => 1, 'II' => 2, 'III' => 3, 'IV' => 4, 'V' => 5, 'VI' => 6, 'VII' => 7, 'VIII' => 8, 'IX' => 9, 'X' => 10][strtok($romano, ' -')] ?? 99;

        return '0-'.str_pad((string) $orden, 2, '0', STR_PAD_LEFT).'-'.$romano;
    }

    /** Ids de los ciclos de la modalidad que forman un grupo (el mismo nombre existe en varios programas). */
    private static function ciclosDelGrupo(string $grupo, bool $ppd): array
    {
        return Ciclo::with('programa')->get()
            ->filter(fn (Ciclo $c) => (bool) $c->programa?->esPpd() === $ppd && self::grupoDe((new User)->setRelation('ciclo', $c)) === $grupo)
            ->pluck('id')->all();
    }

    /**
     * Grupos que se ofrecen en el selector del formulario, de la modalidad del titular: ciclos
     * desde los que se puede iniciar y promociones de egresados, solo los que tienen a alguien sin PTI.
     */
    public static function gruposPara(bool $ppd, ?int $sinUsuario = null): array
    {
        $ciclos = Ciclo::with('programa')->get()
            ->filter(fn (Ciclo $c) => (bool) $c->programa?->esPpd() === $ppd);

        $porGrupo = $ciclos->groupBy(fn (Ciclo $c) => self::grupoDe((new User)->setRelation('ciclo', $c)));
        $grupos = [];
        foreach ($porGrupo as $nombre => $cs) {
            $total = User::whereIn('ciclo_id', $cs->pluck('id'))
                ->when($sinUsuario, fn ($q) => $q->where('id', '!=', $sinUsuario))
                ->where(fn ($q) => $q->where('condicion', 'Egresado')->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['alumno', 'alumnoB'])))
                ->whereDoesntHave('seguimientoTitulacion.pti')
                ->with(['ciclo.programa', 'programa', 'roles'])
                ->get()
                ->filter(fn (User $u) => self::esPpd($u) === $ppd && self::motivoNoPuede($u) === null)
                ->count();
            if ($total > 0) {
                $grupos[$nombre] = $total;
            }
        }
        uksort($grupos, fn ($a, $b) => self::ordenGrupo($a) <=> self::ordenGrupo($b));

        return $grupos;
    }

    public function guardar(Request $request, User $persona)
    {
        $data = $request->validate([
            'titulo' => 'required|string|max:1000',
            'fecha_pti' => 'required|date',
            'fecha_vencimiento' => 'nullable|date|after_or_equal:fecha_pti',
            'integrantes' => 'nullable|array|max:10',
            'integrantes.*' => 'integer|distinct|exists:users,id',
            'solicitud_id' => 'nullable|integer',
        ], [
            'titulo.required' => 'Escriba el título del PTI.',
            'fecha_pti.required' => 'Indique la fecha de inscripción del PTI.',
            'fecha_vencimiento.after_or_equal' => 'El vencimiento no puede ser anterior a la fecha del PTI.',
        ]);

        $miembros = collect([$persona->id])->merge($data['integrantes'] ?? [])->unique()->values()
            ->map(fn ($id) => User::with(['ciclo.programa', 'programa', 'roles', 'seguimientoTitulacion.pti'])->find($id));
        $ppd = self::esPpd($persona);
        foreach ($miembros as $m) {
            if ($motivo = self::motivoNoPuede($m)) {
                return back()->withInput()->with('error', trim($m->apellidos.', '.$m->name).': '.$motivo);
            }
            if (self::esPpd($m) !== $ppd) {
                return back()->withInput()->with('error', trim($m->apellidos.', '.$m->name).': es de '.(self::esPpd($m) ? 'PPD' : 'FID')
                    .' y el grupo es de '.($ppd ? 'PPD' : 'FID').'. Los integrantes deben ser de la misma modalidad.');
            }
        }
        // El PTI vence a los 2 años de su inscripción, salvo que se indique otra fecha.
        $data['fecha_vencimiento'] = $data['fecha_vencimiento'] ?? \Carbon\Carbon::parse($data['fecha_pti'])->addYears(2)->toDateString();

        $solicitud = self::solicitudPendiente($data['solicitud_id'] ?? null);
        $asignados = DB::transaction(function () use ($miembros, $data, $persona, $solicitud, $request) {
            $asignados = [];
            $grupoId = null;
            foreach ($miembros as $i => $m) {
                $seguimiento = SeguimientoTitulacion::firstOrCreate(
                    ['user_id' => $m->id],
                    ['estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti')]
                );
                // Correlativo al último de su serie; quien ya tenía expediente (p. ej. importado) lo conserva.
                if (blank($seguimiento->numero_expediente)) {
                    $seguimiento->numero_expediente = Expediente::siguiente(self::esPpd($m));
                }
                if ($miembros->count() > 1) {
                    $grupoId ??= $seguimiento->id;
                    $seguimiento->grupo_id = $grupoId;
                }
                $seguimiento->save();

                Pti::create([
                    'seguimiento_id' => $seguimiento->id,
                    'numero' => (int) $seguimiento->ptis()->withTrashed()->max('numero') + 1,
                    'etapa' => 'bachiller',
                    'titulo' => $data['titulo'],
                    'fecha_pti' => $data['fecha_pti'],
                    'fecha_vencimiento' => $data['fecha_vencimiento'] ?? null,
                    'estado' => 'pendiente',
                ]);
                $asignados[] = trim($m->apellidos.', '.$m->name).' (N° '.$seguimiento->numero_expediente.')';
            }

            // La solicitud de PTI de la que se partió queda atendida y enlazada al proceso del titular.
            if ($solicitud) {
                $solicitud->update([
                    'estado' => 'atendida',
                    'seguimiento_id' => SeguimientoTitulacion::where('user_id', $persona->id)->value('id'),
                    'atendido_por' => $request->user()->id,
                    'atendido_en' => now(),
                ]);
            }

            return $asignados;
        });

        // A Secretaría le toca ahora la RD de aprobación del PTI: aviso en su campanita (un solo aviso por grupo).
        try {
            \Illuminate\Support\Facades\Notification::send(
                \App\Services\Titulacion\AreasTitulacion::destinatarios(),
                new \App\Notifications\TitulacionPtiParaRd($persona, $data['titulo'], $asignados)
            );
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('No se pudo avisar a Secretaría del PTI por emitir', ['persona' => $persona->id, 'error' => $e->getMessage()]);
        }

        $texto = count($asignados) > 1
            ? 'Proceso iniciado para el grupo: '.implode('; ', $asignados).'.'
            : 'Proceso iniciado: '.$asignados[0].'.';

        return redirect()->route('titulacion.index', ['ver' => $persona->id, 'tab' => 'proceso'])->with('success', $texto);
    }

    /** null si puede iniciar; si no, el motivo. */
    public static function motivoNoPuede(?User $u): ?string
    {
        if (! $u) {
            return 'no existe';
        }
        if ($u->seguimientoTitulacion?->pti) {
            return self::MOTIVO_YA_TIENE_PTI;
        }
        if ($u->condicion === 'Egresado') {
            return null;
        }
        if (! $u->hasAnyRole(['alumno', 'alumnoB'])) {
            return 'no es estudiante ni egresado';
        }

        return TitulacionController::puedeIniciar($u) ? null : 'aún no puede iniciar (FID desde el Ciclo IX)';
    }

    /** PPD por el programa de su ciclo; si no, por su programa o su rol. */
    public static function esPpd(User $u): bool
    {
        return $u->ciclo?->programa?->esPpd() ?? $u->programa?->esPpd() ?? $u->hasRole('alumnoB');
    }

    /** Lo que muestra el formulario de cada integrante (incluye su celular de la ficha FID o PPD). */
    public static function datosPersona(User $u): array
    {
        $u->loadMissing(['ciclo.programa', 'programa', 'alumno', 'alumnoB', 'seguimientoTitulacion']);
        $ficha = $u->alumno ?? $u->alumnoB;
        $ppd = self::esPpd($u);

        return [
            'id' => $u->id,
            'nombre' => trim($u->apellidos.', '.$u->name, ', '),
            'dni' => $u->dni,
            'modalidad' => $ppd ? 'PPD' : 'FID',
            'programa' => $u->ciclo?->programa?->nombre ?? $u->programa?->nombre,
            'situacion' => $u->condicion === 'Egresado' ? 'Egresado' : ($u->ciclo ? 'Ciclo '.$u->ciclo->nombre : 'Estudiante'),
            'celular' => $ficha?->numero ?: null,
            'celular_ref' => $ficha?->numero_referencia ?: null,
            'email' => $u->email,
            'expediente' => $u->seguimientoTitulacion?->numero_expediente,
        ];
    }
}
