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
}
