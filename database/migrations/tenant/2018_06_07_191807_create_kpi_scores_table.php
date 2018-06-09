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
            $table->unsignedInteger('kpi_template_indicator_id')->index();
            $table->timestamps();

            $table->foreign('kpi_template_indicator_id')
                ->references('id')
                ->on('kpi_template_indicators')
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
