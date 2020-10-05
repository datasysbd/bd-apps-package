<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSmsSentFromServersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sms_sent_from_servers', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->string('requestId')->nullable();
            $table->string('applicationId')->nullable();
            $table->mediumText('smsBody')->nullable();
            $table->mediumText('destinationAddresses')->nullable();
            $table->string('statusCode')->nullable();
            $table->mediumText('statusDetail')->nullable();
            $table->string('timeStamp')->nullable();
            $table->string('rew_response')->nullable();
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
        Schema::dropIfExists('sms_sent_from_servers');
    }
}
