<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMakeusersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('makeusers', function (Blueprint $table) {
            $table->increments('id');
            $table->string('archive');
            $table->enum('reasponse', ['msn','move','error']);
            $table->enum('type', ['changeState','assigned','contact','user','timeService','createComment','create','edit','delete']);
            $table->boolean('active');
            $table->unsignedInteger('user_id');
            $table->enum('project', ['aquicreamos', 'darwinyusef', 'english', 'talenti', 'cristianismodigital'])->nullable();
            
            $table->foreign('user_id')->references('id')->on('users');
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
        Schema::dropIfExists('makeusers');
    }
}
