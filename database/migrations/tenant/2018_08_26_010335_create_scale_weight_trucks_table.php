<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScaleWeightTrucksTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scale_weight_trucks', function (Blueprint $table) {
            $table->increments('id');
            $table->string('machine_code');
            $table->string('form_number');
            $table->string('vendor');
            $table->string('driver');
            $table->string('license_number');
            $table->string('item');
            $table->unsignedDecimal('gross_weight', 32, 15);
            $table->unsignedDecimal('tare_weight', 32, 15);
            $table->unsignedDecimal('net_weight', 32, 15);
            $table->datetime('time_in');
            $table->datetime('time_out');
            $table->string('user');
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
        Schema::dropIfExists('scale_weight_trucks');
    }
}
