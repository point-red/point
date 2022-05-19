<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCashAdvancePaymentTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cash_advance_payment', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cash_advance_id')->nullable();
            $table->unsignedInteger('payment_id')->nullable();
            $table->datetime('archived_at')->nullable();

            $table->foreign('cash_advance_id')->references('id')->on('cash_advances')->onDelete('set null');
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
        Schema::dropIfExists('cash_advance_payment');
    }
}
