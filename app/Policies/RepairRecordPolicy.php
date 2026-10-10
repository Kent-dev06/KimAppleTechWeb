<?php

namespace App\Policies;

use App\Models\{RepairRecord, User};

class RepairRecordPolicy
{
    public function manage(User $user, RepairRecord $repair): bool
    {
        return $user->can('manage-repairs');
    }

    public function delete(User $user, RepairRecord $repair): bool
    {
        return $user->role === 'admin';
    }
}
