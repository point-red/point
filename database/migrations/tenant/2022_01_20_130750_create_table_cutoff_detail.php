<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCutoffDetail extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cutoff_details', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cutoff_account_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedInteger('cutoffable_id')->index();
            $table->string('cutoffable_type');
            $table->timestamps();

            $table->foreign('cutoff_account_id')->references('id')->on('cutoff_accounts')->onDelete('restrict');
            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cutoff_details');
    }
}
