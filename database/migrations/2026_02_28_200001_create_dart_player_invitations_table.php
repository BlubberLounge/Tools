<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('dart_player_invitations', function (Blueprint $table) {
            $table->id();
            $table->string('auth_invitation_token')
                ->comment('UUID token from the Auth server invitation');
            $table->foreignId('invited_by_user_id')
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('cascade')
                ->comment('The Tools user who initiated the invitation');
            $table->string('email');
            $table->string('firstname')->nullable();
            $table->string('lastname')->nullable();
            $table->string('local_player_id')->nullable()
                ->comment('IndexedDB player ID from the PWA, for client-side remapping');
            $table->string('status')->default('pending')
                ->comment('pending, sent, registered, failed');
            $table->foreignId('registered_user_id')->nullable()
                ->constrained('users')
                ->onUpdate('cascade')
                ->onDelete('set null')
                ->comment('Tools user_id once they complete OAuth login');
            $table->timestamp('expires_at')->nullable();
            $table->timestamps();

            $table->index('auth_invitation_token');
            $table->index('email');
            $table->index(['email', 'status']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('dart_player_invitations');
    }
};
