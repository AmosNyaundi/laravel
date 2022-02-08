<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSafTxnTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('saf_txn', function (Blueprint $table) {
            $table->id();
            $table->string('msisdn');
            $table->text('content');
            $table->string('offercode');
            $table->string('linkid');
            $table->string('resltStatus');
            $table->string('ResultDetails');
            $table->string('statusCode');
            $table->string('requestId');
            $table->string('responseId');
            $table->string('spid');
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
        Schema::dropIfExists('saf_txn');
    }
}
