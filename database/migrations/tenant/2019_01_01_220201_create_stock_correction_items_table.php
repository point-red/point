<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateStockCorrectionItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('stock_correction_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('stock_correction_id')->index();
            $table->unsignedInteger('item_id')->index();
            $table->unsignedDecimal('quantity', 65, 30);
            $table->datetime('expiry_date')->nullable();
            $table->string('production_number')->nullable();
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->string('notes')->nullable();

            $table->foreign('stock_correction_id')
                ->references('id')->on('stock_corrections')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('stock_correction_items');
    }
}
