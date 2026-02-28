<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement("ALTER TABLE dart_games MODIFY COLUMN `type` ENUM('X01','aroundTheClock','cricket','highscore','elimination') NOT NULL");
        DB::statement("ALTER TABLE dart_tournaments MODIFY COLUMN `game_mode` ENUM('X01','aroundTheClock','cricket','highscore','elimination') NOT NULL");
    }

    public function down(): void
    {
        DB::statement("ALTER TABLE dart_games MODIFY COLUMN `type` ENUM('X01','aroundTheClock','cricket','highscore') NOT NULL");
        DB::statement("ALTER TABLE dart_tournaments MODIFY COLUMN `game_mode` ENUM('X01','aroundTheClock','cricket','highscore') NOT NULL");
    }
};
