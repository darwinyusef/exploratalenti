<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePostsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('posts', function (Blueprint $table) {
            $table->increments('id');
            $table->string('title');
            $table->text('content');
            $table->string('slug')->nullable();
            $table->text('excerpt')->nullable();
            $table->string('password')->nullable();
            $table->string('url')->nullable();
            $table->enum('type', ['attachment', 'page', 'post', 'revision', 'portfolio', 'directory', 'publicity', 'course','homework','reading','leader'])->nullable();
            $table->enum('state', ['published','draft','pending review', 'public', 'rol'])->nullable();
            $table->dateTime('time_in')->nullable();
            $table->dateTime('time_out')->nullable();
            $table->integer('parent')->nullable();
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
        Schema::dropIfExists('posts');
    }
}
