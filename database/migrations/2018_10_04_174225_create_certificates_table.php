<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCertificatesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('certificates', function (Blueprint $table) {
                $table->increments('id');
                $table->uuid('uuid');
                $table->string('cedula')->nullable();
                $table->string('firstname')->nullable();
                $table->string('lastname')->nullable();
                $table->string('email')->nullable();
                $table->string('url')->nullable();
                $table->string('othercompany')->nullable();
                $table->integer('value')->nullable(); // valor completo en porcentaje 79%
                $table->text('configurates')->nullable(); // array con todos las actividades ['id' => 1, 'score' => 25]
                $table->integer('iduser')->nullable();
                $table->integer('idcourse')->nullable();
                $table->boolean('status')->nullable();
                $table->integer('company_id')->unsigned()->nullable();
                $table->foreign('company_id')->references('id')->on('companies');
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
        Schema::dropIfExists('certificates');
    }
}
