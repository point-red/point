<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTransferItemsTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->unsignedInteger('to_warehouse_id')->index();
            $table->string('driver');

            $table->foreign('to_warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_items', function (Blueprint $table) {
            $table->dropForeign(['to_warehouse_id']);
            $table->dropColumn(['to_warehouse_id', 'driver', 'notes']);
        });
    }
}
