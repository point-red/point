<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingPaymentOrderOthersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_payment_order_others', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chart_of_account_id')->index();
            $table->unsignedInteger('allocation_id')->index();
            $table->string('description')->index();
            $table->decimal('amount', 65, 30);

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
        Schema::dropIfExists('purchasing_payment_order_others');
    }
}
