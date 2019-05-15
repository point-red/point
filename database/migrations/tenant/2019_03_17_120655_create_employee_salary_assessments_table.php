<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeeSalaryAssessmentsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_salary_assessments', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('employee_salary_id')->index();
            $table->string('name');
            $table->unsignedDecimal('weight', 5, 2);
            $table->timestamps();

            $table->foreign('employee_salary_id')
                ->references('id')
                ->on('employee_salaries')
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
        Schema::dropIfExists('employee_salary_assessments');
    }
}
