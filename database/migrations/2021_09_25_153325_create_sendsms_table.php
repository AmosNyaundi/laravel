<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSendsmsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sendsms', function (Blueprint $table) {
            $table->id();
            $table->string('msisdn');
            $table->string('spid');
            $table->string('offercode');
            $table->text('message');
            $table->string('shortcode');
            $table->string('keyword');
            $table->string('flag');
            $table->string('username');
            $table->string('groupname');
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
        Schema::dropIfExists('sendsms');
    }
}
