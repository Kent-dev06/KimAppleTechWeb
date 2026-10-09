<?php
use Illuminate\Support\Facades\{Auth,Route};
use App\Http\Controllers\RepairManagementController as Repair;
use App\Http\Controllers\AdminRepairController as RepairAdmin;
use Illuminate\Support\Facades\DB;
use App\Http\Controllers\RepairNotificationController as RepairNotifications;

Route::get('/health', function () {
    DB::select('select 1');
    return response('ok');
});

Route::get('/',fn()=>redirect('/repair'));
Route::get('/login',fn()=>Auth::check()?redirect('/repair'):view('repair.login'))->name('login');
Route::post('/login',[Repair::class,'login']);
Route::get('/register',fn()=>view('repair.register'));
Route::view('/privacy-notice','repair.privacy-notice')->name('privacy-notice');
Route::post('/register',[Repair::class,'register']);
Route::post('/logout',[Repair::class,'logout'])->middleware('auth');
Route::middleware(['auth','active','no-auth-cache'])->prefix('repair')->group(function(){
 Route::get('/session-check',fn()=>response()->noContent())->name('repair.session-check');
 Route::get('/notifications', [RepairNotifications::class, 'index'])->name('repair.notifications.index');
 Route::get('/notifications/poll', [RepairNotifications::class, 'poll'])->name('repair.notifications.poll');
 Route::post('/notifications/read-all', [RepairNotifications::class, 'markAllRead'])->name('repair.notifications.read-all');
 Route::post('/notifications/{notification}/read', [RepairNotifications::class, 'markRead'])->name('repair.notifications.read');
 Route::get('/',[Repair::class,'dashboard'])->name('repair.home');
 Route::patch('/profile',[Repair::class,'profile']);
 Route::middleware('can:access-customer-tools')->group(function(){Route::post('/devices',[Repair::class,'device']);Route::post('/appointments',[Repair::class,'appointment']);});
 Route::delete('/appointments/{appointment}',[Repair::class,'cancelAppointment'])->name('repair.appointments.cancel');
 Route::middleware('can:manage-repairs')->group(function(){Route::get('/reports',[Repair::class,'reports']);Route::post('/customers',[Repair::class,'walkin']);Route::patch('/customers/{customer}',[Repair::class,'updateCustomer']);Route::patch('/devices/{device}',[Repair::class,'updateDevice']);Route::patch('/appointments/{appointment}',[Repair::class,'schedule']);Route::post('/repairs',[Repair::class,'repair']);Route::patch('/repairs/{repair}',[Repair::class,'updateRepair']);});
 Route::prefix('admin')->group(function(){
  Route::middleware('can:manage-accounts')->group(function(){Route::get('/',[RepairAdmin::class,'index'])->name('repair.admin');Route::post('/accounts',[RepairAdmin::class,'storeAccount']);Route::patch('/accounts/{user}/toggle',[RepairAdmin::class,'toggleAccount']);});
  Route::get('/reports/export',[RepairAdmin::class,'exportReports'])->middleware('can:view-admin-reports')->name('repair.admin.reports.export');
  Route::get('/reports',[RepairAdmin::class,'reports'])->middleware('can:view-admin-reports')->name('repair.admin.reports');
  Route::delete('/records/{type}/{id}',[RepairAdmin::class,'destroyRecord'])->whereIn('type',['user','customer','device','appointment','repair'])->middleware('can:hard-delete-records')->name('repair.admin.records.destroy');
 });
});
