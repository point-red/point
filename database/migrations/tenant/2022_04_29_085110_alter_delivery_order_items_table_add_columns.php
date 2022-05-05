<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AlterDeliveryOrderItemsTableAddColumns extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->dropColumn(['quantity']);

            $table->decimal('quantity_requested', 65, 30)->nullable();
            $table->decimal('quantity_delivered', 65, 30)->nullable();
            $table->decimal('quantity_remaining', 65, 30)->nullable();
            $table->decimal('converter_quantity', 65, 30)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('delivery_order_items', function (Blueprint $table) {
            $table->decimal('quantity', 65, 30);

            $table->dropColumn([
                'quantity_requested', 
                'quantity_delivered', 
                'quantity_remaining', 
                'converter_quantity'
            ]);
        });
    }
}
