<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSalesContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('customer_id');
            $table->string('customer_name');
            $table->boolean('cash_only')->default(false);
            $table->boolean('need_down_payment')->default(false);
            $table->decimal('discount_percent', 65, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->string('type_of_tax'); // include / exclude / non
            $table->decimal('tax', 65, 30);
            $table->decimal('amount', 65, 30);

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
        Schema::dropIfExists('sales_contracts');
    }
}
