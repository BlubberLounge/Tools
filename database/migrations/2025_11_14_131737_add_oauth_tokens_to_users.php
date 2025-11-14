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
        Schema::table('users', function (Blueprint $table)
        {
            $table->string('external_id')
                ->nullable()
                ->after('remember_token');
            $table->string('access_token')
                ->nullable()
                ->after('external_id');
            $table->string('refresh_token')
                ->nullable()
                ->after('access_token');
            $table->dateTime('token_expires_at')
                ->nullable()
                ->after('refresh_token');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('users', function (Blueprint $table)
        {
            $table->dropColumn('external_id');
            $table->dropColumn('access_token');
            $table->dropColumn('refresh_token');
            $table->dropColumn('token_expires_at');
        });
    }
};
