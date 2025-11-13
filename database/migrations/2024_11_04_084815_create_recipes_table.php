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
        Schema::create('recipes', function (Blueprint $table)
        {
            $table->id();

            $table->foreignId('cocktail_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->integer('order')
                ->defult(0);
            $table->string('description');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();

            // composite unique key
            $table->unique(['cocktail_id', 'order']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('recipes');
    }
};
