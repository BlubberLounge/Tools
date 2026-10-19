<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Stable OpenID Connect subject (sub) of the user at BlubberLounge Auth.
 * Not the same as external_id, which is the Auth server's public user id.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        if (Schema::hasColumn('users', 'auth_sub'))
            return;

        Schema::table('users', function (Blueprint $table)
        {
            $table->string('auth_sub')->nullable()->unique();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->dropUnique(['auth_sub']);
            $table->dropColumn('auth_sub');
        });
    }
};
