<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinPointSalesVisitationNotInterestReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_point_sales_visitation_not_interest_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_visitation_id')->index('pin_point_sales_visitation_not_interest_reasons_sv_id_index');
            $table->string('name');
            $table->foreign('sales_visitation_id', 'pin_point_sales_visitation_not_interest_reasons_sv_id_foreign')->references('id')->on('pin_point_sales_visitations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pin_point_sales_visitation_not_interest_reasons');
    }
}
