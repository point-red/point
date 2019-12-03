<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateSalesQuotationServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('sales_quotation_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('sales_quotation_id');
            $table->unsignedInteger('service_id');
            $table->string('service_name');
            $table->decimal('quantity', 65, 30);
            $table->unsignedDecimal('price', 65, 30);
            $table->text('notes')->nullable();

            $table->foreign('sales_quotation_id')->references('id')->on('sales_quotations')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('sales_quotation_services');
    }
}
