<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dart_teams', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('dart_game_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('name');
            $table->string('color', 7)->comment('Hex color code, e.g. #FF0000');

            $table->tinyInteger('position')->nullable()->comment('Team play order position');
            $table->tinyInteger('place')->nullable()->comment('Final placement in team game');

            $table->timestamps();
            $table->softDeletes();

            $table->index('dart_game_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dart_teams');
    }
};
