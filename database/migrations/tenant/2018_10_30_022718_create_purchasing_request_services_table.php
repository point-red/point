<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingRequestServicesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_request_services', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('purchasing_request_id');
            $table->unsignedInteger('service_id');
            $table->decimal('quantity', 65, 30);
            $table->text('description');
            $table->unsignedInteger('allocation_id')->nullable();

            $table->foreign('purchasing_request_id')->references('id')->on('purchasing_requests')->onDelete('cascade');
            $table->foreign('service_id')->references('id')->on('services')->onDelete('restrict');
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
        Schema::dropIfExists('purchasing_request_services');
    }
}
