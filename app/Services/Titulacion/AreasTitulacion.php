<?php

namespace App\Services\Titulacion;

/**
 * Qué área puede hacer qué en Titulación. Ver el proceso lo pueden Secretaría e Investigación;
 * modificar, cada una lo suyo. Es la única lista: la aplica el middleware AreaTitulacion en el
 * servidor y las vistas la usan para mostrar los formularios en modo lectura.
 *
 *  - secretaria: rol «secretaria». Las RD y el pago del trámite de sustentación.
 *  - investigacion: rol «gestor-titulacion». Todo lo demás del proceso.
 *  - super-admin: todo, y además lo que solo es suyo (sincronizar planilla).
 *
 * Una acción de escritura que no esté en la lista queda solo para el super-admin.
 */
class AreasTitulacion
{
    public const SECRETARIA = 'secretaria';

    public const INVESTIGACION = 'investigacion';

    public const SOLO_SUPER_ADMIN = 'super-admin';

    /** Rol que da cada área. */
    private const ROLES = [self::SECRETARIA => 'secretaria', self::INVESTIGACION => 'gestor-titulacion'];

    public const NOMBRES = [self::SECRETARIA => 'Secretaría', self::INVESTIGACION => 'el Área de Investigación', self::SOLO_SUPER_ADMIN => 'el super-admin'];

    /** Ruta (sin el prefijo «titulacion.») => área a la que le corresponde. Las que no están son de lectura. */
    public const RUTAS = [
        'rd.crear' => self::SECRETARIA,
        'rd.emitir' => self::SECRETARIA,
        'rd.subir' => self::SECRETARIA,
        'rd.correo' => self::SECRETARIA,
        'resoluciones.guardar' => self::SECRETARIA,
        'resoluciones.actualizar' => self::SECRETARIA,
        'resoluciones.eliminar' => self::SECRETARIA,
        'pti.ampliacion' => self::SECRETARIA,
        'tesis.tramite' => self::SECRETARIA,

        'crear-cuentas-masivo.preview' => self::INVESTIGACION,
        'crear-cuentas-masivo.ejecutar' => self::INVESTIGACION,
        'importaciones.crear-cuenta.form' => self::INVESTIGACION,
        'importaciones.crear-cuenta.guardar' => self::INVESTIGACION,
        'importaciones.completar-ficha.form' => self::INVESTIGACION,
        'importaciones.completar-ficha.guardar' => self::INVESTIGACION,
        'iniciar' => self::INVESTIGACION,
        'inicio.buscar' => self::INVESTIGACION,
        'inicio.crear' => self::INVESTIGACION,
        'inicio.guardar' => self::INVESTIGACION,
        'estado.actualizar' => self::INVESTIGACION,
        'pti.guardar' => self::INVESTIGACION,
        'pti.nuevo' => self::INVESTIGACION,
        'aviso-pti.enviar' => self::INVESTIGACION,
        'ti.informe' => self::INVESTIGACION,
        'cartas.atender' => self::SECRETARIA,
        'cartas.externa' => self::SECRETARIA,
        'grupo.editar' => self::SECRETARIA,
        'grupo.cambiar' => self::SECRETARIA,
        'rdpti.completar' => self::SECRETARIA,
        'rdpti.normalizar' => self::SOLO_SUPER_ADMIN,
        'cartas.importar' => self::SOLO_SUPER_ADMIN,
        'cartas.importar.previa' => self::SOLO_SUPER_ADMIN,
        'cartas.importar.vista' => self::SOLO_SUPER_ADMIN,
        'cartas.importar.aplicar' => self::SOLO_SUPER_ADMIN,
        'rdpti.aplicar' => self::SOLO_SUPER_ADMIN,
        'ti.correo-jurado' => self::INVESTIGACION,
        'participantes.guardar' => self::INVESTIGACION,
        'participantes.eliminar' => self::INVESTIGACION,
        'tesis.guardar' => self::INVESTIGACION,
        'actas.guardar' => self::INVESTIGACION,
        'actas.actualizar' => self::INVESTIGACION,
        'actas.eliminar' => self::INVESTIGACION,
        'jurados.guardar' => self::INVESTIGACION,
        'jurados.eliminar' => self::INVESTIGACION,
        'asesores.guardar' => self::INVESTIGACION,
        'asesores.actualizar' => self::INVESTIGACION,
        'documentos.guardar' => self::INVESTIGACION,
        'documentos.eliminar' => self::INVESTIGACION,

        'sincronizar.form' => self::SOLO_SUPER_ADMIN,
        'sincronizar.previa' => self::SOLO_SUPER_ADMIN,
        'sincronizar.aplicar' => self::SOLO_SUPER_ADMIN,
    ];

    /** Campos de RD dentro de los formularios del PTI y la Tesis, que son de Investigación. */
    private const CAMPOS_DE_RD = ['rd_pti', 'rd_ampliacion', 'fecha_ampliacion', 'vencimiento_ampliado', 'rd_ti', 'rd_extension', 'fecha_extension', 'rd_tesis'];

    public static function puede(string $area): bool
    {
        $user = auth()->user();
        if (! $user) {
            return false;
        }

        return $user->hasRole('super-admin') || (isset(self::ROLES[$area]) && $user->hasRole(self::ROLES[$area]));
    }

    /** $ruta con o sin el prefijo «titulacion.». Las que no están en la lista son de lectura. */
    public static function puedeRuta(string $ruta): bool
    {
        $area = self::areaDe($ruta);

        return $area === null || self::puede($area);
    }

    public static function areaDe(string $ruta): ?string
    {
        return self::RUTAS[preg_replace('/^titulacion\./', '', $ruta)] ?? null;
    }

    /** Quita los campos de RD si quien guarda no es de Secretaría, aunque se altere el formulario. */
    public static function sinCamposDeRd(array $data): array
    {
        return self::puede(self::SECRETARIA) ? $data : array_diff_key($data, array_flip(self::CAMPOS_DE_RD));
    }
}
