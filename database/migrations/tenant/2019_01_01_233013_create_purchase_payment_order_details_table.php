<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchasePaymentOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_payment_order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('purchase_payment_order_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedInteger('allocation_id')->nullable();
            $table->unsignedDecimal('amount', 65, 30);
            $table->text('notes')->nullable();
            // payment reference : invoice / down payment / return
            $table->unsignedInteger('referenceable_id')->nullable();
            $table->string('referenceable_type')->nullable();

            $table->foreign('purchase_payment_order_id')->references('id')->on('purchase_payment_orders')->onDelete('cascade');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('allocation_id')->references('id')->on('allocations')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_payment_order_details');
    }
}
