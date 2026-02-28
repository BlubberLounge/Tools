<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dart_throws', function (Blueprint $table) {
            $table->decimal('r', 5, 4)->nullable()->after('y')
                ->comment('Polar radius, normalized 0-1');
            $table->decimal('theta', 7, 4)->nullable()->after('r')
                ->comment('Polar angle in radians');
        });
    }

    public function down(): void
    {
        Schema::table('dart_throws', function (Blueprint $table) {
            $table->dropColumn(['r', 'theta']);
        });
    }
};
