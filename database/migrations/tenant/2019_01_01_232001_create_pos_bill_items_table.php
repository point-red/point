<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePosBillItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('pos_bill_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('pos_bill_id');
            $table->unsignedInteger('item_id');
            $table->string('item_name');
            $table->decimal('quantity', 65, 30);
            $table->string('production_number')->nullable();
            $table->date('expiry_date')->nullable();
            $table->unsignedDecimal('price', 65, 30);
            $table->unsignedDecimal('discount_percent', 33, 30)->nullable();
            $table->unsignedDecimal('discount_value', 65, 30)->default(0);
            $table->boolean('taxable')->default(true);
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->text('notes')->nullable();

            $table->foreign('pos_bill_id')->references('id')->on('pos_bills')->onDelete('cascade');
            $table->foreign('item_id')->references('id')->on('items')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pos_bill_items');
    }
}
