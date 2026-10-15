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
        Schema::create('cocktails', function (Blueprint $table)
        {
            $table->id();

            $table->string('name');
            $table->string('slug');
            $table->string('description')
                ->nullable();
            $table->string('image')
                ->nullable();
            $table->boolean('isPublic')
                ->default(true);
            $table->enum('status', ['unkown', 'created', 'waiting_for_review', 'review', 'reviewed', 'released', 'parties_only'])
                ->default('unkown');
            $table->foreignId('created_by')
                ->constrained('users')
                ->onUpdate('cascade');

            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cocktails');
    }
};
