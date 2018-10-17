<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceListsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_lists', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pricing_group_id');
            $table->unsignedInteger('item_unit_id');
            $table->decimal('price', 65, 30);
            $table->decimal('discount_percent', 5, 2)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->timestamps();
            $table->foreign('pricing_group_id')->references('id')->on('pricing_groups')->onDelete('cascade');
            $table->foreign('item_unit_id')->references('id')->on('item_units')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_lists');
    }
}
