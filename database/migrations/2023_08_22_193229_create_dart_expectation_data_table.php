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
        if(!Schema::hasTable('dart_expectation_data'))
            Schema::create('dart_expectation_data', function (Blueprint $table)
            {
                $table->id();

                $table->decimal('sigma', 7, 3)
                    ->comment('standard deviation / sigma');
                $table->decimal('score', 7, 3)
                    ->comment('maximum expected score.');
                $table->decimal('x', 5, 4)
                    ->comment('relative normalised representation of a Throws X coordinate to the Board origin (0,0). value range: 0 to 1')
                    ->nullable();
                $table->decimal('y', 5, 4)
                    ->comment('relative normalised representation of a Throws Y coordinate to the Board origin (0,0). value range: 0 to 1')
                    ->nullable();
                $table->tinyInteger('version')
                    ->comment('mark data version');

                $table->timestamps();
                $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dart_expectation_data');
    }
};
