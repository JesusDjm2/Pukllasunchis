<?php

namespace Database\Seeders;

use App\Enums\Modalidad;
use App\Enums\NivelPrograma;
use App\Models\Alumno;
use App\Models\Ayllu\AylluComentario;
use App\Models\Ayllu\AylluEventoCalendario;
use App\Models\Ayllu\AylluGrupo;
use App\Models\Ayllu\AylluMensajeChat;
use App\Models\Ayllu\AylluPublicacion;
use App\Models\BolsaTrabajoOferta;
use App\Models\Ciclo;
use App\Models\Curso;
use App\Models\Departamento;
use App\Models\Distrito;
use App\Models\Docente;
use App\Models\PeriodoActual;
use App\Models\PeriodoActualPpd;
use App\Models\Programa;
use App\Models\Provincia;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\AsignacionCampana;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\Caso;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\Llamada;
use App\Models\Titulacion\EstadoTitulacion;
use App\Models\Titulacion\SeguimientoTitulacion;
use App\Models\TrayectoriaEvento;
use App\Models\User;
use App\Models\ppd;
use App\Notifications\RedPukllaAviso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

/**
 * Seeder de ensayo funcional para Red Puklla de Egresados y Seguimiento de Egresados.
 *
 * REGLAS DE SEGURIDAD:
 * 1. Rechaza su ejecución fuera de los entornos 'local' y 'testing'.
 * 2. Exige habilitación explícita mediante RED_PUKLLA_DEMO_SEED=true (desactivada por defecto).
 * 3. Comprueba la conexión efectiva y rechaza escribir sobre bases con padrón real
 *    salvo en una base exclusiva de ensayo.
 * 4. Idempotente: utiliza claves estables y correos ficticios @example.test sin duplicar registros.
 * 5. No se incluye en DatabaseSeeder.
 */
class RedPukllaEnsayoSeeder extends Seeder
{
    public const PASSWORD_DEMO = 'DemoPuklla2026!';

    public function run(): void
    {
        $this->verificarEntornoAislado();

        DB::transaction(function () {
            $this->asegurarRolesYPermisosInstitucionales();
            $estructura = $this->asegurarEstructuraAcademicaYTerritorial();
            $cuentas = $this->asegurarPersonasYTrayectorias($estructura);
            $campanas = $this->asegurarCampanasYSeguimiento($cuentas, $estructura);
            $comunidad = $this->asegurarComunidadYOportunidades($cuentas);
            $this->asegurarModeracionYSolicitudRevision($cuentas, $comunidad);
        });
    }

    private function verificarEntornoAislado(): void
    {
        if (!app()->environment(['local', 'testing'])) {
            throw new \RuntimeException(
                'PROHIBIDO: RedPukllaEnsayoSeeder solo puede ejecutarse en entornos local o testing.'
            );
        }

        $valorConfig = config('app.red_puklla_demo_seed');
        $habilitado = filter_var(
            $valorConfig !== null ? $valorConfig : env('RED_PUKLLA_DEMO_SEED', false),
            FILTER_VALIDATE_BOOLEAN
        );

        if (!$habilitado) {
            throw new \RuntimeException(
                'EJECUCIÓN RECHAZADA: Debe habilitar explícitamente RED_PUKLLA_DEMO_SEED=true para poblar datos sintéticos de ensayo.'
            );
        }

        // Comprobar conexión efectiva antes de escribir datos
        $pdo = DB::connection()->getPdo();
        $dbName = DB::connection()->getDatabaseName();
        if (!$pdo || empty($dbName)) {
            throw new \RuntimeException('No se pudo verificar la conexión efectiva a la base de datos de ensayo.');
        }

        // Rechazar ejecución si la base contiene usuarios reales ajenos a @example.test / @puklla.test
        $existenReales = User::query()
            ->where('email', 'not like', '%@example.test')
            ->where('email', 'not like', '%@puklla.test')
            ->exists();

        if ($existenReales) {
            throw new \RuntimeException(
                "PROTECCIÓN DE DATOS REALES: La base '{$dbName}' contiene cuentas que no son de ensayo (@example.test). " .
                "Use una base de datos exclusiva y vacía (p. ej. DB_DATABASE=puklla_ensayo)."
            );
        }
    }

