<?php

namespace App\Policies;

use App\Models\{Appointment, User};
use Illuminate\Auth\Access\Response;

class AppointmentPolicy
{
    public function manage(User $user, Appointment $appointment): bool
    {
        return $user->can('manage-repairs')
            && $appointment->device?->device_type === 'Smartphone';
    }

    public function cancel(User $user, Appointment $appointment): Response
    {
        $customerId = $user->customer?->customer_id;

        if (strtolower($user->role) !== 'customer' || (int) $appointment->customer_id !== (int) $customerId) {
            return Response::deny('You may only cancel your own appointments.');
        }

        if ($appointment->status !== 'Pending') {
            return Response::deny('Only pending appointments can be cancelled.');
        }

        return Response::allow();
    }

    public function delete(User $user, Appointment $appointment): bool
    {
        return $user->role === 'admin' && $appointment->device?->device_type === 'Smartphone';
    }
}
