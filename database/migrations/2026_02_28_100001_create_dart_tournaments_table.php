<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dart_tournaments', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignId('created_by')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->string('title');

            $table->enum('format', ['single_elimination', 'round_robin']);

            $table->enum('game_mode', ['X01', 'aroundTheClock', 'cricket', 'highscore'])
                ->comment('Game mode used for all matches in this tournament');

            $table->json('game_options')
                ->nullable()
                ->comment('Options passed to each match (points, checkout, hitsPerField, etc.)');

            $table->enum('seed_type', ['manual', 'random'])
                ->default('manual');

            $table->tinyInteger('best_of')
                ->default(1)
                ->comment('Best-of-N legs per match');

            $table->enum('status', ['created', 'seeded', 'running', 'done', 'aborted'])
                ->default('created');

            $table->foreignId('winner_id')
                ->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null');

            $table->timestamps();
            $table->softDeletes();

            $table->index('status');
            $table->index('created_by');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dart_tournaments');
    }
};
