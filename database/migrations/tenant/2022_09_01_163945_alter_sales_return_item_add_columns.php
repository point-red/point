<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSalesReturnItemAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->unsignedInteger('sales_invoice_item_id')->index()->after('sales_return_id');
            $table->unsignedInteger('quantity_sales')->before('quantity');
            $table->datetime('expiry_date')->nullable()->after('converter');
            $table->string('production_number')->nullable()->after('expiry_date');

            $table->foreign('sales_invoice_item_id')->references('id')->on('sales_invoice_items')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_return_items', function (Blueprint $table) {
            $table->dropForeign(['sales_invoice_item_id']);
            $table->dropColumn(['quantity','sales_invoice_item_id','expiry_date','production_number']);
        });
    }
}
