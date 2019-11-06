<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateUserWarehouseTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('user_warehouse', function (Blueprint $table) {
            $table->bigIncrements('id');
            $table->unsignedInteger('user_id')->index();
            $table->unsignedInteger('warehouse_id')->nullable()->index();
            $table->timestamps();
     
            $table->foreign('user_id')
                ->references('id')
                ->on('users')
                ->onDelete('cascade');

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('user_warehouse');
    }
}
