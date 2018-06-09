<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpiScoreDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_score_details', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kpi_score_id')->index();
            $table->string('description');
            $table->unsignedInteger('score');
            $table->timestamps();

            $table->foreign('kpi_score_id')
                ->references('id')
                ->on('kpi_scores')
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
        Schema::dropIfExists('kpi_score_details');
    }
}
