<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

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
            $table->unsignedInteger('sub_ledger_id')->nullable()->index();
            $table->string('number', 20)->nullable()->unique();
            $table->string('name');
            $table->string('alias');
            $table->unsignedInteger('created_by')->index()->nullable();
            $table->unsignedInteger('updated_by')->index()->nullable();
            $table->unsignedInteger('archived_by')->index()->nullable();
            $table->timestamps();
            $table->timestamp('archived_at')->nullable();

            $table->unique(['number', 'name']);
            $table->foreign('created_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('archived_by')->references('id')->on('users')->onDelete('restrict');
            $table->foreign('type_id')->references('id')->on('chart_of_account_types')->onDelete('restrict');
            $table->foreign('group_id')->references('id')->on('chart_of_account_groups')->onDelete('set null');
            $table->foreign('sub_ledger_id')->references('id')->on('chart_of_account_sub_ledgers')->onDelete('set null');
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
