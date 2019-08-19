<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateMarketplaceItemUnitsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('marketplace_item_units', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('marketplace_item_id');
            $table->unsignedInteger('item_unit_id'); // reference from tenant item_units table
            $table->string('label', 5);
            $table->string('name');
            $table->decimal('converter', 65, 30)->default(1);
            $table->unsignedDecimal('price', 65, 30); // reference from tenant price_list_items table
            $table->unsignedDecimal('discount_percent', 65, 30); // reference from tenant price_list_items table
            $table->unsignedDecimal('discount_value', 65, 30); // reference from tenant price_list_items table
            $table->boolean('disabled')->default(false);
            $table->timestamps();

            $table->foreign('marketplace_item_id')->references('id')->on('marketplace_items')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('marketplace_item_units');
    }
}
