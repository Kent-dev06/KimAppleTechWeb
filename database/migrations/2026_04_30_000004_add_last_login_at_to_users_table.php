<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // No extra user columns are needed by the repair system.
    }

    public function down(): void
    {
        // No-op for the repair system schema.
    }
};
