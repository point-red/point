<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCutoffInventory extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cutoff_inventories', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedInteger('item_id');
            $table->unsignedInteger('warehouse_id');
            $table->unsignedDecimal('quantity', '65', 30);
            $table->string('unit');
            $table->unsignedDecimal('converter', '65', 30);
            $table->unsignedDecimal('price', '65', 30);
            $table->unsignedDecimal('total', '65', 30);
            $table->timestamps();

            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');

            $table->index(['chart_of_account_id', 'item_id'], 'data_key');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cutoff_inventories');
    }
}
