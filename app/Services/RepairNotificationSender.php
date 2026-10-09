<?php

namespace App\Services;

use App\Events\RepairNotificationCreated;
use App\Models\User;
use App\Notifications\RepairInAppNotification;
use Illuminate\Support\Facades\Log;
use Throwable;

class RepairNotificationSender
{
    public function send(User $recipient, array $payload): void
    {
        $notification = new RepairInAppNotification($payload);

        try {
            $recipient->notify($notification);
        } catch (Throwable $exception) {
            Log::warning('Could not save a repair in-app notification.', [
                'user_id' => $recipient->getKey(),
                'exception' => $exception,
            ]);

            return;
        }

        try {
            event(new RepairNotificationCreated(
                (string) $recipient->getKey(),
                ['notification_id' => $notification->id] + $payload,
            ));
        } catch (Throwable $exception) {
            Log::warning('Could not broadcast a repair in-app notification.', [
                'user_id' => $recipient->getKey(),
                'notification_id' => $notification->id,
                'exception' => $exception,
            ]);
        }
    }
}
