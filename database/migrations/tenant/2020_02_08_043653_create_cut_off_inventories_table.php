<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCutOffInventoriesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cut_off_inventories', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cut_off_id')->index();
            $table->unsignedInteger('item_id')->index();
            $table->decimal('quantity', 65, 30);
            $table->string('unit');
            $table->decimal('converter', 65, 30);
            $table->unsignedDecimal('price', 65, 30);
            $table->unsignedDecimal('total', 65, 30);
            $table->timestamps();

            $table->foreign('cut_off_id')
                ->references('id')
                ->on('cut_offs')
                ->onDelete('cascade');

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
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
        Schema::dropIfExists('cut_off_inventories');
    }
}
