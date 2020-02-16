<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXenditFvaPaidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xendit_fva_paid', function (Blueprint $table) {
            $table->increments('id');
            $table->string('xendit_id');
            $table->string('external_id');
            $table->string('owner_id');
            $table->string('payment_id');
            $table->string('bank_code');
            $table->string('account_number');
            $table->string('callback_virtual_account_id');
            $table->string('merchant_code');
            $table->timestamp('transaction_timestamp');
            $table->unsignedDecimal('amount', 65, 30);
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
        Schema::dropIfExists('xendit_fva_paid');
    }
}
