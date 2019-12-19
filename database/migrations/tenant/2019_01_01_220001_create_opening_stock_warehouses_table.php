<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateOpeningStockWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('opening_stock_warehouses', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('opening_stock_id');
            $table->unsignedInteger('warehouse_id');
            $table->unsignedDecimal('quantity', 65, 30);
            $table->string('production_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedDecimal('price', 65, 30);

            $table->foreign('opening_stock_id')->references('id')->on('opening_stocks')->onDelete('cascade');
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
        Schema::dropIfExists('opening_stock_warehouses');
    }
}
