<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufactureOutputFinishGoodsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacture_output_finish_goods', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('manufacture_output_id');
            $table->unsignedInteger('manufacture_input_finish_good_id');
            $table->string('item_name');
            $table->string('warehouse_name');
            $table->decimal('quantity', 65, 30);
            $table->string('production_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->string('unit');

            $table->foreign('manufacture_output_id')->references('id')->on('manufacture_outputs')->onDelete('cascade');
            $table->foreign('manufacture_input_finish_good_id')->references('id')->on('manufacture_input_finish_goods')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacture_output_finish_goods');
    }
}
