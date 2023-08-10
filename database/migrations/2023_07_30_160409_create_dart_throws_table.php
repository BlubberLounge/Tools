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
        if(!Schema::hasTable('dart_throws'))
            Schema::create('dart_throws', function (Blueprint $table)
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

                $table->tinyInteger('set')
                    ->default(1);
                $table->tinyInteger('leg')
                    ->default(1);
                $table->tinyInteger('turn');
                $table->tinyInteger('throw');
                $table->tinyInteger('value')
                    ->unsigned();
                $table->tinyInteger('field')
                    ->unsigned()
                    ->comment('0 to 20 and 25 aka bull');
                $table->enum('ring', ['O', 'S', 'D', 'T'])
                    ->comment('aka. multiplier; O = Out 0x, S = Single 1x, D = Double 2x, T = Tripple 3x');
                $table->decimal('x', 5, 4)
                    ->comment('relative normalised representation of a Throws X coordinate to the Board origin (0,0). value range: 0 to 1');
                $table->decimal('y', 5, 4)
                    ->comment('relative normalised representation of a Throws Y coordinate to the Board origin (0,0). value range: 0 to 1');
                // $table->foreignId('dart_game_user_id')
                //     ->constrained('dart_game_user') // remember: pivot table
                //     ->onUpdate('cascade')
                //     ->onDelete('cascade');

                $table->timestamps();
                $table->softDeletes();

                // composite unique key
                // $table->unique(['dart_game_id', 'user_id', 'set', 'leg', 'turn', 'throw']);

                // composite index keys
                $table->index(['dart_game_id', 'user_id']);
                $table->index(['dart_game_id', 'user_id', 'set', 'leg', 'turn', 'throw']);
                $table->index(['set', 'leg', 'turn', 'throw']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dart_throws');
    }
};
