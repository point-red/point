<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class UpdatePosBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pos_bill_items', function (Blueprint $table) {
            $table->string('production_number')->after('item_name')->nullable()->unique();
            $table->date('expiry_date')->after('production_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pos_bill_items', function (Blueprint $table) {
            $table->dropColumn('production_number');
            $table->dropColumn('expiry_date');
        });
    }
}
