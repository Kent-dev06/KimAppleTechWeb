<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

Artisan::command('demo:reset {--force}', function () {
    if (! $this->option('force')) {
        $this->error('No data was changed. Run php artisan demo:reset --force to remove only the reserved demo accounts and their related records.');

        return 1;
    }

    $demoEmails = [
        'demo.admin@example.test',
        'demo.clerk@example.test',
        'alex.sample@example.test',
        'jamie.example@example.test',
    ];

    $deletedUsers = DB::transaction(function () use ($demoEmails): int {
        $userIds = User::whereIn('email', $demoEmails)->pluck('user_id');

        if ($userIds->isEmpty()) {
            return 0;
        }

        if (Schema::hasTable('notifications')) {
            DB::table('notifications')
                ->where('notifiable_type', User::class)
                ->whereIn('notifiable_id', $userIds)
                ->delete();
        }

        return User::whereIn('user_id', $userIds)->delete();
    });

    $this->info("Demo reset complete. Removed {$deletedUsers} reserved demo account(s) and their related records.");
})->purpose('Remove only reserved Kim Apple Tech demo accounts and their related records');
