<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInteractionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('interactions', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug', 100)->nullable();
            $table->string('interaction')->nullable();
            $table->text('response')->nullable();
            $table->text('content')->nullable();
            $table->integer('value')->nullable();
            $table->integer('force')->nullable();
            $table->integer('rating')->nullable();
            $table->string('type')->nullable(); // expone el tipo de interacción ej(Notas, pregunta, calificaciones, respuesta, ver|archivo, ver|video, ver|leer, ver|comunicar, asistio|reunion, descargar, leer, contenido, file, quizz, evaluation)
            $table->string('rol')->nullable();
            $table->string('notification', 100)->nullable();
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
        Schema::dropIfExists('interactions');
    }
}
