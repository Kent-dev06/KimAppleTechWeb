<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\{DB,Schema};

return new class extends Migration {
    public function up(): void
    {
        $isPostgres = DB::connection()->getDriverName() === 'pgsql';

        if ($isPostgres) {
            DB::statement('ALTER TABLE "users" DROP CONSTRAINT IF EXISTS "users_role_check"');
        }

        // Normalize legacy role labels before applying the approved two-role domain.
        DB::table('users')->whereIn('role',['Customer','customer'])->update(['role'=>'customer']);
        DB::table('users')->whereIn('role',['Admin','Clerk','admin','clerk','Staff'])->update(['role'=>'staff']);
        DB::table('users')->whereNotIn('role',['customer','staff'])->update(['role'=>'customer']);

        if (Schema::hasColumn('users','id') && !Schema::hasColumn('users','user_id')) {
            Schema::table('users',fn(Blueprint $table)=>$table->renameColumn('id','user_id'));
        }
        Schema::table('users',function(Blueprint $table) use ($isPostgres) {
            if ($isPostgres) {
                $table->string('role')->default('customer')->change();
            } else {
                $table->enum('role',['customer','staff'])->default('customer')->change();
            }
        });

        if ($isPostgres) {
            DB::statement('ALTER TABLE "users" ADD CONSTRAINT "users_role_check" CHECK ("role" IN (\'customer\', \'staff\'))');
        }
    }

    public function down(): void
    {
        if (DB::connection()->getDriverName() === 'pgsql') {
            DB::statement('ALTER TABLE "users" DROP CONSTRAINT IF EXISTS "users_role_check"');
        }

        Schema::table('users',function(Blueprint $table){$table->string('role')->default('Customer')->change();});
        DB::table('users')->where('role','customer')->update(['role'=>'Customer']);
        DB::table('users')->where('role','staff')->update(['role'=>'Admin']);
        if (Schema::hasColumn('users','user_id') && !Schema::hasColumn('users','id')) {
            Schema::table('users',fn(Blueprint $table)=>$table->renameColumn('user_id','id'));
        }
    }
};
