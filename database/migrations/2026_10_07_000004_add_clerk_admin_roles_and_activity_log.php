<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB, Schema};

return new class extends Migration {
    public function up(): void
    {
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        if ($isPostgres) {
            DB::statement('ALTER TABLE "users" DROP CONSTRAINT IF EXISTS "users_role_check"');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->change();
            $table->boolean('is_active')->default(true);
        });

        DB::table('users')->where('role', 'staff')->update(['role' => 'clerk']);

        if ($isPostgres) {
            DB::statement('ALTER TABLE "users" ADD CONSTRAINT "users_role_check" CHECK ("role" IN (\'customer\', \'clerk\', \'admin\'))');
        } else {
            Schema::table('users', function (Blueprint $table) {
                $table->enum('role', ['customer', 'clerk', 'admin'])->default('customer')->change();
            });
        }

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
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        Schema::dropIfExists('activity_logs');

        if ($isPostgres) {
            DB::statement('ALTER TABLE "users" DROP CONSTRAINT IF EXISTS "users_role_check"');
        }

        Schema::table('users', function (Blueprint $table) {
            $table->string('role')->default('customer')->change();
        });

        DB::table('users')->whereIn('role', ['clerk', 'admin'])->update(['role' => 'staff']);

        Schema::table('users', function (Blueprint $table) use ($isPostgres) {
            $table->dropColumn('is_active');

            if (!$isPostgres) {
                $table->enum('role', ['customer', 'staff'])->default('customer')->change();
            }
        });

        if ($isPostgres) {
            DB::statement('ALTER TABLE "users" ADD CONSTRAINT "users_role_check" CHECK ("role" IN (\'customer\', \'staff\'))');
        }
    }
};
