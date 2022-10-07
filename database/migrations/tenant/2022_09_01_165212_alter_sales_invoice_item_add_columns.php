<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSalesInvoiceItemAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->decimal('quantity_returned', 65, 30)->nullable()->after('quantity');
            $table->decimal('quantity_remaining', 65, 30)->nullable()->after('quantity_returned');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_invoice_items', function (Blueprint $table) {
            $table->dropColumn(['quantity_returned','quantity_remaining']);
        });
    }
}
