<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseContractItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_contract_items', function (Blueprint $table) {
            $table->unsignedInteger('purchase_contract_id');
            $table->unsignedInteger('item_unit_id');
            $table->decimal('price', 65, 30);
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->text('description');
            $table->unsignedInteger('allocation_id')->nullable();

            $table->foreign('purchase_contract_id')->references('id')->on('purchase_contracts')->onDelete('cascade');
            $table->foreign('item_unit_id')->references('id')->on('item_units')->onDelete('restrict');
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
        Schema::dropIfExists('purchase_contract_items');
    }
}
