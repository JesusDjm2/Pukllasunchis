<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Alumno;
use App\Models\Ciclo;
use App\Models\Programa;
use App\Models\ppd;
use App\Models\Titulacion\Acta;
use App\Models\Titulacion\Documento;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\Importacion;
use App\Models\Titulacion\Jurado;
use App\Models\Titulacion\Pti;
use App\Models\Titulacion\Resolucion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\Titulacion\Tesis;
use App\Models\User;
use App\Services\Titulacion\Expediente;
use App\Services\Titulacion\NombresImportados;
use App\Services\Titulacion\ResolverDocente;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class TitulacionController extends Controller
{
    /**
     * Ciclo (número romano → entero, ver Ciclo::ordenCiclo()) desde el cual
     * un alumno FID activo ya puede arrancar su PTI, según la política de
     * la institución. Solo aplica a FID — PPD no llega tan lejos (topa en
     * Ciclo II antes de Egresados).
     */
    private const CICLO_MINIMO_TITULACION = 8;

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

        // "Registrado" ya no es solo condicion=Egresado: un alumno FID activo
        // en Ciclo VIII o más puede arrancar su PTI antes de egresar (así
        // funciona en la institución) — una vez que alguien tiene fila en
        // seguimiento_titulacion, cuenta como registrado sin importar su
        // condicion todavía.
        $registrados = User::where(fn ($q) => $q->where('condicion', 'Egresado')->orWhereHas('seguimientoTitulacion'))
            ->with([
                'seguimientoTitulacion.estadoTitulacion',
                'seguimientoTitulacion.actas.asesor',
                'seguimientoTitulacion.actas.jurados.docente',
                'seguimientoTitulacion.resoluciones',
                'seguimientoTitulacion.documentos',
                'seguimientoTitulacion.pti.asesor',
                'seguimientoTitulacion.tesis.asesor',
                'programa', 'ciclo',
            ])
            ->get()
            ->map(function (User $user) use ($gruposPorActa) {
                // El detalle completo (PTI/Tesis/Actas/RDs/Documentos) ahora se
                // muestra directo en el acordeón de cada fila (ver
                // titulacion/index.blade.php), así que todo registrado necesita
                // su seguimiento_titulacion ya creado, no solo al entrar a
                // show(). Mismo firstOrCreate que usaba show() antes.
                $seguimiento = $user->seguimientoTitulacion ?: SeguimientoTitulacion::create([
                    'user_id' => $user->id,
                    'estado_titulacion_id' => EstadoTitulacion::where('nombre', 'Egresado')->value('id'),
                ]);

                $acta = $seguimiento->actas?->first();
                $integrantes = $acta && $acta->numero_acta
                    ? ($gruposPorActa[$acta->numero_acta] ?? collect())->reject(fn ($u) => $u->id === $user->id)
                    : collect();

                return (object) [
                    'tipo' => 'registrado',
                    'row_id' => 'reg'.$user->id,
                    'sort_key' => $user->apellidos.' '.$user->name,
                    'nombre' => $user->apellidos.', '.$user->name,
                    'expediente' => $seguimiento->numero_expediente,
                    'dni' => $user->dni,
                    'programa' => $user->programa->nombre ?? '—',
                    'programa_id' => $user->programa_id,
                    'anio' => $user->ciclo?->nombre,
                    'estado_label' => $seguimiento->estadoTitulacion?->nombre ?? 'Sin seguimiento',
                    'estado_badge' => $user->condicion === 'Egresado' ? 'info' : 'primary',
                    'accion_label' => 'Ver / Gestionar',
                    'accion_clase' => 'btn-info',
                    'user' => $user,
                    'seguimiento' => $seguimiento,
                    'acta' => $acta,
                    'rds' => $seguimiento->resoluciones ?? collect(),
                    'integrantes_grupo' => $integrantes,
                ];
            });

        $activosElegibles = $this->activosElegiblesParaTitulacion()
            ->map(fn (User $user) => (object) [
                'tipo' => 'activo_elegible',
                'row_id' => 'act'.$user->id,
                'sort_key' => $user->apellidos.' '.$user->name,
                'nombre' => $user->apellidos.', '.$user->name,
                'dni' => $user->dni,
                'programa' => $user->programa->nombre ?? '—',
                'programa_id' => $user->programa_id,
                'anio' => $user->ciclo?->nombre,
                'estado_label' => 'Activo — Ciclo '.$user->ciclo?->numeroRomano.' (puede iniciar)',
                'estado_badge' => 'primary',
                'accion_url' => route('titulacion.iniciar', $user->id),
                'accion_label' => 'Iniciar proceso',
                'accion_clase' => 'btn-primary',
                'es_form' => true,
            ]);

        $nombresPrograma = Programa::pluck('nombre', 'id');
        $pendientes = $this->pendientesAgrupados()
            ->map(function ($grupo) use ($nombresPrograma) {
                $esCompletarFicha = (bool) $grupo['user_id'];
                return (object) [
                    'tipo' => $esCompletarFicha ? 'pendiente_ficha' : 'pendiente_cuenta',
                    'row_id' => 'pend'.$grupo['importacion_id'],
                    'sort_key' => $grupo['nombre'] ?? '',
                    'nombre' => $grupo['nombre'] ?: '—',
                    'expediente' => $grupo['expediente'],
                    'dni' => $grupo['dni'],
                    // Nombre oficial si se reconoció (dice si es FID o PPD); si no,
                    // el texto tal cual viene en el archivo.
                    'programa' => $nombresPrograma[$grupo['programa_id']] ?? ($grupo['programa'] ?: '—'),
                    'programa_id' => $grupo['programa_id'],
                    'anio' => $grupo['anio_ingreso'] ? 'Egresados '.$grupo['anio_ingreso'] : null,
                    'estado_label' => $esCompletarFicha ? 'Ya tiene cuenta — falta ficha' : 'Sin cuenta en el sistema',
                    'estado_badge' => 'warning',
                    'accion_url' => $esCompletarFicha
                        ? route('titulacion.importaciones.completar-ficha.form', $grupo['importacion_id'])
                        : route('titulacion.importaciones.crear-cuenta.form', $grupo['importacion_id']),
                    'accion_label' => $esCompletarFicha ? 'Completar ficha' : 'Crear cuenta',
                    'accion_clase' => 'btn-warning',
                    'filas_origen' => $grupo['filas'],
                ];
            });

        $todos = $registrados->concat($pendientes)->concat($activosElegibles);

        if ($request->filled('search')) {
            $term = mb_strtolower(trim((string) $request->input('search')));
            $todos = $todos->filter(function ($item) use ($term) {
                if (str_contains(mb_strtolower($item->nombre), $term) || str_contains((string) $item->dni, $term)
                    || str_contains(mb_strtolower((string) ($item->expediente ?? '')), $term)) {
                    return true;
                }
                // También busca por Nº de acta y por cualquier RD (resolución) de la persona.
                $acta = $item->acta ?? null;
                if ($acta && str_contains(mb_strtolower((string) $acta->numero_acta), $term)) {
                    return true;
                }
                foreach (($item->rds ?? []) as $rd) {
                    if (str_contains(mb_strtolower((string) $rd->numero_rd), $term)) {
                        return true;
                    }
                }
                return false;
            });
        }

        if ($request->input('estado') === 'pendientes') {
            $todos = $todos->whereIn('tipo', ['pendiente_cuenta', 'pendiente_ficha']);
        } elseif ($request->input('estado') === 'activos') {
            $todos = $todos->where('tipo', 'activo_elegible');
        } elseif ($request->filled('estado_titulacion_id')) {
            // Filtro por estado de titulación real solo aplica a los ya registrados.
            $estadoNombre = EstadoTitulacion::find((int) $request->input('estado_titulacion_id'))?->nombre;
            $todos = $todos->filter(fn ($item) => $item->tipo === 'registrado' && $item->estado_label === $estadoNombre);
        }

        if ($request->filled('programa_id')) {
            $todos = $todos->filter(fn ($item) => ($item->programa_id ?? null) === (int) $request->input('programa_id'));
        }

        if ($request->filled('anio')) {
            $todos = $todos->filter(fn ($item) => ($item->anio ?? null) === $request->input('anio'));
        }

        $todos = $todos->sortBy('sort_key')->values();

        $page = (int) $request->input('page', 1);
        $perPage = 30;
        $egresados = new \Illuminate\Pagination\LengthAwarePaginator(
            $todos->forPage($page, $perPage),
            $todos->count(),
            $perPage,
            $page,
            ['path' => $request->url(), 'query' => $request->query()]
        );

        $estados = EstadoTitulacion::orderBy('orden')->get();
        $docentes = User::role('docente')->orderBy('name')->get();

        $conteoPorEstado = SeguimientoTitulacion::selectRaw('estado_titulacion_id, COUNT(*) as total')
            ->groupBy('estado_titulacion_id')
            ->pluck('total', 'estado_titulacion_id');

        $totalEgresados = $registrados->count();
        $totalPendientes = $pendientes->count();
        $totalActivosElegibles = $activosElegibles->count();
        $totalConSeguimiento = SeguimientoTitulacion::count();

        $programasFiltro = Programa::orderBy('nombre')->get();
        $aniosFiltro = $todos->pluck('anio')->filter()->unique()->sort()->values();

        return view('titulacion.index', compact(
            'egresados', 'estados', 'docentes', 'conteoPorEstado', 'totalEgresados', 'totalPendientes', 'totalActivosElegibles', 'totalConSeguimiento',
            'programasFiltro', 'aniosFiltro'
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
    private function pendientesAgrupados()
    {
        return Importacion::whereIn('estado_match', ['sin_match', 'vinculado'])
            ->get()
            // Sin DNI ni nombre no hay a quién identificar: son las filas en
            // blanco de la hoja de secretaría ("BLANCOO"). Agrupadas por clave
            // vacía aparecían como una sola "persona" pendiente fantasma.
            ->filter(fn (Importacion $imp) => filled($imp->dni) || filled($imp->nombre_normalizado))
            // Encabezados de promoción de los expedientes de Marilyn
            // ("2016-I al 2021-II" en la columna de nombre): no son personas.
            ->reject(fn (Importacion $imp) => NombresImportados::esFilaDeEncabezado($imp->nombre_estudiante, $imp->dni))
            ->groupBy(fn (Importacion $imp) => $imp->dni ?: $imp->nombre_normalizado)
            ->map(function ($grupo) {
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

                $motivos = [];
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
                    'elegible_masivo' => empty($motivos),
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
            ->filter(function ($grupo) {
                if (! $grupo['user_id']) {
                    return true; // sin cuenta todavía -> sigue pendiente
                }
                $user = User::find($grupo['user_id']);
                return ! $user || $user->condicion !== 'Egresado'; // tiene cuenta pero le falta la ficha
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
    private function resolverProgramaId(string $texto, ?string $tipo): ?int
    {
        $t = \Illuminate\Support\Str::ascii(strtolower($texto));
        // "Educación Primaria Intercultural Bilingüe" es EIB escrito completo.
        if (str_contains($t, 'intercultural') || str_contains($t, 'bilingue')) {
            $t .= ' eib';
        }

        if ($tipo === 'ppd') {
            if (str_contains($t, 'eib')) {
                return Programa::where('nombre', 'like', '%EIB%PPD%')->value('id');
            }
            if (str_contains($t, 'inicial')) {
                return Programa::where('nombre', 'like', '%Inicial%PPD%')->value('id');
            }
            if (str_contains($t, 'primaria')) {
                return Programa::where('nombre', 'like', '%Primaria%PPD%')->where('nombre', 'not like', '%EIB%')->value('id');
            }
        }

        // FID solo ofrece 2 programas hoy (confirmado contra la tabla real:
        // "Programa Inicial" y "Programa Primaria EIB" — no hay "Primaria"
        // FID sin EIB).
        if ($tipo === 'fid') {
            if (str_contains($t, 'eib')) {
                return Programa::where('nombre', 'like', '%Primaria%EIB%')->where('nombre', 'not like', '%PPD%')->value('id');
            }
            if (str_contains($t, 'inicial')) {
                return Programa::where('nombre', 'like', '%Inicial%')->where('nombre', 'not like', '%PPD%')->value('id');
            }
        }

        return null;
    }

    /**
     * Alumnos FID activos (todavía NO egresados) que ya pueden arrancar su
     * proceso de titulación según la política de la institución: desde
     * Ciclo VIII en adelante. Solo PPD/PPD-EIB no aplica acá — esos
     * programas solo llegan hasta Ciclo II antes de "Egresados" (se
     * confirmó contra la BD real), la regla de "Ciclo VIII" es de FID.
     * Se excluye a quien ya tiene seguimiento_titulacion (ya inició, ya
     * cuenta como "registrado" en index()).
     */
    private function activosElegiblesParaTitulacion(): \Illuminate\Support\Collection
    {
        return User::role('alumno')
            ->where('condicion', '!=', 'Egresado')
            ->whereDoesntHave('seguimientoTitulacion')
            ->whereNotNull('ciclo_id')
            ->with(['ciclo', 'programa'])
            ->get()
            ->filter(fn (User $user) => $user->ciclo && $user->ciclo->ordenCiclo() >= self::CICLO_MINIMO_TITULACION)
            ->values();
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
        abort_unless($alumno->hasRole('alumno'), 404);

        SeguimientoTitulacion::create([
            'user_id' => $alumno->id,
            'estado_titulacion_id' => EstadoTitulacion::where('nombre', 'PTI en proceso')->value('id')
                ?? EstadoTitulacion::orderBy('orden')->value('id'),
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
        // VIII+ (ver iniciarSeguimiento()) — cualquiera de los dos cuenta.
        abort_unless($egresado->condicion === 'Egresado' || $egresado->seguimientoTitulacion, 404);

        $seguimiento = SeguimientoTitulacion::firstOrCreate(
            ['user_id' => $egresado->id],
            ['estado_titulacion_id' => EstadoTitulacion::where('nombre', 'Egresado')->value('id')]
        );

        $seguimiento->load(['pti.asesor', 'tesis.asesor', 'actas.asesor', 'actas.jurados.docente', 'resoluciones', 'documentos', 'estadoTitulacion']);

        $estados = EstadoTitulacion::orderBy('orden')->get();
        $docentes = User::role('docente')->orderBy('name')->get();

        return view('titulacion.show', compact('egresado', 'seguimiento', 'estados', 'docentes'));
    }

    public function actualizarEstado(Request $request, User $egresado)
    {
        $data = $request->validate([
            'estado_titulacion_id' => 'required|exists:estados_titulacion,id',
            'observaciones' => 'nullable|string',
            'numero_expediente' => 'nullable|string|max:40',
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

        return $this->volverAlAcordeon($egresado->id, 'Estado de titulación actualizado.');
    }

    public function ptiGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'titulo' => 'nullable|string',
            'asesor_id' => 'nullable|exists:users,id',
            'fecha_pti' => 'nullable|date',
            'rd_pti' => 'nullable|string|max:255',
            'link_iti' => 'nullable|string|max:500',
            'estado' => 'nullable|in:pendiente,aprobado,vencido',
            'fecha_vencimiento' => 'nullable|date',
        ]);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        Pti::updateOrCreate(['seguimiento_id' => $seguimiento->id], $data);

        return $this->volverAlAcordeon($egresado->id, 'PTI guardado.');
    }

    public function tesisGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'titulo' => 'nullable|string',
            'asesor_id' => 'nullable|exists:users,id',
            'fecha_exposicion' => 'nullable|date',
            'rd_ti' => 'nullable|string|max:255',
            'bachiller_sunedu' => 'nullable|string|max:50',
            'rd_extension' => 'nullable|string|max:255',
            'fecha_sustentacion' => 'nullable|date',
            'rd_tesis' => 'nullable|string|max:255',
            'url_repositorio' => 'nullable|string|max:500',
            'estado' => 'nullable|in:pendiente,en_repositorio,aprobado',
        ]);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        Tesis::updateOrCreate(['seguimiento_id' => $seguimiento->id], $data);

        return $this->volverAlAcordeon($egresado->id, 'Tesis guardada.');
    }

    public function actaGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'tipo' => 'required|in:expo,tesis',
            'numero_acta' => 'nullable|string|max:20',
            'fecha' => 'nullable|date',
            'promedio' => 'nullable|numeric|min:0|max:20',
            'titulo_trabajo' => 'nullable|string',
            'asesor_id' => 'nullable|exists:users,id',
        ]);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        $seguimiento->actas()->create($data);

        return $this->volverAlAcordeon($egresado->id, 'Acta agregada.');
    }

    public function actaEliminar(Acta $acta)
    {
        $userId = $acta->seguimiento->user_id;
        $acta->delete();
        return $this->volverAlAcordeon($userId, 'Acta eliminada.');
    }

    public function juradoGuardar(Request $request, Acta $acta)
    {
        $data = $request->validate([
            'docente_id' => 'required|exists:users,id',
            'rol' => 'required|string|max:20',
        ]);

        $acta->jurados()->create($data);

        return $this->volverAlAcordeon($acta->seguimiento->user_id, 'Jurado agregado.');
    }

    public function juradoEliminar(Jurado $jurado)
    {
        $userId = $jurado->acta->seguimiento->user_id;
        $jurado->delete();
        return $this->volverAlAcordeon($userId, 'Jurado eliminado.');
    }

    public function resolucionGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'tipo' => 'required|string|max:50',
            'numero_rd' => 'nullable|string|max:255',
            'fecha' => 'nullable|date',
            'archivo' => 'nullable|string|max:500',
        ]);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        $seguimiento->resoluciones()->create($data);

        return $this->volverAlAcordeon($egresado->id, 'Resolución agregada.');
    }

    public function resolucionEliminar(Resolucion $resolucion)
    {
        $userId = $resolucion->seguimiento->user_id;
        $resolucion->delete();
        return $this->volverAlAcordeon($userId, 'Resolución eliminada.');
    }

    public function documentoGuardar(Request $request, User $egresado)
    {
        $data = $request->validate([
            'tipo' => 'required|string|max:50',
            'url' => 'nullable|string|max:500',
            'descripcion' => 'nullable|string',
        ]);

        $seguimiento = SeguimientoTitulacion::where('user_id', $egresado->id)->firstOrFail();
        $seguimiento->documentos()->create($data);

        return $this->volverAlAcordeon($egresado->id, 'Documento agregado.');
    }

    public function documentoEliminar(Documento $documento)
    {
        $userId = $documento->seguimiento->user_id;
        $documento->delete();
        return $this->volverAlAcordeon($userId, 'Documento eliminado.');
    }

    /**
     * Todas las acciones internas del detalle de un egresado (guardar PTI,
     * agregar jurado, etc.) regresan aquí en vez de a una página propia —
     * `/titulacion?ver={id}` hace que el índice reabra automáticamente el
     * acordeón de esa fila ya con los datos actualizados (ver script en
     * titulacion/index.blade.php), para que nunca se sienta como "navegar a
     * otra vista".
     */
    private function volverAlAcordeon(int $userId, string $mensaje)
    {
        return redirect()->route('titulacion.index', ['ver' => $userId])->with('success', $mensaje);
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
        $elegibles = $pendientes->where('elegible_masivo', true)->where('user_id', null)->values();
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

        return view('titulacion.importaciones-completar-ficha', compact(
            'importacion', 'user', 'nombresPrefill', 'apellidosPrefill', 'programas'
        ));
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

        $user->condicion = 'Egresado';
        $user->save();

        $this->crearFichaEgresado($user, $data + ['email' => $user->email]);

        $importacion->update(['estado_match' => 'vinculado']);

        return redirect()->route('titulacion.index', ['ver' => $user->id])
            ->with('success', 'Ficha de egresado completada para '.$user->apellidos.', '.$user->name.'. Su cuenta y correo de acceso no cambiaron.');
    }

    /**
     * Crea el ciclo "Egresados {año}" (si no existe) y la ficha ppds/alumno
     * para un User ya creado/existente, más su seguimiento_titulacion
     * inicial. Compartido entre "crear cuenta" (persona nueva) y
     * "completar ficha" (persona que ya tenía cuenta por otro motivo).
     */
    private function crearFichaEgresado(User $user, array $data): void
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
            ['estado_titulacion_id' => EstadoTitulacion::where('nombre', 'Egresado')->value('id')]
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
     */
    private function aplicarDatosTitulacionDesdeCsv(SeguimientoTitulacion $seguimiento, string $dni): void
    {
        $filas = Importacion::where('dni', $dni)->get();
        if ($filas->isEmpty()) {
            return;
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

        // Solo si nadie más lo tiene ya (pudo asignarse a mano mientras esta
        // persona seguía pendiente); si choca, queda vacío para revisarlo.
        if (blank($seguimiento->numero_expediente)
            && ($expediente = $this->expedienteDeFilas($filas, $seguimiento->user ? $seguimiento->user->apellidos.' '.$seguimiento->user->name : null))
            && ! SeguimientoTitulacion::where('numero_expediente', $expediente)->exists()) {
            $seguimiento->update(['numero_expediente' => $expediente]);
        }

        $tituloPti = $buscar(['titulo_pti']);
        $asesorPtiNombre = $buscar(['asesor_pti', 'asesor']);
        $fechaPti = $buscarFecha('fecha_pti_parseada');
        $fechaPtiTextoCrudo = $buscar(['fecha_pti']);
        $rdPti = $buscar(['rd_pti']);
        $linkIti = $buscar(['link_iti']);
        $estadoPti = $buscar(['estado_pti']);

        if ($tituloPti || $rdPti || $fechaPti) {
            Pti::updateOrCreate(
                ['seguimiento_id' => $seguimiento->id],
                array_filter([
                    'titulo' => $tituloPti,
                    'asesor_id' => $asesorPtiNombre ? $this->resolverDocentePorNombre($asesorPtiNombre) : null,
                    'fecha_pti' => $fechaPti,
                    'rd_pti' => $rdPti,
                    'link_iti' => $linkIti,
                    'estado' => in_array($estadoPti, ['pendiente', 'aprobado', 'vencido']) ? $estadoPti
                        : (stripos((string) $estadoPti, 'aprob') !== false ? 'aprobado' : null),
                ], fn ($v) => $v !== null)
            );
        }

        $tituloTesis = $buscar(['titulo_tesis']);
        $asesorTesisNombre = $buscar(['asesor_tesis', 'asesor_pti', 'asesor']);
        $fechaExposicion = $buscarFecha('fecha_exposicion_parseada');
        $fechaExposicionTextoCrudo = $buscar(['fecha_exposicion']);
        $fechaSustentacion = $buscarFecha('fecha_sustentacion_parseada');
        $fechaSustentacionTextoCrudo = $buscar(['fecha_sustentacion']);
        $rdTi = $buscar(['rd_ti']);
        $rdTesis = $buscar(['rd_tesis']);
        $bachillerSunedu = $buscar(['bachiller_sunedu']);
        $rdExtension = $buscar(['rd_extension']);
        $urlRepositorio = $buscar(['url_repositorio']);
        $estadoTesisTexto = $buscar(['estado_tesis', 'situacion_tesis']);

        if ($tituloTesis || $rdTesis || $rdTi || $fechaSustentacion) {
            Tesis::updateOrCreate(
                ['seguimiento_id' => $seguimiento->id],
                array_filter([
                    'titulo' => $tituloTesis,
                    'asesor_id' => $asesorTesisNombre ? $this->resolverDocentePorNombre($asesorTesisNombre) : null,
                    'fecha_exposicion' => $fechaExposicion,
                    'rd_ti' => $rdTi,
                    'bachiller_sunedu' => $bachillerSunedu,
                    'rd_extension' => $rdExtension,
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
            'Exposicion' => $buscar(['rd_exposicion']) ?: $rdTi,
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
            ($fechaExposicionTextoCrudo && ! $fechaExposicion) ? 'Fecha de exposición (texto sin formato de fecha en el archivo): '.$fechaExposicionTextoCrudo : null,
            ($fechaSustentacionTextoCrudo && ! $fechaSustentacion) ? 'Fecha de sustentación (texto sin formato de fecha en el archivo): '.$fechaSustentacionTextoCrudo : null,
            $buscar(['grupo_exposicion']) ? 'Grupo de exposición/sustentación: '.$buscar(['grupo_exposicion']) : null,
            $buscar(['dictaminante_1']) ? 'Dictaminante 1: '.$buscar(['dictaminante_1']).($buscar(['link_dictamen_1']) ? ' ('.$buscar(['link_dictamen_1']).')' : '') : null,
            $buscar(['dictaminante_2']) ? 'Dictaminante 2: '.$buscar(['dictaminante_2']).($buscar(['link_dictamen_2']) ? ' ('.$buscar(['link_dictamen_2']).')' : '') : null,
            $buscar(['cambio_asesor']) ? 'Cambio de asesor/tema: '.$buscar(['cambio_asesor']) : null,
            $buscar(['observaciones']) ? 'Observaciones del archivo: '.$buscar(['observaciones']) : null,
            ! empty($juradosNoVinculados) ? 'Jurados sin vincular a un docente del sistema: '.implode('; ', $juradosNoVinculados) : null,
        ], fn ($v) => $v !== null);

        if (! empty($notasExtra) && blank($seguimiento->observaciones)) {
            $seguimiento->update(['observaciones' => implode(' | ', $notasExtra)]);
        }
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
