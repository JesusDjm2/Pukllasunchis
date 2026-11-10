<?php

namespace App\Http\Controllers\Titulacion;

use App\Enums\Modalidad;
use App\Enums\NivelPrograma;
use App\Http\Controllers\Controller;
use App\Mail\Titulacion\AvisoVencimientoPtiMail;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use App\Models\ppd;
use App\Models\Titulacion\Acta;
use App\Models\Titulacion\Asesor;
use App\Models\Titulacion\AvisoPti;
use App\Models\Titulacion\Documento;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\Importacion;
use App\Models\Titulacion\Jurado;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Tesis;
use App\Models\User;
use App\Services\Titulacion\AvanceTitulacion;
use App\Services\Titulacion\Expediente;
use App\Services\Titulacion\GrupoTitulacion;
use App\Services\Titulacion\NombresImportados;
use App\Services\Titulacion\ResolverDocente;
use App\Services\Titulacion\VigenciaPti;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Mail;

class TitulacionController extends Controller
{
    /**
     * Ciclo (número romano → entero, ver Ciclo::ordenCiclo()) desde el cual un alumno ACTIVO
     * ya puede arrancar su proceso de titulación, según la política de la institución:
     *  - FID: desde el Ciclo IX.
     *  - PPD: todo alumno vigente, desde el Ciclo I — el programa dura un año y los
     *    Ciclos I y II se cursan en paralelo (no es secuencial como FID).
     */
    private const CICLO_MINIMO_TITULACION = 9;

    private const CICLO_MINIMO_TITULACION_PPD = 1;

    /**
     * Layout de las vistas de titulación según quién las abre: cada gestor
     * conserva el menú de su panel de siempre (Carlos es docente, Marilyn
     * admin). Un gestor puro, sin otro rol, cae en el layout de admin.
     */
    public static function layout(): string
    {
        $user = auth()->user();

        if ($user?->hasRole('super-admin')) {
            return 'layouts.superadmin';
        }
        if ($user?->hasRole('docente') && ! $user->hasRole('admin') && $user->docente) {
            return 'layouts.docente';
        }

        return 'layouts.admin';
    }

    /**
     * Listado ÚNICO de exalumnos: mezcla a los ya registrados
     * (users.condicion='Egresado', con ficha completa) con los pendientes
     * que salieron de las importaciones de CSV (todavía sin cuenta, o con
     * cuenta pero sin ficha de egresado — ej. alguien que hoy es docente).
     * A propósito no son dos pantallas separadas: para la gestión de
     * Carlos/Marilyn/super-admin debe existir una sola vista de "estos son
     * todos los exalumnos que tengo que gestionar", con la acción que
     * corresponda a cada uno (Ver/Gestionar, Crear cuenta, Completar ficha).
     */
    /** Resumen "Por atender" de la lista: clave => [etiqueta, ícono, ayuda]. */
    public const TAREAS = [
        'sin_asesor' => ['TI sin asesor(a)', 'fa-user-slash', 'Con el PTI aprobado y todavía ningún asesor(a) asignado(a) (se asigna en el TI).'],
        'rd_sin_pdf' => ['RD sin PDF', 'fa-file-pdf', 'Resoluciones registradas que todavía no tienen su PDF.'],
        'sin_expediente' => ['Sin N° de expediente', 'fa-folder-open', 'Personas en proceso sin número de expediente.'],
        'rd_pti' => ['RD del PTI por completar', 'fa-file-signature', 'Con PTI pero su RD de aprobación falta o no tiene número y año (viene del Excel). Se completa en el paso del PTI.'],
        'proximas' => ['Exposiciones y sustentaciones (30 días)', 'fa-calendar-alt', 'Con fecha de exposición o sustentación en los próximos 30 días.'],
    ];

    /** Filtro de vigencia del PTI: clave => [etiqueta, grupos que incluye]. */
    public const FILTROS_VIGENCIA = [
        'por_vencer' => ['Por vencer (≤ 4 meses)', ['por_vencer']],
        'proximo_anio' => ['Vence en ≤ 12 meses', ['por_vencer', 'proximo_anio']],
        'vencido' => ['Vencido', ['vencido']],
        'sin_fecha' => ['Sin fecha', ['sin_fecha']],
    ];

    /** Filtro por grado registrado en SUNEDU (solo personas con proceso): clave => etiqueta. */
    public const FILTROS_GRADO = [
        'licenciado' => 'Licenciado(a)',
        'bachiller' => 'Solo Bachiller',
        'sin_grado' => 'Sin grado registrado',
    ];

    /**
     * Retiro de la institución (deserción) según las hojas de expedientes: lo anotan en la columna
     * del expediente ("Retirada"), del correo o del PTI ("2025-II sale"). Misma regla en la
     * sincronización (no crea su cuenta) y en el panel (filtro «Retirados»).
     */
    public static function marcadoRetirado(array $d): bool
    {
        $texto = mb_strtolower(implode(' ', [$d['num_expediente'] ?? '', $d['correo'] ?? '', $d['fecha_pti'] ?? '', $d['rd_pti'] ?? '']));

        return (bool) preg_match('/retirad[oa]|reirada|\bsale\b/u', $texto);
    }

    /** Grado ya registrado en SUNEDU: 'licenciado', 'bachiller' o null. */
    private static function gradoDe(?Tesis $tesis): ?string
    {
        return match (true) {
            (bool) ($tesis?->fecha_titulo_sunedu || filled($tesis?->titulo_sunedu)) => 'licenciado',
            (bool) ($tesis?->fecha_bachiller_sunedu || filled($tesis?->bachiller_sunedu)) => 'bachiller',
            default => null,
        };
    }

    /**
     * Texto de la columna Promoción/ciclo: "IX" → "Ciclo IX"; "Egresados 2020- I al 2026- II"
     * → "Egresados 2020-I al 2026-II" (los archivos importados traen espacios sueltos).
     */
    /**
     * Promoción de una fila a partir de su ciclo. FID nombra por semestre ("Egresados 2024-I") y PPD
     * por año ("Egresados 2024"); las planillas traen el periodo ("2017-I al 2022-II": egresó en 2022-II).
     * Devuelve ['anio' => 2024, 'semestre' => '2024-I'|null] o, si aún estudia, ['ciclo' => 'IX'].
     */
    public static function promocionDe(?string $anio): array
    {
        $anio = trim(preg_replace('/\s+/', ' ', (string) $anio));
        if (preg_match('/^(?:Ciclo )?([IVX]+)$/i', $anio, $m)) {
            return ['ciclo' => strtoupper($m[1])];
        }
        if (preg_match_all('/(\d{4})(?:\s*-\s*(I{1,2})\b)?/', $anio, $m, PREG_SET_ORDER)) {
            $ultimo = end($m);

            return ['anio' => (int) $ultimo[1], 'semestre' => ($ultimo[2] ?? '') !== '' ? $ultimo[1].'-'.$ultimo[2] : null];
        }

        return [];
    }

    /** Filtro Promoción: 'e2024' = egresó en 2024 (cualquier semestre), 'e2024-I' = ese semestre, 'cIX' = cursa el ciclo IX. */
    private static function enPromocion(object $item, string $clave): bool
    {
        $p = self::promocionDe($item->anio ?? null);

        return match (true) {
            str_starts_with($clave, 'c') => ($p['ciclo'] ?? null) === substr($clave, 1),
            str_contains($clave, '-') => ($p['semestre'] ?? null) === substr($clave, 1),
            default => isset($p['anio']) && (string) $p['anio'] === substr($clave, 1),
        };
    }

    /**
     * Opciones del filtro Promoción con su cantidad: años de egreso (del más reciente) y, si el año se
     * divide en semestres, cada semestre debajo; luego los ciclos de quienes aún estudian (I → X).
     *
     * @return array{egresados: array<string, array{0: string, 1: int, 2: bool}>, ciclos: array<string, array{0: string, 1: int}>}
     */
    private static function opcionesPromocion(\Illuminate\Support\Collection $items): array
    {
        $promos = $items->map(fn ($i) => self::promocionDe($i->anio ?? null))->filter();
        $opciones = ['egresados' => [], 'ciclos' => []];

        foreach ($promos->whereNotNull('anio')->groupBy('anio')->sortKeysDesc() as $anio => $grupo) {
            $opciones['egresados']['e'.$anio] = ['Egresados '.$anio, $grupo->count(), false];
            $semestres = $grupo->pluck('semestre')->filter()->countBy()->sortKeys();
            // Solo si aporta: más de un semestre, o semestres mezclados con quienes solo tienen el año.
            if ($semestres->count() > 1 || ($semestres->isNotEmpty() && $semestres->sum() < $grupo->count())) {
                foreach ($semestres as $semestre => $n) {
                    $opciones['egresados']['e'.$semestre] = [$semestre, $n, true];
                }
            }
        }

        $orden = array_flip(['I', 'II', 'III', 'IV', 'V', 'VI', 'VII', 'VIII', 'IX', 'X']);
        foreach ($promos->whereNotNull('ciclo')->countBy('ciclo')->sortBy(fn ($n, $c) => $orden[$c] ?? 99) as $ciclo => $n) {
            $opciones['ciclos']['c'.$ciclo] = ['Ciclo '.$ciclo, $n];
        }

        return $opciones;
    }

    public static function etiquetaAnio(?string $anio): ?string
    {
        if (blank($anio)) {
            return null;
        }
        $anio = trim(preg_replace('/\s+/', ' ', $anio));
        if (preg_match('/^[IVX]+$/', $anio)) {
            return 'Ciclo '.$anio;
        }

        return preg_replace('/(\d{4})\s*-\s*(I{1,2})\b/', '$1-$2', $anio);
    }

    /** Claves de TAREAS que aplican a una persona (los licenciados ya no tienen pendientes). */
    private static function tareasDe(SeguimientoTitulacion $seguimiento): array
    {
        if ($seguimiento->estadoTitulacion?->clave === 'licenciado') {
            return [];
        }
        $pti = $seguimiento->pti;
        $tesis = $seguimiento->tesis;
        $enRango = fn ($fecha) => $fecha && $fecha->between(now()->startOfDay(), now()->addDays(30)->endOfDay());

        return array_keys(array_filter([
            // El asesor se asigna en el TI: solo falta si el PTI ya está aprobado y no hay asesor ni en el PTI ni en el TI.
            'sin_asesor' => $pti && in_array($seguimiento->estadoTitulacion?->clave, ['pti_aprobado', 'ti_aprobado'], true)
                && ! $pti->asesor_id && ! $pti->asesor_externo_id && ! $tesis?->asesor_id && ! $tesis?->asesor_externo_id,
            'rd_sin_pdf' => $seguimiento->resoluciones->contains(fn ($r) => filled($r->numero_rd) && blank($r->archivo)),
            'sin_expediente' => blank($seguimiento->numero_expediente),
            'rd_pti' => (bool) $pti && \App\Services\Titulacion\RdPti::de($seguimiento)['estado'] !== 'completa',
            'proximas' => $enRango($tesis?->fecha_exposicion) || $enRango($tesis?->fecha_sustentacion)
                || $seguimiento->actas->contains(fn ($a) => $enRango($a->fecha)),
        ]));
    }

    /**
     * En qué grupo de vigencia cae un PTI. Los licenciados ya no cuentan
     * (terminaron el proceso), igual que quien aún no tiene PTI.
     */
    private static function grupoVigencia(VigenciaPti $vigencia, ?string $estadoClave): ?string
    {
        if (in_array($estadoClave, ['sin_pti', 'licenciado'], true)) {
            return null;
        }
        if (! $vigencia->conocida()) {
            return 'sin_fecha';
        }
        if ($vigencia->vencido()) {
            return 'vencido';
        }

        return match (true) {
            $vigencia->mesesRestantes() <= VigenciaPti::MESES_AVISO_URGENTE => 'por_vencer',
            $vigencia->mesesRestantes() < 12 => 'proximo_anio',
            default => 'vigente',
        };
    }

    public function index(Request $request)
    {
        // Mapa numero_acta -> personas, para poder mostrar "con quién se
        // presentó en grupo" sin necesitar una tabla de grupos aparte: en
        // la práctica, quienes comparten numero_acta defendieron juntos
        // (mismo PTI/tesis grupal) — se ve claro en los datos reales (varias
        // actas con 2-3 personas cada una).
        $gruposPorActa = Acta::whereNotNull('numero_acta')->where('numero_acta', '!=', '')
            ->with('seguimiento.user:id,name,apellidos')
            ->get()
            ->groupBy('numero_acta')
            ->map(fn ($actas) => $actas->pluck('seguimiento.user')->filter()->unique('id'));

        // Los expedientes de secretaría no traen actas: ahí el grupo se ve en
        // que comparten la misma RD de PTI/TI/Tesis (un solo documento para
        // todo el grupo). Mapa seguimiento_id -> compañeros por RD.
        // Misma regla que GrupoTitulacion::companeros() (lo que ve el alumno),
        // aquí en bloque para todas las filas a la vez.
        $claveRd = fn (?string $rd) => GrupoTitulacion::claveRd($rd);
        $companerosPorRd = Resolucion::whereIn('tipo', GrupoTitulacion::TIPOS_RD_GRUPALES)->where('agrupa', true)
            ->whereNotNull('numero_rd')->where('numero_rd', '!=', '')
            ->with('seguimiento.user:id,name,apellidos')
            ->get()
            ->groupBy(fn (Resolucion $r) => $r->tipo.'|'.$claveRd($r->numero_rd))
            ->filter(fn ($rs) => $rs->pluck('seguimiento_id')->unique()->count() > 1)
            ->reduce(function ($mapa, $rs) {
                $usuarios = $rs->pluck('seguimiento.user')->filter()->unique('id');
                foreach ($rs->pluck('seguimiento_id')->unique() as $segId) {
                    $mapa[$segId] = ($mapa[$segId] ?? collect())->merge($usuarios)->unique('id');
                }
                return $mapa;
            }, []);

        // Grupos armados al iniciar el proceso (seguimiento_titulacion.grupo_id, desde el 19/10).
        $companerosPorGrupo = SeguimientoTitulacion::whereNotNull('grupo_id')
            ->with('user:id,name,apellidos')->get(['id', 'user_id', 'grupo_id'])
            ->groupBy('grupo_id')
            ->reduce(function ($mapa, $segs) {
                foreach ($segs as $seg) {
                    $mapa[$seg->id] = $segs->pluck('user')->filter();
                }
                return $mapa;
            }, []);

        // Retiro (deserción): el perfil «Retirado» de la cuenta, o la marca en las hojas de
        // expedientes cuando todavía no se registró en su expediente ('planilla').
        // La BD descarta de entrada las ~7.000 filas sin esas palabras; marcadoRetirado() confirma.
        $retiroPlanilla = Importacion::where(fn ($q) => $q->where('datos_normalizados', 'like', '%etirad%')
                ->orWhere('datos_normalizados', 'like', '%eirada%')->orWhere('datos_normalizados', 'like', '%sale%'))
            ->get(['user_id', 'dni', 'datos_normalizados'])
            ->filter(fn (Importacion $i) => self::marcadoRetirado($i->datos_normalizados ?? []));
        $retiroUserIds = $retiroPlanilla->pluck('user_id')->filter()->unique();
        $retiroDnis = $retiroPlanilla->pluck('dni')->filter()->unique();
        // Un perfil «Retirado» en alguien que ya egresó es un dato viejo (se reincorporó y egresó), no un retiro.
        $retiroDe = fn (User $u) => $u->perfil === 'Retirado' && $u->condicion !== 'Egresado' ? 'perfil'
            : ($retiroUserIds->contains($u->id) || ($u->dni && $retiroDnis->contains($u->dni)) ? 'planilla' : null);

        // Contacto de la fila: celular de la ficha (FID o PPD) o, si no tiene, el de la cuenta; correo de la cuenta.
        $contacto = fn (User $u) => [
            'telefono' => ($u->alumno ?? $u->alumnoB)?->numero ?: $u->telefono,
            'email' => $u->email,
        ];

        // "Registrado" ya no es solo condicion=Egresado: un alumno activo (FID desde Ciclo IX,
        // PPD desde Ciclo I) puede arrancar su PTI antes de egresar (así
        // funciona en la institución) — una vez que alguien tiene fila en
        // seguimiento_titulacion, cuenta como registrado sin importar su
        // condicion todavía.
        $registrados = User::where(fn ($q) => $q->where('condicion', 'Egresado')->orWhereHas('seguimientoTitulacion'))
            ->with([
                'seguimientoTitulacion.estadoTitulacion',
                'seguimientoTitulacion.actas.asesor',
                'seguimientoTitulacion.actas.asesorExterno',
                'seguimientoTitulacion.actas.jurados.docente',
                'seguimientoTitulacion.actas.jurados.asesorExterno',
                'seguimientoTitulacion.ptis.asesor',
                'seguimientoTitulacion.ptis.asesorExterno',
                'seguimientoTitulacion.tesis.asesorExterno',
                'seguimientoTitulacion.resoluciones',
                'seguimientoTitulacion.cartasAceptacion',
                'seguimientoTitulacion.participantes',
                'seguimientoTitulacion.documentos',
                'seguimientoTitulacion.pti.asesor',
                'seguimientoTitulacion.tesis.asesor',
                'seguimientoTitulacion.avisosPti.creadoPor',
                'programa', 'ciclo', 'alumno', 'alumnoB',
            ])
            ->get()
            ->map(function (User $user) use ($gruposPorActa, $companerosPorRd, $companerosPorGrupo, $retiroDe, $contacto) {
                // El detalle completo (PTI/Tesis/Actas/RDs/Documentos) ahora se
                // muestra directo en el acordeón de cada fila (ver
                // titulacion/index.blade.php), así que todo registrado necesita
                // su seguimiento_titulacion ya creado, no solo al entrar a
                // show(). Mismo firstOrCreate que usaba show() antes.
                $seguimiento = $user->seguimientoTitulacion ?: SeguimientoTitulacion::create([
                    'user_id' => $user->id,
                    'estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti'),
                ]);

                $acta = $seguimiento->actas?->first();
                $integrantes = ($acta && $acta->numero_acta ? ($gruposPorActa[$acta->numero_acta] ?? collect()) : collect())
                    ->merge($companerosPorRd[$seguimiento->id] ?? collect())
                    ->merge($companerosPorGrupo[$seguimiento->id] ?? collect())
                    ->unique('id')
                    ->reject(fn ($u) => $u->id === $user->id)
                    ->values();

                return (object) [
                    'tipo' => 'registrado',
                    'row_id' => 'reg'.$user->id,
                    'user_id' => $user->id,
                    'sort_key' => $user->apellidos.' '.$user->name,
                    'nombre' => $user->apellidos.', '.$user->name,
                    'expediente' => $seguimiento->numero_expediente,
                    'dni' => $user->dni,
                    'programa' => $user->programa->nombre ?? '—',
                    'programa_id' => $user->programa_id,
                    'modalidad' => $user->programa?->modalidad?->value,
                    'anio' => $user->ciclo?->nombre,
                    'estado_label' => $seguimiento->estadoTitulacion?->nombre ?? 'Sin seguimiento',
                    'estado_badge' => AvanceTitulacion::badge($seguimiento->estadoTitulacion?->clave),
                    'accion_label' => 'Ver / Gestionar',
                    'accion_clase' => 'btn-info',
                    'user' => $user,
                    'seguimiento' => $seguimiento,
                    'acta' => $acta,
                    'rds' => $seguimiento->resoluciones ?? collect(),
                    'integrantes_grupo' => $integrantes,
                    'siguiente' => ($avanceFila = $seguimiento->avance())->siguientePaso()['falta'] ?? null,
                    // Mini barra PTI | TI | Tesis de la columna Estado.
                    'etapas' => $avanceFila->etapas(),
                    'tareas' => self::tareasDe($seguimiento),
                    'vigencia' => $vigencia = new VigenciaPti($seguimiento->pti),
                    'vigencia_grupo' => self::grupoVigencia($vigencia, $seguimiento->estadoTitulacion?->clave),
                    'grado' => self::gradoDe($seguimiento->tesis),
                    'retiro' => $retiroDe($user),
                    ...$contacto($user),
                ];
            });

