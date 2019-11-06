<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesDownPaymentInvoice extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_down_payment_invoice', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('invoice_id');
            $table->unsignedInteger('down_payment_id');
            $table->decimal('amount', 65, 30);

            $table->foreign('invoice_id')->references('id')->on('sales_invoices')->onDelete('restrict');
            $table->foreign('down_payment_id')->references('id')->on('sales_down_payments')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_down_payment_invoice');
    }
}
