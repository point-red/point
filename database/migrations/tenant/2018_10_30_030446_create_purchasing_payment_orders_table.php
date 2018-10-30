<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingPaymentOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_payment_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('supplier_id');
            $table->date('due_date');
            $table->string('payment_type'); // cash or bank or cheque

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('restrict');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchasing_payment_orders');
    }
}
