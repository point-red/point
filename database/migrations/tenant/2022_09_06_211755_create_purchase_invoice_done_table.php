<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePurchaseInvoiceDoneTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchase_invoice_done', function (Blueprint $table) {
            $table->id();
            $table->unsignedInteger('purchase_invoice_id')->index();
            $table->string('ref_no')->nullable();
            $table->decimal('value', 65, 30);
            $table->timestamps();

            $table->foreign('purchase_invoice_id')
                ->references('id')
                ->on('purchase_invoices');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('purchase_invoice_done');
    }
}
