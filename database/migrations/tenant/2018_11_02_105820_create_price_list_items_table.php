<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePriceListItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('price_list_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pricing_group_id');
            $table->unsignedInteger('item_unit_id');
            $table->date('date');
            $table->decimal('price', 65, 30);
            $table->decimal('discount_percent', 33, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->unsignedInteger('created_by')->index()->nullable();
            $table->unsignedInteger('updated_by')->index()->nullable();
            $table->timestamps();

            $table->foreign('pricing_group_id')->references('id')->on('pricing_groups')->onDelete('cascade');
            $table->foreign('item_unit_id')->references('id')->on('item_units')->onDelete('cascade');

            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('price_list_items');
    }
}
