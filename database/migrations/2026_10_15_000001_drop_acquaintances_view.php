<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        DB::statement('DROP VIEW IF EXISTS '.DB::getTablePrefix().'acquaintances_view');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // The view is unused; nothing to restore.
    }
};
