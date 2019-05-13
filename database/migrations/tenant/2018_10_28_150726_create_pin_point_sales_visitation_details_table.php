<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinPointSalesVisitationDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_point_sales_visitation_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_visitation_id')->index();
            $table->unsignedInteger('item_id')->index();
            $table->decimal('quantity', 65, 30);
            $table->unsignedDecimal('price', 65, 30);
            $table->foreign('sales_visitation_id')->references('id')->on('pin_point_sales_visitations')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pin_point_sales_visitation_details');
    }
}