    private function asegurarRolesYPermisosInstitucionales(): void
    {
        app(PermissionRegistrar::class)->forgetCachedPermissions();

        $roles = [
            'super-admin',
            'admin',
            'docente',
            'tutor',
            'investigador',
            'gestor-titulacion',
            'adminB',
            'secretaria',
            'coordinacion-empleabilidad',
            'seguimiento-egresados',
            'encuestador-seguimiento',
            'bienestar',
            'formacion-continua',
            'direccion',
            'egresado',
            'alumno',
            'alumnoB',
            'inhabilitado',
        ];

        foreach ($roles as $rolNombre) {
            Role::firstOrCreate(['name' => $rolNombre, 'guard_name' => 'web']);
        }

        $permisosSeguimiento = [
            'seguimiento-egresados.ver-dashboard',
            'seguimiento-egresados.ver-perfiles-autorizados',
            'seguimiento-egresados.actualizar-perfiles-autorizados',
            'seguimiento-egresados.registrar-entrevistas',
            'seguimiento-egresados.gestionar-campanas',
            'seguimiento-egresados.gestionar-grupos',
            'seguimiento-egresados.ver-analitica',
            'seguimiento-egresados.exportar-analitica',
            'seguimiento-egresados.gestionar-casos',
            'seguimiento-egresados.ver-contactos-autorizados',
        ];

        foreach ($permisosSeguimiento as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $permisosOperativos = [
            'seguimiento-egresados.ver-dashboard',
            'seguimiento-egresados.ver-perfiles-autorizados',
            'seguimiento-egresados.actualizar-perfiles-autorizados',
            'seguimiento-egresados.registrar-entrevistas',
            'seguimiento-egresados.ver-contactos-autorizados',
        ];

        Role::findByName('seguimiento-egresados', 'web')->syncPermissions($permisosOperativos);

        foreach (['super-admin', 'admin', 'coordinacion-empleabilidad'] as $rGestion) {
            Role::findByName($rGestion, 'web')->givePermissionTo($permisosSeguimiento);
        }

        Role::findByName('direccion', 'web')->givePermissionTo([
            'seguimiento-egresados.ver-analitica',
            'seguimiento-egresados.exportar-analitica',
        ]);

        $permisosAyllu = [
            'ayllu.ver-inicio',
            'ayllu.publicar-muro-general',
            'ayllu.publicar-comunidad',
            'ayllu.moderar',
            'ayllu.retroalimentar-formativo',
        ];

        foreach ($permisosAyllu as $p) {
            Permission::firstOrCreate(['name' => $p, 'guard_name' => 'web']);
        }

        $permisosDocenteAyllu = [
            'ayllu.ver-inicio',
            'ayllu.publicar-muro-general',
            'ayllu.publicar-comunidad',
            'ayllu.retroalimentar-formativo',
        ];

        foreach (['super-admin', 'admin', 'adminB', 'docente', 'tutor', 'investigador', 'gestor-titulacion', 'coordinacion-empleabilidad', 'direccion'] as $rMuro) {
            $rol = Role::findByName($rMuro, 'web');
            $rol->givePermissionTo($permisosDocenteAyllu);
            if (!in_array($rMuro, ['super-admin', 'admin', 'coordinacion-empleabilidad'], true) && $rol->hasPermissionTo('ayllu.moderar')) {
                $rol->revokePermissionTo('ayllu.moderar');
            }
        }

        foreach (['super-admin', 'admin', 'coordinacion-empleabilidad'] as $rMod) {
            Role::findByName($rMod, 'web')->givePermissionTo('ayllu.moderar');
        }

        foreach (['alumno', 'alumnoB', 'seguimiento-egresados'] as $rEst) {
            Role::findByName($rEst, 'web')->givePermissionTo(['ayllu.ver-inicio', 'ayllu.publicar-comunidad']);
        }

        app(PermissionRegistrar::class)->forgetCachedPermissions();
    }

    private function asegurarEstructuraAcademicaYTerritorial(): array
    {
        $progFidInicial = Programa::updateOrCreate(
            ['nombre' => 'Educación Inicial'],
            ['modalidad' => Modalidad::FID, 'nivel' => NivelPrograma::INICIAL, 'eib' => false]
        );
        $progFidPrimariaEib = Programa::updateOrCreate(
            ['nombre' => 'Educación Primaria EIB'],
            ['modalidad' => Modalidad::FID, 'nivel' => NivelPrograma::PRIMARIA, 'eib' => true]
        );
        $progPpdInicial = Programa::updateOrCreate(
            ['nombre' => 'PPD Educación Inicial'],
            ['modalidad' => Modalidad::PPD, 'nivel' => NivelPrograma::INICIAL, 'eib' => false]
        );
        $progPpdPrimariaEib = Programa::updateOrCreate(
            ['nombre' => 'PPD Educación Primaria EIB'],
            ['modalidad' => Modalidad::PPD, 'nivel' => NivelPrograma::PRIMARIA, 'eib' => true]
        );

        $cicloFidV = Ciclo::updateOrCreate(
            ['nombre' => 'Ciclo V FID', 'programa_id' => $progFidPrimariaEib->id],
            ['proyecto_id' => null]
        );
        $cicloFidX = Ciclo::updateOrCreate(
            ['nombre' => 'Ciclo X FID', 'programa_id' => $progFidPrimariaEib->id],
            ['proyecto_id' => null]
        );
        $cicloFidInicialX = Ciclo::updateOrCreate(
            ['nombre' => 'Ciclo X Inicial FID', 'programa_id' => $progFidInicial->id],
            ['proyecto_id' => null]
        );
        $cicloPpdX = Ciclo::updateOrCreate(
            ['nombre' => 'Semestre X PPD', 'programa_id' => $progPpdPrimariaEib->id],
            ['proyecto_id' => null]
        );
        $cicloPpdInicialX = Ciclo::updateOrCreate(
            ['nombre' => 'Semestre X Inicial PPD', 'programa_id' => $progPpdInicial->id],
            ['proyecto_id' => null]
        );

        $periodoFid = PeriodoActual::updateOrCreate(
            ['nombre' => '2026-II'],
            ['actual' => true, 'fecha_inicio' => '2026-08-01', 'fecha_cierre' => '2026-12-20', 'formulario_habilitado' => true]
        );
        $periodoPpd = PeriodoActualPpd::updateOrCreate(
            ['nombre' => '2026-II PPD'],
            ['actual' => true, 'calendario' => true]
        );

        $cursoFid = Curso::updateOrCreate(
            ['cc' => 'DEMO-EIB-501', 'ciclo_id' => $cicloFidV->id],
            ['nombre' => 'Didáctica del Quechua y Literatura Oral', 'horas' => '4', 'creditos' => '3']
        );

        $this->call(EstadosTitulacionSeeder::class);
        $estadosTit = EstadoTitulacion::orderBy('orden')->get();
        $estadoEgresado = $estadosTit->first() ?? EstadoTitulacion::updateOrCreate(['clave' => 'egresado'], ['nombre' => 'Egresado', 'orden' => 1]);
        $estadoBachiller = $estadosTit->skip(1)->first() ?? $estadoEgresado;
        $estadoLicenciado = $estadosTit->last() ?? $estadoEgresado;

        // Divisiones territoriales para selectores de Mi espacio
        $depCusco = Departamento::updateOrCreate(['nombre' => 'Cusco']);
        $mapaProvincias = [
            'Cusco' => ['Cusco', 'San Sebastián', 'San Jerónimo', 'Santiago', 'Wanchaq'],
            'Canchis' => ['Sicuani', 'Marangani', 'San Pablo', 'Tinta'],
            'Quispicanchi' => ['Urcos', 'Ocongate', 'Andahuaylillas', 'Marcapata'],
            'Urubamba' => ['Urubamba', 'Ollantaytambo', 'Chinchero', 'Maras'],
            'Calca' => ['Calca', 'Pisac', 'Lamay', 'Lares'],
            'Paucartambo' => ['Paucartambo', 'Challabamba', 'Huancarani'],
        ];
        foreach ($mapaProvincias as $provNombre => $distritos) {
            $prov = Provincia::updateOrCreate(['nombre' => $provNombre, 'departamento_id' => $depCusco->id]);
            foreach ($distritos as $distNombre) {
                Distrito::updateOrCreate(['nombre' => $distNombre, 'provincia_id' => $prov->id]);
            }
        }

        return [
            'prog_fid_inicial' => $progFidInicial,
            'prog_fid_primaria_eib' => $progFidPrimariaEib,
            'prog_ppd_inicial' => $progPpdInicial,
            'prog_ppd_primaria_eib' => $progPpdPrimariaEib,
            'ciclo_fid_v' => $cicloFidV,
            'ciclo_fid_x' => $cicloFidX,
            'ciclo_fid_inicial_x' => $cicloFidInicialX,
            'ciclo_ppd_x' => $cicloPpdX,
            'ciclo_ppd_inicial_x' => $cicloPpdInicialX,
            'periodo_fid' => $periodoFid,
            'periodo_ppd' => $periodoPpd,
            'curso_fid' => $cursoFid,
            'estado_egresado' => $estadoEgresado,
            'estado_bachiller' => $estadoBachiller,
            'estado_licenciado' => $estadoLicenciado,
        ];
    }

    private function asegurarPersonasYTrayectorias(array $estructura): array
    {
        $passwordHash = Hash::make(self::PASSWORD_DEMO);

        // 1. Cuentas institucionales de gestión, docencia, bienestar y estudiante activo
        $superAdmin = $this->upsertUser('superadmin.demo@example.test', [
            'name' => 'Illariy',
            'apellidos' => 'Quispe Superadmin',
            'dni' => '90000001',
            'condicion' => null,
            'password' => $passwordHash,
        ], ['super-admin']);

        $admin = $this->upsertUser('admin.demo@example.test', [
            'name' => 'Kuntur',
            'apellidos' => 'Huamán Administración',
            'dni' => '90000002',
            'condicion' => null,
            'password' => $passwordHash,
        ], ['admin']);

        $docenteAula = $this->upsertUser('docente.aula@example.test', [
            'name' => 'Amaru',
            'apellidos' => 'Cáceres Docente Aula',
            'dni' => '90000003',
            'condicion' => null,
            'password' => $passwordHash,
        ], ['docente']);

        $docenteCoordinador = $this->upsertUser('docente.coordinador@example.test', [
            'name' => 'Sami',
            'apellidos' => 'Túpac Coordinadora',
            'dni' => '90000004',
            'condicion' => null,
            'password' => $passwordHash,
        ], ['docente', 'coordinacion-empleabilidad']);

        $docenteEntrevistador = $this->upsertUser('docente.entrevistador@example.test', [
            'name' => 'Willka',
            'apellidos' => 'Condori Entrevistador',
            'dni' => '90000005',
            'condicion' => null,
            'password' => $passwordHash,
        ], ['docente', 'seguimiento-egresados']);

        $docenteCoordEntrev = $this->upsertUser('docente.coord.entrev@example.test', [
            'name' => 'Nayra',
            'apellidos' => 'Mendoza Coord-Entrevistadora',
            'dni' => '90000006',
            'condicion' => null,
            'password' => $passwordHash,
        ], ['docente', 'coordinacion-empleabilidad', 'seguimiento-egresados']);

        $bienestar = $this->upsertUser('bienestar.demo@example.test', [
            'name' => 'Qori',
            'apellidos' => 'Palomino Bienestar',
            'dni' => '90000007',
            'condicion' => null,
            'password' => $passwordHash,
        ], ['bienestar']);

        // Vincular fichas Docente y curso activo para comprobar conservación del menú lateral docente
        foreach ([$docenteAula, $docenteCoordinador, $docenteEntrevistador, $docenteCoordEntrev] as $uDoc) {
            $docModel = Docente::updateOrCreate(
                ['user_id' => $uDoc->id],
                [
                    'nombre' => trim($uDoc->name . ' ' . $uDoc->apellidos),
                    'dni' => $uDoc->dni,
                    'email' => $uDoc->email,
                    'telefono' => '9840001' . substr($uDoc->dni, -2),
                ]
            );
            DB::table('curso_docente')->updateOrInsert([
                'curso_id' => $estructura['curso_fid']->id,
                'docente_id' => $docModel->id,
            ]);
        }

        // Estudiante activo para comprobar restricciones de ingreso a Red Puklla y Seguimiento
        $estudianteActivo = $this->upsertUser('estudiante.activo@example.test', [
            'name' => 'Wayra',
            'apellidos' => 'Choque Estudiante Activo',
            'dni' => '90000008',
            'condicion' => 'Regular',
            'programa_id' => $estructura['prog_fid_primaria_eib']->id,
            'ciclo_id' => $estructura['ciclo_fid_v']->id,
            'password' => $passwordHash,
        ], ['alumno']);

        $alumnoActivoFicha = $this->upsertFichaAlumno($estudianteActivo, $estructura['prog_fid_primaria_eib']->id, $estructura['ciclo_fid_v']->id, '984100008', '984199008');
        $alumnoActivoFicha->cursos()->syncWithoutDetaching([$estructura['curso_fid']->id]);

        // Facilitador de un ayllu, sin permisos de moderación general (egresado FID confirmado)
        $facilitadorAyllu = $this->crearEgresadoFid(
            0,
            'facilitador.ayllu@example.test',
            'Kusi',
            'Apaza Facilitadora Ayllu',
            '91000000',
            $estructura['prog_fid_primaria_eib'],
            $estructura['ciclo_fid_x'],
            $estructura['estado_licenciado'],
            2022,
            $passwordHash,
            $superAdmin->id
        );
        $this->registrarPerfilConfirmado($facilitadorAyllu, $facilitadorAyllu->id, [
            'programa' => $estructura['prog_fid_primaria_eib']->nombre,
            'egreso' => 2022,
            'res_provincia' => 'Cusco',
            'res_distrito' => 'San Sebastián',
            'res_localidad' => 'Kirkas',
            'res_comunidad_tipo' => 'campesina',
            'res_comunidad_nombre' => 'Comunidad Campesina Kirkas',
            'trabaja' => 'si',
            'institucion' => 'IE 50482 EIB Ocongate',
            'cargo' => 'Docente de aula EIB',
            'trab_provincia' => 'Quispicanchi',
            'trab_distrito' => 'Ocongate',
            'trab_localidad' => 'Lauramarca',
            'trab_comunidad_tipo' => 'campesina',
            'trab_comunidad_nombre' => 'Comunidad Campesina Lauramarca',
            'eib' => 'si',
            'forma_eib' => 'fortalecimiento',
            'escenario' => 1,
        ]);

        // 2. Doce (12) egresados con trayectoria institucional FID
        $egresadosFid = [];
        $nombresFid = [
            1 => ['Sumaq', 'Mamani Callañaupa', 2021, 'Cusco', 'San Jerónimo', 'Quispicanchi', 'Ocongate'],
            2 => ['Inti', 'Huallpa Quispe', 2021, 'Cusco', 'Santiago', 'Urubamba', 'Ollantaytambo'],
            3 => ['Yaku', 'Ccahuana Flores', 2022, 'Canchis', 'Sicuani', 'Canchis', 'Marangani'],
            4 => ['Killari', 'Puma Huanca', 2022, 'Calca', 'Pisac', 'Calca', 'Lares'],
            5 => ['Qori', 'Ttito Yupanqui', 2022, 'Urubamba', 'Chinchero', 'Urubamba', 'Maras'],
            6 => ['Suyana', 'Condori Paucar', 2023, 'Quispicanchi', 'Urcos', 'Quispicanchi', 'Andahuaylillas'],
            7 => ['Tupac', 'Chambi Nina', 2023, 'Paucartambo', 'Paucartambo', 'Paucartambo', 'Challabamba'],
            8 => ['Urpi', 'Soncco Gutiérrez', 2023, 'Cusco', 'Wanchaq', 'Cusco', 'Cusco'],
            9 => ['Sayri', 'Lloclla Ramos', 2024, 'Canchis', 'Tinta', 'Canchis', 'San Pablo'],
            10 => ['Miski', 'Huaraca Vargas', 2024, 'Calca', 'Calca', 'Calca', 'Lamay'],
            11 => ['Auki', 'Ccoa Mamani', 2024, 'Quispicanchi', 'Marcapata', 'Quispicanchi', 'Ocongate'],
            12 => ['Killa', 'Zúñiga Ccorimanya', 2024, 'Urubamba', 'Urubamba', 'Urubamba', 'Ollantaytambo'],
        ];

        foreach ($nombresFid as $idx => [$nom, $ape, $anio, $resProv, $resDist, $trabProv, $trabDist]) {
            $prog = ($idx % 2 === 0) ? $estructura['prog_fid_inicial'] : $estructura['prog_fid_primaria_eib'];
            $ciclo = ($idx % 2 === 0) ? $estructura['ciclo_fid_inicial_x'] : $estructura['ciclo_fid_x'];
            $correo = sprintf('egresado.fid.%02d@example.test', $idx);
            $dni = sprintf('910000%02d', $idx);

            $u = $this->crearEgresadoFid(
                $idx,
                $correo,
                $nom,
                $ape,
                $dni,
                $prog,
                $ciclo,
                $idx <= 6 ? $estructura['estado_licenciado'] : $estructura['estado_bachiller'],
                $anio,
                $passwordHash,
                $superAdmin->id
            );

            if ($idx <= 8) {
                // Confirmados personalmente con ficha completa (100% avance)
                $this->registrarPerfilConfirmado($u, $u->id, [
                    'programa' => $prog->nombre,
                    'egreso' => $anio,
                    'res_provincia' => $resProv,
                    'res_distrito' => $resDist,
                    'res_localidad' => 'Sector ' . $resDist,
                    'res_comunidad_tipo' => ($idx % 2 === 1) ? 'campesina' : 'no',
                    'res_comunidad_nombre' => ($idx % 2 === 1) ? 'Comunidad Campesina ' . $resDist : null,
                    'trabaja' => ($idx === 8) ? 'no' : 'si',
                    'institucion' => 'IE EIB Nº 5010' . $idx . ' ' . $trabDist,
                    'cargo' => 'Docente de Aula',
                    'trab_provincia' => $trabProv,
                    'trab_distrito' => $trabDist,
                    'trab_localidad' => 'Anexo ' . $trabDist,
                    'trab_comunidad_tipo' => ($idx % 3 === 1) ? 'campesina' : 'no',
                    'trab_comunidad_nombre' => ($idx % 3 === 1) ? 'ComunidadOriginaria ' . $trabDist : null,
                    'eib' => 'si',
                    'forma_eib' => ($idx % 2 === 0) ? 'revitalizacion' : 'fortalecimiento',
                    'escenario' => ($idx % 2 === 0) ? 3 : 1,
                    'contacto_alterno' => $idx === 1 ? [
                        'autorizado' => true,
                        'nombre' => 'Hermana Autorizada Mamani',
                        'telefono' => '984111222',
                        'correo' => 'alterno.autorizado.fid01@example.test',
                    ] : ($idx === 2 ? [
                        'autorizado' => false,
                        'nombre' => 'Familiar No Autorizado Huallpa',
                        'telefono' => '984999888',
                        'correo' => 'alterno.noautorizado.fid02@example.test',
                    ] : null),
                    'empleo_historico' => ($idx === 1 || $idx === 5),
                    'segundo_empleo_activo' => ($idx === 3),
                    'apoyo' => ($idx === 6) ? [
                        'solicitar' => true,
                        'tema' => 'pedagogia',
                        'detalle' => 'Solicito acompañamiento en planificación multigrado quechua-castellano.',
                    ] : null,
                ]);
            } elseif ($idx <= 10) {
                // Núcleo confirmado personalmente pero perfil incompleto (< 100%) y con borrador guardado
                $this->registrarPerfilParcialConfirmado($u, $u->id, $prog->nombre, $anio, true);
            } elseif ($idx === 11) {
                // Actualizado por entrevistador SIN confirmación personal posterior del egresado
                $this->registrarPerfilConfirmado($u, $docenteEntrevistador->id, [
                    'programa' => $prog->nombre,
                    'egreso' => $anio,
                    'res_provincia' => $resProv,
                    'res_distrito' => $resDist,
                    'trabaja' => 'si',
                    'institucion' => 'IE Rural ' . $trabDist,
                    'cargo' => 'Docente contratado',
                    'trab_provincia' => $trabProv,
                    'trab_distrito' => $trabDist,
                ]);
            }
            // idx === 12: sin versión en egresado_perfil_versiones (primera visita pendiente de confirmar)

            $egresadosFid[$idx] = $u;
        }

        // 3. Doce (12) egresados con trayectoria institucional PPD
        $egresadosPpd = [];
        $nombresPpd = [
            1 => ['Rumi', 'Huamán Quispe', 2021, 'Canchis', 'Sicuani', 'Canchis', 'San Pablo'],
            2 => ['Chaska', 'Vargas Condori', 2021, 'Quispicanchi', 'Urcos', 'Quispicanchi', 'Marcapata'],
            3 => ['Waman', 'Ccorahua Lazo', 2022, 'Urubamba', 'Ollantaytambo', 'Calca', 'Lares'],
            4 => ['Qantu', 'Meza Huillca', 2022, 'Paucartambo', 'Huancarani', 'Paucartambo', 'Challabamba'],
            5 => ['Antay', 'Rojas Ccallo', 2022, 'Cusco', 'San Sebastián', 'Quispicanchi', 'Andahuaylillas'],
            6 => ['Illapa', 'Saire Choque', 2023, 'Calca', 'Pisac', 'Urubamba', 'Chinchero'],
            7 => ['Nina', 'Ccopa Ttito', 2023, 'Canchis', 'Marangani', 'Canchis', 'Tinta'],
            8 => ['Kallpa', 'Arias Quispe', 2023, 'Cusco', 'Santiago', 'Cusco', 'San Jerónimo'],
            9 => ['Tika', 'Pacheco Mamani', 2024, 'Urubamba', 'Maras', 'Urubamba', 'Urubamba'],
            10 => ['Mayu', 'Cruz Hancco', 2024, 'Quispicanchi', 'Ocongate', 'Quispicanchi', 'Urcos'],
            11 => ['Samin', 'Delgado Puma', 2024, 'Calca', 'Lamay', 'Calca', 'Calca'],
            12 => ['Phuyu', 'Quispe Yucra', 2024, 'Paucartambo', 'Paucartambo', 'Cusco', 'Wanchaq'],
        ];

        foreach ($nombresPpd as $idx => [$nom, $ape, $anio, $resProv, $resDist, $trabProv, $trabDist]) {
            $prog = ($idx % 2 === 0) ? $estructura['prog_ppd_inicial'] : $estructura['prog_ppd_primaria_eib'];
            $ciclo = ($idx % 2 === 0) ? $estructura['ciclo_ppd_inicial_x'] : $estructura['ciclo_ppd_x'];
            $correo = sprintf('egresado.ppd.%02d@example.test', $idx);
            $dni = sprintf('920000%02d', $idx);

            $u = $this->crearEgresadoPpd(
                $idx,
                $correo,
                $nom,
                $ape,
                $dni,
                $prog,
                $ciclo,
                $idx <= 6 ? $estructura['estado_licenciado'] : $estructura['estado_egresado'],
                $anio,
                $passwordHash,
                $superAdmin->id
            );

            if ($idx <= 8) {
                $this->registrarPerfilConfirmado($u, $u->id, [
                    'programa' => $prog->nombre,
                    'egreso' => $anio,
                    'res_provincia' => $resProv,
                    'res_distrito' => $resDist,
                    'res_localidad' => 'Comunidad ' . $resDist,
                    'res_comunidad_tipo' => ($idx === 4) ? 'nativa' : (($idx % 2 === 1) ? 'campesina' : 'no'),
                    'res_comunidad_nombre' => ($idx === 4) ? 'Comunidad Nativa Wachiperi Ensayo' : (($idx % 2 === 1) ? 'Comunidad Campesina ' . $resDist : null),
                    'trabaja' => ($idx === 7) ? 'no' : 'si',
                    'institucion' => 'IE Bilingüe PPD Nº 5020' . $idx . ' ' . $trabDist,
                    'cargo' => 'Docente nombrado EIB',
                    'trab_provincia' => $trabProv,
                    'trab_distrito' => $trabDist,
                    'trab_localidad' => 'Centro Poblado ' . $trabDist,
                    'trab_comunidad_tipo' => ($idx === 4) ? 'nativa' : 'campesina',
                    'trab_comunidad_nombre' => ($idx === 4) ? 'Comunidad Nativa Queros Ensayo' : 'Comunidad Campesina ' . $trabDist,
                    'eib' => 'si',
                    'forma_eib' => 'fortalecimiento',
                    'escenario' => 2,
                    'apoyo' => ($idx === 6) ? [
                        'solicitar' => true,
                        'tema' => 'bienestar',
                        'detalle' => 'Solicitud confidencial de orientación socioemocional y bienestar.',
                    ] : null,
                ]);
            } elseif ($idx <= 10) {
                $this->registrarPerfilParcialConfirmado($u, $u->id, $prog->nombre, $anio, false);
            } elseif ($idx === 11) {
                // Actualizado por entrevistador sin confirmación personal posterior
                $this->registrarPerfilConfirmado($u, $docenteEntrevistador->id, [
                    'programa' => $prog->nombre,
                    'egreso' => $anio,
                    'res_provincia' => $resProv,
                    'res_distrito' => $resDist,
                    'trabaja' => 'si',
                    'institucion' => 'IE PPD ' . $trabDist,
                    'cargo' => 'Docente de aula',
                    'trab_provincia' => $trabProv,
                    'trab_distrito' => $trabDist,
                ]);
            }
            // idx === 12: sin confirmación personal (primera visita pendiente)

            $egresadosPpd[$idx] = $u;
        }

        // 4. Persona con egresos confirmados en AMBOS grupos (FID y PPD)
        $egresadoDual = $this->upsertUser('egresado.dual.fidppd@example.test', [
            'name' => 'Kusisqa',
            'apellidos' => 'Challco Doble Trayectoria',
            'dni' => '93000001',
            'condicion' => 'Egresado',
            'programa_id' => $estructura['prog_ppd_primaria_eib']->id,
            'ciclo_id' => $estructura['ciclo_ppd_x']->id,
            'telefono' => '984300001',
            'password' => $passwordHash,
        ], ['egresado']);

        $this->upsertFichaAlumno($egresadoDual, $estructura['prog_fid_inicial']->id, $estructura['ciclo_fid_inicial_x']->id, '984300001', '984399001');
        $this->upsertFichaPpd($egresadoDual, $estructura['prog_ppd_primaria_eib']->id, $estructura['ciclo_ppd_x']->id, '984300001', '984399001');
        TrayectoriaEvento::updateOrCreate(
            ['user_id' => $egresadoDual->id, 'tipo' => 'egreso', 'ciclo_origen_id' => $estructura['ciclo_fid_inicial_x']->id],
            [
                'fecha_inicio' => '2019-12-15',
                'valor_anterior' => 'Regular',
                'valor_nuevo' => 'Egresado',
                'motivo' => 'Culminación de plan de estudios FID Inicial',
                'origen' => 'acta_egreso',
                'registrado_por' => $superAdmin->id,
            ]
        );
        TrayectoriaEvento::updateOrCreate(
            ['user_id' => $egresadoDual->id, 'tipo' => 'egreso', 'ciclo_origen_id' => $estructura['ciclo_ppd_x']->id],
            [
                'fecha_inicio' => '2023-12-18',
                'valor_anterior' => 'Regular',
                'valor_nuevo' => 'Egresado',
                'motivo' => 'Culminación de segunda trayectoria en PPD Primaria EIB',
                'origen' => 'acta_egreso_ppd',
                'registrado_por' => $superAdmin->id,
            ]
        );
        $this->registrarPerfilConfirmado($egresadoDual, $egresadoDual->id, [
            'programa' => $estructura['prog_ppd_primaria_eib']->nombre,
            'egreso' => 2023,
            'res_provincia' => 'Cusco',
            'res_distrito' => 'Cusco',
            'res_localidad' => 'Centro Histórico',
            'trabaja' => 'si',
            'institucion' => 'IE Intercultural Kuska Yachay',
            'cargo' => 'Coordinadora Pedagógica EIB',
            'trab_provincia' => 'Urubamba',
            'trab_distrito' => 'Chinchero',
            'trab_localidad' => 'Ayllu Pongobamba',
            'trab_comunidad_tipo' => 'campesina',
            'trab_comunidad_nombre' => 'Comunidad Campesina Pongobamba',
            'eib' => 'si',
            'forma_eib' => 'fortalecimiento',
            'escenario' => 1,
        ]);

        // 5. Persona cuyo grupo de formación está POR VERIFICAR (sin evidencia suficiente / fichas en conflicto sin evento de egreso)
        $egresadoPorVerificar = $this->upsertUser('egresado.porverificar@example.test', [
            'name' => 'Yanapay',
            'apellidos' => 'Quispe Por Verificar',
            'dni' => '93000002',
            'condicion' => 'Egresado',
            'programa_id' => null,
            'ciclo_id' => null,
            'telefono' => '984300002',
            'password' => $passwordHash,
        ], ['egresado']);

        $this->upsertFichaAlumno($egresadoPorVerificar, $estructura['prog_fid_primaria_eib']->id, $estructura['ciclo_fid_x']->id, '984300002', '984399002');
        $this->upsertFichaPpd($egresadoPorVerificar, $estructura['prog_ppd_inicial']->id, $estructura['ciclo_ppd_inicial_x']->id, '984300002', '984399002');
        $this->registrarPerfilParcialConfirmado($egresadoPorVerificar, $egresadoPorVerificar->id, 'Por verificar en secretaría', 2022, false);

        return [
            'super_admin' => $superAdmin,
            'admin' => $admin,
            'docente_aula' => $docenteAula,
            'docente_coordinador' => $docenteCoordinador,
            'docente_entrevistador' => $docenteEntrevistador,
            'docente_coord_entrev' => $docenteCoordEntrev,
            'bienestar' => $bienestar,
            'estudiante_activo' => $estudianteActivo,
            'facilitador_ayllu' => $facilitadorAyllu,
            'fid' => $egresadosFid,
            'ppd' => $egresadosPpd,
            'dual' => $egresadoDual,
            'por_verificar' => $egresadoPorVerificar,
        ];
    }

    private function upsertUser(string $email, array $attributes, array $roles): User
    {
        $user = User::withoutEvents(function () use ($email, $attributes) {
            return User::updateOrCreate(['email' => $email], $attributes);
        });
        $user->syncRoles($roles);
        return $user;
    }

    private function upsertFichaAlumno(User $u, int $programaId, int $cicloId, string $numero, string $numeroRef): Alumno
    {
        return Alumno::withoutEvents(function () use ($u, $programaId, $cicloId, $numero, $numeroRef) {
            return Alumno::unguarded(function () use ($u, $programaId, $cicloId, $numero, $numeroRef) {
                return Alumno::updateOrCreate(
                    ['user_id' => $u->id],
                    $this->datosSocioeconomicosBase($u, $programaId, $cicloId, $numero, $numeroRef, false)
                );
            });
        });
    }

    private function upsertFichaPpd(User $u, int $programaId, int $cicloId, string $numero, string $numeroRef): ppd
    {
        return ppd::withoutEvents(function () use ($u, $programaId, $cicloId, $numero, $numeroRef) {
            return ppd::unguarded(function () use ($u, $programaId, $cicloId, $numero, $numeroRef) {
                return ppd::updateOrCreate(
                    ['user_id' => $u->id],
                    $this->datosSocioeconomicosBase($u, $programaId, $cicloId, $numero, $numeroRef, true)
                );
            });
        });
    }

    private function datosSocioeconomicosBase(User $u, int $programaId, int $cicloId, string $numero, string $numeroRef, bool $esPpd): array
    {
        $base = [
            'email' => $u->email,
            'dni' => $u->dni,
            'nombres' => $u->name,
            'apellidos' => $u->apellidos,
            'programa_id' => $programaId,
            'ciclo_id' => $cicloId,
            'numero' => $numero,
            'numero_referencia' => $numeroRef,
            'procedencia_familiar' => 'Cusco',
            'direccion' => 'Av. Institucional s/n (Demostración)',
            'departamento' => 'Cusco',
            'provincia' => $esPpd ? 'Canchis' : 'Cusco',
            'distrito' => $esPpd ? 'Sicuani' : 'Cusco',
            'te_consideras' => 'Quechua',
            'lengua_1' => 'Quechua',
            'lengua_2' => 'Castellano',
            'estado_civil' => 'Soltero(a)',
            'p_m_soltero' => 0,
            'num_hijos' => 0,
            'sector_socioeconomico' => 'Rural',
            'num_comprobante' => 'DEMO-0001',
            'convivientes' => 'Familia',
            'quien_mantiene' => 'Autónomo',
            'cant_dependientes_child' => '0',
            'cant_dependientes_old' => '0',
            'cant_dependientes_otros' => '0',
            'estudio_beca' => 'No',
            'tipo_preparacion' => 'Autoestudio',
            'motivo_estudio_eesp' => 'Vocación pedagógica EIB',
            'motivo_docencia' => 'Compromiso comunitario',
            'motivo_especialidad' => 'Educación intercultural',
            'internet' => 1,
            'servicio_internet' => 'Datos móviles',
            'dispositivo_internet' => 'Laptop',
            'propio_compartido' => 1,
            'correo' => 1,
            'num_hrs_estudio' => 15,
            'forma_estudio' => 'Individual y grupal',
            'egreso' => '0',
            'hrs_laboradas_sem' => 20,
            'ayuda_economica' => 0,
            'tiempo_ayuda' => 'Ninguno',
            'tipo_apoyo_formacion' => 'Tutoría',
            'tipo_vivienda' => 'Casa independiente',
            'situacion_vivienda' => 'Familiar',
            'dormitorios_vivienda' => '2',
            'banos_vivienda' => '1',
            'material_vivienda' => 'Adobe / Noble',
            'hrs_disponibles_agua' => '24',
            'hrs_disponibles_desague' => '24',
            'hrs_disponibles_luz' => '24',
            'problemas_salud' => 0,
            'tipo_seguro' => 'SIS / EsSalud',
            'familiar_salud' => 0,
            'frecuencia_lectura' => 'Semanal',
            'acceso_lectura' => 'Biblioteca institucional',
            'visitas_museos' => 'Semestral',
            'actividades_internet' => 'Investigación pedagógica',
            'tiempo_libre' => 1,
        ];

        if ($esPpd) {
            $base += [
                'carrera_procedencia' => 'Educación',
                'ano_culminaste' => 2018,
                'institucion_procedencia' => 'ISP Público',
                'gestion_institucion' => 'Pública',
                'direccion_institucion' => 'Cusco',
                'dep_dist_prov_institucion' => 'Cusco',
            ];
        } else {
            $base['trabajas'] = 'Sí';
        }

        return $base;
    }

    private function crearEgresadoFid(
        int $idx,
        string $correo,
        string $nombre,
        string $apellidos,
        string $dni,
        Programa $programa,
        Ciclo $ciclo,
        EstadoTitulacion $estadoTit,
        int $anioEgreso,
        string $passwordHash,
        int $registradoPorId
    ): User {
        $u = $this->upsertUser($correo, [
            'name' => $nombre,
            'apellidos' => $apellidos,
            'dni' => $dni,
            'condicion' => 'Egresado',
            'programa_id' => $programa->id,
            'ciclo_id' => $ciclo->id,
            'telefono' => sprintf('98411%04d', $idx),
            'password' => $passwordHash,
        ], ['egresado']);

        // Contacto de emergencia en ficha académica (numero_referencia no debe usarse automáticamente como alterno de campaña)
        $this->upsertFichaAlumno($u, $programa->id, $ciclo->id, sprintf('98411%04d', $idx), sprintf('98419%04d', $idx));

        TrayectoriaEvento::updateOrCreate(
            ['user_id' => $u->id, 'tipo' => 'egreso', 'ciclo_origen_id' => $ciclo->id],
            [
                'fecha_inicio' => "{$anioEgreso}-12-15",
                'valor_anterior' => 'Regular',
                'valor_nuevo' => 'Egresado',
                'motivo' => 'Egreso institucional FID',
                'origen' => 'acta_egreso',
                'registrado_por' => $registradoPorId,
            ]
        );

        SeguimientoTitulacion::withoutEvents(function () use ($u, $estadoTit, $anioEgreso) {
            SeguimientoTitulacion::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'estado_titulacion_id' => $estadoTit->id,
                    'periodo_estudios' => ($anioEgreso - 4) . '-' . $anioEgreso,
                ]
            );
        });

