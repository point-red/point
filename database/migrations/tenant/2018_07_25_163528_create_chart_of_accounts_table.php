<?php

use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Database\Migrations\Migration;

class CreateChartOfAccountsTable extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('chart_of_accounts', function (Blueprint $table) {
            $table->increments('id');
            $table->unsignedInteger('type_id')->index();
            $table->unsignedInteger('group_id')->nullable()->index();
            $table->string('number', 20)->nullable()->unique();
            $table->string('name')->unique();
            $table->string('alias')->unique();
            $table->timestamps();

            $table->foreign('type_id')
                ->references('id')
                ->on('chart_of_account_types')
                ->onDelete('cascade');

            $table->foreign('group_id')
                ->references('id')
                ->on('chart_of_account_groups')
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
        Schema::dropIfExists('chart_of_accounts');
    }
}
