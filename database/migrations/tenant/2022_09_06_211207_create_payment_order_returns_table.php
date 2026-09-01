<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentOrderReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_order_returns', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('payment_order_id')->index();
            $table->unsignedInteger('purchase_return_id')->index();
            $table->decimal('amount', 65, 30);
            $table->timestamps();

            $table->foreign('payment_order_id')
                ->references('id')
                ->on('payment_orders');
            $table->foreign('purchase_return_id')
                ->references('id')
                ->on('purchase_returns');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_order_returns');
    }
}
