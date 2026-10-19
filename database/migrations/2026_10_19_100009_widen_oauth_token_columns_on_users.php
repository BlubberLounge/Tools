<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * The BlubberLounge access and refresh tokens are stored encrypted (User casts);
 * the encrypted values are far longer than 255 characters.
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->text('access_token')->nullable()->change();
            $table->text('refresh_token')->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->string('access_token')->nullable()->change();
            $table->string('refresh_token')->nullable()->change();
        });
    }
};
