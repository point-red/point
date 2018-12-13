<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseOrdersTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_orders', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('purchase_request_id')->nullable();
            $table->unsignedInteger('purchase_contract_id')->nullable();
            $table->unsignedInteger('supplier_id');
            $table->unsignedInteger('warehouse_id')->nullable();
            $table->date('eta'); // estimated time arrival
            $table->boolean('cash_only')->default(false);
            $table->boolean('need_down_payment')->default(false);
            $table->decimal('delivery_fee', 65, 30);
            $table->decimal('discount_percent', 33, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->string('type_of_tax'); // include / exclude / non
            $table->decimal('tax', 65, 30);

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('restrict');
            $table->foreign('purchase_request_id')->references('id')->on('purchase_requests')->onDelete('restrict');
            $table->foreign('purchase_contract_id')->references('id')->on('purchase_contracts')->onDelete('restrict');
            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
            $table->foreign('warehouse_id')->references('id')->on('warehouses')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_orders');
    }
}
