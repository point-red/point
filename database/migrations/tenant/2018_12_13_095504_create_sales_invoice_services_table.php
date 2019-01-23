<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesInvoiceServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoice_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_invoice_id');
            $table->unsignedInteger('sales_order_id');
            $table->unsignedInteger('sales_order_service_id');
            $table->unsignedInteger('service_id');
            $table->decimal('quantity', 65, 30);
            $table->decimal('price', 65, 30);
            $table->decimal('discount_percent', 65, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->boolean('taxable')->default(true);
            $table->text('description');
            $table->unsignedInteger('allocation_id')->nullable();

            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->onDelete('cascade');
            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
            $table->foreign('sales_order_service_id')->references('id')->on('sales_order_services')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('restrict');
            $table->foreign('allocation_id')->references('id')->on('allocations')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_invoice_services');
    }
}
