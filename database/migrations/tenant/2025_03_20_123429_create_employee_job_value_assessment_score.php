<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeJobValueAssessmentScore extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_value_assessment_scores', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('job_value_assessment_id')->index();
            $table->unsignedInteger('criteria_id')->index();
            $table->unsignedDecimal('score', '65', 30);
            $table->string('description');
            $table->unsignedDecimal('value', '65', 30);
            $table->string('note')->nullable();
            $table->timestamps();

            $table->foreign('job_value_assessment_id')->references('id')->on('job_value_assessments')->onDelete('cascade');
            $table->foreign('criteria_id')->references('id')->on('job_value_criterias')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_value_assessment_scores');
    }
}
