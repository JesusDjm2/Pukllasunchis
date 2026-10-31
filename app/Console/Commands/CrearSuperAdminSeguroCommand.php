<?php

namespace App\Console\Commands;

use App\Models\SeguimientoEgresado\AuditoriaRolSeguimiento;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Validator;
use Spatie\Permission\Models\Role;

class CrearSuperAdminSeguroCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'puklla:superadmin-crear
                            {--email= : Correo institucional del nuevo superadministrador}
                            {--dni= : DNI institucional}
                            {--name= : Nombres}
                            {--apellidos= : Apellidos}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Procedimiento seguro y reproducible para la provisión de un usuario con rol super-admin sin credenciales en código';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->info('================================================================');
        $this->info('  EESP PUKLLASUNCHIS · PROVISIÓN SEGURA DE CUENTA SUPER-ADMIN   ');
        $this->info('================================================================');

        $email = $this->option('email') ?: $this->ask('Ingrese el correo institucional del superadministrador:');
        $email = strtolower(trim($email));

        $validadorEmail = Validator::make(['email' => $email], [
            'email' => 'required|email'
        ]);

        if ($validadorEmail->fails()) {
            $this->error('El correo electrónico proporcionado no es válido.');
            return Command::FAILURE;
        }

        // Verificar si el usuario ya existe
        $usuarioExistente = User::where('email', $email)->first();

        if ($usuarioExistente && $usuarioExistente->hasRole('super-admin')) {
            $this->warn("El usuario {$email} ya existe y ya cuenta con el rol super-admin.");
            return Command::SUCCESS;
        }

        $name = $this->option('name') ?: ($usuarioExistente ? $usuarioExistente->name : $this->ask('Nombres:'));
        $apellidos = $this->option('apellidos') ?: ($usuarioExistente ? $usuarioExistente->apellidos : $this->ask('Apellidos:'));
        $dni = $this->option('dni') ?: ($usuarioExistente ? $usuarioExistente->dni : $this->ask('DNI:'));

        // Solicitar contraseña de manera oculta (secret), sin echo en consola ni en historial
        $password = $this->secret('Ingrese una contraseña segura (mínimo 8 caracteres):');
        $passwordConfirm = $this->secret('Confirme la contraseña:');

        if ($password !== $passwordConfirm) {
            $this->error('Las contraseñas no coinciden. Operación cancelada por seguridad.');
            return Command::FAILURE;
        }

        if (strlen($password) < 8) {
            $this->error('La contraseña debe tener al menos 8 caracteres.');
            return Command::FAILURE;
        }

        Role::firstOrCreate(['name' => 'super-admin', 'guard_name' => 'web']);

        if ($usuarioExistente) {
            $this->info("Promoviendo cuenta existente ID {$usuarioExistente->id} a super-admin...");
            $rolesAnteriores = $usuarioExistente->getRoleNames()->toArray();
            $usuarioExistente->password = Hash::make($password);
            $usuarioExistente->assignRole('super-admin');
            $usuarioExistente->save();

            AuditoriaRolSeguimiento::registrarEvento(
                $usuarioExistente,
                'asignado',
                'super-admin',
                'Promoción a super-admin mediante comando CLI de provisión segura',
                $rolesAnteriores,
                $usuarioExistente->getRoleNames()->toArray()
            );

            Log::info("Cuenta de usuario ID {$usuarioExistente->id} promovida a super-admin vía CLI seguro.");
        } else {
            $this->info("Creando nuevo usuario super-admin institucional...");
            $nuevoUsuario = User::create([
                'name' => trim($name),
                'apellidos' => trim($apellidos),
                'email' => $email,
                'dni' => trim($dni),
                'password' => Hash::make($password),
                'condicion' => null,
            ]);

            $nuevoUsuario->assignRole('super-admin');

            AuditoriaRolSeguimiento::registrarEvento(
                $nuevoUsuario,
                'asignado',
                'super-admin',
                'Creación de cuenta super-admin institucional mediante comando CLI seguro',
                [],
                ['super-admin']
            );

            Log::info("Nuevo super-admin institucional creado vía CLI seguro: ID {$nuevoUsuario->id}.");
        }

        $this->newLine();
        $this->info("✔ Provisión completada exitosamente.");
        $this->info("  Usuario: {$name} {$apellidos} ({$email})");
        $this->info("  Rol: super-admin asignado correctamente.");
        $this->info("  Trazabilidad registrada en egresado_auditoria_roles.");
        $this->newLine();

        return Command::SUCCESS;
    }
}
