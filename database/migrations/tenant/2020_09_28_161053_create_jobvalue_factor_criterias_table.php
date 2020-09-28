<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobvalueFactorCriteriasTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('jobvalue_factor_criterias', function (Blueprint $table) {
            $table->increments('id');
            $table->string('level');
            $table->string('description');
            $table->bigInteger('score');
            $table->unsignedInteger('factor_id');
            $table->timestamps();

            $table->foreign('factor_id')->references('id')->on('jobvalue_group_factors');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('jobvalue_factor_criterias');
    }
}
