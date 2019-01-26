<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentCashDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_cash_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_cash_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedInteger('allocation_id');
            $table->unsignedDecimal('amount', 65, 30);
            $table->text('notes');
//            $table->unsignedInteger('paymentable_id')->nullable();
//            $table->string('paymentable_type')->nullable();
//            $table->unsignedInteger('referenceable_id')->nullable();
//            $table->string('referenceable_type')->nullable();

            $table->foreign('payment_cash_id', 'payment_cash_details_payment_cash_id_f')
                ->references('id')->on('payment_cashes')->onDelete('cascade');
            $table->foreign('chart_of_account_id', 'payment_cash_details_chart_of_account_id_f')
                ->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('allocation_id', 'payment_cash_details_allocation_id_f')
                ->references('id')->on('allocations')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cash_details');
    }
}
