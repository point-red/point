<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePurchaseReceiveItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('purchase_receive_items', function (Blueprint $table) {
            $table->string('production_number')->after('item_name')->nullable();
            $table->date('expiry_date')->after('production_number')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('purchase_receive_items', function (Blueprint $table) {
            $table->dropColumn('production_number');
            $table->dropColumn('expiry_date');
        });
    }
}
