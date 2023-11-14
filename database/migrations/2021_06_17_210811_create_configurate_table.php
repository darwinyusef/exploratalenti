<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateConfigurateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('configurates', function (Blueprint $table) {
            $table->increments('id');
            $table->text('payload')->nullable();
            $table->string('keys')->nullable();
            $table->string('requiredActivities')->nullable(); // obtiene la validación del registro vs la cantidad de actividades a desarrollar para pasar
            $table->string('userActivity')->nullable();
            $table->string('tutorActivity')->nullable();
            $table->integer('score')->nullable();

            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullable();

            $table->integer('courses_id')->unsigned()->nullable();
            $table->foreign('courses_id')->references('id')->on('courses')->nullable();

            $table->integer('contents_id')->unsigned()->nullable();
            $table->foreign('contents_id')->references('id')->on('contents')->nullable();

            $table->integer('options_id')->unsigned()->nullable();
            $table->foreign('options_id')->references('id')->on('options')->nullable();

            $table->integer('posts_id')->unsigned()->nullable();
            $table->foreign('posts_id')->references('id')->on('posts')->nullable();

            $table->integer('tutor_id')->unsigned()->nullable();
            $table->foreign('tutor_id')->references('id')->on('users');
            
            $table->integer('interactions_id')->unsigned()->nullable();
            $table->foreign('interactions_id')->references('id')->on('interactions')->nullable();

            $table->integer('comunications_id')->unsigned()->nullable();
            $table->foreign('comunications_id')->references('id')->on('comunications');
            $table->boolean('status')->nullable();
            $table->timestamps();
            $table->softDeletes();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('configurates');
    }
}
