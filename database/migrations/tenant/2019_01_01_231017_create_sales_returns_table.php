<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_returns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_invoice_id');
            $table->unsignedInteger('customer_id');
            $table->string('customer_name');
            $table->string('customer_address')->nullable();
            $table->string('customer_phone')->nullable();
            $table->decimal('tax', 65, 30);

            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_returns');
    }
}
