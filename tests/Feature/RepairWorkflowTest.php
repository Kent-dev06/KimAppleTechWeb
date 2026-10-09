<?php

namespace Tests\Feature;

use App\Models\{Appointment,Customer,Device,RepairRecord,User};
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class RepairWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private function makeUser(string $role, string $email): array
    {
        $user=User::create(['name'=>ucfirst($role),'email'=>$email,'password'=>Hash::make('password'),'role'=>$role]);
        $customer=null; $device=null;
        if($role==='customer'){
            $customer=Customer::create(['user_id'=>$user->user_id,'first_name'=>'Casey','last_name'=>'Customer','contact_number'=>'09170000000','address'=>'Tagum City']);
            $device=Device::create(['customer_id'=>$customer->customer_id,'device_type'=>'Smartphone','brand'=>'Apple','model'=>'iPhone 15','serial_number'=>'TEST-IMEI-1']);
        }
        return [$user,$customer,$device];
    }

    private function appointment(Customer $customer, Device $device, string $status='Pending', string $concern='Screen repair'): Appointment
    {
        return Appointment::create(['customer_id'=>$customer->customer_id,'device_id'=>$device->device_id,'preferred_date'=>now()->addDay()->toDateString(),'preferred_start_time'=>'09:00','preferred_end_time'=>'10:00','concern'=>$concern,'status'=>$status]);
    }

    public function test_customer_registration_creates_linked_account_and_login_redirects(): void
    {
        $response=$this->post('/register',['first_name'=>'Taylor','last_name'=>'Customer','email'=>'taylor@example.test','contact_number'=>'09171112222','address'=>'Tagum','password'=>'password123','password_confirmation'=>'password123','data_privacy_consent'=>'1']);
        $response->assertRedirect('/repair')->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users',['email'=>'taylor@example.test','role'=>'customer']);
        $user=User::where('email','taylor@example.test')->firstOrFail();
        $this->assertSame('Taylor Customer',$user->name);
        $this->assertDatabaseHas('customers',['user_id'=>$user->user_id,'first_name'=>'Taylor']);
        $this->post('/logout');
        $this->post('/login',['email'=>'taylor@example.test','password'=>'password123'])->assertRedirect('/repair');
    }

    public function test_registration_requires_valid_philippine_mobile_and_privacy_consent(): void
    {
        $data=['first_name'=>'Taylor','last_name'=>'Customer','email'=>'taylor@example.test','contact_number'=>'12345','password'=>'password123','password_confirmation'=>'password123'];
        $this->from('/register')->post('/register',$data)->assertRedirect('/register')->assertSessionHasErrors([
            'contact_number'=>'Enter a valid Philippine mobile number starting with 09 (11 digits).',
            'data_privacy_consent'=>'You must agree to the data privacy notice to continue.',
        ]);
        $this->get('/privacy-notice')->assertOk()->assertSee('Data Privacy Notice');
    }

    public function test_customer_can_update_profile_and_only_see_their_own_records(): void
    {
        [$user,$customer,$device]=$this->makeUser('customer','casey@example.test');
        [$other,$otherCustomer,$otherDevice]=$this->makeUser('customer','other@example.test');
        $this->actingAs($user)->patch('/repair/profile',['name'=>'Forged Account Name','first_name'=>'Casey','last_name'=>'Updated','contact_number'=>'09179998888','address'=>'New address'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('users',['user_id'=>$user->user_id,'name'=>'Casey Updated']);
        $this->assertDatabaseHas('customers',['customer_id'=>$customer->customer_id,'address'=>'New address']);
        $this->get('/repair')->assertOk()->assertSee('Casey Updated')->assertDontSee('other@example.test');
        $this->patch('/repair/customers/'.$otherCustomer->customer_id,['name'=>'Tamper','first_name'=>'Other','last_name'=>'Customer'])->assertForbidden();
        $this->get('/repair/reports')->assertForbidden();
    }

    public function test_customer_device_is_saved_as_smartphone_and_request_starts_pending(): void
    {
        [$user,$customer]=$this->makeUser('customer','device@example.test');
        $this->actingAs($user)->post('/repair/devices',['brand'=>'Samsung','model'=>'Galaxy S25','serial_number'=>'IMEI-2'])->assertSessionHasNoErrors();
        $device=Device::where('customer_id',$customer->customer_id)->where('brand','Samsung')->firstOrFail();
        $this->assertSame('Smartphone',$device->device_type);
        $this->post('/repair/appointments',['device_id'=>$device->device_id,'preferred_date'=>now()->addDay()->toDateString(),'preferred_start_time'=>'10:00','preferred_end_time'=>'11:00','concern'=>'Charging port'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('appointments',['device_id'=>$device->device_id,'status'=>'Pending','concern'=>'Charging port']);
    }

    public function test_customer_dashboard_uses_tabs_and_only_shows_their_own_appointments(): void
    {
        [$user,$customer,$device]=$this->makeUser('customer','tabs@example.test');
        [, $otherCustomer,$otherDevice]=$this->makeUser('customer','tabs-other@example.test');
        $ownAppointment=$this->appointment($customer,$device);
        $this->appointment($otherCustomer,$otherDevice,'Pending','Private customer concern');

        $this->actingAs($user)->get('/repair')->assertOk()
            ->assertSee('My Smartphones')->assertSee('Repair History')->assertSee('Pending appointments')
            ->assertSee('Apple iPhone 15')->assertDontSee('Private customer concern');
    }

    public function test_customer_can_cancel_only_their_own_pending_appointment(): void
    {
        [$user,$customer,$device]=$this->makeUser('customer','cancel-owner@example.test');
        [, $otherCustomer,$otherDevice]=$this->makeUser('customer','cancel-other@example.test');
        $own=$this->appointment($customer,$device);
        $other=$this->appointment($otherCustomer,$otherDevice);
        $confirmed=$this->appointment($customer,$device,'Confirmed');

        $this->actingAs($user)->delete('/repair/appointments/'.$own->appointment_id)->assertRedirect();
        $this->assertDatabaseHas('appointments',['appointment_id'=>$own->appointment_id,'status'=>'Cancelled']);
        $this->from('/repair')->delete('/repair/appointments/'.$other->appointment_id)->assertRedirect('/repair')->assertSessionHas('error','You may only cancel your own appointments.');
        $this->from('/repair')->delete('/repair/appointments/'.$confirmed->appointment_id)->assertRedirect('/repair')->assertSessionHas('error','Only pending appointments can be cancelled.');
        $this->assertDatabaseHas('appointments',['appointment_id'=>$other->appointment_id,'status'=>'Pending']);
        $this->assertDatabaseHas('appointments',['appointment_id'=>$confirmed->appointment_id,'status'=>'Confirmed']);
    }

    public function test_appointment_date_and_end_time_are_validated(): void
    {
        [, $customer,$device]=$this->makeUser('customer','appointment-validation@example.test');
        $this->actingAs($customer->user)->from('/repair')->post('/repair/appointments',[
            'device_id'=>$device->device_id,
            'preferred_date'=>now()->subDay()->toDateString(),
            'preferred_start_time'=>'11:00',
            'preferred_end_time'=>'10:00',
            'concern'=>'Screen repair',
        ])->assertRedirect('/repair')->assertSessionHasErrors(['preferred_date','preferred_end_time']);
        $this->assertDatabaseCount('appointments',0);
    }

    public function test_staff_can_confirm_and_overlapping_confirmed_slot_is_rejected(): void
    {
        [$staff]= $this->makeUser('clerk','staff@example.test');
        [, $customer,$device]=$this->makeUser('customer','bookings@example.test');
        $first=$this->appointment($customer,$device);
        $second=$this->appointment($customer,$device,'Pending','Battery');
        $data=['status'=>'Confirmed','confirmed_date'=>now()->addDay()->toDateString(),'confirmed_start_time'=>'10:00','confirmed_end_time'=>'11:00'];
        $this->actingAs($staff)->patch('/repair/appointments/'.$first->appointment_id,$data)->assertSessionHasNoErrors();
        $this->patch('/repair/appointments/'.$second->appointment_id,array_merge($data,['confirmed_start_time'=>'10:30','confirmed_end_time'=>'11:30']))->assertSessionHasErrors('confirmed_start_time');
        $this->assertDatabaseHas('appointments',['appointment_id'=>$second->appointment_id,'status'=>'Pending']);
    }

    public function test_no_show_does_not_create_repair_and_staff_can_advance_repair_lifecycle(): void
    {
        [$staff]= $this->makeUser('clerk','repair-staff@example.test');
        [, $customer,$device]=$this->makeUser('customer','repair-customer@example.test');
        $noShow=$this->appointment($customer,$device);
        $this->actingAs($staff)->patch('/repair/appointments/'.$noShow->appointment_id,['status'=>'No-show'])->assertSessionHasNoErrors();
        $this->assertDatabaseMissing('repair_records',['appointment_id'=>$noShow->appointment_id]);
        $appointment=$this->appointment($customer,$device,'Confirmed','Screen repair');
        $this->post('/repair/repairs',['appointment_id'=>$appointment->appointment_id,'repair_status'=>'Pending','cost_estimate'=>250,'diagnosis'=>'Broken screen'])->assertSessionHasNoErrors();
        $repair=RepairRecord::where('appointment_id',$appointment->appointment_id)->firstOrFail();
        foreach(['Received','Diagnosing','In Repair','Ready for Pickup','Completed'] as $status){
            $updateResponse=$this->patch('/repair/repairs/'.$repair->repair_id,['repair_status'=>$status,'cost_estimate'=>250,'diagnosis'=>'Screen replacement','parts_used'=>'Display','technician_notes'=>'Tested']);
            $updateResponse->assertSessionHasNoErrors();
        }
        $this->assertDatabaseHas('repair_records',['repair_id'=>$repair->repair_id,'repair_status'=>'Completed']);
        $this->actingAs($customer->user)->get('/repair')->assertOk()->assertSee('Completed')->assertSee('Screen replacement');
    }

    public function test_staff_dashboard_search_and_date_reports_load(): void
    {
        [$staff]= $this->makeUser('clerk','report-staff@example.test');
        [, $customer,$device]=$this->makeUser('customer','report-customer@example.test');
        $this->appointment($customer,$device);
        $this->actingAs($staff)->get('/repair?search=Casey')->assertOk()->assertSee('Casey Customer')->assertSee('Pending requests')->assertSee('Customers');
        $this->get('/repair?status=Pending&from='.now()->addDay()->toDateString())->assertOk()->assertSee('Casey Customer');
        $this->get('/repair/reports?from='.now()->toDateString().'&to='.now()->addDays(3)->toDateString())->assertOk()->assertSee('Service reports');
    }

    public function test_staff_can_register_walkin_update_device_create_manual_booking_and_change_appointment_status(): void
    {
        [$staff]=$this->makeUser('clerk','walkin-staff@example.test');
        $this->actingAs($staff)->post('/repair/customers',['name'=>'Morgan Walkin','first_name'=>'Morgan','last_name'=>'Walkin','email'=>'morgan@example.test','contact_number'=>'09173334444','address'=>'Tagum City'])->assertSessionHasNoErrors();
        $customer=Customer::whereHas('user',fn($q)=>$q->where('email','morgan@example.test'))->firstOrFail();
        $this->post('/repair/devices',['customer_id'=>$customer->customer_id,'brand'=>'Apple','model'=>'iPhone 16','serial_number'=>'WALKIN-16'])->assertSessionHasNoErrors();
        $device=Device::where('customer_id',$customer->customer_id)->firstOrFail();
        $this->patch('/repair/devices/'.$device->device_id,['brand'=>'Apple','model'=>'iPhone 16 Pro','serial_number'=>'WALKIN-16'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('devices',['device_id'=>$device->device_id,'model'=>'iPhone 16 Pro']);
        $fields=['customer_id'=>$customer->customer_id,'device_id'=>$device->device_id,'preferred_date'=>now()->addDay()->toDateString(),'preferred_start_time'=>'13:00','preferred_end_time'=>'14:00','concern'=>'Back glass repair'];
        $this->post('/repair/appointments',$fields)->assertSessionHasNoErrors();
        $appointment=Appointment::where('customer_id',$customer->customer_id)->firstOrFail();
        $this->assertSame('Pending',$appointment->status);
        $date=now()->addDay()->toDateString();
        $this->patch('/repair/appointments/'.$appointment->appointment_id,['status'=>'Confirmed','confirmed_date'=>$date,'confirmed_start_time'=>'13:00','confirmed_end_time'=>'14:00'])->assertSessionHasNoErrors();
        $this->patch('/repair/appointments/'.$appointment->appointment_id,['status'=>'Rescheduled','confirmed_date'=>$date,'confirmed_start_time'=>'14:00','confirmed_end_time'=>'15:00'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('appointments',['appointment_id'=>$appointment->appointment_id,'status'=>'Rescheduled','confirmed_start_time'=>'14:00']);
        $this->actingAs($customer->user)->get('/repair')->assertOk()->assertSee('Rescheduled')->assertSee('14:00');
        $this->actingAs($staff);
        $this->patch('/repair/appointments/'.$appointment->appointment_id,['status'=>'Cancelled'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('appointments',['appointment_id'=>$appointment->appointment_id,'status'=>'Cancelled']);
        $this->patch('/repair/appointments/'.$appointment->appointment_id,['status'=>'Completed'])->assertSessionHasErrors(['status'=>'This appointment cannot be completed before its scheduled date.']);
        $appointment->update(['confirmed_date'=>now()->toDateString()]);
        $this->patch('/repair/appointments/'.$appointment->appointment_id,['status'=>'Completed'])->assertSessionHasNoErrors();
        $this->assertDatabaseHas('appointments',['appointment_id'=>$appointment->appointment_id,'status'=>'Completed']);
    }

    public function test_staff_dashboard_repair_status_filter_and_report_totals_reflect_records(): void
    {
        [$staff]=$this->makeUser('clerk','filter-staff@example.test');
        [, $customer,$device]=$this->makeUser('customer','filter-customer@example.test');
        $appointment=$this->appointment($customer,$device,'Confirmed');
        $this->appointment($customer,$device,'Pending','Charging inspection');
        $repair=RepairRecord::create(['appointment_id'=>$appointment->appointment_id,'device_id'=>$device->device_id,'diagnosis'=>'Battery service','repair_status'=>'Pending','cost_estimate'=>100]);
        $this->actingAs($staff)->get('/repair?repair_status=Pending')->assertOk()->assertSee('Battery service')->assertSee('<span>Customers</span><strong class="fs-3">1</strong>',false)->assertSee('<span>Pending requests</span><strong class="fs-3">1</strong>',false);
        $repair->update(['repair_status'=>'Completed','date_completed'=>now()->toDateString()]);
        $this->get('/repair/reports?from='.now()->toDateString().'&to='.now()->toDateString())->assertOk()->assertSeeInOrder(['Completed repairs','1 record','Battery service'])->assertSee('Confirmed schedule');
    }

    public function test_clerk_and_admin_dashboards_render_with_no_records(): void
    {
        foreach (['clerk', 'admin'] as $role) {
            [$user] = $this->makeUser($role, $role.'-empty@example.test');

            $this->assertTrue($user->is_active);
            $this->actingAs($user)->get('/repair')->assertOk()
                ->assertSee(ucfirst($role).' dashboard')
                ->assertSee('No smartphones registered.')
                ->assertSee('No appointments found.')
                ->assertSee('No repair records yet.')
                ->assertDontSee('@endif');
            $this->get('/repair/reports')->assertOk();
        }
    }

    public function test_staff_customer_edit_uses_form_fields_and_keeps_the_account_name_in_sync(): void
    {
        [, $customer] = $this->makeUser('customer', 'edit-customer@example.test');

        foreach (['clerk', 'admin'] as $role) {
            [$staff] = $this->makeUser($role, $role.'-edit@example.test');
            $fields = ['first_name' => 'Casey', 'last_name' => ucfirst($role), 'contact_number' => '09179998888', 'address' => 'Updated address'];

            $this->actingAs($staff)->from('/repair')->patch('/repair/customers/'.$customer->customer_id, $fields)
                ->assertRedirect('/repair')->assertSessionHasNoErrors()->assertSessionHas('success');
            $this->assertDatabaseHas('customers', ['customer_id' => $customer->customer_id] + $fields);
            $this->assertDatabaseHas('users', ['user_id' => $customer->user_id, 'name' => 'Casey '.ucfirst($role)]);

            $this->patch('/repair/customers/'.$customer->customer_id, $fields + ['name' => 'Forged name'])
                ->assertSessionHasNoErrors();
            $this->assertDatabaseHas('users', ['user_id' => $customer->user_id, 'name' => 'Casey '.ucfirst($role)]);
        }
    }

    public function test_staff_can_select_a_customer_outside_the_dashboard_search_results_when_registering_a_smartphone(): void
    {
        [$staff] = $this->makeUser('clerk', 'selector-staff@example.test');
        $this->makeUser('customer', 'visible-customer@example.test');
        [, $otherCustomer] = $this->makeUser('customer', 'hidden-customer@example.test');
        $otherCustomer->update(['first_name' => 'Zoe', 'last_name' => 'Unlisted']);

        $this->actingAs($staff)->get('/repair?search=Casey')->assertOk()
            ->assertSee('Zoe Unlisted · hidden-customer@example.test');
    }

    public function test_admin_accounts_and_analytics_render_with_repair_records(): void
    {
        [$admin] = $this->makeUser('admin', 'admin-pages@example.test');
        [, $customer, $device] = $this->makeUser('customer', 'analytics-customer@example.test');
        $appointment = $this->appointment($customer, $device, 'Completed');
        RepairRecord::create(['appointment_id' => $appointment->appointment_id, 'device_id' => $device->device_id, 'diagnosis' => 'Analytics screen repair', 'repair_status' => 'Completed', 'cost_estimate' => 500, 'date_completed' => now()->toDateString()]);

        $this->actingAs($admin)->get('/repair')->assertOk()->assertSee('Admin dashboard')->assertSee('Analytics screen repair');
        $this->get('/repair/admin')->assertOk()->assertSee('Accounts &amp; activity', false)->assertSee('admin-pages@example.test');
        $this->get('/repair/admin/reports')->assertOk()->assertSee('Appointment status breakdown')->assertSee('PHP 500.00')->assertDontSee('Analytics screen repair');
    }

    public function test_inactive_accounts_still_cannot_access_the_dashboard_or_log_in(): void
    {
        [$user] = $this->makeUser('clerk', 'inactive-clerk@example.test');
        $user->update(['is_active' => false]);

        $this->actingAs($user)->get('/repair')->assertRedirect('/login')
            ->assertSessionHasErrors(['email' => 'This account has been deactivated.']);
        $this->assertGuest();
        $this->post('/login', ['email' => $user->email, 'password' => 'password'])->assertSessionHasErrors('email');
        $this->assertGuest();
    }

    public function test_appointment_booking_enforces_shop_hours_duration_and_date(): void
    {
        [, $customer, $device] = $this->makeUser('customer', 'time-rules@example.test');
        $this->actingAs($customer->user);
        $base = ['device_id' => $device->device_id, 'preferred_date' => today()->toDateString(), 'concern' => 'Screen repair'];

        $this->from('/repair')->post('/repair/appointments', $base + ['preferred_start_time' => '07:30', 'preferred_end_time' => '08:00'])
            ->assertSessionHasErrors('preferred_start_time');
        $this->from('/repair')->post('/repair/appointments', $base + ['preferred_start_time' => '09:00', 'preferred_end_time' => '11:30'])
            ->assertSessionHasErrors('preferred_end_time');
        $this->from('/repair')->post('/repair/appointments', $base + ['preferred_start_time' => '09:00', 'preferred_end_time' => '09:00'])
            ->assertSessionHasErrors('preferred_end_time');
        $this->from('/repair')->post('/repair/appointments', array_merge($base, ['preferred_date' => now()->subDay()->toDateString(), 'preferred_start_time' => '09:00', 'preferred_end_time' => '10:00']))
            ->assertSessionHasErrors('preferred_date');
        $this->post('/repair/appointments', $base + ['preferred_start_time' => '08:00', 'preferred_end_time' => '08:30'])
            ->assertSessionHasNoErrors();
        $this->assertDatabaseCount('appointments', 1);
    }

    public function test_staff_cannot_complete_future_appointment_and_view_offers_one_repair_record(): void
    {
        [$staff] = $this->makeUser('clerk', 'future-guard@example.test');
        [, $customer, $device] = $this->makeUser('customer', 'future-customer@example.test');
        $appointment = $this->appointment($customer, $device, 'Confirmed');

        $this->actingAs($staff)->from('/repair')->patch('/repair/appointments/'.$appointment->appointment_id, ['status' => 'Completed'])
            ->assertSessionHasErrors(['status' => 'This appointment cannot be completed before its scheduled date.']);
        $this->assertDatabaseHas('appointments', ['appointment_id' => $appointment->appointment_id, 'status' => 'Confirmed']);
        $this->get('/repair')->assertOk()->assertSee('Create repair record')->assertSee('disabled', false);

        RepairRecord::create(['appointment_id' => $appointment->appointment_id, 'device_id' => $device->device_id, 'diagnosis' => 'Screen repair', 'repair_status' => 'Pending', 'cost_estimate' => 250]);
        $this->get('/repair')->assertOk()->assertDontSee('Create repair record');
    }

    public function test_customer_cannot_create_more_than_configured_pending_appointments(): void
    {
        [, $customer, $device] = $this->makeUser('customer', 'pending-limit@example.test');
        foreach (range(1, config('shop.max_active_pending_appointments')) as $index) {
            $this->appointment($customer, $device, 'Pending', 'Request '.$index);
        }

        $this->actingAs($customer->user)->from('/repair')->post('/repair/appointments', [
            'device_id' => $device->device_id,
            'preferred_date' => today()->addDays(2)->toDateString(),
            'preferred_start_time' => '13:00',
            'preferred_end_time' => '14:00',
            'concern' => 'Another request',
        ])->assertSessionHasErrors(['device_id' => 'You can have up to 3 active pending appointments at a time.']);
        $this->assertDatabaseCount('appointments', config('shop.max_active_pending_appointments'));
    }
}
