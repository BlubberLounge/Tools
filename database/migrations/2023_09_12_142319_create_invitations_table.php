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
        if(!Schema::hasTable('invitations'))
            Schema::create('invitations', function (Blueprint $table)
            {
                $table->id();

                $table->uuid('token');
                $table->enum('status', ['new', 'unkown', 'approved', 'denied'])
                    ->default('new');
                $table->string('firstname')
                    ->comment('users temporary firstname');
                $table->string('lastname')
                    ->comment('users temporary lastname/sirname');
                $table->string('email')
                    ->comment('users temporary email');
                $table->timestamp('expires_at')
                    ->comment('Timestamp when this access token expires')
                    ->nullable();

                $table->timestamps();
                $table->softDeletes();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('invitations');
    }
};
