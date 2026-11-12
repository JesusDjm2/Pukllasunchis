<?php

namespace App\Services\Titulacion;

use App\Models\User;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

/**
 * Reconoce a estudiantes y egresados a partir de lo que escribieron en un formulario de Google
 * (correo, DNI, nombre o celular). Es la misma regla que ImportarCartasFormulario, pero sobre todas las
 * personas que pueden tener un trámite de titulación (no solo quienes ya tienen proceso), porque la
 * solicitud de PTI es justamente la que lo inicia.
 */
class ReconocerPersonas
{
    public const METODOS = ['correo' => 'Correo', 'dni' => 'DNI', 'nombre' => 'Nombre', 'celular' => 'Celular'];

    private Collection $lista;

    private array $porCorreo;

    private array $porDni;

    private array $tokens;

    private array $celulares;

    public function __construct()
    {
        $this->lista = User::query()
            ->where(fn ($q) => $q->where('condicion', 'Egresado')
                ->orWhereHas('roles', fn ($r) => $r->whereIn('name', ['alumno', 'alumnoB']))
                ->orWhereHas('seguimientoTitulacion'))
            ->with(['seguimientoTitulacion.pti', 'alumno:id,user_id,numero', 'alumnoB:id,user_id,numero'])
            ->get(['id', 'name', 'apellidos', 'dni', 'email', 'telefono', 'programa_id', 'condicion']);

        $this->porCorreo = $this->lista->filter(fn ($u) => filled($u->email))->keyBy(fn ($u) => mb_strtolower(trim($u->email)))->all();
        $this->porDni = $this->lista->filter(fn ($u) => filled($u->dni))->keyBy(fn ($u) => preg_replace('/\D/', '', $u->dni))->all();
        $this->tokens = $this->lista->mapWithKeys(fn ($u) => [$u->id => self::tokensDe($u->name.' '.$u->apellidos)])->all();
        $this->celulares = $this->lista->mapWithKeys(fn ($u) => [$u->id => collect([$u->telefono, $u->alumno?->numero, $u->alumnoB?->numero])
            ->map(fn ($c) => substr(preg_replace('/\D/', '', (string) $c), -9))->filter(fn ($c) => strlen($c) === 9)->values()->all()])->all();
    }

    public function porCorreo(string $correo): ?User
    {
        return $this->porCorreo[mb_strtolower(trim($correo))] ?? null;
    }

    public function porDni(string $dni): ?User
    {
        $dni = preg_replace('/\D/', '', $dni);

        return $dni !== '' ? ($this->porDni[$dni] ?? null) : null;
    }

    /**
     * Por nombre, solo si es único: comparte al menos 3 palabras (o todas, si tiene menos) con el nombre
     * completo de la persona.
     *
     * @return array{0:?User, 1:?string} [persona, por qué no]
     */
    public function porNombre(string $nombre): array
    {
        $t = self::tokensDe($nombre);
        if ($t->count() < 2) {
            return [null, 'Nombre incompleto'];
        }
        $hallados = $this->lista->filter(fn ($u) => count($this->tokens[$u->id]) >= 2
            && $this->tokens[$u->id]->intersect($t)->count() >= min(3, $this->tokens[$u->id]->count()));

        return match ($hallados->count()) {
            1 => [$hallados->first(), null],
            0 => [null, 'No se encontró a la persona'],
            default => [null, 'Varias personas con ese nombre'],
        };
    }

    /** Por cualquiera de los celulares escritos (9 dígitos), solo si es una sola persona. */
    public function porCelular(string $texto): ?User
    {
        preg_match_all('/9\d{8}/', preg_replace('/[^\d]/', '', str_replace([' ', '-'], '', $texto)) ?: '', $m);
        $numeros = array_unique($m[0] ?? []);
        $hallados = $this->lista->filter(fn ($u) => array_intersect($numeros, $this->celulares[$u->id]) !== []);

        return $hallados->count() === 1 ? $hallados->first() : null;
    }

    /** ¿Ese nombre escrito corresponde a esa persona? */
    public function esLaMisma(string $nombre, User $u): bool
    {
        $t = self::tokensDe($nombre);

        return $t->isNotEmpty() && ($this->tokens[$u->id] ?? collect())->intersect($t)->count() >= min(2, $t->count());
    }

    public static function tokensDe(string $texto): Collection
    {
        return collect(preg_split('/[^a-z]+/', Str::lower(Str::ascii($texto))))->filter(fn ($t) => strlen($t) > 2)->values();
    }
}
