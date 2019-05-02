<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesPaymentCollectionsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_payment_collections', function (Blueprint $table) {
            $table->increments('id');
            $table->string('payment_type');
            $table->datetime('due_date')->nullable();
            $table->unsignedInteger('payment_account_id')->nullable();
            $table->decimal('amount', 65, 30);
            $table->unsignedInteger('supplier_id');
            $table->string('supplier_name');

            $table->foreign('payment_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_payment_collections');
    }
}
