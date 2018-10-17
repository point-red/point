<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('chart_of_account_id');
            $table->string('code')->nullable();
            $table->string('barcode')->nullable();
            $table->string('name');
            $table->string('size')->nullable();
            $table->string('color')->nullable();
            $table->string('weight')->nullable();
            $table->boolean('disabled')->default(false);
            $table->timestamps();
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('items');
    }
}
