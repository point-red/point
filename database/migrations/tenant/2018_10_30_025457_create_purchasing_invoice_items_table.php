<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingInvoiceItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_invoice_items', function (Blueprint $table) {
            $table->unsignedInteger('purchasing_invoice_id');
            $table->unsignedInteger('purchasing_received_id')->nullable();
            $table->unsignedInteger('item_id');
            $table->decimal('quantity', 65, 30);
            $table->decimal('price', 65, 30);
            $table->decimal('discount_percent', 33, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->boolean('taxable')->default(true);
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->text('description');
            $table->unsignedInteger('allocation_id')->nullable();

            $table->foreign('purchasing_invoice_id')->references('id')->on('purchasing_invoices')->onDelete('cascade');
            $table->foreign('purchasing_received_id')->references('id')->on('purchasing_received')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
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
        Schema::dropIfExists('purchasing_invoice_items');
    }
}
