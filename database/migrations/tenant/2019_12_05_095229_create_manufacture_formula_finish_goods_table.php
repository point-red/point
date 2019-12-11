<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufactureFormulaFinishGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacture_formula_finish_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('manufacture_formula_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('warehouse_id');
            $table->string('item_name');
            $table->string('warehouse_name');
            $table->decimal('quantity', 65, 30);
            $table->string('unit');

            $table->foreign('manufacture_formula_id')->references('id')->on('manufacture_formulas')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
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
        Schema::dropIfExists('manufacture_formula_finished_goods');
    }
}
