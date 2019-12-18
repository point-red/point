<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateManufactureOutputsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('manufacture_outputs', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('manufacture_machine_id');
            $table->unsignedInteger('manufacture_process_id');
            $table->unsignedInteger('manufacture_input_id');
            $table->string('manufacture_machine_name');
            $table->string('manufacture_process_name');
            $table->text('notes')->nullable();

            $table->foreign('manufacture_machine_id')->references('id')->on('manufacture_machines')->onDelete('restrict');
            $table->foreign('manufacture_input_id')->references('id')->on('manufacture_inputs')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('manufacture_outputs');
    }
}
