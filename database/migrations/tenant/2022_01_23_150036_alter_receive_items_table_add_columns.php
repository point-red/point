<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterReceiveItemsTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('receive_items', function (Blueprint $table) {
            $table->unsignedInteger('from_warehouse_id')->index();
            $table->unsignedInteger('transfer_item_id')->index();
            $table->string('driver');

            $table->foreign('transfer_item_id')->references('id')->on('transfer_items')->onDelete('restrict');
            $table->foreign('from_warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('receive_items', function (Blueprint $table) {
            $table->dropForeign(['from_warehouse_id']);
            $table->dropForeign(['transfer_item_id']);
            $table->dropColumn(['from_warehouse_id', 'transfer_item_id', 'driver', 'notes']);
        });
    }
}
