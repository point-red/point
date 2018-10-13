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
            $table->decimal('item_unit_id');
            $table->decimal('pricing_group_id');
            $table->timestamp('start_at');
            $table->timestamp('updated_at');
            $table->decimal('start_price');
            $table->decimal('updated_price');
            $table->decimal('start_discount_percentage')->nullable();
            $table->decimal('updated_discount_percentage')->nullable();
            $table->decimal('start_discount_value')->default(0);
            $table->decimal('updated_discount_value')->default(0);
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
