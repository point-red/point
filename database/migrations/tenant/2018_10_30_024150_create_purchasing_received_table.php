<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingReceivedTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_received', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('supplier_id');
            $table->unsignedInteger('warehouse_id');
            $table->unsignedInteger('purchasing_order_id');
            $table->string('driver')->nullable();
            $table->string('license_plate')->nullable();

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
            $table->foreign('purchasing_order_id')->references('id')->on('purchasing_orders')->onDelete('restrict');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchasing_receiveds');
    }
}
