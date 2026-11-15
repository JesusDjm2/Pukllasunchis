<?php

namespace App\Models\Titulacion;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * Solicitud de la carta de aceptación de la Institución Educativa donde se hará la investigación.
 * Hay una fila por integrante del grupo con el mismo «lote»; todas cambian juntas.
 *  - enviada:   la envió el estudiante; Investigación debe subir la carta u observarla.
 *  - emitida:   Investigación subió la carta (el paso queda logrado para todo el grupo).
 *  - observada: Investigación la devolvió con un comentario; el estudiante puede enviarla de nuevo.
 */
class CartaAceptacion extends Model
{
    protected $table = 'titulacion_cartas_aceptacion';

    protected $fillable = [
        'lote', 'seguimiento_id', 'solicitante_id', 'correo', 'grupo', 'celular', 'programa_estudios', 'programa_formacion',
        'institucion', 'director', 'titulo', 'comprobante', 'declaracion', 'estado', 'observacion', 'carta', 'numero_carta', 'atendido_por', 'atendido_en',
        'origen', 'datos_extra', 'clave_importacion',
    ];

    protected $casts = [
        'declaracion' => 'boolean',
        'atendido_en' => 'datetime',
        'datos_extra' => 'array',
    ];

    /** De dónde vino la solicitud. */
    public const ORIGENES = ['sistema' => 'Formulario del sistema', 'formulario' => 'Importada del formulario de Google', 'manual' => 'Registrada a mano'];

    /** El comprobante es un enlace (Drive, del formulario de Google) y no un archivo guardado en el sistema. */
    public function comprobanteEsEnlace(): bool
    {
        return str_starts_with((string) $this->comprobante, 'http');
    }

    /**
     * Carpeta «secretaria» de la unidad compartida de Secretaría (Google Drive): ahí, en la subcarpeta
     * SUBCARPETA_DRIVE, se guardan los comprobantes desde el 06/11. Se puede cambiar con
     * services.google_drive.folders.cartas (GOOGLE_DRIVE_CARTAS_FOLDER_ID).
     */
    public const CARPETA_DRIVE = '1IDhMGwoO19NWyH5mcXWg9KhfjFGEfg2I';

    public const SUBCARPETA_DRIVE = 'Comprobantes · Carta de Presentación';

    /**
     * Comprobante subido a Drive por el sistema: se guarda como «drive:{id}:{ext}». Devuelve [id, ext] o null.
     *
     * @return array{0:string, 1:string}|null
     */
    public function comprobanteEnDrive(): ?array
    {
        return preg_match('/^drive:([\w-]+):(\w+)$/', (string) $this->comprobante, $m) ? [$m[1], strtolower($m[2])] : null;
    }

    /** Extensión del comprobante (para saber si se puede mostrar como imagen). */
    public function extensionComprobante(): string
    {
        return $this->comprobanteEnDrive()[1] ?? strtolower(pathinfo((string) $this->comprobante, PATHINFO_EXTENSION));
    }

    /** Claves guardadas (no cambiar) => nombre visible. */
    public const ESTADOS = ['enviada' => 'Recibida', 'emitida' => 'Aprobada', 'observada' => 'Observada'];

    /**
     * Correo de aprobación que Secretaría envía al grupo (texto oficial de Secretaría, 05/11; se puede
     * editar antes de enviar). Van adjuntas la Carta de Presentación (Word, llena) y el modelo de Carta de
     * Aceptación. Marcadores disponibles: {institucion}, {director}, {titulo}.
     */
    public const MENSAJE_APROBACION = "Estimados/as:\n\n"
        ."Su solicitud ha sido atendida. Por la presente, adjunto la Carta de Presentación para la aplicación de su trabajo de investigación.\n\n"
        ."Asimismo, se adjunta el modelo de Carta de Aceptación, que es el documento mediante el cual la Institución Educativa responde aceptando la aplicación. "
        ."Esta carta debe contar obligatoriamente con la firma y sello de la dirección. Recuerden que dicha carta de aceptación forma parte del expediente requerido para el proceso de titulación.";

    /** Días hábiles en que Secretaría responde una solicitud. */
    public const DIAS_RESPUESTA = 3;

    /** Fecha límite de la respuesta: 3 días hábiles desde que se recibió. */
    public function fechaLimite(): \Carbon\CarbonInterface
    {
        return \App\Services\Titulacion\DiasHabiles::sumar($this->created_at ?? now(), self::DIAS_RESPUESTA);
    }

    /** Respuesta automática de Secretaría al recibir la solicitud (pantalla, aviso y correo). */
    public function mensajeRecepcion(): string
    {
        return 'Su solicitud fue recibida exitosamente. Secretaría le dará una respuesta en '.self::DIAS_RESPUESTA
            .' días hábiles (a más tardar el '.$this->fechaLimite()->format('d/m/Y').').';
    }

    public function mensajeAprobacion(): string
    {
        return strtr(self::MENSAJE_APROBACION, [
            '{institucion}' => $this->institucion, '{director}' => $this->director, '{titulo}' => '“'.$this->titulo.'”',
        ]);
    }

    public const PROGRAMAS_ESTUDIOS = ['Educación Inicial', 'Educación Primaria Intercultural Bilingüe', 'Educación Primaria'];

    public const PROGRAMAS_FORMACION = [
        'FID' => 'Formación Inicial Docente (FID) - 5 años de estudios',
        'PPD' => 'Profesionalización Docente (PPD) - 1 año de estudios',
        'SEE' => 'Segunda Especialidad en Educación (SEE) - 2 años de estudios',
    ];

    /** Dominio de los correos institucionales: solo con ellos se puede solicitar. */
    public const DOMINIO = 'pukllavirtual.edu.pe';

    /** Carpeta (disco local, no pública) de comprobantes y cartas. */
    public const CARPETA = 'titulacion/cartas';

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function atendidoPor()
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    /** Las filas del mismo envío (todo el grupo). */
    public function delLote()
    {
        return self::where('lote', $this->lote);
    }

    public static function esCorreoInstitucional(?string $correo): bool
    {
        return Str::endsWith(mb_strtolower(trim((string) $correo)), '@'.self::DOMINIO);
    }

    /** Ruta en disco de un archivo de la carpeta (null si el nombre no es válido o no existe). */
    public static function ruta(?string $nombre): ?string
    {
        if (! $nombre || ! preg_match('/^[0-9a-f\-]{36}\.(pdf|jpe?g|png|docx)$/i', $nombre) || ! Storage::disk('local')->exists(self::CARPETA.'/'.$nombre)) {
            return null;
        }

        return Storage::disk('local')->path(self::CARPETA.'/'.$nombre);
    }
}
