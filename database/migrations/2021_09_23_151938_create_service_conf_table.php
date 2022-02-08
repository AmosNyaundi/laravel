<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateServiceConfTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('service_conf', function (Blueprint $table) {

            $table->id();
            $table->string('servicetype', 20);
            $table->string('spid', 20);
            $table->string('offercode', 20)->unique();
            $table->string('offername', 20);
            $table->string('price', 20);
            $table->string('keyword', 20);
            $table->string('senderId', 20);
            $table->string('username', 20);
            $table->string('groupname', 20);
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
        Schema::dropIfExists('service_conf');
    }
}
