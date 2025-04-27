<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobValueAssessmentCalculation extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_value_assessment_calculations', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('assessment_id')->index();
            $table->unsignedInteger('prev_assessment_id')->index()->nullable();
            $table->unsignedDecimal('score_diff', '65', 30);
            $table->unsignedDecimal('score_diff_pct', '65', 30);
            $table->unsignedInteger('prev_area_value_id')->index()->nullable();
            $table->unsignedDecimal('prev_area_value', '65', 30);
            $table->unsignedInteger('area_value_id')->index()->nullable();
            $table->unsignedDecimal('area_value', '65', 30);
            $table->unsignedDecimal('area_value_diff', '65', 30);
            $table->unsignedDecimal('area_value_pct', '65', 30);
            $table->unsignedDecimal('kpi_avg', '65', 30);
            $table->unsignedDecimal('current_point', '65', 30);
            $table->unsignedDecimal('next_year_point', '65', 30);
            $table->unsignedDecimal('basic_fee', '65', 30);
            $table->unsignedDecimal('prev_fee', '65', 30);
            $table->timestamps();

            $table->foreign('assessment_id')->references('id')->on('job_value_assessments')->onDelete('restrict');
            $table->foreign('prev_assessment_id')->references('id')->on('job_value_assessments')->onDelete('restrict');
            $table->foreign('area_value_id')->references('id')->on('employee_area_values')->onDelete('restrict');
            $table->foreign('prev_area_value_id')->references('id')->on('employee_area_values')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_value_assessment_calculations');
    }
}
