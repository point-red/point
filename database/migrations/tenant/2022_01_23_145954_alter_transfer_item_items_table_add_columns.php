<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterTransferItemItemsTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('transfer_item_items', function (Blueprint $table) {
            $table->string('item_name')->after('item_id');
            $table->unsignedDecimal('stock', 65, 30);
            $table->unsignedDecimal('balance', 65, 30);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('transfer_item_items', function (Blueprint $table) {
            $table->dropColumn(['item_name','stock', 'balance']);
        });
    }
}
