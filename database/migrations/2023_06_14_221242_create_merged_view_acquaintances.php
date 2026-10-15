<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

/**
 * Originally created the merged view 'acquaintances_view' via staudenmeir/laravel-merged-relations.
 * The view is no longer used and the package was removed, so this migration is a no-op now.
 * Existing databases drop the view in 2026_10_15_000001_drop_acquaintances_view.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        //
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        DB::statement('DROP VIEW IF EXISTS '.DB::getTablePrefix().'acquaintances_view');
    }
};
