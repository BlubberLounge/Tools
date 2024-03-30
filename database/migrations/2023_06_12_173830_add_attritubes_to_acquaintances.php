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
        if(Schema::hasTable('acquaintances'))
            Schema::table('acquaintances', function (Blueprint $table)
            {
                $table->boolean('showOnHomeView')
                    ->default(true)
                    ->comment('Wheter the user should be displayed on the homescreen in the timetable')
                    ->after('status');
            });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if(Schema::hasTable('acquaintances'))
            Schema::table('acquaintances', function (Blueprint $table) {
                $table->dropColumn('showOnHomeView');
            });
    }
};
