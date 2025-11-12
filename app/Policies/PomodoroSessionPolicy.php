<?php

namespace App\Policies;

use App\Models\PomodoroSession;
use App\Models\User;

class PomodoroSessionPolicy
{
    public function view(User $user, PomodoroSession $session)
    {
        return $user->id === $session->user_id;
    }

    public function update(User $user, PomodoroSession $session)
    {
        return $user->id === $session->user_id;
    }

    public function delete(User $user, PomodoroSession $session)
    {
        return $user->id === $session->user_id;
    }
}
