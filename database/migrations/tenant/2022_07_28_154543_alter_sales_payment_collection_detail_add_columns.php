<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterSalesPaymentCollectionDetailAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('sales_payment_collection_details', function (Blueprint $table) {
            $table->unsignedInteger('chart_of_account_id')->nullable()->change();
            $table->unsignedDecimal('available', 65, 30);
            $table->datetime('referenceable_form_date')->nullable();
            $table->string('referenceable_form_number')->nullable();
            $table->text('referenceable_form_notes')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('sales_payment_collection_details', function (Blueprint $table) {
            $table->unsignedInteger('chart_of_account_id')->nullable(false)->change();
            $table->dropColumn(['available', 'referenceable_form_date', 'referenceable_form_number', 'referenceable_form_notes']);
        });
    }
}
