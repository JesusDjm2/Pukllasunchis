<?php

namespace App\Models\Titulacion\Concerns;

use App\Models\Titulacion\Asesor;
use App\Models\User;

/**
 * Asesor de un PTI/TI/Tesis/acta (o jurado): un docente (users) o un asesor
 * del catálogo de Titulación. En los selects el valor es el id del docente
 * ("12") o "a" + id del catálogo ("a5"). La columna del docente se indica con
 * $columnaDocente (por defecto asesor_id; los jurados usan docente_id).
 */
trait TieneAsesor
{
    public function asesorExterno()
    {
        return $this->belongsTo(Asesor::class, 'asesor_externo_id');
    }

    protected function columnaDocente(): string
    {
        return property_exists($this, 'columnaDocente') ? $this->columnaDocente : 'asesor_id';
    }

    /** Nombre para mostrar, sea docente o del catálogo. */
    public function asesorNombre(): ?string
    {
        $docente = $this->getRelationValue($this->columnaDocente() === 'docente_id' ? 'docente' : 'asesor');
        if ($docente instanceof User) {
            return trim($docente->name.' '.$docente->apellidos);
        }

        return $this->asesorExterno?->nombreCompleto();
    }

    /** Valor del select ("12", "a5" o null). */
    public function asesorValor(): ?string
    {
        $col = $this->columnaDocente();
        if ($this->{$col}) {
            return (string) $this->{$col};
        }

        return $this->asesor_externo_id ? 'a'.$this->asesor_externo_id : null;
    }

    /**
     * Convierte el valor del select en las dos columnas.
     *
     * @return array<string, int|null>
     */
    public static function columnasAsesor(?string $valor, string $columnaDocente = 'asesor_id'): array
    {
        $valor = trim((string) $valor);

        return match (true) {
            $valor === '' => [$columnaDocente => null, 'asesor_externo_id' => null],
            str_starts_with($valor, 'a') => [$columnaDocente => null, 'asesor_externo_id' => (int) substr($valor, 1)],
            default => [$columnaDocente => (int) $valor, 'asesor_externo_id' => null],
        };
    }
}
