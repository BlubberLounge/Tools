<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Drop in FK-safe order: children first
        Schema::dropIfExists('ingredients');
        Schema::dropIfExists('recipes');
        Schema::dropIfExists('ratings');
        Schema::dropIfExists('cocktails');
    }

    public function down(): void
    {
        // No rollback — feature removed permanently
    }
};
