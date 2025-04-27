<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateJobValueScoreSettings extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('job_value_score_settings', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedDecimal('minimum_kpi', '65', 30);
            $table->unsignedDecimal('minimum_coc', '65', 30);
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('job_value_score_settings');
    }
}
