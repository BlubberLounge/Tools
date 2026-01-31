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
        Schema::table('feedback', function (Blueprint $table) {
            // Severity for bug reports (low, medium, high)
            $table->enum('severity', ['low', 'medium', 'high'])
                ->nullable()
                ->after('type');

            // Steps to reproduce for bug reports
            $table->text('steps_to_reproduce')
                ->nullable()
                ->after('message');

            // Device info as JSON (userAgent, platform, screenSize, appVersion, etc.)
            $table->json('device_info')
                ->nullable()
                ->after('area');

            // Make device_id nullable (DartApp may not have registered device)
            $table->foreignId('device_id')
                ->nullable()
                ->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('feedback', function (Blueprint $table) {
            $table->dropColumn(['severity', 'steps_to_reproduce', 'device_info']);
        });
    }
};
