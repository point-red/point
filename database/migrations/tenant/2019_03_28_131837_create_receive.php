<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateReceive extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receives', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('warehouse_from');
            $table->unsignedInteger('warehouse_to');
            $table->string("note")->nullable();
            
            $table->foreign('warehouse_from')->references('id')->on('warehouses')->onDelete('restrict');
            $table->foreign('warehouse_to')->references('id')->on('warehouses')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receives');
    }
}
