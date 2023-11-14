<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSlidersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up() {
        Schema::create('sliders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('parameters')->nullable();
            $table->text('settings')->nullable();
            $table->text('layers')->nullable();
            $table->enum('type', ['attachment', 'page', 'post', 'revision', 'portfolio', 'directory', 'publicity'])->nullable();
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->boolean('status')->nullable();
            $table->enum('project', ['aquicreamos', 'darwinyusef', 'english', 'talenti', 'cristianismodigital'])->nullable();
            
            $table->softDeletes();
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
        Schema::dropIfExists('sliders');
    }
}
