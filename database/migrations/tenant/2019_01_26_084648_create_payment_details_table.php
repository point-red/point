<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('payment_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedInteger('allocation_id');
            $table->unsignedDecimal('amount', 65, 30);
            $table->text('notes');
            // with who we make / receive payment
            // it can be supplier / customer / employee
            $table->unsignedInteger('paymentable_id')->nullable();
            $table->string('paymentable_type')->nullable();
            // payment reference : invoice / down payment / return
            $table->unsignedInteger('referenceable_id')->nullable();
            $table->string('referenceable_type')->nullable();

            $table->foreign('payment_id', 'payment_details_payment_id_f')
                ->references('id')->on('payments')->onDelete('cascade');
            $table->foreign('chart_of_account_id', 'payment_details_chart_of_account_id_f')
                ->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('allocation_id', 'payment_details_allocation_id_f')
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
        Schema::dropIfExists('payment_details');
    }
}
