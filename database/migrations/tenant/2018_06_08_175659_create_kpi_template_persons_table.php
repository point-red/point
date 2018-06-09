<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateKpiTemplatePersonsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('kpi_template_persons', function (Blueprint $table) {
            $table->unsignedInteger('person_id')->unique();
            $table->unsignedInteger('kpi_template_id');
            $table->timestamps();

            $table->foreign('kpi_template_id')
                ->references('id')
                ->on('kpi_templates')
                ->onDelete('cascade');

            $table->foreign('person_id')
                ->references('id')
                ->on('persons')
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
        Schema::dropIfExists('kpi_template_persons');
    }
}
