<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

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
            $table->unsignedInteger('kpi_category_id')->index();
            $table->string('name');
            $table->timestamps();

            $table->foreign('kpi_category_id')
                ->references('id')
                ->on('kpi_categories')
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
