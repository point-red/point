<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateCutOffAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cut_off_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('cut_off_id')->index();
            $table->unsignedInteger('chart_of_account_id')->index();
            $table->decimal('debit', 65, 30)->default(0);
            $table->decimal('credit', 65, 30)->default(0);
            $table->timestamps();

            $table->foreign('cut_off_id')
                ->references('id')
                ->on('cut_offs')
                ->onDelete('cascade');

            $table->foreign('chart_of_account_id')
                ->references('id')
                ->on('chart_of_accounts')
                ->onDelete('restrict');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('cut_off_accounts');
    }
}
