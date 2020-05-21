<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateReceiveItemItemsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('receive_item_items', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('receive_item_id')->index();
            $table->unsignedInteger('item_id')->index();
            $table->unsignedDecimal('quantity', 65, 30);
            $table->datetime('expiry_date')->nullable();
            $table->string('production_number')->nullable();
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->string('notes')->nullable();

            $table->foreign('receive_item_id')
                ->references('id')->on('receive_items')
                ->onUpdate('cascade')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('id')->on('items')
                ->onUpdate('restrict')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('receive_item_items');
    }
}
