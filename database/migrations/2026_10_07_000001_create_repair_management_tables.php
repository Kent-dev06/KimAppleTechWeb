<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    public function up(): void
    {
        Schema::create('customers', function (Blueprint $table) {
            $table->id('customer_id');
            $table->foreignId('user_id')->unique()->constrained('users', 'user_id')->cascadeOnDelete();
            $table->string('first_name', 50); $table->string('last_name', 50);
            $table->string('contact_number', 20)->nullable(); $table->string('address')->nullable();
            $table->timestamps();
        });
        Schema::create('devices', function (Blueprint $table) {
            $table->id('device_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->cascadeOnDelete();
            $table->string('device_type', 50); $table->string('brand', 50); $table->string('model', 50);
            $table->string('serial_number', 50)->nullable(); $table->timestamps();
        });
        Schema::create('appointments', function (Blueprint $table) {
            $table->id('appointment_id');
            $table->foreignId('customer_id')->constrained('customers', 'customer_id')->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('devices', 'device_id')->cascadeOnDelete();
            $table->date('preferred_date'); $table->time('preferred_start_time'); $table->time('preferred_end_time');
            $table->date('confirmed_date')->nullable(); $table->time('confirmed_start_time')->nullable(); $table->time('confirmed_end_time')->nullable();
            $table->string('concern', 255); $table->enum('status', ['Pending','Confirmed','Rescheduled','Cancelled','Completed','No-show'])->default('Pending');
            $table->timestamps();
        });
        Schema::create('repair_records', function (Blueprint $table) {
            $table->id('repair_id');
            $table->foreignId('appointment_id')->unique()->constrained('appointments', 'appointment_id')->cascadeOnDelete();
            $table->foreignId('device_id')->constrained('devices', 'device_id')->cascadeOnDelete();
            $table->text('diagnosis')->nullable(); $table->text('parts_used')->nullable(); $table->text('technician_notes')->nullable();
            $table->enum('repair_status', ['Pending','Received','Diagnosing','In Repair','Ready for Pickup','Completed'])->default('Pending');
            $table->decimal('cost_estimate', 10, 2)->default(0); $table->date('date_completed')->nullable(); $table->timestamps();
        });
    }
    public function down(): void { Schema::dropIfExists('repair_records'); Schema::dropIfExists('appointments'); Schema::dropIfExists('devices'); Schema::dropIfExists('customers'); }
};
