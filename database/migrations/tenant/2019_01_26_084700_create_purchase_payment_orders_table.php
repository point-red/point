<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasePaymentOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_payment_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('payment_type');
            $table->datetime('due_date')->nullable();
            $table->unsignedInteger('payment_account_id')->nullable();
            $table->decimal('amount', 65, 30);
            $table->unsignedInteger('supplier_id');
            $table->string('supplier_name');
            $table->unsignedInteger('payment_id')->nullable();

            $table->foreign('payment_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('payment_id')->references('id')->on('payments')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_payment_orders');
    }
}
