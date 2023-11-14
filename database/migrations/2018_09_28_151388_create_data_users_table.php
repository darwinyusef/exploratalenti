<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateDataUsersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('data_users', function (Blueprint $table) {
            $table->increments('id');
            $table->string('first')->nullable();
            $table->string('last')->nullable();
            $table->date('birth')->nullable();
            $table->string('card_id')->unique();
            $table->enum('type_card', ['CC', 'TI', 'CE', 'PS', 'NT'])->nullable();
            $table->enum('gender', ['M', 'F'])->nullable();
            $table->string('mobile')->nullable();
            $table->string('phone_home')->nullable();
            $table->string('address')->nullable();
            $table->string('neighborhood', 100)->nullable();
            $table->string('postcode')->nullable();
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
        Schema::dropIfExists('data_users');
    }
}
