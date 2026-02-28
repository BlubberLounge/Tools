<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dart_tournament_rounds', function (Blueprint $table) {
            $table->uuid('id')->primary();

            $table->foreignUuid('dart_tournament_id')
                ->constrained('dart_tournaments')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->tinyInteger('round_number')->comment('1-based round order');
            $table->string('name')->nullable()->comment('e.g. Quarter-Finals, Round 1');

            $table->enum('status', ['pending', 'running', 'done'])
                ->default('pending');

            $table->timestamps();

            $table->index(['dart_tournament_id', 'round_number']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dart_tournament_rounds');
    }
};
