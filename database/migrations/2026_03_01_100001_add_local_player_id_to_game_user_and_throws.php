<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dart_game_user', function (Blueprint $table) {
            $table->dropUnique(['dart_game_id', 'user_id']);

            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->string('local_player_id')->nullable()->after('user_id');

            $table->unique(['dart_game_id', 'user_id'], 'dgu_game_user_unique');
            $table->unique(['dart_game_id', 'local_player_id'], 'dgu_game_local_player_unique');
            $table->index('local_player_id', 'dgu_local_player_index');
        });

        Schema::table('dart_throws', function (Blueprint $table) {
            $table->unsignedBigInteger('user_id')->nullable()->change();

            $table->string('local_player_id')->nullable()->after('user_id');

            $table->index('local_player_id', 'dt_local_player_index');
        });
    }

    public function down(): void
    {
        Schema::table('dart_throws', function (Blueprint $table) {
            $table->dropIndex('dt_local_player_index');
            $table->dropColumn('local_player_id');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
        });

        Schema::table('dart_game_user', function (Blueprint $table) {
            $table->dropIndex('dgu_local_player_index');
            $table->dropUnique('dgu_game_local_player_unique');
            $table->dropUnique('dgu_game_user_unique');
            $table->dropColumn('local_player_id');
            $table->unsignedBigInteger('user_id')->nullable(false)->change();
            $table->unique(['dart_game_id', 'user_id']);
        });
    }
};
