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
        if(!Schema::hasTable('acquaintances'))
            Schema::create('acquaintances', function (Blueprint $table)
            {
                $table->id();

                $table->foreignId('transmitter_user_id')
                    ->constrained('users')
                    ->comment('the user that initialised the friend request')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->foreignId('receiver_user_id')
                    ->constrained('users')
                    ->comment('the user that received the friend request')
                    ->onUpdate('cascade')
                    ->onDelete('cascade');
                $table->enum('status', ['pending', 'accepted','denied','blocked'])
                    ->default('pending')
                    ->comment('pending/accepted/denied/blocked');

                $table->timestamps();

                // composite unique key
                $table->unique(['transmitter_user_id', 'receiver_user_id']);
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('acquaintances');
    }
};
