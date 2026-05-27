<?php

namespace App\Policies;

use App\Models\Perro;
use App\Models\User;

class DogPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('ver');
    }

    public function view(User $user, Perro $perro): bool
    {
        return $user->can('ver');
    }

    public function create(User $user): bool
    {
        return $user->can('crear');
    }

    public function update(User $user, Perro $perro): bool
    {
        // El dueño siempre puede editar su perro
        return $perro->user_id === $user->id || $user->can('editar');
    }

    public function delete(User $user, Perro $perro): bool
    {
        return $perro->user_id === $user->id || $user->can('borrar');
    }
}
