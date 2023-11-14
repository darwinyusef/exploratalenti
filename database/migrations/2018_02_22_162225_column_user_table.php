<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class ColumnUserTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('users', function (Blueprint $table) {
            $table->boolean('active')->nullable();
            $table->boolean('pago')->nullable();
            $table->string('validate_token')->nullable();
            $table
                ->enum('project', [
                    'aquicreamos',
                    'darwinyusef',
                    'english',
                    'talenti',
                    'cristianismodigital',
                ])
                ->nullable();
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
        //
    }
}
