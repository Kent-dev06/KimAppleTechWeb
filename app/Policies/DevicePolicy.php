<?php

namespace App\Policies;

use App\Models\{Device, User};

class DevicePolicy
{
    public function manage(User $user, Device $device): bool
    {
        return $user->can('manage-repairs');
    }

    public function delete(User $user, Device $device): bool
    {
        return $user->role === 'admin';
    }
}
