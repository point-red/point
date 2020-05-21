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
            $table->unsignedInteger('allocation_id')->nullable()->index();
            $table->unsignedInteger('chart_of_account_id')->index();
            $table->unsignedDecimal('quantity', 65, 30);
            $table->datetime('expiry_date')->nullable();
            $table->string('production_number')->nullable();
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

            $table->foreign('allocation_id')
                ->references('id')->on('allocations')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('chart_of_account_id')
                ->references('id')->on('chart_of_accounts')
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
