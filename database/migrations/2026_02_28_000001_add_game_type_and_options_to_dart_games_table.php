<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            if (!Schema::hasColumn('dart_games', 'game_type')) {
                $table->enum('game_type', ['standard', 'team', 'tournament'])
                    ->default('standard')
                    ->after('type')
                    ->comment('Play mode: standard (individual), team, or tournament');
            }

            if (!Schema::hasColumn('dart_games', 'options')) {
                $table->json('options')
                    ->nullable()
                    ->after('fields')
                    ->comment('Game-mode-specific options (JSON). X01: {checkout}, Cricket: {hitsPerField}, ATC: {includeBull}, Highscore: {rounds}');
            }

            $table->enum('type', ['X01', 'aroundTheClock', 'cricket', 'highscore'])->change();
        });
    }

    public function down(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            $table->enum('type', ['X01', 'aroundTheClock', 'cricket'])->change();
            $table->dropColumn(['game_type', 'options']);
        });
    }
};
