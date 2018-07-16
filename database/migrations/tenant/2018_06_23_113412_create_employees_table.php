<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateEmployeesTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->increments('id');
            // Data related to personal info
            $table->unsignedInteger('person_id')->index();
            $table->string('last_education')->nullable();
            $table->timestamp('birth_date')->nullable();
            $table->string('birth_place')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('married_with')->nullable();
            $table->string('religion')->nullable();
            // Data related to job
            $table->unsignedInteger('employee_group_id')->nullable()->index();
            $table->timestamp('join_date')->nullable();
            $table->string('job_title')->nullable();
            $table->unsignedInteger('kpi_template_id')->nullable();
            $table->timestamps();
            // Relationship
            $table->foreign('person_id')
                ->references('id')
                ->on('persons')
                ->onDelete('cascade');

            $table->foreign('employee_group_id')
                ->references('id')
                ->on('employee_groups')
                ->onDelete('cascade');

            $table->foreign('kpi_template_id')
                ->references('id')
                ->on('kpi_templates')
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
        Schema::dropIfExists('employees');
    }
}
