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
        if(!Schema::hasTable('dart_x01_games'))
            Schema::create('dart_games', function (Blueprint $table)
            {
                $table->uuid('id')
                    ->primary();

                $table->enum('type', ['XO1', 'aroundTheClock','cricket'])
                    ->default('XO1');
                $table->enum('status', ['unkown', 'created', 'started', 'running', 'done', 'aborted'])
                    ->default('unkown');
                $table->boolean('private')
                    ->default(false);
                $table->string('title');
                $table->text('comment')
                    ->nullable();

                $table->integer('points')
                    ->nullable()
                    ->comment('used in XO1 games');

                $table->integer('start')
                    ->nullable()
                    ->comment('used in aroundTheClock games');
                $table->integer('end')
                    ->nullable()
                    ->comment('used in aroundTheClock games');

                $table->json('fields')
                    ->nullable()
                    ->comment('used in cricket games');

                $table->boolean('singleOut')
                    ->default(true)
                    ->comment('used in XO1 games');
                $table->boolean('doubleOut')
                    ->default(true)
                    ->comment('used in XO1 games');
                $table->boolean('trippleOut')
                    ->default(true)
                    ->comment('used in XO1 games');
                $table->boolean('singleIn')
                    ->default(true)
                    ->comment('used in XO1 games');
                $table->boolean('doubleIn')
                    ->default(true)
                    ->comment('used in XO1 games');
                $table->boolean('trippleIn')
                    ->default(true)
                    ->comment('used in XO1 games');

                $table->timestamps();
                $table->softDeletes();

                $table->index('status');
                $table->index('title');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dart_games');
    }
};
