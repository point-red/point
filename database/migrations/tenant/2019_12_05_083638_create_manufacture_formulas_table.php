<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufactureFormulasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacture_formulas', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('manufacture_process_id');
            $table->string('manufacture_process_name');
            $table->string('name');
            $table->text('notes')->nullable();

            $table->foreign('manufacture_process_id')->references('id')->on('manufacture_processes')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacture_formulas');
    }
}
