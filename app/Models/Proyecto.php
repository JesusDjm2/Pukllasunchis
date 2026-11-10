<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

/**
 * Proyecto Integrador de un par de ciclos (ciclos.proyecto_id). Lo muestra el sílabo (Sección III) y
 * define en qué proyecto trabaja cada grupo de Investigaciones: un alumno solo participa en el de SU ciclo.
 * La descripción es texto enriquecido (CKEditor) y se sanea al guardar con limpiarHtml().
 */
class Proyecto extends Model
{
    use HasFactory;

    protected $table = 'proyectos';

    protected $fillable = ['nombre', 'producto', 'descripcion'];

    public function ciclos()
    {
        return $this->hasMany(Ciclo::class);
    }

    /** Docentes encargados u organizadores del proyecto (aparte de los docentes de los cursos). */
    public function docentes()
    {
        return $this->belongsToMany(Docente::class, 'proyecto_docente')->withPivot('rol')->withTimestamps();
    }

    /**
     * ¿Está Investigaciones instalado? En producción el módulo aún no existe (ni su tabla): el CRUD de proyectos
     * oculta conteos y textos de Investigaciones hasta que se despliegue, sin cambiar este código.
     */
    public static function taqeActivo(): bool
    {
        static $activo;

        return $activo ??= \Illuminate\Support\Facades\Schema::hasTable('taqe_grupos');
    }

    public function taqeGrupos()
    {
        return $this->hasMany(TaqeGrupo::class);
    }

    /** HTML permitido en la descripción: formato básico de CKEditor, sin scripts, estilos ni eventos. */
    public static function limpiarHtml(?string $html): string
    {
        $config = \HTMLPurifier_Config::createDefault();
        $config->set('Cache.SerializerPath', storage_path('framework/cache'));
        $config->set('HTML.Allowed', 'p,br,strong,b,em,i,u,s,ul,ol,li,blockquote,h3,h4,a[href|target|rel],span');
        $config->set('Attr.AllowedFrameTargets', ['_blank']);
        $config->set('URI.AllowedSchemes', ['http' => true, 'https' => true, 'mailto' => true]);
        $config->set('AutoFormat.RemoveEmpty', true);

        return trim((new \HTMLPurifier($config))->purify((string) $html));
    }

    /**
     * Descripción lista para mostrar con {!! !!}: las antiguas son texto plano (se pasan a párrafos)
     * y las nuevas, HTML ya saneado al guardar (se vuelve a sanear por seguridad).
     */
    public function descripcionHtml(): string
    {
        $texto = (string) $this->descripcion;
        if ($texto === strip_tags($texto)) {
            return collect(preg_split('/\R{2,}/', trim($texto)))
                ->filter()
                ->map(fn ($p) => '<p>'.nl2br(e(trim($p))).'</p>')
                ->implode('');
        }

        return self::limpiarHtml($texto);
    }
}
