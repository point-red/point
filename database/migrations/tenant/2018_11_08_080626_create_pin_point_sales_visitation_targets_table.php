<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePinPointSalesVisitationTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pin_point_sales_visitation_targets', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('date');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedDecimal('call', 65, 30);
            $table->unsignedDecimal('effective_call', 65, 30);
            $table->unsignedDecimal('value', 65, 30);
            $table->timestamps();
            $table->unsignedInteger('created_by')->index();
            $table->unsignedInteger('updated_by')->index();

            $table->foreign('user_id')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pin_point_sales_visitation_targets');
    }
}
