<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddDistrictPinPointSalesVisitations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pin_point_sales_visitations', function (Blueprint $table) {
            $table->string('district')->after('address')->nullable();
            $table->string('sub_district')->after('district')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pin_point_sales_visitations', function (Blueprint $table) {
            $table->dropColumn('district');
            $table->dropColumn('sub_district');
        });
    }
}
