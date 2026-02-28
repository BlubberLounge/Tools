<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            $table->enum('game_type', ['standard', 'team', 'tournament'])
                ->default('standard')
                ->after('type')
                ->comment('Play mode: standard (individual), team, or tournament');

            $table->json('options')
                ->nullable()
                ->after('fields')
                ->comment('Game-mode-specific options (JSON). X01: {checkout}, Cricket: {hitsPerField}, ATC: {includeBull}, Highscore: {rounds}');
        });

        DB::statement("ALTER TABLE dart_games MODIFY COLUMN `type` ENUM('X01','aroundTheClock','cricket','highscore') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE dart_games MODIFY COLUMN `type` ENUM('X01','aroundTheClock','cricket') NOT NULL");

        Schema::table('dart_games', function (Blueprint $table) {
            $table->dropColumn(['game_type', 'options']);
        });
    }
};
