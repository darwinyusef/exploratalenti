<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateOptionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('options', function (Blueprint $table) {
            $table->increments('id');
            $table->string('option_key');
            $table->text('option_value');
            $table->text('settings')->nullable();
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();          
            $table->boolean('autoload')->nullable();
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
        Schema::dropIfExists('options');
    }
}
