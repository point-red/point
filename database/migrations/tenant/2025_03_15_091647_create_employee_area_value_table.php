<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateEmployeeAreaValueTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('employee_area_values', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('job_location_id')->index();
            $table->unsignedInteger('year');
            $table->unsignedDecimal('value', '65', 30);
            $table->string('notes')->nullable();
            $table->timestamps();
            
            // Relationship
            $table->foreign('job_location_id')->references('id')->on('employee_job_locations')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('employee_area_values');
    }
}
