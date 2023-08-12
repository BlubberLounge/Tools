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
        if(!Schema::hasTable('devices'))
            Schema::create('devices', function (Blueprint $table)
            {
                $table->id();

                $table->foreignId('user_id')
                    ->constrained('users')
                    ->onUpdate('cascade');
                $table->enum('device_type', ['unkown', 'mobile', 'tablet', 'desktop', 'bot'])
                    ->default('unkown')
                    ->index();
                $table->string('device_family')
                    ->nullable();
                $table->string('device_model')
                    ->nullable();
                $table->string('device_grade')
                    ->nullable();
                $table->string('browser')
                    ->nullable();
                $table->string('browser_family')
                    ->nullable();
                $table->string('browser_version')
                    ->nullable();
                $table->string('platform')
                    ->nullable();
                $table->string('platform_family')
                    ->nullable();
                $table->string('platform_version')
                    ->nullable();
                $table->ipAddress('ip')
                    ->index();
                $table->json('data')
                    ->nullable();
                $table->integer('login_count')
                    ->default(0);
                $table->timestamp('verified_at')
                    ->nullable();
                $table->timestamp('last_active')
                    ->nullable();

                $table->timestamps();
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('devices');
    }
};
