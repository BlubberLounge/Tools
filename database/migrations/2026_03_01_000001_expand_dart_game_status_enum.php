<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            $table->enum('status', ['unkown', 'created', 'started', 'running', 'done', 'aborted', 'error', 'initialised', 'paused', 'playerWon', 'finished'])
                ->default('unkown')
                ->change();
        });
    }

    public function down(): void
    {
        Schema::table('dart_games', function (Blueprint $table) {
            $table->enum('status', ['unkown', 'created', 'started', 'running', 'done', 'aborted', 'error'])
                ->default('unkown')
                ->change();
        });
    }
};
