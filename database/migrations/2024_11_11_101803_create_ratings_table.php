<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateRatingsTable extends Migration
{
    /**
     * Run the migrations.
     */
    public function up()
    {
        Schema::create('ratings', function (Blueprint $table)
        {
            $table->increments('id');

            $table->integer('rating');
            $table->text('comment')
                ->nullable();
            $table->morphs('rateable');
            $table->foreignId('user_id')
                ->constrained()
                ->onUpdate('cascade')
                ->onDelete('cascade');


            $table->timestamps();
            $table->softDeletes();

            $table->index(['rateable_id', 'rateable_type']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down()
    {
        Schema::drop('ratings');
    }
}
