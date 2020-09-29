<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->string('feature')->unique();
            $table->string('name')->unique();
            $table->text('description');
            $table->unsignedInteger('chart_of_account_id')->nullable();
            $table->timestamps();

            // $table->unique(['feature', 'name']);
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
