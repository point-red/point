<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingContractsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_contracts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->unsignedInteger('supplier_id');
            $table->decimal('amount', 65, 30);

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('cascade');
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
        Schema::dropIfExists('purchasing_contracts');
    }
}
