<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if(!Schema::hasTable('dart_game_user'))
            Schema::create('dart_game_user', function (Blueprint $table)
            {
                $table->id();

                $table->foreignUuid('dart_game_id')
                    ->constrained()
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->foreignId('user_id')
                    ->constrained()
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->enum('status', ['pending', 'accepted', 'denied'])
                    ->comment('pending / accepted / denied')
                    ->default('pending');
                $table->tinyInteger('position')
                    ->comment('user position')
                    ->nullable()
                    ->default(null);
                $table->tinyInteger('place')
                    ->comment('IF a user has won the number place is here.')
                    ->nullable()
                    ->default(null);

                $table->timestamps();
                $table->softDeletes();

                // composite unique key
                $table->unique(['dart_game_id', 'user_id']);
                $table->unique(['dart_game_id', 'position']);
                $table->unique(['dart_game_id', 'place']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dart_game_user');
    }
};
