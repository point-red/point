<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('purchase_request_id')->nullable();
            $table->unsignedInteger('purchase_contract_id')->nullable();
            $table->unsignedInteger('supplier_id');
            $table->string('supplier_name');
            $table->string('supplier_address')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->string('billing_address')->nullable();
            $table->string('billing_phone')->nullable();
            $table->string('billing_email')->nullable();
            $table->string('shipping_address')->nullable();
            $table->string('shipping_phone')->nullable();
            $table->string('shipping_email')->nullable();
            $table->unsignedInteger('warehouse_id')->nullable();
            $table->datetime('eta'); // estimated time arrival
            $table->boolean('cash_only')->default(false);
            $table->decimal('need_down_payment', 65, 30)->default(0);
            $table->decimal('delivery_fee', 65, 30)->default(0);
            $table->unsignedDecimal('discount_percent', 33, 30)->nullable();
            $table->unsignedDecimal('discount_value', 65, 30)->default(0);
            $table->string('type_of_tax'); // include / exclude / non
            $table->decimal('tax', 65, 30);
            $table->decimal('amount', 65, 30);

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
