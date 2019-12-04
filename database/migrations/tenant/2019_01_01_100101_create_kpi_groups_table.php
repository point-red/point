<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateKpiGroupsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_groups', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kpi_id')->index();
            $table->string('name');
            $table->timestamps();

            $table->foreign('kpi_id')
                ->references('id')
                ->on('kpis')
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
        Schema::dropIfExists('kpi_groups');
    }
}
