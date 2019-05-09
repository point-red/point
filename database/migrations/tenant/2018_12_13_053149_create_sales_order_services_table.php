<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrderServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_order_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_order_id');
            $table->unsignedInteger('sales_quotation_service_id')->nullable();
            $table->unsignedInteger('service_id');
            $table->string('service_name');
            $table->decimal('quantity', 65, 30);
            $table->unsignedDecimal('price', 65, 30);
            $table->unsignedDecimal('discount_percent', 33, 30)->nullable();
            $table->boolean('taxable')->default(true);
            $table->text('notes')->nullable();
            $table->unsignedInteger('allocation_id')->nullable();

            $table->foreign('sales_order_id')->references('id')->on('sales_orders')->onDelete('cascade');
            $table->foreign('sales_quotation_service_id')->references('id')->on('sales_quotation_services')->onDelete('cascade');
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
        Schema::dropIfExists('sales_order_services');
    }
}
