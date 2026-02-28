<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dart_tournament_participants', function (Blueprint $table) {
            $table->id();

            $table->foreignUuid('dart_tournament_id')
                ->constrained('dart_tournaments')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->tinyInteger('seed_position')->nullable()->comment('Position after seeding');
            $table->tinyInteger('final_placement')->nullable()->comment('Final rank (1=winner)');
            $table->boolean('eliminated')->default(false)->comment('For single elimination');

            $table->integer('wins')->default(0);
            $table->integer('losses')->default(0);
            $table->integer('points_for')->default(0)->comment('Aggregate points scored');
            $table->integer('points_against')->default(0)->comment('Aggregate points conceded');

            $table->timestamps();

            $table->unique(['dart_tournament_id', 'user_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dart_tournament_participants');
    }
};
