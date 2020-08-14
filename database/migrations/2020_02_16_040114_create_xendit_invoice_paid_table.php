<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateXenditInvoicePaidTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('xendit_invoice_paid', function (Blueprint $table) {
            $table->increments('id');
            $table->string('xendit_id');
            $table->string('external_id');
            $table->string('user_id');
            $table->boolean('is_high');
            $table->string('status');
            $table->string('merchant_name');
            $table->string('payer_email');
            $table->string('description');
            $table->string('bank_code');
            $table->string('payment_method');
            $table->string('payment_channel');
            $table->string('payment_destination');
            $table->string('currency');
            $table->unsignedDecimal('amount', 65, 30);
            $table->unsignedDecimal('paid_amount', 65, 30);
            $table->unsignedDecimal('adjusted_received_amount', 65, 30);
            $table->unsignedDecimal('fees_paid_amount', 65, 30);
            $table->timestamp('paid_at');
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
        Schema::dropIfExists('xendit_invoice_paid');
    }
}
