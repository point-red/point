<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePurchasingReturnItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('purchasing_return_items', function (Blueprint $table) {
            $table->unsignedInteger('purchasing_return_id');
            $table->unsignedInteger('item_id');
            $table->decimal('quantity', 65, 30);
            $table->decimal('price', 65, 30);
            $table->decimal('discount_percent', 33, 30)->nullable();
            $table->decimal('discount_value', 65, 30)->default(0);
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->text('description');
            $table->unsignedInteger('allocation_id')->nullable();

            $table->foreign('purchasing_return_id')->references('id')->on('purchasing_returns')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
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
        Schema::dropIfExists('purchasing_return_items');
    }
}
