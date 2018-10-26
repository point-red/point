<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->increments('id');
            $table->datetime('date');
            $table->string('form_number')->index();
            $table->unsignedInteger('warehouse_id')->index();
            $table->unsignedInteger('item_id')->index();
            $table->decimal('quantity', 65, 30)->default(0);
            $table->decimal('price', 65, 30)->default(0);
            $table->decimal('cogs', 65, 30)->default(0);
            $table->decimal('total_quantity', 65, 30)->default(0);
            $table->decimal('total_value', 65, 30)->default(0);
            $table->boolean('need_recalculate')->default(false);
            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->onDelete('cascade');

            $table->foreign('form_number')
                ->references('number')
                ->on('forms')
                ->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}
