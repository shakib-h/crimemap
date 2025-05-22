<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Crime;

class CrimePolicy
{
    public function moderate(User $user, Crime $crime)
    {
        return $user->isAdmin() || $user->isModerator();
    }
}