<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateSettingJournalsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('setting_journals', function (Blueprint $table) {
            $table->increments('id');
            $table->string('feature');
            $table->string('name');
            $table->text('description');
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->timestamps();

            $table->unique(['feature', 'name']);
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('setting_journals');
    }
}
