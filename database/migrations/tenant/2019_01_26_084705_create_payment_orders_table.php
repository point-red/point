<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePaymentOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->string('payment_type');
            $table->datetime('due_date')->nullable();
            $table->unsignedInteger('payment_account_id')->nullable();
            $table->decimal('amount', 65, 30);
            // with who we make / receive payment
            // it can be supplier / customer / employee
            $table->unsignedInteger('paymentable_id')->nullable();
            $table->string('paymentable_type')->nullable();
            $table->string('paymentable_name')->nullable();
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
        Schema::dropIfExists('payment_orders');
    }
}