        $activosElegibles = $this->activosElegiblesParaTitulacion()
            ->map(fn (User $user) => (object) [
                'tipo' => 'activo_elegible',
                'row_id' => 'act'.$user->id,
                'user_id' => $user->id,
                'sort_key' => $user->apellidos.' '.$user->name,
                'nombre' => $user->apellidos.', '.$user->name,
                'dni' => $user->dni,
                'programa' => $user->programa->nombre ?? '—',
                'programa_id' => $user->programa_id,
                'modalidad' => $user->programa?->modalidad?->value,
                'anio' => $user->ciclo?->nombre,
                // Con la modalidad a la vista: PPD de Ciclo II es normal (programa de un año); FID, desde el IX.
                'estado_label' => 'Activo '.($user->ciclo?->programa?->esPpd() ? 'PPD' : 'FID').' — Ciclo '.$user->ciclo?->numeroRomano.' (puede iniciar)',
                'estado_badge' => 'primary',
                'accion_url' => route('titulacion.inicio.crear', $user->id),
                'accion_label' => 'Iniciar proceso',
                'accion_clase' => 'btn-primary',
                'es_form' => true,
                'retiro' => $retiroDe($user),
                ...$contacto($user),
            ]);

        $programasPorId = Programa::all()->keyBy('id');
        $pendientes = $this->pendientesAgrupados()
            ->map(function ($grupo) use ($programasPorId) {
                $programaReconocido = $grupo['programa_id'] ? $programasPorId->get($grupo['programa_id']) : null;
                $esCompletarFicha = (bool) $grupo['user_id'];
                $retirado = $grupo['retirado'] && ! $esCompletarFicha;
                return (object) [
                    'tipo' => $retirado ? 'retirado' : ($esCompletarFicha ? 'pendiente_ficha' : 'pendiente_cuenta'),
                    'row_id' => 'pend'.$grupo['importacion_id'],
                    'sort_key' => $grupo['nombre'] ?? '',
                    'nombre' => $grupo['nombre'] ?: '—',
                    'expediente' => $grupo['expediente'],
                    'dni' => $grupo['dni'],
                    // Nombre oficial si se reconoció (dice si es FID o PPD); si no,
                    // el texto tal cual viene en el archivo.
                    'programa' => $programaReconocido?->nombre ?? ($grupo['programa'] ?: '—'),
                    'programa_id' => $grupo['programa_id'],
                    // Del programa reconocido; si no se reconoció, de las pistas
                    // FID/PPD de los archivos de origen (null si se contradicen).
                    'modalidad' => $programaReconocido?->modalidad?->value
                        ?? ($grupo['tipo'] ? strtoupper($grupo['tipo']) : null),
                    'anio' => $grupo['anio_ingreso'] ? 'Egresados '.$grupo['anio_ingreso'] : null,
                    'estado_label' => $retirado ? 'Retirado(a) según la planilla — sin cuenta'
                        : ($esCompletarFicha ? 'Ya tiene cuenta — falta ficha' : 'Sin cuenta en el sistema'),
                    'estado_badge' => $retirado ? 'danger' : 'warning',
                    // A quien se retiró de la institución no se le crea cuenta (igual que en la sincronización).
                    'accion_url' => match (true) {
                        $retirado => null,
                        $esCompletarFicha => route('titulacion.importaciones.completar-ficha.form', $grupo['importacion_id']),
                        default => route('titulacion.importaciones.crear-cuenta.form', $grupo['importacion_id']),
                    },
                    'accion_label' => $esCompletarFicha ? 'Completar ficha' : 'Crear cuenta',
                    'accion_clase' => 'btn-warning',
                    'filas_origen' => $grupo['filas'],
                    'user_id_importado' => $grupo['user_id'],
                    // Sin cuenta todavía: lo que traen las planillas (a veces el "correo" dice "Retirado").
                    'email' => str_contains((string) $grupo['email'], '@') ? $grupo['email'] : null,
                    'telefono' => collect($grupo['filas'])->pluck('datos.celular')->first(fn ($v) => filled($v)),
                ];
            });

        // Filas importadas de alguien que YA está en la lista (con proceso iniciado o
        // activo que puede iniciar): no son otra persona. Se quitan de "pendientes" y
        // sus datos de origen se muestran dentro de la ficha de esa persona.
        // Cuentas que la planilla marca como retiradas y que no están en la lista (no egresaron ni
        // iniciaron proceso): se muestran en «Retirados» para registrar el retiro en su expediente.
        $idsEnLista = $registrados->concat($activosElegibles)->pluck('user_id');
        $retiradosConCuenta = User::where(fn ($q) => $q->whereIn('id', $retiroUserIds)->orWhereIn('dni', $retiroDnis))
            ->whereNotIn('id', $idsEnLista)
            ->with(['programa', 'ciclo', 'alumno', 'alumnoB'])
            ->get()
            ->map(fn (User $user) => (object) [
                'tipo' => 'retirado',
                'row_id' => 'ret'.$user->id,
                'user_id' => $user->id,
                'sort_key' => $user->apellidos.' '.$user->name,
                'nombre' => $user->apellidos.', '.$user->name,
                'dni' => $user->dni,
                'programa' => $user->programa->nombre ?? '—',
                'programa_id' => $user->programa_id,
                'modalidad' => $user->programa?->modalidad?->value,
                'anio' => $user->ciclo?->nombre,
                'estado_label' => $user->perfil === 'Retirado' ? 'Retirado(a)' : 'Retirado(a) según la planilla',
                'estado_badge' => 'danger',
                'retiro' => $retiroDe($user),
                'accion_url' => route('expediente.show', $user->id),
                'accion_label' => 'Ver expediente',
                'accion_clase' => 'btn-outline-secondary',
                ...$contacto($user),
            ]);

        $presentes = $registrados->concat($activosElegibles)->concat($retiradosConCuenta);
        $porUserId = $presentes->keyBy('user_id');
        $porDni = $presentes->filter(fn ($i) => filled($i->dni))->keyBy('dni');
        $pendientes = $pendientes->reject(function ($p) use ($porUserId, $porDni) {
            $dueno = ($p->user_id_importado ? $porUserId->get($p->user_id_importado) : null)
                ?? (filled($p->dni) ? $porDni->get($p->dni) : null);
            if (! $dueno) {
                return false;
            }
            $dueno->filas_importadas = collect($dueno->filas_importadas ?? [])->concat($p->filas_origen);

            return true;
        })->values();

        $todos = $registrados->concat($pendientes)->concat($activosElegibles)->concat($retiradosConCuenta);
        $totalRetirados = $todos->filter(fn ($item) => $item->tipo === 'retirado' || ($item->retiro ?? null))->count();

        // Quien se retiró sin cuenta ni proceso no es trabajo pendiente: solo sale en «Retirados» o al buscarlo.
        if ($request->input('estado') !== 'retirados' && ! $request->filled('search')) {
            $todos = $todos->reject(fn ($item) => $item->tipo === 'retirado');
        }

        // Un DNI o un N° de expediente identifica a UNA persona: se busca en toda la lista, sin los filtros
        // (siguen elegidos, en pausa). Un nombre se busca dentro de los filtros; si ahí no hay nadie, se
        // avisa cuántos hay en toda la lista.
        $textoBuscado = trim((string) $request->input('search'));
        $expedienteBuscado = Expediente::normalizar($textoBuscado);
        $busquedaGlobal = $expedienteBuscado !== null || preg_match('/^\d{7,12}$/', preg_replace('/\s+/', '', $textoBuscado));
        $soloBusqueda = $todos;

        if ($request->filled('search')) {
            // Sin distinguir tildes, ñ ni mayúsculas: "titulacion" encuentra "Titulación".
            $normalizar = fn ($texto) => mb_strtolower(\Illuminate\Support\Str::ascii((string) $texto));
            $term = $normalizar($expedienteBuscado ? $textoBuscado : preg_replace('/\s+/', ' ', $textoBuscado));
            $todos = $todos->filter(function ($item) use ($term, $normalizar, $expedienteBuscado) {
                // "N°54-T/PUK", "0054-t/puk"… encuentran 0054-T/PUK (como se guarda).
                if ($expedienteBuscado !== null) {
                    return ($item->expediente ?? null) === $expedienteBuscado;
                }
                if (str_contains($normalizar($item->nombre), $term) || str_contains((string) $item->dni, str_replace(' ', '', $term))
                    || str_contains($normalizar($item->expediente ?? ''), $term)) {
                    return true;
                }
                // También busca por Nº de acta y por cualquier RD (resolución) de la persona.
                $acta = $item->acta ?? null;
                if ($acta && str_contains($normalizar($acta->numero_acta), $term)) {
                    return true;
                }
                foreach (($item->rds ?? []) as $rd) {
                    if (str_contains($normalizar($rd->numero_rd), $term)) {
                        return true;
                    }
                }
                return false;
            });
            $soloBusqueda = $todos;
        }

        if ($request->input('estado') === 'pendientes') {
            $todos = $todos->whereIn('tipo', ['pendiente_cuenta', 'pendiente_ficha']);
        } elseif ($request->input('estado') === 'activos') {
            $todos = $todos->where('tipo', 'activo_elegible');
        } elseif ($request->input('estado') === 'retirados') {
            $todos = $todos->filter(fn ($item) => $item->tipo === 'retirado' || ($item->retiro ?? null));
        } elseif ($request->filled('estado_titulacion_id')) {
            // Filtro por estado de titulación real solo aplica a los ya registrados.
            $estadoNombre = EstadoTitulacion::find((int) $request->input('estado_titulacion_id'))?->nombre;
            $todos = $todos->filter(fn ($item) => $item->tipo === 'registrado' && $item->estado_label === $estadoNombre);
        }

        // Vigencia del PTI (solo registrados): para encontrar a quién avisar.
        $filtroVigencia = array_key_exists((string) $request->input('vigencia'), self::FILTROS_VIGENCIA) ? $request->input('vigencia') : null;
        if ($filtroVigencia) {
            $todos = $todos->filter(fn ($item) => in_array($item->vigencia_grupo ?? null, self::FILTROS_VIGENCIA[$filtroVigencia][1], true));
        }

        $filtroTarea = array_key_exists((string) $request->input('tarea'), self::TAREAS) ? $request->input('tarea') : null;
        if ($filtroTarea) {
            $todos = $todos->filter(fn ($item) => in_array($filtroTarea, $item->tareas ?? [], true));
        }

        // Grado en SUNEDU: solo aplica a quien tiene proceso (registrados).
        $filtroGrado = array_key_exists((string) $request->input('grado'), self::FILTROS_GRADO) ? $request->input('grado') : null;
        if ($filtroGrado) {
            $todos = $todos->filter(fn ($item) => $item->tipo === 'registrado'
                && ($item->grado ?? 'sin_grado') === $filtroGrado);
        }

        if (in_array($request->input('modalidad'), ['FID', 'PPD'], true)) {
            $todos = $todos->filter(fn ($item) => ($item->modalidad ?? null) === $request->input('modalidad'));
        }

        // Las listas Programa y Promoción solo ofrecen lo que hay con los demás filtros (con su cantidad).
        $conteoPrograma = $todos->countBy(fn ($item) => $item->programa_id ?? 0);
        if ($request->filled('programa_id')) {
            $todos = $todos->filter(fn ($item) => ($item->programa_id ?? null) === (int) $request->input('programa_id'));
        }

        $opcionesPromocion = self::opcionesPromocion($todos);
        if ($request->filled('anio')) {
            $todos = $todos->filter(fn ($item) => self::enPromocion($item, (string) $request->input('anio')));
        }

        $coincidenciasFuera = 0;
        if ($busquedaGlobal) {
            $todos = $soloBusqueda;
        } elseif ($request->filled('search') && $todos->isEmpty()) {
            $coincidenciasFuera = $soloBusqueda->count();
        }

        // Con filtro de vigencia, primero el que vence (o venció) antes.
        $todos = $filtroVigencia && $filtroVigencia !== 'sin_fecha' && ! $busquedaGlobal
            ? $todos->sortBy(fn ($item) => $item->vigencia->vencimiento->format('Ymd').$item->sort_key)->values()
            : $todos->sortBy('sort_key')->values();

        // Exportar a Excel exactamente lo filtrado (todas las páginas).
        if ($request->boolean('exportar')) {
            return \Maatwebsite\Excel\Facades\Excel::download(new \App\Exports\TitulacionExport($todos), 'titulacion_'.now()->format('Y-m-d').'.xlsx');
        }

