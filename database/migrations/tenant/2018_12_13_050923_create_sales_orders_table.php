<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_quotation_id')->nullable();
            $table->unsignedInteger('sales_contract_id')->nullable();
            $table->unsignedInteger('customer_id');
            $table->unsignedInteger('warehouse_id')->nullable();
            $table->date('eta'); // estimated time arrival
            $table->boolean('cash_only')->default(false);
            $table->boolean('need_down_payment')->default(false);
            $table->decimal('delivery_fee', 65, 30)->default(0);
            $table->decimal('discount_percent', 33, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->string('type_of_tax'); // include / exclude / non
            $table->decimal('tax', 65, 30);

            $table->foreign('sales_quotation_id')->references('id')->on('sales_quotations')->onDelete('restrict');
            $table->foreign('sales_contract_id')->references('id')->on('sales_contracts')->onDelete('restrict');
            $table->foreign('customer_id')->references('id')->on('customers')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
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
        Schema::dropIfExists('sales_orders');
    }
}
