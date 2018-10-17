<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePricingHistoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pricing_histories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_unit_id');
            $table->unsignedInteger('pricing_group_id');
            $table->timestamp('start_at');
            $table->timestamp('updated_at');
            $table->decimal('start_price');
            $table->decimal('updated_price');
            $table->decimal('start_discount_percentage')->nullable();
            $table->decimal('updated_discount_percentage')->nullable();
            $table->decimal('start_discount_value')->default(0);
            $table->decimal('updated_discount_value')->default(0);
            $table->foreign('item_unit_id')->references('id')->on('item_units')->onDelete('cascade');
            $table->foreign('pricing_group_id')->references('id')->on('pricing_groups')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pricing_histories');
    }
}
