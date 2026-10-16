<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    protected $fillable = [
        'name',
        'apellidos',
        'dni',
        'condicion',
        'pendiente',
        'perfil',
        'beca',
        'email',
        'genero',
        'etnicoidad',
        'password',
        'foto',

        'estadoCivil',
        'fecha_nacimiento',
        'edad',
        'hijos',
        'lengua_1',
        'lengua_2',
        'domicilio',
        'telefono',
        'whatsapp_key',
        'dni_adjunto',
        'programa_id',  // <-- ASEGÚRATE QUE ESTÉN AQUÍ
        'ciclo_id', 
    ];

    protected $hidden = [
        'password',
        'remember_token',
    ];

    protected $casts = [
        'email_verified_at' => 'datetime',
        'password' => 'hashed',
    ];

    /**
     * Cada cambio de perfil / condición / ciclo / programa de un estudiante queda como evento
     * de trayectoria (licencia, retiro, reincorporación, egreso…): estos campos solo guardan
     * el estado actual. En 'updated' getOriginal() todavía tiene los valores de antes.
     */
    protected static function booted(): void
    {
        static::updated(function (User $user) {
            $campos = array_intersect(['perfil', 'condicion', 'ciclo_id', 'programa_id'], array_keys($user->getChanges()));
            if ($campos) {
                \App\Services\Trayectoria::registrarCambios(
                    $user,
                    collect($campos)->mapWithKeys(fn ($c) => [$c => $user->getOriginal($c)])->all()
                );
            }
            // Nombres, DNI, programa y ciclo también viven en la ficha de matrícula: se mantienen iguales.
            \App\Services\SincronizarFicha::desdeCuenta($user);
        });
    }

    public function trayectoriaEventos()
    {
        return $this->hasMany(TrayectoriaEvento::class)->orderByDesc('fecha_inicio')->orderByDesc('id');
    }

    public function alumno()
    {
        return $this->hasOne(Alumno::class);
    }

    public function alumnoB()
    {
        return $this->hasOne(ppd::class);
    }

    public function programa()
    {
        return $this->belongsTo(Programa::class);
    }

    public function docente()
    {
        return $this->hasOne(Docente::class);
    }

    /** Separador de users.pendiente: los nombres de curso pueden tener comas. */
    public const SEPARADOR_PENDIENTES = ' | ';

    /**
     * Cursos pendientes (a cargo) como lista. Se guardan elegidos de los cursos reales
     * ("Nombre - Ciclo X", separados por " | "); los registros antiguos, escritos a mano,
     * están separados por comas. "No", "Ninguno", "-"… no son cursos: lista vacía.
     */
    public function cursosPendientes(): array
    {
        $texto = trim((string) $this->pendiente);
        if ($texto === '') {
            return [];
        }
        $partes = str_contains($texto, '|') ? explode('|', $texto) : explode(',', $texto);

        return array_values(array_filter(
            array_map('trim', $partes),
            fn ($p) => $p !== '' && ! preg_match('/^(no|ninguno|ninguna|-+|n\/?a|sin cursos?( pendientes?)?)\.?$/iu', $p)
        ));
    }

    public function seguimientoTitulacion()
    {
        return $this->hasOne(\App\Models\Titulacion\SeguimientoTitulacion::class);
    }

    public function ciclo()
    {
        return $this->belongsTo(Ciclo::class);
    }

    public function postulante()
    {
        return $this->hasOne(Postulante::class);
    }

    public function tutorCiclos()
    {
        return $this->belongsToMany(Ciclo::class, 'tutor_ciclos')->withTimestamps();
    }

    /**
     * Correo a usar para notificaciones: un tutor a menudo tiene el correo
     * "real" cargado en su ficha de Docente y no en su cuenta de usuario.
     */
    public function correoNotificacion(): ?string
    {
        return $this->docente?->email ?: $this->email;
    }

    /**
     * Primer nombre + primer apellido, para mostrar en espacios reducidos
     * (p. ej. el póster de QR) sin el nombre completo ni segundos nombres/apellidos.
     */
    public function nombreCorto(): string
    {
        $primerNombre = trim(explode(' ', trim((string) $this->name))[0] ?? '');
        $primerApellido = trim(explode(' ', trim((string) $this->apellidos))[0] ?? '');

        return trim($primerNombre.' '.$primerApellido);
    }
}
