<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingReturnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_returns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('purchasing_invoice_id');
            $table->unsignedInteger('supplier_id');
            $table->decimal('tax', 65, 30);

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('restrict');
            $table->foreign('purchasing_invoice_id')->references('id')->on('purchasing_invoices')->onDelete('restrict');
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
        Schema::dropIfExists('purchasing_returns');
    }
}
