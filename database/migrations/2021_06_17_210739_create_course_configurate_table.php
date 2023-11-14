<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCourseConfigurateTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('course_configurate', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->nullable();

            $table->integer('courses_id')->unsigned()->nullable();
            $table->foreign('courses_id')->references('id')->on('courses')->nullable();

            $table->integer('contents_id')->unsigned()->nullable();
            $table->foreign('contents_id')->references('id')->on('contents')->nullable();
            $table->boolean('state')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('course_configurate');
    }
}
