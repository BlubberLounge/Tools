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
        Schema::create('ingredients', function (Blueprint $table)
        {
            $table->id();

            $table->string('name');
            $table->integer('quantity');
            $table->enum('unit_of_measurement', ['part', 'dl', 'cl', 'ml', 'oz', 'dash', 'splash', 'drop', 'piece', 'slice', 'wedge', 'twist', 'tsp', 'Tbsp', 'bar spoon', 'cup', 'pinch', 'jigger', 'shot', 'finger', 'g', 'scoop']);
            $table->foreignId('recipe_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ingredients');
    }
};
