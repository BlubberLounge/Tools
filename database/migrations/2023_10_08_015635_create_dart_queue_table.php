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
        Schema::create('dart_queue', function (Blueprint $table)
        {
            $table->id();

            $table->foreignId('parent_user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->foreignId('child_user_id')
                ->constrained('users')
                ->nullable()
                ->default(null)
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->timestamps();

            // composite unique key
            $table->unique('parent_user_id');
            $table->unique('child_user_id');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dart_queue');
    }
};
