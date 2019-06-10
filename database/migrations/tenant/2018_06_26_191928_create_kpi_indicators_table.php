<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpiIndicatorsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_indicators', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kpi_group_id')->index();
            $table->string('name');
            $table->unsignedDecimal('weight', 5, 2);
            $table->unsignedInteger('target');
            $table->string('automated_code')->nullable();
            $table->unsignedInteger('score');
            $table->float('score_percentage');
            $table->text('score_description');
            $table->timestamps();

            $table->foreign('kpi_group_id')
                ->references('id')
                ->on('kpi_groups')
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
        Schema::dropIfExists('kpi_indicators');
    }
}
