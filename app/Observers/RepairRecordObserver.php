<?php

namespace App\Observers;

use App\Models\RepairRecord;
use App\Services\RepairNotificationSender;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Throwable;

class RepairRecordObserver
{
    public function created(RepairRecord $repairRecord): void
    {
        $this->notifyCustomer($repairRecord);
    }

    public function updated(RepairRecord $repairRecord): void
    {
        if ($repairRecord->wasChanged('repair_status')) {
            $this->notifyCustomer($repairRecord);
        }
    }

    private function notifyCustomer(RepairRecord $repairRecord): void
    {
        try {
            $customerUser = $repairRecord->device?->customer?->user;
            $actorId = Auth::id();

            if (! $customerUser || (string) $customerUser->getKey() === (string) $actorId) {
                return;
            }

            app(RepairNotificationSender::class)->send($customerUser, [
                'message' => 'Your repair status is now '.$repairRecord->repair_status.'.',
                'id' => $repairRecord->getKey(),
                'status' => $repairRecord->repair_status,
                'link' => '/repair',
                'kind' => 'repair',
            ]);
        } catch (Throwable $exception) {
            Log::warning('Could not notify a customer about a repair update.', [
                'repair_id' => $repairRecord->getKey(),
                'exception' => $exception,
            ]);
        }
    }
}
