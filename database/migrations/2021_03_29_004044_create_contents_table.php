<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateContentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('contents', function (Blueprint $table) {
            $table->increments('id');
            $table->string('slug')->nullable();
            
            $table->text('content')->nullable();
            $table->text('description')->nullable();
            $table->string('excerpt',100)->nullable();
            $table->string('password',100)->nullable();
            $table->string('payload')->nullable();  /// genera la carga que se tomará como resultante del contenido este se registrara según la interacción y según el tipo de contenido
           
            $table->string('value')->nullable(); // a verificar
            
            $table->string('view',100)->nullable();
            $table->string('rating')->nullable();
           
           
            $table->integer('order')->nullable();
            
            $table->text('urlInbox')->nullable();
            $table->text('iframeInbox')->nullable();
            
            $table->dateTime('timeIn')->nullable();
            $table->dateTime('timeOut')->nullable();
            
            $table->mediumText('type')->nullable(); // Indica el tipo bajo parametro de contenido ej(investigación, pregunta, video|Multimedia, guia, introducción, archivo|recurso, contenido, tarea)
            
            $table->string('assing')->nullable();
            
            // Presencial 
            $table->string('rol')->nullable();
            $table->string('classroom')->nullable();
            $table->string('classroomText')->nullable();
            $table->string('address')->nullable();

            $table->integer('timeLine')->nullable();
            
            // Tipo de activaciones de la interacción
            $table->string('send')->nullable();

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
        Schema::dropIfExists('contents');
    }
}
