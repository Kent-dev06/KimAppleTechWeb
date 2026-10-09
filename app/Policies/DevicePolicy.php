<?php

namespace App\Policies;

use App\Models\{Device, User};

class DevicePolicy
{
    public function manage(User $user, Device $device): bool
    {
        return $user->can('manage-repairs') && $device->device_type === 'Smartphone';
    }

    public function delete(User $user, Device $device): bool
    {
        return $user->role === 'admin' && $device->device_type === 'Smartphone';
    }
}
