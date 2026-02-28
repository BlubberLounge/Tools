<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dart_game_user', function (Blueprint $table) {
            $table->foreignUuid('dart_team_id')
                ->nullable()
                ->after('user_id')
                ->constrained('dart_teams')
                ->onUpdate('cascade')
                ->onDelete('set null');
        });
    }

    public function down(): void
    {
        Schema::table('dart_game_user', function (Blueprint $table) {
            $table->dropForeign(['dart_team_id']);
            $table->dropColumn('dart_team_id');
        });
    }
};
