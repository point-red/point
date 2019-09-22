<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreatePsychologyKraeplinColumnsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('psychology_kraeplin_columns', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kraeplin_id')->index();
            $table->integer('current_first_number');
            $table->integer('current_second_number');
            $table->integer('correct');
            $table->timestamps();

            // References
            $table->foreign('kraeplin_id')
                ->references('id')
                ->on('psychology_kraeplins')
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
        Schema::dropIfExists('psychology_kraeplin_column');
    }
}
