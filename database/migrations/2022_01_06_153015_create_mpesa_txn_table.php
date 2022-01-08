<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateMpesaTxnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('mpesa_txn', function (Blueprint $table) {
            $table->id();
            $table->string('MerchantRequestID');
            $table->string('CheckoutRequestID');
            $table->string('ResponseCode');
            $table->string('ResponseDescription');
            $table->string('CustomerMessage');
            $table->string('requestId');
            $table->string('errorCode');
            $table->string('errorMessage');
            $table->string('ResultCode');
            $table->string('ResultDesc');
            $table->string('Amount');
            $table->string('MpesaReceiptNumber');
            $table->string('Balance');
            $table->timestamp('TransactionDate');
            $table->string('PhoneNumber');
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
        Schema::dropIfExists('mpesa_txn');
    }
}
