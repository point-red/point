<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasePaymentOrderDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_payment_order_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('reference_id')->index();
            $table->string('reference_type')->index();
            $table->string('notes');
            $table->decimal('amount', 65, 30);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_payment_order_details');
    }
}
