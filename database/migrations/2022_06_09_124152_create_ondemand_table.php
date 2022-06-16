<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOndemandTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('ondemand', function (Blueprint $table) {
            $table->id();
            $table->string('msisdn');
            $table->string('criteria');
            $table->string('message');
            $table->string('shortcode');
            $table->string('offercode');
            $table->string('keyword');
            $table->string('linkid');
            $table->string('requestid');
            $table->string('clientTransactionId');
            $table->string('referenceId');
            $table->string('spid');
            $table->string('requestTimeStamp');
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
        Schema::dropIfExists('ondemand');
    }
}
