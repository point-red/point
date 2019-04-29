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
            // payment type should be cash, bank or cheque
            // or payment order / payment collection
            $table->string('payment_type');
            // if payment_type is payment-order or payment-collection
            // payment type replacement should be cash, bank or cheque
            $table->string('payment_type_replacement')->nullable();
            // move payment order / collection number here when paid
            $table->string('payment_verification_number')->nullable();
            // if this pre payment type is payment-order or payment-collection then we can set due date
            $table->datetime('due_date')->nullable();
            // chart of account of cash, bank, or cheque
            // value null if payment_approval is not null
            // because no payment yet
            $table->unsignedInteger('payment_account_id')->nullable();
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
