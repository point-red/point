<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobValueCriteriaScaleTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_value_criteria_scales', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('criteria_id')->index();
            $table->string('description');
            $table->unsignedDecimal('value', '65', 30);
            $table->timestamps();

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
        Schema::dropIfExists('job_value_criteria_scales');
    }
}
