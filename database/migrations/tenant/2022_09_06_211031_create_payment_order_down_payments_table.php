<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentOrderDownPaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_order_down_payments', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payment_order_id')->index();
            $table->unsignedInteger('purchase_down_payment_id')->index();
            $table->decimal('amount', 65, 30);
            $table->timestamps();

            $table->foreign('payment_order_id')
                ->references('id')
                ->on('payment_orders');
            $table->foreign('purchase_down_payment_id')
                ->references('id')
                ->on('purchase_down_payments');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_order_down_payments');
    }
}
