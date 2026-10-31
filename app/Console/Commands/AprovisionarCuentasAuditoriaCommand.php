<?php

namespace App\Console\Commands;

use App\Models\Curso;
use App\Models\Docente;
use App\Models\Programa;
use App\Models\SeguimientoEgresado\Actualizacion;
use App\Models\SeguimientoEgresado\Campana;
use App\Models\SeguimientoEgresado\EpisodioLaboral;
use App\Models\SeguimientoEgresado\GrupoEntrevistador;
use App\Models\SeguimientoEgresado\InvitacionCampana;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class AprovisionarCuentasAuditoriaCommand extends Command
{
    protected $signature = 'puklla:auditoria-perfiles {--password= : Contraseña fija para todos los perfiles de ensayo}';

    protected $description = 'Aprovisiona de forma segura los 6 perfiles institucionales sintéticos para auditoría manual local';

    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  PUKLLA EN RED — Aprovisionamiento de Cuentas para Auditoría  ');
        $this->info('================================================================');

        $passwordComun = $this->option('password') ?: 'Puklla2026!Auditor';
        $hashed = Hash::make($passwordComun);

        // 1. Asegurar roles institucionales en Spatie
        $rolesNecesarios = [
            'super-admin', 'admin', 'docente', 'seguimiento-egresados',
            'coordinacion-empleabilidad', 'direccion', 'egresado', 'bienestar', 'investigador'
        ];
        foreach ($rolesNecesarios as $r) {
            Role::firstOrCreate(['name' => $r, 'guard_name' => 'web']);
        }

        // Permisos de analítica para dirección
        $permAnalitica = Permission::firstOrCreate(['name' => 'seguimiento-egresados.ver-analitica', 'guard_name' => 'web']);
        $permExportar = Permission::firstOrCreate(['name' => 'seguimiento-egresados.exportar-analitica', 'guard_name' => 'web']);
        $rolDireccion = Role::where('name', 'direccion')->first();
        if ($rolDireccion) {
            $rolDireccion->givePermissionTo([$permAnalitica, $permExportar]);
        }

        $programa = Programa::first();
        $curso = Curso::first();

        // 2. Perfil 1: Superadministrador
        $sa = User::updateOrCreate(
            ['email' => 'auditor.superadmin@puklla.test'],
            [
                'name' => 'Auditor SuperAdmin',
                'apellidos' => 'Pukllasunchis Cusco',
                'password' => $hashed,
                'dni' => '00000001',
            ]
        );
        $sa->syncRoles(['super-admin']);

        // 3. Perfil 2: Docente con rol seguimiento-egresados
        $docSegUser = User::updateOrCreate(
            ['email' => 'auditor.docente.seguimiento@puklla.test'],
            [
                'name' => 'Prof. Carlos',
                'apellidos' => 'Mendoza Tapia',
                'password' => $hashed,
                'dni' => '00000002',
            ]
        );
        $docSegUser->syncRoles(['docente', 'seguimiento-egresados']);
        $docenteSeg = Docente::updateOrCreate(
            ['user_id' => $docSegUser->id],
            [
                'nombre' => 'Carlos Mendoza Tapia',
                'dni' => '00000002',
                'email' => $docSegUser->email,
            ]
        );
        if ($curso && !$docenteSeg->cursos()->where('curso_id', $curso->id)->exists()) {
            $docenteSeg->cursos()->attach($curso->id);
        }

        // 4. Perfil 3: Docente regular (sin seguimiento de egresados)
        $docRegUser = User::updateOrCreate(
            ['email' => 'auditor.docente.regular@puklla.test'],
            [
                'name' => 'Prof. Elena',
                'apellidos' => 'Ramos Huamán',
                'password' => $hashed,
                'dni' => '00000003',
            ]
        );
        $docRegUser->syncRoles(['docente']);
        $docenteReg = Docente::updateOrCreate(
            ['user_id' => $docRegUser->id],
            [
                'nombre' => 'Elena Ramos Huamán',
                'dni' => '00000003',
                'email' => $docRegUser->email,
            ]
        );
        if ($curso && !$docenteReg->cursos()->where('curso_id', $curso->id)->exists()) {
            $docenteReg->cursos()->attach($curso->id);
        }

        // 5. Perfil 4: Coordinación de Empleabilidad y Seguimiento
        $coordUser = User::updateOrCreate(
            ['email' => 'auditor.coordinacion@puklla.test'],
            [
                'name' => 'Lic. Mariana',
                'apellidos' => 'Morales Vega',
                'password' => $hashed,
                'dni' => '00000004',
            ]
        );
        $coordUser->syncRoles(['coordinacion-empleabilidad', 'admin']);

        // 6. Perfil 5: Dirección General / Académica
        $dirUser = User::updateOrCreate(
            ['email' => 'auditor.direccion@puklla.test'],
            [
                'name' => 'Dra. Rosa',
                'apellidos' => 'Benavente Ochoa',
                'password' => $hashed,
                'dni' => '00000005',
            ]
        );
        $dirUser->syncRoles(['direccion']);
        $dirUser->givePermissionTo([$permAnalitica, $permExportar]);

        // 7. Perfil 6: Egresada Asignada
        $egresada = User::updateOrCreate(
            ['email' => 'auditor.egresada@puklla.test'],
            [
                'name' => 'Ana María',
                'apellidos' => 'Cusihuamán Quispe',
                'password' => $hashed,
                'dni' => '00000006',
                'condicion' => 'Egresado',
                'programa_id' => $programa?->id,
            ]
        );
        $egresada->syncRoles(['egresado']);

        // 8. Egresado No Asignado (para comprobar restricción 403)
        $egrNoAsig = User::updateOrCreate(
            ['email' => 'egresado.noasignado@puklla.test'],
            [
                'name' => 'Juan Diego',
                'apellidos' => 'Paucar Condori',
                'password' => $hashed,
                'dni' => '00000007',
                'condicion' => 'Egresado',
                'programa_id' => $programa?->id,
            ]
        );
        $egrNoAsig->syncRoles(['egresado']);

        // 9. Campaña vigente institucional
        $campana = Campana::updateOrCreate(
            ['codigo' => 'CAMP-2026-I'],
            [
                'nombre' => 'Campaña Semestral de Seguimiento 2026-I',
                'anio' => 2026,
                'semestre' => 'I',
                'fecha_inicio' => '2026-01-01',
                'fecha_fin' => '2026-12-31',
                'estado' => 'activa',
                'descripcion' => 'Campaña oficial institucional para evaluación y acompañamiento laboral',
            ]
        );

        // 10. Grupo activo con asignación al Prof. Carlos Mendoza
        $grupo = GrupoEntrevistador::updateOrCreate(
            ['nombre' => 'Grupo 01: EIB Anta y Calca'],
            [
                'campana_id' => $campana->id,
                'coordinador_id' => $coordUser->id,
                'programa' => $programa?->nombre ?? 'Educación Intercultural Bilingüe',
                'cohorte' => 'Promoción 2025',
                'entrevistadores' => [$docSegUser->id],
                'egresados_ids' => [$egresada->id],
                'estado' => 'activo',
                'observaciones' => 'Grupo de acompañamiento prioritario territorial',
            ]
        );

        // 11. Episodio laboral previo para la egresada asignada
        $episodio = EpisodioLaboral::firstOrCreate(
            [
                'user_id' => $egresada->id,
                'institucion' => 'I.E. 50123 Virgen del Carmen - Anta',
            ],
            [
                'cargo' => 'Docente de Educación Inicial Bilingüe',
                'fecha_inicio' => '2025-03-01',
                'es_actual' => true,
                'contrato' => 'Nombrado / Concurso Público',
                'sector' => 'Público',
                'es_empleo_educativo' => true,
                'es_docencia_directa' => true,
                'canal_registro' => 'institucional',
                'registrado_por' => $coordUser->id,
            ]
        );

        // 12. Actualización de padrón
        Actualizacion::firstOrCreate(
            [
                'user_id' => $egresada->id,
                'campana_id' => $campana->id,
            ],
            [
                'nombre_completo' => 'Ana María Cusihuamán Quispe',
                'trabaja' => 'si',
                'institucion' => 'I.E. 50123 Virgen del Carmen - Anta',
                'cargo' => 'Docente de Educación Inicial Bilingüe',
                'canal' => 'institucional',
                'estado_verificacion' => 'verificado',
                'procedencia' => 'entrevista_docente',
                'registrado_por' => $coordUser->id,
            ]
        );

        // 13. Token de invitación WhatsApp para ensayo de formulario público
        $resInv = InvitacionCampana::crearInvitacionSegura(
            $egresada,
            $coordUser->id,
            '2026-I',
            'whatsapp'
        );
        $tokenInvitacion = $resInv['plain_token'];

        // 14. Guardar archivo local de credenciales seguro (ignorado por git)
        $archivoCredenciales = storage_path('credenciales_auditoria.txt');
        $contenidoTxt = "====================================================================\n"
            . "  PUKLLA EN RED — CREDENCIALES LOCALES DE AUDITORÍA MANUAL\n"
            . "  EESP Pukllasunchis · Entorno de Ensayo Local\n"
            . "====================================================================\n\n"
            . "CONTRASEÑA GENERAL PARA TODOS LOS PERFILES: {$passwordComun}\n\n"
            . "1. SUPERADMINISTRADOR (Gestión de usuarios, roles y auditoría)\n"
            . "   - Correo: auditor.superadmin@puklla.test\n"
            . "   - Destino post-login: /admin (Panel de Administración)\n\n"
            . "2. DOCENTE CON ROL SEGUIMIENTO (Entrevistas, campañas y asignaciones)\n"
            . "   - Correo: auditor.docente.seguimiento@puklla.test\n"
            . "   - Destino post-login: /docentes/{$docenteSeg->id} (Dashboard Docente)\n"
            . "   - Puklla en Red: visible en barra lateral y tarjeta destacada\n"
            . "   - Egresada asignada: Ana María Cusihuamán Quispe (ID {$egresada->id})\n\n"
            . "3. DOCENTE REGULAR (Sin rol seguimiento-egresados)\n"
            . "   - Correo: auditor.docente.regular@puklla.test\n"
            . "   - Destino post-login: /docentes/{$docenteReg->id} (Dashboard Docente)\n"
            . "   - Puklla en Red: NO VISIBLE (dashboard docente regular intacto)\n\n"
            . "4. COORDINACIÓN DE EMPLEABILIDAD (Padrón, grupos, campañas, casos)\n"
            . "   - Correo: auditor.coordinacion@puklla.test\n"
            . "   - Destino post-login: /seguimiento-egresados (Puklla en Red)\n\n"
            . "5. DIRECCIÓN GENERAL / ACADÉMICA (Observatorio, analítica, reportes)\n"
            . "   - Correo: auditor.direccion@puklla.test\n"
            . "   - Destino post-login: /seguimiento-egresados/laboratorio\n\n"
            . "6. EGRESADA (Actualización de datos propios)\n"
            . "   - Correo: auditor.egresada@puklla.test\n"
            . "   - Destino post-login: /egresado (Intranet de egresado)\n"
            . "   - Enlace público con token WhatsApp (Fase 1C):\n"
            . "     /seguimiento-egresados/campana/{$tokenInvitacion}\n\n"
            . "7. EGRESADO NO ASIGNADO (Para prueba de bloqueo 403):\n"
            . "   - Correo: egresado.noasignado@puklla.test (ID {$egrNoAsig->id})\n"
            . "====================================================================\n";

        file_put_contents($archivoCredenciales, $contenidoTxt);

        $this->info("✓ Perfiles aprovisionados con éxito.");
        $this->info("✓ Archivo local seguro generado en: {$archivoCredenciales}");
        $this->table(
            ['Rol / Responsabilidad', 'Correo Electrónico', 'Destino Post-Login'],
            [
                ['Superadministrador', 'auditor.superadmin@puklla.test', '/admin'],
                ['Docente c/ Seguimiento', 'auditor.docente.seguimiento@puklla.test', "/docentes/{$docenteSeg->id}"],
                ['Docente Regular', 'auditor.docente.regular@puklla.test', "/docentes/{$docenteReg->id}"],
                ['Coordinación Empleabilidad', 'auditor.coordinacion@puklla.test', '/seguimiento-egresados'],
                ['Dirección General', 'auditor.direccion@puklla.test', '/seguimiento-egresados/laboratorio'],
                ['Egresada Institucional', 'auditor.egresada@puklla.test', '/egresado'],
            ]
        );

        return 0;
    }
}
