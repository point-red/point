<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseReceiveTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_receives', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_id');
            $table->unsignedInteger('warehouse_id');
            $table->unsignedInteger('purchase_order_id');
            $table->string('driver')->nullable();
            $table->string('license_plate')->nullable();

            $table->foreign('purchase_order_id')->references('id')->on('purchase_orders')->onDelete('restrict');
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
        Schema::dropIfExists('purchase_receives');
    }
}
