<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dart_tournament_matches', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('dart_tournament_id')
                ->constrained('dart_tournaments')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignUuid('dart_tournament_round_id')
                ->constrained('dart_tournament_rounds')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignUuid('dart_game_id')
                ->nullable()
                ->constrained('dart_games')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->tinyInteger('match_number')->comment('Position within round');

            $table->foreignId('player1_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreignId('player2_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->foreignId('winner_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->enum('status', ['pending', 'ready', 'running', 'done', 'bye'])
                ->default('pending');

            $table->integer('bracket_position')
                ->nullable()
                ->comment('For single elimination bracket rendering');

            $table->timestamps();

            $table->index(['dart_tournament_id', 'dart_tournament_round_id'], 'tournament_match_round_index');
            $table->index('dart_game_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dart_tournament_matches');
    }
};
