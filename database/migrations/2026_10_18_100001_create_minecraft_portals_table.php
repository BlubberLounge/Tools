<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('minecraft_portals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade');
            $table->string('name', 64);
            $table->string('world', 64)->nullable();
            $table->enum('dimension', ['overworld', 'nether']);
            $table->integer('x');
            $table->integer('y');
            $table->integer('z');
            $table->timestamps();

            $table->index(['user_id', 'world']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('minecraft_portals');
    }
};
