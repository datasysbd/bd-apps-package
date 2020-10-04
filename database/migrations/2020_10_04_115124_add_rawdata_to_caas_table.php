<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddRawdataToCaasTable extends Migration
{
    /**
     * Run the migrations.
     * php artisan make:migration add_device_id_to_caas_table --table=caas
     * @return void
     */
    public function up()
    {
        Schema::table('caas', function (Blueprint $table) {
            //
            $table->string('raw_response')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('caas', function (Blueprint $table) {
            //
        });
    }
}
