<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMultimediaTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('multimedia', function (Blueprint $table) {
            $table->increments('id');
            $table->string('name');
            $table->text('description')->nullable();
            $table->string('url')->nullable();
            $table->dateTime('expiration')->nullable();
            $table->enum('type_file', ['csv', 'pdf', 'doc', 'docx', 'pps', 'ppt', 'xls', 'xlsx',
                                        'pptx', 'jpg', 'jpeg', 'gif', 'png', 'bmp', 'tiff', 'psd', 'mp3', 'mp4',
                                        '3gp','ogg', 'tar', 'zip', 'rar', '7z', 'sql'])->nullable();
            $table->string('file_location');
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
        Schema::dropIfExists('multimedia');
    }
}
