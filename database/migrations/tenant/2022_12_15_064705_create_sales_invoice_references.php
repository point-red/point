<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesInvoiceReferences extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_invoice_references', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_invoice_id')->index();
            $table->foreign('sales_invoice_id')->references('id')->on('sales_invoices')->onDelete('restrict');
            $table->string('referenceable_type')->nullable();
            $table->string('referenceable_id')->nullable();
            $table->decimal('amount', 65, 30);            
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_invoice_references');
    }
}
