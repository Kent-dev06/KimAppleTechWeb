<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Customer;
use App\Models\Device;
use App\Models\Appointment;
use App\Models\RepairRecord;
use Illuminate\Support\Facades\Hash;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        User::updateOrCreate(['email' => 'staff@kimapple.tech'], ['name' => 'Kim Apple Tech Clerk', 'password' => Hash::make('password'), 'role' => 'clerk', 'is_active' => true]);
        User::updateOrCreate(['email' => 'admin@kimapple.tech'], ['name' => 'Kim Apple Tech Admin', 'password' => Hash::make('password'), 'role' => 'admin', 'is_active' => true]);
        $user = User::updateOrCreate(['email' => 'customer@example.com'], ['name' => 'Alex Customer', 'password' => Hash::make('password'), 'role' => 'customer']);
        $customer = Customer::updateOrCreate(['user_id' => $user->user_id], ['first_name' => 'Alex', 'last_name' => 'Customer', 'contact_number' => '09171234567', 'address' => 'Manila']);
        $device = Device::updateOrCreate(['customer_id' => $customer->customer_id, 'serial_number' => 'KAT-DEMO-001'], ['device_type' => 'Smartphone', 'brand' => 'Apple', 'model' => 'iPhone 15']);
        $appointment = Appointment::updateOrCreate(['customer_id' => $customer->customer_id, 'device_id' => $device->device_id, 'concern' => 'Battery replacement'], ['preferred_date' => now()->addDay()->toDateString(), 'preferred_start_time' => '09:00', 'preferred_end_time' => '10:00', 'confirmed_date' => now()->addDay()->toDateString(), 'confirmed_start_time' => '09:00', 'confirmed_end_time' => '10:00', 'status' => 'Completed']);
        RepairRecord::updateOrCreate(['appointment_id' => $appointment->appointment_id], ['device_id' => $device->device_id, 'diagnosis' => 'Battery health below service threshold', 'parts_used' => 'Replacement battery', 'technician_notes' => 'Function tested after replacement', 'repair_status' => 'Completed', 'cost_estimate' => 2500, 'date_completed' => now()->toDateString()]);

        $user2 = User::updateOrCreate(['email' => 'customer2@example.com'], ['name' => 'Jamie Sample', 'password' => Hash::make('password'), 'role' => 'customer']);
        $customer2 = Customer::updateOrCreate(['user_id' => $user2->user_id], ['first_name' => 'Jamie', 'last_name' => 'Sample', 'contact_number' => '09181234567', 'address' => 'Tagum City']);
        $device2 = Device::updateOrCreate(['customer_id' => $customer2->customer_id, 'serial_number' => 'KAT-DEMO-002'], ['device_type' => 'Smartphone', 'brand' => 'Samsung', 'model' => 'Galaxy A55']);
        Appointment::updateOrCreate(['customer_id' => $customer2->customer_id, 'device_id' => $device2->device_id, 'concern' => 'Charging port inspection'], ['preferred_date' => now()->addDays(2)->toDateString(), 'preferred_start_time' => '11:00', 'preferred_end_time' => '12:00', 'status' => 'Pending']);
    }
}
