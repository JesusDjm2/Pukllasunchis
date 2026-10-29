<?php

namespace App\Http\Controllers;

use App\Models\Alumno;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Provincia;
use App\Models\SeguimientoEgresado\AccesoContacto;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\GrupoEntrevistador;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class SeguimientoEgresadoController extends Controller
{
    /**
     * Vista principal del módulo de Seguimiento de Egresados (Simulador V8 conectado a la BD institucional).
     */
    public function index(Request $request)
    {
        // 1. Obtener egresados reales de la base de datos institucional
        $egresadosReales = User::query()
            ->where('condicion', 'Egresado')
            ->with(['alumno.programa', 'seguimientoTitulacion.estadoTitulacion'])
            ->orderBy('apellidos')
            ->orderBy('name')
            ->limit(300)
            ->get()
            ->map(function ($u) {
                $alumno = $u->alumno;
                $tit = $u->seguimientoTitulacion;

                // Inferir o limpiar año de cohorte a partir de su correo o periodo de estudios
                $cohorte = 'Por verificar';
                if ($tit && $tit->periodo_estudios) {
                    $cohorte = $tit->periodo_estudios;
                } elseif (preg_match('/(20\d{2})/', $u->email, $m)) {
                    $cohorte = $m[1];
                }

                $progNombre = 'Programa por verificar';
                if ($alumno && $alumno->programa) {
                    $progNombre = str_replace('Programa ', '', $alumno->programa->nombre);
                }

                return [
                    'id' => 'REAL-' . $u->id,
                    'user_id' => $u->id,
                    'nombre' => trim($u->name . ' ' . $u->apellidos),
                    'programa' => $progNombre,
                    'egreso' => $cohorte,
                    'periodo_referencia' => $cohorte,
                    'egreso_confirmado' => true,
                    'correo' => $u->email,
                    'telefono' => $alumno?->numero ?: '',
                    'telefono_alt' => $alumno?->numero_referencia ?: '',
                    'direccion_contacto' => $alumno?->direccion ?: '',
                    'res_departamento' => $alumno?->departamento ?: 'CUSCO',
                    'res_provincia' => $alumno?->provincia ?: 'CUSCO',
                    'res_distrito' => $alumno?->distrito ?: 'CUSCO',
                    'estado_academico' => $tit?->estadoTitulacion?->nombre ?: 'Egresado/a',
                    'origen' => 'Base institucional EESPP Pukllasunchis'
                ];
            });

        // 2. Obtener actualizaciones registradas en base de datos
        $actualizacionesDb = Actualizacion::latest()->get()->keyBy(function ($item) {
            return $item->identificador_temporal ?: ('REAL-' . $item->user_id);
        });

        // 3. Obtener casos de acción institucional
        $casosDb = Caso::latest()->get();

        // 4. Obtener llamadas registradas
        $llamadasDb = Llamada::latest()->get();

        // 5. Obtener bitácora de auditoría de contactos
        $accesosContactoDb = AccesoContacto::latest()->limit(50)->get();

        // 6. Obtener grupos de entrevistadores para seguimiento
        $gruposDb = GrupoEntrevistador::latest()->get();

        // 7. Entrevistadores disponibles (docentes, tutores, alumnos o admins)
        $entrevistadoresDisponibles = User::whereHas('roles', fn ($q) => $q->whereIn('name', ['docente', 'tutor', 'admin', 'alumno']))
            ->orderBy('apellidos')
            ->limit(150)
            ->get()
            ->map(fn ($u) => ['id' => $u->id, 'nombre' => trim($u->name . ' ' . $u->apellidos), 'email' => $u->email]);

        // 8. Catálogo de departamentos, provincias y distritos principales
        $departamentos = Departamento::orderBy('nombre')->pluck('nombre');
        $provinciasCusco = Provincia::whereHas('departamento', function ($q) {
            $q->where('nombre', 'like', '%Cusco%');
        })->orderBy('nombre')->pluck('nombre');
        $distritosCusco = Distrito::whereHas('provincia', function ($q) {
            $q->where('nombre', 'like', '%Cusco%');
        })->orderBy('nombre')->pluck('nombre');

        return view('seguimiento_egresados.index', compact(
            'egresadosReales',
            'actualizacionesDb',
            'casosDb',
            'llamadasDb',
            'accesosContactoDb',
            'gruposDb',
            'entrevistadoresDisponibles',
            'departamentos',
            'provinciasCusco',
            'distritosCusco'
        ));
    }

    /**
     * Guardar actualización de ficha (vía AJAX desde actualización ágil o cuestionario común).
     */
    public function guardarActualizacion(Request $request)
    {
        $idStr = $request->input('id');
        $userId = null;
        $idTemporal = $idStr;

        if (str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
            $idTemporal = null;
        }

        $nombre = $request->input('nombre', 'Egresado/a');
        if ($userId && ($u = User::find($userId))) {
            $nombre = trim($u->name . ' ' . $u->apellidos);
        }

        $actualizacion = Actualizacion::updateOrCreate(
            ['user_id' => $userId, 'identificador_temporal' => $idTemporal],
            [
                'nombre_completo' => $nombre,
                'programa' => $request->input('programa'),
                'cohorte_egreso' => $request->input('egreso'),
                'canal' => $request->input('canal', 'autorespuesta'),
                'telefono' => $request->input('telefono'),
                'whatsapp' => $request->input('whatsapp'),
                'telefono_alt' => $request->input('telefono_alt'),
                'correo' => $request->input('correo'),
                'direccion_contacto' => $request->input('direccion_contacto'),
                'medio_preferido' => $request->input('medio_preferido'),
                'res_departamento' => $request->input('res_departamento'),
                'res_provincia' => $request->input('res_provincia'),
                'res_distrito' => $request->input('res_distrito'),
                'titulo_bach' => $request->input('titulo_bach'),
                'fecha_bach' => $request->input('fecha_bach') ?: null,
                'titulo_lic' => $request->input('titulo_lic'),
                'fecha_lic' => $request->input('fecha_lic') ?: null,
                'apoyo_titulacion' => $request->input('apoyo_titulacion'),
                'trabaja' => $request->input('trabaja'),
                'cambio_empleo' => $request->input('cambio_empleo'),
                'campo' => $request->input('campo'),
                'tipo_practica' => $request->input('tipo_practica'),
                'institucion' => $request->input('institucion'),
                'cargo' => $request->input('cargo'),
                'nivel_modalidad' => $request->input('nivel'),
                'sector' => $request->input('sector'),
                'contrato' => $request->input('contrato'),
                'inicio_empleo' => $request->input('inicio_empleo') ?: null,
                'fin_empleo' => $request->input('fin_empleo') ?: null,
                'trab_departamento' => $request->input('trab_departamento'),
                'trab_provincia' => $request->input('trab_provincia'),
                'trab_distrito' => $request->input('trab_distrito'),
                'lugar_trabajo_declarado' => $request->input('lugar_trabajo'),
                'satisfaccion' => $request->input('satisfaccion'),
                'temas_interes' => $request->input('temas'),
                'necesidades_apoyo' => $request->input('necesidad_prioritaria') ?: $request->input('necesidades'),
                'puedo_compartir' => $request->input('puedo_compartir'),
                'practicas_que_permanecen' => $request->input('practicas_que_permanecen'),
                'eib' => $request->input('eib'),
                'quechua' => $request->input('quechua'),
                'comunidad' => $request->input('comunidad'),
                'dificultades_practica' => $request->input('dificultades_practica'),
                'companeros_contacto' => $request->input('companeros_contacto'),
                'frecuencia_vinculo' => $request->input('frecuencia_vinculo'),
                'tipo_vinculo' => $request->input('tipo_vinculo'),
                'encuentros_red' => $request->input('encuentros_red'),
                'desea_red' => $request->input('red'),
                'compartir_perfil' => $request->input('compartir_perfil'),
            ]
        );

        // Si se declaró inicio de nuevo empleo, registrar episodio laboral
        if ($request->input('trabaja') === 'Trabaja' && $request->input('inicio_empleo')) {
            EpisodioLaboral::create([
                'user_id' => $userId,
                'identificador_temporal' => $idTemporal,
                'fecha_inicio' => $request->input('inicio_empleo'),
                'fecha_fin' => $request->input('fin_empleo') ?: null,
                'institucion' => $request->input('institucion') ?: 'Institución no especificada',
                'cargo' => $request->input('cargo'),
                'contrato' => $request->input('contrato'),
                'es_empleo_educativo' => ($request->input('campo') === 'En educación'),
                'es_docencia_directa' => ($request->input('tipo_practica') === 'Docencia directa'),
                'canal_registro' => $request->input('canal', 'autorespuesta'),
            ]);
        }

        // Si solicitó acompañamiento prioritario, abrir caso de gestión institucional
        $necesidad = $request->input('necesidad_prioritaria');
        if ($necesidad && str_starts_with($necesidad, 'Sí:')) {
            $tema = trim(str_replace('Sí:', '', $necesidad));
            Caso::create([
                'codigo' => 'CASO-' . time() . '-' . rand(10, 99),
                'user_id' => $userId,
                'nombre_egresado' => $nombre,
                'tema' => $tema,
                'responsable' => 'Coordinación de Empleabilidad',
                'fecha_gestion' => now()->addDays(7)->toDateString(),
                'estado' => 'Por revisar',
                'proposito' => 'Solicitud de apoyo voluntario registrada desde actualización de egresado.',
                'origen' => 'Formulario de actualización'
            ]);
        }

        return response()->json([
            'success' => true,
            'message' => 'Actualización guardada correctamente en la base institucional.',
            'actualizacion_id' => $actualizacion->id
        ]);
    }

    /**
     * Registrar nueva acción o caso de acompañamiento.
     */
    public function guardarAccion(Request $request)
    {
        $idStr = $request->input('pid');
        $userId = null;
        if (str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
        }

        $caso = Caso::create([
            'codigo' => 'ACT-' . time() . '-' . rand(10, 99),
            'user_id' => $userId,
            'nombre_egresado' => $request->input('nombre', 'Egresado/a'),
            'tema' => $request->input('tema', 'Acompañamiento general'),
            'responsable' => $request->input('responsable', 'Coordinación'),
            'fecha_gestion' => $request->input('fecha') ?: now()->addDays(7)->toDateString(),
            'estado' => 'Por revisar',
            'proposito' => $request->input('nota', 'Respuesta institucional programada.'),
            'origen' => 'Planificación institucional'
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Acción de acompañamiento registrada en la base de datos.',
            'caso' => $caso
        ]);
    }

    /**
     * Avanzar estado de un caso (Por revisar -> En atención -> Cerrado).
     */
    public function avanzarAccion(Request $request, $id)
    {
        $caso = Caso::findOrFail($id);

        $nuevoEstado = match ($caso->estado) {
            'Por revisar' => 'En atención',
            'En atención' => 'Cerrado',
            default => 'Por revisar',
        };

        $caso->update(['estado' => $nuevoEstado]);

        return response()->json([
            'success' => true,
            'nuevo_estado' => $nuevoEstado
        ]);
    }

    /**
     * Registrar intento o entrevista telefónica.
     */
    public function guardarLlamada(Request $request)
    {
        $idStr = $request->input('id');
        $userId = null;
        $idTemporal = $idStr;
        if (str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
            $idTemporal = null;
        }

        $llamada = Llamada::create([
            'user_id' => $userId,
            'identificador_temporal' => $idTemporal,
            'resultado' => $request->input('result', 'No contesta'),
            'observaciones' => $request->input('comment', ''),
            'encuestador' => auth()->check() ? auth()->user()->name : 'Entrevistador institucional',
            'fecha_hora' => now()
        ]);

        return response()->json([
            'success' => true,
            'llamada_id' => $llamada->id
        ]);
    }

    /**
     * Auditar acceso a los datos de contacto de un egresado.
     */
    public function auditarContacto(Request $request)
    {
        $idStr = $request->input('id');
        $userId = null;
        if (str_starts_with($idStr, 'REAL-')) {
            $userId = (int) str_replace('REAL-', '', $idStr);
        }

        $log = AccesoContacto::create([
            'user_id_consultante' => auth()->id(),
            'rol_consultante' => $request->input('role', 'coordinacion'),
            'user_id_consultado' => $userId,
            'identificador_consultado' => $idStr,
            'finalidad' => $request->input('purpose', 'Seguimiento semestral'),
            'fecha_hora' => now()
        ]);

        return response()->json([
            'success' => true,
            'log_id' => $log->id
        ]);
    }

    /**
     * Crear o actualizar un grupo de entrevistadores para seguimiento.
     */
    public function guardarGrupo(Request $request)
    {
        $grupo = GrupoEntrevistador::create([
            'nombre' => $request->input('nombre', 'Equipo de Entrevistadores'),
            'programa' => $request->input('programa'),
            'cohorte' => $request->input('cohorte'),
            'coordinador_id' => auth()->id(),
            'entrevistadores' => $request->input('entrevistadores', []),
            'egresados_ids' => $request->input('egresados_ids', []),
            'estado' => 'activo',
            'observaciones' => $request->input('observaciones', '')
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Grupo de entrevistadores creado exitosamente.',
            'grupo' => $grupo
        ]);
    }

    /**
     * Eliminar un grupo de entrevistadores.
     */
    public function eliminarGrupo($id)
    {
        $grupo = GrupoEntrevistador::findOrFail($id);
        $grupo->delete();

        return response()->json([
            'success' => true,
            'message' => 'Grupo eliminado correctamente.'
        ]);
    }

    /**
     * Generar datos y enlace directo para compartir por WhatsApp.
     */
    public function generarEnlaceWhatsapp(Request $request)
    {
        $campana = $request->input('campana', '2026-II');
        $urlCampana = route('seguimiento-egresados.campana-publica');
        
        $mensajeDefault = "¡Hola! 🎓 La EESP Pukllasunchis te invita a participar en la Campaña de Actualización de Egresados ({$campana}). \n\n"
            . "Te tomará solo 3 minutos confirmar tus datos y contarnos sobre tu práctica profesional y necesidades de formación continua:\n"
            . "👉 {$urlCampana}\n\n"
            . "¡Gracias por formar parte de nuestra comunidad educativa!";

        $whatsappUrl = 'https://api.whatsapp.com/send?text=' . urlencode($mensajeDefault);

        return response()->json([
            'success' => true,
            'campana' => $campana,
            'url' => $urlCampana,
            'mensaje' => $mensajeDefault,
            'whatsapp_url' => $whatsappUrl
        ]);
    }

    /**
     * Enlace público de campaña (para WhatsApp / Magic Link).
     */
    public function campanaPublica()
    {
        return view('seguimiento_egresados.campana_publica');
    }
}
