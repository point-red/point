<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdateOpeningStockWarehousesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('opening_stock_warehouses', function (Blueprint $table) {
            $table->string('production_number')->after('warehouse_id')->nullable();
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
        Schema::table('opening_stock_warehouses', function (Blueprint $table) {
            $table->dropColumn('production_number');
            $table->dropColumn('expiry_date');
        });
    }
}
