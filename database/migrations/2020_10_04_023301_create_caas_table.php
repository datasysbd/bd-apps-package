<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCaasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('caas', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('applicationId')->nullable();
            $table->string('subscriberId')->nullable();
            $table->string('amount')->nullable();
            $table->string('externalTrxId')->nullable();
            $table->string('internalTrxId')->nullable();
            $table->string('statusCode')->nullable();
            $table->string('statusDetail')->nullable();
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
        Schema::dropIfExists('caas');
    }
}
