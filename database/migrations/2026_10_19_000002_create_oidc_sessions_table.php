<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Maps local sessions created by "Sign in with BlubberLounge" to the Auth
 * server's session id (sid), for back-channel logout.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasTable('oidc_sessions'))
            return;

        Schema::create('oidc_sessions', function (Blueprint $table)
        {
            $table->id();
            $table->string('session_id')->index();
            $table->string('sid')->nullable()->index();
            $table->foreignId('user_id')->constrained('users')->cascadeOnDelete();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('oidc_sessions');
    }
};
