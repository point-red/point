<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesPaymentCollectionDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_payment_collection_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_payment_collection_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedInteger('allocation_id')->nullable();
            $table->unsignedDecimal('amount', 65, 30);
            $table->text('notes')->nullable();
            // payment reference : invoice / down payment / return
            $table->unsignedInteger('referenceable_id')->nullable();
            $table->string('referenceable_type')->nullable();

            $table->foreign('sales_payment_collection_id', 'sales_payment_collection_details_spc_id')
                ->references('id')->on('sales_payment_collections')->onDelete('cascade');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
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
        Schema::dropIfExists('sales_payment_collection_details');
    }
}
