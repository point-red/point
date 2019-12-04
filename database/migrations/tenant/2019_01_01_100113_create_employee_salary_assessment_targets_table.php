<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeSalaryAssessmentTargetsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_salary_assessment_targets', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('assessment_id')->index();
            $table->string('week_of_month');
            $table->unsignedDecimal('target', 65, 30);
            $table->timestamps();

            $table->foreign('assessment_id')
                ->references('id')
                ->on('employee_salary_assessments')
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
        Schema::dropIfExists('employee_salary_assessment_targets');
    }
}
