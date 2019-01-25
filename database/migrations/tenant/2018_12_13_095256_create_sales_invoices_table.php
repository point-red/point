<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('customer_name');
            $table->string('billing_address');
            $table->string('billing_phone')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('shipping_address');
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_email')->nullable();
            $table->date('due_date');
            $table->decimal('delivery_fee', 65, 30)->default(0);
            $table->decimal('discount_percent', 65, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->string('type_of_tax'); // include / exclude / non
            $table->decimal('tax', 65, 30);

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
        Schema::dropIfExists('sales_invoices');
    }
}
