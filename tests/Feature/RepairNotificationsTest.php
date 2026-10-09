<?php

namespace Tests\Feature;

use App\Events\RepairNotificationCreated;
use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Device;
use App\Models\RepairRecord;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Broadcast;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RepairNotificationsTest extends TestCase
{
    use RefreshDatabase;

    private function user(string $role, string $email, bool $active = true): User
    {
        return User::create([
            'name' => ucfirst($role),
            'email' => $email,
            'password' => Hash::make('password'),
            'role' => $role,
            'is_active' => $active,
        ]);
    }

    private function customerWithDevice(string $email): array
    {
        $user = $this->user('customer', $email);
        $customer = Customer::create([
            'user_id' => $user->getKey(),
            'first_name' => 'Test',
            'last_name' => 'Customer',
            'contact_number' => '09171234567',
            'address' => 'Davao City',
        ]);
        $device = Device::create([
            'customer_id' => $customer->getKey(),
            'device_type' => 'Smartphone',
            'brand' => 'Apple',
            'model' => 'iPhone 16',
        ]);

        return [$user, $customer, $device];
    }

    private function appointment(Customer $customer, Device $device, string $status = 'Pending'): Appointment
    {
        return Appointment::create([
            'customer_id' => $customer->getKey(),
            'device_id' => $device->getKey(),
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_start_time' => '09:00',
            'preferred_end_time' => '10:00',
            'concern' => 'Screen repair',
            'status' => $status,
        ]);
    }

    public function test_customer_appointment_submission_notifies_active_staff_and_broadcasts_privately(): void
    {
        [$customer] = $this->customerWithDevice('appointment-owner@example.test');
        $clerk = $this->user('clerk', 'notifications-clerk@example.test');
        $admin = $this->user('admin', 'notifications-admin@example.test');
        $inactiveClerk = $this->user('clerk', 'inactive-clerk@example.test', false);
        Event::fake([RepairNotificationCreated::class]);

        $this->actingAs($customer)->post('/repair/appointments', [
            'device_id' => $customer->customer->devices()->first()->getKey(),
            'preferred_date' => now()->addDay()->toDateString(),
            'preferred_start_time' => '11:00',
            'preferred_end_time' => '12:00',
            'concern' => 'Battery replacement',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $clerk->notifications()->count());
        $this->assertSame(1, $admin->notifications()->count());
        $this->assertSame(0, $inactiveClerk->notifications()->count());
        $this->assertSame(0, $customer->notifications()->count());
        $this->assertSame('Pending', $clerk->notifications()->first()->data['status']);
        Event::assertDispatched(RepairNotificationCreated::class, fn ($event) => $event->userId === (string) $clerk->getKey());
    }

    public function test_customer_cancelling_a_pending_appointment_notifies_staff_only(): void
    {
        [$customer, $profile, $device] = $this->customerWithDevice('canceller@example.test');
        $clerk = $this->user('clerk', 'cancel-clerk@example.test');
        $otherCustomer = $this->user('customer', 'other-customer@example.test');
        $appointment = $this->appointment($profile, $device);

        $this->actingAs($customer)->delete('/repair/appointments/'.$appointment->getKey())
            ->assertSessionHasNoErrors();

        $this->assertSame(1, $clerk->notifications()->count());
        $this->assertSame(0, $customer->notifications()->count());
        $this->assertSame(0, $otherCustomer->notifications()->count());
        $this->assertSame('Cancelled', $clerk->notifications()->first()->data['status']);
    }

    public function test_staff_appointment_update_notifies_only_its_customer_and_customer_can_poll_and_read(): void
    {
        $clerk = $this->user('clerk', 'status-clerk@example.test');
        [$customer, $profile, $device] = $this->customerWithDevice('status-owner@example.test');
        [$otherCustomer] = $this->customerWithDevice('status-other@example.test');
        $appointment = $this->appointment($profile, $device);
        $otherNotification = $otherCustomer->notifications()->create([
            'id' => (string) \Illuminate\Support\Str::uuid(),
            'type' => 'repair-update',
            'data' => ['message' => 'Private to another customer.'],
        ]);

        $this->actingAs($clerk)->patch('/repair/appointments/'.$appointment->getKey(), [
            'status' => 'Confirmed',
            'confirmed_date' => now()->addDay()->toDateString(),
            'confirmed_start_time' => '11:00',
            'confirmed_end_time' => '12:00',
        ])->assertSessionHasNoErrors();

        $this->assertSame(1, $customer->notifications()->count());
        $this->assertSame(1, $otherCustomer->notifications()->count());
        $notification = $customer->notifications()->firstOrFail();
        $this->actingAs($customer)->getJson('/repair/notifications/poll')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.status', 'Confirmed');
        $this->postJson('/repair/notifications/'.$notification->getKey().'/read')->assertOk()->assertJsonPath('unread_count', 0);
        $this->postJson('/repair/notifications/'.$otherNotification->getKey().'/read')->assertNotFound();
    }

    public function test_repair_status_change_notifies_its_customer_and_broadcast_failure_does_not_break_action(): void
    {
        $clerk = $this->user('clerk', 'repair-clerk@example.test');
        [$customer, $profile, $device] = $this->customerWithDevice('repair-owner@example.test');
        $appointment = $this->appointment($profile, $device, 'Confirmed');
        $repair = RepairRecord::create([
            'appointment_id' => $appointment->getKey(),
            'device_id' => $device->getKey(),
            'diagnosis' => 'Screen damaged',
            'repair_status' => 'Pending',
            'cost_estimate' => 100,
        ]);
        DB::table('notifications')->delete();

        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb' => [
                'driver' => 'reverb',
                'key' => 'test-key',
                'secret' => 'test-secret',
                'app_id' => '123456',
                'options' => ['host' => '127.0.0.1', 'port' => 1, 'scheme' => 'http', 'useTLS' => false],
                'client_options' => ['connect_timeout' => 0.2, 'timeout' => 0.2],
            ],
        ]);

        $this->actingAs($clerk)->patch('/repair/repairs/'.$repair->getKey(), [
            'diagnosis' => 'Screen damaged',
            'parts_used' => 'Display assembly',
            'technician_notes' => 'Replacement underway',
            'repair_status' => 'In Repair',
            'cost_estimate' => 100,
        ])->assertSessionHasNoErrors();

        $this->assertSame('In Repair', $repair->fresh()->repair_status);
        $this->assertSame(1, $customer->notifications()->count());
        $this->actingAs($customer)->getJson('/repair/notifications/poll')
            ->assertOk()
            ->assertJsonPath('unread_count', 1)
            ->assertJsonPath('items.0.status', 'In Repair');
    }

    public function test_private_channel_auth_uses_user_id_and_denies_other_users(): void
    {
        $first = $this->user('customer', 'channel-owner@example.test');
        $second = $this->user('customer', 'channel-other@example.test');
        config([
            'broadcasting.default' => 'reverb',
            'broadcasting.connections.reverb.key' => 'test-key',
            'broadcasting.connections.reverb.secret' => 'test-secret',
            'broadcasting.connections.reverb.app_id' => '123456',
        ]);
        require base_path('routes/channels.php');

        $this->actingAs($first)->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-App.Models.User.'.$first->getKey(),
        ])->assertOk();

        $this->actingAs($first)->postJson('/broadcasting/auth', [
            'socket_id' => '123.456',
            'channel_name' => 'private-App.Models.User.'.$second->getKey(),
        ])->assertForbidden();
    }
}
