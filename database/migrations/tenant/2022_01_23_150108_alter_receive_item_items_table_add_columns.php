<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReceiveItemItemsTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receive_item_items', function (Blueprint $table) {
            $table->string('item_name')->after('item_id');
            $table->unsignedDecimal('stock', 65, 30);
            $table->unsignedDecimal('balance', 65, 30);
            $table->unsignedInteger('allocation_id')->nullable();

            $table->foreign('allocation_id')->references('id')->on('allocations')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receive_item_items', function (Blueprint $table) {
            $table->dropForeign(['allocation_id']);
            $table->dropColumn(['item_name', 'stock', 'balance', 'allocation_id']);
        });
    }
}
