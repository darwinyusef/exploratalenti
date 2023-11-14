<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCoursesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('courses', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug',100)->nullable();
            $table->string('excerpt',100)->nullable();
            $table->string('course',100)->nullable();
            $table->string('payload')->nullable();
            $table->text('description')->nullable();
            $table->enum('context', ['masterClass','course','tutorial','review','audit','webinar','seminar', 'conference', 'webcast','meeting','reading','mooc', 'spoc','poadcast','video', 'smallTalk'])->nullable();
            $table->enum('state', ['published','draft','pending review', 'public', 'rol'])->nullable();
            $table->string('classroom')->nullable();
            $table->integer('views')->nullable();
            $table->integer('rating')->nullable();
            $table->string('level',100)->nullable();
            $table->string('descriptionTask',100)->nullable();
            $table->string('amountTask',100)->nullable();
            $table->dateTime('timeOut')->nullable();
            $table->integer('calification')->nullable();
            $table->integer('subject')->nullable();
            $table->string('notification',100)->nullable();
            $table->integer('send')->nullable(); // cantidad de entregas debe realizar el estudiante 
            $table->text('meta')->nullable();
            $table->text('json')->nullable();
            $table->text('html')->nullable();
            $table->boolean('status')->nullable();
            $table->integer('parent')->nullable();
            $table->string('language')->nullable();
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
        Schema::dropIfExists('courses');
    }
}
