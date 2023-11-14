<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateTaxonomiesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('taxonomies', function (Blueprint $table) {
            $table->increments('id');
            $table->string('taxonomy');
            $table->enum('type', ['category','item','tag','publicity', 'course', 'category-course','category-content','tag-content','tag-course'])->nullable();
            $table->text('description')->nullable();
            $table->integer('parent')->nullable();
            $table->enum('project', ['aquicreamos', 'darwinyusef', 'english', 'talenti', 'cristianismodigital'])->nullable();
            $table->softDeletes();
            $table->timestamps();
        });
    }

    public function down(){
        Schema::dropIfExists('taxonomies');
    }
}
