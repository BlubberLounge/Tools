<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            $table->enum('type', ['X01', 'aroundTheClock', 'cricket', 'highscore', 'elimination'])->change();
        });

        Schema::table('dart_tournaments', function (Blueprint $table) {
            $table->enum('game_mode', ['X01', 'aroundTheClock', 'cricket', 'highscore', 'elimination'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            $table->enum('type', ['X01', 'aroundTheClock', 'cricket', 'highscore'])->change();
        });

        Schema::table('dart_tournaments', function (Blueprint $table) {
            $table->enum('game_mode', ['X01', 'aroundTheClock', 'cricket', 'highscore'])->change();
        });
    }
};
