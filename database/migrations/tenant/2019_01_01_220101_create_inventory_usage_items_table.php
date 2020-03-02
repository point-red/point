<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateInventoryUsageItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventory_usage_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('inventory_usage_id')->index();
            $table->unsignedInteger('item_id')->index();
            $table->unsignedDecimal('quantity', 65, 30);
            $table->datetime('expiry_date')->nullable();
            $table->string('production_number')->nullable();
            $table->unsignedDecimal('price', 65, 30);
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->string('notes')->nullable();

            $table->foreign('inventory_usage_id')
                ->references('id')->on('inventory_usages')
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
        Schema::dropIfExists('inventory_usage_items');
    }
}
