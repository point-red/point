<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinPointSalesVisitationInterestReasonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_point_sales_visitation_interest_reasons', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_visitation_id')->index();
            $table->string('name');
            $table->foreign('sales_visitation_id')->references('id')->on('sales_visitations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pin_point_sales_visitation_interest_reasons');
    }
}
