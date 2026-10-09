<?php

namespace App\Providers;

use App\Models\User;
use App\Models\Appointment;
use App\Models\RepairRecord;
use App\Observers\AppointmentObserver;
use App\Observers\RepairRecordObserver;
use Illuminate\Support\Facades\Gate;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Appointment::observe(AppointmentObserver::class);
        RepairRecord::observe(RepairRecordObserver::class);

        Paginator::useBootstrapFive();
        Gate::define('manage-repairs', fn (User $user) => in_array($user->role, ['clerk', 'admin'], true));
        Gate::define('access-customer-tools', fn (User $user) => in_array($user->role, ['customer', 'clerk', 'admin'], true));
        Gate::define('update-own-profile', fn (User $user) => $user->role === 'customer');
        Gate::define('manage-accounts', fn (User $user) => $user->role === 'admin');
        Gate::define('view-admin-reports', fn (User $user) => $user->role === 'admin');
        Gate::define('view-activity-log', fn (User $user) => $user->role === 'admin');
        Gate::define('hard-delete-records', fn (User $user) => $user->role === 'admin');
    }
}
