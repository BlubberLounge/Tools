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
        if(!Schema::hasTable('hookah_heads')) {
            Schema::create('hookah_heads', function (Blueprint $table)
            {
                $table->id();
                $table->string('name');
                $table->string('description')
                    ->nullable();
                $table->enum('type', ['Multi-hole', 'Funnel (Single-hole)', 'Power Bowl', 'Vortex', 'Hot Shot RT']);
                $table->enum('material', ['clay', 'stone', 'glass', 'metal']);
                $table->decimal('volume', 7, 2)
                    ->comment('Hookah Head / Sisha Kopf -- volume in cubic mm');
                $table->timestamps();
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
        Schema::dropIfExists('hookah_heads');
    }
};
