<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateTableCutoffAccounts extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cutoff_accounts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('cutoff_id');
            $table->unsignedInteger('chart_of_account_id');
            $table->unsignedDecimal('debit', '65', 30);
            $table->unsignedDecimal('credit', '65', 30);
            $table->timestamps();

            $table->foreign('chart_of_account_id')->references('id')->on('chart_of_accounts')->onDelete('restrict');
            $table->foreign('cutoff_id')->references('id')->on('cutoffs_new')->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cutoff_accounts');
    }
}
