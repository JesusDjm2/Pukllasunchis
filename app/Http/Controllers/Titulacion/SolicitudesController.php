<?php

namespace App\Http\Controllers\Titulacion;

use App\Http\Controllers\Controller;
use App\Models\Titulacion\Solicitud;
use App\Services\Titulacion\BandejasSolicitudes;
use App\Services\Titulacion\ReconocerPersonas;
use Illuminate\Http\Request;
use Illuminate\Support\Str;

/**
 * Sección «Solicitudes» de Titulación: la pestaña «Por atender» reúne lo pendiente de todos los trámites
 * (de lo más antiguo a lo más reciente); cada trámite tiene su propia pestaña (BandejasSolicitudes).
 *
 * Secretaría (AreasTitulacion) puede además EDITAR cualquier solicitud de los formularios (PTI, originalidad…):
 * corregir datos mal escritos o importados, vincular a un integrante con su cuenta (por DNI) o cambiar el estado.
 * Cada edición queda en datos.ediciones (quién, cuándo y qué campos).
 */
class SolicitudesController extends Controller
{
    /** Tipo => ruta de su bandeja. */
    private const BANDEJAS = [
        Solicitud::PTI => 'titulacion.solicitudes-pti.index',
        Solicitud::ORIGINALIDAD => 'titulacion.solicitudes-originalidad.index',
    ];

    /** Integrantes que se pueden registrar en una solicitud. */
    public const MAX_INTEGRANTES = 3;

    public function index()
    {
        return view('titulacion.solicitudes.index', ['pendientes' => BandejasSolicitudes::porAtender()]);
    }

    public function editar(Solicitud $solicitud)
    {
        abort_unless(isset(self::BANDEJAS[$solicitud->tipo]), 404);
        $solicitud->load(['solicitante', 'seguimiento']);
        $cuentas = \App\Models\User::whereIn('id', $solicitud->idsIntegrantes())->get(['id', 'name', 'apellidos', 'dni'])->keyBy('id');

        return view('titulacion.solicitudes.editar', [
            's' => $solicitud,
            'cuentas' => $cuentas,
            'volver' => route(self::BANDEJAS[$solicitud->tipo], ['resaltar' => $solicitud->id]),
        ]);
    }

