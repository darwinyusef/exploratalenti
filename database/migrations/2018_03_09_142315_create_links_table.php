<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateLinksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('links', function (Blueprint $table) {
          $table->increments('id');
          $table->string('url');
          $table->string('name')->nullable();
          $table->string('icon')->nullable();
          $table->enum('location', ['header','footer','services','social','social_footer'])->nullable();
          $table->string('target')->nullable();
          $table->text('description')->nullable();
          $table->string('visible')->nullable();
          $table->string('notes')->nullable();
          $table->integer('parent_id')->nullable();
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
        Schema::dropIfExists('links');
    }
}
