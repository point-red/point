<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpiTemplateIndicatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_template_indicators', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kpi_template_group_id')->index();
            $table->string('name');
            $table->unsignedInteger('weight');
            $table->unsignedInteger('target');
            $table->timestamps();

            $table->foreign('kpi_template_group_id')
                ->references('id')
                ->on('kpi_template_groups')
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
        Schema::dropIfExists('kpi_template_indicators');
    }
}