    public function actualizar(Request $request, Solicitud $solicitud)
    {
        abort_unless(isset(self::BANDEJAS[$solicitud->tipo]), 404);
        $esPti = $solicitud->tipo === Solicitud::PTI;
        $data = $request->validate([
            'titulo' => 'required|string|min:3|max:1000',
            'correo' => 'nullable|string|max:150',
            'celulares' => 'nullable|string|max:200',
            'programa_estudios' => 'nullable|string|max:100',
            'ciclo' => 'nullable|string|max:30',
            'estado' => 'required|in:'.implode(',', array_keys(Solicitud::ESTADOS)),
            'observacion' => 'nullable|string|max:1500',
            'integrantes' => 'array|max:'.self::MAX_INTEGRANTES,
            'integrantes.*.nombre' => 'nullable|string|max:200',
            'integrantes.*.dni' => 'nullable|digits_between:8,12',
            // PTI
            'area' => [$esPti ? 'nullable' : 'prohibited', 'in:'.implode(',', array_keys(Solicitud::AREAS))],
            'linea' => [$esPti ? 'nullable' : 'prohibited', 'in:'.implode(',', array_keys(Solicitud::LINEAS))],
            // Revisión de originalidad
            'etapa' => [$esPti ? 'prohibited' : 'required', 'in:'.implode(',', array_keys(Solicitud::ETAPAS))],
            'fecha_pago' => [$esPti ? 'prohibited' : 'nullable', 'date'],
            'similitud' => [$esPti ? 'prohibited' : 'nullable', 'numeric', 'min:0', 'max:100'],
        ], [
            'integrantes.*.dni.digits_between' => 'El DNI debe tener solo números (8 dígitos).',
        ], ['titulo' => 'título', 'programa_estudios' => 'programa de estudios', 'linea' => 'línea de investigación', 'etapa' => 'documento', 'fecha_pago' => 'fecha de pago']);
        if ($esPti && filled($data['linea'] ?? null) && filled($data['area'] ?? null) && ! str_starts_with($data['linea'], $data['area'].'-')) {
            return back()->withInput()->withErrors(['linea' => 'La línea elegida no pertenece al área elegida.']);
        }

        // Integrantes: con DNI se vincula esa cuenta; sin DNI se conserva la cuenta si el nombre no cambió, o se busca por nombre.
        $personas = new ReconocerPersonas;
        $antes = collect($solicitud->integrantes ?? [])->keyBy(fn ($i) => Str::lower(trim($i['nombre'] ?? '')));
        $integrantes = [];
        $noEncontrados = [];
        foreach ($data['integrantes'] ?? [] as $i) {
            $nombre = trim(preg_replace('/\s+/', ' ', (string) ($i['nombre'] ?? '')));
            $dni = (string) ($i['dni'] ?? '');
            if ($nombre === '' && $dni === '') {
                continue;
            }
            $u = $dni !== '' ? $personas->porDni($dni) : null;
            if ($dni !== '' && ! $u) {
                $noEncontrados[] = $dni;
            }
            $id = $u?->id ?? ($antes[Str::lower($nombre)]['user_id'] ?? $personas->porNombre($nombre)[0]?->id);
            $integrantes[] = ['nombre' => $nombre !== '' ? $nombre : trim(($u?->apellidos ?? '').', '.($u?->name ?? ''), ', '), 'user_id' => $id];
        }
        if ($noEncontrados) {
            return back()->withInput()->withErrors(['integrantes' => 'No hay ningún estudiante o egresado con DNI '.implode(', ', $noEncontrados).'.']);
        }
        if (! $integrantes) {
            return back()->withInput()->withErrors(['integrantes' => 'Registre al menos un integrante.']);
        }

        $datos = $solicitud->datos ?? [];
        if ($esPti) {
            $datos = array_merge($datos, ['area' => $data['area'] ?? null, 'linea' => $data['linea'] ?? null]);
            unset($datos['area_texto'], $datos['linea_texto']);
        } else {
            $datos = array_merge($datos, ['etapa' => $data['etapa'], 'fecha_pago' => $data['fecha_pago'] ?? null]);
            unset($datos['etapa_supuesta']);
            if (array_key_exists('similitud', $data)) {
                $datos['similitud'] = filled($data['similitud']) ? round((float) $data['similitud'], 2) : null;
            }
        }

        $nuevos = [
            'titulo' => trim($data['titulo'], " \"“”"), 'correo' => (string) ($data['correo'] ?? ''), 'celulares' => (string) ($data['celulares'] ?? ''),
            'programa_estudios' => (string) ($data['programa_estudios'] ?? ''), 'ciclo' => (string) ($data['ciclo'] ?? ''),
            'estado' => $data['estado'], 'observacion' => filled($data['observacion'] ?? null) ? trim($data['observacion']) : null,
            'integrantes' => $integrantes,
        ];
        // Qué cambió (para el registro de ediciones).
        $cambiados = collect($nuevos)->filter(fn ($v, $k) => $solicitud->{$k} != $v)->keys()
            ->merge(collect(['area', 'linea', 'etapa', 'fecha_pago', 'similitud'])->filter(fn ($k) => ($solicitud->datos[$k] ?? null) != ($datos[$k] ?? null)))
            ->values()->all();
        if (! $cambiados) {
            return redirect()->to(route(self::BANDEJAS[$solicitud->tipo], ['resaltar' => $solicitud->id]))->with('success', 'No había cambios que guardar.');
        }
        $datos['ediciones'] = array_merge($datos['ediciones'] ?? [], [[
            'por' => $request->user()->id, 'nombre' => $request->user()->name, 'en' => now()->toDateTimeString(), 'campos' => $cambiados,
        ]]);

        // Al pasar a atendida u observada se registra quién y cuándo (si no estaba ya).
        if ($data['estado'] !== 'recibida' && $solicitud->estado === 'recibida') {
            $nuevos += ['atendido_por' => $request->user()->id, 'atendido_en' => now()];
        }
        $solicitud->update($nuevos + ['datos' => array_filter($datos, fn ($v) => $v !== null)]);

        return redirect()->to(route(self::BANDEJAS[$solicitud->tipo], ['resaltar' => $solicitud->id]))
            ->with('success', 'Solicitud actualizada ('.count($cambiados).' '.(count($cambiados) === 1 ? 'dato' : 'datos').'). No se envió ningún aviso.');
    }
}
