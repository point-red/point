<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payments', function (Blueprint $table) {
            $table->increments('id');
            // payment type should be payment-order, payment-collection, cash, bank or cheque
            $table->string('payment_type');
            // if this payment type is payment-order or payment-collection then we can set due date
            $table->datetime('due_date')->nullable();
            // chart of account of cash, bank, or cheque
            $table->unsignedInteger('payment_account_id');
            $table->boolean('disbursed');
            $table->decimal('amount', 65, 30);
            // with who we make / receive payment
            // it can be supplier / customer / employee
            $table->unsignedInteger('paymentable_id')->nullable();
            $table->string('paymentable_type')->nullable();
            $table->string('paymentable_name')->nullable();

            $table->foreign('payment_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payments');
    }
}
