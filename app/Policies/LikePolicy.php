<?php

namespace App\Policies;

use App\Models\Like;
use App\Models\User;

class LikePolicy
{
    public function create(User $user): bool
    {
        return $user->can('crear');
    }

    public function delete(User $user, Like $like): bool
    {
        return $like->de_user_id === $user->id;
    }
}
