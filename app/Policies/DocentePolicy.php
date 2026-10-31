<?php

namespace App\Policies;

use App\Models\Docente;
use App\Models\User;

class DocentePolicy
{
    private const ADMIN_ROLES = ['admin', 'super-admin'];

    public function viewAny(User $user): bool
    {
        return $this->isAdmin($user);
    }

    public function create(User $user): bool
    {
        return $this->isAdmin($user);
    }

    /**
     * Un docente solo sobre su propio registro; la administración sobre cualquiera.
     * El perfil también lo consultan alumnos, y esos casos se autorizan por rol en la ruta.
     */
    public function view(User $user, Docente $docente): bool
    {
        return $this->isAdmin($user)
            || $user->hasAnyRole(['alumno', 'alumnoB'])
            || $this->isOwn($user, $docente);
    }

    public function update(User $user, Docente $docente): bool
    {
        return $this->isAdmin($user) || $this->isOwn($user, $docente);
    }

    public function delete(User $user, Docente $docente): bool
    {
        return $this->isAdmin($user);
    }

    /** Pantallas de trabajo del docente: su propio panel de cursos, notas e incidencias. */
    public function gestionar(User $user, Docente $docente): bool
    {
        return $this->update($user, $docente);
    }

    private function isAdmin(User $user): bool
    {
        return $user->hasAnyRole(self::ADMIN_ROLES);
    }

    private function isOwn(User $user, Docente $docente): bool
    {
        return optional($user->docente)->id === $docente->id;
    }
}
