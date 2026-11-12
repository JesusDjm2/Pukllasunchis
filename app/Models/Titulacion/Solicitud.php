<?php

namespace App\Models\Titulacion;

use App\Models\User;
use Illuminate\Database\Eloquent\Model;

/**
 * Solicitud de un trámite de titulación (los formularios que hoy son de Google), una fila por envío.
 * 'tipo' = clave de App\Services\Titulacion\TramitesExtraordinarios. Por ahora: 'aprobacion_pti'.
 *  - recibida:  llegó la solicitud; Investigación debe atenderla.
 *  - atendida:  ya tiene su proceso (PTI registrado al iniciar el proceso desde aquí, o ya existía).
 *  - observada: se devolvió con un comentario.
 */
class Solicitud extends Model
{
    protected $table = 'titulacion_solicitudes';

    protected $fillable = [
        'tipo', 'solicitante_id', 'seguimiento_id', 'correo', 'integrantes', 'celulares', 'programa_estudios', 'ciclo',
        'titulo', 'datos', 'archivo', 'comprobante', 'declaracion', 'estado', 'observacion', 'origen', 'clave_importacion',
        'atendido_por', 'atendido_en',
    ];

    protected $casts = [
        'integrantes' => 'array',
        'datos' => 'array',
        'declaracion' => 'boolean',
        'atendido_en' => 'datetime',
    ];

    public const PTI = 'aprobacion_pti';

    /** Revisión de originalidad (Turnitin), del TI o de la Tesis (datos.etapa). */
    public const ORIGINALIDAD = 'revision_originalidad';

    public const ETAPAS = ['ti' => 'Trabajo de Investigación', 'tesis' => 'Tesis'];

    /** Pago en Caja Cusco de cada revisión de originalidad: etapa => [código, monto, concepto] (PDF oficial 2026-II). */
    public const PAGO_ORIGINALIDAD = [
        'ti' => [16, 25.00, 'UNA REVISIÓN DE TURNITIN DE TI'],
        'tesis' => [37, 25.00, 'UNA REVISIÓN TURNITIN DE TESIS'],
    ];

    /** Similitud máxima (%) para que el documento quede apto. */
    public const SIMILITUD_MAXIMA = 20;

    /** Días hábiles en que se entrega el reporte de originalidad. */
    public const DIAS_ORIGINALIDAD = 20;

    public function etapa(): ?string
    {
        return $this->datos['etapa'] ?? null;
    }

    /** % de similitud del reporte (null si todavía no hay resultado). */
    public function similitud(): ?float
    {
        return isset($this->datos['similitud']) ? (float) $this->datos['similitud'] : null;
    }

    public function esApto(): ?bool
    {
        return $this->similitud() === null ? null : $this->similitud() <= self::SIMILITUD_MAXIMA;
    }

    /** Fecha límite de la respuesta, en días hábiles desde que se recibió (null si el tipo no tiene plazo). */
    public function fechaLimite(): ?\Carbon\CarbonInterface
    {
        return $this->tipo === self::ORIGINALIDAD
            ? \App\Services\Titulacion\DiasHabiles::sumar($this->created_at ?? now(), self::DIAS_ORIGINALIDAD)
            : null;
    }

    /** Claves guardadas (no cambiar) => nombre visible. */
    public const ESTADOS = ['recibida' => 'Por atender', 'atendida' => 'Atendida', 'observada' => 'Observada'];

    public const ORIGENES = ['sistema' => 'Formulario del sistema', 'formulario' => 'Importada del formulario de Google', 'manual' => 'Registrada a mano'];

    public const PROGRAMAS_ESTUDIOS = ['Educación Inicial', 'Educación Primaria', 'Educación Primaria Intercultural Bilingüe'];

    /** «Ciclo o programa» del formulario del PTI. */
    public const CICLOS = ['IX Ciclo', 'X Ciclo', 'Egresado', 'PPD'];

    /** Áreas de investigación (formulario del PTI). */
    public const AREAS = [
        'D1' => 'Área D1: Preparación para el aprendizaje de los estudiantes',
        'D2' => 'Área D2: Enseñanza para el aprendizaje de los estudiantes',
        'D3' => 'Área D3: Participación en gestión de la escuela articulada a la comunidad',
        'D4' => 'Área D4: Desarrollo personal, profesional e identidad docente',
    ];

    /** Líneas de investigación; la clave empieza con su área. */
    public const LINEAS = [
        'D1-1' => 'D1-Línea 1. Comprensión y conocimiento de estudiantes, su contexto y su territorio',
        'D1-2' => 'D1-Línea 2. Propuestas curriculares alternativas, contextualizadas y diversificadas',
        'D1-3' => 'D1-Línea 3. Revitalización y/o fortalecimiento de lenguas originarias',
        'D1-4' => 'D1-Línea 4. Recuperación, fortalecimiento e incorporación de saberes culturales en prácticas pedagógicas y/o curriculares',
        'D2-1' => 'D2-Línea 1. Clima de aula y relacionamiento docente - estudiante - familia - comunidad',
        'D2-2' => 'D2-Línea 2. Enseñanza mediante modelos pedagógicos alternativos en contextos socioculturales diversos',
        'D2-3' => 'D2-Línea 3. Evaluación, valoración y atención de procesos de aprendizaje',
        'D2-4' => 'D2-Línea 4. Diversidad e Inclusión educativa',
        'D3-1' => 'D3-Línea 1. Promoción de la participación comunitaria en la gestión de escuelas',
        'D3-2' => 'D3-Línea 2. Integración y colaboración escuela y familia, escuela y comunidad',
        'D3-3' => 'D3-Línea 3. Gestión Educativa Intercultural',
        'D4-1' => 'D4-Línea 1. Reflexión sobre el desempeño y la práctica pedagógica',
        'D4-2' => 'D4-Línea 2. Ética de la profesión docente',
        'D4-3' => 'D4-Línea 3. Desarrollo personal del docente en su ejercicio profesional',
        'D4-4' => 'D4-Línea 4. Desarrollo personal del estudiante en su proceso de aprendizaje',
        'D4-5' => 'D4-Línea 5. Educación mediada por Tecnologías de la Información y la Comunicación (TIC)',
    ];

