<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasePaymentOrderOthersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_payment_order_others', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('purchase_payment_order_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedDecimal('amount', 65, 30);
            $table->text('notes');

            $table->foreign('purchase_payment_order_id', 'purchase_payment_order_others_payment_order_id_f')
                ->references('id')->on('purchase_payment_orders')->onDelete('cascade');
            $table->foreign('chart_of_account_id', 'purchase_payment_order_others_chart_of_account_id_f')
                ->references('id')->on('chart_of_accounts')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_payment_order_others');
    }
}
