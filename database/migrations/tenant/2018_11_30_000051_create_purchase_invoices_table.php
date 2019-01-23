<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchaseInvoicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoices', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('supplier_id');
            $table->date('due_date');
            $table->decimal('delivery_fee', 65, 30)->default(0);
            $table->decimal('discount_percent', 65, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->string('type_of_tax'); // include / exclude / non
            $table->decimal('tax', 65, 30);

            $table->foreign('supplier_id')->references('id')->on('suppliers')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoices');
    }
}
