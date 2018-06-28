<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpisTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpis', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('kpi_group_id')->index();
            $table->string('indicator');
            $table->unsignedInteger('weight');
            $table->unsignedInteger('target');
            $table->unsignedInteger('score');
            $table->float('score_percentage');
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
        Schema::dropIfExists('kpis');
    }
}
