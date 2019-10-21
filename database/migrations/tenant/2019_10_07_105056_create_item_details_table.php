<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateItemDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('item_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('item_id')->index();
            $table->unsignedDecimal('stock', '65', 30)->default(0);
            $table->string('production_number')->nullable()->unique();
            $table->date('expiry_date');
            $table->timestamps();

            $table->foreign('item_id')
                ->references('id')
                ->on('items')
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
        Schema::dropIfExists('item_details');
    }
}
