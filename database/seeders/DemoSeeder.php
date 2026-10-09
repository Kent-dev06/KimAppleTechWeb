<?php

namespace Database\Seeders;

use App\Models\Appointment;
use App\Models\Customer;
use App\Models\Device;
use App\Models\RepairRecord;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class DemoSeeder extends Seeder
{
    public function run(): void
    {
        $this->user('Demo Admin', 'demo.admin@example.test', 'admin');
        $this->user('Demo Clerk', 'demo.clerk@example.test', 'clerk');
        $customerUser = $this->user('Alex Sample', 'alex.sample@example.test', 'customer');
        $secondCustomerUser = $this->user('Jamie Example', 'jamie.example@example.test', 'customer');

        $customer = Customer::updateOrCreate(['user_id' => $customerUser->user_id], [
            'first_name' => 'Alex',
            'last_name' => 'Sample',
            'contact_number' => '09170000001',
            'address' => 'Demo Address, Davao City',
        ]);
        $secondCustomer = Customer::updateOrCreate(['user_id' => $secondCustomerUser->user_id], [
            'first_name' => 'Jamie',
            'last_name' => 'Example',
            'contact_number' => '09170000002',
            'address' => 'Demo Address, Tagum City',
        ]);

        $device = Device::updateOrCreate(['customer_id' => $customer->customer_id, 'serial_number' => 'DEMO-KAT-001'], [
            'device_type' => 'Smartphone',
            'brand' => 'Apple',
            'model' => 'iPhone 15',
        ]);
        $secondDevice = Device::updateOrCreate(['customer_id' => $secondCustomer->customer_id, 'serial_number' => 'DEMO-KAT-002'], [
            'device_type' => 'Smartphone',
            'brand' => 'Samsung',
            'model' => 'Galaxy A55',
        ]);

        $completedAppointment = Appointment::updateOrCreate([
            'customer_id' => $customer->customer_id,
            'device_id' => $device->device_id,
            'concern' => 'Battery replacement demonstration',
        ], [
            'preferred_date' => today()->toDateString(),
            'preferred_start_time' => '09:00',
            'preferred_end_time' => '10:00',
            'confirmed_date' => today()->toDateString(),
            'confirmed_start_time' => '09:00',
            'confirmed_end_time' => '10:00',
            'status' => 'Completed',
        ]);
        RepairRecord::updateOrCreate(['appointment_id' => $completedAppointment->appointment_id], [
            'device_id' => $device->device_id,
            'diagnosis' => 'Battery health below service threshold',
            'parts_used' => 'Replacement battery',
            'technician_notes' => 'Function tested after replacement',
            'repair_status' => 'Completed',
            'cost_estimate' => 2500,
            'date_completed' => today()->toDateString(),
        ]);

        Appointment::updateOrCreate([
            'customer_id' => $secondCustomer->customer_id,
            'device_id' => $secondDevice->device_id,
            'concern' => 'Charging port inspection',
        ], [
            'preferred_date' => today()->addDay()->toDateString(),
            'preferred_start_time' => '11:00',
            'preferred_end_time' => '12:00',
            'status' => 'Pending',
        ]);
    }

    private function user(string $name, string $email, string $role): User
    {
        $password = config('demo.passwords.'.$role);

        if (! is_string($password) || strlen($password) < 8) {
            throw new RuntimeException('Set DEMO_'.strtoupper($role).'_PASSWORD in the environment to seed demo accounts.');
        }

        return User::updateOrCreate(['email' => $email], [
            'name' => $name,
            'password' => Hash::make($password),
            'role' => $role,
            'is_active' => true,
        ]);
    }
}
