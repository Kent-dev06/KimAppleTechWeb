<?php

namespace App\Http\Controllers;

use App\Models\{ActivityLog, Appointment, Customer, Device, RepairRecord, User};
use Illuminate\Http\Request;
use Illuminate\Support\Facades\{Auth, Gate, Hash};

class AdminRepairController extends Controller
{
    public function index()
    {
        Gate::authorize('manage-accounts');
        Gate::authorize('view-activity-log');

        $accounts = User::whereIn('role', ['clerk', 'admin'])->orderBy('role')->orderBy('name')->get();
        $activityLogs = ActivityLog::with('user')->latest('created_at')->limit(50)->get();

        return view('repair.admin', compact('accounts', 'activityLogs'));
    }

    public function storeAccount(Request $request)
    {
        Gate::authorize('manage-accounts');
        $data = $request->validate([
            'name' => ['required', 'string', 'max:100'],
            'email' => ['required', 'email', 'max:255', 'unique:users,email'],
            'role' => ['required', 'in:clerk,admin'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
        ]);

        $account = User::create(array_merge($data, ['password' => Hash::make($data['password']), 'is_active' => true]));
        $this->log('Account created: '.$account->role, $account);

        return back()->with('success', ucfirst($account->role).' account created.');
    }

    public function toggleAccount(User $user)
    {
        Gate::authorize('manage-accounts');
        abort_unless(in_array($user->role, ['clerk', 'admin'], true), 404);

        if ($user->role === 'admin' && $user->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'You cannot deactivate the last active admin account.');
        }

        $user->update(['is_active' => !$user->is_active]);
        $this->log($user->is_active ? 'Account activated' : 'Account deactivated', $user);

        return back()->with('success', 'Account status updated.');
    }

    public function reports(Request $request)
    {
        Gate::authorize('view-admin-reports');
        $request->validate(['from' => ['nullable', 'date'], 'to' => ['nullable', 'date', 'after_or_equal:from']]);

        $completedRepairs = RepairRecord::with(['device', 'appointment.customer'])
            ->where('repair_status', 'Completed')
            ->whereHas('device', fn ($query) => $query->where('device_type', 'Smartphone'))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('date_completed', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('date_completed', '<=', $request->input('to')))
            ->orderByDesc('date_completed')->get();

        $repairsQuery = RepairRecord::where('repair_status', 'Completed')
            ->whereHas('device', fn ($query) => $query->where('device_type', 'Smartphone'))
            ->when($request->filled('from'), fn ($query) => $query->whereDate('date_completed', '>=', $request->input('from')))
            ->when($request->filled('to'), fn ($query) => $query->whereDate('date_completed', '<=', $request->input('to')));

        $stats = [
            'customers' => Customer::whereHas('user', fn ($query) => $query->where('role', 'customer'))->count(),
            'completed_repairs' => $repairsQuery->count(),
            'revenue_estimate' => (float) (clone $repairsQuery)->sum('cost_estimate'),
        ];

        return view('repair.admin-reports', compact('completedRepairs', 'stats'));
    }

    public function destroyRecord(string $type, int $id)
    {
        Gate::authorize('hard-delete-records');
        $models = [
            'user' => User::class,
            'customer' => Customer::class,
            'device' => Device::class,
            'appointment' => Appointment::class,
            'repair' => RepairRecord::class,
        ];
        abort_unless(isset($models[$type]), 404);
        $record = $models[$type]::findOrFail($id);

        if ($record instanceof Customer || $record instanceof Device || $record instanceof Appointment || $record instanceof RepairRecord) {
            Gate::authorize('delete', $record);
        }

        if ($record instanceof User && $record->user_id === Auth::id()) {
            return back()->with('error', 'You cannot delete your own signed-in account.');
        }
        if ($record instanceof User && $record->role === 'admin' && $record->is_active && User::where('role', 'admin')->where('is_active', true)->count() <= 1) {
            return back()->with('error', 'You cannot delete the last active admin account.');
        }

        $modelLabel = class_basename($record).'#'.$record->getKey();
        $this->log('Record permanently deleted', $record);
        $record->delete();

        return back()->with('success', $modelLabel.' was permanently deleted.');
    }

    private function log(string $action, $model): void
    {
        ActivityLog::create([
            'user_id' => Auth::id(),
            'action' => $action,
            'model' => class_basename($model).'#'.$model->getKey(),
        ]);
    }
}
