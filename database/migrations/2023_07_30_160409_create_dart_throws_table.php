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
                $enumField = range(0, 20, 1);
                $enumField[] = 25;


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
                $table->enum('field', $enumField)
                    ->comment('0 to 20 and 25 aka bull');
                $table->enum('ring', ['S', 'D', 'T'])
                    ->comment('aka. multiplier; S = Single 1x, D = Double 2x, T = Tripple 3x');
                $table->decimal('x', 5, 4)
                    ->comment('relative normalised representation of a Throws X coordinate to the Board origin (0,0). value range: 0 to 1');
                $table->decimal('y', 5, 4)
                    ->comment('relative normalised representation of a Throws Y coordinate to the Board origin (0,0). value range: 0 to 1');
                $table->enum('data_input_type', ['NUMPAD', 'DARTBOARD', 'API'])
                    ->comment('NumPad = number field in the frontend. With this input method no positions can be saved; Dartboard = Graphical Dartboard to click a exact postion; Api = external data input')
                    ->default('NUMPAD');
                // $table->foreignId('dart_game_user_id')
                //     ->constrained('dart_game_user') // remember: pivot table
                //     ->onUpdate('cascade')
                //     ->onDelete('cascade');

                $table->timestamps();
                $table->softDeletes();

                // composite unique key
                $table->unique(['dart_game_id', 'user_id', 'set', 'leg', 'turn', 'throw']);

                // composite index keys
                $table->index(['dart_game_id', 'user_id', 'data_input_type']);
                $table->index(['dart_game_id', 'user_id', 'set', 'leg', 'turn', 'throw']);
                $table->index(['set', 'leg', 'turn', 'throw']);
                $table->index(['set', 'leg', 'turn', 'throw', 'data_input_type']);
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