        $perPage = 30;
        $page = (int) $request->input('page', 1);
        // Al volver de guardar (?ver=), ir a la página donde está esa persona.
        if ($request->filled('ver') && ! $request->filled('page')) {
            $posicion = $todos->search(fn ($item) => $item->row_id === 'reg'.(int) $request->input('ver'));
            $page = $posicion === false ? 1 : intdiv($posicion, $perPage) + 1;
        }
        $egresados = new \Illuminate\Pagination\LengthAwarePaginator(
            $todos->forPage($page, $perPage),
            $todos->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->except('ver', 'tab', 'paso')]
        );

        // Pestaña "Grupo": correo y estado de cada integrante, en una sola
        // consulta para las filas de esta página (no para las ~200 del total).
        $idsIntegrantes = $egresados->getCollection()->flatMap(fn ($item) => collect($item->integrantes_grupo ?? [])->pluck('id'))->unique();
        if ($idsIntegrantes->isNotEmpty()) {
            $integrantesCompletos = User::whereIn('id', $idsIntegrantes)
                ->with('seguimientoTitulacion.estadoTitulacion')
                ->get(['id', 'name', 'apellidos', 'email', 'dni'])->keyBy('id');
            $egresados->getCollection()->each(function ($item) use ($integrantesCompletos) {
                if (isset($item->integrantes_grupo)) {
                    $item->integrantes_grupo = $item->integrantes_grupo->map(fn ($u) => $integrantesCompletos[$u->id] ?? $u);
                }
            });
        }

        // La ficha que se acaba de editar (?ver=) viene armada en la lista: mismos datos importados que la de AJAX.
        if ($request->filled('ver')) {
            $itemVer = $egresados->getCollection()->first(fn ($item) => $item->tipo === 'registrado' && $item->user_id === (int) $request->input('ver'));
            if ($itemVer) {
                $itemVer->filas_importadas = $this->filasImportadasDe($itemVer->user);
            }
        }

        $estados = EstadoTitulacion::orderBy('orden')->get();
        $docentes = User::role('docente')->orderBy('name')->get();
        $asesores = Asesor::orderBy('apellidos')->orderBy('nombres')->get();

        $conteoPorEstado = SeguimientoTitulacion::selectRaw('estado_titulacion_id, COUNT(*) as total')
            ->groupBy('estado_titulacion_id')
            ->pluck('total', 'estado_titulacion_id');

        $totalEgresados = $registrados->count();
        $totalPendientes = $pendientes->whereIn('tipo', ['pendiente_cuenta', 'pendiente_ficha'])->count();
        $totalActivosElegibles = $activosElegibles->count();
        $conteoTareas = collect(self::TAREAS)->map(fn ($t, $clave) => $registrados->filter(fn ($i) => in_array($clave, $i->tareas, true))->count());
        $conteoVigencia = collect(self::FILTROS_VIGENCIA)->map(fn ($f) => $registrados->whereIn('vigencia_grupo', $f[1])->count());

        // Al llegar desde la campanita ("dato por revisar"), mostrar arriba lo
        // que escribió la persona (la notificación solo trae un resumen).
        $reportesDeDatos = $request->filled('ver')
            ? $request->user()->notifications()
                ->where('type', \App\Notifications\TitulacionDatoReportado::class)
                ->get()
                ->filter(fn ($n) => (int) ($n->data['persona_id'] ?? 0) === (int) $request->input('ver'))
                ->values()
            : collect();

        // Solo los programas que tienen personas con los filtros de arriba (y el elegido, aunque quede en 0).
        $programasFiltro = Programa::orderBy('modalidad')->orderBy('nombre')->get()
            ->filter(fn ($p) => ($conteoPrograma[$p->id] ?? 0) > 0 || (int) $request->input('programa_id') === $p->id);

        return view('titulacion.index', compact(
            'egresados', 'estados', 'docentes', 'asesores', 'conteoPorEstado', 'totalEgresados', 'totalPendientes', 'totalActivosElegibles',
            'programasFiltro', 'conteoPrograma', 'opcionesPromocion', 'reportesDeDatos', 'conteoVigencia', 'filtroVigencia', 'conteoTareas', 'filtroTarea',
            'totalRetirados', 'filtroGrado', 'busquedaGlobal', 'coincidenciasFuera'
        ));
    }

    /**
     * Agrupa titulacion_importaciones por identidad (DNI, o nombre
     * normalizado cuando no hay DNI) para que la misma persona que
     * apareció en varios archivos fuente cuente como UNA sola fila
     * pendiente, no una por archivo. Excluye a quien ya quedó con ficha
     * completa (condicion='Egresado').
     *
     * @return \Illuminate\Support\Collection<int, array{importacion_id:int,dni:?string,nombre:?string,programa:?string,user_id:?int}>
     */
    /**
     * Las hojas sin DNI (actas EXPO-SUST, archivos generales de Investigación) escriben el nombre a su
     * manera ("Nombres Apellidos"), así que agrupar por nombre las dejaba como OTRA persona pendiente,
     * "sin correo", aunque la hoja de expedientes ya la trae con DNI y correo. Un grupo sin DNI se une
     * al grupo con DNI de la misma persona: misma cuenta vinculada, o el nombre coincide con UNO solo.
     */
    private function unirFilasSinDni($grupos)
    {
        $conDni = $grupos->filter(fn ($g) => filled($g->first()->dni));
        $palabras = fn ($t) => array_filter(explode(' ', preg_replace('/[^A-Z]+/', ' ', strtoupper(\Illuminate\Support\Str::ascii((string) $t)))), fn ($p) => strlen($p) >= 4);

        // Índice palabra -> grupos con DNI que la tienen, para no comparar contra todos.
        $indice = [];
        foreach ($conDni as $clave => $g) {
            foreach ($g as $fila) {
                foreach ($palabras($fila->nombre_estudiante) as $p) {
                    $indice[$p][$clave] = true;
                }
            }
        }
        $porUsuario = $conDni->mapWithKeys(fn ($g, $clave) => [$g->firstWhere('user_id', '!=', null)?->user_id ?? '' => $clave])->forget('');

        foreach ($grupos->filter(fn ($g) => blank($g->first()->dni)) as $clave => $g) {
            $destino = ($u = $g->firstWhere('user_id', '!=', null)?->user_id) ? ($porUsuario[$u] ?? null) : null;
            if (! $destino) {
                $nombre = (string) $g->first()->nombre_estudiante;
                $candidatos = collect($palabras($nombre))->flatMap(fn ($p) => array_keys($indice[$p] ?? []))->unique()
                    ->filter(fn ($c) => $conDni[$c]->contains(fn ($fila) => NombresImportados::mismaPersona($nombre, (string) $fila->nombre_estudiante)));
                $destino = $candidatos->count() === 1 ? $candidatos->first() : null;
            }
            if ($destino) {
                $grupos[$destino] = $grupos[$destino]->merge($g);
                $grupos->forget($clave);
            }
        }

        return $grupos;
    }

    /**
     * Lo que sigue sin DNI tras unirFilasSinDni casi siempre es alguien que YA está registrado y cuyo
     * nombre el acta o el archivo general escribe a su manera y con erratas ("Judith Pila Zerceda" por
     * "Pila Zerceda, Yudith"). Se compara palabra por palabra tolerando UNA letra distinta en las de 5+
     * letras, y solo se une si coincide con UNA sola persona: primero con una cuenta (se vincula, solo
     * en memoria), si no con un pendiente que sí trae DNI (se juntan). Las filas de grupo ("Ana Pérez
     * y Luis Soto") se quitan si cada integrante tiene cuenta; si no, siguen pendientes para revisar.
     */
    private function vincularSinDniACuentas($grupos)
    {
        $sinCuenta = $grupos->filter(fn ($g) => blank($g->first()->dni) && ! $g->firstWhere('user_id', '!=', null));
        if ($sinCuenta->isEmpty()) {
            return $grupos;
        }

        $palabras = fn ($t) => array_values(array_unique(array_filter(
            explode(' ', preg_replace('/[^A-Z]+/', ' ', strtoupper(\Illuminate\Support\Str::ascii((string) $t)))),
            fn ($p) => strlen($p) > 2 && ! in_array($p, ['DEL', 'LOS', 'LAS'], true)
        )));
        $parecida = fn ($a, $b) => $a === $b || (strlen($a) >= 5 && strlen($b) >= 5 && levenshtein($a, $b) <= 1);
        // Misma regla que NombresImportados::mismaPersona (todas las palabras del nombre más corto), con erratas.
        $mismaPersona = function (array $pa, array $pb) use ($parecida) {
            [$corto, $largo] = count($pa) <= count($pb) ? [$pa, $pb] : [$pb, $pa];
            $iguales = count(array_filter($corto, fn ($w) => collect($largo)->contains(fn ($x) => $parecida($w, $x))));

            return $iguales >= 2 && $iguales === count($corto);
        };
        $unica = function (string $nombre, array $indice, array $palabrasDe) use ($palabras, $mismaPersona) {
            $pn = $palabras($nombre);
            $candidatas = collect($pn)->flatMap(fn ($p) => array_keys($indice[$p] ?? []))->unique()
                ->filter(fn ($id) => $mismaPersona($pn, $palabrasDe[$id]));

            return $candidatas->count() === 1 ? $candidatas->first() : null;
        };

        $cuentasIndice = [];
        $cuentasPalabras = [];
        foreach (User::whereNotNull('dni')->where('dni', '!=', '')->get(['id', 'name', 'apellidos']) as $u) {
            $cuentasPalabras[$u->id] = $palabras($u->apellidos.' '.$u->name);
            foreach ($cuentasPalabras[$u->id] as $p) {
                $cuentasIndice[$p][$u->id] = true;
            }
        }
        $gruposIndice = [];
        $gruposPalabras = [];
        foreach ($grupos->filter(fn ($g) => filled($g->first()->dni)) as $clave => $g) {
            $gruposPalabras[$clave] = $palabras($g->first()->nombre_estudiante);
            foreach ($gruposPalabras[$clave] as $p) {
                $gruposIndice[$p][$clave] = true;
            }
        }
        $cuentaDe = fn (string $nombre) => $unica($nombre, $cuentasIndice, $cuentasPalabras);

        foreach ($sinCuenta as $clave => $g) {
            $nombre = (string) $g->first()->nombre_estudiante;
            if ($userId = $cuentaDe($nombre)) {
                $g->each(fn (Importacion $fila) => $fila->user_id = $userId);
                continue;
            }
            if (($destino = $unica($nombre, $gruposIndice, $gruposPalabras)) !== null) {
                $grupos[$destino] = $grupos[$destino]->merge($g);
                $grupos->forget($clave);
                continue;
            }
            $integrantes = array_values(array_filter(array_map('trim', preg_split('/\R+|\s+y\s+|;|,/iu', $nombre))));
            if (count($integrantes) > 1 && collect($integrantes)->every(fn ($n) => $cuentaDe($n))) {
                $grupos->forget($clave);
            }
        }

        return $grupos;
    }

    /**
     * Agrupar ~7.000 filas importadas toma unos segundos y solo cambia al sincronizar una planilla o al
     * crear/editar cuentas: se guarda en caché con una huella de esas tablas (cantidad, último id y última
     * modificación). Cualquier cambio da otra huella y se recalcula; nunca se muestra algo desactualizado.
     */
    private function pendientesAgrupados()
    {
        $huella = collect([Importacion::query(), User::query(), Programa::query()])
            ->map(fn ($q) => implode('.', (array) $q->toBase()->selectRaw('COUNT(*) c, MAX(id) i, MAX(updated_at) u')->first()))
            ->implode('|');

        return cache()->remember('titulacion.pendientes.'.md5($huella), now()->addDay(), fn () => $this->calcularPendientesAgrupados());
    }

    private function calcularPendientesAgrupados()
    {
        // DNI que ya tienen cuenta: crear otra con el mismo DNI fallaría (ver crearCuentasMasivo).
        $cuentaPorDni = User::whereNotNull('dni')->where('dni', '!=', '')->pluck('id', 'dni');

        // Sin datos_originales (~6 MB de JSON que aquí no se usa).
        return Importacion::whereIn('estado_match', ['sin_match', 'vinculado'])
            ->get(['id', 'origen', 'archivo_nombre', 'dni', 'nombre_estudiante', 'nombre_normalizado', 'user_id', 'estado_match', 'datos_normalizados', 'created_at'])
            // Sin DNI ni nombre no hay a quién identificar: son las filas en
            // blanco de la hoja de secretaría ("BLANCOO"). Agrupadas por clave
            // vacía aparecían como una sola "persona" pendiente fantasma.
            ->filter(fn (Importacion $imp) => filled($imp->dni) || filled($imp->nombre_normalizado))
            // Encabezados de promoción de los expedientes de Marilyn
            // ("2016-I al 2021-II" en la columna de nombre): no son personas.
            ->reject(fn (Importacion $imp) => NombresImportados::esFilaDeEncabezado($imp->nombre_estudiante, $imp->dni))
            ->groupBy(fn (Importacion $imp) => $imp->dni ?: $imp->nombre_normalizado)
            ->pipe(fn ($grupos) => $this->unirFilasSinDni($grupos))
            ->pipe(fn ($grupos) => $this->vincularSinDniACuentas($grupos))
            ->map(function ($grupo) use ($cuentaPorDni) {
                $vinculado = $grupo->first(fn (Importacion $i) => $i->user_id);
                $conProgramaOPeriodo = $grupo->first(fn (Importacion $i) => ! empty($i->datos_normalizados['programa'] ?? null));

                // Nombres/apellidos separados de forma CONFIABLE: solo si algún
                // archivo fuente ya los trae en columnas distintas (ej.
                // ppd-unificado). Nunca se parte a la fuerza un "APELLIDOS Y
                // NOMBRES" combinado — eso es ambiguo en español y se deja para
                // que un humano lo confirme a mano (ver importacionCrearCuentaForm).
                $conNombreSeparado = $grupo->first(fn (Importacion $i) => ! empty($i->datos_normalizados['nombres'] ?? null) && ! empty($i->datos_normalizados['apellidos'] ?? null));

                $email = $grupo->map(fn (Importacion $i) => $i->datos_normalizados['email'] ?? $i->datos_normalizados['correo'] ?? null)
                    ->first(fn ($v) => ! empty($v));

                $anio = $grupo->map(fn (Importacion $i) => $i->datos_normalizados['anio_ingreso'] ?? $i->datos_normalizados['periodo'] ?? null)
                    ->first(fn ($v) => ! empty($v));

                // FID/PPD: del nombre del archivo de origen, o en la hoja de
                // secretaría (expo-sust) de su columna K ("EXPO PPD", "TESIS
                // FID"). Se mira en todas las filas de la persona.
                $pistasTipo = $grupo->flatMap(fn (Importacion $i) => [
                    $i->origen,
                    strtolower((string) ($i->datos_normalizados['tipo_k'] ?? '')),
                ]);
                $esPpd = $pistasTipo->contains(fn ($o) => str_contains($o, 'ppd'));
                $esFid = $pistasTipo->contains(fn ($o) => str_contains($o, 'fid'));
                // Si los archivos se contradicen, no se adivina: queda para revisión.
                $tipoContradictorio = $esPpd && $esFid;
                $tipo = $tipoContradictorio ? null : ($esPpd ? 'ppd' : ($esFid ? 'fid' : null));
                // La hoja de secretaría llama "carrera" a lo que el resto llama "programa".
                $programaTexto = $conProgramaOPeriodo?->datos_normalizados['programa']
                    ?? $grupo->map(fn (Importacion $i) => $i->datos_normalizados['carrera'] ?? null)
                        ->first(fn ($v) => filled($v) && trim($v) !== '-');
                $programaId = $programaTexto ? $this->resolverProgramaId($programaTexto, $tipo) : null;

                $retirado = $grupo->contains(fn (Importacion $i) => self::marcadoRetirado($i->datos_normalizados ?? []));

                $motivos = [];
                if ($retirado) {
                    $motivos[] = 'retirado(a) de la institución según la planilla';
                }
                if (! $email) {
                    $motivos[] = 'sin correo';
                }
                if (! $conNombreSeparado) {
                    $motivos[] = 'nombre no separado con certeza (requiere confirmar a mano)';
                }
                if (! $programaId) {
                    $motivos[] = 'programa no identificado';
                }
                if (! $tipo) {
                    $motivos[] = $tipoContradictorio
                        ? 'los archivos la registran como FID y como PPD (confirmar a mano)'
                        : 'no se pudo determinar si es FID o PPD';
                }

                return [
                    'importacion_id' => ($vinculado ?? $grupo->first())->id,
                    'expediente' => $this->expedienteDeFilas($grupo),
                    'dni' => $grupo->first()->dni,
                    'nombre' => $grupo->first(fn (Importacion $i) => $i->nombre_estudiante)?->nombre_estudiante,
                    'nombres' => $conNombreSeparado?->datos_normalizados['nombres'] ?? null,
                    'apellidos' => $conNombreSeparado?->datos_normalizados['apellidos'] ?? null,
                    'email' => $email,
                    'programa' => $programaTexto,
                    'programa_id' => $programaId,
                    'anio_ingreso' => $anio,
                    'tipo' => $tipo,
                    'user_id' => $vinculado?->user_id,
                    'cuenta_por_dni' => $grupo->first()->dni ? ($cuentaPorDni[$grupo->first()->dni] ?? null) : null,
                    'elegible_masivo' => empty($motivos),
                    'retirado' => $retirado,
                    'motivos_no_elegible' => $motivos,
                    // Detalle crudo de cada archivo del que salió esta persona — para
                    // no necesitar una pantalla aparte de "Importaciones": se muestra
                    // desplegable en la misma fila (ver titulacion/index.blade.php).
                    'filas' => $grupo->map(fn (Importacion $i) => [
                        'origen' => $i->origen,
                        'archivo' => $i->archivo_nombre,
                        'fecha' => $i->created_at->format('d/m/Y H:i'),
                        'datos' => collect($i->datos_normalizados ?? [])
                            ->except(['nombre_estudiante', 'dni'])
                            ->filter(fn ($v) => $v !== '' && $v !== null)
                            ->all(),
                    ])->values(),
                ];
            })
            ->pipe(function ($grupos) {
                // Condición de todas las cuentas vinculadas en una sola consulta (antes, una por persona).
                $condicion = User::whereIn('id', $grupos->pluck('user_id')->filter()->unique())->pluck('condicion', 'id');

                return $grupos->filter(fn ($grupo) => ! $grupo['user_id'] // sin cuenta todavía -> sigue pendiente
                    || ! $condicion->has($grupo['user_id'])
                    || $condicion[$grupo['user_id']] !== 'Egresado'); // tiene cuenta pero le falta la ficha
            })
            ->values();
    }

    /**
     * Mapea el texto libre de "programa" de un CSV al id real en la tabla
     * `programas`. Solo cubre PPD por ahora (única fuente real con datos
     * hoy) — si no se puede resolver con confianza, devuelve null (mejor
     * dejarlo pendiente de revisión manual que asignar el programa
     * equivocado a alguien).
     */
    protected function resolverProgramaId(string $texto, ?string $tipo): ?int
    {
        // Cientos de filas repiten los mismos textos ("Educación Inicial", "inicial "…): se resuelve una vez.
        static $resueltos = [];

        return array_key_exists($clave = $tipo.'|'.$texto, $resueltos)
            ? $resueltos[$clave]
            : $resueltos[$clave] = $this->buscarProgramaId($texto, $tipo);
    }

    private function buscarProgramaId(string $texto, ?string $tipo): ?int
    {
        $t = \Illuminate\Support\Str::ascii(strtolower($texto));
        // "Educación Primaria Intercultural Bilingüe" es EIB escrito completo.
        if (str_contains($t, 'intercultural') || str_contains($t, 'bilingue')) {
            $t .= ' eib';
        }

        if ($tipo === 'ppd') {
            if (str_contains($t, 'eib')) {
                return Programa::buscar(Modalidad::PPD, NivelPrograma::PRIMARIA, true)?->id;
            }
            if (str_contains($t, 'inicial')) {
                return Programa::buscar(Modalidad::PPD, NivelPrograma::INICIAL, false)?->id;
            }
            if (str_contains($t, 'primaria')) {
                return Programa::buscar(Modalidad::PPD, NivelPrograma::PRIMARIA, false)?->id;
            }
        }

        // FID solo ofrece 2 programas hoy (confirmado contra la tabla real:
        // "Programa Inicial" y "Programa Primaria EIB" — no hay "Primaria"
        // FID sin EIB).
        if ($tipo === 'fid') {
            if (str_contains($t, 'eib')) {
                return Programa::buscar(Modalidad::FID, NivelPrograma::PRIMARIA, true)?->id;
            }
            if (str_contains($t, 'inicial')) {
                return Programa::fid()->nivel(NivelPrograma::INICIAL)->orderBy('id')->value('id');
            }
        }

        return null;
    }

    /**
     * Alumnos activos (todavía NO egresados) que ya pueden arrancar su proceso de titulación:
     * FID desde el Ciclo IX y PPD desde el Ciclo I (todo vigente). Se excluye a quien ya tiene
     * seguimiento_titulacion (ya inició, ya cuenta como "registrado" en index()).
     */
    private function activosElegiblesParaTitulacion(): \Illuminate\Support\Collection
    {
        return User::role(['alumno', 'alumnoB'])
            ->where(fn ($q) => $q->whereNull('condicion')->orWhere('condicion', '!=', 'Egresado'))
            ->where(fn ($q) => $q->whereNull('perfil')->orWhere('perfil', '!=', 'Retirado'))
            ->whereDoesntHave('seguimientoTitulacion')
            ->whereNotNull('ciclo_id')
            ->with(['ciclo.programa', 'programa', 'roles', 'alumno', 'alumnoB'])
            ->get()
            ->filter(fn (User $user) => $this->puedeIniciarTitulacion($user))
            ->values();
    }

    /** FID desde el Ciclo IX; PPD desde el Ciclo I (cualquier alumno vigente). */
    private function puedeIniciarTitulacion(User $user): bool
    {
        return self::puedeIniciar($user);
    }

    /** Público: las intranets de alumno (FID y PPD) muestran la guía del proceso a quien ya puede iniciarlo. */
    public static function puedeIniciar(User $user): bool
    {
        $user->loadMissing('ciclo.programa', 'programa');
        if (! $user->ciclo || $user->ciclo->ordenCiclo() >= 999) {
            return false; // sin ciclo, o "Egresados <año>" (esos ya se gestionan como egresados)
        }
        // La modalidad sale del ciclo en que está: un usuario con programa PPD mal cargado pero en
        // un ciclo FID (p. ej. Ciclo III de Inicial) se rige por la regla FID (desde el Ciclo IX).
        $esPpd = $user->ciclo->programa?->esPpd() ?? $user->programa?->esPpd() ?? $user->hasRole('alumnoB');

        return $user->ciclo->ordenCiclo() >= ($esPpd ? self::CICLO_MINIMO_TITULACION_PPD : self::CICLO_MINIMO_TITULACION);
    }

    /**
     * Crea el seguimiento_titulacion de un alumno activo que todavía no es
     * egresado — a propósito NO toca `condicion` (sigue siendo alumno
     * regular con matrícula activa) ni crea/edita su ficha `alumnos`, que ya
     * gestiona el sistema normal de matrícula. Arranca en "PTI en proceso"
     * en vez de "Egresado" — ese estado no aplica todavía a alguien que
     * sigue cursando.
     */
    public function iniciarSeguimiento(User $alumno)
    {
        abort_if($alumno->condicion === 'Egresado', 404, 'Ya es egresado — se gestiona igual que cualquier egresado.');
        abort_if($alumno->seguimientoTitulacion, 404, 'Ya tiene un seguimiento de titulación iniciado.');
        abort_unless($alumno->hasAnyRole(['alumno', 'alumnoB']) && $this->puedeIniciarTitulacion($alumno), 404, 'Aún no puede iniciar su titulación: FID desde el Ciclo IX.');

        // El estado se calcula de los datos: arranca "Sin PTI" y avanza solo
        // cuando se registre su PTI.
        SeguimientoTitulacion::create([
            'user_id' => $alumno->id,
            'estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti'),
        ]);

        return redirect()->route('titulacion.index', ['ver' => $alumno->id])
            ->with('success', 'Proceso de titulación iniciado para '.$alumno->apellidos.', '.$alumno->name.'.');
    }

    /**
     * Detalle de un egresado en página completa — el detalle del día a día
     * ahora vive directo en el acordeón de /titulacion (ver index()), esta
     * ruta queda para acceso directo/bookmark a un egresado puntual.
     */
    public function show(User $egresado)
    {
        // Egresado real, o alumno activo que ya inició su PTI desde Ciclo
        // IX+ (ver iniciarSeguimiento()) — cualquiera de los dos cuenta.
        abort_unless($egresado->condicion === 'Egresado' || $egresado->seguimientoTitulacion, 404);

        $seguimiento = SeguimientoTitulacion::firstOrCreate(
            ['user_id' => $egresado->id],
            ['estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti')]
        );

        $seguimiento->load([
            'pti.asesor', 'pti.asesorExterno', 'ptis.asesor', 'ptis.asesorExterno',
            'tesis.asesor', 'tesis.asesorExterno', 'actas.asesor', 'actas.asesorExterno',
            'actas.jurados.docente', 'actas.jurados.asesorExterno',
            'resoluciones', 'cartasAceptacion', 'documentos', 'estadoTitulacion', 'avisosPti.creadoPor', 'participantes.docente', 'participantes.asesorExterno',
        ]);

        $estados = EstadoTitulacion::orderBy('orden')->get();
        $docentes = User::role('docente')->orderBy('name')->get();
        $asesores = Asesor::orderBy('apellidos')->orderBy('nombres')->get();
        $integrantes = GrupoTitulacion::companeros($seguimiento);

        return view('titulacion.show', compact('egresado', 'seguimiento', 'estados', 'docentes', 'asesores', 'integrantes'));
    }

    /**
     * Datos demográficos de quienes están en el proceso de titulación: la misma herramienta
     * de /admin/alumnos/demograficos (fichas FID y PPD normalizadas por DatosDemograficos),
     * limitada a esta población y con una sección propia del proceso (condición, estado,
     * vigencia del PTI, promoción).
     */
    public function demograficos()
    {
        $estados = EstadoTitulacion::orderBy('orden')->pluck('nombre');
        $etiquetaVigencia = [
            'por_vencer' => 'Por vencer (≤ 4 meses)', 'proximo_anio' => 'Vence en ≤ 12 meses',
            'vigente' => 'Vigente', 'vencido' => 'Vencido', 'sin_fecha' => 'Sin fecha',
        ];

        $registrados = User::where(fn ($q) => $q->where('condicion', 'Egresado')->orWhereHas('seguimientoTitulacion'))
            ->with(['seguimientoTitulacion.estadoTitulacion', 'seguimientoTitulacion.pti', 'ciclo', 'programa', 'alumnoB'])
            ->get();
        $promocionesPpd = \App\Services\PromocionPpd::anios($registrados->filter(fn ($u) => $u->programa?->esPpd()));

        $poblacion = [];
        foreach ($registrados as $u) {
            $seg = $u->seguimientoTitulacion;
            $promocion = 'En curso';
            if ($u->condicion === 'Egresado') {
                // PPD: año de su promoción; FID: último año que figura en su ciclo ("Egresados 2025-I").
                preg_match_all('/\d{4}/', (string) $u->ciclo?->nombre, $m);
                $promocion = (string) ($promocionesPpd[$u->id] ?? (end($m[0]) ?: 'Sin dato'));
            }
            $poblacion[$u->id] = [
                'condicion' => $u->condicion === 'Egresado' ? 'Egresado(a)' : 'Estudiante con proceso iniciado',
                'estado' => $seg?->estadoTitulacion?->nombre ?? $estados->first(),
                'vigencia' => $seg ? ($etiquetaVigencia[self::grupoVigencia(new VigenciaPti($seg->pti), $seg->estadoTitulacion?->clave)] ?? null) : null,
                'promocion' => $promocion,
            ];
        }
        foreach ($this->activosElegiblesParaTitulacion() as $u) {
            $poblacion[$u->id] ??= [
                'condicion' => 'Estudiante que puede iniciar',
                'estado' => 'Aún no inicia',
                'vigencia' => null,
                'promocion' => 'En curso',
            ];
        }

        $promociones = collect($poblacion)->pluck('promocion')->unique()
            ->sortBy(fn ($p) => is_numeric($p) ? $p : '9999'.$p)->values()->all();
        $ordenEstados = $estados->concat(['Aún no inicia'])->all();
        $poblacion = array_map(fn ($p) => $p + [
            'orden_estados' => $ordenEstados,
            'orden_promociones' => $promociones,
        ], $poblacion);

        // Quienes no tienen ficha socioeconómica (FID ni PPD) no entran en los gráficos: se avisa.
        $conFicha = Alumno::whereIn('user_id', array_keys($poblacion))->pluck('user_id')
            ->merge(ppd::whereIn('user_id', array_keys($poblacion))->pluck('user_id'))->unique()->flip();
        $sinFicha = User::whereIn('id', array_keys(array_diff_key($poblacion, $conFicha->all())))
            ->orderBy('apellidos')->get(['id', 'name', 'apellidos', 'condicion']);

        return view('admin.demograficos.alumnos', [
            'datos' => (new \App\Services\DatosDemograficos)->construirTitulacion($poblacion),
            'vistaDm' => [
                'modo' => 'titulacion',
                'layout' => self::layout(),
                'titulo' => 'Datos demográficos · Titulación',
                'total' => count($poblacion),
                'sinFicha' => $sinFicha,
                'volver' => route('titulacion.index'),
            ],
        ]);
    }

    /**
     * Ficha de una persona para el acordeón de la lista, pedida al abrir su fila:
     * la lista ya no trae las 30 fichas completas (pesaba ~1,4 MB y 344 formularios).
     */
    public function ficha(User $egresado)
    {
        abort_unless($egresado->seguimientoTitulacion, 404);

        $seguimiento = $egresado->seguimientoTitulacion->load([
            'pti.asesor', 'pti.asesorExterno', 'ptis.asesor', 'ptis.asesorExterno',
            'tesis.asesor', 'tesis.asesorExterno', 'actas.asesor', 'actas.asesorExterno',
            'actas.jurados.docente', 'actas.jurados.asesorExterno',
            'resoluciones', 'cartasAceptacion', 'documentos', 'estadoTitulacion', 'avisosPti.creadoPor', 'actualizadoPor', 'participantes.docente', 'participantes.asesorExterno',
        ]);
        $egresado->load(['programa', 'ciclo']);

        return view('titulacion._ficha_ajax', [
            'egresado' => $egresado,
            'seguimiento' => $seguimiento,
            'docentes' => User::role('docente')->orderBy('name')->get(['id', 'name', 'apellidos']),
            'asesores' => Asesor::orderBy('apellidos')->orderBy('nombres')->get(),
            'integrantes' => GrupoTitulacion::companeros($seguimiento),
            'filasImportadas' => $this->filasImportadasDe($egresado),
        ]);
    }

    /** Filas de los archivos importados que corresponden a esta persona (por cuenta o DNI). */
    private function filasImportadasDe(User $user): \Illuminate\Support\Collection
    {
        return Importacion::whereIn('estado_match', ['sin_match', 'vinculado'])
            ->where(fn ($q) => $q->where('user_id', $user->id)->when(filled($user->dni), fn ($q) => $q->orWhere('dni', $user->dni)))
            ->orderBy('created_at')
            ->get()
            ->reject(fn (Importacion $imp) => NombresImportados::esFilaDeEncabezado($imp->nombre_estudiante, $imp->dni))
            ->map(fn (Importacion $i) => [
                'origen' => $i->origen,
                'archivo' => $i->archivo_nombre,
                'fecha' => $i->created_at->format('d/m/Y H:i'),
                'datos' => collect($i->datos_normalizados ?? [])
                    ->except(['nombre_estudiante', 'dni'])
                    ->filter(fn ($v) => $v !== '' && $v !== null)
                    ->all(),
            ])
            ->values();
    }

    public function actualizarEstado(Request $request, User $egresado)
    {
        // El estado de titulación ya no se elige: se calcula de PTI/TI/Tesis/RD
        // (SeguimientoTitulacion::recalcularEstado). Aquí solo datos del expediente.
        $data = $request->validate([
            'observaciones' => 'nullable|string',
            'numero_expediente' => 'nullable|string|max:40',
            'periodo_estudios' => 'nullable|string|max:40',
        ]);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();

        // Se acepta como lo escriban ("N°27-T/PUK", "0027-T/PUK") y se guarda
        // normalizado; un mismo número no puede quedar en dos personas.
        if (filled($data['numero_expediente'] ?? null)) {
            $normalizado = Expediente::normalizar($data['numero_expediente']);
            if (! $normalizado) {
                return back()->withInput()->withErrors(['numero_expediente' => 'El N° de expediente debe tener la forma 0001-T/PUK (o 0001-T/PUK-PPD).']);
            }
            $otro = SeguimientoTitulacion::where('numero_expediente', $normalizado)->where('id', '!=', $seguimiento->id)->with('user')->first();
            if ($otro) {
                return back()->withInput()->withErrors(['numero_expediente' => "El expediente {$normalizado} ya está asignado a {$otro->user?->apellidos}, {$otro->user?->name}."]);
            }
            $data['numero_expediente'] = $normalizado;
        } else {
            $data['numero_expediente'] = null;
        }

        $seguimiento->update($data);

        return $this->volverAlAcordeon($egresado->id, 'Expediente actualizado.');
    }

    public function ptiGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'pti_id' => 'nullable|integer',
            'etapa' => 'nullable|in:'.implode(',', array_keys(Pti::ETAPAS)),
            'titulo' => 'nullable|string',
            'asesor_id' => self::REGLA_ASESOR,
            'fecha_pti' => 'nullable|date',
            'rd_pti' => 'nullable|string|max:255',
            'link_iti' => 'nullable|string|max:500',
            'estado' => 'nullable|in:vencido',
            'fecha_vencimiento' => 'nullable|date',
            'rd_ampliacion' => 'nullable|string|max:255',
            'fecha_ampliacion' => 'nullable|date',
            'vencimiento_ampliado' => 'nullable|date|required_with:rd_ampliacion',
        ], [
            'vencimiento_ampliado.required_with' => 'Indique el nuevo vencimiento que otorga la RD de ampliación.',
        ]);
        $data = \App\Services\Titulacion\AreasTitulacion::sinCamposDeRd($data);

        // Aprobado = tiene RD (lo pone recalcularEstado al guardar); a mano
        // solo se puede marcar "vencido".
        $data['estado'] = ($data['estado'] ?? null) === 'vencido' ? 'vencido' : 'pendiente';
        $data = array_merge($data, Pti::columnasAsesor($data['asesor_id'] ?? null));
        $this->verificarAsesorExterno($data['asesor_externo_id']);
        $ptiId = $data['pti_id'] ?? null;
        unset($data['pti_id']);
        $data['etapa'] ??= 'bachiller';

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        $pti = $ptiId
            ? $seguimiento->ptis()->findOrFail($ptiId)
            : ($seguimiento->pti ?? new Pti(['seguimiento_id' => $seguimiento->id, 'numero' => 1]));
        // PTI aprobado (con RD): sus datos ya no se editan (el título cambia con la RD de cambio de
        // tema y el asesor con la de cambio de asesor). Solo se registra su ampliación o se marca vencido.
        $aprobado = $pti->exists && (filled($pti->rd_pti)
            || ($pti->id === $seguimiento->pti?->id && $seguimiento->avance()->hitos['pti_aprobado']['logrado']));
        if ($aprobado) {
            $data = array_intersect_key($data, array_flip(['estado', 'rd_ampliacion', 'fecha_ampliacion', 'vencimiento_ampliado']));
        }
        $pti->fill($data)->save();
        $this->registrarRdAmpliacionPti($seguimiento, $pti);

        return $this->volverAlAcordeon($egresado->id, 'PTI N° '.$pti->numero.' guardado.', 'pti');
    }

    /**
     * Registra un PTI nuevo cuando el anterior venció. Sin TI aprobado el
     * proceso vuelve a empezar (PTI de Bachiller); con TI aprobado, el nuevo
     * PTI es para la Licenciatura. El anterior queda en el historial como
     * vencido.
     */
    public function ptiNuevo(Request $request, User $egresado)
    {
        $data = $request->validate([
            'etapa' => 'required|in:'.implode(',', array_keys(Pti::ETAPAS)),
            'titulo' => 'nullable|string',
            'asesor_id' => self::REGLA_ASESOR,
            'fecha_pti' => 'nullable|date',
            'rd_pti' => 'nullable|string|max:255',
            'fecha_vencimiento' => 'nullable|date',
        ]);
        $data = \App\Services\Titulacion\AreasTitulacion::sinCamposDeRd($data);
        $data = array_merge($data, Pti::columnasAsesor($data['asesor_id'] ?? null));
        $this->verificarAsesorExterno($data['asesor_externo_id']);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();

        DB::transaction(function () use ($seguimiento, $data) {
            $anterior = $seguimiento->pti;
            if ($anterior && $anterior->estado !== 'vencido') {
                $anterior->update(['estado' => 'vencido']);
            }
            $seguimiento->ptis()->create($data + [
                'numero' => (int) $seguimiento->ptis()->withTrashed()->max('numero') + 1,
                'estado' => 'pendiente',
            ]);
        });

        return $this->volverAlAcordeon($egresado->id, 'Nuevo PTI ('.Pti::ETAPAS[$data['etapa']].') registrado; el anterior queda en el historial.', 'pti');
    }

    /**
     * Registra una ampliación del PTI vigente: es una RD NUEVA (no reemplaza la de aprobación ni las
     * ampliaciones anteriores) y su vencimiento pasa a ser el del PTI. Con "aplicar al grupo", también
     * para los compañeros (la misma RD y el mismo PDF).
     */
    public function ptiAmpliacion(Request $request, User $egresado)
    {
        $data = $request->validate([
            'numero_rd' => 'required|string|max:255',
            'fecha' => 'nullable|date',
            'vencimiento_ampliado' => 'required|date',
            'pdf' => 'nullable|file|mimes:pdf|max:10240',
            'archivo' => 'nullable|url|max:500',
        ], [
            'vencimiento_ampliado.required' => 'Indique el nuevo vencimiento que otorga la RD de ampliación.',
            'archivo.url' => 'El enlace debe ser completo, por ejemplo https://drive.google.com/…',
        ], ['numero_rd' => 'N° de RD', 'pdf' => 'PDF de la RD']);

        $seguimiento = SeguimientoTitulacion::with(['pti', 'resoluciones'])->where('user_id', $egresado->id)->firstOrFail();
        abort_unless($seguimiento->pti, 404);
        $archivo = $request->hasFile('pdf')
            ? \App\Services\Titulacion\ArchivosRd::guardarSubido($request->file('pdf'))
            : ($data['archivo'] ?? null);

        $aplicados = 0;
        $omitidos = [];
        DB::transaction(function () use ($seguimiento, $request, $data, $archivo, &$aplicados, &$omitidos) {
            $this->registrarAmpliacion($seguimiento, $data, $archivo);
            foreach ($this->companerosDestino($seguimiento, $request) as $otro) {
                if (! $otro->pti) {
                    $omitidos[] = $otro->user?->apellidos.' (sin PTI)';
                    continue;
                }
                $this->registrarAmpliacion($otro, $data, $archivo);
                $aplicados++;
            }
        });

        return $this->volverAlAcordeon($egresado->id, 'Ampliación del PTI (RD '.$data['numero_rd'].') registrada'.$this->textoGrupo($aplicados, $omitidos), 'pti');
    }

    /** Una ampliación en un seguimiento: RD nueva (si esa RD no estaba ya) + nuevo vencimiento del PTI. */
    private function registrarAmpliacion(SeguimientoTitulacion $seguimiento, array $data, ?string $archivo): void
    {
        $pti = $seguimiento->pti;
        $pti->update([
            'rd_ampliacion' => $data['numero_rd'],
            'fecha_ampliacion' => $data['fecha'] ?? null,
            'vencimiento_ampliado' => $data['vencimiento_ampliado'],
        ]);
        $clave = GrupoTitulacion::claveRd($data['numero_rd']);
        $existente = $seguimiento->resoluciones()->where('tipo', 'AmpliacionPti')->get()
            ->first(fn ($r) => GrupoTitulacion::claveRd($r->numero_rd) === $clave);
        $detalle = 'PTI N° '.$pti->numero.' ampliado hasta el '.\Carbon\Carbon::parse($data['vencimiento_ampliado'])->format('d/m/Y');
        if ($existente) {
            $existente->update(array_filter(['fecha' => $data['fecha'] ?? null, 'archivo' => $archivo, 'detalle' => $detalle]));

            return;
        }
        $seguimiento->resoluciones()->create([
            'tipo' => 'AmpliacionPti',
            'numero_rd' => $data['numero_rd'],
            'fecha' => $data['fecha'] ?? null,
            'archivo' => $archivo,
            'detalle' => $detalle,
        ]);
    }

    /** La ampliación del PTI también figura como RD en la línea de tiempo. */
    private function registrarRdAmpliacionPti(SeguimientoTitulacion $seguimiento, Pti $pti): void
    {
        if (blank($pti->rd_ampliacion)) {
            return;
        }
        $existe = $seguimiento->resoluciones()->where('tipo', 'AmpliacionPti')
            ->where('numero_rd', $pti->rd_ampliacion)->exists();
        if (! $existe) {
            $seguimiento->resoluciones()->create([
                'tipo' => 'AmpliacionPti',
                'numero_rd' => $pti->rd_ampliacion,
                'fecha' => $pti->fecha_ampliacion,
                'detalle' => 'PTI N° '.$pti->numero.' ampliado hasta el '.$pti->vencimiento_ampliado?->format('d/m/Y'),
            ]);
        }
    }

    /** Valor de un select de asesor/jurado: id de docente ("12") o del catálogo ("a5"). */
    protected const REGLA_ASESOR = ['nullable', 'regex:/^a?\d+$/'];

    protected function verificarAsesorExterno(?int $id): void
    {
        abort_if($id && ! Asesor::whereKey($id)->exists(), 422, 'El asesor elegido ya no existe.');
    }

    public function tesisGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'titulo' => 'nullable|string',
            'asesor_id' => self::REGLA_ASESOR,
            'fecha_exposicion' => 'nullable|date',
            'rd_ti' => 'nullable|string|max:255',
            'bachiller_sunedu' => 'nullable|string|max:50',
            'fecha_bachiller_sunedu' => 'nullable|date',
            'rd_extension' => 'nullable|string|max:255',
            'fecha_extension' => 'nullable|date',
            'fecha_sustentacion' => 'nullable|date',
            'rd_tesis' => 'nullable|string|max:255',
            'titulo_sunedu' => 'nullable|string|max:50',
            'fecha_titulo_sunedu' => 'nullable|date',
            'url_repositorio' => 'nullable|string|max:500',
            'url_repositorio_ti' => 'nullable|string|max:500',
            // 'aprobado' solo lo traen registros antiguos.
            'estado' => 'nullable|in:'.implode(',', array_keys(Tesis::SITUACIONES)).',aprobado',
            'enlaces_dictamen' => 'nullable|array|max:10',
            'enlaces_dictamen.*' => 'nullable|string|max:500',
            'link_tesis' => 'nullable|string|max:500',
            'link_ti' => 'nullable|url|max:500',
            'link_ti_estado' => 'nullable|in:'.implode(',', array_keys(Tesis::ESTADOS_ENLACE)),
            'link_ti_enviado_en' => 'nullable|date',
            'link_ti_aprobado_en' => 'nullable|date',
            'link_tesis_estado' => 'nullable|in:'.implode(',', array_keys(Tesis::ESTADOS_ENLACE)),
            'link_tesis_enviado_en' => 'nullable|date',
            'link_tesis_aprobado_en' => 'nullable|date',
            'licenciatura_escuela' => 'nullable|boolean',
            'fecha_licenciatura_escuela' => 'nullable|date',
            'licenciado_sunedu' => 'nullable|in:si,pendiente',
        ], [
            'link_ti.url' => 'El enlace del TI debe ser un enlace completo, por ejemplo https://drive.google.com/…',
        ]);
        $data = \App\Services\Titulacion\AreasTitulacion::sinCamposDeRd($data);

        // Pasos de la Tesis con reglas propias (ver _proceso):
        // enlaces de los dictámenes (uno o más; el formulario puede quedar vacío para quitarlos todos),
        if ($request->boolean('enlaces_dictamen_form')) {
            $enlaces = collect($data['enlaces_dictamen'] ?? [])->map(fn ($e) => trim((string) $e))->filter()->unique()->values()->all();
            $data['enlaces_dictamen'] = $enlaces ?: null;
        }
        // la casilla «aceptada por la Escuela» (sin fecha, hoy; al desmarcarla se borra la fecha)
        if ($request->exists('licenciatura_escuela')) {
            $data['licenciatura_escuela'] = $request->boolean('licenciatura_escuela');
            $data['fecha_licenciatura_escuela'] = $data['licenciatura_escuela'] ? ($data['fecha_licenciatura_escuela'] ?? now()->toDateString()) : null;
        }
        // y «Licenciado en SUNEDU: Sí / Pendiente» (Sí sin N° de registro queda como «Sí»).
        if (array_key_exists('licenciado_sunedu', $data)) {
            $actualSunedu = SeguimientoTitulacion::where('user_id', $egresado->id)->first()?->tesis?->titulo_sunedu;
            if ($data['licenciado_sunedu'] === 'pendiente') {
                $data['titulo_sunedu'] = null;
                $data['fecha_titulo_sunedu'] = null;
            } elseif ($data['licenciado_sunedu'] === 'si') {
                $data['titulo_sunedu'] = filled($data['titulo_sunedu'] ?? null) ? $data['titulo_sunedu'] : ($actualSunedu ?: 'Sí');
            }
            unset($data['licenciado_sunedu']);
        }
        // Enlace del TI / de la Tesis (paso «Enlace»): el estudiante lo envía y el asesor lo aprueba.
        // Las fechas se ponen solas (hoy) si no vienen; sin enlace se borran estado y fechas.
        $tesisActual = SeguimientoTitulacion::where('user_id', $egresado->id)->first()?->tesis;
        foreach (['ti', 'tesis'] as $et) {
            if (! $request->exists("link_{$et}_estado")) {
                continue;
            }
            $hoy = now()->toDateString();
            if (blank($data["link_{$et}"] ?? null)) {
                $data["link_{$et}"] = null;
                $data["link_{$et}_estado"] = $data["link_{$et}_enviado_en"] = $data["link_{$et}_aprobado_en"] = null;
                continue;
            }
            $aprobado = ($data["link_{$et}_estado"] ?? null) === 'aprobado';
            $data["link_{$et}_estado"] = $aprobado ? 'aprobado' : 'enviado';
            $data["link_{$et}_enviado_en"] = ($data["link_{$et}_enviado_en"] ?? null) ?: ($tesisActual?->{"link_{$et}_enviado_en"}?->toDateString() ?? $hoy);
            $data["link_{$et}_aprobado_en"] = $aprobado
                ? (($data["link_{$et}_aprobado_en"] ?? null) ?: ($tesisActual?->{"link_{$et}_aprobado_en"}?->toDateString() ?? $hoy))
                : null;
        }
        // Las acciones cortas de cada etapa ("Registrar exposición", "Registrar Bachiller"…)
        // mandan solo sus campos: se actualiza lo enviado y el asesor solo si vino en el form.
        if ($request->exists('asesor_id')) {
            $data = array_merge($data, Tesis::columnasAsesor($data['asesor_id'] ?? null));
            $this->verificarAsesorExterno($data['asesor_externo_id']);
        }

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        // Orden del proceso: no se registra un paso antes que el anterior (ver ReglasProceso).
        // Lo que se quita (casilla desmarcada, enlaces vacíos) no pide requisitos.
        $paraValidar = collect($data)->except(array_merge(['enlaces_dictamen'], empty($data['licenciatura_escuela']) ? ['licenciatura_escuela', 'fecha_licenciatura_escuela'] : []))->all();
        $motivo = \App\Services\Titulacion\ReglasProceso::validarTesis($seguimiento, $paraValidar);
        if (! $motivo && ! empty($data['enlaces_dictamen']) && ($falta = \App\Services\Titulacion\ReglasProceso::falta($seguimiento->avance(), 'bachiller'))) {
            $motivo = 'Todavía no se pueden registrar los dictámenes: primero debe estar registrado «'.$falta.'».';
        }
        if ($motivo) {
            return $this->volverAlAcordeon($egresado->id, $motivo, 'proceso', 'error');
        }
        Tesis::updateOrCreate(['seguimiento_id' => $seguimiento->id], $data);

        // "Aplicar también al grupo" (acciones cortas): el mismo paso a cada integrante que esté en
        // condiciones; a quien le falte un paso anterior se le omite y se avisa.
        [$aplicados, $omitidos] = [0, []];
        foreach ($this->companerosDestino($seguimiento, $request) as $otro) {
            if ($motivoOtro = \App\Services\Titulacion\ReglasProceso::validarTesis($otro, $paraValidar)) {
                $omitidos[] = $otro->user?->apellidos.' ('.$motivoOtro.')';
                continue;
            }
            Tesis::updateOrCreate(['seguimiento_id' => $otro->id], $data);
            $aplicados++;
        }

        $mensaje = $request->input('mensaje_ok') === 'paso' ? 'Paso registrado' : 'TI/Tesis guardado';

        return $this->volverAlAcordeon($egresado->id, $mensaje.$this->textoGrupo($aplicados, $omitidos), 'tesis', $omitidos ? 'warning' : 'success');
    }

    /** Seguimientos de los compañeros de grupo si el formulario pidió "aplicar también al grupo". */
    protected function companerosDestino(SeguimientoTitulacion $seguimiento, Request $request): \Illuminate\Support\Collection
    {
        if (! $request->boolean('aplicar_grupo')) {
            return collect();
        }
        $seguimiento->loadMissing('actas', 'resoluciones');
        $ids = GrupoTitulacion::companeros($seguimiento)->pluck('id');

        return SeguimientoTitulacion::with(['user', 'pti', 'ptis', 'tesis', 'actas', 'resoluciones', 'participantes'])
            ->whereIn('user_id', $ids)->get();
    }

    /** ", también para 2 integrantes del grupo" (+ a quiénes se omitió). */
    protected function textoGrupo(int $aplicados, array $omitidos): string
    {
        $texto = $aplicados ? ', también para '.$aplicados.' integrante'.($aplicados > 1 ? 's' : '').' del grupo' : '';
        if ($omitidos) {
            $texto .= '. No se aplicó a: '.implode('; ', $omitidos);
        }

        return $texto.'.';
    }

    public function actaGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'tipo' => 'required|in:expo,tesis',
            'numero_acta' => 'nullable|string|max:20',
            'fecha' => 'nullable|date',
            'promedio' => 'nullable|numeric|min:0|max:20',
            'titulo_trabajo' => 'nullable|string',
            'asesor_id' => self::REGLA_ASESOR,
        ]);
        $data = array_merge($data, Acta::columnasAsesor($data['asesor_id'] ?? null));
        $this->verificarAsesorExterno($data['asesor_externo_id']);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        if ($motivo = \App\Services\Titulacion\ReglasProceso::validarActa($seguimiento, $data['tipo'])) {
            return $this->volverAlAcordeon($egresado->id, $motivo, 'proceso', 'error');
        }
        $seguimiento->actas()->create($data);

        return $this->volverAlAcordeon($egresado->id, 'Acta agregada.', 'actas');
    }

    public function actaActualizar(Request $request, Acta $acta)
    {
        $data = $request->validate([
            'tipo' => 'required|in:expo,tesis',
            'numero_acta' => 'nullable|string|max:20',
            'fecha' => 'nullable|date',
            'promedio' => 'nullable|numeric|min:0|max:20',
            'titulo_trabajo' => 'nullable|string',
            'asesor_id' => self::REGLA_ASESOR,
        ]);
        $data = array_merge($data, Acta::columnasAsesor($data['asesor_id'] ?? null));
        $this->verificarAsesorExterno($data['asesor_externo_id']);

        $acta->update($data);

        return $this->volverAlAcordeon($acta->seguimiento->user_id, 'Acta actualizada.', 'actas');
    }

    public function actaEliminar(Acta $acta)
    {
        $userId = $acta->seguimiento->user_id;
        $acta->delete();
        return $this->volverAlAcordeon($userId, 'Acta eliminada.', 'actas');
    }

    public function juradoGuardar(Request $request, Acta $acta)
    {
        $data = $request->validate([
            'docente_id' => ['required', 'regex:/^a?\d+$/'],
            'rol' => 'required|string|max:20',
        ]);
        $data = array_merge($data, Jurado::columnasAsesor($data['docente_id'], 'docente_id'));
        $this->verificarAsesorExterno($data['asesor_externo_id']);

        $acta->jurados()->create($data);

        return $this->volverAlAcordeon($acta->seguimiento->user_id, 'Jurado agregado.', 'actas');
    }

    public function juradoEliminar(Jurado $jurado)
    {
        $userId = $jurado->acta->seguimiento->user_id;
        $jurado->delete();
        return $this->volverAlAcordeon($userId, 'Jurado eliminado.', 'actas');
    }

    public function resolucionGuardar(Request $request, User $egresado)
    {
        $data = $this->datosResolucion($request);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        if ($motivo = \App\Services\Titulacion\ReglasProceso::validarRd($seguimiento, $data['tipo'])) {
            return $this->volverAlAcordeon($egresado->id, $motivo, 'proceso', 'error');
        }
        $seguimiento->resoluciones()->create($data);

        return $this->volverAlAcordeon($egresado->id, 'Resolución agregada.', 'resoluciones');
    }

    public function resolucionActualizar(Request $request, Resolucion $resolucion)
    {
        $resolucion->update($this->datosResolucion($request));

        return $this->volverAlAcordeon($resolucion->seguimiento->user_id, 'Resolución actualizada.', 'resoluciones');
    }

    /** Datos validados de una RD; si se sube un PDF, reemplaza al enlace (ver ArchivosRd). */
    private function datosResolucion(Request $request): array
    {
        $data = $request->validate($this->reglasResolucion() + ['pdf' => 'nullable|file|mimes:pdf|max:10240'], [], ['pdf' => 'PDF de la RD']);
        if ($request->hasFile('pdf')) {
            $data['archivo'] = \App\Services\Titulacion\ArchivosRd::guardarSubido($request->file('pdf'));
        }
        unset($data['pdf']);

        return $data;
    }

    private function reglasResolucion(): array
    {
        return [
            'tipo' => 'required|in:'.implode(',', array_keys(Resolucion::TIPOS)),
            'numero_rd' => 'required|string|max:255',
            'fecha' => 'nullable|date',
            'archivo' => 'nullable|url|max:500',
            'detalle' => 'nullable|string|max:500',
        ];
    }

    // ── Catálogo de asesores de Titulación ──────────────────────────────

    public function asesoresIndex()
    {
        $asesores = Asesor::orderBy('apellidos')->orderBy('nombres')->get();
        $participaciones = \App\Services\Titulacion\ParticipacionAsesores::porAsesor();

        return view('titulacion.asesores', compact('asesores', 'participaciones'));
    }

    /** Alta desde la página del catálogo o desde la ficha (vuelve a la pestaña). */
    public function asesorGuardar(Request $request)
    {
        $data = $request->validate($this->reglasAsesor());
        $asesor = Asesor::create($data + ['activo' => true, 'created_by' => auth()->id()]);
        $mensaje = 'Asesor(a) '.$asesor->nombreCompleto().' agregado(a). Ya aparece en las listas de asesor y jurado.';

        if ($request->filled('volver_a')) {
            return $this->volverAlAcordeon((int) $request->input('volver_a'), $mensaje, $request->input('tab', 'pti'));
        }

        return redirect()->route('titulacion.asesores.index')->with('success', $mensaje);
    }

    public function asesorActualizar(Request $request, Asesor $asesor)
    {
        $data = $request->validate($this->reglasAsesor() + ['activo' => 'nullable|boolean']);
        $asesor->update($data + ['activo' => $request->boolean('activo'), 'updated_by' => auth()->id()]);

        return redirect()->route('titulacion.asesores.index')->with('success', 'Datos de '.$asesor->nombreCompleto().' actualizados.');
    }

    private function reglasAsesor(): array
    {
        return [
            'nombres' => 'required|string|max:120',
            'apellidos' => 'required|string|max:120',
            'dni' => 'nullable|string|max:15',
            'email' => 'nullable|email|max:150',
            'telefono' => 'nullable|string|max:30',
            'institucion' => 'nullable|string|max:150',
        ];
    }

    /** Las RD emitidas no vencen ni se eliminan (Unidad de Investigación, 30/10): solo se corrigen. */
    public function resolucionEliminar(Resolucion $resolucion)
    {
        return $this->volverAlAcordeon($resolucion->seguimiento->user_id,
            'Las resoluciones no se eliminan: corrija su número, fecha o PDF desde «Editar».', 'resoluciones', 'error');
    }

    /**
     * Envía el aviso de vencimiento del PTI (recordatorio o urgente) a la
     * persona y, si se marcaron, a los integrantes de su grupo: un correo
     * PERSONAL por cada uno (su nombre, sus fechas; nadie ve el correo del
     * otro). Cada envío queda en titulacion_avisos_pti, salga bien o no.
     */
    public function avisoPtiEnviar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'tipo' => 'required|in:recordatorio,urgente',
            'integrantes' => 'nullable|array',
            'integrantes.*' => 'integer|exists:users,id',
        ]);

        $ids = collect([$egresado->id])->merge($data['integrantes'] ?? [])->unique();
        $enviados = [];
        $omitidos = [];

        foreach (User::with(['seguimientoTitulacion.pti', 'alumno'])->whereIn('id', $ids)->get() as $persona) {
            $nombre = $persona->apellidos.', '.$persona->name;
            $seguimiento = $persona->seguimientoTitulacion;
            $vigencia = new VigenciaPti($seguimiento?->pti);

            $motivo = match (true) {
                ! $seguimiento?->pti => 'no tiene PTI registrado',
                ! $vigencia->conocida() => 'su PTI no tiene fecha de inscripción ni de vencimiento',
                $vigencia->vencido() => 'su PTI ya venció ('.$vigencia->vencimiento->format('d/m/Y').')',
                blank($persona->email) => 'no tiene correo',
                default => null,
            };
            if ($motivo) {
                $omitidos[] = "{$nombre}: {$motivo}";
                continue;
            }

            $error = null;
            try {
                Mail::mailer(\App\Mail\Titulacion\Remitente::mailer('investigacion'))->to($persona->email)->send(new AvisoVencimientoPtiMail($persona, $vigencia, $data['tipo']));
                $enviados[] = $nombre;
            } catch (\Throwable $e) {
                $error = $e->getMessage();
                $omitidos[] = "{$nombre}: falló el envío ({$error})";
            }

            AvisoPti::create([
                'seguimiento_id' => $seguimiento->id,
                'tipo' => $data['tipo'],
                'email' => $persona->email,
                'fecha_vencimiento' => $vigencia->vencimiento,
                'enviado' => $error === null,
                'error' => $error,
            ]);
        }

        $mensaje = ($enviados ? 'Aviso '.($data['tipo'] === 'urgente' ? 'URGENTE' : 'de recordatorio').' enviado a: '.implode('; ', $enviados).'.' : 'No se envió ningún correo.')
            .($omitidos ? ' No enviado a — '.implode(' | ', $omitidos) : '');

        return $this->volverAlAcordeon($egresado->id, $mensaje, 'pti', $enviados ? 'success' : 'error');
    }

    /** Vista previa del correo tal como le llegaría a esta persona. */
    public function avisoPtiVista(User $egresado, string $tipo)
    {
        abort_unless(in_array($tipo, ['recordatorio', 'urgente'], true), 404);
        $egresado->load(['seguimientoTitulacion.pti', 'alumno']);
        $vigencia = new VigenciaPti($egresado->seguimientoTitulacion?->pti);
        abort_unless($vigencia->conocida(), 404, 'El PTI no tiene fecha de inscripción ni de vencimiento.');

        return new AvisoVencimientoPtiMail($egresado, $vigencia, $tipo);
    }

    public function documentoGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'tipo' => 'required|string|max:50',
            'url' => 'nullable|string|max:500|required_without:archivo',
            'archivo' => 'nullable|file|mimes:pdf|max:20480',
            'descripcion' => 'nullable|string|max:255',
        ], [
            'url.required_without' => 'Pegue un enlace o suba el PDF del documento.',
            'archivo.mimes' => 'El documento debe ser un PDF.',
            'archivo.max' => 'El PDF no puede pesar más de 20 MB.',
        ]);
        // El PDF se guarda igual que las RD (privado, se abre con la misma ruta protegida).
        if ($request->hasFile('archivo')) {
            $data['url'] = \App\Services\Titulacion\ArchivosRd::guardarSubido($request->file('archivo'));
        }
        unset($data['archivo']);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        $seguimiento->documentos()->create($data);

        return $this->volverAlAcordeon($egresado->id, 'Documento agregado.', 'resoluciones');
    }

    public function documentoEliminar(Documento $documento)
    {
        $userId = $documento->seguimiento->user_id;
        $documento->delete();
        return $this->volverAlAcordeon($userId, 'Documento eliminado.', 'resoluciones');
    }

    /**
     * Todas las acciones internas del detalle de un egresado (guardar PTI,
     * agregar jurado, etc.) regresan aquí en vez de a una página propia —
     * `/titulacion?ver={id}` hace que el índice reabra automáticamente el
     * acordeón de esa fila ya con los datos actualizados (ver script en
     * titulacion/index.blade.php), para que nunca se sienta como "navegar a
     * otra vista".
     */
    /**
     * Vuelve al listado con la ficha de la persona abierta (?ver=) en la pestaña
     * donde se trabajaba (?tab=), conservando los filtros que tenía el listado.
     * La página la elige index() (la que contiene a la persona).
     */
    protected function volverAlAcordeon(int $userId, string $mensaje, string $tab = 'resumen', string $flash = 'success')
    {
        // Solo si se venía del listado (se compara por nombre de ruta, no por
        // path, para no depender de la base de la URL: /Puklla2026/public/…).
        $filtros = [];
        try {
            $previa = Request::create(url()->previous());
            if (app('router')->getRoutes()->match($previa)->getName() === 'titulacion.index') {
                $filtros = $previa->query();
            }
        } catch (\Throwable) {
            // URL previa que no es de esta app: se vuelve sin filtros
        }
        unset($filtros['page'], $filtros['ver'], $filtros['tab'], $filtros['paso']);
        // Paso de la línea de tiempo desde el que se guardó (ver _proceso): se vuelve a él.
        if (preg_match('/^[a-z_]{3,30}$/', (string) request('volver_paso'))) {
            $filtros['paso'] = request('volver_paso');
        }

        return redirect()->route('titulacion.index', $filtros + ['ver' => $userId, 'tab' => $tab])->with($flash, $mensaje);
    }


    /**
     * Formulario para crear la cuenta (User + ficha ppds/alumno) de una
     * persona que el importador no encontró en el sistema (estado_match
     * = sin_match). El nombre no se separa nombres/apellidos automático
     * cuando el archivo trae "APELLIDOS Y NOMBRES" en un solo campo (ej.
     * exp-ppd-marilyn) — eso es ambiguo en español, se deja que el
     * gestor lo confirme/corrija a mano en vez de adivinar mal el nombre
     * legal de alguien.
     */
    public function importacionCrearCuentaForm(Importacion $importacion)
    {
        abort_if($importacion->user_id, 404, 'Esta fila ya está vinculada a un usuario.');

        $datos = $importacion->datos_normalizados ?? [];
        $nombresPrefill = $datos['nombres'] ?? '';
        $apellidosPrefill = $datos['apellidos'] ?? ($importacion->nombre_estudiante ?? '');

        $programas = Programa::orderBy('nombre')->get();

        return view('titulacion.importaciones-crear-cuenta', compact(
            'importacion', 'nombresPrefill', 'apellidosPrefill', 'programas'
        ));
    }

    public function importacionCrearCuentaGuardar(Request $request, Importacion $importacion)
    {
        abort_if($importacion->user_id, 404, 'Esta fila ya está vinculada a un usuario.');

        $data = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:20|unique:users,dni',
            'email' => 'required|email|max:255|unique:users,email',
            'programa_id' => 'required|exists:programas,id',
            'anio_ingreso' => 'nullable|string|max:10',
            'tipo' => 'required|in:fid,ppd',
        ]);

        $user = User::create([
            'name' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'dni' => $data['dni'],
            'email' => $data['email'],
            'condicion' => 'Egresado',
            'password' => Hash::make($data['dni']),
        ]);

        $this->crearFichaEgresado($user, $data);

        $importacion->update(['user_id' => $user->id, 'estado_match' => 'vinculado']);

        return redirect()->route('titulacion.index', ['ver' => $user->id])
            ->with('success', 'Cuenta creada para '.$data['apellidos'].', '.$data['nombres'].'. Contraseña inicial: su DNI.');
    }

    /**
     * Vista previa de creación masiva de cuentas: solo se ofrecen los
     * pendientes "elegibles" (nombre separado con certeza + correo +
     * programa identificado — ver pendientesAgrupados()/resolverProgramaId()).
     * Nunca crea nada por sí sola; siempre pasa por esta confirmación antes
     * de escribir en la BD real, porque son cuentas y contraseñas de
     * personas reales.
     */
    public function crearCuentasMasivoPreview()
    {
        $pendientes = $this->pendientesAgrupados();
        $elegibles = $pendientes->where('elegible_masivo', true)->where('user_id', null)->where('cuenta_por_dni', null)->values();
        $excluidos = $pendientes->where('elegible_masivo', false)->values();

        return view('titulacion.crear-cuentas-masivo', compact('elegibles', 'excluidos'));
    }

    /**
     * Crea, de un solo golpe, la cuenta+ficha de todos los pendientes
     * elegibles marcados en el formulario de vista previa. Cada uno se crea
     * en su propia transacción (si uno falla por algo imprevisto —p.ej. un
     * DNI que se volvió duplicado entre el preview y el submit—, no tumba
     * a los demás) y se reporta un resumen al final.
     */
    public function crearCuentasMasivoEjecutar(Request $request)
    {
        $data = $request->validate([
            'importacion_ids' => 'required|array|min:1',
            'importacion_ids.*' => 'integer|exists:titulacion_importaciones,id',
        ]);

        // Se reconstruye la lista de elegibles desde cero (no se confía en
        // datos ocultos del form) y se cruza contra lo que el gestor marcó.
        $elegibles = $this->pendientesAgrupados()
            ->where('elegible_masivo', true)
            ->where('user_id', null)
            ->where('cuenta_por_dni', null)
            ->whereIn('importacion_id', $data['importacion_ids']);

        $creadas = 0;
        $fallidas = [];

        foreach ($elegibles as $grupo) {
            DB::beginTransaction();
            try {
                $user = User::create([
                    'name' => $grupo['nombres'],
                    'apellidos' => $grupo['apellidos'],
                    'dni' => $grupo['dni'],
                    'email' => $grupo['email'],
                    'condicion' => 'Egresado',
                    'password' => Hash::make($grupo['dni']),
                ]);

                $this->crearFichaEgresado($user, [
                    'nombres' => $grupo['nombres'],
                    'apellidos' => $grupo['apellidos'],
                    'dni' => $grupo['dni'],
                    'email' => $grupo['email'],
                    'programa_id' => $grupo['programa_id'],
                    'anio_ingreso' => $grupo['anio_ingreso'],
                    'tipo' => $grupo['tipo'],
                ]);

                Importacion::where('dni', $grupo['dni'])
                    ->whereIn('estado_match', ['sin_match', 'vinculado'])
                    ->update(['user_id' => $user->id, 'estado_match' => 'vinculado']);

                DB::commit();
                $creadas++;
            } catch (\Throwable $e) {
                DB::rollBack();
                $fallidas[] = ($grupo['nombre'] ?? $grupo['dni']).': '.$e->getMessage();
            }
        }

        $mensaje = "{$creadas} cuenta(s) creada(s). Contraseña inicial de todas: su DNI.";
        if (! empty($fallidas)) {
            $mensaje .= ' '.count($fallidas).' fallaron: '.implode(' | ', $fallidas);
        }

        return redirect()->route('titulacion.index')->with(count($fallidas) ? 'error' : 'success', $mensaje);
    }

    /**
     * Formulario para "completar ficha" — el caso de alguien que YA tiene
     * cuenta en el sistema (ej. hoy es docente/tutor) pero cuyo historial
     * como alumno FID/PPD nunca se digitalizó: el importador lo encontró
     * por `users.dni` (estado_match=vinculado) pero no tiene fila en
     * ppds/alumnos. A diferencia de "crear cuenta", aquí NO se toca el
     * login/correo/contraseña actual de la persona — solo se le agrega el
     * perfil de egresado encima del que ya tiene.
     */
    public function importacionCompletarFichaForm(Importacion $importacion)
    {
        abort_unless($importacion->user_id, 404, 'Esta fila todavía no está vinculada a ningún usuario.');
        $user = User::findOrFail($importacion->user_id);
        abort_if($user->condicion === 'Egresado', 404, 'Esta persona ya tiene su ficha de egresado completa.');

        $datos = $importacion->datos_normalizados ?? [];
        $nombresPrefill = $datos['nombres'] ?? $user->name;
        $apellidosPrefill = $datos['apellidos'] ?? $user->apellidos;

        $programas = Programa::orderBy('nombre')->get();
        $tipo = str_contains($importacion->origen, 'ppd') ? 'ppd' : 'fid';
        $programaSugerido = filled($datos['programa'] ?? null) ? $this->resolverProgramaId($datos['programa'], $tipo) : null;
        // Cuenta de trabajo (personal de la escuela, sin rol de estudiante): se ofrece crearle aparte su cuenta de egresado(a).
        $esCuentaDeTrabajo = self::esCuentaDeTrabajo($user);
        $correoSugerido = $esCuentaDeTrabajo
            ? self::correoInstitucional($nombresPrefill, $apellidosPrefill, $programas->firstWhere('id', $programaSugerido), $tipo, $datos['anio_ingreso'] ?? $datos['periodo'] ?? null)
            : null;

        // ¿Correo del puesto («cobranzas.eesp@») o personal («sofiakancha.iesp@»)? Si no lleva su nombre ni
        // su primer apellido, se recomienda la cuenta propia; si los lleva, completar en la misma.
        $local = strtolower(\Illuminate\Support\Str::ascii(strstr((string) $user->email, '@', true) ?: ''));
        $palabras = collect(preg_split('/\s+/', strtolower(\Illuminate\Support\Str::ascii($nombresPrefill.' '.$apellidosPrefill))))->filter(fn ($p) => strlen($p) >= 4);
        $modoSugerido = $esCuentaDeTrabajo && $palabras->every(fn ($p) => ! str_contains($local, $p)) ? 'cuenta_propia' : 'misma';

        return view('titulacion.importaciones-completar-ficha', compact(
            'importacion', 'user', 'nombresPrefill', 'apellidosPrefill', 'programas', 'tipo', 'programaSugerido', 'esCuentaDeTrabajo', 'correoSugerido', 'modoSugerido'
        ));
    }

    /** Personal de la escuela (admin, docente, tutor…) cuya cuenta no es de estudiante. */
    private static function esCuentaDeTrabajo(User $user): bool
    {
        return ! $user->hasAnyRole(['alumno', 'alumnoB'])
            && $user->hasAnyRole(['super-admin', 'admin', 'adminB', 'docente', 'tutor', 'gestor-titulacion', 'secretaria', 'investigador']);
    }

    /**
     * Correo institucional con la regla de la escuela: FID = primer apellido + primer nombre +
     * «.ini|.eib» + año de ingreso; PPD = primer nombre + primer apellido + «.ppdini|.ppdpri|.ppdeib».
     */
    private static function correoInstitucional(string $nombres, string $apellidos, ?Programa $programa, string $tipo, ?string $periodo): string
    {
        $limpio = fn ($s) => preg_replace('/[^a-z]/', '', strtolower(\Illuminate\Support\Str::ascii((string) $s)));
        $nombre = $limpio(strtok(trim($nombres), ' '));
        $apellido = $limpio(strtok(trim($apellidos), ' '));
        $nivel = $programa?->nivel?->value;
        if ($tipo === 'ppd') {
            $sufijo = 'ppd'.($programa?->eib ? 'eib' : ($nivel === NivelPrograma::INICIAL->value ? 'ini' : 'pri'));

            return "{$nombre}{$apellido}.{$sufijo}@pukllavirtual.edu.pe";
        }
        $anio = preg_match('/(\d{4})/', (string) $periodo, $m) ? $m[1] : '';

        return "{$apellido}{$nombre}.".($programa?->eib ? 'eib' : 'ini')."{$anio}@pukllavirtual.edu.pe";
    }

    public function importacionCompletarFichaGuardar(Request $request, Importacion $importacion)
    {
        abort_unless($importacion->user_id, 404, 'Esta fila todavía no está vinculada a ningún usuario.');
        $user = User::findOrFail($importacion->user_id);
        abort_if($user->condicion === 'Egresado', 404, 'Esta persona ya tiene su ficha de egresado completa.');

        $data = $request->validate([
            'nombres' => 'required|string|max:255',
            'apellidos' => 'required|string|max:255',
            'dni' => 'required|string|max:20',
            'programa_id' => 'required|exists:programas,id',
            'anio_ingreso' => 'nullable|string|max:10',
            'tipo' => 'required|in:fid,ppd',
        ]);

        // El DNI de la ficha nueva debe calzar con el de la cuenta existente
        // (si no calza, es la fila equivocada — no se fuerza).
        abort_unless($data['dni'] === $user->dni, 422, 'El DNI no coincide con el de la cuenta vinculada.');

        if ($request->input('modo') === 'cuenta_propia') {
            return $this->crearCuentaPropia($request, $importacion, $user, $data);
        }

        $user->condicion = 'Egresado';
        $user->save();

        $this->crearFichaEgresado($user, $data + ['email' => $user->email]);

        $importacion->update(['estado_match' => 'vinculado']);

        return redirect()->route('titulacion.index', ['ver' => $user->id])
            ->with('success', 'Ficha de egresado completada para '.$user->apellidos.', '.$user->name.'. Su cuenta y correo de acceso no cambiaron.');
    }

    /**
     * Personal de la escuela con cuenta de trabajo (p. ej. cobranzas.eesp@): en vez de volver egresada
     * esa cuenta del puesto, se le crea su cuenta propia. El DNI real pasa a la cuenta nueva (en el
     * sistema un DNI es de una sola cuenta); la de trabajo queda con «OFICINA-{id}» y sigue igual en
     * todo lo demás: correo, contraseña y roles. Las filas de las planillas pasan a la cuenta nueva.
     */
    private function crearCuentaPropia(Request $request, Importacion $importacion, User $trabajo, array $data)
    {
        abort_unless(self::esCuentaDeTrabajo($trabajo), 422, 'Solo para cuentas de trabajo del personal (sin rol de estudiante).');
        $data += $request->validate(
            ['email' => 'required|email|max:255|unique:users,email'],
            ['email.unique' => 'Ese correo ya es de otra cuenta.'],
            ['email' => 'correo de la cuenta propia']
        );

        $nueva = DB::transaction(function () use ($trabajo, $data) {
            $dni = $trabajo->dni;
            $trabajo->update(['dni' => 'OFICINA-'.$trabajo->id]);

            $nueva = User::create([
                'name' => $data['nombres'],
                'apellidos' => $data['apellidos'],
                'dni' => $dni,
                'email' => $data['email'],
                'condicion' => 'Egresado',
                'password' => Hash::make($dni),
            ]);
            $this->crearFichaEgresado($nueva, $data);
            Importacion::where('user_id', $trabajo->id)->orWhere('dni', $dni)
                ->update(['user_id' => $nueva->id, 'estado_match' => 'vinculado']);

            return $nueva;
        });

        return redirect()->route('titulacion.index', ['ver' => $nueva->id])
            ->with('success', 'Cuenta propia creada para '.$nueva->apellidos.', '.$nueva->name.' ('.$nueva->email.'; contraseña inicial: su DNI). '
                .'Su cuenta de trabajo '.$trabajo->email.' no cambió.');
    }

    /**
     * Crea el ciclo "Egresados {año}" (si no existe) y la ficha ppds/alumno
     * para un User ya creado/existente, más su seguimiento_titulacion
     * inicial. Compartido entre "crear cuenta" (persona nueva) y
     * "completar ficha" (persona que ya tenía cuenta por otro motivo).
     */
    protected function crearFichaEgresado(User $user, array $data): void
    {
        $anio = $data['anio_ingreso'] ?: (string) now()->year;

        $ciclo = Ciclo::firstOrCreate(
            ['programa_id' => $data['programa_id'], 'nombre' => 'Egresados '.$anio],
            []
        );

        $user->programa_id = $data['programa_id'];
        $user->ciclo_id = $ciclo->id;
        $user->save();

        $camposBase = [
            'dni' => $data['dni'],
            'nombres' => $data['nombres'],
            'apellidos' => $data['apellidos'],
            'email' => $data['email'],
            'programa_id' => $data['programa_id'],
            'ciclo_id' => $ciclo->id,
        ];

        if ($data['tipo'] === 'ppd') {
            if (! $user->hasRole('alumnoB')) {
                $user->assignRole('alumnoB');
            }
            // ppds/alumnos tienen ~50 columnas de la encuesta socioeconómica
            // (vivienda, salud, etc.) NOT NULL sin default — no aplican a un
            // egresado histórico que nunca llenó ese formulario. Se rellenan
            // con blancos "vacíos" del tipo correcto (no se puede omitirlas,
            // MySQL rechaza el insert si faltan). ppd.user_id no está en
            // $fillable a propósito (para que no se asigne masivamente desde
            // un form de usuario final), se asigna por propiedad directa.
            $ppdRecord = new ppd($camposBase + $this->defaultsObligatorios('ppds', $camposBase));
            $ppdRecord->user_id = $user->id;
            $ppdRecord->save();
        } else {
            if (! $user->hasRole('alumno')) {
                $user->assignRole('alumno');
            }
            Alumno::create($camposBase + ['user_id' => $user->id] + $this->defaultsObligatorios('alumnos', $camposBase));
        }

        $seguimiento = SeguimientoTitulacion::firstOrCreate(
            ['user_id' => $user->id],
            ['estado_titulacion_id' => EstadoTitulacion::idPorClave('sin_pti')]
        );

        // El identity+ficha de arriba no traía el contenido real de
        // titulación (PTI, tesis, actas, jurados, RDs) que sí venía en el
        // CSV — se aplica aparte, fusionando TODAS las filas de staging de
        // este DNI (una persona puede venir de más de un archivo fuente).
        $this->aplicarDatosTitulacionDesdeCsv($seguimiento, $data['dni']);
    }

    /**
     * Vuelca a las tablas reales (PTI, Tesis, Resoluciones, Acta+Jurados)
     * todo lo que se haya capturado en titulacion_importaciones para este
     * DNI, fusionando las filas de los distintos archivos fuente (toma el
     * primer valor no vacío encontrado por campo, probando los alias que
     * usa cada origen). Nunca se pierde información: lo que no tiene un
     * campo estructurado propio (grupo de exposición, dictaminantes,
     * jurados que no se pudieron vincular a un docente real, etc.) se anexa
     * como texto a seguimiento_titulacion.observaciones. Se puede llamar
     * más de una vez para la misma persona sin duplicar PTI/Tesis (hasOne,
     * updateOrCreate) ni Resoluciones/Actas (dedupe por campo único).
     *
     * $soloVacios (sincronización de planillas): el archivo solo llena lo que
     * está vacío en el sistema; lo que ya tiene otro valor NO se toca y se
     * devuelve como diferencia para revisarlo a mano. $lotePrioritario: las
     * filas de ese lote (la planilla recién subida) mandan sobre las de
     * importaciones anteriores del mismo DNI.
     *
     * @return string[] diferencias "campo: sistema «x» · planilla «y»" (solo con $soloVacios)
     */
    public function aplicarDatosTitulacionDesdeCsv(SeguimientoTitulacion $seguimiento, string $dni, bool $soloVacios = false, ?string $lotePrioritario = null): array
    {
        $diferencias = [];
        $filas = Importacion::where('dni', $dni)
            ->when($lotePrioritario, fn ($q) => $q->orderByRaw('lote_importacion = ? desc', [$lotePrioritario]))
            ->orderBy('id')
            ->get();
        if ($filas->isEmpty()) {
            return $diferencias;
        }

        $buscar = function (array $claves) use ($filas) {
            foreach ($filas as $fila) {
                $datos = $fila->datos_normalizados ?? [];
                foreach ($claves as $clave) {
                    if (! empty($datos[$clave] ?? null)) {
                        return $datos[$clave];
                    }
                }
            }
            return null;
        };

        // Las fechas SOLO se toman de la versión ya parseada a Y-m-d
        // (parsearFecha() en el comando de importación) — el texto crudo de
        // esa misma columna puede no ser una fecha real (ej. "Vencío PTI"
        // como estado en vez de fecha), y forzarlo en una columna `date`
        // revienta el guardado. Ese texto no-fecha igual se conserva como
        // nota en observaciones más abajo, nunca se descarta en silencio.
        $buscarFecha = fn (string $claveParseada) => $buscar([$claveParseada]);

        // Las columnas de RD también traen estados en vez de números
        // ("FV julio 2026", "expo. pendiente", "no solicito", "enviado"):
        // solo cuenta como RD lo que lo parece; el resto va a observaciones.
        // Una celda puede traer varias RD, una por línea (la del PTI y luego
        // la de cambio de asesor): la primera es la de la columna y las demás
        // se registran aparte en $rdExtras.
        $notasRd = [];
        $rdExtras = [];
        $buscarRd = function (array $claves, string $etiqueta, string $tipoRd) use ($filas, &$notasRd, &$rdExtras) {
            $textoNoRd = null;
            foreach ($filas as $fila) {
                $datos = $fila->datos_normalizados ?? [];
                foreach ($claves as $clave) {
                    $lineas = array_values(array_filter(array_map('trim', preg_split('/\R+/', (string) ($datos[$clave] ?? '')))));
                    $rds = array_values(array_filter($lineas, fn ($l) => self::pareceRd($l)));
                    if ($rds) {
                        // La celda puede listar varias RD («2. RD ANULACION / 1. RD PTI»): la principal es la de este
                        // tipo (no una anulación o incorporación que vaya primero); las demás se registran aparte.
                        $principal = collect($rds)->first(fn ($l) => self::tipoRdPorTexto($l, $tipoRd) === $tipoRd) ?? $rds[0];
                        foreach (array_diff($rds, [$principal]) as $extra) {
                            $rdExtras[] = [self::tipoRdPorTexto($extra, $tipoRd), $extra];
                        }
                        return $principal;
                    }
                    // La columna de fecha solo se revisa por si trae la RD; si
                    // trae una fecha, no es un "texto de RD" que anotar.
                    if ($clave !== 'fecha_exposicion') {
                        $textoNoRd ??= $lineas ? implode(' / ', $lineas) : null;
                    }
                }
            }
            if ($textoNoRd !== null) {
                $notasRd[] = "{$etiqueta} (texto del archivo, no es un número de RD): {$textoNoRd}";
            }
            return null;
        };

        // Todo el texto de la persona, para detectar datos sueltos que la
        // hoja escribe en cualquier columna ("FV julio 2026", "vencio pti").
        $textoCompleto = $filas->flatMap(fn ($f) => array_filter($f->datos_normalizados ?? [], 'is_string'))->implode(' | ');

        // Solo si nadie más lo tiene ya (pudo asignarse a mano mientras esta
        // persona seguía pendiente); si choca, queda vacío para revisarlo.
        $expediente = $this->expedienteDeFilas($filas, $seguimiento->user ? $seguimiento->user->apellidos.' '.$seguimiento->user->name : null);
        if (blank($seguimiento->numero_expediente) && $expediente) {
            $duenio = SeguimientoTitulacion::with('user')->where('numero_expediente', $expediente)->first();
            if (! $duenio) {
                $seguimiento->update(['numero_expediente' => $expediente]);
            } elseif ($soloVacios) {
                $diferencias[] = "Expediente {$expediente}: ya lo tiene ".trim(($duenio->user->apellidos ?? '').', '.($duenio->user->name ?? ''), ', ').' — no se asignó';
            }
        } elseif ($soloVacios && $expediente && $seguimiento->numero_expediente !== $expediente) {
            $diferencias[] = "Expediente: sistema «{$seguimiento->numero_expediente}» · planilla «{$expediente}»";
        }

        // Periodo de la promoción: "2018- I al 2023- II" -> "2018-I al 2023-II".
        $periodo = $buscar(['periodo']);
        if (blank($seguimiento->periodo_estudios) && $periodo && preg_match('/\d{4}.*\bal\b.*\d{4}/i', $periodo)) {
            $seguimiento->update(['periodo_estudios' => preg_replace(['/\s*-\s*/', '/\s+/'], ['-', ' '], trim($periodo))]);
        }

        $tituloPti = $buscar(['titulo_pti']);
        $asesorPtiNombre = $buscar(['asesor_pti', 'asesor']);
        $fechaPti = $buscarFecha('fecha_pti_parseada');
        $fechaPtiTextoCrudo = $buscar(['fecha_pti']);
        $rdPti = $buscarRd(['rd_pti'], 'RD PTI', 'PTI');
        $linkIti = $buscar(['link_iti']);
        $estadoPti = $buscar(['estado_pti']) ?? (preg_match('/venci[oó]\s+(el\s+)?pti/iu', $textoCompleto) ? 'vencido' : null);
        $vencimientoPti = self::fechaDeVencimiento($textoCompleto);

        if ($tituloPti || $rdPti || $fechaPti || $vencimientoPti || $estadoPti) {
            $this->guardarDatosImportados(Pti::class, $seguimiento->id, 'PTI', $soloVacios, $diferencias,
                array_filter([
                    'titulo' => $tituloPti,
                    'asesor_id' => $asesorPtiNombre ? $this->resolverDocentePorNombre($asesorPtiNombre) : null,
                    'fecha_pti' => $fechaPti,
                    'rd_pti' => $rdPti,
                    'link_iti' => $linkIti,
                    'estado' => in_array($estadoPti, ['pendiente', 'aprobado', 'vencido']) ? $estadoPti
                        : (stripos((string) $estadoPti, 'aprob') !== false ? 'aprobado' : null),
                    'fecha_vencimiento' => $vencimientoPti,
                ], fn ($v) => $v !== null)
            );
        }

        $tituloTesis = $buscar(['titulo_tesis']);
        $asesorTesisNombre = $buscar(['asesor_tesis', 'asesor_pti', 'asesor']);
        $fechaExposicion = $buscarFecha('fecha_exposicion_parseada');
        $fechaExposicionTextoCrudo = $buscar(['fecha_exposicion']);
        $fechaSustentacion = $buscarFecha('fecha_sustentacion_parseada');
        $fechaSustentacionTextoCrudo = $buscar(['fecha_sustentacion']);
        // En algunas filas la RD de exposición quedó en la columna de fecha.
        $rdTi = $buscarRd(['rd_ti', 'rd_exposicion', 'fecha_exposicion'], 'RD TI', 'Exposicion');
        $rdTesis = $buscarRd(['rd_tesis'], 'RD Tesis', 'Tesis');
        $bachillerSunedu = $buscar(['bachiller_sunedu']);
        $rdExtension = $buscarRd(['rd_extension'], 'Extensión', 'Extension');
        // La hoja a veces repite en "RD EXTENSION" la RD de sustentación/tesis: no es una extensión.
        if ($rdExtension && preg_match('/sustentaci[oó]n|\btesis\b/iu', $rdExtension) && ! preg_match('/exten[sc]i[oó]n|ampliaci[oó]n|plazo/iu', $rdExtension)) {
            $notasRd[] = 'La columna de extensión traía otra RD (no se tomó como extensión): '.$rdExtension;
            $rdExtension = null;
        }
        // "extension 19/12/24", "extencion 17/12/2024", "ext. 17/12/24"
        $fechaExtension = preg_match('/\bext(?:ensi[oó]n|enci[oó]n|\.)\s*(\d{1,2})\/(\d{1,2})\/(\d{2,4})/iu', $textoCompleto, $mExt)
            && checkdate((int) $mExt[2], (int) $mExt[1], strlen($mExt[3]) === 2 ? 2000 + (int) $mExt[3] : (int) $mExt[3])
            ? sprintf('%04d-%02d-%02d', strlen($mExt[3]) === 2 ? 2000 + (int) $mExt[3] : (int) $mExt[3], $mExt[2], $mExt[1])
            : null;

        // Cambio de asesor/tema: sus líneas con RD se registran como
        // resolución; el resto (texto libre) queda como nota.
        $cambioAsesorNotas = [];
        foreach (preg_split('/\R+/', (string) $buscar(['cambio_asesor'])) as $linea) {
            if (($linea = trim($linea)) === '' || $linea === '-') {
                continue;
            }
            if (self::pareceRd($linea)) {
                $rdExtras[] = [self::tipoRdPorTexto($linea, 'CambioAsesor'), $linea];
            } else {
                $cambioAsesorNotas[] = $linea;
            }
        }
        $urlRepositorio = $buscar(['url_repositorio']);
        $estadoTesisTexto = $buscar(['estado_tesis', 'situacion_tesis']);

        if ($tituloTesis || $rdTesis || $rdTi || $fechaSustentacion || $rdExtension || $fechaExtension) {
            $this->guardarDatosImportados(Tesis::class, $seguimiento->id, 'TI/Tesis', $soloVacios, $diferencias,
                array_filter([
                    'titulo' => $tituloTesis,
                    'asesor_id' => $asesorTesisNombre ? $this->resolverDocentePorNombre($asesorTesisNombre) : null,
                    'fecha_exposicion' => $fechaExposicion,
                    'rd_ti' => $rdTi,
                    'bachiller_sunedu' => $bachillerSunedu,
                    'rd_extension' => $rdExtension,
                    'fecha_extension' => $fechaExtension,
                    'fecha_sustentacion' => $fechaSustentacion,
                    'rd_tesis' => $rdTesis,
                    'url_repositorio' => $urlRepositorio,
                    'estado' => in_array($estadoTesisTexto, ['pendiente', 'en_repositorio', 'aprobado']) ? $estadoTesisTexto
                        : (stripos((string) $estadoTesisTexto, 'reposit') !== false ? 'en_repositorio'
                            : (stripos((string) $estadoTesisTexto, 'acept') !== false || stripos((string) $estadoTesisTexto, 'aprob') !== false ? 'aprobado' : null)),
                ], fn ($v) => $v !== null)
            );
        }

        // RDs sueltas como resoluciones propias — es justo el dato que se
        // pidió no perder ("nombre de los grupos, enlaces, RDs, etc.").
        foreach ([
            'PTI' => $rdPti,
            'Exposicion' => $rdTi,
            'Extension' => $rdExtension,
            'Tesis' => $rdTesis,
        ] as $tipoRd => $numeroRd) {
            if (! $numeroRd) {
                continue;
            }
            Resolucion::firstOrCreate([
                'seguimiento_id' => $seguimiento->id,
                'tipo' => $tipoRd,
                'numero_rd' => $numeroRd,
            ]);
        }
        foreach ($rdExtras as [$tipoRd, $numeroRd]) {
            Resolucion::firstOrCreate([
                'seguimiento_id' => $seguimiento->id,
                'tipo' => $tipoRd,
                'numero_rd' => $numeroRd,
            ]);
        }

        // Acta de exposición (jurados + promedio + nº de acta) si hay algo
        // de eso en el CSV.
        $numeroActa = $buscar(['numero_acta']);
        $promedio = $buscar(['promedio']);
        $jurados = array_filter([
            'jurado1' => $buscar(['jurado_1']),
            'jurado2' => $buscar(['jurado_2']),
            'jurado3' => $buscar(['jurado_3']),
            'jurado4' => $buscar(['jurado_4']),
        ]);

        $juradosNoVinculados = [];
        if ($numeroActa || $promedio || ! empty($jurados)) {
            $acta = Acta::firstOrCreate(
                ['seguimiento_id' => $seguimiento->id, 'tipo' => 'expo', 'numero_acta' => $numeroActa],
                [
                    'promedio' => is_numeric($promedio) ? $promedio : null,
                    'titulo_trabajo' => $tituloTesis,
                    'fecha' => $fechaExposicion,
                    'asesor_id' => $asesorTesisNombre ? $this->resolverDocentePorNombre($asesorTesisNombre) : null,
                ]
            );

            foreach ($jurados as $rol => $nombreJurado) {
                $docenteId = $this->resolverDocentePorNombre($nombreJurado);
                if ($docenteId) {
                    Jurado::firstOrCreate(['acta_id' => $acta->id, 'docente_id' => $docenteId], ['rol' => $rol]);
                } else {
                    $juradosNoVinculados[] = "{$rol}: {$nombreJurado}";
                }
            }
        }

        // Lo que no tiene campo estructurado propio (grupo de exposición,
        // dictaminantes con sus links, jurados que no se pudieron vincular a
        // un docente real, cambio de asesor) se anexa como nota — nunca se
        // descarta en silencio.
        $notasExtra = array_filter([
            ($fechaPtiTextoCrudo && ! $fechaPti) ? 'Fecha de PTI (texto sin formato de fecha en el archivo): '.$fechaPtiTextoCrudo : null,
            ($fechaExposicionTextoCrudo && ! $fechaExposicion && ! self::pareceRd($fechaExposicionTextoCrudo)) ?'Fecha de exposición (texto sin formato de fecha en el archivo): '.$fechaExposicionTextoCrudo : null,
            ($fechaSustentacionTextoCrudo && ! $fechaSustentacion) ? 'Fecha de sustentación (texto sin formato de fecha en el archivo): '.$fechaSustentacionTextoCrudo : null,
            $buscar(['grupo_exposicion']) ? 'Grupo de exposición/sustentación: '.$buscar(['grupo_exposicion']) : null,
            $buscar(['dictaminante_1']) ? 'Dictaminante 1: '.$buscar(['dictaminante_1']).($buscar(['link_dictamen_1']) ? ' ('.$buscar(['link_dictamen_1']).')' : '') : null,
            $buscar(['dictaminante_2']) ? 'Dictaminante 2: '.$buscar(['dictaminante_2']).($buscar(['link_dictamen_2']) ? ' ('.$buscar(['link_dictamen_2']).')' : '') : null,
            $cambioAsesorNotas ? 'Cambio de asesor/tema: '.implode(' / ', $cambioAsesorNotas) : null,
            $buscar(['columnas_sin_titulo']) ? 'Columnas sin título en el archivo: '.$buscar(['columnas_sin_titulo']) : null,
            ($notasNombre = $filas->flatMap(fn ($f) => preg_match_all('/\(([^)]+)\)/', (string) $f->nombre_estudiante, $m) ? array_map('trim', $m[1]) : [])->unique()->implode('; '))
                ? 'Nota junto al nombre en el archivo: '.$notasNombre : null,
            $buscar(['observaciones']) ? 'Observaciones del archivo: '.$buscar(['observaciones']) : null,
            ! empty($juradosNoVinculados) ? 'Jurados sin vincular a un docente del sistema: '.implode('; ', $juradosNoVinculados) : null,
            ...$notasRd,
        ], fn ($v) => $v !== null);

        // "enviado" en la hoja de expedientes = la Unidad de Investigación ya
        // le mandó el correo de vencimiento del PTI (sin fecha ni tipo).
        if (preg_match('/\benviado\b/i', (string) $buscar(['columnas_sin_titulo']))) {
            AvisoPti::firstOrCreate(['seguimiento_id' => $seguimiento->id, 'tipo' => 'excel']);
        }

        if (! empty($notasExtra) && blank($seguimiento->observaciones)) {
            $seguimiento->update(['observaciones' => implode(' | ', $notasExtra)]);
        }

        return $diferencias;
    }

    /**
     * PTI/Tesis (uno por seguimiento) con los datos de una planilla. Normal: los
     * datos del archivo reemplazan (updateOrCreate, como siempre). $soloVacios:
     * solo se llenan los campos vacíos; un valor distinto ya cargado se respeta
     * y se anota en $diferencias.
     */
    private function guardarDatosImportados(string $modelo, int $seguimientoId, string $etiqueta, bool $soloVacios, array &$diferencias, array $datos): void
    {
        if (! $soloVacios) {
            $modelo::updateOrCreate(['seguimiento_id' => $seguimientoId], $datos);

            return;
        }

        $registro = $modelo::firstOrNew(['seguimiento_id' => $seguimientoId]);
        $comparable = fn ($v) => $v instanceof \DateTimeInterface ? $v->format('Y-m-d')
            : mb_strtolower(preg_replace('/\s+/u', ' ', trim((string) $v)));
        foreach ($datos as $campo => $valor) {
            $actual = $registro->{$campo};
            if (blank($actual)) {
                $registro->{$campo} = $valor;
            } elseif ($comparable($actual) !== $comparable($valor)) {
                $mostrar = fn ($v) => $v instanceof \DateTimeInterface ? $v->format('d/m/Y')
                    : (preg_match('/^\d{4}-\d{2}-\d{2}$/', (string) $v) ? \Illuminate\Support\Carbon::parse($v)->format('d/m/Y') : \Illuminate\Support\Str::limit((string) $v, 70));
                $diferencias[] = "{$etiqueta} · ".str_replace('_', ' ', $campo).": sistema «{$mostrar($actual)}» · planilla «{$mostrar($valor)}»";
            }
        }
        if (! $registro->exists || $registro->isDirty()) {
            $registro->save();
        }
    }

    /**
     * true si el texto de una celda "RD ..." es una resolución: menciona RD,
     * es un archivo/enlace, o es solo el número ("227", "177-24", "88-2024").
     */
    protected static function pareceRd(string $valor): bool
    {
        return (bool) preg_match('/\bR\.?\s?D\b|\.(pdf|docx?)\b|https?:\/\//i', $valor)
            || (bool) preg_match('/^\s*\d{1,4}(\s*-\s*\d{2,4})?\s*$/', $valor);
    }

    /** Tipo de una RD suelta según su texto; $porDefecto si no lo dice. */
    protected static function tipoRdPorTexto(string $texto, string $porDefecto): string
    {
        return match (true) {
            (bool) preg_match('/anulaci[oó]n/iu', $texto) => 'AnulacionPti',
            (bool) preg_match('/incorpora(ci[oó]n|r)/iu', $texto) => 'IncorporacionGrupo',
            (bool) preg_match('/asesor|modificaci[oó]n/iu', $texto) => 'CambioAsesor',
            (bool) preg_match('/extensi[oó]n|extenci[oó]n/iu', $texto) => 'Extension',
            (bool) preg_match('/sustentaci[oó]n|tesis/iu', $texto) && $porDefecto !== 'PTI' => 'Tesis',
            (bool) preg_match('/\bPTI\b/i', $texto) => 'PTI',
            (bool) preg_match('/exposici[oó]n|\bTI\b/iu', $texto) => 'Exposicion',
            default => $porDefecto,
        };
    }

    /**
     * Vencimiento del PTI escrito como "FV julio 2026", "FV.Dic 2026",
     * "vence julio 2026" o "vence en abril del 2027" -> último día de ese mes
     * (el plazo corre hasta fin de mes). Null si no aparece.
     */
    private static function fechaDeVencimiento(string $texto): ?string
    {
        $meses = ['ene' => 1, 'feb' => 2, 'mar' => 3, 'abr' => 4, 'may' => 5, 'jun' => 6,
            'jul' => 7, 'ago' => 8, 'set' => 9, 'sep' => 9, 'oct' => 10, 'nov' => 11, 'dic' => 12];
        $texto = \Illuminate\Support\Str::ascii($texto);
        if (preg_match('/\b(?:FV|vence(?:\s+en)?)\.?\s*([a-z]{3,})\.?\s+(?:del?\s+)?(\d{4})\b/i', $texto, $m)
            && isset($meses[strtolower(substr($m[1], 0, 3))])) {
            return \Illuminate\Support\Carbon::create((int) $m[2], $meses[strtolower(substr($m[1], 0, 3))], 1)->endOfMonth()->toDateString();
        }

        return null;
    }

    /**
     * Número de expediente de una persona según sus filas importadas: el
     * confirmado a mano (lista de la coordinación) si lo hay; si no, el único
     * que digan los archivos. Si se contradicen, null: lo decide un humano.
     */
    private function expedienteDeFilas($filas, ?string $nombrePersona = null): ?string
    {
        $confirmado = $filas->first(fn (Importacion $f) => ! empty($f->datos_normalizados['expediente_confirmado']));
        $candidatos = ($confirmado ? collect([$confirmado]) : $filas)
            ->map(fn (Importacion $f) => Expediente::deFila($f->datos_normalizados ?? [], $f->nombre_estudiante ?: $nombrePersona))
            ->filter()->unique();

        return $candidatos->count() === 1 ? $candidatos->first() : null;
    }

    /**
     * Asesor/jurado en texto libre -> docente_id real, solo si hay un único
     * candidato (criterio en ResolverDocente). Si no, null: el nombre igual
     * queda en observaciones, nunca se atribuye al docente equivocado.
     */
    private function resolverDocentePorNombre(?string $nombre): ?int
    {
        return ResolverDocente::resolver($nombre);
    }

    /**
     * ppds/alumnos tienen decenas de columnas NOT NULL sin default (la
     * encuesta socioeconómica completa) que no tienen sentido para un
     * egresado histórico creado a partir de un CSV. Sin ellas, MySQL
     * rechaza el insert ("doesn't have a default value"). Se resuelven
     * dinámicamente vía information_schema (no una lista hardcodeada) para
     * no romperse si la tabla cambia, y se excluyen las columnas que ya se
     * están llenando explícitamente.
     */
    private function defaultsObligatorios(string $tabla, array $yaProvistos): array
    {
        $columnas = DB::select(
            'SELECT COLUMN_NAME, DATA_TYPE FROM information_schema.COLUMNS
             WHERE TABLE_SCHEMA = DATABASE() AND TABLE_NAME = ? AND IS_NULLABLE = "NO" AND COLUMN_DEFAULT IS NULL
             AND COLUMN_NAME NOT IN ("id", "created_at", "updated_at")',
            [$tabla]
        );

        $numericos = ['tinyint', 'smallint', 'mediumint', 'int', 'bigint', 'decimal', 'float', 'double'];

        $defaults = [];
        foreach ($columnas as $col) {
            if (array_key_exists($col->COLUMN_NAME, $yaProvistos)) {
                continue;
            }
            $defaults[$col->COLUMN_NAME] = in_array($col->DATA_TYPE, $numericos, true) ? 0 : '';
        }

        return $defaults;
    }
}
