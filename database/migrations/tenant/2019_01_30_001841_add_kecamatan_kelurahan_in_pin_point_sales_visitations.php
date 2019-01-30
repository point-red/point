<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class AddKecamatanKelurahanInPinPointSalesVisitations extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('pin_point_sales_visitations', function (Blueprint $table) {
            $table->string('kecamatan')->after('address');
            $table->string('kelurahan')->after('kecamatan');
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
            $table->dropColumn('kecamatan');
            $table->dropColumn('kelurahan');
        });
    }
}
