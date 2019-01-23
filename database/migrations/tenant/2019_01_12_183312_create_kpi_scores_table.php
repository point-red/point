<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpiScoresTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_scores', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kpi_indicator_id')->index();
            $table->string('description');
            $table->unsignedInteger('score');
            $table->timestamps();

            $table->foreign('kpi_indicator_id')
                ->references('id')
                ->on('kpi_indicators')
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
        Schema::dropIfExists('kpi_scores');
    }
}
