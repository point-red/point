<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXenditRetailOutletPaidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xendit_retail_outlet_paid', function (Blueprint $table) {
            $table->increments('id');
            $table->string('xendit_id');
            $table->string('external_id');
            $table->string('user_id');
            $table->string('prefix');
            $table->string('retail_outlet_name');
            $table->string('name');
            $table->unsignedDecimal('amount', 65, 30);
            $table->unsignedDecimal('fees_paid_amount', 65, 30);
            $table->string('payment_id');
            $table->string('payment_code');
            $table->string('fixed_payment_code_payment_id');
            $table->string('fixed_payment_code_id');
            $table->string('status');
            $table->string('transaction_id');
            $table->timestamp('transaction_timestamp');
            $table->timestamp('created');
            $table->timestamp('updated');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('xendit_retail_outlet_paid');
    }
}