        return $u;
    }

    private function crearEgresadoPpd(
        int $idx,
        string $correo,
        string $nombre,
        string $apellidos,
        string $dni,
        Programa $programa,
        Ciclo $ciclo,
        EstadoTitulacion $estadoTit,
        int $anioEgreso,
        string $passwordHash,
        int $registradoPorId
    ): User {
        $u = $this->upsertUser($correo, [
            'name' => $nombre,
            'apellidos' => $apellidos,
            'dni' => $dni,
            'condicion' => 'Egresado',
            'programa_id' => $programa->id,
            'ciclo_id' => $ciclo->id,
            'telefono' => sprintf('98422%04d', $idx),
            'password' => $passwordHash,
        ], ['egresado']);

        $this->upsertFichaPpd($u, $programa->id, $ciclo->id, sprintf('98422%04d', $idx), sprintf('98429%04d', $idx));

        TrayectoriaEvento::updateOrCreate(
            ['user_id' => $u->id, 'tipo' => 'egreso', 'ciclo_origen_id' => $ciclo->id],
            [
                'fecha_inicio' => "{$anioEgreso}-12-18",
                'valor_anterior' => 'Regular',
                'valor_nuevo' => 'Egresado',
                'motivo' => 'Egreso institucional PPD',
                'origen' => 'acta_egreso_ppd',
                'registrado_por' => $registradoPorId,
            ]
        );

        SeguimientoTitulacion::withoutEvents(function () use ($u, $estadoTit, $anioEgreso) {
            SeguimientoTitulacion::updateOrCreate(
                ['user_id' => $u->id],
                [
                    'estado_titulacion_id' => $estadoTit->id,
                    'periodo_estudios' => ($anioEgreso - 2) . '-' . $anioEgreso,
                ]
            );
        });

        return $u;
    }

    private function registrarPerfilConfirmado(User $u, int $registradoPorId, array $cfg): void
    {
        $trabajos = [];

        if (($cfg['trabaja'] ?? 'si') === 'si') {
            $epActivo = EpisodioLaboral::updateOrCreate(
                ['user_id' => $u->id, 'institucion' => $cfg['institucion']],
                [
                    'cargo' => $cfg['cargo'] ?? 'Docente de aula',
                    'fecha_inicio' => '2025-03-01',
                    'fecha_fin' => null,
                    'es_actual' => true,
                    'es_empleo_educativo' => true,
                    'es_docencia_directa' => true,
                    'trab_departamento' => 'Cusco',
                    'trab_provincia' => $cfg['trab_provincia'] ?? 'Quispicanchi',
                    'trab_distrito' => $cfg['trab_distrito'] ?? 'Ocongate',
                    'fecha_captura' => now(),
                    'canal_registro' => $registradoPorId === $u->id ? 'mi_espacio' : 'llamada_docente',
                ]
            );

            $trabajos[] = [
                'episodio_id' => $epActivo->id,
                'institucion' => $epActivo->institucion,
                'cargo' => $epActivo->cargo,
                'inicio' => '2025-03-01',
                'fin' => null,
                'educativo' => true,
                'docencia' => true,
                'modalidad' => 'EBR',
                'nivel' => 'Primaria',
                'caracteristica' => 'polidocente_multigrado',
                'aula' => 'multigrado',
                'area' => 'rural',
                'eib' => $cfg['eib'] ?? 'si',
                'forma_eib' => $cfg['forma_eib'] ?? 'fortalecimiento',
                'escenario' => $cfg['escenario'] ?? 1,
                'lenguas' => 'Quechua y castellano',
                'lugar' => [
                    'departamento' => 'Cusco',
                    'provincia' => $cfg['trab_provincia'] ?? 'Quispicanchi',
                    'distrito' => $cfg['trab_distrito'] ?? 'Ocongate',
                    'localidad' => $cfg['trab_localidad'] ?? 'Anexo Escolar',
                    'comunidad_tipo' => $cfg['trab_comunidad_tipo'] ?? 'no',
                    'comunidad_nombre' => $cfg['trab_comunidad_nombre'] ?? null,
                    // Coordenadas sintéticas de demostración (marcadas como declarada_pendiente para no entrar al catálogo oficial)
                    'latitud' => -13.6251,
                    'longitud' => -71.3842,
                    'precision' => 'declarada_pendiente',
                    'fuente' => 'coordenada_sintetica_ensayo',
                ],
            ];

            if (!empty($cfg['segundo_empleo_activo'])) {
                $epSegundo = EpisodioLaboral::updateOrCreate(
                    ['user_id' => $u->id, 'institucion' => 'CEBA Intercultural Nocturno Sicuani'],
                    [
                        'cargo' => 'Facilitador EBA a tiempo parcial',
                        'fecha_inicio' => '2025-05-01',
                        'fecha_fin' => null,
                        'es_actual' => true,
                        'es_empleo_educativo' => true,
                        'es_docencia_directa' => true,
                        'trab_departamento' => 'Cusco',
                        'trab_provincia' => 'Canchis',
                        'trab_distrito' => 'Sicuani',
                        'fecha_captura' => now(),
                        'canal_registro' => 'mi_espacio',
                    ]
                );
                $trabajos[] = [
                    'episodio_id' => $epSegundo->id,
                    'institucion' => $epSegundo->institucion,
                    'cargo' => $epSegundo->cargo,
                    'inicio' => '2025-05-01',
                    'fin' => null,
                    'educativo' => true,
                    'docencia' => true,
                    'modalidad' => 'EBA',
                    'area' => 'urbana',
                    'eib' => 'no',
                    'lugar' => [
                        'departamento' => 'Cusco',
                        'provincia' => 'Canchis',
                        'distrito' => 'Sicuani',
                        'localidad' => 'Barrio Central',
                        'comunidad_tipo' => 'no',
                        'comunidad_nombre' => null,
                        'precision' => 'sin_coordenadas',
                        'fuente' => 'coordenada_sintetica_ensayo',
                    ],
                ];
            }

            if (!empty($cfg['empleo_historico'])) {
                $epCerrado = EpisodioLaboral::updateOrCreate(
                    ['user_id' => $u->id, 'institucion' => 'IE Inicial Comunitaria Huancarani (Concluido)'],
                    [
                        'cargo' => 'Docente contratado temporal',
                        'fecha_inicio' => '2023-03-01',
                        'fecha_fin' => '2024-12-20',
                        'es_actual' => false,
                        'es_empleo_educativo' => true,
                        'es_docencia_directa' => true,
                        'motivo_cambio' => 'Fin de contrato anual y traslado a nueva plaza EIB',
                        'trab_departamento' => 'Cusco',
                        'trab_provincia' => 'Paucartambo',
                        'trab_distrito' => 'Huancarani',
                        'fecha_captura' => now()->subYear(),
                        'canal_registro' => 'mi_espacio',
                    ]
                );
                $trabajos[] = [
                    'episodio_id' => $epCerrado->id,
                    'institucion' => $epCerrado->institucion,
                    'cargo' => $epCerrado->cargo,
                    'inicio' => '2023-03-01',
                    'fin' => '2024-12-20',
                    'educativo' => true,
                    'docencia' => true,
                    'modalidad' => 'EBR',
                    'area' => 'rural',
                    'eib' => 'si',
                    'forma_eib' => 'fortalecimiento',
                    'escenario' => 1,
                    'lugar' => [
                        'departamento' => 'Cusco',
                        'provincia' => 'Paucartambo',
                        'distrito' => 'Huancarani',
                        'localidad' => 'Centro Huancarani',
                        'comunidad_tipo' => 'campesina',
                        'comunidad_nombre' => 'Comunidad Campesina Huancarani',
                        'precision' => 'sin_coordenadas',
                        'fuente' => 'coordenada_sintetica_ensayo',
                    ],
                ];
            }
        }

        $datosPerfil = [
            'contacto' => [
                'nombre_preferido' => $u->name,
                'correo' => $u->email,
                'telefono' => $u->telefono,
                'medio' => 'WhatsApp',
                'programa' => $cfg['programa'],
                'egreso' => $cfg['egreso'],
            ],
            'residencia' => [
                'departamento' => 'Cusco',
                'provincia' => $cfg['res_provincia'],
                'distrito' => $cfg['res_distrito'],
                'localidad' => $cfg['res_localidad'] ?? ('Sector ' . $cfg['res_distrito']),
                'comunidad_tipo' => $cfg['res_comunidad_tipo'] ?? 'no',
                'comunidad_nombre' => $cfg['res_comunidad_nombre'] ?? null,
                'latitud' => -13.5319,
                'longitud' => -71.9675,
                'precision' => 'declarada_pendiente',
                'fuente' => 'coordenada_sintetica_ensayo',
            ],
            'trabaja' => $cfg['trabaja'] ?? 'si',
            'busqueda' => ($cfg['trabaja'] ?? 'si') === 'no' ? 'con_experiencia' : 'no_busco',
            'trabajos' => $trabajos,
            'comunidad' => [
                'vinculos' => ['origen', 'trabajo', 'colaboro'],
                'actividades' => 'Calendario comunal agrofestivo y talleres con familias.',
                'desde' => '2023-04-01',
                'influencia' => 'mucho',
                'relato' => 'Articulación de saberes locales con proyectos escolares.',
            ],
            'formacion' => [
                'temas' => 'Lectoescritura en quechua, evaluación formativa y proyectos integradores',
                'prioridad' => 'Didáctica EIB en aulas multigrado',
                'compartir' => 'Materiales bilingües y relatos orales sistematizados',
            ],
            'encuentros' => [
                'lugar' => 'Cusco o Sicuani',
                'laborales' => 'Tardes a partir de las 5:00 p. m.',
                'fin_semana' => 'Sábados por la mañana',
                'variable' => true,
            ],
        ];

        if (!empty($cfg['contacto_alterno'])) {
            $datosPerfil['contacto_alterno'] = $cfg['contacto_alterno'];
        }
        if (!empty($cfg['apoyo'])) {
            $datosPerfil['apoyo'] = $cfg['apoyo'];
        }

        Actualizacion::updateOrCreate(
            ['user_id' => $u->id, 'estado_verificacion' => 'verificado'],
            [
                'nombre_completo' => trim($u->name . ' ' . $u->apellidos),
                'es_ensayo' => true,
                'periodo_campana' => '2026-II',
                'tipo_registro' => 'respuesta_campana',
                'procedencia' => $registradoPorId === $u->id ? 'intranet_autogestion' : 'entrevista_docente',
                'canal' => $registradoPorId === $u->id ? 'mi_espacio' : 'llamada_docente',
                'fecha_ultima_confirmacion' => now(),
                'es_confirmacion_sin_cambios' => false,
                'programa' => $cfg['programa'],
                'cohorte_egreso' => (string) $cfg['egreso'],
                'correo' => $u->email,
                'telefono' => $u->telefono,
                'medio_preferido' => 'WhatsApp',
                'res_departamento' => 'Cusco',
                'res_provincia' => $cfg['res_provincia'],
                'res_distrito' => $cfg['res_distrito'],
                'trabaja' => ($cfg['trabaja'] ?? 'si') === 'si' ? 'Trabaja' : 'No trabaja',
                'temas_interes' => $datosPerfil['formacion']['temas'],
                'puedo_compartir' => $datosPerfil['formacion']['compartir'],
            ]
        );

        DB::table('egresado_perfil_versiones')->where('user_id', $u->id)->delete();
        DB::table('egresado_perfil_versiones')->insert([
            'user_id' => $u->id,
            'registrado_por' => $registradoPorId,
            'datos' => json_encode($datosPerfil, JSON_UNESCAPED_UNICODE),
            'confirmado_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    private function registrarPerfilParcialConfirmado(User $u, int $registradoPorId, string $programa, int $egreso, bool $conBorrador): void
    {
        $datosParciales = [
            'contacto' => [
                'nombre_preferido' => $u->name,
                'correo' => $u->email,
                'telefono' => $u->telefono,
                'medio' => 'Llamada',
                'programa' => $programa,
                'egreso' => $egreso,
            ],
            'residencia' => [
                'departamento' => 'Cusco',
                'provincia' => 'Canchis',
                // Distrito pendiente para reflejar avance parcial (< 100%)
                'distrito' => null,
            ],
            'trabaja' => null,
        ];

        DB::table('egresado_perfil_versiones')->where('user_id', $u->id)->delete();
        DB::table('egresado_perfil_versiones')->insert([
            'user_id' => $u->id,
            'registrado_por' => $registradoPorId,
            'datos' => json_encode($datosParciales, JSON_UNESCAPED_UNICODE),
            'confirmado_at' => now()->subDays(4),
            'created_at' => now()->subDays(4),
            'updated_at' => now()->subDays(4),
        ]);

        if ($conBorrador) {
            DB::table('egresado_perfil_borradores')->updateOrInsert(
                ['user_id' => $u->id],
                [
                    'datos' => json_encode(array_replace_recursive($datosParciales, [
                        'residencia' => ['distrito' => 'Sicuani'],
                        'formacion' => ['temas' => 'Borrador en curso sobre educación artística EIB'],
                    ]), JSON_UNESCAPED_UNICODE),
                    'created_at' => now()->subDay(),
                    'updated_at' => now()->subDay(),
                ]
            );
        }
    }

    private function asegurarCampanasYSeguimiento(array $cuentas, array $estructura): array
    {
        // 1. Campaña finalizada (con informe congelado)
        $campanaFinalizada = Campana::updateOrCreate(
            ['codigo' => 'CAMP-DEMO-2026-I'],
            [
                'nombre' => 'Campaña de Seguimiento Egresados 2026-I (Finalizada)',
                'anio' => 2026,
                'semestre' => 'I',
                'fecha_inicio' => now()->subMonths(6)->toDateString(),
                'fecha_fin' => now()->subMonths(4)->toDateString(),
                'estado' => 'cerrada',
                'descripcion' => 'Campaña semestral concluida con corte estadístico congelado.',
                'es_ensayo' => true,
            ]
        );

        // 2. Campaña vigente
        $campanaVigente = Campana::updateOrCreate(
            ['codigo' => 'CAMP-DEMO-2026-II'],
            [
                'nombre' => 'Campaña de Seguimiento Egresados 2026-II (Vigente)',
                'anio' => 2026,
                'semestre' => 'II',
                'fecha_inicio' => now()->subDays(15)->toDateString(),
                'fecha_fin' => now()->addDays(30)->toDateString(),
                'estado' => 'activa',
                'descripcion' => 'Campaña vigente para actualización territorial, laboral y necesidades formativas.',
                'es_ensayo' => true,
            ]
        );

        // 3. Campaña futura
        $campanaFutura = Campana::updateOrCreate(
            ['codigo' => 'CAMP-DEMO-2027-I'],
            [
                'nombre' => 'Campaña Programada 2027-I (Futura)',
                'anio' => 2027,
                'semestre' => 'I',
                'fecha_inicio' => now()->addDays(20)->toDateString(),
                'fecha_fin' => now()->addDays(65)->toDateString(),
                'estado' => 'activa',
                'descripcion' => 'Campaña futura programada aún no iniciada.',
                'es_ensayo' => true,
            ]
        );

        $coord = $cuentas['docente_coordinador'];
        $entrev = $cuentas['docente_entrevistador'];
        $coordEntrev = $cuentas['docente_coord_entrev'];

        // Asignación vencida (solo en la campaña finalizada CAMP-DEMO-2026-I): egresado.ppd.10
        AsignacionCampana::updateOrCreate(
            [
                'campana_id' => $campanaFinalizada->id,
                'egresado_user_id' => $cuentas['ppd'][10]->id,
                'docente_user_id' => $entrev->id,
            ],
            [
                'asignado_por' => $coord->id,
                'estado' => 'completado',
                'fecha_asignacion' => now()->subMonths(5),
                'es_ensayo' => true,
            ]
        );

        // Asignación en campaña futura (aún no vigente): egresado.fid.10
        AsignacionCampana::updateOrCreate(
            [
                'campana_id' => $campanaFutura->id,
                'egresado_user_id' => $cuentas['fid'][10]->id,
                'docente_user_id' => $entrev->id,
            ],
            [
                'asignado_por' => $coord->id,
                'estado' => 'asignado',
                'fecha_asignacion' => now(),
                'es_ensayo' => true,
            ]
        );

        // Asignación reasignada en campaña vigente conservando historial:
        // egresado.fid.05 estuvo asignado a $entrev (estado = 'reasignado') y pasó a $coordEntrev (estado = 'asignado')
        AsignacionCampana::updateOrCreate(
            [
                'campana_id' => $campanaVigente->id,
                'egresado_user_id' => $cuentas['fid'][5]->id,
                'docente_user_id' => $entrev->id,
            ],
            [
                'asignado_por' => $coord->id,
                'estado' => 'reasignado',
                'motivo_reasignacion' => 'Reasignado por equilibrio de carga semanal hacia docente coordinadora-entrevistadora.',
                'fecha_asignacion' => now()->subDays(12),
                'es_ensayo' => true,
            ]
        );
        Llamada::updateOrCreate(
            [
                'campana_id' => $campanaVigente->id,
                'user_id' => $cuentas['fid'][5]->id,
                'docente_user_id' => $entrev->id,
                'tipo_resultado' => 'intento_sin_respuesta',
            ],
            [
                'resultado' => 'No contesta (antes de reasignación)',
                'es_contacto_efectivo' => false,
                'es_ensayo' => true,
                'observaciones' => 'Primer intento registrado por entrevistador original antes de la reasignación.',
                'encuestador' => $entrev->name,
                'fecha_hora' => now()->subDays(11),
            ]
        );
        AsignacionCampana::updateOrCreate(
            [
                'campana_id' => $campanaVigente->id,
                'egresado_user_id' => $cuentas['fid'][5]->id,
                'docente_user_id' => $coordEntrev->id,
            ],
            [
                'asignado_por' => $coord->id,
                'docente_anterior_id' => $entrev->id,
                'motivo_reasignacion' => 'Reasignado por equilibrio de carga semanal hacia docente coordinadora-entrevistadora.',
                'estado' => 'contactado',
                'fecha_asignacion' => now()->subDays(8),
                'es_ensayo' => true,
            ]
        );

        // Asignaciones vigentes de $entrev (docente entrevistador)
        $asignadosEntrev = [
            [$cuentas['fid'][1], 'completado'],
            [$cuentas['fid'][2], 'asignado'],
            [$cuentas['fid'][3], 'asignado'],
            [$cuentas['fid'][4], 'asignado'], // Caso 1: Sin contacto (sin llamadas)
            [$cuentas['fid'][11], 'contactado'],
            [$cuentas['ppd'][1], 'completado'],
            [$cuentas['ppd'][2], 'asignado'],
            [$cuentas['ppd'][3], 'contactado'],
            [$cuentas['ppd'][4], 'contactado'],
            [$cuentas['ppd'][11], 'contactado'],
        ];

        foreach ($asignadosEntrev as [$egUser, $estadoAsig]) {
            AsignacionCampana::updateOrCreate(
                [
                    'campana_id' => $campanaVigente->id,
                    'egresado_user_id' => $egUser->id,
                    'docente_user_id' => $entrev->id,
                ],
                [
                    'asignado_por' => $coord->id,
                    'estado' => $estadoAsig,
                    'fecha_asignacion' => now()->subDays(10),
                    'es_ensayo' => true,
                ]
            );
        }

        // Asignaciones vigentes de $coordEntrev (docente coordinadora y entrevistadora simultáneamente)
        $asignadosCoordEntrev = [
            [$cuentas['fid'][6], 'contactado'],
            [$cuentas['fid'][7], 'completado'],
            [$cuentas['fid'][8], 'completado'],
            [$cuentas['ppd'][5], 'asignado'],
            [$cuentas['ppd'][6], 'contactado'],
            [$cuentas['ppd'][7], 'contactado'],
            [$cuentas['ppd'][8], 'completado'],
            [$cuentas['dual'], 'completado'],
            [$cuentas['por_verificar'], 'contactado'],
        ];

        foreach ($asignadosCoordEntrev as [$egUser, $estadoAsig]) {
            AsignacionCampana::updateOrCreate(
                [
                    'campana_id' => $campanaVigente->id,
                    'egresado_user_id' => $egUser->id,
                    'docente_user_id' => $coordEntrev->id,
                ],
                [
                    'asignado_por' => $coord->id,
                    'estado' => $estadoAsig,
                    'fecha_asignacion' => now()->subDays(10),
                    'es_ensayo' => true,
                ]
            );
        }

        // Registrar los casos de llamadas requeridos en la campaña vigente:
        // Caso 2: No responde
        $this->upsertLlamada($campanaVigente->id, $cuentas['fid'][2]->id, $entrev, 'No responde', 'intento_sin_respuesta', false, 'Se llamó en dos horarios distintos sin respuesta.');
        // Caso 3: Número equivocado
        $this->upsertLlamada($campanaVigente->id, $cuentas['fid'][3]->id, $entrev, 'Número equivocado', 'numero_equivocado', false, 'La línea pertenece a otra persona; pendiente verificar por correo.');
        // Caso 4: Contacto mediante otra persona
        $this->upsertLlamada($campanaVigente->id, $cuentas['ppd'][2]->id, $entrev, 'Contacto mediante otra persona', 'tercero_conocido', false, 'Contestó su familiar e indicó que la egresada se encuentra en comunidad sin señal hasta el viernes.');
        // Caso 5: Entrevista programada
        $this->upsertLlamada($campanaVigente->id, $cuentas['ppd'][3]->id, $entrev, 'Entrevista programada', 'entrevista_programada', true, 'Acordamos llamada de entrevista para el viernes por la tarde.', now()->addDays(3));
        // Caso 6: Actualización parcial
        $this->upsertLlamada($campanaVigente->id, $cuentas['ppd'][4]->id, $entrev, 'Actualización parcial', 'actualizacion_parcial', true, 'Confirmó datos de residencia y primer empleo; pendiente completar segundo centro laboral.');
        // Caso 7: Actualización confirmada
        $this->upsertLlamada($campanaVigente->id, $cuentas['fid'][1]->id, $entrev, 'Actualización completa', 'actualizacion_completa', true, 'Datos territoriales, laborales y de comunidad confirmados íntegramente.');
        $this->upsertLlamada($campanaVigente->id, $cuentas['ppd'][1]->id, $entrev, 'Confirmación sin cambios', 'confirmacion_sin_cambios', true, 'Confirmó que continúa en la misma institución educativa EIB.');
        // Caso 8: Solicitud de apoyo o derivación
        $this->upsertLlamada($campanaVigente->id, $cuentas['fid'][6]->id, $coordEntrev, 'Requiere derivación', 'requiere_derivacion', true, 'Solicita acompañamiento pedagógico en aula multigrado EIB.', null, true, 'Derivación a Coordinación de Formación Continua / Pedagogía EIB.');
        $this->upsertLlamada($campanaVigente->id, $cuentas['ppd'][6]->id, $coordEntrev, 'Requiere derivación', 'requiere_derivacion', true, 'Solicita apoyo reservado al área de Bienestar.', null, true, 'Atención reservada por Bienestar Social.');
        // Caso 9: Rechazo de participación
        $this->upsertLlamada($campanaVigente->id, $cuentas['ppd'][7]->id, $coordEntrev, 'Rechazo de participación', 'rechazo_participacion', true, 'Indicó que por motivos personales prefiere no responder la encuesta en este semestre.');

        // Registrar casos en egresado_casos (uno pedagógico/empleabilidad y uno confidencial de bienestar)
        Caso::updateOrCreate(
            ['codigo' => 'CASO-DEMO-PEDAGOGIA-01'],
            [
                'user_id' => $cuentas['fid'][6]->id,
                'nombre_egresado' => trim($cuentas['fid'][6]->name . ' ' . $cuentas['fid'][6]->apellidos),
                'tema' => 'pedagogia',
                'responsable' => trim($coord->name . ' ' . $coord->apellidos),
                'fecha_gestion' => now()->addDays(5)->toDateString(),
                'estado' => 'En atención',
                'proposito' => 'Orientación sobre planificación curricular en aulas multigrado EIB.',
                'es_confidencial_bienestar' => false,
                'origen' => 'Mi espacio',
            ]
        );

        Caso::updateOrCreate(
            ['codigo' => 'CASO-DEMO-BIENESTAR-02'],
            [
                'user_id' => $cuentas['ppd'][6]->id,
                'nombre_egresado' => trim($cuentas['ppd'][6]->name . ' ' . $cuentas['ppd'][6]->apellidos),
                'tema' => 'bienestar',
                'responsable' => trim($cuentas['bienestar']->name . ' ' . $cuentas['bienestar']->apellidos),
                'fecha_gestion' => now()->addDays(3)->toDateString(),
                'estado' => 'Por revisar',
                'proposito' => 'NOTA_CONFIDENCIAL_BIENESTAR_DEMO: Acompañamiento socioemocional reservado.',
                'es_confidencial_bienestar' => true,
                'origen' => 'Mi espacio',
            ]
        );

        // Informe congelado de la campaña cerrada (CAMP-DEMO-2026-I) sin nombres ni datos personales
        if (Schema::hasTable('egresado_campana_cortes')) {
            DB::table('egresado_campana_cortes')->where('campana_id', $campanaFinalizada->id)->delete();
            DB::table('egresado_campana_cortes')->insert([
                'campana_id' => $campanaFinalizada->id,
                'registrado_por' => $coord->id,
                'datos' => json_encode([
                    'campana' => $campanaFinalizada->only(['nombre', 'codigo', 'fecha_inicio', 'fecha_fin', 'es_ensayo']),
                    'metricas' => [
                        'TODOS' => [
                            'total_asignados' => 24,
                            'total_contactados_efectivos' => 20,
                            'total_actualizados' => 18,
                            'total_intentos_llamada' => 39,
                            'no_localizados' => 4,
                            'perfil_territorio' => [
                                'perfiles_confirmados' => 18,
                                'trabaja' => 16,
                                'no_trabaja' => 2,
                                'reside_comunidad' => 9,
                                'trabaja_comunidad' => 11,
                            ],
                        ],
                        'FID' => [
                            'total_asignados' => 12,
                            'total_contactados_efectivos' => 10,
                            'total_actualizados' => 9,
                            'total_intentos_llamada' => 19,
                            'no_localizados' => 2,
                            'perfil_territorio' => [
                                'perfiles_confirmados' => 9,
                                'trabaja' => 8,
                                'no_trabaja' => 1,
                                'reside_comunidad' => 4,
                                'trabaja_comunidad' => 5,
                            ],
                        ],
                        'PPD' => [
                            'total_asignados' => 12,
                            'total_contactados_efectivos' => 10,
                            'total_actualizados' => 9,
                            'total_intentos_llamada' => 20,
                            'no_localizados' => 2,
                            'perfil_territorio' => [
                                'perfiles_confirmados' => 9,
                                'trabaja' => 8,
                                'no_trabaja' => 1,
                                'reside_comunidad' => 5,
                                'trabaja_comunidad' => 6,
                            ],
                        ],
                        'POR_VERIFICAR' => [
                            'total_asignados' => 0,
                            'total_contactados_efectivos' => 0,
                            'total_actualizados' => 0,
                            'total_intentos_llamada' => 0,
                            'no_localizados' => 0,
                            'perfil_territorio' => [
                                'perfiles_confirmados' => 0,
                                'trabaja' => 0,
                                'no_trabaja' => 0,
                                'reside_comunidad' => 0,
                                'trabaja_comunidad' => 0,
                            ],
                        ],
                    ],
                    'balance' => [
                        'balance' => 'Cierre semestral 2026-I con 75% de cobertura confirmada en FID y PPD.',
                        'horas' => 48,
                        'costo' => 1200,
                        'presupuesto_siguiente' => 1500,
                    ],
                ], JSON_UNESCAPED_UNICODE),
                'created_at' => now()->subMonths(4),
            ]);
        }

        return [
            'finalizada' => $campanaFinalizada,
            'vigente' => $campanaVigente,
            'futura' => $campanaFutura,
        ];
    }

    private function upsertLlamada(
        int $campanaId,
        int $egresadoId,
        User $docente,
        string $resultado,
        string $tipoResultado,
        bool $efectivo,
        string $obs,
        $fechaReprogramada = null,
        bool $requiereDerivacion = false,
        ?string $motivoDerivacion = null
    ): void {
        Llamada::updateOrCreate(
            [
                'campana_id' => $campanaId,
                'user_id' => $egresadoId,
                'docente_user_id' => $docente->id,
                'tipo_resultado' => $tipoResultado,
            ],
            [
                'resultado' => $resultado,
                'es_contacto_efectivo' => $efectivo,
                'fecha_reprogramada' => $fechaReprogramada,
                'requiere_derivacion' => $requiereDerivacion,
                'motivo_derivacion' => $motivoDerivacion,
                'es_ensayo' => true,
                'observaciones' => $obs,
                'encuestador' => $docente->name,
                'fecha_hora' => now()->subDays(2),
            ]
        );
    }

    private function asegurarComunidadYOportunidades(array $cuentas): array
    {
        $facilitador = $cuentas['facilitador_ayllu'];
        $fid1 = $cuentas['fid'][1];
        $fid2 = $cuentas['fid'][2];
        $fid3 = $cuentas['fid'][3];
        $ppd1 = $cuentas['ppd'][1];
        $ppd2 = $cuentas['ppd'][2];
        $ppd5 = $cuentas['ppd'][5];
        $dual = $cuentas['dual'];
        $docAula = $cuentas['docente_aula'];

        // 1. Cuatro Ayllus de Red Puklla (abierto por interés, privado por interés, por promoción e inactivo histórico)
        $aylluAbierto = AylluGrupo::deRedPuklla()->updateOrCreate(
            ['slug' => 'circulo-saberes-eib-demo'],
            [
                'nombre' => 'Círculo de Lectura y Saberes EIB',
                'descripcion' => 'Espacio abierto para compartir prácticas de lectoescritura bilingüe y memoria comunitaria.',
                'tipo' => 'circulo_estudio',
                'privacidad_red' => 'abierto',
                'creado_por' => $facilitador->id,
                'activo' => true,
                'es_oficial' => false,
                'red_egresados' => true,
            ]
        );

        $aylluPrivado = AylluGrupo::deRedPuklla()->updateOrCreate(
            ['slug' => 'taller-materiales-quechua-demo'],
            [
                'nombre' => 'Taller de Materiales en Quechua Collao y Chanka',
                'descripcion' => 'Ayllu privado de cocreación de cuadernos de trabajo bilingües para primaria EIB.',
                'tipo' => 'taller',
                'privacidad_red' => 'privado',
                'creado_por' => $facilitador->id,
                'activo' => true,
                'es_oficial' => false,
                'red_egresados' => true,
            ]
        );

        $aylluPromocion = AylluGrupo::deRedPuklla()->updateOrCreate(
            ['slug' => 'promocion-2023-red-intercultural-demo'],
            [
                'nombre' => 'Promoción 2023 · Red Intercultural',
                'descripcion' => 'Punto de encuentro de egresados y egresadas de la cohorte 2023 (FID y PPD).',
                'tipo' => 'promocion',
                'promocion_red' => 2023,
                'privacidad_red' => 'privado',
                'creado_por' => $dual->id,
                'activo' => true,
                'es_oficial' => false,
                'red_egresados' => true,
            ]
        );

        $aylluInactivo = AylluGrupo::deRedPuklla()->updateOrCreate(
            ['slug' => 'ayllu-historico-piloto-2022-demo'],
            [
                'nombre' => 'Ayllu Histórico Piloto 2022',
                'descripcion' => 'Espacio concluido e inactivo conservado para archivo y revisión institucional.',
                'tipo' => 'intercultural',
                'privacidad_red' => 'privado',
                'creado_por' => $facilitador->id,
                'activo' => false,
                'es_oficial' => false,
                'red_egresados' => true,
            ]
        );

        // Membresías aceptadas y solicitudes pendientes
        $aylluAbierto->miembros()->sync([
            $facilitador->id => ['rol' => 'moderador', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $fid1->id => ['rol' => 'miembro', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $ppd1->id => ['rol' => 'miembro', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $dual->id => ['rol' => 'miembro', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $docAula->id => ['rol' => 'miembro', 'estado_red' => 'aceptado', 'avisos_red' => false],
        ]);

        $aylluPrivado->miembros()->sync([
            $facilitador->id => ['rol' => 'moderador', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $fid1->id => ['rol' => 'miembro', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $ppd1->id => ['rol' => 'miembro', 'estado_red' => 'aceptado', 'avisos_red' => true],
            // Solicitudes pendientes de aceptación por el facilitador
            $ppd5->id => ['rol' => 'miembro', 'estado_red' => 'pendiente', 'avisos_red' => true],
            $fid3->id => ['rol' => 'miembro', 'estado_red' => 'pendiente', 'avisos_red' => true],
        ]);

        $aylluPromocion->miembros()->sync([
            $dual->id => ['rol' => 'moderador', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $cuentas['fid'][6]->id => ['rol' => 'miembro', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $cuentas['ppd'][6]->id => ['rol' => 'miembro', 'estado_red' => 'aceptado', 'avisos_red' => true],
            $fid2->id => ['rol' => 'miembro', 'estado_red' => 'pendiente', 'avisos_red' => true],
        ]);

        // 2. Publicaciones para las 6 categorías (yachay, yuyay, ruray, munay, rimay, pukllay)
        //    y las 5 intenciones (experiencia, pregunta, colaboracion, ofrecer, encuentro)
        $pubYachay = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Relatos del agua y alfabetización inicial en quechua (Yachay)'],
            [
                'user_id' => $fid1->id,
                'ambito' => 'grupo',
                'ayllu_grupo_id' => $aylluAbierto->id,
                'tipo' => 'reflexion',
                'contenido' => 'Comparto las cartillas trabajadas con sabios de la comunidad sobre el cuidado de las qochas y manantes.',
                'resumen_quechua' => 'Unu uywaymanta qillqakuna wawakunawan llamk’anapaq.',
                'categoria_red' => 'yachay',
                'intencion_red' => 'experiencia',
                'enlace_externo' => 'https://www.youtube-nocookie.com/embed/dQw4w9WgXcQ',
                'red_egresados' => true,
                'estado_red' => 'visible',
                'visible' => true,
            ]
        );
        // Normalizar el enlace mediante ContenidoRed::enlace para conservar el formato exacto del controlador
        $pubYachay->update(['enlace_externo' => 'https://youtu.be/dQw4w9WgXcQ']);

        $pubYuyay = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => '¿Cómo acompañamos la transición de primaria EIB a secundaria rural? (Yuyay)'],
            [
                'user_id' => $ppd1->id,
                'ambito' => 'institucional',
                'ayllu_grupo_id' => null,
                'tipo' => 'reflexion',
                'contenido' => 'Abro esta pregunta para recoger experiencias de colegas FID y PPD en Canchis y Quispicanchi.',
                'resumen_quechua' => 'Imaynatataq wawakunata yanapanchik hatun yachaywasiman ripuqtinku.',
                'categoria_red' => 'yuyay',
                'intencion_red' => 'pregunta',
                'enlace_externo' => 'https://www.instagram.com/reel/C8abc123xyz/',
                'red_egresados' => true,
                'estado_red' => 'visible',
                'visible' => true,
            ]
        );

        $pubRuray = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Cocreación de cancionero bilingüe para ciclo III y IV (Ruray)'],
            [
                'user_id' => $dual->id,
                'ambito' => 'grupo',
                'ayllu_grupo_id' => $aylluPrivado->id,
                'tipo' => 'reflexion',
                'contenido' => 'Estamos armando un repertorio de cantos agrícolas con partituras y audio. Buscamos colaboraciones.',
                'resumen_quechua' => 'Takikunata huñusunchik wawakunapaq.',
                'categoria_red' => 'ruray',
                'intencion_red' => 'colaboracion',
                'enlace_externo' => 'https://repositorio.example.test/cancionero-eib',
                'red_egresados' => true,
                'estado_red' => 'visible',
                'visible' => true,
            ]
        );

        $pubMunay = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Acompañamiento entre colegas en el primer año de docencia rural (Munay)'],
            [
                'user_id' => $facilitador->id,
                'ambito' => 'institucional',
                'ayllu_grupo_id' => null,
                'tipo' => 'reflexion',
                'contenido' => 'Ofrezco conversar y compartir orientaciones prácticas para quienes inician contrato en escuelas unidocentes.',
                'categoria_red' => 'munay',
                'intencion_red' => 'ofrecer',
                'red_egresados' => true,
                'estado_red' => 'visible',
                'visible' => true,
            ]
        );

        $pubRimay = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Encuentro presencial de egresados EIB en Sicuani este sábado (Rimay)'],
            [
                'user_id' => $docAula->id,
                'ambito' => 'grupo',
                'ayllu_grupo_id' => $aylluAbierto->id,
                'tipo' => 'reflexion',
                'contenido' => 'Nos reuniremos para dialogar sobre proyectos integradores y saberes territoriales.',
                'categoria_red' => 'rimay',
                'intencion_red' => 'encuentro',
                'red_egresados' => true,
                'estado_red' => 'visible',
                'visible' => true,
            ]
        );

        $pubPukllay = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Juegos tradicionales andinos para matemática intercultural (Pukllay)'],
            [
                'user_id' => $fid2->id,
                'ambito' => 'institucional',
                'ayllu_grupo_id' => null,
                'tipo' => 'reflexion',
                'contenido' => 'Sistematización del juego de la taptana y chockos para resolver problemas aditivos en aula.',
                'categoria_red' => 'pukllay',
                'intencion_red' => 'experiencia',
                'red_egresados' => true,
                'estado_red' => 'visible',
                'visible' => true,
            ]
        );

        $pubAylluInactivo = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Memoria del círculo piloto 2022 en ayllu inactivo'],
            [
                'user_id' => $facilitador->id,
                'ambito' => 'grupo',
                'ayllu_grupo_id' => $aylluInactivo->id,
                'tipo' => 'reflexion',
                'contenido' => 'Registro archivado dentro de un ayllu hoy inactivo, revisable por moderación autorizada.',
                'categoria_red' => 'yachay',
                'intencion_red' => 'experiencia',
                'red_egresados' => true,
                'estado_red' => 'visible',
                'visible' => true,
            ]
        );

        // Publicación compartida por referencia (red_puklla_difusiones) en varios ayllus sin duplicar el registro original
        DB::table('red_puklla_difusiones')->updateOrInsert(
            ['publicacion_id' => $pubYachay->id, 'ayllu_grupo_id' => $aylluPrivado->id],
            ['user_id' => $facilitador->id, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('red_puklla_difusiones')->updateOrInsert(
            ['publicacion_id' => $pubYachay->id, 'ayllu_grupo_id' => $aylluPromocion->id],
            ['user_id' => $dual->id, 'created_at' => now(), 'updated_at' => now()]
        );

        // Comentarios y colaboraciones en distintos estados (ofrecido, en_curso, realizado, retirado)
        AylluComentario::updateOrCreate(
            ['ayllu_publicacion_id' => $pubYachay->id, 'user_id' => $ppd1->id],
            ['contenido' => 'Allinllachu Sumaq, las cartillas nos sirvieron mucho en Marangani.', 'es_aporte_docente' => false]
        );
        AylluComentario::updateOrCreate(
            ['ayllu_publicacion_id' => $pubYuyay->id, 'user_id' => $docAula->id],
            ['contenido' => 'Desde docencia FID podemos articular visitas entre sexto grado y primero de secundaria.', 'es_aporte_docente' => false]
        );

        $colaboraciones = [
            [$pubYachay->id, $ppd1->id, 'experiencia', 'realizado', 'Apliqué la cartilla del agua con tercer grado en Sicuani y sumamos relatos locales.'],
            [$pubYuyay->id, $fid1->id, 'pregunta', 'en_curso', 'Estoy sistematizando las entrevistas a familias de Ocongate para compartirlas.'],
            [$pubRuray->id, $fid1->id, 'colaboracion', 'ofrecido', 'Puedo transcribir tres cantos de cosecha en quechua Collao.'],
            [$pubRimay->id, $ppd2->id, 'encuentro', 'retirado', 'Por distancia desde Marcapata esta vez no alcanzaré a llegar presencialmente.'],
        ];
        foreach ($colaboraciones as [$pubId, $uId, $acc, $est, $aporte]) {
            DB::table('red_puklla_acciones')->updateOrInsert(
                ['publicacion_id' => $pubId, 'user_id' => $uId],
                ['accion' => $acc, 'estado' => $est, 'aporte' => $aporte, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // Encuentros (Calendario Vivo de Red Puklla) y mensajes de chat en los ayllus
        AylluEventoCalendario::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Círculo virtual de lectura EIB · Cartillas del agua', 'ayllu_grupo_id' => $aylluAbierto->id],
            [
                'descripcion' => 'Socialización de experiencias de lectura intergeneracional.',
                'fecha_inicio' => now()->addDays(7)->toDateString(),
                'hora' => '18:00',
                'enlace' => 'https://meet.example.test/circulo-eib',
                'creado_por' => $facilitador->id,
                'tipo' => 'comunitario',
                'red_egresados' => true,
            ]
        );

        AylluMensajeChat::updateOrCreate(
            ['canal_tipo' => 'grupo', 'ayllu_grupo_id' => $aylluAbierto->id, 'emisor_user_id' => $facilitador->id],
            ['mensaje' => 'Allin p’unchay colegas, bienvenidos al Círculo de Lectura y Saberes EIB.']
        );
        AylluMensajeChat::updateOrCreate(
            ['canal_tipo' => 'grupo', 'ayllu_grupo_id' => $aylluPrivado->id, 'emisor_user_id' => $fid1->id],
            ['mensaje' => 'Subí las propuestas de fichas en quechua para revisión del taller.']
        );

        // 3. Oportunidades (trabajo, formación continua y convocatorias) + oferta pública separada
        $ofertaTrabajo = BolsaTrabajoOferta::deRedPuklla()->updateOrCreate(
            ['nombre' => 'Docente EIB Primaria Bilingüe en UGEL Quispicanchi'],
            [
                'autor_red_id' => $facilitador->id,
                'institucion_red' => 'UGEL Quispicanchi',
                'detalles' => 'Convocatoria para plaza orgánica de Educación Primaria EIB en Ocongate.',
                'tipo_red' => 'trabajo',
                'fuente_red' => 'comparte',
                'enlace_red' => 'https://ugelquispicanchi.example.test/convocatoria-eib',
                'correo_red' => 'personal@ugelquispicanchi.example.test',
                'telefono_publicador' => '+51 984 555 101',
                'lugar_red' => 'Ocongate, Quispicanchi',
                'plazo_red' => now()->addDays(14)->toDateString(),
                'situacion_red' => 'abierta',
                'estado_red' => 'visible',
                'red_egresados' => true,
                'nombre_publicador' => trim($facilitador->name . ' ' . $facilitador->apellidos),
                'relacion_publicador' => 'Comparte una fuente',
            ]
        );

        $ofertaFormacion = BolsaTrabajoOferta::deRedPuklla()->updateOrCreate(
            ['nombre' => 'Diplomado en Revitalización Lingüística y Pedagogía Intercultural'],
            [
                'autor_red_id' => $docAula->id,
                'institucion_red' => 'EESP Pukllasunchis · Formación Continua',
                'detalles' => 'Programa semipresencial con becas parciales para egresados FID y PPD.',
                'tipo_red' => 'formacion',
                'fuente_red' => 'ofrece',
                'enlace_red' => 'https://formacion.example.test/diplomado-eib',
                'correo_red' => 'formacion@example.test',
                'lugar_red' => 'Cusco (Semipresencial)',
                'plazo_red' => now()->addDays(25)->toDateString(),
                'situacion_red' => 'abierta',
                'estado_red' => 'visible',
                'red_egresados' => true,
                'nombre_publicador' => trim($docAula->name . ' ' . $docAula->apellidos),
                'relacion_publicador' => 'Ofrece una oportunidad',
            ]
        );

        $ofertaConvocatoriaCerrada = BolsaTrabajoOferta::deRedPuklla()->updateOrCreate(
            ['nombre' => 'Fondo Concursable de Proyectos de Innovación Educativa Rural 2026'],
            [
                'autor_red_id' => $dual->id,
                'institucion_red' => 'FONDEP / Red Docente Andina',
                'detalles' => 'Convocatoria para escuelas rurales EIB con proyectos comunitarios.',
                'tipo_red' => 'convocatoria',
                'fuente_red' => 'comparte',
                'enlace_red' => 'https://fondep.example.test/proyectos-rurales',
                'lugar_red' => 'Región Cusco',
                'plazo_red' => now()->subDays(5)->toDateString(),
                'situacion_red' => 'cerrada',
                'estado_red' => 'visible',
                'red_egresados' => true,
                'nombre_publicador' => trim($dual->name . ' ' . $dual->apellidos),
                'relacion_publicador' => 'Comparte una fuente',
            ]
        );

        // Oferta de la bolsa pública general (red_egresados = false) para comprobar separación de ámbitos
        $ofertaBolsaPublica = BolsaTrabajoOferta::updateOrCreate(
            ['nombre' => 'Oferta Exclusiva de Bolsa Pública Externa (No Red Puklla)'],
            [
                'detalles' => 'Esta oferta pertenece al portal público general y no debe mezclarse en Red Puklla.',
                'nombre_publicador' => 'Entidad Externa Pública',
                'relacion_publicador' => 'Empleador externo',
                'telefono_publicador' => '984000777',
                'red_egresados' => false,
                'estado_red' => 'visible',
            ]
        );

        // 4. Guardados privados en distintos estados (guardado, me_interesa, contacte, postule, finalizada)
        $guardados = [
            [$fid1->id, 'oportunidad', $ofertaTrabajo->id, 'postule'],
            [$fid1->id, 'oportunidad', $ofertaFormacion->id, 'me_interesa'],
            [$fid1->id, 'publicacion', $pubYuyay->id, 'guardado'],
            [$ppd1->id, 'oportunidad', $ofertaTrabajo->id, 'contacte'],
            [$ppd1->id, 'oportunidad', $ofertaConvocatoriaCerrada->id, 'finalizada'],
        ];
        foreach ($guardados as [$uId, $tipo, $refId, $estado]) {
            DB::table('red_puklla_guardados')->updateOrInsert(
                ['user_id' => $uId, 'tipo' => $tipo, 'referencia_id' => $refId],
                ['estado' => $estado, 'created_at' => now(), 'updated_at' => now()]
            );
        }

        // 5. Preferencias de avisos activados y desactivados
        DB::table('red_puklla_preferencias')->updateOrInsert(
            ['user_id' => $fid1->id],
            ['avisos' => true, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('red_puklla_preferencias')->updateOrInsert(
            ['user_id' => $fid2->id],
            ['avisos' => false, 'created_at' => now(), 'updated_at' => now()]
        );
        DB::table('red_puklla_preferencias')->updateOrInsert(
            ['user_id' => $fid3->id],
            ['avisos' => false, 'created_at' => now(), 'updated_at' => now()]
        );

        return [
            'ayllu_abierto' => $aylluAbierto,
            'ayllu_privado' => $aylluPrivado,
            'ayllu_promocion' => $aylluPromocion,
            'ayllu_inactivo' => $aylluInactivo,
            'pub_yachay' => $pubYachay,
            'pub_yuyay' => $pubYuyay,
            'pub_ruray' => $pubRuray,
            'pub_munay' => $pubMunay,
            'pub_rimay' => $pubRimay,
            'pub_pukllay' => $pubPukllay,
            'pub_ayllu_inactivo' => $pubAylluInactivo,
            'oferta_trabajo' => $ofertaTrabajo,
            'oferta_formacion' => $ofertaFormacion,
            'oferta_cerrada' => $ofertaConvocatoriaCerrada,
            'oferta_bolsa_publica' => $ofertaBolsaPublica,
        ];
    }

    private function asegurarModeracionYSolicitudRevision(array $cuentas, array $comunidad): void
    {
        $coord = $cuentas['docente_coordinador'];
        $admin = $cuentas['admin'];
        $autorOculto = $cuentas['fid'][2];
        $autorRetirado = $cuentas['fid'][3];
        $lector = $cuentas['fid'][1];

        // 1. Publicación en estado OCULTO (en revisión por coordinación)
        $pubOculta = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Publicación en revisión preventiva por datos de terceros'],
            [
                'user_id' => $autorOculto->id,
                'ambito' => 'institucional',
                'ayllu_grupo_id' => null,
                'tipo' => 'reflexion',
                'contenido' => 'Texto temporalmente oculto mientras se verifica autorización de difusión.',
                'categoria_red' => 'yachay',
                'intencion_red' => 'experiencia',
                'red_egresados' => true,
                'estado_red' => 'oculto',
                'visible' => false,
            ]
        );

        // 2. Publicación en estado RETIRADO que había sido compartida por referencia en dos ayllus y guardada
        $pubRetirada = AylluPublicacion::deRedPuklla()->updateOrCreate(
            ['titulo' => 'Publicación retirada de circulación por incumplir política comunitaria'],
            [
                'user_id' => $autorRetirado->id,
                'ambito' => 'grupo',
                'ayllu_grupo_id' => $comunidad['ayllu_abierto']->id,
                'tipo' => 'reflexion',
                'contenido' => 'Contenido retirado globalmente de todos los muros y referencias.',
                'categoria_red' => 'rimay',
                'intencion_red' => 'pregunta',
                'red_egresados' => true,
                'estado_red' => 'retirado',
                'visible' => false,
            ]
        );

        // Referencias previas en varios ayllus y en guardados para comprobar que el retiro afecta a todas las referencias
        DB::table('red_puklla_difusiones')->updateOrInsert(
            ['publicacion_id' => $pubRetirada->id, 'ayllu_grupo_id' => $comunidad['ayllu_privado']->id],
            ['user_id' => $cuentas['facilitador_ayllu']->id, 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)]
        );
        DB::table('red_puklla_guardados')->updateOrInsert(
            ['user_id' => $lector->id, 'tipo' => 'publicacion', 'referencia_id' => $pubRetirada->id],
            ['estado' => 'guardado', 'created_at' => now()->subDays(2), 'updated_at' => now()->subDays(2)]
        );

        // Limpiar registros previos de moderación sobre estas dos publicaciones para mantener idempotencia
        DB::table('red_puklla_moderacion')
            ->where('tipo', 'publicacion')
            ->whereIn('referencia_id', [$pubOculta->id, $pubRetirada->id])
            ->delete();

        $motivoOcultar = 'Ocultada preventivamente: incluye fotografías de aula sin constancia de autorización familiar.';
        $motivoRetirar = 'Retirada por incumplir la política de convivencia: difusión de datos personales sensibles de terceros.';
        $motivoSolicitudRevision = 'Solicito revisión de la decisión: he retirado los datos personales y adjunto autorización institucional.';

        DB::table('red_puklla_moderacion')->insert([
            [
                'tipo' => 'publicacion',
                'referencia_id' => $pubOculta->id,
                'actor_id' => $lector->id,
                'accion' => 'reportar',
                'motivo' => 'Solicito verificar si cuenta con permiso para difundir nombres completos.',
                'created_at' => now()->subHours(6),
                'updated_at' => now()->subHours(6),
            ],
            [
                'tipo' => 'publicacion',
                'referencia_id' => $pubOculta->id,
                'actor_id' => $coord->id,
                'accion' => 'ocultar',
                'motivo' => $motivoOcultar,
                'created_at' => now()->subHours(5),
                'updated_at' => now()->subHours(5),
            ],
            [
                'tipo' => 'publicacion',
                'referencia_id' => $pubRetirada->id,
                'actor_id' => $admin->id,
                'accion' => 'retirar',
                'motivo' => $motivoRetirar,
                'created_at' => now()->subHours(4),
                'updated_at' => now()->subHours(4),
            ],
            [
                'tipo' => 'publicacion',
                'referencia_id' => $pubRetirada->id,
                'actor_id' => $autorRetirado->id,
                'accion' => 'solicitar_revision',
                'motivo' => $motivoSolicitudRevision,
                'created_at' => now()->subHours(2),
                'updated_at' => now()->subHours(2),
            ],
        ]);

        // Notificación interna obligatoria enviada a los autores moderados (incluso con avisos comunitarios desactivados)
        $autorOculto->notifications()->where('type', RedPukllaAviso::class)->delete();
        $autorOculto->notify(new RedPukllaAviso(
            "Tu contenido fue revisado. Estado: oculto. Motivo: {$motivoOcultar}",
            'publicacion',
            $pubOculta->id
        ));

        $autorRetirado->notifications()->where('type', RedPukllaAviso::class)->delete();
        $autorRetirado->notify(new RedPukllaAviso(
            "Tu contenido fue revisado. Estado: retirado. Motivo: {$motivoRetirar}",
            'publicacion',
            $pubRetirada->id
        ));
    }
}
