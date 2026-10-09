<?php

namespace App\Observers;

use App\Models\Appointment;
use App\Models\User;
use App\Services\RepairNotificationSender;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class AppointmentObserver
{
    public function created(Appointment $appointment): void
    {
        try {
            $actor = Auth::user();

            if (! $actor || $actor->role !== 'customer') {
                return;
            }

            $customer = $appointment->customer;
            $customerName = trim(($customer?->first_name ?? '').' '.($customer?->last_name ?? ''));

            $this->notifyStaff([
                'message' => 'New appointment request'.($customerName !== '' ? " from {$customerName}." : '.'),
                'id' => $appointment->getKey(),
                'status' => $appointment->status,
                'link' => '/repair',
                'kind' => 'appointment',
            ], $actor->getKey());
        } catch (Throwable $exception) {
            Log::warning('Could not notify staff about a new appointment.', [
                'appointment_id' => $appointment->getKey(),
                'exception' => $exception,
            ]);
        }
    }

    public function updated(Appointment $appointment): void
    {
        if (! $appointment->wasChanged('status')) {
            return;
        }

        try {
            $actor = Auth::user();

            if (! $actor) {
                return;
            }

            $previousStatus = $appointment->getOriginal('status');

            if ($actor->role === 'customer') {
                if ($previousStatus === 'Pending' && $appointment->status === 'Cancelled') {
                    $this->notifyStaff([
                        'message' => 'A customer cancelled a pending appointment.',
                        'id' => $appointment->getKey(),
                        'status' => $appointment->status,
                        'link' => '/repair',
                        'kind' => 'appointment',
                    ], $actor->getKey());
                }

                return;
            }

            if (! in_array($actor->role, ['clerk', 'admin'], true)
                || ! in_array($appointment->status, ['Confirmed', 'Rescheduled', 'Cancelled', 'Completed', 'No-show'], true)) {
                return;
            }

            $customerUser = $appointment->customer?->user;

            if (! $customerUser || (string) $customerUser->getKey() === (string) $actor->getKey()) {
                return;
            }

            app(RepairNotificationSender::class)->send($customerUser, [
                'message' => 'Your appointment status is now '.$appointment->status.'.',
                'id' => $appointment->getKey(),
                'status' => $appointment->status,
                'link' => '/repair',
                'kind' => 'appointment',
            ]);
        } catch (Throwable $exception) {
            Log::warning('Could not notify a customer about an appointment update.', [
                'appointment_id' => $appointment->getKey(),
                'exception' => $exception,
            ]);
        }
    }

    private function notifyStaff(array $payload, mixed $actorId): void
    {
        $staff = User::query()
            ->whereIn('role', ['clerk', 'admin'])
            ->where('is_active', true)
            ->where('user_id', '!=', $actorId)
            ->get();

        foreach ($staff as $recipient) {
            app(RepairNotificationSender::class)->send($recipient, $payload);
        }
    }
}
