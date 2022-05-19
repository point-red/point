<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashAdvanceDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cash_advance_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedDecimal('amount', 65, 30);
            $table->text('notes')->nullable();

            $table->foreign('cash_advance_id')->references('id')->on('cash_advances')->onDelete('cascade');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_advance_details');
    }
}
