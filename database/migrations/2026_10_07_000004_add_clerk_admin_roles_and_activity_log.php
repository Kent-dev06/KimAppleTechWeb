<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->change();
            $table->boolean('is_active')->default(true);
        });

        DB::table('users')->where('role', 'staff')->update(['role' => 'clerk']);

        Schema::table('users', function (Blueprint $table) {
            $table->enum('role', ['customer', 'clerk', 'admin'])->default('customer')->change();
        });

        Schema::create('activity_logs', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')->nullable()->constrained('users', 'user_id')->nullOnDelete();
            $table->string('action', 100);
            $table->string('model', 150)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->index('created_at');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('activity_logs');

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->change();
        });

        DB::table('users')->whereIn('role', ['clerk', 'admin'])->update(['role' => 'staff']);

        Schema::table('users', function (Blueprint $table) {
            $table->dropColumn('is_active');
            $table->enum('role', ['customer', 'staff'])->default('customer')->change();
        });
    }
};
