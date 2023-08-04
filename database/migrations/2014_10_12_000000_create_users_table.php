<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        if (!Schema::hasTable('users')) {
            Schema::create('users', function (Blueprint $table)
            {
                $table->id();
                $table->string('name')
                    ->comment('unique username of the user')
                    ->unique();
                $table->string('firstname')
                    ->comment('users firstname')
                    ->nullable();
                $table->string('lastname')
                    ->comment('users lastname/sirname')
                    ->nullable();
                $table->string('email')
                    ->unique()
                    ->nullable();
                $table->timestamp('dob')
                    ->comment('date of birth')
                    ->nullable();
                $table->string('img')
                    ->comment('users profile picture')
                    ->nullable();
                $table->timestamp('email_verified_at')->nullable();
                $table->string('password');
                $table->rememberToken();
                $table->timestamp('locked')
                    ->comment('User Account is locked may be temporarly banned / disabled')
                    ->nullable();
                $table->timestamps();
                $table->softDeletes();
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('users');
    }
};
