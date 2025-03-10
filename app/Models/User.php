<?php

namespace App\Models;

//use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
    use HasRoles;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'apellidos',
        'dni',
        'condicion',
        'pendiente',
        'perfil',
        'beca',
        'email',
        'password',
        'foto',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * The attributes that should be cast.
     *
     * @var array<string, string>
     */
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
        return $this->hasOne(Ppd::class);
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
}
