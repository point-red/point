<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_returns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('purchase_invoice_id');
            $table->unsignedInteger('supplier_id');
            $table->string('supplier_name');
            $table->string('supplier_address')->nullable();
            $table->string('supplier_phone')->nullable();
            $table->decimal('tax', 65, 30);

            $table->foreign('purchase_invoice_id')->references('id')->on('purchase_invoices')->onDelete('restrict');
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
        Schema::dropIfExists('purchase_returns');
    }
}
