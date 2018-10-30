<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingPaymentOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_payment_order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chart_of_account_id')->index();
            $table->unsignedInteger('reference_id')->index();
            $table->string('reference_type')->index();
            $table->decimal('amount', 65, 30);

            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchasing_payment_order_details');
    }
}
