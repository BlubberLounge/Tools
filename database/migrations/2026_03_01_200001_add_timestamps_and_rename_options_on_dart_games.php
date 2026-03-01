<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            $table->timestamp('started_at')->nullable()->after('status');
            $table->timestamp('finished_at')->nullable()->after('started_at');

            $table->renameColumn('options', 'settings');
        });
    }

    public function down(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            $table->renameColumn('settings', 'options');

            $table->dropColumn(['started_at', 'finished_at']);
        });
    }
};
