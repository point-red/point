<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_order_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedDecimal('amount', 65, 30);
            $table->text('notes');

            $table->foreign('payment_order_id', 'payment_order_details_payment_order_id_f')
                ->references('id')->on('payment_orders')->onDelete('cascade');
            $table->foreign('chart_of_account_id', 'payment_order_details_chart_of_account_id_f')
                ->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_order_details');
    }
}
