<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateFileableTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('fileables', function (Blueprint $table) {
            $table->increments('id');
            $table->integer('files_id')->unsigned()->nullable();
            $table->integer('fileable_id');
            $table->string('fileable_type');
            $table->enum('project', ['aquicreamos', 'darwinyusef', 'english', 'talenti', 'cristianismodigital'])->nullable();
            
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
        Schema::dropIfExists('fileable');
    }
}
