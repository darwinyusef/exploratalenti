<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEducationalTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('educational', function (Blueprint $table) {
            $table->increments('id');
            $table->string('exam_type')->nullable();
            $table->string('slug')->nullable();
            $table->string('icon')->nullable();
            $table->text('synonymous')->nullable();
            $table->text('content')->nullable();
            $table->string('meaning')->nullable();
            $table->string('range')->nullable();
            $table->string('method')->nullable();
            $table->text('json')->nullable();
            $table->enum('project', ['aquicreamos', 'darwinyusef', 'english', 'talenti', 'cristianismodigital'])->nullable();
            
            $table->integer('user_id')->unsigned()->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
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
        Schema::dropIfExists('educational');
    }
}
