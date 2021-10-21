<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSettingLogoTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_logos', function (Blueprint $table) {
            $table->increments('id');
            $table->string('purchase_request')->nullable();
            $table->string('purchase_order')->nullable();
            $table->string('purchase_down_payment')->nullable();
            $table->string('purchase_receive')->nullable();
            $table->string('purchase_invoice')->nullable();
            $table->string('purchase_return')->nullable();
            $table->string('payment_order_purchase')->nullable();
            $table->string('point_of_sales')->nullable();
            $table->string('sales_quotation')->nullable();
            $table->string('sales_order')->nullable();
            $table->string('sales_down_payment')->nullable();
            $table->string('sales_invoice')->nullable();
            $table->string('sales_return')->nullable();
            $table->string('payment_collection_sales')->nullable();
            $table->string('expedition_order')->nullable();
            $table->string('expedition_down_payment')->nullable();
            $table->string('expedition_invoice')->nullable();
            $table->string('payment_order_expedition')->nullable();
            $table->unsignedInteger('created_by');
            $table->unsignedInteger('updated_by')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setting_logos');
    }
}
