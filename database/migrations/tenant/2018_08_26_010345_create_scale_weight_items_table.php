<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateScaleWeightItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('scale_weight_items', function (Blueprint $table) {
            $table->increments('id');
            $table->string('uuid')->unique()->nullable();
            $table->string('machine_code');
            $table->string('form_number');
            $table->string('vendor');
            $table->string('driver')->nullable();
            $table->string('license_number')->nullable();
            $table->string('item');
            $table->unsignedDecimal('gross_weight', 32, 15);
            $table->unsignedDecimal('tare_weight', 32, 15);
            $table->unsignedDecimal('net_weight', 32, 15);
            $table->datetime('time');
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
        Schema::dropIfExists('scale_weight_items');
    }
}
