<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingRequestsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_requests', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('form_id');
            $table->date('required_date');
            $table->unsignedInteger('employee_id');
            $table->unsignedInteger('supplier_id')->nullable();

            $table->foreign('form_id')->references('id')->on('forms')->onDelete('restrict');
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('restrict');
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
        Schema::dropIfExists('purchasing_requests');
    }
}
