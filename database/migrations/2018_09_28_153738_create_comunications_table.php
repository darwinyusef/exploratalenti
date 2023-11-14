<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateComunicationsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
      Schema::create('comunications', function (Blueprint $table) {
          $table->increments('id');
          $table->string('title')->nullable();
          $table->string('description')->nullable();
          $table->datetime('expiration')->nullable();
          $table->string('url')->nullable();
          $table->string('target')->default('_self')->nullable();
          $table->string('icon_class')->nullable();
          $table->string('color')->nullable();
          $table->integer('progress')->nullable();
          $table->string('rol')->nullable();
          $table->enum('location', ['none', 'notification', 'tutorial', 'activity', 'task'])->nullable();
          $table->unsignedInteger('user_id');
          $table->foreign('user_id')->references('id')->on('users');
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
      Schema::drop('comunications');
    }
}
