<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('form_id')->index();
            $table->unsignedInteger('warehouse_id')->index();
            $table->unsignedInteger('item_id')->index();
            $table->decimal('quantity', 65, 30)->default(0);
            $table->unsignedDecimal('price', 65, 30)->default(0);
            $table->decimal('cogs', 65, 30)->default(0);
            $table->decimal('total_quantity', 65, 30)->default(0);
            $table->decimal('total_value', 65, 30)->default(0);
            $table->boolean('need_recalculate')->default(false);
            $table->boolean('is_audit')->default(false);
            $table->timestamps();

            $table->foreign('warehouse_id')
                ->references('id')
                ->on('warehouses')
                ->onDelete('restrict');

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
                ->onUpdate('restrict')
                ->onDelete('restrict');

            $table->foreign('form_id')
                ->references('id')
                ->on('forms')
                ->onUpdate('cascade')
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
