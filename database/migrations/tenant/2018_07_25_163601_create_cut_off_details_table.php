<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateCutOffDetailsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('cut_off_details', function (Blueprint $table) {
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
        Schema::dropIfExists('cut_off_details');
    }
}
