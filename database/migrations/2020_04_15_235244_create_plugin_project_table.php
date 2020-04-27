<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreatePluginProjectTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('plugin_project', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('project_id')->index();
            $table->unsignedInteger('plugin_id')->index();
            $table->dateTime('expired_date');
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
        Schema::dropIfExists('plugin_project');
    }
}
