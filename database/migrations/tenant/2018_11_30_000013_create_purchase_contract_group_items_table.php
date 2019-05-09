<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseContractGroupItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_contract_group_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('purchase_contract_id');
            $table->unsignedInteger('group_id');
            $table->string('group_name');
            $table->unsignedDecimal('price', 65, 30);
            $table->decimal('quantity', 65, 30);
            $table->text('notes');
            $table->unsignedInteger('allocation_id')->nullable();

            $table->foreign('purchase_contract_id')->references('id')->on('purchase_contracts')->onDelete('cascade');
            $table->foreign('group_id')->references('id')->on('groups')->onDelete('restrict');
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
        Schema::dropIfExists('purchase_contract_group_items');
    }
}
