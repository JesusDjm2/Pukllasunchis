<?php

namespace App\Services;

use App\Models\User;

/**
 * Estado de matrícula de un estudiante en un periodo, a partir de SU CUENTA (no de la ficha):
 * así también aparece quien nunca llenó la ficha (antes era invisible en "Matrículas").
 * Lo usan el directorio de /Administrador y su exportación, para que digan siempre lo mismo.
 *
 * Requiere precargadas `alumno.matriculas` / `alumnoB.matriculas` YA filtradas al periodo.
 *
 * estado:
 *  - no_aplica     egresado o retirado: no se le pide matrícula
 *  - sin_ficha     no llenó la ficha de matrícula (no puede matricularse)
 *  - sin_matricula tiene ficha pero no matrícula en el periodo
 *  - matriculado   con matrícula en el periodo (ver voucher / ficha enviada)
 */
class EstadoMatricula
{
    public const ETIQUETAS = [
        'matriculado' => 'Matriculado',
        'sin_matricula' => 'Sin matrícula',
        'sin_ficha' => 'Sin ficha',
        'no_aplica' => 'No aplica',
    ];

    public static function de(User $u): object
    {
        $esPpd = $u->roles->contains('name', 'alumnoB') || (! $u->alumno && $u->alumnoB);
        $ficha = $esPpd ? $u->alumnoB : $u->alumno;
        $matricula = $ficha?->matriculas?->first();

        $estado = match (true) {
            $u->condicion === 'Egresado', $u->perfil === 'Retirado' => 'no_aplica',
            ! $ficha => 'sin_ficha',
            ! $matricula => 'sin_matricula',
            default => 'matriculado',
        };

        return (object) [
            'estado' => $estado,
            'etiqueta' => self::ETIQUETAS[$estado],
            'modalidad' => $esPpd ? 'ppd' : 'fid',
            'ficha' => $ficha,
            'matricula' => $matricula,
            'voucher' => $matricula?->comprobante,
            'voucher_imagen' => $esPpd && $matricula?->voucher_imagen ? asset('vouchers/ppd/' . $matricula->voucher_imagen) : null,
            'voucher_verificado' => (bool) $matricula?->voucher_verificado,
            'ficha_enviada_at' => $matricula?->ficha_enviada_at,
            'fecha' => $matricula?->fecha_completado ?? $matricula?->created_at,
        ];
    }
}