    public function solicitante()
    {
        return $this->belongsTo(User::class, 'solicitante_id');
    }

    public function seguimiento()
    {
        return $this->belongsTo(SeguimientoTitulacion::class, 'seguimiento_id');
    }

    public function atendidoPor()
    {
        return $this->belongsTo(User::class, 'atendido_por');
    }

    public function scopeDeTipo($q, string $tipo)
    {
        return $q->where('tipo', $tipo);
    }

    /** Nombres de los integrantes, como los escribieron. */
    public function nombresIntegrantes(): array
    {
        return array_values(array_filter(array_map(fn ($i) => trim((string) ($i['nombre'] ?? '')), $this->integrantes ?? [])));
    }

    /** Ids de los integrantes que se reconocieron en el sistema (sin repetir). */
    public function idsIntegrantes(): array
    {
        return array_values(array_unique(array_filter(array_map(fn ($i) => $i['user_id'] ?? null, $this->integrantes ?? []))));
    }

    /** Enlaces (uno o varios, separados por coma) de un campo con archivos del formulario de Google. */
    public static function enlaces(?string $valor): array
    {
        return array_values(array_filter(array_map('trim', preg_split('/[,\s]+/', (string) $valor)), fn ($e) => str_starts_with($e, 'http')));
    }

    /** Carpeta (disco local, no pública) de los archivos que se suben desde el formulario del sistema. */
    public const CARPETA = 'titulacion/solicitudes';

    /** Archivos que guarda el sistema: «local:{uuid}.{ext}» separados por coma. */
    public static function locales(?string $valor): array
    {
        return array_values(array_filter(array_map(fn ($e) => preg_match('/^local:([0-9a-f\-]{36}\.(pdf|docx?|jpe?g|png))$/i', trim($e), $m) ? $m[1] : null,
            explode(',', (string) $valor))));
    }

    /**
     * Enlaces para abrir los archivos de un campo ('archivo' o 'comprobante'): los de Drive (formulario de Google)
     * tal cual, y los del sistema por la ruta que revisa quién puede verlos.
     */
    public function urls(string $campo): array
    {
        $valor = $this->valorArchivo($campo);
        $locales = array_map(fn ($n) => route('mi-titulacion.solicitud-pti.archivo', [$this->id, $campo, $n]), array_keys(self::locales($valor)));

        return array_merge(self::enlaces($valor), $locales);
    }

    /** Campos con archivos: 'archivo' y 'comprobante' (columnas) y 'reporte' (lo que sube quien atiende, en datos). */
    public const CAMPOS_ARCHIVO = ['archivo', 'comprobante', 'reporte'];

    public function valorArchivo(string $campo): ?string
    {
        return match ($campo) {
            'archivo', 'comprobante' => $this->{$campo},
            'reporte' => $this->datos['reporte'] ?? null,
            default => null,
        };
    }

    /** Solicitudes de un tipo en las que figura la persona (la envió o es integrante), de la más reciente a la más antigua. */
    public static function dePersona(string $tipo, User $u)
    {
        return self::deTipo($tipo)
            ->where(fn ($q) => $q->where('solicitante_id', $u->id)->orWhereJsonContains('integrantes', ['user_id' => $u->id]))
            ->orderByDesc('created_at')->orderByDesc('id');
    }

    /** Solicitudes de PTI en las que figura la persona. */
    public static function ptiDe(User $u)
    {
        return self::dePersona(self::PTI, $u);
    }

    /** ¿La persona figura en esta solicitud? */
    public function incluye(User $u): bool
    {
        return $this->solicitante_id === $u->id || in_array($u->id, $this->idsIntegrantes(), true);
    }

    public function area(): ?string
    {
        return self::AREAS[$this->datos['area'] ?? ''] ?? ($this->datos['area_texto'] ?? null);
    }

    public function linea(): ?string
    {
        return self::LINEAS[$this->datos['linea'] ?? ''] ?? ($this->datos['linea_texto'] ?? null);
    }

    /** Clave del área a partir del texto del formulario («Área D2: …» → D2). */
    public static function claveArea(string $texto): ?string
    {
        return preg_match('/\bD([1-4])\b/u', $texto, $m) ? 'D'.$m[1] : null;
    }

    /** Clave de la línea a partir del texto del formulario («D2-Línea 3. …» → D2-3). */
    public static function claveLinea(string $texto): ?string
    {
        return preg_match('/D([1-4])\s*-\s*L[ií]nea\s*(\d)/iu', $texto, $m) && isset(self::LINEAS['D'.$m[1].'-'.$m[2]]) ? 'D'.$m[1].'-'.$m[2] : null;
    }
}
