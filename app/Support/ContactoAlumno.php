<?php

namespace App\Support;

use App\Models\Alumno;
use App\Models\User;

/**
 * Ficha de contacto de un alumno con la misma forma para FID (modelo Alumno) y PPD (modelo
 * User + ficha alumnoB), para que las listas del docente y del tutor usen una sola vista.
 */
class ContactoAlumno
{
    public function __construct(
        public string $clave,
        public string $nombre,
        public ?string $foto,
        public ?string $correo,
        public ?string $numero,
        public ?string $numeroReferencia,
        /** @var array<int, array{0: string, 1: string}> [texto, variante] */
        public array $etiquetas = [],
    ) {}

    public static function desdeAlumno(Alumno $alumno, ?int $cicloDelCurso = null): self
    {
        $user = $alumno->user;
        $etiquetas = [];
        if ($user?->beca == 1) {
            $etiquetas[] = ['Beca', 'ok'];
        }
        if ($alumno->es_inhabilitado ?? $user?->hasRole('inhabilitado')) {
            $etiquetas[] = [$user?->perfil ?: 'Inhabilitado', 'warn'];
        }
        if ($cicloDelCurso && $alumno->ciclo_id && (int) $alumno->ciclo_id !== $cicloDelCurso) {
            $etiquetas[] = ['Ciclo '.(optional($alumno->ciclo)->nombre ?? '—'), 'info'];
        }
        if ($alumno->condicion) {
            $etiquetas[] = [$alumno->condicion, 'muted'];
        }

        return new self(
            clave: 'fid-'.$alumno->id,
            nombre: trim($alumno->apellidos.', '.$alumno->nombres, ', '),
            foto: $user?->foto ? asset('img/estudiantes/'.$user->foto) : null,
            // El de la cuenta es con el que inicia sesión; el de la ficha a veces tiene erratas.
            correo: $user?->email ?: $alumno->email,
            numero: $alumno->numero,
            numeroReferencia: $alumno->numero_referencia,
            etiquetas: $etiquetas,
        );
    }

    public static function desdeUsuarioPpd(User $user): self
    {
        $ficha = $user->alumnoB;
        $etiquetas = [];
        if ($user->beca == 1) {
            $etiquetas[] = ['Beca', 'ok'];
        }
        if ($user->es_inhabilitado ?? $user->hasRole('inhabilitado')) {
            $etiquetas[] = [$user->perfil ?: 'Inhabilitado', 'warn'];
        }
        if ($user->ciclo) {
            $etiquetas[] = ['Ciclo '.$user->ciclo->nombre, 'info'];
        }

        // PPD convertidos desde postulante guardan la ruta completa (img/admin/ppd/…);
        // el resto, solo el nombre de archivo dentro de img/estudiantes/.
        $foto = $user->foto
            ? asset(str_contains($user->foto, '/') ? $user->foto : 'img/estudiantes/'.$user->foto)
            : null;

        return new self(
            clave: 'ppd-'.$user->id,
            nombre: trim($user->apellidos.', '.$user->name, ', '),
            foto: $foto,
            correo: $user->email ?: $ficha?->email,
            numero: $ficha?->numero,
            numeroReferencia: $ficha?->numero_referencia,
            etiquetas: $etiquetas,
        );
    }

    /** $nombre viene como "Apellidos, Nombres". */
    public function primerNombre(): string
    {
        $nombres = trim(explode(',', $this->nombre, 2)[1] ?? $this->nombre);

        return mb_convert_case(mb_strtolower(explode(' ', $nombres)[0]), MB_CASE_TITLE);
    }

    public function iniciales(): string
    {
        $apellido = trim(explode(',', $this->nombre, 2)[0]);

        return mb_strtoupper(mb_substr($this->primerNombre(), 0, 1).mb_substr($apellido, 0, 1));
    }

    public function whatsapp(string $texto): ?string
    {
        return Telefono::whatsapp($this->numero, $texto);
    }

    public function whatsappReferencia(string $texto): ?string
    {
        return Telefono::whatsapp($this->numeroReferencia, $texto);
    }
}
