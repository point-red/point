<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePaymentCashesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('payment_cashes', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('paymentable_id');
            $table->string('paymentable_type');
            $table->date('due_date');
            $table->boolean('disbursed');
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
        Schema::dropIfExists('payment_cashes');
    }
}
