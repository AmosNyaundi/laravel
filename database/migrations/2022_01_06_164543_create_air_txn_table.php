<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateAirTxnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('air_txn', function (Blueprint $table) {
            $table->id();
            $table->string('responseId');
            $table->text('responseDesc');
            $table->string('responseStatus');
            $table->string('transId');
            //$table->string('Cust_message');
            $table->string('senderMsisdn');
            $table->string('amount');
            $table->string('receiverMsisdn');
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
        Schema::dropIfExists('air_txn');
    }
}
